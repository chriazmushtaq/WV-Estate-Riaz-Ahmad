<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
Router::connect('/api/properties/view/*', array('controller' => 'api', 'action' => 'view'));
Router::connect('/api/properties', array('controller' => 'api', 'action' => 'properties'));
Router::connect('/api/meta', array('controller' => 'api', 'action' => 'meta'));
Router::connect('/api/contact', array('controller' => 'api', 'action' => 'contact'));
Router::connect('/api/login', array('controller' => 'api', 'action' => 'login'));
Router::connect('/api/:action/*', array('controller' => 'api'));

Router::connect('/admin/api/properties/view/*', array('controller' => 'api', 'action' => 'view'));
Router::connect('/admin/api/properties', array('controller' => 'api', 'action' => 'properties'));
Router::connect('/admin/api/meta', array('controller' => 'api', 'action' => 'meta'));
Router::connect('/admin/api/contact', array('controller' => 'api', 'action' => 'contact'));
Router::connect('/admin/api/login', array('controller' => 'api', 'action' => 'login'));
Router::connect('/admin/api/:action/*', array('controller' => 'api'));

// Facebook Integrations Public Webhook & Cron Sync Routes
Router::connect('/facebook_integrations/cron_autofetch', array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'action' => 'cron_autofetch'));
Router::connect('/facebook_integrations/webhook', array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'action' => 'webhook'));
Router::connect('/cron/facebook_leads', array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'action' => 'cron_autofetch'));
Router::connect('/cron/facebook_autofetch', array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'action' => 'cron_autofetch'));

// Portal Menus Route Aliases
Router::connect('/admin/agency_portal_menus', array('plugin' => 'admin', 'controller' => 'portalmenus', 'action' => 'index'));
Router::connect('/admin/agency_portal_menus/:action/*', array('plugin' => 'admin', 'controller' => 'portalmenus'));

Router::connect('/bills', array('controller' => 'bills', 'action' => 'check_online'));
Router::connect('/Bills', array('controller' => 'bills', 'action' => 'check_online'));
Router::connect('/bills/check_online', array('controller' => 'bills', 'action' => 'check_online'));
Router::connect('/Bills/check_online', array('controller' => 'bills', 'action' => 'check_online'));
Router::connect('/bills/checkOnline', array('controller' => 'bills', 'action' => 'check_online'));
Router::connect('/Bills/checkOnline', array('controller' => 'bills', 'action' => 'check_online'));
Router::connect('/bills/:action/*', array('controller' => 'bills'));
Router::connect('/Bills/:action/*', array('controller' => 'bills'));
Router::connect('/online-bill', array('controller' => 'bills', 'action' => 'check_online'));
Router::connect('/check-online', array('controller' => 'bills', 'action' => 'check_online'));

Router::connect('/UtilityBills', array('controller' => 'utility_bills', 'action' => 'index'));
Router::connect('/UtilityBills/:action/*', array('controller' => 'utility_bills'));
Router::connect('/utilitybills', array('controller' => 'utility_bills', 'action' => 'index'));
Router::connect('/utilitybills/:action/*', array('controller' => 'utility_bills'));
Router::connect('/utility_bills', array('controller' => 'utility_bills', 'action' => 'index'));
Router::connect('/utility_bills/:action/*', array('controller' => 'utility_bills'));
Router::connect('/MyUtilityBills', array('controller' => 'utility_bills', 'action' => 'index'));
Router::connect('/MyUtilityBills/:action/*', array('controller' => 'utility_bills'));
Router::connect('/myutilitybills', array('controller' => 'utility_bills', 'action' => 'index'));
Router::connect('/myutilitybills/:action/*', array('controller' => 'utility_bills'));
Router::connect('/my_utility_bills', array('controller' => 'utility_bills', 'action' => 'index'));
Router::connect('/my_utility_bills/:action/*', array('controller' => 'utility_bills'));

