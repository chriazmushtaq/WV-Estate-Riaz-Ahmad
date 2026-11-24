<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-home"></span> <?php echo __('Society & Project Profiles'); ?></h1>
        <p class="description"><?php echo __('Manage society branding, contact details, codes, and inventory stats for White Villas & Awami Block.'); ?></p>
    </div>
    <div class="pull-right" style="margin-top:10px;">
        <?php echo $this->Html->link('<span class="fa fa-plus"></span> ' . __('Add New Society'), array('action' => 'add'), array('escape' => false, 'class' => 'btn btn-primary', 'style' => 'font-weight:700;')); ?>
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
                        <th><?php echo __('Society / Project Name'); ?></th>
                        <th><?php echo __('Code'); ?></th>
                        <th><?php echo __('Office Address'); ?></th>
                        <th><?php echo __('Helpline Phone'); ?></th>
                        <th><?php echo __('Inventory Stats'); ?></th>
                        <th><?php echo __('Status'); ?></th>
                        <th style="min-width:110px;"><?php echo __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($socData as $row){ $s = $row['society']; ?>
                        <tr>
                            <td><?php echo (int)$s['id']; ?></td>
                            <td>
                                <strong style="color:#0f172a;font-size:15px;"><?php echo h($s['name']); ?></strong>
                                <small class="text-muted" style="display:block;">Project Society</small>
                            </td>
                            <td><span class="label label-primary" style="font-size:12px;padding:4px 8px;"><?php echo h($s['code']); ?></span></td>
                            <td><?php echo h($s['address']); ?></td>
                            <td><strong><?php echo h($s['phone']); ?></strong></td>
                            <td>
                                <span class="label label-info" style="margin-right:3px;"><span class="fa fa-building"></span> <?php echo $row['props_count']; ?> Units</span>
                                <span class="label label-warning" style="margin-right:3px;"><span class="fa fa-plug"></span> <?php echo $row['meters_count']; ?> Meters</span>
                                <span class="label label-success"><span class="fa fa-file-text"></span> <?php echo $row['bills_count']; ?> Bills</span>
                            </td>
                            <td><span class="label label-success"><?php echo ucfirst(h($s['status'])); ?></span></td>
                            <td>
                                <?php echo $this->Html->link('<span class="fa fa-pencil"></span> ' . __('Edit'), array('action' => 'edit', $s['id']), array('escape' => false, 'class' => 'btn btn-xs btn-default', 'style' => 'font-weight:700;')); ?>
                                <?php if($s['id'] > 2){ ?>
                                    <?php echo $this->Html->link('<span class="fa fa-trash"></span>', array('action' => 'delete', $s['id']), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'title' => __('Delete'), 'onclick' => "return confirm('".__('Delete this society profile?')."');")); ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
