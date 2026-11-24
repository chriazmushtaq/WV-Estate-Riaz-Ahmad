<?php
$defaultSettings = isset($defaultSettings) ? $defaultSettings : array();
?>
<style type="text/css">
.dt-add-shell {
    background: #f4f7fb;
    border: 1px solid #dbe6f3;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 25px;
}
.dt-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 18px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e2e8f0;
}
.dt-page-header h1 {
    margin: 0;
    font-size: 22px;
    font-weight: 800;
    color: #08213f;
    letter-spacing: -0.3px;
}
.dt-page-header p {
    margin: 4px 0 0;
    font-size: 13px;
    color: #64748b;
}
.dt-workflow-banner {
    background: #eef7ff;
    border-left: 4px solid #0b63ce;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    font-size: 13px;
    color: #1e3a5f;
}
.dt-footer-actions {
    background: #ffffff;
    border: 1px solid #dbe6f3;
    border-radius: 10px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    box-shadow: 0 4px 14px rgba(15,35,58,.04);
}
.dt-finalize-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 6px;
    padding: 8px 14px;
    cursor: pointer;
}
.dt-finalize-toggle input[type="checkbox"] {
    width: 17px;
    height: 17px;
    margin: 0;
    cursor: pointer;
}
.dt-finalize-label {
    font-size: 13px;
    font-weight: 700;
    color: #92400e;
    display: flex;
    flex-direction: column;
}
.dt-finalize-sub {
    font-size: 11px;
    font-weight: 500;
    color: #b45309;
}
</style>

<div class="dt-add-shell">
    <div class="dt-page-header">
        <div>
            <h1><i class="fa fa-plus-circle" style="color:#0b63ce;margin-right:6px;"></i> <?php echo __('New Deal Transfer / Merge Request');?></h1>
            <p><?php echo __('Create a safe transfer of ownership or consolidate payments through a non-destructive merge.');?></p>
        </div>
        <div>
            <?php echo $this->Html->link('<i class="fa fa-list"></i> '.__('View All Requests'), array('controller'=>'Dealtransfers','action'=>'index'), array('escape'=>false,'class'=>'btn btn-default btn-sm','style'=>'font-weight:700;'));?>
            <?php echo $this->Html->link('<i class="fa fa-sliders"></i> '.__('Settings'), array('controller'=>'Dealtransfers','action'=>'settings'), array('escape'=>false,'class'=>'btn btn-info btn-sm','style'=>'font-weight:700;margin-left:6px;'));?>
        </div>
    </div>

    <?php echo $this->Session->flash();?>

    <div class="dt-workflow-banner">
        <div>
            <strong><i class="fa fa-shield"></i> <?php echo __('Safest Workflow:');?></strong>
            <?php echo __('Saving will create a safe Draft. You can review all financial metrics, print fee vouchers, and verify customer identities before finalizing.');?>
        </div>
    </div>

    <?php echo $this->Form->create('Dealtransfer', array('class'=>'form-horizontal'));?>

    <?php echo $this->element('dealtransfer_form', array(
        'dealOptions'=>$dealOptions,
        'clientOptions'=>$clientOptions,
        'agencyOptions'=>$agencyOptions,
        'typeOptions'=>$typeOptions,
        'feeTypes'=>$feeTypes,
        'defaultSettings'=>$defaultSettings
    ));?>

    <div class="dt-footer-actions">
        <label class="dt-finalize-toggle">
            <?php echo $this->Form->checkbox('finalize_now');?>
            <span class="dt-finalize-label">
                <?php echo __('Finalize Immediately Upon Saving');?>
                <span class="dt-finalize-sub"><?php echo __('Unchecked (Recommended): Creates Draft for review & voucher payment first.');?></span>
            </span>
        </label>

        <div style="display:flex;gap:10px;">
            <?php echo $this->Html->link(__('Cancel'), array('controller'=>'Dealtransfers','action'=>'index'), array('class'=>'btn btn-default','style'=>'font-weight:700;padding:8px 18px;'));?>
            <button type="submit" class="btn btn-success" style="font-weight:800;padding:8px 24px;">
                <span class="fa fa-check-circle"></span> <?php echo __('Save Transfer Request');?>
            </button>
        </div>
    </div>

    <?php echo $this->Form->end();?>
</div>
