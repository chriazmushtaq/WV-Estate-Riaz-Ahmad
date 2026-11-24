<div class="dashboard-subheader">
    <?php echo $this->Session->flash();?>
    <h2><?php echo __('View');?> <span><?php echo __('Letter');?></span></h2>
</div>

<div class="row">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="btn-group" style="margin-bottom:12px;">
                <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;'.__('Back'),array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'letters',$dealId),array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-print"></span>&nbsp;'.__('Print'),array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'printletter',$dealId,$post['Realestateletter']['id']),array('escape'=>false,'class'=>'btn btn-success','target'=>'_blank'));?>
            </div>
            <table class="table table-bordered">
                <tr>
                    <th width="25%"><?php echo __('Reference No');?></th>
                    <td><?php echo h($post['Realestateletter']['reference_no']);?></td>
                </tr>
                <tr>
                    <th><?php echo __('Title');?></th>
                    <td><?php echo h($post['Realestateletter']['title']);?></td>
                </tr>
                <tr>
                    <th><?php echo __('Type');?></th>
                    <td><?php echo h($letterTypeLabel);?></td>
                </tr>
                <tr>
                    <th><?php echo __('Date');?></th>
                    <td><?php echo !empty($post['Realestateletter']['letter_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,$post['Realestateletter']['letter_date']) : '';?></td>
                </tr>
                <tr>
                    <th><?php echo __('Client');?></th>
                    <td><?php echo h($post['Client']['name']);?></td>
                </tr>
                <tr>
                    <th><?php echo __('Property');?></th>
                    <td><?php echo h($post['Property']['name']);?></td>
                </tr>
                <tr>
                    <th><?php echo __('Membership');?></th>
                    <td><?php echo h($post['Deal']['invoice_no']);?></td>
                </tr>
                <tr>
                    <th><?php echo __('Status');?></th>
                    <td><?php echo h($post['Realestateletter']['status']);?></td>
                </tr>
            </table>
            <hr>
            <h4><?php echo __('Letter Body');?></h4>
            <?php if(!empty($isFullHtmlTemplate)){ ?>
                <iframe src="<?php echo $this->Html->url(array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'printletter',$dealId,$post['Realestateletter']['id']));?>" style="width:100%;min-height:900px;border:1px solid #d9d9d9;background:#fff;"></iframe>
            <?php } else { ?>
                <div class="well" style="background:#fff;">
                    <?php echo $renderedDescription;?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
