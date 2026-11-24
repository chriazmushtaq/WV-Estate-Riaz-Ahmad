<?php
/**
 * White Villas Real-time Notification Engine - CLI / cPanel Cron Runner
 *
 * Runs scheduled follow-up reminders, overdue alerts, and automatic cleanup.
 * Designed for 100% reliable execution via cPanel Cron Jobs, Windows Task Scheduler,
 * or curl/wget HTTP calls. Operates without requiring an active user session.
 *
 * Recommended cPanel Cron Command (Every 5 or 10 minutes):
 * (every 5 mins): /usr/local/bin/php -q /home/YOUR_CPANEL_USER/public_html/cron_notifications.php > /dev/null 2>&1
 *
 * Alternative HTTP Cron URL:
 * curl -s "https://yourdomain.com/admin/notifications/cron_reminders" > /dev/null 2>&1
 */

@ini_set('max_execution_time', '180');
@set_time_limit(180);
@ini_set('memory_limit', '256M');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));
define('APP_DIR', 'app');
define('WEBROOT_DIR', 'webroot');
define('WWW_ROOT', ROOT . DS . APP_DIR . DS . WEBROOT_DIR . DS);

if (!file_exists(ROOT . DS . 'lib' . DS . 'Cake' . DS . 'bootstrap.php')) {
    echo "[" . date('Y-m-d H:i:s') . "] Error: CakePHP core bootstrap not found at " . ROOT . "\n";
    exit(1);
}

require_once ROOT . DS . 'lib' . DS . 'Cake' . DS . 'bootstrap.php';
App::uses('Dispatcher', 'Routing');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');

$request = new CakeRequest('/admin/notifications/cron_reminders');
$response = new CakeResponse();
$dispatcher = new Dispatcher();

echo "[" . date('Y-m-d H:i:s') . "] Starting Notification Reminders & Maintenance Cron...\n";
try {
    $dispatcher->dispatch($request, $response);
} catch (Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
}
echo "\n[" . date('Y-m-d H:i:s') . "] Completed Notification Cron.\n";