// Letter Public Verification Routes
Router::connect('/verify/cnic/:id/:token', array('controller' => 'letterverifications', 'action' => 'verify_cnic'), array('pass' => array('id', 'token')));
Router::connect('/verify/letter/:id/:token', array('controller' => 'letterverifications', 'action' => 'verify'), array('pass' => array('id', 'token')));
Router::connect('/verify/letter/:id', array('controller' => 'letterverifications', 'action' => 'verify'), array('pass' => array('id')));
Router::connect('/verify/letter/*', array('controller' => 'letterverifications', 'action' => 'verify'));
Router::connect('/verify/:action/*', array('controller' => 'letterverifications'));
Router::connect('/letter_verifications/verify/:id/:token', array('controller' => 'letterverifications', 'action' => 'verify'), array('pass' => array('id', 'token')));
Router::connect('/letter_verifications/verify/:id', array('controller' => 'letterverifications', 'action' => 'verify'), array('pass' => array('id')));

Router::connect('/billing', array('plugin' => 'BillingManager', 'controller' => 'BillingPortals', 'action' => 'check_bill'));
Router::connect('/billing/history', array('plugin' => 'BillingManager', 'controller' => 'BillingPortals', 'action' => 'history'));
Router::connect('/billing/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingPortals'));
Router::connect('/check-bill', array('plugin' => 'BillingManager', 'controller' => 'BillingPortals', 'action' => 'check_bill'));
Router::connect('/paymentplans', array('controller' => 'paymentplans', 'action' => 'index'));
Router::connect('/paymentplans/:slug', array('controller' => 'paymentplans', 'action' => 'view'), array('pass' => array('slug'), 'slug' => '[A-Za-z0-9\-_]+'));
Router::connect('/apartments-detailes', array('controller' => 'paymentplans', 'action' => 'index'));
Router::connect('/apartments-detailes/:slug', array('controller' => 'paymentplans', 'action' => 'view'), array('pass' => array('slug'), 'slug' => '[A-Za-z0-9\-_]+'));
Router::connect('/apartments-details', array('controller' => 'paymentplans', 'action' => 'index'));
Router::connect('/apartments-details/:slug', array('controller' => 'paymentplans', 'action' => 'view'), array('pass' => array('slug'), 'slug' => '[A-Za-z0-9\-_]+'));
Router::connect('/Properties/:slug', array('controller' => 'properties', 'action' => 'view'), array(
    'pass' => array('slug'),
    'slug' => '(?!view$|index$|search$|calculator$|sendbymail$|printproperty$|inquiry$|favourite$|compare$|rating$|savesearch$|trackcontact$|[0-9]+$)[A-Za-z0-9\-_]+'
));

// Robust Multi-Location Installed Guard
$dbConfigExists = file_exists(APP . 'Config' . DS . 'database.php');
$applicationInstalled = false;

// If a user explicitly navigates to /install, /installer, or /installers, let's allow the installer controller to run!
$isExplicitInstallUrl = false;
$rawRequestUri = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '';
$rawRequestPath = parse_url($rawRequestUri, PHP_URL_PATH);
if ($rawRequestPath !== false && preg_match('#/(install|installer|installers)(/|$)#i', $rawRequestPath)) {
    $isExplicitInstallUrl = true;
}

if ($dbConfigExists) {
    // Only consider installed if database connection can actually be established
    // and core tables exist. This prevents infinite redirect loops and broken sites on cPanel.
    try {
        App::uses('ConnectionManager', 'Model');
        $db = ConnectionManager::getDataSource('default');
        if ($db && $db->isConnected()) {
            $tables = $db->listSources();
            if (in_array('configurations', $tables, true) || in_array('users', $tables, true)) {
                $applicationInstalled = true;
            }
        }
    } catch (Exception $e) {
        $applicationInstalled = false;
    }
}

