<?php echo $this->element('property_enhancement_nav');?>
<?php echo $this->Session->flash();?>
<div class="wv-enhance-card">
    <h3><?php echo __('Latest Property Quality');?></h3>
    <p class="wv-muted"><?php echo __('Use this dashboard to improve property listing quality without changing core property, deal, or payment logic.');?></p>
    <div class="table-responsive">
        <table class="table table-bordered table-striped wv-enhance-table">
            <tr>
                <th><?php echo __('Office ID');?></th>
                <th><?php echo __('Property');?></th>
                <th><?php echo __('Published');?></th>
                <th><?php echo __('Quality');?></th>
                <th><?php echo __('Missing');?></th>
                <th><?php echo __('Action');?></th>
            </tr>
            <?php foreach((array)$qualityRows as $row){
                $post = $row['property'];
                $quality = $row['quality'];
                $pid = (int)$post['Property']['id'];
            ?>
            <tr>
                <td><?php echo h($post['Property']['uniq_id']);?></td>
                <td><?php echo h($post['Property']['name']);?></td>
                <td><?php echo h($post['Property']['published']);?></td>
                <td>
                    <div class="wv-score"><span style="width:<?php echo (int)$quality['score'];?>%"></span></div>
                    <div class="wv-score-text"><?php echo (int)$quality['score'];?>%</div>
                </td>
                <td><?php echo !empty($quality['missing']) ? h(implode(', ', array_slice($quality['missing'],0,3))) : __('Ready');?></td>
                <td><?php echo $this->Html->link(__('Open'), array('action'=>'quality',$pid), array('class'=>'btn btn-primary btn-sm'));?></td>
            </tr>
            <?php } ?>
            <?php if(empty($qualityRows)){ ?><tr><td colspan="6"><?php echo __('No properties found.');?></td></tr><?php } ?>
        </table>
    </div>
</div>
