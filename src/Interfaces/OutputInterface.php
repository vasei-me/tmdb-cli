<?php
namespace App\Interfaces;

interface OutputInterface
{
    public function displayMovies(array $movies, string $type): void;
}