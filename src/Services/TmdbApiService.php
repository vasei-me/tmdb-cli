<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Interfaces\MovieFetcherInterface;
use App\Services\CacheService;
use App\Services\Logger;

class TmdbApiService implements MovieFetcherInterface
{
    private Client $client;
    private array $config;
    private CacheService $cache;
    private Logger $logger;

    public function __construct()
    {
        // Load configuration directly - removed config() function dependency
        $cfg = [];
        $cfgFile = __DIR__ . '/../../config/config.php';
        if (file_exists($cfgFile)) {
            $fileCfg = require $cfgFile;
            if (is_array($fileCfg) && isset($fileCfg['tmdb'])) {
                $cfg = $fileCfg['tmdb'];
            }
        }

        // Allow environment variables to override or provide missing values
        $envApiKey = $_ENV['TMDB_API_KEY'] ?? getenv('TMDB_API_KEY') ?: null;
        if ($envApiKey) {
            $cfg['api_key'] = $envApiKey;
        }

        // sensible defaults
        $defaults = [
            'base_url' => 'https://api.themoviedb.org/3',
            'language' => 'en-US',
            'api_key' => $cfg['api_key'] ?? null,
            'cache_ttl' => $cfg['cache_ttl'] ?? 600,
        ];

        $this->config = array_merge($defaults, $cfg);

        $this->client = new Client([
            'base_uri' => rtrim($this->config['base_url'] ?? $defaults['base_url'], '/') . '/',
            'timeout' => 5.0,
            'connect_timeout' => 3.0,
        ]);
        $this->cache = new CacheService();
        $this->logger = new Logger();
    }

    public function fetchMovies(string $type, int $page = 1): array
    {
        $endpoints = [
            'playing' => 'movie/now_playing',
            'popular' => 'movie/popular',
            'top' => 'movie/top_rated',
            'upcoming' => 'movie/upcoming',
        ];

        if (!array_key_exists($type, $endpoints)) {
            throw new \InvalidArgumentException("Invalid movie type: {$type}");
        }

        $query = [
            'api_key' => $this->config['api_key'],
            'language' => $this->config['language'] ?? 'en-US',
            'page' => $page,
        ];

        if (empty($query['api_key'])) {
            throw new \RuntimeException('TMDB API key is not set. Set TMDB_API_KEY in your environment or use --mock.');
        }

        // Build a cache key from endpoint+query
        $cacheKey = md5($endpoints[$type] . '|' . json_encode($query));
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            $this->logger->log('debug', "Cache hit for {$endpoints[$type]} page={$page}");
            return $cached;
        }

        $maxRetries = 3;
        $attempt = 0;
        $lastEx = null;

        while ($attempt <= $maxRetries) {
            try {
                $response = $this->client->get($endpoints[$type], [
                    'query' => $query,
                    'headers' => ['Accept' => 'application/json'],
                    'http_errors' => false,
                ]);

                $status = $response->getStatusCode();
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                if ($status >= 500) {
                    // Server error: retry
                    $this->logger->log('warning', "TMDB server error {$status}, attempt {$attempt}");
                    throw new \RuntimeException('TMDB server error: ' . ($data['status_message'] ?? $status));
                }

                if ($status >= 400) {
                    $msg = $data['status_message'] ?? 'Unknown API error';
                    throw new \RuntimeException("TMDB API error ({$status}): {$msg}");
                }

                if (isset($data['success']) && $data['success'] === false) {
                    $msg = $data['status_message'] ?? 'Unknown';
                    throw new \RuntimeException('TMDB API error: ' . $msg);
                }

                $results = $data['results'] ?? [];
                // store cache (short TTL)
                $this->cache->set($cacheKey, $results, $this->config['cache_ttl'] ?? 600);
                return $results;
            } catch (RequestException $e) {
                $lastEx = $e;
                $this->logger->log('error', 'Network error: ' . $e->getMessage());
            } catch (\RuntimeException $e) {
                $lastEx = $e;
                // If it's a 401 or 4xx, don't retry; break and rethrow later
                if (strpos($e->getMessage(), 'TMDB API error (401)') !== false || strpos($e->getMessage(), 'TMDB API error (4') !== false) {
                    $this->logger->log('error', 'Non-retriable API error: ' . $e->getMessage());
                    throw $e;
                }
                $this->logger->log('warning', 'API error: ' . $e->getMessage());
            }

            $attempt++;
            if ($attempt > $maxRetries) {
                break;
            }

            // exponential backoff
            $sleep = (int) pow(2, $attempt);
            $this->logger->log('debug', "Retrying in {$sleep}s (attempt {$attempt})");
            sleep($sleep);
        }

        if ($lastEx) {
            throw new \RuntimeException('API/Network error: ' . $lastEx->getMessage());
        }

        return [];
    }
}