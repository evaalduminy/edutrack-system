"""
Application Configuration

Centralized settings using Pydantic BaseSettings for
environment variable management.
"""

from pydantic_settings import BaseSettings
from typing import List


class Settings(BaseSettings):
    """Application settings loaded from environment variables."""

    APP_VERSION: str = "1.0.0"
    DEBUG: bool = True

    # Redis
    REDIS_HOST: str = "127.0.0.1"
    REDIS_PORT: int = 6379
    REDIS_PASSWORD: str = ""
    REDIS_DB: int = 0

    # CORS
    ALLOWED_ORIGINS: List[str] = [
        "http://localhost:8000",
        "http://127.0.0.1:8000",
        "http://localhost:3000",
    ]

    # NLP Settings
    MAX_TEXT_LENGTH: int = 50000
    DEFAULT_LANGUAGE: str = "ar"
    MAX_KEYWORDS: int = 20

    class Config:
        env_file = ".env"
        env_file_encoding = "utf-8"


settings = Settings()
