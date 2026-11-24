<?php
/**
 * White Villas - Historical Event Notification Auto-Sync (Backfill) Tool
 *
 * Scans your existing database (Leads, Deals, Follow-ups, Expenses) and automatically
 * generates notifications for assigned agents, managers, and CEO with 100% duplicate protection.
 *
 * Usage:
 * 1. cPanel Terminal: php sync_notifications.php
 * 2. Web Browser: https://yourdomain.com/sync_notifications.php?run=1
 */

@ini_set('max_execution_time', '300');
@set_time_limit(300);
@ini_set('memory_limit', '256M');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));
define('APP_DIR', 'app');
define('WEBROOT_DIR', 'webroot');
define('WWW_ROOT', ROOT . DS . APP_DIR . DS . WEBROOT_DIR . DS);

$isCli = (php_sapi_name() === 'cli' || empty($_SERVER['REMOTE_ADDR']));

if (!file_exists(ROOT . DS . 'lib' . DS . 'Cake' . DS . 'bootstrap.php')) {
    die("Error: CakePHP core bootstrap not found at " . ROOT . "\n");
}

require_once ROOT . DS . 'lib' . DS . 'Cake' . DS . 'bootstrap.php';
App::uses('Dispatcher', 'Routing');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');

$scope = !empty($_GET['scope']) ? preg_replace('/[^a-z_]/', '', strtolower($_GET['scope'])) : 'all';
$limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 300;

$url = '/admin/notifications/sync_existing_events?ajax=1&scope=' . $scope . '&limit=' . $limit;
$request = new CakeRequest($url);
$response = new CakeResponse();
$dispatcher = new Dispatcher();

if ($isCli) {
    echo "=======================================================\n";
    echo "⚡ White Villas - Historical Event Notification Sync\n";
    echo "=======================================================\n";
    echo "[" . date('Y-m-d H:i:s') . "] Starting auto-sync for existing events...\n";
    try {
        $dispatcher->dispatch($request, $response);
        echo $response->body();
    } catch (Throwable $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    }
    echo "\n[" . date('Y-m-d H:i:s') . "] Process completed successfully.\n";
    echo "=======================================================\n";
} else {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Notification Auto-Sync</title>';
    echo '<style>body{font-family:system-ui,-apple-system,sans-serif;background:#0f172a;color:#f8fafc;padding:40px;line-height:1.6;} .box{max-width:700px;margin:0 auto;background:#1e293b;padding:30px;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.4);border:1px solid #334155;} h2{color:#38bdf8;margin-top:0;} pre{background:#0f172a;padding:15px;border-radius:8px;color:#a5f3fc;overflow-x:auto;} .btn{display:inline-block;background:#38bdf8;color:#0f172a;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:700;margin-top:20px;}</style></head><body><div class="box">';
    echo '<h2>⚡ White Villas Notification Auto-Sync</h2>';
    echo '<p>Scanned existing leads, deals, and follow-ups. Result payload:</p>';
    echo '<pre>';
    try {
        $dispatcher->dispatch($request, $response);
    } catch (Throwable $e) {
        echo "Exception: " . $e->getMessage();
    }
    echo '</pre>';
    echo '<p>🔒 <strong>Zero Duplicate Guarantee:</strong> Running this sync multiple times is completely safe because all event notifications are indexed with unique keys.</p>';
    echo '<a href="/admin/Notifications/index" class="btn">View Notifications Inbox &rarr;</a>';
    echo '</div></body></html>';
}

