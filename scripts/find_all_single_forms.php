<?php
function searchDir($dir, $pattern) {
    $results = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if ($file->isDir()) continue;
        $path = $file->getPathname();
        if (strpos($path, 'tmp') !== false) continue;
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (!in_array($ext, ['php', 'ctp', 'js', 'html'])) continue;
        $content = file_get_contents($path);
        if (stripos($content, $pattern) !== false) {
            $results[] = $path;
        }
    }
    return $results;
}

echo "=== Files containing 'generate_single' ===\n";
print_r(searchDir('app', 'generate_single'));

echo "=== Files containing 'modalSingleBill' ===\n";
print_r(searchDir('app', 'modalSingleBill'));

echo "=== Files containing 'single bill' ===\n";
print_r(searchDir('app', 'single bill'));
