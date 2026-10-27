<?php

declare(strict_types=1);

$root = $argv[1] ?? 'public/build';
$manifest = json_decode((string) file_get_contents($root.'/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
foreach (['resources/css/app.css', 'resources/js/app.js'] as $entry) {
    if (empty($manifest[$entry]['isEntry']) || empty($manifest[$entry]['file'])) {
        throw new RuntimeException('Missing Vite entry: '.$entry);
    }
}
foreach ($manifest as $entry) {
    foreach (array_merge([$entry['file'] ?? ''], $entry['css'] ?? [], $entry['assets'] ?? []) as $file) {
        if ($file === '' || str_contains($file, '..') || str_contains($file, '\\') || str_starts_with($file, '/')
            || ! is_file($root.'/'.$file) || filesize($root.'/'.$file) === 0) {
            throw new RuntimeException('Missing or invalid built asset: '.$file);
        }
    }
}
echo "Production web manifest and assets are ready.\n";
