<?php
class AjaxcontentsController extends AppController
{
    public $helpers = array('Html', 'Form');
    public function agent()
    {
        $this->layout=null;
        $this->request->onlyAllow('ajax');
        $id = (int)$this->request->query('id');
        $ugroupId = (int)$this->request->query('ugroup_id');
        $selectId = trim((string)$this->request->query('select_id'));
        $fieldName = trim((string)$this->request->query('field_name'));
        $isMultiple = !empty($this->request->query('multiple')) ? 1 : 0;
        $selectedRaw = $this->request->query('selected');
        if($selectId === '')
        {
            $selectId = 'agentId';
        }
        if($fieldName === '')
        {
            $fieldName = 'user_id';
        }
        $selectedValues = array();
        if(is_array($selectedRaw))
        {
            foreach($selectedRaw as $selectedValue)
            {
                $selectedValue = (int)$selectedValue;
                if($selectedValue > 0)
                {
                    $selectedValues[$selectedValue] = $selectedValue;
                }
            }
        }
        else
        {
            $selectedRaw = trim((string)$selectedRaw);
            if($selectedRaw !== '')
            {
                foreach(explode(',', $selectedRaw) as $selectedPart)
                {
                    $selectedValue = (int)$selectedPart;
                    if($selectedValue > 0)
                    {
                        $selectedValues[$selectedValue] = $selectedValue;
                    }
                }
            }
        }
        $this->loadModel('User');
        $conditions = array($this->User->assignableScopeConditions($id,'User'));
        if($ugroupId > 0)
        {
            $conditions[] = array('User.ugroup_id' => $ugroupId);
        }
        $agentName=$this->User->find('list',array(
            'conditions'=>$conditions,
            'order'=>array('User.name'=>'asc')
        ));
        $selectedValues = array_values($selectedValues);
        $this->set(compact('agentName','selectId','fieldName','isMultiple','selectedValues'));
    }
    public function Vendor()
    {
        $this->layout=null;
        $this->request->onlyAllow('ajax');
        $id = $this->request->query('id');
        $this->loadModel('Vendor');
        $vendorName=$this->Vendor->find('list',array('conditions'=>array('project_id'=>$id),'order'=>array('Vendor.name'=>'asc')));
        $this->set(compact('vendorName'));
    }
}
