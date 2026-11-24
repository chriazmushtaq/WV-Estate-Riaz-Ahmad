<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-building-o"></span> <?php echo __('Property Units & Utility Profiles'); ?></h1>
        <p class="description"><?php echo __('Overview of society units, assigned sub-meters, and registered resident consumers.'); ?></p>
    </div>
    <div class="pull-right" style="margin-top:10px;">
        <?php echo $this->Html->link('<span class="fa fa-plus"></span> ' . __('Add Property Unit'), array('action' => 'add'), array('escape' => false, 'class' => 'btn btn-primary', 'style' => 'font-weight:700;')); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <div class="table-responsive">
            <table class="table table-striped table-bordered" style="vertical-align:middle;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th>#</th>
                        <th><?php echo __('Property Title / No'); ?></th>
                        <th><?php echo __('Category'); ?></th>
                        <th><?php echo __('Assigned Consumer'); ?></th>
                        <th><?php echo __('Address'); ?></th>
                        <th><?php echo __('Assigned Meters'); ?></th>
                        <th><?php echo __('Tenant / NDC'); ?></th>
                        <th><?php echo __('Status'); ?></th>
                        <th style="min-width:140px;"><?php echo __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($properties)){ ?>
                        <tr><td colspan="9" class="text-center text-muted" style="padding:24px;"><?php echo __('No property units found.'); ?></td></tr>
                    <?php } else { foreach($properties as $p){ $pr = $p['BillingProperty']; $c = $p['BillingClient']; $meters = $p['BillingMeter']; ?>
                        <tr>
                            <td><?php echo (int)$pr['id']; ?></td>
                            <td>
                                <strong style="color:#0f172a;font-size:14px;"><?php echo h($pr['title']); ?></strong>
                                <div class="text-muted" style="font-size:11px;"><?php echo h($pr['property_no']); ?></div>
                            </td>
                            <td><span class="label label-default"><?php echo h($pr['category']); ?></span></td>
                            <td>
                                <div><strong><?php echo h(!empty($c['name']) ? $c['name'] : '-'); ?></strong></div>
                                <small class="text-muted"><?php echo h(!empty($c['phone']) ? $c['phone'] : ''); ?></small>
                            </td>
                            <td><?php echo h($pr['address']); ?></td>
                            <td>
                                <?php if(!empty($meters)){ foreach($meters as $m){ ?>
                                    <span class="label label-info" style="margin-right:3px;margin-bottom:2px;display:inline-block;">
                                        <span class="fa fa-plug"></span> <?php echo h($m['meter_number']); ?> (<?php echo h($m['utility_type']); ?>)
                                    </span>
                                <?php } } else { ?>
                                    <span class="text-muted" style="font-size:11px;"><?php echo __('No meter'); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if(!empty($pr['tenant_name'])){ ?>
                                    <div style="font-weight:700;font-size:11.5px;color:#0284c7;"><i class="fa fa-user"></i> <?php echo h($pr['tenant_name']); ?></div>
                                <?php } ?>
                                <?php if(!empty($pr['ndc_status']) && $pr['ndc_status'] === 'CLEARED'){ ?>
                                    <span class="label label-success" style="font-size:10px;"><i class="fa fa-certificate"></i> NDC Cleared</span>
                                <?php } else { ?>
                                    <span class="label label-default" style="font-size:10px;">No NDC</span>
                                <?php } ?>
                            </td>
                            <td>
                                <span class="label label-success"><?php echo __('Active'); ?></span>
                            </td>
                            <td>
                                <?php echo $this->Html->link('<span class="fa fa-certificate"></span> NDC', array('action' => 'ndc_clearance', $pr['id']), array('escape' => false, 'class' => 'btn btn-xs btn-info', 'title' => __('Tenant Move-Out Clearance & NDC'))); ?>
                                <?php echo $this->Html->link('<span class="fa fa-pencil"></span>', array('action' => 'edit', $pr['id']), array('escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => __('Edit'))); ?>
                                <?php echo $this->Html->link('<span class="fa fa-trash"></span>', array('action' => 'delete', $pr['id']), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'title' => __('Delete'), 'onclick' => "return confirm('".__('Delete this property unit?')."');")); ?>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
