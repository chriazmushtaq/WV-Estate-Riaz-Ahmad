<?php
App::uses('AppModel', 'Model');

class SystemModule extends AppModel {
    public $name = 'SystemModule';
    public $useTable = 'system_modules';

    public $validate = array(
        'slug' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Module slug is required'
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'Module slug already exists'
            ),
            'alphanumeric' => array(
                'rule' => array('custom', '/^[a-z0-9_]+$/i'),
                'message' => 'Slug can only contain letters, numbers, and underscores'
            )
        ),
        'name' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Module name is required'
            )
        )
    );

    public function getActiveModules() {
        return $this->find('all', array(
            'conditions' => array('SystemModule.status' => 1),
            'order' => array('SystemModule.name' => 'ASC'),
            'recursive' => -1
        ));
    }
}
