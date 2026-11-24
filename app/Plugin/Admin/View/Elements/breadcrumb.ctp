<?php
$crumbController = !empty($this->request->params['controller']) ? ucwords(str_replace('_', ' ', strtolower((string)$this->request->params['controller']))) : __('Dashboard');
$crumbAction = !empty($this->request->params['action']) ? ucwords(str_replace('_', ' ', strtolower((string)$this->request->params['action']))) : __('Index');
?>
<div class="wv-admin-breadcrumbs" aria-label="<?php echo h(__('Breadcrumb'));?>">
    <a href="<?php echo $this->Html->url(array('plugin' => 'admin', 'controller' => 'Dashboards', 'action' => 'index'));?>"><?php echo __('Dashboard');?></a>
    <span class="wv-admin-breadcrumb-sep"><i class="fa fa-angle-right"></i></span>
    <span><?php echo h($crumbController);?></span>
    <?php if(strtolower((string)$crumbAction) !== 'index'){ ?>
        <span class="wv-admin-breadcrumb-sep"><i class="fa fa-angle-right"></i></span>
        <span><?php echo h($crumbAction);?></span>
    <?php } ?>
</div>
