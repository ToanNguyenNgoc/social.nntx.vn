<?php

return [

  'paths' => [
    'api/*',
    'broadcasting/auth',
    'sanctum/csrf-cookie', // nếu bạn dùng Sanctum
  ],

  'allowed_methods' => ['*'],

  'allowed_origins' => [
    'http://localhost:5173', // React app
  ],

  'allowed_origins_patterns' => [],

  'allowed_headers' => ['*'],

  'exposed_headers' => [],

  'max_age' => 0,

  'supports_credentials' => true, // quan trọng nếu bạn dùng cookie / auth

];
