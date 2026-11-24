<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingReadingsController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingReading',
        'BillingManager.BillingMeter',
        'BillingManager.BillingProperty',
        'BillingManager.BillingClient',
        'BillingManager.BillingSociety',
        'BillingManager.BillingSetting'
    );

    public function index()
    {
        $conditions = array();
        if(!empty($this->request->query['society_id']))
        {
            $conditions['BillingReading.society_id'] = (int)$this->request->query['society_id'];
        }
        if(!empty($this->request->query['month']))
        {
            $conditions['BillingReading.reading_month'] = $this->request->query['month'];
        }
        if(!empty($this->request->query['property_id']))
        {
            $conditions['BillingReading.billing_property_id'] = (int)$this->request->query['property_id'];
        }

        // Export CSV handler
        if(!empty($this->request->query['export']) && $this->request->query['export'] === 'csv')
        {
            $this->_exportReadingsCsv($conditions);
            return;
        }

        $this->paginate = array(
            'conditions' => $conditions,
            'order' => array('BillingReading.reading_date' => 'DESC', 'BillingReading.id' => 'DESC'),
            'limit' => 30,
            'contain' => array('BillingMeter', 'BillingProperty' => array('BillingClient'))
        );

        $readings = $this->paginate('BillingReading');
        $properties = $this->BillingProperty->find('list', array('fields' => array('id', 'title'), 'order' => array('title' => 'ASC')));
        $societies = $this->BillingSociety->find('list', array('fields' => array('id', 'name')));
        $allMeters = $this->BillingMeter->find('all', array(
            'contain' => array('BillingProperty' => array('BillingClient')),
            'order' => array('BillingMeter.id' => 'ASC')
        ));

        // Direct Property to Client lookup mapping
        $clientsList = $this->BillingClient->find('all');
        $clientById = array();
        foreach($clientsList as $cl) {
            $clientById[$cl['BillingClient']['id']] = $cl['BillingClient'];
        }

        $propsList = $this->BillingProperty->find('all');
        $propToClient = array();
        foreach($propsList as $pr) {
            $pId = (int)$pr['BillingProperty']['id'];
            $cId = !empty($pr['BillingProperty']['billing_client_id']) ? (int)$pr['BillingProperty']['billing_client_id'] : 0;
            if($cId && isset($clientById[$cId])) {
                $propToClient[$pId] = $clientById[$cId];
            }
        }

        $this->set(compact('readings', 'properties', 'societies', 'allMeters', 'propToClient'));
    }

    public function add()
    {
        return $this->save_single();
    }

    public function save_single()
    {
        if($this->request->is('post'))
        {
            $data = $this->request->data['BillingReading'];
            $meterId = (int)$data['meter_id'];
            $meter = $this->BillingMeter->findById($meterId);
            if(empty($meter))
            {
                $this->Session->setFlash(__('Meter not found.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'index'));
            }

            $id = !empty($data['id']) ? (int)$data['id'] : null;
            if($id) {
                $this->BillingReading->id = $id;
            } else {
                $this->BillingReading->create();
            }

            $prev = (float)$data['previous_reading'];
            $curr = (float)$data['current_reading'];
            $units = max(0.0, $curr - $prev);
            $month = !empty($data['reading_month']) ? trim($data['reading_month']) : date('Y-m');
            $socId = !empty($meter['BillingMeter']['society_id']) ? (int)$meter['BillingMeter']['society_id'] : 1;

            $anomaly = $this->_detectAnomaly($meterId, $curr, $prev, $socId);

            $exportReading = isset($data['export_reading']) ? (float)$data['export_reading'] : 0.0;
            $exportUnits = isset($data['export_units']) ? (float)$data['export_units'] : max(0.0, $exportReading);

            $readingData = array(
                'society_id' => $socId,
                'meter_id' => $meterId,
                'billing_property_id' => $meter['BillingMeter']['billing_property_id'],
                'reading_date' => !empty($data['reading_date']) ? $data['reading_date'] : date('Y-m-d'),
                'reading_month' => $month,
                'previous_reading' => $prev,
                'current_reading' => $curr,
                'units_consumed' => $units,
                'export_reading' => $exportReading,
                'export_units' => $exportUnits,
                'anomaly_flag' => $anomaly['flag'],
                'photo_url' => !empty($data['photo_url']) ? trim($data['photo_url']) : '',
                'reader_name' => !empty($data['reader_name']) ? $data['reader_name'] : 'Admin',
                'is_billed' => 0
            );

            if($this->BillingReading->save($readingData))
            {
                $this->BillingMeter->id = $meterId;
                $this->BillingMeter->saveField('current_reading', $curr);
                
                if($anomaly['flag'] !== 'NORMAL') {
                    $this->Session->setFlash(__('Reading saved with Anomaly Flag [') . $anomaly['flag'] . ']: ' . $anomaly['message'], 'flash', array('alert' => 'warning'));
                } else {
                    $this->Session->setFlash(__('Single meter reading recorded successfully.'), 'flash', array('alert' => 'success'));
                }
                return $this->redirect(array('action' => 'index'));
            }
            else
            {
                $this->Session->setFlash(__('Could not save reading.'), 'flash', array('alert' => 'danger'));
            }
        }

        return $this->redirect(array('action' => 'index'));
    }

    public function delete($id = null)
    {
        $this->BillingReading->delete((int)$id);
        $this->Session->setFlash(__('Reading deleted.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function upload_meter_photo()
    {
        $this->autoRender = false;
        header('Content-Type: application/json');

        if(!$this->request->is('post'))
        {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid request method.'));
            exit;
        }

        $base64Data = !empty($this->request->data['image_data']) ? $this->request->data['image_data'] : '';
        $meterNumber = !empty($this->request->data['meter_number']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $this->request->data['meter_number']) : 'METER';

        // Target Directory: app/webroot/img/Reading/
        $webrootImg = WWW_ROOT . 'img' . DS . 'Reading';
        if(!is_dir($webrootImg))
        {
            @mkdir($webrootImg, 0777, true);
        }

        $fileName = 'reading_' . $meterNumber . '_' . date('Ymd_His') . '_' . rand(100, 999) . '.jpg';
        $targetPath = $webrootImg . DS . $fileName;
        $urlPath = '/whitevillas/img/Reading/' . $fileName;

        if(!empty($base64Data))
        {
            if(strpos($base64Data, ',') !== false)
            {
                $parts = explode(',', $base64Data);
                $rawImg = base64_decode($parts[1]);
            }
            else
            {
                $rawImg = base64_decode($base64Data);
            }

            if(file_put_contents($targetPath, $rawImg) === false)
            {
                echo json_encode(array('status' => 'error', 'message' => 'Failed to save cropped image to disk.'));
                exit;
            }
        }
        elseif(!empty($_FILES['image_file']['tmp_name']) && is_uploaded_file($_FILES['image_file']['tmp_name']))
        {
            if(!move_uploaded_file($_FILES['image_file']['tmp_name'], $targetPath))
            {
                echo json_encode(array('status' => 'error', 'message' => 'Failed to move uploaded file.'));
                exit;
            }
        }
        else
        {
            echo json_encode(array('status' => 'error', 'message' => 'No image data received.'));
            exit;
        }

        $fileSize = file_exists($targetPath) ? filesize($targetPath) : 0;

        // Ensure "Reading" folder exists in media_folders table
        try {
            $db = ConnectionManager::getDataSource('default');
            $folderRows = $db->fetchAll("SELECT id FROM media_folders WHERE name = 'Reading' OR slug = 'reading' LIMIT 1");
            $folderId = 0;
            if(!empty($folderRows[0]['media_folders']['id'])) {
                $folderId = (int)$folderRows[0]['media_folders']['id'];
            } elseif(!empty($folderRows[0]['id'])) {
                $folderId = (int)$folderRows[0]['id'];
            } else {
                $db->execute("INSERT INTO media_folders (name, slug, system_key, visibility, status, sort_order, created, modified) 
                              VALUES ('Reading', 'reading', 'meter_readings', 'both', 'Active', 10, NOW(), NOW())");
                $newFolder = $db->fetchAll("SELECT LAST_INSERT_ID() as id");
                $folderId = !empty($newFolder[0][0]['id']) ? (int)$newFolder[0][0]['id'] : 1;
            }

            // Save record in media_files table so it appears in the Media Gallery under folder "Reading"
            $db->execute("INSERT INTO media_files (folder_id, source_type, title, file_name, original_name, dir, file_path, url_path, mime_type, file_type, extension, file_size, visibility, status, created, modified)
                          VALUES ({$folderId}, 'meter_reading', 'Meter Reading Photo - {$meterNumber}', '{$fileName}', '{$fileName}', 'Reading', 'Reading/{$fileName}', 'Reading/{$fileName}', 'image/jpeg', 'image', 'jpg', {$fileSize}, 'both', 'Active', NOW(), NOW())");

        } catch(Exception $e) {}

        echo json_encode(array(
            'status' => 'success',
            'url' => $urlPath,
            'file_name' => $fileName,
            'message' => 'Meter image cropped and saved to Media Gallery under "Reading" folder.'
        ));
        exit;
    }

    public function bulk_entry()
    {
        $month = !empty($this->request->query['month']) ? trim($this->request->query['month']) : date('Y-m');

        if($this->request->is(array('post', 'put')))
        {
            $month = trim($this->request->data['BillingReading']['reading_month']);
            $date = !empty($this->request->data['BillingReading']['reading_date']) ? $this->request->data['BillingReading']['reading_date'] : date('Y-m-d');
            $readerName = !empty($this->request->data['BillingReading']['reader_name']) ? trim($this->request->data['BillingReading']['reader_name']) : 'Admin';

            $rows = isset($this->request->data['ReadingRow']) ? $this->request->data['ReadingRow'] : array();
            $savedCount = 0;
            $anomalyCount = 0;

            foreach($rows as $meterId => $row)
            {
                if(!isset($row['current_reading']) || trim((string)$row['current_reading']) === '')
                {
                    continue;
                }

                $meterId = (int)$meterId;
                $propId = (int)$row['property_id'];
                $prev = (float)$row['previous_reading'];
                $curr = (float)$row['current_reading'];
                $units = max(0.0, $curr - $prev);

                $meterRec = $this->BillingMeter->findById($meterId);
                $socId = !empty($meterRec['BillingMeter']['society_id']) ? (int)$meterRec['BillingMeter']['society_id'] : 1;

                $existing = $this->BillingReading->find('first', array(
                    'conditions' => array(
                        'BillingReading.meter_id' => $meterId,
                        'BillingReading.reading_month' => $month
                    )
                ));

                $anomaly = $this->_detectAnomaly($meterId, $curr, $prev, $socId);
                if($anomaly['flag'] !== 'NORMAL') {
                    $anomalyCount++;
                }

                $exportReading = isset($row['export_reading']) ? (float)$row['export_reading'] : 0.0;
                $exportUnits = isset($row['export_units']) ? (float)$row['export_units'] : max(0.0, $exportReading);

                $readingData = array(
                    'society_id' => $socId,
                    'meter_id' => $meterId,
                    'billing_property_id' => $propId,
                    'reading_date' => $date,
                    'reading_month' => $month,
                    'previous_reading' => $prev,
                    'current_reading' => $curr,
                    'units_consumed' => $units,
                    'export_reading' => $exportReading,
                    'export_units' => $exportUnits,
                    'anomaly_flag' => $anomaly['flag'],
                    'reader_name' => $readerName,
                    'is_billed' => 0
                );

                if(!empty($existing['BillingReading']['id']))
                {
                    $readingData['id'] = (int)$existing['BillingReading']['id'];
                }
                else
                {
                    $this->BillingReading->create();
                }

                if($this->BillingReading->save(array('BillingReading' => $readingData)))
                {
                    $this->BillingMeter->id = $meterId;
                    $this->BillingMeter->saveField('current_reading', $curr);
                    $savedCount++;
                }
            }

            if($anomalyCount > 0) {
                $this->Session->setFlash(sprintf(__('Saved %d meter readings for period %s. Note: %d reading(s) were flagged by Anomaly Guard for review!'), $savedCount, h($month), $anomalyCount), 'flash', array('alert' => 'warning'));
            } else {
                $this->Session->setFlash(sprintf(__('Saved %d meter readings for period %s.'), $savedCount, h($month)), 'flash', array('alert' => 'success'));
            }
            return $this->redirect(array('action' => 'index', '?' => array('month' => $month)));
        }

        $meters = $this->BillingMeter->find('all', array(
            'conditions' => array('BillingMeter.status' => 'active'),
            'contain' => array('BillingProperty' => array('BillingClient')),
            'order' => array('BillingMeter.id' => 'ASC')
        ));

        $meterRows = array();
        foreach($meters as $m)
        {
            $meterId = (int)$m['BillingMeter']['id'];
            
            // Check if a reading record already exists for the selected month
            $currReadingRow = $this->BillingReading->find('first', array(
                'conditions' => array('BillingReading.meter_id' => $meterId, 'BillingReading.reading_month' => $month)
            ));

            // Find the latest recorded reading BEFORE the selected month (or absolute latest reading)
            $lastReading = $this->BillingReading->find('first', array(
                'conditions' => array(
                    'BillingReading.meter_id' => $meterId,
                    'BillingReading.reading_month <' => $month
                ),
                'order' => array('BillingReading.reading_month' => 'DESC', 'BillingReading.reading_date' => 'DESC', 'BillingReading.id' => 'DESC')
            ));

            if(empty($lastReading)) {
                $lastReading = $this->BillingReading->find('first', array(
                    'conditions' => array('BillingReading.meter_id' => $meterId),
                    'order' => array('BillingReading.reading_month' => 'DESC', 'BillingReading.reading_date' => 'DESC', 'BillingReading.id' => 'DESC')
                ));
            }

            if(!empty($lastReading) && (float)$lastReading['BillingReading']['current_reading'] > 0)
            {
                $prev = (float)$lastReading['BillingReading']['current_reading'];
            }
            else
            {
                $init = (float)$m['BillingMeter']['initial_reading'];
                $currRec = (float)$m['BillingMeter']['current_reading'];
                $prev = ($currRec > 0) ? $currRec : $init;
            }

            if(!empty($currReadingRow))
            {
                $currVal = $currReadingRow['BillingReading']['current_reading'];
                $unitsVal = max(0.0, (float)$currVal - $prev);
            }
            else
            {
                $currVal = '';
                $unitsVal = '';
            }

            $meterRows[] = array(
                'meter' => $m['BillingMeter'],
                'property' => $m['BillingProperty'],
                'client' => isset($m['BillingProperty']['BillingClient']) ? $m['BillingProperty']['BillingClient'] : array(),
                'previous_reading' => $prev,
                'current_reading' => $currVal,
                'units' => $unitsVal
            );
        }

        $this->set(compact('meterRows', 'month'));
    }

    protected function _exportReadingsCsv($conditions)
    {
        $this->autoRender = false;
        $readings = $this->BillingReading->find('all', array(
            'conditions' => $conditions,
            'contain' => array('BillingMeter', 'BillingProperty' => array('BillingClient')),
            'order' => array('BillingReading.id' => 'DESC')
        ));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=meter_readings_' . date('Y-m-d_His') . '.csv');
        $fp = fopen('php://output', 'w');
        fputcsv($fp, array('ID', 'Month', 'Date', 'Meter No', 'Utility', 'Property Unit', 'Consumer Name', 'Prev Reading', 'Curr Reading', 'Units Consumed', 'Reader Name', 'Billed Status'));

        foreach($readings as $r)
        {
            $rd = $r['BillingReading'];
            $m = $r['BillingMeter'];
            $p = $r['BillingProperty'];
            $c = isset($p['BillingClient']) ? $p['BillingClient'] : array();
            fputcsv($fp, array(
                $rd['id'],
                $rd['reading_month'],
                $rd['reading_date'],
                !empty($m['meter_number']) ? $m['meter_number'] : '-',
                !empty($m['utility_type']) ? $m['utility_type'] : 'Electricity',
                !empty($p['title']) ? $p['title'] : '-',
                !empty($c['name']) ? $c['name'] : '-',
                $rd['previous_reading'],
                $rd['current_reading'],
                $rd['units_consumed'],
                $rd['reader_name'],
                $rd['is_billed'] ? 'BILLED' : 'PENDING'
            ));
        }
        fclose($fp);
        exit;
    }

    protected function _detectAnomaly($meterId, $curr, $prev, $societyId = 1)
    {
        $enabled = $this->BillingSetting->getSetting('subm_feat_anomaly_guard', '1', $societyId);
        if($enabled === '0' || $enabled === 0) {
            return array('flag' => 'NORMAL', 'message' => null);
        }

        if($curr < $prev) {
            return array(
                'flag' => 'ROLLBACK',
                'message' => sprintf('Meter Rollback Alert: Current reading (%s) is lower than previous (%s).', number_format($curr, 2), number_format($prev, 2))
            );
        }

        $units = $curr - $prev;
        $pastReadings = $this->BillingReading->find('all', array(
            'conditions' => array('BillingReading.meter_id' => (int)$meterId),
            'order' => array('BillingReading.id' => 'DESC'),
            'limit' => 3
        ));

        if(!empty($pastReadings)) {
            $sum = 0;
            $cnt = 0;
            foreach($pastReadings as $pr) {
                $u = (float)$pr['BillingReading']['units_consumed'];
                if($u > 0) {
                    $sum += $u;
                    $cnt++;
                }
            }
            if($cnt > 0) {
                $avg = $sum / $cnt;
                if($avg >= 20 && $units > ($avg * 3)) {
                    return array(
                        'flag' => 'SPIKE',
                        'message' => sprintf('Spike Anomaly Alert: Consumed units (%s) is >300%% of 3-month average (%s units).', number_format($units, 1), number_format($avg, 1))
                    );
                }
            }
        }

        return array('flag' => 'NORMAL', 'message' => null);
    }
}
