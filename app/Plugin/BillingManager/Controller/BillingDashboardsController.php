<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingDashboardsController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingSociety',
        'BillingManager.BillingBill',
        'BillingManager.BillingClient',
        'BillingManager.BillingProperty',
        'BillingManager.BillingMeter',
        'BillingManager.BillingReading',
        'BillingManager.BillingSetting'
    );

    public function index()
    {
        $societyId = $this->_getActiveSocietyId();
        $currentMonth = date('Y-m');

        // Filters from query
        $meterTypeFilter = isset($this->request->query['meter_type']) ? $this->request->query['meter_type'] : '';
        $fromDate = isset($this->request->query['from_date']) ? $this->request->query['from_date'] : '';
        $toDate = isset($this->request->query['to_date']) ? $this->request->query['to_date'] : '';

        // Societes count
        $societiesCount = $this->BillingSociety->find('count');

        // Clients & Properties Count
        $totalClients = $this->BillingClient->find('count', array('conditions' => array('BillingClient.society_id' => $societyId)));
        $totalProperties = $this->BillingProperty->find('count', array('conditions' => array('BillingProperty.society_id' => $societyId)));

        // Meter Counts & Breakdowns
        $totalMeters = $this->BillingMeter->find('count', array('conditions' => array('BillingMeter.society_id' => $societyId)));
        $elecMeters = $this->BillingMeter->find('count', array('conditions' => array('BillingMeter.society_id' => $societyId, 'BillingMeter.utility_type' => 'electricity')));
        $waterMeters = $this->BillingMeter->find('count', array('conditions' => array('BillingMeter.society_id' => $societyId, 'BillingMeter.utility_type' => 'water')));
        $gasMeters = $this->BillingMeter->find('count', array('conditions' => array('BillingMeter.society_id' => $societyId, 'BillingMeter.utility_type' => 'gas')));
        $metersWithNumber = $this->BillingMeter->find('count', array('conditions' => array('BillingMeter.society_id' => $societyId, 'BillingMeter.meter_number !=' => '')));

        // Bills query with optional filters
        $billConds = array('BillingBill.society_id' => $societyId);
        if(!empty($meterTypeFilter))
        {
            $billConds['BillingBill.utility_type'] = $meterTypeFilter;
        }
        if(!empty($fromDate))
        {
            $billConds['BillingBill.issue_date >='] = $fromDate;
        }
        if(!empty($toDate))
        {
            $billConds['BillingBill.issue_date <='] = $toDate;
        }

        $allBills = $this->BillingBill->find('all', array('conditions' => $billConds, 'order' => array('BillingBill.id' => 'DESC')));

        $totalBillsCount = count($allBills);
        $paidBillsCount = 0;
        $unpaidBillsCount = 0;
        $partialBillsCount = 0;

        $totalBillAmount = 0.0;
        $paidBillAmount = 0.0;
        $overdueAmount = 0.0;
        $unpaidAmount = 0.0;

        $today = date('Y-m-d');

        foreach($allBills as $b)
        {
            $bi = $b['BillingBill'];
            $tot = (float)$bi['total_amount'];
            $paid = (float)$bi['paid_amount'];
            $bal = max(0.0, $tot - $paid);
            $st = strtolower($bi['payment_status']);

            $totalBillAmount += $tot;
            $paidBillAmount += $paid;

            if($st === 'paid' || ($paid >= $tot && $tot > 0)) {
                $paidBillsCount++;
            } elseif($st === 'partial' || ($paid > 0 && $paid < $tot)) {
                $partialBillsCount++;
                $unpaidAmount += $bal;
            } else {
                $unpaidBillsCount++;
                $unpaidAmount += $bal;
            }

            if(!empty($bi['due_date']) && $bi['due_date'] < $today && $st !== 'paid') {
                $overdueAmount += $bal;
            }
        }

        // Monthly Billing Graph Data (Last 6 Months)
        $monthlyGraphData = array();
        for($i = 5; $i >= 0; $i--)
        {
            $mKey = date('Y-m', strtotime("-{$i} month"));
            $mLabel = date('M-y', strtotime("-{$i} month"));
            
            $mTotal = 0.0;
            $mPaid = 0.0;
            $mDue = 0.0;

            foreach($allBills as $b)
            {
                if(strpos((string)$b['BillingBill']['billing_period'], $mKey) === 0 || strpos((string)$b['BillingBill']['issue_date'], $mKey) === 0)
                {
                    $t = (float)$b['BillingBill']['total_amount'];
                    $p = (float)$b['BillingBill']['paid_amount'];
                    $mTotal += $t;
                    $mPaid += $p;
                    $mDue += max(0.0, $t - $p);
                }
            }

            $monthlyGraphData[] = array(
                'month' => $mLabel,
                'total' => $mTotal,
                'paid' => $mPaid,
                'due' => $mDue
            );
        }

        // Dropdown Lists for Top Action Bar
        $createdBillsList = $this->BillingBill->find('list', array(
            'fields' => array('BillingBill.id', 'BillingBill.bill_number'),
            'conditions' => array('BillingBill.society_id' => $societyId),
            'order' => array('BillingBill.id' => 'DESC'),
            'limit' => 50
        ));

        $meterLedgersList = $this->BillingMeter->find('list', array(
            'fields' => array('BillingMeter.id', 'BillingMeter.meter_number'),
            'conditions' => array('BillingMeter.society_id' => $societyId),
            'order' => array('BillingMeter.id' => 'ASC'),
            'limit' => 50
        ));

        // Recent 10 Bills
        $recentBills = array_slice($allBills, 0, 10);

        $this->set(compact(
            'societiesCount',
            'totalClients',
            'totalProperties',
            'totalMeters',
            'elecMeters',
            'waterMeters',
            'gasMeters',
            'metersWithNumber',
            'totalBillsCount',
            'paidBillsCount',
            'unpaidBillsCount',
            'partialBillsCount',
            'totalBillAmount',
            'paidBillAmount',
            'overdueAmount',
            'unpaidAmount',
            'monthlyGraphData',
            'createdBillsList',
            'meterLedgersList',
            'recentBills',
            'meterTypeFilter',
            'fromDate',
            'toDate',
            'societyId'
        ));
    }
}
