<?php
App::uses('AdminAppController', 'Admin.Controller');
App::uses('CakeNumber', 'Utility');
App::uses('CakeEmail', 'Network/Email');
App::uses('Cache', 'Cache');
class DealsController extends AdminAppController {
    public $helpers = array('Html', 'Form','Session','Paginator','NumToWord','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('Deal.id'=>'desc'));
    protected $_dealControlStatuses = array('Active','Blocked','Transferd','Permanent Blocked','Refund','Refund-Running');
    protected $_dealPaymentBlockedStatuses = array('Transferd','Transferred','Permanent Blocked','Pemanent Blocked','Refund','Refund-Running');
    protected $_dealPaymentStatuses = array('Paid','Partial');
    protected $_nomineeRelationOptions = array(
        'Father' => 'Father',
        'Mother' => 'Mother',
        'Wife' => 'Wife',
        'Husband' => 'Husband',
        'Son' => 'Son',
        'Brother' => 'Brother',
        'Sister' => 'Sister',
        'Friend' => 'Friend'
    );

    protected function _ensurePropertyHistoryTable()
    {
        static $ready = null;
        if($ready !== null)
        {
            return $ready;
        }
        $this->loadModel('PropertyHistory');
        try
        {
            $this->PropertyHistory->query("CREATE TABLE IF NOT EXISTS `property_histories` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `property_id` bigint(20) NOT NULL,
                `deal_id` bigint(20) DEFAULT NULL,
                `client_id` bigint(20) DEFAULT NULL,
                `project_id` bigint(20) DEFAULT NULL,
                `actor_user_id` bigint(20) DEFAULT NULL,
                `event_type` varchar(50) NOT NULL DEFAULT 'change',
                `title` varchar(191) DEFAULT NULL,
                `old_value` text NULL,
                `new_value` text NULL,
                `amount_delta` decimal(14,2) NOT NULL DEFAULT 0.00,
                `meta_json` longtext NULL,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `property_id` (`property_id`),
                KEY `deal_id` (`deal_id`),
                KEY `client_id` (`client_id`),
                KEY `event_type` (`event_type`),
                KEY `created` (`created`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
            $this->PropertyHistory->schema(true);
            $ready = true;
            return $ready;
        }
        catch(Exception $e)
        {
            $ready = false;
            return $ready;
        }
    }

    protected function _recordPropertyHistory($propertyId, $eventType, $title, $oldValue='', $newValue='', $options=array())
    {
        if(!$this->_ensurePropertyHistoryTable())
        {
            return false;
        }
        $this->PropertyHistory->create();
        return (bool)$this->PropertyHistory->save(array('PropertyHistory'=>array(
            'property_id'=>(int)$propertyId,
            'deal_id'=>isset($options['deal_id']) ? (int)$options['deal_id'] : null,
            'client_id'=>isset($options['client_id']) ? (int)$options['client_id'] : null,
            'project_id'=>isset($options['project_id']) ? (int)$options['project_id'] : null,
            'actor_user_id'=>(int)$this->luserId,
            'event_type'=>$eventType,
            'title'=>$title,
            'old_value'=>(string)$oldValue,
            'new_value'=>(string)$newValue,
            'amount_delta'=>isset($options['amount_delta']) ? round((float)$options['amount_delta'],2) : 0,
            'meta_json'=>!empty($options['meta']) ? json_encode($options['meta']) : null
        )),false);
    }

    protected function _ensureDealNomineeColumns()
    {
        $nomineeColumns = array(
            'nominee_name' => "ALTER TABLE `deals` ADD `nominee_name` VARCHAR(150) NULL",
            'nominee_cnic' => "ALTER TABLE `deals` ADD `nominee_cnic` VARCHAR(25) NULL",
            'nominee_mobile' => "ALTER TABLE `deals` ADD `nominee_mobile` VARCHAR(25) NULL",
            'nominee_relation' => "ALTER TABLE `deals` ADD `nominee_relation` VARCHAR(25) NULL",
            'occupation' => "ALTER TABLE `deals` ADD `occupation` VARCHAR(150) NULL AFTER `nominee_relation`"
        );
        $schemaChanged = false;
        foreach($nomineeColumns as $fieldName=>$sql)
        {
            if($this->Deal->hasField($fieldName))
            {
                continue;
            }
            try
            {
                $this->Deal->query($sql);
                $schemaChanged = true;
            }
            catch(Exception $e)
            {
                // Keep flow running; caller will fallback if column still missing.
            }
        }
        $dealCacheSources = $this->Deal->cacheSources;
        $dealDataSource = $this->Deal->getDataSource();
        $dataSourceCacheSources = $dealDataSource->cacheSources;
        $dealSchemaCacheKey = ConnectionManager::getSourceName($dealDataSource).'_'.$dealDataSource->fullTableName($this->Deal, false);
        Cache::delete($dealSchemaCacheKey, '_cake_model_');
        $this->Deal->cacheSources = false;
        $dealDataSource->cacheSources = false;
        $this->Deal->schema(true);
        $this->Deal->cacheSources = $dealCacheSources;
        $dealDataSource->cacheSources = $dataSourceCacheSources;
        if($schemaChanged)
        {
            $this->Deal->create();
        }
        foreach(array_keys($nomineeColumns) as $fieldName)
        {
            if(!$this->Deal->hasField($fieldName))
            {
                return false;
            }
        }
        return true;
    }

    protected function _dealPaymentVirtualFieldSql()
    {
        return 'IFNULL((SELECT SUM(DealPayment.emi) FROM `deals_payments` as `DealPayment` WHERE `DealPayment`.`deal_id`=`Deal`.`id`),0)';
    }

    protected function _setDealPaymentVirtualField()
    {
        $paymentSql = $this->_dealPaymentVirtualFieldSql();
        $this->Deal->virtualFields = array('payment' => $paymentSql);
        return $paymentSql;
    }

    protected function _extractDealStatusFilters($params=array())
    {
        $dealPaymentStatus = '';
        $dealControlStatus = '';
        if(isset($params['deal_payment_status']))
        {
            $dealPaymentStatus = trim($params['deal_payment_status']);
        }
        if(isset($params['deal_control_status']))
        {
            $dealControlStatus = trim($params['deal_control_status']);
        }
        if(!in_array($dealPaymentStatus,$this->_dealPaymentStatuses))
        {
            $dealPaymentStatus = '';
        }
        if(strlen($dealControlStatus)>0 && !in_array($dealControlStatus,$this->_dealControlStatuses))
        {
            $dealControlStatus = '';
        }
        return array($dealPaymentStatus,$dealControlStatus);
    }

    protected function _buildDealControlFilterCondition($dealControlStatus)
    {
        if(strlen($dealControlStatus)<=0 || !$this->Deal->hasField('deal_status'))
        {
            return array();
        }
        if($dealControlStatus==='Active')
        {
            return array('OR'=>array(
                array('Deal.deal_status'=>'Active'),
                array('Deal.deal_status'=>null),
                array('Deal.deal_status'=>'')
            ));
        }
        if($dealControlStatus==='Transferd')
        {
            return array('Deal.deal_status'=>array('Transferd','Transferred'));
        }
        if($dealControlStatus==='Permanent Blocked')
        {
            return array('Deal.deal_status'=>array('Permanent Blocked','Pemanent Blocked'));
        }
        return array('Deal.deal_status'=>$dealControlStatus);
    }

    protected function _buildDealPaymentStatusCondition($dealPaymentStatus,$paymentSql)
    {
        if($dealPaymentStatus==='Paid')
        {
            return '(Deal.total_amount - '.$paymentSql.') <= 0';
        }
        if($dealPaymentStatus==='Partial')
        {
            return '(Deal.total_amount - '.$paymentSql.') > 0';
        }
        return '';
    }

    protected function _dealChargesTableReady()
    {
        static $isReady = null;
        if($isReady !== null)
        {
            return $isReady;
        }
        $isReady = false;
        try
        {
            $rows = (array)$this->Deal->query("SHOW TABLES LIKE 'deal_charges'");
            $isReady = !empty($rows);
        }
        catch(Exception $e)
        {
            $isReady = false;
        }
        return $isReady;
    }

    protected function _dealChargeTotalsByDealIds($dealIds = array())
    {
        $map = array();
        $normalizedIds = array();
        foreach((array)$dealIds as $singleId)
        {
            $singleId = (int)$singleId;
            if($singleId > 0)
            {
                $normalizedIds[$singleId] = $singleId;
            }
        }
        if(empty($normalizedIds) || !$this->_dealChargesTableReady())
        {
            return $map;
        }
        $idSql = implode(',', array_values($normalizedIds));
        if($idSql === '')
        {
            return $map;
        }
        try
        {
            $rows = (array)$this->Deal->query(
                "SELECT `deal_id`, COALESCE(SUM(`total_amount`),0) AS charge_total ".
                "FROM `deal_charges` WHERE `status`=1 AND `deal_id` IN (".$idSql.") GROUP BY `deal_id`"
            );
            foreach($rows as $row)
            {
                $dealId = 0;
                $chargeTotal = 0.0;
                if(isset($row['deal_charges']['deal_id']))
                {
                    $dealId = (int)$row['deal_charges']['deal_id'];
                    $chargeTotal = isset($row['deal_charges']['charge_total']) ? (float)$row['deal_charges']['charge_total'] : 0.0;
                }
                elseif(isset($row[0]['deal_id']))
                {
                    $dealId = (int)$row[0]['deal_id'];
                    $chargeTotal = isset($row[0]['charge_total']) ? (float)$row[0]['charge_total'] : 0.0;
                }
                if($dealId > 0)
                {
                    $map[$dealId] = round(max(0, $chargeTotal), 2);
                }
            }
        }
        catch(Exception $e)
        {
            $map = array();
        }
        return $map;
    }

    protected function _dealListTotalAmountValue($dealRow = array(), $dealChargeTotal = 0.0)
    {
        $dealData = isset($dealRow['Deal']) ? (array)$dealRow['Deal'] : (array)$dealRow;
        $storedTotal = isset($dealData['total_amount']) ? (float)$dealData['total_amount'] : 0.0;
        $baseAmount = isset($dealData['amount']) ? (float)$dealData['amount'] : 0.0;
        $discount = isset($dealData['discount']) ? (float)$dealData['discount'] : 0.0;
        $dealChargeTotal = max(0, (float)$dealChargeTotal);

        // The saved deal total is the final authoritative payable amount after discount.
        // Prefer it for list rendering so deal table matches the deal ledger / payment pages.
        if($storedTotal > 0)
        {
            return round($storedTotal, 2);
        }

        if($baseAmount <= 0 && $storedTotal > 0)
        {
            $baseAmount = max(0, $storedTotal - $dealChargeTotal);
        }

        $afterDiscount = round($baseAmount - max(0, $discount), 2);
        if($afterDiscount < 0)
        {
            $afterDiscount = 0;
        }

        $computedTotal = round($afterDiscount + $dealChargeTotal, 2);
        if($computedTotal <= 0 && $storedTotal > 0)
        {
            $computedTotal = round($storedTotal, 2);
        }
        if($computedTotal < 0)
        {
            $computedTotal = 0;
        }
        return $computedTotal;
    }

    protected function _dealMoneyValue($value)
    {
        if(is_array($value) || is_object($value))
        {
            return 0.0;
        }
        $value = trim((string)$value);
        if($value === '')
        {
            return 0.0;
        }
        $value = preg_replace('/[^0-9\.\-]/', '', $value);
        if($value === '' || $value === '-' || $value === '.')
        {
            return 0.0;
        }
        return round((float)$value, 2);
    }

    protected function _cleanupDealChargeArtifacts($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return;
        }
        try
        {
            $this->Deal->query("DELETE FROM `plans_payments` WHERE `deal_id`=".$dealId." AND `is_deal_charge`=1");
        }
        catch(Exception $e)
        {
            // Charge cycle cleanup is best effort only.
        }
        try
        {
            $this->Deal->query("DELETE FROM `deal_charges` WHERE `deal_id`=".$dealId);
        }
        catch(Exception $e)
        {
            // The deal charge table may not exist on older installs.
        }
    }

    protected function _canUseAgencyFilters()
    {
        return $this->_isSuperAdminSession();
    }

    protected function _resolveDealProjectId($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return 0;
        }
        $dealRow = $this->Deal->find('first', array(
            'fields' => array('Deal.id', 'Property.project_id'),
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id'))
            ),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
        if(!empty($dealRow['Property']['project_id']))
        {
            return (int)$dealRow['Property']['project_id'];
        }
        return 0;
    }

    protected function _canAccessDealRecord($dealRow)
    {
        if($this->_isSuperAdminSession() || strtoupper(trim((string)$this->userType)) === 'ADR')
        {
            return true;
        }
        $dealRow = (array)$dealRow;
        if(empty($dealRow['Deal']['id']))
        {
            return false;
        }
        $dealProjectId = 0;
        if(!empty($dealRow['Property']['project_id']))
        {
            $dealProjectId = (int)$dealRow['Property']['project_id'];
        }
        elseif(!empty($dealRow['Deal']['id']))
        {
            $dealProjectId = $this->_resolveDealProjectId((int)$dealRow['Deal']['id']);
        }
        if($this->_isAgencyAdminSession() || strtoupper(trim((string)$this->userType)) === 'AGY')
        {
            return ((int)$this->agencyId > 0 && (int)$this->agencyId === $dealProjectId);
        }
        if(!empty($dealRow['Deal']['user_id']) && (int)$dealRow['Deal']['user_id'] === (int)$this->luserId)
        {
            return true;
        }
        return false;
    }

    protected function _dealCommissionMeta($dealRow = array())
    {
        $deal = !empty($dealRow['Deal']) ? (array)$dealRow['Deal'] : (array)$dealRow;
        $propertyId = !empty($deal['property_id']) ? (int)$deal['property_id'] : 0;
        $propertyRow = array();
        if(!empty($dealRow['Property']))
        {
            $propertyRow = (array)$dealRow['Property'];
        }
        elseif($propertyId > 0)
        {
            $this->loadModel('Property');
            $propertyRow = (array)$this->Property->find('first', array(
                'fields' => array('Property.id','Property.project_id','Property.commission_type','Property.agent_commission','Property.name'),
                'conditions' => array('Property.id' => $propertyId),
                'recursive' => -1
            ));
            $propertyRow = !empty($propertyRow['Property']) ? (array)$propertyRow['Property'] : array();
        }
        $commissionType = !empty($propertyRow['commission_type']) ? trim((string)$propertyRow['commission_type']) : '';
        $commissionRule = !empty($propertyRow['agent_commission']) ? (float)$propertyRow['agent_commission'] : 0.0;
        $totalAmount = isset($deal['total_amount']) ? (float)$deal['total_amount'] : 0.0;
        $amount = 0.0;
        if($commissionRule > 0 && $totalAmount > 0)
        {
            if(strtolower($commissionType) === 'percentage')
            {
                $amount = round(($commissionRule * $totalAmount) / 100, 2);
            }
            elseif(strtolower($commissionType) === 'value')
            {
                $amount = round($commissionRule, 2);
            }
        }
        return array(
            'amount' => $amount,
            'type' => $commissionType,
            'rule' => $commissionRule,
            'project_id' => !empty($propertyRow['project_id']) ? (int)$propertyRow['project_id'] : 0,
            'property_name' => !empty($propertyRow['name']) ? (string)$propertyRow['name'] : ''
        );
    }

    protected function _findDealCommissionCreditRow($dealId, $agentId = 0)
    {
        $dealId = (int)$dealId;
        $agentId = (int)$agentId;
        if($dealId <= 0)
        {
            return array();
        }
        $this->loadModel('AwalletHistory');
        $conditions = array(
            'AwalletHistory.type' => 'CR',
            'AwalletHistory.amount >' => 0,
            'AwalletHistory.remarks LIKE' => '%Deal Commission #'.$dealId.'%'
        );
        if($agentId > 0)
        {
            $conditions['AwalletHistory.user_id'] = $agentId;
        }
        $row = (array)$this->AwalletHistory->find('first', array(
            'fields' => array('AwalletHistory.id','AwalletHistory.user_id','AwalletHistory.project_id','AwalletHistory.amount','AwalletHistory.remarks','AwalletHistory.date'),
            'conditions' => $conditions,
            'order' => array('AwalletHistory.id' => 'asc'),
            'recursive' => -1
        ));
        return !empty($row['AwalletHistory']) ? (array)$row['AwalletHistory'] : array();
    }

    protected function _findLegacyDealCommissionCreditRows($dealRow = array(), $agentId = 0, $expectedAmount = 0.0)
    {
        $agentId = (int)$agentId;
        $expectedAmount = round((float)$expectedAmount, 2);
        $deal = !empty($dealRow['Deal']) ? (array)$dealRow['Deal'] : (array)$dealRow;
        $dealId = !empty($deal['id']) ? (int)$deal['id'] : 0;
        if($agentId <= 0 || $expectedAmount <= 0 || $dealId <= 0)
        {
            return array();
        }
        $anchorDate = '';
        if(!empty($deal['created']))
        {
            $anchorDate = date('Y-m-d H:i:s', strtotime((string)$deal['created']));
        }
        elseif(!empty($deal['date']))
        {
            $anchorDate = date('Y-m-d 00:00:00', strtotime((string)$deal['date']));
        }
        $this->loadModel('AwalletHistory');
        $rows = (array)$this->AwalletHistory->find('all', array(
            'joins' => array(
                array(
                    'table' => 'deals_payments',
                    'alias' => 'DealsPayment',
                    'type' => 'LEFT',
                    'conditions' => array('DealsPayment.id = AwalletHistory.deals_payment_id')
                )
            ),
            'fields' => array('AwalletHistory.id','AwalletHistory.user_id','AwalletHistory.project_id','AwalletHistory.deals_payment_id','AwalletHistory.amount','AwalletHistory.type','AwalletHistory.date','AwalletHistory.remarks'),
            'conditions' => array(
                'AwalletHistory.user_id' => $agentId,
                'AwalletHistory.type' => 'CR',
                'AwalletHistory.amount' => $expectedAmount,
                array(
                    'OR' => array(
                        array('DealsPayment.deal_id' => $dealId),
                        array(
                            'OR' => array(
                                array('AwalletHistory.deals_payment_id' => null),
                                array('AwalletHistory.deals_payment_id' => 0)
                            )
                        )
                    )
                ),
                array('AwalletHistory.remarks LIKE' => 'Added for generating commission%')
            ),
            'order' => array('AwalletHistory.date' => 'asc','AwalletHistory.id' => 'asc'),
            'recursive' => -1
        ));
        if(empty($rows))
        {
            return array();
        }
        $normalized = array();
        foreach((array)$rows as $row)
        {
            $history = !empty($row['AwalletHistory']) ? (array)$row['AwalletHistory'] : array();
            if(empty($history['id']))
            {
                continue;
            }
            if($anchorDate !== '')
            {
                $historyTime = !empty($history['date']) ? strtotime((string)$history['date']) : false;
                $anchorTime = strtotime($anchorDate);
                if($historyTime !== false && $anchorTime !== false)
                {
                    $history['_distance_seconds'] = abs($historyTime - $anchorTime);
                }
                else
                {
                    $history['_distance_seconds'] = PHP_INT_MAX;
                }
            }
            else
            {
                $history['_distance_seconds'] = PHP_INT_MAX;
            }
            $normalized[] = $history;
        }
        usort($normalized, function($a, $b){
            $left = isset($a['_distance_seconds']) ? (int)$a['_distance_seconds'] : PHP_INT_MAX;
            $right = isset($b['_distance_seconds']) ? (int)$b['_distance_seconds'] : PHP_INT_MAX;
            if($left === $right)
            {
                $leftId = isset($a['id']) ? (int)$a['id'] : 0;
                $rightId = isset($b['id']) ? (int)$b['id'] : 0;
                if($leftId === $rightId)
                {
                    return 0;
                }
                return ($leftId < $rightId) ? -1 : 1;
            }
            return ($left < $right) ? -1 : 1;
        });
        return $normalized;
    }

    protected function _canTransferDealCommissionWallet($agentId, $amount)
    {
        $agentId = (int)$agentId;
        $amount = round((float)$amount, 2);
        if($agentId <= 0 || $amount <= 0)
        {
            return true;
        }
        $this->loadModel('Awallet');
        $wallet = (array)$this->Awallet->find('first', array(
            'fields' => array('Awallet.id','Awallet.credit','Awallet.balance'),
            'conditions' => array('Awallet.user_id' => $agentId),
            'recursive' => -1
        ));
        if(empty($wallet['Awallet']['id']))
        {
            return false;
        }
        $credit = isset($wallet['Awallet']['credit']) ? (float)$wallet['Awallet']['credit'] : 0.0;
        $balance = isset($wallet['Awallet']['balance']) ? (float)$wallet['Awallet']['balance'] : 0.0;
        return ($credit >= $amount && $balance >= $amount);
    }

    protected function _dealAgentOptionsWithAgency($conditions = array(), $defaultAgencyName = null)
    {
        if($defaultAgencyName === null)
        {
            $defaultAgencyName = !empty($this->siteOrganization) ? trim((string)$this->siteOrganization) : 'White Villas';
        }
        if($defaultAgencyName === '')
        {
            $defaultAgencyName = 'White Villas';
        }
        $this->loadModel('Project');
        $projectList = (array)$this->Project->find('list', array(
            'fields' => array('Project.id', 'Project.name'),
            'recursive' => -1
        ));
        $this->loadModel('User');
        $agentRows = (array)$this->User->find('all', array(
            'fields' => array('User.id', 'User.name', 'User.project_id'),
            'conditions' => $conditions,
            'order' => array('User.name' => 'asc'),
            'recursive' => -1
        ));
        $options = array();
        foreach($agentRows as $row)
        {
            if(empty($row['User']['id']))
            {
                continue;
            }
            $userId = (int)$row['User']['id'];
            $agentName = isset($row['User']['name']) ? trim((string)$row['User']['name']) : '';
            if($agentName === '')
            {
                $agentName = sprintf(__('Agent #%d'), $userId);
            }
            $projId = !empty($row['User']['project_id']) ? (int)$row['User']['project_id'] : 0;
            $agencyName = (!empty($projId) && !empty($projectList[$projId])) ? trim((string)$projectList[$projId]) : $defaultAgencyName;
            $options[$userId] = "{$agentName} | {$agencyName}";
        }
        return $options;
    }

    protected function _formatSingleAgentWithAgency($userId, $defaultAgencyName = null)
    {
        $userId = (int)$userId;
        if($userId <= 0)
        {
            return '';
        }
        $opts = $this->_dealAgentOptionsWithAgency(array('User.id' => $userId), $defaultAgencyName);
        return isset($opts[$userId]) ? $opts[$userId] : '';
    }

    protected function _transferDealCommissionAgent($dealId, $newAgentId, $reason = '', $requestedAmount = null)
    {
        $dealId = (int)$dealId;
        $newAgentId = (int)$newAgentId;
        $reason = trim((string)$reason);
        $requestedAmount = ($requestedAmount === null || $requestedAmount === '') ? null : round((float)$requestedAmount, 2);
        if(!$this->_isSuperAdminSession())
        {
            return array('ok' => false, 'message' => __('Only super admin can change commission agent.'));
        }
        if($dealId <= 0)
        {
            return array('ok' => false, 'message' => __('Please select a valid deal.'));
        }
        $this->loadModel('User');
        $this->loadModel('Property');
        $this->loadModel('Awallet');
        $this->loadModel('AwalletHistory');
        $dealRow = (array)$this->Deal->find('first', array(
            'fields' => array('Deal.id','Deal.user_id','Deal.property_id','Deal.client_id','Deal.total_amount','Deal.invoice_no'),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => 0
        ));
        if(empty($dealRow['Deal']['id']))
        {
            return array('ok' => false, 'message' => __('Deal not found.'));
        }
        $oldAgentId = !empty($dealRow['Deal']['user_id']) ? (int)$dealRow['Deal']['user_id'] : 0;
        if($newAgentId <= 0)
        {
            $newAgentId = $oldAgentId;
        }
        $allowedAgents = (array)$this->User->find('list', array(
            'fields' => array('User.id','User.name'),
            'conditions' => array(
                $this->User->assignableScopeConditions(0,'User'),
                array('User.status' => 'Active')
            ),
            'recursive' => -1
        ));
        if(empty($allowedAgents[$newAgentId]))
        {
            return array('ok' => false, 'message' => __('Please select a valid active agent.'));
        }

        $commissionMeta = $this->_dealCommissionMeta($dealRow);
        $creditRow = $this->_findDealCommissionCreditRow($dealId, $oldAgentId);
        $legacyRows = array();
        if(empty($creditRow))
        {
            $creditRow = $this->_findDealCommissionCreditRow($dealId, 0);
        }
        $currentCommissionAmount = !empty($creditRow['amount']) ? round((float)$creditRow['amount'], 2) : round((float)$commissionMeta['amount'], 2);
        if($oldAgentId > 0 && $currentCommissionAmount > 0)
        {
            $legacyRows = $this->_findLegacyDealCommissionCreditRows($dealRow, $oldAgentId, $currentCommissionAmount);
        }
        if(empty($creditRow) && !empty($legacyRows))
        {
            $creditRow = (array)$legacyRows[0];
            unset($legacyRows[0]);
            $legacyRows = array_values($legacyRows);
        }
        $newAgentExistingCreditRow = $this->_findDealCommissionCreditRow($dealId, $newAgentId);
        if(!empty($creditRow['id']) && !empty($newAgentExistingCreditRow['id']) && (int)$newAgentExistingCreditRow['id'] !== (int)$creditRow['id'])
        {
            return array('ok' => false, 'message' => __('Selected new agent already has a commission credit row for this deal. Please clean duplicate commission entries first.'));
        }
        $commissionAmount = ($requestedAmount !== null) ? $requestedAmount : $currentCommissionAmount;
        if($commissionAmount <= 0)
        {
            return array('ok' => false, 'message' => __('Please enter a valid commission amount greater than zero.'));
        }
        $sourceAgentId = !empty($creditRow['user_id']) ? (int)$creditRow['user_id'] : $oldAgentId;
        if($oldAgentId === $newAgentId && abs($commissionAmount - $currentCommissionAmount) < 0.01)
        {
            return array('ok' => false, 'message' => __('No commission agent or amount change detected.'));
        }

        if(!empty($creditRow['id']) && $sourceAgentId > 0)
        {
            $requiredReverseAmount = ($sourceAgentId === $newAgentId) ? max(0, round($currentCommissionAmount - $commissionAmount, 2)) : $currentCommissionAmount;
            if($requiredReverseAmount > 0 && !$this->_canTransferDealCommissionWallet($sourceAgentId, $requiredReverseAmount))
            {
                return array(
                    'ok' => false,
                    'message' => __('This commission cannot be updated because the current agent wallet no longer has enough available commission balance. Please settle / review paid commission first.')
                );
            }
        }

        $ds = $this->Deal->getDataSource();
        $transactionStarted = false;
        try
        {
            $ds->begin();
            $transactionStarted = true;
        }
        catch(Exception $e)
        {
            $transactionStarted = false;
        }
        try
        {
            $saveFields = array('id');
            $saveDealData = array('Deal' => array('id' => $dealId));
            if($oldAgentId !== $newAgentId)
            {
                $saveDealData['Deal']['user_id'] = $newAgentId;
                $saveFields[] = 'user_id';
            }
            if(count($saveFields) > 1 && !$this->Deal->save($saveDealData, false, $saveFields))
            {
                throw new Exception(__('Unable to update deal agent.'));
            }

            if(!empty($creditRow['id']) && $currentCommissionAmount > 0)
            {
                if($sourceAgentId === $newAgentId)
                {
                    if($commissionAmount > $currentCommissionAmount)
                    {
                        $increaseAmount = round($commissionAmount - $currentCommissionAmount, 2);
                        if($increaseAmount > 0 && !$this->CustomFunction->awalletInsert($newAgentId, $increaseAmount, 'CR', null, null, false))
                        {
                            throw new Exception(__('Unable to increase commission in agent wallet.'));
                        }
                    }
                    elseif($commissionAmount < $currentCommissionAmount)
                    {
                        $decreaseAmount = round($currentCommissionAmount - $commissionAmount, 2);
                        if($decreaseAmount > 0 && !$this->CustomFunction->awalletDelete($sourceAgentId, $decreaseAmount))
                        {
                            throw new Exception(__('Unable to reduce commission from agent wallet.'));
                        }
                    }
                }
                else
                {
                    if($sourceAgentId > 0 && !$this->CustomFunction->awalletDelete($sourceAgentId, $currentCommissionAmount))
                    {
                        throw new Exception(__('Unable to reverse commission from previous agent wallet.'));
                    }
                    if(!$this->CustomFunction->awalletInsert($newAgentId, $commissionAmount, 'CR', null, null, false))
                    {
                        throw new Exception(__('Unable to add commission into new agent wallet.'));
                    }
                }
                $creditSave = array(
                    'AwalletHistory' => array(
                        'id' => (int)$creditRow['id'],
                        'user_id' => $newAgentId,
                        'project_id' => !empty($commissionMeta['project_id']) ? (int)$commissionMeta['project_id'] : (!empty($creditRow['project_id']) ? (int)$creditRow['project_id'] : 0),
                        'amount' => $commissionAmount,
                        'remarks' => 'Deal Commission #'.$dealId.' | Added for generating commission'
                    )
                );
                if(!$this->AwalletHistory->save($creditSave, false, array('id','user_id','project_id','amount','remarks')))
                {
                    throw new Exception(__('Unable to move commission history to new agent.'));
                }
            }
            elseif($commissionAmount > 0)
            {
                if(!$this->CustomFunction->awalletInsertDealCommissionOnce($newAgentId, $commissionAmount, $dealId, 'Deal commission moved to new agent by super admin', null))
                {
                    throw new Exception(__('Unable to create commission credit for new agent.'));
                }
            }

            if($oldAgentId > 0 && !empty($legacyRows))
            {
                foreach((array)$legacyRows as $legacyRow)
                {
                    $legacyId = !empty($legacyRow['id']) ? (int)$legacyRow['id'] : 0;
                    $legacyAmount = !empty($legacyRow['amount']) ? round((float)$legacyRow['amount'], 2) : 0.0;
                    if($legacyId <= 0 || $legacyAmount <= 0)
                    {
                        continue;
                    }
                    if(!$this->CustomFunction->awalletDelete($oldAgentId, $legacyAmount))
                    {
                        throw new Exception(__('Unable to reverse duplicate legacy commission from previous agent wallet.'));
                    }
                    if(!$this->AwalletHistory->delete($legacyId))
                    {
                        throw new Exception(__('Unable to remove duplicate legacy commission history row.'));
                    }
                }
            }

            $oldAgentLabel = ($oldAgentId > 0 && !empty($allowedAgents[$oldAgentId])) ? $allowedAgents[$oldAgentId] : __('No Agent');
            $newAgentLabel = $allowedAgents[$newAgentId];
            $historyOld = __('Commission Agent').': '.$oldAgentLabel.' | '.__('Amount').': '.$commissionMeta['amount'];
            if($currentCommissionAmount > 0)
            {
                $historyOld = __('Commission Agent').': '.$oldAgentLabel.' | '.__('Amount').': '.$currentCommissionAmount;
            }
            $historyNew = __('Commission Agent').': '.$newAgentLabel.' | '.__('Amount').': '.$commissionAmount;
            if($reason !== '')
            {
                $historyNew .= ' | '.__('Reason').': '.$reason;
            }
            $this->_recordPropertyHistory(
                !empty($dealRow['Deal']['property_id']) ? (int)$dealRow['Deal']['property_id'] : 0,
                'commission_agent_change',
                __('Commission Agent / Amount Changed'),
                $historyOld,
                $historyNew,
                array(
                    'deal_id' => $dealId,
                    'client_id' => !empty($dealRow['Deal']['client_id']) ? (int)$dealRow['Deal']['client_id'] : null,
                    'project_id' => !empty($commissionMeta['project_id']) ? (int)$commissionMeta['project_id'] : 0,
                    'amount_delta' => $commissionAmount,
                    'meta' => array(
                        'invoice_no' => !empty($dealRow['Deal']['invoice_no']) ? $dealRow['Deal']['invoice_no'] : '',
                        'old_agent_id' => $oldAgentId,
                        'new_agent_id' => $newAgentId,
                        'old_amount' => $currentCommissionAmount,
                        'new_amount' => $commissionAmount,
                        'reason' => $reason
                    )
                )
            );

            if($transactionStarted)
            {
                try
                {
                    $ds->commit();
                }
                catch(Exception $e)
                {
                }
            }
            return array('ok' => true, 'message' => __('Commission agent / amount updated successfully.'));
        }
        catch(Exception $e)
        {
            if($transactionStarted)
            {
                try
                {
                    $ds->rollback();
                }
                catch(Exception $rollbackException)
                {
                }
            }
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }

    protected function _legacyDealInvoiceNo()
    {
        $lastRecord = $this->Deal->find('first', array(
            'fields' => array('Deal.id'),
            'order' => array('Deal.id' => 'DESC'),
            'recursive' => -1
        ));
        $currYear = date('Y');
        if($lastRecord)
        {
            return $this->siteShort.'-'.$currYear.'-'.str_pad($lastRecord['Deal']['id'] + 1, 4, "0", STR_PAD_LEFT);
        }
        return $this->siteShort.'-'.$currYear.'-0001';
    }

    protected function _dealInvoiceDefaults()
    {
        $prefixBase = trim((string)$this->siteShort);
        if($prefixBase === '')
        {
            $prefixBase = 'DEAL';
        }
        return array(
            'deal_invoice_prefix' => $prefixBase.'-'.date('Y').'-',
            'deal_invoice_next_no' => 1
        );
    }

    protected function _dealInvoiceSettingsRow($projectId = 0)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return array('project_id' => 0);
        }
        $this->_ensureDealInvoiceSettingsColumns();
        $this->loadModel('Admin.Ardsetting');
        $defaults = $this->_dealInvoiceDefaults();
        try
        {
            $row = $this->Ardsetting->find('first', array(
                'conditions' => array('Ardsetting.project_id' => $projectId),
                'order' => array('Ardsetting.id' => 'desc'),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            $row = array();
        }
        $merged = array_merge($defaults, array('project_id' => $projectId));
        if(!empty($row['Ardsetting']))
        {
            $merged = array_merge($merged, $row['Ardsetting']);
        }
        if(!empty($row['Ardsetting']['id']))
        {
            $merged['id'] = (int)$row['Ardsetting']['id'];
        }
        return $merged;
    }

    protected function _findNextAvailableDealInvoiceNo($projectId = 0, $consume = false)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return $this->_legacyDealInvoiceNo();
        }
        $settings = $this->_dealInvoiceSettingsRow($projectId);
        $defaults = $this->_dealInvoiceDefaults();
        $prefix = trim((string)(isset($settings['deal_invoice_prefix']) ? $settings['deal_invoice_prefix'] : ''));
        if($prefix === '')
        {
            $prefix = (string)$defaults['deal_invoice_prefix'];
        }
        if($prefix === '')
        {
            $prefix = 'DEAL-';
        }
        if(strlen($prefix) > 20)
        {
            $prefix = substr($prefix, 0, 20);
        }
        $nextNo = max(1, (int)(isset($settings['deal_invoice_next_no']) ? $settings['deal_invoice_next_no'] : 1));
        $candidate = '';
        $guard = 0;
        while(true)
        {
            $candidate = $prefix . $nextNo;
            $exists = (int)$this->Deal->find('count', array(
                'conditions' => array('Deal.invoice_no' => $candidate),
                'recursive' => -1
            ));
            if($exists <= 0)
            {
                break;
            }
            $nextNo++;
            $guard++;
            if($guard > 100000)
            {
                break;
            }
        }
        if($consume)
        {
            $this->loadModel('Admin.Ardsetting');
            $saveRow = array(
                'project_id' => $projectId,
                'deal_invoice_prefix' => $prefix,
                'deal_invoice_next_no' => $nextNo + 1
            );
            if(!empty($settings['id']))
            {
                $saveRow['id'] = (int)$settings['id'];
            }
            try
            {
                $this->Ardsetting->create();
                $this->Ardsetting->save(array('Ardsetting' => $saveRow), false);
            }
            catch(Exception $e)
            {
                // Keep deal creation working even if settings table schema is behind.
            }
        }
        return $candidate;
    }

    protected function _peekDealInvoiceNo($projectId = 0)
    {
        return $this->_findNextAvailableDealInvoiceNo((int)$projectId, false);
    }

    protected function _nextDealInvoiceNo($projectId = 0)
    {
        return $this->_findNextAvailableDealInvoiceNo((int)$projectId, true);
    }

    protected function _ensureDealInvoiceSettingsColumns()
    {
        try
        {
            $this->loadModel('Admin.Ardsetting');
        }
        catch(Exception $e)
        {
            return;
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `deal_invoice_prefix` varchar(20) DEFAULT 'DEAL-' AFTER `journal_voucher_next_no`");
        }
        catch(Exception $e)
        {
            // Ignore if already exists or table not available.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `deal_invoice_next_no` int(11) NOT NULL DEFAULT 1 AFTER `deal_invoice_prefix`");
        }
        catch(Exception $e)
        {
            // Ignore if already exists or table not available.
        }
    }

    protected function _extractDealListFilters($params=array(),$routeProjectId=null)
    {
        $filters = array(
            'project_id' => 0,
            'start_date_input' => '',
            'end_date_input' => '',
            'conditions' => array()
        );
        $params = (array)$params;
        $canUseAgencyFilters = $this->_canUseAgencyFilters();

        if($routeProjectId!==null && $routeProjectId!=='')
        {
            $filters['project_id'] = (int)$routeProjectId;
        }
        if(!$canUseAgencyFilters)
        {
            $filters['project_id'] = (int)$this->agencyId;
        }
        elseif(array_key_exists('project_id',$params))
        {
            $projectFilterRaw = trim((string)$params['project_id']);
            $filters['project_id'] = ($projectFilterRaw==='') ? 0 : max(0,(int)$projectFilterRaw);
        }

        if($filters['project_id']>0)
        {
            $filters['conditions'][] = array('Property.project_id'=>$filters['project_id']);
        }

        $filters['start_date_input'] = isset($params['start_date']) ? trim((string)$params['start_date']) : '';
        $filters['end_date_input'] = isset($params['end_date']) ? trim((string)$params['end_date']) : '';
        $startDate = '';
        $endDate = '';
        if($filters['start_date_input']!=='')
        {
            $startDate = $this->CustomFunction->dateFormatBeforeSave($filters['start_date_input']);
        }
        if($filters['end_date_input']!=='')
        {
            $endDate = $this->CustomFunction->dateFormatBeforeSave($filters['end_date_input']);
        }
        if($startDate!=='' && $endDate!=='')
        {
            $filters['conditions'][] = array('Deal.date BETWEEN ? AND ?'=>array($startDate,$endDate));
        }
        elseif($startDate!=='')
        {
            $filters['conditions'][] = array('Deal.date >='=>$startDate);
        }
        elseif($endDate!=='')
        {
            $filters['conditions'][] = array('Deal.date <='=>$endDate);
        }

        return $filters;
    }

    protected function _calculateDealHealthMap($dealIds)
    {
        if(empty($dealIds))
        {
            return array();
        }
        $dealIds = array_map('intval', array_unique((array)$dealIds));
        $this->loadModel('DealsPayment');
        $this->loadModel('PlansPayment');

        $paidRows = $this->DealsPayment->find('all', array(
            'fields' => array('DealsPayment.deal_id', 'COALESCE(SUM(DealsPayment.amount),0) AS total_paid'),
            'conditions' => array('DealsPayment.deal_id' => $dealIds),
            'group' => array('DealsPayment.deal_id'),
            'recursive' => -1
        ));
        $paidMap = array();
        foreach($paidRows as $pr)
        {
            $paidMap[(int)$pr['DealsPayment']['deal_id']] = (float)$pr[0]['total_paid'];
        }

        $planRows = $this->PlansPayment->find('all', array(
            'fields' => array('PlansPayment.deal_id', 'PlansPayment.id', 'PlansPayment.amount', 'PlansPayment.date'),
            'conditions' => array('PlansPayment.deal_id' => $dealIds),
            'order' => array('PlansPayment.deal_id' => 'asc', 'PlansPayment.date' => 'asc', 'PlansPayment.id' => 'asc'),
            'recursive' => -1
        ));
        $plansByDeal = array();
        foreach($planRows as $plr)
        {
            $dId = (int)$plr['PlansPayment']['deal_id'];
            $plansByDeal[$dId][] = array(
                'id' => (int)$plr['PlansPayment']['id'],
                'amount' => (float)$plr['PlansPayment']['amount'],
                'date' => $plr['PlansPayment']['date']
            );
        }

        $todayTs = strtotime(date('Y-m-d'));
        $healthMap = array();
        foreach($dealIds as $dealId)
        {
            $totalPaid = isset($paidMap[$dealId]) ? (float)$paidMap[$dealId] : 0.0;
            $plans = isset($plansByDeal[$dealId]) ? $plansByDeal[$dealId] : array();
            $health = 'healthy';
            $overdueDays = 0;
            $overdueAmount = 0.0;
            $remainingPaid = $totalPaid;

            foreach($plans as $plan)
            {
                $planAmt = (float)$plan['amount'];
                if($remainingPaid >= $planAmt)
                {
                    $remainingPaid -= $planAmt;
                }
                else
                {
                    $unpaidPart = $planAmt - $remainingPaid;
                    $remainingPaid = 0;
                    $dueDate = $plan['date'];
                    if(!empty($dueDate))
                    {
                        $dueTs = strtotime($dueDate);
                        if($dueTs < $todayTs)
                        {
                            $days = (int)floor(($todayTs - $dueTs) / 86400);
                            if($days > $overdueDays)
                            {
                                $overdueDays = $days;
                            }
                            $overdueAmount += $unpaidPart;
                        }
                    }
                }
            }

            if($overdueDays <= 0)
            {
                $health = 'healthy';
            }
            elseif($overdueDays <= 30)
            {
                $health = 'attention';
            }
            elseif($overdueDays <= 60)
            {
                $health = 'moderate';
            }
            else
            {
                $health = 'critical';
            }

            $healthMap[$dealId] = array(
                'status' => $health,
                'overdue_days' => $overdueDays,
                'overdue_amount' => round($overdueAmount, 2),
                'total_paid' => round($totalPaid, 2)
            );
        }
        return $healthMap;
    }

    protected function _computeOverallHealthSummary($conditions = array())
    {
        $this->loadModel('Deal');
        $deals = $this->Deal->find('all', array(
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id = Property.id'))
            ),
            'fields' => array('Deal.id'),
            'conditions' => $conditions,
            'recursive' => -1
        ));
        $dealIds = array();
        foreach($deals as $d)
        {
            if(!empty($d['Deal']['id']))
            {
                $dealIds[] = (int)$d['Deal']['id'];
            }
        }
        $healthMap = $this->_calculateDealHealthMap($dealIds);
        $summary = array('total' => count($dealIds), 'healthy' => 0, 'attention' => 0, 'moderate' => 0, 'critical' => 0);
        foreach($healthMap as $h)
        {
            $st = isset($h['status']) ? $h['status'] : 'healthy';
            if(isset($summary[$st]))
            {
                $summary[$st]++;
            }
        }
        return $summary;
    }

