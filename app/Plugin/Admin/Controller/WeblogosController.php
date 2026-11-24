<?php
App::uses('Router', 'Routing');

class WeblogosController extends AdminAppController {
    public $helpers = array('Html','Form','Session');
    public $components = array('Session','search-master.Prg');

    public function beforeFilter()
    {
        parent::beforeFilter();
        if($this->_shouldRunControllerBootstrap('weblogos.schema', '2026-04-09.2'))
        {
            $this->_ensurePortalEmbedColumns();
        }
    }

    protected function _portalEmbedDefaults()
    {
        return array(
            'client_portal_embed_width' => '100%',
            'client_portal_embed_height' => 740,
            'client_portal_button_label' => 'Client Portal Login',
            'client_portal_open_target' => '_blank'
        );
    }

    protected function _normalizePortalEmbedSettings($settings)
    {
        $settings = (array)$settings;
        $defaults = $this->_portalEmbedDefaults();
        $normalized = array(
            'client_portal_embed_width' => isset($settings['client_portal_embed_width']) ? trim((string)$settings['client_portal_embed_width']) : '',
            'client_portal_embed_height' => isset($settings['client_portal_embed_height']) ? (int)$settings['client_portal_embed_height'] : 0,
            'client_portal_button_label' => isset($settings['client_portal_button_label']) ? trim((string)$settings['client_portal_button_label']) : '',
            'client_portal_open_target' => isset($settings['client_portal_open_target']) ? trim((string)$settings['client_portal_open_target']) : ''
        );

        if($normalized['client_portal_embed_width'] === '')
        {
            $normalized['client_portal_embed_width'] = $defaults['client_portal_embed_width'];
        }
        if(strlen($normalized['client_portal_embed_width']) > 20)
        {
            $normalized['client_portal_embed_width'] = substr($normalized['client_portal_embed_width'], 0, 20);
        }

        if($normalized['client_portal_embed_height'] < 300)
        {
            $normalized['client_portal_embed_height'] = (int)$defaults['client_portal_embed_height'];
        }
        if($normalized['client_portal_embed_height'] > 2000)
        {
            $normalized['client_portal_embed_height'] = 2000;
        }

        if($normalized['client_portal_button_label'] === '')
        {
            $normalized['client_portal_button_label'] = $defaults['client_portal_button_label'];
        }
        if(strlen($normalized['client_portal_button_label']) > 120)
        {
            $normalized['client_portal_button_label'] = substr($normalized['client_portal_button_label'], 0, 120);
        }

        if(!in_array($normalized['client_portal_open_target'], array('_self','_blank'), true))
        {
            $normalized['client_portal_open_target'] = $defaults['client_portal_open_target'];
        }

        return $normalized;
    }

    protected function _portalLoginUrl()
    {
        return Router::url(array('plugin' => false, 'admin' => false, 'controller' => 'Clients', 'action' => 'login'), true);
    }

    protected function _ensurePortalEmbedColumns()
    {
        try
        {
            $schema = (array)$this->Weblogo->query("SHOW COLUMNS FROM `configurations`");
        }
        catch (Exception $e)
        {
            $schema = array();
        }
        if(empty($schema))
        {
            return;
        }
        $columnSet = array();
        foreach($schema as $row)
        {
            if(!empty($row['COLUMNS']['Field']))
            {
                $columnSet[(string)$row['COLUMNS']['Field']] = true;
            }
            elseif(!empty($row[0]['Field']))
            {
                $columnSet[(string)$row[0]['Field']] = true;
            }
            elseif(!empty($row['Field']))
            {
                $columnSet[(string)$row['Field']] = true;
            }
        }

        if(empty($columnSet['client_portal_embed_width']))
        {
            try
            {
                $this->Weblogo->query("ALTER TABLE `configurations` ADD COLUMN `client_portal_embed_width` varchar(20) DEFAULT '100%' AFTER `photo`");
            }
            catch (Exception $e) {}
        }
        if(empty($columnSet['client_portal_embed_height']))
        {
            try
            {
                $this->Weblogo->query("ALTER TABLE `configurations` ADD COLUMN `client_portal_embed_height` int(11) NOT NULL DEFAULT 740 AFTER `client_portal_embed_width`");
            }
            catch (Exception $e) {}
        }
        if(empty($columnSet['client_portal_button_label']))
        {
            try
            {
                $this->Weblogo->query("ALTER TABLE `configurations` ADD COLUMN `client_portal_button_label` varchar(120) DEFAULT 'Client Portal Login' AFTER `client_portal_embed_height`");
            }
            catch (Exception $e) {}
        }
        if(empty($columnSet['client_portal_open_target']))
        {
            try
            {
                $this->Weblogo->query("ALTER TABLE `configurations` ADD COLUMN `client_portal_open_target` varchar(10) DEFAULT '_blank' AFTER `client_portal_button_label`");
            }
            catch (Exception $e) {}
        }
        try
        {
            $this->Weblogo->schema(true);
        }
        catch (Exception $e) {}
    }

