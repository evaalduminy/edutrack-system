"""
Tests for the AI Microservice endpoints.

Run with: python -m pytest ai-service/tests/ -v
"""

import pytest
from fastapi.testclient import TestClient
from app.main import app

client = TestClient(app)


# ─── Health Check Tests ───

def test_health_check():
    """Test the health check endpoint."""
    response = client.get("/health")
    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "healthy"
    assert data["service"] == "edutrack-ai"


def test_root_endpoint():
    """Test the root endpoint."""
    response = client.get("/")
    assert response.status_code == 200
    assert "service" in response.json()


# ─── NLP Processing Tests ───

def test_process_arabic_text():
    """Test NLP processing of Arabic text."""
    response = client.post("/api/v1/process-text", json={
        "text": "تطبيق الذكاء الاصطناعي في التشخيص الطبي. هذه الدراسة تهدف إلى استكشاف التطبيقات المختلفة.",
        "language": "ar"
    })
    assert response.status_code == 200
    data = response.json()["data"]
    assert data["language"] == "ar"
    assert data["token_count"] > 0
    assert data["word_count"] > 0


def test_process_english_text():
    """Test NLP processing of English text."""
    response = client.post("/api/v1/process-text", json={
        "text": "Artificial Intelligence in medical diagnosis. This study explores various applications.",
        "language": "en"
    })
    assert response.status_code == 200
    data = response.json()["data"]
    assert data["language"] == "en"


def test_process_empty_text():
    """Test that empty text returns 400."""
    response = client.post("/api/v1/process-text", json={
        "text": "   ",
    })
    assert response.status_code == 400


# ─── Metadata Extraction Tests ───

def test_extract_metadata():
    """Test metadata extraction from Arabic academic text."""
    response = client.post("/api/v1/extract-metadata", json={
        "text": (
            "تطبيق الذكاء الاصطناعي في التشخيص الطبي. "
            "تهدف هذه الدراسة إلى استكشاف تطبيقات الذكاء الاصطناعي "
            "في مجال التشخيص الطبي. نستخدم خوارزميات التعلم العميق "
            "لتحليل الصور الطبية والكشف المبكر عن الأمراض."
        )
    })
    assert response.status_code == 200
    data = response.json()["data"]
    assert "keywords" in data
    assert "summary" in data
    assert "statistics" in data
    assert len(data["keywords"]) > 0


def test_extract_keywords():
    """Test keyword extraction."""
    response = client.post("/api/v1/extract-keywords", json={
        "text": "الذكاء الاصطناعي والتعلم الآلي والتعلم العميق في التشخيص الطبي",
        "max_keywords": 5
    })
    assert response.status_code == 200
    data = response.json()["data"]
    assert "keywords" in data
    assert len(data["keywords"]) <= 5


# ─── Similarity Tests ───

def test_identical_texts_similarity():
    """Test that identical texts have high similarity."""
    text = "تطبيق الذكاء الاصطناعي في التشخيص الطبي"
    response = client.post("/api/v1/similarity", json={
        "text1": text,
        "text2": text,
    })
    assert response.status_code == 200
    data = response.json()["data"]
    assert data["similarity"] > 0.9
    assert data["is_similar"] is True


def test_different_texts_similarity():
    """Test that different texts have low similarity."""
    response = client.post("/api/v1/similarity", json={
        "text1": "الذكاء الاصطناعي في الطب",
        "text2": "تاريخ الحضارة الإسلامية في الأندلس",
    })
    assert response.status_code == 200
    data = response.json()["data"]
    assert data["similarity"] < 0.5


def test_similarity_with_empty_text():
    """Test that empty texts return 400."""
    response = client.post("/api/v1/similarity", json={
        "text1": "",
        "text2": "some text",
    })
    assert response.status_code == 400


def test_similarity_response_structure():
    """Test the full response structure of similarity endpoint."""
    response = client.post("/api/v1/similarity", json={
        "text1": "التعلم الآلي",
        "text2": "التعلم العميق",
    })
    assert response.status_code == 200
    data = response.json()["data"]

    assert "similarity" in data
    assert "cosine_similarity" in data
    assert "jaccard_similarity" in data
    assert "common_terms" in data
    assert "similarity_level" in data
    assert isinstance(data["similarity"], float)
