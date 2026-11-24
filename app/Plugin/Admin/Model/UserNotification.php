<?php
App::uses('AdminAppModel', 'Admin.Model');

class UserNotification extends AdminAppModel
{
    public $useTable = 'user_notifications';

    public $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
            'fields' => array('User.id', 'User.name', 'User.photo')
        )
    );

    public function getUnreadCount($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return 0;
        }
        try {
            return (int)$this->find('count', array(
                'conditions' => array(
                    'UserNotification.user_id' => $userId,
                    'UserNotification.is_read' => 0
                ),
                'recursive' => -1
            ));
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getRecentFeed($userId, $limit = 10, $onlyUnread = false)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return array();
        }
        $cond = array('UserNotification.user_id' => $userId);
        if ($onlyUnread) {
            $cond['UserNotification.is_read'] = 0;
        }
        try {
            return (array)$this->find('all', array(
                'conditions' => $cond,
                'order' => array('UserNotification.id' => 'DESC'),
                'limit' => max(1, min(100, (int)$limit)),
                'recursive' => -1
            ));
        } catch (Exception $e) {
            return array();
        }
    }

    public function markAsRead($id, $userId)
    {
        $id = (int)$id;
        $userId = (int)$userId;
        if ($id <= 0 || $userId <= 0) {
            return false;
        }
        return (bool)$this->updateAll(
            array('UserNotification.is_read' => 1),
            array('UserNotification.id' => $id, 'UserNotification.user_id' => $userId)
        );
    }

    public function markAllAsRead($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return false;
        }
        return (bool)$this->updateAll(
            array('UserNotification.is_read' => 1),
            array('UserNotification.user_id' => $userId, 'UserNotification.is_read' => 0)
        );
    }

    public function purgeOldNotifications($days = 90)
    {
        $days = max(30, (int)$days);
        $threshold = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return (bool)$this->deleteAll(array(
            'UserNotification.created <' => $threshold,
            'UserNotification.is_read' => 1
        ), false);
    }
}
