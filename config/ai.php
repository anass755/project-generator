<?php
return [
    'driver' => env('AI_DRIVER', 'ollama'),   // ollama | openai | openrouter
    'model'  => env('AI_MODEL', 'llama3.2'),
];
