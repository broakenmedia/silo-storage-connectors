<?php

// config for Silo/StorageConnectors
return [
    'google_drive' => [
        'service_account' => env('GOOGLE_APPLICATION_CREDENTIALS'),
    ],
    'confluence' => [
        'username' => env('CONFLUENCE_USERNAME'),
        'api_token' => env('CONFLUENCE_API_TOKEN'),
        'domain' => env('CONFLUENCE_DOMAIN'),
    ],
];
