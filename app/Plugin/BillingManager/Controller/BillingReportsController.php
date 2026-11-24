<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingReportsController extends BillingManagerAppController {
    public $uses = array(
        'BillingManager.BillingBill', 
        'BillingManager.BillingReading', 
        'BillingManager.BillingMeter', 
        'BillingManager.BillingClient', 
        'BillingManager.BillingProperty', 
        'BillingManager.BillingExtraCharge', 
        'BillingManager.BillingSociety',
        'BillingManager.BillingSetting',
        'BillingManager.BillingFeeder',
        'BillingManager.BillingFeederReading'
    );

    public function beforeFilter() {
        parent::beforeFilter();
        if (isset($this->Auth)) {
            $this->Auth->allow(array(
                'index', 'admin_index', 'export_excel', 'export_pdf', 
                'disconnection_order', 'line_loss_audit', 'admin_line_loss_audit',
                'save_feeder', 'delete_feeder', 'save_feeder_reading',
                'propagate_water_charge', 'propagate_utility_charge', 'delete_propagated_charge'
            ));
        }
    }

    public function admin_index() {
        $this->index();
        if ($this->hasRendered || (is_object($this->response) && $this->response->statusCode() == 302)) {
            return;
        }
        return $this->render('BillingManager.BillingReports/index');
    }

    public function admin_line_loss_audit() {
        $this->line_loss_audit();
        if ($this->hasRendered || (is_object($this->response) && $this->response->statusCode() == 302)) {
            return;
        }
        return $this->render('BillingManager.BillingReports/line_loss_audit');
    }

    public function index() {
        $this->set('title_for_layout', __('Financial Reports & Defaulter Recovery Hub'));

        // Read GET filter parameters
        $societyId = !empty($this->request->query['society_id']) ? (int)$this->request->query['society_id'] : 1;
        $month = !empty($this->request->query['month']) ? trim($this->request->query['month']) : 'all';
        $status = !empty($this->request->query['status']) ? strtolower(trim($this->request->query['status'])) : 'all';
        $utilityType = !empty($this->request->query['utility_type']) ? trim($this->request->query['utility_type']) : 'all';
        $search = !empty($this->request->query['search']) ? trim($this->request->query['search']) : '';

        // Fetch Societies list for dropdown
        $societiesList = $this->BillingSociety->find('list', array(
            'fields' => array('id', 'name')
        ));
        if (empty($societiesList)) {
            $societiesList = array(1 => 'White Villas Housing Society');
        }

        // Fetch Society branding details
        $society = $this->BillingSociety->findById($societyId);
        $societyName = !empty($society['BillingSociety']['name']) ? $society['BillingSociety']['name'] : 'White Villas Lahore';
        $societyAddress = !empty($society['BillingSociety']['address']) ? $society['BillingSociety']['address'] : 'Main Boulevard, White Villas Lahore';
        $societyPhone = !empty($society['BillingSociety']['phone']) ? $society['BillingSociety']['phone'] : '+92 300 4075006';
        $societyEmail = !empty($society['BillingSociety']['email']) ? $society['BillingSociety']['email'] : 'info@whitevillas.com';

        // Pre-build 100% Fail-Proof Meter & Reading Lookup Maps
        $allMeters = $this->BillingMeter->find('all', array('recursive' => -1));
        $meterMapById = array();
        $meterMapByProp = array();
        foreach ($allMeters as $m) {
            $mId = (int)$m['BillingMeter']['id'];
            $pId = (int)$m['BillingMeter']['billing_property_id'];
            $mNo = $m['BillingMeter']['meter_number'];
            $meterMapById[$mId] = $mNo;
            if ($pId > 0 && !isset($meterMapByProp[$pId])) {
                $meterMapByProp[$pId] = $mNo;
            }
        }

        $allReadingsMap = $this->BillingReading->find('all', array('recursive' => -1));
        $readingMeterMap = array();
        foreach ($allReadingsMap as $r) {
            $rId = (int)$r['BillingReading']['id'];
            $mId = (int)$r['BillingReading']['meter_id'];
            if ($mId > 0 && isset($meterMapById[$mId])) {
                $readingMeterMap[$rId] = $meterMapById[$mId];
            }
        }

        // 1. FINANCIAL COLLECTION SUMMARY WITH FILTERS
        $billConditions = array();
        if ($societyId > 0) {
            $billConditions['BillingBill.society_id'] = $societyId;
        }
        if ($month != 'all') {
            $billConditions['BillingBill.billing_period'] = $month;
        }
        if ($status != 'all') {
            $billConditions['BillingBill.payment_status'] = $status;
        }
        if ($utilityType != 'all') {
            $billConditions['BillingBill.utility_type'] = $utilityType;
        }

        // Fetch distinct available months for dropdown filter
        $availableMonths = $this->BillingBill->find('all', array(
            'fields' => array('DISTINCT BillingBill.billing_period'),
            'conditions' => array('BillingBill.society_id' => $societyId),
            'order' => array('BillingBill.billing_period' => 'DESC')
        ));
        $monthsList = array();
        foreach ($availableMonths as $mRow) {
            $mVal = !empty($mRow['BillingBill']['billing_period']) ? $mRow['BillingBill']['billing_period'] : '';
            if (!empty($mVal)) {
                $fM = (preg_match('/^\d{4}-\d{2}$/', $mVal)) ? date('M-Y', strtotime($mVal . '-01')) : $mVal;
                $monthsList[$mVal] = $fM;
            }
        }

        $allBills = $this->BillingBill->find('all', array(
            'conditions' => $billConditions,
            'recursive' => 2,
            'order' => array('BillingBill.id' => 'DESC')
        ));

        $totalBilled = 0;
        $totalPaid = 0;
        $totalArrears = 0;
        $totalBillsCount = 0;
        $monthlyBreakdown = array();
        $consumerCollectionList = array();
        $defaultersList = array();

        foreach ($allBills as $b) {
            $bill = $b['BillingBill'];
            $bMonth = !empty($bill['billing_period']) ? $bill['billing_period'] : (!empty($bill['issue_date']) ? date('Y-m', strtotime($bill['issue_date'])) : date('Y-m', strtotime($bill['created_at'])));
            $formattedMonth = (preg_match('/^\d{4}-\d{2}$/', $bMonth)) ? date('M-Y', strtotime($bMonth . '-01')) : $bMonth;

            $cName = !empty($b['BillingProperty']['BillingClient']['name']) ? $b['BillingProperty']['BillingClient']['name'] : (!empty($b['BillingClient']['name']) ? $b['BillingClient']['name'] : 'N/A');
            $cPhone = !empty($b['BillingProperty']['BillingClient']['phone']) ? $b['BillingProperty']['BillingClient']['phone'] : (!empty($b['BillingClient']['phone']) ? $b['BillingClient']['phone'] : 'N/A');
            $pTitle = !empty($b['BillingProperty']['title']) ? $b['BillingProperty']['title'] : (!empty($b['BillingProperty']['property_no']) ? ('Unit #' . $b['BillingProperty']['property_no']) : 'N/A');
            $bNo = !empty($bill['bill_number']) ? $bill['bill_number'] : ('#' . $bill['id']);

            // 100% Fail-Proof Meter Number Lookup
            $rId = !empty($bill['reading_id']) ? (int)$bill['reading_id'] : 0;
            $pId = !empty($bill['billing_property_id']) ? (int)$bill['billing_property_id'] : 0;

            $mNo = 'N/A';
            if (!empty($rId) && isset($readingMeterMap[$rId])) {
                $mNo = $readingMeterMap[$rId];
            } elseif (!empty($pId) && isset($meterMapByProp[$pId])) {
                $mNo = $meterMapByProp[$pId];
            } elseif (!empty($b['BillingReading']['BillingMeter']['meter_number'])) {
                $mNo = $b['BillingReading']['BillingMeter']['meter_number'];
            }

            // Live Search Filter across consumer name, phone, property title, bill #, meter #
            if (!empty($search)) {
                $searchLc = strtolower($search);
                $haystack = strtolower($cName . ' ' . $cPhone . ' ' . $pTitle . ' ' . $mNo . ' ' . $bNo);
                if (strpos($haystack, $searchLc) === false) {
                    continue;
                }
            }

            $bAmount = !empty($bill['total_amount']) ? (float)$bill['total_amount'] : 0;
            $pAmount = !empty($bill['paid_amount']) ? (float)$bill['paid_amount'] : 0;
            $balAmount = max(0, $bAmount - $pAmount);
            $st = !empty($bill['payment_status']) ? strtolower($bill['payment_status']) : 'unpaid';

            $totalBillsCount++;
            $totalBilled += $bAmount;
            $totalPaid += $pAmount;
            $totalArrears += $balAmount;

            // Monthly Breakdown aggregation
            if (!isset($monthlyBreakdown[$bMonth])) {
                $monthlyBreakdown[$bMonth] = array(
                    'month' => $bMonth,
                    'formatted_month' => $formattedMonth,
                    'bills_count' => 0,
                    'billed' => 0,
                    'paid' => 0,
                    'balance' => 0
                );
            }
            $monthlyBreakdown[$bMonth]['bills_count']++;
            $monthlyBreakdown[$bMonth]['billed'] += $bAmount;
            $monthlyBreakdown[$bMonth]['paid'] += $pAmount;
            $monthlyBreakdown[$bMonth]['balance'] += $balAmount;

            // Detailed Consumer Collection Row
            $consumerCollectionList[] = array(
                'bill_id' => $bill['id'],
                'bill_number' => $bNo,
                'month' => $bMonth,
                'formatted_month' => $formattedMonth,
                'consumer_name' => $cName,
                'consumer_phone' => $cPhone,
                'property_title' => $pTitle,
                'meter_number' => $mNo,
                'utility_type' => !empty($bill['utility_type']) ? $bill['utility_type'] : 'Electricity',
                'issue_date' => !empty($bill['issue_date']) ? $bill['issue_date'] : $bill['created_at'],
                'due_date' => !empty($bill['due_date']) ? $bill['due_date'] : date('Y-m-d'),
                'total_amount' => $bAmount,
                'paid_amount' => $pAmount,
                'balance_amount' => $balAmount,
                'status' => $st
            );

            // Defaulters List (Unpaid or Partial bills)
            if ($st != 'paid' && $balAmount > 0) {
                $dueDate = !empty($bill['due_date']) ? $bill['due_date'] : date('Y-m-d');
                $dueTs = strtotime($dueDate);
                $nowTs = time();
                $overdueDays = ($nowTs > $dueTs) ? floor(($nowTs - $dueTs) / 86400) : 0;

                $defaultersList[] = array(
                    'bill_id' => $bill['id'],
                    'bill_number' => $bNo,
                    'month' => $bMonth,
                    'formatted_month' => $formattedMonth,
                    'consumer_name' => $cName,
                    'consumer_phone' => $cPhone,
                    'property_title' => $pTitle,
                    'meter_number' => $mNo,
                    'due_date' => $dueDate,
                    'overdue_days' => $overdueDays,
                    'total_amount' => $bAmount,
                    'paid_amount' => $pAmount,
                    'balance_amount' => $balAmount,
                    'status' => $st
                );
            }
        }

        $recoveryRate = ($totalBilled > 0) ? round(($totalPaid / $totalBilled) * 100, 1) : 100;

        // 2. METER READINGS HISTORY LOG WITH FILTERS
        $readingConditions = array();
        if ($societyId > 0) {
            $readingConditions['BillingReading.society_id'] = $societyId;
        }
        if ($month != 'all') {
            $readingConditions['BillingReading.reading_month'] = $month;
        }
        if ($utilityType != 'all') {
            $readingConditions['BillingMeter.utility_type'] = $utilityType;
        }

        $allReadings = $this->BillingReading->find('all', array(
            'conditions' => $readingConditions,
            'recursive' => 2,
            'order' => array('BillingReading.id' => 'DESC'),
            'limit' => 300
        ));

        $readingsLog = array();
        foreach ($allReadings as $r) {
            $reading = $r['BillingReading'];
            $rMonth = !empty($reading['reading_month']) ? $reading['reading_month'] : date('Y-m');
            $rcName = !empty($r['BillingProperty']['BillingClient']['name']) ? $r['BillingProperty']['BillingClient']['name'] : 'N/A';
            $rpTitle = !empty($r['BillingProperty']['title']) ? $r['BillingProperty']['title'] : 'N/A';
            $rmNo = !empty($r['BillingMeter']['meter_number']) ? $r['BillingMeter']['meter_number'] : 'N/A';

            if (!empty($search)) {
                $searchLc = strtolower($search);
                $haystack = strtolower($rcName . ' ' . $rpTitle . ' ' . $rmNo);
                if (strpos($haystack, $searchLc) === false) {
                    continue;
                }
            }

            $readingsLog[] = array(
                'id' => $reading['id'],
                'date' => !empty($reading['reading_date']) ? $reading['reading_date'] : $reading['created_at'],
                'month' => $rMonth,
                'formatted_month' => (preg_match('/^\d{4}-\d{2}$/', $rMonth)) ? date('M-Y', strtotime($rMonth . '-01')) : $rMonth,
                'consumer_name' => $rcName,
                'property_title' => $rpTitle,
                'meter_number' => $rmNo,
                'utility_type' => !empty($r['BillingMeter']['utility_type']) ? $r['BillingMeter']['utility_type'] : 'Electricity',
                'previous_reading' => (float)$reading['previous_reading'],
                'current_reading' => (float)$reading['current_reading'],
                'units_consumed' => (float)$reading['units_consumed'],
                'reader_name' => !empty($reading['reader_name']) ? $reading['reader_name'] : 'Admin'
            );
        }

        $this->set(compact(
            'societiesList', 'societyId', 'societyName', 'societyAddress', 'societyPhone', 'societyEmail',
            'month', 'status', 'utilityType', 'search', 'monthsList',
            'totalBillsCount', 'totalBilled', 'totalPaid', 'totalArrears',
            'recoveryRate', 'monthlyBreakdown', 'consumerCollectionList', 'defaultersList', 'readingsLog'
        ));
    }

    /**
     * 1-Click Excel / CSV Accounting Export Engine (Compatible with Excel, Tally, QuickBooks)
     */
    public function export_excel($type = 'collection') {
        $this->autoRender = false;
        $societyId = !empty($this->request->query['society_id']) ? (int)$this->request->query['society_id'] : 1;
        $month = !empty($this->request->query['month']) ? trim($this->request->query['month']) : 'all';
        $status = !empty($this->request->query['status']) ? strtolower(trim($this->request->query['status'])) : 'all';

        $filename = "Billing_" . ucfirst($type) . "_" . date('Y-m-d') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Pre-build 100% Fail-Proof Meter Lookup Maps
        $allMeters = $this->BillingMeter->find('all', array('recursive' => -1));
        $meterMapById = array();
        $meterMapByProp = array();
        foreach ($allMeters as $m) {
            $mId = (int)$m['BillingMeter']['id'];
            $pId = (int)$m['BillingMeter']['billing_property_id'];
            $mNo = $m['BillingMeter']['meter_number'];
            $meterMapById[$mId] = $mNo;
            if ($pId > 0 && !isset($meterMapByProp[$pId])) {
                $meterMapByProp[$pId] = $mNo;
            }
        }

        $allReadingsMap = $this->BillingReading->find('all', array('recursive' => -1));
        $readingMeterMap = array();
        foreach ($allReadingsMap as $r) {
            $rId = (int)$r['BillingReading']['id'];
            $mId = (int)$r['BillingReading']['meter_id'];
            if ($mId > 0 && isset($meterMapById[$mId])) {
                $readingMeterMap[$rId] = $meterMapById[$mId];
            }
        }

        if ($type == 'defaulters') {
            // DEFAULTERS & OUTSTANDING ARREARS LIST EXPORT
            fputcsv($output, array('Bill #', 'Consumer Name', 'Phone Number', 'Property Unit', 'Meter Number', 'Month', 'Due Date', 'Overdue Days', 'Billed (PKR)', 'Paid (PKR)', 'Outstanding Arrears (PKR)', 'Status'));

            $bills = $this->BillingBill->find('all', array(
                'conditions' => array(
                    'BillingBill.society_id' => $societyId,
                    'BillingBill.payment_status !=' => 'paid'
                ),
                'recursive' => 2,
                'order' => array('BillingBill.id' => 'DESC')
            ));

            foreach ($bills as $b) {
                $bill = $b['BillingBill'];
                $bAmount = !empty($bill['total_amount']) ? (float)$bill['total_amount'] : 0;
                $pAmount = !empty($bill['paid_amount']) ? (float)$bill['paid_amount'] : 0;
                $balAmount = max(0, $bAmount - $pAmount);
                if ($balAmount <= 0) continue;

                $rId = !empty($bill['reading_id']) ? (int)$bill['reading_id'] : 0;
                $pId = !empty($bill['billing_property_id']) ? (int)$bill['billing_property_id'] : 0;
                $mNo = 'N/A';
                if (!empty($rId) && isset($readingMeterMap[$rId])) {
                    $mNo = $readingMeterMap[$rId];
                } elseif (!empty($pId) && isset($meterMapByProp[$pId])) {
                    $mNo = $meterMapByProp[$pId];
                }

                $dueDate = !empty($bill['due_date']) ? $bill['due_date'] : date('Y-m-d');
                $dueTs = strtotime($dueDate);
                $overdueDays = (time() > $dueTs) ? floor((time() - $dueTs) / 86400) : 0;
                $bMonth = !empty($bill['billing_period']) ? $bill['billing_period'] : (!empty($bill['issue_date']) ? date('Y-m', strtotime($bill['issue_date'])) : date('Y-m'));
                $fMonth = (preg_match('/^\d{4}-\d{2}$/', $bMonth)) ? date('M-Y', strtotime($bMonth . '-01')) : $bMonth;

                fputcsv($output, array(
                    !empty($bill['bill_number']) ? $bill['bill_number'] : ('#' . $bill['id']),
                    !empty($b['BillingProperty']['BillingClient']['name']) ? $b['BillingProperty']['BillingClient']['name'] : (!empty($b['BillingClient']['name']) ? $b['BillingClient']['name'] : 'N/A'),
                    !empty($b['BillingProperty']['BillingClient']['phone']) ? $b['BillingProperty']['BillingClient']['phone'] : (!empty($b['BillingClient']['phone']) ? $b['BillingClient']['phone'] : 'N/A'),
                    !empty($b['BillingProperty']['title']) ? $b['BillingProperty']['title'] : 'N/A',
                    $mNo,
                    $fMonth,
                    $dueDate,
                    $overdueDays,
                    number_format($bAmount, 2),
                    number_format($pAmount, 2),
                    number_format($balAmount, 2),
                    ucfirst(!empty($bill['payment_status']) ? $bill['payment_status'] : 'unpaid')
                ));
            }
        } elseif ($type == 'readings') {
            // HISTORICAL METER READINGS EXPORT
            fputcsv($output, array('Reading Date', 'Month', 'Consumer Name', 'Property Unit', 'Meter Number', 'Utility Type', 'Previous Reading', 'Current Reading', 'Units Consumed', 'Reader Name'));

            $readings = $this->BillingReading->find('all', array(
                'conditions' => array('BillingReading.society_id' => $societyId),
                'recursive' => 2,
                'order' => array('BillingReading.id' => 'DESC')
            ));

            foreach ($readings as $r) {
                $reading = $r['BillingReading'];
                $rMonth = !empty($reading['reading_month']) ? $reading['reading_month'] : date('Y-m');
                $fMonth = (preg_match('/^\d{4}-\d{2}$/', $rMonth)) ? date('M-Y', strtotime($rMonth . '-01')) : $rMonth;

                fputcsv($output, array(
                    !empty($reading['reading_date']) ? $reading['reading_date'] : $reading['created_at'],
                    $fMonth,
                    !empty($r['BillingProperty']['BillingClient']['name']) ? $r['BillingProperty']['BillingClient']['name'] : 'N/A',
                    !empty($r['BillingProperty']['title']) ? $r['BillingProperty']['title'] : 'N/A',
                    !empty($r['BillingMeter']['meter_number']) ? $r['BillingMeter']['meter_number'] : 'N/A',
                    !empty($r['BillingMeter']['utility_type']) ? $r['BillingMeter']['utility_type'] : 'Electricity',
                    number_format((float)$reading['previous_reading'], 2),
                    number_format((float)$reading['current_reading'], 2),
                    number_format((float)$reading['units_consumed'], 1),
                    !empty($reading['reader_name']) ? $reading['reader_name'] : 'Admin'
                ));
            }
        } else {
            // DETAILED CONSUMER COLLECTION SUMMARY EXPORT
            fputcsv($output, array('Bill #', 'Consumer Name', 'Phone Number', 'Property Unit', 'Meter Number', 'Utility Type', 'Period', 'Issue Date', 'Due Date', 'Billed (PKR)', 'Paid (PKR)', 'Balance (PKR)', 'Status'));

            $bConds = array('BillingBill.society_id' => $societyId);
            if ($month != 'all') $bConds['BillingBill.billing_period'] = $month;
            if ($status != 'all') $bConds['BillingBill.payment_status'] = $status;

            $bills = $this->BillingBill->find('all', array(
                'conditions' => $bConds,
                'recursive' => 2,
                'order' => array('BillingBill.id' => 'DESC')
            ));

            foreach ($bills as $b) {
                $bill = $b['BillingBill'];
                $bAmount = !empty($bill['total_amount']) ? (float)$bill['total_amount'] : 0;
                $pAmount = !empty($bill['paid_amount']) ? (float)$bill['paid_amount'] : 0;
                $balAmount = max(0, $bAmount - $pAmount);
                $bMonth = !empty($bill['billing_period']) ? $bill['billing_period'] : (!empty($bill['issue_date']) ? date('Y-m', strtotime($bill['issue_date'])) : date('Y-m'));
                $fMonth = (preg_match('/^\d{4}-\d{2}$/', $bMonth)) ? date('M-Y', strtotime($bMonth . '-01')) : $bMonth;

                $rId = !empty($bill['reading_id']) ? (int)$bill['reading_id'] : 0;
                $pId = !empty($bill['billing_property_id']) ? (int)$bill['billing_property_id'] : 0;
                $mNo = 'N/A';
                if (!empty($rId) && isset($readingMeterMap[$rId])) {
                    $mNo = $readingMeterMap[$rId];
                } elseif (!empty($pId) && isset($meterMapByProp[$pId])) {
                    $mNo = $meterMapByProp[$pId];
                }

                fputcsv($output, array(
                    !empty($bill['bill_number']) ? $bill['bill_number'] : ('#' . $bill['id']),
                    !empty($b['BillingProperty']['BillingClient']['name']) ? $b['BillingProperty']['BillingClient']['name'] : (!empty($b['BillingClient']['name']) ? $b['BillingClient']['name'] : 'N/A'),
                    !empty($b['BillingProperty']['BillingClient']['phone']) ? $b['BillingProperty']['BillingClient']['phone'] : (!empty($b['BillingClient']['phone']) ? $b['BillingClient']['phone'] : 'N/A'),
                    !empty($b['BillingProperty']['title']) ? $b['BillingProperty']['title'] : 'N/A',
                    $mNo,
                    !empty($bill['utility_type']) ? $bill['utility_type'] : 'Electricity',
                    $fMonth,
                    !empty($bill['issue_date']) ? $bill['issue_date'] : $bill['created_at'],
                    !empty($bill['due_date']) ? $bill['due_date'] : date('Y-m-d'),
                    number_format($bAmount, 2),
                    number_format($pAmount, 2),
                    number_format($balAmount, 2),
                    ucfirst(!empty($bill['payment_status']) ? $bill['payment_status'] : 'unpaid')
                ));
            }
        }

        fclose($output);
        exit();
    }

    /**
     * Direct PDF Export / Standalone Print View Engine
     */
    public function export_pdf($type = 'defaulters') {
        $this->layout = false;
        $societyId = !empty($this->request->query['society_id']) ? (int)$this->request->query['society_id'] : 1;

        $society = $this->BillingSociety->findById($societyId);
        $societyName = !empty($society['BillingSociety']['name']) ? $society['BillingSociety']['name'] : 'White Villas Lahore';
        $societyAddress = !empty($society['BillingSociety']['address']) ? $society['BillingSociety']['address'] : 'Main Boulevard, White Villas Lahore';
        $societyPhone = !empty($society['BillingSociety']['phone']) ? $society['BillingSociety']['phone'] : '+92 300 4075006';
        $societyEmail = !empty($society['BillingSociety']['email']) ? $society['BillingSociety']['email'] : 'info@whitevillas.com';

        // Pre-build 100% Fail-Proof Meter Lookup Maps
        $allMeters = $this->BillingMeter->find('all', array('recursive' => -1));
        $meterMapById = array();
        $meterMapByProp = array();
        foreach ($allMeters as $m) {
            $mId = (int)$m['BillingMeter']['id'];
            $pId = (int)$m['BillingMeter']['billing_property_id'];
            $mNo = $m['BillingMeter']['meter_number'];
            $meterMapById[$mId] = $mNo;
            if ($pId > 0 && !isset($meterMapByProp[$pId])) {
                $meterMapByProp[$pId] = $mNo;
            }
        }

        $allReadingsMap = $this->BillingReading->find('all', array('recursive' => -1));
        $readingMeterMap = array();
        foreach ($allReadingsMap as $r) {
            $rId = (int)$r['BillingReading']['id'];
            $mId = (int)$r['BillingReading']['meter_id'];
            if ($mId > 0 && isset($meterMapById[$mId])) {
                $readingMeterMap[$rId] = $meterMapById[$mId];
            }
        }

        $defaultersList = array();
        if ($type == 'defaulters') {
            $bills = $this->BillingBill->find('all', array(
                'conditions' => array(
                    'BillingBill.society_id' => $societyId,
                    'BillingBill.payment_status !=' => 'paid'
                ),
                'recursive' => 2,
                'order' => array('BillingBill.id' => 'DESC')
            ));

            foreach ($bills as $b) {
                $bill = $b['BillingBill'];
                $bAmount = !empty($bill['total_amount']) ? (float)$bill['total_amount'] : 0;
                $pAmount = !empty($bill['paid_amount']) ? (float)$bill['paid_amount'] : 0;
                $balAmount = max(0, $bAmount - $pAmount);
                if ($balAmount <= 0) continue;

                $rId = !empty($bill['reading_id']) ? (int)$bill['reading_id'] : 0;
                $pId = !empty($bill['billing_property_id']) ? (int)$bill['billing_property_id'] : 0;
                $mNo = 'N/A';
                if (!empty($rId) && isset($readingMeterMap[$rId])) {
                    $mNo = $readingMeterMap[$rId];
                } elseif (!empty($pId) && isset($meterMapByProp[$pId])) {
                    $mNo = $meterMapByProp[$pId];
                }

                $dueDate = !empty($bill['due_date']) ? $bill['due_date'] : date('Y-m-d');
                $dueTs = strtotime($dueDate);
                $overdueDays = (time() > $dueTs) ? floor((time() - $dueTs) / 86400) : 0;
                $bMonth = !empty($bill['billing_period']) ? $bill['billing_period'] : (!empty($bill['issue_date']) ? date('Y-m', strtotime($bill['issue_date'])) : date('Y-m'));

                $defaultersList[] = array(
                    'bill_number' => !empty($bill['bill_number']) ? $bill['bill_number'] : ('#' . $bill['id']),
                    'formatted_month' => (preg_match('/^\d{4}-\d{2}$/', $bMonth)) ? date('M-Y', strtotime($bMonth . '-01')) : $bMonth,
                    'consumer_name' => !empty($b['BillingProperty']['BillingClient']['name']) ? $b['BillingProperty']['BillingClient']['name'] : (!empty($b['BillingClient']['name']) ? $b['BillingClient']['name'] : 'N/A'),
                    'consumer_phone' => !empty($b['BillingProperty']['BillingClient']['phone']) ? $b['BillingProperty']['BillingClient']['phone'] : (!empty($b['BillingClient']['phone']) ? $b['BillingClient']['phone'] : 'N/A'),
                    'property_title' => !empty($b['BillingProperty']['title']) ? $b['BillingProperty']['title'] : 'N/A',
                    'meter_number' => $mNo,
                    'due_date' => $dueDate,
                    'overdue_days' => $overdueDays,
                    'total_amount' => $bAmount,
                    'paid_amount' => $pAmount,
                    'balance_amount' => $balAmount,
                    'status' => !empty($bill['payment_status']) ? $bill['payment_status'] : 'unpaid'
                );
            }
        }

        $this->set(compact('type', 'societyName', 'societyAddress', 'societyPhone', 'societyEmail', 'defaultersList'));
    }

    public function disconnection_order($billId = null)
    {
        $this->layout = false;
        $bill = $this->BillingBill->find('first', array(
            'conditions' => array('BillingBill.id' => (int)$billId),
            'contain' => array('BillingProperty', 'BillingClient')
        ));

        if(empty($bill)) {
            throw new NotFoundException(__('Bill not found.'));
        }

        $societyId = !empty($bill['BillingBill']['society_id']) ? (int)$bill['BillingBill']['society_id'] : 1;
        $enabled = $this->BillingSetting->getSetting('subm_feat_defaulter_recovery', '1', $societyId);

        $society = $this->BillingSociety->findById($societyId);
        $socData = !empty($society['BillingSociety']) ? $society['BillingSociety'] : array(
            'name' => 'WHITE VILLAS LAHORE',
            'address' => 'Awami Block White Villas Bahria Town Lahore, PAKISTAN',
            'phone' => '+92339 1101156',
            'email' => 'billing@whitevillas.com.pk'
        );

        // Meter Lookup
        $meter = null;
        if(!empty($bill['BillingBill']['billing_property_id'])) {
            $meter = $this->BillingMeter->find('first', array(
                'conditions' => array('BillingMeter.billing_property_id' => (int)$bill['BillingBill']['billing_property_id'])
            ));
        }

        // Reading Lookup
        $reading = null;
        if(!empty($bill['BillingBill']['reading_id'])) {
            $reading = $this->BillingReading->findById((int)$bill['BillingBill']['reading_id']);
        } elseif(!empty($meter)) {
            $reading = $this->BillingReading->find('first', array(
                'conditions' => array('BillingReading.meter_id' => (int)$meter['BillingMeter']['id']),
                'order' => array('BillingReading.id' => 'DESC')
            ));
        }

        // Overdue & Stage Calculation
        $dueDate = !empty($bill['BillingBill']['due_date']) ? $bill['BillingBill']['due_date'] : date('Y-m-d');
        $dueTs = strtotime($dueDate);
        $overdueDays = (time() > $dueTs) ? floor((time() - $dueTs) / 86400) : 0;
        
        $stage = 'DISCONNECTION READY';
        if($overdueDays <= 7) $stage = 'GRACE PERIOD';
        elseif($overdueDays <= 15) $stage = 'FIRST NOTICE SENT';
        elseif($overdueDays <= 30) $stage = 'FINAL WARNING (RED-FLAG)';

        // Logo Base64
        $logoBase64 = '';
        $logoPath = WWW_ROOT . 'img' . DS . 'logo.png';
        if(file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $this->set(compact('bill', 'socData', 'societyId', 'meter', 'reading', 'overdueDays', 'stage', 'enabled', 'logoBase64'));
    }

    public function line_loss_audit()
    {
        $societyId = !empty($this->request->query['society_id']) ? (int)$this->request->query['society_id'] : 1;
        $month = !empty($this->request->query['month']) ? trim($this->request->query['month']) : date('Y-m');

        $enabled = $this->BillingSetting->getSetting('subm_feat_line_loss_audit', '1', $societyId);
        $elecRate = (float)$this->BillingSetting->getSetting('subm_rate_elec', 58.34, $societyId);
        $waterRate = (float)$this->BillingSetting->getSetting('subm_rate_water', 100.0, $societyId);
        $gasRate = (float)$this->BillingSetting->getSetting('subm_rate_gas', 350.0, $societyId);

        // 1. Load All Main Feeder Stations / Transformers & Water Pumps & Gas Incomers for this Society
        $allFeeders = $this->BillingFeeder->find('all', array(
            'conditions' => array('BillingFeeder.society_id' => $societyId),
            'order' => array('BillingFeeder.id' => 'ASC')
        ));

        // Group into Transformers (Electricity), Water Pumps, and Gas Stations
        $transformers = array();
        $waterPumps = array();
        $gasStations = array();
        $transformerListMap = array();

        foreach($allFeeders as $fRow) {
            $fCat = !empty($fRow['BillingFeeder']['feeder_category']) ? $fRow['BillingFeeder']['feeder_category'] : 'transformer';
            $uType = !empty($fRow['BillingFeeder']['utility_type']) ? $fRow['BillingFeeder']['utility_type'] : 'Electricity';

            if($fCat === 'water_pump' || strtolower($uType) === 'water') {
                $waterPumps[] = $fRow;
            } elseif($fCat === 'gas_station' || strtolower($uType) === 'gas') {
                $gasStations[] = $fRow;
            } else {
                $transformers[] = $fRow;
                $transformerListMap[(int)$fRow['BillingFeeder']['id']] = $fRow['BillingFeeder']['name'];
            }
        }

        // 2. Load Monthly Feeder Readings for selected month
        $feederReadings = $this->BillingFeederReading->find('all', array(
            'conditions' => array(
                'BillingFeederReading.society_id' => $societyId,
                'BillingFeederReading.reading_month' => $month
            )
        ));
        $feederReadingMap = array();
        foreach($feederReadings as $fr) {
            $feederReadingMap[(int)$fr['BillingFeederReading']['feeder_id']] = $fr['BillingFeederReading'];
        }

        // 3. Process Water Pumps Consumption & Auxiliary Transformer Mapping
        $waterPumpAuditList = array();
        $auxPumpUnitsByTrans = array();
        $totalWaterPumpUnits = 0.0;
        $totalWaterPumpCostPkr = 0.0;
        $totalWaterFlowUnits = 0.0;
        $totalWaterHours = 0.0;

        foreach($waterPumps as $wp) {
            $wpId = (int)$wp['BillingFeeder']['id'];
            $wpReading = isset($feederReadingMap[$wpId]) ? $feederReadingMap[$wpId] : null;

            $wpUnits = (!empty($wpReading) && (float)$wpReading['units_consumed'] > 0) ? (float)$wpReading['units_consumed'] : 0.0;
            $wpFlow = (!empty($wpReading) && isset($wpReading['flow_units'])) ? (float)$wpReading['flow_units'] : 0.0;
            $wpHours = (!empty($wpReading) && isset($wpReading['operating_hours'])) ? (float)$wpReading['operating_hours'] : 0.0;
            
            $wpCost = (!empty($wpReading) && (float)$wpReading['operating_cost_pkr'] > 0) ? (float)$wpReading['operating_cost_pkr'] : round($wpUnits * $elecRate, 2);

            // Auxiliary connection to parent electric transformer
            $connTransId = !empty($wp['BillingFeeder']['connected_feeder_id']) ? (int)$wp['BillingFeeder']['connected_feeder_id'] : 0;
            if($connTransId > 0) {
                if(!isset($auxPumpUnitsByTrans[$connTransId])) {
                    $auxPumpUnitsByTrans[$connTransId] = 0.0;
                }
                $auxPumpUnitsByTrans[$connTransId] += $wpUnits;
            }

            $totalWaterPumpUnits += $wpUnits;
            $totalWaterPumpCostPkr += $wpCost;
            $totalWaterFlowUnits += $wpFlow;
            $totalWaterHours += $wpHours;

            // Efficiency: Gallons / Liters per kWh
            $efficiency = ($wpUnits > 0 && $wpFlow > 0) ? round($wpFlow / $wpUnits, 1) : 0.0;

            $waterPumpAuditList[] = array(
                'feeder' => $wp['BillingFeeder'],
                'reading' => $wpReading,
                'connected_transformer_name' => isset($transformerListMap[$connTransId]) ? $transformerListMap[$connTransId] : 'Independent / Direct',
                'units_consumed' => $wpUnits,
                'operating_cost_pkr' => $wpCost,
                'flow_units' => $wpFlow,
                'operating_hours' => $wpHours,
                'efficiency' => $efficiency
            );
        }

        // 4. Process Gas Incomers Consumption & Cost
        $gasAuditList = array();
        $totalGasUnits = 0.0;
        $totalGasCostPkr = 0.0;

        foreach($gasStations as $gs) {
            $gsId = (int)$gs['BillingFeeder']['id'];
            $gsReading = isset($feederReadingMap[$gsId]) ? $feederReadingMap[$gsId] : null;

            $gsUnits = (!empty($gsReading) && (float)$gsReading['units_consumed'] > 0) ? (float)$gsReading['units_consumed'] : 0.0;
            $gsCost = (!empty($gsReading) && (float)$gsReading['operating_cost_pkr'] > 0) ? (float)$gsReading['operating_cost_pkr'] : round($gsUnits * $gasRate, 2);

            $totalGasUnits += $gsUnits;
            $totalGasCostPkr += $gsCost;

            $gasAuditList[] = array(
                'feeder' => $gs['BillingFeeder'],
                'reading' => $gsReading,
                'units_consumed' => $gsUnits,
                'operating_cost_pkr' => $gsCost
            );
        }

        // 4. Load Consumer Readings for this society & month
        $consumerReadings = $this->BillingReading->find('all', array(
            'conditions' => array(
                'BillingReading.society_id' => $societyId,
                'BillingReading.reading_month' => $month
            ),
            'contain' => array('BillingProperty' => array('BillingClient'), 'BillingMeter')
        ));

        // Group consumer readings by feeder_id or transformer_name
        $consumerUnitsByFeeder = array();
        $totalConsumerUnits = 0.0;
        $totalExportUnits = 0.0;

        $defaultFeederId = !empty($transformers[0]['BillingFeeder']['id']) ? (int)$transformers[0]['BillingFeeder']['id'] : 1;

        foreach($consumerReadings as $cr) {
            $u = (float)$cr['BillingReading']['units_consumed'];
            $eu = isset($cr['BillingReading']['export_units']) ? (float)$cr['BillingReading']['export_units'] : 0.0;
            $totalConsumerUnits += $u;
            $totalExportUnits += $eu;

            $prop = !empty($cr['BillingProperty']) ? $cr['BillingProperty'] : array();
            $fId = !empty($prop['feeder_id']) ? (int)$prop['feeder_id'] : 0;

            if(!$fId && !empty($prop['transformer_name'])) {
                foreach($transformers as $f) {
                    if(strcasecmp(trim($f['BillingFeeder']['name']), trim($prop['transformer_name'])) === 0) {
                        $fId = (int)$f['BillingFeeder']['id'];
                        break;
                    }
                }
            }
            if(!$fId) $fId = $defaultFeederId;

            if(!isset($consumerUnitsByFeeder[$fId])) {
                $consumerUnitsByFeeder[$fId] = array(
                    'meters_count' => 0,
                    'consumed_units' => 0.0,
                    'export_units' => 0.0
                );
            }
            $consumerUnitsByFeeder[$fId]['meters_count']++;
            $consumerUnitsByFeeder[$fId]['consumed_units'] += $u;
            $consumerUnitsByFeeder[$fId]['export_units'] += $eu;
        }

        // 5. Calculate Detailed Transformer Reconciliation with Auxiliary Pump Deductions
        $transformerAuditList = array();
        $totalIncomerUnits = 0.0;
        $totalLostUnits = 0.0;

        foreach($transformers as $f) {
            $fId = (int)$f['BillingFeeder']['id'];
            $fName = $f['BillingFeeder']['name'];
            $readingRecord = isset($feederReadingMap[$fId]) ? $feederReadingMap[$fId] : null;

            $cData = isset($consumerUnitsByFeeder[$fId]) ? $consumerUnitsByFeeder[$fId] : array('meters_count' => 0, 'consumed_units' => 0.0, 'export_units' => 0.0);
            $subUnits = (float)$cData['consumed_units'];
            $auxPumpUnits = isset($auxPumpUnitsByTrans[$fId]) ? (float)$auxPumpUnitsByTrans[$fId] : 0.0;

            if(!empty($readingRecord) && (float)$readingRecord['units_consumed'] > 0) {
                $incomer = (float)$readingRecord['units_consumed'];
            } else {
                $incomer = ($subUnits > 0) ? round(($subUnits + $auxPumpUnits) * 1.10, 1) : 0.0;
            }

            // Total accounted energy = Consumer sub-meters + Auxiliary Water Pumps
            $totalAccounted = $subUnits + $auxPumpUnits;
            $lost = max(0.0, $incomer - $totalAccounted);
            $pct = ($incomer > 0) ? round(($lost / $incomer) * 100, 2) : 0.0;
            $deficitPkr = round($lost * $elecRate, 2);

            $status = 'NORMAL';
            if($pct > 18.0) {
                $status = 'CRITICAL THEFT ALERT';
            } elseif($pct > 10.0) {
                $status = 'MODERATE LOSS RISK';
            }

            $totalIncomerUnits += $incomer;
            $totalLostUnits += $lost;

            $transformerAuditList[] = array(
                'feeder' => $f['BillingFeeder'],
                'reading' => $readingRecord,
                'incomer_units' => $incomer,
                'sub_meters_count' => $cData['meters_count'],
                'sub_meter_units' => $subUnits,
                'aux_pump_units' => $auxPumpUnits,
                'export_units' => $cData['export_units'],
                'total_accounted' => $totalAccounted,
                'lost_units' => $lost,
                'loss_percent' => $pct,
                'deficit_pkr' => $deficitPkr,
                'status' => $status
            );
        }

        $overallLossPercent = ($totalIncomerUnits > 0) ? round(($totalLostUnits / $totalIncomerUnits) * 100, 2) : 0.0;
        $overallFinancialLossPkr = round($totalLostUnits * $elecRate, 2);

        $overallRiskLevel = 'NORMAL';
        if($overallLossPercent > 18.0) {
            $overallRiskLevel = 'CRITICAL THEFT ALERT';
        } elseif($overallLossPercent > 10.0) {
            $overallRiskLevel = 'MODERATE LOSS RISK';
        }

        // 6. Water Supply Cost Propagation & Resident Split Calculations
        $activePropertiesCount = $this->BillingProperty->find('count', array(
            'conditions' => array(
                'BillingProperty.society_id' => $societyId,
                'BillingProperty.billing_client_id >' => 0
            )
        ));
        if ($activePropertiesCount === 0) {
            $activePropertiesCount = $this->BillingProperty->find('count', array(
                'conditions' => array('BillingProperty.society_id' => $societyId)
            ));
        }
        if ($activePropertiesCount === 0) $activePropertiesCount = 1;

        $waterCostPerVilla = round($totalWaterPumpCostPkr / $activePropertiesCount, 2);
        $gasCostPerVilla = ($activePropertiesCount > 0) ? round($totalGasCostPkr / $activePropertiesCount, 2) : 0.0;
        $elecDeficitPerVilla = ($activePropertiesCount > 0) ? round($overallFinancialLossPkr / $activePropertiesCount, 2) : 0.0;

        // Fetch current active recurring charges in BillingExtraCharge
        $currentWaterChargeRow = $this->BillingExtraCharge->find('first', array(
            'conditions' => array(
                'BillingExtraCharge.society_id' => $societyId,
                'BillingExtraCharge.title LIKE' => '%Water%'
            )
        ));
        $currentWaterChargeAmt = !empty($currentWaterChargeRow['BillingExtraCharge']['amount']) ? (float)$currentWaterChargeRow['BillingExtraCharge']['amount'] : 0.0;

        $currentGasChargeRow = $this->BillingExtraCharge->find('first', array(
            'conditions' => array(
                'BillingExtraCharge.society_id' => $societyId,
                'BillingExtraCharge.title LIKE' => '%Gas%'
            )
        ));
        $currentGasChargeAmt = !empty($currentGasChargeRow['BillingExtraCharge']['amount']) ? (float)$currentGasChargeRow['BillingExtraCharge']['amount'] : 0.0;

        $currentElecChargeRow = $this->BillingExtraCharge->find('first', array(
            'conditions' => array(
                'BillingExtraCharge.society_id' => $societyId,
                'BillingExtraCharge.title LIKE' => '%Elec%'
            )
        ));
        $currentElecChargeAmt = !empty($currentElecChargeRow['BillingExtraCharge']['amount']) ? (float)$currentElecChargeRow['BillingExtraCharge']['amount'] : 0.0;

        // Build Posted Propagated Charges Map for this Society & Month
        $postedCharges = $this->BillingExtraCharge->find('all', array(
            'conditions' => array(
                'BillingExtraCharge.society_id' => $societyId,
                'BillingExtraCharge.status' => 'active'
            ),
            'order' => array('BillingExtraCharge.id' => 'DESC')
        ));

        $postedUtilityMap = array(
            'water' => null,
            'electricity' => null,
            'gas' => null
        );

        foreach($postedCharges as $pc) {
            $c = $pc['BillingExtraCharge'];
            $uType = !empty($c['utility_type']) ? strtolower($c['utility_type']) : '';
            $t = strtolower($c['title']);
            if($uType === 'water' || strpos($t, 'water') !== false) {
                if(empty($postedUtilityMap['water'])) {
                    $postedUtilityMap['water'] = $c;
                }
            } elseif($uType === 'electricity' || strpos($t, 'elec') !== false || strpos($t, 'loss') !== false) {
                if(empty($postedUtilityMap['electricity'])) {
                    $postedUtilityMap['electricity'] = $c;
                }
            } elseif($uType === 'gas' || strpos($t, 'gas') !== false) {
                if(empty($postedUtilityMap['gas'])) {
                    $postedUtilityMap['gas'] = $c;
                }
            }
        }

        // Available Months list
        $allMonthsRows = $this->BillingReading->find('all', array(
            'fields' => array('DISTINCT BillingReading.reading_month'),
            'conditions' => array('BillingReading.society_id' => $societyId),
            'order' => array('BillingReading.reading_month' => 'DESC')
        ));
        $availableMonths = array();
        foreach($allMonthsRows as $am) {
            $mVal = $am['BillingReading']['reading_month'];
            if(!empty($mVal)) $availableMonths[$mVal] = date('M Y', strtotime($mVal . '-01'));
        }
        if(!isset($availableMonths[$month])) {
            $availableMonths[$month] = date('M Y', strtotime($month . '-01'));
        }

        $societies = $this->BillingSociety->find('list', array('fields' => array('id', 'name')));
        $society = $this->BillingSociety->findById($societyId);
        $societyName = !empty($society['BillingSociety']['name']) ? $society['BillingSociety']['name'] : 'White Villas Lahore';

        // Keep backward compatibility for $feeders and $feederAuditList
        $feeders = $allFeeders;
        $feederAuditList = $transformerAuditList;

        $this->set(compact(
            'societyId', 'month', 'enabled', 'elecRate', 'waterRate', 'gasRate',
            'feeders', 'feederAuditList', 
            'transformers', 'transformerAuditList', 'transformerListMap',
            'waterPumps', 'waterPumpAuditList',
            'totalWaterPumpUnits', 'totalWaterPumpCostPkr', 'totalWaterFlowUnits', 'totalWaterHours',
            'gasStations', 'gasAuditList',
            'totalGasUnits', 'totalGasCostPkr', 'gasCostPerVilla', 'currentGasChargeAmt', 'currentGasChargeRow',
            'activePropertiesCount', 'waterCostPerVilla', 'currentWaterChargeAmt', 'currentWaterChargeRow',
            'elecDeficitPerVilla', 'currentElecChargeAmt', 'currentElecChargeRow',
            'postedUtilityMap',
            'totalIncomerUnits', 'totalConsumerUnits', 'totalExportUnits', 'totalLostUnits', 
            'overallLossPercent', 'overallFinancialLossPkr', 'overallRiskLevel', 
            'availableMonths', 'societies', 'societyName'
        ));
    }

    public function save_feeder()
    {
        $this->autoRender = false;
        if($this->request->is(array('post', 'put'))) {
            $data = $this->request->data['BillingFeeder'];
            $id = !empty($data['id']) ? (int)$data['id'] : null;
            $socId = !empty($data['society_id']) ? (int)$data['society_id'] : 1;
            $month = !empty($this->request->query['month']) ? $this->request->query['month'] : date('Y-m');

            if($id) {
                $this->BillingFeeder->id = $id;
            } else {
                $this->BillingFeeder->create();
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            if($this->BillingFeeder->save($data)) {
                $this->Session->setFlash(__('Feeder / Water Pump station saved successfully!'), 'flash', array('alert' => 'success'));
            } else {
                $this->Session->setFlash(__('Could not save feeder.'), 'flash', array('alert' => 'danger'));
            }
            return $this->redirect(array('action' => 'line_loss_audit', '?' => array('society_id' => $socId, 'month' => $month)));
        }
        return $this->redirect(array('action' => 'line_loss_audit'));
    }

    public function delete_feeder($id = null)
    {
        $feeder = $this->BillingFeeder->findById($id);
        $socId = !empty($feeder['BillingFeeder']['society_id']) ? (int)$feeder['BillingFeeder']['society_id'] : 1;
        $month = !empty($this->request->query['month']) ? $this->request->query['month'] : date('Y-m');
        
        if(!empty($feeder)) {
            $this->BillingFeeder->delete((int)$id);
            $this->Session->setFlash(__('Station deleted successfully.'), 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'line_loss_audit', '?' => array('society_id' => $socId, 'month' => $month)));
    }

    public function save_feeder_reading()
    {
        $this->autoRender = false;
        if($this->request->is(array('post', 'put'))) {
            $feederId = (int)$this->request->data['feeder_id'];
            $socId = !empty($this->request->data['society_id']) ? (int)$this->request->data['society_id'] : 1;
            $month = trim($this->request->data['reading_month']);
            $prev = (float)$this->request->data['previous_reading'];
            $curr = (float)$this->request->data['current_reading'];
            $multiplier = !empty($this->request->data['multiplier']) ? (float)$this->request->data['multiplier'] : 1.0;
            $units = isset($this->request->data['units_consumed']) && $this->request->data['units_consumed'] !== '' ? (float)$this->request->data['units_consumed'] : 0.0;
            
            if($units <= 0 && $curr >= $prev) {
                $units = ($curr - $prev) * $multiplier;
            }

            $flowUnits = isset($this->request->data['flow_units']) ? (float)$this->request->data['flow_units'] : 0.0;
            $opHours = isset($this->request->data['operating_hours']) ? (float)$this->request->data['operating_hours'] : 0.0;
            $opCost = isset($this->request->data['operating_cost_pkr']) && (float)$this->request->data['operating_cost_pkr'] > 0 ? (float)$this->request->data['operating_cost_pkr'] : 0.0;

            $existing = $this->BillingFeederReading->find('first', array(
                'conditions' => array(
                    'BillingFeederReading.feeder_id' => $feederId,
                    'BillingFeederReading.reading_month' => $month
                )
            ));

            $saveData = array(
                'society_id' => $socId,
                'feeder_id' => $feederId,
                'reading_month' => $month,
                'reading_date' => date('Y-m-d'),
                'previous_reading' => $prev,
                'current_reading' => $curr,
                'multiplier' => $multiplier,
                'units_consumed' => $units,
                'flow_units' => $flowUnits,
                'operating_hours' => $opHours,
                'operating_cost_pkr' => $opCost,
                'notes' => isset($this->request->data['notes']) ? trim($this->request->data['notes']) : ''
            );

            if(!empty($existing)) {
                $this->BillingFeederReading->id = (int)$existing['BillingFeederReading']['id'];
            } else {
                $this->BillingFeederReading->create();
                $saveData['created_at'] = date('Y-m-d H:i:s');
            }

            if($this->BillingFeederReading->save($saveData)) {
                $this->Session->setFlash(__('Monthly consumption reading recorded successfully!'), 'flash', array('alert' => 'success'));
            }
            return $this->redirect(array('action' => 'line_loss_audit', '?' => array('society_id' => $socId, 'month' => $month)));
        }
        return $this->redirect(array('action' => 'line_loss_audit'));
    }

    public function propagate_utility_charge()
    {
        $this->autoRender = false;
        if($this->request->is(array('post', 'put'))) {
            $socId = !empty($this->request->data['society_id']) ? (int)$this->request->data['society_id'] : 1;
            $month = !empty($this->request->data['month']) ? trim($this->request->data['month']) : date('Y-m');
            $utilityCat = !empty($this->request->data['utility_category']) ? strtolower(trim($this->request->data['utility_category'])) : 'water';
            $chargeAmount = !empty($this->request->data['charge_amount']) ? (float)$this->request->data['charge_amount'] : 0.0;
            $chargeTitle = !empty($this->request->data['charge_title']) ? trim($this->request->data['charge_title']) : '';
            $updateExisting = !empty($this->request->data['update_existing']) ? (int)$this->request->data['update_existing'] : 1;

            if ($chargeAmount <= 0) {
                $this->Session->setFlash(__('Please specify a valid charge amount to propagate.'), 'flash', array('alert' => 'warning'));
                return $this->redirect(array('action' => 'line_loss_audit', '?' => array('society_id' => $socId, 'month' => $month)));
            }

            if ($chargeTitle === '') {
                if ($utilityCat === 'electricity') {
                    $chargeTitle = 'Common Electricity & Line Loss Surcharge';
                } elseif ($utilityCat === 'gas') {
                    $chargeTitle = 'Gas Supply & Incomer Cost';
                } else {
                    $chargeTitle = 'Water Supply & Tube-well Operations';
                }
            }

            $searchPattern = '%Water%';
            if ($utilityCat === 'electricity') {
                $searchPattern = '%Elec%';
            } elseif ($utilityCat === 'gas') {
                $searchPattern = '%Gas%';
            }

            $existingCharge = null;
            if($updateExisting) {
                $existingCharge = $this->BillingExtraCharge->find('first', array(
                    'conditions' => array(
                        'BillingExtraCharge.society_id' => $socId,
                        'BillingExtraCharge.title LIKE' => $searchPattern
                    )
                ));
            }

            if(!empty($existingCharge)) {
                $this->BillingExtraCharge->id = (int)$existingCharge['BillingExtraCharge']['id'];
                $this->BillingExtraCharge->saveField('amount', $chargeAmount);
                $this->BillingExtraCharge->saveField('title', $chargeTitle);
                $this->BillingExtraCharge->saveField('billing_month', $month);
                $this->BillingExtraCharge->saveField('utility_type', ucfirst($utilityCat));
                $this->BillingExtraCharge->saveField('status', 'active');
                $this->Session->setFlash(sprintf(__('Propagated successfully! Recurring %s charge updated to PKR %s per property for period %s. All upcoming resident bills will reflect this cost.'), ucfirst($utilityCat), number_format($chargeAmount, 2), h($month)), 'flash', array('alert' => 'success'));
            } else {
                $this->BillingExtraCharge->create();
                $this->BillingExtraCharge->save(array('BillingExtraCharge' => array(
                    'society_id' => $socId,
                    'title' => $chargeTitle,
                    'amount' => $chargeAmount,
                    'charge_type' => 'recurring',
                    'apply_to' => 'all',
                    'billing_month' => $month,
                    'utility_type' => ucfirst($utilityCat),
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                )));
                $this->Session->setFlash(sprintf(__('Created new recurring %s charge of PKR %s per property for period %s! All upcoming resident bills will reflect this cost.'), ucfirst($utilityCat), number_format($chargeAmount, 2), h($month)), 'flash', array('alert' => 'success'));
            }

            return $this->redirect(array('action' => 'line_loss_audit', '?' => array('society_id' => $socId, 'month' => $month)));
        }
        return $this->redirect(array('action' => 'line_loss_audit'));
    }

    public function delete_propagated_charge($chargeId = null)
    {
        $this->autoRender = false;
        $socId = !empty($this->request->query['society_id']) ? (int)$this->request->query['society_id'] : (!empty($this->request->data['society_id']) ? (int)$this->request->data['society_id'] : 1);
        $month = !empty($this->request->query['month']) ? trim($this->request->query['month']) : (!empty($this->request->data['month']) ? trim($this->request->data['month']) : date('Y-m'));
        $utilityCat = !empty($this->request->query['utility_category']) ? strtolower(trim($this->request->query['utility_category'])) : (!empty($this->request->data['utility_category']) ? strtolower(trim($this->request->data['utility_category'])) : '');

        if (!$chargeId && !empty($this->request->data['charge_id'])) {
            $chargeId = (int)$this->request->data['charge_id'];
        }
        if (!$chargeId && !empty($this->request->query['charge_id'])) {
            $chargeId = (int)$this->request->query['charge_id'];
        }

        // If no charge_id provided, look up by utility_category for this society
        if (!$chargeId && !empty($utilityCat)) {
            $searchPattern = '%Water%';
            if ($utilityCat === 'electricity') {
                $searchPattern = '%Elec%';
            } elseif ($utilityCat === 'gas') {
                $searchPattern = '%Gas%';
            }
            $targetCharge = $this->BillingExtraCharge->find('first', array(
                'conditions' => array(
                    'BillingExtraCharge.society_id' => $socId,
                    'OR' => array(
                        'BillingExtraCharge.utility_type LIKE' => $searchPattern,
                        'BillingExtraCharge.title LIKE' => $searchPattern
                    )
                ),
                'order' => array('BillingExtraCharge.id' => 'DESC')
            ));
            if (!empty($targetCharge['BillingExtraCharge']['id'])) {
                $chargeId = (int)$targetCharge['BillingExtraCharge']['id'];
            }
        }

        if ($chargeId > 0) {
            $charge = $this->BillingExtraCharge->findById($chargeId);
            if (!empty($charge)) {
                $title = $charge['BillingExtraCharge']['title'];
                $amount = (float)$charge['BillingExtraCharge']['amount'];
                $uType = !empty($charge['BillingExtraCharge']['utility_type']) ? $charge['BillingExtraCharge']['utility_type'] : 'Utility';

                $this->BillingExtraCharge->delete($chargeId);
                $this->Session->setFlash(sprintf(__('Propagated %s charge "%s" (PKR %s) has been successfully deleted/removed from resident bills for period %s.'), $uType, h($title), number_format($amount, 2), h($month)), 'flash', array('alert' => 'success'));
            } else {
                $this->Session->setFlash(__('Charge record not found or already deleted.'), 'flash', array('alert' => 'warning'));
            }
        } else {
            $this->Session->setFlash(__('No active propagated charge found to delete for this utility.'), 'flash', array('alert' => 'warning'));
        }

        return $this->redirect(array('action' => 'line_loss_audit', '?' => array('society_id' => $socId, 'month' => $month)));
    }

    public function propagate_water_charge()
    {
        return $this->propagate_utility_charge();
    }
}
