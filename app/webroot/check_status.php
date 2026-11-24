<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/plain; charset=UTF-8');

echo "=== DIAGNOSTIC REPORT ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n\n";

// 1. Check database.php
$dbFile = dirname(dirname(__FILE__)) . '/Config/database.php';
echo "Checking database.php path: $dbFile\n";
if (file_exists($dbFile)) {
    echo "[OK] app/Config/database.php EXISTS.\n";
    require_once $dbFile;
    if (class_exists('DATABASE_CONFIG')) {
        echo "[OK] DATABASE_CONFIG class found.\n";
        $dbConfig = new DATABASE_CONFIG();
        if (isset($dbConfig->default)) {
            $conf = $dbConfig->default;
            echo "Database details configured: host={$conf['host']}, db={$conf['database']}, user={$conf['login']}\n";
            echo "Testing MySQL Connection via PDO...\n";
            try {
                $dsn = "mysql:host={$conf['host']};dbname={$conf['database']};charset=utf8";
                $pdo = new PDO($dsn, $conf['login'], $conf['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                echo "[SUCCESS] Connected to MySQL successfully via PDO!\n";
                $stmt = $pdo->query("SHOW TABLES;");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo "[SUCCESS] Found " . count($tables) . " tables in database.\n";
            } catch (Exception $e) {
                echo "[ERROR] PDO Connection Failed: " . $e->getMessage() . "\n";
            }
        } else {
            echo "[ERROR] default database configuration not found in DATABASE_CONFIG.\n";
        }
    } else {
        echo "[ERROR] DATABASE_CONFIG class NOT found in database.php.\n";
    }
} else {
    echo "[ERROR] app/Config/database.php DOES NOT EXIST!\n";
}

// 2. Check app/tmp writable
echo "\nChecking app/tmp directories...\n";
$tmp = dirname(dirname(__FILE__)) . '/tmp';
$dirs = [
    $tmp,
    $tmp . '/cache',
    $tmp . '/cache/models',
    $tmp . '/cache/persistent',
    $tmp . '/cache/views',
    $tmp . '/logs'
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    echo is_writable($dir) ? "[OK] Writable: $dir\n" : "[ERROR] NOT Writable: $dir\n";
}

// 3. Try CakePHP Dispatcher
echo "\nTesting CakePHP Dispatcher load...\n";
try {
    define('DS', DIRECTORY_SEPARATOR);
    define('ROOT', dirname(dirname(dirname(__FILE__))));
    define('APP_DIR', basename(dirname(dirname(__FILE__))));
    define('WEBROOT_DIR', basename(dirname(__FILE__)));
    define('WWW_ROOT', dirname(__FILE__) . DS);

    if (!defined('CAKE_CORE_INCLUDE_PATH')) {
        if (function_exists('ini_set')) {
            ini_set('include_path', ROOT . DS . 'lib' . PATH_SEPARATOR . ini_get('include_path'));
        }
        require 'Cake' . DS . 'bootstrap.php';
    }

    echo "[OK] CakePHP bootstrap loaded.\n";
    Configure::write('debug', 2);
    App::uses('Dispatcher', 'Routing');
    $dispatcher = new Dispatcher();
    echo "[OK] Dispatcher initialized successfully.\n";
} catch (Throwable $t) {
    echo "[EXCEPTION] CakePHP bootstrap/dispatcher failed:\n" . $t->getMessage() . "\n";
    echo $t->getTraceAsString() . "\n";
}

echo "\n=== END DIAGNOSTIC REPORT ===\n";
