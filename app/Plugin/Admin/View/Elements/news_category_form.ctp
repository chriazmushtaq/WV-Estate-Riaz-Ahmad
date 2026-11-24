<style>.wv-news-form{background:#f6f8fb;padding:14px}.wv-news-panel{background:#fff;border:1px solid #dbe5f2;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.06);padding:18px}.wv-news-title{color:#07152a;font-size:22px;font-weight:900;margin:0 0 16px}</style>
<div class="wv-news-form">
    <div class="wv-news-panel">
        <h3 class="wv-news-title"><i class="fa fa-folder-open"></i> <?php echo h($formTitle);?></h3>
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('NewsCategory',array('class'=>'form-horizontal'));?>
        <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Name');?></label><div class="col-sm-10"><?php echo $this->Form->input('name',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Category Name')));?></div></div>
        <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Slug');?></label><div class="col-sm-10"><?php echo $this->Form->input('slug',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('auto-generated if empty')));?></div></div>
        <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Description');?></label><div class="col-sm-10"><?php echo $this->Form->input('description',array('type'=>'textarea','rows'=>4,'label'=>false,'div'=>false,'class'=>'form-control'));?></div></div>
        <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Sort Order');?></label><div class="col-sm-4"><?php echo $this->Form->input('sort_order',array('label'=>false,'div'=>false,'class'=>'form-control','value'=>isset($this->request->data['NewsCategory']['sort_order']) ? $this->request->data['NewsCategory']['sort_order'] : 0));?></div><label class="col-sm-2 control-label"><?php echo __('Status');?></label><div class="col-sm-4"><?php echo $this->Form->input('status',array('label'=>false,'div'=>false,'class'=>'form-control','options'=>array('Published'=>__('Published'),'Draft'=>__('Draft'),'Unpublished'=>__('Unpublished')),'default'=>'Published'));?></div></div>
        <div class="form-group"><div class="col-sm-offset-2 col-sm-10"><button class="btn btn-success" type="submit"><i class="fa fa-save"></i> <?php echo h($submitText);?></button> <?php echo $this->Html->link(__('Back'),array('action'=>'index'),array('class'=>'btn btn-danger'));?></div></div>
        <?php echo $this->Form->end();?>
    </div>
</div>
