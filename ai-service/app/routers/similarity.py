"""
Text Similarity Router

Calculates similarity between two texts using TF-IDF and cosine similarity.
"""

import re
import math
from collections import Counter
from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field

from app.core.config import settings

router = APIRouter()


class SimilarityRequest(BaseModel):
    """Request model for text similarity."""
    text1: str = Field(..., max_length=settings.MAX_TEXT_LENGTH, description="First text")
    text2: str = Field(..., max_length=settings.MAX_TEXT_LENGTH, description="Second text")


class SimilarityResponse(BaseModel):
    """Response model for similarity result."""
    data: dict


# Stop words for filtering
STOP_WORDS = {
    'في', 'من', 'على', 'إلى', 'عن', 'مع', 'هذا', 'هذه', 'ذلك', 'تلك',
    'التي', 'الذي', 'هو', 'هي', 'أن', 'إن', 'كان', 'لا', 'لم', 'لن',
    'قد', 'ما', 'و', 'أو', 'ثم', 'بل', 'لكن', 'حتى', 'كل', 'بعض',
    'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
    'of', 'with', 'by', 'from', 'is', 'was', 'are', 'were', 'be', 'been',
    'it', 'this', 'that', 'not', 'no',
}


def tokenize(text: str) -> list:
    """Tokenize text and remove stop words."""
    words = re.findall(r'[\u0600-\u06FFa-zA-Z]{2,}', text.lower())
    return [w for w in words if w not in STOP_WORDS]


def cosine_similarity(vec1: dict, vec2: dict) -> float:
    """
    Calculate cosine similarity between two term-frequency vectors.

    cos(θ) = (A · B) / (||A|| × ||B||)
    """
    # Get all terms
    all_terms = set(vec1.keys()) | set(vec2.keys())

    # Dot product
    dot_product = sum(vec1.get(t, 0) * vec2.get(t, 0) for t in all_terms)

    # Magnitudes
    mag1 = math.sqrt(sum(v ** 2 for v in vec1.values()))
    mag2 = math.sqrt(sum(v ** 2 for v in vec2.values()))

    if mag1 == 0 or mag2 == 0:
        return 0.0

    return dot_product / (mag1 * mag2)


def jaccard_similarity(set1: set, set2: set) -> float:
    """
    Calculate Jaccard similarity coefficient.

    J(A,B) = |A ∩ B| / |A ∪ B|
    """
    intersection = len(set1 & set2)
    union = len(set1 | set2)

    if union == 0:
        return 0.0

    return intersection / union


@router.post("/similarity", response_model=SimilarityResponse)
async def calculate_similarity(request: SimilarityRequest):
    """
    Calculate similarity between two texts.

    Uses two methods:
    1. **Cosine Similarity** (TF-based): Measures angular distance between
       term frequency vectors. Best for longer texts.
    2. **Jaccard Similarity**: Measures overlap of unique terms.
       Best for short texts.

    Returns a combined weighted score (70% cosine + 30% Jaccard).
    """
    if not request.text1.strip() or not request.text2.strip():
        raise HTTPException(status_code=400, detail="Both texts must be non-empty")

    # Tokenize
    tokens1 = tokenize(request.text1)
    tokens2 = tokenize(request.text2)

    if not tokens1 or not tokens2:
        return SimilarityResponse(data={
            "similarity": 0.0,
            "cosine_similarity": 0.0,
            "jaccard_similarity": 0.0,
            "common_terms": [],
            "is_similar": False,
        })

    # Term frequency vectors
    tf1 = dict(Counter(tokens1))
    tf2 = dict(Counter(tokens2))

    # Calculate similarities
    cosine_sim = cosine_similarity(tf1, tf2)
    jaccard_sim = jaccard_similarity(set(tokens1), set(tokens2))

    # Weighted combined score
    combined = (0.7 * cosine_sim) + (0.3 * jaccard_sim)
    combined = round(combined, 4)

    # Common terms
    common = set(tokens1) & set(tokens2)

    return SimilarityResponse(data={
        "similarity": combined,
        "cosine_similarity": round(cosine_sim, 4),
        "jaccard_similarity": round(jaccard_sim, 4),
        "common_terms": sorted(list(common))[:30],
        "common_term_count": len(common),
        "text1_unique_terms": len(set(tokens1)),
        "text2_unique_terms": len(set(tokens2)),
        "is_similar": combined > 0.7,  # Threshold for "similar"
        "similarity_level": (
            "identical" if combined > 0.95 else
            "very_similar" if combined > 0.8 else
            "similar" if combined > 0.6 else
            "somewhat_similar" if combined > 0.3 else
            "different"
        ),
    })
