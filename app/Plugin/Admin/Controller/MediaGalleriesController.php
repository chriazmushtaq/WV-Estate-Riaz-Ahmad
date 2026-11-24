<?php
App::uses('Folder', 'Utility');
App::uses('Router', 'Routing');
App::uses('CakeNumber', 'Utility');
App::uses('Cache', 'Cache');

class MediaGalleriesController extends AdminAppController
{
    public $uses = array('MediaFile', 'MediaFolder');
    public $helpers = array('Html', 'Form', 'Session', 'Paginator', 'Js' => array('Jquery'), 'Number');
    public $components = array('Session', 'Paginator', 'CustomFunction');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_ensureMediaLibraryTables();
        $this->_ensureMediaLibraryDirectories();
    }

    public function index()
    {
        $this->_syncExistingMediaLibrary(false);

        $conditions = array('MediaFile.status' => 'Active');
        $scopeCondition = $this->_mediaScopeCondition('MediaFile');
        if(!empty($scopeCondition))
        {
            $conditions[] = $scopeCondition;
        }

        $query = (array)$this->request->query;
        $folderId = isset($query['folder_id']) ? (int)$query['folder_id'] : 0;
        $fileType = isset($query['file_type']) ? strtolower(trim((string)$query['file_type'])) : '';
        $visibility = isset($query['visibility']) ? strtolower(trim((string)$query['visibility'])) : '';
        $keyword = isset($query['keyword']) ? trim((string)$query['keyword']) : '';

        if($folderId > 0)
        {
            $conditions['MediaFile.folder_id'] = $folderId;
        }
        if(in_array($fileType, array('image', 'document', 'video', 'audio', 'other'), true))
        {
            $conditions['MediaFile.file_type'] = $fileType;
        }
        if(in_array($visibility, array('backend', 'frontend', 'both'), true))
        {
            $conditions['MediaFile.visibility'] = $visibility;
        }
        if($keyword !== '')
        {
            $conditions[] = array('OR' => array(
                'MediaFile.title LIKE' => '%'.$keyword.'%',
                'MediaFile.original_name LIKE' => '%'.$keyword.'%',
                'MediaFile.file_name LIKE' => '%'.$keyword.'%',
                'MediaFile.source_type LIKE' => '%'.$keyword.'%'
            ));
        }

        $this->Paginator->settings = array(
            'MediaFile' => array(
                'conditions' => $conditions,
                'limit' => 24,
                'maxLimit' => 60,
                'order' => array('MediaFile.created' => 'desc', 'MediaFile.id' => 'desc'),
                'recursive' => 0
            )
        );
        $mediaFiles = $this->_decorateMediaRows($this->Paginator->paginate('MediaFile'));
        $folders = $this->_folderRowsWithCounts();
        $folderOptions = $this->_folderOptions();
        $stats = $this->_mediaStats();
        $canManageAllMedia = $this->_isMediaAdminUser();

        $this->set(compact('mediaFiles', 'folders', 'folderOptions', 'stats', 'query', 'folderId', 'fileType', 'visibility', 'keyword', 'canManageAllMedia'));
    }

    public function sync()
    {
        if(empty($this->userPermissionArr['save_right']) || !$this->_isMediaAdminUser())
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $count = $this->_syncExistingMediaLibrary(true);
        $this->Session->setFlash(sprintf(__('%s existing media files are now indexed in Media Gallery.'), (int)$count), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function add()
    {
        if(!$this->request->is('post'))
        {
            return $this->redirect(array('action' => 'index'));
        }
        $post = isset($this->request->data['MediaFile']) ? (array)$this->request->data['MediaFile'] : array();
        $folderId = !empty($post['folder_id']) ? (int)$post['folder_id'] : 0;
        $folderId = $this->_normalizeMediaFolderId($folderId);
        $visibility = !empty($post['visibility']) ? strtolower(trim((string)$post['visibility'])) : 'backend';
        if(!in_array($visibility, array('backend', 'frontend', 'both'), true))
        {
            $visibility = 'backend';
        }
        $title = !empty($post['title']) ? trim((string)$post['title']) : '';
        $files = $this->_normalizeUploadFiles(isset($post['upload']) ? $post['upload'] : array());
        $saved = 0;
        $uploadErrors = array();
        foreach($files as $file)
        {
            $uploadError = '';
            if($this->_saveUploadedMediaFile($file, $folderId, $visibility, $title, $uploadError))
            {
                $saved++;
            }
            elseif($uploadError !== '')
            {
                $uploadErrors[] = $uploadError;
            }
        }
        if($saved > 0)
        {
            $this->Session->setFlash(sprintf(__('%s media file(s) uploaded successfully.'), (int)$saved), 'flash', array('alert' => 'success'));
        }
        else
        {
            $message = __('No valid media file was uploaded.');
            if(!empty($uploadErrors))
            {
                $message .= ' '.implode(' ', array_slice(array_unique($uploadErrors), 0, 3));
            }
            $this->Session->setFlash($message, 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function edit($id = null)
    {
        $id = (int)$id;
        $media = $this->MediaFile->find('first', array(
            'conditions' => array('MediaFile.id' => $id),
            'recursive' => -1
        ));
        if(empty($media['MediaFile']) || !$this->_canAccessMediaRow($media['MediaFile']))
        {
            $this->Session->setFlash(__('Invalid media file.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        if($this->request->is(array('post', 'put')))
        {
            $post = isset($this->request->data['MediaFile']) ? (array)$this->request->data['MediaFile'] : array();
            $save = array(
                'id' => $id,
                'title' => isset($post['title']) ? trim((string)$post['title']) : '',
                'alt_text' => isset($post['alt_text']) ? trim((string)$post['alt_text']) : '',
                'folder_id' => $this->_normalizeMediaFolderId(!empty($post['folder_id']) ? (int)$post['folder_id'] : 0),
                'visibility' => !empty($post['visibility']) ? strtolower(trim((string)$post['visibility'])) : 'backend',
                'status' => !empty($post['status']) ? trim((string)$post['status']) : 'Active',
                'modified_by' => (int)$this->luserId,
                'modified' => date('Y-m-d H:i:s')
            );
            if(!in_array($save['visibility'], array('backend', 'frontend', 'both'), true))
            {
                $save['visibility'] = 'backend';
            }
            if(!in_array($save['status'], array('Active', 'Inactive', 'Deleted'), true))
            {
                $save['status'] = 'Active';
            }
            if($save['title'] === '')
            {
                $save['title'] = !empty($media['MediaFile']['original_name']) ? $media['MediaFile']['original_name'] : $media['MediaFile']['file_name'];
            }

            if($this->MediaFile->save(array('MediaFile' => $save), array('validate' => false)))
            {
                if(isset($post['upload']) && $media['MediaFile']['source_type'] === 'library')
                {
                    $files = $this->_normalizeUploadFiles($post['upload']);
                    if(!empty($files[0]))
                    {
                        $this->_replaceLibraryMediaFile($id, $files[0]);
                    }
                }
                $this->Session->setFlash(__('Media file has been updated.'), 'flash', array('alert' => 'success'));
                return $this->_mediaEditCloseResponse();
            }
            $this->Session->setFlash(__('Media file could not be updated.'), 'flash', array('alert' => 'danger'));
        }
        else
        {
            $this->request->data = $media;
        }

        $folderOptions = $this->_folderOptions();
        $media = $this->_decorateMediaRows(array($media));
        $media = !empty($media[0]) ? $media[0] : array();
        $this->set(compact('media', 'folderOptions'));
    }

    public function saveimageedit($id = null)
    {
        $id = (int)$id;
        if(!$this->request->is('post'))
        {
            return $this->redirect(array('action' => 'edit', $id));
        }
        $media = $this->MediaFile->find('first', array(
            'conditions' => array('MediaFile.id' => $id),
            'recursive' => -1
        ));
        if(empty($media['MediaFile']) || !$this->_canAccessMediaRow($media['MediaFile']))
        {
            $this->Session->setFlash(__('Invalid media file.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(empty($media['MediaFile']['file_type']) || $media['MediaFile']['file_type'] !== 'image')
        {
            $this->Session->setFlash(__('Only image files can be edited with crop tools.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'edit', $id));
        }

        $post = isset($this->request->data['MediaFile']) ? (array)$this->request->data['MediaFile'] : array();
        $dataUrl = isset($post['edited_image']) ? trim((string)$post['edited_image']) : '';
        $saveMode = isset($post['save_mode']) ? strtolower(trim((string)$post['save_mode'])) : 'copy';
        $decoded = $this->_decodeEditedImageDataUrl($dataUrl);
        if(empty($decoded['binary']))
        {
            $this->Session->setFlash(__('Edited image data was not valid. Please try again.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'edit', $id));
        }

        $updateOriginal = ($saveMode === 'update_original');
        if($updateOriginal)
        {
            if($this->_updateOriginalMediaImage($media['MediaFile'], $decoded))
            {
                $this->Session->setFlash(__('Original image has been updated with the same file name.'), 'flash', array('alert' => 'success'));
                return $this->_mediaEditCloseResponse();
            }
            $this->Session->setFlash(__('Original image could not be updated. Please keep the same file type as the original image.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'edit', $id));
        }

        $replaceCurrent = ($saveMode === 'replace' && $media['MediaFile']['source_type'] === 'library');
        $savedId = $this->_saveEditedMediaImage($media['MediaFile'], $decoded, $replaceCurrent);
        if($savedId > 0)
        {
            if($replaceCurrent)
            {
                $this->Session->setFlash(__('Image edits have been saved on this gallery file.'), 'flash', array('alert' => 'success'));
                return $this->_mediaEditCloseResponse();
            }
            $this->Session->setFlash(__('Edited image copy has been saved in Media Gallery.'), 'flash', array('alert' => 'success'));
            return $this->_mediaEditCloseResponse();
        }

        $this->Session->setFlash(__('Image edits could not be saved.'), 'flash', array('alert' => 'danger'));
        return $this->redirect(array('action' => 'edit', $id));
    }

    protected function _mediaEditCloseResponse()
    {
        $this->autoRender = false;
        $this->response->type('html');
        $targetUrl = Router::url(array('admin' => true, 'plugin' => 'admin', 'controller' => 'media_galleries', 'action' => 'index'), true);
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>'.h(__('Saving Media')).'</title></head><body><script type="text/javascript">
(function(){
    var targetUrl = '.json_encode($targetUrl).';
    try{
        if(window.opener && !window.opener.closed){
            window.opener.location.href = targetUrl;
            window.close();
            return;
        }
    }catch(e){}
    window.location.href = targetUrl;
})();
</script></body></html>';
        return $this->response;
    }

    public function delete($id = null)
    {
        $id = (int)$id;
        $media = $this->MediaFile->find('first', array(
            'conditions' => array('MediaFile.id' => $id),
            'recursive' => -1
        ));
        if(empty($media['MediaFile']) || !$this->_canAccessMediaRow($media['MediaFile']))
        {
            $this->Session->setFlash(__('Invalid media file.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->MediaFile->id = $id;
        $this->MediaFile->save(array('MediaFile' => array(
            'id' => $id,
            'status' => 'Deleted',
            'modified_by' => (int)$this->luserId,
            'modified' => date('Y-m-d H:i:s')
        )), array('validate' => false));
        $this->Session->setFlash(__('Media file has been removed from gallery.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function folderadd()
    {
        if(!$this->request->is('post'))
        {
            return $this->redirect(array('action' => 'index'));
        }
        $post = isset($this->request->data['MediaFolder']) ? (array)$this->request->data['MediaFolder'] : array();
        $name = isset($post['name']) ? trim((string)$post['name']) : '';
        if($name === '')
        {
            $this->Session->setFlash(__('Folder name is required.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $visibility = !empty($post['visibility']) ? strtolower(trim((string)$post['visibility'])) : 'backend';
        if(!in_array($visibility, array('backend', 'frontend', 'both'), true))
        {
            $visibility = 'backend';
        }
        $parentId = !empty($post['parent_id']) ? $this->_normalizeMediaFolderId((int)$post['parent_id']) : 0;
        $this->MediaFolder->create();
        $this->MediaFolder->save(array('MediaFolder' => array(
            'parent_id' => $parentId > 0 ? $parentId : null,
            'project_id' => $this->_currentMediaProjectId(),
            'name' => $name,
            'slug' => $this->_slug($name),
            'system_key' => '',
            'description' => isset($post['description']) ? trim((string)$post['description']) : '',
            'visibility' => $visibility,
            'status' => 'Active',
            'sort_order' => 50,
            'created_by' => (int)$this->luserId,
            'modified_by' => (int)$this->luserId,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s')
        )), array('validate' => false));
        $this->Session->setFlash(__('Media folder has been created.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function folderedit($id = null)
    {
        $id = (int)$id;
        $folder = $this->MediaFolder->find('first', array(
            'conditions' => array('MediaFolder.id' => $id, 'MediaFolder.status <>' => 'Deleted'),
            'recursive' => -1
        ));
        if(empty($folder['MediaFolder']) || !$this->_canAccessFolderRow($folder['MediaFolder']))
        {
            $this->Session->setFlash(__('Invalid media folder.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if($this->request->is(array('post', 'put')))
        {
            $post = isset($this->request->data['MediaFolder']) ? (array)$this->request->data['MediaFolder'] : array();
            $name = isset($post['name']) ? trim((string)$post['name']) : '';
            if($name === '')
            {
                $this->Session->setFlash(__('Folder name is required.'), 'flash', array('alert' => 'danger'));
            }
            else
            {
                $visibility = !empty($post['visibility']) ? strtolower(trim((string)$post['visibility'])) : 'backend';
                if(!in_array($visibility, array('backend', 'frontend', 'both'), true))
                {
                    $visibility = 'backend';
                }
                $parentId = !empty($post['parent_id']) ? $this->_normalizeMediaFolderId((int)$post['parent_id']) : 0;
                $this->MediaFolder->save(array('MediaFolder' => array(
                    'id' => $id,
                    'parent_id' => $parentId > 0 ? $parentId : null,
                    'name' => $name,
                    'slug' => $this->_slug($name),
                    'description' => isset($post['description']) ? trim((string)$post['description']) : '',
                    'visibility' => $visibility,
                    'status' => !empty($post['status']) ? trim((string)$post['status']) : 'Active',
                    'modified_by' => (int)$this->luserId,
                    'modified' => date('Y-m-d H:i:s')
                )), array('validate' => false));
                $this->Session->setFlash(__('Media folder has been updated.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
        }
        else
        {
            $this->request->data = $folder;
        }
        $folderOptions = $this->_folderOptions($id);
        $this->set(compact('folder', 'folderOptions'));
    }

    public function folderdelete($id = null)
    {
        $id = (int)$id;
        $folder = $this->MediaFolder->find('first', array(
            'conditions' => array('MediaFolder.id' => $id, 'MediaFolder.status <>' => 'Deleted'),
            'recursive' => -1
        ));
        if(empty($folder['MediaFolder']) || !$this->_canAccessFolderRow($folder['MediaFolder']))
        {
            $this->Session->setFlash(__('Invalid media folder.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $fileMoveConditions = array('MediaFile.folder_id' => $id);
        $fileScope = $this->_mediaScopeCondition('MediaFile');
        if(!empty($fileScope))
        {
            $fileMoveConditions[] = $fileScope;
        }
        $folderMoveConditions = array('MediaFolder.parent_id' => $id);
        $folderScope = $this->_mediaScopeCondition('MediaFolder');
        if(!empty($folderScope))
        {
            $folderMoveConditions[] = $folderScope;
        }
        $this->MediaFile->updateAll(array('MediaFile.folder_id' => 0), $fileMoveConditions);
        $this->MediaFolder->updateAll(array('MediaFolder.parent_id' => 'NULL'), $folderMoveConditions);
        $this->MediaFolder->save(array('MediaFolder' => array(
            'id' => $id,
            'status' => 'Deleted',
            'modified_by' => (int)$this->luserId,
            'modified' => date('Y-m-d H:i:s')
        )), array('validate' => false));
        $this->Session->setFlash(__('Media folder has been removed. Files were moved to All Media.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function download($id = null)
    {
        $id = (int)$id;
        $media = $this->MediaFile->find('first', array(
            'conditions' => array('MediaFile.id' => $id, 'MediaFile.status' => 'Active'),
            'recursive' => -1
        ));
        if(empty($media['MediaFile']) || !$this->_canAccessMediaRow($media['MediaFile']))
        {
            throw new NotFoundException(__('Invalid media file.'));
        }
        $path = $this->_physicalMediaPath($media['MediaFile']['dir'], $media['MediaFile']['file_name']);
        if(!is_file($path))
        {
            throw new NotFoundException(__('Media file not found.'));
        }
        $this->response->file($path, array(
            'download' => true,
            'name' => !empty($media['MediaFile']['original_name']) ? $media['MediaFile']['original_name'] : $media['MediaFile']['file_name']
        ));
        return $this->response;
    }

    public function serve($id = null, $variant = 'file')
    {
        $id = (int)$id;
        $media = $this->MediaFile->find('first', array(
            'conditions' => array('MediaFile.id' => $id, 'MediaFile.status' => 'Active'),
            'recursive' => -1
        ));
        if(empty($media['MediaFile']) || !$this->_canAccessMediaRow($media['MediaFile']))
        {
            throw new NotFoundException(__('Invalid media file.'));
        }

        $file = $media['MediaFile'];
        $path = $this->_physicalMediaPath($file['dir'], $file['file_name']);
        if($variant === 'thumb' && !empty($file['file_type']) && $file['file_type'] === 'image')
        {
            $thumbDir = !empty($file['dir']) ? $file['dir'].'_thumb' : '';
            $thumbPath = $this->_physicalMediaPath($thumbDir, $file['file_name']);
            if(is_file($thumbPath))
            {
                $path = $thumbPath;
            }
        }
        if(!is_file($path))
        {
            throw new NotFoundException(__('Media file not found.'));
        }

        $this->response->file($path, array(
            'download' => false,
            'name' => !empty($file['original_name']) ? $file['original_name'] : $file['file_name']
        ));
        return $this->response;
    }

    public function picker()
    {
        $this->autoRender = false;
        $this->response->type('json');

        $query = (array)$this->request->query;
        $conditions = array('MediaFile.status' => 'Active');
        $scopeCondition = $this->_mediaScopeCondition('MediaFile');
        if(!empty($scopeCondition))
        {
            $conditions[] = $scopeCondition;
        }

        $fileType = isset($query['file_type']) ? strtolower(trim((string)$query['file_type'])) : '';
        if(in_array($fileType, array('image', 'document', 'video', 'audio', 'other'), true))
        {
            $conditions['MediaFile.file_type'] = $fileType;
        }

        $keyword = isset($query['keyword']) ? trim((string)$query['keyword']) : '';
        if($keyword !== '')
        {
            $conditions[] = array('OR' => array(
                'MediaFile.title LIKE' => '%'.$keyword.'%',
                'MediaFile.original_name LIKE' => '%'.$keyword.'%',
                'MediaFile.file_name LIKE' => '%'.$keyword.'%',
                'MediaFile.source_type LIKE' => '%'.$keyword.'%'
            ));
        }

        $limit = isset($query['limit']) ? (int)$query['limit'] : 60;
        if($limit < 1 || $limit > 120)
        {
            $limit = 60;
        }

        $rows = $this->MediaFile->find('all', array(
            'conditions' => $conditions,
            'order' => array('MediaFile.created' => 'desc', 'MediaFile.id' => 'desc'),
            'limit' => $limit,
            'recursive' => -1
        ));
        $rows = $this->_decorateMediaRows($rows);

        $items = array();
        foreach((array)$rows as $row)
        {
            if(empty($row['MediaFile']) || empty($row['MediaFile']['exists']))
            {
                continue;
            }
            $m = $row['MediaFile'];
            $id = (int)$m['id'];
            $mType = !empty($m['file_type']) ? $m['file_type'] : 'other';
            $previewUrl = '';
            if($mType === 'image')
            {
                $previewUrl = Router::url(array('action' => 'serve', $id, 'thumb'), true);
            }
            elseif($mType === 'video' || $mType === 'document')
            {
                $previewUrl = !empty($m['public_url']) ? $m['public_url'] : Router::url(array('action' => 'serve', $id, 'file'), true);
            }

            $items[] = array(
                'id' => $id,
                'title' => !empty($m['title']) ? $m['title'] : $m['file_name'],
                'file_name' => !empty($m['original_name']) ? $m['original_name'] : $m['file_name'],
                'file_type' => $mType,
                'extension' => !empty($m['extension']) ? $m['extension'] : '',
                'mime_type' => !empty($m['mime_type']) ? $m['mime_type'] : '',
                'size_label' => !empty($m['size_label']) ? $m['size_label'] : '',
                'visibility' => !empty($m['visibility']) ? $m['visibility'] : 'backend',
                'source_type' => !empty($m['source_type']) ? $m['source_type'] : 'library',
                'public_url' => !empty($m['public_url']) ? $m['public_url'] : Router::url(array('action' => 'serve', $id, 'file'), true),
                'preview_url' => $previewUrl,
                'fetch_url' => Router::url(array('action' => 'serve', $id, 'file'), true)
            );
        }

        return $this->response->body(json_encode(array(
            'success' => true,
            'items' => $items
        )));
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
            KEY `idx_created_by` (`created_by`),
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
            KEY `idx_created_by` (`created_by`),
            KEY `idx_source` (`source_type`, `source_id`),
            KEY `idx_type_status` (`file_type`, `status`),
            KEY `idx_visibility` (`visibility`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        try
        {
            if(!$this->_indexExists('media_folders', 'idx_created_by'))
            {
                $Schema->query("ALTER TABLE `media_folders` ADD KEY `idx_created_by` (`created_by`)");
            }
            if(!$this->_indexExists('media_files', 'idx_created_by'))
            {
                $Schema->query("ALTER TABLE `media_files` ADD KEY `idx_created_by` (`created_by`)");
            }
        }
        catch(Exception $e) {}
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

    protected function _ensureMediaLibraryDirectories()
    {
        new Folder(WWW_ROOT.'img'.DS.'media_gallery', true, 0775);
        new Folder(WWW_ROOT.'img'.DS.'media_gallery_thumb', true, 0775);
    }

    protected function _syncExistingMediaLibrary($force = false)
    {
        if(!$force && $this->Session->read('MediaGallery.synced') === 'yes')
        {
            return 0;
        }
        $folderIds = $this->_ensureDefaultMediaFolders();
        $count = 0;

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
                $count += $this->_upsertSourceMedia(array(
                    'source_type' => 'property_photo',
                    'source_id' => $src['id'],
                    'folder_id' => $folderIds['property_photos'],
                    'project_id' => isset($prop['project_id']) ? (int)$prop['project_id'] : 0,
                    'title' => sprintf(__('Property Photo - %s'), !empty($prop['property_name']) ? $prop['property_name'] : '#'.$src['property_id']),
                    'file_name' => $src['photo'],
                    'dir' => $src['dir'],
                    'visibility' => 'both'
                ));
            }
        }

        if($this->_tableExists('properties_documents'))
        {
            $rows = $this->MediaFile->query("SELECT `pd`.`id`, `pd`.`photo`, `pd`.`dir`, `pd`.`property_id`, `p`.`name` AS `property_name`, `p`.`project_id`
                FROM `properties_documents` `pd`
                LEFT JOIN `properties` `p` ON `p`.`id`=`pd`.`property_id`
                WHERE `pd`.`photo` IS NOT NULL AND TRIM(`pd`.`photo`)<>''");
            foreach($rows as $row)
            {
                $src = !empty($row['pd']) ? $row['pd'] : array();
                $prop = !empty($row['p']) ? $row['p'] : array();
                $count += $this->_upsertSourceMedia(array(
                    'source_type' => 'property_document',
                    'source_id' => $src['id'],
                    'folder_id' => $folderIds['property_documents'],
                    'project_id' => isset($prop['project_id']) ? (int)$prop['project_id'] : 0,
                    'title' => sprintf(__('Property Document - %s'), !empty($prop['property_name']) ? $prop['property_name'] : '#'.$src['property_id']),
                    'file_name' => $src['photo'],
                    'dir' => $src['dir'],
                    'visibility' => 'backend'
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
                $count += $this->_upsertSourceMedia(array(
                    'source_type' => 'property_floorplan',
                    'source_id' => $src['id'],
                    'folder_id' => $folderIds['property_floorplans'],
                    'project_id' => isset($prop['project_id']) ? (int)$prop['project_id'] : 0,
                    'title' => sprintf(__('Floor Plan - %s'), !empty($prop['property_name']) ? $prop['property_name'] : '#'.$src['property_id']),
                    'file_name' => $src['photo'],
                    'dir' => $src['dir'],
                    'visibility' => 'both'
                ));
            }
        }

        if($this->_tableExists('deal_documents'))
        {
            $rows = $this->MediaFile->query("SELECT `dd`.`id`, `dd`.`photo`, `dd`.`dir`, `dd`.`document_type`, `dd`.`project_id`, `c`.`name` AS `client_name`
                FROM `deal_documents` `dd`
                LEFT JOIN `clients` `c` ON `c`.`id`=`dd`.`client_id`
                WHERE `dd`.`photo` IS NOT NULL AND TRIM(`dd`.`photo`)<>''");
            foreach($rows as $row)
            {
                $src = !empty($row['dd']) ? $row['dd'] : array();
                $client = !empty($row['c']) ? $row['c'] : array();
                $count += $this->_upsertSourceMedia(array(
                    'source_type' => 'deal_document',
                    'source_id' => $src['id'],
                    'folder_id' => $folderIds['deal_documents'],
                    'project_id' => isset($src['project_id']) ? (int)$src['project_id'] : 0,
                    'title' => trim((string)$src['document_type']).(!empty($client['client_name']) ? ' - '.$client['client_name'] : ''),
                    'file_name' => $src['photo'],
                    'dir' => $src['dir'],
                    'visibility' => 'backend'
                ));
            }
        }

        if($this->_tableExists('slides'))
        {
            $rows = $this->MediaFile->query("SELECT `id`, `slide_name`, `photo`, `dirt` FROM `slides` WHERE `photo` IS NOT NULL AND TRIM(`photo`)<>''");
            foreach($rows as $row)
            {
                $src = !empty($row['slides']) ? $row['slides'] : (!empty($row['Slide']) ? $row['Slide'] : (!empty($row[0]) ? $row[0] : array()));
                $count += $this->_upsertSourceMedia(array(
                    'source_type' => 'slide',
                    'source_id' => $src['id'],
                    'folder_id' => $folderIds['website_media'],
                    'project_id' => 0,
                    'title' => !empty($src['slide_name']) ? $src['slide_name'] : __('Website Slide'),
                    'file_name' => $src['photo'],
                    'dir' => $src['dirt'],
                    'visibility' => 'both'
                ));
            }
        }

        if($this->_tableExists('types'))
        {
            $rows = $this->MediaFile->query("SELECT `id`, `name`, `photo`, `dirt` FROM `types` WHERE `photo` IS NOT NULL AND TRIM(`photo`)<>''");
            foreach($rows as $row)
            {
                $src = !empty($row['types']) ? $row['types'] : (!empty($row[0]) ? $row[0] : array());
                $count += $this->_upsertSourceMedia(array(
                    'source_type' => 'property_type',
                    'source_id' => $src['id'],
                    'folder_id' => $folderIds['website_media'],
                    'project_id' => 0,
                    'title' => !empty($src['name']) ? $src['name'] : __('Property Category'),
                    'file_name' => $src['photo'],
                    'dir' => $src['dirt'],
                    'visibility' => 'both'
                ));
            }
        }

        if($this->_tableExists('users'))
        {
            $rows = $this->MediaFile->query("SELECT `id`, `name`, `photo`, `dir`, `project_id` FROM `users` WHERE `photo` IS NOT NULL AND TRIM(`photo`)<>''");
            foreach($rows as $row)
            {
                $src = !empty($row['users']) ? $row['users'] : (!empty($row[0]) ? $row[0] : array());
                $count += $this->_upsertSourceMedia(array(
                    'source_type' => 'user_photo',
                    'source_id' => $src['id'],
                    'folder_id' => $folderIds['user_media'],
                    'project_id' => isset($src['project_id']) ? (int)$src['project_id'] : 0,
                    'title' => !empty($src['name']) ? $src['name'] : __('User Photo'),
                    'file_name' => $src['photo'],
                    'dir' => !empty($src['dir']) ? $src['dir'] : 'user',
                    'visibility' => 'backend'
                ));
            }
        }

        if($this->_tableExists('clients'))
        {
            $count += $this->_syncClientMediaColumn('client_photo', 'photo', 'dirt', $folderIds['client_media'], __('Client Photo'));
            $count += $this->_syncClientMediaColumn('client_id_front', 'id_front', 'id_front_dir', $folderIds['client_media'], __('Client ID Front'));
            $count += $this->_syncClientMediaColumn('client_id_back', 'id_back', 'id_back_dir', $folderIds['client_media'], __('Client ID Back'));
            $count += $this->_syncClientMediaColumn('client_side_photo', 'side_photo', 'side_photo_dir', $folderIds['client_media'], __('Client Side Photo'));
        }

        if($this->_tableExists('configurations'))
        {
            $rows = $this->MediaFile->query("SELECT `id`, `name`, `organization_name`, `photo` FROM `configurations` WHERE `photo` IS NOT NULL AND TRIM(`photo`)<>''");
            foreach($rows as $row)
            {
                $src = !empty($row['configurations']) ? $row['configurations'] : (!empty($row[0]) ? $row[0] : array());
                $photoInfo = $this->_splitConfiguredPath($src['photo']);
                $count += $this->_upsertSourceMedia(array(
                    'source_type' => 'site_logo',
                    'source_id' => $src['id'],
                    'folder_id' => $folderIds['website_media'],
                    'project_id' => 0,
                    'title' => !empty($src['organization_name']) ? $src['organization_name'] : (!empty($src['name']) ? $src['name'] : __('Website Logo')),
                    'file_name' => $photoInfo['file'],
                    'visibility' => 'both'
                ));
            }
        }

        if($this->_tableExists('properties') && $this->_columnExists('properties', 'payment_plan_pdf'))
        {
            $rows = $this->MediaFile->query("SELECT `id`, `name`, `payment_plan_pdf`, `project_id` FROM `properties` WHERE `payment_plan_pdf` IS NOT NULL AND TRIM(`payment_plan_pdf`)<>''");
            foreach($rows as $row)
            {
                $src = !empty($row['properties']) ? $row['properties'] : (!empty($row['Property']) ? $row['Property'] : (!empty($row[0]) ? $row[0] : array()));
                if(empty($src['payment_plan_pdf'])) continue;
                $count += $this->_upsertSourceMedia(array(
                    'source_type' => 'payment_plan_pdf',
                    'source_id' => $src['id'],
                    'folder_id' => $folderIds['property_documents'],
                    'project_id' => isset($src['project_id']) ? (int)$src['project_id'] : 0,
                    'title' => sprintf(__('Payment Plan PDF - %s'), !empty($src['name']) ? $src['name'] : '#'.$src['id']),
                    'file_name' => $src['payment_plan_pdf'],
                    'dir' => 'files/payment_plans',
                    'visibility' => 'both'
                ));
            }
        }

        // Also index any existing PDF files found in files/payment_plans/ directory
        $planDir = WWW_ROOT . 'files' . DS . 'payment_plans';
        if(is_dir($planDir))
        {
            $diskFiles = (array)glob($planDir . DS . '*.pdf');
            foreach($diskFiles as $planFile)
            {
                $bName = basename($planFile);
                if($bName === '') continue;
                $count += $this->_upsertSourceMedia(array(
                    'source_type' => 'payment_plan_pdf',
                    'source_id' => 0,
                    'folder_id' => $folderIds['property_documents'],
                    'project_id' => 0,
                    'title' => ucwords(str_replace(array('-', '_', '.pdf'), array(' ', ' ', ''), $bName)) . ' (Payment Plan PDF)',
                    'file_name' => $bName,
                    'dir' => 'files/payment_plans',
                    'visibility' => 'both'
                ));
            }
        }

        $this->Session->write('MediaGallery.synced', 'yes');
        return $count;
    }

    protected function _syncClientMediaColumn($sourceType, $fileColumn, $dirColumn, $folderId, $label)
    {
        if(!$this->_columnExists('clients', $fileColumn))
        {
            return 0;
        }
        $dirSelect = $this->_columnExists('clients', $dirColumn) ? "`$dirColumn`" : "''";
        $rows = $this->MediaFile->query("SELECT `id`, `name`, `project_id`, `$fileColumn` AS `media_file`, $dirSelect AS `media_dir` FROM `clients` WHERE `$fileColumn` IS NOT NULL AND TRIM(`$fileColumn`)<>''");
        $count = 0;
        foreach($rows as $row)
        {
            $src = !empty($row['clients']) ? $row['clients'] : (!empty($row[0]) ? $row[0] : array());
            $count += $this->_upsertSourceMedia(array(
                'source_type' => $sourceType,
                'source_id' => $src['id'],
                'folder_id' => $folderId,
                'project_id' => isset($src['project_id']) ? (int)$src['project_id'] : 0,
                'title' => $label.(!empty($src['name']) ? ' - '.$src['name'] : ''),
                'file_name' => $src['media_file'],
                'dir' => !empty($src['media_dir']) ? $src['media_dir'] : 'client',
                'visibility' => 'backend'
            ));
        }
        return $count;
    }

    protected function _ensureDefaultMediaFolders()
    {
        return array(
            'library' => $this->_ensureMediaFolder('library', __('Library Uploads'), 'backend', 1),
            'property_photos' => $this->_ensureMediaFolder('property_photos', __('Property Photos'), 'both', 2),
            'property_documents' => $this->_ensureMediaFolder('property_documents', __('Property Documents'), 'backend', 3),
            'property_floorplans' => $this->_ensureMediaFolder('property_floorplans', __('Property Floor Plans'), 'both', 4),
            'deal_documents' => $this->_ensureMediaFolder('deal_documents', __('Deal Documents'), 'backend', 5),
            'client_media' => $this->_ensureMediaFolder('client_media', __('Client Media'), 'backend', 6),
            'user_media' => $this->_ensureMediaFolder('user_media', __('User & Agent Media'), 'backend', 7),
            'website_media' => $this->_ensureMediaFolder('website_media', __('Website Media'), 'both', 8)
        );
    }

    protected function _ensureMediaFolder($systemKey, $name, $visibility, $sortOrder)
    {
        $row = $this->MediaFolder->find('first', array(
            'conditions' => array('MediaFolder.system_key' => $systemKey),
            'recursive' => -1
        ));
        if(!empty($row['MediaFolder']['id']))
        {
            if($row['MediaFolder']['status'] === 'Deleted')
            {
                $this->MediaFolder->save(array('MediaFolder' => array(
                    'id' => (int)$row['MediaFolder']['id'],
                    'status' => 'Active',
                    'modified' => date('Y-m-d H:i:s')
                )), array('validate' => false));
            }
            return (int)$row['MediaFolder']['id'];
        }
        $this->MediaFolder->create();
        $this->MediaFolder->save(array('MediaFolder' => array(
            'parent_id' => null,
            'project_id' => 0,
            'name' => $name,
            'slug' => $this->_slug($name),
            'system_key' => $systemKey,
            'description' => '',
            'visibility' => $visibility,
            'status' => 'Active',
            'sort_order' => (int)$sortOrder,
            'created_by' => 0,
            'modified_by' => 0,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s')
        )), array('validate' => false));
        return (int)$this->MediaFolder->id;
    }

    protected function _upsertSourceMedia($data)
    {
        $sourceType = isset($data['source_type']) ? trim((string)$data['source_type']) : '';
        $sourceId = isset($data['source_id']) ? (int)$data['source_id'] : 0;
        $fileName = isset($data['file_name']) ? trim((string)$data['file_name']) : '';
        $dir = isset($data['dir']) ? trim((string)$data['dir']) : '';
        if($sourceType === '' || $sourceId <= 0 || $fileName === '')
        {
            return 0;
        }
        $fileName = basename($fileName);
        if($fileName === '')
        {
            return 0;
        }
        $path = $this->_physicalMediaPath($dir, $fileName);
        if(!is_file($path))
        {
            return 0;
        }

        $existing = $this->MediaFile->find('first', array(
            'conditions' => array(
                'MediaFile.source_type' => $sourceType,
                'MediaFile.source_id' => $sourceId
            ),
            'recursive' => -1
        ));
        if(!empty($existing['MediaFile']) && $existing['MediaFile']['status'] === 'Deleted')
        {
            return 0;
        }

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileType = $this->_fileTypeByExtension($extension);
        $mime = $this->_detectMimeType($path, $extension);
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
            'mime_type' => $mime,
            'file_type' => $fileType,
            'extension' => $extension,
            'file_size' => (int)@filesize($path),
            'visibility' => isset($data['visibility']) ? trim((string)$data['visibility']) : 'backend',
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
        if($this->MediaFile->save(array('MediaFile' => $save), array('validate' => false)))
        {
            return empty($existing['MediaFile']['id']) ? 1 : 0;
        }
        return 0;
    }

    protected function _saveUploadedMediaFile($file, $folderId, $visibility, $title, &$errorMessage = '')
    {
        $errorMessage = '';
        if(empty($file['tmp_name']) || empty($file['name']) || (isset($file['error']) && (int)$file['error'] !== UPLOAD_ERR_OK))
        {
            if(isset($file['error']) && (int)$file['error'] !== UPLOAD_ERR_NO_FILE)
            {
                $errorMessage = $this->_uploadErrorMessage((int)$file['error'], isset($file['name']) ? $file['name'] : '');
            }
            return false;
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if(!in_array($extension, $this->_allowedExtensions(), true))
        {
            $errorMessage = sprintf(__('File type not allowed: %s.'), h($file['name']));
            return false;
        }
        $base = pathinfo($file['name'], PATHINFO_FILENAME);
        $base = preg_replace('/[^A-Za-z0-9_\-]+/', '-', $base);
        $base = trim($base, '-_');
        if($base === '')
        {
            $base = 'media';
        }
        $fileName = date('YmdHis').'-'.substr(md5(uniqid('', true)), 0, 8).'-'.$base.'.'.$extension;
        $target = WWW_ROOT.'img'.DS.'media_gallery'.DS.$fileName;
        new Folder(WWW_ROOT.'img'.DS.'media_gallery', true, 0775);
        if(!@move_uploaded_file($file['tmp_name'], $target))
        {
            if(!@copy($file['tmp_name'], $target))
            {
                $errorMessage = __('Upload folder is not writable or the temporary file is missing.');
                return false;
            }
        }
        $fileType = $this->_fileTypeByExtension($extension);
        if($fileType === 'image' && in_array($extension, array('jpg', 'jpeg', 'png', 'gif'), true))
        {
            try
            {
                $this->CustomFunction->resizedUrl($fileName, 'img'.DS.'media_gallery'.DS, 'img'.DS.'media_gallery_thumb'.DS, 260, 180);
            }
            catch(Exception $e)
            {
                // Missing thumbnail should not block the upload.
            }
        }
        $displayTitle = trim((string)$title);
        if($displayTitle === '')
        {
            $displayTitle = pathinfo($file['name'], PATHINFO_FILENAME);
        }
        $now = date('Y-m-d H:i:s');
        $this->MediaFile->create();
        $saved = $this->MediaFile->save(array('MediaFile' => array(
            'folder_id' => (int)$folderId,
            'project_id' => $this->_currentMediaProjectId(),
            'source_type' => 'library',
            'source_id' => 0,
            'title' => $displayTitle,
            'alt_text' => $displayTitle,
            'file_name' => $fileName,
            'original_name' => $file['name'],
            'dir' => 'media_gallery',
            'file_path' => 'media_gallery/'.$fileName,
            'url_path' => 'media_gallery/'.$fileName,
            'mime_type' => $this->_detectMimeType($target, $extension),
            'file_type' => $fileType,
            'extension' => $extension,
            'file_size' => (int)@filesize($target),
            'visibility' => $visibility,
            'status' => 'Active',
            'created_by' => (int)$this->luserId,
            'modified_by' => (int)$this->luserId,
            'created' => $now,
            'modified' => $now
        )), array('validate' => false));
        if($saved)
        {
            $id = (int)$this->MediaFile->id;
            $this->MediaFile->save(array('MediaFile' => array('id' => $id, 'source_id' => $id)), array('validate' => false));
            return true;
        }
        $errorMessage = __('Media record could not be saved in database.');
        return false;
    }

    protected function _replaceLibraryMediaFile($id, $file)
    {
        if(empty($file['tmp_name']) || empty($file['name']) || (isset($file['error']) && (int)$file['error'] !== UPLOAD_ERR_OK))
        {
            return false;
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if(!in_array($extension, $this->_allowedExtensions(), true))
        {
            return false;
        }
        $base = preg_replace('/[^A-Za-z0-9_\-]+/', '-', pathinfo($file['name'], PATHINFO_FILENAME));
        $base = trim($base, '-_');
        if($base === '')
        {
            $base = 'media';
        }
        $fileName = date('YmdHis').'-'.substr(md5(uniqid('', true)), 0, 8).'-'.$base.'.'.$extension;
        $target = WWW_ROOT.'img'.DS.'media_gallery'.DS.$fileName;
        if(!@move_uploaded_file($file['tmp_name'], $target))
        {
            if(!@copy($file['tmp_name'], $target))
            {
                return false;
            }
        }
        $fileType = $this->_fileTypeByExtension($extension);
        if($fileType === 'image' && in_array($extension, array('jpg', 'jpeg', 'png', 'gif'), true))
        {
            try
            {
                $this->CustomFunction->resizedUrl($fileName, 'img'.DS.'media_gallery'.DS, 'img'.DS.'media_gallery_thumb'.DS, 260, 180);
            }
            catch(Exception $e) {}
        }
        return $this->MediaFile->save(array('MediaFile' => array(
            'id' => (int)$id,
            'file_name' => $fileName,
            'original_name' => $file['name'],
            'dir' => 'media_gallery',
            'file_path' => 'media_gallery/'.$fileName,
            'url_path' => 'media_gallery/'.$fileName,
            'mime_type' => $this->_detectMimeType($target, $extension),
            'file_type' => $fileType,
            'extension' => $extension,
            'file_size' => (int)@filesize($target),
            'modified_by' => (int)$this->luserId,
            'modified' => date('Y-m-d H:i:s')
        )), array('validate' => false));
    }

    protected function _decodeEditedImageDataUrl($dataUrl)
    {
        if(!preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $dataUrl, $match))
        {
            return array();
        }
        $extension = strtolower($match[1]);
        if($extension === 'jpeg')
        {
            $extension = 'jpg';
        }
        $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $binary = base64_decode($base64, true);
        if($binary === false || strlen($binary) <= 0)
        {
            return array();
        }
        $info = @getimagesizefromstring($binary);
        if(empty($info[0]) || empty($info[1]) || $info[0] > 6000 || $info[1] > 6000)
        {
            return array();
        }
        return array(
            'binary' => $binary,
            'extension' => $extension,
            'mime_type' => $extension === 'jpg' ? 'image/jpeg' : 'image/png',
            'width' => (int)$info[0],
            'height' => (int)$info[1]
        );
    }

    protected function _saveEditedMediaImage($media, $decoded, $replaceCurrent = false)
    {
        $this->_ensureMediaLibraryDirectories();
        $extension = !empty($decoded['extension']) ? $decoded['extension'] : 'png';
        if(!in_array($extension, array('png', 'jpg'), true))
        {
            $extension = 'png';
        }
        $fileName = 'edited-'.$media['id'].'-'.date('YmdHis').'-'.substr(md5(uniqid('', true)), 0, 8).'.'.$extension;
        $target = WWW_ROOT.'img'.DS.'media_gallery'.DS.$fileName;
        if(@file_put_contents($target, $decoded['binary']) === false)
        {
            return 0;
        }
        if(!is_file($target))
        {
            return 0;
        }
        try
        {
            $this->CustomFunction->resizedUrl($fileName, 'img'.DS.'media_gallery'.DS, 'img'.DS.'media_gallery_thumb'.DS, 260, 180);
        }
        catch(Exception $e)
        {
            // The full edited image is still valid if the thumbnail cannot be generated.
        }

        $now = date('Y-m-d H:i:s');
        $title = !empty($media['title']) ? $media['title'] : (!empty($media['original_name']) ? $media['original_name'] : __('Media Image'));
        if($replaceCurrent)
        {
            $save = array(
                'id' => (int)$media['id'],
                'file_name' => $fileName,
                'original_name' => $fileName,
                'dir' => 'media_gallery',
                'file_path' => 'media_gallery/'.$fileName,
                'url_path' => 'media_gallery/'.$fileName,
                'mime_type' => $decoded['mime_type'],
                'file_type' => 'image',
                'extension' => $extension,
                'file_size' => (int)@filesize($target),
                'modified_by' => (int)$this->luserId,
                'modified' => $now
            );
            if($this->MediaFile->save(array('MediaFile' => $save), array('validate' => false)))
            {
                return (int)$media['id'];
            }
            return 0;
        }

        $this->MediaFile->create();
        $copyFolderId = !empty($media['folder_id']) ? $this->_normalizeMediaFolderId((int)$media['folder_id']) : 0;
        $save = array(
            'folder_id' => $copyFolderId,
            'project_id' => isset($media['project_id']) ? (int)$media['project_id'] : $this->_currentMediaProjectId(),
            'source_type' => 'library',
            'source_id' => 0,
            'title' => $title.' - '.__('Edited Copy'),
            'alt_text' => !empty($media['alt_text']) ? $media['alt_text'] : $title,
            'file_name' => $fileName,
            'original_name' => $fileName,
            'dir' => 'media_gallery',
            'file_path' => 'media_gallery/'.$fileName,
            'url_path' => 'media_gallery/'.$fileName,
            'mime_type' => $decoded['mime_type'],
            'file_type' => 'image',
            'extension' => $extension,
            'file_size' => (int)@filesize($target),
            'visibility' => !empty($media['visibility']) ? $media['visibility'] : 'backend',
            'status' => 'Active',
            'created_by' => (int)$this->luserId,
            'modified_by' => (int)$this->luserId,
            'created' => $now,
            'modified' => $now
        );
        if($this->MediaFile->save(array('MediaFile' => $save), array('validate' => false)))
        {
            $newId = (int)$this->MediaFile->id;
            $this->MediaFile->save(array('MediaFile' => array('id' => $newId, 'source_id' => $newId)), array('validate' => false));
            return $newId;
        }
        return 0;
    }

    protected function _updateOriginalMediaImage($media, $decoded)
    {
        $fileName = !empty($media['file_name']) ? basename((string)$media['file_name']) : '';
        $dir = isset($media['dir']) ? $media['dir'] : '';
        if($fileName === '' || empty($decoded['binary']))
        {
            return false;
        }
        $originalExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if($originalExtension === 'jpeg')
        {
            $originalExtension = 'jpg';
        }
        $decodedExtension = !empty($decoded['extension']) ? strtolower($decoded['extension']) : '';
        if($decodedExtension === 'jpeg')
        {
            $decodedExtension = 'jpg';
        }
        if(!in_array($originalExtension, array('jpg', 'png'), true) || $decodedExtension !== $originalExtension)
        {
            return false;
        }

        $target = $this->_physicalMediaPath($dir, $fileName);
        if(!is_file($target))
        {
            return false;
        }
        $tempTarget = $target.'.edit-'.date('YmdHis').'-'.substr(md5(uniqid('', true)), 0, 6).'.tmp';
        if(@file_put_contents($tempTarget, $decoded['binary']) === false)
        {
            return false;
        }
        if(!@copy($tempTarget, $target))
        {
            @unlink($tempTarget);
            return false;
        }
        @unlink($tempTarget);

        $cleanDir = $this->_cleanMediaDir($dir);
        if($cleanDir !== '')
        {
            try
            {
                $this->CustomFunction->resizedUrl($fileName, 'img'.DS.$cleanDir.DS, 'img'.DS.$cleanDir.'_thumb'.DS, 260, 180);
            }
            catch(Exception $e)
            {
                // The original image has still been updated if thumbnail refresh fails.
            }
        }

        return $this->MediaFile->save(array('MediaFile' => array(
            'id' => (int)$media['id'],
            'mime_type' => $decoded['mime_type'],
            'file_size' => (int)@filesize($target),
            'modified_by' => (int)$this->luserId,
            'modified' => date('Y-m-d H:i:s')
        )), array('validate' => false));
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
            $urlPrefix = (strpos($publicPath, 'files/') === 0 || strpos($publicPath, 'files\\') === 0) ? '/' : '/img/';
            $rows[$key]['MediaFile']['public_url'] = Router::url($urlPrefix.$publicPath, true);
            $rows[$key]['MediaFile']['public_thumb_url'] = Router::url('/img/'.$thumbPath, true);
            if(!empty($file['id']))
            {
                $rows[$key]['MediaFile']['url'] = Router::url(array('action' => 'serve', (int)$file['id'], 'file'), true);
                $rows[$key]['MediaFile']['thumb_url'] = Router::url(array('action' => 'serve', (int)$file['id'], 'thumb'), true);
            }
            else
            {
                $rows[$key]['MediaFile']['url'] = Router::url($urlPrefix.$publicPath, true);
                $rows[$key]['MediaFile']['thumb_url'] = Router::url('/img/'.$thumbPath, true);
            }
            $rows[$key]['MediaFile']['size_label'] = $this->_formatBytes(!empty($file['file_size']) ? (int)$file['file_size'] : (int)@filesize($path));
            $rows[$key]['MediaFile']['icon'] = $this->_fileIconClass($file);
        }
        return $rows;
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

    protected function _folderOptions($excludeId = 0)
    {
        $conditions = array('MediaFolder.status' => 'Active');
        $scopeCondition = $this->_mediaScopeCondition('MediaFolder');
        if(!empty($scopeCondition))
        {
            $conditions[] = $scopeCondition;
        }
        if((int)$excludeId > 0)
        {
            $conditions['MediaFolder.id <>'] = (int)$excludeId;
        }
        $options = $this->MediaFolder->find('list', array(
            'conditions' => $conditions,
            'fields' => array('MediaFolder.id', 'MediaFolder.name'),
            'order' => array('MediaFolder.sort_order' => 'asc', 'MediaFolder.name' => 'asc'),
            'recursive' => -1
        ));
        return (array)$options;
    }

    protected function _folderRowsWithCounts()
    {
        $conditions = array('MediaFolder.status' => 'Active');
        $scopeCondition = $this->_mediaScopeCondition('MediaFolder');
        if(!empty($scopeCondition))
        {
            $conditions[] = $scopeCondition;
        }
        $folders = (array)$this->MediaFolder->find('all', array(
            'conditions' => $conditions,
            'order' => array('MediaFolder.sort_order' => 'asc', 'MediaFolder.name' => 'asc'),
            'recursive' => -1
        ));
        $fileConditions = array('MediaFile.status' => 'Active');
        $fileScope = $this->_mediaScopeCondition('MediaFile');
        if(!empty($fileScope))
        {
            $fileConditions[] = $fileScope;
        }
        $countRows = (array)$this->MediaFile->find('all', array(
            'fields' => array('MediaFile.folder_id', 'COUNT(MediaFile.id) AS total_files'),
            'conditions' => $fileConditions,
            'group' => array('MediaFile.folder_id'),
            'recursive' => -1
        ));
        $counts = array();
        foreach($countRows as $row)
        {
            $folderId = isset($row['MediaFile']['folder_id']) ? (int)$row['MediaFile']['folder_id'] : 0;
            $counts[$folderId] = isset($row[0]['total_files']) ? (int)$row[0]['total_files'] : 0;
        }
        foreach($folders as $key => $folder)
        {
            $id = (int)$folder['MediaFolder']['id'];
            $folders[$key]['MediaFolder']['file_count'] = isset($counts[$id]) ? $counts[$id] : 0;
        }
        return $folders;
    }

    protected function _mediaStats()
    {
        $conditions = array('MediaFile.status' => 'Active');
        $scope = $this->_mediaScopeCondition('MediaFile');
        if(!empty($scope))
        {
            $conditions[] = $scope;
        }
        return array(
            'all' => (int)$this->MediaFile->find('count', array('conditions' => $conditions, 'recursive' => -1)),
            'image' => (int)$this->MediaFile->find('count', array('conditions' => array_merge($conditions, array('MediaFile.file_type' => 'image')), 'recursive' => -1)),
            'document' => (int)$this->MediaFile->find('count', array('conditions' => array_merge($conditions, array('MediaFile.file_type' => 'document')), 'recursive' => -1)),
            'video' => (int)$this->MediaFile->find('count', array('conditions' => array_merge($conditions, array('MediaFile.file_type' => 'video')), 'recursive' => -1)),
            'frontend' => (int)$this->MediaFile->find('count', array('conditions' => array_merge($conditions, array('MediaFile.visibility' => array('frontend', 'both'))), 'recursive' => -1))
        );
    }

    protected function _normalizeUploadFiles($files)
    {
        $normalized = array();

        // 1. Process array if passed via CakePHP request data
        if(!empty($files) && is_array($files))
        {
            if(isset($files[0]) && is_array($files[0]))
            {
                foreach($files as $file)
                {
                    if(is_array($file) && !empty($file['name']))
                    {
                        $normalized[] = $file;
                    }
                }
            }
            elseif(isset($files['name']) && is_array($files['name']))
            {
                foreach($files['name'] as $key => $name)
                {
                    if(!empty($name))
                    {
                        $normalized[] = array(
                            'name' => $name,
                            'type' => isset($files['type'][$key]) ? $files['type'][$key] : '',
                            'tmp_name' => isset($files['tmp_name'][$key]) ? $files['tmp_name'][$key] : '',
                            'error' => isset($files['error'][$key]) ? $files['error'][$key] : UPLOAD_ERR_NO_FILE,
                            'size' => isset($files['size'][$key]) ? $files['size'][$key] : 0
                        );
                    }
                }
            }
            elseif(isset($files['name']) && !empty($files['name']))
            {
                $normalized[] = $files;
            }
            else
            {
                foreach($files as $item)
                {
                    if(is_array($item))
                    {
                        if(isset($item['name']) && !is_array($item['name']) && !empty($item['name']))
                        {
                            $normalized[] = $item;
                        }
                        elseif(isset($item['name']) && is_array($item['name']))
                        {
                            foreach($item['name'] as $k => $subName)
                            {
                                if(!empty($subName))
                                {
                                    $normalized[] = array(
                                        'name' => $subName,
                                        'type' => isset($item['type'][$k]) ? $item['type'][$k] : '',
                                        'tmp_name' => isset($item['tmp_name'][$k]) ? $item['tmp_name'][$k] : '',
                                        'error' => isset($item['error'][$k]) ? $item['error'][$k] : UPLOAD_ERR_NO_FILE,
                                        'size' => isset($item['size'][$k]) ? $item['size'][$k] : 0
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        // 2. If nothing normalized yet, check direct $_FILES global superglobal
        if(empty($normalized) && !empty($_FILES))
        {
            if(isset($_FILES['data']['name']['MediaFile']['upload']))
            {
                $rawNames = $_FILES['data']['name']['MediaFile']['upload'];
                if(is_array($rawNames))
                {
                    foreach($rawNames as $k => $name)
                    {
                        if(!empty($name))
                        {
                            $normalized[] = array(
                                'name' => $name,
                                'type' => isset($_FILES['data']['type']['MediaFile']['upload'][$k]) ? $_FILES['data']['type']['MediaFile']['upload'][$k] : '',
                                'tmp_name' => isset($_FILES['data']['tmp_name']['MediaFile']['upload'][$k]) ? $_FILES['data']['tmp_name']['MediaFile']['upload'][$k] : '',
                                'error' => isset($_FILES['data']['error']['MediaFile']['upload'][$k]) ? $_FILES['data']['error']['MediaFile']['upload'][$k] : UPLOAD_ERR_NO_FILE,
                                'size' => isset($_FILES['data']['size']['MediaFile']['upload'][$k]) ? $_FILES['data']['size']['MediaFile']['upload'][$k] : 0
                            );
                        }
                    }
                }
                elseif(!empty($rawNames))
                {
                    $normalized[] = array(
                        'name' => $rawNames,
                        'type' => isset($_FILES['data']['type']['MediaFile']['upload']) ? $_FILES['data']['type']['MediaFile']['upload'] : '',
                        'tmp_name' => isset($_FILES['data']['tmp_name']['MediaFile']['upload']) ? $_FILES['data']['tmp_name']['MediaFile']['upload'] : '',
                        'error' => isset($_FILES['data']['error']['MediaFile']['upload']) ? $_FILES['data']['error']['MediaFile']['upload'] : UPLOAD_ERR_NO_FILE,
                        'size' => isset($_FILES['data']['size']['MediaFile']['upload']) ? $_FILES['data']['size']['MediaFile']['upload'] : 0
                    );
                }
            }
            
            if(empty($normalized))
            {
                foreach($_FILES as $topKey => $fileArr)
                {
                    if(!is_array($fileArr)) continue;
                    if(isset($fileArr['name']) && is_array($fileArr['name']))
                    {
                        foreach($fileArr['name'] as $k => $name)
                        {
                            if(is_array($name))
                            {
                                foreach($name as $subK => $subName)
                                {
                                    if(!empty($subName))
                                    {
                                        $normalized[] = array(
                                            'name' => $subName,
                                            'type' => isset($fileArr['type'][$k][$subK]) ? $fileArr['type'][$k][$subK] : '',
                                            'tmp_name' => isset($fileArr['tmp_name'][$k][$subK]) ? $fileArr['tmp_name'][$k][$subK] : '',
                                            'error' => isset($fileArr['error'][$k][$subK]) ? $fileArr['error'][$k][$subK] : UPLOAD_ERR_NO_FILE,
                                            'size' => isset($fileArr['size'][$k][$subK]) ? $fileArr['size'][$k][$subK] : 0
                                        );
                                    }
                                }
                            }
                            elseif(!empty($name))
                            {
                                $normalized[] = array(
                                    'name' => $name,
                                    'type' => isset($fileArr['type'][$k]) ? $fileArr['type'][$k] : '',
                                    'tmp_name' => isset($fileArr['tmp_name'][$k]) ? $fileArr['tmp_name'][$k] : '',
                                    'error' => isset($fileArr['error'][$k]) ? $fileArr['error'][$k] : UPLOAD_ERR_NO_FILE,
                                    'size' => isset($fileArr['size'][$k]) ? $fileArr['size'][$k] : 0
                                );
                            }
                        }
                    }
                    elseif(isset($fileArr['name']) && !empty($fileArr['name']))
                    {
                        $normalized[] = $fileArr;
                    }
                }
            }
        }

        return $normalized;
    }

    protected function _uploadErrorMessage($code, $fileName = '')
    {
        $fileName = trim((string)$fileName);
        $label = $fileName !== '' ? $fileName.': ' : '';
        $messages = array(
            UPLOAD_ERR_INI_SIZE => __('file is larger than the server upload limit.'),
            UPLOAD_ERR_FORM_SIZE => __('file is larger than the form upload limit.'),
            UPLOAD_ERR_PARTIAL => __('file was only partially uploaded.'),
            UPLOAD_ERR_NO_FILE => __('no file was selected.'),
            UPLOAD_ERR_NO_TMP_DIR => __('server temporary upload folder is missing.'),
            UPLOAD_ERR_CANT_WRITE => __('server could not write the uploaded file.'),
            UPLOAD_ERR_EXTENSION => __('a PHP extension stopped the upload.')
        );
        if(isset($messages[$code]))
        {
            return $label.$messages[$code];
        }
        return $label.__('upload failed.');
    }

    protected function _mediaScopeCondition($alias)
    {
        if($this->_isMediaAdminUser())
        {
            return array();
        }
        $alias = preg_replace('/[^A-Za-z0-9_]/', '', (string)$alias);
        if($alias === '')
        {
            return array();
        }

        if($alias === 'MediaFolder')
        {
            return array('OR' => array(
                array(
                    $alias.'.project_id' => 0,
                    $alias.'.system_key <>' => ''
                ),
                array(
                    $alias.'.project_id' => $this->_mediaAllowedProjectIds(),
                    $alias.'.created_by' => (int)$this->luserId
                )
            ));
        }

        return array('AND' => array(
            array($alias.'.project_id' => $this->_mediaAllowedProjectIds()),
            array('OR' => $this->_mediaOwnerConditions($alias))
        ));
    }

    protected function _canAccessMediaRow($row)
    {
        if($this->_isMediaAdminUser())
        {
            return true;
        }
        return $this->_mediaProjectMatches($row) && $this->_mediaOwnerMatches($row);
    }

    protected function _canAccessFolderRow($row)
    {
        if($this->_isMediaAdminUser())
        {
            return true;
        }
        if(!$this->_mediaProjectMatches($row))
        {
            return false;
        }
        return isset($row['created_by']) && (int)$row['created_by'] === (int)$this->luserId;
    }

    protected function _currentMediaProjectId()
    {
        return (int)$this->agencyId > 0 ? (int)$this->agencyId : 0;
    }

    protected function _isMediaAdminUser()
    {
        return !empty($this->viewVars['isSuperAdminSession']) || (method_exists($this, '_isSuperAdminSession') && $this->_isSuperAdminSession((array)$this->adminValue));
    }

    protected function _mediaAllowedProjectIds()
    {
        $projectIds = array();
        if((int)$this->agencyId > 0)
        {
            $projectIds[] = (int)$this->agencyId;
        }
        else
        {
            $projectIds[] = 0;
        }
        if((int)$this->luserId > 0)
        {
            $projectIds[] = 0;
        }
        return array_values(array_unique(array_map('intval', $projectIds)));
    }

    protected function _mediaOwnerConditions($alias)
    {
        $alias = preg_replace('/[^A-Za-z0-9_]/', '', (string)$alias);
        $userId = (int)$this->luserId;
        if($alias === '')
        {
            return array();
        }
        return array(
            array($alias.'.created_by' => $userId),
            array(
                $alias.'.source_type' => 'user_photo',
                $alias.'.source_id' => $userId
            )
        );
    }

    protected function _mediaProjectMatches($row)
    {
        $projectId = isset($row['project_id']) ? (int)$row['project_id'] : 0;
        return in_array($projectId, $this->_mediaAllowedProjectIds(), true);
    }

    protected function _mediaOwnerMatches($row)
    {
        $userId = (int)$this->luserId;
        if($userId <= 0)
        {
            return false;
        }
        if(isset($row['created_by']) && (int)$row['created_by'] === $userId)
        {
            return true;
        }
        $sourceType = isset($row['source_type']) ? trim((string)$row['source_type']) : '';
        $sourceId = isset($row['source_id']) ? (int)$row['source_id'] : 0;
        return ($sourceType === 'user_photo' && $sourceId === $userId);
    }

    protected function _normalizeMediaFolderId($folderId)
    {
        $folderId = (int)$folderId;
        if($folderId <= 0)
        {
            return 0;
        }
        if($this->_canUseFolderForMedia($folderId))
        {
            return $folderId;
        }
        return 0;
    }

    protected function _canUseFolderForMedia($folderId)
    {
        $folderId = (int)$folderId;
        if($folderId <= 0)
        {
            return true;
        }
        $folder = $this->MediaFolder->find('first', array(
            'conditions' => array('MediaFolder.id' => $folderId, 'MediaFolder.status' => 'Active'),
            'recursive' => -1
        ));
        if(empty($folder['MediaFolder']))
        {
            return false;
        }
        if($this->_isMediaAdminUser())
        {
            return true;
        }
        $row = $folder['MediaFolder'];
        if(isset($row['project_id'], $row['system_key']) && (int)$row['project_id'] === 0 && trim((string)$row['system_key']) !== '')
        {
            return true;
        }
        return $this->_canAccessFolderRow($row);
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

    protected function _columnExists($table, $column)
    {
        $table = preg_replace('/[^A-Za-z0-9_]/', '', (string)$table);
        $column = preg_replace('/[^A-Za-z0-9_]/', '', (string)$column);
        if($table === '' || $column === '')
        {
            return false;
        }
        $rows = $this->MediaFile->query("SHOW COLUMNS FROM `$table` LIKE '".$column."'");
        return !empty($rows);
    }

    protected function _indexExists($table, $index)
    {
        $table = preg_replace('/[^A-Za-z0-9_]/', '', (string)$table);
        $index = preg_replace('/[^A-Za-z0-9_]/', '', (string)$index);
        if($table === '' || $index === '')
        {
            return false;
        }
        $rows = $this->MediaFile->query("SHOW INDEX FROM `$table` WHERE `Key_name`='".$index."'");
        return !empty($rows);
    }

    protected function _splitConfiguredPath($path)
    {
        $path = str_replace('\\', '/', trim((string)$path));
        $path = preg_replace('#^/?img/#i', '', $path);
        $file = basename($path);
        $dir = trim(dirname($path), './');
        if($dir === '.')
        {
            $dir = '';
        }
        return array('dir' => $dir, 'file' => $file);
    }

    protected function _physicalMediaPath($dir, $file)
    {
        $dir = $this->_cleanMediaDir($dir);
        $file = basename((string)$file);
        if($dir === '')
        {
            return WWW_ROOT.'img'.DS.$file;
        }
        if(strpos($dir, 'files/') === 0 || strpos($dir, 'files\\') === 0 || $dir === 'files')
        {
            return WWW_ROOT.str_replace('/', DS, $dir).DS.$file;
        }
        return WWW_ROOT.'img'.DS.str_replace('/', DS, $dir).DS.$file;
    }

    protected function _publicMediaPath($dir, $file)
    {
        $dir = $this->_cleanMediaDir($dir);
        $file = basename((string)$file);
        if($dir === '')
        {
            return $file;
        }
        return trim($dir, '/').'/'.$file;
    }

    protected function _cleanMediaDir($dir)
    {
        $dir = str_replace('\\', '/', trim((string)$dir));
        $dir = preg_replace('#^/?img/#i', '', $dir);
        $dir = preg_replace('/[^A-Za-z0-9_\/\-]/', '', $dir);
        $dir = trim($dir, '/');
        if(strpos($dir, '..') !== false)
        {
            $dir = '';
        }
        return $dir;
    }

    protected function _allowedExtensions()
    {
        return array(
            // Images
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'web', 'bmp', 'svg', 'ico', 'tiff', 'avif',
            // Documents
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf', 'odt', 'ods', 'odp',
            // Videos
            'mp4', 'webm', 'mov', 'mkv', 'avi', 'wmv', 'm4v', 'flv', '3gp', 'ogv', 'ts',
            // Audio
            'mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac',
            // Archives
            'zip', 'rar', '7z', 'tar', 'gz'
        );
    }

    protected function _imageExtensions()
    {
        return array('jpg', 'jpeg', 'png', 'gif', 'webp', 'web', 'bmp', 'svg', 'ico', 'tiff', 'avif');
    }

    protected function _fileTypeByExtension($extension)
    {
        $extension = strtolower((string)$extension);
        if(in_array($extension, $this->_imageExtensions(), true))
        {
            return 'image';
        }
        if(in_array($extension, array('mp4', 'webm', 'mov', 'mkv', 'avi', 'wmv', 'm4v', 'flv', '3gp', 'ogv', 'ts'), true))
        {
            return 'video';
        }
        if(in_array($extension, array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf', 'odt', 'ods', 'odp'), true))
        {
            return 'document';
        }
        if(in_array($extension, array('mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac'), true))
        {
            return 'audio';
        }
        return 'other';
    }

    protected function _detectMimeType($path, $extension = '')
    {
        if(function_exists('finfo_open') && is_file($path))
        {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if($finfo)
            {
                $mime = finfo_file($finfo, $path);
                finfo_close($finfo);
                if($mime)
                {
                    return $mime;
                }
            }
        }
        $map = array(
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'web' => 'image/webp',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'avif' => 'image/avif',
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
            'mkv' => 'video/x-matroska',
            'avi' => 'video/x-msvideo',
            'wmv' => 'video/x-ms-wmv',
            'm4v' => 'video/x-m4v',
            'flv' => 'video/x-flv',
            '3gp' => 'video/3gpp',
            'ogv' => 'video/ogg',
            'ts' => 'video/mp2t',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'm4a' => 'audio/mp4',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'rtf' => 'application/rtf',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed'
        );
        $extension = strtolower((string)$extension);
        return isset($map[$extension]) ? $map[$extension] : 'application/octet-stream';
    }

    protected function _fileIconClass($file)
    {
        $extension = !empty($file['extension']) ? strtolower($file['extension']) : strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
        $fileType = !empty($file['file_type']) ? $file['file_type'] : '';
        if($fileType === 'video' || in_array($extension, array('mp4','webm','mov','mkv','avi','wmv','m4v','flv','3gp','ogv','ts'), true))
        {
            return 'fa fa-file-video-o';
        }
        if($fileType === 'image')
        {
            return 'fa fa-file-image-o';
        }
        if($extension === 'pdf')
        {
            return 'fa fa-file-pdf-o';
        }
        if(in_array($extension, array('doc', 'docx', 'odt', 'rtf'), true))
        {
            return 'fa fa-file-word-o';
        }
        if(in_array($extension, array('xls', 'xlsx', 'csv', 'ods'), true))
        {
            return 'fa fa-file-excel-o';
        }
        if(in_array($extension, array('ppt', 'pptx', 'odp'), true))
        {
            return 'fa fa-file-powerpoint-o';
        }
        if($fileType === 'audio' || in_array($extension, array('mp3', 'wav', 'ogg', 'm4a'), true))
        {
            return 'fa fa-file-audio-o';
        }
        if(in_array($extension, array('zip', 'rar', '7z', 'tar', 'gz'), true))
        {
            return 'fa fa-file-archive-o';
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
