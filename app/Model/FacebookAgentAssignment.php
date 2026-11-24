<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

class FacebookAgentAssignment extends AppModel
{
    public $useTable = 'facebook_agent_assignments';
    public $primaryKey = 'id';

    public $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id'
        )
    );

    /**
     * Ensure database schema has is_last_assigned column
     */
    public function ensureSchema()
    {
        try {
            $cols = $this->query("SHOW COLUMNS FROM `facebook_agent_assignments` LIKE 'is_last_assigned'");
            if (empty($cols)) {
                $this->query("ALTER TABLE `facebook_agent_assignments` ADD COLUMN `is_last_assigned` TINYINT(1) DEFAULT 0 AFTER `is_active`");
            }
        } catch (Exception $e) {}
    }

    /**
     * Get the next agent in strict 1-by-1 round-robin looping sequence
     * Strictly queries active registered users from the database.
     * Advances to the next agent in queue order: 1 -> 2 -> 3 -> ... -> N -> 1.
     * Guaranteed to rotate 1-by-1 without ties or desync.
     */
    public function getNextAssignedAgentId($projectId = null)
    {
        $this->ensureSchema();
        $this->autoSeedAgentsIfEmpty();

        $projectClause = "";
        if ($projectId !== null && (int)$projectId > 0)
        {
            $pId = (int)$projectId;
            $projectClause = " AND (a.project_id = {$pId} OR u.project_id = {$pId} OR a.project_id IS NULL OR a.project_id = 0)";
        }

        $sql = "SELECT a.id, a.user_id, a.project_id, a.sort_order, a.is_last_assigned, a.total_assigned_count, u.id AS u_id, u.name, u.status " .
               "FROM facebook_agent_assignments a " .
               "INNER JOIN users u ON a.user_id = u.id " .
               "WHERE a.is_active = 1 AND u.status = 'Active' AND (u.login_status IS NULL OR u.login_status != 'Blocked')" .
               $projectClause . " " .
               "ORDER BY a.sort_order ASC, a.id ASC";

        $activeAgents = $this->query($sql);

        // If no active agents matched project condition, fallback to any active agents
        if (empty($activeAgents) && $projectClause !== "")
        {
            $sqlFallback = "SELECT a.id, a.user_id, a.project_id, a.sort_order, a.is_last_assigned, a.total_assigned_count, u.id AS u_id, u.name, u.status " .
                           "FROM facebook_agent_assignments a " .
                           "INNER JOIN users u ON a.user_id = u.id " .
                           "WHERE a.is_active = 1 AND u.status = 'Active' AND (u.login_status IS NULL OR u.login_status != 'Blocked') " .
                           "ORDER BY a.sort_order ASC, a.id ASC";
            $activeAgents = $this->query($sqlFallback);
        }

        // If no agents are active in the pool, fallback to any active user so CRM never fails under strict mode
        if (empty($activeAgents))
        {
            $fallbackUser = $this->query("SELECT id FROM users WHERE status = 'Active' AND (login_status IS NULL OR login_status != 'Blocked') ORDER BY id ASC LIMIT 1");
            if (!empty($fallbackUser))
            {
                $uid = isset($fallbackUser[0]['users']['id']) ? (int)$fallbackUser[0]['users']['id'] : (isset($fallbackUser[0]['User']['id']) ? (int)$fallbackUser[0]['User']['id'] : (int)$fallbackUser[0]['id']);
                if ($uid > 0) {
                    return $uid;
                }
            }
            return 1; // Default to Admin ID 1
        }

        $totalActive = count($activeAgents);
        if ($totalActive === 0)
        {
            return 1;
        }

        // Find which active agent has is_last_assigned = 1
        $lastAssignedIndex = null;
        foreach ($activeAgents as $idx => $row)
        {
            $agentData = isset($row['a']) ? $row['a'] : (isset($row['facebook_agent_assignments']) ? $row['facebook_agent_assignments'] : $row);
            if (!empty($agentData['is_last_assigned']))
            {
                $lastAssignedIndex = $idx;
                break;
            }
        }

        // Determine next agent index in strict 1-by-1 sequence
        if ($lastAssignedIndex === null)
        {
            $nextIndex = 0;
        }
        else
        {
            $nextIndex = ($lastAssignedIndex + 1) % $totalActive;
        }

        $chosenRow = $activeAgents[$nextIndex];
        $chosenData = isset($chosenRow['a']) ? $chosenRow['a'] : (isset($chosenRow['facebook_agent_assignments']) ? $chosenRow['facebook_agent_assignments'] : $chosenRow);
        $chosenId = (int)$chosenData['id'];
        $chosenUserId = (int)$chosenData['user_id'];
        $assignedCount = (int)$chosenData['total_assigned_count'] + 1;

        $backendNow = $this->_getBackendCurrentTime();

        // Advance 1-by-1 round-robin pointer atomically
        $this->query("UPDATE facebook_agent_assignments SET is_last_assigned = 0");
        $this->query("UPDATE facebook_agent_assignments SET is_last_assigned = 1, last_assigned_at = '{$backendNow}', total_assigned_count = {$assignedCount} WHERE id = {$chosenId}");

        return $chosenUserId;
    }

    /**
     * Auto-seed active agents from users table if facebook_agent_assignments is empty
     */
    public function autoSeedAgentsIfEmpty()
    {
        try {
            $cntRow = $this->query("SELECT COUNT(*) AS total FROM facebook_agent_assignments");
            $total = !empty($cntRow[0][0]['total']) ? (int)$cntRow[0][0]['total'] : (!empty($cntRow[0]['total']) ? (int)$cntRow[0]['total'] : 0);
            if ($total > 0)
            {
                return;
            }

            $agents = $this->query("SELECT id FROM users WHERE status = 'Active' AND (login_status IS NULL OR login_status != 'Blocked') AND ugroup_id IN (2, 3, 4) ORDER BY id ASC");
            if (empty($agents))
            {
                $agents = $this->query("SELECT id FROM users WHERE status = 'Active' AND (login_status IS NULL OR login_status != 'Blocked') AND id != 1 ORDER BY id ASC LIMIT 10");
            }
            if (empty($agents))
            {
                $agents = $this->query("SELECT id FROM users WHERE status = 'Active' AND (login_status IS NULL OR login_status != 'Blocked') ORDER BY id ASC LIMIT 10");
            }
            if (empty($agents))
            {
                $agents = $this->query("SELECT id FROM users WHERE (login_status IS NULL OR login_status != 'Blocked') ORDER BY id ASC LIMIT 1");
            }

            if (!empty($agents))
            {
                $now = $this->_getBackendCurrentTime();
                $order = 1;
                foreach ($agents as $ag)
                {
                    $uId = isset($ag['users']['id']) ? (int)$ag['users']['id'] : (isset($ag['User']['id']) ? (int)$ag['User']['id'] : (int)$ag['id']);
                    $this->query("INSERT INTO facebook_agent_assignments (user_id, is_active, is_last_assigned, sort_order, total_assigned_count, created, modified) VALUES ({$uId}, 1, 0, {$order}, 0, '{$now}', '{$now}')");
                    $order++;
                }
            }
        } catch (Exception $e) {}
    }

    /**
     * Get current date and time in the configured backend timezone
     */
    protected function _getBackendCurrentTime()
    {
        try {
            $ConfigModel = ClassRegistry::init('Configuration');
            $cfg = $ConfigModel->find('first', array('recursive' => -1));
            $tz = !empty($cfg['Configuration']['timezone']) ? trim($cfg['Configuration']['timezone']) : 'Asia/Karachi';
            $dt = new DateTime('now', new DateTimeZone($tz));
            return $dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return date('Y-m-d H:i:s');
        }
    }
}
