"""
EduTrack AI Microservice — Main Application

FastAPI-based microservice for NLP processing, metadata extraction,
and text similarity detection. Designed to work with the Laravel
Core Engine via REST API.
"""

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from contextlib import asynccontextmanager

from app.routers import nlp, metadata, similarity
from app.core.config import settings


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Application lifespan: startup and shutdown events."""
    # Startup: Initialize NLP resources
    print("🚀 EduTrack AI Service starting...")
    print(f"   Version: {settings.APP_VERSION}")
    print(f"   Redis: {settings.REDIS_HOST}:{settings.REDIS_PORT}")
    yield
    # Shutdown
    print("👋 EduTrack AI Service shutting down...")


app = FastAPI(
    title="EduTrack AI Microservice",
    description=(
        "AI-powered NLP microservice for the EduTrack & Archive system. "
        "Provides text processing, metadata extraction, keyword extraction, "
        "and text similarity detection."
    ),
    version=settings.APP_VERSION,
    docs_url="/docs",           # Swagger UI
    redoc_url="/redoc",         # ReDoc
    openapi_url="/openapi.json",
    lifespan=lifespan,
)

# ─── CORS Middleware ───
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.ALLOWED_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ─── Include Routers ───
app.include_router(nlp.router, prefix="/api/v1", tags=["NLP Processing"])
app.include_router(metadata.router, prefix="/api/v1", tags=["Metadata Extraction"])
app.include_router(similarity.router, prefix="/api/v1", tags=["Text Similarity"])


# ─── Health Check ───
@app.get("/health", tags=["Health"])
async def health_check():
    """Health check endpoint for monitoring and Laravel integration."""
    return {
        "status": "healthy",
        "service": "edutrack-ai",
        "version": settings.APP_VERSION,
    }


@app.get("/", tags=["Root"])
async def root():
    """Root endpoint with service information."""
    return {
        "service": "EduTrack AI Microservice",
        "version": settings.APP_VERSION,
        "docs": "/docs",
        "health": "/health",
    }
