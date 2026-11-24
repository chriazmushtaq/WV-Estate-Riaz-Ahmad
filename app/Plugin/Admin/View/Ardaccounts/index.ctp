<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Accounts Dashboard');?></h1>
        <p class="description"><?php echo __('Business performance overview');?></p>
    </div>
</div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php
            echo $this->Html->link('<span class="fa fa-plus"></span> '.__('Add Income'),array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId,'?'=>array('kind'=>'income')),array('escape'=>false,'class'=>'btn btn-success'));
            echo $this->Html->link('<span class="fa fa-minus"></span> '.__('Add Expense'),array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId,'?'=>array('kind'=>'expense')),array('escape'=>false,'class'=>'btn btn-danger'));
            echo $this->Html->link('<span class="fa fa-exchange"></span> '.__('Journal Voucher'),array('controller'=>'Ardaccounts','action'=>'transfer',$currentProjectId),array('escape'=>false,'class'=>'btn btn-primary'));
            echo $this->Html->link('<span class="fa fa-bar-chart"></span> '.__('Reports'),array('controller'=>'Ardaccounts','action'=>'reports',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));
            if(isset($userType) && $userType==='ADR')
            {
                echo $this->Form->postLink('<span class="fa fa-refresh"></span> '.__('Backfill Receipts'),array('controller'=>'DealsPayments','action'=>'backfill_all_receipts','?'=>array('project_id'=>$currentProjectId)),array('escape'=>false,'class'=>'btn btn-warning'),__('This will sync all historical receipts into Accounts. Continue?'));
            }
            ?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create(false,array('type'=>'get','url'=>array('controller'=>'Ardaccounts','action'=>'dashboard'),'class'=>'form-inline'));?>
        <div class="form-group">
            <label><?php echo __('Agency');?>&nbsp;</label>
            <?php echo $this->Form->select('project_id',$projectName,array('empty'=>__('All'),'class'=>'form-control','label'=>false,'div'=>false,'value'=>$currentProjectId));?>
        </div>
        <button class="btn btn-default" type="submit"><?php echo __('Filter');?></button>
        <?php echo $this->Form->end();?>

        <hr>
        <div class="row">
            <div class="col-sm-4"><div class="well text-center"><h5><?php echo __('Total Assets');?></h5><h2><?php echo $currency.$this->Number->format($stats['total_assets']);?></h2></div></div>
            <div class="col-sm-4"><div class="well text-center"><h5><?php echo __('Total Expenses');?></h5><h2><?php echo $currency.$this->Number->format($stats['total_expenses']);?></h2></div></div>
            <div class="col-sm-4"><div class="well text-center"><h5><?php echo __('Net Balance');?></h5><h2><?php echo $currency.$this->Number->format($stats['net_balance']);?></h2></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Cash In Hand');?></h5><h3><?php echo $currency.$this->Number->format($stats['cash_in_hand']);?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Receivables');?></h5><h3><?php echo $currency.$this->Number->format($stats['receivables']);?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Payables');?></h5><h3><?php echo $currency.$this->Number->format($stats['payables']);?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Construction Spend');?></h5><h3><?php echo $currency.$this->Number->format($stats['construction_spend']);?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Construction Due');?></h5><h3><?php echo $currency.$this->Number->format($stats['construction_due']);?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Payroll Due');?></h5><h3><?php echo $currency.$this->Number->format($stats['payroll_due']);?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Site Cost Variance');?></h5><h3><?php echo $currency.$this->Number->format($stats['site_cost_variance']);?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Low Stock');?></h5><h3><?php echo (int)$stats['low_stock_count'];?></h3></div></div>
        </div>

        <h4>
            <?php echo __('Recent Transactions');?>
            <span class="pull-right">
                <?php echo $this->Html->link(__('View All'),array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId),array('class'=>'btn btn-xs btn-default'));?>
                <?php echo $this->Html->link(__('Sync Paid Bills'),array('controller'=>'Ardaccounts','action'=>'syncpaidbills',$currentProjectId),array('class'=>'btn btn-xs btn-info'));?>
            </span>
        </h4>
        <table class="table table-striped table-bordered">
            <tr>
                <th><?php echo __('Date');?></th>
                <th><?php echo __('Account');?></th>
                <th><?php echo __('Type');?></th>
                <th><?php echo __('Amount');?></th>
                <th><?php echo __('Actions');?></th>
            </tr>
            <?php foreach($recentTransactions as $row):?>
            <tr>
                <td><?php echo (!empty($row['Ardtransaction']['entry_date']) && $row['Ardtransaction']['entry_date'] !== '0000-00-00') ? date('d-M-Y', strtotime((string)$row['Ardtransaction']['entry_date'])) : '-';?></td>
                <td><?php echo h($row['Ardledger']['name']);?></td>
                <td><?php echo h(strtoupper($row['Ardtransaction']['type']));?></td>
                <td><?php echo $currency.$this->Number->format($row['Ardtransaction']['amount']);?></td>
                <td>
                    <?php echo $this->Html->link(__('Print'),array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId),array('class'=>'btn btn-xs btn-default'));?>
                    <?php echo $this->Html->link(__('Edit'),array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId,'edit:'.$row['Ardtransaction']['id']),array('class'=>'btn btn-xs btn-warning'));?>
                </td>
            </tr>
            <?php endforeach;?>
        </table>
    </div>
</div>
