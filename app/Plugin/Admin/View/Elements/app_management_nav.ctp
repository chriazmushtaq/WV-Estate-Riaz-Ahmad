<?php
$currentAction = !empty($this->params['action']) ? $this->params['action'] : 'index';
?>
<div class="panel panel-default" style="margin-bottom: 20px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
    <div class="panel-body" style="padding: 10px 15px;">
        <div class="btn-group btn-group-justified" role="group">
            <div class="btn-group" role="group">
                <?php echo $this->Html->link(
                    '<i class="fa fa-dashboard"></i> ' . __('Overview & Controls'),
                    array('controller' => 'Appmanagements', 'action' => 'index'),
                    array('escape' => false, 'class' => 'btn ' . ($currentAction === 'index' ? 'btn-primary' : 'btn-default'))
                ); ?>
            </div>
            <div class="btn-group" role="group">
                <?php echo $this->Html->link(
                    '<i class="fa fa-cloud-upload"></i> ' . __('Version & APK Update'),
                    array('controller' => 'Appmanagements', 'action' => 'version_control'),
                    array('escape' => false, 'class' => 'btn ' . ($currentAction === 'version_control' ? 'btn-primary' : 'btn-default'))
                ); ?>
            </div>
            <div class="btn-group" role="group">
                <?php echo $this->Html->link(
                    '<i class="fa fa-paper-plane"></i> ' . __('Send In-App Notification'),
                    array('controller' => 'Appmanagements', 'action' => 'send_notification'),
                    array('escape' => false, 'class' => 'btn ' . ($currentAction === 'send_notification' ? 'btn-primary' : 'btn-default'))
                ); ?>
            </div>
            <div class="btn-group" role="group">
                <?php echo $this->Html->link(
                    '<i class="fa fa-history"></i> ' . __('Broadcast History'),
                    array('controller' => 'Appmanagements', 'action' => 'notifications_history'),
                    array('escape' => false, 'class' => 'btn ' . ($currentAction === 'notifications_history' ? 'btn-primary' : 'btn-default'))
                ); ?>
            </div>
            <div class="btn-group" role="group">
                <?php echo $this->Html->link(
                    '<i class="fa fa-users"></i> ' . __('App Client Users'),
                    array('controller' => 'Appmanagements', 'action' => 'client_activity'),
                    array('escape' => false, 'class' => 'btn ' . ($currentAction === 'client_activity' ? 'btn-primary' : 'btn-default'))
                ); ?>
            </div>
        </div>
    </div>
</div>
