<?php
$folderOptions = isset($folderOptions) ? (array)$folderOptions : array();
$folderEditId = 0;
if(!empty($this->request->data['MediaFolder']['id']))
{
    $folderEditId = (int)$this->request->data['MediaFolder']['id'];
}
elseif(!empty($folder['MediaFolder']['id']))
{
    $folderEditId = (int)$folder['MediaFolder']['id'];
}
?>
<style>
.wv-folder-edit{background:#f6f8fb;border:1px solid #dbe5f0;border-radius:10px;padding:14px;}
.wv-folder-card{background:#fff;border:1px solid #dce6f2;border-radius:9px;box-shadow:0 10px 24px rgba(15,23,42,.06);margin:auto;max-width:760px;padding:18px;}
.wv-folder-card h1{color:#07152a;font-size:22px;font-weight:900;margin:0 0 14px;}
.wv-folder-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
.wv-folder-field.full{grid-column:1/-1;}
.wv-folder-field label{color:#07152a;display:block;font-size:10px;font-weight:900;margin-bottom:5px;text-transform:uppercase;}
.wv-folder-field .form-control{border:1px solid #cfdbea;border-radius:7px;height:40px;}
.wv-folder-field textarea.form-control{height:100px;}
.wv-folder-actions{border-top:1px solid #eef2f7;margin-top:16px;padding-top:14px;}
.wv-folder-actions .btn{border-radius:8px;font-weight:900;}
@media(max-width:640px){.wv-folder-grid{grid-template-columns:1fr;}}
</style>

<div class="page-title">
    <div class="title-env"><h1 class="title"><?php echo __('Edit Media Folder');?></h1></div>
</div>

<div class="wv-folder-edit">
    <?php echo $this->Session->flash();?>
    <div class="wv-folder-card">
        <h1><?php echo __('Folder Details');?></h1>
        <?php echo $this->Form->create('MediaFolder', array('url'=>array('controller'=>'media_galleries','action'=>'folderedit',$folderEditId)));?>
        <?php echo $this->Form->hidden('id');?>
        <div class="wv-folder-grid">
            <div class="wv-folder-field">
                <label><?php echo __('Folder Name');?></label>
                <?php echo $this->Form->text('name', array('class'=>'form-control'));?>
            </div>
            <div class="wv-folder-field">
                <label><?php echo __('Parent Folder');?></label>
                <?php echo $this->Form->select('parent_id', $folderOptions, array('empty'=>__('No Parent'),'label'=>false,'div'=>false,'class'=>'form-control'));?>
            </div>
            <div class="wv-folder-field">
                <label><?php echo __('Visibility');?></label>
                <?php echo $this->Form->select('visibility', array('backend'=>__('Backend Only'),'frontend'=>__('Frontend Only'),'both'=>__('Backend + Frontend')), array('label'=>false,'div'=>false,'class'=>'form-control'));?>
            </div>
            <div class="wv-folder-field">
                <label><?php echo __('Status');?></label>
                <?php echo $this->Form->select('status', array('Active'=>__('Active'),'Inactive'=>__('Inactive'),'Deleted'=>__('Deleted')), array('label'=>false,'div'=>false,'class'=>'form-control'));?>
            </div>
            <div class="wv-folder-field full">
                <label><?php echo __('Description');?></label>
                <?php echo $this->Form->textarea('description', array('class'=>'form-control'));?>
            </div>
        </div>
        <div class="wv-folder-actions">
            <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> <?php echo __('Update Folder');?></button>
            <?php echo $this->Html->link('<i class="fa fa-close"></i> '.__('Cancel'), array('action'=>'index'), array('escape'=>false,'class'=>'btn btn-default'));?>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
