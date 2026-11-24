<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-users"></span> <?php echo __('Billing Consumers & Resident Directory'); ?></h1>
        <p class="description"><?php echo __('Manage resident billing accounts, linked properties, contact information, and billing ledgers.'); ?></p>
    </div>
    <div class="pull-right" style="margin-top:10px;">
        <?php echo $this->Html->link('<span class="fa fa-user-plus"></span> ' . __('Add New Consumer'), array('action' => 'add'), array('escape' => false, 'class' => 'btn btn-primary', 'style' => 'font-weight:700;')); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <!-- Search Form -->
        <div class="well" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:18px;">
            <form method="get" action="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="form-inline">
                <input type="text" name="q" value="<?php echo isset($this->request->query['q']) ? h($this->request->query['q']) : ''; ?>" placeholder="<?php echo __('Search name, phone, CNIC...'); ?>" class="form-control input-sm" style="min-width:250px;margin-right:8px;" />
                <button type="submit" class="btn btn-sm btn-primary"><span class="fa fa-search"></span> <?php echo __('Search'); ?></button>
                <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-sm btn-default"><?php echo __('Reset'); ?></a>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered" style="vertical-align:middle;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th>#</th>
                        <th><?php echo __('Consumer Name'); ?></th>
                        <th><?php echo __('Phone / WhatsApp'); ?></th>
                        <th><?php echo __('Email'); ?></th>
                        <th><?php echo __('Address'); ?></th>
                        <th><?php echo __('Linked App Client'); ?></th>
                        <th style="min-width:140px;"><?php echo __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($clients)){ ?>
                        <tr><td colspan="7" class="text-center text-muted" style="padding:24px;"><?php echo __('No consumers found.'); ?></td></tr>
                    <?php } else { foreach($clients as $c){ $cl = $c['BillingClient']; $appC = $c['Client']; ?>
                        <tr>
                            <td><?php echo (int)$cl['id']; ?></td>
                            <td>
                                <strong style="color:#0f172a;font-size:13.5px;"><?php echo h($cl['name']); ?></strong>
                                <?php if(!empty($cl['cnic'])){ ?>
                                    <small class="text-muted" style="display:block;"><?php echo __('CNIC'); ?>: <?php echo h($cl['cnic']); ?></small>
                                <?php } ?>
                            </td>
                            <td>
                                <div><span class="fa fa-phone text-primary"></span> <?php echo h($cl['phone']); ?></div>
                                <?php if(!empty($cl['whatsapp']) && $cl['whatsapp'] !== $cl['phone']){ ?>
                                    <small class="text-success"><span class="fa fa-whatsapp"></span> <?php echo h($cl['whatsapp']); ?></small>
                                <?php } ?>
                            </td>
                            <td><?php echo !empty($cl['email']) ? h($cl['email']) : '<span class="text-muted">-</span>'; ?></td>
                            <td><?php echo h($cl['address']); ?></td>
                            <td>
                                <?php if(!empty($appC['id'])){ ?>
                                    <span class="label label-success"><span class="fa fa-check"></span> <?php echo h($appC['name']); ?> (ID: <?php echo $appC['id']; ?>)</span>
                                <?php } else { ?>
                                    <span class="label label-default"><?php echo __('Standalone'); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php echo $this->Html->link('<span class="fa fa-book"></span> ' . __('Ledger'), array('action' => 'ledger', $cl['id']), array('escape' => false, 'class' => 'btn btn-xs btn-info', 'style' => 'font-weight:700;')); ?>
                                <?php echo $this->Html->link('<span class="fa fa-pencil"></span>', array('action' => 'edit', $cl['id']), array('escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => __('Edit'))); ?>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
