<?php echo $this->element('property_enhancement_nav');?>
<?php echo $this->Session->flash();?>
<div class="wv-enhance-card">
    <h3><?php echo __('Saved Property Searches');?></h3>
    <div class="table-responsive">
        <table class="table table-bordered table-striped wv-enhance-table">
            <tr><th><?php echo __('Date');?></th><th><?php echo __('Name');?></th><th><?php echo __('Email');?></th><th><?php echo __('Status');?></th><th><?php echo __('Criteria');?></th></tr>
            <?php foreach((array)$savedSearches as $row){ $criteria=json_decode($row['PropertySavedSearch']['criteria_json'], true); ?>
            <tr>
                <td><?php echo !empty($row['PropertySavedSearch']['created']) ? h(date('d-M-Y H:i',strtotime($row['PropertySavedSearch']['created']))) : '-';?></td>
                <td><?php echo h($row['PropertySavedSearch']['name']);?></td>
                <td><?php echo h($row['PropertySavedSearch']['email']);?></td>
                <td><?php echo h($row['PropertySavedSearch']['status']);?></td>
                <td><?php echo h(json_encode((array)$criteria));?></td>
            </tr>
            <?php } ?>
            <?php if(empty($savedSearches)){ ?><tr><td colspan="5"><?php echo __('No saved searches found.');?></td></tr><?php } ?>
        </table>
    </div>
</div>
