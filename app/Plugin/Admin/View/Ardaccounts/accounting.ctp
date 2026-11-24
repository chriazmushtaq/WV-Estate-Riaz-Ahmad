<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Accounting Module');?></h1></div></div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php
            echo $this->Html->link('<span class="fa fa-book"></span> '.__('Ledgers'),array('controller'=>'Ardaccounts','action'=>'ledgers',$currentProjectId),array('escape'=>false,'class'=>'btn btn-primary'));
            echo $this->Html->link('<span class="fa fa-money"></span> '.__('Vouchers'),array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId),array('escape'=>false,'class'=>'btn btn-success'));
            echo $this->Html->link('<span class="fa fa-exchange"></span> '.__('Journal Voucher'),array('controller'=>'Ardaccounts','action'=>'transfer',$currentProjectId),array('escape'=>false,'class'=>'btn btn-info'));
            echo $this->Html->link('<span class="fa fa-repeat"></span> '.__('Recurring'),array('controller'=>'Ardaccounts','action'=>'recurring',$currentProjectId),array('escape'=>false,'class'=>'btn btn-warning'));
            echo $this->Html->link('<span class="fa fa-balance-scale"></span> '.__('AR / AP'),array('controller'=>'Ardaccounts','action'=>'arap',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));
            ?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="row">
            <div class="col-sm-2"><div class="well text-center"><h5><?php echo __('Ledgers');?></h5><h3><?php echo (int)$summary['ledgers'];?></h3></div></div>
            <div class="col-sm-2"><div class="well text-center"><h5><?php echo __('Vouchers');?></h5><h3><?php echo (int)$summary['transactions'];?></h3></div></div>
            <div class="col-sm-2"><div class="well text-center"><h5><?php echo __('Recurring');?></h5><h3><?php echo (int)$summary['recurring'];?></h3></div></div>
            <div class="col-sm-2"><div class="well text-center"><h5><?php echo __('Budgets');?></h5><h3><?php echo (int)$summary['budgets'];?></h3></div></div>
            <div class="col-sm-2"><div class="well text-center"><h5><?php echo __('AR/AP');?></h5><h3><?php echo (int)$summary['arap'];?></h3></div></div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4><?php echo __('Recent Recurring');?></h4>
                <table class="table table-striped table-bordered">
                    <tr><th><?php echo __('Title');?></th><th><?php echo __('Type');?></th><th><?php echo __('Amount');?></th><th><?php echo __('Next Run');?></th></tr>
                    <?php foreach($recentRecurring as $row):?>
                    <tr>
                        <td><?php echo h($row['Ardrecurring']['title']);?></td>
                        <td><?php echo h(ucfirst($row['Ardrecurring']['tx_type']));?></td>
                        <td><?php echo $currency.$this->Number->format($row['Ardrecurring']['amount']);?></td>
                        <td><?php echo (!empty($row['Ardrecurring']['next_run_date']) && $row['Ardrecurring']['next_run_date'] !== '0000-00-00') ? date('d-M-Y', strtotime((string)$row['Ardrecurring']['next_run_date'])) : '-';?></td>
                    </tr>
                    <?php endforeach;?>
                </table>
            </div>
            <div class="col-sm-6">
                <h4><?php echo __('Open AR/AP');?></h4>
                <table class="table table-striped table-bordered">
                    <tr><th><?php echo __('Party');?></th><th><?php echo __('Type');?></th><th><?php echo __('Amount');?></th><th><?php echo __('Due Date');?></th></tr>
                    <?php foreach($recentArap as $row):?>
                    <tr>
                        <td><?php echo h($row['Ardarap']['party_name']);?></td>
                        <td><?php echo h(ucfirst($row['Ardarap']['entry_type']));?></td>
                        <td><?php echo $currency.$this->Number->format($row['Ardarap']['amount']);?></td>
                        <td><?php echo (!empty($row['Ardarap']['due_date']) && $row['Ardarap']['due_date'] !== '0000-00-00') ? date('d-M-Y', strtotime((string)$row['Ardarap']['due_date'])) : '-';?></td>
                    </tr>
                    <?php endforeach;?>
                </table>
            </div>
        </div>
    </div>
</div>
