<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array(
    'update' => '#resultDiv',
    'evalScripts' => true,
));
$url = $this->Html->url(array('controller'=>'Contacts'));
$addressPrintUrl = $this->Html->url(array('controller'=>'Contacts','action'=>'addressprint'));
$contactLimitUrl = '/admin/Contacts/index';
if(isset($this->params['named']) && is_array($this->params['named']))
{
    foreach($this->params['named'] as $contactLimitKey=>$contactLimitValue)
    {
        if(in_array($contactLimitKey,array('limit','page'),true))
        {
            continue;
        }
        $contactLimitUrl .= '/'.rawurlencode($contactLimitKey).':'.rawurlencode($contactLimitValue);
    }
    unset($contactLimitKey,$contactLimitValue);
}
$pageParams = $this->Paginator->params();
$limit = isset($pageParams['limit']) ? (int)$pageParams['limit'] : 20;
$page = isset($pageParams['page']) ? (int)$pageParams['page'] : 1;
$serialNo = 1 * $limit * ($page - 1) + 1;
$visibleContacts = count((array)$Contact);
?>
<style>
.wv-admin-contacts{background:#f8fafc;margin:-10px -10px 0;padding:20px;}
.wv-contacts-hero{align-items:center;background:#ffffff!important;border:1px solid #e2e8f0!important;border-radius:12px!important;box-shadow:0 4px 16px -2px rgba(15,23,42,.05)!important;color:#0f172a!important;display:flex;gap:18px;justify-content:space-between;margin-bottom:16px;overflow:hidden;padding:20px 24px!important;position:relative;}
.wv-contacts-hero:before,.wv-contacts-hero:after{display:none!important;}
.wv-contacts-hero > *{position:relative;z-index:2;}
.wv-contacts-title{align-items:center;display:flex;gap:16px;}
.wv-contacts-title-icon{align-items:center;background:#eff6ff!important;border:1px solid #dbeafe!important;border-radius:12px!important;color:#2563eb!important;display:flex;font-size:24px;height:52px;justify-content:center;width:52px;box-shadow:0 2px 6px rgba(37,99,235,.12)!important;flex-shrink:0;}
.wv-contacts-title-icon .fa{color:#2563eb!important;text-shadow:none!important;}
.wv-contacts-title h1{color:#0f172a!important;font-size:24px!important;font-weight:800!important;line-height:1.2;margin:0 0 4px;text-shadow:none!important;}
.wv-contacts-title p{color:#64748b!important;font-size:13px!important;font-weight:500;line-height:1.45;margin:0;text-shadow:none!important;}
.wv-contacts-count{background:#f1f5f9!important;border:1px solid #e2e8f0!important;border-radius:999px!important;color:#334155!important;font-size:12px!important;font-weight:800!important;padding:8px 16px!important;white-space:nowrap;box-shadow:0 1px 3px rgba(0,0,0,.04)!important;}
.wv-contacts-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 4px 16px -2px rgba(15,23,42,.05);margin-bottom:16px;overflow:hidden;}
.wv-contacts-command-card{padding:14px 16px;}
.wv-contacts-toolbar{align-items:flex-end;display:flex;gap:14px;justify-content:space-between;}
.wv-contacts-actions{display:flex;flex:0 0 auto;flex-wrap:wrap;gap:8px;}
.wv-contacts-actions .btn{border-radius:8px;font-size:12px;font-weight:700;padding:9px 14px;display:inline-flex;align-items:center;gap:6px;box-shadow:0 1px 3px rgba(0,0,0,.08);transition:all .15s ease;}
.wv-contacts-actions .btn:hover{transform:translateY(-1px);box-shadow:0 3px 8px rgba(0,0,0,.12);}
.wv-contacts-actions .btn-success{background:#10b981!important;border:1px solid #059669!important;color:#fff!important;}
.wv-contacts-actions .btn-warning{background:#f59e0b!important;border:1px solid #d97706!important;color:#fff!important;}
.wv-contacts-actions .btn-danger{background:#ef4444!important;border:1px solid #dc2626!important;color:#fff!important;}
.wv-contacts-actions .btn-info{background:#0284c7!important;border:1px solid #0369a1!important;color:#fff!important;}
.wv-contacts-actions .btn-primary{background:#2563eb!important;border:1px solid #1d4ed8!important;color:#fff!important;}
.wv-contacts-actions .btn-default{background:#fff!important;border:1px solid #cbd5e1!important;color:#334155!important;}
.wv-contacts-filter{background:transparent;flex:1 1 auto;min-width:360px;padding:0;}
.wv-contacts-filter .row{align-items:flex-end;display:flex;flex-wrap:nowrap;gap:8px;justify-content:flex-end;margin:0;}
.wv-contacts-filter [class*="col-"]{float:none!important;padding:0;width:auto;}
.wv-contacts-filter .wv-filter-category{flex:0 0 160px;}
.wv-contacts-filter .wv-filter-kyc{flex:0 0 145px;}
.wv-contacts-filter .wv-filter-name{flex:1 1 230px;max-width:320px;}
.wv-contacts-filter .wv-filter-submit{flex:0 0 auto;}
.wv-contacts-filter strong{color:#475569;display:block;font-size:11px;font-weight:800;letter-spacing:.35px;margin-bottom:6px;text-transform:uppercase;}
.wv-contacts-filter .form-control{background:#fff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:13px;height:38px;box-shadow:inset 0 1px 2px rgba(0,0,0,.02);}
.wv-contacts-filter .form-control:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.15);}
.wv-contacts-filter .btn{background:#2563eb!important;border:1px solid #1d4ed8!important;border-radius:8px;color:#fff!important;font-size:12.5px;font-weight:700;height:38px;padding:0 16px;white-space:nowrap;box-shadow:0 2px 6px rgba(37,99,235,.25);transition:all .15s ease;}
.wv-contacts-filter .btn:hover{background:#1d4ed8!important;}
.wv-pagination-wrap{margin:14px 0;}
.wv-admin-contacts .wv-contacts-list-form{display:block!important;margin:0!important;width:100%!important;}
.wv-admin-contacts #printable_content{clear:both;display:block!important;margin:0!important;max-width:none!important;padding:0!important;width:100%!important;}
.wv-contacts-table-wrap{clear:both;display:block!important;margin-left:0!important;margin-right:0!important;max-width:none!important;padding:0;width:100%!important;}
.wv-contacts-table-responsive{border:0;margin:0!important;max-width:none!important;width:100%!important;}
.wv-contacts-table{border:0!important;margin:0!important;min-width:1220px;width:100%!important;}
.wv-contacts-table thead tr,.table.wv-contacts-table > thead > tr{background:#f8fafc!important;background-color:#f8fafc!important;border-bottom:2px solid #e2e8f0!important;}
.wv-contacts-table th,.table.wv-contacts-table > thead > tr > th,.wv-contacts-table th.wv-address-head,.wv-contacts-table th.wv-district-head,.wv-contacts-table th.wv-email-head,.wv-contacts-table th.wv-cnic-head,.wv-contacts-table th.wv-kyc-head,.wv-contacts-table th.actions-col,.wv-contacts-table th.asc,.wv-contacts-table th.desc{background-color:#f8fafc!important;background:#f8fafc!important;border-top:0!important;border-bottom:2px solid #e2e8f0!important;border-left:0!important;border-right:0!important;color:#475569!important;font-size:11px!important;font-weight:800!important;letter-spacing:.4px!important;padding:13px 14px!important;text-transform:uppercase!important;white-space:nowrap!important;vertical-align:middle!important;}
.wv-contacts-table th a,.table.wv-contacts-table > thead > tr > th a,.wv-contacts-table th a.asc,.wv-contacts-table th a.desc{color:#334155!important;background:transparent!important;font-weight:800!important;text-decoration:none!important;display:inline-flex;align-items:center;gap:4px;transition:color .15s ease;}
.wv-contacts-table th a:hover,.table.wv-contacts-table > thead > tr > th a:hover{color:#2563eb!important;background:transparent!important;}
.wv-contacts-table td,.table.wv-contacts-table > tbody > tr > td{border-top:1px solid #f1f5f9!important;border-bottom:0!important;border-left:0!important;border-right:0!important;color:#1e293b!important;font-size:12.5px!important;font-weight:600!important;padding:12px 14px!important;vertical-align:middle!important;}
.wv-contacts-table tbody tr{background-color:#ffffff;transition:background-color .12s ease;}
.wv-contacts-table tbody tr:nth-of-type(even){background-color:#fafbfc;}
.wv-contacts-table tbody tr:hover td,.table.wv-contacts-table > tbody > tr:hover > td{background-color:#eff6ff!important;}
.wv-contact-person{align-items:center;display:flex;gap:10px;min-width:190px;}
.wv-contact-avatar{align-items:center;background:linear-gradient(135deg,#3b82f6,#1d4ed8);background-position:center;background-size:cover;border:2px solid #fff;border-radius:50%;box-shadow:0 4px 10px rgba(15,23,42,.14);color:#fff;display:flex;flex:0 0 38px;font-size:14px;font-weight:900;height:38px;justify-content:center;text-transform:uppercase;width:38px;}
.wv-contact-avatar img{border-radius:50%;display:block;height:100%;object-fit:cover;object-position:center;width:100%;}
.wv-contact-person strong{color:#0f172a;display:block;font-size:13px;font-weight:800;line-height:1.25;}
.wv-contact-person small{color:#64748b;display:block;font-size:11px;font-weight:700;margin-top:2px;}
.wv-contact-phone{align-items:center;background:#effdf7;border:1px solid #cdeee0;border-radius:999px;color:#047857!important;display:inline-flex;font-size:12px;font-weight:800;gap:7px;padding:6px 11px;text-decoration:none!important;white-space:nowrap;}
.wv-contact-phone:before{content:"\f095";font-family:FontAwesome;}
.wv-muted{color:#94a3b8;font-style:italic;font-weight:700;}
/* Address auto-wrap and fixed column sizing */
.wv-address-head,.table th.wv-address-head{min-width:220px!important;max-width:320px!important;width:260px!important;white-space:nowrap!important;}
.wv-address-cell,.table td.wv-address-cell,.table.wv-contacts-table > tbody > tr > td.wv-address-cell{white-space:normal!important;word-wrap:break-word!important;overflow-wrap:break-word!important;word-break:normal!important;min-width:220px!important;max-width:320px!important;width:260px!important;line-height:1.45!important;color:#334155!important;font-size:12px!important;}
.wv-district-head,.table th.wv-district-head{min-width:120px!important;white-space:nowrap!important;}
.wv-district-cell,.table td.wv-district-cell,.table.wv-contacts-table > tbody > tr > td.wv-district-cell{min-width:120px!important;white-space:nowrap!important;color:#475569!important;font-weight:700!important;}
.wv-email-head,.table th.wv-email-head{min-width:180px!important;white-space:nowrap!important;}
.wv-email-cell,.table td.wv-email-cell,.table.wv-contacts-table > tbody > tr > td.wv-email-cell{min-width:180px!important;max-width:240px!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;color:#0f766e!important;font-weight:600!important;}
.wv-cnic-cell,.wv-cnic-head{font-variant-numeric:tabular-nums;min-width:130px;white-space:nowrap!important;}
.wv-kyc-cell,.wv-kyc-head{min-width:138px;white-space:nowrap!important;}
.wv-kyc-wrap{display:flex;flex-direction:column;gap:5px;}
.wv-kyc-badge{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:800;padding:3px 7px;border-radius:6px;width:fit-content;letter-spacing:.2px;text-transform:uppercase;}
.wv-kyc-verified{background:#dcfce7!important;color:#15803d!important;border:1px solid #bbf7d0;}
.wv-kyc-partial{background:#fef3c7!important;color:#b45309!important;border:1px solid #fde68a;}
.wv-kyc-pending{background:#fee2e2!important;color:#b91c1c!important;border:1px solid #fecaca;}
.wv-kyc-docs{display:flex;gap:3px;align-items:center;}
.wv-doc-pill{display:inline-flex;align-items:center;gap:2px;font-size:9.5px;font-weight:800;padding:2px 4px;border-radius:4px;text-decoration:none!important;cursor:default;}
.wv-doc-pill.has-doc{background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;}
.wv-doc-pill.missing-doc{background:#f1f5f9;color:#94a3b8;border:1px solid #e2e8f0;opacity:.75;}
.wv-contacts-action .btn,.wv-contacts-table .actions-col .btn{background:#fff!important;border:1px solid #cbd5e1!important;border-radius:6px!important;color:#1e293b!important;font-size:12px!important;font-weight:700!important;padding:6px 12px!important;box-shadow:0 1px 2px rgba(0,0,0,.05)!important;transition:all .15s ease!important;}
.wv-contacts-action .btn:hover,.wv-contacts-table .actions-col .btn:hover{background:#f8fafc!important;border-color:#94a3b8!important;color:#0f172a!important;}
.wv-contacts-action .dropdown-menu{background:#fff!important;border:1px solid #cbd5e1!important;border-radius:8px!important;box-shadow:0 14px 30px -4px rgba(15,23,42,.18),0 4px 10px -2px rgba(15,23,42,.08)!important;padding:6px!important;}
.wv-contacts-action .dropdown-menu li a{border-radius:6px!important;color:#334155!important;font-size:12.5px!important;font-weight:600!important;padding:8px 12px!important;text-decoration:none!important;transition:all .12s ease!important;}
.wv-contacts-action .dropdown-menu li a:hover{background:#f1f5f9!important;color:#0f172a!important;}
.wv-contacts-action .dropdown-menu li a .fa{color:#2563eb;width:17px;}
.wv-empty-row td{color:#64748b!important;font-weight:800;padding:24px!important;text-align:center;}
@media(max-width:1199px){.wv-contacts-toolbar{align-items:stretch;flex-direction:column}.wv-contacts-actions{width:100%}.wv-contacts-filter{min-width:0;width:100%}.wv-contacts-filter .row{justify-content:flex-start}.wv-contacts-filter .wv-filter-name{max-width:none;}}
@media(max-width:991px){.wv-admin-contacts{margin:0;padding:12px}.wv-contacts-hero{display:block}.wv-contacts-count{display:inline-block;margin-top:12px}.wv-contacts-actions{margin-top:0}.wv-contacts-table{min-width:980px;}}
@media(max-width:640px){.wv-contacts-filter .row{flex-wrap:wrap}.wv-contacts-filter .wv-filter-category,.wv-contacts-filter .wv-filter-kyc,.wv-contacts-filter .wv-filter-name,.wv-contacts-filter .wv-filter-submit{flex:1 1 100%;}.wv-contacts-filter .btn,.wv-contacts-actions .btn{justify-content:center;width:100%;}}
/* Modern ON/OFF Toggle Switch for Login Status */
.wv-status-toggle-switch {
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
    outline: none;
    display: inline-block;
    vertical-align: middle;
    user-select: none;
    text-decoration: none !important;
}
.wv-status-toggle-switch:focus { outline: none; }
.wv-status-toggle-switch .wv-switch-track {
    display: inline-flex;
    align-items: center;
    position: relative;
    width: 82px;
    height: 28px;
    border-radius: 999px;
    padding: 3px;
    transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15), 0 1px 2px rgba(255, 255, 255, 0.2);
}
.wv-status-toggle-switch .wv-switch-knob {
    position: absolute;
    top: 3px;
    width: 22px;
    height: 22px;
    background: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25);
    transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 10px;
    line-height: 1;
}
.wv-status-toggle-switch .wv-switch-text {
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    color: #ffffff;
    width: 100%;
    text-align: center;
    transition: all 0.2s ease;
    user-select: none;
}
/* ON / ACTIVE STATE */
.wv-status-toggle-switch.is-active .wv-switch-track {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1), 0 2px 6px rgba(16, 185, 129, 0.35);
}
.wv-status-toggle-switch.is-active .wv-switch-knob {
    left: calc(100% - 25px);
    color: #059669;
}
.wv-status-toggle-switch.is-active .wv-switch-text {
    padding-right: 22px;
    padding-left: 6px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}
/* OFF / BLOCKED STATE */
.wv-status-toggle-switch.is-blocked .wv-switch-track {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1), 0 2px 6px rgba(239, 68, 68, 0.35);
}
.wv-status-toggle-switch.is-blocked .wv-switch-knob {
    left: 3px;
    color: #dc2626;
}
.wv-status-toggle-switch.is-blocked .wv-switch-text {
    padding-left: 22px;
    padding-right: 6px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}
.wv-status-toggle-switch:hover .wv-switch-track {
    filter: brightness(1.08);
    transform: scale(1.04);
}
.wv-status-toggle-switch:active .wv-switch-track {
    transform: scale(0.97);
}
.wv-status-toggle-switch.is-loading {
    opacity: 0.65;
    pointer-events: none;
    cursor: wait;
}
.wv-status-toggle-switch.is-loading .wv-switch-knob i {
    animation: fa-spin 1s infinite linear;
}

@media print{.wv-contacts-hero,.wv-contacts-toolbar,.wv-contacts-filter,.wv-pagination-wrap,.pbutton{display:none!important}.wv-admin-contacts{background:#fff;margin:0;padding:0}.wv-contacts-card{border:0;box-shadow:none}}
</style>

<div id="resultDiv">
    <div class="wv-admin-contacts">
        <?php echo $this->Session->flash();?>
        <div class="wv-contacts-hero" style="background:#ffffff !important; border:1px solid #e2e8f0 !important; border-radius:12px !important; box-shadow:0 4px 16px -2px rgba(15,23,42,.05) !important; color:#0f172a !important; padding:20px 24px !important;">
            <div class="wv-contacts-title">
                <div class="wv-contacts-title-icon" style="background:#eff6ff !important; border:1px solid #dbeafe !important; color:#2563eb !important; box-shadow:0 2px 6px rgba(37,99,235,.12) !important;"><i class="fa fa-address-book" style="color:#2563eb !important; text-shadow:none !important;"></i></div>
                <div>
                    <h1 style="color:#0f172a !important; text-shadow:none !important; font-size:24px !important; font-weight:800 !important; margin:0 0 4px !important;"><?php echo __('Contacts');?></h1>
                    <p style="color:#64748b !important; text-shadow:none !important; font-size:13px !important; font-weight:500 !important; margin:0 !important;"><?php echo __('Manage customer contacts, identity details, communication channels, and account actions.');?></p>
                </div>
            </div>
            <div class="wv-contacts-count" style="background:#f1f5f9 !important; border:1px solid #e2e8f0 !important; color:#334155 !important; font-size:12px !important; font-weight:800 !important; padding:8px 16px !important; border-radius:999px !important;">
                <?php echo (int)$visibleContacts;?> <?php echo __('Showing');?>
            </div>
        </div>

        <div class="wv-contacts-card wv-contacts-command-card">
            <div class="wv-contacts-toolbar">
                <div class="wv-contacts-actions">
                    <?php echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add New Contact'),array('controller'=>'Contacts','action'=>'add'),array('escape'=>false,'class'=>'btn btn-success'));?>
                    <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),'#',array('name'=>'editallfrm','id'=>'editallfrm','onclick'=>"check_perform_edit('$url');",'escape'=>false,'class'=>'btn btn-warning'));?>
                    <?php echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'#',array('name'=>'deleteallfrm','id'=>'deleteallfrm','onclick'=>'check_perform_delete();','escape'=>false,'class'=>'btn btn-danger'));?>
                    <?php echo $this->Html->link('<span class="fa fa-file-excel-o"></span>&nbsp;'.__('Export to Excel'), array('controller'=>'Contacts','action'=>'exportExcel'), array('escape'=>false,'class'=>'btn btn-info'));?>
                    <?php echo $this->Html->link('<span class="fa fa-address-card-o"></span>&nbsp;'.__('Address Print'),'#',array('onclick'=>"contacts_address_print('$addressPrintUrl');return false;",'escape'=>false,'class'=>'btn btn-primary'));?>
                    <?php echo $this->Html->link('<span class="fa fa-print"></span>&nbsp;'.__('Print'),'#',array('id'=>'printme','escape'=>false,'class'=>'btn btn-default'));?>
                </div>
                <div class="wv-contacts-filter">
                    <?php echo $this->Form->create(array('name'=>'searchfrm','id'=>'searchfrm','action' => 'index'));?>
                    <div class="row">
                        <div class="wv-filter-category">
                            <strong><?php echo __('Category');?></strong>
                            <?php echo $this->Form->select('cid',$category,array('label' => false,'class'=>'form-control','empty'=>__('All'),'div'=>false));?>
                        </div>
                        <div class="wv-filter-kyc">
                            <strong><?php echo __('KYC Status');?></strong>
                            <?php echo $this->Form->select('kyc_status',array('verified'=>__('Verified (4/4)'),'partial'=>__('Partial'),'pending'=>__('Pending')),array('label' => false,'class'=>'form-control','empty'=>__('All KYC'),'div'=>false));?>
                        </div>
                        <div class="wv-filter-name">
                            <strong><?php echo __('Name');?></strong>
                            <?php echo $this->Form->input('cname',array('label' => false,'class'=>'form-control','placeholder'=>__('Contact Name'),'div'=>false));?>
                        </div>
                        <div class="wv-filter-submit">
                            <strong><?php echo __('Filter');?></strong>
                            <button class="btn btn-default" type="submit"><i class="fa fa-search"></i><?php echo ' '.__('Search');?></button>
                        </div>
                    </div>
                    <?php echo $this->Form->end();?>
                </div>
            </div>
        </div>

        <div class="wv-pagination-wrap">
            <?php echo $this->element('pagination',array('IsSearch'=>'No','UseNamedLimit'=>true,'UseQueryLimit'=>true,'NamedLimitBaseUrl'=>$contactLimitUrl));?>
        </div>

        <?php echo $this->Form->create(array('name'=>'deleteallfrm','action' => 'deleteall','class'=>'wv-no-auto-layout wv-contacts-list-form'));?>
        <div id="printable_content">
            <?php echo $this->Html->css('print');?>
            <div class="wv-contacts-card wv-contacts-table-wrap">
                <div class="table-responsive wv-contacts-table-responsive">
                    <table class="table table-striped table-bordered wv-contacts-table">
                        <thead>
                            <tr>
                                <th class="pbutton"><?php echo $this->Form->checkbox('checkbox', array('value'=>'deleteall','name'=>'selectAll','label'=>false,'id'=>'selectAll','hiddenField'=>false));?></th>
                                <th><?php echo $this->Paginator->sort('id', __('#'), array('direction' => 'desc'));?></th>
                                <th><?php echo $this->Paginator->sort('name', __('Name'), array('direction' => 'asc'));?></th>
                                <th><?php echo $this->Paginator->sort('fathername', __('Father / Guardian'), array('direction' => 'asc'));?></th>
                                <th><?php echo $this->Paginator->sort('mobile', __('Mobile No'), array('direction' => 'asc'));?></th>
                                <th class="wv-cnic-head"><?php echo $this->Paginator->sort('CNIC', __('CNIC'), array('direction' => 'asc'));?></th>
                                <th class="wv-kyc-head"><?php echo __('KYC / Documents');?></th>
                                <th class="wv-address-head"><?php echo $this->Paginator->sort('address', __('Address'), array('direction' => 'asc'));?></th>
                                <th class="wv-district-head"><?php echo $this->Paginator->sort('district', __('District'), array('direction' => 'asc'));?></th>
                                <th class="wv-email-head"><?php echo $this->Paginator->sort('email', __('Email'), array('direction' => 'asc'));?></th>
                                <th class="text-center" style="min-width:130px;"><?php echo $this->Paginator->sort('status', __('Login Access'), array('direction' => 'asc'));?></th>
                                <th class="pbutton actions-col"><?php echo __('Action');?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(empty($Contact)){ ?>
                            <tr class="wv-empty-row"><td colspan="12"><?php echo __('No contacts found.');?></td></tr>
                        <?php } ?>
                        <?php 
                        $totalContacts = !empty($Contact) ? count($Contact) : 0;
                        $contactIndex = 0;
                        foreach ($Contact as $post):
                            $contactIndex++;
                            $id = $post['Contact']['id'];
                            $cStatus = !empty($post['Contact']['status']) ? trim((string)$post['Contact']['status']) : 'Active';
                            $isBlocked = (strcasecmp($cStatus, 'Block') === 0 || strcasecmp($cStatus, 'Blocked') === 0);
                            $contactName = trim((string)$post['Contact']['name']);
                            $contactInitial = strtoupper(substr($contactName,0,1));
                            if($contactInitial === '')$contactInitial = 'C';
                            $contactPhoto = '';
                            if(!empty($post['Contact']['photo']))
                            {
                                $contactPhotoDir = !empty($post['Contact']['dirt']) ? trim(str_replace('\\','/',$post['Contact']['dirt']),'/') : 'contact';
                                $contactPhotoDir = basename($contactPhotoDir);
                                $contactPhotoFile = basename((string)$post['Contact']['photo']);
                                $contactPhotoCandidates = array(
                                    $contactPhotoDir.'_thumb/'.$contactPhotoFile,
                                    $contactPhotoDir.'/'.$contactPhotoFile,
                                    'contact_thumb/'.$contactPhotoFile,
                                    'contact/'.$contactPhotoFile
                                );
                                foreach(array_unique($contactPhotoCandidates) as $contactPhotoCandidate)
                                {
                                    if(file_exists(WWW_ROOT.'img'.DS.str_replace('/',DS,$contactPhotoCandidate)))
                                    {
                                        $contactPhoto = $contactPhotoCandidate;
                                        break;
                                    }
                                }
                            }
                        ?>
                            <tr>
                                <td class="pbutton"><?php if($userType!="AGT" || $post['Contact']['user_id']==$luserId){ echo $this->Form->checkbox(false,array('value' => $post['Contact']['id'],'name'=>'data[Contact][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect')); } else echo "&nbsp;";?></td>
                                <td><?php echo $serialNo++;?></td>
                                <td>
                                    <div class="wv-contact-person">
                                        <span class="wv-contact-avatar"><?php if($contactPhoto !== ''){ echo $this->Html->image($contactPhoto,array('alt'=>$contactName,'loading'=>'lazy')); } else { echo h($contactInitial); }?></span>
                                        <span>
                                            <strong>
                                                <?php echo h($contactName !== '' ? $contactName : __('Unnamed Contact'));?>
                                                <?php if($isBlocked){ ?>
                                                    <span class="label label-danger" style="background-color:#EF4444!important;color:#ffffff;padding:2px 7px;border-radius:4px;font-size:9.5px;font-weight:bold;margin-left:6px;display:inline-block;vertical-align:middle;box-shadow:0 2px 4px rgba(239,68,68,0.25);" title="<?php echo __('Login is blocked for this client');?>"><i class="fa fa-lock"></i> Blocked</span>
                                                <?php } elseif(!empty($post['Contact']['app_login_status']) || !empty($post['Contact']['app_last_login'])){ ?>
                                                    <span class="label label-success" style="background-color:#10B981!important;color:#ffffff;padding:2px 7px;border-radius:4px;font-size:9.5px;font-weight:bold;margin-left:6px;display:inline-block;vertical-align:middle;box-shadow:0 2px 4px rgba(16,185,129,0.2);">Login</span>
                                                <?php } else { ?>
                                                    <span class="label label-danger" style="background-color:#EF4444!important;color:#ffffff;padding:2px 7px;border-radius:4px;font-size:9.5px;font-weight:bold;margin-left:6px;display:inline-block;vertical-align:middle;box-shadow:0 2px 4px rgba(239,68,68,0.2);">Logout</span>
                                                <?php } ?>
                                            </strong>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <?php if(!empty($post['Contact']['fathername'])): ?>
                                        <strong style="color:#1e293b;"><?php echo h($post['Contact']['fathername']);?></strong>
                                        <?php if(!empty($post['Contact']['relation_type'])): ?>
                                            <small class="text-muted" style="display:block;font-size:11px;font-weight:600;"><?php echo h($post['Contact']['relation_type']);?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="wv-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $contactMobileDisplay = trim((string)$post['Contact']['mobile']) !== '' ? $post['Contact']['mobile'] : (isset($post['Contact']['phone']) ? $post['Contact']['phone'] : '');
                                    $contactCallNo = preg_replace('/[^0-9\+]/', '', (string)$contactMobileDisplay);
                                    if(trim((string)$contactMobileDisplay) === '')
                                    {
                                        echo '<span class="wv-muted">-</span>';
                                    }
                                    elseif(trim((string)$contactCallNo) !== '')
                                    {
                                        echo $this->Html->link(h($contactMobileDisplay), 'tel:'.$contactCallNo, array('escape' => false,'class'=>'wv-contact-phone'));
                                    }
                                    else
                                    {
                                        echo h($contactMobileDisplay);
                                    }
                                    ?>
                                </td>
                                <td class="wv-cnic-cell"><?php echo trim((string)$post['Contact']['pincode']) !== '' ? h($post['Contact']['pincode']) : '<span class="wv-muted">-</span>';?></td>
                                <?php
                                $hasCnicNo  = !empty(trim((string)$post['Contact']['pincode']));
                                $hasPhoto   = !empty(trim((string)$post['Contact']['photo']));
                                $hasIdFront = !empty(trim((string)$post['Contact']['id_front']));
                                $hasIdBack  = !empty(trim((string)$post['Contact']['id_back']));
                                $docScore   = ($hasCnicNo ? 1 : 0) + ($hasPhoto ? 1 : 0) + ($hasIdFront ? 1 : 0) + ($hasIdBack ? 1 : 0);

                                if ($docScore === 4) {
                                    $kycClass = 'wv-kyc-verified';
                                    $kycIcon  = 'fa-check-circle';
                                    $kycLabel = __('Verified');
                                } elseif ($docScore > 0) {
                                    $kycClass = 'wv-kyc-partial';
                                    $kycIcon  = 'fa-exclamation-circle';
                                    $kycLabel = sprintf(__('Partial (%d/4)'), $docScore);
                                } else {
                                    $kycClass = 'wv-kyc-pending';
                                    $kycIcon  = 'fa-clock-o';
                                    $kycLabel = __('Pending');
                                }
                                ?>
                                <td class="wv-kyc-cell">
                                    <div class="wv-kyc-wrap">
                                        <span class="wv-kyc-badge <?php echo $kycClass;?>" title="<?php echo sprintf(__('KYC: %d of 4 items completed'), $docScore);?>">
                                            <i class="fa <?php echo $kycIcon;?>"></i> <?php echo $kycLabel;?>
                                        </span>
                                        <div class="wv-kyc-docs">
                                            <span class="wv-doc-pill <?php echo $hasCnicNo ? 'has-doc' : 'missing-doc';?>" title="<?php echo $hasCnicNo ? __('CNIC Number: ').h($post['Contact']['pincode']) : __('CNIC Number missing');?>">
                                                <i class="fa fa-id-card"></i> CNIC
                                            </span>
                                            <span class="wv-doc-pill <?php echo $hasIdFront ? 'has-doc' : 'missing-doc';?>" title="<?php echo $hasIdFront ? __('ID Front uploaded') : __('ID Front missing');?>">
                                                <i class="fa fa-file-image-o"></i> Front
                                            </span>
                                            <span class="wv-doc-pill <?php echo $hasIdBack ? 'has-doc' : 'missing-doc';?>" title="<?php echo $hasIdBack ? __('ID Back uploaded') : __('ID Back missing');?>">
                                                <i class="fa fa-file-image-o"></i> Back
                                            </span>
                                            <span class="wv-doc-pill <?php echo $hasPhoto ? 'has-doc' : 'missing-doc';?>" title="<?php echo $hasPhoto ? __('Photo uploaded') : __('Photo missing');?>">
                                                <i class="fa fa-user"></i> Photo
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="wv-address-cell"><?php echo trim((string)$post['Contact']['address']) !== '' ? h($post['Contact']['address']) : '<span class="wv-muted">-</span>';?></td>
                                <td class="wv-district-cell"><?php echo trim((string)$post['Contact']['district']) !== '' ? h($post['Contact']['district']) : '<span class="wv-muted">-</span>';?></td>
                                <td class="wv-email-cell"><?php echo trim((string)$post['Contact']['email']) !== '' ? h($post['Contact']['email']) : '<span class="wv-muted">-</span>';?></td>
                                <td class="text-center wv-status-cell" style="vertical-align:middle;white-space:nowrap;">
                                    <div class="wv-login-status-wrap" data-contact-id="<?php echo $id;?>">
                                        <button type="button" 
                                                class="wv-status-toggle-switch <?php echo $isBlocked ? 'is-blocked' : 'is-active'; ?> js-toggle-contact-status" 
                                                data-id="<?php echo $id; ?>" 
                                                data-current="<?php echo $isBlocked ? 'Block' : 'Active'; ?>"
                                                data-url="<?php echo $this->Html->url(array('controller'=>'Contacts','action'=>'change_status',$id)); ?>"
                                                title="<?php echo $isBlocked ? __('Currently BLOCKED. Click to turn ON / ACTIVATE login.') : __('Currently ACTIVE. Click to turn OFF / BLOCK login.'); ?>"
                                                aria-label="<?php echo $isBlocked ? __('Login Blocked - Click to Activate') : __('Login Active - Click to Block'); ?>">
                                            <span class="wv-switch-track">
                                                <span class="wv-switch-knob">
                                                    <i class="fa <?php echo $isBlocked ? 'fa-ban' : 'fa-check'; ?>"></i>
                                                </span>
                                                <span class="wv-switch-text"><?php echo $isBlocked ? __('Block') : __('Active'); ?></span>
                                            </span>
                                        </button>
                                    </div>
                                </td>
                                <td class="pbutton wv-contacts-action actions-col">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="<?php echo h(__('Actions'));?>">
                                            <i class="fa fa-cog"></i> <?php echo __('Action');?> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                            <li><?php echo $this->Html->link('<span class="fa fa-arrows-alt"></span>&nbsp;'.__('View'),'#',array('onclick'=>"show_modal('$url/View/$id');",'escape'=>false));?></li>
                                            <li><?php echo $this->Html->link('<span class="fa fa-print"></span>&nbsp;'.__('Print'),array('controller'=>'Contacts','action'=>'printclient',$id),array('target'=>'_blank','escape'=>false));?></li>
                                            <li><?php echo $this->Html->link('<span class="fa fa-paper-plane" style="color:#25D366;"></span>&nbsp;'.__('Send Login Credentials'),array('controller'=>'Contacts','action'=>'sendcredentials',$id),array('escape'=>false,'onclick'=>"return confirm('".__('Send portal login credentials to this contact via WhatsApp, SMS, and Email?')."');"));?></li>
                                            <?php if ($isBlocked): ?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-unlock text-success"></span>&nbsp;'.__('Active Login'),array('controller'=>'Contacts','action'=>'change_status',$id,'Active'),array('escape'=>false,'onclick'=>"return confirm('".__('Activate login access for this contact?')."');"));?></li>
                                            <?php else: ?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-ban text-danger"></span>&nbsp;'.__('Block Login'),array('controller'=>'Contacts','action'=>'change_status',$id,'Block'),array('escape'=>false,'onclick'=>"return confirm('".__('Block login access for this contact? The client will not be able to login to client portal or Flutter app.')."');"));?></li>
                                            <?php endif; ?>
                                            <?php if($userType=='ADR'){ ?>
                                            <?php if (!$isBlocked): ?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-sign-in"></span>&nbsp;'.__('Login'),array('controller'=>'Contacts','action'=>'loginas',$id),array('escape'=>false,'target'=>'_blank','rel'=>'noopener','onclick'=>"return confirm('".__('Login as this contact in a new tab?')."');"));?></li>
                                            <?php else: ?>
                                            <li class="disabled"><a href="javascript:void(0);" onclick="alert('<?php echo __('This contact is blocked. You cannot login as a blocked contact.');?>');" style="color:#aaa;"><span class="fa fa-lock text-danger"></span>&nbsp;<?php echo __('Login (Blocked)');?></a></li>
                                            <?php endif; ?>
                                            <?php } ?>
                                            <?php if($userType!="AGT" || $post['Contact']['user_id']==$luserId){?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-cog"></span>&nbsp;'.__('Change Password'),'#',array('onclick'=>"show_modal('$url/changepass/$id');",'escape'=>false));?></li>
                                            <li><?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),array('controller'=>'Contacts','action'=>'edit',$id),array('escape'=>false));?></li>
                                            <li><?php echo $this->Html->Link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'#',array('onclick'=>"check_perform_sdelete('$id');",'escape'=>false));?></li>
                                            <?php }?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php unset($post); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php echo $this->Form->end();?>

        <div class="wv-pagination-wrap">
            <?php echo $this->element('pagination',array('IsSearch'=>'No','UseNamedLimit'=>true,'UseQueryLimit'=>true,'NamedLimitBaseUrl'=>$contactLimitUrl));?>
        </div>
    </div>
</div>

<!-- Modal: Confirm Contact Login Access Change -->
<div class="modal fade" id="contactLoginAccessModal" tabindex="-1" role="dialog" aria-labelledby="contactLoginModalTitle" aria-hidden="true" style="z-index: 10500;">
    <div class="modal-dialog" role="document" style="max-width: 480px; margin: 70px auto;">
        <div class="modal-content" style="border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 20px 45px -5px rgba(15, 23, 42, 0.35); overflow: hidden;">
            <div class="modal-header" id="contactLoginModalHeader" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 18px 24px; border-bottom: 1px solid #334155; display: flex; align-items: center; justify-content: space-between;">
                <h4 class="modal-title" id="contactLoginModalTitle" style="font-size: 16px; font-weight: 700; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 9px;">
                    <i id="contactLoginModalTitleIcon" class="fa fa-shield text-primary"></i> <span><?php echo __('Change Login Access');?></span>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #94a3b8; opacity: 1; font-size: 22px; font-weight: 400; text-shadow: none; margin-top: -2px; transition: color 0.15s ease;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 24px; background: #ffffff;">
                <!-- Contact Summary Preview Card -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 18px; display: flex; align-items: center; gap: 14px;">
                    <div id="contactLoginModalAvatar" style="width: 44px; height: 44px; border-radius: 50%; background: #2563eb; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.12); overflow: hidden;">
                        C
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div id="contactLoginModalName" style="font-size: 14.5px; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            Contact Name
                        </div>
                        <div id="contactLoginModalMeta" style="font-size: 12px; color: #64748b; font-weight: 600; margin-top: 2px;">
                            Mobile / Account
                        </div>
                    </div>
                    <div id="contactLoginModalCurrentStatus">
                        <span class="label label-success" style="font-size: 10.5px; padding: 4px 9px; border-radius: 999px;">ACTIVE</span>
                    </div>
                </div>

                <!-- Action Confirmation Message Box -->
                <div id="contactLoginActionBox" style="border-radius: 8px; padding: 14px 16px; display: flex; gap: 12px; align-items: flex-start; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;">
                    <i id="contactLoginActionIcon" class="fa fa-exclamation-triangle" style="font-size: 18px; margin-top: 2px; flex-shrink: 0;"></i>
                    <div id="contactLoginActionText" style="font-size: 13px; line-height: 1.5; font-weight: 600;">
                        Confirmation message text.
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 24px; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 7px; font-weight: 600; font-size: 13px; padding: 8px 18px; color: #475569; background: #ffffff; border: 1px solid #cbd5e1;">
                    <i class="fa fa-times"></i> <?php echo __('Cancel');?>
                </button>
                <button type="button" id="btnConfirmLoginAccessSubmit" class="btn btn-danger" style="border-radius: 7px; font-weight: 700; font-size: 13px; padding: 8px 20px;">
                    <span id="contactLoginSubmitText"><?php echo __('Confirm Action');?></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function contacts_address_print(targetUrl)
{
    var numberOfChecked = $('.chkselect:checked').length;
    if(numberOfChecked === 0)
    {
        alert(typeof getLocale === 'function' ? getLocale("Please Select Any Record") : "Please select any record");
        return false;
    }
    var frm = document.forms['deleteallfrm'];
    if(!frm)
    {
        return false;
    }
    var oldAction = frm.action;
    var oldTarget = frm.target;
    frm.action = targetUrl;
    frm.target = '_blank';
    frm.submit();
    setTimeout(function(){
        frm.action = oldAction;
        frm.target = oldTarget;
    }, 200);
    return false;
}

var pendingContactToggleBtn = null;
var pendingContactTargetStatus = null;
var pendingContactRequestUrl = null;

$(document).on('click', '.js-toggle-contact-status', function(e) {
    e.preventDefault();
    var $btn = $(this);
    var contactId = $btn.data('id');
    var currentStatus = $btn.data('current');
    var isCurrentlyBlocked = (currentStatus === 'Block');
    var targetStatus = isCurrentlyBlocked ? 'Active' : 'Block';
    
    pendingContactToggleBtn = $btn;
    pendingContactTargetStatus = targetStatus;
    pendingContactRequestUrl = $btn.data('url') + '/' + targetStatus;

    var $row = $btn.closest('tr');
    var contactName = $.trim($row.find('.wv-contact-person strong').contents().first().text());
    if(!contactName) {
        contactName = "<?php echo __('Contact');?> #" + contactId;
    }
    var contactMobile = $.trim($row.find('.wv-contact-phone').text()) || $.trim($row.find('td:nth-child(5)').text()) || '';
    var $avatarElem = $row.find('.wv-contact-avatar');
    var avatarHtml = $avatarElem.length ? $avatarElem.html() : contactName.charAt(0).toUpperCase();

    $('#contactLoginModalName').text(contactName);
    $('#contactLoginModalMeta').text(contactMobile ? ('<?php echo __("Mobile: ");?>' + contactMobile) : ('<?php echo __("ID: #");?>' + contactId));
    $('#contactLoginModalAvatar').html(avatarHtml);

    if (targetStatus === 'Block') {
        $('#contactLoginModalTitle').html('<i class="fa fa-ban" style="color:#ef4444;"></i> <?php echo __("Block Login Access");?>');
        $('#contactLoginModalCurrentStatus').html('<span class="label label-success" style="background:#10b981;color:#fff;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:800;"><?php echo __("ACTIVE");?></span>');
        $('#contactLoginActionBox').css({
            'background': '#fef2f2',
            'border': '1px solid #fecaca',
            'color': '#991b1b'
        });
        $('#contactLoginActionIcon').attr('class', 'fa fa-ban text-danger').css('color', '#dc2626');
        $('#contactLoginActionText').html('<?php echo __("Are you sure you want to ");?><strong><?php echo __("BLOCK");?></strong> <?php echo __("login access for ");?><strong>' + $('<div>').text(contactName).html() + '</strong>?<br><span style="font-weight:500;font-size:12px;color:#7f1d1d;margin-top:4px;display:block;"><?php echo __("When blocked, this client cannot log in to their account on the client portal or Flutter mobile app.");?></span>');
        $('#btnConfirmLoginAccessSubmit').attr('class', 'btn btn-danger').css({
            'background': '#dc2626',
            'border-color': '#dc2626',
            'color': '#ffffff'
        }).html('<i class="fa fa-ban"></i> <?php echo __("Block Access");?>');
    } else {
        $('#contactLoginModalTitle').html('<i class="fa fa-check-circle" style="color:#10b981;"></i> <?php echo __("Activate Login Access");?>');
        $('#contactLoginModalCurrentStatus').html('<span class="label label-danger" style="background:#ef4444;color:#fff;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:800;"><?php echo __("BLOCKED");?></span>');
        $('#contactLoginActionBox').css({
            'background': '#f0fdf4',
            'border': '1px solid #bbf7d0',
            'color': '#166534'
        });
        $('#contactLoginActionIcon').attr('class', 'fa fa-check-circle text-success').css('color', '#16a34a');
        $('#contactLoginActionText').html('<?php echo __("Activate login access for ");?><strong>' + $('<div>').text(contactName).html() + '</strong>?<br><span style="font-weight:500;font-size:12px;color:#14532d;margin-top:4px;display:block;"><?php echo __("The client will immediately be able to log in to the client portal and Flutter mobile app.");?></span>');
        $('#btnConfirmLoginAccessSubmit').attr('class', 'btn btn-success').css({
            'background': '#10b981',
            'border-color': '#059669',
            'color': '#ffffff'
        }).html('<i class="fa fa-check"></i> <?php echo __("Activate Access");?>');
    }

    $('#contactLoginAccessModal').modal('show');
    return false;
});

$(document).on('click', '#btnConfirmLoginAccessSubmit', function(e) {
    e.preventDefault();
    if (!pendingContactToggleBtn || !pendingContactRequestUrl) {
        $('#contactLoginAccessModal').modal('hide');
        return false;
    }

    var $submitBtn = $(this);
    var $btn = pendingContactToggleBtn;
    var targetStatus = pendingContactTargetStatus;
    var requestUrl = pendingContactRequestUrl;

    $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo __("Updating...");?>');
    $btn.addClass('is-loading');

    $.ajax({
        url: requestUrl,
        type: 'POST',
        dataType: 'json',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        success: function(resp) {
            $submitBtn.prop('disabled', false);
            $btn.removeClass('is-loading');
            $('#contactLoginAccessModal').modal('hide');

            if(resp && resp.status) {
                var newBlocked = (resp.new_status === 'Block');
                if(newBlocked) {
                    $btn.removeClass('is-active').addClass('is-blocked');
                    $btn.data('current', 'Block');
                    $btn.attr('title', '<?php echo __('Currently BLOCKED. Click to turn ON / ACTIVATE login.'); ?>');
                    $btn.attr('aria-label', '<?php echo __('Login Blocked - Click to Activate'); ?>');
                    $btn.find('.wv-switch-knob i').removeClass('fa-check fa-spinner fa-spin').addClass('fa-ban');
                    $btn.find('.wv-switch-text').text('<?php echo __('Block'); ?>');
                    var $nameStrong = $btn.closest('tr').find('.wv-contact-person strong');
                    $nameStrong.find('.label').remove();
                    $nameStrong.append('<span class="label label-danger" style="background-color:#EF4444!important;color:#ffffff;padding:2px 7px;border-radius:4px;font-size:9.5px;font-weight:bold;margin-left:6px;display:inline-block;vertical-align:middle;box-shadow:0 2px 4px rgba(239,68,68,0.25);" title="<?php echo __('Login is blocked for this client');?>"><i class="fa fa-lock"></i> Blocked</span>');
                } else {
                    $btn.removeClass('is-blocked').addClass('is-active');
                    $btn.data('current', 'Active');
                    $btn.attr('title', '<?php echo __('Currently ACTIVE. Click to turn OFF / BLOCK login.'); ?>');
                    $btn.attr('aria-label', '<?php echo __('Login Active - Click to Block'); ?>');
                    $btn.find('.wv-switch-knob i').removeClass('fa-ban fa-spinner fa-spin').addClass('fa-check');
                    $btn.find('.wv-switch-text').text('<?php echo __('Active'); ?>');
                    var $nameStrong = $btn.closest('tr').find('.wv-contact-person strong');
                    $nameStrong.find('.label').remove();
                    $nameStrong.append('<span class="label label-danger" style="background-color:#EF4444!important;color:#ffffff;padding:2px 7px;border-radius:4px;font-size:9.5px;font-weight:bold;margin-left:6px;display:inline-block;vertical-align:middle;box-shadow:0 2px 4px rgba(239,68,68,0.2);">Logout</span>');
                }
            } else {
                location.reload();
            }
        },
        error: function() {
            window.location.href = requestUrl;
        }
    });
});
</script>
