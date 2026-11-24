<?php
$defaultSettings = isset($defaultSettings) ? $defaultSettings : array();
$transferId = isset($this->request->data['Dealtransfer']['id']) ? (int)$this->request->data['Dealtransfer']['id'] : 0;
$transferNo = isset($this->request->data['Dealtransfer']['transfer_no']) ? $this->request->data['Dealtransfer']['transfer_no'] : '';
?>
<style type="text/css">
.dt-edit-shell {
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
.dt-edit-badge {
    display: inline-block;
    background: #fef3c7;
    border: 1px solid #fde68a;
    color: #92400e;
    font-size: 12px;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 4px;
    text-transform: uppercase;
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
</style>

<div class="dt-edit-shell">
    <div class="dt-page-header">
        <div>
            <h1>
                <i class="fa fa-pencil-square" style="color:#0b63ce;margin-right:6px;"></i>
                <?php echo __('Edit Draft Request:');?> <span style="color:#0b63ce;"><?php echo h($transferNo);?></span>
            </h1>
            <p><?php echo __('Modify draft parameters, update fee calculations, or adjust destination deal / client.');?></p>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="dt-edit-badge"><i class="fa fa-pencil"></i> <?php echo __('Draft Mode');?></span>
            <?php echo $this->Html->link('<i class="fa fa-eye"></i> '.__('View Record'), array('controller'=>'Dealtransfers','action'=>'view', $transferId), array('escape'=>false,'class'=>'btn btn-default btn-sm','style'=>'font-weight:700;'));?>
        </div>
    </div>

    <?php echo $this->Session->flash();?>

    <?php echo $this->Form->create('Dealtransfer', array('class'=>'form-horizontal'));?>
    <?php echo $this->Form->hidden('transfer_no');?>

    <?php echo $this->element('dealtransfer_form', array(
        'dealOptions'=>$dealOptions,
        'clientOptions'=>$clientOptions,
        'agencyOptions'=>$agencyOptions,
        'typeOptions'=>$typeOptions,
        'feeTypes'=>$feeTypes,
        'defaultSettings'=>$defaultSettings
    ));?>

    <div class="dt-footer-actions">
        <div style="font-size:13px;color:#64748b;font-weight:600;">
            <i class="fa fa-info-circle" style="color:#0b63ce;"></i> <?php echo __('Only Draft requests can be edited. Changes to fees or deals update preview amounts immediately.');?>
        </div>
        <div style="display:flex;gap:10px;">
            <?php echo $this->Html->link(__('Cancel'), array('controller'=>'Dealtransfers','action'=>'view', $transferId), array('class'=>'btn btn-default','style'=>'font-weight:700;padding:8px 18px;'));?>
            <button type="submit" class="btn btn-success" style="font-weight:800;padding:8px 24px;">
                <span class="fa fa-save"></span> <?php echo __('Save Changes');?>
            </button>
        </div>
    </div>

    <?php echo $this->Form->end();?>
</div>
