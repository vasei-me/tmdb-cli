<?php
namespace App\Services;

use App\Interfaces\MovieFetcherInterface;

class MockTmdbService implements MovieFetcherInterface
{
    public function fetchMovies(string $type, int $page = 1): array
    {
        // Return a small set of fake movie data matching TMDB structure
        return [
            [
                'title' => 'Mock Movie One',
                'release_date' => '2024-01-15',
                'overview' => 'Overview for mock movie one. This is a sample overview used for testing output.',
                'vote_average' => 7.5,
            ],
            [
                'title' => 'Mock Movie Two',
                'release_date' => '2023-11-01',
                'overview' => 'Overview for mock movie two. Another sample to demonstrate mapping and display.',
                'vote_average' => 8.2,
            ],
            [
                'title' => 'Mock Movie Three',
                'release_date' => '2022-06-20',
                'overview' => 'Overview for mock movie three. Contains additional text to show truncation behavior.',
                'vote_average' => 6.9,
            ],
        ];
    }
}
