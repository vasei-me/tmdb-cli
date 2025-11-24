<?php
namespace App\Output;

use App\Mappers\MovieMapper;
use App\Interfaces\MovieFetcherInterface;
use App\Interfaces\OutputInterface;

class CliHandler
{
    private MovieFetcherInterface $fetcher;
    private OutputInterface $output;

    public function __construct(MovieFetcherInterface $fetcher, OutputInterface $output)
    {
        $this->fetcher = $fetcher;
        $this->output = $output;
    }

    public function handle(array $args): void
    {
        $options = getopt('', ['type:', 'page::', 'verbose']);

        if (!isset($options['type'])) {
            echo "Usage: php tmdb-app --type [playing|popular|top|upcoming] [--page N]\n";
            return;
        }

        $type = $options['type'];
        $page = isset($options['page']) ? (int)$options['page'] : 1;

        $verbose = isset($options['verbose']);

        try {
            $rawMovies = $this->fetcher->fetchMovies($type, $page);
            $mappedMovies = MovieMapper::mapToArray($rawMovies);
            $this->output->displayMovies($mappedMovies, $type);
        } catch (\Exception $e) {
            // If the real service failed, try falling back to a mock service if available
            // Log the detailed error but present a user-friendly message to the CLI
            if (class_exists('\App\\Services\\Logger')) {
                try {
                    $logger = new \App\Services\Logger();
                    $logger->log('error', 'API error in CliHandler: ' . $e->getMessage());
                } catch (\Throwable $t) {
                    // ignore logger failures
                }
            }
            if ($verbose) {
                echo "Warning: " . $e->getMessage() . "\n";
                echo $e->__toString() . "\n";
            } else {
                echo "Unable to fetch live data; falling back to local test data.\n";
            }
            if (class_exists('\App\\Services\\MockTmdbService')) {
                try {
                    $mock = new \App\Services\MockTmdbService();
                    $mappedMovies = MovieMapper::mapToArray($mock->fetchMovies($type, $page));
                    echo "Falling back to mock data:\n\n";
                    $this->output->displayMovies($mappedMovies, $type);
                    return;
                } catch (\Exception $ex) {
                    // continue to show the original error
                }
            }
            echo "Error: could not retrieve movies.\n";
        }
    }
}