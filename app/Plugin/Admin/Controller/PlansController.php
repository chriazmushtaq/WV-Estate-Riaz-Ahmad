<?php
class PlansController extends AdminAppController {
    public $helpers = array('Html', 'Form','Session','Paginator','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('Plan.id'=>'desc'));
    protected function _prepareScheduleTemplates()
    {
        $this->Plan->ensureScheduleTemplateSchema();
        $this->loadModel('Admin.PlanScheduleRow');
    }

    protected function _scheduleTypeOptions()
    {
        return array(
            'token'=>__('Token'),
            'booking'=>__('Booking'),
            'confirmation'=>__('Confirmation'),
            'monthly'=>__('Monthly'),
            'quarterly'=>__('Quarterly'),
            'biannual'=>__('Bi-Annual'),
            'possession'=>__('Possession'),
            'final'=>__('Final Payment'),
            'custom'=>__('Custom Payment')
        );
    }

    protected function _defaultScheduleRows()
    {
        return array(
            array('payment_type'=>'token','title'=>'Token','amount_mode'=>'fixed','amount_value'=>'','repeat_count'=>1,'interval_months'=>0),
            array('payment_type'=>'booking','title'=>'Booking','amount_mode'=>'fixed','amount_value'=>'','repeat_count'=>1,'interval_months'=>0),
            array('payment_type'=>'confirmation','title'=>'Confirmation','amount_mode'=>'fixed','amount_value'=>'','repeat_count'=>1,'interval_months'=>1),
            array('payment_type'=>'monthly','title'=>'Monthly Installment','amount_mode'=>'fixed','amount_value'=>'','repeat_count'=>1,'interval_months'=>1),
            array('payment_type'=>'quarterly','title'=>'Quarterly Payment','amount_mode'=>'fixed','amount_value'=>'','repeat_count'=>1,'interval_months'=>3),
            array('payment_type'=>'biannual','title'=>'Bi-Annual Payment','amount_mode'=>'fixed','amount_value'=>'','repeat_count'=>1,'interval_months'=>6),
            array('payment_type'=>'possession','title'=>'Possession','amount_mode'=>'fixed','amount_value'=>'','repeat_count'=>1,'interval_months'=>1),
            array('payment_type'=>'final','title'=>'Final Payment','amount_mode'=>'fixed','amount_value'=>'','repeat_count'=>1,'interval_months'=>1)
        );
    }

    protected function _normalizeScheduleRows($rawRows, $planTotal)
    {
        $types = $this->_scheduleTypeOptions();
        $fixedIntervals = array('token'=>0,'booking'=>0,'confirmation'=>1,'monthly'=>1,'quarterly'=>3,'biannual'=>6,'possession'=>1,'final'=>1);
        $singleTypes = array('token','booking','confirmation','possession','final');
        $rows = array();
        $calculatedTotal = 0.0;
        $installmentCount = 0;
        foreach((array)$rawRows as $raw)
        {
            $type = isset($raw['payment_type']) ? strtolower(trim((string)$raw['payment_type'])) : 'custom';
            $title = isset($raw['title']) ? trim((string)$raw['title']) : '';
            $mode = isset($raw['amount_mode']) ? strtolower(trim((string)$raw['amount_mode'])) : 'fixed';
            $value = isset($raw['amount_value']) ? round((float)str_replace(',', '', $raw['amount_value']),4) : 0;
            $count = isset($raw['repeat_count']) ? max(1,(int)$raw['repeat_count']) : 1;
            $interval = isset($raw['interval_months']) ? max(0,(int)$raw['interval_months']) : 0;
            if(!isset($types[$type])) $type = 'custom';
            if($title === '' && isset($types[$type])) $title = $types[$type];
            if(!in_array($mode,array('fixed','percentage'),true)) $mode = 'fixed';
            if(in_array($type,$singleTypes,true)) $count = 1;
            if(isset($fixedIntervals[$type])) $interval = $fixedIntervals[$type];
            if($title === '' || $value <= 0)
            {
                continue;
            }
            if($mode === 'percentage' && $value > 100)
            {
                return array('ok'=>false,'message'=>__('A payment percentage cannot be greater than 100.'),'rows'=>array());
            }
            $unitAmount = $mode === 'percentage' ? round(($planTotal*$value)/100,2) : round($value,2);
            $calculatedTotal += ($unitAmount*$count);
            if(!in_array($type,array('token','booking'),true)) $installmentCount += $count;
            $rows[] = array(
                'payment_type'=>$type,
                'title'=>$title,
                'amount_mode'=>$mode,
                'amount_value'=>$value,
                'repeat_count'=>$count,
                'interval_months'=>$interval,
                'sort_order'=>count($rows)+1
            );
        }
        $calculatedTotal = round($calculatedTotal,2);
        if($planTotal <= 0)
        {
            return array('ok'=>false,'message'=>__('Total Payment must be greater than zero.'),'rows'=>$rows);
        }
        if(empty($rows))
        {
            return array('ok'=>false,'message'=>__('Add at least one valid payment schedule row.'),'rows'=>$rows);
        }
        if(abs($calculatedTotal-$planTotal)>0.01)
        {
            return array('ok'=>false,'message'=>sprintf(__('Schedule total is %s. It must equal Total Payment %s.'),CakeNumber::format($calculatedTotal),CakeNumber::format($planTotal)),'rows'=>$rows);
        }
        return array('ok'=>true,'message'=>'','rows'=>$rows,'installment_count'=>$installmentCount,'calculated_total'=>$calculatedTotal);
    }

    protected function _savePlanTemplate($planId, $rows)
    {
        $this->PlanScheduleRow->deleteAll(array('PlanScheduleRow.plan_id'=>(int)$planId),false);
        $saveRows = array();
        foreach($rows as $row)
        {
            $row['plan_id'] = (int)$planId;
            $saveRows[] = array('PlanScheduleRow'=>$row);
        }
        // add() and edit() already wrap the plan and its schedule rows in one
        // transaction. A nested atomic save commits that outer transaction on
        // this CakePHP/PDO stack and leaves the final commit with no active
        // transaction.
        $results = $this->PlanScheduleRow->saveAll($saveRows,array('atomic'=>false,'validate'=>false));
        return is_array($results) && !in_array(false,$results,true);
    }

    public function index()
    {
        $this->_prepareScheduleTemplates();
        $this->Prg->commonProcess();
        $this->Paginator->settings = $this->paginate;

        // Keep legacy plans available to their existing deals, while the plan
        // manager lists only the nine newest templates for future use.
        $latestPlans = $this->Plan->find('all',array(
            'fields'=>array('Plan.id'),
            'order'=>array('Plan.id'=>'DESC'),
            'limit'=>9,
            'recursive'=>-1
        ));
        $latestPlanIds = Hash::extract($latestPlans,'{n}.Plan.id');
        $conditions = $this->Plan->parseCriteria($this->Prg->parsedParams());
        $conditions['Plan.id'] = !empty($latestPlanIds) ? $latestPlanIds : array(0);
        $this->Paginator->settings['conditions'] = $conditions;
		$this->Paginator->settings['limit']=$this->pageLimit;
        $this->Paginator->settings['maxLimit']=$this->maxLimit;
        $plans = $this->Paginator->paginate();
        $planIds = array();
        foreach($plans as $planRow) $planIds[] = (int)$planRow['Plan']['id'];
        $scheduleCountMap = array();
        if(!empty($planIds))
        {
            $counts = $this->PlanScheduleRow->find('all',array(
                'fields'=>array('PlanScheduleRow.plan_id','SUM(PlanScheduleRow.repeat_count) AS payment_count'),
                'conditions'=>array('PlanScheduleRow.plan_id'=>$planIds),
                'group'=>array('PlanScheduleRow.plan_id'),
                'recursive'=>-1
            ));
            foreach($counts as $countRow)
            {
                $scheduleCountMap[(int)$countRow['PlanScheduleRow']['plan_id']] = (int)$countRow[0]['payment_count'];
            }
        }
        $this->set('scheduleCountMap',$scheduleCountMap);
        $this->set('Plan', $plans);  
		 if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
    }
    public function propertysearch()
    {
        $this->autoRender = false;
        // get the search term from URL
        $this->loadModel('Property');
        $term = $this->request->query['q'];
        $cond=array();$cond1=array();
        if(isset($this->request->query['q1']))
        {
            $type=$this->request->query['q1'];
            $cond=array('Property.type' => $type);
        }
        if(!$this->_isSuperAdminSession() && (int)$this->agencyId > 0)
        {
            $cond1=array('Property.project_id' => (int)$this->agencyId);
        }
        elseif(isset($this->request->query['q2']) && $this->request->query['q2'])
        {
            $cond1=array('Property.project_id' => $this->request->query['q2']);
        }
        $users = $this->Property->find('all',array('conditions' => array('Property.name LIKE' => '%'.$term.'%','Property.status'=>'Availiable',$cond,$cond1)));
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
    public function add()
    {
        $this->_prepareScheduleTemplates();
        $this->set('scheduleTypeOptions',$this->_scheduleTypeOptions());
        $this->set('scheduleRows',$this->_defaultScheduleRows());
        $this->set('formMode','add');
        if ($this->request->is('post'))
        {
            $this->Plan->create();
            $dataSource = $this->Plan->getDataSource();
            try
            {
                $planTotal = isset($this->request->data['Plan']['total_amount']) ? round((float)str_replace(',','',$this->request->data['Plan']['total_amount']),2) : 0;
                $normalized = $this->_normalizeScheduleRows(isset($this->request->data['PlanScheduleRow']) ? $this->request->data['PlanScheduleRow'] : array(),$planTotal);
                if(empty($normalized['ok'])) throw new Exception($normalized['message']);
                $dataSource->begin();
                $this->request->data['Plan']['total_amount'] = $planTotal;
                $this->request->data['Plan']['installment'] = (int)$normalized['installment_count'];
                $this->request->data['Plan']['status'] = !empty($this->request->data['Plan']['status']) ? $this->request->data['Plan']['status'] : 'Active';
                if (!$this->Plan->save($this->request->data) || !$this->_savePlanTemplate($this->Plan->id,$normalized['rows']))
                {
                    throw new Exception(__('Unable to save the payment-plan template.'));
                }
                $dataSource->commit();
                $this->Session->setFlash(__('Payment plan has been saved.'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action' => 'index'));
            }
            catch (Exception $e)
            {
                if(isset($dataSource)) { try{$dataSource->rollback();}catch(Exception $ignore){} }
                $this->set('scheduleRows',isset($normalized['rows']) && !empty($normalized['rows']) ? $normalized['rows'] : (isset($this->request->data['PlanScheduleRow']) ? $this->request->data['PlanScheduleRow'] : $this->_defaultScheduleRows()));
                $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            }
        }
    }
    public function edit($id = null)
    {
        $this->_prepareScheduleTemplates();
        $id = (int)$id;
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $post=$this->Plan->findByid($id);
        if (!$post)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $scheduleRows = $this->PlanScheduleRow->find('all',array('conditions'=>array('PlanScheduleRow.plan_id'=>$id),'order'=>array('PlanScheduleRow.sort_order'=>'ASC','PlanScheduleRow.id'=>'ASC'),'recursive'=>-1));
        $scheduleRows = Hash::extract($scheduleRows,'{n}.PlanScheduleRow');
        if(empty($scheduleRows)) $scheduleRows = $this->_defaultScheduleRows();
        $this->set('scheduleTypeOptions',$this->_scheduleTypeOptions());
        $this->set('scheduleRows',$scheduleRows);
        $this->set('formMode','edit');
        $this->set('planId',$id);
        if ($this->request->is(array('post', 'put')))
        {
            $dataSource = $this->Plan->getDataSource();
            try
            {
                $planTotal = isset($this->request->data['Plan']['total_amount']) ? round((float)str_replace(',','',$this->request->data['Plan']['total_amount']),2) : 0;
                $normalized = $this->_normalizeScheduleRows(isset($this->request->data['PlanScheduleRow']) ? $this->request->data['PlanScheduleRow'] : array(),$planTotal);
                if(empty($normalized['ok'])) throw new Exception($normalized['message']);
                $dataSource->begin();
                $this->Plan->id = $id;
                $this->request->data['Plan']['id'] = $id;
                $this->request->data['Plan']['total_amount'] = $planTotal;
                $this->request->data['Plan']['installment'] = (int)$normalized['installment_count'];
                if (!$this->Plan->save($this->request->data) || !$this->_savePlanTemplate($id,$normalized['rows']))
                {
                    throw new Exception(__('Unable to update the payment-plan template.'));
                }
                $dataSource->commit();
                $this->Session->setFlash(__('Payment plan has been updated.'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action' => 'index'));
            }
            catch (Exception $e)
            {
                if(isset($dataSource)) { try{$dataSource->rollback();}catch(Exception $ignore){} }
                $this->set('scheduleRows',isset($normalized['rows']) && !empty($normalized['rows']) ? $normalized['rows'] : (isset($this->request->data['PlanScheduleRow']) ? $this->request->data['PlanScheduleRow'] : $scheduleRows));
                $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            }
        }
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }
    }    

    public function view($id = null)
    {
        $this->_prepareScheduleTemplates();
        $id = (int)$id;
        if($id <= 0)
        {
            throw new NotFoundException(__('Invalid payment plan.'));
        }

        $plan = $this->Plan->find('first',array(
            'conditions'=>array('Plan.id'=>$id),
            'recursive'=>-1
        ));
        if(empty($plan))
        {
            throw new NotFoundException(__('Payment plan was not found.'));
        }

        $scheduleRows = $this->PlanScheduleRow->find('all',array(
            'conditions'=>array('PlanScheduleRow.plan_id'=>$id),
            'order'=>array('PlanScheduleRow.sort_order'=>'ASC','PlanScheduleRow.id'=>'ASC'),
            'recursive'=>-1
        ));
        $scheduleRows = Hash::extract($scheduleRows,'{n}.PlanScheduleRow');
        $planTotal = round((float)$plan['Plan']['total_amount'],2);
        $scheduleTotal = 0.0;
        $paymentCount = 0;
        foreach($scheduleRows as $key=>$row)
        {
            $count = max(1,(int)$row['repeat_count']);
            $value = (float)$row['amount_value'];
            $unitAmount = $row['amount_mode']==='percentage' ? round(($planTotal*$value)/100,2) : round($value,2);
            $rowTotal = round($unitAmount*$count,2);
            $scheduleRows[$key]['unit_amount'] = $unitAmount;
            $scheduleRows[$key]['row_total'] = $rowTotal;
            $scheduleTotal += $rowTotal;
            $paymentCount += $count;
        }

        $this->set(compact('plan','scheduleRows','planTotal','scheduleTotal','paymentCount'));
    }

    public function deleteall()
    {
        $this->_prepareScheduleTemplates();
        try
        {
            if ($this->request->is('post'))
            {
                foreach($this->data['Plan']['id'] as $key => $value)
                {
                    if($this->Plan->delete($value))
                    {
                        $this->PlanScheduleRow->deleteAll(array('PlanScheduleRow.plan_id'=>(int)$value),false);
                    }
                }
                $this->Session->setFlash(__('Plan has been deleted.'),'flash',array('alert'=>'danger'));
            }        
            $this->redirect(array('action' => 'index'));
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Please delete related record first.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
    }    
}
