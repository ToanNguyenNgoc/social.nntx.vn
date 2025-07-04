<?php

return [

  'paths' => [
    'api/*',
    'broadcasting/auth',
    'sanctum/csrf-cookie',
  ],

  'allowed_methods' => ['*'],

  'allowed_origins' => array_map('trim', explode(',', env('ALLOWED_ORIGINS', '*'))),

  'allowed_origins_patterns' => [],

  'allowed_headers' => ['*'],

  'exposed_headers' => [],

  'max_age' => 0,

  'supports_credentials' => true,

];
