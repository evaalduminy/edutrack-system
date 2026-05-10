<?php

namespace Tests\Unit;

use App\Services\FileHashingService;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests: FileHashingService
 *
 * Tests the SHA-256 hashing functionality independently
 * without relying on the database or other services.
 */
class FileHashingServiceTest extends TestCase
{
    protected FileHashingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FileHashingService();
    }

    /** @test */
    public function it_returns_sha256_algorithm_name(): void
    {
        $this->assertEquals('sha256', $this->service->getAlgorithm());
    }

    /** @test */
    public function it_hashes_content_with_sha256(): void
    {
        $content = 'Hello, EduTrack!';
        $hash = $this->service->hashContent($content);

        // SHA-256 produces 64 hex characters
        $this->assertEquals(64, strlen($hash));

        // Verify it matches PHP's built-in hash
        $this->assertEquals(hash('sha256', $content), $hash);
    }

    /** @test */
    public function it_produces_consistent_hashes_for_same_content(): void
    {
        $content = 'تطبيق الذكاء الاصطناعي في التشخيص الطبي';

        $hash1 = $this->service->hashContent($content);
        $hash2 = $this->service->hashContent($content);

        $this->assertEquals($hash1, $hash2);
    }

    /** @test */
    public function it_produces_different_hashes_for_different_content(): void
    {
        $hash1 = $this->service->hashContent('Content A');
        $hash2 = $this->service->hashContent('Content B');

        $this->assertNotEquals($hash1, $hash2);
    }

    /** @test */
    public function it_hashes_a_file(): void
    {
        // Create a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'edutrack_test_');
        file_put_contents($tempFile, 'Test file content for hashing');

        $hash = $this->service->hashFile($tempFile);

        $this->assertEquals(64, strlen($hash));
        $this->assertEquals(hash_file('sha256', $tempFile), $hash);

        unlink($tempFile);
    }

    /** @test */
    public function it_verifies_file_against_known_hash(): void
    {
        $content = 'Verify this content';
        $tempFile = tempnam(sys_get_temp_dir(), 'edutrack_test_');
        file_put_contents($tempFile, $content);

        $expectedHash = hash('sha256', $content);

        $this->assertTrue($this->service->verifyFile($tempFile, $expectedHash));
        $this->assertFalse($this->service->verifyFile($tempFile, 'invalid_hash'));

        unlink($tempFile);
    }

    /** @test */
    public function it_throws_exception_for_nonexistent_file(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File not found');

        $this->service->hashFile('/nonexistent/path/file.pdf');
    }

    /** @test */
    public function it_handles_empty_content(): void
    {
        $hash = $this->service->hashContent('');

        // SHA-256 of empty string is a known constant
        $this->assertEquals(
            'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
            $hash
        );
    }

    /** @test */
    public function it_handles_arabic_content(): void
    {
        $arabicContent = 'بسم الله الرحمن الرحيم';
        $hash = $this->service->hashContent($arabicContent);

        $this->assertEquals(64, strlen($hash));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);
    }
}
