<?php
$dealRow = !empty($dealMeta) ? $dealMeta : array();
$dealInfo = !empty($dealRow['Deal']) ? $dealRow['Deal'] : array();
$clientInfo = !empty($dealRow['Client']) ? $dealRow['Client'] : array();
$propertyInfo = !empty($dealRow['Property']) ? $dealRow['Property'] : array();
$projectInfo = !empty($dealRow['Project']) ? $dealRow['Project'] : array();
$propertyId = !empty($propertyInfo['id']) ? (int)$propertyInfo['id'] : 0;
$paymentPlanTabUrl = $this->Html->url(array('controller'=>'plans_payments','action'=>'index',$dealId));
$documentTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'documents',$dealId));
$lettersTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'letters',$dealId));
$paidTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'index',$dealId));
?>

<style type="text/css">
.deal-comm-wrap {
    background: #f5f7fa;
    border: 1px solid #dbe3ec;
    padding: 12px;
}
.deal-comm-title {
    margin: 0 0 8px;
    font-size: 22px;
    color: #1f2f40;
    font-weight: 700;
}
.deal-comm-sub {
    margin: 0 0 10px;
    color: #4e6072;
    font-size: 12px;
}
.deal-comm-tabs {
    margin-bottom: 12px;
}
.deal-comm-tabs > li > a {
    font-size: 12px;
    font-weight: 600;
}
.deal-comm-card {
    background: #fff;
    border: 1px solid #dbe3ec;
    padding: 10px;
    margin-bottom: 12px;
}
.deal-comm-card h4 {
    margin: 0 0 10px;
    color: #1f4364;
    font-size: 14px;
    font-weight: 700;
}
.deal-comm-meta {
    margin-bottom: 8px;
    font-size: 12px;
    color: #31465b;
}
.deal-comm-meta strong {
    color: #1f2f40;
}
</style>

<div class="panel-heading">
    <div class="btn-group">
        <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;'.__('Back To Ledger'),array('controller'=>'deals_payments','action'=>'index',$dealId),array('escape'=>false,'class'=>'btn btn-info'));?>
        <?php echo $this->Html->link('<span class="fa fa-dollar"></span>&nbsp;'.__('Add New Payment'),array('controller'=>'deals_payments','action'=>'add',$dealId),array('escape'=>false,'class'=>'btn btn-success'));?>
    </div>
</div>

