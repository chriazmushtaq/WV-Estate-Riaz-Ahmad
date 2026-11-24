<?php
class HelpsController extends AdminAppController {
    public function index()
    {
        $this->loadModel('Page');
        $rootRows = $this->Page->find('all', array(
            'conditions' => array(
                'Page.published' => 'Yes',
                'Page.parent_id' => 0,
                'Page.page_name IS NOT NULL',
                'Page.page_name !=' => ''
            ),
            'order' => array('Page.ordering' => 'asc', 'Page.id' => 'asc'),
            'recursive' => -1
        ));
        $rootIds = array();
        foreach($rootRows as $row)
        {
            if(!empty($row['Page']['id']))
            {
                $rootIds[] = (int)$row['Page']['id'];
            }
        }
        $childrenByParent = array();
        if(!empty($rootIds))
        {
            $childRows = $this->Page->find('all', array(
                'conditions' => array(
                    'Page.published' => 'Yes',
                    'Page.parent_id' => $rootIds,
                    'Page.page_name IS NOT NULL',
                    'Page.page_name !=' => ''
                ),
                'order' => array('Page.parent_id' => 'asc', 'Page.ordering' => 'asc', 'Page.id' => 'asc'),
                'recursive' => -1
            ));
            foreach($childRows as $child)
            {
                $parentId = (int)$child['Page']['parent_id'];
                if(!isset($childrenByParent[$parentId]))
                {
                    $childrenByParent[$parentId] = array();
                }
                $childrenByParent[$parentId][] = $child;
            }
        }
        $this->set(compact('rootRows','childrenByParent'));
        $this->set('helpUpdatedOn', date('d M Y'));
    }    
}
