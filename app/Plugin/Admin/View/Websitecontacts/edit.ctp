<style>
.wv-admin-web{background:#f6f8fb;padding:16px;}
.wv-web-head{background:linear-gradient(135deg,#07152a,#102746 62%,#ff0a46 155%);border-radius:8px;color:#fff;margin-bottom:16px;padding:18px 20px;}
.wv-web-head h3{color:#fff;font-size:22px;font-weight:800;margin:0 0 5px;}
.wv-web-head p{color:#cbd5e1;margin:0;}
.wv-web-card{background:#fff;border:1px solid #dfe7f1;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.06);margin-bottom:18px;overflow:hidden;padding:16px;}
.wv-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
.wv-form-grid .full{grid-column:1/-1;}
.wv-actions{border-top:1px solid #eef2f7;margin-top:16px;padding-top:14px;}
@media(max-width:767px){.wv-form-grid{grid-template-columns:1fr;}}
</style>
<div class="wv-admin-web">
    <div class="wv-web-head">
        <h3><?php echo __('Edit Website Contact');?></h3>
        <p><?php echo __('Super admin can update saved website contact message details here.');?></p>
    </div>
    <?php echo $this->Session->flash();?>
    <div class="wv-web-card">
        <?php echo $this->Form->create('WebsiteContact', array('url'=>array('action'=>'edit',$this->request->data['WebsiteContact']['id'])));?>
        <div class="wv-form-grid">
            <div class="form-group">
                <label><?php echo __('Name');?></label>
                <?php echo $this->Form->input('name',array('label'=>false,'class'=>'form-control'));?>
            </div>
            <div class="form-group">
                <label><?php echo __('Phone');?></label>
                <?php echo $this->Form->input('phone',array('label'=>false,'class'=>'form-control'));?>
            </div>
            <div class="form-group">
                <label><?php echo __('Email');?></label>
                <?php echo $this->Form->input('email',array('label'=>false,'class'=>'form-control'));?>
            </div>
            <div class="form-group">
                <label><?php echo __('Status');?></label>
                <?php echo $this->Form->input('status',array('label'=>false,'class'=>'form-control','options'=>array('New'=>__('New'),'Read'=>__('Read'))));?>
            </div>
            <div class="form-group">
                <label><?php echo __('Subject');?></label>
                <?php echo $this->Form->input('subject',array('label'=>false,'class'=>'form-control'));?>
            </div>
            <div class="form-group">
                <label><?php echo __('Source');?></label>
                <?php echo $this->Form->input('source',array('label'=>false,'class'=>'form-control'));?>
            </div>
            <div class="form-group full">
                <label><?php echo __('Message');?></label>
                <?php echo $this->Form->input('message',array('label'=>false,'class'=>'form-control','type'=>'textarea','rows'=>8));?>
            </div>
        </div>
        <div class="wv-actions">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo __('Save Changes');?></button>
            <?php echo $this->Html->link(__('Back'),array('action'=>'index'),array('class'=>'btn btn-default'));?>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