if ($applicationInstalled && !$isExplicitInstallUrl) {
    // Auto self-heal tmp lock file if cleared during cache purge
    if (!file_exists(TMP . 'installed.txt')) {
        @file_put_contents(TMP . 'installed.txt', "INSTALLED: " . date('Y-m-d H:i:s'));
    }
    if (!file_exists(APP . 'Config' . DS . 'installed.txt')) {
        @file_put_contents(APP . 'Config' . DS . 'installed.txt', "INSTALLED: " . date('Y-m-d H:i:s'));
    }

    Router::connect('/', array('controller' => 'fronts', 'action' => 'display', 'home'));
    Router::connect('/pages/*', array('controller' => 'fronts', 'action' => 'display'));

    // If fully installed and not an explicit installer URL, protect installer routes
    Router::connect('/install', array('plugin' => 'admin', 'controller' => 'users', 'action' => 'login', 'admin' => true));
    Router::connect('/installer', array('plugin' => 'admin', 'controller' => 'users', 'action' => 'login', 'admin' => true));
    Router::connect('/installers', array('plugin' => 'admin', 'controller' => 'users', 'action' => 'login', 'admin' => true));
} else {
    // Not yet installed, or database not yet configured/connected on this server:
    // Route all base and installer URLs directly to the installer wizard!
    Router::connect('/', array('controller' => 'installers', 'action' => 'index'));
    Router::connect('/install', array('controller' => 'installers', 'action' => 'index'));
    Router::connect('/installer', array('controller' => 'installers', 'action' => 'index'));
    Router::connect('/installers', array('controller' => 'installers', 'action' => 'index'));
    Router::connect('/installers/:action/*', array('controller' => 'installers'));
}
/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
    Router::connect('/admin/invpastdues/limit::limit', array(
        'admin' => true,
        'plugin' => 'admin',
        'controller' => 'invpastdues',
        'action' => 'index'
    ), array(
        'limit' => '[0-9]+'
    ));
    Router::connect('/admin/deals/limit::limit', array(
        'admin' => true,
        'plugin' => 'admin',
        'controller' => 'deals',
        'action' => 'index'
    ), array(
        'limit' => '[0-9]+'
    ));
    Router::connect('/admin/media_folders/:action/*', array(
        'admin' => true,
        'plugin' => 'admin',
        'controller' => 'media_galleries'
    ));
    Router::connect('/admin/media_files/:action/*', array(
        'admin' => true,
        'plugin' => 'admin',
        'controller' => 'media_galleries'
    ));

    Router::connect('/admin/RealEstateThemes', array('plugin' => 'RealEstateTheme', 'controller' => 'RealEstateThemes', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/RealEstateThemes/:action/*', array('plugin' => 'RealEstateTheme', 'controller' => 'RealEstateThemes', 'admin' => true));
    Router::connect('/admin/real_estate_themes', array('plugin' => 'RealEstateTheme', 'controller' => 'RealEstateThemes', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/real_estate_themes/:action/*', array('plugin' => 'RealEstateTheme', 'controller' => 'RealEstateThemes', 'admin' => true));
    Router::connect('/admin/realestatethemes', array('plugin' => 'RealEstateTheme', 'controller' => 'RealEstateThemes', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/realestatethemes/:action/*', array('plugin' => 'RealEstateTheme', 'controller' => 'RealEstateThemes', 'admin' => true));

    // BillingManager Direct Admin Routes
    Router::connect('/admin/BillingManager', array('plugin' => 'BillingManager', 'controller' => 'BillingDashboards', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_manager', array('plugin' => 'BillingManager', 'controller' => 'BillingDashboards', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billingmanager', array('plugin' => 'BillingManager', 'controller' => 'BillingDashboards', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingManager/:controller', array('plugin' => 'BillingManager', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingManager/:controller/:action/*', array('plugin' => 'BillingManager', 'admin' => true));
    Router::connect('/admin/billing_manager/:controller', array('plugin' => 'BillingManager', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_manager/:controller/:action/*', array('plugin' => 'BillingManager', 'admin' => true));
    Router::connect('/admin/billingmanager/:controller', array('plugin' => 'BillingManager', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billingmanager/:controller/:action/*', array('plugin' => 'BillingManager', 'admin' => true));
    Router::connect('/admin/BillingDashboards', array('plugin' => 'BillingManager', 'controller' => 'BillingDashboards', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingDashboards/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingDashboards', 'admin' => true));
    Router::connect('/admin/billing_dashboards', array('plugin' => 'BillingManager', 'controller' => 'BillingDashboards', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_dashboards/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingDashboards', 'admin' => true));
    Router::connect('/admin/BillingBills', array('plugin' => 'BillingManager', 'controller' => 'BillingBills', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingBills/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingBills', 'admin' => true));
    Router::connect('/admin/billing_bills', array('plugin' => 'BillingManager', 'controller' => 'BillingBills', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_bills/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingBills', 'admin' => true));
    Router::connect('/admin/BillingPaymentRequests', array('plugin' => 'BillingManager', 'controller' => 'BillingPaymentRequests', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingPaymentRequests/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingPaymentRequests', 'admin' => true));
    Router::connect('/admin/billing_payment_requests', array('plugin' => 'BillingManager', 'controller' => 'BillingPaymentRequests', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_payment_requests/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingPaymentRequests', 'admin' => true));
    Router::connect('/admin/BillingReadings', array('plugin' => 'BillingManager', 'controller' => 'BillingReadings', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingReadings/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingReadings', 'admin' => true));
    Router::connect('/admin/billing_readings', array('plugin' => 'BillingManager', 'controller' => 'BillingReadings', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_readings/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingReadings', 'admin' => true));
    Router::connect('/admin/BillingMeters', array('plugin' => 'BillingManager', 'controller' => 'BillingMeters', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingMeters/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingMeters', 'admin' => true));
    Router::connect('/admin/billing_meters', array('plugin' => 'BillingManager', 'controller' => 'BillingMeters', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_meters/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingMeters', 'admin' => true));
    Router::connect('/admin/BillingProperties', array('plugin' => 'BillingManager', 'controller' => 'BillingProperties', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingProperties/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingProperties', 'admin' => true));
    Router::connect('/admin/billing_properties', array('plugin' => 'BillingManager', 'controller' => 'BillingProperties', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_properties/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingProperties', 'admin' => true));
    Router::connect('/admin/BillingClients', array('plugin' => 'BillingManager', 'controller' => 'BillingClients', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingClients/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingClients', 'admin' => true));
    Router::connect('/admin/billing_clients', array('plugin' => 'BillingManager', 'controller' => 'BillingClients', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_clients/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingClients', 'admin' => true));
    Router::connect('/admin/BillingExtraCharges', array('plugin' => 'BillingManager', 'controller' => 'BillingExtraCharges', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingExtraCharges/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingExtraCharges', 'admin' => true));
    Router::connect('/admin/billing_extra_charges', array('plugin' => 'BillingManager', 'controller' => 'BillingExtraCharges', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_extra_charges/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingExtraCharges', 'admin' => true));
    Router::connect('/admin/BillingTemplates', array('plugin' => 'BillingManager', 'controller' => 'BillingTemplates', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingTemplates/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingTemplates', 'admin' => true));
    Router::connect('/admin/billing_templates', array('plugin' => 'BillingManager', 'controller' => 'BillingTemplates', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_templates/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingTemplates', 'admin' => true));
    Router::connect('/admin/BillingBankReconciliations', array('plugin' => 'BillingManager', 'controller' => 'BillingBankReconciliations', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingBankReconciliations/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingBankReconciliations', 'admin' => true));
    Router::connect('/admin/billing_bank_reconciliations', array('plugin' => 'BillingManager', 'controller' => 'BillingBankReconciliations', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_bank_reconciliations/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingBankReconciliations', 'admin' => true));
    Router::connect('/admin/BillingReminders', array('plugin' => 'BillingManager', 'controller' => 'BillingReminders', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingReminders/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingReminders', 'admin' => true));
    Router::connect('/admin/billing_reminders', array('plugin' => 'BillingManager', 'controller' => 'BillingReminders', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_reminders/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingReminders', 'admin' => true));
    Router::connect('/admin/BillingSocieties', array('plugin' => 'BillingManager', 'controller' => 'BillingSocieties', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingSocieties/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingSocieties', 'admin' => true));
    Router::connect('/admin/billing_societies', array('plugin' => 'BillingManager', 'controller' => 'BillingSocieties', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_societies/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingSocieties', 'admin' => true));
    Router::connect('/admin/BillingAuditLogs', array('plugin' => 'BillingManager', 'controller' => 'BillingAuditLogs', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingAuditLogs/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingAuditLogs', 'admin' => true));
    Router::connect('/admin/billing_audit_logs', array('plugin' => 'BillingManager', 'controller' => 'BillingAuditLogs', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_audit_logs/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingAuditLogs', 'admin' => true));
    Router::connect('/admin/BillingSettings', array('plugin' => 'BillingManager', 'controller' => 'BillingSettings', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingSettings/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingSettings', 'admin' => true));
    Router::connect('/admin/billing_settings', array('plugin' => 'BillingManager', 'controller' => 'BillingSettings', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_settings/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingSettings', 'admin' => true));
    Router::connect('/admin/BillingMigrations', array('plugin' => 'BillingManager', 'controller' => 'BillingMigrations', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingMigrations/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingMigrations', 'admin' => true));
    Router::connect('/admin/billing_migrations', array('plugin' => 'BillingManager', 'controller' => 'BillingMigrations', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_migrations/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingMigrations', 'admin' => true));
    Router::connect('/admin/BillingReports', array('plugin' => 'BillingManager', 'controller' => 'BillingReports', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/BillingReports/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingReports', 'admin' => true));
    Router::connect('/admin/billing_reports', array('plugin' => 'BillingManager', 'controller' => 'BillingReports', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/billing_reports/:action/*', array('plugin' => 'BillingManager', 'controller' => 'BillingReports', 'admin' => true));
    Router::connect('/admin/modules', array('plugin' => 'admin', 'controller' => 'modules', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/modules/:action/*', array('plugin' => 'admin', 'controller' => 'modules', 'admin' => true));
    Router::connect('/admin/Modules', array('plugin' => 'admin', 'controller' => 'modules', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/Modules/:action/*', array('plugin' => 'admin', 'controller' => 'modules', 'admin' => true));
    Router::connect('/admin/installment_sync_settings', array('plugin' => 'admin', 'controller' => 'installment_sync_settings', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/installment_sync_settings/:action/*', array('plugin' => 'admin', 'controller' => 'installment_sync_settings', 'admin' => true));
    Router::connect('/admin/InstallmentSyncSettings', array('plugin' => 'admin', 'controller' => 'installment_sync_settings', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/system_updates', array('plugin' => 'admin', 'controller' => 'system_updates', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/system_updates/:action/*', array('plugin' => 'admin', 'controller' => 'system_updates', 'admin' => true));
    Router::connect('/admin/SystemUpdates', array('plugin' => 'admin', 'controller' => 'system_updates', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/SystemUpdates/:action/*', array('plugin' => 'admin', 'controller' => 'system_updates', 'admin' => true));
    Router::connect('/admin/Systemupdates', array('plugin' => 'admin', 'controller' => 'system_updates', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/Systemupdates/:action/*', array('plugin' => 'admin', 'controller' => 'system_updates', 'admin' => true));
    Router::connect('/admin/systemupdates', array('plugin' => 'admin', 'controller' => 'system_updates', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/systemupdates/:action/*', array('plugin' => 'admin', 'controller' => 'system_updates', 'admin' => true));

    // Facebook Integration Public Webhook & Admin Routes
    Router::connect('/api/facebook/webhook', array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'action' => 'webhook'));
    Router::connect('/admin/facebook_integrations', array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/facebook_integrations/:action/*', array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'admin' => true));
    Router::connect('/admin/FacebookIntegrations', array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/FacebookIntegrations/:action/*', array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'admin' => true));

    // Facebook Accounts Route Aliases
    Router::connect('/admin/facebook_accounts', array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'action' => 'settings', 'admin' => true));
    Router::connect('/admin/facebook_accounts/:action/*', array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'admin' => true));
    Router::connect('/admin/FacebookAccounts', array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'action' => 'settings', 'admin' => true));
    Router::connect('/admin/FacebookAccounts/:action/*', array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'admin' => true));

    // Admin Plugin Generic Routes (maps all backend controllers cleanly without /index/admin:1 named parameter)
    Router::connect('/admin/:controller', array('plugin' => 'admin', 'action' => 'index', 'admin' => true));
    Router::connect('/admin/:controller/:action/*', array('plugin' => 'admin', 'admin' => true));

	CakePlugin::routes();

	Router::connect('/admin/trash', array(
		'admin' => true,
		'plugin' => 'admin',
		'controller' => 'ardaccounts',
		'action' => 'trash'
	));

/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
