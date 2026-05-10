<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

/**
 * File Hashing Service
 *
 * Responsible for generating SHA-256 fingerprints for files.
 * Used for duplicate detection and file integrity verification.
 */
class FileHashingService
{
    /**
     * The hashing algorithm used for file fingerprinting.
     */
    protected string $algorithm = 'sha256';

    /**
     * Generate a SHA-256 hash for a file at the given path.
     *
     * @param  string  $filePath  Absolute path to the file.
     * @return string  The hex-encoded SHA-256 hash.
     *
     * @throws \RuntimeException  If the file does not exist or is unreadable.
     */
    public function hashFile(string $filePath): string
    {
        if (! file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        if (! is_readable($filePath)) {
            throw new \RuntimeException("File is not readable: {$filePath}");
        }

        $hash = hash_file($this->algorithm, $filePath);

        if ($hash === false) {
            throw new \RuntimeException("Failed to generate hash for: {$filePath}");
        }

        return $hash;
    }

    /**
     * Generate a SHA-256 hash from raw content string.
     *
     * @param  string  $content  The content to hash.
     * @return string  The hex-encoded SHA-256 hash.
     */
    public function hashContent(string $content): string
    {
        return hash($this->algorithm, $content);
    }

    /**
     * Verify a file against a known hash.
     *
     * @param  string  $filePath  Absolute path to the file.
     * @param  string  $expectedHash  The expected SHA-256 hash.
     * @return bool  True if the file matches the expected hash.
     */
    public function verifyFile(string $filePath, string $expectedHash): bool
    {
        return hash_equals($expectedHash, $this->hashFile($filePath));
    }

    /**
     * Get the algorithm name.
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }
}
