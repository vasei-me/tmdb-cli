<?php
return [
    'tmdb' => [
        'base_url' => 'https://api.themoviedb.org/3',
        'api_key' => $_ENV['TMDB_API_KEY'] ?? getenv('TMDB_API_KEY'),
        'language' => 'en-US',
    ],
];