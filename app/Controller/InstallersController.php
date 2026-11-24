<?php
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('ConnectionManager', 'Model');
ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');

class InstallersController extends AppController
{
    public $uses = array();
    public $components = array('Session');
    public $helpers = array('Html', 'Form', 'Session', 'Js' => array('Jquery'));
    protected $_requiredPhpMin = '7.2.0';
    protected $_requiredPhpMax = '8.4.0';

    public function beforeFilter()
    {
        // Do not call parent::beforeFilter() here; installer must run before DB/config bootstrap.
        if ($this->_isInstalled()) {
            $this->redirect(array('plugin' => 'admin', 'controller' => 'users', 'action' => 'login', 'admin' => true));
            return;
        }
    }

    public function index()
    {
        $this->layout = null;
        $requirements = $this->_buildRequirementChecks();
        $permissions = $this->_buildPermissionChecks();
        $allRequiredOk = $this->_allRequiredChecksPass($requirements) && $this->_allRequiredChecksPass($permissions);
        $this->set('requirements', $requirements);
        $this->set('permissions', $permissions);
        $this->set('allRequiredOk', $allRequiredOk);
        $this->set('serverSoftware', (string)env('SERVER_SOFTWARE'));
        $this->set('phpVersion', PHP_VERSION);
        $this->set('mysqlClientVersion', $this->_mysqlClientVersion());
    }

