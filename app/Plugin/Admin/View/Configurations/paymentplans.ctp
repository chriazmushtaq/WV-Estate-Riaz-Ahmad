<?php
$selectedAgencyName = (!empty($agencyOptions[$projectId])) ? $agencyOptions[$projectId] : __('Selected Agency');
$page = !empty($this->request->data['AgencyPaymentPlanPage']) ? (array)$this->request->data['AgencyPaymentPlanPage'] : array();
$amountPlans = !empty($amountPlans) ? (array)$amountPlans : array();
$paymentPlanSettingsController = !empty($paymentPlanSettingsController) ? $paymentPlanSettingsController : $this->params['controller'];
$paymentPlanSettingsAction = !empty($paymentPlanSettingsAction) ? $paymentPlanSettingsAction : $this->params['action'];
?>
<style type="text/css">
.wv-plan-admin{background:#f5f8fc;border:1px solid #dbe5f0;border-radius:12px;margin:0 auto;max-width:1320px;padding:16px;}
.wv-plan-hero{align-items:center;background:linear-gradient(135deg,#07152a 0%,#15304f 78%,#ff0a46 150%);border-radius:12px;box-shadow:0 16px 34px rgba(15,23,42,.12);color:#fff;display:flex;justify-content:space-between;margin-bottom:16px;padding:20px 22px;}
.wv-plan-hero h2{color:#fff;font-size:22px;font-weight:900;margin:0;}
.wv-plan-hero p{color:#cfe2ff;font-size:12px;font-weight:700;margin:4px 0 0;}
.wv-plan-hero .btn{border-radius:8px;font-weight:900;margin-left:8px;}
.wv-plan-card{background:#fff;border:1px solid #dce6f2;border-radius:12px;box-shadow:0 14px 30px rgba(15,23,42,.06);margin-bottom:16px;padding:20px;}
.wv-plan-card h4{align-items:center;border-bottom:1px solid #edf2f7;color:#07152a;display:flex;font-size:16px;font-weight:900;gap:8px;margin:0 0 18px;padding-bottom:12px;}
.wv-plan-grid{display:grid;grid-template-columns:repeat(2,minmax(240px,1fr));gap:14px 18px;}
.wv-plan-grid.three{grid-template-columns:repeat(4,minmax(180px,1fr));}
.wv-plan-grid.single{grid-template-columns:repeat(2,minmax(240px,1fr));max-width:none;}
.wv-plan-grid.single .wv-plan-field:last-child{grid-column:1/-1;}
.wv-plan-grid .full{grid-column:1/-1;}
.wv-plan-field label,.wv-plan-label{color:#07152a;display:block;font-size:11px;font-weight:900;letter-spacing:.02em;margin:0 0 7px;text-align:left;text-transform:uppercase;}
.wv-plan-field .form-control{background:#fbfdff;border:1px solid #cfdbea;border-radius:8px;box-shadow:none;color:#07152a;font-size:13px;font-weight:800;min-height:42px;padding:9px 11px;width:100%;}
.wv-plan-field textarea.form-control{line-height:1.55;min-height:120px;resize:vertical;}
.wv-plan-field .form-control:focus{border-color:#1687ff;box-shadow:0 0 0 3px rgba(22,135,255,.12);}
.wv-plan-toolbar{align-items:flex-end;background:#fff;border:1px solid #dce6f2;border-radius:12px;box-shadow:0 10px 22px rgba(15,23,42,.04);display:flex;gap:10px;margin-bottom:16px;padding:14px;}
.wv-plan-toolbar .form-control{border-radius:8px;max-width:300px;}
.wv-plan-form-layout{display:block;margin:0 auto;max-width:1240px;}
.wv-plan-form-layout .wv-plan-card,.wv-plan-form-layout .wv-plan-actions{margin-bottom:16px;}
.wv-plan-form-layout .wv-plan-full{grid-column:auto;}
.wv-plan-table{border-collapse:collapse;width:100%;}
.wv-plan-table th{background:#f1f5f9;color:#07152a;font-size:10px;font-weight:900;padding:10px;text-transform:uppercase;white-space:nowrap;}
.wv-plan-table td{border-top:1px solid #e2e8f0;padding:7px;}
.wv-plan-table input{background:#fbfdff;border:1px solid #cfdbea;border-radius:7px;color:#07152a;font-size:12px;font-weight:800;height:38px;padding:7px 9px;width:100%;}
.wv-plan-table textarea{background:#fbfdff;border:1px solid #cfdbea;border-radius:7px;color:#07152a;font-size:12px;font-weight:800;line-height:1.45;min-height:82px;padding:7px 9px;width:100%;}
.wv-plan-table .wv-row-action{width:82px;}
.wv-plan-table.wv-amount-plans{min-width:1220px;}
.wv-amount-title-row td{background:#f8fafc;border-top:0;padding:10px 12px 16px;}
.wv-amount-title-row .wv-plan-grid{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px;}
.wv-plan-mini-actions{align-items:center;display:flex;gap:8px;justify-content:flex-end;margin:-4px 0 14px;}
.wv-plan-mini-actions .btn{border-radius:8px;font-size:12px;font-weight:900;}
.wv-remove-plan-row{border-radius:7px;font-size:11px;font-weight:900;padding:7px 9px;}
.wv-plan-toggle{align-items:center;background:#f8fafc;border:1px solid #dce6f2;border-radius:10px;display:flex;gap:10px;min-height:42px;padding:10px 12px;}
.wv-plan-toggle input{height:18px;margin:0;width:18px;}
.wv-plan-toggle span{color:#07152a;font-size:12px;font-weight:900;text-transform:uppercase;}
.wv-plan-help{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;color:#526277;font-size:12px;font-weight:700;line-height:1.5;margin-top:8px;padding:9px 10px;}
.wv-plan-actions{align-items:center;background:#f8fafc;border:1px solid #dce6f2;border-radius:12px;display:flex;justify-content:flex-end;margin-top:0;padding:16px;}
.wv-plan-actions .btn{border-radius:8px;font-weight:900;padding:9px 16px;}
@media(max-width:1199px){.wv-plan-grid.three{grid-template-columns:repeat(3,minmax(180px,1fr));}}
@media(max-width:991px){.wv-plan-hero,.wv-plan-toolbar{display:block}.wv-plan-grid,.wv-plan-grid.three,.wv-plan-grid.single{grid-template-columns:1fr}.wv-plan-grid.single .wv-plan-field:last-child{grid-column:auto}.wv-plan-card{padding:14px}.wv-plan-table{min-width:760px}.wv-plan-table-wrap{overflow:auto}.wv-plan-hero .btn{margin:10px 6px 0 0}}
</style>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Agency Payment Plan Pages');?></h1>
    </div>
</div>

<div class="wv-plan-admin">
    <div class="wv-plan-hero">
        <div>
            <h2><?php echo __('Payment Plan Page Settings');?></h2>
            <p><?php echo h($selectedAgencyName);?> - <?php echo __('control the public apartments payment plan page for this agency.');?></p>
        </div>
        <div>
            <?php echo $this->Html->link('<span class="fa fa-cogs"></span> '.__('General Configuration'),array('controller'=>'Configurations','action'=>'index'),array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php if(!empty($publicUrl)){ echo $this->Html->link('<span class="fa fa-external-link"></span> '.__('Open Public Page'),$publicUrl,array('escape'=>false,'class'=>'btn btn-info','target'=>'_blank')); } ?>
        </div>
    </div>

    <?php echo $this->Session->flash();?>

    <?php if(empty($agencyOptions) && !empty($paymentPlanTableSql)){ ?>
    <div class="wv-plan-card">
        <h4><span class="fa fa-database"></span><?php echo __('Create Payment Plan Settings Table');?></h4>
        <div class="wv-plan-help" style="margin-bottom:12px;">
            <?php echo __('The separate agency payment-plan settings table is missing. Try the button below first. If your cPanel database user cannot create tables, import the SQL shown here in phpMyAdmin.');?>
        </div>
        <p>
            <?php echo $this->Html->link('<span class="fa fa-magic"></span> '.__('Create Table Now'),array('controller'=>'PaymentPlanSettings','action'=>'installtable','?' => array('refresh_menu'=>1)),array('escape'=>false,'class'=>'btn btn-success'));?>
        </p>
        <div class="wv-plan-field">
            <label><?php echo __('SQL for phpMyAdmin');?></label>
            <textarea class="form-control" rows="12" readonly="readonly"><?php echo h($paymentPlanTableSql);?></textarea>
            <div class="wv-plan-help"><?php echo __('File also available at');?> app/Config/payment_plan_settings_table.sql</div>
        </div>
    </div>
    <?php } ?>

    <?php if(!empty($agencyOptions)){ ?>
        <?php if(!empty($isSuperAdminSession)){ ?>
        <div class="wv-plan-toolbar">
            <?php echo $this->Form->create('PaymentPlanFilter', array('type'=>'get','url'=>array('controller'=>$paymentPlanSettingsController,'action'=>$paymentPlanSettingsAction)));?>
            <label class="wv-plan-label"><?php echo __('Agency');?></label>
            <?php echo $this->Form->select('project_id',$agencyOptions,array('value'=>$projectId,'empty'=>false,'class'=>'form-control','name'=>'project_id','div'=>false));?>
            <button type="submit" class="btn btn-primary"><span class="fa fa-refresh"></span> <?php echo __('Load Agency');?></button>
            <?php echo $this->Form->end();?>
        </div>
        <?php } ?>

        <?php echo $this->Form->create('AgencyPaymentPlanPage', array('url'=>array('controller'=>$paymentPlanSettingsController,'action'=>$paymentPlanSettingsAction,'?' => array('project_id'=>$projectId)), 'type'=>'post', 'class'=>'form-horizontal'));?>
        <?php echo $this->Form->hidden('id');?>
        <?php echo $this->Form->hidden('project_id', array('value'=>$projectId));?>

        <div class="wv-plan-form-layout">
        <div class="wv-plan-card">
            <h4><span class="fa fa-globe"></span><?php echo __('Public Page');?></h4>
            <div class="wv-plan-grid">
                <div class="wv-plan-field">
                    <label><?php echo __('Page Status');?></label>
                    <label class="wv-plan-toggle">
                        <?php echo $this->Form->checkbox('enabled',array('div'=>false,'label'=>false,'checked'=>!isset($page['enabled']) || !empty($page['enabled'])));?>
                        <span><?php echo __('Show this agency page publicly');?></span>
                    </label>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Public Slug');?></label>
                    <?php echo $this->Form->input('slug',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>'white-villas-lahore'));?>
                    <div class="wv-plan-help"><?php echo __('URL will be');?>: /paymentplans/<?php echo h(!empty($page['slug']) ? $page['slug'] : 'agency-slug');?></div>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Page Title');?></label>
                    <?php echo $this->Form->input('page_title',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Agency Payment Plan')));?>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Highlights Title');?></label>
                    <?php echo $this->Form->input('highlights_title',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Latest availability highlights')));?>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Subtitle');?></label>
                    <?php echo $this->Form->input('hero_subtitle',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Flexible booking and installment details')));?>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Unit Label');?></label>
                    <?php echo $this->Form->input('unit_label',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Apartment')));?>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Contact Button Text');?></label>
                    <?php echo $this->Form->input('cta_label',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Contact Agency')));?>
                </div>
            </div>
        </div>

        <div class="wv-plan-card">
            <h4><span class="fa fa-calculator"></span><?php echo __('Main Amounts');?></h4>
            <div class="wv-plan-mini-actions">
                <button type="button" class="btn btn-info" id="wvAddAmountPlan"><span class="fa fa-plus"></span> <?php echo __('Add Another Plan');?></button>
            </div>
            <div class="wv-plan-help"><?php echo __('Titles and footer for the main/default plan. Extra added plans have their own title/footer fields below.');?></div>
            <div class="wv-plan-grid single" style="margin-top:12px;">
                <div class="wv-plan-field">
                    <label><?php echo __('Main Amounts Title');?></label>
                    <?php echo $this->Form->input('summary_title',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Main Amounts')));?>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Footer Title');?></label>
                    <?php echo $this->Form->input('footer_title',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Need updated availability?')));?>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Footer Text');?></label>
                    <?php echo $this->Form->input('footer_text',array('type'=>'textarea','label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Footer note shown at bottom of the public page')));?>
                </div>
            </div>
            <div class="wv-plan-help" style="margin-top:14px;"><?php echo __('Main amount values shown at the top of the public payment plan page.');?></div>
            <div class="wv-plan-grid three">
                <div class="wv-plan-field">
                    <label><?php echo __('Booking Amount');?></label>
                    <?php echo $this->Form->input('booking_amount',array('label'=>false,'div'=>false,'class'=>'form-control'));?>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Confirmation Amount');?></label>
                    <?php echo $this->Form->input('confirmation_amount',array('label'=>false,'div'=>false,'class'=>'form-control'));?>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Allotment Amount');?></label>
                    <?php echo $this->Form->input('allotment_amount',array('label'=>false,'div'=>false,'class'=>'form-control'));?>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Monthly Installment');?></label>
                    <?php echo $this->Form->input('monthly_installment',array('label'=>false,'div'=>false,'class'=>'form-control'));?>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Bi-Annualy Installment');?></label>
                    <?php echo $this->Form->input('quarterly_installment',array('label'=>false,'div'=>false,'class'=>'form-control'));?>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Possession Amount');?></label>
                    <?php echo $this->Form->input('possession_amount',array('label'=>false,'div'=>false,'class'=>'form-control'));?>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Total Price');?></label>
                    <?php echo $this->Form->input('total_price',array('label'=>false,'div'=>false,'class'=>'form-control'));?>
                </div>
            </div>
            <div class="wv-plan-help" style="margin-top:14px;"><?php echo __('Use the rows below when this agency has more than one apartment/payment plan, such as different sizes or categories.');?></div>
            <div class="wv-plan-table-wrap" style="margin-top:12px;">
                <table class="wv-plan-table wv-amount-plans">
                    <thead>
                        <tr>
                            <th><?php echo __('Plan Title');?></th>
                            <th><?php echo __('Unit');?></th>
                            <th><?php echo __('Booking');?></th>
                            <th><?php echo __('Confirmation');?></th>
                            <th><?php echo __('Allotment');?></th>
                            <th><?php echo __('Monthly');?></th>
                            <th><?php echo __('Bi-Annualy');?></th>
                            <th><?php echo __('Possession');?></th>
                            <th><?php echo __('Total');?></th>
                            <th><?php echo __('Note');?></th>
                            <th class="wv-row-action"><?php echo __('Action');?></th>
                        </tr>
                    </thead>
                    <tbody id="wvAmountPlansBody">
                        <?php foreach((array)$amountPlans as $index => $row){ ?>
                        <tr>
                            <td><input type="text" name="data[AmountPlan][<?php echo (int)$index;?>][plan_title]" value="<?php echo h(isset($row['plan_title']) ? $row['plan_title'] : '');?>"></td>
                            <td><input type="text" name="data[AmountPlan][<?php echo (int)$index;?>][unit_label]" value="<?php echo h(isset($row['unit_label']) ? $row['unit_label'] : '');?>"></td>
                            <td><input type="text" name="data[AmountPlan][<?php echo (int)$index;?>][booking_amount]" value="<?php echo h(isset($row['booking_amount']) ? $row['booking_amount'] : '');?>"></td>
                            <td><input type="text" name="data[AmountPlan][<?php echo (int)$index;?>][confirmation_amount]" value="<?php echo h(isset($row['confirmation_amount']) ? $row['confirmation_amount'] : '');?>"></td>
                            <td><input type="text" name="data[AmountPlan][<?php echo (int)$index;?>][allotment_amount]" value="<?php echo h(isset($row['allotment_amount']) ? $row['allotment_amount'] : '');?>"></td>
                            <td><input type="text" name="data[AmountPlan][<?php echo (int)$index;?>][monthly_installment]" value="<?php echo h(isset($row['monthly_installment']) ? $row['monthly_installment'] : '');?>"></td>
                            <td><input type="text" name="data[AmountPlan][<?php echo (int)$index;?>][quarterly_installment]" value="<?php echo h(isset($row['quarterly_installment']) ? $row['quarterly_installment'] : '');?>"></td>
                            <td><input type="text" name="data[AmountPlan][<?php echo (int)$index;?>][possession_amount]" value="<?php echo h(isset($row['possession_amount']) ? $row['possession_amount'] : '');?>"></td>
                            <td><input type="text" name="data[AmountPlan][<?php echo (int)$index;?>][total_price]" value="<?php echo h(isset($row['total_price']) ? $row['total_price'] : '');?>"></td>
                            <td><input type="text" name="data[AmountPlan][<?php echo (int)$index;?>][note]" value="<?php echo h(isset($row['note']) ? $row['note'] : '');?>"></td>
                            <td><button type="button" class="btn btn-danger wv-remove-amount-plan"><span class="fa fa-trash"></span></button></td>
                        </tr>
                        <tr class="wv-amount-title-row">
                            <td colspan="11">
                                <div class="wv-plan-grid single">
                                    <div class="wv-plan-field">
                                        <label><?php echo __('Main Amounts Title');?></label>
                                        <input type="text" name="data[AmountPlan][<?php echo (int)$index;?>][summary_title]" value="<?php echo h(isset($row['summary_title']) ? $row['summary_title'] : '');?>">
                                    </div>
                                    <div class="wv-plan-field">
                                        <label><?php echo __('Highlights Title');?></label>
                                        <input type="text" name="data[AmountPlan][<?php echo (int)$index;?>][highlights_title]" value="<?php echo h(isset($row['highlights_title']) ? $row['highlights_title'] : '');?>">
                                    </div>
                                    <div class="wv-plan-field">
                                        <label><?php echo __('Footer Title');?></label>
                                        <input type="text" name="data[AmountPlan][<?php echo (int)$index;?>][footer_title]" value="<?php echo h(isset($row['footer_title']) ? $row['footer_title'] : '');?>">
                                    </div>
                                    <div class="wv-plan-field">
                                        <label><?php echo __('Footer Text');?></label>
                                        <textarea name="data[AmountPlan][<?php echo (int)$index;?>][footer_text]"><?php echo h(isset($row['footer_text']) ? $row['footer_text'] : '');?></textarea>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="wv-plan-card">
            <h4><span class="fa fa-check-circle"></span><?php echo __('Contact & SEO');?></h4>
            <div class="wv-plan-grid">
                <div class="wv-plan-field">
                    <label><?php echo __('Contact Heading');?></label>
                    <?php echo $this->Form->input('contact_heading',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Talk to this agency')));?>
                </div>
                <div class="wv-plan-field">
                    <label><?php echo __('Meta Title');?></label>
                    <?php echo $this->Form->input('meta_title',array('label'=>false,'div'=>false,'class'=>'form-control'));?>
                </div>
                <div class="wv-plan-field full">
                    <label><?php echo __('Meta Description');?></label>
                    <?php echo $this->Form->input('meta_desc',array('label'=>false,'div'=>false,'class'=>'form-control'));?>
                </div>
            </div>
        </div>

        <div class="wv-plan-actions">
            <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Payment Plan Page');?></button>
        </div>
        </div>
        <?php echo $this->Form->end();?>
        <script type="text/javascript">
        (function(){
            var nextAmountPlanIndex = <?php echo (int)count((array)$amountPlans);?>;
            function amountInput(index, key, placeholder){
                return '<input type="text" name="data[AmountPlan][' + index + '][' + key + ']" value="" placeholder="' + placeholder + '">';
            }
            function amountTextarea(index, key, placeholder){
                return '<textarea name="data[AmountPlan][' + index + '][' + key + ']" placeholder="' + placeholder + '"></textarea>';
            }
            function amountTitleFields(index){
                return '<td colspan="11"><div class="wv-plan-grid single">' +
                    '<div class="wv-plan-field"><label><?php echo h(__('Main Amounts Title'));?></label>' + amountInput(index, 'summary_title', '<?php echo h(__('Main Amounts'));?>') + '</div>' +
                    '<div class="wv-plan-field"><label><?php echo h(__('Highlights Title'));?></label>' + amountInput(index, 'highlights_title', '<?php echo h(__('Latest availability highlights'));?>') + '</div>' +
                    '<div class="wv-plan-field"><label><?php echo h(__('Footer Title'));?></label>' + amountInput(index, 'footer_title', '<?php echo h(__('Need updated availability?'));?>') + '</div>' +
                    '<div class="wv-plan-field"><label><?php echo h(__('Footer Text'));?></label>' + amountTextarea(index, 'footer_text', '<?php echo h(__('Footer note shown with this plan'));?>') + '</div>' +
                    '</div></td>';
            }
            function addAmountPlan(){
                var body = document.getElementById('wvAmountPlansBody');
                if(!body){ return; }
                var index = nextAmountPlanIndex++;
                var row = document.createElement('tr');
                var titleRow = document.createElement('tr');
                titleRow.className = 'wv-amount-title-row';
                row.innerHTML =
                    '<td>' + amountInput(index, 'plan_title', '<?php echo h(__('Plan title'));?>') + '</td>' +
                    '<td>' + amountInput(index, 'unit_label', '<?php echo h(__('Unit'));?>') + '</td>' +
                    '<td>' + amountInput(index, 'booking_amount', '<?php echo h(__('Booking'));?>') + '</td>' +
                    '<td>' + amountInput(index, 'confirmation_amount', '<?php echo h(__('Confirmation'));?>') + '</td>' +
                    '<td>' + amountInput(index, 'allotment_amount', '<?php echo h(__('Allotment'));?>') + '</td>' +
                    '<td>' + amountInput(index, 'monthly_installment', '<?php echo h(__('Monthly'));?>') + '</td>' +
                    '<td>' + amountInput(index, 'quarterly_installment', '<?php echo h(__('Bi-Annualy'));?>') + '</td>' +
                    '<td>' + amountInput(index, 'possession_amount', '<?php echo h(__('Possession'));?>') + '</td>' +
                    '<td>' + amountInput(index, 'total_price', '<?php echo h(__('Total'));?>') + '</td>' +
                    '<td>' + amountInput(index, 'note', '<?php echo h(__('Note'));?>') + '</td>' +
                    '<td><button type="button" class="btn btn-danger wv-remove-amount-plan"><span class="fa fa-trash"></span></button></td>';
                titleRow.innerHTML = amountTitleFields(index);
                body.appendChild(row);
                body.appendChild(titleRow);
                if(row.scrollIntoView){ row.scrollIntoView({behavior:'smooth', block:'center'}); }
            }
            var amountAdd = document.getElementById('wvAddAmountPlan');
            if(amountAdd){ amountAdd.onclick = addAmountPlan; }
            document.addEventListener('click', function(e){
                var target = e.target;
                while(target && target !== document && (!target.className || String(target.className).indexOf('wv-remove-amount-plan') === -1)){
                    target = target.parentNode;
                }
                if(target && target.className && String(target.className).indexOf('wv-remove-amount-plan') !== -1){
                    var amountRow = target;
                    while(amountRow && amountRow.tagName !== 'TR'){ amountRow = amountRow.parentNode; }
                    if(amountRow && amountRow.parentNode){
                        var titleRow = amountRow.nextElementSibling;
                        if(titleRow && titleRow.className && String(titleRow.className).indexOf('wv-amount-title-row') !== -1){
                            titleRow.parentNode.removeChild(titleRow);
                        }
                        amountRow.parentNode.removeChild(amountRow);
                    }
                }
            });
        })();
        </script>
    <?php } ?>
</div>
