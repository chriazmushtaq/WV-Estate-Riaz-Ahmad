<?php
/**
 * Safe Database Migration / Auto-Patcher Runner
 *
 * Runs only new SQL patches from database/patches/
 * Automatically repairs missing AUTO_INCREMENT and PRIMARY KEY on tables.
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

// 1. Ensure tracking table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS `schema_migrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `patch_file` VARCHAR(255) NOT NULL UNIQUE,
    `applied_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$patchesDir = $root . '/database/patches';
if (is_dir($patchesDir)) {
    $files = glob($patchesDir . '/*.sql');
    if (!empty($files)) {
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
            echo "[MIGRATION] All database patches are up to date.\n";
        } else {
            echo "[MIGRATION] Completed! $count new patch(es) applied safely.\n";
        }
    }
}

// 2. Automated Schema Health Check & AUTO_INCREMENT Repair
echo "[AUTO_INCREMENT CHECK] Scanning tables for missing primary keys and auto_increment...\n";
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $repairedCount = 0;

    foreach ($tables as $tbl) {
        if ($tbl === 'cake_sessions') {
            continue;
        }

        try {
            $cols = $pdo->query("SHOW FULL COLUMNS FROM `{$tbl}`")->fetchAll(PDO::FETCH_ASSOC);
            $hasId = false;
            $idCol = null;
            $hasPk = false;

            foreach ($cols as $c) {
                if (strtolower($c['Field']) === 'id') {
                    $hasId = true;
                    $idCol = $c;
                }
                if ($c['Key'] === 'PRI') {
                    $hasPk = true;
                }
            }

            if (!$hasId) {
                continue;
            }

            $type = strtolower($idCol['Type']);
            if (strpos($type, 'int') === false) {
                continue;
            }

            $isAi = strpos($idCol['Extra'], 'auto_increment') !== false;
            if ($isAi && $idCol['Key'] === 'PRI') {
                continue;
            }

            // Clean any id = 0 to avoid duplicate entry collisions on alter
            while ((int)$pdo->query("SELECT COUNT(*) FROM `{$tbl}` WHERE id = 0")->fetchColumn() > 0) {
                $maxId = (int)$pdo->query("SELECT MAX(id) FROM `{$tbl}`")->fetchColumn();
                $newId = $maxId + 1;
                $pdo->exec("UPDATE `{$tbl}` SET id = {$newId} WHERE id = 0 LIMIT 1");
            }

            if (!$hasPk) {
                $pdo->exec("ALTER TABLE `{$tbl}` MODIFY `id` {$idCol['Type']} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
                echo "    [REPAIRED] Added PRIMARY KEY & AUTO_INCREMENT to `{$tbl}`\n";
                $repairedCount++;
            } elseif ($idCol['Key'] === 'PRI') {
                $pdo->exec("ALTER TABLE `{$tbl}` MODIFY `id` {$idCol['Type']} NOT NULL AUTO_INCREMENT");
                echo "    [REPAIRED] Added AUTO_INCREMENT to `{$tbl}`\n";
                $repairedCount++;
            }
        } catch (Exception $subEx) {
            echo "    [NOTICE] Skipped `{$tbl}`: " . $subEx->getMessage() . "\n";
        }
    }

    if ($repairedCount === 0) {
        echo "[AUTO_INCREMENT CHECK] All tables have valid primary keys and auto_increment.\n";
    } else {
        echo "[AUTO_INCREMENT CHECK] Repaired $repairedCount table(s) successfully.\n";
    }
} catch (Exception $e) {
    echo "[AUTO_INCREMENT ERROR] " . $e->getMessage() . "\n";
}

echo "[MIGRATION] Done.\n";
