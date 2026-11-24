<?php $u = isset($row['BallotingUnit']) ? $row['BallotingUnit'] : array();?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Balloting Unit Details');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'), array('action' => 'unitedit', (int)$u['id']), array('escape' => false, 'class' => 'btn btn-primary'));?>
        <?php echo $this->Html->link('<span class="fa fa-list"></span>&nbsp;'.__('Manage Units'), array('action' => 'units', '?' => array('project_id' => (int)$u['project_id'])), array('escape' => false, 'class' => 'btn btn-default'));?>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th><?php echo __('Allotment #');?></th><td><?php echo h($u['allotment_no']);?></td></tr>
                    <tr><th><?php echo __('Property No');?></th><td><?php echo h($u['property_no']);?></td></tr>
                    <tr><th><?php echo __('Street No');?></th><td><?php echo h($u['street_no']);?></td></tr>
                    <tr><th><?php echo __('Block');?></th><td><?php echo h($u['block_name']);?></td></tr>
                    <tr><th><?php echo __('Street Address');?></th><td><?php echo h($u['street_address']);?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th><?php echo __('Area');?></th><td><?php echo h($u['unit_area']);?></td></tr>
                    <tr><th><?php echo __('Status');?></th><td><span class="label label-<?php echo ($u['status']==='Available') ? 'success' : (($u['status']==='Allotted') ? 'primary' : 'warning');?>"><?php echo h($u['status']);?></span></td></tr>
                    <tr><th><?php echo __('Notes');?></th><td><?php echo nl2br(h($u['notes']));?></td></tr>
                    <tr><th><?php echo __('Created');?></th><td><?php echo !empty($u['created']) ? h(date('d-M-Y H:i',strtotime($u['created']))) : '-';?></td></tr>
                    <tr><th><?php echo __('Modified');?></th><td><?php echo !empty($u['modified']) ? h(date('d-M-Y H:i',strtotime($u['modified']))) : '-';?></td></tr>
                </table>
            </div>
        </div>

        <h4><?php echo __('Allotment History');?></h4>
        <table class="table table-striped table-bordered">
            <tr>
                <th><?php echo __('#');?></th>
                <th><?php echo __('Deal');?></th>
                <th><?php echo __('Client');?></th>
                <th><?php echo __('Date');?></th>
                <th><?php echo __('Status');?></th>
            </tr>
            <?php if(empty($history)){?>
            <tr><td colspan="5" class="text-center"><?php echo __('No history');?></td></tr>
            <?php } else { $sn=1; foreach($history as $h){?>
            <tr>
                <td><?php echo $sn++;?></td>
                <td><?php echo h($h['Deal']['invoice_no']);?></td>
                <td><?php echo h($h['Client']['name']);?></td>
                <td><?php echo !empty($h['BallotingAllotment']['allotment_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $h['BallotingAllotment']['allotment_date']) : '';?></td>
                <td><?php echo h($h['BallotingAllotment']['status']);?></td>
            </tr>
            <?php }}?>
        </table>
    </div>
</div>
