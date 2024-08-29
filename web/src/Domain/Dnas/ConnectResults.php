<?php

namespace App\Domain\Dnas;

final readonly class ConnectResults
{
    public function __construct(
        public string $contentType,
        public int $contentLength,
        public mixed $content
    ) {
        
    }
}
