"""
NLP Processing Router

Handles text processing, tokenization, and language detection.
"""

import re
import string
from collections import Counter
from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field

from app.core.config import settings

router = APIRouter()


class TextProcessRequest(BaseModel):
    """Request model for text processing."""
    text: str = Field(..., max_length=settings.MAX_TEXT_LENGTH, description="Text to process")
    language: str = Field(default="ar", description="Language code (ar, en)")


class TextProcessResponse(BaseModel):
    """Response model for processed text."""
    data: dict


# ─── Arabic stop words ───
ARABIC_STOP_WORDS = {
    'في', 'من', 'على', 'إلى', 'عن', 'مع', 'هذا', 'هذه', 'ذلك', 'تلك',
    'التي', 'الذي', 'هو', 'هي', 'نحن', 'هم', 'أن', 'إن', 'كان', 'كانت',
    'يكون', 'لا', 'لم', 'لن', 'قد', 'ما', 'و', 'أو', 'ثم', 'بل',
    'لكن', 'حتى', 'إذا', 'إذ', 'بعد', 'قبل', 'بين', 'كل', 'بعض', 'غير',
    'أي', 'أيضا', 'كما', 'عند', 'عندما', 'منذ', 'خلال', 'حول', 'ضد',
    'فوق', 'تحت', 'أمام', 'وراء', 'داخل', 'خارج', 'حيث', 'كيف', 'لماذا',
    'متى', 'أين', 'التي', 'الذين', 'اللذان', 'اللتان', 'الذي',
}

ENGLISH_STOP_WORDS = {
    'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
    'of', 'with', 'by', 'from', 'is', 'was', 'are', 'were', 'be', 'been',
    'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
    'could', 'should', 'may', 'might', 'can', 'shall', 'it', 'its', 'this',
    'that', 'these', 'those', 'i', 'you', 'he', 'she', 'we', 'they',
    'not', 'no', 'nor', 'so', 'very', 'just', 'than', 'too', 'also',
}


def detect_language(text: str) -> str:
    """Simple language detection based on character set analysis."""
    arabic_chars = len(re.findall(r'[\u0600-\u06FF]', text))
    latin_chars = len(re.findall(r'[a-zA-Z]', text))

    if arabic_chars > latin_chars:
        return "ar"
    return "en"


def clean_text(text: str, language: str = "ar") -> str:
    """Clean and normalize text."""
    # Remove extra whitespace
    text = re.sub(r'\s+', ' ', text).strip()

    # Remove special characters but keep Arabic/English letters
    if language == "ar":
        # Keep Arabic letters, digits, and basic punctuation
        text = re.sub(r'[^\u0600-\u06FF\u0750-\u077F\s\d.,!?؟،؛]', '', text)
    else:
        text = re.sub(r'[^a-zA-Z\s\d.,!?]', '', text)

    return text


def tokenize(text: str, language: str = "ar") -> list:
    """Simple whitespace tokenizer with stop word removal."""
    stop_words = ARABIC_STOP_WORDS if language == "ar" else ENGLISH_STOP_WORDS

    tokens = text.split()
    # Remove stop words and short tokens
    tokens = [t for t in tokens if t not in stop_words and len(t) > 1]

    return tokens


def extract_sentences(text: str) -> list:
    """Split text into sentences."""
    # Split on Arabic and English sentence terminators
    sentences = re.split(r'[.!?؟]\s*', text)
    return [s.strip() for s in sentences if s.strip()]


@router.post("/process-text", response_model=TextProcessResponse)
async def process_text(request: TextProcessRequest):
    """
    Process text through the NLP pipeline.

    Performs:
    - Language detection
    - Text cleaning & normalization
    - Tokenization (with stop word removal)
    - Sentence splitting
    - Basic statistics
    """
    if not request.text.strip():
        raise HTTPException(status_code=400, detail="Text cannot be empty")

    # Detect language if not specified or set to auto
    language = request.language
    if language not in ("ar", "en"):
        language = detect_language(request.text)

    # Clean text
    cleaned = clean_text(request.text, language)

    # Tokenize
    tokens = tokenize(cleaned, language)

    # Extract sentences
    sentences = extract_sentences(request.text)

    # Word frequency
    word_freq = Counter(tokens).most_common(20)

    return TextProcessResponse(data={
        "language": language,
        "original_length": len(request.text),
        "cleaned_text": cleaned[:1000],  # Truncate for response
        "tokens": tokens[:100],          # Top 100 tokens
        "token_count": len(tokens),
        "sentence_count": len(sentences),
        "word_count": len(request.text.split()),
        "char_count": len(request.text),
        "word_frequency": [{"word": w, "count": c} for w, c in word_freq],
    })
