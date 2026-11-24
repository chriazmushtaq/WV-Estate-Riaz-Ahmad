<?php
App::uses('Router', 'Routing');

class PaymentplansController extends AppController
{
    public $uses = array('AgencyPaymentPlanPage', 'Agency');
    public $helpers = array('Html', 'Form', 'Session', 'Number');
    protected $_paymentPlanSchemaReady = true;

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_paymentPlanSchemaReady = $this->AgencyPaymentPlanPage->ensureSchema();
    }

    public function index($slug = null)
    {
        $slug = trim((string)$slug);
        if ($slug !== '') {
            return $this->view($slug);
        }
        if (!$this->_paymentPlanSchemaReady) {
            $this->set('pages', array());
            $this->set('contentId', 'apartments-detailes');
            $this->set('pageTitle', __('Apartment Payment Plans'));
            return;
        }

        $pages = $this->AgencyPaymentPlanPage->find('all', array(
            'fields' => array(
                'AgencyPaymentPlanPage.*',
                'Agency.id',
                'Agency.name',
                'Agency.mobile',
                'Agency.phone',
                'Agency.email'
            ),
            'conditions' => $this->_publicPaymentPlanConditions(),
            'order' => array('AgencyPaymentPlanPage.modified' => 'desc', 'AgencyPaymentPlanPage.id' => 'asc'),
            'recursive' => 0
        ));
        $pages = $this->_decoratePages($pages);
        $this->set('pages', $pages);
        $this->set('contentId', 'apartments-detailes');
        $this->set('pageTitle', __('Apartment Payment Plans'));
    }

    public function view($slug = null)
    {
        if (!$this->_paymentPlanSchemaReady) {
            throw new NotFoundException(__('Payment plan page was not found.'));
        }
        $slug = $this->AgencyPaymentPlanPage->slugify($slug);
        $page = $this->AgencyPaymentPlanPage->find('first', array(
            'fields' => array(
                'AgencyPaymentPlanPage.*',
                'Agency.id',
                'Agency.name',
                'Agency.mobile',
                'Agency.phone',
                'Agency.email'
            ),
            'conditions' => $this->_publicPaymentPlanConditions(array('AgencyPaymentPlanPage.slug' => $slug)),
            'recursive' => 0
        ));
        if (empty($page['AgencyPaymentPlanPage']['id'])) {
            throw new NotFoundException(__('Payment plan page was not found.'));
        }
        $page = $this->_decoratePages(array($page));
        $page = !empty($page[0]) ? $page[0] : array();
        $this->set('page', $page);
        $this->set('contentId', 'apartments-detailes');
        if (!empty($page['AgencyPaymentPlanPage']['meta_title'])) {
            $this->set('siteTitle', $page['AgencyPaymentPlanPage']['meta_title']);
        }
        if (!empty($page['AgencyPaymentPlanPage']['meta_desc'])) {
            $this->set('siteDescription', $page['AgencyPaymentPlanPage']['meta_desc']);
        }
    }

    protected function _decoratePages($pages)
    {
        foreach ((array)$pages as $key => $page) {
            $p = isset($page['AgencyPaymentPlanPage']) ? $page['AgencyPaymentPlanPage'] : array();
            $agency = isset($page['Agency']) ? $page['Agency'] : array();
            $pages[$key]['AgencyPaymentPlanPage']['amount_plans'] = $this->AgencyPaymentPlanPage->decodeJsonList(isset($p['amount_plans_json']) ? $p['amount_plans_json'] : '');
            $pages[$key]['AgencyPaymentPlanPage']['public_url'] = Router::url('/paymentplans/'.(isset($p['slug']) ? $p['slug'] : ''), true);
            $pages[$key]['Agency']['phone_display'] = $this->_firstNonEmpty(array(
                isset($agency['mobile']) ? $agency['mobile'] : '',
                isset($agency['phone']) ? $agency['phone'] : '',
                isset($this->contact) ? $this->contact : ''
            ));
            $pages[$key]['Agency']['email_display'] = $this->_firstNonEmpty(array(
                isset($agency['email']) ? $agency['email'] : '',
                isset($this->siteEmail) ? $this->siteEmail : ''
            ));
        }
        return $pages;
    }

    protected function _publicPaymentPlanConditions($extra = array())
    {
        $conditions = array(
            'AgencyPaymentPlanPage.enabled' => 1,
            "EXISTS (
                SELECT 1
                FROM users PublicAgencyUser
                INNER JOIN ugroups PublicAgencyGroup ON PublicAgencyGroup.id = PublicAgencyUser.ugroup_id
                WHERE PublicAgencyUser.project_id = AgencyPaymentPlanPage.project_id
                  AND PublicAgencyGroup.type = 'AGY'
                  AND PublicAgencyUser.status = 'Active'
                  AND COALESCE(PublicAgencyUser.front_view, 'On') != 'Off'
                LIMIT 1
            )"
        );
        return array_merge($conditions, (array)$extra);
    }

    protected function _firstNonEmpty($values)
    {
        foreach ((array)$values as $value) {
            $value = trim((string)$value);
            if ($value !== '') {
                return $value;
            }
        }
        return '';
    }
}
?>
