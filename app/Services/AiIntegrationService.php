<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

/**
 * AI Integration Service
 *
 * Client for communicating with the Python FastAPI AI microservice.
 * Implements retry mechanism and logging for failed connections.
 * Uses Laravel's HTTP Client (Guzzle wrapper) with configurable timeouts.
 */
class AiIntegrationService
{
    protected string $baseUrl;
    protected int $timeout;
    protected int $retryTimes;
    protected int $retrySleep;

    public function __construct()
    {
        $this->baseUrl    = config('ai_service.base_url');
        $this->timeout    = config('ai_service.timeout');
        $this->retryTimes = config('ai_service.retry.times');
        $this->retrySleep = config('ai_service.retry.sleep');
    }

    /**
     * Check if the AI service is healthy and reachable.
     */
    public function healthCheck(): bool
    {
        try {
            $response = $this->makeRequest('GET', config('ai_service.endpoints.health'));

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('AI Service health check failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Extract metadata from text content using NLP.
     *
     * @param  string  $text  The text to process.
     * @return array  Extracted metadata (keywords, entities, summary, etc.)
     */
    public function extractMetadata(string $text): array
    {
        try {
            $response = $this->makeRequest('POST', config('ai_service.endpoints.extract'), [
                'text' => $text,
            ]);

            return $response->json('data', []);
        } catch (\Exception $e) {
            Log::error('AI metadata extraction failed', [
                'error'      => $e->getMessage(),
                'text_length' => strlen($text),
            ]);

            return [];
        }
    }

    /**
     * Calculate text similarity between two texts.
     *
     * @param  string  $text1  First text.
     * @param  string  $text2  Second text.
     * @return float  Similarity score between 0.0 and 1.0.
     */
    public function calculateSimilarity(string $text1, string $text2): float
    {
        try {
            $response = $this->makeRequest('POST', config('ai_service.endpoints.similarity'), [
                'text1' => $text1,
                'text2' => $text2,
            ]);

            return (float) $response->json('data.similarity', 0.0);
        } catch (\Exception $e) {
            Log::error('AI similarity calculation failed', [
                'error' => $e->getMessage(),
            ]);

            return 0.0;
        }
    }

    /**
     * Process text through NLP pipeline (tokenization, stemming, etc.)
     *
     * @param  string  $text  The text to process.
     * @param  string  $language  Language code (default: 'ar' for Arabic).
     * @return array  Processed text data.
     */
    public function processText(string $text, string $language = 'ar'): array
    {
        try {
            $response = $this->makeRequest('POST', config('ai_service.endpoints.nlp'), [
                'text'     => $text,
                'language' => $language,
            ]);

            return $response->json('data', []);
        } catch (\Exception $e) {
            Log::error('AI text processing failed', [
                'error'    => $e->getMessage(),
                'language' => $language,
            ]);

            return [];
        }
    }

    /**
     * Extract keywords from text.
     *
     * @param  string  $text  The text to extract keywords from.
     * @param  int  $maxKeywords  Maximum number of keywords to return.
     * @return array  List of extracted keywords with scores.
     */
    public function extractKeywords(string $text, int $maxKeywords = 10): array
    {
        try {
            $response = $this->makeRequest('POST', config('ai_service.endpoints.keywords'), [
                'text'         => $text,
                'max_keywords' => $maxKeywords,
            ]);

            return $response->json('data.keywords', []);
        } catch (\Exception $e) {
            Log::error('AI keyword extraction failed', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Make an HTTP request to the AI service with retry and logging.
     *
     * @param  string  $method  HTTP method (GET, POST, etc.)
     * @param  string  $endpoint  The API endpoint path.
     * @param  array  $data  Request body data.
     * @return \Illuminate\Http\Client\Response
     *
     * @throws RequestException
     */
    protected function makeRequest(string $method, string $endpoint, array $data = [])
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        Log::debug('AI Service request', [
            'method'   => $method,
            'url'      => $url,
            'data_keys' => array_keys($data),
        ]);

        $response = Http::timeout($this->timeout)
            ->retry($this->retryTimes, $this->retrySleep, function ($exception, $request) {
                // Only retry on connection errors or 5xx server errors
                Log::warning('AI Service retry attempt', [
                    'error' => $exception->getMessage(),
                ]);

                return $exception instanceof \Illuminate\Http\Client\ConnectionException
                    || ($exception instanceof RequestException && $exception->response->serverError());
            })
            ->acceptJson()
            ->{strtolower($method)}($url, $data);

        $response->throw();

        return $response;
    }
}
