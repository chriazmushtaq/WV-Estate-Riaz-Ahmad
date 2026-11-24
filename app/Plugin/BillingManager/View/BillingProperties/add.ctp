<?php
echo $this->Html->css('/css/select2/select2.css');
echo $this->Html->css('/css/select2/select2-bootstrap.css');
echo $this->Html->script('/js/select2.min.js');
?>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-building-o"></span> <?php echo __('Add New Property Unit'); ?></h1>
        <p class="description"><?php echo __('Register a society property unit linked directly to real estate deals and property owners.'); ?></p>
    </div>
    <div class="pull-right" style="margin-top:10px;">
        <?php echo $this->Html->link('&larr; ' . __('Back to Property Units'), array('action' => 'index'), array('escape' => false, 'class' => 'btn btn-default')); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <?php echo $this->Form->create('BillingProperty', array('class' => 'form-horizontal', 'style' => 'max-width:850px;')); ?>
            <input type="hidden" name="data[BillingProperty][deal_client_id]" id="dealClientId" value="" />

            <!-- Project / Agency Selection -->
            <div class="form-group" style="background:#f8fafc;padding:14px 0;border-radius:8px;margin-bottom:18px;border:1px solid #e2e8f0;">
                <label class="col-sm-4 control-label" style="color:#0f172a;font-weight:bold;font-size:13.5px;">
                    <span class="fa fa-sitemap text-primary"></span> <?php echo __('Project / Agency / Society'); ?> <span class="text-danger">*</span>
                </label>
                <div class="col-sm-8">
                    <select name="data[BillingProperty][society_id]" id="societySelect" class="form-control select2-control" style="font-weight:bold;color:#0284c7;" required>
                        <option value=""><?php echo __('-- Select Project / Society --'); ?></option>
                        <?php foreach($societies as $sId => $sName){ ?>
                            <option value="<?php echo $sId; ?>" <?php if($sId == 1) echo 'selected'; ?>><?php echo h($sName); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <!-- Property Title / Name (Searchable Dropdown + Auto-fill) -->
            <div class="form-group">
                <label class="col-sm-4 control-label" style="color:#0f172a;font-weight:bold;">
                    <span class="fa fa-home text-success"></span> <?php echo __('Property Title / Name'); ?> <span class="text-danger">*</span>
                </label>
                <div class="col-sm-8">
                    <div style="position:relative;">
                        <select id="propSelectDropdown" class="form-control select2-control" style="font-weight:700;color:#0f172a;width:100%;">
                            <option value=""><?php echo __('-- Search & Choose Property from Selected Project/Agency --'); ?></option>
                        </select>
                    </div>
                    <div style="margin-top:6px;display:flex;align-items:center;justify-content:space-between;">
                        <small class="text-muted"><span class="fa fa-search"></span> <?php echo __('Type property code, plot no, or owner name to search.'); ?></small>
                        <button type="button" class="btn btn-xs btn-default" id="toggleCustomNameBtn" title="Type custom name manually">
                            <span class="fa fa-pencil"></span> <?php echo __('Type Custom Title'); ?>
                        </button>
                    </div>
                    <input type="text" name="data[BillingProperty][title]" id="propTitleInput" class="form-control" placeholder="e.g. 4 Marla Double Story Villa" style="display:none;margin-top:8px;font-weight:bold;" required />
                    <div id="dealStatusBadge" style="margin-top:8px;display:none;"></div>
                </div>
            </div>

            <!-- Property Number / Code -->
            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Property Number / Code'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('property_no', array('id' => 'propNo', 'class' => 'form-control', 'placeholder' => 'e.g. B1-GF#1 or Plot 31', 'label' => false, 'div' => false, 'required' => true)); ?>
                </div>
            </div>

            <!-- Assigned Resident / Owner (Searchable Dropdown) -->
            <div class="form-group" style="background:#eff6ff;padding:14px 0;border-radius:8px;border:1px solid #bfdbfe;margin-bottom:18px;">
                <label class="col-sm-4 control-label" style="color:#1e40af;font-weight:bold;font-size:13.5px;">
                    <span class="fa fa-user"></span> <?php echo __('Assigned Resident / Owner'); ?>
                </label>
                <div class="col-sm-8">
                    <select name="data[BillingProperty][billing_client_id]" id="propClient" class="form-control select2-control" style="font-weight:bold;width:100%;">
                        <option value=""><?php echo __('-- Search & Select Consumer / Deal Owner --'); ?></option>
                    </select>
                    <small class="text-muted" id="clientHelpText" style="display:block;margin-top:6px;">
                        <?php echo __('Auto-fetches deal owner if property is sold. If no deal exists, lists all contacts for this project.'); ?>
                    </small>
                </div>
            </div>

            <!-- Category -->
            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Category'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('category', array('id' => 'propCategory', 'type' => 'select', 'options' => array('Residential' => 'Residential', 'Commercial' => 'Commercial', 'Apartment' => 'Apartment', 'Plot' => 'Plot'), 'class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <!-- Main Feeder / Transformer Station -->
            <div class="form-group" style="background:#fefce8;padding:12px 0;border-radius:8px;border:1px solid #fef08a;margin-bottom:18px;">
                <label class="col-sm-4 control-label" style="color:#854d0e;font-weight:bold;">
                    <span class="fa fa-flash text-warning"></span> <?php echo __('Feeder / Transformer'); ?>
                </label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('feeder_id', array('type' => 'select', 'options' => !empty($feeders) ? $feeders : array(), 'empty' => __('-- Select Main Feeder Station --'), 'class' => 'form-control', 'label' => false, 'div' => false, 'style' => 'font-weight:bold;color:#0f172a;')); ?>
                    <small class="text-muted" style="display:block;margin-top:4px;">
                        <?php echo __('Assigns this property unit to a specific transformer for line loss and power theft detection.'); ?>
                    </small>
                </div>
            </div>

            <!-- Address -->
            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Address / Location'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('address', array('id' => 'propAddress', 'class' => 'form-control', 'placeholder' => 'e.g. Awami Block, White Villas Lahore', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group" style="margin-top:28px;">
                <div class="col-sm-offset-4 col-sm-8">
                    <button type="submit" class="btn btn-success btn-lg" style="font-weight:800;padding:10px 36px;">
                        <span class="fa fa-save"></span> <?php echo __('Save Property Unit'); ?>
                    </button>
                    <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default btn-lg"><?php echo __('Cancel'); ?></a>
                </div>
            </div>
        <?php echo $this->Form->end(); ?>

    </div>
</div>

<script type="text/javascript">
var allBackendProps = <?php echo json_encode(!empty($backendProperties) ? $backendProperties : array()); ?>;
var allAgencyContacts = <?php echo json_encode(!empty($agencyContacts) ? $agencyContacts : array()); ?>;
var isCustomMode = false;

function initSelect2() {
    if(window.jQuery && jQuery().select2) {
        jQuery('#societySelect').select2({ placeholder: '-- Select Project / Society --', allowClear: true });
        jQuery('#propSelectDropdown').select2({ placeholder: '-- Search & Choose Property --', allowClear: true });
        jQuery('#propClient').select2({ placeholder: '-- Search & Select Owner/Contact --', allowClear: true });
    }
}

function refreshProjectData() {
    var socId = document.getElementById('societySelect').value;
    var propSelect = document.getElementById('propSelectDropdown');

    // 1. Populate Properties for this Project
    propSelect.innerHTML = '<option value="">-- Search & Choose Property from Selected Project/Agency --</option>';
    if(socId) {
        var filteredProps = allBackendProps.filter(function(p) {
            return p.society_id == socId;
        });

        filteredProps.forEach(function(p) {
            var opt = document.createElement('option');
            opt.value = p.name;
            var dealTag = p.has_deal ? (' [Deal: ' + p.deal_client_name + ']') : ' [No Deal / Available]';
            opt.textContent = p.name + (p.area ? ' (' + p.area + 'M)' : '') + dealTag;
            opt.dataset.propName = p.name;
            opt.dataset.propAddress = p.address || p.project_name || '';
            opt.dataset.propArea = p.area || '';
            opt.dataset.hasDeal = p.has_deal;
            opt.dataset.dealClientName = p.deal_client_name || '';
            opt.dataset.dealClientId = p.deal_client_id || '';
            opt.dataset.billingClientId = p.billing_client_id || '';
            propSelect.appendChild(opt);
        });
    }

    // 2. Populate Contacts for this Project
    populateContacts(socId, null, false);

    if(window.jQuery && jQuery().select2) {
        jQuery('#propSelectDropdown').trigger('change.select2');
    }
}

function populateContacts(socId, autoSelectId, onlyUndealed) {
    var clientSelect = document.getElementById('propClient');
    clientSelect.innerHTML = '<option value="">-- Search & Select Consumer / Contact --</option>';

    var parsedSocId = parseInt(socId);
    var primaryContacts = [];
    var otherContacts = [];

    allAgencyContacts.forEach(function(c) {
        var isForThisSoc = c.society_ids && c.society_ids.indexOf(parsedSocId) !== -1;
        if(isForThisSoc) {
            if(!onlyUndealed || c.is_undealed == 1) {
                primaryContacts.push(c);
            }
        } else {
            if(!onlyUndealed) {
                otherContacts.push(c);
            }
        }
    });

    var renderList = primaryContacts.length > 0 ? primaryContacts : allAgencyContacts;

    renderList.forEach(function(c) {
        var opt = document.createElement('option');
        opt.value = c.billing_client_id ? c.billing_client_id : '';
        var statusTag = c.is_undealed ? ' [Undealed Contact]' : ' [Deal Client]';
        opt.textContent = c.name + (c.phone ? ' (' + c.phone + ')' : '') + statusTag;
        opt.dataset.appClientId = c.id;
        opt.dataset.bClientId = c.billing_client_id || '';
        if(autoSelectId && (c.id == autoSelectId || c.billing_client_id == autoSelectId)) {
            opt.selected = true;
        }
        clientSelect.appendChild(opt);
    });

    // If other contacts exist, add them too so all clients are searchable
    if(otherContacts.length > 0 && primaryContacts.length > 0 && !onlyUndealed) {
        otherContacts.forEach(function(c) {
            var opt = document.createElement('option');
            opt.value = c.billing_client_id ? c.billing_client_id : '';
            opt.textContent = c.name + (c.phone ? ' (' + c.phone + ')' : '') + ' [Other Project]';
            opt.dataset.appClientId = c.id;
            opt.dataset.bClientId = c.billing_client_id || '';
            if(autoSelectId && (c.id == autoSelectId || c.billing_client_id == autoSelectId)) {
                opt.selected = true;
            }
            clientSelect.appendChild(opt);
        });
    }

    if(window.jQuery && jQuery().select2) {
        jQuery('#propClient').trigger('change.select2');
    }
}

document.getElementById('societySelect').addEventListener('change', function() {
    refreshProjectData();
});

document.getElementById('propSelectDropdown').addEventListener('change', function() {
    var selectedOpt = this.options[this.selectedIndex];
    var propInput = document.getElementById('propTitleInput');
    var propNo = document.getElementById('propNo');
    var propAddress = document.getElementById('propAddress');
    var badge = document.getElementById('dealStatusBadge');
    var dealClientInput = document.getElementById('dealClientId');
    var clientSelect = document.getElementById('propClient');
    var socId = document.getElementById('societySelect').value;

    if(selectedOpt && selectedOpt.value) {
        propInput.value = selectedOpt.dataset.propName || selectedOpt.value;
        propNo.value = selectedOpt.dataset.propName || selectedOpt.value;
        propAddress.value = selectedOpt.dataset.propAddress || '';

        // Deal Check
        if(selectedOpt.dataset.hasDeal == "1" && selectedOpt.dataset.dealClientName) {
            badge.style.display = 'block';
            badge.innerHTML = '<span class="label label-success" style="font-size:12.5px;padding:5px 12px;font-weight:bold;display:inline-block;"><span class="fa fa-check-circle"></span> Deal Owner Auto-Linked from Deals: ' + selectedOpt.dataset.dealClientName + '</span>';
            
            dealClientInput.value = selectedOpt.dataset.dealClientId || '';
            
            populateContacts(socId, selectedOpt.dataset.dealClientId, false);
            
            var found = false;
            for(var i=0; i<clientSelect.options.length; i++) {
                if(clientSelect.options[i].selected) {
                    found = true;
                    break;
                }
            }
            if(!found) {
                var newOpt = document.createElement('option');
                newOpt.value = selectedOpt.dataset.billingClientId || '';
                newOpt.textContent = selectedOpt.dataset.dealClientName + ' (Auto-Linked Deal Owner)';
                newOpt.selected = true;
                clientSelect.insertBefore(newOpt, clientSelect.options[1]);
            }
        } else {
            badge.style.display = 'block';
            badge.innerHTML = '<span class="label label-info" style="font-size:12.5px;padding:5px 12px;font-weight:bold;display:inline-block;"><span class="fa fa-info-circle"></span> No Deal Recorded Yet in Deals (Showing all undealed contacts for this Project)</span>';
            dealClientInput.value = '';
            populateContacts(socId, null, true);
        }
    } else {
        badge.style.display = 'none';
        dealClientInput.value = '';
        populateContacts(socId, null, false);
    }

    if(window.jQuery && jQuery().select2) {
        jQuery('#propClient').trigger('change.select2');
    }
});

// Toggle custom name input
document.getElementById('toggleCustomNameBtn').addEventListener('click', function() {
    isCustomMode = !isCustomMode;
    var dropdown = document.getElementById('propSelectDropdown');
    var input = document.getElementById('propTitleInput');
    var select2Container = document.querySelector('.select2-container');
    if(isCustomMode) {
        if(select2Container) select2Container.style.display = 'none';
        dropdown.style.display = 'none';
        input.style.display = 'block';
        input.focus();
        this.innerHTML = '<span class="fa fa-list"></span> Searchable List';
    } else {
        if(select2Container) select2Container.style.display = 'block';
        dropdown.style.display = 'block';
        input.style.display = 'none';
        this.innerHTML = '<span class="fa fa-pencil"></span> Type Custom Title';
    }
});

// Form submit sync
document.querySelector('form').addEventListener('submit', function() {
    var dropdown = document.getElementById('propSelectDropdown');
    var input = document.getElementById('propTitleInput');
    if(!isCustomMode && dropdown.value) {
        input.value = dropdown.value;
    }
});

// Initial load
document.addEventListener('DOMContentLoaded', function() {
    refreshProjectData();
    initSelect2();
});
refreshProjectData();
initSelect2();
</script>
