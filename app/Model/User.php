<?php
class User extends AppModel
{
    public $belongsTo=array('Ugroup');
    public $actsAs = array('search-master.Searchable');
    public $filterArgs = array(
        'keyword' => array('type' => 'like', 'field' => array('User.name', 'User.username', 'User.email', 'User.mobile'))
    );
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
}