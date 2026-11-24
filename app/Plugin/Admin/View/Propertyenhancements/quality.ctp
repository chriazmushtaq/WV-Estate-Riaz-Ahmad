<?php echo $this->element('property_enhancement_nav', array('property'=>$property));?>
<?php echo $this->Session->flash();?>
<div class="wv-enhance-card">
    <h3><?php echo __('Publish Quality Checklist');?></h3>
    <div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;margin-bottom:14px;">
        <div class="wv-score" style="width:260px;"><span style="width:<?php echo (int)$quality['score'];?>%"></span></div>
        <div class="wv-score-text"><?php echo (int)$quality['score'];?>%</div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped wv-enhance-table">
            <tr><th><?php echo __('Status');?></th><th><?php echo __('Checklist Item');?></th></tr>
            <?php foreach((array)$quality['checks'] as $check){ ?>
            <tr>
                <td><?php echo !empty($check['done']) ? '<span class="label label-success">'.__('Done').'</span>' : '<span class="label label-warning">'.__('Missing').'</span>';?></td>
                <td><?php echo h($check['label']);?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
    <?php echo $this->Form->create('PropertyQualityCheck', array('url'=>array('action'=>'quality',(int)$property['Property']['id'])));?>
    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?php echo __('Save Quality Snapshot');?></button>
    <?php echo $this->Form->end();?>
</div>
<div class="wv-enhance-card">
    <h3><?php echo __('Recent Snapshots');?></h3>
    <div class="table-responsive">
        <table class="table table-bordered table-striped wv-enhance-table">
            <tr><th><?php echo __('Date');?></th><th><?php echo __('Score');?></th><th><?php echo __('Missing');?></th></tr>
            <?php foreach((array)$snapshots as $snapshot){ ?>
            <tr>
                <td><?php echo !empty($snapshot['PropertyQualityCheck']['created']) ? h(date('d-M-Y H:i',strtotime($snapshot['PropertyQualityCheck']['created']))) : '-';?></td>
                <td><?php echo (int)$snapshot['PropertyQualityCheck']['completeness_score'];?>%</td>
                <td><?php echo h(implode(', ', (array)json_decode($snapshot['PropertyQualityCheck']['missing_items_json'], true)));?></td>
            </tr>
            <?php } ?>
            <?php if(empty($snapshots)){ ?><tr><td colspan="3"><?php echo __('No snapshots saved yet.');?></td></tr><?php } ?>
        </table>
    </div>
</div>
