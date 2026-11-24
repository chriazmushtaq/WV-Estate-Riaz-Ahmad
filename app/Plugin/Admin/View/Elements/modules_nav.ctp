<?php
$currAction = strtolower($this->params['action']);
?>
<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div style="display: inline-flex; background: #f1f5f9; padding: 4px; border-radius: 8px; gap: 4px;">
        <a href="<?php echo $this->Html->url(array('controller' => 'Modules', 'action' => 'index')); ?>" 
           style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; font-size: 13px; font-weight: 700; text-decoration: none; border-radius: 6px; <?php echo ($currAction === 'index') ? 'background: #ffffff; color: #2563eb; box-shadow: 0 2px 6px rgba(0,0,0,0.06);' : 'color: #475569;'; ?>">
            <span class="fa fa-cubes"></span> <?php echo __('Installed Modules'); ?>
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'Modules', 'action' => 'builder')); ?>" 
           style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; font-size: 13px; font-weight: 700; text-decoration: none; border-radius: 6px; <?php echo ($currAction === 'builder') ? 'background: #ffffff; color: #2563eb; box-shadow: 0 2px 6px rgba(0,0,0,0.06);' : 'color: #475569;'; ?>">
            <span class="fa fa-magic"></span> <?php echo __('Module Builder & Generator'); ?>
        </a>
    </div>

    <div>
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalInstallModule" style="font-weight: 700; border-radius: 6px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">
            <span class="fa fa-cloud-upload"></span> <?php echo __('Install Module from ZIP'); ?>
        </button>
    </div>
</div>
