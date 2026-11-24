<?php
$summaryCardChecked = !empty($this->request->data['Configuration']['show_receipt_summary_card']) && ((int)$this->request->data['Configuration']['show_receipt_summary_card'] === 1);
$printDateChecked = !empty($this->request->data['Configuration']['show_receipt_print_date']) && ((int)$this->request->data['Configuration']['show_receipt_print_date'] === 1);
?>
<style type="text/css">
.wv-config-page{background:#f5f8fc;border:1px solid #dbe5f0;border-radius:10px;padding:14px;}
.wv-config-hero{align-items:center;background:linear-gradient(135deg,#07152a 0%,#15304f 72%,#ff0a46 155%);border-radius:10px;color:#fff;display:flex;justify-content:space-between;margin-bottom:14px;padding:18px 20px;}
.wv-config-hero h2{color:#fff;font-size:22px;font-weight:900;margin:0;}
.wv-config-hero p{color:#cfe2ff;font-size:12px;font-weight:700;margin:4px 0 0;}
.wv-config-card{background:#fff;border:1px solid #dce6f2;border-radius:10px;box-shadow:0 12px 28px rgba(15,23,42,.06);margin-bottom:14px;padding:18px;}
.wv-config-card h4{align-items:center;border-bottom:1px solid #edf2f7;color:#07152a;display:flex;font-size:15px;font-weight:900;gap:8px;margin:0 0 16px;padding-bottom:10px;}
.wv-config-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px 18px;}
.wv-config-grid .full{grid-column:1/-1;}
.wv-config-field label.wv-label{color:#07152a;display:block;font-size:11px;font-weight:900;letter-spacing:.02em;margin:0 0 7px;text-align:left;text-transform:uppercase;}
.wv-config-field .form-control{background:#fbfdff;border:1px solid #cfdbea;border-radius:8px;box-shadow:none;color:#07152a;font-size:13px;font-weight:700;height:40px;max-width:100%;width:100%;}
.wv-config-field .form-control:focus{border-color:#1687ff;box-shadow:0 0 0 3px rgba(22,135,255,.12);}
.wv-config-help{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;color:#526277;font-size:12px;font-weight:700;line-height:1.5;margin-top:7px;padding:9px 10px;}
.wv-config-radio,.wv-config-toggle{align-items:center;background:#f8fafc;border:1px solid #dbe5f0;border-radius:8px;display:flex;gap:12px;min-height:40px;padding:8px 10px;}
.wv-config-radio label,.wv-config-toggle label{color:#07152a;font-size:12px;font-weight:800;margin:0 12px 0 0;}
.wv-config-radio input[type=radio],.wv-config-toggle input[type=checkbox]{height:18px;margin:0 6px 0 0;width:18px;}
.wv-config-toggle-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;}
.wv-config-toggle-item{background:#fbfdff;border:1px solid #dce6f2;border-radius:10px;padding:12px;}
.wv-config-toggle-item strong{color:#07152a;display:block;font-size:12px;font-weight:900;margin-bottom:9px;text-transform:uppercase;}
.wv-config-actions{align-items:center;background:#f8fafc;border:1px solid #dce6f2;border-radius:10px;display:flex;justify-content:flex-end;margin-top:16px;padding:14px;}
.wv-config-actions .btn{border-radius:8px;font-weight:900;padding:9px 16px;}
@media(max-width:1399px){.wv-config-grid{grid-template-columns:repeat(3,minmax(0,1fr));}}
@media(max-width:1199px){.wv-config-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:991px){.wv-config-grid,.wv-config-toggle-grid{grid-template-columns:1fr}.wv-config-hero{display:block}.wv-config-card{padding:14px}}
</style>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Configuration Options');?></h1>
    </div>
</div>

<div class="wv-config-page">
    <div class="wv-config-hero">
        <div>
            <h2><?php echo __('Configuration Options');?></h2>
            <p><?php echo __('Manage core website, portal, map, notification, and receipt settings.');?></p>
        </div>
        <div>
            <?php echo $this->Html->link('<span class="fa fa-map-marker"></span> '.__('Pakistan Address Hierarchy'), array('controller'=>'Addresshierarchies','action'=>'index'), array('escape'=>false,'class'=>'btn btn-info'));?>
        </div>
    </div>

    <?php echo $this->Session->flash();?>
    <?php echo $this->Form->create('Configuration', array('controller' => 'Configurations', 'action' => 'index', 'name'=>'post_req', 'id'=>'post_req', 'class'=>'form-horizontal', 'type'=>'file'));?>

        <div class="wv-config-card">
        <h4><span class="fa fa-picture-o"></span><?php echo __('Site Logo, Favicon & Social Media Sharing (WhatsApp / Facebook)');?></h4>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; height: 100%;">
                    <label class="wv-label"><i class="fa fa-image text-primary"></i> <?php echo __('Site Logo');?></label>
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 12px;">
                        <?php 
                        $currentLogo = !empty($this->request->data['Configuration']['photo']) ? $this->request->data['Configuration']['photo'] : '';
                        if (!empty($currentLogo)): ?>
                            <div style="background: #ffffff; padding: 8px 14px; border: 1px solid #cbd5e1; border-radius: 6px; display: inline-flex; align-items: center; min-height: 60px;">
                                <img src="<?php echo $this->Html->url('/img/' . $currentLogo); ?>" alt="Site Logo" style="max-height: 50px; max-width: 180px; object-fit: contain;">
                            </div>
                        <?php else: ?>
                            <div style="background: #e2e8f0; color: #64748b; padding: 12px 18px; border-radius: 6px; font-size: 12px; font-weight: 700;">
                                <?php echo __('No Logo Uploaded'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="wv-config-field mb-2">
                        <input type="file" name="logo_file" accept="image/*" class="form-control" style="padding-top: 6px;">
                    </div>
                    <div class="wv-config-help">
                        <i class="fa fa-info-circle text-info"></i> <?php echo __('Used across the portal & automatically sent as preview thumbnail when sharing your site link on <strong>WhatsApp, Facebook, Twitter/X, LinkedIn</strong>.'); ?>
                    </div>
                    <?php if (!empty($currentLogo)): ?>
                        <div class="mt-2" style="font-size: 12px; font-weight: 600;">
                            <label style="cursor: pointer; color: #d11242;">
                                <input type="checkbox" name="data[Configuration][remove_logo]" value="1"> <?php echo __('Remove / Delete Current Logo'); ?>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; height: 100%;">
                    <label class="wv-label"><i class="fa fa-bookmark text-primary"></i> <?php echo __('Site Favicon (Browser Tab Icon)');?></label>
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 12px;">
                        <?php 
                        $currentFav = !empty($this->request->data['Configuration']['favicon']) ? $this->request->data['Configuration']['favicon'] : '';
                        if (!empty($currentFav)): ?>
                            <div style="background: #ffffff; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; display: inline-flex; align-items: center;">
                                <img src="<?php echo $this->Html->url('/img/' . $currentFav); ?>" alt="Favicon" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                        <?php else: ?>
                            <div style="background: #e2e8f0; color: #64748b; padding: 8px 14px; border-radius: 6px; font-size: 12px; font-weight: 700;">
                                <img src="<?php echo $this->Html->url('/favicon.ico'); ?>" alt="Favicon" style="width: 24px; height: 24px; object-fit: contain; margin-right: 5px;" onerror="this.style.display='none'"> <?php echo __('Default Favicon'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="wv-config-field mb-2">
                        <input type="file" name="favicon_file" accept=".ico,.png,.jpg,.jpeg,.svg,.gif,.webp" class="form-control" style="padding-top: 6px;">
                    </div>
                    <div class="wv-config-help">
                        <i class="fa fa-info-circle text-info"></i> <?php echo __('Displayed on the browser tab, bookmark bar, and mobile home screen shortcut. Recommended: .ico or .png (32x32px or 64x64px).'); ?>
                    </div>
                    <?php if (!empty($currentFav)): ?>
                        <div class="mt-2" style="font-size: 12px; font-weight: 600;">
                            <label style="cursor: pointer; color: #d11242;">
                                <input type="checkbox" name="data[Configuration][remove_favicon]" value="1"> <?php echo __('Remove / Reset Favicon'); ?>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="wv-config-card">
        <h4><span class="fa fa-building"></span><?php echo __('Business Profile');?></h4>
        <div class="wv-config-grid">
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Site Name');?></label>
                <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__('Site Name'),'div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Organization Name');?></label>
                <?php echo $this->Form->input('organization_name',array('label' => false,'class'=>'form-control','placeholder'=>__('Organization Name'),'div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Domain Name');?></label>
                <?php echo $this->Form->input('domain_name',array('label' => false,'class'=>'form-control','placeholder'=>__('Domain Name'),'div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Organization Email');?></label>
                <?php echo $this->Form->input('email',array('label' => false,'class'=>'form-control','placeholder'=>__('Organization Email'),'div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Short Name');?></label>
                <?php echo $this->Form->input('short_name',array('label' => false,'class'=>'form-control','placeholder'=>__('Short Name'),'div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Contact Nos.');?></label>
                <?php echo $this->Form->input('contact',array('label' => false,'class'=>'form-control','placeholder'=>__('Contact No. should be comma seprated'),'div'=>false));?>
            </div>
            <div class="wv-config-field full">
                <label class="wv-label"><?php echo __('Address');?></label>
                <?php echo $this->Form->input('address',array('label' => false,'class'=>'form-control','placeholder'=>__('Address'),'div'=>false));?>
            </div>
            <div class="wv-config-field full">
                <label class="wv-label"><?php echo __('Account Details');?></label>
                <?php echo $this->Form->input('account_details',array('label' => false,'class'=>'form-control','placeholder'=>__('Account Details'),'div'=>false));?>
            </div>
        </div>
    </div>

    <div class="wv-config-card">
        <h4><span class="fa fa-search"></span><?php echo __('Website Meta & Localization');?></h4>
        <div class="wv-config-grid">
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Meta Title');?></label>
                <?php echo $this->Form->input('meta_title',array('label' => false,'class'=>'form-control','placeholder'=>__('Meta Title'),'div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Meta Description');?></label>
                <?php echo $this->Form->input('meta_desc',array('label' => false,'class'=>'form-control','placeholder'=>__('Meta Description'),'div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Time Zone');?></label>
                <?php echo $this->Form->select('timezone',$this->Time->listTimezones(),array('empty'=>__('Please Select'),'label' => false,'class'=>'form-control','div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Currency');?></label>
                <?php echo $this->Form->input('currency',array('options'=>$currency,'empty'=>false,'label' => false,'class'=>'form-control','div'=>false,'escape'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Language');?></label>
                <?php echo $this->Form->select('language',$language,array('empty'=>__('Please Select'),'label' => false,'class'=>'form-control','div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Text Direction');?></label>
                <div class="wv-config-radio">
                    <?php echo $this->Form->input('dir_type',array('type'=>'radio','options'=>array("1"=>__('Left-to-right (LTR)'),"0"=>__('Right-to-Left (RTL)')),'default'=>'1','legend'=>false,'before' => '<label class="radio-inline">','separator' => '</label><label class="radio-inline">','after'=>'</label>','label' => false,'div'=>false));?>
                </div>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Date Format');?></label>
                <?php echo $this->Form->input('date_format',array('label' => false,'class'=>'form-control','data-errormessage'=>__('Date Format Required'),'placeholder'=>__('Date Format'),'div'=>false));?>
                <div class="wv-config-help"><?php echo __('Date, Month, Year, Hour, Min, Sec, Meridian, Date Separator, Time Separator');?></div>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Capthca');?></label>
                <div class="wv-config-radio">
                    <?php echo $this->Form->input('captcha_type',array('type'=>'radio','options'=>array("1"=>__('Image'),"0"=>__('Text')),'default'=>'1','legend'=>false,'before' => '<label class="radio-inline">','separator' => '</label><label class="radio-inline">','after'=>'</label>','label' => false,'div'=>false));?>
                </div>
            </div>
        </div>
    </div>

    <div class="wv-config-card">
        <h4><span class="fa fa-list"></span><?php echo __('Listing, Map & Billing Defaults');?></h4>
        <div class="wv-config-grid">
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Display records per page');?></label>
                <?php echo $this->Form->input('min_limit',array('label' => false,'class'=>'form-control','placeholder'=>__('Display records per page'),'div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Max records per page');?></label>
                <?php echo $this->Form->input('max_limit',array('label' => false,'class'=>'form-control','placeholder'=>__('Max records per page'),'div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Map Key');?></label>
                <?php echo $this->Form->input('map_key',array('label' => false,'class'=>'form-control','placeholder'=>__('Map Key'),'div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Default Map Provider');?></label>
                <?php echo $this->Form->select('default_map_provider',$mapProviderOptions,array('label' => false,'class'=>'form-control','empty'=>false,'div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Due Days');?></label>
                <?php echo $this->Form->input('due_days',array('label' => false,'class'=>'form-control','placeholder'=>__('Number of Due Days'),'div'=>false));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __("Late Fees (% annually)");?></label>
                <?php echo $this->Form->input('late_fees',array('label' => false,'class'=>'form-control','placeholder'=>__('Late Fees (% annually)'),'div'=>false));?>
            </div>
        </div>
    </div>

    <div class="wv-config-card">
        <h4><span class="fa fa-toggle-on"></span><?php echo __('Switches & Frontend Display');?></h4>
        <div class="wv-config-toggle-grid">
            <div class="wv-config-toggle-item">
                <strong><?php echo __('Email Notification');?></strong>
                <div class="wv-config-toggle"><?php echo $this->Form->input('email_notification',array('label' => false,'class'=>'form-control','data-errormessage'=>__('Date Format Required'),'placeholder'=>__('Date Format'),'div'=>false));?></div>
            </div>
            <div class="wv-config-toggle-item">
                <strong><?php echo __('SMS Notification');?></strong>
                <div class="wv-config-toggle"><?php echo $this->Form->checkbox('sms_notification',array('label' => false,'div'=>false));?></div>
            </div>
            <div class="wv-config-toggle-item">
                <strong><?php echo __('Google Translation');?></strong>
                <div class="wv-config-toggle"><?php echo $this->Form->checkbox('translate',array('label' => false,'div'=>false));?></div>
            </div>
            <div class="wv-config-toggle-item">
                <strong><?php echo __('Receipt Summary Card');?></strong>
                <div class="wv-config-toggle"><?php echo $this->Form->checkbox('show_receipt_summary_card',array('label' => false,'div'=>false,'checked'=>$summaryCardChecked));?></div>
            </div>
            <div class="wv-config-toggle-item">
                <strong><?php echo __('Receipt Print Date');?></strong>
                <div class="wv-config-toggle"><?php echo $this->Form->checkbox('show_receipt_print_date',array('label' => false,'div'=>false,'checked'=>$printDateChecked));?></div>
            </div>
            <div class="wv-config-toggle-item">
                <strong><?php echo __('Front End Registration');?></strong>
                <div class="wv-config-toggle"><?php echo $this->Form->checkbox('front_end',array('label' => false,'div'=>false));?></div>
            </div>
            <div class="wv-config-toggle-item">
                <strong><?php echo __('Front End Slides');?></strong>
                <div class="wv-config-toggle"><?php echo $this->Form->checkbox('slides',array('label' => false,'div'=>false));?></div>
            </div>
            <div class="wv-config-toggle-item">
                <strong><?php echo __('Featured Ticker');?></strong>
                <div class="wv-config-toggle"><?php echo $this->Form->checkbox('featured_ticker_enabled',array('label' => false,'div'=>false));?></div>
            </div>
        </div>
        <div class="wv-config-help" style="margin-top:12px;"><?php echo __('Enable right-to-left featured property ticker on home');?></div>
    </div>

    <div class="wv-config-card">
        <h4><span class="fa fa-exchange"></span><?php echo __('Featured Ticker Settings');?></h4>
        <div class="wv-config-grid">
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Ticker Speed (seconds)');?></label>
                <?php echo $this->Form->input('featured_ticker_speed',array('label' => false,'class'=>'form-control','placeholder'=>__('Ticker Speed in seconds'),'div'=>false,'min'=>10,'max'=>180));?>
            </div>
            <div class="wv-config-field">
                <label class="wv-label"><?php echo __('Ticker Effect');?></label>
                <?php echo $this->Form->select('featured_ticker_effect',$featuredTickerEffectOptions,array('label' => false,'class'=>'form-control','empty'=>false,'div'=>false));?>
            </div>
        </div>
    </div>

    <div class="wv-config-card" style="border-left: 4px solid #9f002d;">
        <h4><span class="fa fa-shield text-danger"></span>&nbsp;<?php echo __('Deals & Financial Risk Management Settings');?></h4>
        <p style="font-size:12px;color:#526277;margin-bottom:14px;">
            <?php echo __('Control backend toggles for Deal Document Vault, Health & Recovery Risk Scoring, and Cash Flow Forecasting.'); ?>
        </p>
        <div class="wv-config-toggle-grid">
            <div class="wv-config-toggle-item">
                <strong><i class="fa fa-folder-open text-primary"></i> <?php echo __('Deal Document Vault');?></strong>
                <div class="wv-config-toggle">
                    <?php 
                    $vaultChecked = !isset($this->request->data['Configuration']['deal_vault_enabled']) || (int)$this->request->data['Configuration']['deal_vault_enabled'] === 1;
                    echo $this->Form->checkbox('deal_vault_enabled', array('label' => false, 'div' => false, 'checked' => $vaultChecked)); 
                    ?>
                    <label><?php echo __('Enable Document Vault tab inside deals');?></label>
                </div>
            </div>
            <div class="wv-config-toggle-item">
                <strong><i class="fa fa-heartbeat text-success"></i> <?php echo __('Deal Health & Risk Scoring');?></strong>
                <div class="wv-config-toggle">
                    <?php 
                    $riskChecked = !isset($this->request->data['Configuration']['deal_risk_score_enabled']) || (int)$this->request->data['Configuration']['deal_risk_score_enabled'] === 1;
                    echo $this->Form->checkbox('deal_risk_score_enabled', array('label' => false, 'div' => false, 'checked' => $riskChecked)); 
                    ?>
                    <label><?php echo __('Enable Healthy / Attention / Critical badging');?></label>
                </div>
            </div>
            <div class="wv-config-toggle-item">
                <strong><i class="fa fa-line-chart text-info"></i> <?php echo __('Cash Flow Forecasting');?></strong>
                <div class="wv-config-toggle">
                    <?php 
                    $cashflowChecked = !isset($this->request->data['Configuration']['deal_cashflow_forecast_enabled']) || (int)$this->request->data['Configuration']['deal_cashflow_forecast_enabled'] === 1;
                    echo $this->Form->checkbox('deal_cashflow_forecast_enabled', array('label' => false, 'div' => false, 'checked' => $cashflowChecked)); 
                    ?>
                    <label><?php echo __('Enable 30/60/90-Day Projected Cash Flow');?></label>
                </div>
            </div>
            <div class="wv-config-toggle-item" style="grid-column: 1 / -1; background: #fff5f7; border-color: #ffd6df;">
                <strong style="color:#9f002d;"><i class="fa fa-lock text-danger"></i> <?php echo __('Admin & Management Only Access');?></strong>
                <div class="wv-config-toggle" style="background:#fff;">
                    <?php 
                    $adminOnlyChecked = !isset($this->request->data['Configuration']['deal_features_admin_only']) || (int)$this->request->data['Configuration']['deal_features_admin_only'] === 1;
                    echo $this->Form->checkbox('deal_features_admin_only', array('label' => false, 'div' => false, 'checked' => $adminOnlyChecked)); 
                    ?>
                    <label style="color:#7a0022;"><?php echo __('Restrict Document Vault uploads/deletions, Health Score filters, and Cash Flow Forecasting strictly to Admin / CEO roles');?></label>
                </div>
            </div>
        </div>
    </div>

    <div class="wv-config-actions">
        <button type="submit" class="btn btn-success"><span class="fa fa-refresh"></span>&nbsp;<?php echo __('Save Settings');?></button>
    </div>

    <?php echo $this->Form->end();?>
</div>
