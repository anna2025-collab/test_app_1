<?php

namespace App\Services\Ai;

interface ContactAnalyzer
{
    public function analyze(array $contact): array;
}
