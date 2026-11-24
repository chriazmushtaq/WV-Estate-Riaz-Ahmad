<?php
$path = isset($path) && is_array($path) ? $path : array();
?>
<style type="text/css">
.wv-address-form{background:#f5f8fc;border:1px solid #dbe5f0;border-radius:12px;padding:16px;}
.wv-address-card{background:#fff;border:1px solid #dce6f2;border-radius:12px;box-shadow:0 12px 28px rgba(15,23,42,.06);padding:18px;margin-bottom:14px;}
.wv-address-card h3{color:#07152a;font-size:20px;font-weight:900;margin:0 0 14px;}
.wv-address-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
.wv-address-grid label{color:#07152a;display:block;font-size:11px;font-weight:900;margin:0 0 6px;text-transform:uppercase;}
.wv-address-grid .form-control{border:1px solid #cfdbea;border-radius:8px;box-shadow:none;height:42px;width:100%;}
.wv-address-path{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:8px;}
.wv-address-path span{background:#f8fafc;border:1px solid #dbe5ef;border-radius:8px;color:#07152a;display:block;font-size:12px;font-weight:800;min-height:38px;padding:9px;word-break:break-word;}
.wv-address-path small{color:#64748b;display:block;font-size:9px;font-weight:900;text-transform:uppercase;}
.wv-address-actions{border-top:1px solid #edf2f7;margin-top:18px;padding-top:14px;}
@media(max-width:900px){.wv-address-grid,.wv-address-path{grid-template-columns:1fr}}
</style>

<div class="page-title">
    <div class="title-env"><h1 class="title"><?php echo __('Edit Address Level');?></h1></div>
</div>
<div class="wv-address-form">
    <?php echo $this->Session->flash();?>
    <div class="wv-address-card">
        <h3><?php echo __('Current Address Path');?></h3>
        <div class="wv-address-path">
            <?php foreach(array('country','province','district','tehsil','city','town','block') as $level){ ?>
                <span><small><?php echo h(ucfirst($level));?></small><?php echo !empty($path[$level]) ? h($path[$level]) : '-';?></span>
            <?php } ?>
        </div>
    </div>
    <div class="wv-address-card">
        <h3><?php echo __('Edit Selected Level');?></h3>
        <?php echo $this->Form->create('Addresshierarchy', array('url'=>array('action'=>'edit',(int)$row['Addresshierarchy']['id'])));?>
        <?php echo $this->Form->hidden('id');?>
        <div class="wv-address-grid">
            <div>
                <label><?php echo __('Level');?></label>
                <?php echo $this->Form->select('level_type', $levelOptions, array('empty'=>__('Select Level'), 'class'=>'form-control'));?>
            </div>
            <div>
                <label><?php echo __('Parent');?></label>
                <?php echo $this->Form->select('parent_id', $parentOptions, array('empty'=>__('No Parent'), 'class'=>'form-control'));?>
            </div>
            <div>
                <label><?php echo __('Name');?></label>
                <?php echo $this->Form->text('name', array('class'=>'form-control', 'placeholder'=>__('Name')));?>
            </div>
            <div>
                <label><?php echo __('Sort');?></label>
                <?php echo $this->Form->text('sort_order', array('class'=>'form-control'));?>
            </div>
            <div>
                <label><?php echo __('Status');?></label>
                <label style="padding-top:10px;"><?php echo $this->Form->checkbox('status', array('hiddenField'=>false));?> <?php echo __('Active');?></label>
            </div>
        </div>
        <div class="wv-address-actions">
            <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Update');?></button>
            <?php echo $this->Html->link(__('Back'), array('action'=>'index'), array('class'=>'btn btn-default'));?>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
