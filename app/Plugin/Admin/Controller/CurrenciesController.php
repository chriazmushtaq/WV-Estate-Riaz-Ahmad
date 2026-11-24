<?php
class CurrenciesController extends AdminAppController {
    public $helpers = array('Html', 'Form','Session','Paginator','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('name'=>'asc'));
    protected function _ensureCurrencySymbolColumn()
    {
        if($this->Currency->hasField('symbol'))
        {
            return true;
        }
        try
        {
            $this->Currency->query("ALTER TABLE `currencies` ADD `symbol` VARCHAR(20) NULL AFTER `short`");
            $this->Currency->schema(true);
        }
        catch(Exception $e)
        {
            $this->Currency->schema(true);
        }
        return $this->Currency->hasField('symbol');
    }
    public function index()
    {
        $this->set('hasCurrencySymbolColumn',$this->_ensureCurrencySymbolColumn());
        $this->Prg->commonProcess();
        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['conditions'] = $this->Currency->parseCriteria($this->Prg->parsedParams());
		$this->Paginator->settings['limit']=$this->pageLimit;
        $this->Paginator->settings['maxLimit']=$this->maxLimit;
        $this->set('Currency', $this->Paginator->paginate());  
		if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
    }    
    public function add()
    {
        $hasCurrencySymbolColumn = $this->_ensureCurrencySymbolColumn();
        $this->set('hasCurrencySymbolColumn',$hasCurrencySymbolColumn);
        if ($this->request->is('post'))
        {
            if ($this->request->is('post'))
            {
                $this->Currency->create();
                try
                {
                    if(!$hasCurrencySymbolColumn)
                    {
                        $this->Session->setFlash(__('Currency symbol column is missing. Please update currencies table.'),'flash',array('alert'=>'danger'));
                        return $this->redirect(array('action' => 'index'));
                    }
                    if(isset($this->request->data['Currency']['symbol']))
                    {
                        $this->request->data['Currency']['symbol']=trim($this->request->data['Currency']['symbol']);
                    }
                    if(isset($this->request->data['Currency']['photo']))
                    {
                        unset($this->request->data['Currency']['photo']);
                    }
                    if ($this->Currency->save($this->request->data))
                    {
                        $this->Session->setFlash(__('Your Currency has been saved.'),'flash',array('alert'=>'success'));
                        return $this->redirect(array('action' => 'add'));
                    }
                }
                catch (Exception $e)
                {
                    $this->Session->setFlash(__('Currency Name already exist.'),'flash',array('alert'=>'danger'));
                }
            }
        }
    }    
    public function deleteall()
    {
        if ($this->request->is('post'))
        {
            foreach($this->data['Currency']['id'] as $key => $value)
            {
                $this->Currency->delete($value);
            }
            $this->Session->setFlash(__('Your Currency has been deleted.'),'flash',array('alert'=>'success'));
        }        
        $this->redirect(array('action' => 'index'));
    }
}
