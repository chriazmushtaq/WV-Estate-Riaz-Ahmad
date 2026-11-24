<?php
App::uses('AdminAppController', 'Admin.Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class ModulesController extends AdminAppController {
    public $helpers = array('Html', 'Form', 'Session', 'Paginator');
    public $components = array('Session', 'search-master.Prg', 'Paginator');
    public $uses = array('SystemModule', 'Page', 'PageRight', 'Ugroup');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->set('allowViewAction', true);
        $this->set('allowAddAction', true);
        $this->set('allowEditAction', true);
        $this->set('allowDeleteAction', true);
        $this->set('isSuperAdminSession', true);
        $sessionUser = (array)$this->Session->read('User');
        if (!$this->_isSuperAdminSession($sessionUser) && !$this->_isCeoRoleContext($sessionUser)) {
            $this->Session->setFlash(__('Only Super Admin can access Module Management.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('controller' => 'Dashboards', 'action' => 'index'));
            return;
        }
    }

    public function admin_index() {
        $this->index();
        if ($this->hasRendered || (is_object($this->response) && $this->response->statusCode() == 302)) {
            return;
        }
        return $this->render('index');
    }

    public function admin_builder() {
        $this->builder();
        if ($this->hasRendered || (is_object($this->response) && $this->response->statusCode() == 302)) {
            return;
        }
        return $this->render('builder');
    }

    public function admin_install() {
        $this->install();
    }

    public function admin_toggle_status($id = null) {
        $this->toggle_status($id);
    }

    public function admin_export($id = null) {
        $this->export($id);
    }

    public function admin_uninstall($id = null) {
        $this->uninstall($id);
    }

    /**
     * List all installed modules
     */
    public function index() {
        $this->set('title_for_layout', __('Module Management & Installer'));

        $modules = $this->SystemModule->find('all', array(
            'order' => array('SystemModule.is_system' => 'DESC', 'SystemModule.name' => 'ASC'),
            'recursive' => -1
        ));

        // Scan Plugin directory for unmanaged / discoverable plugins
        $discoveredPlugins = array();
        $folders = array();
        $pluginDirs = (array)glob(APP . 'Plugin' . DS . '*', GLOB_ONLYDIR);
        foreach ($pluginDirs as $pDir) {
            $folders[] = basename($pDir);
        }
        
        $knownSlugs = array();
        foreach ($modules as $m) {
            $knownSlugs[strtolower($m['SystemModule']['slug'])] = true;
        }

        // Core system plugins to skip from unregistered list
        $ignorePlugins = array('admin', 'debugkit', 'upload', 'highcharts', 'utils', 'exportcsv', 'paypal', 'tinymceelfinder', 'menubuilder', 'search-master');

        foreach ($folders as $f) {
            $fLower = strtolower($f);
            if (in_array($fLower, $ignorePlugins, true)) {
                continue;
            }
            if (!isset($knownSlugs[$fLower])) {
                $manifestPath = APP . 'Plugin' . DS . $f . DS . 'module.json';
                $manifest = array();
                if (file_exists($manifestPath)) {
                    $json = file_get_contents($manifestPath);
                    $manifest = json_decode($json, true);
                }
                $discoveredPlugins[] = array(
                    'folder' => $f,
                    'has_manifest' => !empty($manifest),
                    'manifest' => $manifest
                );
            }
        }

        $totalModules = count($modules);
        $activeModules = 0;
        $inactiveModules = 0;
        foreach ($modules as $m) {
            if (!empty($m['SystemModule']['status'])) {
                $activeModules++;
            } else {
                $inactiveModules++;
            }
        }

        $this->set(compact('modules', 'discoveredPlugins', 'totalModules', 'activeModules', 'inactiveModules'));
    }

    /**
     * Upload and Install Module from ZIP
     */
    public function install() {
        $this->autoRender = false;
        if (!$this->request->is('post')) {
            $this->redirect(array('action' => 'index'));
            return;
        }

        if (empty($_FILES['module_zip']['name']) || $_FILES['module_zip']['error'] !== UPLOAD_ERR_OK) {
            $this->Session->setFlash(__('Please select a valid module ZIP file.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'index'));
            return;
        }

        $file = $_FILES['module_zip'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            $this->Session->setFlash(__('Invalid archive format. Only .zip files are supported.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'index'));
            return;
        }

        if (!class_exists('ZipArchive')) {
            $this->Session->setFlash(__('PHP ZipArchive extension is not enabled on this server.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'index'));
            return;
        }

        $zip = new ZipArchive();
        if ($zip->open($file['tmp_name']) !== true) {
            $this->Session->setFlash(__('Failed to open uploaded ZIP archive.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'index'));
            return;
        }

        // Locate module.json inside zip
        $manifestContent = null;
        $rootDirInZip = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (basename($entryName) === 'module.json') {
                $manifestContent = $zip->getFromIndex($i);
                $parts = explode('/', trim($entryName, '/'));
                if (count($parts) > 1) {
                    $rootDirInZip = $parts[0];
                }
                break;
            }
        }

        if (empty($manifestContent)) {
            $zip->close();
            $this->Session->setFlash(__('Invalid module package: module.json manifest was not found in the archive.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'index'));
            return;
        }

        $manifest = json_decode($manifestContent, true);
        if (empty($manifest['slug']) || empty($manifest['name'])) {
            $zip->close();
            $this->Session->setFlash(__('Invalid module.json manifest: slug and name are required.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'index'));
            return;
        }

        $slug = Inflector::camelize(trim($manifest['slug']));
        $targetDir = APP . 'Plugin' . DS . $slug;

        // Extract to temporary directory first
        $tempExtractDir = TMP . 'module_install_' . time() . '_' . mt_rand(1000, 9999);
        mkdir($tempExtractDir, 0777, true);
        $zip->extractTo($tempExtractDir);
        $zip->close();

        // Determine source folder to move
        $sourceDir = $tempExtractDir;
        if (!empty($rootDirInZip) && is_dir($tempExtractDir . DS . $rootDirInZip)) {
            $sourceDir = $tempExtractDir . DS . $rootDirInZip;
        }

        // Copy files to target plugin directory
        $folder = new Folder();
        if (is_dir($targetDir)) {
            // Updating existing module
            $folder->delete($targetDir);
        }
        $folder->copy(array('from' => $sourceDir, 'to' => $targetDir, 'mode' => 0777));
        $folder->delete($tempExtractDir);

        // Run SQL installation schema if present
        $this->_runModuleSql($targetDir, 'schema.sql');
        $this->_runModuleSql($targetDir, 'install.sql');

        // Save / Update in system_modules
        $existing = $this->SystemModule->find('first', array(
            'conditions' => array('SystemModule.slug' => $slug),
            'recursive' => -1
        ));

        $modData = array(
            'slug' => $slug,
            'name' => trim($manifest['name']),
            'description' => !empty($manifest['description']) ? trim($manifest['description']) : '',
            'version' => !empty($manifest['version']) ? trim($manifest['version']) : '1.0.0',
            'author' => !empty($manifest['author']) ? trim($manifest['author']) : '',
            'author_url' => !empty($manifest['author_url']) ? trim($manifest['author_url']) : '',
            'icon' => !empty($manifest['icon']) ? trim($manifest['icon']) : 'fa fa-cube',
            'status' => 1,
            'is_system' => 0,
            'menu_config' => !empty($manifest['admin_menu']) ? json_encode($manifest['admin_menu']) : null,
            'config_data' => !empty($manifest['config']) ? json_encode($manifest['config']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        );

        if (!empty($existing['SystemModule']['id'])) {
            $modData['id'] = (int)$existing['SystemModule']['id'];
        } else {
            $modData['installed_at'] = date('Y-m-d H:i:s');
            $this->SystemModule->create();
        }
        $this->SystemModule->save($modData);

        // Register Admin Menu in pages table
        if (!empty($manifest['admin_menu']) && is_array($manifest['admin_menu'])) {
            $this->_registerModuleMenus($slug, $manifest);
        }

        $this->_clearModuleCaches();

        $this->Session->setFlash(__('Module "%s" (v%s) installed and enabled successfully!', h($manifest['name']), h($modData['version'])), 'flash', array('alert' => 'success'));
        $this->redirect(array('action' => 'index'));
    }

    /**
     * Module Builder / Scaffolder (Generate a new module directly from backend)
     */
    public function builder() {
        $this->set('title_for_layout', __('Module Builder & Generator'));

        if ($this->request->is('post')) {
            $data = $this->request->data['ModuleBuilder'];
            $name = trim(!empty($data['name']) ? $data['name'] : '');
            $rawSlug = trim(!empty($data['slug']) ? $data['slug'] : '');
            if (empty($rawSlug)) {
                $rawSlug = Inflector::slug(strtolower($name), '_');
            }
            $slug = Inflector::camelize($rawSlug);
            $version = !empty($data['version']) ? trim($data['version']) : '1.0.0';
            $author = !empty($data['author']) ? trim($data['author']) : 'White Villas Admin';
            $description = !empty($data['description']) ? trim($data['description']) : 'Custom extension module for White Villas portal.';
            $icon = !empty($data['icon']) ? trim($data['icon']) : 'fa fa-cube';
            $menuTitle = !empty($data['menu_title']) ? trim($data['menu_title']) : $name;

            if (empty($name) || empty($slug)) {
                $this->Session->setFlash(__('Module Name and Slug are required.'), 'flash', array('alert' => 'danger'));
                return;
            }

            $targetDir = APP . 'Plugin' . DS . $slug;
            if (is_dir($targetDir)) {
                $this->Session->setFlash(__('A module with slug "%s" already exists at app/Plugin/%s.', h($slug), h($slug)), 'flash', array('alert' => 'danger'));
                return;
            }

            // Create Directory Structure
            $dirs = array(
                $targetDir,
                $targetDir . DS . 'Config',
                $targetDir . DS . 'Controller',
                $targetDir . DS . 'Model',
                $targetDir . DS . 'View',
                $targetDir . DS . 'View' . DS . $slug,
                $targetDir . DS . 'webroot'
            );
            foreach ($dirs as $d) {
                if (!is_dir($d)) {
                    mkdir($d, 0777, true);
                }
            }

            $controllerName = $slug . 'Controller';
            $appControllerName = $slug . 'AppController';
            $appModelName = $slug . 'AppModel';
            $modelName = Inflector::classify($slug);
            $tableName = Inflector::tableize($slug);

            // 1. module.json manifest
            $manifest = array(
                'name' => $name,
                'slug' => $slug,
                'version' => $version,
                'description' => $description,
                'author' => $author,
                'author_url' => 'https://whitevillas.com.pk',
                'icon' => $icon,
                'admin_menu' => array(
                    array(
                        'page_name' => $menuTitle,
                        'controller_name' => $slug,
                        'action_name' => 'index',
                        'icon' => $icon,
                        'is_root' => true
                    ),
                    array(
                        'page_name' => 'Overview',
                        'controller_name' => $slug,
                        'action_name' => 'index',
                        'icon' => 'fa fa-dashboard'
                    ),
                    array(
                        'page_name' => 'Settings',
                        'controller_name' => $slug,
                        'action_name' => 'settings',
                        'icon' => 'fa fa-cogs'
                    )
                )
            );
            file_put_contents($targetDir . DS . 'module.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            // 2. Config/routes.php
            $routesContent = "<?php\nRouter::connect('/admin/" . strtolower($slug) . "', array('plugin' => '" . $slug . "', 'controller' => '" . $slug . "', 'action' => 'index', 'admin' => true));\n";
            file_put_contents($targetDir . DS . 'Config' . DS . 'routes.php', $routesContent);

            // 3. Config/schema.sql
            $schemaSql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (\n" .
                "  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,\n" .
                "  `title` varchar(255) NOT NULL,\n" .
                "  `status` tinyint(1) NOT NULL DEFAULT 1,\n" .
                "  `notes` text DEFAULT NULL,\n" .
                "  `created` datetime DEFAULT NULL,\n" .
                "  `updated` datetime DEFAULT NULL,\n" .
                "  PRIMARY KEY (`id`)\n" .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8;\n";
            file_put_contents($targetDir . DS . 'Config' . DS . 'schema.sql', $schemaSql);

            // 4. Config/uninstall.sql
            $uninstallSql = "DROP TABLE IF EXISTS `{$tableName}`;\n";
            file_put_contents($targetDir . DS . 'Config' . DS . 'uninstall.sql', $uninstallSql);

            // 5. Controller/AppController
            $appCtrlContent = "<?php\nApp::uses('AdminAppController', 'Admin.Controller');\n\nclass {$appControllerName} extends AdminAppController {\n}\n";
            file_put_contents($targetDir . DS . 'Controller' . DS . $appControllerName . '.php', $appCtrlContent);

            // 6. Controller/MainController
            $mainCtrlContent = "<?php\nApp::uses('{$appControllerName}', '{$slug}.Controller');\n\nclass {$controllerName} extends {$appControllerName} {\n" .
                "    public \$helpers = array('Html', 'Form', 'Session', 'Paginator');\n" .
                "    public \$uses = array('{$slug}.{$modelName}');\n\n" .
                "    public function beforeFilter() {\n" .
                "        parent::beforeFilter();\n" .
                "    }\n\n" .
                "    public function admin_index() {\n" .
                "        \$this->index();\n" .
                "        \$this->render('index');\n" .
                "    }\n\n" .
                "    public function index() {\n" .
                "        \$this->set('title_for_layout', __('" . addslashes($name) . " Dashboard'));\n" .
                "        \$records = array();\n" .
                "        try {\n" .
                "            \$records = \$this->{$modelName}->find('all', array('limit' => 20, 'order' => array('{$modelName}.id' => 'DESC')));\n" .
                "        } catch (Exception \$e) {}\n" .
                "        \$this->set(compact('records'));\n" .
                "    }\n\n" .
                "    public function settings() {\n" .
                "        \$this->set('title_for_layout', __('" . addslashes($name) . " Settings'));\n" .
                "        if (\$this->request->is(array('post', 'put'))) {\n" .
                "            \$this->Session->setFlash(__('Settings saved successfully!'), 'flash', array('alert' => 'success'));\n" .
                "        }\n" .
                "    }\n" .
                "}\n";
            file_put_contents($targetDir . DS . 'Controller' . DS . $controllerName . '.php', $mainCtrlContent);

            // 7. Model/AppModel
            $appModelContent = "<?php\nApp::uses('AdminAppModel', 'Admin.Model');\n\nclass {$appModelName} extends AdminAppModel {\n}\n";
            file_put_contents($targetDir . DS . 'Model' . DS . $appModelName . '.php', $appModelContent);

            // 8. Model/MainModel
            $mainModelContent = "<?php\nApp::uses('{$appModelName}', '{$slug}.Model');\n\nclass {$modelName} extends {$appModelName} {\n" .
                "    public \$name = '{$modelName}';\n" .
                "    public \$useTable = '{$tableName}';\n" .
                "}\n";
            file_put_contents($targetDir . DS . 'Model' . DS . $modelName . '.php', $mainModelContent);

            // 9. View/index.ctp
            $viewIndexContent = "<div class=\"page-title-box mb-4\">\n" .
                "    <div class=\"d-flex align-items-center justify-content-between flex-wrap gap-2\">\n" .
                "        <div>\n" .
                "            <h3 class=\"page-title mb-1\"><i class=\"{$icon} text-primary me-2\"></i> " . htmlspecialchars($name) . "</h3>\n" .
                "            <p class=\"text-muted mb-0\">" . htmlspecialchars($description) . "</p>\n" .
                "        </div>\n" .
                "        <div>\n" .
                "            <span class=\"badge badge-success px-3 py-2\"><i class=\"fa fa-check-circle\"></i> Active Module v" . htmlspecialchars($version) . "</span>\n" .
                "        </div>\n" .
                "    </div>\n" .
                "</div>\n\n" .
                "<div class=\"card shadow-sm border-0 rounded-3 mb-4\">\n" .
                "    <div class=\"card-body p-4\">\n" .
                "        <h5 class=\"card-title mb-3\"><i class=\"fa fa-info-circle text-info\"></i> Module Workspace Ready</h5>\n" .
                "        <p class=\"text-muted\">This module was scaffolded cleanly under <code>app/Plugin/{$slug}</code>. You can customize the controller, models, views, and database tables without affecting any other part of the system.</p>\n" .
                "        <div class=\"alert alert-light border p-3 mt-3\">\n" .
                "            <strong>Module Info:</strong>\n" .
                "            <ul class=\"mb-0 mt-2\">\n" .
                "                <li><strong>Controller:</strong> <code>app/Plugin/{$slug}/Controller/{$controllerName}.php</code></li>\n" .
                "                <li><strong>Model:</strong> <code>app/Plugin/{$slug}/Model/{$modelName}.php</code></li>\n" .
                "                <li><strong>Database Table:</strong> <code>{$tableName}</code></li>\n" .
                "            </ul>\n" .
                "        </div>\n" .
                "    </div>\n" .
                "</div>\n";
            file_put_contents($targetDir . DS . 'View' . DS . $slug . DS . 'index.ctp', $viewIndexContent);

            // 10. View/settings.ctp
            $viewSettingsContent = "<div class=\"page-title-box mb-4\">\n" .
                "    <h3 class=\"page-title mb-1\"><i class=\"fa fa-cogs text-primary me-2\"></i> " . htmlspecialchars($name) . " Settings</h3>\n" .
                "</div>\n" .
                "<div class=\"card shadow-sm border-0 rounded-3\">\n" .
                "    <div class=\"card-body p-4\">\n" .
                "        <p class=\"text-muted\">Configure settings and options for " . htmlspecialchars($name) . ".</p>\n" .
                "        <form method=\"post\">\n" .
                "            <div class=\"form-group mb-3\">\n" .
                "                <label class=\"form-label font-weight-bold\">Module Status</label>\n" .
                "                <select class=\"form-control\" name=\"status\"><option value=\"1\">Enabled</option><option value=\"0\">Disabled</option></select>\n" .
                "            </div>\n" .
                "            <button type=\"submit\" class=\"btn btn-primary px-4\"><i class=\"fa fa-save\"></i> Save Settings</button>\n" .
                "        </form>\n" .
                "    </div>\n" .
                "</div>\n";
            file_put_contents($targetDir . DS . 'View' . DS . $slug . DS . 'settings.ctp', $viewSettingsContent);

            // Execute SQL schema
            $this->_runModuleSql($targetDir, 'schema.sql');

            // Register in system_modules table
            $this->SystemModule->create();
            $this->SystemModule->save(array(
                'slug' => $slug,
                'name' => $name,
                'description' => $description,
                'version' => $version,
                'author' => $author,
                'icon' => $icon,
                'status' => 1,
                'is_system' => 0,
                'menu_config' => json_encode($manifest['admin_menu']),
                'installed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ));

            // Register Admin Menu in pages table
            $this->_registerModuleMenus($slug, $manifest);

            $this->_clearModuleCaches();

            $this->Session->setFlash(__('Module "%s" scaffolded and registered successfully in app/Plugin/%s!', h($name), h($slug)), 'flash', array('alert' => 'success'));
            $this->redirect(array('action' => 'index'));
        }
    }

    /**
     * Toggle Active / Inactive Status
     */
    public function toggle_status($id = null) {
        $this->autoRender = false;
        $id = (int)$id;
        $module = $this->SystemModule->find('first', array('conditions' => array('SystemModule.id' => $id), 'recursive' => -1));
        if (empty($module)) {
            $this->Session->setFlash(__('Module not found.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'index'));
            return;
        }

        $newStatus = !empty($module['SystemModule']['status']) ? 0 : 1;
        $this->SystemModule->id = $id;
        $this->SystemModule->saveField('status', $newStatus);
        $this->SystemModule->saveField('updated_at', date('Y-m-d H:i:s'));

        $published = ($newStatus === 1) ? 'Yes' : 'No';
        $slug = $module['SystemModule']['slug'];

        // Find root pages for this module
        $rootPages = (array)$this->Page->find('list', array(
            'fields' => array('Page.id', 'Page.id'),
            'conditions' => array('OR' => array(
                'LOWER(Page.model_name)' => strtolower($slug),
                'LOWER(Page.controller_name)' => strtolower($slug),
                'LOWER(Page.controller_name)' => strtolower($slug . 's'),
                'LOWER(Page.page_name) LIKE' => '%' . strtolower($module['SystemModule']['name']) . '%'
            )),
            'recursive' => -1
        ));

        // Toggle visibility in pages table for root and all child subpages
        $pageConds = array('OR' => array(
            'LOWER(Page.model_name)' => strtolower($slug),
            'LOWER(Page.controller_name)' => strtolower($slug),
            'LOWER(Page.controller_name)' => strtolower($slug . 's'),
            'LOWER(Page.controller_name) LIKE' => strtolower($slug) . '%',
            'LOWER(Page.sel_name) LIKE' => '%' . strtolower($slug) . '%'
        ));
        if (!empty($rootPages)) {
            $pageConds['OR']['Page.parent_id'] = array_values($rootPages);
            $pageConds['OR']['Page.id'] = array_values($rootPages);
        }

        $this->Page->updateAll(
            array('Page.published' => "'{$published}'"),
            $pageConds
        );

        $this->_clearModuleCaches();
        for ($ug = 1; $ug <= 10; $ug++) {
            $this->_bumpMenuPermissionVersion($ug);
        }

        $stateText = ($newStatus === 1) ? 'enabled' : 'disabled';
        $this->Session->setFlash(__('Module "%s" has been %s.', h($module['SystemModule']['name']), $stateText), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * Export Module to ZIP archive
     */
    public function export($id = null) {
        $this->autoRender = false;
        $id = (int)$id;
        $module = $this->SystemModule->find('first', array('conditions' => array('SystemModule.id' => $id), 'recursive' => -1));
        if (empty($module)) {
            $this->Session->setFlash(__('Module not found.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'index'));
            return;
        }

        $slug = $module['SystemModule']['slug'];
        $pluginPath = APP . 'Plugin' . DS . $slug;
        if (!is_dir($pluginPath)) {
            $this->Session->setFlash(__('Module directory app/Plugin/%s does not exist on disk.', h($slug)), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'index'));
            return;
        }

        if (!class_exists('ZipArchive')) {
            $this->Session->setFlash(__('PHP ZipArchive extension is required for export.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'index'));
            return;
        }

        $zipFileName = 'module_' . strtolower($slug) . '_v' . str_replace('.', '_', $module['SystemModule']['version']) . '.zip';
        $zipFilePath = TMP . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->Session->setFlash(__('Could not create export archive.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'index'));
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($pluginPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($pluginPath) + 1);
            $relativePath = str_replace('\\', '/', $relativePath);
            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();

        // Stream zip download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
        header('Content-Length: ' . filesize($zipFilePath));
        header('Pragma: no-cache');
        readfile($zipFilePath);
        @unlink($zipFilePath);
        exit;
    }

    /**
     * Safely Uninstall Module
     */
    public function uninstall($id = null) {
        $this->autoRender = false;
        if (!$this->request->is('post')) {
            $this->redirect(array('action' => 'index'));
            return;
        }

        $id = (int)$id;
        $module = $this->SystemModule->find('first', array('conditions' => array('SystemModule.id' => $id), 'recursive' => -1));
        if (empty($module)) {
            $this->Session->setFlash(__('Module not found.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'index'));
            return;
        }

        if (!empty($module['SystemModule']['is_system'])) {
            $this->Session->setFlash(__('Core system modules cannot be uninstalled.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('action' => 'index'));
            return;
        }

        $slug = $module['SystemModule']['slug'];
        $dropTables = !empty($this->request->data['drop_tables']);
        $pluginPath = APP . 'Plugin' . DS . $slug;

        // Run uninstall.sql if requested
        if ($dropTables && is_dir($pluginPath)) {
            $this->_runModuleSql($pluginPath, 'uninstall.sql');
        }

        // Delete menu and permissions records
        $pages = $this->Page->find('all', array(
            'conditions' => array('OR' => array(
                'LOWER(Page.controller_name)' => strtolower($slug),
                'LOWER(Page.controller_name)' => strtolower($slug . 's')
            )),
            'recursive' => -1
        ));

        if (!empty($pages)) {
            $pageIds = Hash::extract($pages, '{n}.Page.id');
            if (!empty($pageIds)) {
                $this->PageRight->deleteAll(array('PageRight.page_id' => $pageIds), false);
                $this->Page->deleteAll(array('Page.id' => $pageIds), false);
            }
        }

        // Delete folder from disk
        if (is_dir($pluginPath)) {
            $folder = new Folder($pluginPath);
            $folder->delete();
        }

        // Remove from system_modules table
        $this->SystemModule->delete($id, false);

        $this->_clearModuleCaches();

        $this->Session->setFlash(__('Module "%s" uninstalled successfully.', h($module['SystemModule']['name'])), 'flash', array('alert' => 'success'));
        $this->redirect(array('action' => 'index'));
    }

    /**
     * Helper to run SQL script file
     */
    protected function _runModuleSql($pluginDir, $fileName) {
        $filePath = $pluginDir . DS . 'Config' . DS . $fileName;
        if (!file_exists($filePath)) {
            return;
        }
        $sql = file_get_contents($filePath);
        if (empty(trim($sql))) {
            return;
        }
        try {
            $db = ConnectionManager::getDataSource('default');
            $queries = explode(';', $sql);
            foreach ($queries as $q) {
                $q = trim($q);
                if (!empty($q)) {
                    $db->rawQuery($q);
                }
            }
        } catch (Exception $e) {
            CakeLog::write('error', "Module SQL execution error ({$fileName}): " . $e->getMessage());
        }
    }

    /**
     * Helper to register module menus in pages & page_rights tables
     */
    protected function _registerModuleMenus($slug, $manifest) {
        if (empty($manifest['admin_menu']) || !is_array($manifest['admin_menu'])) {
            return;
        }

        $rootId = 0;
        $order = 1;

        // First look for root menu item
        foreach ($manifest['admin_menu'] as $menuItem) {
            if (!empty($menuItem['is_root'])) {
                $rootTitle = !empty($menuItem['page_name']) ? trim($menuItem['page_name']) : $manifest['name'];
                $rootCtrl = !empty($menuItem['controller_name']) ? trim($menuItem['controller_name']) : $slug;
                $rootAction = !empty($menuItem['action_name']) ? trim($menuItem['action_name']) : 'index';
                $rootIcon = !empty($menuItem['icon']) ? trim($menuItem['icon']) : (!empty($manifest['icon']) ? $manifest['icon'] : 'fa fa-cube');

                $existingRoot = $this->Page->find('first', array(
                    'conditions' => array(
                        'LOWER(Page.controller_name)' => strtolower($rootCtrl),
                        'LOWER(Page.action_name)' => strtolower($rootAction),
                        'Page.parent_id' => 0
                    ),
                    'recursive' => -1
                ));

                $rootData = array(
                    'model_name' => $slug,
                    'page_name' => $rootTitle,
                    'controller_name' => $rootCtrl,
                    'action_name' => $rootAction,
                    'icon' => $rootIcon,
                    'parent_id' => 0,
                    'ordering' => 95,
                    'published' => 'Yes',
                    'sel_name' => strtolower($slug)
                );

                if (!empty($existingRoot['Page']['id'])) {
                    $rootData['id'] = (int)$existingRoot['Page']['id'];
                }
                $this->Page->create();
                $this->Page->save(array('Page' => $rootData), array('validate' => false));
                $rootId = !empty($rootData['id']) ? (int)$rootData['id'] : (int)$this->Page->id;
                break;
            }
        }

        // If no explicit root was found, create one
        if ($rootId <= 0) {
            $rootData = array(
                'model_name' => $slug,
                'page_name' => $manifest['name'],
                'controller_name' => $slug,
                'action_name' => 'index',
                'icon' => !empty($manifest['icon']) ? $manifest['icon'] : 'fa fa-cube',
                'parent_id' => 0,
                'ordering' => 95,
                'published' => 'Yes',
                'sel_name' => strtolower($slug)
            );
            $this->Page->create();
            $this->Page->save(array('Page' => $rootData), array('validate' => false));
            $rootId = (int)$this->Page->id;
        }

        // Grant root right to Super Admin (ugroup_id=1)
        if ($rootId > 0) {
            $this->_grantPageRightToAdmin($rootId);
        }

        // Register submenus
        foreach ($manifest['admin_menu'] as $menuItem) {
            $subTitle = !empty($menuItem['page_name']) ? trim($menuItem['page_name']) : 'Dashboard';
            $subCtrl = !empty($menuItem['controller_name']) ? trim($menuItem['controller_name']) : $slug;
            $subAction = !empty($menuItem['action_name']) ? trim($menuItem['action_name']) : 'index';
            $subIcon = !empty($menuItem['icon']) ? trim($menuItem['icon']) : 'fa fa-angle-right';

            $existingSub = $this->Page->find('first', array(
                'conditions' => array(
                    'LOWER(Page.controller_name)' => strtolower($subCtrl),
                    'LOWER(Page.action_name)' => strtolower($subAction),
                    'Page.parent_id' => $rootId
                ),
                'recursive' => -1
            ));

            $subData = array(
                'model_name' => $slug,
                'page_name' => $subTitle,
                'controller_name' => $subCtrl,
                'action_name' => $subAction,
                'icon' => $subIcon,
                'parent_id' => $rootId,
                'ordering' => $order++,
                'published' => 'Yes',
                'sel_name' => strtolower($slug)
            );

            if (!empty($existingSub['Page']['id'])) {
                $subData['id'] = (int)$existingSub['Page']['id'];
            }
            $this->Page->create();
            $this->Page->save(array('Page' => $subData), array('validate' => false));
            $subId = !empty($subData['id']) ? (int)$subData['id'] : (int)$this->Page->id;
            if ($subId > 0) {
                $this->_grantPageRightToAdmin($subId);
            }
        }
    }

    /**
     * Grant full rights to Super Admin (ugroup_id = 1)
     */
    protected function _grantPageRightToAdmin($pageId) {
        $existing = $this->PageRight->find('first', array(
            'conditions' => array('PageRight.page_id' => $pageId, 'PageRight.ugroup_id' => 1),
            'recursive' => -1
        ));
        $rightData = array(
            'page_id' => $pageId,
            'ugroup_id' => 1,
            'save_right' => 1,
            'update_right' => 1,
            'view_right' => 1,
            'search_right' => 1
        );
        if (!empty($existing['PageRight']['id'])) {
            $rightData['id'] = (int)$existing['PageRight']['id'];
        }
        $this->PageRight->create();
        $this->PageRight->save(array('PageRight' => $rightData), array('validate' => false));
    }

    /**
     * Clear CakePHP core and menu session caches
     */
    protected function _clearModuleCaches() {
        Cache::clear(false, '_cake_core_');
        Cache::clear(false, '_cake_model_');
        Cache::clear(false, 'default');
        $this->_bumpMenuPermissionVersion(1);
    }
}
