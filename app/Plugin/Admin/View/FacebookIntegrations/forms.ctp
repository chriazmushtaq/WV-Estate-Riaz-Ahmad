<?php
if(empty($availableForms)) {
    $availableForms = array(
        'White ICE Cottages' => array(
            '419864743398043' => 'Plot or Cottage Booking Form (ID: 419864743398043)',
            '1415106342586333' => 'Rent Offer (ID: 1415106342586333)',
            '881336549225622' => 'White ICE 3.5 Marla (ID: 881336549225622)',
            '671367367203657' => '6Marla New Cottage Compaing (ID: 671367367203657)',
            '568310655456652' => 'White ice 20/05/2023 (ID: 568310655456652)',
            '466766848360572' => 'Karachi Add (ID: 466766848360572)',
            '399681851598922' => 'Book Cottage (ID: 399681851598922)'
        ),
        'White Alpine Lodges' => array(
            '2038407180131077' => "White Alpine Lodges's form (ID: 2038407180131077)",
            '1648793206760172' => 'White Alpine Lodges Naran A (ID: 1648793206760172)'
        )
    );
    $formsMetaMap = array(
        '419864743398043' => array('name' => 'Plot or Cottage Booking Form'),
        '1415106342586333' => array('name' => 'Rent Offer'),
        '881336549225622' => array('name' => 'White ICE 3.5 Marla'),
        '671367367203657' => array('name' => '6Marla New Cottage Compaing'),
        '568310655456652' => array('name' => 'White ice 20/05/2023'),
        '466766848360572' => array('name' => 'Karachi Add'),
        '399681851598922' => array('name' => 'Book Cottage'),
        '2038407180131077' => array('name' => "White Alpine Lodges's form"),
        '1648793206760172' => array('name' => 'White Alpine Lodges Naran A')
    );
}
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-list-alt text-primary"></i> <?php echo __('Facebook Lead Form to Project Mapping');?></h1>
        <p class="description"><?php echo __('Select any form from your connected Facebook Pages and map it directly to target Projects, Pipeline Stages, or Dedicated Agents.');?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-heading" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div class="btn-group" style="display:flex;flex-wrap:wrap;gap:4px;">
            <?php echo $this->Html->link('<i class="fa fa-dashboard"></i> '.__('Overview'), array('action'=>'index'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-cogs"></i> '.__('API Accounts'), array('action'=>'settings'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-users"></i> '.__('Agent Looper'), array('action'=>'agents'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-list-alt"></i> '.__('Form Mapping'), array('action'=>'forms'), array('class'=>'btn btn-primary', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-bell"></i> '.__('Notifications'), array('action'=>'notifications'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-whatsapp"></i> '.__('WhatsApp API'), array('action'=>'whatsapp'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-history"></i> '.__('Sync Logs'), array('action'=>'logs'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-book"></i> '.__('Documentation'), array('action'=>'docs'), array('class'=>'btn btn-default', 'escape'=>false));?>
        </div>

        <div>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#mapFormModal" style="background:#1877f2;border-color:#1877f2;font-weight:700;">
                <i class="fa fa-plus-circle"></i> <?php echo __('Map New Facebook Form');?>
            </button>
        </div>
    </div>

    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <!-- Quick Summary Box -->
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <div>
                <strong style="color:#1e40af;font-size:14px;"><i class="fa fa-info-circle"></i> <?php echo __('Auto-Discovered Forms from Connected Pages:');?></strong>
                <p style="margin:4px 0 0 0;font-size:12px;color:#1e3a8a;">
                    <?php 
                        $totalDiscovered = 0;
                        if(!empty($availableForms)) {
                            foreach($availableForms as $pg => $fList) { $totalDiscovered += count($fList); }
                        }
                        echo sprintf(__('We automatically found %d active lead forms across your connected Facebook Pages ready for 1-click mapping.'), $totalDiscovered);
                    ?>
                </p>
            </div>
            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#mapFormModal" style="font-weight:700;">
                <i class="fa fa-plus"></i> <?php echo __('Choose Form from Dropdown');?>
            </button>
        </div>

        <div class="panel panel-default" style="border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
            <div class="panel-heading" style="background:#f8fafc;font-weight:700;display:flex;justify-content:space-between;align-items:center;">
                <span><i class="fa fa-link text-primary"></i> <?php echo __('Configured Form Mappings');?></span>
                <span class="badge badge-info"><?php echo count($mappings);?> <?php echo __('Mapped');?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;vertical-align:middle;">
                    <thead>
                        <tr style="background:#f1f5f9;color:#334155;">
                            <th><?php echo __('Facebook Form ID');?></th>
                            <th><?php echo __('Form Friendly Name');?></th>
                            <th><?php echo __('Target Agency / Project');?></th>
                            <th><?php echo __('Default Pipeline Status');?></th>
                            <th><?php echo __('Dedicated Agent');?></th>
                            <th><?php echo __('Status');?></th>
                            <th><?php echo __('Created Date');?></th>
                            <th style="width:90px;text-align:center;"><?php echo __('Actions');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($mappings)){ foreach($mappings as $m){ ?>
                        <tr>
                            <td><code style="font-size:12px;font-weight:700;"><?php echo h($m['FacebookFormMapping']['facebook_form_id']);?></code></td>
                            <td><strong style="font-size:14px;color:#0f172a;"><?php echo h($m['FacebookFormMapping']['form_name']);?></strong></td>
                            <td>
                                <?php if(!empty($m['Project']['name'])){ ?>
                                    <span class="badge badge-primary" style="background:#3b82f6;"><?php echo h($m['Project']['name']);?></span>
                                <?php } else { ?>
                                    <span class="text-muted"><?php echo __('Default / All Projects');?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if(!empty($m['Status']['name'])){ ?>
                                    <span class="label label-info"><?php echo h($m['Status']['name']);?></span>
                                <?php } else { ?>
                                    <span class="text-muted"><?php echo __('Default (New Lead)');?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php 
                                    $agId = !empty($m['FacebookFormMapping']['default_agent_id']) ? (int)$m['FacebookFormMapping']['default_agent_id'] : 0;
                                    if($agId > 0 && isset($agents[$agId])) {
                                        echo '<span class="label label-success">' . h($agents[$agId]) . '</span>';
                                    } else {
                                        echo '<span class="text-muted"><i class="fa fa-refresh"></i> ' . __('Auto Agent Looper') . '</span>';
                                    }
                                ?>
                            </td>
                            <td><span class="label label-success"><i class="fa fa-check"></i> <?php echo __('Active');?></span></td>
                            <td style="font-size:12px;color:#64748b;"><?php echo h($m['FacebookFormMapping']['created']);?></td>
                            <td style="text-align:center;">
                                <?php echo $this->Html->link('<i class="fa fa-trash"></i>', array('action'=>'delete_form', $m['FacebookFormMapping']['id']), array('class'=>'btn btn-danger btn-xs', 'escape'=>false, 'confirm'=>__('Are you sure you want to remove this form mapping?')));?>
                            </td>
                        </tr>
                        <?php }} else { ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted" style="padding:40px;">
                                <i class="fa fa-list-alt fa-3x" style="color:#94a3b8;margin-bottom:10px;"></i><br/>
                                <p style="font-size:14px;font-weight:600;"><?php echo __('No specific form mappings added yet.');?></p>
                                <p style="font-size:12px;color:#64748b;"><?php echo __('Incoming leads will automatically use the parent Page project and Agent Looper settings. Click "Map New Facebook Form" to customize specific forms.');?></p>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODERN MODAL POPUP FOR FORM MAPPING ================= -->
<div class="modal fade" id="mapFormModal" tabindex="-1" role="dialog" aria-labelledby="mapFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.2);">
            <?php echo $this->Form->create('FacebookFormMapping', array('url'=>array('plugin'=>'admin', 'controller'=>'facebook_integrations', 'action'=>'forms')));?>
            <div class="modal-header" style="background:#1877f2;color:#fff;padding:16px 24px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="mapFormModalLabel" style="font-weight:800;">
                    <i class="fa fa-link"></i> <?php echo __('Map Facebook Lead Form to Project & Pipeline');?>
                </h4>
            </div>

            <div class="modal-body" style="padding:24px;">
                <!-- Form Selection Dropdown -->
                <div class="form-group" style="margin-bottom:18px;">
                    <label style="font-weight:800;color:#0f172a;font-size:13px;">
                        <i class="fa fa-facebook-square text-primary"></i> <?php echo __('Select Facebook Form from Linked Pages:');?> *
                    </label>
                    <select name="data[FacebookFormMapping][facebook_form_id]" id="selectFacebookForm" class="form-control" required style="font-size:13px;height:42px;border-radius:8px;">
                        <option value=""><?php echo __('-- Choose Form from Connected Facebook Pages --');?></option>
                        <?php if(!empty($availableForms)){ foreach($availableForms as $pageGroup => $fList){ ?>
                            <optgroup label="<?php echo h($pageGroup);?>">
                                <?php foreach($fList as $fId => $fLabel){ ?>
                                    <option value="<?php echo h($fId);?>" data-form-name="<?php echo h(isset($formsMetaMap[$fId]['name']) ? $formsMetaMap[$fId]['name'] : '');?>" data-page-name="<?php echo h($pageGroup);?>">
                                        <?php echo h($fLabel);?>
                                    </option>
                                <?php } ?>
                            </optgroup>
                        <?php }} ?>
                        <option value="CUSTOM"><?php echo __('[ + Enter Custom Form ID Manually ]');?></option>
                    </select>
                    <p class="help-block" style="font-size:11px;color:#64748b;margin-top:4px;">
                        <?php echo __('Forms are auto-fetched from your connected Facebook Pages (White ICE Cottages, White Alpine Lodges, etc.).');?>
                    </p>
                </div>

                <!-- Custom Manual Form ID (Hidden by default, shown if CUSTOM selected) -->
                <div class="form-group" id="customFormIdGroup" style="display:none;margin-bottom:18px;">
                    <label style="font-weight:700;color:#0f172a;"><?php echo __('Manual Facebook Form ID:');?></label>
                    <input type="text" name="data[FacebookFormMapping][custom_form_id]" id="inputCustomFormId" class="form-control" placeholder="e.g. 2038407180131077">
                </div>

                <!-- Form Friendly Name -->
                <div class="form-group" style="margin-bottom:18px;">
                    <label style="font-weight:700;color:#0f172a;"><?php echo __('Form Friendly Name:');?> *</label>
                    <?php echo $this->Form->input('FacebookFormMapping.form_name', array('label'=>false, 'id'=>'inputFormFriendlyName', 'class'=>'form-control', 'placeholder'=>'e.g. White Alpine Lodges Inquiry Form', 'required'=>true, 'style'=>'border-radius:8px;'));?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group" style="margin-bottom:18px;">
                            <label style="font-weight:700;color:#0f172a;"><i class="fa fa-building text-primary"></i> <?php echo __('Target Agency / Branch (e.g. White Villas Lahore, White Villas Awami Block):');?></label>
                            <?php echo $this->Form->select('FacebookFormMapping.project_id', $projects, array('empty'=>__('Default Account Project'), 'class'=>'form-control', 'style'=>'border-radius:8px;height:40px;'));?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" style="margin-bottom:18px;">
                            <label style="font-weight:700;color:#0f172a;"><i class="fa fa-filter text-primary"></i> <?php echo __('Default Pipeline Status:');?></label>
                            <?php echo $this->Form->select('FacebookFormMapping.default_status_id', $statuses, array('empty'=>__('Default (New Lead)'), 'class'=>'form-control', 'style'=>'border-radius:8px;height:40px;'));?>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:10px;">
                    <label style="font-weight:700;color:#0f172a;"><i class="fa fa-user text-primary"></i> <?php echo __('Dedicated Agent Override (Optional):');?></label>
                    <?php echo $this->Form->select('FacebookFormMapping.default_agent_id', $agents, array('empty'=>__('Auto 1-by-1 Agent Looper (Default)'), 'class'=>'form-control', 'style'=>'border-radius:8px;height:40px;'));?>
                    <p class="help-block" style="font-size:11px;color:#64748b;margin-top:4px;">
                        <?php echo __('Leave empty to use automatic 1-by-1 agent rotation. Select an agent only if this specific form belongs to one person.');?>
                    </p>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;padding:16px 24px;border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel');?></button>
                <button type="submit" class="btn btn-primary" style="background:#1877f2;border-color:#1877f2;font-weight:800;padding:8px 24px;">
                    <i class="fa fa-save"></i> <?php echo __('Save Form Mapping');?>
                </button>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#selectFacebookForm').on('change', function() {
        var selectedVal = $(this).val();
        var selectedOption = $(this).find('option:selected');
        
        if (selectedVal === 'CUSTOM') {
            $('#customFormIdGroup').slideDown();
            $('#inputFormFriendlyName').val('');
        } else if (selectedVal !== '') {
            $('#customFormIdGroup').slideUp();
            var formName = selectedOption.attr('data-form-name') || '';
            var pageName = selectedOption.attr('data-page-name') || '';
            if (formName) {
                $('#inputFormFriendlyName').val(formName);
            }
        } else {
            $('#customFormIdGroup').slideUp();
            $('#inputFormFriendlyName').val('');
        }
    });
});
</script>