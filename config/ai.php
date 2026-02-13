<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Summarization Provider
    |--------------------------------------------------------------------------
    |
    | Which AI backend to use for research-digest summaries.
    | Supported: "gemini", "openai", "ollama"
    |
    */

    'provider' => env('DIGEST_AI_PROVIDER', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | Gemini (default)
    |--------------------------------------------------------------------------
    */

    'gemini' => [
        'api_key' => env('GOOGLE_API_KEY'),
        'model'   => env('DIGEST_AI_MODEL', 'gemini-2.0-flash'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI (optional)
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model'   => env('DIGEST_AI_MODEL_OPENAI', 'gpt-4o-mini'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ollama (optional, local)
    |--------------------------------------------------------------------------
    */

    'ollama' => [
        'host'  => env('OLLAMA_HOST', 'http://127.0.0.1:11434'),
        'model' => env('DIGEST_AI_MODEL_OLLAMA', 'llama3.1'),
    ],

];
