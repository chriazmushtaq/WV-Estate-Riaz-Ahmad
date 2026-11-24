<?php
class User extends AppModel {
    public $validationDomain = 'validation';
    public $belongsTo=array('Ugroup','Location');
    public $actsAs = array('search-master.Searchable','Upload.Upload' => array(
            'photo' => array('pathMethod'=>'flat','thumbnailSizes' => array('' => '150x150',),'thumbnailMethod' => 'php','thumbnailPrefixStyle' => false,'thumbnailType'=>true),));
    public $filterArgs = array('keyword' => array('type' => 'like','field'=>'User.name'));
    public $validate = array(
                             'name' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' => true,'allowEmpty' => false,'message' => 'Only letters and numbers allowed')),
                           'username' => array('isUnique'=>array('rule' => 'isUnique','message' => 'This Username has already been taken.'),
                                            'alphaNumeric'=>array('rule'=>'alphaNumericCustom','message'=>'Only letters and numbers allowed')),
                           'address' => array('alphaNumeric'=>array('rule' => 'alphaNumericCustom','message' => 'Address Required.')),
                           'phone' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' => false,'allowEmpty' => true,'message' => 'Only numbers allowed')),
                           'mobile' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' => false,'allowEmpty' => true,'message' => 'Only numbers allowed')),
                           'email' => array('rule' => 'email','message' => 'Enter a valid email','required' => true,'allowEmpty' => true),
                           'skype' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' =>false,'allowEmpty' => true,'message' => 'Only correct Skype ID is allowed.')),
                           'twitter' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' =>false,'allowEmpty' => true,'message' => 'Only correct Twitter ID is allowed.')),
                           'facebook' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' =>false,'allowEmpty' => true,'message' => 'Only correct Facebook ID is allowed.')),
                           'linkedin' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' =>false,'allowEmpty' => true,'message' => 'Only correct Linkedin ID is allowed.')),
                           'description' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' => false,'allowEmpty' => true,'message' => 'Only correct Skype ID is allowed.')),
                           'photo' => array('isValidExtension' =>array('rule' => array('isValidExtension', array('jpg', 'jpeg', 'png','gif'),false),'allowEmpty'=>true,'message' => 'File does not have a valid extension'),
                                           'isValidMimeType' => array('rule' => array('isValidMimeType', array('image/jpeg','image/png','image/bmp','image/gif'),false),'allowEmpty'=>true,'message' => 'You must supply a JPG, GIF  or PNG File.')),
                            );
    public function assingPages($id)
    {
        $Page=ClassRegistry::init('Page');
        $rows = $Page->find('all',array(
            'joins'=>array(array(
                'table'=>'page_rights',
                'alias'=>'PageRight',
                'type'=>'Left',
                'conditions'=>array('Page.id=PageRight.page_id',"PageRight.ugroup_id=$id")
            )),
            'fields'=>array('Page.*','PageRight.*'),
            'order'=>array('Page.model_name'=>'asc','Page.parent_id'=>'asc','Page.ordering'=>'asc','Page.id'=>'asc'),
            'conditions'=>array(
                'Page.page_name NOT'=>NULL,
                'OR'=>array(
                    'Page.published'=>'Yes',
                    'LOWER(Page.controller_name)'=>'ardaccounts'
                )
            )
        ));
        $adminTailCategories = array('configuration','configurations');
        $adminRelatedCategories = array('user','users','contents','content','trash','help','administrator','admin','system');
        usort($rows, function($leftRow, $rightRow) use ($adminTailCategories, $adminRelatedCategories){
            $leftModel = strtolower(trim((string)(isset($leftRow['Page']['model_name']) ? $leftRow['Page']['model_name'] : '')));
            $rightModel = strtolower(trim((string)(isset($rightRow['Page']['model_name']) ? $rightRow['Page']['model_name'] : '')));
            $leftRank = in_array($leftModel, $adminTailCategories, true) ? 3 : (in_array($leftModel, $adminRelatedCategories, true) ? 2 : 1);
            $rightRank = in_array($rightModel, $adminTailCategories, true) ? 3 : (in_array($rightModel, $adminRelatedCategories, true) ? 2 : 1);
            if($leftRank !== $rightRank)
            {
                return ($leftRank < $rightRank) ? -1 : 1;
            }
            if($leftModel !== $rightModel)
            {
                return strcmp($leftModel, $rightModel);
            }
            $leftParent = isset($leftRow['Page']['parent_id']) ? (int)$leftRow['Page']['parent_id'] : 0;
            $rightParent = isset($rightRow['Page']['parent_id']) ? (int)$rightRow['Page']['parent_id'] : 0;
            if($leftParent !== $rightParent)
            {
                return ($leftParent < $rightParent) ? -1 : 1;
            }
            $leftOrdering = isset($leftRow['Page']['ordering']) ? (int)$leftRow['Page']['ordering'] : 0;
            $rightOrdering = isset($rightRow['Page']['ordering']) ? (int)$rightRow['Page']['ordering'] : 0;
            if($leftOrdering !== $rightOrdering)
            {
                return ($leftOrdering < $rightOrdering) ? -1 : 1;
            }
            $leftId = isset($leftRow['Page']['id']) ? (int)$leftRow['Page']['id'] : 0;
            $rightId = isset($rightRow['Page']['id']) ? (int)$rightRow['Page']['id'] : 0;
            if($leftId === $rightId)
            {
                return 0;
            }
            return ($leftId < $rightId) ? -1 : 1;
        });
        return $rows;
    }
    public function ensureUserProjectsTable()
    {
        static $isEnsured = null;
        if($isEnsured !== null)
        {
            return $isEnsured;
        }
        try
        {
            $this->query(
                "CREATE TABLE IF NOT EXISTS `user_projects` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL,
                    `project_id` int(11) NOT NULL,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `user_project_unique` (`user_id`,`project_id`),
                    KEY `project_id` (`project_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );
            $isEnsured = true;
        }
        catch(Exception $e)
        {
            $isEnsured = false;
        }
        return $isEnsured;
    }
    public function normalizeProjectIdList($rawProjectIds, $fallbackProjectId = 0)
    {
        $projectIds = array();
        $queue = is_array($rawProjectIds) ? $rawProjectIds : array($rawProjectIds);
        foreach($queue as $rawValue)
        {
            if(is_array($rawValue))
            {
                foreach($this->normalizeProjectIdList($rawValue, 0) as $nestedId)
                {
                    $projectIds[(int)$nestedId] = (int)$nestedId;
                }
                continue;
            }
            foreach(explode(',', (string)$rawValue) as $candidate)
            {
                $projectId = (int)trim((string)$candidate);
                if($projectId > 0)
                {
                    $projectIds[$projectId] = $projectId;
                }
            }
        }
        $fallbackProjectId = (int)$fallbackProjectId;
        if(empty($projectIds) && $fallbackProjectId > 0)
        {
            $projectIds[$fallbackProjectId] = $fallbackProjectId;
        }
        return array_values($projectIds);
    }
    public function assignedProjectIds($userId, $includePrimary = true)
    {
        $userId = (int)$userId;
        $projectIds = array();
        if($userId <= 0)
        {
            return $projectIds;
        }
        if($includePrimary)
        {
            $primaryProjectId = (int)$this->field('project_id', array($this->alias.'.id' => $userId));
            if($primaryProjectId > 0)
            {
                $projectIds[$primaryProjectId] = $primaryProjectId;
            }
        }
        if($this->ensureUserProjectsTable())
        {
            try
            {
                $rows = (array)$this->query("SELECT `project_id` FROM `user_projects` WHERE `user_id`=".(int)$userId." ORDER BY `project_id` ASC");
                foreach($rows as $row)
                {
                    $projectId = 0;
                    if(isset($row['user_projects']['project_id']))
                    {
                        $projectId = (int)$row['user_projects']['project_id'];
                    }
                    elseif(isset($row[0]['project_id']))
                    {
                        $projectId = (int)$row[0]['project_id'];
                    }
                    if($projectId > 0)
                    {
                        $projectIds[$projectId] = $projectId;
                    }
                }
            }
            catch(Exception $e)
            {
            }
        }
        return array_values($projectIds);
    }
    public function clearProjectAssignments($userId)
    {
        $userId = (int)$userId;
        if($userId <= 0 || !$this->ensureUserProjectsTable())
        {
            return false;
        }
        try
        {
            $this->query("DELETE FROM `user_projects` WHERE `user_id`=".(int)$userId);
            return true;
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    public function syncProjectAssignments($userId, $projectIds, $primaryProjectId = 0)
    {
        $userId = (int)$userId;
        $primaryProjectId = (int)$primaryProjectId;
        if($userId <= 0)
        {
            return array();
        }
        $projectIds = $this->normalizeProjectIdList($projectIds, $primaryProjectId);
        $this->clearProjectAssignments($userId);
        if(!$this->ensureUserProjectsTable())
        {
            return $projectIds;
        }
        foreach($projectIds as $projectId)
        {
            $projectId = (int)$projectId;
            if($projectId <= 0 || $projectId === $primaryProjectId)
            {
                continue;
            }
            try
            {
                $this->query(
                    "INSERT INTO `user_projects` (`user_id`,`project_id`,`created`,`modified`) VALUES (".
                    (int)$userId.",".(int)$projectId.",NOW(),NOW())"
                );
            }
            catch(Exception $e)
            {
            }
        }
        return $projectIds;
    }
    public function projectMembershipCondition($projectId, $alias = null)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return '';
        }
        $alias = trim((string)$alias);
        if($alias === '')
        {
            $alias = $this->alias;
        }
        $alias = preg_replace('/[^A-Za-z0-9_]/', '', $alias);
        if($alias === '')
        {
            $alias = $this->alias;
        }
        if(!$this->ensureUserProjectsTable())
        {
            return '(' . $alias . '.project_id=' . $projectId . ')';
        }
        return '((' . $alias . '.project_id=' . $projectId . ') OR EXISTS (SELECT 1 FROM `user_projects` `UserProjectMembership` WHERE `UserProjectMembership`.`user_id`=' . $alias . '.id AND `UserProjectMembership`.`project_id`=' . $projectId . '))';
    }
    public function userHasProjectAccess($userId, $projectId)
    {
        $userId = (int)$userId;
        $projectId = (int)$projectId;
        if($userId <= 0 || $projectId <= 0)
        {
            return false;
        }
        $primaryProjectId = (int)$this->field('project_id', array($this->alias.'.id' => $userId));
        if($primaryProjectId === $projectId)
        {
            return true;
        }
        if(!$this->ensureUserProjectsTable())
        {
            return false;
        }
        try
        {
            $rows = (array)$this->query("SELECT COUNT(*) AS `cnt` FROM `user_projects` WHERE `user_id`=".(int)$userId." AND `project_id`=".(int)$projectId);
            if(!empty($rows[0][0]['cnt']) && (int)$rows[0][0]['cnt'] > 0)
            {
                return true;
            }
        }
        catch(Exception $e)
        {
        }
        return false;
    }
    public function assignedProjectNameMap($userIds)
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', (array)$userIds))));
        $nameMap = array();
        if(empty($userIds))
        {
            return $nameMap;
        }
        $projectMapByUser = array();
        $rows = (array)$this->find('all', array(
            'fields' => array($this->alias.'.id', $this->alias.'.project_id'),
            'conditions' => array($this->alias.'.id' => $userIds),
            'recursive' => -1
        ));
        foreach($rows as $row)
        {
            $userId = !empty($row[$this->alias]['id']) ? (int)$row[$this->alias]['id'] : 0;
            $projectId = !empty($row[$this->alias]['project_id']) ? (int)$row[$this->alias]['project_id'] : 0;
            if($userId <= 0)
            {
                continue;
            }
            $projectMapByUser[$userId] = array();
            if($projectId > 0)
            {
                $projectMapByUser[$userId][$projectId] = $projectId;
            }
        }
        if($this->ensureUserProjectsTable())
        {
            try
            {
                $assignmentRows = (array)$this->query(
                    "SELECT `user_id`,`project_id` FROM `user_projects` WHERE `user_id` IN (".implode(',', $userIds).") ORDER BY `project_id` ASC"
                );
                foreach($assignmentRows as $assignmentRow)
                {
                    $userId = 0;
                    $projectId = 0;
                    if(isset($assignmentRow['user_projects']['user_id']))
                    {
                        $userId = (int)$assignmentRow['user_projects']['user_id'];
                    }
                    elseif(isset($assignmentRow[0]['user_id']))
                    {
                        $userId = (int)$assignmentRow[0]['user_id'];
                    }
                    if(isset($assignmentRow['user_projects']['project_id']))
                    {
                        $projectId = (int)$assignmentRow['user_projects']['project_id'];
                    }
                    elseif(isset($assignmentRow[0]['project_id']))
                    {
                        $projectId = (int)$assignmentRow[0]['project_id'];
                    }
                    if($userId > 0 && $projectId > 0)
                    {
                        if(!isset($projectMapByUser[$userId]))
                        {
                            $projectMapByUser[$userId] = array();
                        }
                        $projectMapByUser[$userId][$projectId] = $projectId;
                    }
                }
            }
            catch(Exception $e)
            {
            }
        }
        $projectIds = array();
        foreach($projectMapByUser as $projectMap)
        {
            foreach((array)$projectMap as $projectId)
            {
                $projectId = (int)$projectId;
                if($projectId > 0)
                {
                    $projectIds[$projectId] = $projectId;
                }
            }
        }
        $projectNameLookup = array();
        if(!empty($projectIds))
        {
            $Project = ClassRegistry::init('Project');
            $projectNameLookup = (array)$Project->find('list', array(
                'fields' => array('Project.id', 'Project.name'),
                'conditions' => array('Project.id' => array_values($projectIds))
            ));
        }
        foreach($projectMapByUser as $userId => $projectMap)
        {
            $names = array();
            foreach((array)$projectMap as $projectId)
            {
                $projectId = (int)$projectId;
                if($projectId > 0 && !empty($projectNameLookup[$projectId]))
                {
                    $names[$projectId] = $projectNameLookup[$projectId];
                }
            }
            $nameMap[(int)$userId] = implode(', ', $names);
        }
        return $nameMap;
    }
    public function assignableGroupIds($refresh = false)
    {
        static $cachedGroupIds = null;
        if($refresh || $cachedGroupIds === null)
        {
            $groupIds = array();
            try
            {
                $Ugroup = ClassRegistry::init('Ugroup');
                $groupRows = (array)$Ugroup->find('all', array(
                    'fields' => array('Ugroup.id', 'Ugroup.type'),
                    'recursive' => -1
                ));
                foreach($groupRows as $groupRow)
                {
                    $groupId = !empty($groupRow['Ugroup']['id']) ? (int)$groupRow['Ugroup']['id'] : 0;
                    $groupType = !empty($groupRow['Ugroup']['type']) ? strtoupper(trim((string)$groupRow['Ugroup']['type'])) : '';
                    if($groupId <= 0 || in_array($groupType, array('ADR','AGY'), true))
                    {
                        continue;
                    }
                    $groupIds[$groupId] = $groupId;
                }
            }
            catch(Exception $e)
            {
                $groupIds = array();
            }
            if(empty($groupIds))
            {
                // Preserve legacy behavior if the role lookup fails.
                $groupIds = array(2 => 2);
            }
            $cachedGroupIds = array_values($groupIds);
        }
        return (array)$cachedGroupIds;
    }
    public function assignableScopeConditions($projectId = 0, $alias = null)
    {
        $alias = trim((string)$alias);
        if($alias === '')
        {
            $alias = $this->alias;
        }
        $conditions = array(
            $alias.'.deleted IS NULL',
            $alias.'.status' => 'Active',
            $alias.'.ugroup_id' => $this->assignableGroupIds(),
            'OR' => array(
                $alias.'.login_status IS NULL',
                $alias.'.login_status !=' => 'Blocked'
            )
        );
        $projectId = (int)$projectId;
        if($projectId > 0)
        {
            $conditions[] = $this->projectMembershipCondition($projectId, $alias);
        }
        return $conditions;
    }
    public function beforeSave($options = array())
    {
        if (!empty($this->data['User']['password'])) {
        $this->data['User']['password'] = $this->passwordHasher($this->data['User']['password']);
        }
        else
        {
          unset($this->data['User']['password']);  
        }
        return true;
    }
}
?>
