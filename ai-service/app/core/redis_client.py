"""
Redis Client

Provides Redis connection for caching and communication
between Laravel and the AI microservice.
"""

import redis
from app.core.config import settings


def get_redis_client() -> redis.Redis:
    """
    Create and return a Redis client instance.

    Used for:
    - Caching NLP results
    - Communication with Laravel queue system
    - Storing temporary processing data
    """
    return redis.Redis(
        host=settings.REDIS_HOST,
        port=settings.REDIS_PORT,
        password=settings.REDIS_PASSWORD or None,
        db=settings.REDIS_DB,
        decode_responses=True,
        socket_connect_timeout=5,
        retry_on_timeout=True,
    )


class RedisCache:
    """Simple caching wrapper around Redis."""

    def __init__(self):
        self._client = None

    @property
    def client(self) -> redis.Redis:
        if self._client is None:
            try:
                self._client = get_redis_client()
                self._client.ping()
            except redis.ConnectionError:
                self._client = None
        return self._client

    def get(self, key: str) -> str | None:
        """Get a cached value."""
        if self.client:
            try:
                return self.client.get(key)
            except redis.ConnectionError:
                return None
        return None

    def set(self, key: str, value: str, ttl: int = 3600) -> bool:
        """Set a cached value with TTL (default: 1 hour)."""
        if self.client:
            try:
                return self.client.setex(key, ttl, value)
            except redis.ConnectionError:
                return False
        return False

    def delete(self, key: str) -> bool:
        """Delete a cached value."""
        if self.client:
            try:
                return self.client.delete(key) > 0
            except redis.ConnectionError:
                return False
        return False


# Singleton instance
cache = RedisCache()
