<?php
App::uses('Folder', 'Utility');
App::uses('Router', 'Routing');
App::uses('Cache', 'Cache');

class MediaGalleriesController extends AppController
{
    public $uses = array('MediaFile', 'MediaFolder');
    public $helpers = array('Html', 'Form', 'Session', 'Paginator', 'Js' => array('Jquery'), 'Number');
    public $components = array('Session', 'Paginator');

    public function beforeFilter()
    {
        parent::beforeFilter();
    }

    public function index()
    {
        throw new NotFoundException(__('Media gallery is not available publicly.'));
    }

    public function view($id = null)
    {
        throw new NotFoundException(__('Media gallery is not available publicly.'));
    }

    protected function _ensureMediaLibraryTables()
    {
        $Schema = $this->_mediaSchemaModel();
        $Schema->query("CREATE TABLE IF NOT EXISTS `media_folders` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_id` int(11) DEFAULT NULL,
            `project_id` int(11) NOT NULL DEFAULT 0,
            `name` varchar(150) NOT NULL,
            `slug` varchar(160) DEFAULT '',
            `system_key` varchar(120) DEFAULT '',
            `description` text,
            `visibility` varchar(20) NOT NULL DEFAULT 'backend',
            `status` varchar(20) NOT NULL DEFAULT 'Active',
            `sort_order` int(11) NOT NULL DEFAULT 0,
            `created_by` int(11) NOT NULL DEFAULT 0,
            `modified_by` int(11) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_parent_id` (`parent_id`),
            KEY `idx_project_id` (`project_id`),
            KEY `idx_system_key` (`system_key`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $Schema->query("CREATE TABLE IF NOT EXISTS `media_files` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `folder_id` int(11) NOT NULL DEFAULT 0,
            `project_id` int(11) NOT NULL DEFAULT 0,
            `source_type` varchar(80) NOT NULL DEFAULT 'library',
            `source_id` bigint(20) NOT NULL DEFAULT 0,
            `title` varchar(255) DEFAULT '',
            `alt_text` varchar(255) DEFAULT '',
            `file_name` varchar(255) NOT NULL,
            `original_name` varchar(255) DEFAULT '',
            `dir` varchar(255) DEFAULT '',
            `file_path` varchar(500) DEFAULT '',
            `url_path` varchar(500) DEFAULT '',
            `mime_type` varchar(150) DEFAULT '',
            `file_type` varchar(30) DEFAULT 'other',
            `extension` varchar(20) DEFAULT '',
            `file_size` bigint(20) NOT NULL DEFAULT 0,
            `visibility` varchar(20) NOT NULL DEFAULT 'backend',
            `status` varchar(20) NOT NULL DEFAULT 'Active',
            `created_by` int(11) NOT NULL DEFAULT 0,
            `modified_by` int(11) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_folder_id` (`folder_id`),
            KEY `idx_project_id` (`project_id`),
            KEY `idx_source` (`source_type`, `source_id`),
            KEY `idx_type_status` (`file_type`, `status`),
            KEY `idx_visibility` (`visibility`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $this->_refreshMediaModelSchema($Schema);
    }

    protected function _mediaSchemaModel()
    {
        $Schema = ClassRegistry::init('Configuration');
        if(!$Schema)
        {
            $Schema = $this->MediaFile;
        }
        return $Schema;
    }

    protected function _refreshMediaModelSchema($Schema)
    {
        try
        {
            $ds = $Schema->getDataSource();
            $dbName = !empty($ds->config['database']) ? $ds->config['database'] : '';
            if($dbName !== '')
            {
                Cache::delete('default_'.$dbName.'_list', 'models');
            }
            Cache::delete('default_list', 'models');
        }
        catch(Exception $e) {}
        try
        {
            $this->MediaFolder->schema(true);
            $this->MediaFile->schema(true);
        }
        catch(Exception $e) {}
    }

    protected function _syncPublicExistingMedia($force = false)
    {
        if(!$force && $this->Session->read('MediaGallery.frontSynced') === 'yes')
        {
            return;
        }
        $folders = array(
            'property_photos' => $this->_ensureMediaFolder('property_photos', __('Property Photos'), 'both', 2),
            'property_floorplans' => $this->_ensureMediaFolder('property_floorplans', __('Property Floor Plans'), 'both', 4),
            'website_media' => $this->_ensureMediaFolder('website_media', __('Website Media'), 'both', 8)
        );
        if($this->_tableExists('properties_photos'))
        {
            $rows = $this->MediaFile->query("SELECT `pp`.`id`, `pp`.`photo`, `pp`.`dir`, `pp`.`property_id`, `p`.`name` AS `property_name`, `p`.`project_id`
                FROM `properties_photos` `pp`
                LEFT JOIN `properties` `p` ON `p`.`id`=`pp`.`property_id`
                WHERE `pp`.`photo` IS NOT NULL AND TRIM(`pp`.`photo`)<>''");
            foreach($rows as $row)
            {
                $src = !empty($row['pp']) ? $row['pp'] : array();
                $prop = !empty($row['p']) ? $row['p'] : array();
                $this->_upsertSourceMedia(array(
                    'source_type' => 'property_photo',
                    'source_id' => $src['id'],
                    'folder_id' => $folders['property_photos'],
                    'project_id' => isset($prop['project_id']) ? (int)$prop['project_id'] : 0,
                    'title' => sprintf(__('Property Photo - %s'), !empty($prop['property_name']) ? $prop['property_name'] : '#'.$src['property_id']),
                    'file_name' => $src['photo'],
                    'dir' => $src['dir'],
                    'visibility' => 'both'
                ));
            }
        }
        if($this->_tableExists('properties_floorplans'))
        {
            $rows = $this->MediaFile->query("SELECT `pf`.`id`, `pf`.`photo`, `pf`.`dir`, `pf`.`property_id`, `p`.`name` AS `property_name`, `p`.`project_id`
                FROM `properties_floorplans` `pf`
                LEFT JOIN `properties` `p` ON `p`.`id`=`pf`.`property_id`
                WHERE `pf`.`photo` IS NOT NULL AND TRIM(`pf`.`photo`)<>''");
            foreach($rows as $row)
            {
                $src = !empty($row['pf']) ? $row['pf'] : array();
                $prop = !empty($row['p']) ? $row['p'] : array();
                $this->_upsertSourceMedia(array(
                    'source_type' => 'property_floorplan',
                    'source_id' => $src['id'],
                    'folder_id' => $folders['property_floorplans'],
                    'project_id' => isset($prop['project_id']) ? (int)$prop['project_id'] : 0,
                    'title' => sprintf(__('Floor Plan - %s'), !empty($prop['property_name']) ? $prop['property_name'] : '#'.$src['property_id']),
                    'file_name' => $src['photo'],
                    'dir' => $src['dir'],
                    'visibility' => 'both'
                ));
            }
        }
        $this->Session->write('MediaGallery.frontSynced', 'yes');
    }

    protected function _ensureMediaFolder($systemKey, $name, $visibility, $sortOrder)
    {
        $row = $this->MediaFolder->find('first', array('conditions' => array('MediaFolder.system_key' => $systemKey), 'recursive' => -1));
        if(!empty($row['MediaFolder']['id']))
        {
            return (int)$row['MediaFolder']['id'];
        }
        $this->MediaFolder->create();
        $this->MediaFolder->save(array('MediaFolder' => array(
            'name' => $name,
            'slug' => $this->_slug($name),
            'system_key' => $systemKey,
            'visibility' => $visibility,
            'status' => 'Active',
            'sort_order' => (int)$sortOrder,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s')
        )), array('validate' => false));
        return (int)$this->MediaFolder->id;
    }

    protected function _upsertSourceMedia($data)
    {
        $sourceType = isset($data['source_type']) ? trim((string)$data['source_type']) : '';
        $sourceId = isset($data['source_id']) ? (int)$data['source_id'] : 0;
        $fileName = isset($data['file_name']) ? basename(trim((string)$data['file_name'])) : '';
        $dir = isset($data['dir']) ? trim((string)$data['dir']) : '';
        if($sourceType === '' || $sourceId <= 0 || $fileName === '')
        {
            return false;
        }
        $path = $this->_physicalMediaPath($dir, $fileName);
        if(!is_file($path))
        {
            return false;
        }
        $existing = $this->MediaFile->find('first', array(
            'conditions' => array('MediaFile.source_type' => $sourceType, 'MediaFile.source_id' => $sourceId),
            'recursive' => -1
        ));
        if(!empty($existing['MediaFile']) && $existing['MediaFile']['status'] === 'Deleted')
        {
            return false;
        }
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $save = array(
            'folder_id' => isset($data['folder_id']) ? (int)$data['folder_id'] : 0,
            'project_id' => isset($data['project_id']) ? (int)$data['project_id'] : 0,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'title' => isset($data['title']) ? trim((string)$data['title']) : $fileName,
            'file_name' => $fileName,
            'original_name' => $fileName,
            'dir' => $this->_cleanMediaDir($dir),
            'file_path' => $this->_publicMediaPath($dir, $fileName),
            'url_path' => $this->_publicMediaPath($dir, $fileName),
            'mime_type' => '',
            'file_type' => $this->_fileTypeByExtension($extension),
            'extension' => $extension,
            'file_size' => (int)@filesize($path),
            'visibility' => isset($data['visibility']) ? trim((string)$data['visibility']) : 'both',
            'status' => 'Active',
            'modified' => date('Y-m-d H:i:s')
        );
        if(!empty($existing['MediaFile']['id']))
        {
            $save['id'] = (int)$existing['MediaFile']['id'];
            if(!empty($existing['MediaFile']['folder_id']))
            {
                $save['folder_id'] = (int)$existing['MediaFile']['folder_id'];
            }
            if(!empty($existing['MediaFile']['title']))
            {
                $save['title'] = $existing['MediaFile']['title'];
            }
            if(!empty($existing['MediaFile']['visibility']))
            {
                $save['visibility'] = $existing['MediaFile']['visibility'];
            }
        }
        else
        {
            $save['created'] = date('Y-m-d H:i:s');
        }
        $this->MediaFile->create();
        return $this->MediaFile->save(array('MediaFile' => $save), array('validate' => false));
    }

    protected function _decorateMediaRows($rows)
    {
        foreach((array)$rows as $key => $row)
        {
            if(empty($row['MediaFile']))
            {
                continue;
            }
            $file = $row['MediaFile'];
            $path = $this->_physicalMediaPath($file['dir'], $file['file_name']);
            $publicPath = $this->_publicMediaPath($file['dir'], $file['file_name']);
            $thumbPath = $this->_thumbnailPublicPath($file);
            $rows[$key]['MediaFile']['exists'] = is_file($path) ? 1 : 0;
            $rows[$key]['MediaFile']['url'] = Router::url('/img/'.$publicPath, true);
            $rows[$key]['MediaFile']['thumb_url'] = Router::url('/img/'.$thumbPath, true);
            $rows[$key]['MediaFile']['size_label'] = $this->_formatBytes(!empty($file['file_size']) ? (int)$file['file_size'] : (int)@filesize($path));
            $rows[$key]['MediaFile']['icon'] = $this->_fileIconClass($file);
        }
        return $rows;
    }

    protected function _frontendFolders()
    {
        return (array)$this->MediaFolder->find('all', array(
            'conditions' => array('MediaFolder.status' => 'Active', 'MediaFolder.visibility' => array('frontend', 'both')),
            'order' => array('MediaFolder.sort_order' => 'asc', 'MediaFolder.name' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _frontendFolderOptions()
    {
        return (array)$this->MediaFolder->find('list', array(
            'conditions' => array('MediaFolder.status' => 'Active', 'MediaFolder.visibility' => array('frontend', 'both')),
            'fields' => array('MediaFolder.id', 'MediaFolder.name'),
            'order' => array('MediaFolder.sort_order' => 'asc', 'MediaFolder.name' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _thumbnailPublicPath($file)
    {
        $dir = isset($file['dir']) ? trim((string)$file['dir']) : '';
        $fileName = isset($file['file_name']) ? basename((string)$file['file_name']) : '';
        if(isset($file['file_type']) && $file['file_type'] === 'image')
        {
            $thumbDir = $dir !== '' ? $dir.'_thumb' : '';
            $thumbPath = $this->_physicalMediaPath($thumbDir, $fileName);
            if(is_file($thumbPath))
            {
                return $this->_publicMediaPath($thumbDir, $fileName);
            }
            return $this->_publicMediaPath($dir, $fileName);
        }
        return 'media-document.png';
    }

    protected function _tableExists($table)
    {
        $table = preg_replace('/[^A-Za-z0-9_]/', '', (string)$table);
        if($table === '')
        {
            return false;
        }
        $rows = $this->MediaFile->query("SHOW TABLES LIKE '".$table."'");
        return !empty($rows);
    }

    protected function _physicalMediaPath($dir, $file)
    {
        $dir = $this->_cleanMediaDir($dir);
        $file = basename((string)$file);
        if($dir === '')
        {
            return WWW_ROOT.'img'.DS.$file;
        }
        return WWW_ROOT.'img'.DS.str_replace('/', DS, $dir).DS.$file;
    }

    protected function _publicMediaPath($dir, $file)
    {
        $dir = $this->_cleanMediaDir($dir);
        $file = basename((string)$file);
        return $dir === '' ? $file : trim($dir, '/').'/'.$file;
    }

    protected function _cleanMediaDir($dir)
    {
        $dir = str_replace('\\', '/', trim((string)$dir));
        $dir = preg_replace('#^/?img/#i', '', $dir);
        $dir = preg_replace('/[^A-Za-z0-9_\/\-]/', '', $dir);
        $dir = trim($dir, '/');
        return strpos($dir, '..') !== false ? '' : $dir;
    }

    protected function _fileTypeByExtension($extension)
    {
        $extension = strtolower((string)$extension);
        if(in_array($extension, array('jpg', 'jpeg', 'png', 'gif', 'webp'), true))
        {
            return 'image';
        }
        if(in_array($extension, array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'), true))
        {
            return 'document';
        }
        return 'other';
    }

    protected function _fileIconClass($file)
    {
        $extension = !empty($file['extension']) ? strtolower($file['extension']) : strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
        if(!empty($file['file_type']) && $file['file_type'] === 'image')
        {
            return 'fa fa-file-image-o';
        }
        if($extension === 'pdf')
        {
            return 'fa fa-file-pdf-o';
        }
        if(in_array($extension, array('doc', 'docx'), true))
        {
            return 'fa fa-file-word-o';
        }
        if(in_array($extension, array('xls', 'xlsx', 'csv'), true))
        {
            return 'fa fa-file-excel-o';
        }
        return 'fa fa-file-o';
    }

    protected function _formatBytes($bytes)
    {
        $bytes = (int)$bytes;
        if($bytes <= 0)
        {
            return '0 KB';
        }
        $units = array('B', 'KB', 'MB', 'GB');
        $power = (int)floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        return round($bytes / pow(1024, $power), 1).' '.$units[$power];
    }

    protected function _slug($value)
    {
        $value = strtolower(trim((string)$value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim($value, '-');
        return $value !== '' ? $value : 'folder';
    }
}
?>
