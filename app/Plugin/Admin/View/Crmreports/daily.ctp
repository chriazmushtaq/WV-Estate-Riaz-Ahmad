<?php
$sysDay = isset($sysDay) ? $sysDay : 'd';
$sysMonth = isset($sysMonth) ? $sysMonth : 'm';
$sysYear = isset($sysYear) ? $sysYear : 'Y';
$dateSep = isset($dateSep) ? $dateSep : '-';
$displayDate = date($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, strtotime($today));
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('CRM Daily Progress');?></h1>
        <p class="description"><?php echo __('Date');?>: <?php echo h($displayDate);?> | <?php echo h($scopeLabel);?></p>
    </div>
</div>

<div class="row">
    <div class="col-sm-3">
        <div class="panel panel-success">
            <div class="panel-body text-center">
                <h4><?php echo __('Leads Today');?></h4>
                <div class="h2"><?php echo (int)$leadsToday;?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-info">
            <div class="panel-body text-center">
                <h4><?php echo __('Contacts Today');?></h4>
                <div class="h2"><?php echo (int)$contactsToday;?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-primary">
            <div class="panel-body text-center">
                <h4><?php echo __('Contacted Today');?></h4>
                <div class="h2"><?php echo (int)$contactedToday;?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-warning">
            <div class="panel-body text-center">
                <h4><?php echo __('Follow Ups Today');?></h4>
                <div class="h2"><?php echo (int)$followUpsToday;?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong><?php echo __('Today Leads');?></strong>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo __('#');?></th>
                                <th><?php echo __('Name');?></th>
                                <th><?php echo __('Mobile');?></th>
                                <th><?php echo __('Email');?></th>
                                <th><?php echo __('Created');?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recentLeads)){ ?>
                                <tr><td colspan="5" class="text-center"><?php echo __('No leads found today.');?></td></tr>
                            <?php } else { $rowNo=1; foreach($recentLeads as $row){ ?>
                                <tr>
                                    <td><?php echo $rowNo++;?></td>
                                    <td><?php echo h($row['Lead']['lead_name']);?></td>
                                    <td><?php echo h($row['Lead']['lead_mobile']);?></td>
                                    <td><?php echo h($row['Lead']['lead_email']);?></td>
                                    <td><?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $row['Lead']['created']);?></td>
                                </tr>
                            <?php }} ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong><?php echo __('Today Contacts');?></strong>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo __('#');?></th>
                                <th><?php echo __('Name');?></th>
                                <th><?php echo __('Mobile');?></th>
                                <th><?php echo __('Email');?></th>
                                <th><?php echo __('Created');?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recentContacts)){ ?>
                                <tr><td colspan="5" class="text-center"><?php echo __('No contacts found today.');?></td></tr>
                            <?php } else { $rowNo=1; foreach($recentContacts as $row){ ?>
                                <tr>
                                    <td><?php echo $rowNo++;?></td>
                                    <td><?php echo h($row['Contact']['name']);?></td>
                                    <td><?php echo h($row['Contact']['mobile']);?></td>
                                    <td><?php echo h($row['Contact']['email']);?></td>
                                    <td><?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $row['Contact']['created']);?></td>
                                </tr>
                            <?php }} ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
