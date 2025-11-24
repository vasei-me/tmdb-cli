<?php
// debug_test.php
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== ENVIRONMENT DEBUG ===\n";
echo "TMDB_API_KEY from \$_ENV: " . ($_ENV['TMDB_API_KEY'] ?? 'NOT FOUND') . "\n";
echo "TMDB_API_KEY from getenv: " . (getenv('TMDB_API_KEY') ?: 'NOT FOUND') . "\n";

$config = require __DIR__ . '/config/config.php';
echo "API Key from config: " . ($config['tmdb']['api_key'] ?: 'EMPTY') . "\n";

// تست اتصال مستقیم به API
if (!empty($config['tmdb']['api_key'])) {
    $url = "https://api.themoviedb.org/3/movie/popular?api_key={$config['tmdb']['api_key']}";
    echo "\n=== API CONNECTION TEST ===\n";
    echo "Testing URL: " . $url . "\n";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        $error = error_get_last();
        echo "ERROR: " . ($error['message'] ?? 'Unknown error') . "\n";
        
        // تست با curl اگر موجود است
        if (function_exists('curl_init')) {
            echo "\n=== TRYING CURL ===\n";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $curl_response = curl_exec($ch);
            if (curl_error($ch)) {
                echo "CURL Error: " . curl_error($ch) . "\n";
            } else {
                echo "CURL Success! Response length: " . strlen($curl_response) . "\n";
                $data = json_decode($curl_response, true);
                if (isset($data['results'])) {
                    echo "Found " . count($data['results']) . " movies\n";
                } else {
                    echo "Response: " . substr($curl_response, 0, 200) . "...\n";
                }
            }
            curl_close($ch);
        }
    } else {
        $data = json_decode($response, true);
        if (isset($data['results'])) {
            echo "SUCCESS! Found " . count($data['results']) . " movies\n";
            echo "First movie: " . $data['results'][0]['title'] . "\n";
        } else {
            echo "API Response error: " . substr($response, 0, 200) . "...\n";
        }
    }
} else {
    echo "ERROR: No API key found in config!\n";
}