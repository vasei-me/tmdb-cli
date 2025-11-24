<?php
namespace App\Mappers;

class MovieMapper
{
    public static function mapToArray(array $movies): array
    {
        return array_map(function ($movie) {
            return [
                'title' => $movie['title'] ?? 'N/A',
                'release_date' => $movie['release_date'] ?? 'N/A',
                'overview' => (function($o){
                    $o = $o ?? '';
                    if (mb_strlen($o) > 100) {
                        return mb_substr($o, 0, 100) . '...';
                    }
                    return $o;
                })($movie['overview'] ?? ''),
                'vote_average' => $movie['vote_average'] ?? 0,
            ];
        }, $movies);
    }
}