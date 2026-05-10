"""
Metadata Extraction Router

Extracts structured metadata and keywords from academic text.
"""

import re
from collections import Counter
from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field

from app.core.config import settings

router = APIRouter()


class ExtractMetadataRequest(BaseModel):
    """Request model for metadata extraction."""
    text: str = Field(..., max_length=settings.MAX_TEXT_LENGTH)


class ExtractKeywordsRequest(BaseModel):
    """Request model for keyword extraction."""
    text: str = Field(..., max_length=settings.MAX_TEXT_LENGTH)
    max_keywords: int = Field(default=10, ge=1, le=50)


class MetadataResponse(BaseModel):
    """Response model for extracted metadata."""
    data: dict


# ─── Arabic stop words for filtering ───
ARABIC_STOP_WORDS = {
    'في', 'من', 'على', 'إلى', 'عن', 'مع', 'هذا', 'هذه', 'ذلك', 'تلك',
    'التي', 'الذي', 'هو', 'هي', 'نحن', 'هم', 'أن', 'إن', 'كان', 'كانت',
    'يكون', 'لا', 'لم', 'لن', 'قد', 'ما', 'و', 'أو', 'ثم', 'بل',
    'لكن', 'حتى', 'إذا', 'إذ', 'بعد', 'قبل', 'بين', 'كل', 'بعض', 'غير',
    'أي', 'أيضا', 'كما', 'عند', 'عندما', 'منذ', 'خلال', 'حول', 'باستخدام',
    'يتم', 'تم', 'التي', 'هذه', 'هذا', 'تلك', 'ذلك', 'والتي', 'والذي',
}


def detect_language(text: str) -> str:
    """Detect text language."""
    arabic_chars = len(re.findall(r'[\u0600-\u06FF]', text))
    latin_chars = len(re.findall(r'[a-zA-Z]', text))
    return "ar" if arabic_chars > latin_chars else "en"


def extract_keywords_from_text(text: str, max_keywords: int = 10) -> list:
    """
    Extract keywords using TF-based scoring.

    Uses word frequency analysis with stop word filtering
    and minimum length requirements for meaningful keyword extraction.
    """
    # Tokenize
    words = re.findall(r'[\u0600-\u06FFa-zA-Z]{3,}', text)

    # Filter stop words
    filtered = [w for w in words if w.lower() not in ARABIC_STOP_WORDS and len(w) > 2]

    # Count frequencies
    freq = Counter(filtered)
    total = len(filtered) if filtered else 1

    # Calculate TF scores
    keywords = []
    for word, count in freq.most_common(max_keywords):
        score = round(count / total, 4)
        keywords.append({
            "keyword": word,
            "score": score,
            "frequency": count,
        })

    return keywords


def generate_summary(text: str, max_sentences: int = 3) -> str:
    """
    Generate a simple extractive summary.

    Selects the most representative sentences based on
    keyword density scoring.
    """
    # Split into sentences
    sentences = re.split(r'[.!?؟]\s*', text)
    sentences = [s.strip() for s in sentences if len(s.strip()) > 20]

    if len(sentences) <= max_sentences:
        return '. '.join(sentences)

    # Score sentences by keyword density
    all_words = re.findall(r'[\u0600-\u06FFa-zA-Z]{3,}', text.lower())
    word_freq = Counter(all_words)

    scored = []
    for i, sentence in enumerate(sentences):
        words = re.findall(r'[\u0600-\u06FFa-zA-Z]{3,}', sentence.lower())
        score = sum(word_freq.get(w, 0) for w in words)
        # Boost first sentences (positional weighting)
        if i < 2:
            score *= 1.5
        scored.append((score, i, sentence))

    # Sort by score, take top N, then re-order by position
    top = sorted(scored, key=lambda x: x[0], reverse=True)[:max_sentences]
    top_ordered = sorted(top, key=lambda x: x[1])

    return '. '.join(s[2] for s in top_ordered)


def extract_entities(text: str) -> dict:
    """
    Simple named entity extraction using pattern matching.

    Identifies dates, emails, URLs, and potential proper nouns.
    """
    entities = {
        "dates": [],
        "emails": [],
        "urls": [],
        "numbers": [],
    }

    # Dates (various formats)
    date_patterns = re.findall(
        r'\d{4}[-/]\d{1,2}[-/]\d{1,2}|\d{1,2}[-/]\d{1,2}[-/]\d{4}',
        text
    )
    entities["dates"] = list(set(date_patterns))

    # Emails
    entities["emails"] = list(set(re.findall(
        r'[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}',
        text
    )))

    # URLs
    entities["urls"] = list(set(re.findall(
        r'https?://[^\s<>"{}|\\^`\[\]]+',
        text
    )))

    # Numbers/percentages
    entities["numbers"] = list(set(re.findall(
        r'\d+\.?\d*%?',
        text
    )))[:20]  # Limit

    return entities


@router.post("/extract-metadata", response_model=MetadataResponse)
async def extract_metadata(request: ExtractMetadataRequest):
    """
    Extract comprehensive metadata from academic text.

    Returns:
    - Language detection
    - Auto-generated summary
    - Keywords with TF scores
    - Named entities (dates, emails, URLs)
    - Text statistics
    """
    if not request.text.strip():
        raise HTTPException(status_code=400, detail="Text cannot be empty")

    text = request.text
    language = detect_language(text)
    keywords = extract_keywords_from_text(text, max_keywords=15)
    summary = generate_summary(text)
    entities = extract_entities(text)

    return MetadataResponse(data={
        "language": language,
        "summary": summary,
        "keywords": keywords,
        "entities": entities,
        "statistics": {
            "word_count": len(text.split()),
            "char_count": len(text),
            "sentence_count": len(re.split(r'[.!?؟]', text)),
        },
    })


@router.post("/extract-keywords", response_model=MetadataResponse)
async def extract_keywords(request: ExtractKeywordsRequest):
    """
    Extract keywords from text with TF scoring.
    """
    if not request.text.strip():
        raise HTTPException(status_code=400, detail="Text cannot be empty")

    keywords = extract_keywords_from_text(request.text, request.max_keywords)

    return MetadataResponse(data={
        "keywords": keywords,
        "total_extracted": len(keywords),
    })
