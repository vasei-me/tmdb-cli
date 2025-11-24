<?php
namespace App\Output;

use App\Interfaces\OutputInterface;

class CliOutput implements OutputInterface
{
    public function displayMovies(array $movies, string $type): void
    {
        echo "Top {$type} movies:\n\n";

        if (empty($movies)) {
            echo "No movies found.\n";
            return;
        }

        foreach ($movies as $movie) {
            echo "- {$movie['title']} ({$movie['release_date']}) - Rating: {$movie['vote_average']}\n";
            echo "  Overview: {$movie['overview']}\n\n";
        }
    }
}