    protected function _getDealIdsByHealthStatus($targetStatus, $conditions = array())
    {
        $this->loadModel('Deal');
        $deals = $this->Deal->find('all', array(
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id = Property.id'))
            ),
            'fields' => array('Deal.id'),
            'conditions' => $conditions,
            'recursive' => -1
        ));
        $dealIds = array();
        foreach($deals as $d)
        {
            if(!empty($d['Deal']['id']))
            {
                $dealIds[] = (int)$d['Deal']['id'];
            }
        }
        $healthMap = $this->_calculateDealHealthMap($dealIds);
        $matchingIds = array();
        foreach($healthMap as $dId => $h)
        {
            if(isset($h['status']) && $h['status'] === $targetStatus)
            {
                $matchingIds[] = (int)$dId;
            }
        }
        return empty($matchingIds) ? array(0) : $matchingIds;
    }

    public function index($id=null)
    {
        $this->Prg->commonProcess();
        $this->Deal->virtualFields['net_total_amount'] = '(CASE WHEN COALESCE(Deal.total_amount,0) > 0 THEN COALESCE(Deal.total_amount,0) WHEN COALESCE(Deal.amount,0) > 0 THEN (COALESCE(Deal.amount,0) - COALESCE(Deal.discount,0)) ELSE 0 END)';
        $parsedParams = (array)$this->Prg->parsedParams();
        $namedParams = isset($this->request->params['named']) ? (array)$this->request->params['named'] : array();
        $listFilters = $this->_extractDealListFilters(array_merge($namedParams,$parsedParams),$id);
        $selectedProjectFilter = (int)$listFilters['project_id'];
        if(!$this->_isSuperAdminSession() && (int)$this->agencyId > 0)
        {
            $selectedProjectFilter = (int)$this->agencyId;
        }
        $condition=$this->CustomFunction->userWisePermission($selectedProjectFilter,$this->luserId,$this->userType,$this->agencyId,'Property.project_id','Deal.user_id');
        list($dealPaymentStatus,$dealControlStatus) = $this->_extractDealStatusFilters(array_merge($namedParams,$parsedParams));
        $paymentSql = $this->_setDealPaymentVirtualField();
        $dealFilterConditions = array();
        $paymentStatusCondition = $this->_buildDealPaymentStatusCondition($dealPaymentStatus,$paymentSql);
        if(!empty($paymentStatusCondition))
        {
            $dealFilterConditions[] = $paymentStatusCondition;
        }
        $dealControlCondition = $this->_buildDealControlFilterCondition($dealControlStatus);
        if(!empty($dealControlCondition))
        {
            $dealFilterConditions[] = $dealControlCondition;
        }
        $filterConditions = array($this->Deal->parseCriteria($parsedParams),$condition);
        foreach((array)$listFilters['conditions'] as $listFilterCondition)
        {
            $filterConditions[] = $listFilterCondition;
        }
        if(!empty($dealFilterConditions))
        {
            $filterConditions[] = $dealFilterConditions;
        }
        $salesScope = isset($namedParams['sales_scope']) ? strtolower(trim((string)$namedParams['sales_scope'])) : (isset($this->request->query['sales_scope']) ? strtolower(trim((string)$this->request->query['sales_scope'])) : '');
        if($salesScope === 'eligible')
        {
            $filterConditions[] = array('NOT' => array('Deal.deal_status' => array(
                'Refund', 'Refunded', 'Refund-Running', 'Refund Running', 'Running Refund',
                'Transferd', 'Transferred', 'Permanent Blocked', 'Pemanent Blocked'
            )));
        }

        $this->loadModel('Configuration');
        $cfg = $this->Configuration->findById(1);
        $dealRiskScoreEnabled = !isset($cfg['Configuration']['deal_risk_score_enabled']) || (int)$cfg['Configuration']['deal_risk_score_enabled'] === 1;
        $dealCashflowForecastEnabled = !isset($cfg['Configuration']['deal_cashflow_forecast_enabled']) || (int)$cfg['Configuration']['deal_cashflow_forecast_enabled'] === 1;
        $dealAdminOnly = !isset($cfg['Configuration']['deal_features_admin_only']) || (int)$cfg['Configuration']['deal_features_admin_only'] === 1;
        $isAdmin = ($this->_isSuperAdminSession((array)$this->adminValue) || $this->userType === 'admin' || $this->_isCeoRoleContext((array)$this->adminValue));

        $canAccessRiskScore = $dealRiskScoreEnabled && (!$dealAdminOnly || $isAdmin);
        $canAccessForecast = $dealCashflowForecastEnabled && (!$dealAdminOnly || $isAdmin);

        $selectedHealthStatus = '';
        $candidateHealthStatus = '';
        if(isset($namedParams['health_status'])) {
            $candidateHealthStatus = $namedParams['health_status'];
        } elseif(isset($parsedParams['health_status'])) {
            $candidateHealthStatus = $parsedParams['health_status'];
        } elseif(isset($this->request->query['health_status'])) {
            $candidateHealthStatus = $this->request->query['health_status'];
        } elseif(isset($this->request->data['Deal']['health_status'])) {
            $candidateHealthStatus = $this->request->data['Deal']['health_status'];
        }
        $candidateHealthStatus = strtolower(trim((string)$candidateHealthStatus));
        if(in_array($candidateHealthStatus, array('healthy', 'attention', 'moderate', 'critical'), true)) {
            $selectedHealthStatus = $candidateHealthStatus;
        }

        if($canAccessRiskScore && !empty($selectedHealthStatus))
        {
            $matchingHealthIds = $this->_getDealIdsByHealthStatus($selectedHealthStatus, $condition);
            $filterConditions[] = array('Deal.id' => $matchingHealthIds);
        }

        $pageLimit = $this->_dealPaginatorLimit();
        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['conditions'] = $filterConditions;
	$this->Paginator->settings['limit']=$pageLimit;
        $this->Paginator->settings['maxLimit']=max((int)$this->maxLimit,$pageLimit,500);
        $dealRows = $this->Paginator->paginate();
        $dealIds = array();
        foreach((array)$dealRows as $dealRow)
        {
            if(!empty($dealRow['Deal']['id']))
            {
                $dealIds[] = (int)$dealRow['Deal']['id'];
            }
        }
        $dealChargeTotalsMap = $this->_dealChargeTotalsByDealIds($dealIds);
        $this->set('Deal', $dealRows);
        $this->set('dealChargeTotalsMap', $dealChargeTotalsMap);

        $dealHealthMap = array();
        if($canAccessRiskScore && !empty($dealIds))
        {
            $dealHealthMap = $this->_calculateDealHealthMap($dealIds);
        }
        $healthSummary = array('total' => 0, 'healthy' => 0, 'attention' => 0, 'moderate' => 0, 'critical' => 0);
        if($canAccessRiskScore)
        {
            $healthSummary = $this->_computeOverallHealthSummary($condition);
        }
        $this->set('canAccessRiskScore', $canAccessRiskScore);
        $this->set('canAccessForecast', $canAccessForecast);
        $this->set('healthSummary', $healthSummary);
        $this->set('dealHealthMap', $dealHealthMap);
        $this->set('selectedHealthStatus', $selectedHealthStatus);

        $reportAgency = array();
        $reportAgencyId = 0;
        if($selectedProjectFilter>0)
        {
            $reportAgencyId = $selectedProjectFilter;
        }
        elseif(!$this->_canUseAgencyFilters() && (int)$this->agencyId>0)
        {
            $reportAgencyId = (int)$this->agencyId;
        }
        if($reportAgencyId>0)
        {
            $this->loadModel('Agency');
            $agencyFields = array('Agency.id');
            foreach(array('name','photo','address','phone','mobile','email') as $agencyHeaderField)
            {
                if($this->Agency->hasField($agencyHeaderField))
                {
                    $agencyFields[] = 'Agency.'.$agencyHeaderField;
                }
            }
            if(count($agencyFields)<=1)
            {
                $agencyFields[] = 'Agency.id';
            }
            $agencyRow = $this->Agency->find('first',array(
                'fields'=>$agencyFields,
                'conditions'=>array('Agency.id'=>$reportAgencyId),
                'recursive'=>-1
            ));
            if(!empty($agencyRow['Agency']))
            {
                $reportAgency = $agencyRow['Agency'];
                if(empty($reportAgency['address']) || empty($reportAgency['phone']) || empty($reportAgency['mobile']) || empty($reportAgency['email']))
                {
                    $this->loadModel('User');
                    $userFields = array('User.id');
                    foreach(array('address','phone','mobile','email') as $userHeaderField)
                    {
                        if($this->User->hasField($userHeaderField))
                        {
                            $userFields[] = 'User.'.$userHeaderField;
                        }
                    }
                    $agencyUser = $this->User->find('first',array(
                        'fields'=>$userFields,
                        'conditions'=>array('User.project_id'=>$reportAgencyId),
                        'order'=>array('User.id'=>'asc'),
                        'recursive'=>-1
                    ));
                    if(!empty($agencyUser['User']))
                    {
                        if(empty($reportAgency['address']) && !empty($agencyUser['User']['address']))$reportAgency['address'] = $agencyUser['User']['address'];
                        if(empty($reportAgency['phone']) && !empty($agencyUser['User']['phone']))$reportAgency['phone'] = $agencyUser['User']['phone'];
                        if(empty($reportAgency['mobile']) && !empty($agencyUser['User']['mobile']))$reportAgency['mobile'] = $agencyUser['User']['mobile'];
                        if(empty($reportAgency['email']) && !empty($agencyUser['User']['email']))$reportAgency['email'] = $agencyUser['User']['email'];
                    }
                }
            }
        }
        $this->set('reportAgency',$reportAgency);

        $this->set('dealControlStatuses',$this->_dealControlStatuses);
        $this->set('dealPaymentBlockedStatuses',$this->_dealPaymentBlockedStatuses);
        $this->set('dealPaymentStatuses',$this->_dealPaymentStatuses);
        $this->set('selectedDealPaymentStatus',$dealPaymentStatus);
        $this->set('selectedDealControlStatus',$dealControlStatus);
        $this->set('selectedProjectFilter',$selectedProjectFilter);
        $this->set('selectedStartDateInput',$listFilters['start_date_input']);
        $this->set('selectedEndDateInput',$listFilters['end_date_input']);
        $this->set('canUseAgencyFilters',$this->_canUseAgencyFilters());
        $this->set('canShowDealAction', (strtoupper(trim((string)$this->userType)) !== 'AGT'));
        if($this->_canUseAgencyFilters())
        {
            $this->loadModel('Project');
            $this->set('projectName',$this->Project->find('list',array('fields'=>array('Project.id','Project.name'),'order'=>array('Project.name'=>'asc'))));
        }
        $this->set('currentProjectId',$id);
        $this->set('canChangeDealControlStatus',($this->userType==='ADR'));
	if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
    }

    public function cashflow()
    {
        $this->loadModel('Configuration');
        $cfg = $this->Configuration->findById(1);
        $dealCashflowForecastEnabled = !isset($cfg['Configuration']['deal_cashflow_forecast_enabled']) || (int)$cfg['Configuration']['deal_cashflow_forecast_enabled'] === 1;
        $dealAdminOnly = !isset($cfg['Configuration']['deal_features_admin_only']) || (int)$cfg['Configuration']['deal_features_admin_only'] === 1;
        $isAdmin = ($this->_isSuperAdminSession((array)$this->adminValue) || $this->userType === 'admin' || $this->_isCeoRoleContext((array)$this->adminValue));
        $canAccessForecast = $dealCashflowForecastEnabled && (!$dealAdminOnly || $isAdmin);

        if(!$canAccessForecast)
        {
            $this->Session->setFlash(__('Cash Flow Forecasting is currently disabled or restricted to Administrators.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('controller' => 'Deals', 'action' => 'index'));
        }

        $selectedProjectId = isset($this->request->query['project_id']) ? (int)$this->request->query['project_id'] : 0;
        if(!$this->_isSuperAdminSession() && (int)$this->agencyId > 0)
        {
            $selectedProjectId = (int)$this->agencyId;
        }
        $condition = $this->CustomFunction->userWisePermission($selectedProjectId, $this->luserId, $this->userType, $this->agencyId, 'Property.project_id', 'Deal.user_id');

        $excludeStatuses = array('Permanent Blocked', 'Pemanent Blocked', 'Refund', 'Refunded', 'Transferd', 'Transferred');
        $dealConditions = array(
            $condition,
            array('NOT' => array('Deal.deal_status' => $excludeStatuses))
        );

        $this->loadModel('Deal');
        $this->loadModel('DealsPayment');
        $this->loadModel('PlansPayment');
        $this->loadModel('Project');

        $deals = $this->Deal->find('all', array(
            'fields' => array(
                'Deal.id', 'Deal.invoice_no', 'Deal.total_amount', 'Deal.date',
                'Client.id', 'Client.name', 'Client.mobile', 'Client.phone',
                'Property.id', 'Property.name', 'Property.project_id',
                'Project.id', 'Project.name'
            ),
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Deal.client_id = Client.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id = Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id = Project.id'))
            ),
            'conditions' => $dealConditions,
            'order' => array('Deal.id' => 'desc'),
            'recursive' => -1
        ));

        $dealIds = array();
        $dealMetaMap = array();
        foreach($deals as $d)
        {
            $dId = (int)$d['Deal']['id'];
            $dealIds[] = $dId;
            $dealMetaMap[$dId] = array(
                'id' => $dId,
                'invoice_no' => $d['Deal']['invoice_no'],
                'client_name' => !empty($d['Client']['name']) ? $d['Client']['name'] : 'N/A',
                'client_contact' => !empty($d['Client']['mobile']) ? $d['Client']['mobile'] : (!empty($d['Client']['phone']) ? $d['Client']['phone'] : ''),
                'property_name' => !empty($d['Property']['name']) ? $d['Property']['name'] : 'N/A',
                'project_id' => !empty($d['Project']['id']) ? (int)$d['Project']['id'] : 0,
                'project_name' => !empty($d['Project']['name']) ? $d['Project']['name'] : 'Independent / General',
                'total_amount' => (float)$d['Deal']['total_amount']
            );
        }

        $paidMap = array();
        if(!empty($dealIds))
        {
            $paidRows = $this->DealsPayment->find('all', array(
                'fields' => array('DealsPayment.deal_id', 'COALESCE(SUM(DealsPayment.amount),0) AS total_paid'),
                'conditions' => array('DealsPayment.deal_id' => $dealIds),
                'group' => array('DealsPayment.deal_id'),
                'recursive' => -1
            ));
            foreach($paidRows as $pr)
            {
                $paidMap[(int)$pr['DealsPayment']['deal_id']] = (float)$pr[0]['total_paid'];
            }
        }

        $plansByDeal = array();
        if(!empty($dealIds))
        {
            $planRows = $this->PlansPayment->find('all', array(
                'fields' => array('PlansPayment.id', 'PlansPayment.deal_id', 'PlansPayment.name', 'PlansPayment.amount', 'PlansPayment.date'),
                'conditions' => array('PlansPayment.deal_id' => $dealIds),
                'order' => array('PlansPayment.deal_id' => 'asc', 'PlansPayment.date' => 'asc', 'PlansPayment.id' => 'asc'),
                'recursive' => -1
            ));
            foreach($planRows as $plr)
            {
                $dId = (int)$plr['PlansPayment']['deal_id'];
                $plansByDeal[$dId][] = $plr['PlansPayment'];
            }
        }

        $todayTs = strtotime(date('Y-m-d'));
        $day30Ts = strtotime('+30 days', $todayTs);
        $day60Ts = strtotime('+60 days', $todayTs);
        $day90Ts = strtotime('+90 days', $todayTs);

        $kpis = array(
            'next_30_days' => 0.0,
            'days_31_60' => 0.0,
            'days_61_90' => 0.0,
            'beyond_90' => 0.0,
            'overdue_total' => 0.0,
            'grand_total' => 0.0,
            'total_active_deals' => count($dealIds)
        );

        $monthlyForecast = array();
        $projectBreakdown = array();
        $upcomingInstallments = array();

        foreach($dealIds as $dealId)
        {
            $meta = $dealMetaMap[$dealId];
            $projKey = $meta['project_id'];
            $projName = $meta['project_name'];
            if(!isset($projectBreakdown[$projKey]))
            {
                $projectBreakdown[$projKey] = array(
                    'name' => $projName,
                    'active_deals' => 0,
                    'overdue' => 0.0,
                    'next_30' => 0.0,
                    'days_31_60' => 0.0,
                    'days_61_90' => 0.0,
                    'beyond_90' => 0.0,
                    'total_receivable' => 0.0
                );
            }
            $projectBreakdown[$projKey]['active_deals']++;

            $totalPaid = isset($paidMap[$dealId]) ? (float)$paidMap[$dealId] : 0.0;
            $plans = isset($plansByDeal[$dealId]) ? $plansByDeal[$dealId] : array();
            $remainingPaid = $totalPaid;

            foreach($plans as $plan)
            {
                $planAmt = (float)$plan['amount'];
                if($remainingPaid >= $planAmt)
                {
                    $remainingPaid -= $planAmt;
                    continue;
                }
                $unpaidAmt = $planAmt - $remainingPaid;
                $remainingPaid = 0.0;

                $dueDate = trim((string)$plan['date']);
                $dueTs = !empty($dueDate) ? strtotime($dueDate) : 0;
                $monthKey = !empty($dueDate) ? substr($dueDate, 0, 7) : date('Y-m');

                if($dueTs > 0 && $dueTs < $todayTs)
                {
                    $kpis['overdue_total'] += $unpaidAmt;
                    $projectBreakdown[$projKey]['overdue'] += $unpaidAmt;
                }
                elseif($dueTs >= $todayTs && $dueTs <= $day30Ts)
                {
                    $kpis['next_30_days'] += $unpaidAmt;
                    $projectBreakdown[$projKey]['next_30'] += $unpaidAmt;
                    if(!isset($monthlyForecast[$monthKey])) $monthlyForecast[$monthKey] = 0.0;
                    $monthlyForecast[$monthKey] += $unpaidAmt;

                    $upcomingInstallments[] = array(
                        'deal_id' => $dealId,
                        'invoice_no' => $meta['invoice_no'],
                        'client_name' => $meta['client_name'],
                        'property_name' => $meta['property_name'],
                        'project_name' => $meta['project_name'],
                        'installment_name' => $plan['name'],
                        'due_date' => $dueDate,
                        'due_ts' => $dueTs,
                        'amount' => $unpaidAmt
                    );
                }
                elseif($dueTs > $day30Ts && $dueTs <= $day60Ts)
                {
                    $kpis['days_31_60'] += $unpaidAmt;
                    $projectBreakdown[$projKey]['days_31_60'] += $unpaidAmt;
                    if(!isset($monthlyForecast[$monthKey])) $monthlyForecast[$monthKey] = 0.0;
                    $monthlyForecast[$monthKey] += $unpaidAmt;

                    $upcomingInstallments[] = array(
                        'deal_id' => $dealId,
                        'invoice_no' => $meta['invoice_no'],
                        'client_name' => $meta['client_name'],
                        'property_name' => $meta['property_name'],
                        'project_name' => $meta['project_name'],
                        'installment_name' => $plan['name'],
                        'due_date' => $dueDate,
                        'due_ts' => $dueTs,
                        'amount' => $unpaidAmt
                    );
                }
                elseif($dueTs > $day60Ts && $dueTs <= $day90Ts)
                {
                    $kpis['days_61_90'] += $unpaidAmt;
                    $projectBreakdown[$projKey]['days_61_90'] += $unpaidAmt;
                    if(!isset($monthlyForecast[$monthKey])) $monthlyForecast[$monthKey] = 0.0;
                    $monthlyForecast[$monthKey] += $unpaidAmt;
                }
                else
                {
                    $kpis['beyond_90'] += $unpaidAmt;
                    $projectBreakdown[$projKey]['beyond_90'] += $unpaidAmt;
                    if(!empty($monthKey) && $dueTs >= $todayTs)
                    {
                        if(!isset($monthlyForecast[$monthKey])) $monthlyForecast[$monthKey] = 0.0;
                        $monthlyForecast[$monthKey] += $unpaidAmt;
                    }
                }
                $kpis['grand_total'] += $unpaidAmt;
                $projectBreakdown[$projKey]['total_receivable'] += $unpaidAmt;
            }
        }

        ksort($monthlyForecast);
        usort($upcomingInstallments, function($a, $b) {
            return $a['due_ts'] - $b['due_ts'];
        });

        $projectsList = $this->Project->find('list', array('fields' => array('Project.id', 'Project.name'), 'order' => array('Project.name' => 'asc')));

        $this->set(compact('kpis', 'monthlyForecast', 'projectBreakdown', 'upcomingInstallments', 'projectsList', 'selectedProjectId'));
    }
    protected function _dealPaginatorLimit()
    {
        $limit = 0;
        if(isset($this->request->params['named']['limit']))
        {
            $limit = (int)$this->request->params['named']['limit'];
        }
        elseif(isset($this->request->params['limit']))
        {
            $limit = (int)$this->request->params['limit'];
        }
        elseif(isset($this->passedArgs['limit']))
        {
            $limit = (int)$this->passedArgs['limit'];
        }
        elseif(isset($this->request->query['limit']))
        {
            $limit = (int)$this->request->query['limit'];
        }
        elseif(isset($this->request->data['limit']))
        {
            $limit = (int)$this->request->data['limit'];
        }
        elseif(isset($this->request->data['Deal']['limit']))
        {
            $limit = (int)$this->request->data['Deal']['limit'];
        }
        if($limit<=0 && !empty($this->request->params['pass']))
        {
            foreach((array)$this->request->params['pass'] as $passedValue)
            {
                if(preg_match('/^limit[:=](\d+)$/',(string)$passedValue,$matches))
                {
                    $limit = (int)$matches[1];
                    break;
                }
                if(is_numeric($passedValue))
                {
                    $limit = (int)$passedValue;
                    break;
                }
            }
        }
        if($limit<=0 && !empty($this->passedArgs))
        {
            foreach((array)$this->passedArgs as $passedKey=>$passedValue)
            {
                if((string)$passedKey === 'limit')
                {
                    $limit = (int)$passedValue;
                    break;
                }
                if(preg_match('/^limit[:=](\d+)$/',(string)$passedValue,$matches))
                {
                    $limit = (int)$matches[1];
                    break;
                }
            }
        }
        if($limit<=0)
        {
            $limit = (int)$this->pageLimit;
        }
        $maxLimit = max((int)$this->maxLimit,500);
        if($maxLimit<=0)
        {
            $maxLimit = 500;
        }
        $limit = max(1,min($limit,$maxLimit));
        $this->request->params['named']['limit'] = $limit;
        $this->passedArgs['limit'] = $limit;
        return $limit;
    }
    public function exportcsv($id=null)
    {
        $this->autoRender = false;
        $this->layout = null;
        $namedParams = isset($this->request->params['named']) ? $this->request->params['named'] : array();
        $listFilters = $this->_extractDealListFilters($namedParams,$id);
        $selectedProjectFilter = (int)$listFilters['project_id'];
        if(!$this->_isSuperAdminSession() && (int)$this->agencyId > 0)
        {
            $selectedProjectFilter = (int)$this->agencyId;
        }
        $condition=$this->CustomFunction->userWisePermission($selectedProjectFilter,$this->luserId,$this->userType,$this->agencyId,'Property.project_id','Deal.user_id');
        list($dealPaymentStatus,$dealControlStatus) = $this->_extractDealStatusFilters($namedParams);
        $paymentSql = $this->_setDealPaymentVirtualField();
        $dealFilterConditions = array();
        $paymentStatusCondition = $this->_buildDealPaymentStatusCondition($dealPaymentStatus,$paymentSql);
        if(!empty($paymentStatusCondition))
        {
            $dealFilterConditions[] = $paymentStatusCondition;
        }
        $dealControlCondition = $this->_buildDealControlFilterCondition($dealControlStatus);
        if(!empty($dealControlCondition))
        {
            $dealFilterConditions[] = $dealControlCondition;
        }
        $csvConditions = array($this->Deal->parseCriteria($namedParams),$condition);
        foreach((array)$listFilters['conditions'] as $listFilterCondition)
        {
            $csvConditions[] = $listFilterCondition;
        }
        if(!empty($dealFilterConditions))
        {
            $csvConditions[] = $dealFilterConditions;
        }
        $fields = array('Deal.id','Deal.invoice_no','Deal.date','Deal.total_amount','Deal.payment','Client.name','Client.phone','Property.name','Property.area','Unit.name');
        if($this->Deal->hasField('amount'))
        {
            $fields[]='Deal.amount';
        }
        if($this->Deal->hasField('discount'))
        {
            $fields[]='Deal.discount';
        }
        if($this->Deal->hasField('deal_status'))
        {
            $fields[]='Deal.deal_status';
        }
        $rows = $this->Deal->find('all',array(
            'fields'=>$fields,
            'conditions'=>$csvConditions,
            'order'=>array('Deal.id'=>'desc')
        ));
        $csvDealIds = array();
        foreach((array)$rows as $csvRow)
        {
            if(!empty($csvRow['Deal']['id']))
            {
                $csvDealIds[] = (int)$csvRow['Deal']['id'];
            }
        }
        $csvDealChargeTotalsMap = $this->_dealChargeTotalsByDealIds($csvDealIds);
        $filename = "deals_export_" . date("Y-m-d_H-i-s") . ".csv";
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"".$filename."\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        $output = fopen("php://output", "w");
        fputcsv($output, array('#','Customer Name / Father Name','Mobile No','Property / File','Membership No','Booking Date','Area','Total Amount','Balance Due','Status','Deal Control'));
        $serialNo = 1;
        foreach($rows as $row)
        {
            $paidAmount = isset($row['Deal']['payment']) ? (float)$row['Deal']['payment'] : 0;
            $dealId = isset($row['Deal']['id']) ? (int)$row['Deal']['id'] : 0;
            $chargeTotal = isset($csvDealChargeTotalsMap[$dealId]) ? (float)$csvDealChargeTotalsMap[$dealId] : 0.0;
            $totalAmount = $this->_dealListTotalAmountValue($row, $chargeTotal);
            $balanceDue = $totalAmount - $paidAmount;
            $status = ($balanceDue<=0) ? 'Paid' : 'Partial';
            $dealControl = (!empty($row['Deal']['deal_status']) ? $row['Deal']['deal_status'] : 'Active');
            $area = trim((isset($row['Property']['area']) ? $row['Property']['area'] : '').' '.(isset($row['Unit']['name']) ? $row['Unit']['name'] : ''));
            fputcsv($output, array(
                $serialNo++,
                isset($row['Client']['name']) ? $row['Client']['name'] : '',
                isset($row['Client']['phone']) ? $row['Client']['phone'] : '',
                isset($row['Property']['name']) ? $row['Property']['name'] : '',
                isset($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '',
                $this->Time->format($this->sysDay.$this->dateSep.$this->sysMonth.$this->dateSep.$this->sysYear,isset($row['Deal']['date']) ? $row['Deal']['date'] : ''),
                $area,
                $this->currency.CakeNumber::format($totalAmount),
                $this->currency.CakeNumber::format($balanceDue),
                $status,
                $dealControl
            ));
        }
        fclose($output);
        exit();
    }
    public function changestatus($dealId=null,$status=null)
    {
        if($this->userType!=='ADR')
        {
            $this->Session->setFlash(__('Only admin/super admin can change deal control status.'),'flash',array('alert'=>'danger'));
            return $this->redirect($this->referer(array('action'=>'index')));
        }
        if(!$dealId || !$status || !in_array($status,$this->_dealControlStatuses))
        {
            $this->Session->setFlash(__('Invalid status request.'),'flash',array('alert'=>'danger'));
            return $this->redirect($this->referer(array('action'=>'index')));
        }
        if(!$this->Deal->hasField('deal_status'))
        {
            $this->Session->setFlash(__('Deal status feature requires DB update. Please add deal_status columns in deals table.'),'flash',array('alert'=>'danger'));
            return $this->redirect($this->referer(array('action'=>'index')));
        }
        $deal = $this->Deal->findById($dealId);
        if(empty($deal))
        {
            $this->Session->setFlash(__('Invalid deal.'),'flash',array('alert'=>'danger'));
            return $this->redirect($this->referer(array('action'=>'index')));
        }
        $reason = '';
        if(isset($this->request->query['reason']))
        {
            $reason = trim($this->request->query['reason']);
        }
        if(in_array($status,$this->_dealPaymentBlockedStatuses) && strlen($reason)<=0)
        {
            $this->Session->setFlash(__('Reason is required for selected status.'),'flash',array('alert'=>'danger'));
            return $this->redirect($this->referer(array('action'=>'index')));
        }
        $saveData = array('id'=>$dealId,'deal_status'=>$status);
        if($this->Deal->hasField('deal_status_reason'))
        {
            $saveData['deal_status_reason']=$reason;
        }
        if($this->Deal->hasField('deal_status_changed'))
        {
            $saveData['deal_status_changed']=date('Y-m-d H:i:s');
        }
        if($this->Deal->save(array('Deal'=>$saveData),false))
        {
            $oldStatus = !empty($deal['Deal']['deal_status']) ? $deal['Deal']['deal_status'] : 'Active';
            $this->_recordPropertyHistory($deal['Deal']['property_id'],'deal_status',__('Deal Status Changed'),$oldStatus,$status,array(
                'deal_id'=>$dealId,
                'client_id'=>isset($deal['Deal']['client_id']) ? (int)$deal['Deal']['client_id'] : null,
                'meta'=>array('reason'=>$reason)
            ));
            $this->_syncDealCancellationForfeitureVoucher($deal, $status);
            $this->Session->setFlash(__('Deal control status updated successfully.'),'flash',array('alert'=>'success'));
        }
        else
        {
            $this->Session->setFlash(__('Unable to update deal control status.'),'flash',array('alert'=>'danger'));
        }
        return $this->redirect($this->referer(array('action'=>'index')));
    }
    public function select2()
    {
        $this->Client->recursive = 0;
        $this->set('clients', $this->paginate());
    }
    public function clientsearch()
    {
        $this->autoRender = false;
        $this->request->onlyAllow('ajax');
        // get the search term from URL
        $this->loadModel('Client');
        $term = $this->request->query['q'];
        $users = $this->Client->find('all',array('conditions' => array('Client.name LIKE' => '%'.$term.'%')));
        // Format the result for select2
        $result = array();
        foreach($users as $key => $user)
        {
            $result[$key]['id'] = (int) $user['Client']['id'];
            $result[$key]['text'] = $user['Client']['name'];
        }
        $users = $result;        
        echo json_encode($users);
    }
    public function propertysearch()
    {
        $this->autoRender = false;
        $this->request->onlyAllow('ajax');
        // get the search term from URL
        $this->loadModel('Property');
        $term = $this->request->query['q'];
        $cond=array();$cond1=array();
        $condProject=array();
        if($this->request->query['q1'])
        {
            $cond=array('Property.type_id' => $this->request->query['q1']);
        }
        if($this->request->query['q2'])
        {
            $cond1=array('Property.contract_id' => $this->request->query['q2']);
        }
        if($this->_canUseAgencyFilters() && !empty($this->request->query['q3']))
        {
            $selectedProjectId = (int)$this->request->query['q3'];
            if($selectedProjectId > 0)
            {
                $condProject = array('Property.project_id' => $selectedProjectId);
            }
        }
        $condition=$this->CustomFunction->agencyWisePermission(NULL,$this->userType,$this->agencyId,'Property.project_id');
        $users = $this->Property->find('all',array('fields'=>array('Property.id','Property.name'),
						   'conditions' => array('Property.name LIKE' => '%'.$term.'%',$cond,'Property.status'=>'Available',$cond1,$condProject,$condition)));
        // Format the result for select2
        $result = array();
        foreach($users as $key => $user)
        {
            $result[$key]['id'] = (int) $user['Property']['id'];
            $result[$key]['text'] = $user['Property']['name'];
        }
        $users = $result;        
        echo json_encode($users);
    }
    public function propertydetails()
    {
	$this->layout=null;
        //$this->request->onlyAllow('ajax');
        $id=$this->request->query['id'];
        $this->loadModel('Property');
        $this->loadModel('User');
        $post=$this->Property->find('first',array('fields'=>array('Property.area','Property.price','Unit.name','Property.contract_id','Property.project_id'),
                                                        'conditions'=>array('Property.id'=>$id)));
        $agentConditions = array(
            $this->User->assignableScopeConditions((int)$post['Property']['project_id'],'User'),
            array('User.status'=>'Active')
        );
        $agentName = $this->_dealAgentOptionsWithAgency($agentConditions);
        $invoiceNo = $this->_peekDealInvoiceNo(isset($post['Property']['project_id']) ? (int)$post['Property']['project_id'] : 0);
        $this->set('invoiceNo', $invoiceNo);
        $this->set('post',$post);
	$this->set(compact('agentName'));
    }
    public function dealinvoicepreview()
    {
        $this->autoRender = false;
        $this->request->onlyAllow('ajax');
        $projectId = 0;
        if(isset($this->request->query['project_id']))
        {
            $projectId = (int)$this->request->query['project_id'];
        }
        if(!$this->_canUseAgencyFilters())
        {
            $projectId = (int)$this->agencyId;
        }
        $invoiceNo = $this->_peekDealInvoiceNo($projectId);
        echo json_encode(array('invoice_no' => $invoiceNo));
    }
    public function plandetails()
    {
        $this->layout=null;
        $this->autoRender = false;
        $this->layout=null;
        $this->request->onlyAllow('ajax');
        $id=$this->request->query['id'];
        $this->loadModel('Admin.Plan');
        $plan=$this->Plan->find('list',array('fields'=>array('Plan.id','Plan.name'),'conditions'=>array('Plan.properties_id'=>$id)));
        $this->set(compact('plan'));
        $this->render('showplan');
    }

    public function plantemplatepreview()
    {
        $this->autoRender = false;
        $this->request->onlyAllow('ajax');
        $planId = !empty($this->request->query['id']) ? (int)$this->request->query['id'] : 0;
        $plan = $this->_dealPlanRecord($planId);
        if(empty($plan))
        {
            echo json_encode(array('enabled'=>false));
            return;
        }
        $this->loadModel('Admin.PlanScheduleRow');
        $rows = $this->PlanScheduleRow->find('all',array(
            'conditions'=>array('PlanScheduleRow.plan_id'=>$planId),
            'order'=>array('PlanScheduleRow.sort_order'=>'ASC','PlanScheduleRow.id'=>'ASC'),
            'recursive'=>-1
        ));
        $planTotal = round((float)$plan['total_amount'],2);
        $bookingAmount = 0.0;
        $paymentCount = 0;
        $scheduleTotal = 0.0;
        foreach($rows as $row)
        {
            $item = $row['PlanScheduleRow'];
            $count = max(1,(int)$item['repeat_count']);
            $unitAmount = $item['amount_mode']==='percentage' ? round(($planTotal*(float)$item['amount_value'])/100,2) : round((float)$item['amount_value'],2);
            if($item['payment_type']==='booking') $bookingAmount += ($unitAmount*$count);
            $paymentCount += $count;
            $scheduleTotal += ($unitAmount*$count);
        }
        $basePaymentCount = $paymentCount;
        $grossDealAmount = isset($this->request->query['deal_amount']) ? round((float)str_replace(',','',$this->request->query['deal_amount']),2) : 0;
        $discountAmount = isset($this->request->query['discount']) ? max(0,round((float)str_replace(',','',$this->request->query['discount']),2)) : 0;
        $dealTotal = max(0,round($grossDealAmount-$discountAmount,2));
        $adjustmentAmount = 0;
        $ready = abs(round($scheduleTotal,2)-$planTotal)<=0.01;
        if(!empty($rows) && $grossDealAmount>0 && $dealTotal>0)
        {
            try
            {
                $adjustedTemplate = $this->_dealPlanTemplate($planId,$dealTotal,$grossDealAmount);
                $paymentCount = 0;
                foreach($adjustedTemplate['rows'] as $adjustedRow)
                {
                    $paymentCount += max(1,(int)$adjustedRow['repeat_count']);
                }
                $bookingAmount = $adjustedTemplate['booking_amount'];
                $scheduleTotal = $adjustedTemplate['schedule_total'];
                $adjustmentAmount = $adjustedTemplate['adjustment_amount'];
                $ready = abs($scheduleTotal-$dealTotal)<=0.01;
            }
            catch(Exception $e)
            {
                echo json_encode(array('enabled'=>true,'ready'=>false,'message'=>$e->getMessage(),'total_amount'=>$planTotal));
                return;
            }
        }
        echo json_encode(array(
            'enabled'=>!empty($rows),
            'ready'=>$ready,
            'total_amount'=>$planTotal,
            'booking_amount'=>round($bookingAmount,2),
            'payment_count'=>$paymentCount,
            'base_payment_count'=>$basePaymentCount,
            'schedule_total'=>round($scheduleTotal,2),
            'deal_total'=>$dealTotal,
            'adjustment_amount'=>round($adjustmentAmount,2),
            'adjustment_type'=>$adjustmentAmount>0.01 ? 'extended' : ($adjustmentAmount<-0.01 ? 'reduced' : 'exact')
        ));
    }

    protected function _dealPlanRecord($planId)
    {
        $planId = (int)$planId;
        if($planId<=0) return array();
        $rows = $this->Deal->query(
            "SELECT `id`,`name`,`installment`,`total_amount`,`status` FROM `plans` WHERE `id`=".$planId." LIMIT 1"
        );
        if(empty($rows)) return array();
        $row = reset($rows);
        if(isset($row['plans'])) return (array)$row['plans'];
        if(isset($row['Plan'])) return (array)$row['Plan'];
        if(isset($row[0])) return (array)$row[0];
        return array();
    }

    protected function _dealPlanTemplate($planId, $dealTotal, $grossDealAmount = null)
    {
        $planId = (int)$planId;
        $dealTotal = round((float)$dealTotal,2);
        if($planId <= 0)
        {
            return array('enabled'=>false,'rows'=>array(),'booking_amount'=>0);
        }
        $this->loadModel('Admin.PlanScheduleRow');
        $plan = $this->_dealPlanRecord($planId);
        $rows = $this->PlanScheduleRow->find('all',array(
            'conditions'=>array('PlanScheduleRow.plan_id'=>$planId),
            'order'=>array('PlanScheduleRow.sort_order'=>'ASC','PlanScheduleRow.id'=>'ASC'),
            'recursive'=>-1
        ));
        if(empty($rows))
        {
            // Preserve the existing manual installment flow for legacy plans
            // that predate configurable schedule templates.
            return array('enabled'=>false,'rows'=>array(),'booking_amount'=>0);
        }
        $planTotal = !empty($plan['total_amount']) ? round((float)$plan['total_amount'],2) : 0;
        $grossDealAmount = $grossDealAmount === null ? $dealTotal : round((float)$grossDealAmount,2);
        if($planTotal <= 0)
        {
            throw new Exception(__('The selected payment plan must have a total amount greater than zero.'));
        }
        if($dealTotal <= 0)
        {
            throw new Exception(__('The discounted deal total must be greater than zero.'));
        }
        $normalizedRows = array();
        $scheduleTotal = 0.0;
        foreach($rows as $row)
        {
            $item = $row['PlanScheduleRow'];
            $count = max(1,(int)$item['repeat_count']);
            $value = (float)$item['amount_value'];
            // Percentages are calculated from the original plan value. Any
            // deal discount is then deducted from the final payments only.
            $unitAmount = $item['amount_mode']==='percentage' ? round(($planTotal*$value)/100,2) : round($value,2);
            $scheduleTotal += ($unitAmount*$count);
            $item['unit_amount'] = $unitAmount;
            $item['unit_amounts'] = array_fill(0,$count,$unitAmount);
            $item['repeat_count'] = $count;
            $normalizedRows[] = $item;
        }
        $scheduleTotal = round($scheduleTotal,2);
        if(abs($scheduleTotal-$planTotal)>0.01)
        {
            throw new Exception(sprintf(__('Selected payment schedule totals %s and does not equal the payment-plan total %s.'),CakeNumber::format($scheduleTotal),CakeNumber::format($planTotal)));
        }

        $reductionRemaining = max(0,round($planTotal-$dealTotal,2));
        for($rowIndex=count($normalizedRows)-1;$rowIndex>=0 && $reductionRemaining>0.001;$rowIndex--)
        {
            for($paymentIndex=count($normalizedRows[$rowIndex]['unit_amounts'])-1;$paymentIndex>=0 && $reductionRemaining>0.001;$paymentIndex--)
            {
                $available = round((float)$normalizedRows[$rowIndex]['unit_amounts'][$paymentIndex],2);
                $deduction = min($available,$reductionRemaining);
                $normalizedRows[$rowIndex]['unit_amounts'][$paymentIndex] = round($available-$deduction,2);
                $reductionRemaining = round($reductionRemaining-$deduction,2);
            }
        }
        if($reductionRemaining>0.01)
        {
            throw new Exception(__('The deal total could not be fitted to the selected payment schedule.'));
        }

        $extensionRemaining = max(0,round($dealTotal-$planTotal,2));
        if($extensionRemaining>0.001)
        {
            $extensionRowIndex = null;
            foreach(array('monthly','quarterly','biannual') as $preferredType)
            {
                foreach($normalizedRows as $candidateIndex=>$candidateRow)
                {
                    if(strtolower((string)$candidateRow['payment_type'])===$preferredType && !empty($candidateRow['unit_amounts']))
                    {
                        $extensionRowIndex = $candidateIndex;
                        break 2;
                    }
                }
            }
            if($extensionRowIndex===null)
            {
                foreach($normalizedRows as $candidateIndex=>$candidateRow)
                {
                    if(strtolower((string)$candidateRow['payment_type'])==='custom' && !empty($candidateRow['unit_amounts']))
                    {
                        $extensionRowIndex = $candidateIndex;
                        break;
                    }
                }
            }
            if($extensionRowIndex!==null)
            {
                $referenceAmounts = $normalizedRows[$extensionRowIndex]['unit_amounts'];
                $referenceAmount = round((float)reset($referenceAmounts),2);
                if($referenceAmount<=0)
                {
                    throw new Exception(__('The recurring payment amount must be greater than zero to extend this plan.'));
                }
                while($extensionRemaining>0.001)
                {
                    $nextAmount = min($referenceAmount,$extensionRemaining);
                    $normalizedRows[$extensionRowIndex]['unit_amounts'][] = round($nextAmount,2);
                    $extensionRemaining = round($extensionRemaining-$nextAmount,2);
                }
                $normalizedRows[$extensionRowIndex]['repeat_count'] = count($normalizedRows[$extensionRowIndex]['unit_amounts']);
            }
            else
            {
                $insertAt = count($normalizedRows);
                foreach($normalizedRows as $candidateIndex=>$candidateRow)
                {
                    if(in_array(strtolower((string)$candidateRow['payment_type']),array('possession','final'),true))
                    {
                        $insertAt = $candidateIndex;
                        break;
                    }
                }
                $extensionRow = array(
                    'payment_type'=>'custom',
                    'title'=>__('Additional Balance Payment'),
                    'amount_mode'=>'fixed',
                    'amount_value'=>$extensionRemaining,
                    'repeat_count'=>1,
                    'interval_months'=>1,
                    'sort_order'=>$insertAt+1,
                    'unit_amount'=>$extensionRemaining,
                    'unit_amounts'=>array($extensionRemaining)
                );
                array_splice($normalizedRows,$insertAt,0,array($extensionRow));
                $extensionRemaining = 0;
            }
        }

        $adjustedTotal = 0.0;
        $bookingAmount = 0.0;
        $adjustedRows = array();
        foreach($normalizedRows as $rowIndex=>$item)
        {
            $item['unit_amounts'] = array_values(array_filter($item['unit_amounts'],function($amount){ return round((float)$amount,2)>0; }));
            if(empty($item['unit_amounts'])) continue;
            $item['repeat_count'] = count($item['unit_amounts']);
            $rowTotal = round(array_sum($item['unit_amounts']),2);
            $adjustedTotal += $rowTotal;
            if($item['payment_type']==='booking') $bookingAmount += $rowTotal;
            $item['unit_amount'] = (float)$item['unit_amounts'][0];
            $adjustedRows[] = $item;
        }
        $adjustedTotal = round($adjustedTotal,2);
        if(abs($adjustedTotal-$dealTotal)>0.01)
        {
            throw new Exception(sprintf(__('Adjusted schedule total is %s and must equal the discounted deal total %s.'),CakeNumber::format($adjustedTotal),CakeNumber::format($dealTotal)));
        }
        return array(
            'enabled'=>true,
            'rows'=>$adjustedRows,
            'booking_amount'=>round($bookingAmount,2),
            'plan_total'=>$planTotal,
            'discount'=>max(0,round($grossDealAmount-$dealTotal,2)),
            'schedule_total'=>$adjustedTotal,
            'adjustment_amount'=>round($dealTotal-$planTotal,2)
        );
    }

    protected function _dealScheduleDateByMonthOffset($baseTimestamp, $monthOffset)
    {
        $monthOffset = max(0,(int)$monthOffset);
        $baseDay = (int)date('j',$baseTimestamp);
        $targetMonthStart = strtotime(date('Y-m-01',$baseTimestamp).' +'.$monthOffset.' month');
        $targetDay = min($baseDay,(int)date('t',$targetMonthStart));
        return date('Y-m-', $targetMonthStart).str_pad($targetDay,2,'0',STR_PAD_LEFT);
    }

    protected function _createDealScheduleFromTemplate($dealId, $template, $dealDate, $atomic = true)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0 || empty($template['enabled']) || empty($template['rows']))
        {
            return false;
        }
        $this->loadModel('PlansPayment');
        $baseTimestamp = strtotime((string)$dealDate);
        if(!$baseTimestamp) $baseTimestamp = time();
        $recurringTypes = array('monthly','quarterly','biannual');
        $terminalMonth = 0;
        foreach($template['rows'] as $row)
        {
            $type = strtolower(trim((string)$row['payment_type']));
            $count = max(1,(int)$row['repeat_count']);
            $intervalMonths = max(0,(int)$row['interval_months']);
            if(in_array($type,$recurringTypes,true) || ($type==='custom' && $count>1))
            {
                $terminalMonth = max($terminalMonth,$intervalMonths*$count);
            }
            elseif($type==='confirmation')
            {
                $terminalMonth = max($terminalMonth,$intervalMonths);
            }
        }
        $terminalCursor = $terminalMonth;
        $saveRows = array();
        foreach($template['rows'] as $sortIndex=>$row)
        {
            $count = max(1,(int)$row['repeat_count']);
            $intervalMonths = max(0,(int)$row['interval_months']);
            $type = strtolower(trim((string)$row['payment_type']));
            $unitAmounts = !empty($row['unit_amounts']) ? array_values($row['unit_amounts']) : array_fill(0,$count,(float)$row['unit_amount']);
            for($index=1;$index<=$count;$index++)
            {
                $dueMonth = 0;
                if(in_array($type,$recurringTypes,true) || ($type==='custom' && $count>1))
                {
                    $dueMonth = $intervalMonths*$index;
                }
                elseif($type==='confirmation')
                {
                    $dueMonth = $intervalMonths;
                }
                elseif(in_array($type,array('possession','final'),true))
                {
                    $terminalCursor += max(1,$intervalMonths);
                    $dueMonth = $terminalCursor;
                }
                elseif($type==='custom')
                {
                    $dueMonth = $intervalMonths;
                }
                $title = trim((string)$row['title']);
                if($count>1) $title .= ' '.$index;
                $dueDate = $this->_dealScheduleDateByMonthOffset($baseTimestamp,$dueMonth);
                $dueTimestamp = strtotime($dueDate);
                $saveRows[] = array(
                    '_due_timestamp'=>$dueTimestamp,
                    '_sort_index'=>(int)$sortIndex,
                    '_payment_index'=>(int)$index,
                    'PlansPayment'=>array(
                        'deal_id'=>$dealId,
                        'name'=>$title,
                        'amount'=>round((float)(isset($unitAmounts[$index-1]) ? $unitAmounts[$index-1] : $row['unit_amount']),2),
                        'date'=>$dueDate,
                        'is_deal_charge'=>0,
                        'deal_charge_id'=>0
                    )
                );
            }
        }
        if(empty($saveRows)) return false;
        usort($saveRows,function($left,$right){
            if($left['_due_timestamp']===$right['_due_timestamp'])
            {
                if($left['_sort_index']===$right['_sort_index']) return $left['_payment_index']-$right['_payment_index'];
                return $left['_sort_index']-$right['_sort_index'];
            }
            return $left['_due_timestamp']<$right['_due_timestamp'] ? -1 : 1;
        });
        // Clean up un-paid regular rows to avoid partial leftovers
        try
        {
            $this->PlansPayment->deleteAll(array(
                'PlansPayment.deal_id' => $dealId,
                'OR' => array('PlansPayment.is_deal_charge' => 0, 'PlansPayment.is_deal_charge IS NULL')
            ), false);
        }
        catch(Exception $delEx) {}

        // Fetch MAX(id) and assign strictly ascending IDs to prevent duplicate key collisions
        $maxIdRow = $this->PlansPayment->query("SELECT MAX(`id`) AS max_id FROM `plans_payments`");
        $currentMaxId = 0;
        if(!empty($maxIdRow[0][0]['max_id'])) { $currentMaxId = (int)$maxIdRow[0][0]['max_id']; }
        elseif(!empty($maxIdRow[0]['plans_payments']['max_id'])) { $currentMaxId = (int)$maxIdRow[0]['plans_payments']['max_id']; }
        $nextPlanId = $currentMaxId + 1;

        foreach($saveRows as $key=>$saveRow)
        {
            unset($saveRows[$key]['_due_timestamp'],$saveRows[$key]['_sort_index'],$saveRows[$key]['_payment_index']);
            $saveRows[$key]['PlansPayment']['id'] = $nextPlanId++;
        }

        $savedOk = false;
        try
        {
            $results = $this->PlansPayment->saveAll($saveRows,array('atomic'=>(bool)$atomic,'validate'=>false));
            $savedOk = $atomic ? (bool)$results : (is_array($results) && !in_array(false,$results,true));
        }
        catch(Exception $saveEx)
        {
            $savedOk = false;
        }

        if(!$savedOk)
        {
            $savedOk = true;
            foreach($saveRows as $sRow)
            {
                $rec = isset($sRow['PlansPayment']) ? $sRow['PlansPayment'] : $sRow;
                $curMax = $this->PlansPayment->query("SELECT MAX(`id`) AS max_id FROM `plans_payments`");
                $freshMax = !empty($curMax[0][0]['max_id']) ? (int)$curMax[0][0]['max_id'] : (int)$nextPlanId;
                $rec['id'] = $freshMax + 1;
                $this->PlansPayment->create();
                if(!$this->PlansPayment->save(array('PlansPayment' => $rec), array('validate' => false, 'callbacks' => false)))
                {
                    $savedOk = false;
                    break;
                }
            }
        }

        try
        {
            $finMax = $this->PlansPayment->query("SELECT MAX(`id`) AS max_id FROM `plans_payments`");
            $finalMaxId = !empty($finMax[0][0]['max_id']) ? (int)$finMax[0][0]['max_id'] : 0;
            if($finalMaxId > 0)
            {
                $this->PlansPayment->query("ALTER TABLE `plans_payments` AUTO_INCREMENT = " . ($finalMaxId + 1));
            }
        }
        catch(Exception $aiEx) {}

        return $savedOk;
    }

    protected function _dealAccountingProjectId($dealRow = array(), $propertyRow = array())
    {
        if(!empty($propertyRow['Property']['project_id']))
        {
            return (int)$propertyRow['Property']['project_id'];
        }
        $propertyId = 0;
        if(!empty($dealRow['Deal']['property_id']))
        {
            $propertyId = (int)$dealRow['Deal']['property_id'];
        }
        elseif(!empty($dealRow['property_id']))
        {
            $propertyId = (int)$dealRow['property_id'];
        }
        if($propertyId > 0)
        {
            try
            {
                $this->loadModel('Property');
                return (int)$this->Property->field('project_id', array('Property.id' => $propertyId));
            }
            catch(Exception $e) {}
        }
        return 0;
    }

    protected function _syncDealDiscountVoucher($dealId, $dealRow = array(), $propertyRow = array())
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return 0;
        }
        $this->loadModel('Admin.Ardtransaction');
        $this->loadModel('Admin.Ardjournalentry');
        $deal = !empty($dealRow['Deal']) ? (array)$dealRow['Deal'] : (array)$dealRow;
        if(empty($deal))
        {
            $dealRow = $this->Deal->find('first', array(
                'conditions' => array('Deal.id' => $dealId),
                'recursive' => -1
            ));
            $deal = !empty($dealRow['Deal']) ? (array)$dealRow['Deal'] : array();
        }
        $existingTxId = !empty($deal['ard_discount_transaction_id']) ? (int)$deal['ard_discount_transaction_id'] : 0;
        $orConditions = array(
            array(
                'Ardtransaction.source_type' => 'deal_discount',
                'Ardtransaction.source_id' => $dealId
            ),
            array(
                'Ardtransaction.description' => 'Deal discount / waiver for deal #'.$dealId
            )
        );
        if($existingTxId > 0)
        {
            $orConditions[] = array('Ardtransaction.id' => $existingTxId);
        }
        $voucherRows = (array)$this->Ardtransaction->find('all', array(
            'fields' => array('Ardtransaction.id','Ardtransaction.ledger_id','Ardtransaction.income_ledger_id','Ardtransaction.expense_ledger_id'),
            'conditions' => array('OR' => $orConditions),
            'recursive' => -1
        ));
        $ledgerIds = array();
        foreach($voucherRows as $voucherRow)
        {
            if(empty($voucherRow['Ardtransaction']['id']))
            {
                continue;
            }
            $voucher = (array)$voucherRow['Ardtransaction'];
            foreach(array('ledger_id','income_ledger_id','expense_ledger_id') as $ledgerField)
            {
                if(!empty($voucher[$ledgerField])){$ledgerIds[(int)$voucher[$ledgerField]] = (int)$voucher[$ledgerField];}
            }
            $voucherId = (int)$voucher['id'];
            $this->Ardjournalentry->deleteAll(array('Ardjournalentry.transaction_id' => $voucherId), false);
            $this->Ardtransaction->delete($voucherId);
        }
        try
        {
            $this->Deal->query("UPDATE deals SET ard_discount_transaction_id=0 WHERE id=".$dealId);
        }
        catch(Exception $e) {}
        foreach($ledgerIds as $ledgerId)
        {
            $this->_ardRecalculateLedgerBalanceCompat((int)$ledgerId);
        }
        return 0;
    }
    protected function _dealDiscountChangeLockMessage($existingDeal, $newDiscount)
    {
        // Deal discounts affect the sale price only and no longer create or lock accounting vouchers.
        return '';
    }

    protected function _syncDealDiscountSchedule($dealId, $oldDiscount, $newDiscount, $existingDeal)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return false;
        }
        $this->loadModel('PlansPayment');
        $this->loadModel('DealsPayment');

        $diff = round((float)$newDiscount - (float)$oldDiscount, 2);
        if(abs($diff) <= 0.001)
        {
            return true;
        }

        $receiptCount = (int)$this->DealsPayment->find('count', array(
            'conditions' => array('DealsPayment.deal_id' => $dealId),
            'recursive' => -1
        ));

        $planId = !empty($existingDeal['Deal']['plan_id']) ? (int)$existingDeal['Deal']['plan_id'] : 0;
        $existingTotal = !empty($existingDeal['Deal']['total_amount']) ? (float)$existingDeal['Deal']['total_amount'] : 0.0;
        $chargeTotals = $this->_dealChargeTotalsByDealIds(array($dealId));
        $dealCharges = isset($chargeTotals[$dealId]) ? (float)$chargeTotals[$dealId] : 0.0;
        $grossRegularTotal = max(0, round($existingTotal - $dealCharges + (float)$oldDiscount, 2));
        $newRegularTotal = max(0, round($grossRegularTotal - (float)$newDiscount, 2));

        if($receiptCount === 0 && $planId > 0)
        {
            $template = $this->_dealPlanTemplate($planId, $newRegularTotal, $grossRegularTotal);
            if(!empty($template['enabled']))
            {
                $regularScheduleConditions = array(
                    'PlansPayment.deal_id' => $dealId,
                    'OR' => array('PlansPayment.is_deal_charge' => 0, 'PlansPayment.is_deal_charge IS NULL')
                );
                $this->PlansPayment->deleteAll($regularScheduleConditions, false);
                $scheduleDate = !empty($existingDeal['Deal']['date']) ? $existingDeal['Deal']['date'] : date('Y-m-d');
                $this->_createDealScheduleFromTemplate($dealId, $template, $scheduleDate, false);
                return true;
            }
        }

        $installments = $this->PlansPayment->find('all', array(
            'conditions' => array(
                'PlansPayment.deal_id' => $dealId,
                'OR' => array('PlansPayment.is_deal_charge' => 0, 'PlansPayment.is_deal_charge IS NULL')
            ),
            'order' => array('PlansPayment.id' => 'ASC'),
            'recursive' => -1
        ));

        if(empty($installments))
        {
            return true;
        }

        $paidRows = $this->DealsPayment->find('all', array(
            'fields' => array('DealsPayment.plans_payment_id', 'SUM(DealsPayment.emi) AS paid_total'),
            'conditions' => array('DealsPayment.deal_id' => $dealId),
            'group' => array('DealsPayment.plans_payment_id'),
            'recursive' => -1
        ));
        $paidMap = array();
        foreach((array)$paidRows as $pr)
        {
            $ppId = (int)$pr['DealsPayment']['plans_payment_id'];
            if($ppId > 0)
            {
                $paidMap[$ppId] = isset($pr[0]['paid_total']) ? round((float)$pr[0]['paid_total'], 2) : 0.0;
            }
        }

        if($diff > 0)
        {
            $reductionRemaining = $diff;
            for($i = count($installments) - 1; $i >= 0 && $reductionRemaining > 0.001; $i--)
            {
                $inst = $installments[$i]['PlansPayment'];
                $instId = (int)$inst['id'];
                $currentAmt = round((float)$inst['amount'], 2);
                $paidAmt = isset($paidMap[$instId]) ? $paidMap[$instId] : 0.0;
                $reducible = max(0, round($currentAmt - $paidAmt, 2));

                if($reducible > 0)
                {
                    $reduction = min($reducible, $reductionRemaining);
                    $newAmt = round($currentAmt - $reduction, 2);
                    $reductionRemaining = round($reductionRemaining - $reduction, 2);

                    if($newAmt <= 0 && $paidAmt <= 0)
                    {
                        $this->PlansPayment->delete($instId);
                    }
                    else
                    {
                        $this->PlansPayment->id = $instId;
                        $this->PlansPayment->saveField('amount', $newAmt);
                    }
                }
            }
        }
        else
        {
            $increaseRemaining = abs($diff);
            for($i = count($installments) - 1; $i >= 0 && $increaseRemaining > 0.001; $i--)
            {
                $inst = $installments[$i]['PlansPayment'];
                $instId = (int)$inst['id'];
                $currentAmt = round((float)$inst['amount'], 2);
                $newAmt = round($currentAmt + $increaseRemaining, 2);
                $this->PlansPayment->id = $instId;
                $this->PlansPayment->saveField('amount', $newAmt);
                $increaseRemaining = 0;
                break;
            }
        }

        return true;
    }
    protected function _syncDealCancellationForfeitureVoucher($dealRow, $status)
    {
        $deal = !empty($dealRow['Deal']) ? (array)$dealRow['Deal'] : (array)$dealRow;
        $dealId = !empty($deal['id']) ? (int)$deal['id'] : 0;
        $status = trim((string)$status);
        if($dealId <= 0)
        {
            return 0;
        }
        $projectId = $this->_dealAccountingProjectId(array('Deal' => $deal));
        if($projectId <= 0)
        {
            return 0;
        }
        if(!in_array($status, array('Permanent Blocked','Pemanent Blocked'), true))
        {
            $this->_ardVoidAutoVoucherBySource($projectId, 'deal_cancellation', $dealId, 'Deal cancellation status removed');
            return 0;
        }
        $this->loadModel('DealsPayment');
        $paidRow = $this->DealsPayment->find('first', array(
            'fields' => array('SUM(DealsPayment.amount) AS paid_total'),
            'conditions' => array('DealsPayment.deal_id' => $dealId),
            'recursive' => -1
        ));
        $paidTotal = isset($paidRow[0]['paid_total']) ? round((float)$paidRow[0]['paid_total'], 2) : 0;
        if($paidTotal <= 0)
        {
            return 0;
        }
        $sourceLedgerId = $this->_ardGetOrCreateLedger($projectId, 'Cancellation Clearing', 'asset', 'Auto Business Clearing');
        return $this->_ardPostAutoVoucher(
            $projectId,
            $sourceLedgerId,
            'income',
            $paidTotal,
            'CANCEL-'.$dealId,
            date('Y-m-d'),
            'Cancellation / forfeiture for deal #'.$dealId,
            array(
                'income_settings_field' => 'cancellation_forfeiture_income_ledger_id',
                'income_fallback' => 'Cancellation / Forfeiture Income',
                'source_type' => 'deal_cancellation',
                'source_id' => $dealId,
                'status' => 'Posted'
            )
        );
    }

    public function add()
    {
        $this->loadModel('Client');
        $this->loadModel('Property');
        $this->loadModel('Project');
        $this->loadModel('User');
        $this->loadModel('Admin.Plan');
        $this->loadModel('Type');
        $this->loadModel('Contract');
        $condition=$this->CustomFunction->agencyWisePermission(null,$this->userType,$this->agencyId,'User.project_id');
        $this->set('type',$this->Type->find('list'));
        $this->set('contract',$this->Contract->find('list'));
        if(!$this->_isSuperAdminSession() && (int)$this->agencyId > 0)
        {
            $this->set('clientId',$this->Client->find('list',array('fields'=>array('id','name'),'conditions'=>array('Client.project_id'=>(int)$this->agencyId))));
            $this->set('propertyId',$this->Property->find('list',array('fields'=>array('id','name'),'conditions'=>array('Property.project_id'=>(int)$this->agencyId,'Property.status'=>'Available'))));
            $projectName = $this->Project->find('list',array('conditions'=>array('Project.id'=>(int)$this->agencyId)));
            $this->set('projectName',$projectName);
        }
        else
        {
            $this->set('clientId',$this->Client->find('list',array('fields'=>array('id','name'))));
            $this->set('propertyId',$this->Property->find('list',array('fields'=>array('id','name'))));
            $projectName = $this->Project->find('list');
            $this->set('projectName',$projectName);
        }
        $this->set('plan',$this->Plan->find('list',array(
            'fields'=>array('Plan.id','Plan.name'),
            'order'=>array('Plan.id'=>'DESC'),
            'limit'=>9,
            'recursive'=>-1
        )));
        $agentConditions = array(
            $this->User->assignableScopeConditions(0,'User'),
            array('User.status'=>'Active'),
            $condition
        );
        $this->set('agent',$this->_dealAgentOptionsWithAgency($agentConditions));
        $this->set('nomineeRelationOptions',$this->_nomineeRelationOptions);
        $canUseAgencyFilters = $this->_canUseAgencyFilters();
        $selectedDealProjectId = 0;
        if($canUseAgencyFilters)
        {
            if(isset($this->request->query['project_id']))
            {
                $selectedDealProjectId = (int)$this->request->query['project_id'];
            }
            elseif(isset($this->request->data['Filter']['project_id']))
            {
                $selectedDealProjectId = (int)$this->request->data['Filter']['project_id'];
            }
            if($selectedDealProjectId <= 0 || !isset($projectName[$selectedDealProjectId]))
            {
                foreach((array)$projectName as $projectId => $projectTitle)
                {
                    if((int)$projectId > 0)
                    {
                        $selectedDealProjectId = (int)$projectId;
                        break;
                    }
                }
            }
        }
        else
        {
            $selectedDealProjectId = (int)$this->agencyId;
        }
        $this->set('canUseAgencyFilters', $canUseAgencyFilters);
        $this->set('selectedDealProjectId', $selectedDealProjectId);
        $hasNomineeColumns = $this->_ensureDealNomineeColumns();
        $this->set('hasNomineeColumns',$hasNomineeColumns);
        $invoiceNo = $this->_peekDealInvoiceNo($selectedDealProjectId);
        $this->set('invoiceNo',$invoiceNo);
        if ($this->request->is('post'))
        {
            $this->Deal->create();
            try
            {
                $dealDiscount=0;
                if(!$hasNomineeColumns)
                {
                    unset($this->request->data['Deal']['nominee_name']);
                    unset($this->request->data['Deal']['nominee_cnic']);
                    unset($this->request->data['Deal']['nominee_mobile']);
                    unset($this->request->data['Deal']['nominee_relation']);
                    unset($this->request->data['Deal']['occupation']);
                }
                else
                {
                    $this->request->data['Deal']['nominee_name'] = isset($this->request->data['Deal']['nominee_name']) ? trim($this->request->data['Deal']['nominee_name']) : null;
                    $this->request->data['Deal']['nominee_cnic'] = isset($this->request->data['Deal']['nominee_cnic']) ? trim($this->request->data['Deal']['nominee_cnic']) : null;
                    $this->request->data['Deal']['nominee_mobile'] = isset($this->request->data['Deal']['nominee_mobile']) ? trim($this->request->data['Deal']['nominee_mobile']) : null;
                    $relation = isset($this->request->data['Deal']['nominee_relation']) ? trim($this->request->data['Deal']['nominee_relation']) : '';
                    if(!array_key_exists($relation,$this->_nomineeRelationOptions))
                    {
                        $relation = null;
                    }
                    $this->request->data['Deal']['nominee_relation'] = $relation;
                    $this->request->data['Deal']['occupation'] = isset($this->request->data['Deal']['occupation']) ? trim((string)$this->request->data['Deal']['occupation']) : null;
                }

                if(empty($this->request->data['Deal']['property_id']))
                {
                    $this->Session->setFlash(__('Please select a property for this deal.'),'flash',array('alert'=>'danger'));
                    return;
                }
                $flatArr=$this->Property->findById((int)$this->request->data['Deal']['property_id']);
                if(empty($flatArr['Property']['id']))
                {
                    $this->Session->setFlash(__('Selected property not found.'),'flash',array('alert'=>'danger'));
                    return;
                }
                if(empty($this->request->data['Deal']['client_id']))
                {
                    $this->Session->setFlash(__('Please select a client for this deal.'),'flash',array('alert'=>'danger'));
                    return;
                }
                $dealProjectId = !empty($flatArr['Property']['project_id']) ? (int)$flatArr['Property']['project_id'] : 0;
                if(!$this->_isSuperAdminSession() && (int)$this->agencyId > 0 && $dealProjectId > 0 && (int)$this->agencyId !== $dealProjectId)
                {
                    $this->Session->setFlash(__('Access Denied. Selected property belongs to another agency.'),'flash',array('alert'=>'danger'));
                    return;
                }
                $selectedFormProjectId = isset($this->request->data['Filter']['project_id']) ? (int)$this->request->data['Filter']['project_id'] : 0;
                if($this->_canUseAgencyFilters() && $selectedFormProjectId > 0 && $dealProjectId > 0 && $selectedFormProjectId !== $dealProjectId)
                {
                    $this->Session->setFlash(__('Selected property does not belong to selected agency.'),'flash',array('alert'=>'danger'));
                    return;
                }
                $postedInvoiceNo = isset($this->request->data['Deal']['invoice_no']) ? trim((string)$this->request->data['Deal']['invoice_no']) : '';
                $projectPeekInvoiceNo = $this->_peekDealInvoiceNo($dealProjectId);
                if($postedInvoiceNo === '' || $postedInvoiceNo === $invoiceNo || $postedInvoiceNo === $projectPeekInvoiceNo)
                {
                    $this->request->data['Deal']['invoice_no'] = $this->_nextDealInvoiceNo($dealProjectId);
                }
                else
                {
                    $this->request->data['Deal']['invoice_no'] = $postedInvoiceNo;
                }
                $dealDate = !empty($this->request->data['Deal']['date']) ? trim((string)$this->request->data['Deal']['date']) : date('Y-m-d');
                $this->request->data['Deal']['date'] = date('Y-m-d', strtotime($dealDate));

                $dealAmount=$this->_dealMoneyValue(isset($flatArr['Property']['price']) ? $flatArr['Property']['price'] : 0);
                $dealArea=$flatArr['Property']['area'];
		        $contractFetch=$this->Contract->findById($flatArr['Property']['contract_id']);
                $dealDiscount=$this->_dealMoneyValue(isset($this->request->data['Deal']['discount']) ? $this->request->data['Deal']['discount'] : 0);
                $this->request->data['Deal']['discount']=$dealDiscount;
                $totalAmount=($dealAmount-$dealDiscount);
		        $selectedPlanId = isset($this->request->data['Deal']['plan_id']) ? (int)$this->request->data['Deal']['plan_id'] : 0;
		        $dealPlanTemplate = $this->_dealPlanTemplate($selectedPlanId,$totalAmount,$dealAmount);
		        if(!empty($dealPlanTemplate['enabled']))
		        {
		            $this->request->data['Deal']['booking_amount'] = $dealPlanTemplate['booking_amount'];
		        }
                else
                {
                    $this->request->data['Deal']['booking_amount'] = isset($this->request->data['Deal']['booking_amount']) ? $this->_dealMoneyValue($this->request->data['Deal']['booking_amount']) : 0;
                }
                $this->request->data['Deal']['area']=$dealArea;
                $this->request->data['Deal']['amount']=$dealAmount;
                $this->request->data['Deal']['total_amount']=$totalAmount;

                $isRentContract = (!empty($contractFetch['Contract']['name']) && strtolower($contractFetch['Contract']['name']) === 'rent') || (!empty($contractFetch['Contract']['id']) && (int)$contractFetch['Contract']['id'] === 1);
		        if($isRentContract)
                {
                    if(empty($this->request->data['Deal']['evocate_date']))
                    {
                        $this->Session->setFlash(__('Please fill the evocate date for rental contract.'),'flash',array('alert'=>'danger'));
                        return;
                    }
                    $this->request->data['Deal']['evocate_date'] = date('Y-m-d', strtotime((string)$this->request->data['Deal']['evocate_date']));
                }
                else
                {
                    $this->request->data['Deal']['evocate_date'] = null;
                }

                $existingDeal = null;
                if(!empty($this->request->data['Deal']['invoice_no']))
                {
                    $existingDeal = $this->Deal->find('first', array(
                        'conditions' => array('Deal.invoice_no' => $this->request->data['Deal']['invoice_no']),
                        'recursive' => -1
                    ));
                }

                $savedDealId = 0;
                $isCompletingExisting = false;
                if(!empty($existingDeal['Deal']['id']))
                {
                    $this->loadModel('DealsPayment');
                    $paidCount = (int)$this->DealsPayment->find('count', array(
                        'conditions' => array('DealsPayment.deal_id' => (int)$existingDeal['Deal']['id']),
                        'recursive' => -1
                    ));
                    if($paidCount === 0)
                    {
                        $savedDealId = (int)$existingDeal['Deal']['id'];
                        $this->request->data['Deal']['id'] = $savedDealId;
                        $this->Deal->id = $savedDealId;
                        $isCompletingExisting = true;
                    }
                }

                if($savedDealId > 0 || $this->Deal->save($this->request->data))
                {
                    if($savedDealId <= 0)
                    {
                        $savedDealId = (int)$this->Deal->id;
                    }
                    else
                    {
                        $this->Deal->save($this->request->data, false);
                    }
                    $autoScheduleCreated = false;
                    if(!empty($dealPlanTemplate['enabled']))
                    {
                        $savedDealDate = $this->Deal->field('date',array('Deal.id'=>$savedDealId));
                        if(!$this->_createDealScheduleFromTemplate($savedDealId,$dealPlanTemplate,$savedDealDate))
                        {
                            if(!$isCompletingExisting)
                            {
                                $this->Deal->delete($savedDealId);
                            }
                            throw new Exception(__('The deal was not created because its payment schedule could not be generated.'));
                        }
                        $autoScheduleCreated = true;
                    }
                    $this->_cleanupDealChargeArtifacts($savedDealId);
                    $dealAgentId = isset($this->request->data['Deal']['user_id']) ? (int)$this->request->data['Deal']['user_id'] : 0;
                    $dealCommissionType = isset($flatArr['Property']['commission_type']) ? trim((string)$flatArr['Property']['commission_type']) : '';
                    $dealCommissionRule = isset($flatArr['Property']['agent_commission']) ? (float)$flatArr['Property']['agent_commission'] : 0.0;
                    $dealCommissionAmount = 0.0;
                    if($dealAgentId>0 && $dealCommissionRule>0)
                    {
                        if(strtolower($dealCommissionType)==='percentage')
                        {
                            $dealCommissionAmount = round(($dealCommissionRule*(float)$totalAmount)/100,2);
                        }
                        elseif(strtolower($dealCommissionType)==='value')
                        {
                            $dealCommissionAmount = round($dealCommissionRule,2);
                        }
                    }
                    if($dealCommissionAmount>0 && $savedDealId>0)
                    {
                        $this->CustomFunction->awalletInsertDealCommissionOnce(
                            $dealAgentId,
                            $dealCommissionAmount,
                            $savedDealId,
                            'Deal commission credited at deal creation',
                            null
                        );
                    }
                    $this->_syncDealDiscountVoucher($savedDealId, array('Deal' => array(
                        'id' => $savedDealId,
                        'property_id' => $flatArr['Property']['id'],
                        'invoice_no' => $this->request->data['Deal']['invoice_no'],
                        'discount' => $dealDiscount,
                        'date' => isset($this->request->data['Deal']['date']) ? $this->request->data['Deal']['date'] : date('Y-m-d')
                    )), $flatArr);
                    $this->Property->save(array('id'=>$flatArr['Property']['id'],'status' => 'Sold'));
                    $this->_recordPropertyHistory($flatArr['Property']['id'],'sold',__('Property Sold'),isset($flatArr['Property']['status']) ? $flatArr['Property']['status'] : '',__('Sold'),array(
                        'deal_id'=>$savedDealId,
                        'client_id'=>isset($this->request->data['Deal']['client_id']) ? (int)$this->request->data['Deal']['client_id'] : null,
                        'project_id'=>$dealProjectId,
                        'amount_delta'=>$totalAmount,
                        'meta'=>array('invoice_no'=>$this->request->data['Deal']['invoice_no'],'property_price'=>$dealAmount,'discount'=>$dealDiscount,'deal_total'=>$totalAmount)
                    ));
                    $this->_autoCreateLettersForNewDeal((int)$this->Deal->id, (int)$dealProjectId, (int)$this->luserId);
                    $this->Session->setFlash(__('Deal has been saved.'),'flash',array('alert'=>'success'));
                    if(!empty($autoScheduleCreated))
                    {
                        return $this->redirect(array('controller'=>'deals_payments','action' => 'add',$this->Deal->id));
                    }
                    return $this->redirect(array('controller'=>'plans_payments','action' => 'add',$this->Deal->id));
                }
                else
                {
                    $errors = array();
                    if(!empty($this->Deal->validationErrors))
                    {
                        foreach($this->Deal->validationErrors as $field => $errMsgs)
                        {
                            $errors[] = implode(', ', (array)$errMsgs);
                        }
                    }
                    $errMsg = !empty($errors) ? implode(' | ', $errors) : __('The deal could not be saved. Please check your form values.');
                    $this->Session->setFlash($errMsg,'flash',array('alert'=>'danger'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            }
        }
    }

    

    public function edit($id = null)
    {
        $this->response->disableCache();
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $this->loadModel('User');
        $this->loadModel('Admin.Plan');
        $this->loadModel('PlansPayment');
        $this->loadModel('DealsPayment');
        $this->set('nomineeRelationOptions',$this->_nomineeRelationOptions);
        $hasNomineeColumns = $this->_ensureDealNomineeColumns();
        $this->set('hasNomineeColumns',$hasNomineeColumns);
        $post = $this->Deal->find('first',array('conditions'=>array('Deal.id'=>$id)));
        if(!empty($post['Deal']))
        {
            $this->Deal->id = (int)$id;
            $savedOccupation = $this->Deal->field('occupation');
            if($savedOccupation !== false && $savedOccupation !== null)
            {
                $post['Deal']['occupation'] = $savedOccupation;
            }
        }
        $this->set('post',$post);
        if (!$post)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        if (!$this->_canAccessDealRecord($post))
        {
            $this->Session->setFlash(__('Access Denied. You do not have permission to access deals from other agencies.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }
        $latestPlanRows = $this->Plan->find('all',array(
            'fields'=>array('Plan.id'),
            'order'=>array('Plan.id'=>'DESC'),
            'limit'=>9,
            'recursive'=>-1
        ));
        $dealPlanIds = Hash::extract($latestPlanRows,'{n}.Plan.id');
        $dealPlanIds[] = (int)$post['Deal']['plan_id'];
        $dealPlanIds = array_values(array_unique(array_filter(array_map('intval',$dealPlanIds))));
        $dealPlanOptions = $this->Plan->find('list',array(
            'fields'=>array('Plan.id','Plan.name'),
            'conditions'=>array('Plan.id'=>$dealPlanIds),
            'order'=>array('Plan.id'=>'DESC'),
            'recursive'=>-1
        ));
        $dealPlanReceiptCount = $this->DealsPayment->find('count',array(
            'conditions'=>array('DealsPayment.deal_id'=>(int)$id),
            'recursive'=>-1
        ));
        $dealPlanScheduleCount = $this->PlansPayment->find('count',array(
            'conditions'=>array(
                'PlansPayment.deal_id'=>(int)$id,
                'OR'=>array('PlansPayment.is_deal_charge'=>0,'PlansPayment.is_deal_charge IS NULL')
            ),
            'recursive'=>-1
        ));
        $dealChargeTotals = $this->_dealChargeTotalsByDealIds(array((int)$id));
        $dealPlanChargeTotal = isset($dealChargeTotals[(int)$id]) ? round((float)$dealChargeTotals[(int)$id],2) : 0;
        $dealPlanRegularTotal = max(0,round((float)$post['Deal']['total_amount']-$dealPlanChargeTotal,2));
        $dealPlanDiscount = !empty($post['Deal']['discount']) ? round((float)$post['Deal']['discount'],2) : 0;
        $dealPlanGrossTotal = round($dealPlanRegularTotal+$dealPlanDiscount,2);
        $this->set('dealPlanOptions',$dealPlanOptions);
        $this->set('dealPlanReceiptCount',(int)$dealPlanReceiptCount);
        $this->set('dealPlanScheduleCount',(int)$dealPlanScheduleCount);
        $this->set('dealPlanChargeTotal',$dealPlanChargeTotal);
        $this->set('dealPlanRegularTotal',$dealPlanRegularTotal);
        $this->set('dealPlanDiscount',$dealPlanDiscount);
        $this->set('dealPlanGrossTotal',$dealPlanGrossTotal);
        $canTransferCommissionAgent = $this->_isSuperAdminSession();
        $commissionTransferAgents = array();
        $currentDealCommissionAmount = 0;
        if($canTransferCommissionAgent)
        {
            $commissionTransferAgents = (array)$this->_dealAgentOptionsWithAgency(array(
                $this->User->assignableScopeConditions(0,'User'),
                array('User.status' => 'Active')
            ));
            $existingCommissionRow = $this->_findDealCommissionCreditRow((int)$id, !empty($post['Deal']['user_id']) ? (int)$post['Deal']['user_id'] : 0);
            if(empty($existingCommissionRow))
            {
                $existingCommissionRow = $this->_findDealCommissionCreditRow((int)$id, 0);
            }
            if(!empty($existingCommissionRow['amount']))
            {
                $currentDealCommissionAmount = round((float)$existingCommissionRow['amount'], 2);
            }
            else
            {
                $currentDealCommissionMeta = $this->_dealCommissionMeta($post);
                $currentDealCommissionAmount = !empty($currentDealCommissionMeta['amount']) ? round((float)$currentDealCommissionMeta['amount'], 2) : 0;
            }
        }
        $currentAgentDisplay = '';
        if(!empty($post['Deal']['user_id']))
        {
            $currentAgentDisplay = $this->_formatSingleAgentWithAgency((int)$post['Deal']['user_id']);
        }
        $this->set('currentAgentDisplay', $currentAgentDisplay);
        $this->set('canTransferCommissionAgent',$canTransferCommissionAgent);
        $this->set('commissionTransferAgents',$commissionTransferAgents);
        $this->set('currentDealCommissionAmount',$currentDealCommissionAmount);
        if ($this->request->is(array('post', 'put')))
        {
            $actionMode = isset($this->request->data['Deal']['action_mode']) ? trim((string)$this->request->data['Deal']['action_mode']) : '';
            if($actionMode === 'transfer_commission_agent')
            {
                $newAgentId = isset($this->request->data['CommissionTransfer']['new_agent_id']) ? (int)$this->request->data['CommissionTransfer']['new_agent_id'] : 0;
                $reason = isset($this->request->data['CommissionTransfer']['reason']) ? trim((string)$this->request->data['CommissionTransfer']['reason']) : '';
                $newCommissionAmount = isset($this->request->data['CommissionTransfer']['commission_amount']) ? $this->request->data['CommissionTransfer']['commission_amount'] : null;
                $transferResult = $this->_transferDealCommissionAgent((int)$id, $newAgentId, $reason, $newCommissionAmount);
                $this->Session->setFlash(isset($transferResult['message']) ? $transferResult['message'] : __('Unable to change commission agent.'),'flash',array('alert' => !empty($transferResult['ok']) ? 'success' : 'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            $inlineCommissionAgentId = isset($this->request->data['CommissionTransfer']['new_agent_id']) ? (int)$this->request->data['CommissionTransfer']['new_agent_id'] : 0;
            $inlineCommissionReason = isset($this->request->data['CommissionTransfer']['reason']) ? trim((string)$this->request->data['CommissionTransfer']['reason']) : '';
            $inlineCommissionAmountRaw = isset($this->request->data['CommissionTransfer']['commission_amount']) ? trim((string)$this->request->data['CommissionTransfer']['commission_amount']) : '';
            $inlineCommissionAmount = null;
            if($inlineCommissionAmountRaw !== '')
            {
                $inlineCommissionAmount = round((float)str_replace(',', '', $inlineCommissionAmountRaw), 2);
            }
            $hasInlineCommissionChange = false;
            if(!empty($canTransferCommissionAgent))
            {
                if($inlineCommissionAgentId > 0)
                {
                    $hasInlineCommissionChange = true;
                }
                elseif($inlineCommissionAmount !== null && abs((float)$inlineCommissionAmount - (float)$currentDealCommissionAmount) >= 0.01)
                {
                    $hasInlineCommissionChange = true;
                }
            }
            $this->Deal->id = $id;
            $planTransactionStarted = false;
            try
            {
                if($hasInlineCommissionChange)
                {
                    $transferResult = $this->_transferDealCommissionAgent((int)$id, $inlineCommissionAgentId, $inlineCommissionReason, $inlineCommissionAmount);
                    if(empty($transferResult['ok']))
                    {
                        $this->Session->setFlash(isset($transferResult['message']) ? $transferResult['message'] : __('Unable to change commission agent.'),'flash',array('alert'=>'danger'));
                        return $this->redirect(array('action' => 'index'));
                    }
                }
                if(!$hasNomineeColumns)
                {
                    unset($this->request->data['Deal']['nominee_name']);
                    unset($this->request->data['Deal']['nominee_cnic']);
                    unset($this->request->data['Deal']['nominee_mobile']);
                    unset($this->request->data['Deal']['nominee_relation']);
                    unset($this->request->data['Deal']['occupation']);
                }
                else
                {
                    $this->request->data['Deal']['nominee_name'] = isset($this->request->data['Deal']['nominee_name']) ? trim($this->request->data['Deal']['nominee_name']) : null;
                    $this->request->data['Deal']['nominee_cnic'] = isset($this->request->data['Deal']['nominee_cnic']) ? trim($this->request->data['Deal']['nominee_cnic']) : null;
                    $this->request->data['Deal']['nominee_mobile'] = isset($this->request->data['Deal']['nominee_mobile']) ? trim($this->request->data['Deal']['nominee_mobile']) : null;
                    $relation = isset($this->request->data['Deal']['nominee_relation']) ? trim($this->request->data['Deal']['nominee_relation']) : '';
                    if(!array_key_exists($relation,$this->_nomineeRelationOptions))
                    {
                        $relation = null;
                    }
                    $this->request->data['Deal']['nominee_relation'] = $relation;
                    $this->request->data['Deal']['occupation'] = isset($this->request->data['Deal']['occupation']) ? trim((string)$this->request->data['Deal']['occupation']) : null;
                }
                $oldDiscount = isset($post['Deal']['discount']) ? round((float)$post['Deal']['discount'], 2) : 0.0;
                $newDiscount = $oldDiscount;
                $discountChanged = false;
                if(isset($this->request->data['Deal']['discount']))
                {
                    $rawDiscount = trim((string)$this->request->data['Deal']['discount']);
                    $newDiscount = $rawDiscount !== '' ? max(0, round((float)str_replace(',', '', $rawDiscount), 2)) : 0.0;
                    $grossPrice = (float)$dealPlanGrossTotal;
                    if($grossPrice <= 0 && !empty($post['Property']['price']))
                    {
                        $grossPrice = (float)$post['Property']['price'];
                    }
                    if($grossPrice <= 0)
                    {
                        $grossPrice = round((float)$post['Deal']['total_amount'] + $oldDiscount, 2);
                    }
                    if($newDiscount > $grossPrice)
                    {
                        throw new Exception(sprintf(__('Discount (%s) cannot exceed the property gross price (%s).'), CakeNumber::format($newDiscount), CakeNumber::format($grossPrice)));
                    }
                    $discountLockMessage = $this->_dealDiscountChangeLockMessage($post, $newDiscount);
                    if($discountLockMessage !== '')
                    {
                        $this->Session->setFlash($discountLockMessage,'flash',array('alert'=>'danger'));
                        return $this->redirect(array('action' => 'index'));
                    }
                    $discountChanged = abs($newDiscount - $oldDiscount) > 0.001;
                    $this->request->data['Deal']['discount'] = $newDiscount;
                    $newRegularTotal = max(0, round($grossPrice - $newDiscount, 2));
                    $this->request->data['Deal']['total_amount'] = round($newRegularTotal + (float)$dealPlanChargeTotal, 2);
                    $dealPlanRegularTotal = $newRegularTotal;
                }
                $oldPlanId = !empty($post['Deal']['plan_id']) ? (int)$post['Deal']['plan_id'] : 0;
                $newPlanId = isset($this->request->data['Deal']['plan_id']) ? (int)$this->request->data['Deal']['plan_id'] : $oldPlanId;
                $planChanged = $newPlanId !== $oldPlanId;
                $replacementTemplate = null;
                if($planChanged)
                {
                    if($newPlanId<=0)
                    {
                        throw new Exception(__('Please select a valid payment plan.'));
                    }
                    if($dealPlanReceiptCount>0)
                    {
                        throw new Exception(__('This deal already has payment receipts. Its payment plan cannot be replaced because that would change paid installment links.'));
                    }
                    $replacementTemplate = $this->_dealPlanTemplate($newPlanId,$dealPlanRegularTotal,$dealPlanGrossTotal);
                    if(empty($replacementTemplate['enabled']))
                    {
                        throw new Exception(__('The selected payment plan has no configured automatic schedule. Update that plan first, then assign it to this deal.'));
                    }
                    $this->request->data['Deal']['booking_amount'] = $replacementTemplate['booking_amount'];
                    $planDataSource = $this->Deal->getDataSource();
                    if(!$planDataSource->begin())
                    {
                        throw new Exception(__('Unable to start the payment-plan update.'));
                    }
                    $planTransactionStarted = true;
                }
                if ($this->Deal->save($this->request->data))
                {
                    if($planChanged)
                    {
                        $regularScheduleConditions = array(
                            'PlansPayment.deal_id'=>(int)$id,
                            'OR'=>array('PlansPayment.is_deal_charge'=>0,'PlansPayment.is_deal_charge IS NULL')
                        );
                        if(!$this->PlansPayment->deleteAll($regularScheduleConditions,false))
                        {
                            throw new Exception(__('Unable to replace the existing payment schedule.'));
                        }
                        $scheduleDate = !empty($this->request->data['Deal']['date']) ? $this->request->data['Deal']['date'] : $post['Deal']['date'];
                        if(!$this->_createDealScheduleFromTemplate((int)$id,$replacementTemplate,$scheduleDate,false))
                        {
                            throw new Exception(__('Unable to generate the selected payment-plan schedule.'));
                        }
                        if(!$planDataSource->commit())
                        {
                            throw new Exception(__('Unable to complete the payment-plan update.'));
                        }
                        $planTransactionStarted = false;
                    }
                    elseif($discountChanged)
                    {
                        $this->_syncDealDiscountSchedule((int)$id, $oldDiscount, $newDiscount, $post);
                    }
                    $this->Session->setFlash(__('Deal has been updated.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'index'));
                }
                if($planTransactionStarted)
                {
                    throw new Exception(__('Unable to save the deal before replacing its payment schedule.'));
                }
            }
            catch (Exception $e)
            {
                if($planTransactionStarted && isset($planDataSource))
                {
                    try{$planDataSource->rollback();}catch(Exception $ignore){}
                }
                $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->set('isError',true);
        }
        else
        {
            $this->layout = null;
            $this->set('isError',false);
        }
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }
    }
    public function deleteall()
    {
        try
        {
            if ($this->request->is('post'))
            {
                $this->loadModel('Property');
                foreach($this->data['Deal']['id'] as $key => $value)
                {
                    $dealArr=$this->Deal->findById($value);
                    if($dealArr)
                    {
                        if(!$this->_canAccessDealRecord($dealArr))
                        {
                            continue;
                        }
                        $propertyId=$dealArr['Deal']['property_id'];
                        $this->Property->save(array('id'=>$propertyId,'status' => 'Available'));
                    }
                    if($this->Deal->delete($value))
                    {
                        $this->_cleanupDealChargeArtifacts($value);
                    }
                }
                $this->Session->setFlash(__('Deal has been deleted.'),'flash',array('alert'=>'success'));
            }        
            $this->redirect(array('action' => 'index'));
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Please Delete Deals Payment first'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
    }
    public function view($id = null)
    {
        $this->layout = null;
        $this->response->disableCache();
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        try {
            $this->_ensureDealNomineeColumns();
        } catch (Exception $eNominee) {}
        $post = $this->Deal->find('first',array('conditions'=>array('Deal.id'=>$id)));
        if(!$post)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        try {
            $this->_autoGenerateDealScheduleIfMissing((int)$id);
        } catch (Exception $eSchedule) {}
        try {
            $dealProjectId = $this->_resolveDealProjectId((int)$id);
            $this->_autoCreateLettersForNewDeal((int)$id, (int)$dealProjectId, (int)$this->luserId);
        } catch (Exception $eLetters) {}
        if (!$this->_canAccessDealRecord($post))
        {
            $this->Session->setFlash(__('Access Denied. You do not have permission to access deals from other agencies.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }
        $this->Deal->id = (int)$id;
        $savedOccupation = $this->Deal->field('occupation');
        if($savedOccupation !== false && $savedOccupation !== null)
        {
            $post['Deal']['occupation'] = $savedOccupation;
        }
        $agencyProject = array();
        if(!empty($post['Property']['project_id']))
        {
            $this->loadModel('Project');
            $agencyProject = $this->Project->find('first',array(
                'conditions'=>array('Project.id'=>(int)$post['Property']['project_id']),
                'recursive'=>-1
            ));
        }
        $this->loadModel('DealsPayment');
        $paidRow = $this->DealsPayment->find('first',array(
            'fields'=>array('SUM(DealsPayment.emi) AS paid_total'),
            'conditions'=>array('DealsPayment.deal_id'=>(int)$id),
            'recursive'=>-1
        ));
        $paidAmount = isset($paidRow[0]['paid_total']) ? (float)$paidRow[0]['paid_total'] : 0;
        $this->loadModel('PlansPayment');
        $scheduleCount = (int)$this->PlansPayment->find('count',array(
            'conditions'=>array('PlansPayment.deal_id'=>(int)$id),
            'recursive'=>-1
        ));
        $this->set('post',$post);
        $this->set('agencyProject',$agencyProject);
        $this->set('paidAmount',$paidAmount);
        $this->set('scheduleCount',$scheduleCount);
    }
    public function printinvoice($id = null)
    {
        $this->layout = null;
        $this->loadModel('PlansPayment');
        $this->loadModel('Project');
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $this->loadModel('DealsPayment');
        $post = $this->Deal->findById($id);
        if(!$post)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        if (!$this->_canAccessDealRecord($post))
        {
            $this->Session->setFlash(__('Access Denied. You do not have permission to access deals from other agencies.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }
        $plansPaymentArr = $this->PlansPayment->find('all',array(
            'conditions'=>array('PlansPayment.deal_id'=>$id),
            'order'=>array('PlansPayment.id'=>'asc'),
            'recursive'=>-1
        ));
        $planStatusMap = array();
        if(!empty($plansPaymentArr))
        {
            $planIds = array();
            foreach($plansPaymentArr as $planRow)
            {
                if(isset($planRow['PlansPayment']['id']))
                {
                    $planIds[] = (int)$planRow['PlansPayment']['id'];
                }
            }
            $planIds = array_values(array_unique(array_filter($planIds)));
            if(!empty($planIds))
            {
                $paidRows = $this->DealsPayment->find('all',array(
                    'fields'=>array('DealsPayment.plans_payment_id','SUM(DealsPayment.emi) AS paid_total'),
                    'conditions'=>array(
                        'DealsPayment.deal_id'=>$id,
                        'DealsPayment.plans_payment_id'=>$planIds
                    ),
                    'group'=>array('DealsPayment.plans_payment_id'),
                    'recursive'=>-1
                ));
                $paidMap = array();
                foreach((array)$paidRows as $paidRow)
                {
                    $planId = isset($paidRow['DealsPayment']['plans_payment_id']) ? (int)$paidRow['DealsPayment']['plans_payment_id'] : 0;
                    if($planId<=0)
                    {
                        continue;
                    }
                    $paidMap[$planId] = isset($paidRow[0]['paid_total']) ? (float)$paidRow[0]['paid_total'] : 0;
                }
                $today = !empty($this->currentDate) ? $this->currentDate : date('Y-m-d');
                foreach($plansPaymentArr as $planRow)
                {
                    $planId = isset($planRow['PlansPayment']['id']) ? (int)$planRow['PlansPayment']['id'] : 0;
                    if($planId<=0)
                    {
                        continue;
                    }
                    $planAmount = isset($planRow['PlansPayment']['amount']) ? (float)$planRow['PlansPayment']['amount'] : 0;
                    $paidAmount = isset($paidMap[$planId]) ? (float)$paidMap[$planId] : 0;
                    $pendingAmount = round($planAmount-$paidAmount,2);
                    if($paidAmount <= 0)
                    {
                        $dueDate = isset($planRow['PlansPayment']['date']) ? $planRow['PlansPayment']['date'] : '';
                        if(strlen($dueDate)>0 && $dueDate<=$today)
                        {
                            $status = 'Overdue';
                        }
                        else
                        {
                            $status = 'Un-Paid';
                        }
                    }
                    elseif($pendingAmount <= 0)
                    {
                        $status = 'Paid';
                    }
                    else
                    {
                        $status = 'Partial';
                    }
                    $planStatusMap[$planId] = array(
                        'status'=>$status,
                        'paid'=>$paidAmount,
                        'pending'=>$pendingAmount
                    );
                }
            }
        }
        $this->set('plansPaymentArr',$plansPaymentArr);
        $this->set('planStatusMap',$planStatusMap);
        $dealChargeRows = array();
        $dealChargeTotal = 0;
        $dealChargeNames = array();
        try
        {
            $this->loadModel('Admin.DealCharge');
            $dealChargeRows = $this->DealCharge->find('all', array(
                'conditions' => array(
                    'DealCharge.deal_id' => (int)$id,
                    'DealCharge.status' => 1
                ),
                'order' => array('DealCharge.id' => 'asc'),
                'recursive' => -1
            ));
            foreach((array)$dealChargeRows as $dealChargeRow)
            {
                if(empty($dealChargeRow['DealCharge']))
                {
                    continue;
                }
                $dealCharge = $dealChargeRow['DealCharge'];
                $dealChargeTotal += isset($dealCharge['total_amount']) ? (float)$dealCharge['total_amount'] : 0;
                $chargeName = isset($dealCharge['charge_name']) ? trim((string)$dealCharge['charge_name']) : '';
                if($chargeName !== '')
                {
                    $dealChargeNames[$chargeName] = $chargeName;
                }
            }
        }
        catch(Exception $e)
        {
            $dealChargeRows = array();
            $dealChargeTotal = 0;
            $dealChargeNames = array();
        }
        $this->set('dealChargeRows',$dealChargeRows);
        $this->set('dealChargeTotal',round((float)$dealChargeTotal, 2));
        $this->set('dealChargeNames',array_values($dealChargeNames));
        $agencyProject = array();
        $agencyLogoFile = '';
        $projectId = 0;
        if(!empty($post['Property']['project_id']))
        {
            $projectId = (int)$post['Property']['project_id'];
        }
        if($projectId > 0)
        {
            $agencyProjectRow = $this->Project->find('first', array(
                'fields' => array('Project.*'),
                'conditions' => array('Project.id' => $projectId),
                'recursive' => -1
            ));
            if(!empty($agencyProjectRow['Project']))
            {
                $agencyProject = $agencyProjectRow['Project'];
            }
            $this->loadModel('User');
            $userFields = array('User.id');
            if($this->User->hasField('photo'))
            {
                $userFields[] = 'User.photo';
            }
            $baseConditions = array(
                'User.project_id' => $projectId,
                'User.photo <>' => ''
            );
            $agencyUserRow = array();
            if($this->User->hasField('ugroup_id'))
            {
                $agencyUserRow = $this->User->find('first', array(
                    'fields' => $userFields,
                    'conditions' => array_merge($baseConditions, array('User.ugroup_id' => 4)),
                    'order' => array('User.id' => 'asc'),
                    'recursive' => -1
                ));
            }
            if(empty($agencyUserRow))
            {
                $agencyUserRow = $this->User->find('first', array(
                    'fields' => $userFields,
                    'conditions' => $baseConditions,
                    'order' => array('User.id' => 'asc'),
                    'recursive' => -1
                ));
            }
            if(!empty($agencyUserRow['User']['photo']))
            {
                $agencyLogoFile = trim((string)$agencyUserRow['User']['photo']);
            }
        }
        $this->set('agencyProject',$agencyProject);
        $this->set('agencyLogoFile',$agencyLogoFile);
        $this->set('agency_logo',$agencyLogoFile);
        $this->set('post',$post);
    }

    public function calendar_ics($id = null)
    {
        $this->autoRender = false;
        $id = (int)$id;
        if ($id <= 0) {
            throw new NotFoundException(__('Invalid Deal'));
        }

        $this->loadModel('Admin.Deal');
        $this->loadModel('Admin.PlansPayment');
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.InstallmentSyncSetting');

        $deal = $this->Deal->find('first', array(
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Property.unit_id=Unit.id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Deal.client_id=Client.id'))
            ),
            'fields' => array('Deal.*', 'Property.*', 'Project.*', 'Unit.*', 'Client.*'),
            'conditions' => array('Deal.id' => $id),
            'recursive' => -1
        ));

        if (empty($deal)) {
            throw new NotFoundException(__('Deal not found.'));
        }

        $projectId = !empty($deal['Project']['id']) ? (int)$deal['Project']['id'] : 0;
        $settings = $this->InstallmentSyncSetting->getSettings($projectId);

        $planRows = $this->PlansPayment->find('all', array(
            'conditions' => array('PlansPayment.deal_id' => $id),
            'order' => array('PlansPayment.date' => 'ASC', 'PlansPayment.id' => 'ASC'),
            'recursive' => -1
        ));

        $paidRows = $this->DealsPayment->find('all', array(
            'fields' => array('DealsPayment.plans_payment_id', 'SUM(DealsPayment.emi) AS paid_total'),
            'conditions' => array('DealsPayment.deal_id' => $id, 'DealsPayment.plans_payment_id >' => 0),
            'group' => array('DealsPayment.plans_payment_id'),
            'recursive' => -1
        ));
        $paidMap = array();
        foreach ((array)$paidRows as $pr) {
            $pid = (int)$pr['DealsPayment']['plans_payment_id'];
            $paidMap[$pid] = isset($pr[0]['paid_total']) ? (float)$pr[0]['paid_total'] : 0;
        }

        $agencyName = !empty($deal['Project']['name']) ? $deal['Project']['name'] : $this->siteOrganization;
        $propName = !empty($deal['Property']['name']) ? $deal['Property']['name'] : 'White Villas Property';
        $invoiceNo = !empty($deal['Deal']['invoice_no']) ? $deal['Deal']['invoice_no'] : 'WV-'.$id;
        $clientName = !empty($deal['Client']['name']) ? $deal['Client']['name'] : 'Valued Resident';

        $alarmDays = array(7, 3, 1);
        if (!empty($settings['calendar_reminder_days'])) {
            $parsedAlarms = array_map('intval', explode(',', $settings['calendar_reminder_days']));
            $alarmDays = array_values(array_filter($parsedAlarms, function($d) { return $d > 0; }));
        }

        $alarmTime = !empty($settings['calendar_alarm_time']) ? trim($settings['calendar_alarm_time']) : '09:00';
        $timeParts = explode(':', $alarmTime);
        $alarmHour = isset($timeParts[0]) ? str_pad(trim($timeParts[0]), 2, '0', STR_PAD_LEFT) : '09';
        $alarmMin = isset($timeParts[1]) ? str_pad(trim($timeParts[1]), 2, '0', STR_PAD_LEFT) : '00';

        $eol = "\r\n";
        $ics = "BEGIN:VCALENDAR" . $eol;
        $ics .= "VERSION:2.0" . $eol;
        $ics .= "PRODID:-//White Villas//Installment Schedule//EN" . $eol;
        $ics .= "CALSCALE:GREGORIAN" . $eol;
        $ics .= "METHOD:PUBLISH" . $eol;
        $ics .= "X-WR-CALNAME:" . $this->_escapeIcs("Installments: {$propName} ({$invoiceNo})") . $eol;
        $ics .= "X-WR-TIMEZONE:Asia/Karachi" . $eol;

        $dtStamp = gmdate('Ymd\THis\Z');

        foreach ($planRows as $row) {
            $pId = (int)$row['PlansPayment']['id'];
            $pName = !empty($row['PlansPayment']['name']) ? $row['PlansPayment']['name'] : 'Installment';
            $pAmt = isset($row['PlansPayment']['amount']) ? (float)$row['PlansPayment']['amount'] : 0;
            $pPaid = isset($paidMap[$pId]) ? (float)$paidMap[$pId] : 0;
            $pPending = max(0, round($pAmt - $pPaid, 2));
            $dueDateStr = !empty($row['PlansPayment']['date']) ? $row['PlansPayment']['date'] : null;

            if (empty($dueDateStr) || $pPending <= 0) {
                continue;
            }

            $dueDateTime = strtotime($dueDateStr . ' ' . $alarmHour . ':' . $alarmMin . ':00');
            $dtStart = date('Ymd\T', $dueDateTime) . $alarmHour . $alarmMin . '00';
            $dtEnd = date('Ymd\T', $dueDateTime + 3600) . $alarmHour . $alarmMin . '00';
            $uid = "wv-plan-{$pId}-deal-{$id}-" . date('Ymd', $dueDateTime) . "@whitevillas.com.pk";

            $summary = "Installment Due: {$pName} - Rs. " . number_format($pPending) . " ({$propName})";
            $description = "Installment: {$pName}\\n"
                . "Property: {$propName}\\n"
                . "Deal / Membership: {$invoiceNo}\\n"
                . "Client: {$clientName}\\n"
                . "Amount Due: Rs. " . number_format($pPending) . "\\n"
                . "Scheduled Due Date: " . date('d-M-Y', $dueDateTime) . "\\n"
                . "Project: {$agencyName}\\n\\n"
                . "Please make payment before due date to avoid late surcharges.";

            $location = $agencyName;

            $ics .= "BEGIN:VEVENT" . $eol;
            $ics .= "UID:{$uid}" . $eol;
            $ics .= "DTSTAMP:{$dtStamp}" . $eol;
            $ics .= "DTSTART:{$dtStart}" . $eol;
            $ics .= "DTEND:{$dtEnd}" . $eol;
            $ics .= "SUMMARY:" . $this->_escapeIcs($summary) . $eol;
            $ics .= "DESCRIPTION:" . $this->_escapeIcs($description) . $eol;
            $ics .= "LOCATION:" . $this->_escapeIcs($location) . $eol;
            $ics .= "STATUS:CONFIRMED" . $eol;

            foreach ($alarmDays as $daysBefore) {
                $ics .= "BEGIN:VALARM" . $eol;
                $ics .= "TRIGGER:-P{$daysBefore}D" . $eol;
                $ics .= "ACTION:DISPLAY" . $eol;
                $ics .= "DESCRIPTION:" . $this->_escapeIcs("Reminder: {$pName} Due in {$daysBefore} day(s) for {$propName}") . $eol;
                $ics .= "END:VALARM" . $eol;
            }

            $ics .= "END:VEVENT" . $eol;
        }

        $ics .= "END:VCALENDAR" . $eol;

        $cleanFileName = 'installments_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $invoiceNo) . '.ics';
        $this->response->type('text/calendar');
        $this->response->header('Content-Disposition', 'attachment; filename="' . $cleanFileName . '"');
        $this->response->body($ics);
        return $this->response;
    }

    public function early_discount_data($id = null)
    {
        $this->autoRender = false;
        $this->response->type('json');
        $id = (int)$id;

        if ($id <= 0) {
            $this->response->body(json_encode(array('status' => 'error', 'message' => __('Invalid Deal ID.'))));
            return $this->response;
        }

        $this->loadModel('Admin.Deal');
        $this->loadModel('Admin.PlansPayment');
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.InstallmentSyncSetting');

        $deal = $this->Deal->find('first', array(
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Property.unit_id=Unit.id'))
            ),
            'fields' => array('Deal.*', 'Property.*', 'Project.*', 'Unit.*'),
            'conditions' => array('Deal.id' => $id),
            'recursive' => -1
        ));

        if (empty($deal)) {
            $this->response->body(json_encode(array('status' => 'error', 'message' => __('Deal not found.'))));
            return $this->response;
        }

        $projectId = !empty($deal['Project']['id']) ? (int)$deal['Project']['id'] : 0;
        $settings = $this->InstallmentSyncSetting->getSettings($projectId);

        $planRows = $this->PlansPayment->find('all', array(
            'conditions' => array('PlansPayment.deal_id' => $id),
            'order' => array('PlansPayment.date' => 'ASC', 'PlansPayment.id' => 'ASC'),
            'recursive' => -1
        ));

        $paidRows = $this->DealsPayment->find('all', array(
            'fields' => array('DealsPayment.plans_payment_id', 'SUM(DealsPayment.emi) AS paid_total'),
            'conditions' => array('DealsPayment.deal_id' => $id, 'DealsPayment.plans_payment_id >' => 0),
            'group' => array('DealsPayment.plans_payment_id'),
            'recursive' => -1
        ));
        $paidMap = array();
        foreach ((array)$paidRows as $pr) {
            $pid = (int)$pr['DealsPayment']['plans_payment_id'];
            $paidMap[$pid] = isset($pr[0]['paid_total']) ? (float)$pr[0]['paid_total'] : 0;
        }

        $pendingInstallments = array();
        $totalPendingBalance = 0;
        $totalOverdueAmount = 0;
        $totalOverdueCount = 0;
        $totalUpcomingAmount = 0;
        $totalUpcomingCount = 0;
        $today = date('Y-m-d');

        foreach ($planRows as $row) {
            $pId = (int)$row['PlansPayment']['id'];
            $pName = !empty($row['PlansPayment']['name']) ? $row['PlansPayment']['name'] : 'Installment';
            $pAmt = isset($row['PlansPayment']['amount']) ? (float)$row['PlansPayment']['amount'] : 0;
            $pPaid = isset($paidMap[$pId]) ? (float)$paidMap[$pId] : 0;
            $pPending = max(0, round($pAmt - $pPaid, 2));
            $dueDateStr = !empty($row['PlansPayment']['date']) ? $row['PlansPayment']['date'] : null;

            if ($pPending <= 0) {
                continue;
            }

            $totalPendingBalance += $pPending;
            $isOverdue = false;
            $overdueDays = 0;
            $monthsAhead = 0;

            if (!empty($dueDateStr)) {
                $d1 = new DateTime($today);
                $d2 = new DateTime($dueDateStr);
                if ($d2 < $d1) {
                    $isOverdue = true;
                    $diff = $d2->diff($d1);
                    $overdueDays = (int)$diff->days;
                    $totalOverdueAmount += $pPending;
                    $totalOverdueCount++;
                } else {
                    $diff = $d1->diff($d2);
                    $monthsAhead = ($diff->y * 12) + $diff->m + ($diff->d > 10 ? 1 : 0);
                    $totalUpcomingAmount += $pPending;
                    $totalUpcomingCount++;
                }
            } else {
                $totalUpcomingAmount += $pPending;
                $totalUpcomingCount++;
            }

            $pendingInstallments[] = array(
                'id' => $pId,
                'name' => $pName,
                'total_amount' => $pAmt,
                'paid_amount' => $pPaid,
                'pending_amount' => $pPending,
                'due_date' => $dueDateStr,
                'due_date_formatted' => !empty($dueDateStr) ? date('d-M-Y', strtotime($dueDateStr)) : 'N/A',
                'is_overdue' => $isOverdue,
                'overdue_days' => $overdueDays,
                'months_ahead' => max(0, $monthsAhead)
            );
        }

        $payload = array(
            'status' => 'success',
            'deal' => array(
                'id' => $id,
                'invoice_no' => !empty($deal['Deal']['invoice_no']) ? $deal['Deal']['invoice_no'] : 'WV-'.$id,
                'property_name' => !empty($deal['Property']['name']) ? $deal['Property']['name'] : 'Property',
                'total_amount' => isset($deal['Deal']['total_amount']) ? (float)$deal['Deal']['total_amount'] : 0,
                'total_pending_balance' => round($totalPendingBalance, 2),
                'total_overdue_amount' => round($totalOverdueAmount, 2),
                'total_overdue_count' => $totalOverdueCount,
                'total_upcoming_amount' => round($totalUpcomingAmount, 2),
                'total_upcoming_count' => $totalUpcomingCount
            ),
            'settings' => array(
                'calculator_enabled' => !empty($settings['early_payment_calculator_enabled']) ? 1 : 0,
                'discount_mode' => !empty($settings['early_payment_discount_mode']) ? $settings['early_payment_discount_mode'] : 'percentage',
                'discount_percent' => isset($settings['early_payment_discount_percent']) ? (float)$settings['early_payment_discount_percent'] : 5.00,
                'annual_rate' => isset($settings['early_payment_annual_rate']) ? (float)$settings['early_payment_annual_rate'] : 8.00,
                'min_installments' => isset($settings['early_payment_min_installments']) ? (int)$settings['early_payment_min_installments'] : 3,
                'require_all_overdue' => isset($settings['require_all_overdue']) ? (int)$settings['require_all_overdue'] : 1,
                'allow_overdue_discount_package' => isset($settings['allow_overdue_discount_package']) ? (int)$settings['allow_overdue_discount_package'] : 1,
                'overdue_special_discount_percent' => isset($settings['overdue_special_discount_percent']) ? (float)$settings['overdue_special_discount_percent'] : 0.00,
                'terms_en' => !empty($settings['early_payment_terms_en']) ? $settings['early_payment_terms_en'] : '',
                'terms_ur' => !empty($settings['early_payment_terms_ur']) ? $settings['early_payment_terms_ur'] : ''
            ),
            'installments' => $pendingInstallments
        );

        $this->response->body(json_encode($payload));
        return $this->response;
    }

    protected function _escapeIcs($text)
    {
        $text = str_replace(array("\\", ";", ","), array("\\\\", "\\;", "\\,"), (string)$text);
        return $text;
    }
}
