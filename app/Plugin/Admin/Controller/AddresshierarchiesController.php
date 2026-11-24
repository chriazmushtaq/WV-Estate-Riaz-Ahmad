<?php
class AddresshierarchiesController extends AdminAppController
{
    public $helpers = array('Html','Form','Session','Paginator');
    public $components = array('Session','Paginator');
    public $paginate = array('limit' => 50, 'maxLimit' => 500, 'order' => array('Addresshierarchy.id' => 'desc'));

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->loadModel('Admin.Addresshierarchy');
        $this->Addresshierarchy->ensureSchema();
    }

    public function index()
    {
        $conditions = array();
        $limitOptions = array(5=>5,10=>10,20=>20,25=>25,30=>30,50=>50,100=>100,200=>200,500=>500);
        $currentLimit = (int)$this->_addressQueryValue('limit', 50);
        if(!isset($limitOptions[$currentLimit]))
        {
            $currentLimit = 50;
        }
        $selectedLevelType = trim((string)$this->_addressQueryValue('level_type', ''));
        $searchQuery = trim((string)$this->_addressQueryValue('q', ''));
        if($selectedLevelType !== '')
        {
            $conditions['Addresshierarchy.level_type'] = $selectedLevelType;
        }
        if($searchQuery !== '')
        {
            $conditions['Addresshierarchy.name LIKE'] = '%'.$searchQuery.'%';
        }
        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['conditions'] = $conditions;
        $this->Paginator->settings['limit'] = $currentLimit;
        $this->Paginator->settings['maxLimit'] = 500;
        $rows = $this->Paginator->paginate('Addresshierarchy');
        $pathMap = $this->_pathMapForRows($rows);
        $levelOptions = array_combine($this->Addresshierarchy->levelOrder, array_map('ucfirst', $this->Addresshierarchy->levelOrder));
        $isSuperAdmin = $this->_isSuperAdminSession((array)$this->adminValue);
        $this->set(compact('rows','pathMap','levelOptions','isSuperAdmin','limitOptions','currentLimit','selectedLevelType','searchQuery'));
    }

    public function add()
    {
        if($this->request->is(array('post','put')))
        {
            $data = isset($this->request->data['AddressAdd']) ? (array)$this->request->data['AddressAdd'] : array();
            $levelType = isset($data['level_type']) ? trim((string)$data['level_type']) : '';
            $name = isset($data['name']) ? trim((string)$data['name']) : '';
            if(!in_array($levelType, $this->Addresshierarchy->levelOrder, true) || $name === '')
            {
                $this->Session->setFlash(__('Please select what you want to add and enter a name.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'add'));
            }
            $parentId = $this->_boundParentIdForAdd($levelType);
            if($parentId <= 0 && $levelType !== 'country')
            {
                $this->Session->setFlash(__('Please select the complete parent address chain first.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'add'));
            }
            $id = $this->Addresshierarchy->findOrCreateNode($levelType, $name, $parentId);
            if($id > 0)
            {
                $this->Addresshierarchy->save(array('Addresshierarchy' => array(
                    'id' => $id,
                    'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
                    'status' => !empty($data['status']) ? 1 : 0
                )), false, array('sort_order','status'));
                $this->Session->setFlash(__('Address hierarchy level saved with correct parent binding.'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->Session->setFlash(__('Unable to save address hierarchy level.'),'flash',array('alert'=>'danger'));
        }
        $levelLabels = $this->_levelLabels();
        $levelOptions = array_combine($this->Addresshierarchy->levelOrder, array_values($levelLabels));
        $addressHierarchyTree = $this->Addresshierarchy->treePayload();
        $this->set(compact('levelLabels','levelOptions','addressHierarchyTree'));
    }

    public function edit($id = null)
    {
        $id = (int)$id;
        $row = $this->Addresshierarchy->find('first', array(
            'conditions' => array('Addresshierarchy.id' => $id),
            'recursive' => -1
        ));
        if(empty($row['Addresshierarchy']['id']))
        {
            $this->Session->setFlash(__('Invalid address record.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if($this->request->is(array('post','put')))
        {
            $data = isset($this->request->data['Addresshierarchy']) ? (array)$this->request->data['Addresshierarchy'] : array();
            $name = isset($data['name']) ? trim((string)$data['name']) : '';
            $levelType = isset($data['level_type']) ? trim((string)$data['level_type']) : '';
            if($name === '' || !in_array($levelType, $this->Addresshierarchy->levelOrder, true))
            {
                $this->Session->setFlash(__('Please enter a valid address level and name.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'edit', $id));
            }
            $save = array(
                'id' => $id,
                'parent_id' => !empty($data['parent_id']) ? (int)$data['parent_id'] : 0,
                'level_type' => $levelType,
                'name' => $name,
                'slug' => $this->Addresshierarchy->slugify($name),
                'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
                'status' => !empty($data['status']) ? 1 : 0
            );
            if($this->Addresshierarchy->save(array('Addresshierarchy' => $save), false))
            {
                $this->Session->setFlash(__('Address hierarchy updated.'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->Session->setFlash(__('Unable to update address hierarchy.'),'flash',array('alert'=>'danger'));
        }
        $this->request->data = $row;
        $levelOptions = array_combine($this->Addresshierarchy->levelOrder, array_map('ucfirst', $this->Addresshierarchy->levelOrder));
        $parentOptions = $this->Addresshierarchy->find('list', array(
            'fields' => array('Addresshierarchy.id','Addresshierarchy.name'),
            'conditions' => array('Addresshierarchy.id !=' => $id, 'Addresshierarchy.status' => 1),
            'order' => array('Addresshierarchy.level_type' => 'asc', 'Addresshierarchy.name' => 'asc'),
            'recursive' => -1
        ));
        $pathMap = $this->_pathMapForRows(array($row));
        $path = isset($pathMap[$id]) ? $pathMap[$id] : array();
        $this->set(compact('row','levelOptions','parentOptions','path'));
    }

    public function delete($id = null)
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        $id = (int)$id;
        if($id > 0)
        {
            $childCount = (int)$this->Addresshierarchy->find('count', array(
                'conditions' => array('Addresshierarchy.parent_id' => $id),
                'recursive' => -1
            ));
            if($childCount > 0)
            {
                $this->Addresshierarchy->save(array('Addresshierarchy' => array('id' => $id, 'status' => 0)), false, array('status'));
                $this->Session->setFlash(__('Address level has children, so it was disabled instead of deleted.'),'flash',array('alert'=>'warning'));
            }
            else
            {
                $this->Addresshierarchy->delete($id);
                $this->Session->setFlash(__('Address hierarchy deleted.'),'flash',array('alert'=>'success'));
            }
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function bulkdelete()
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            $this->Session->setFlash(__('Only Super Admin can bulk delete address records.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $ids = isset($this->request->data['Addresshierarchy']['id']) ? (array)$this->request->data['Addresshierarchy']['id'] : array();
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if(empty($ids))
        {
            $this->Session->setFlash(__('Please select address records first.'),'flash',array('alert'=>'warning'));
            return $this->redirect(array('action' => 'index'));
        }
        $deleted = 0;
        $disabled = 0;
        foreach($ids as $id)
        {
            $result = $this->_deleteOrDisableNode($id);
            if($result === 'deleted'){$deleted++;}
            elseif($result === 'disabled'){$disabled++;}
        }
        $this->Session->setFlash(sprintf(__('Bulk action completed. Deleted: %s, Disabled with children: %s'), $deleted, $disabled),'flash',array('alert'=>'success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function import()
    {
        if($this->request->is('post'))
        {
            @set_time_limit(300);
            @ini_set('memory_limit', '512M');

            $file = isset($this->request->data['Addresshierarchy']['file']) ? $this->request->data['Addresshierarchy']['file'] : array();
            $tmp = !empty($file['tmp_name']) ? $file['tmp_name'] : '';
            if($tmp === '' || !is_uploaded_file($tmp))
            {
                $this->Session->setFlash(__('Please upload a CSV or Excel file.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'import'));
            }
            $fileName = !empty($file['name']) ? (string)$file['name'] : '';
            $rows = $this->_readAddressImportRows($tmp, $fileName);
            if(empty($rows))
            {
                $this->Session->setFlash(__('Unable to read uploaded file or file is empty. CSV and XLSX files are supported.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'import'));
            }

            $this->Addresshierarchy->ensureSchema();

            $headerMapping = $this->_resolveHeaderMapping($rows[0]);
            $startIndex = !empty($headerMapping['is_header']) ? 1 : 0;

            $totalRows = count($rows);
            $importedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;

            for($i = $startIndex; $i < $totalRows; $i++)
            {
                $rawRow = $rows[$i];
                $path = $this->_extractAddressPath($rawRow, $headerMapping['columns']);
                if(empty(array_filter($path)))
                {
                    $skippedCount++;
                    continue;
                }

                try
                {
                    $res = $this->Addresshierarchy->importPath($path);
                    if($res > 0)
                    {
                        $importedCount++;
                    }
                    else
                    {
                        $skippedCount++;
                    }
                }
                catch(Exception $ex)
                {
                    CakeLog::write('error', 'Address import error at line '.($i + 1).': '.$ex->getMessage());
                    $errorCount++;
                }
            }

            if($importedCount > 0)
            {
                $msg = sprintf(__('%d address hierarchy rows imported successfully.'), $importedCount);
                if($skippedCount > 0)
                {
                    $msg .= sprintf(__(' (%d skipped)'), $skippedCount);
                }
                if($errorCount > 0)
                {
                    $msg .= sprintf(__(' (%d errors)'), $errorCount);
                }
                $this->Session->setFlash($msg, 'flash', array('alert' => 'success'));
            }
            else
            {
                if($errorCount > 0)
                {
                    $this->Session->setFlash(sprintf(__('Import failed with %d errors. Please verify data format.'), $errorCount), 'flash', array('alert' => 'danger'));
                }
                else
                {
                    $this->Session->setFlash(__('No valid address rows found to import.'), 'flash', array('alert' => 'warning'));
                }
            }
            return $this->redirect(array('action' => 'index'));
        }
    }

    protected function _stripBom($str)
    {
        if(!is_string($str))
        {
            return '';
        }
        if(substr($str, 0, 3) === "\xEF\xBB\xBF")
        {
            $str = substr($str, 3);
        }
        elseif(substr($str, 0, 2) === "\xFE\xFF" || substr($str, 0, 2) === "\xFF\xFE")
        {
            $str = substr($str, 2);
        }
        return $str;
    }

    protected function _resolveHeaderMapping($firstRow)
    {
        $aliasMap = array(
            'country'  => array('country', 'country_name', 'countryname', 'mulk', 'nation'),
            'province' => array('province', 'state', 'suba', 'province_name', 'provincename', 'state_name'),
            'district' => array('district', 'district_name', 'districtname', 'zila'),
            'tehsil'   => array('tehsil', 'tehsil_name', 'tehsilname', 'taluka', 'subdistrict', 'sub_district'),
            'city'     => array('city', 'city_name', 'cityname', 'shehar'),
            'town'     => array('town', 'town_name', 'townname', 'area', 'locality', 'sublocality', 'zone'),
            'block'    => array('block', 'block_name', 'blockname', 'phase', 'sector', 'street', 'mohallah', 'block_no')
        );

        $columns = array();
        foreach($this->Addresshierarchy->levelOrder as $level)
        {
            $columns[$level] = -1;
        }

        $matchedCount = 0;
        if(is_array($firstRow))
        {
            foreach($firstRow as $colIdx => $colVal)
            {
                $clean = strtolower(preg_replace('/[^a-z0-9_]/i', '', $this->_stripBom((string)$colVal)));
                if($clean === '')
                {
                    continue;
                }
                foreach($aliasMap as $level => $aliases)
                {
                    if($columns[$level] === -1 && in_array($clean, $aliases, true))
                    {
                        $columns[$level] = (int)$colIdx;
                        $matchedCount++;
                        break;
                    }
                }
            }
        }

        $isHeader = ($matchedCount >= 1 && ($columns['country'] !== -1 || $columns['province'] !== -1 || $columns['district'] !== -1 || $columns['city'] !== -1));
        if(!$isHeader)
        {
            $defaultIdx = 0;
            foreach($this->Addresshierarchy->levelOrder as $level)
            {
                $columns[$level] = $defaultIdx++;
            }
        }

        return array(
            'is_header' => $isHeader,
            'columns' => $columns
        );
    }

    protected function _extractAddressPath($row, $columns)
    {
        $path = array();
        foreach($this->Addresshierarchy->levelOrder as $level)
        {
            $idx = isset($columns[$level]) ? (int)$columns[$level] : -1;
            $val = '';
            if($idx >= 0 && isset($row[$idx]))
            {
                $val = $this->_stripBom(trim((string)$row[$idx]));
            }
            $path[$level] = $val;
        }
        return $path;
    }

    protected function _readAddressImportRows($tmp, $fileName)
    {
        if(!file_exists($tmp) || !is_readable($tmp))
        {
            return array();
        }

        $ext = strtolower(pathinfo((string)$fileName, PATHINFO_EXTENSION));

        $magic = '';
        $handle = @fopen($tmp, 'rb');
        if($handle)
        {
            $magic = (string)fread($handle, 4);
            fclose($handle);
        }

        if($magic === "PK\x03\x04" || $ext === 'xlsx')
        {
            $xlsxRows = $this->_readAddressXlsxRows($tmp);
            if(!empty($xlsxRows))
            {
                return $xlsxRows;
            }
        }

        if($ext === 'xls')
        {
            $sample = @file_get_contents($tmp, false, null, 0, 8192);
            if($sample !== false && (stripos($sample, '<table') !== false || stripos($sample, '<html') !== false))
            {
                $htmlRows = $this->_readAddressHtmlTableRows($tmp);
                if(!empty($htmlRows))
                {
                    return $htmlRows;
                }
            }
        }

        return $this->_readAddressCsvRows($tmp);
    }

    protected function _readAddressCsvRows($tmp)
    {
        $handle = @fopen($tmp, 'r');
        if(!$handle)
        {
            return array();
        }

        $sample = fread($handle, 4096);
        rewind($handle);

        if($sample === false || strlen($sample) === 0)
        {
            fclose($handle);
            return array();
        }

        $hasBom = (substr($sample, 0, 3) === "\xEF\xBB\xBF");
        if($hasBom)
        {
            fseek($handle, 3);
        }
        $firstLine = fgets($handle);
        fseek($handle, $hasBom ? 3 : 0);

        $delimiter = ',';
        if($firstLine !== false)
        {
            $commas = substr_count($firstLine, ',');
            $semicolons = substr_count($firstLine, ';');
            $tabs = substr_count($firstLine, "\t");
            if($semicolons > $commas && $semicolons >= $tabs)
            {
                $delimiter = ';';
            }
            elseif($tabs > $commas && $tabs > $semicolons)
            {
                $delimiter = "\t";
            }
        }

        @ini_set('auto_detect_line_endings', '1');
        $rows = array();
        while(($data = fgetcsv($handle, 0, $delimiter)) !== false)
        {
            if(!is_array($data))
            {
                continue;
            }
            $cleanRow = array();
            $hasData = false;
            foreach($data as $cell)
            {
                $cleanCell = $this->_stripBom(trim((string)$cell));
                if($cleanCell !== '')
                {
                    $hasData = true;
                }
                $cleanRow[] = $cleanCell;
            }
            if($hasData)
            {
                $rows[] = $cleanRow;
            }
        }
        fclose($handle);
        return $rows;
    }

    protected function _readAddressHtmlTableRows($tmp)
    {
        $content = @file_get_contents($tmp);
        if($content === false || trim($content) === '')
        {
            return array();
        }
        $rows = array();
        $prevErrors = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);
        libxml_clear_errors();
        libxml_use_internal_errors($prevErrors);

        $trList = $dom->getElementsByTagName('tr');
        foreach($trList as $tr)
        {
            $rowCells = array();
            $hasData = false;
            foreach($tr->childNodes as $node)
            {
                if(strtolower($node->nodeName) === 'td' || strtolower($node->nodeName) === 'th')
                {
                    $val = $this->_stripBom(trim($node->textContent));
                    if($val !== '')
                    {
                        $hasData = true;
                    }
                    $rowCells[] = $val;
                }
            }
            if($hasData)
            {
                $rows[] = $rowCells;
            }
        }
        return $rows;
    }

    protected function _readAddressXlsxRows($tmp)
    {
        if(!class_exists('ZipArchive'))
        {
            return array();
        }
        $zip = new ZipArchive();
        if($zip->open($tmp) !== true)
        {
            return array();
        }
        $sharedStrings = array();
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if($sharedXml !== false)
        {
            $shared = @simplexml_load_string($sharedXml);
            if($shared)
            {
                foreach($shared->si as $si)
                {
                    $text = '';
                    if(isset($si->t))
                    {
                        $text = (string)$si->t;
                    }
                    else
                    {
                        $tNodes = $si->xpath('.//t');
                        if($tNodes)
                        {
                            foreach($tNodes as $tNode)
                            {
                                $text .= (string)$tNode;
                            }
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        $sheetName = 'xl/worksheets/sheet1.xml';
        $workbookRels = $zip->getFromName('xl/_rels/workbook.xml.rels');
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        if($workbookXml !== false && $workbookRels !== false)
        {
            $workbook = @simplexml_load_string($workbookXml);
            $rels = @simplexml_load_string($workbookRels);
            if($workbook && $rels)
            {
                $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                $firstSheet = $workbook->sheets->sheet[0];
                $attrs = $firstSheet ? $firstSheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships') : array();
                $rid = isset($attrs['id']) ? (string)$attrs['id'] : '';
                if($rid !== '')
                {
                    foreach($rels->Relationship as $rel)
                    {
                        $relAttrs = $rel->attributes();
                        if((string)$relAttrs['Id'] === $rid && !empty($relAttrs['Target']))
                        {
                            $target = ltrim((string)$relAttrs['Target'], '/');
                            $sheetName = (strpos($target, 'xl/') === 0) ? $target : ('xl/' . $target);
                            break;
                        }
                    }
                }
            }
        }

        $sheetXml = $zip->getFromName($sheetName);
        if($sheetXml === false)
        {
            for($idx = 0; $idx < $zip->numFiles; $idx++)
            {
                $entryName = $zip->getNameIndex($idx);
                if(preg_match('#^xl/worksheets/sheet[0-9]+\.xml$#i', $entryName))
                {
                    $sheetXml = $zip->getFromName($entryName);
                    break;
                }
            }
        }
        $zip->close();

        if($sheetXml === false)
        {
            return array();
        }
        $sheet = @simplexml_load_string($sheetXml);
        if(!$sheet || !isset($sheet->sheetData->row))
        {
            return array();
        }

        $rows = array();
        foreach($sheet->sheetData->row as $row)
        {
            $cells = array();
            $currentCol = 0;
            foreach($row->c as $cell)
            {
                $attrs = $cell->attributes();
                $ref = isset($attrs['r']) ? (string)$attrs['r'] : '';
                if($ref !== '')
                {
                    $colLetters = preg_replace('/[^A-Z]/', '', strtoupper($ref));
                    $colIndex = $this->_xlsxColumnIndex($colLetters);
                    if($colIndex >= 0)
                    {
                        $currentCol = $colIndex;
                    }
                }

                $type = isset($attrs['t']) ? (string)$attrs['t'] : '';
                $value = '';
                if($type === 's')
                {
                    $idx = isset($cell->v) ? (int)$cell->v : -1;
                    $value = isset($sharedStrings[$idx]) ? $sharedStrings[$idx] : '';
                }
                elseif($type === 'inlineStr' && isset($cell->is))
                {
                    if(isset($cell->is->t))
                    {
                        $value = (string)$cell->is->t;
                    }
                    else
                    {
                        $tNodes = $cell->is->xpath('.//t');
                        if($tNodes)
                        {
                            foreach($tNodes as $tNode)
                            {
                                $value .= (string)$tNode;
                            }
                        }
                    }
                }
                elseif(isset($cell->v))
                {
                    $value = (string)$cell->v;
                }

                $cells[$currentCol] = $this->_stripBom(trim($value));
                $currentCol++;
            }

            if(!empty($cells))
            {
                $maxKey = max(array_keys($cells));
                $normalizedRow = array_fill(0, $maxKey + 1, '');
                foreach($cells as $k => $v)
                {
                    $normalizedRow[$k] = $v;
                }
                if(trim(implode('', $normalizedRow)) !== '')
                {
                    $rows[] = $normalizedRow;
                }
            }
        }
        return $rows;
    }

    protected function _xlsxColumnIndex($letters)
    {
        $letters = strtoupper(trim((string)$letters));
        if($letters === '')
        {
            return -1;
        }
        $num = 0;
        $len = strlen($letters);
        for($i = 0; $i < $len; $i++)
        {
            $num = ($num * 26) + (ord($letters[$i]) - 64);
        }
        return $num - 1;
    }

    public function sample()
    {
        $this->autoRender = false;
        $this->layout = null;
        $csv = "\xEF\xBB\xBFcountry,province,district,tehsil,city,town,block\r\n";
        $csv .= "Pakistan,Punjab,Lahore,Lahore City,Lahore,Model Town,Block A\r\n";
        $csv .= "Pakistan,Sindh,Karachi South,Saddar Town,Karachi,Clifton,Block 2\r\n";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=pakistan_address_hierarchy_sample.csv');
        echo $csv;
    }

    public function children()
    {
        $this->autoRender = false;
        $this->layout = null;
        $level = isset($this->request->query['level']) ? trim((string)$this->request->query['level']) : '';
        $parentId = isset($this->request->query['parent_id']) ? (int)$this->request->query['parent_id'] : 0;
        $items = array();
        if(in_array($level, $this->Addresshierarchy->levelOrder, true))
        {
            $options = $this->Addresshierarchy->levelOptions($level, $parentId);
            foreach($options as $id => $name)
            {
                $items[] = array('id' => (int)$id, 'name' => (string)$name);
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('ok' => 1, 'items' => $items));
    }

    protected function _parentNames($rows)
    {
        $ids = array();
        foreach((array)$rows as $row)
        {
            if(!empty($row['Addresshierarchy']['parent_id']))
            {
                $ids[] = (int)$row['Addresshierarchy']['parent_id'];
            }
        }
        $ids = array_values(array_unique($ids));
        if(empty($ids))
        {
            return array();
        }
        return (array)$this->Addresshierarchy->find('list', array(
            'fields' => array('Addresshierarchy.id','Addresshierarchy.name'),
            'conditions' => array('Addresshierarchy.id' => $ids),
            'recursive' => -1
        ));
    }

    protected function _deleteOrDisableNode($id)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            return '';
        }
        $childCount = (int)$this->Addresshierarchy->find('count', array(
            'conditions' => array('Addresshierarchy.parent_id' => $id),
            'recursive' => -1
        ));
        if($childCount > 0)
        {
            $this->Addresshierarchy->save(array('Addresshierarchy' => array('id' => $id, 'status' => 0)), false, array('status'));
            return 'disabled';
        }
        if($this->Addresshierarchy->delete($id))
        {
            return 'deleted';
        }
        return '';
    }

    protected function _pathFromRequest()
    {
        $data = isset($this->request->data['AddressPath']) ? (array)$this->request->data['AddressPath'] : array();
        $path = array();
        foreach($this->Addresshierarchy->levelOrder as $level)
        {
            $path[] = isset($data[$level]) ? trim((string)$data[$level]) : '';
        }
        return $path;
    }

    protected function _boundParentIdForAdd($levelType)
    {
        $levelType = trim((string)$levelType);
        $index = array_search($levelType, $this->Addresshierarchy->levelOrder, true);
        if($index === false || $index === 0)
        {
            return 0;
        }
        $parentLevel = $this->Addresshierarchy->levelOrder[$index - 1];
        $parentData = isset($this->request->data['AddressParent']) ? (array)$this->request->data['AddressParent'] : array();
        $parentId = isset($parentData[$parentLevel.'_id']) ? (int)$parentData[$parentLevel.'_id'] : 0;
        if($parentId <= 0)
        {
            return 0;
        }
        $parentRow = $this->Addresshierarchy->find('first', array(
            'fields' => array('Addresshierarchy.id','Addresshierarchy.level_type'),
            'conditions' => array(
                'Addresshierarchy.id' => $parentId,
                'Addresshierarchy.level_type' => $parentLevel,
                'Addresshierarchy.status' => 1
            ),
            'recursive' => -1
        ));
        if(empty($parentRow['Addresshierarchy']['id']))
        {
            return 0;
        }
        return $parentId;
    }

    protected function _addressQueryValue($key, $default = '')
    {
        $key = (string)$key;
        if(isset($this->request->query[$key]))
        {
            return $this->request->query[$key];
        }
        if(isset($this->request->query['data']) && is_array($this->request->query['data']) && isset($this->request->query['data'][$key]))
        {
            return $this->request->query['data'][$key];
        }
        if(isset($this->request->query['Addresshierarchy']) && is_array($this->request->query['Addresshierarchy']) && isset($this->request->query['Addresshierarchy'][$key]))
        {
            return $this->request->query['Addresshierarchy'][$key];
        }
        return $default;
    }

    protected function _levelLabels()
    {
        return array(
            'country' => __('Country'),
            'province' => __('Province'),
            'district' => __('District'),
            'tehsil' => __('Tehsil'),
            'city' => __('City'),
            'town' => __('Town'),
            'block' => __('Block')
        );
    }

    protected function _pathMapForRows($rows)
    {
        $allRows = (array)$this->Addresshierarchy->find('all', array(
            'fields' => array('Addresshierarchy.id','Addresshierarchy.parent_id','Addresshierarchy.level_type','Addresshierarchy.name'),
            'recursive' => -1
        ));
        $nodeMap = array();
        foreach($allRows as $nodeRow)
        {
            if(!empty($nodeRow['Addresshierarchy']['id']))
            {
                $nodeMap[(int)$nodeRow['Addresshierarchy']['id']] = $nodeRow['Addresshierarchy'];
            }
        }
        $pathMap = array();
        foreach((array)$rows as $row)
        {
            $node = isset($row['Addresshierarchy']) ? $row['Addresshierarchy'] : array();
            $nodeId = isset($node['id']) ? (int)$node['id'] : 0;
            if($nodeId <= 0)
            {
                continue;
            }
            $path = array();
            foreach($this->Addresshierarchy->levelOrder as $level)
            {
                $path[$level] = '';
            }
            $guard = 0;
            $cursorId = $nodeId;
            while($cursorId > 0 && isset($nodeMap[$cursorId]) && $guard < 20)
            {
                $cursor = $nodeMap[$cursorId];
                $level = isset($cursor['level_type']) ? (string)$cursor['level_type'] : '';
                if(isset($path[$level]))
                {
                    $path[$level] = isset($cursor['name']) ? (string)$cursor['name'] : '';
                }
                $cursorId = isset($cursor['parent_id']) ? (int)$cursor['parent_id'] : 0;
                $guard++;
            }
            $pathMap[$nodeId] = $path;
        }
        return $pathMap;
    }
}
?>
