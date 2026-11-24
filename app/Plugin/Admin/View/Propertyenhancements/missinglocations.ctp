<?php echo $this->element('property_enhancement_nav');?>
<?php echo $this->Session->flash();?>
<div class="wv-enhance-card">
    <h3><?php echo __('Properties Missing Map Location');?></h3>
    <div class="table-responsive">
        <table class="table table-bordered table-striped wv-enhance-table">
            <tr><th><?php echo __('Office ID');?></th><th><?php echo __('Property');?></th><th><?php echo __('Address');?></th><th><?php echo __('Published');?></th><th><?php echo __('Action');?></th></tr>
            <?php foreach((array)$properties as $post){ ?>
            <tr>
                <td><?php echo h($post['Property']['uniq_id']);?></td>
                <td><?php echo h($post['Property']['name']);?></td>
                <td><?php echo h($post['Property']['address']);?></td>
                <td><?php echo h($post['Property']['published']);?></td>
                <td><?php echo $this->Html->link(__('Edit Map'), array('controller'=>'Properties','action'=>'edit',(int)$post['Property']['id']), array('class'=>'btn btn-primary btn-sm'));?></td>
            </tr>
            <?php } ?>
            <?php if(empty($properties)){ ?><tr><td colspan="5"><?php echo __('All listed properties have map coordinates.');?></td></tr><?php } ?>
        </table>
    </div>
</div>