<div class="panel">
    <?php echo $this->Session->flash(); ?>
    <div class="panel-body deal-comm-wrap deal-comm-content">
        <h2 class="deal-comm-title"><?php echo __('Deal Communication'); ?></h2>
        <p class="deal-comm-sub">
            <strong><?php echo __('Agency'); ?>:</strong> <?php echo h(!empty($projectInfo['name']) ? $projectInfo['name'] : '-'); ?>
            &nbsp; | &nbsp;
            <strong><?php echo __('Client'); ?>:</strong> <?php echo h(!empty($clientInfo['name']) ? $clientInfo['name'] : '-'); ?>
            &nbsp; | &nbsp;
            <strong><?php echo __('Mobile'); ?>:</strong> <?php echo h(!empty($clientInfo['phone']) ? $clientInfo['phone'] : '-'); ?>
            &nbsp; | &nbsp;
            <strong><?php echo __('File'); ?>:</strong> <?php echo h(!empty($propertyInfo['name']) ? $propertyInfo['name'] : '-'); ?>
        </p>

        <ul class="nav nav-tabs deal-comm-tabs">
            <li><a href="<?php echo $paidTabUrl; ?>"><?php echo __('Paid'); ?></a></li>
            <li><a href="<?php echo $paymentPlanTabUrl; ?>"><?php echo __('Payment Plan'); ?></a></li>
            <li><a href="<?php echo $documentTabUrl; ?>"><?php echo __('Document'); ?></a></li>
            <li><a href="<?php echo $lettersTabUrl; ?>"><?php echo __('Letters'); ?></a></li>
            <li class="active"><a href="javascript:void(0);"><?php echo __('Communication'); ?></a></li>
        </ul>

        <div class="row">
            <div class="col-md-6">
                <div class="deal-comm-card">
                    <h4><?php echo __('Send Custom SMS'); ?></h4>
                    <?php echo $this->Form->create('DealCommunication',array('url'=>array('controller'=>'deals_payments','action'=>'communication',$dealId),'class'=>'form-horizontal'));?>
                    <?php echo $this->Form->hidden('entry_type',array('value'=>'SMS'));?>
                    <div class="deal-comm-meta">
                        <strong><?php echo __('To'); ?>:</strong> <?php echo h(!empty($clientInfo['phone']) ? $clientInfo['phone'] : __('No Number')); ?>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo __('Subject'); ?></label>
                        <div class="col-sm-9">
                            <?php echo $this->Form->input('subject',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Custom SMS')));?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo __('Message'); ?></label>
                        <div class="col-sm-9">
                            <?php echo $this->Form->textarea('message',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>4,'placeholder'=>__('Type SMS message')));?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="submit" class="btn btn-success"><span class="fa fa-paper-plane"></span>&nbsp;<?php echo __('Send & Save'); ?></button>
                        </div>
                    </div>
                    <?php echo $this->Form->end();?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="deal-comm-card">
                    <h4><?php echo __('Add Recovery Call Detail'); ?></h4>
                    <?php echo $this->Form->create('DealCommunication',array('url'=>array('controller'=>'deals_payments','action'=>'communication',$dealId),'class'=>'form-horizontal'));?>
                    <?php echo $this->Form->hidden('entry_type',array('value'=>'CALL'));?>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo __('Subject'); ?></label>
                        <div class="col-sm-9">
                            <?php echo $this->Form->input('subject',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Recovery Call')));?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo __('Status'); ?></label>
                        <div class="col-sm-9">
                            <?php
                            echo $this->Form->select('call_status',array(
                                'Connected'=>__('Connected'),
                                'No Answer'=>__('No Answer'),
                                'Switched Off'=>__('Switched Off'),
                                'Promise To Pay'=>__('Promise To Pay'),
                                'Follow Up Required'=>__('Follow Up Required')
                            ),array('empty'=>__('Please Select'),'label'=>false,'div'=>false,'class'=>'form-control'));
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo __('Followup Date'); ?></label>
                        <div class="col-sm-9">
                            <?php echo $this->Form->input('followup_date',array('type'=>'text','label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>'YYYY-MM-DD'));?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo __('Notes'); ?></label>
                        <div class="col-sm-9">
                            <?php echo $this->Form->textarea('message',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>4,'placeholder'=>__('Call notes/details')));?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="submit" class="btn btn-primary"><span class="fa fa-phone"></span>&nbsp;<?php echo __('Save Call Log'); ?></button>
                        </div>
                    </div>
                    <?php echo $this->Form->end();?>
                </div>
            </div>
        </div>

        <div class="deal-comm-card">
            <h4><?php echo __('Communication History'); ?></h4>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th><?php echo __('#'); ?></th>
                            <th><?php echo __('Date'); ?></th>
                            <th><?php echo __('Type'); ?></th>
                            <th><?php echo __('Subject'); ?></th>
                            <th><?php echo __('Status'); ?></th>
                            <th><?php echo __('Followup'); ?></th>
                            <th><?php echo __('Details'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($communicationHistory)){ $s=1; foreach($communicationHistory as $row){ ?>
                        <tr>
                            <td><?php echo $s++; ?></td>
                            <td><?php echo !empty($row['DealCommunication']['created']) ? $this->Time->format('d-M-Y H:i',$row['DealCommunication']['created']) : '-'; ?></td>
                            <td><?php echo h($row['DealCommunication']['entry_type']); ?></td>
                            <td><?php echo h($row['DealCommunication']['subject']); ?></td>
                            <td><?php echo h(!empty($row['DealCommunication']['call_status']) ? $row['DealCommunication']['call_status'] : '-'); ?></td>
                            <td><?php echo !empty($row['DealCommunication']['followup_date']) && $row['DealCommunication']['followup_date']!='0000-00-00' ? $this->Time->format('d-M-Y',$row['DealCommunication']['followup_date']) : '-'; ?></td>
                            <td><?php echo nl2br(h($row['DealCommunication']['message'])); ?></td>
                        </tr>
                        <?php }}else{ ?>
                        <tr><td colspan="7" class="text-center"><?php echo __('No communication history found.'); ?></td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
