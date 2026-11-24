<?php
class LeadduplicatesController extends AdminAppController
{
    public $helpers = array('Html','Form','Session','Paginator');
    public $components = array('Session','Paginator','CustomFunction');
    public $uses = array('Admin.Lead');

    public function index()
    {
        $mobile = isset($this->request->query['mobile']) ? trim((string)$this->request->query['mobile']) : '';
        $email = isset($this->request->query['email']) ? strtolower(trim((string)$this->request->query['email'])) : '';
        $keyword = isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : '';
        $rows = array();
        if($mobile !== '' || $email !== '' || $keyword !== '')
        {
            $conditions = array();
            $or = array();
            if($mobile !== '')
            {
                $cleanMobile = preg_replace('/\s+/', '', $mobile);
                $or[] = array('Lead.lead_mobile' => $cleanMobile);
                $or[] = array('Client.mobile' => $cleanMobile);
                $or[] = array('Client.phone' => $cleanMobile);
            }
            if($email !== '')
            {
                $or[] = array('LOWER(Lead.lead_email)' => $email);
                $or[] = array('LOWER(Client.email)' => $email);
            }
            if($keyword !== '')
            {
                $or[] = array('Lead.lead_name LIKE' => '%'.$keyword.'%');
                $or[] = array('Client.name LIKE' => '%'.$keyword.'%');
                $or[] = array('Deal.invoice_no LIKE' => '%'.$keyword.'%');
                $or[] = array('Property.name LIKE' => '%'.$keyword.'%');
            }
            if(!empty($or))
            {
                $conditions[] = array('OR' => $or);
            }
            $permission = $this->CustomFunction->userWisePermission(null, $this->luserId, $this->userType, $this->agencyId, 'Lead.project_id', 'Lead.user_id');
            if(!empty($permission))
            {
                $conditions[] = $permission;
            }
            $this->Paginator->settings = array(
                'Lead' => array(
                    'fields' => array(
                        'Lead.id','Lead.lead_name','Lead.lead_mobile','Lead.lead_email','Lead.follow_up','Lead.created','Lead.remarks',
                        'Client.name','Client.mobile','Client.phone','Client.email',
                        'Project.name','AssignedUser.name','Status.name','Property.name','Deal.invoice_no'
                    ),
                    'joins' => array(
                        array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Client.id=Lead.client_id')),
                        array('table'=>'projects','alias'=>'Project','type'=>'LEFT','conditions'=>array('Project.id=Lead.project_id')),
                        array('table'=>'users','alias'=>'AssignedUser','type'=>'LEFT','conditions'=>array('AssignedUser.id=Lead.user_id')),
                        array('table'=>'statuses','alias'=>'Status','type'=>'LEFT','conditions'=>array('Status.id=Lead.status_id')),
                        array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Property.id=Lead.property_id')),
                        array('table'=>'deals','alias'=>'Deal','type'=>'LEFT','conditions'=>array('Deal.client_id=Lead.client_id'))
                    ),
                    'conditions' => $conditions,
                    'order' => array('Lead.id' => 'desc'),
                    'limit' => $this->pageLimit,
                    'maxLimit' => $this->maxLimit,
                    'recursive' => -1
                )
            );
            try
            {
                $rows = $this->Paginator->paginate('Lead');
            }
            catch(Exception $e)
            {
                $rows = array();
                $this->Session->setFlash(__('Unable to search duplicate leads.'), 'flash', array('alert'=>'danger'));
            }
        }
        $this->set(compact('rows','mobile','email','keyword'));
    }
}
?>