    public function step1()
    {
        $this->layout = null;
        $requirements = $this->_buildRequirementChecks();
        $permissions = $this->_buildPermissionChecks();
        $allRequiredOk = $this->_allRequiredChecksPass($requirements) && $this->_allRequiredChecksPass($permissions);
        $this->set('allRequiredOk', $allRequiredOk);

        if (!$allRequiredOk) {
            $this->Session->setFlash(__('Please resolve all required server checks before installation.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
    }

    public function step2()
    {
        $this->layout = null;
        $dbFileStatus = $this->_databaseFileStatus();
        $this->set('dbFileStatus', $dbFileStatus);

        if (!$dbFileStatus['ok']) {
            $this->Session->setFlash(__('Database configuration file is not writable. Please fix permissions for app/Config/database.php.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        if (empty($this->request->data['Installer']['hostname'])) {
            $this->request->data['Installer']['hostname'] = 'localhost';
        }
        if (empty($this->request->data['Installer']['dbtype'])) {
            $this->request->data['Installer']['dbtype'] = 'Mysqli';
        }

        if (!$this->request->is(array('post', 'put'))) {
            return;
        }

        $isConnectionSubmit = !empty($this->request->data['Installer']['dbconnection']);
        if (!$isConnectionSubmit) {
            $accepted = !empty($this->request->data['Installer']['accept']);
            if (!$accepted) {
                $this->Session->setFlash(__('Please accept the installation terms to continue.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'step1'));
            }
            return;
        }

        $safe = array(
            'hostname' => trim((string)$this->request->data['Installer']['hostname']),
            'dbname' => trim((string)$this->request->data['Installer']['dbname']),
            'dbuser' => trim((string)$this->request->data['Installer']['dbuser']),
            'dbpassword' => isset($this->request->data['Installer']['dbpassword']) ? (string)$this->request->data['Installer']['dbpassword'] : '',
            'dbtype' => trim((string)$this->request->data['Installer']['dbtype'])
        );

        if ($safe['hostname'] === '' || $safe['dbname'] === '' || $safe['dbuser'] === '') {
            $this->Session->setFlash(__('Host, database name and database user are required.'), 'flash', array('alert' => 'danger'));
            $this->request->data['Installer'] = array_merge((array)$this->request->data['Installer'], $safe);
            return;
        }

        if (!in_array($safe['dbtype'], array('Mysql', 'Mysqli'), true)) {
            $safe['dbtype'] = 'Mysqli';
        }

        $test = $this->_testDatabaseConnection($safe);
        if (empty($test['ok'])) {
            $this->Session->setFlash(sprintf(__('Database connection failed: %s'), (isset($test['message']) ? $test['message'] : __('Unknown error'))), 'flash', array('alert' => 'danger'));
            $this->request->data['Installer'] = array_merge((array)$this->request->data['Installer'], $safe);
            return;
        }
        if (!empty($test['params']) && is_array($test['params'])) {
            $safe = array_merge($safe, $test['params']);
        }

        $write = $this->_writeDatabaseConfig($safe);
        if (empty($write['ok'])) {
            $this->Session->setFlash(sprintf(__('Connected to database, but unable to save config file: %s'), (isset($write['message']) ? $write['message'] : __('Unknown error'))), 'flash', array('alert' => 'danger'));
            $this->request->data['Installer'] = array_merge((array)$this->request->data['Installer'], $safe);
            return;
        }

        $this->Session->setFlash(__('Database connection successful and configuration saved.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'step3'));
    }

    public function step3()
    {
        $this->layout = null;
        $timezoneOptions = $this->_timezoneOptions();
        $this->set('timezoneOptions', $timezoneOptions);

        if (!$this->_databaseConfigExists()) {
            $this->Session->setFlash(__('Please configure database connection first.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'step2'));
        }

        $dbReady = $this->_validateDefaultDatasource();
        if (empty($dbReady['ok'])) {
            $this->Session->setFlash(sprintf(__('Unable to connect using saved database config: %s'), (isset($dbReady['message']) ? $dbReady['message'] : __('Unknown error'))), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'step2'));
        }

        if (!$this->request->is(array('post', 'put'))) {
            $this->request->data = array(
                'Installer' => array(
                    'name' => 'White Villas',
                    'domain_name' => $this->_defaultDomain(),
                    'meta_title' => 'White Villas',
                    'meta_desc' => 'Real estate portal',
                    'timezone' => 'Asia/Karachi',
                    'organization_name' => 'White Villas',
                    'email' => '',
                    'installdata' => 0
                )
            );
            return;
        }

        if (empty($this->request->data['Installer']['step3'])) {
            return $this->redirect(array('action' => 'step3'));
        }

        $posted = (array)$this->request->data['Installer'];
        $installSampleData = !empty($posted['installdata']) ? 1 : 0;
        $name = trim((string)$posted['name']);
        $domainName = $this->_normalizeDomainName((string)$posted['domain_name']);
        $metaTitle = trim((string)$posted['meta_title']);
        $metaDesc = trim((string)$posted['meta_desc']);
        $timezone = trim((string)$posted['timezone']);
        $organizationName = trim((string)$posted['organization_name']);
        $email = trim((string)$posted['email']);

        if ($name === '' || $domainName === '' || $timezone === '' || $organizationName === '' || $email === '') {
            $this->Session->setFlash(__('Please fill all required fields.'), 'flash', array('alert' => 'danger'));
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->Session->setFlash(__('Please enter a valid organization email address.'), 'flash', array('alert' => 'danger'));
            return;
        }
        if (!isset($timezoneOptions[$timezone])) {
            $this->Session->setFlash(__('Please select a valid timezone.'), 'flash', array('alert' => 'danger'));
            return;
        }

        if (!$this->database($installSampleData)) {
            return;
        }

        $this->loadModel('Configuration');
        $this->Configuration->id = 1;
        $saveData = array(
            'Configuration' => array(
                'name' => $name,
                'domain_name' => $domainName,
                'meta_title' => ($metaTitle !== '' ? $metaTitle : $name),
                'meta_desc' => $metaDesc,
                'timezone' => $timezone,
                'organization_name' => $organizationName,
                'email' => $email
            )
        );

        if (!$this->Configuration->save($saveData, array('validate' => false))) {
            $this->Session->setFlash(__('Database imported, but configuration could not be saved.'), 'flash', array('alert' => 'danger'));
            return;
        }

        if (!$this->thanks()) {
            return;
        }
        return $this->redirect(array('controller' => 'Completes', 'action' => 'index'));
    }

    public function database($installdata)
    {
        try {
            $db = ConnectionManager::getDataSource('default');
            if (!$db->isConnected()) {
                $this->Session->setFlash(__('Could not connect to database. Please check app/Config/database.php and try again.'), 'flash', array('alert' => 'danger'));
                return false;
            }

            $fileName = $this->_resolveSqlFile((int)$installdata === 1);
            if ($fileName === '') {
                $this->Session->setFlash(__('SQL install file was not found.'), 'flash', array('alert' => 'danger'));
                return false;
            }

            $this->__executeSQLScript($db, $fileName);
            $PaymentPlanPage = ClassRegistry::init('AgencyPaymentPlanPage');
            if($PaymentPlanPage && method_exists($PaymentPlanPage, 'ensureSchema'))
            {
                $PaymentPlanPage->ensureSchema();
            }
            $this->_postInstallModuleSync($db);
            return true;
        } catch (Exception $e) {
            $this->Session->setFlash(sprintf(__('Database import failed: %s'), $e->getMessage()), 'flash', array('alert' => 'danger'));
            return false;
        }
    }

    public function thanks()
    {
        try {
            $file = new File(TMP . 'installed.txt', true, 0644);
            if (!$file->writable()) {
                $this->Session->setFlash(__('Permission error: app/tmp must be writable.'), 'flash', array('alert' => 'danger'));
                $this->redirect(array('action' => 'step3'));
                return false;
            }
            $payload = date('Y-m-d, H:i:s');
            $file->write($payload, 'w', true);
            $file->close();
            return true;
        } catch (Exception $e) {
            $this->Session->setFlash(__('Unable to write installer lock file in app/tmp.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'step3'));
            return false;
        }
    }

    protected function _isInstalled()
    {
        if (!$this->_installerLockExists()) {
            return false;
        }
        if (!$this->_databaseConfigExists()) {
            return false;
        }
        if (!$this->_installerRuntimeReady()) {
            return false;
        }
        try {
            $db = ConnectionManager::getDataSource('default');
            if (!$db->isConnected()) {
                return false;
            }
            $configurationTable = $db->query("SHOW TABLES LIKE 'configurations'");
            if (empty($configurationTable)) {
                return false;
            }
            $configurationRows = $db->query("SELECT id FROM configurations LIMIT 1");
            return !empty($configurationRows);
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _installerLockExists()
    {
        if (file_exists(APP . 'Config' . DS . 'installed.txt') || file_exists(APP . 'installed.lock') || file_exists(APP . 'Config' . DS . 'database.php')) {
            return true;
        }
        if (file_exists(TMP . 'installed.txt')) {
            return true;
        }
        $lockFiles = (array)glob(TMP . '*installed.txt');
        return !empty($lockFiles);
    }

    protected function _mysqlClientVersion()
    {
        if (function_exists('mysqli_get_client_info')) {
            return (string)mysqli_get_client_info();
        }
        return __('Unavailable');
    }

    protected function _buildRequirementChecks()
    {
        $phpOk = version_compare(PHP_VERSION, $this->_requiredPhpMin, '>=') && version_compare(PHP_VERSION, $this->_requiredPhpMax, '<');
        $imageLibOk = extension_loaded('gd') || extension_loaded('imagick');

        return array(
            array('label' => __('Web Server'), 'required' => true, 'ok' => true, 'value' => (string)env('SERVER_SOFTWARE')),
            array('label' => sprintf(__('PHP Version (%s to %s)'), $this->_requiredPhpMin, '8.3.x'), 'required' => true, 'ok' => $phpOk, 'value' => PHP_VERSION),
            array('label' => __('PDO Extension'), 'required' => true, 'ok' => extension_loaded('pdo'), 'value' => extension_loaded('pdo') ? __('Enabled') : __('Disabled')),
            array('label' => __('PDO MySQL Extension'), 'required' => true, 'ok' => extension_loaded('pdo_mysql'), 'value' => extension_loaded('pdo_mysql') ? __('Enabled') : __('Disabled')),
            array('label' => __('MySQLi Extension'), 'required' => true, 'ok' => extension_loaded('mysqli'), 'value' => extension_loaded('mysqli') ? __('Enabled') : __('Disabled')),
            array('label' => __('JSON Extension'), 'required' => true, 'ok' => extension_loaded('json'), 'value' => extension_loaded('json') ? __('Enabled') : __('Disabled')),
            array('label' => __('MBString Extension'), 'required' => true, 'ok' => extension_loaded('mbstring'), 'value' => extension_loaded('mbstring') ? __('Enabled') : __('Disabled')),
            array('label' => __('OpenSSL Extension'), 'required' => true, 'ok' => extension_loaded('openssl'), 'value' => extension_loaded('openssl') ? __('Enabled') : __('Disabled')),
            array('label' => __('Zip Extension (ZipArchive, recommended for backups)'), 'required' => false, 'ok' => class_exists('ZipArchive'), 'value' => class_exists('ZipArchive') ? __('Enabled') : __('Disabled')),
            array('label' => __('XML + DOM Extension'), 'required' => true, 'ok' => (extension_loaded('xml') && class_exists('DOMDocument')), 'value' => (extension_loaded('xml') && class_exists('DOMDocument')) ? __('Enabled') : __('Disabled')),
            array('label' => __('Image Library (GD or Imagick)'), 'required' => true, 'ok' => $imageLibOk, 'value' => $imageLibOk ? __('Enabled') : __('Disabled')),
            array('label' => __('cURL Extension (recommended for APIs)'), 'required' => false, 'ok' => extension_loaded('curl'), 'value' => extension_loaded('curl') ? __('Enabled') : __('Disabled'))
        );
    }

    protected function _buildPermissionChecks()
    {
        $checks = array();
        $checks[] = $this->_pathCheck(APP . 'tmp', 'app/tmp', true, true);
        $checks[] = $this->_pathCheck(APP . 'tmp' . DS . 'cache', 'app/tmp/cache', true, true);
        $checks[] = $this->_pathCheck(APP . 'tmp' . DS . 'cache' . DS . 'models', 'app/tmp/cache/models', true, true);
        $checks[] = $this->_pathCheck(APP . 'tmp' . DS . 'cache' . DS . 'persistent', 'app/tmp/cache/persistent', true, true);
        $checks[] = $this->_pathCheck(APP . 'tmp' . DS . 'logs', 'app/tmp/logs', true, true);
        $checks[] = $this->_pathCheck(APP . 'webroot' . DS . 'img', 'app/webroot/img', true, true);

        $dbFileStatus = $this->_databaseFileStatus();
        $checks[] = array(
            'label' => 'app/Config/database.php',
            'required' => true,
            'ok' => !empty($dbFileStatus['ok']),
            'value' => !empty($dbFileStatus['message']) ? $dbFileStatus['message'] : __('Writable')
        );

        return $checks;
    }

    protected function _pathCheck($absolutePath, $label, $required = true, $isDir = true)
    {
        $exists = $isDir ? is_dir($absolutePath) : is_file($absolutePath);
        $writable = $exists ? is_writable($absolutePath) : false;
        $ok = $exists && $writable;
        $value = '';
        if (!$exists) {
            $value = __('Missing');
        } elseif (!$writable) {
            $value = __('Not writable');
        } else {
            $value = __('Writable');
        }

        return array(
            'label' => $label,
            'required' => (bool)$required,
            'ok' => $ok,
            'value' => $value
        );
    }

    protected function _databaseFileStatus()
    {
        $databasePath = APP . 'Config' . DS . 'database.php';
        if (is_file($databasePath)) {
            $ok = is_writable($databasePath);
            return array(
                'ok' => $ok,
                'message' => $ok ? __('Writable') : __('Not writable')
            );
        }

        $dirOk = is_writable(dirname($databasePath));
        return array(
            'ok' => $dirOk,
            'message' => $dirOk ? __('Will be created (Config folder writable)') : __('Missing and Config folder not writable')
        );
    }

    protected function _allRequiredChecksPass($checks)
    {
        foreach ((array)$checks as $row) {
            if (!empty($row['required']) && empty($row['ok'])) {
                return false;
            }
        }
        return true;
    }

    protected function _testDatabaseConnection($params)
    {
        if (!class_exists('PDO') || !extension_loaded('pdo_mysql')) {
            return array('ok' => false, 'message' => __('PDO MySQL extension is not enabled on server.'));
        }

        $params['hostname'] = trim((string)$params['hostname']);
        $params['dbname'] = trim((string)$params['dbname']);
        $params['dbuser'] = trim((string)$params['dbuser']);
        $params['dbpassword'] = (string)$params['dbpassword'];

        $hostCandidates = array($params['hostname']);
        $hostInfo = $this->_parseMysqlHost($params['hostname']);
        $hostOnly = strtolower($hostInfo['host']);
        $portSuffix = $hostInfo['port'] !== '' ? ':' . $hostInfo['port'] : '';
        if ($hostOnly === 'localhost') {
            $hostCandidates[] = '127.0.0.1' . $portSuffix;
        } elseif ($hostOnly === '127.0.0.1') {
            $hostCandidates[] = 'localhost' . $portSuffix;
        }
        $hostCandidates = array_values(array_unique($hostCandidates));

        $errors = array();
        foreach ($hostCandidates as $hostCandidate) {
            $attempt = $this->_pdoConnectionAttempt($hostCandidate, $params['dbname'], $params['dbuser'], $params['dbpassword']);
            if (!empty($attempt['ok'])) {
                return array('ok' => true, 'message' => '', 'params' => array('hostname' => $hostCandidate));
            }
            $errors[] = $hostCandidate . ': ' . $attempt['message'];
        }

        foreach ($hostCandidates as $hostCandidate) {
            $credentialOnlyAttempt = $this->_pdoConnectionAttempt($hostCandidate, '', $params['dbuser'], $params['dbpassword']);
            if (!empty($credentialOnlyAttempt['ok'])) {
                return array(
                    'ok' => false,
                    'message' => sprintf(
                        __('Database user connected, but does not have permission on database "%s". In cPanel, open MySQL Databases and add user "%s" to this database with ALL PRIVILEGES.'),
                        $params['dbname'],
                        $params['dbuser']
                    )
                );
            }
        }

        $allErrors = !empty($errors) ? implode(' | ', $errors) : __('Unknown error');
        return array(
            'ok' => false,
            'message' => sprintf(
                __('MySQL rejected these credentials. Please reset the cPanel database user password, confirm the full prefixed username/database name, and make sure the user is added to the database with ALL PRIVILEGES. Tried host(s): %s. Errors: %s'),
                implode(', ', $hostCandidates),
                $allErrors
            )
        );
    }

    protected function _parseMysqlHost($hostRaw)
    {
        $hostRaw = trim((string)$hostRaw);
        $host = $hostRaw;
        $port = '';
        if (strpos($hostRaw, ':') !== false) {
            $parts = explode(':', $hostRaw, 2);
            if (count($parts) === 2 && ctype_digit((string)$parts[1])) {
                $host = $parts[0];
                $port = $parts[1];
            }
        }
        if ($host === '') {
            $host = 'localhost';
        }
        return array('host' => $host, 'port' => $port);
    }

    protected function _pdoConnectionAttempt($hostRaw, $databaseName, $dbUser, $dbPassword)
    {
        $hostInfo = $this->_parseMysqlHost($hostRaw);
        $dsn = 'mysql:host=' . $hostInfo['host'] . ';charset=utf8';
        if ($hostInfo['port'] !== '') {
            $dsn .= ';port=' . $hostInfo['port'];
        }
        $databaseName = trim((string)$databaseName);
        if ($databaseName !== '') {
            $dsn .= ';dbname=' . $databaseName;
        }

        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 8
        );
        if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';
        }

        try {
            $pdo = new PDO($dsn, (string)$dbUser, (string)$dbPassword, $options);
            $pdo = null;
            return array('ok' => true, 'message' => '');
        } catch (Exception $e) {
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }

    protected function _writeDatabaseConfig($params)
    {
        $databasePath = APP . 'Config' . DS . 'database.php';
        $content = $this->_databaseConfigTemplate($params);

        try {
            $file = new File($databasePath, true, 0644);
            if (!$file->writable()) {
                return array('ok' => false, 'message' => sprintf(__('File is not writable: %s'), $databasePath));
            }
            $file->write($content, 'w', true);
            $file->close();
            return array('ok' => true, 'message' => '');
        } catch (Exception $e) {
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }

    protected function _databaseConfigTemplate($params)
    {
        $host = var_export((string)$params['hostname'], true);
        $dbName = var_export((string)$params['dbname'], true);
        $dbUser = var_export((string)$params['dbuser'], true);
        $dbPassword = var_export((string)$params['dbpassword'], true);
        $dbType = preg_replace('/[^A-Za-z0-9_]/', '', (string)$params['dbtype']);

        return "<?php\n" .
            "class DATABASE_CONFIG {\n" .
            "    public \$default = array(\n" .
            "        'datasource' => 'Database/" . $dbType . "',\n" .
            "        'persistent' => false,\n" .
            "        'host' => " . $host . ",\n" .
            "        'login' => " . $dbUser . ",\n" .
            "        'password' => " . $dbPassword . ",\n" .
            "        'database' => " . $dbName . ",\n" .
            "        'prefix' => '',\n" .
            "        'encoding' => 'utf8',\n" .
            "    );\n" .
            "}\n" .
            "?>";
    }

    protected function _databaseConfigExists()
    {
        $databasePath = APP . 'Config' . DS . 'database.php';
        return is_file($databasePath) && filesize($databasePath) > 0;
    }

    protected function _validateDefaultDatasource()
    {
        if (!$this->_installerRuntimeReady()) {
            return array(
                'ok' => false,
                'message' => __('Wrong PHP version or missing database extensions. Please open /install and fix PHP version, PDO, PDO MySQL and MySQLi checks first.')
            );
        }
        try {
            $db = ConnectionManager::getDataSource('default');
            if (!$db->isConnected()) {
                return array('ok' => false, 'message' => __('Connection failed.'));
            }
            return array('ok' => true, 'message' => '');
        } catch (Exception $e) {
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }

    protected function _installerRuntimeReady()
    {
        $phpOk = version_compare(PHP_VERSION, $this->_requiredPhpMin, '>=') && version_compare(PHP_VERSION, $this->_requiredPhpMax, '<');
        return $phpOk && class_exists('PDO') && extension_loaded('pdo') && extension_loaded('pdo_mysql') && extension_loaded('mysqli');
    }

    protected function _defaultDomain()
    {
        $scheme = $this->request->is('ssl') ? 'https' : 'http';
        $host = (string)env('HTTP_HOST');
        $base = rtrim((string)$this->request->webroot, '/');
        if ($base === '/' || $base === '') {
            $base = '';
        }
        if ($host === '') {
            return 'http://localhost';
        }
        return $scheme . '://' . $host . $base;
    }

    protected function _normalizeDomainName($domainName)
    {
        $domainName = trim((string)$domainName);
        if ($domainName === '') {
            return '';
        }
        if (!preg_match('#^https?://#i', $domainName)) {
            $domainName = 'https://' . $domainName;
        }
        $domainName = rtrim($domainName, '/');
        if (!filter_var($domainName, FILTER_VALIDATE_URL)) {
            return '';
        }
        return $domainName;
    }

    protected function _timezoneOptions()
    {
        $tz = DateTimeZone::listIdentifiers();
        $out = array();
        foreach ((array)$tz as $name) {
            $out[(string)$name] = (string)$name;
        }
        return $out;
    }

    protected function _resolveSqlFile($withSampleData)
    {
        $candidates = array();
        if ($withSampleData) {
            $candidates = array(
                TMP . 'app_data.sql',
                ROOT . DS . 'app_data.sql',
                APP . 'Config' . DS . 'databaseupdated.sql',
                APP . 'Config' . DS . 'Schema' . DS . 'app_data.sql',
                TMP . 'wv59.sql',
                TMP . 'wv59 (1).sql',
                ROOT . DS . 'sabircon_whitevillas_portal.sql',
                ROOT . DS . 'whitevillas_portal 2.8.sql'
            );
        } else {
            $candidates = array(
                TMP . 'app.sql',
                ROOT . DS . 'app.sql',
                APP . 'Config' . DS . 'Schema' . DS . 'app.sql'
            );
        }

        foreach ($candidates as $path) {
            if (is_file($path) && filesize($path) > 0) {
                return $path;
            }
        }

        $searchDirs = array(TMP, ROOT . DS);
        foreach ($searchDirs as $dir) {
            foreach ((array)glob($dir . '*.sql') as $path) {
                $baseName = strtolower(basename($path));
                if (!$withSampleData && $baseName !== 'app.sql') {
                    continue;
                }
                if ($withSampleData && !preg_match('/(app_data|whitevillas|portal|^wv[0-9])/i', $baseName)) {
                    continue;
                }
                if (is_file($path) && filesize($path) > 0) {
                    return $path;
                }
            }
        }
        return '';
    }

    public function __executeSQLScript($db, $fileName)
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $handle = @fopen($fileName, 'r');
        if (!$handle) {
            throw new Exception(__('Unable to read SQL file.'));
        }

        // Explicitly disable foreign key checks, unique checks, and strict mode
        try {
            $db->query("SET FOREIGN_KEY_CHECKS = 0;");
            $db->query("SET UNIQUE_CHECKS = 0;");
            $db->query("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");
            $db->query("SET NAMES utf8mb4;");
        } catch (Exception $e) {}

        $statement = '';
        $statementCount = 0;

        while (($line = fgets($handle)) !== false) {
            $line = str_replace("\r", "\n", $line);
            $trimmed = trim($line);
            if ($trimmed === '' || strpos($trimmed, '--') === 0 || strpos($trimmed, '#') === 0) {
                continue;
            }
            if (strpos($trimmed, '/*') === 0) {
                if (preg_match('/SET\s+(?:FOREIGN_KEY_CHECKS|UNIQUE_CHECKS|SQL_MODE|NAMES|CHARACTER_SET)/i', $trimmed)) {
                    $cleanSet = preg_replace('#/\*!?[0-9]*\s*|\s*\*/;?#', '', $trimmed);
                    if ($cleanSet !== '') {
                        $cleanSet = rtrim($cleanSet, ';') . ';';
                        try {
                            $db->query($cleanSet);
                        } catch(Exception $ex) {}
                    }
                }
                continue;
            }
            $statement .= $line . "\n";
            if (substr(rtrim($line), -1) === ';') {
                $sql = trim($statement);
                if ($sql !== '') {
                    try {
                        $db->query($sql);
                    } catch (Exception $qe) {
                        // If it's a non-fatal DROP or duplicate index warning, continue; otherwise rethrow
                        $errMsg = $qe->getMessage();
                        if (stripos($errMsg, 'already exists') === false && stripos($errMsg, 'Unknown table') === false && stripos($errMsg, 'does not exist') === false) {
                            // Retry with foreign key checks off explicitly for this query
                            try {
                                $db->query("SET FOREIGN_KEY_CHECKS = 0;");
                                $db->query($sql);
                            } catch (Exception $retryEx) {
                                fclose($handle);
                                throw $retryEx;
                            }
                        }
                    }
                    $statementCount++;
                    if ($statementCount % 100 === 0 && function_exists('set_time_limit')) {
                        @set_time_limit(0);
                    }
                }
                $statement = '';
            }
        }
        fclose($handle);

        $sql = trim($statement);
        if ($sql !== '') {
            try {
                $db->query($sql);
            } catch (Exception $qe) {}
        }

        try {
            $db->query("SET FOREIGN_KEY_CHECKS = 1;");
            $db->query("SET UNIQUE_CHECKS = 1;");
        } catch (Exception $e) {}
    }

    /**
     * Ensure modules, permissions and published status are set post fresh install
     */
    protected function _postInstallModuleSync($db)
    {
        try {
            // 1. Ensure system_modules table exists and default modules are registered & active
            $db->query("CREATE TABLE IF NOT EXISTS `system_modules` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `slug` varchar(100) NOT NULL,
                `name` varchar(255) NOT NULL,
                `description` text DEFAULT NULL,
                `version` varchar(30) NOT NULL DEFAULT '1.0.0',
                `author` varchar(100) DEFAULT NULL,
                `author_url` varchar(255) DEFAULT NULL,
                `icon` varchar(50) NOT NULL DEFAULT 'fa fa-cube',
                `status` tinyint(1) NOT NULL DEFAULT 1,
                `is_system` tinyint(1) NOT NULL DEFAULT 0,
                `menu_config` text DEFAULT NULL,
                `config_data` text DEFAULT NULL,
                `installed_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Register or activate RealEstateTheme
            $db->query("INSERT INTO `system_modules` (`slug`, `name`, `description`, `version`, `author`, `icon`, `status`, `is_system`, `installed_at`, `updated_at`) 
                VALUES ('RealEstateTheme', 'Real Estate Pro Theme', 'Luxury frontend layout, customizer and landing page options', '1.0.0', 'Al-Riaz Tech', 'fa fa-paint-brush', 1, 0, NOW(), NOW())
                ON DUPLICATE KEY UPDATE `status` = 1, `updated_at` = NOW();");

            // Register or activate BillingManager
            $db->query("INSERT INTO `system_modules` (`slug`, `name`, `description`, `version`, `author`, `icon`, `status`, `is_system`, `installed_at`, `updated_at`) 
                VALUES ('BillingManager', 'Society Utility & Maintenance Billing System', 'Complete resident billing, meter readings, receipts and reports', '20.1.0', 'Al-Riaz Tech', 'fa fa-calculator', 1, 0, NOW(), NOW())
                ON DUPLICATE KEY UPDATE `status` = 1, `updated_at` = NOW();");

            // 2. Ensure Super Admin (ugroup_id = 1) has all page rights for every page in `pages`
            $db->query("INSERT IGNORE INTO `page_rights` (`page_id`, `ugroup_id`, `save_right`, `update_right`, `view_right`, `search_right`)
                SELECT p.`id`, 1, 1, 1, 1, 1 FROM `pages` p
                LEFT JOIN `page_rights` pr ON pr.`page_id` = p.`id` AND pr.`ugroup_id` = 1
                WHERE pr.`id` IS NULL;");

            // Update existing rights for Super Admin to 1
            $db->query("UPDATE `page_rights` SET `save_right` = 1, `update_right` = 1, `view_right` = 1, `search_right` = 1 WHERE `ugroup_id` = 1;");

            // 2b. Ensure Super Admin user exists in users table
            try {
                $adminCheck = $db->query("SELECT id FROM `users` WHERE `id` = 1 LIMIT 1;");
                if (empty($adminCheck)) {
                    $adminPass = password_hash('admin', PASSWORD_BCRYPT);
                    $db->query("INSERT INTO `users` (`id`, `ugroup_id`, `project_id`, `name`, `username`, `password`, `email`, `status`, `front_view`, `created`, `modified`) 
                        VALUES (1, 1, 0, 'Super Administrator', 'admin', '".$adminPass."', 'admin@whitevillas.com', 'Active', 'On', NOW(), NOW());");
                }
            } catch (Exception $ue) {}

            // 2c. Ensure default Facebook Integration settings exist
            try {
                $fbCheck = $db->query("SELECT id FROM `facebook_settings` WHERE `id` = 1 LIMIT 1;");
                if (empty($fbCheck)) {
                    $db->query("INSERT INTO `facebook_settings` (`id`, `webhook_verify_token`, `auto_fetch_enabled`, `auto_assign_enabled`, `created`, `modified`, `notify_agent_sms`, `notify_agent_email`, `notify_admin_email`, `notify_client_email`, `notify_client_sms`, `agent_sms_template`, `agent_email_subject`, `client_email_subject`, `client_sms_template`) 
                        VALUES (1, 'whitevillas_fb_verify_token_2026', 1, 1, NOW(), NOW(), 1, 1, 1, 1, 0, '🔔 WV CRM: New FB Lead Assigned! Name: {lead_name}, Phone: {lead_mobile}, Form: {form_name}, Project: {project_name}. Open CRM: {crm_link} - {agency_name}', '🔔 New Facebook Lead Assigned: {lead_name} ({project_name})', 'Thank you for your interest in {project_name} | {agency_name}', 'Dear {lead_name}, thank you for inquiring about {project_name} on Facebook! Your assigned property advisor is {agent_name} ({agent_mobile}). We will connect with you shortly. - {agency_name}');");
                }
            } catch (Exception $fbe) {}

            // 3. Ensure all billing and theme pages are published
            $db->query("UPDATE `pages` SET `published` = 'Yes' WHERE LOWER(`model_name`) IN ('billingmanager', 'realestatetheme') OR LOWER(`controller_name`) LIKE 'billing%' OR LOWER(`controller_name`) LIKE 'realestatetheme%';");

            // 4. Clear caches
            try {
                Cache::clear(false, '_cake_core_');
                Cache::clear(false, '_cake_model_');
                Cache::clear(false, 'default');
            } catch (Exception $ce) {}
        } catch (Exception $e) {}
    }
}
?>
