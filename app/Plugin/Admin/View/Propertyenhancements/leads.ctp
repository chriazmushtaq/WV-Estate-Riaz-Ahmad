<?php echo $this->element('property_enhancement_nav', array('property'=>$property));?>
<?php echo $this->Session->flash();?>
<div class="wv-enhance-card">
    <h3><?php echo __('Contact Click Analytics');?></h3>
    <div class="row">
        <?php foreach((array)$clickSummary as $summary){ ?>
        <div class="col-sm-3">
            <div class="well">
                <strong><?php echo h(ucfirst($summary['PropertyLeadClick']['click_type']));?></strong><br>
                <span class="wv-score-text"><?php echo (int)$summary[0]['total'];?></span>
            </div>
        </div>
        <?php } ?>
        <?php if(empty($clickSummary)){ ?><div class="col-sm-12 wv-muted"><?php echo __('No contact clicks tracked yet.');?></div><?php } ?>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped wv-enhance-table">
            <tr><th><?php echo __('Date');?></th><th><?php echo __('Type');?></th><th><?php echo __('IP');?></th><th><?php echo __('Referrer');?></th></tr>
            <?php foreach((array)$clickRows as $row){ ?>
            <tr>
                <td><?php echo !empty($row['PropertyLeadClick']['created']) ? h(date('d-M-Y H:i',strtotime($row['PropertyLeadClick']['created']))) : '-';?></td>
                <td><?php echo h($row['PropertyLeadClick']['click_type']);?></td>
                <td><?php echo h($row['PropertyLeadClick']['visitor_ip']);?></td>
                <td><?php echo h($row['PropertyLeadClick']['referrer']);?></td>
            </tr>
            <?php } ?>
            <?php if(empty($clickRows)){ ?><tr><td colspan="4"><?php echo __('No click records found.');?></td></tr><?php } ?>
        </table>
    </div>
</div>
