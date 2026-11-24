<?php
$crmHelpUrl = $this->Html->url('/help/crm_integrations_setup.html');
?><div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('CRM Sync Logs');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-plug"></span>&nbsp;'.__('CRM Settings'), array('controller' => 'Crmintegrations', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-upload"></span>&nbsp;'.__('CRM Bulk Import'), array('controller' => 'Crmintegrations', 'action' => 'bulkimport'), array('escape' => false, 'class' => 'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-random"></span>&nbsp;'.__('CRM Routing'), array('controller' => 'Crmintegrations', 'action' => 'routes'), array('escape' => false, 'class' => 'btn btn-primary'));?>
            <a href="<?php echo $crmHelpUrl;?>" target="_blank" class="btn btn-warning"><span class="fa fa-book"></span>&nbsp;<?php echo __('Integration Help');?></a>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <?php echo $this->Form->create(false, array('type' => 'get', 'class' => 'form-inline', 'style' => 'margin-bottom:15px;'));?>
        <div class="form-group">
            <label for="CrmLogProviderFilter"><?php echo __('Provider');?></label>
            <?php echo $this->Form->select('provider', $providerOptions, array('empty' => __('All Providers'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'CrmLogProviderFilter', 'value' => $selectedProvider));?>
        </div>
        <button type="submit" class="btn btn-success"><span class="fa fa-filter"></span> <?php echo __('Apply');?></button>
        <?php echo $this->Html->link('<span class="fa fa-refresh"></span> '.__('Reset'), array('action' => 'logs'), array('escape' => false, 'class' => 'btn btn-default'));?>
        <?php echo $this->Form->end();?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><?php echo __('Date');?></th>
                        <th><?php echo __('Provider');?></th>
                        <th><?php echo __('Event');?></th>
                        <th><?php echo __('Status');?></th>
                        <th><?php echo __('External Ref');?></th>
                        <th><?php echo __('Agency');?></th>
                        <th><?php echo __('User');?></th>
                        <th><?php echo __('Lead');?></th>
                        <th><?php echo __('Message');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($logRows)){ ?>
                    <tr><td colspan="9" class="text-center"><?php echo __('No CRM sync logs found.');?></td></tr>
                    <?php } ?>
                    <?php foreach((array)$logRows as $logRow){
                        $row = (array)$logRow['CrmIntegrationLog'];
                    ?>
                    <tr>
                        <td><?php echo !empty($row['created']) ? h(date('d-M-Y H:i',strtotime($row['created']))) : '-';?></td>
                        <td><?php echo h(isset($providerOptions[$row['provider']]) ? $providerOptions[$row['provider']] : $row['provider']);?></td>
                        <td><?php echo h(isset($row['event_type']) ? $row['event_type'] : '');?></td>
                        <td><?php echo h(isset($row['status']) ? $row['status'] : '');?></td>
                        <td><?php echo h(isset($row['external_ref']) ? $row['external_ref'] : '');?></td>
                        <td><?php echo h(isset($logRow['Project']['name']) ? $logRow['Project']['name'] : '');?></td>
                        <td><?php echo h(isset($logRow['User']['name']) ? $logRow['User']['name'] : '');?></td>
                        <td><?php if(!empty($row['lead_id'])){ ?><?php echo $this->Html->link('#'.(int)$row['lead_id'], array('controller' => 'Leads', 'action' => 'leadview', (int)$row['lead_id']));?><?php } ?></td>
                        <td style="max-width:360px; white-space:normal;"><?php echo h(isset($row['message']) ? $row['message'] : '');?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if(!empty($logRows)){ ?>
        <div class="text-center">
            <ul class="pagination">
                <?php echo $this->Paginator->prev('&laquo;', array('tag' => 'li', 'escape' => false), '<a href="javascript:void(0);">&laquo;</a>', array('tag' => 'li', 'escape' => false, 'class' => 'disabled'));?>
                <?php echo $this->Paginator->numbers(array('tag' => 'li', 'currentTag' => 'a', 'currentClass' => 'active', 'separator' => ''));?>
                <?php echo $this->Paginator->next('&raquo;', array('tag' => 'li', 'escape' => false), '<a href="javascript:void(0);">&raquo;</a>', array('tag' => 'li', 'escape' => false, 'class' => 'disabled'));?>
            </ul>
        </div>
        <?php } ?>
    </div>
</div>


