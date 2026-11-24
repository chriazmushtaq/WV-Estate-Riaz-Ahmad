<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-whatsapp" style="color:#25D366;"></i> <?php echo __('Meta WhatsApp Cloud API Multi-Project Automation Platform');?></h1>
        <p class="description"><?php echo __('Configure Master and Isolated WhatsApp Gateways per Agency/Project with granular toggles, lead alerts, deals, and receipts.');?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-heading" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div class="btn-group" style="display:flex;flex-wrap:wrap;gap:4px;">
            <?php echo $this->Html->link('<i class="fa fa-dashboard"></i> '.__('Overview'), array('action'=>'index'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-cogs"></i> '.__('API Accounts'), array('action'=>'settings'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-users"></i> '.__('Agent Looper'), array('action'=>'agents'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-list-alt"></i> '.__('Form Mapping'), array('action'=>'forms'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-bell"></i> '.__('Notifications'), array('action'=>'notifications'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-whatsapp"></i> '.__('WhatsApp API'), array('action'=>'whatsapp'), array('class'=>'btn btn-success', 'escape'=>false, 'style'=>'background:#25D366;border-color:#25D366;font-weight:700;'));?>
            <?php echo $this->Html->link('<i class="fa fa-history"></i> '.__('Sync Logs'), array('action'=>'logs'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-book"></i> '.__('Documentation'), array('action'=>'docs'), array('class'=>'btn btn-default', 'escape'=>false));?>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#projectGatewayModal" style="font-weight:700;">
                <i class="fa fa-plus-circle"></i> <?php echo __('+ Add Isolated Project WhatsApp API');?>
            </button>
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#testWaModal" style="font-weight:700;color:#166534;border-color:#bbf7d0;background:#f0fdf4;">
                <i class="fa fa-paper-plane" style="color:#22c55e;"></i> <?php echo __('Send Test WhatsApp');?>
            </button>
        </div>
    </div>

    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <!-- 4 Top KPI & Gateway Health Cards -->
        <div class="row" style="margin-bottom:24px;">
            <!-- Gateway Status Card -->
            <div class="col-md-3 col-sm-6" style="margin-bottom:12px;">
                <div style="background:#ffffff;border:1.5px solid #e2e8f0;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.04);position:relative;overflow:hidden;">
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;letter-spacing:0.5px;">Master Gateway</div>
                    <div style="margin-top:6px;display:flex;align-items:center;gap:8px;">
                        <?php if(!empty($waSettings['WhatsappSetting']['is_active']) && !empty($waSettings['WhatsappSetting']['phone_number_id'])){ ?>
                            <span class="badge badge-success" style="background:#22c55e;font-size:13px;padding:4px 10px;">
                                <i class="fa fa-check-circle"></i> Master Connected
                            </span>
                        <?php } else { ?>
                            <span class="badge badge-warning" style="background:#f59e0b;font-size:13px;padding:4px 10px;">
                                <i class="fa fa-exclamation-triangle"></i> Needs Credentials
                            </span>
                        <?php } ?>
                    </div>
                    <div style="margin-top:8px;font-size:11px;color:#64748b;">
                        Isolated Gateways: <strong><?php echo count($projectGateways);?> Active</strong>
                    </div>
                </div>
            </div>

            <!-- Total WhatsApp Sent Card -->
            <div class="col-md-3 col-sm-6" style="margin-bottom:12px;">
                <div style="background:#ffffff;border:1.5px solid #e2e8f0;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;letter-spacing:0.5px;">Total WhatsApp Sent</div>
                    <div style="font-size:24px;font-weight:800;color:#0f172a;margin-top:4px;">
                        <?php echo number_format($totalSent);?> <span style="font-size:12px;font-weight:normal;color:#10b981;"><i class="fa fa-arrow-up"></i> Live</span>
                    </div>
                    <div style="margin-top:4px;font-size:11px;color:#ef4444;">
                        Failed: <?php echo number_format($totalFailed);?>
                    </div>
                </div>
            </div>

            <!-- Today's Activity Card -->
            <div class="col-md-3 col-sm-6" style="margin-bottom:12px;">
                <div style="background:#ffffff;border:1.5px solid #e2e8f0;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;letter-spacing:0.5px;">Today's Dispatches</div>
                    <div style="font-size:24px;font-weight:800;color:#0284c7;margin-top:4px;">
                        <?php echo number_format($todaySent);?> <span style="font-size:12px;font-weight:normal;color:#64748b;">messages</span>
                    </div>
                    <div style="margin-top:4px;font-size:11px;color:#64748b;">
                        Automated Background Dispatch
                    </div>
                </div>
            </div>

            <!-- Categories Breakdown Card -->
            <div class="col-md-3 col-sm-6" style="margin-bottom:12px;">
                <div style="background:#ffffff;border:1.5px solid #e2e8f0;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;letter-spacing:0.5px;">Categories Sent</div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px;">
                        <span class="label label-info" style="font-size:10px;">Agents: <?php echo $agentAlertsCount;?></span>
                        <span class="label label-success" style="font-size:10px;background:#22c55e;">Clients: <?php echo $clientWelcomeCount;?></span>
                        <span class="label label-primary" style="font-size:10px;">Deals: <?php echo $dealsCount;?></span>
                        <span class="label label-warning" style="font-size:10px;">Letters: <?php echo $lettersCount;?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= ISOLATED MULTI-AGENCY & PROJECT WHATSAPP GATEWAYS TABLE ================= -->
        <div class="panel panel-default" style="border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:24px;border:1.5px solid #e2e8f0;">
            <div class="panel-heading" style="background:#f8fafc;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                <div>
                    <h4 style="margin:0;font-weight:800;color:#0f172a;font-size:16px;">
                        <i class="fa fa-building text-primary"></i> <?php echo __('Isolated Agency / Branch WhatsApp API Gateways');?>
                    </h4>
                    <p style="margin:4px 0 0 0;font-size:12px;color:#64748b;">
                        <?php echo __('Assign distinct Meta WhatsApp Cloud Phone Numbers per Project / Brand, or share between agencies.');?>
                    </p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#projectGatewayModal" style="font-weight:700;">
                    <i class="fa fa-plus"></i> <?php echo __('Add Isolated Project Gateway');?>
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;vertical-align:middle;font-size:13px;">
                    <thead>
                        <tr style="background:#f1f5f9;color:#334155;">
                            <th><?php echo __('Gateway / Brand Name');?></th>
                            <th><?php echo __('Agency / Branch Name (e.g. White Villas Lahore, White Villas Awami Block)');?></th>
                            <th><?php echo __('Sender WhatsApp Number');?></th>
                            <th><?php echo __('Phone Number ID');?></th>
                            <th><?php echo __('Dispatched');?></th>
                            <th><?php echo __('Status');?></th>
                            <th style="width:100px;text-align:center;"><?php echo __('Actions');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Master Global Row -->
                        <tr style="background:#f8fafc;">
                            <td>
                                <strong style="color:#0f172a;"><i class="fa fa-star text-warning"></i> Master Agency Gateway (Global Default)</strong>
                                <div style="font-size:11px;color:#64748b;">Used for general CRM dispatches & unmapped projects</div>
                            </td>
                            <td><span class="badge badge-default">Global Fallback</span></td>
                            <td><code><?php echo !empty($cfg['Configuration']['contact']) ? h($cfg['Configuration']['contact']) : '+92 339 1101156';?></code></td>
                            <td><code><?php echo !empty($waSettings['WhatsappSetting']['phone_number_id']) ? h($waSettings['WhatsappSetting']['phone_number_id']) : 'Not set';?></code></td>
                            <td><span class="badge badge-info"><?php echo (int)$totalSent;?> sent</span></td>
                            <td>
                                <?php if(!empty($waSettings['WhatsappSetting']['is_active'])){ ?>
                                    <span class="label label-success" style="background:#22c55e;">Master Active</span>
                                <?php } else { ?>
                                    <span class="label label-danger">Disabled</span>
                                <?php } ?>
                            </td>
                            <td style="text-align:center;"><span class="text-muted"><i class="fa fa-lock"></i> Default</span></td>
                        </tr>

                        <!-- Custom Project Gateways -->
                        <?php if(!empty($projectGateways)){ foreach($projectGateways as $gw){ $g = $gw['WhatsappProjectGateway']; ?>
                        <tr>
                            <td>
                                <strong style="color:#0284c7;font-size:14px;"><?php echo h($g['gateway_name']);?></strong>
                                <?php if(!empty($gw['FacebookAccount']['account_name'])){ ?>
                                    <div style="font-size:11px;color:#64748b;"><i class="fa fa-facebook-square"></i> FB Page: <?php echo h($gw['FacebookAccount']['account_name']);?></div>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if(!empty($gw['Project']['name'])){ ?>
                                    <span class="badge badge-primary" style="background:#3b82f6;"><?php echo h($gw['Project']['name']);?></span>
                                <?php } else { ?>
                                    <span class="text-muted">Shared Across Selected Pages</span>
                                <?php } ?>
                            </td>
                            <td><strong><?php echo h($g['sender_phone_number']);?></strong></td>
                            <td><code><?php echo h($g['phone_number_id']);?></code></td>
                            <td><span class="badge badge-success" style="background:#22c55e;"><?php echo (int)$g['total_sent'];?> sent</span></td>
                            <td>
                                <?php if(!empty($g['is_active'])){ ?>
                                    <span class="label label-success" style="background:#22c55e;"><i class="fa fa-check"></i> Active</span>
                                <?php } else { ?>
                                    <span class="label label-warning">Paused</span>
                                <?php } ?>
                            </td>
                            <td style="text-align:center;">
                                <button type="button" class="btn btn-xs btn-default btn-edit-gateway" data-json='<?php echo json_encode($g);?>' title="Edit Gateway"><i class="fa fa-pencil text-primary"></i></button>
                                <?php echo $this->Html->link('<i class="fa fa-trash"></i>', array('action'=>'delete_project_gateway', $g['id']), array('class'=>'btn btn-danger btn-xs', 'escape'=>false, 'confirm'=>__('Delete this isolated WhatsApp gateway?')));?>
                            </td>
                        </tr>
                        <?php }} ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= PROJECT & PAGE GATEWAY ROUTING MAPPING CARD ================= -->
        <div class="panel panel-default" style="border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:24px;border:1.5px solid #e2e8f0;">
            <div class="panel-heading" style="background:#f8fafc;padding:16px 20px;">
                <h4 style="margin:0;font-weight:800;color:#0f172a;font-size:16px;">
                    <i class="fa fa-random text-info"></i> <?php echo __('Project & Facebook Page WhatsApp Gateway Selector / Inheritance');?>
                </h4>
                <p style="margin:4px 0 0 0;font-size:12px;color:#64748b;">
                    <?php echo __('Select which WhatsApp Phone Number / Gateway each Project or Facebook Page should use when sending alerts.');?>
                </p>
            </div>

            <div class="panel-body" style="padding:24px;">
                <?php echo $this->Form->create(false, array('url'=>array('plugin'=>'admin', 'controller'=>'facebook_integrations', 'action'=>'save_gateway_mapping')));?>
                <div class="row">
                    <!-- Projects Gateway Routing -->
                    <div class="col-md-6" style="margin-bottom:16px;">
                        <h5 style="font-weight:800;color:#0f172a;margin-top:0;margin-bottom:12px;">
                            <i class="fa fa-building-o text-primary"></i> <?php echo __('Agencies / Branches WhatsApp Senders (White Villas Lahore, White Villas Awami Block, etc.):');?>
                        </h5>
                        <?php if(!empty($projects)){ foreach($projects as $p){ ?>
                            <div class="form-group" style="margin-bottom:12px;background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                                <label style="margin:0;font-weight:700;color:#0f172a;width:45%;"><?php echo h($p['Project']['name']);?>:</label>
                                <select name="ProjectGatewayMapping[<?php echo (int)$p['Project']['id'];?>]" class="form-control" style="width:55%;height:36px;font-size:12px;">
                                    <option value=""><?php echo __('● Master Agency Gateway (Global)');?></option>
                                    <?php if(!empty($projectGateways)){ foreach($projectGateways as $gw){ $g = $gw['WhatsappProjectGateway']; ?>
                                        <option value="<?php echo (int)$g['id'];?>" <?php echo (!empty($p['Project']['whatsapp_gateway_id']) && (int)$p['Project']['whatsapp_gateway_id'] === (int)$g['id']) ? 'selected' : '';?>>
                                            <?php echo h($g['gateway_name']);?> (<?php echo h($g['sender_phone_number']);?>)
                                        </option>
                                    <?php }} ?>
                                </select>
                            </div>
                        <?php }} ?>
                    </div>

                    <!-- Facebook Pages Gateway Routing -->
                    <div class="col-md-6" style="margin-bottom:16px;">
                        <h5 style="font-weight:800;color:#0f172a;margin-top:0;margin-bottom:12px;">
                            <i class="fa fa-facebook-square text-primary"></i> <?php echo __('Facebook Pages WhatsApp Senders:');?>
                        </h5>
                        <?php if(!empty($accounts)){ foreach($accounts as $acc){ $a = $acc['FacebookAccount']; ?>
                            <div class="form-group" style="margin-bottom:12px;background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                                <label style="margin:0;font-weight:700;color:#0f172a;width:45%;"><?php echo h($a['account_name']);?>:</label>
                                <select name="AccountGatewayMapping[<?php echo (int)$a['id'];?>]" class="form-control" style="width:55%;height:36px;font-size:12px;">
                                    <option value=""><?php echo __('● Master Agency Gateway (Global)');?></option>
                                    <?php if(!empty($projectGateways)){ foreach($projectGateways as $gw){ $g = $gw['WhatsappProjectGateway']; ?>
                                        <option value="<?php echo (int)$g['id'];?>" <?php echo (!empty($a['whatsapp_gateway_id']) && (int)$a['whatsapp_gateway_id'] === (int)$g['id']) ? 'selected' : '';?>>
                                            <?php echo h($g['gateway_name']);?> (<?php echo h($g['sender_phone_number']);?>)
                                        </option>
                                    <?php }} ?>
                                </select>
                            </div>
                        <?php }} ?>
                    </div>
                </div>

                <div style="margin-top:8px;">
                    <button type="submit" class="btn btn-primary" style="font-weight:700;">
                        <i class="fa fa-save"></i> <?php echo __('Save Gateway Routing Mappings');?>
                    </button>
                </div>
                <?php echo $this->Form->end();?>
            </div>
        </div>

        <!-- ================= MASTER GATEWAY SETTINGS FORM ================= -->
        <?php echo $this->Form->create(false, array('url' => array('plugin'=>'admin', 'controller'=>'facebook_integrations', 'action' => 'save_whatsapp'), 'id' => 'waSettingsForm')); ?>

        <!-- Master Credentials Card -->
        <div class="panel panel-default" style="border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:24px;border:1.5px solid #e2e8f0;">
            <div class="panel-heading" style="background:#f8fafc;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                <div>
                    <h4 style="margin:0;font-weight:800;color:#0f172a;font-size:16px;">
                        <i class="fa fa-key text-primary"></i> <?php echo __('Master Agency WhatsApp Cloud API Gateway Credentials');?>
                    </h4>
                    <p style="margin:4px 0 0 0;font-size:12px;color:#64748b;">
                        <?php echo __('Default gateway used for all general CRM notifications and shared projects.');?>
                    </p>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <label style="font-weight:800;color:#0f172a;margin:0;font-size:13px;"><?php echo __('Master Switch:');?></label>
                    <label class="switch">
                        <input type="checkbox" name="is_active" value="1" <?php echo !empty($waSettings['WhatsappSetting']['is_active']) ? 'checked' : '';?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="panel-body" style="padding:24px;">
                <!-- Quick Meta WhatsApp Direct Links & Step-by-Step Guide Banner -->
                <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:18px;margin-bottom:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
                        <h5 style="margin:0;font-weight:800;color:#1e40af;font-size:14px;">
                            <i class="fa fa-info-circle text-primary"></i> <?php echo __('How to Get Your Meta WhatsApp Cloud API Credentials in 3 Minutes:');?>
                        </h5>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            <a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-xs" style="background:#1877f2;border-color:#1877f2;font-weight:700;">
                                <i class="fa fa-th-large"></i> 1. Meta Apps Dashboard <i class="fa fa-external-link"></i>
                            </a>
                            <a href="https://business.facebook.com/settings/system-users" target="_blank" rel="noopener noreferrer" class="btn btn-default btn-xs" style="font-weight:700;color:#1e40af;border-color:#bfdbfe;background:#fff;">
                                <i class="fa fa-user-secret"></i> 2. System Users (Token) <i class="fa fa-external-link"></i>
                            </a>
                            <a href="https://business.facebook.com/wa/manage/message-templates/" target="_blank" rel="noopener noreferrer" class="btn btn-default btn-xs" style="font-weight:700;color:#166534;border-color:#bbf7d0;background:#fff;">
                                <i class="fa fa-commenting"></i> 3. WhatsApp Templates <i class="fa fa-external-link"></i>
                            </a>
                            <a href="https://developers.facebook.com/tools/debug/accesstoken/" target="_blank" rel="noopener noreferrer" class="btn btn-default btn-xs" style="font-weight:700;color:#d97706;border-color:#fed7aa;background:#fff;">
                                <i class="fa fa-shield"></i> 4. Token Debugger <i class="fa fa-external-link"></i>
                            </a>
                        </div>
                    </div>

                    <div class="row" style="margin-top:10px;">
                        <div class="col-md-4" style="margin-bottom:10px;">
                            <div style="background:#fff;border:1px solid #dbeafe;border-radius:8px;padding:12px;height:100%;">
                                <strong style="color:#1e40af;font-size:12px;display:block;margin-bottom:4px;">
                                    <span class="badge" style="background:#1877f2;padding:3px 6px;">Step 1</span> Phone Number ID & WABA ID
                                </strong>
                                <p style="font-size:11px;color:#334155;margin:0;line-height:1.4;">
                                    Go to <a href="https://developers.facebook.com/apps/" target="_blank" style="font-weight:700;color:#1877f2;">Meta Apps</a> &gt; Click your App &gt; Left sidebar click <strong>WhatsApp &gt; API Setup</strong>. Copy your <strong>Phone Number ID</strong> and <strong>WhatsApp Business Account ID</strong>.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4" style="margin-bottom:10px;">
                            <div style="background:#fff;border:1px solid #dbeafe;border-radius:8px;padding:12px;height:100%;">
                                <strong style="color:#1e40af;font-size:12px;display:block;margin-bottom:4px;">
                                    <span class="badge" style="background:#1877f2;padding:3px 6px;">Step 2</span> Permanent Access Token
                                </strong>
                                <p style="font-size:11px;color:#334155;margin:0;line-height:1.4;">
                                    In <a href="https://business.facebook.com/settings/system-users" target="_blank" style="font-weight:700;color:#1877f2;">Meta Business Suite</a>, create an Admin System User ➔ Click <strong>Generate New Token</strong> ➔ Select App ➔ Check <code>whatsapp_business_messaging</code> &amp; <code>whatsapp_business_management</code>.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4" style="margin-bottom:10px;">
                            <div style="background:#fff;border:1px solid #dbeafe;border-radius:8px;padding:12px;height:100%;">
                                <strong style="color:#1e40af;font-size:12px;display:block;margin-bottom:4px;">
                                    <span class="badge" style="background:#10b981;padding:3px 6px;">Step 3</span> 1-Click Fast Test
                                </strong>
                                <p style="font-size:11px;color:#334155;margin:0;line-height:1.4;">
                                    Paste your Phone Number ID and Permanent Token below, click <strong>Save</strong>, then click <strong>"Send Test WhatsApp"</strong> to verify instant live delivery to your mobile phone!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-weight:700;color:#0f172a;"><?php echo __('Master WhatsApp Phone Number ID:');?> *</label>
                            <input type="text" name="phone_number_id" class="form-control" value="<?php echo h(!empty($waSettings['WhatsappSetting']['phone_number_id']) ? $waSettings['WhatsappSetting']['phone_number_id'] : '');?>" placeholder="e.g. 104829104829104" style="height:40px;border-radius:6px;" required>
                            <p class="help-block" style="font-size:11px;color:#64748b;margin-top:4px;">From Meta for Developers &gt; WhatsApp &gt; API Setup.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-weight:700;color:#0f172a;"><?php echo __('WhatsApp Business Account (WABA) ID:');?></label>
                            <input type="text" name="waba_id" class="form-control" value="<?php echo h(!empty($waSettings['WhatsappSetting']['waba_id']) ? $waSettings['WhatsappSetting']['waba_id'] : '');?>" placeholder="e.g. 192837465019283" style="height:40px;border-radius:6px;">
                            <p class="help-block" style="font-size:11px;color:#64748b;margin-top:4px;">Your Meta WhatsApp Business Account ID.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-weight:700;color:#0f172a;"><?php echo __('Graph API Version:');?></label>
                            <select name="api_version" class="form-control" style="height:40px;border-radius:6px;">
                                <option value="v19.0" <?php echo (!empty($waSettings['WhatsappSetting']['api_version']) && $waSettings['WhatsappSetting']['api_version'] === 'v19.0') ? 'selected' : '';?>>v19.0 (Recommended)</option>
                                <option value="v20.0" <?php echo (!empty($waSettings['WhatsappSetting']['api_version']) && $waSettings['WhatsappSetting']['api_version'] === 'v20.0') ? 'selected' : '';?>>v20.0</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-weight:700;color:#0f172a;"><?php echo __('Permanent Meta User / System Access Token:');?> *</label>
                    <div class="input-group">
                        <input type="password" id="inputWaToken" name="access_token" class="form-control" value="<?php echo h(!empty($waSettings['WhatsappSetting']['access_token']) ? $waSettings['WhatsappSetting']['access_token'] : '');?>" placeholder="EAA..." style="height:40px;font-family:monospace;font-size:12px;">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" id="btnToggleWaToken" style="height:40px;"><i class="fa fa-eye"></i> Show</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Granular Notification Toggles (5 Trigger Switches) -->
        <div class="panel panel-default" style="border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:24px;border:1.5px solid #e2e8f0;">
            <div class="panel-heading" style="background:#f8fafc;padding:16px 20px;">
                <h4 style="margin:0;font-weight:800;color:#0f172a;font-size:16px;">
                    <i class="fa fa-sliders text-success"></i> <?php echo __('Automated Notification Triggers (On / Off Controls)');?>
                </h4>
            </div>

            <div class="panel-body" style="padding:24px;">
                <div class="row">
                    <!-- Toggle 1: FB Lead Agent Alert -->
                    <div class="col-md-4 col-sm-6" style="margin-bottom:16px;">
                        <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:18px;">
                            <label style="font-weight:700;color:#0f172a;display:block;margin-bottom:8px;">
                                <i class="fa fa-user-secret text-primary"></i> <?php echo __('1. FB Lead - Agent WhatsApp Alert');?>
                            </label>
                            <label class="switch">
                                <input type="checkbox" name="notify_fb_agent" value="1" <?php echo !empty($waSettings['WhatsappSetting']['notify_fb_agent']) ? 'checked' : '';?>>
                                <span class="slider round"></span>
                            </label>
                            <div style="font-size:11px;color:#64748b;margin-top:8px;">Instant WhatsApp lead breakdown & CRM link to assigned sales agent.</div>
                        </div>
                    </div>

                    <!-- Toggle 2: FB Lead Client Welcome -->
                    <div class="col-md-4 col-sm-6" style="margin-bottom:16px;">
                        <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:18px;">
                            <label style="font-weight:700;color:#0f172a;display:block;margin-bottom:8px;">
                                <i class="fa fa-paper-plane text-success"></i> <?php echo __('2. FB Lead - Client Welcome');?>
                            </label>
                            <label class="switch">
                                <input type="checkbox" name="notify_fb_client" value="1" <?php echo !empty($waSettings['WhatsappSetting']['notify_fb_client']) ? 'checked' : '';?>>
                                <span class="slider round"></span>
                            </label>
                            <div style="font-size:11px;color:#64748b;margin-top:8px;">Instant branded WhatsApp introduction to lead client with advisor contact.</div>
                        </div>
                    </div>

                    <!-- Toggle 3: Deals & Bookings -->
                    <div class="col-md-4 col-sm-6" style="margin-bottom:16px;">
                        <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:18px;">
                            <label style="font-weight:700;color:#0f172a;display:block;margin-bottom:8px;">
                                <i class="fa fa-handshake-o text-warning"></i> <?php echo __('3. Deals & Booking Milestones');?>
                            </label>
                            <label class="switch">
                                <input type="checkbox" name="notify_deals" value="1" <?php echo !empty($waSettings['WhatsappSetting']['notify_deals']) ? 'checked' : '';?>>
                                <span class="slider round"></span>
                            </label>
                            <div style="font-size:11px;color:#64748b;margin-top:8px;">WhatsApp alert when unit is booked or deal progresses in CRM.</div>
                        </div>
                    </div>

                    <!-- Toggle 4: Official Letters & Receipts -->
                    <div class="col-md-6 col-sm-6" style="margin-bottom:16px;">
                        <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:18px;">
                            <label style="font-weight:700;color:#0f172a;display:block;margin-bottom:8px;">
                                <i class="fa fa-file-text-o text-danger"></i> <?php echo __('4. Official Letters, Receipts & Notices');?>
                            </label>
                            <label class="switch">
                                <input type="checkbox" name="notify_letters" value="1" <?php echo !empty($waSettings['WhatsappSetting']['notify_letters']) ? 'checked' : '';?>>
                                <span class="slider round"></span>
                            </label>
                            <div style="font-size:11px;color:#64748b;margin-top:8px;">WhatsApp alert when payment receipt, allotment, or demand notice is issued.</div>
                        </div>
                    </div>

                    <!-- Toggle 5: Scheduled Follow-ups -->
                    <div class="col-md-6 col-sm-6" style="margin-bottom:16px;">
                        <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:18px;">
                            <label style="font-weight:700;color:#0f172a;display:block;margin-bottom:8px;">
                                <i class="fa fa-clock-o text-info"></i> <?php echo __('5. Follow-Up Reminders to Agents');?>
                            </label>
                            <label class="switch">
                                <input type="checkbox" name="notify_followups" value="1" <?php echo !empty($waSettings['WhatsappSetting']['notify_followups']) ? 'checked' : '';?>>
                                <span class="slider round"></span>
                            </label>
                            <div style="font-size:11px;color:#64748b;margin-top:8px;">Sends reminder to sales agents for pending/overdue lead follow-ups.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WhatsApp Message Templates Customizer -->
        <div class="panel panel-default" style="border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:24px;border:1.5px solid #e2e8f0;">
            <div class="panel-heading" style="background:#f8fafc;padding:16px 20px;">
                <h4 style="margin:0;font-weight:800;color:#0f172a;font-size:16px;">
                    <i class="fa fa-commenting text-success"></i> <?php echo __('WhatsApp Message Templates & Multi-Brand Placeholders');?>
                </h4>
            </div>

            <div class="panel-body" style="padding:24px;">
                <div class="nav-tabs-custom" style="box-shadow:none;">
                    <ul class="nav nav-tabs" style="border-bottom:2px solid #e2e8f0;font-weight:700;">
                        <li class="active">
                            <a href="#wa_tab_agent" data-toggle="tab"><i class="fa fa-user-secret text-primary"></i> <?php echo __('Agent Lead Alert');?></a>
                        </li>
                        <li>
                            <a href="#wa_tab_client" data-toggle="tab"><i class="fa fa-paper-plane text-success"></i> <?php echo __('Client Welcome Intro');?></a>
                        </li>
                        <li>
                            <a href="#wa_tab_deals" data-toggle="tab"><i class="fa fa-handshake-o text-warning"></i> <?php echo __('Deals & Bookings');?></a>
                        </li>
                        <li>
                            <a href="#wa_tab_letters" data-toggle="tab"><i class="fa fa-file-text-o text-danger"></i> <?php echo __('Letters & Receipts');?></a>
                        </li>
                        <li>
                            <a href="#wa_tab_followups" data-toggle="tab"><i class="fa fa-clock-o text-info"></i> <?php echo __('Follow-Up Reminder');?></a>
                        </li>
                    </ul>

                    <div class="tab-content" style="padding:20px 0;">
                        <!-- Tab 1: Agent Lead Alert -->
                        <div class="tab-pane active" id="wa_tab_agent">
                            <div class="form-group">
                                <label style="font-weight:700;color:#0f172a;"><?php echo __('WhatsApp Message Body (Dispatched immediately to assigned agent):');?></label>
                                <textarea name="template_fb_agent" rows="8" class="form-control" style="font-family:monospace;font-size:13px;border-radius:8px;line-height:1.5;"><?php echo h(!empty($waSettings['WhatsappSetting']['template_fb_agent']) ? $waSettings['WhatsappSetting']['template_fb_agent'] : '');?></textarea>
                            </div>
                        </div>

                        <!-- Tab 2: Client Welcome Intro -->
                        <div class="tab-pane" id="wa_tab_client">
                            <div class="form-group">
                                <label style="font-weight:700;color:#0f172a;"><?php echo __('WhatsApp Message Body (Dispatched immediately to lead client):');?></label>
                                <textarea name="template_fb_client" rows="8" class="form-control" style="font-family:monospace;font-size:13px;border-radius:8px;line-height:1.5;"><?php echo h(!empty($waSettings['WhatsappSetting']['template_fb_client']) ? $waSettings['WhatsappSetting']['template_fb_client'] : '');?></textarea>
                            </div>
                        </div>

                        <!-- Tab 3: Deals & Bookings -->
                        <div class="tab-pane" id="wa_tab_deals">
                            <div class="form-group">
                                <label style="font-weight:700;color:#0f172a;"><?php echo __('WhatsApp Message Body (Deal creation / milestone update):');?></label>
                                <textarea name="template_deals" rows="8" class="form-control" style="font-family:monospace;font-size:13px;border-radius:8px;line-height:1.5;"><?php echo h(!empty($waSettings['WhatsappSetting']['template_deals']) ? $waSettings['WhatsappSetting']['template_deals'] : '');?></textarea>
                            </div>
                        </div>

                        <!-- Tab 4: Letters & Receipts -->
                        <div class="tab-pane" id="wa_tab_letters">
                            <div class="form-group">
                                <label style="font-weight:700;color:#0f172a;"><?php echo __('WhatsApp Message Body (Official letter / payment receipt issued):');?></label>
                                <textarea name="template_letters" rows="8" class="form-control" style="font-family:monospace;font-size:13px;border-radius:8px;line-height:1.5;"><?php echo h(!empty($waSettings['WhatsappSetting']['template_letters']) ? $waSettings['WhatsappSetting']['template_letters'] : '');?></textarea>
                            </div>
                        </div>

                        <!-- Tab 5: Follow-Up Reminder -->
                        <div class="tab-pane" id="wa_tab_followups">
                            <div class="form-group">
                                <label style="font-weight:700;color:#0f172a;"><?php echo __('WhatsApp Message Body (Agent follow-up reminder):');?></label>
                                <textarea name="template_followups" rows="8" class="form-control" style="font-family:monospace;font-size:13px;border-radius:8px;line-height:1.5;"><?php echo h(!empty($waSettings['WhatsappSetting']['template_followups']) ? $waSettings['WhatsappSetting']['template_followups'] : '');?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Merge Tags Guide -->
                <div style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:10px;padding:16px;margin-bottom:20px;">
                    <div style="font-weight:800;font-size:13px;color:#0f172a;margin-bottom:8px;">
                        <i class="fa fa-tags text-primary"></i> <?php echo __('Available WhatsApp Parameter Tags:');?>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        <code>{lead_name}</code>
                        <code>{lead_mobile}</code>
                        <code>{lead_email}</code>
                        <code>{lead_city}</code>
                        <code>{lead_remarks}</code>
                        <code>{page_name}</code>
                        <code>{project_name}</code>
                        <code>{form_name}</code>
                        <code>{agent_name}</code>
                        <code>{agent_mobile}</code>
                        <code>{agent_whatsapp_link}</code>
                        <code>{deal_unit}</code>
                        <code>{deal_stage}</code>
                        <code>{deal_amount}</code>
                        <code>{letter_type}</code>
                        <code>{receipt_no}</code>
                        <code>{agency_name}</code>
                        <code>{agency_phone}</code>
                        <code>{agency_domain}</code>
                        <code>{agency_address}</code>
                        <code>{crm_link}</code>
                        <code>{date_time}</code>
                    </div>
                </div>

                <!-- Save Button -->
                <div>
                    <button type="submit" class="btn btn-success btn-lg" style="font-weight:800;padding:12px 36px;border-radius:8px;box-shadow:0 4px 14px rgba(34,197,94,0.3);background:#22c55e;border-color:#22c55e;">
                        <i class="fa fa-save"></i> <?php echo __('Save WhatsApp Settings & Templates');?>
                    </button>
                </div>
            </div>
        </div>
        <?php echo $this->Form->end(); ?>

        <!-- ================= RECENT WHATSAPP DISPATCH LOGS ================= -->
        <div class="panel panel-default" style="border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
            <div class="panel-heading" style="background:#f8fafc;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h4 style="margin:0;font-weight:800;color:#0f172a;font-size:16px;">
                        <i class="fa fa-history text-primary"></i> <?php echo __('Recent WhatsApp Messages Dispatch History');?>
                    </h4>
                </div>
                <span class="badge badge-info"><?php echo count($recentLogs);?> <?php echo __('Recent Logs');?></span>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;vertical-align:middle;font-size:13px;">
                    <thead>
                        <tr style="background:#f1f5f9;color:#334155;">
                            <th><?php echo __('Status');?></th>
                            <th><?php echo __('Sender Gateway');?></th>
                            <th><?php echo __('Recipient');?></th>
                            <th><?php echo __('Mobile Number');?></th>
                            <th><?php echo __('Category / Trigger');?></th>
                            <th><?php echo __('Brand / Project');?></th>
                            <th><?php echo __('Message Preview');?></th>
                            <th><?php echo __('Dispatched Time');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($recentLogs)){ foreach($recentLogs as $log){ $l = $log['WhatsappLog']; ?>
                        <tr>
                            <td>
                                <?php if($l['status'] === 'Sent'){ ?>
                                    <span class="label label-success" style="background:#22c55e;"><i class="fa fa-check"></i> Sent</span>
                                <?php } elseif($l['status'] === 'Pending Setup'){ ?>
                                    <span class="label label-warning"><i class="fa fa-clock-o"></i> Pending Setup</span>
                                <?php } else { ?>
                                    <span class="label label-danger" title="<?php echo h($l['error_message']);?>"><i class="fa fa-times"></i> Failed</span>
                                <?php } ?>
                            </td>
                            <td>
                                <strong style="font-size:12px;color:#0f172a;"><?php echo !empty($l['gateway_name']) ? h($l['gateway_name']) : 'Master Agency Gateway';?></strong>
                            </td>
                            <td>
                                <strong><?php echo h($l['recipient_name']);?></strong>
                                <div style="font-size:11px;color:#64748b;"><?php echo h($l['recipient_type']);?></div>
                            </td>
                            <td><code><?php echo h($l['recipient_mobile']);?></code></td>
                            <td>
                                <span class="badge badge-info" style="background:#0284c7;font-size:11px;">
                                    <?php echo h(str_replace('_', ' ', $l['message_category']));?>
                                </span>
                            </td>
                            <td>
                                <?php echo !empty($l['page_name']) ? h($l['page_name']) : (!empty($l['project_name']) ? h($l['project_name']) : '-');?>
                            </td>
                            <td>
                                <div style="max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:12px;color:#475569;" title="<?php echo h($l['message_body']);?>">
                                    <?php echo h($l['message_body']);?>
                                </div>
                            </td>
                            <td style="font-size:12px;color:#64748b;white-space:nowrap;">
                                <?php echo date('d-M-Y h:i A', strtotime($l['created']));?>
                            </td>
                        </tr>
                        <?php }} else { ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted" style="padding:40px;">
                                <i class="fa fa-whatsapp fa-3x" style="color:#cbd5e1;margin-bottom:10px;"></i>
                                <p style="font-weight:600;font-size:14px;margin:0;"><?php echo __('No WhatsApp messages dispatched yet.');?></p>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================= ADD / EDIT ISOLATED PROJECT GATEWAY MODAL ================= -->
<div class="modal fade" id="projectGatewayModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">
            <?php echo $this->Form->create('WhatsappProjectGateway', array('url'=>array('plugin'=>'admin', 'controller'=>'facebook_integrations', 'action'=>'save_project_gateway'), 'id'=>'projectGatewayForm'));?>
            <input type="hidden" name="data[WhatsappProjectGateway][id]" id="gwModalId" value="">
            
            <div class="modal-header" style="background:#0f172a;color:#fff;padding:16px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" style="font-weight:800;" id="gwModalTitle">
                    <i class="fa fa-plus-circle text-success"></i> <?php echo __('Add Isolated Project / Agency WhatsApp API Gateway');?>
                </h4>
            </div>

            <div class="modal-body" style="padding:24px;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-weight:700;color:#0f172a;"><?php echo __('Gateway / Brand Display Name:');?> *</label>
                            <input type="text" name="data[WhatsappProjectGateway][gateway_name]" id="gwModalName" class="form-control" placeholder="e.g. White Alpine Lodges Official WhatsApp" required style="height:40px;border-radius:6px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-weight:700;color:#0f172a;"><?php echo __('Sender WhatsApp Phone Number:');?> *</label>
                            <input type="text" name="data[WhatsappProjectGateway][sender_phone_number]" id="gwModalPhone" class="form-control" placeholder="e.g. +92 300 1234567" required style="height:40px;border-radius:6px;">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-weight:700;color:#0f172a;"><?php echo __('Assign to Agency / Branch (White Villas Lahore, White Villas Awami Block, etc.):');?></label>
                            <select name="data[WhatsappProjectGateway][project_id]" id="gwModalProjectId" class="form-control" style="height:40px;border-radius:6px;">
                                <option value=""><?php echo __('-- Shared Across All Agencies --');?></option>
                                <?php if(!empty($projects)){ foreach($projects as $p){ ?>
                                    <option value="<?php echo (int)$p['Project']['id'];?>"><?php echo h($p['Project']['name']);?></option>
                                <?php }} ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-weight:700;color:#0f172a;"><?php echo __('Link to Facebook Page (Optional):');?></label>
                            <select name="data[WhatsappProjectGateway][facebook_account_id]" id="gwModalAccountId" class="form-control" style="height:40px;border-radius:6px;">
                                <option value=""><?php echo __('-- None / Standalone --');?></option>
                                <?php if(!empty($accounts)){ foreach($accounts as $acc){ $a = $acc['FacebookAccount']; ?>
                                    <option value="<?php echo (int)$a['id'];?>"><?php echo h($a['account_name']);?> (Page ID: <?php echo h($a['page_id']);?>)</option>
                                <?php }} ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-weight:700;color:#0f172a;"><?php echo __('WhatsApp Phone Number ID:');?> *</label>
                            <input type="text" name="data[WhatsappProjectGateway][phone_number_id]" id="gwModalPhoneId" class="form-control" placeholder="From Meta WhatsApp API Setup" required style="height:40px;border-radius:6px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-weight:700;color:#0f172a;"><?php echo __('WhatsApp Business Account (WABA) ID:');?></label>
                            <input type="text" name="data[WhatsappProjectGateway][waba_id]" id="gwModalWabaId" class="form-control" placeholder="e.g. 192837465019283" style="height:40px;border-radius:6px;">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-weight:700;color:#0f172a;"><?php echo __('Meta Permanent Access Token:');?> *</label>
                    <input type="password" name="data[WhatsappProjectGateway][access_token]" id="gwModalToken" class="form-control" placeholder="EAA..." required style="height:40px;font-family:monospace;font-size:12px;border-radius:6px;">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-weight:700;color:#0f172a;"><?php echo __('Graph API Version:');?></label>
                            <select name="data[WhatsappProjectGateway][api_version]" id="gwModalApiVer" class="form-control" style="height:40px;border-radius:6px;">
                                <option value="v19.0">v19.0</option>
                                <option value="v20.0">v20.0</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-weight:700;color:#0f172a;"><?php echo __('Gateway Status:');?></label>
                            <select name="data[WhatsappProjectGateway][is_active]" id="gwModalActive" class="form-control" style="height:40px;border-radius:6px;">
                                <option value="1">Active</option>
                                <option value="0">Paused / Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;padding:16px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel');?></button>
                <button type="submit" class="btn btn-success" style="background:#22c55e;border-color:#22c55e;font-weight:800;padding:8px 24px;">
                    <i class="fa fa-save"></i> <?php echo __('Save Project Gateway');?>
                </button>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>

<!-- ================= FAST TEST WHATSAPP MODAL ================= -->
<div class="modal fade" id="testWaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">
            <div class="modal-header" style="background:#25D366;color:#fff;padding:16px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" style="font-weight:800;">
                    <i class="fa fa-whatsapp"></i> <?php echo __('Send Test WhatsApp Message');?>
                </h4>
            </div>
            <div class="modal-body" style="padding:20px;">
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:700;"><?php echo __('Recipient Phone / WhatsApp Number:');?></label>
                    <input type="text" id="testWaPhone" class="form-control" placeholder="e.g. 03001234567 or 923001234567" style="height:40px;">
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:700;"><?php echo __('Message Content:');?></label>
                    <textarea id="testWaMessage" rows="4" class="form-control">Hello! This is a test WhatsApp message from White Villas CRM Meta Cloud API.</textarea>
                </div>
                <div id="testWaFeedback" style="margin-top:10px;font-weight:700;font-size:13px;"></div>
            </div>
            <div class="modal-footer" style="background:#f8fafc;padding:12px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                <button type="button" class="btn btn-success" id="btnSendTestWa" style="background:#25D366;border-color:#25D366;font-weight:800;">
                    <i class="fa fa-paper-plane"></i> <?php echo __('Send Test WhatsApp Now');?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.switch { position: relative; display: inline-block; width: 48px; height: 26px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; -webkit-transition: .3s; transition: .3s; }
.slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; -webkit-transition: .3s; transition: .3s; box-shadow:0 2px 4px rgba(0,0,0,0.2); }
input:checked + .slider { background-color: #22c55e; }
input:focus + .slider { box-shadow: 0 0 1px #22c55e; }
input:checked + .slider:before { -webkit-transform: translateX(22px); -ms-transform: translateX(22px); transform: translateX(22px); }
.slider.round { border-radius: 26px; }
.slider.round:before { border-radius: 50%; }
</style>

<script type="text/javascript">
$(document).ready(function() {
    $('#btnToggleWaToken').on('click', function() {
        var $inp = $('#inputWaToken');
        if($inp.attr('type') === 'password') {
            $inp.attr('type', 'text');
            $(this).html('<i class="fa fa-eye-slash"></i> Hide');
        } else {
            $inp.attr('type', 'password');
            $(this).html('<i class="fa fa-eye"></i> Show');
        }
    });

    // Edit Gateway modal loader
    $('.btn-edit-gateway').on('click', function() {
        var g = $(this).data('json');
        if(g) {
            $('#gwModalId').val(g.id);
            $('#gwModalTitle').html('<i class="fa fa-pencil text-primary"></i> Edit WhatsApp Gateway: ' + g.gateway_name);
            $('#gwModalName').val(g.gateway_name);
            $('#gwModalPhone').val(g.sender_phone_number);
            $('#gwModalProjectId').val(g.project_id || '');
            $('#gwModalAccountId').val(g.facebook_account_id || '');
            $('#gwModalPhoneId').val(g.phone_number_id);
            $('#gwModalWabaId').val(g.waba_id);
            $('#gwModalToken').val(g.access_token);
            $('#gwModalApiVer').val(g.api_version || 'v19.0');
            $('#gwModalActive').val(g.is_active);
            $('#projectGatewayModal').modal('show');
        }
    });

    $('#projectGatewayModal').on('hidden.bs.modal', function() {
        $('#projectGatewayForm')[0].reset();
        $('#gwModalId').val('');
        $('#gwModalTitle').html('<i class="fa fa-plus-circle text-success"></i> <?php echo __("Add Isolated Project / Agency WhatsApp API Gateway");?>');
    });

    $('#btnSendTestWa').on('click', function() {
        var $btn = $(this);
        var phone = $('#testWaPhone').val().trim();
        var msg = $('#testWaMessage').val().trim();
        var $fb = $('#testWaFeedback');

        if(phone === '') {
            $fb.html('<span class="text-danger"><i class="fa fa-times-circle"></i> Please enter a phone number.</span>');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
        $fb.html('<span class="text-muted"><i class="fa fa-spinner fa-spin"></i> Dispatching via Meta Cloud API...</span>');

        $.ajax({
            url: '<?php echo $this->Html->url(array("action"=>"send_test_whatsapp"));?>',
            type: 'POST',
            data: { phone: phone, message: msg },
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?php echo __("Send Test WhatsApp Now");?>');
                if(res.success) {
                    $fb.html('<span class="text-success"><i class="fa fa-check-circle"></i> ' + res.message + (res.meta_id ? ' (Meta ID: ' + res.meta_id + ')' : '') + '</span>');
                } else {
                    $fb.html('<span class="text-danger"><i class="fa fa-times-circle"></i> ' + res.message + '</span>');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?php echo __("Send Test WhatsApp Now");?>');
                $fb.html('<span class="text-danger"><i class="fa fa-times-circle"></i> Connection failed to server.</span>');
            }
        });
    });
});
</script>