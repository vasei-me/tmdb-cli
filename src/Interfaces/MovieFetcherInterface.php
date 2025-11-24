<?php
namespace App\Interfaces;

interface MovieFetcherInterface
{
    public function fetchMovies(string $type, int $page = 1): array;
}