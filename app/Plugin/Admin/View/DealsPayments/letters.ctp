<?php
$dealRow = !empty($dealMeta) ? $dealMeta : array();
$dealInfo = !empty($dealRow['Deal']) ? $dealRow['Deal'] : array();
$clientInfo = !empty($dealRow['Client']) ? $dealRow['Client'] : array();
$propertyInfo = !empty($dealRow['Property']) ? $dealRow['Property'] : array();
$projectInfo = !empty($dealRow['Project']) ? $dealRow['Project'] : array();
$paidTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'index',$dealId));
$paymentPlanTabUrl = $this->Html->url(array('controller'=>'plans_payments','action'=>'index',$dealId));
$documentTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'documents',$dealId));
$lettersTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'letters',$dealId));
$communicationTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'communication',$dealId));
$letterTypeArr = !empty($letterTypeArr) ? (array)$letterTypeArr : array();
$autoLetterTypeArr = !empty($autoLetterTypeArr) ? (array)$autoLetterTypeArr : array();
?>

<style type="text/css">
.deal-letters-wrap {
    background: #f5f8fc;
    border: 1px solid #dbe3ec;
    padding: 12px;
}
.deal-letters-meta {
    margin-bottom: 10px;
    font-size: 12px;
    color: #31465b;
}
.deal-letters-meta strong {
    color: #1f2f40;
}
.deal-letters-tabs {
    margin-bottom: 10px;
}
.deal-letters-tabs > li > a {
    font-size: 12px;
    font-weight: 600;
}
</style>

<div id="deal-letters-content">
    <?php echo $this->Session->flash(); ?>
    <div class="deal-letters-wrap">
        <div class="deal-letters-meta">
            <strong><?php echo __('Agency'); ?>:</strong> <?php echo h(!empty($projectInfo['name']) ? $projectInfo['name'] : '-'); ?>
            &nbsp; | &nbsp;
            <strong><?php echo __('Client'); ?>:</strong> <?php echo h(!empty($clientInfo['name']) ? $clientInfo['name'] : '-'); ?>
            &nbsp; | &nbsp;
            <strong><?php echo __('File'); ?>:</strong> <?php echo h(!empty($propertyInfo['name']) ? $propertyInfo['name'] : '-'); ?>
            &nbsp; | &nbsp;
            <strong><?php echo __('Membership'); ?>:</strong> <?php echo h(!empty($dealInfo['invoice_no']) ? $dealInfo['invoice_no'] : '-'); ?>
        </div>

        <ul class="nav nav-tabs deal-letters-tabs no-print">
            <li><a href="<?php echo $paidTabUrl; ?>"><?php echo __('Paid'); ?></a></li>
            <li><a href="<?php echo $paymentPlanTabUrl; ?>"><?php echo __('Payment Plan'); ?></a></li>
            <li><a href="<?php echo $documentTabUrl; ?>"><?php echo __('Document'); ?></a></li>
            <li class="active"><a href="javascript:void(0);"><?php echo __('Letters'); ?></a></li>
            <li><a href="<?php echo $communicationTabUrl; ?>"><?php echo __('Communication'); ?></a></li>
        </ul>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th style="width:50px;"><?php echo __('#'); ?></th>
                        <th style="width:140px;"><?php echo __('Ref No'); ?></th>
                        <th style="width:120px;"><?php echo __('Date'); ?></th>
                        <th style="width:190px;"><?php echo __('Type'); ?></th>
                        <th><?php echo __('Title'); ?></th>
                        <th style="width:120px;"><?php echo __('Status'); ?></th>
                        <th style="width:150px;"><?php echo __('Action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($dealLetters)){ $sr=1; foreach($dealLetters as $row){
                        $letter = !empty($row['Realestateletter']) ? $row['Realestateletter'] : array();
                        $letterId = !empty($letter['id']) ? (int)$letter['id'] : 0;
                        $typeCode = !empty($letter['letter_type']) ? (string)$letter['letter_type'] : '';
                        $typeLabel = isset($letterTypeArr[$typeCode]) ? $letterTypeArr[$typeCode] : $typeCode;
                        $isAuto = isset($autoLetterTypeArr[$typeCode]);
                        ?>
                        <tr>
                            <td><?php echo $sr++; ?></td>
                            <td><?php echo h(!empty($letter['reference_no']) ? $letter['reference_no'] : '-'); ?></td>
                            <td><?php echo !empty($letter['letter_date']) ? $this->Time->format('d-M-Y',$letter['letter_date']) : '-'; ?></td>
                            <td>
                                <?php echo h($typeLabel); ?>
                                <?php if($isAuto){ ?><span class="label label-info" style="margin-left:4px;"><?php echo __('Auto'); ?></span><?php } ?>
                            </td>
                            <td><?php echo h(!empty($letter['title']) ? $letter['title'] : '-'); ?></td>
                            <td>
                                <span class="label label-<?php echo (!empty($letter['status']) && $letter['status'] === 'Published') ? 'success' : 'default'; ?>">
                                    <?php echo h(!empty($letter['status']) ? $letter['status'] : '-'); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($letterId > 0){ ?>
                                    <a class="btn btn-default btn-xs" target="_blank" href="<?php echo $this->Html->url(array('controller'=>'Realestateletters','action'=>'view',$letterId)); ?>">
                                        <span class="fa fa-eye"></span>&nbsp;<?php echo __('View'); ?>
                                    </a>
                                    <a class="btn btn-success btn-xs" target="_blank" href="<?php echo $this->Html->url(array('controller'=>'Realestateletters','action'=>'printletter',$letterId)); ?>">
                                        <span class="fa fa-print"></span>&nbsp;<?php echo __('Print'); ?>
                                    </a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php }} else { ?>
                        <tr>
                            <td colspan="7" class="text-center"><?php echo __('No letters found for this deal.'); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
