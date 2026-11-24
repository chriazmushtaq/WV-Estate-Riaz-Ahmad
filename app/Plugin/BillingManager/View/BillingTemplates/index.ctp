<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-print"></span> <?php echo __('Bill Print Templates & Designer'); ?></h1>
        <p class="description"><?php echo __('Choose, customize, and preview responsive HTML invoice formats and bank vouchers.'); ?></p>
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
                        <th><?php echo __('Template Name'); ?></th>
                        <th><?php echo __('Slug'); ?></th>
                        <th><?php echo __('Template Type'); ?></th>
                        <th><?php echo __('Default'); ?></th>
                        <th style="min-width:160px;"><?php echo __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($templates)){ ?>
                        <tr><td colspan="6" class="text-center text-muted" style="padding:24px;"><?php echo __('No templates configured.'); ?></td></tr>
                    <?php } else { foreach($templates as $t){ $tm = $t['BillingTemplate']; ?>
                        <tr>
                            <td><?php echo (int)$tm['id']; ?></td>
                            <td><strong style="font-size:14px;color:#0f172a;"><?php echo h($tm['name']); ?></strong></td>
                            <td><code><?php echo h($tm['slug']); ?></code></td>
                            <td><span class="label label-default"><?php echo h($tm['template_type']); ?></span></td>
                            <td>
                                <?php if(!empty($tm['is_default'])){ ?>
                                    <span class="label label-success"><span class="fa fa-check-circle"></span> <?php echo __('Active Default'); ?></span>
                                <?php } else { ?>
                                    <span class="text-muted"><?php echo __('Optional'); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <a href="<?php echo $this->Html->url(array('action' => 'preview', $tm['id'])); ?>" target="_blank" class="btn btn-xs btn-info" style="font-weight:700;margin-right:4px;">
                                    <span class="fa fa-eye"></span> <?php echo __('Preview / View Print'); ?>
                                </a>
                                <?php if(empty($tm['is_default'])){ ?>
                                    <?php echo $this->Html->link('<span class="fa fa-check"></span> ' . __('Set Default'), array('action' => 'set_default', $tm['id']), array('escape' => false, 'class' => 'btn btn-xs btn-primary')); ?>
                                <?php } ?>
                                <?php echo $this->Html->link('<span class="fa fa-pencil"></span> ' . __('Edit'), array('action' => 'edit', $tm['id']), array('escape' => false, 'class' => 'btn btn-xs btn-default')); ?>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
