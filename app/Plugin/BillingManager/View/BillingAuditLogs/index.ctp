<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-history"></span> <?php echo __('Billing System Audit Trail & Trash Bin'); ?></h1>
        <p class="description"><?php echo __('Track all billing modifications, deletions, reading adjustments, administrative actions, and restore trashed records.'); ?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <ul class="nav nav-tabs" style="margin-bottom:15px;">
            <li class="active"><a href="#tab-audit" data-toggle="tab"><span class="fa fa-history"></span> <?php echo __('Audit Trail'); ?></a></li>
            <li><a href="#tab-trash" data-toggle="tab"><span class="fa fa-trash"></span> <?php echo __('Trash Bin & Rollback'); ?></a></li>
        </ul>

        <div class="tab-content">
            <!-- Tab 1: Audit Logs -->
            <div class="tab-pane active" id="tab-audit">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" style="vertical-align:middle;font-size:12.5px;">
                        <thead style="background:#f1f5f9;">
                            <tr>
                                <th>#</th>
                                <th><?php echo __('Action'); ?></th>
                                <th><?php echo __('Entity Type / ID'); ?></th>
                                <th><?php echo __('User'); ?></th>
                                <th><?php echo __('Details'); ?></th>
                                <th><?php echo __('Timestamp'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($logs)){ ?>
                                <tr><td colspan="6" class="text-center text-muted" style="padding:20px;"><?php echo __('No audit records found.'); ?></td></tr>
                            <?php } else { foreach($logs as $l){ $lg = $l['BillingAuditLog']; ?>
                                <tr>
                                    <td><?php echo (int)$lg['id']; ?></td>
                                    <td><span class="label label-primary"><?php echo strtoupper(h($lg['action'])); ?></span></td>
                                    <td><?php echo h($lg['entity_type']); ?> #<?php echo (int)$lg['entity_id']; ?></td>
                                    <td><strong><?php echo h($lg['user_id']); ?></strong></td>
                                    <td><small><?php echo h($lg['details']); ?></small></td>
                                    <td><?php echo date('d-M-Y H:i', strtotime((string)$lg['created_at'])); ?></td>
                                </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Trash Bin & Rollback -->
            <div class="tab-pane" id="tab-trash">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" style="vertical-align:middle;font-size:12.5px;">
                        <thead style="background:#f1f5f9;">
                            <tr>
                                <th>#</th>
                                <th><?php echo __('Item Type'); ?></th>
                                <th><?php echo __('Original ID'); ?></th>
                                <th><?php echo __('Title / Reference'); ?></th>
                                <th><?php echo __('Deleted Date'); ?></th>
                                <th><?php echo __('Action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($trashItems)){ ?>
                                <tr><td colspan="6" class="text-center text-muted" style="padding:20px;"><?php echo __('Trash bin is empty. No deleted records available for rollback.'); ?></td></tr>
                            <?php } else { foreach($trashItems as $t){ $tr = $t['BillingTrash']; ?>
                                <tr>
                                    <td><?php echo (int)$tr['id']; ?></td>
                                    <td><span class="label label-warning"><?php echo strtoupper(h($tr['item_type'])); ?></span></td>
                                    <td>#<?php echo (int)$tr['original_id']; ?></td>
                                    <td><strong><?php echo h($tr['title']); ?></strong></td>
                                    <td><?php echo date('d-M-Y H:i', strtotime((string)$tr['deleted_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo $this->Html->url(array('action' => 'rollback', $tr['id'])); ?>" class="btn btn-xs btn-success" onclick="return confirm('Restore this item back to active database?');">
                                            <span class="fa fa-undo"></span> <?php echo __('Rollback / Restore'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
