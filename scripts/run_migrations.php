<?php
/**
 * Safe Database Migration / Auto-Patcher Runner
 *
 * Runs only new SQL patches from database/patches/
 * Never deletes or wipes existing data.
 */

$root = dirname(dirname(__FILE__));
$dbConfigFile = $root . '/app/Config/database.php';

if (!file_exists($dbConfigFile)) {
    echo "[MIGRATION SKIP] app/Config/database.php not found.\n";
    exit(0);
}

require_once $dbConfigFile;
if (!class_exists('DATABASE_CONFIG')) {
    echo "[MIGRATION ERROR] DATABASE_CONFIG class not found.\n";
    exit(1);
}

$dbConfig = new DATABASE_CONFIG();
if (!isset($dbConfig->default)) {
    echo "[MIGRATION ERROR] default database config not found.\n";
    exit(1);
}

$conf = $dbConfig->default;

try {
    $dsn = "mysql:host={$conf['host']};dbname={$conf['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $conf['login'], $conf['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
    ]);
} catch (Exception $e) {
    echo "[MIGRATION ERROR] Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Ensure tracking table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS `schema_migrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `patch_file` VARCHAR(255) NOT NULL UNIQUE,
    `applied_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$patchesDir = $root . '/database/patches';
if (!is_dir($patchesDir)) {
    echo "[MIGRATION] No database/patches directory found.\n";
    exit(0);
}

$files = glob($patchesDir . '/*.sql');
if (empty($files)) {
    echo "[MIGRATION] No SQL patches pending.\n";
    exit(0);
}

sort($files);

$applied = $pdo->query("SELECT patch_file FROM `schema_migrations`")->fetchAll(PDO::FETCH_COLUMN);
$appliedSet = array_flip($applied);

$recordStmt = $pdo->prepare("INSERT INTO `schema_migrations` (`patch_file`, `applied_at`) VALUES (:file, NOW())");

$count = 0;
foreach ($files as $filePath) {
    $fileName = basename($filePath);
    if (isset($appliedSet[$fileName])) {
        continue;
    }

    echo "==> Applying patch: $fileName ...\n";
    $sql = file_get_contents($filePath);
    if (trim($sql) === '') {
        continue;
    }

    try {
        $pdo->exec($sql);
        $recordStmt->execute([':file' => $fileName]);
        echo "    [SUCCESS] $fileName applied.\n";
        $count++;
    } catch (Exception $ex) {
        echo "    [FAILED] Error in $fileName: " . $ex->getMessage() . "\n";
    }
}

if ($count === 0) {
    echo "[MIGRATION] All database patches are already up to date.\n";
} else {
    echo "[MIGRATION] Completed! $count new patch(es) applied safely.\n";
}
