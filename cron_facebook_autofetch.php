<?php
/**
 * White Villas CRM - Standalone Facebook Lead Auto-Sync CLI Cron Runner
 *
 * Designed for 100% reliable background execution via cPanel Cron Jobs, Windows Task Scheduler,
 * or direct curl/wget HTTP calls. Operates without requiring an active admin session.
 *
 * cPanel Cron Command (Recommended - runs every 5 minutes):
 * (every 5 mins): /usr/local/bin/php -q /home/YOUR_CPANEL_USER/public_html/cron_facebook_autofetch.php > /dev/null 2>&1
 *
 * Alternative HTTP Cron URL:
 * curl -s "https://yourdomain.com/cron/facebook_leads" > /dev/null 2>&1
 */

@ini_set('max_execution_time', '300');
@set_time_limit(300);
@ini_set('memory_limit', '512M');

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

$request = new CakeRequest('/admin/facebook_integrations/cron_autofetch');
$response = new CakeResponse();
$dispatcher = new Dispatcher();

echo "[" . date('Y-m-d H:i:s') . "] Starting Facebook Lead Ingestion and Auto-Assignment...\n";
try {
    $dispatcher->dispatch($request, $response);
} catch (Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
}
echo "\n[" . date('Y-m-d H:i:s') . "] Completed Facebook Lead Ingestion.\n";