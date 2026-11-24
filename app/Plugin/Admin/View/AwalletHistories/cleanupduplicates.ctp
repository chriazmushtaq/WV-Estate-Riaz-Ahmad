<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Commission Cleanup');?></h1>
    </div>
</div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;'.__('Back'),array('controller'=>'AwalletHistories','action'=>'index'),array('escape'=>false,'class'=>'btn btn-info'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="alert alert-warning">
            <strong><?php echo __('Safety Note');?>:</strong>
            <?php echo __('This tool removes duplicate agent commission credits for the same deal and reverses those amounts from wallet balance. It keeps only one commission credit per deal/user.');?>
        </div>

        <table class="table table-bordered">
            <tr>
                <th><?php echo __('Duplicate Groups');?></th>
                <th><?php echo __('Duplicate Rows');?></th>
                <th><?php echo __('Duplicate Amount');?></th>
            </tr>
            <tr>
                <td><?php echo (int)$plan['total_groups'];?></td>
                <td><?php echo (int)$plan['total_duplicate_rows'];?></td>
                <td><?php echo $currency.$this->Number->format($plan['total_duplicate_amount']);?></td>
            </tr>
        </table>

        <?php if(empty($plan['groups'])){?>
            <div class="alert alert-success"><?php echo __('No duplicate commission entries found.');?></div>
        <?php }else{?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <tr>
                        <th><?php echo __('#');?></th>
                        <th><?php echo __('Deal');?></th>
                        <th><?php echo __('Agent');?></th>
                        <th><?php echo __('Kept Entry');?></th>
                        <th><?php echo __('Duplicates');?></th>
                        <th><?php echo __('Amount To Reverse');?></th>
                    </tr>
                    <?php $i=1; foreach((array)$plan['groups'] as $group){ 
                        $dealId = (int)$group['deal_id'];
                        $userId = (int)$group['user_id'];
                        $keepRow = isset($group['keep_row']['AwalletHistory']) ? $group['keep_row']['AwalletHistory'] : array();
                        $dupRows = isset($group['duplicate_rows']) ? (array)$group['duplicate_rows'] : array();
                    ?>
                    <tr>
                        <td><?php echo $i++;?></td>
                        <td><?php echo h(isset($dealNameMap[$dealId]) ? $dealNameMap[$dealId] : ('Deal #'.$dealId));?></td>
                        <td><?php echo h(isset($userNameMap[$userId]) ? $userNameMap[$userId] : ('User #'.$userId));?></td>
                        <td>
                            <?php
                            $keepId = isset($keepRow['id']) ? (int)$keepRow['id'] : 0;
                            $keepAmt = isset($keepRow['amount']) ? (float)$keepRow['amount'] : 0;
                            $keepDate = isset($keepRow['date']) ? $keepRow['date'] : '';
                            echo __('ID').': '.$keepId.' | '.__('Amount').': '.$currency.$this->Number->format($keepAmt).' | '.__('Date').': '.$this->Time->Format('d-M-Y h:i:s A',$keepDate);
                            ?>
                        </td>
                        <td>
                            <?php foreach($dupRows as $dup){ 
                                $d = isset($dup['AwalletHistory']) ? $dup['AwalletHistory'] : array();
                                $dId = isset($d['id']) ? (int)$d['id'] : 0;
                                $dAmt = isset($d['amount']) ? (float)$d['amount'] : 0;
                                $dDate = isset($d['date']) ? $d['date'] : '';
                            ?>
                                <div><?php echo __('ID').': '.$dId.' | '.__('Amount').': '.$currency.$this->Number->format($dAmt).' | '.__('Date').': '.$this->Time->Format('d-M-Y h:i:s A',$dDate);?></div>
                            <?php } ?>
                        </td>
                        <td><?php echo $currency.$this->Number->format($group['duplicate_amount']);?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>

            <?php echo $this->Form->create('AwalletHistory', array('class'=>'form-inline'));?>
            <?php echo $this->Form->hidden('run_cleanup', array('value'=>1));?>
            <button type="submit" class="btn btn-danger" onclick="return confirm('<?php echo __('Are you sure to remove duplicate commissions and reverse those wallet amounts?');?>');">
                <span class="fa fa-trash"></span>&nbsp;<?php echo __('Run Cleanup Now');?>
            </button>
            <?php echo $this->Form->end();?>
        <?php }?>
    </div>
</div>
