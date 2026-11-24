<?php echo $this->element('property_enhancement_nav', array('property'=>$property));?>
<?php echo $this->Session->flash();?>
<div class="wv-enhance-card">
    <h3><?php echo __('Price History');?></h3>
    <p class="wv-muted"><?php echo __('Price changes are recorded when the existing property edit form changes price. This page is read-only to avoid payment/deal logic changes.');?></p>
    <div class="table-responsive">
        <table class="table table-bordered table-striped wv-enhance-table">
            <tr><th><?php echo __('Date');?></th><th><?php echo __('Old Price');?></th><th><?php echo __('New Price');?></th><th><?php echo __('Source');?></th><th><?php echo __('Note');?></th></tr>
            <?php foreach((array)$priceRows as $row){ ?>
            <tr>
                <td><?php echo !empty($row['PropertyPriceHistory']['created']) ? h(date('d-M-Y H:i',strtotime($row['PropertyPriceHistory']['created']))) : '-';?></td>
                <td><?php echo $currency.number_format((float)$row['PropertyPriceHistory']['old_price'],0);?></td>
                <td><?php echo $currency.number_format((float)$row['PropertyPriceHistory']['new_price'],0);?></td>
                <td><?php echo h($row['PropertyPriceHistory']['source']);?></td>
                <td><?php echo h($row['PropertyPriceHistory']['note']);?></td>
            </tr>
            <?php } ?>
            <?php if(empty($priceRows)){ ?><tr><td colspan="5"><?php echo __('No price changes recorded yet.');?></td></tr><?php } ?>
        </table>
    </div>
</div>
