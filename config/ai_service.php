<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Microservice Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Python FastAPI AI microservice that handles
    | NLP processing, metadata extraction, and text similarity detection.
    |
    */

    'base_url' => env('AI_SERVICE_URL', 'http://127.0.0.1:8001'),
    'timeout'  => env('AI_SERVICE_TIMEOUT', 30),

    'retry' => [
        'times' => env('AI_SERVICE_RETRY_TIMES', 3),
        'sleep' => env('AI_SERVICE_RETRY_SLEEP', 1000), // milliseconds
    ],

    'endpoints' => [
        'health'     => '/health',
        'extract'    => '/api/v1/extract-metadata',
        'similarity' => '/api/v1/similarity',
        'nlp'        => '/api/v1/process-text',
        'keywords'   => '/api/v1/extract-keywords',
    ],
];
