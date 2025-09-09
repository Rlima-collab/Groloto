<?php
declare(strict_types=1);

namespace Classes\Provider;

final class DataLoaderJson extends DataLoader {
    
    public function __construct(string $path) {
        $this->content = json_decode(file_get_contents($path), true);
    }
}