    public function index()
    {
        $row = $this->Weblogo->find('first', array(
            'conditions' => array('Weblogo.id' => 1),
            'recursive' => -1
        ));
        if(empty($row['Weblogo']))
        {
            $row = array('Weblogo' => array('id' => 1));
        }
        $defaults = $this->_portalEmbedDefaults();
        $currentSettings = array_merge($defaults, (array)$row['Weblogo']);
        $currentSettings = array_merge($currentSettings, $this->_normalizePortalEmbedSettings($currentSettings));

        if ($this->request->is(array('post','put')))
        {
            try
            {
                $saveData = (array)$this->request->data;
                if(empty($saveData['Weblogo']))
                {
                    $saveData['Weblogo'] = array();
                }
                foreach(array('gallery_media_id','logo_data','logo_name') as $logoField)
                {
                    if(empty($saveData['Weblogo'][$logoField]) && isset($saveData[$logoField]))
                    {
                        $saveData['Weblogo'][$logoField] = $saveData[$logoField];
                    }
                }
                if(empty($saveData['Weblogo']['photo']) && isset($_FILES['data']['name']['Weblogo']['photo']))
                {
                    $saveData['Weblogo']['photo'] = array(
                        'name' => $_FILES['data']['name']['Weblogo']['photo'],
                        'type' => isset($_FILES['data']['type']['Weblogo']['photo']) ? $_FILES['data']['type']['Weblogo']['photo'] : '',
                        'tmp_name' => isset($_FILES['data']['tmp_name']['Weblogo']['photo']) ? $_FILES['data']['tmp_name']['Weblogo']['photo'] : '',
                        'error' => isset($_FILES['data']['error']['Weblogo']['photo']) ? $_FILES['data']['error']['Weblogo']['photo'] : UPLOAD_ERR_NO_FILE,
                        'size' => isset($_FILES['data']['size']['Weblogo']['photo']) ? $_FILES['data']['size']['Weblogo']['photo'] : 0
                    );
                }
                $normalizedPortal = $this->_normalizePortalEmbedSettings($saveData['Weblogo']);
                $saveData['Weblogo'] = array_merge((array)$saveData['Weblogo'], $normalizedPortal);
                $saveData['Weblogo']['id'] = 1;
                $oldLogo = !empty($currentSettings['photo']) ? (string)$currentSettings['photo'] : '';
                $logoResult = $this->_prepareLogoUpload($saveData['Weblogo'], $oldLogo);
                if(empty($logoResult['success']))
                {
                    $this->Session->setFlash($logoResult['message'], 'flash', array('alert'=>'danger'));
                    return $this->redirect(array('action' => 'index'));
                }

                if ($this->_saveWeblogoSettings($saveData['Weblogo']))
                {
                    if(!empty($logoResult['new_file']) && $oldLogo !== '' && $oldLogo !== $logoResult['new_file'])
                    {
                        $this->_deleteLogoFile($oldLogo);
                    }
                    $this->Session->setFlash(__('Logo and client portal embed settings have been saved.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'index'));
                }
                $this->Session->setFlash(__('Logo could not be saved. Please try again.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('File type mismatch'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
        }

        $portalLoginUrl = $this->_portalLoginUrl();
        $portalButtonLabel = trim((string)$currentSettings['client_portal_button_label']);
        $portalEmbedWidth = trim((string)$currentSettings['client_portal_embed_width']);
        $portalEmbedHeight = (int)$currentSettings['client_portal_embed_height'];
        $portalOpenTarget = trim((string)$currentSettings['client_portal_open_target']);

        $shortcode = '[wv_client_portal_login url="'.$portalLoginUrl.'" width="'.$portalEmbedWidth.'" height="'.(int)$portalEmbedHeight.'" label="'.$portalButtonLabel.'" target="'.$portalOpenTarget.'"]';
        $embedCode = '<iframe src="'.$portalLoginUrl.'" title="'.$portalButtonLabel.'" width="'.$portalEmbedWidth.'" height="'.(int)$portalEmbedHeight.'" style="border:0;max-width:100%;" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>';
        $inPageCode = '<a href="'.$portalLoginUrl.'" target="'.$portalOpenTarget.'" rel="noopener noreferrer">'.$portalButtonLabel.'</a>';

        $postedWeblogo = isset($this->request->data['Weblogo']) ? (array)$this->request->data['Weblogo'] : array();
        $this->request->data['Weblogo'] = array_merge((array)$currentSettings, $postedWeblogo);
        $this->set('clientPortalLoginUrl', $portalLoginUrl);
        $this->set('clientPortalShortcode', $shortcode);
        $this->set('clientPortalEmbedCode', $embedCode);
        $this->set('clientPortalInPageCode', $inPageCode);
    }

    protected function _saveWeblogoSettings($settings)
    {
        $settings = (array)$settings;
        $fields = array(
            'client_portal_embed_width' => isset($settings['client_portal_embed_width']) ? $settings['client_portal_embed_width'] : '100%',
            'client_portal_embed_height' => isset($settings['client_portal_embed_height']) ? (int)$settings['client_portal_embed_height'] : 740,
            'client_portal_button_label' => isset($settings['client_portal_button_label']) ? $settings['client_portal_button_label'] : 'Client Portal Login',
            'client_portal_open_target' => isset($settings['client_portal_open_target']) ? $settings['client_portal_open_target'] : '_blank'
        );
        if(isset($settings['photo']) && !is_array($settings['photo']) && trim((string)$settings['photo']) !== '')
        {
            $fields['photo'] = basename((string)$settings['photo']);
        }
        if($this->Weblogo->hasField('modified'))
        {
            $fields['modified'] = date('Y-m-d H:i:s');
        }

        $ds = $this->Weblogo->getDataSource();
        $setSql = array();
        foreach($fields as $field => $value)
        {
            $setSql[] = '`'.$field.'`='.$ds->value($value);
        }
        if(empty($setSql))
        {
            return false;
        }
        try
        {
            $this->Weblogo->query("UPDATE `configurations` SET ".implode(',', $setSql)." WHERE `id`=1");
            return true;
        }
        catch(Exception $e)
        {
            return false;
        }
    }

    protected function _prepareLogoUpload(&$weblogoData, $oldLogo = '')
    {
        $weblogoData = (array)$weblogoData;
        $galleryMediaId = !empty($weblogoData['gallery_media_id']) ? (int)$weblogoData['gallery_media_id'] : 0;
        if(empty($weblogoData['photo']) || !is_array($weblogoData['photo']))
        {
            $galleryError = null;
            if($galleryMediaId > 0)
            {
                $galleryResult = $this->_prepareLogoFromGallery($weblogoData, $galleryMediaId);
                if(!empty($galleryResult['success']))
                {
                    return $galleryResult;
                }
                $galleryError = $galleryResult;
            }
            $dataResult = $this->_prepareLogoFromData($weblogoData);
            if($dataResult !== null)
            {
                return $dataResult;
            }
            if($galleryError !== null)
            {
                return $galleryError;
            }
            unset($weblogoData['photo']);
            return array('success' => true, 'new_file' => '');
        }

        $upload = (array)$weblogoData['photo'];
        $error = isset($upload['error']) ? (int)$upload['error'] : UPLOAD_ERR_NO_FILE;
        if(in_array($error, array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE), true))
        {
            unset($weblogoData['photo']);
            return array('success' => false, 'message' => __('Logo file is too large. Please choose a smaller JPG or PNG image.'));
        }
        if($error === UPLOAD_ERR_NO_FILE)
        {
            $galleryError = null;
            if($galleryMediaId > 0)
            {
                $galleryResult = $this->_prepareLogoFromGallery($weblogoData, $galleryMediaId);
                if(!empty($galleryResult['success']))
                {
                    return $galleryResult;
                }
                $galleryError = $galleryResult;
            }
            $dataResult = $this->_prepareLogoFromData($weblogoData);
            if($dataResult !== null)
            {
                return $dataResult;
            }
            if($galleryError !== null)
            {
                return $galleryError;
            }
            unset($weblogoData['photo']);
            return array('success' => true, 'new_file' => '');
        }
        if($error !== UPLOAD_ERR_OK || empty($upload['tmp_name']) || !is_file($upload['tmp_name']))
        {
            $dataResult = $this->_prepareLogoFromData($weblogoData);
            if($dataResult !== null)
            {
                return $dataResult;
            }
            unset($weblogoData['photo']);
            return array('success' => false, 'message' => __('Logo upload failed. Please select the image again.'));
        }

        $extension = strtolower(pathinfo(isset($upload['name']) ? $upload['name'] : '', PATHINFO_EXTENSION));
        if($extension === 'jpeg')
        {
            $extension = 'jpg';
        }
        if(!in_array($extension, array('jpg','png'), true))
        {
            unset($weblogoData['photo']);
            return array('success' => false, 'message' => __('Please upload JPG or PNG logo only.'));
        }

        $info = @getimagesize($upload['tmp_name']);
        if(empty($info[0]) || empty($info[1]))
        {
            unset($weblogoData['photo']);
            return array('success' => false, 'message' => __('Selected logo is not a valid image.'));
        }

        $targetName = 'organisation-logo-'.date('YmdHis').'-'.substr(md5(uniqid('', true)), 0, 6).'.'.$extension;
        $targetPath = APP.WEBROOT_DIR.DS.'img'.DS.$targetName;
        if(!$this->_saveLogoImage($upload['tmp_name'], $targetPath, $extension, 220))
        {
            $dataResult = $this->_prepareLogoFromData($weblogoData);
            if($dataResult !== null)
            {
                return $dataResult;
            }
            unset($weblogoData['photo']);
            return array('success' => false, 'message' => __('Logo image could not be prepared. Please try another JPG or PNG file.'));
        }

        $weblogoData['photo'] = $targetName;
        return array('success' => true, 'new_file' => $targetName);
    }

    protected function _prepareLogoFromData(&$weblogoData)
    {
        $dataUrl = !empty($weblogoData['logo_data']) ? trim((string)$weblogoData['logo_data']) : '';
        if($dataUrl === '')
        {
            return null;
        }
        if(!preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $dataUrl, $match))
        {
            return array('success' => false, 'message' => __('Selected logo preview data was not valid.'));
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
            return array('success' => false, 'message' => __('Selected logo preview data was empty.'));
        }
        $info = @getimagesizefromstring($binary);
        if(empty($info[0]) || empty($info[1]))
        {
            return array('success' => false, 'message' => __('Selected logo preview is not a valid image.'));
        }
        $tmpFile = TMP.'weblogo-'.date('YmdHis').'-'.substr(md5(uniqid('', true)), 0, 6).'.'.$extension;
        if(@file_put_contents($tmpFile, $binary) === false)
        {
            return array('success' => false, 'message' => __('Logo preview could not be prepared.'));
        }
        $targetName = 'organisation-logo-'.date('YmdHis').'-'.substr(md5(uniqid('', true)), 0, 6).'.'.$extension;
        $targetPath = APP.WEBROOT_DIR.DS.'img'.DS.$targetName;
        $saved = $this->_saveLogoImage($tmpFile, $targetPath, $extension, 220);
        @unlink($tmpFile);
        if(!$saved)
        {
            return array('success' => false, 'message' => __('Logo image could not be saved from preview.'));
        }
        $weblogoData['photo'] = $targetName;
        return array('success' => true, 'new_file' => $targetName);
    }

    protected function _prepareLogoFromGallery(&$weblogoData, $mediaId)
    {
        unset($weblogoData['photo']);
        $mediaId = (int)$mediaId;
        if($mediaId <= 0)
        {
            return array('success' => true, 'new_file' => '');
        }
        $MediaFile = ClassRegistry::init('MediaFile');
        $media = $MediaFile->find('first', array(
            'conditions' => array('MediaFile.id' => $mediaId, 'MediaFile.status' => 'Active'),
            'recursive' => -1
        ));
        if(empty($media['MediaFile']))
        {
            return array('success' => false, 'message' => __('Selected gallery logo was not found.'));
        }
        $m = $media['MediaFile'];
        $projectId = isset($m['project_id']) ? (int)$m['project_id'] : 0;
        $isSuper = (!empty($this->viewVars['isSuperAdminSession']) || (method_exists($this, '_isSuperAdminSession') && $this->_isSuperAdminSession((array)$this->adminValue)));
        if(!$isSuper && $projectId > 0 && (int)$this->agencyId > 0 && $projectId !== (int)$this->agencyId)
        {
            return array('success' => false, 'message' => __('Selected gallery logo is not available for this agency.'));
        }
        $fileName = !empty($m['file_name']) ? basename((string)$m['file_name']) : '';
        $dir = !empty($m['dir']) ? trim((string)$m['dir']) : '';
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if($extension === 'jpeg')
        {
            $extension = 'jpg';
        }
        if($fileName === '' || !in_array($extension, array('jpg','png'), true))
        {
            return array('success' => false, 'message' => __('Please select JPG or PNG logo from gallery.'));
        }
        $dir = str_replace('\\', '/', $dir);
        $dir = preg_replace('#^/?img/#i', '', $dir);
        $dir = preg_replace('/[^A-Za-z0-9_\/\-]/', '', $dir);
        $dir = trim($dir, '/');
        if(strpos($dir, '..') !== false)
        {
            $dir = '';
        }
        $sourcePath = APP.WEBROOT_DIR.DS.'img'.DS.($dir !== '' ? str_replace('/', DS, $dir).DS : '').$fileName;
        if(!is_file($sourcePath))
        {
            return array('success' => false, 'message' => __('Selected gallery logo file is missing.'));
        }
        $targetName = 'organisation-logo-'.date('YmdHis').'-'.substr(md5(uniqid('', true)), 0, 6).'.'.$extension;
        $targetPath = APP.WEBROOT_DIR.DS.'img'.DS.$targetName;
        if(!$this->_saveLogoImage($sourcePath, $targetPath, $extension, 220))
        {
            return array('success' => false, 'message' => __('Gallery logo could not be prepared. Please try another JPG or PNG file.'));
        }
        $weblogoData['photo'] = $targetName;
        return array('success' => true, 'new_file' => $targetName);
    }

    protected function _saveLogoImage($sourcePath, $targetPath, $extension, $maxHeight = 220)
    {
        if(!function_exists('imagecreatetruecolor') || ($extension === 'png' && !function_exists('imagecreatefrompng')) || ($extension !== 'png' && !function_exists('imagecreatefromjpeg')))
        {
            return @copy($sourcePath, $targetPath);
        }
        $info = @getimagesize($sourcePath);
        if(empty($info[0]) || empty($info[1]))
        {
            return false;
        }
        $width = (int)$info[0];
        $height = (int)$info[1];
        $newWidth = $width;
        $newHeight = $height;
        if($height > (int)$maxHeight)
        {
            $ratio = (int)$maxHeight / $height;
            $newHeight = (int)$maxHeight;
            $newWidth = max(1, (int)round($width * $ratio));
        }

        if($extension === 'png')
        {
            $source = @imagecreatefrompng($sourcePath);
        }
        else
        {
            $source = @imagecreatefromjpeg($sourcePath);
        }
        if(!$source)
        {
            return false;
        }

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        if($extension === 'png')
        {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
            imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);
        }
        else
        {
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $white);
        }

        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        $saved = $extension === 'png' ? imagepng($canvas, $targetPath, 8) : imagejpeg($canvas, $targetPath, 90);
        imagedestroy($source);
        imagedestroy($canvas);

        return $saved && is_file($targetPath);
    }

    protected function _deleteLogoFile($fileName)
    {
        $fileName = basename((string)$fileName);
        if($fileName === '')
        {
            return;
        }
        $file = APP.WEBROOT_DIR.DS.'img'.DS.$fileName;
        if(is_file($file))
        {
            @unlink($file);
        }
    }

    public function weblogodel()
    {
        $post=$this->Weblogo->findById(1);
        $userResult=array('id'=>1,'photo'=>'');
        $logoFile = !empty($post['Weblogo']['photo']) ? basename((string)$post['Weblogo']['photo']) : '';
        $file = $logoFile !== '' ? APP.WEBROOT_DIR.DS.'img'.DS.$logoFile : '';
        try
        {
            if($this->Weblogo->save($userResult))
            {
                if($file !== '' && is_file($file))
                {
                    unlink($file);
                }
                $this->Session->setFlash(__('Your logo has been deleted.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            else
            {
                $this->Session->setFlash(__('Something wrong.!'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Something wrong.!'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
    }
}
?>
