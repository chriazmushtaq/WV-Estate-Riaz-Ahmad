<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-home"></span> <?php echo __('Edit Society & Project Profile'); ?></h1>
        <p class="description"><?php echo __('Update society branding, contact details, address, and billing codes.'); ?></p>
    </div>
    <div class="pull-right" style="margin-top:10px;">
        <?php echo $this->Html->link('&larr; ' . __('Back to Societies'), array('action' => 'index'), array('escape' => false, 'class' => 'btn btn-default')); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <!-- Quick Auto-Fill Project Dropdown -->
        <div class="well" style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:10px;padding:16px;margin-bottom:24px;max-width:850px;">
            <div class="form-group" style="margin-bottom:0;">
                <label style="font-weight:800;color:#0f172a;margin-bottom:6px;display:block;">
                    <span class="fa fa-magic text-primary"></span> <?php echo __('Select Backend Project to Re-Link:'); ?>
                </label>
                <?php $projectList = !empty($projects) ? $projects : (!empty($agencies) ? $agencies : array()); ?>
                <select id="projectQuickSelect" class="form-control input-lg" style="font-weight:bold;color:#0284c7;">
                    <option value="">-- Choose Project --</option>
                    <?php foreach($projectList as $projKey => $proj){ ?>
                        <option value="<?php echo htmlspecialchars(json_encode($proj)); ?>">
                            <?php echo h($proj['label']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <?php echo $this->Form->create('BillingSociety', array('class' => 'form-horizontal', 'style' => 'max-width:850px;')); ?>
            <?php echo $this->Form->input('id', array('type' => 'hidden')); ?>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Society / Project Name'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('name', array('id' => 'socName', 'class' => 'form-control', 'label' => false, 'div' => false, 'required' => true)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Society Code / Prefix'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('code', array('id' => 'socCode', 'class' => 'form-control', 'label' => false, 'div' => false, 'required' => true)); ?>
                    <span class="help-block" style="font-size:11px;"><?php echo __('Used in bill numbering & utility account prefixes.'); ?></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Office Address'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('address', array('id' => 'socAddress', 'type' => 'textarea', 'rows' => 3, 'class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Helpline Phone'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('phone', array('id' => 'socPhone', 'class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Official Email'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('email', array('id' => 'socEmail', 'class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Logo Image Path / URL'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('logo_url', array('id' => 'socLogoUrl', 'class' => 'form-control', 'label' => false, 'div' => false, 'placeholder' => '/img/site-logo-20260824135207-8538.png')); ?>
                    <span class="help-block" style="font-size:11px;"><?php echo __('Relative image path or external URL for society/project branding on bill vouchers.'); ?></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Status'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('status', array('type' => 'select', 'options' => array('active' => 'Active', 'inactive' => 'Inactive'), 'class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group" style="margin-top:24px;">
                <div class="col-sm-offset-4 col-sm-8">
                    <button type="submit" class="btn btn-success" style="font-weight:800;padding:10px 28px;">
                        <span class="fa fa-save"></span> <?php echo __('Update Society Profile'); ?>
                    </button>
                    <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default"><?php echo __('Cancel'); ?></a>
                </div>
            </div>
        <?php echo $this->Form->end(); ?>

    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('projectQuickSelect') || document.getElementById('agencyQuickSelect');
    if(sel) {
        sel.addEventListener('change', function() {
            if(!this.value) return;
            try {
                var d = JSON.parse(this.value);
                if(d.name) {
                    document.getElementById('socName').value = d.name;
                    var code = d.code ? d.code : d.name.replace(/[^a-zA-Z0-9]/g, '').substr(0, 4).toUpperCase();
                    document.getElementById('socCode').value = code || 'WV';
                }
                if(d.address) document.getElementById('socAddress').value = d.address;
                if(d.phone) document.getElementById('socPhone').value = d.phone;
                if(d.email) document.getElementById('socEmail').value = d.email;
                if(d.logo_url) document.getElementById('socLogoUrl').value = d.logo_url;
            } catch(e) {}
        });
    }
});
</script>
