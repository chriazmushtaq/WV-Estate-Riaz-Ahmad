<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Construction Management');?></h1></div></div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php
            echo $this->Html->link('<span class="fa fa-tasks"></span> '.__('Construction Projects'),array('controller'=>'Ardaccounts','action'=>'constructionprojects',$currentProjectId),array('escape'=>false,'class'=>'btn btn-primary'));
            echo $this->Html->link('<span class="fa fa-briefcase"></span> '.__('Contracts & Work Orders'),array('controller'=>'Ardaccounts','action'=>'contracts',$currentProjectId),array('escape'=>false,'class'=>'btn btn-success'));
            echo $this->Html->link('<span class="fa fa-file-text-o"></span> '.__('Running Bills (IPC)'),array('controller'=>'Ardaccounts','action'=>'contractbills',$currentProjectId),array('escape'=>false,'class'=>'btn btn-info'));
            echo $this->Html->link('<span class="fa fa-users"></span> '.__('Site Labour'),array('controller'=>'Ardaccounts','action'=>'labourcontrol',$currentProjectId),array('escape'=>false,'class'=>'btn btn-warning'));
            echo $this->Html->link('<span class="fa fa-plus"></span> '.__('Add Stock'),array('controller'=>'Ardaccounts','action'=>'stock',$currentProjectId,'?'=>array('new'=>1,'movement'=>'in')),array('escape'=>false,'class'=>'btn btn-default'));
            echo $this->Html->link('<span class="fa fa-arrow-circle-down"></span> '.__('Stock Out'),array('controller'=>'Ardaccounts','action'=>'stockout',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));
            echo $this->Html->link('<span class="fa fa-cubes"></span> '.__('Stock'),array('controller'=>'Ardaccounts','action'=>'stock',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));
            echo $this->Html->link('<span class="fa fa-map-marker"></span> '.__('Stock Stores'),array('controller'=>'Ardaccounts','action'=>'stockstore',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));
            echo $this->Html->link('<span class="fa fa-truck"></span> '.__('Vendors'),array('controller'=>'Ardaccounts','action'=>'vendors',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));
            echo $this->Html->link('<span class="fa fa-file-text-o"></span> '.__('Purchase Orders'),array('controller'=>'Ardaccounts','action'=>'purchaseorders',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));
            echo $this->Html->link('<span class="fa fa-check-square-o"></span> '.__('GRN'),array('controller'=>'Ardaccounts','action'=>'grn',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));
            echo $this->Html->link('<span class="fa fa-list-alt"></span> '.__('Invoices'),array('controller'=>'Ardaccounts','action'=>'purchaseinvoices',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));
            echo $this->Html->link('<span class="fa fa-money"></span> '.__('Payments'),array('controller'=>'Ardaccounts','action'=>'purchasepayments',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));
            echo $this->Html->link('<span class="fa fa-bell"></span> '.__('Reorder Rules'),array('controller'=>'Ardaccounts','action'=>'reorderrules',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));
            echo $this->Html->link('<span class="fa fa-cog"></span> '.__('Settings'),array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>'construction')),array('escape'=>false,'class'=>'btn btn-default'));
            ?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="row">
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Construction Projects');?></h5><h3><?php echo isset($summary['construction_projects']) ? (int)$summary['construction_projects'] : 0;?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Work Orders / Contracts');?></h5><h3><?php echo isset($summary['contracts']) ? (int)$summary['contracts'] : 0;?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Running Bills (IPC)');?></h5><h3><?php echo isset($summary['contract_bills']) ? (int)$summary['contract_bills'] : 0;?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Registered Labour');?></h5><h3><?php echo (int)$summary['labours'];?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Stock Stores');?></h5><h3><?php echo (int)$summary['sites'];?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Stock Entries');?></h5><h3><?php echo (int)$summary['stock'];?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Material Vendors');?></h5><h3><?php echo (int)$summary['vendors'];?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Purchase Orders');?></h5><h3><?php echo (int)$summary['purchase_orders'];?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Goods Received (GRN)');?></h5><h3><?php echo (int)$summary['grn'];?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Purchase Invoices');?></h5><h3><?php echo (int)$summary['purchase_invoices'];?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Purchase Payments');?></h5><h3><?php echo (int)$summary['purchase_payments'];?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Reorder Rules');?></h5><h3><?php echo (int)$summary['reorder_rules'];?></h3></div></div>
        </div>

        <h4><?php echo __('Recent Vendors');?></h4>
        <table class="table table-striped table-bordered">
            <tr><th>#</th><th><?php echo __('Vendor');?></th><th><?php echo __('Phone');?></th><th><?php echo __('Status');?></th></tr>
            <?php $i=1; foreach($recentVendors as $row):?>
            <tr>
                <td><?php echo $i++;?></td>
                <td><?php echo h($row['Ardvendor']['vendor_name']);?></td>
                <td><?php echo h($row['Ardvendor']['phone']);?></td>
                <td><?php echo h(ucfirst($row['Ardvendor']['status']));?></td>
            </tr>
            <?php endforeach;?>
        </table>
    </div>
</div>
