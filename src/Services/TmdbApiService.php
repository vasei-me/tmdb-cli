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
        $this->config = \config()['tmdb'];
        $this->client = new Client([
            'base_uri' => rtrim($this->config['base_url'], '/') . '/',
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

        // Build a cache key from endpoint+query
        $cacheKey = md5($endpoints[$type] . '|' . $page);
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
                // تشخیص نوع احراز هویت بر اساس نوع کلید
                $apiKey = $this->config['api_key'];
                $requestOptions = [
                    'headers' => ['Accept' => 'application/json'],
                    'http_errors' => false,
                ];

                if (str_starts_with($apiKey, 'eyJ')) {
                    // استفاده از API Read Access Token (Bearer Token)
                    $requestOptions['headers']['Authorization'] = 'Bearer ' . $apiKey;
                    $requestOptions['query'] = [
                        'language' => $this->config['language'] ?? 'en-US',
                        'page' => $page,
                    ];
                } else {
                    // استفاده از API Key معمولی
                    $requestOptions['query'] = [
                        'api_key' => $apiKey,
                        'language' => $this->config['language'] ?? 'en-US',
                        'page' => $page,
                    ];
                }

                $response = $this->client->get($endpoints[$type], $requestOptions);

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