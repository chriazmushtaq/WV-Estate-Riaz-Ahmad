<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-bell text-warning"></i> <?php echo __('Facebook Lead Notifications & Templates');?></h1>
        <p class="description"><?php echo __('Configure automated SMS & Email alerts for assigned agents and professional welcome emails for lead clients.');?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-heading" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div class="btn-group" style="display:flex;flex-wrap:wrap;gap:4px;">
            <?php echo $this->Html->link('<i class="fa fa-dashboard"></i> '.__('Overview'), array('action'=>'index'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-cogs"></i> '.__('API Accounts'), array('action'=>'settings'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-users"></i> '.__('Agent Looper'), array('action'=>'agents'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-list-alt"></i> '.__('Form Mapping'), array('action'=>'forms'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-bell"></i> '.__('Notifications'), array('action'=>'notifications'), array('class'=>'btn btn-primary', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-whatsapp"></i> '.__('WhatsApp API'), array('action'=>'whatsapp'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-history"></i> '.__('Sync Logs'), array('action'=>'logs'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-envelope-o"></i> '.__('Notification Logs'), array('action'=>'notification_logs'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-book"></i> '.__('Documentation'), array('action'=>'docs'), array('class'=>'btn btn-default', 'escape'=>false));?>
        </div>

        <div>
            <?php echo $this->Html->link('<i class="fa fa-refresh fa-spin-hover"></i> '.__('Sync All Accounts Now'), array('action'=>'sync_now'), array('class'=>'btn btn-success', 'escape'=>false, 'style'=>'font-weight:700;'));?>
        </div>
    </div>

    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <!-- Lead Notifications Settings Card -->
        <div class="panel panel-default" style="border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.06);border:1.5px solid #e2e8f0;margin-bottom:24px;">
            <div class="panel-heading" style="background:#f8fafc;padding:18px 24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                <div>
                    <h4 style="margin:0;font-weight:800;color:#0f172a;font-size:17px;">
                        <i class="fa fa-paper-plane text-primary"></i> <?php echo __('Automated Lead Ingestion Notifications & Dispatch Rules');?>
                    </h4>
                    <p style="margin:4px 0 0 0;font-size:12px;color:#64748b;">
                        <?php echo __('When leads sync from Facebook, instantly notify your sales agents and send professional real estate introductions to clients.');?>
                    </p>
                </div>
            </div>

            <div class="panel-body" style="padding:24px;">
                <?php echo $this->Form->create(false, array('url' => array('action' => 'save_notifications'), 'id' => 'fbNotificationsForm')); ?>
                
                <!-- 5 On/Off Toggle Switches Grid -->
                <div class="row" style="margin-bottom:20px;">
                    <div class="col-md-4 col-sm-6" style="margin-bottom:14px;">
                        <div style="background:#ffffff;border:1.5px solid #e2e8f0;border-radius:10px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.02);height:100%;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                <label style="font-weight:700;color:#0f172a;margin:0;font-size:14px;">
                                    <i class="fa fa-envelope text-primary" style="font-size:16px;margin-right:4px;"></i> <?php echo __('Agent Email Alert');?>
                                </label>
                                <label class="switch" style="margin:0;">
                                    <input type="checkbox" name="notify_agent_email" value="1" <?php echo !empty($fbSettings['FacebookSetting']['notify_agent_email']) ? 'checked' : '';?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div style="font-size:12px;color:#64748b;line-height:1.4;">Send modern light email alert with lead profile & 1-click CRM link to assigned agent.</div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6" style="margin-bottom:14px;">
                        <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:18px;box-shadow:0 2px 8px rgba(59,130,246,0.06);height:100%;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                <label style="font-weight:700;color:#1e40af;margin:0;font-size:14px;">
                                    <i class="fa fa-users text-primary" style="font-size:16px;margin-right:4px;"></i> <?php echo __('Notify All Active Agents');?>
                                </label>
                                <label class="switch" style="margin:0;">
                                    <input type="checkbox" name="notify_all_agents_email" value="1" <?php echo !empty($fbSettings['FacebookSetting']['notify_all_agents_email']) ? 'checked' : '';?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div style="font-size:12px;color:#1e40af;line-height:1.4;">Send lead assignment alert email to ALL active agents configured in Facebook Agent Looper simultaneously.</div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6" style="margin-bottom:14px;">
                        <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:18px;box-shadow:0 4px 12px rgba(34,197,94,0.08);height:100%;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                <label style="font-weight:800;color:#15803d;margin:0;font-size:14px;">
                                    <i class="fa fa-shield text-success" style="font-size:16px;margin-right:4px;"></i> <?php echo __('Agency Admin Email Alert');?>
                                </label>
                                <label class="switch" style="margin:0;">
                                    <input type="checkbox" name="notify_admin_email" value="1" <?php echo !isset($fbSettings['FacebookSetting']['notify_admin_email']) || !empty($fbSettings['FacebookSetting']['notify_admin_email']) ? 'checked' : '';?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div style="font-size:12px;color:#166534;line-height:1.4;">Send real-time copy of lead assignment notification to the agency admin email.</div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6" style="margin-bottom:14px;">
                        <div style="background:#ffffff;border:1.5px solid #e2e8f0;border-radius:10px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.02);height:100%;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                <label style="font-weight:700;color:#0f172a;margin:0;font-size:14px;">
                                    <i class="fa fa-comment text-success" style="font-size:16px;margin-right:4px;"></i> <?php echo __('Agent SMS Alert');?>
                                </label>
                                <label class="switch" style="margin:0;">
                                    <input type="checkbox" name="notify_agent_sms" value="1" <?php echo !empty($fbSettings['FacebookSetting']['notify_agent_sms']) ? 'checked' : '';?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div style="font-size:12px;color:#64748b;line-height:1.4;">Send instant SMS alert to assigned agent immediately on lead sync.</div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-6" style="margin-bottom:14px;">
                        <div style="background:#ffffff;border:1.5px solid #e2e8f0;border-radius:10px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.02);height:100%;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                <label style="font-weight:700;color:#0f172a;margin:0;font-size:14px;">
                                    <i class="fa fa-paper-plane text-info" style="font-size:16px;margin-right:4px;"></i> <?php echo __('Lead Client Welcome Email');?>
                                </label>
                                <label class="switch" style="margin:0;">
                                    <input type="checkbox" name="notify_client_email" value="1" <?php echo !empty($fbSettings['FacebookSetting']['notify_client_email']) ? 'checked' : '';?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div style="font-size:12px;color:#64748b;line-height:1.4;">Send luxury real estate email with agency logo & agent advisor card to lead.</div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-6" style="margin-bottom:14px;">
                        <div style="background:#ffffff;border:1.5px solid #e2e8f0;border-radius:10px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.02);height:100%;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                <label style="font-weight:700;color:#0f172a;margin:0;font-size:14px;">
                                    <i class="fa fa-mobile text-warning" style="font-size:16px;margin-right:4px;"></i> <?php echo __('Lead Welcome SMS');?>
                                </label>
                                <label class="switch" style="margin:0;">
                                    <input type="checkbox" name="notify_client_sms" value="1" <?php echo !empty($fbSettings['FacebookSetting']['notify_client_sms']) ? 'checked' : '';?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div style="font-size:12px;color:#64748b;line-height:1.4;">Send instant SMS greeting to client with advisor contact number.</div>
                        </div>
                    </div>
                </div>

                <!-- Agency Admin Notification Email Destination Card -->
                <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:16px 20px;margin-bottom:26px;display:flex;align-items:center;flex-wrap:wrap;gap:15px;">
                    <div style="flex:0 0 auto;">
                        <span style="display:inline-block;background:#0f172a;color:#fff;padding:6px 14px;border-radius:6px;font-weight:800;font-size:12px;letter-spacing:0.5px;">
                            <i class="fa fa-envelope-open"></i> <?php echo __('ADMIN DESTINATION');?>
                        </span>
                    </div>
                    <div style="flex:1;min-width:280px;">
                        <label style="font-weight:700;color:#0f172a;margin-bottom:4px;display:block;font-size:13px;">
                            <?php echo __('Agency Admin Notification Email Address:');?>
                        </label>
                        <input type="email" name="admin_notification_email" class="form-control" placeholder="<?php echo !empty($cfg['Configuration']['email']) ? h($cfg['Configuration']['email']) : 'info@whitevillas.com.pk'; ?>" value="<?php echo h(!empty($fbSettings['FacebookSetting']['admin_notification_email']) ? $fbSettings['FacebookSetting']['admin_notification_email'] : '');?>" style="font-weight:600;border-radius:6px;height:38px;background:#ffffff;">
                        <div style="font-size:11px;color:#64748b;margin-top:4px;">
                            Leave blank to automatically use system agency email (<strong><?php echo !empty($cfg['Configuration']['email']) ? h($cfg['Configuration']['email']) : 'info@whitevillas.com.pk'; ?></strong>). Receives a real-time copy of every assigned Facebook lead!
                        </div>
                    </div>
                </div>

                <!-- Template Customizer Navigation Tabs -->
                <div class="nav-tabs-custom" style="box-shadow:none;">
                    <ul class="nav nav-tabs" style="border-bottom:2px solid #e2e8f0;font-weight:700;font-size:13px;">
                        <li class="active">
                            <a href="#tab_client_email" data-toggle="tab"><i class="fa fa-paper-plane text-primary"></i> <?php echo __('Lead Client Welcome Email Template');?></a>
                        </li>
                        <li>
                            <a href="#tab_agent_email" data-toggle="tab"><i class="fa fa-envelope text-info"></i> <?php echo __('Assigned Agent Email Alert Template');?></a>
                        </li>
                        <li>
                            <a href="#tab_agent_sms" data-toggle="tab"><i class="fa fa-comment text-success"></i> <?php echo __('Assigned Agent SMS Template');?></a>
                        </li>
                        <li>
                            <a href="#tab_client_sms" data-toggle="tab"><i class="fa fa-mobile text-warning"></i> <?php echo __('Lead Client SMS Template');?></a>
                        </li>
                    </ul>

                    <div class="tab-content" style="padding:22px 0;">
                        <!-- Tab 1: Client Welcome Email -->
                        <div class="tab-pane active" id="tab_client_email">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                                <label style="font-weight:700;color:#0f172a;font-size:13px;margin:0;"><?php echo __('Email Subject Line:');?></label>
                                <button type="button" class="btn btn-info btn-sm btn-preview-email" data-template-target="client" style="font-weight:700;border-radius:6px;box-shadow:0 2px 6px rgba(14,165,233,0.25);">
                                    <i class="fa fa-eye"></i> <?php echo __('Preview Client Email with Demo Data');?>
                                </button>
                            </div>
                            <div class="form-group">
                                <input type="text" id="clientEmailSubjectInput" name="client_email_subject" class="form-control" value="<?php echo h(!empty($fbSettings['FacebookSetting']['client_email_subject']) ? $fbSettings['FacebookSetting']['client_email_subject'] : 'Thank You for Your Inquiry with {agency_name} - {project_name}');?>" style="font-weight:600;border-radius:6px;height:38px;">
                            </div>
                            <div class="form-group">
                                <label style="font-weight:700;color:#0f172a;font-size:13px;">
                                    <?php echo __('Professional Real Estate HTML Email Template (Includes Agency Logo in Header, Assigned Agent Profile Card & Business Footer):');?>
                                </label>
                                <textarea id="clientEmailTextarea" name="client_email_template" rows="14" class="form-control" style="font-family:monospace;font-size:12px;border-radius:8px;line-height:1.5;background:#fcfcfc;"><?php echo h(!empty($fbSettings['FacebookSetting']['client_email_template']) ? $fbSettings['FacebookSetting']['client_email_template'] : '');?></textarea>
                            </div>
                        </div>

                        <!-- Tab 2: Agent Email Alert (Light Template) -->
                        <div class="tab-pane" id="tab_agent_email">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                                <div>
                                    <label style="font-weight:700;color:#0f172a;font-size:13px;margin:0;"><?php echo __('Email Subject Line:');?></label>
                                    <span class="label label-success" style="margin-left:6px;font-size:11px;background:#10b981;font-weight:700;">Light Template</span>
                                </div>
                                <div style="display:flex;gap:6px;">
                                    <button type="button" class="btn btn-default btn-sm" id="btnResetLightTemplate" style="font-weight:600;border-radius:6px;">
                                        <i class="fa fa-refresh"></i> <?php echo __('Reset to Default Light Template');?>
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm btn-preview-email" data-template-target="agent" style="font-weight:700;border-radius:6px;box-shadow:0 2px 6px rgba(59,130,246,0.25);">
                                        <i class="fa fa-eye"></i> <?php echo __('Preview Agent Email with Demo Data');?>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="text" id="agentEmailSubjectInput" name="agent_email_subject" class="form-control" value="<?php echo h(!empty($fbSettings['FacebookSetting']['agent_email_subject']) ? $fbSettings['FacebookSetting']['agent_email_subject'] : '🔔 New Facebook Lead Assigned: {lead_name} ({project_name})');?>" style="font-weight:600;border-radius:6px;height:38px;">
                            </div>
                            <div class="form-group">
                                <label style="font-weight:700;color:#0f172a;font-size:13px;">
                                    <?php echo __('Agent HTML Email Template (Modern Light Template with Luxury Styling, Lead Card, Direct Call & WhatsApp Buttons, and 1-Click CRM Action Link):');?>
                                </label>
                                <?php 
                                $agentTemplateContent = !empty($fbSettings['FacebookSetting']['agent_email_template']) 
                                    ? $fbSettings['FacebookSetting']['agent_email_template'] 
                                    : (!empty($defaultAgentTemplate) ? $defaultAgentTemplate : '');
                                ?>
                                <textarea id="agentEmailTextarea" name="agent_email_template" rows="15" class="form-control" style="font-family:monospace;font-size:12px;border-radius:8px;line-height:1.5;background:#fcfcfc;"><?php echo h($agentTemplateContent);?></textarea>
                            </div>
                        </div>

                        <!-- Tab 3: Agent SMS -->
                        <div class="tab-pane" id="tab_agent_sms">
                            <div class="form-group">
                                <label style="font-weight:700;color:#0f172a;font-size:13px;"><?php echo __('SMS Message Body:');?></label>
                                <textarea name="agent_sms_template" rows="4" class="form-control" style="font-size:13px;border-radius:8px;"><?php echo h(!empty($fbSettings['FacebookSetting']['agent_sms_template']) ? $fbSettings['FacebookSetting']['agent_sms_template'] : '🔔 WV CRM: New FB Lead Assigned! Name: {lead_name}, Phone: {lead_mobile}, Form: {form_name}, Project: {project_name}. Open CRM: {crm_link} - {agency_name}');?></textarea>
                            </div>
                        </div>

                        <!-- Tab 4: Client SMS -->
                        <div class="tab-pane" id="tab_client_sms">
                            <div class="form-group">
                                <label style="font-weight:700;color:#0f172a;font-size:13px;"><?php echo __('SMS Message Body:');?></label>
                                <textarea name="client_sms_template" rows="4" class="form-control" style="font-size:13px;border-radius:8px;"><?php echo h(!empty($fbSettings['FacebookSetting']['client_sms_template']) ? $fbSettings['FacebookSetting']['client_sms_template'] : 'Dear {lead_name}, thank you for inquiring about {project_name} on Facebook! Your assigned property advisor is {agent_name} ({agent_mobile}). We will connect with you shortly. - {agency_name}');?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Merge Tags Guide -->
                <div style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:10px;padding:18px;margin-bottom:24px;">
                    <div style="font-weight:800;font-size:13px;color:#0f172a;margin-bottom:10px;">
                        <i class="fa fa-tags text-primary"></i> <?php echo __('Available Placeholder Tags (Auto-Replaced at Dispatch):');?>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{lead_name}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{lead_mobile}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{lead_email}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{lead_city}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{lead_remarks}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{form_name}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{project_name}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{agent_name}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{agent_mobile}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{agent_email}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{agent_whatsapp_link}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{whatsapp_link}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{agency_name}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{agency_phone}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{agency_email}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{agency_address}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{agency_domain}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{agency_logo_html}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{crm_link}</code>
                        <code style="font-size:12px;padding:4px 8px;background:#fff;border:1px solid #cbd5e1;color:#0f172a;">{inquiry_date}</code>
                    </div>
                </div>

                <!-- Action Button -->
                <div>
                    <button type="submit" class="btn btn-success btn-lg" style="font-weight:800;padding:12px 36px;border-radius:8px;box-shadow:0 4px 14px rgba(16,185,129,0.3);">
                        <i class="fa fa-save"></i> <?php echo __('Save Notification Settings & Templates');?>
                    </button>
                </div>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>
</div>

<!-- ================= LIVE EMAIL TEMPLATE PREVIEW MODAL ================= -->
<div class="modal fade" id="emailPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="width:90%;max-width:850px;">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,0.25);">
            <div class="modal-header" style="background:#0f172a;color:#fff;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h4 class="modal-title" id="previewModalTitle" style="font-weight:800;font-size:16px;margin:0;">
                        <i class="fa fa-eye text-primary"></i> <?php echo __('Live Email Template Preview');?>
                    </h4>
                    <div id="previewModalSubject" style="font-size:12px;color:#94a3b8;margin-top:2px;"></div>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-default active" id="btnPreviewDesktop"><i class="fa fa-desktop"></i> Desktop</button>
                        <button type="button" class="btn btn-default" id="btnPreviewMobile"><i class="fa fa-mobile fa-lg"></i> Mobile</button>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:0.8;font-size:24px;margin-left:10px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <div class="modal-body" style="background:#f1f5f9;padding:24px;display:flex;justify-content:center;">
                <div id="previewContainerWrapper" style="width:100%;max-width:700px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);transition:all .3s ease;">
                    <iframe id="previewEmailIframe" style="width:100%;height:600px;border:none;display:block;"></iframe>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;padding:12px 24px;display:flex;justify-content:space-between;align-items:center;">
                <span class="text-muted" style="font-size:12px;">
                    <i class="fa fa-info-circle text-info"></i> <?php echo __('Rendered with live demo data from your CRM configuration.');?>
                </span>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close Preview');?></button>
            </div>
        </div>
    </div>
</div>

<style>
.switch { position: relative; display: inline-block; width: 48px; height: 26px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; -webkit-transition: .3s; transition: .3s; }
.slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; -webkit-transition: .3s; transition: .3s; box-shadow:0 2px 4px rgba(0,0,0,0.2); }
input:checked + .slider { background-color: #10b981; }
input:focus + .slider { box-shadow: 0 0 1px #10b981; }
input:checked + .slider:before { -webkit-transform: translateX(22px); -ms-transform: translateX(22px); transform: translateX(22px); }
.slider.round { border-radius: 26px; }
.slider.round:before { border-radius: 50%; }
</style>

<script type="text/javascript">
$(document).ready(function() {
    // Demo data for preview
    var demoTags = {
        '{lead_name}': 'Muhammad Arslan Khan',
        '{lead_mobile}': '+92 300 1234567',
        '{lead_email}': 'arslan.khan@example.com',
        '{lead_city}': 'Lahore',
        '{lead_remarks}': 'Interested in 5 Marla Executive Luxury Cottage - 3 Year Installments',
        '{form_name}': 'Plot or Cottage Booking Form',
        '{project_name}': 'White Villas Lahore',
        '{agent_name}': 'Ch Riaz Ahmad',
        '{agent_mobile}': '+92 311 4432729',
        '{agent_email}': 'chriazmushtaq@gmail.com',
        '{agent_email_row}': '<div style="margin-top:6px;font-size:12px;color:#64748b;">✉️ Email: <a href="mailto:chriazmushtaq@gmail.com" style="color:#0284c7;">chriazmushtaq@gmail.com</a></div>',
        '{agent_whatsapp_link}': 'https://wa.me/923114432729',
        '{whatsapp_link}': 'https://wa.me/923001234567',
        '{agency_name}': '<?php echo !empty($cfg["Configuration"]["name"]) ? addslashes($cfg["Configuration"]["name"]) : "White Villas";?>',
        '{agency_phone}': '<?php echo !empty($cfg["Configuration"]["contact"]) ? addslashes($cfg["Configuration"]["contact"]) : "+92339 1101156";?>',
        '{agency_email}': '<?php echo !empty($cfg["Configuration"]["email"]) ? addslashes($cfg["Configuration"]["email"]) : "info@whitevillas.com.pk";?>',
        '{agency_address}': '<?php echo !empty($cfg["Configuration"]["address"]) ? addslashes($cfg["Configuration"]["address"]) : "Bahria Town Lahore, Pakistan";?>',
        '{agency_domain}': '<?php echo !empty($cfg["Configuration"]["domain_name"]) ? addslashes($cfg["Configuration"]["domain_name"]) : "https://portal.whitevillas.com.pk";?>',
        '{agency_logo_html}': '<?php 
            $logoName = !empty($cfg["Configuration"]["photo"]) ? $cfg["Configuration"]["photo"] : "";
            $logoUrl = Router::url("/", true) . "img/" . $logoName;
            echo ($logoName !== "") ? "<img src=\"" . $logoUrl . "\" style=\"max-height:55px;margin-bottom:8px;\" alt=\"Logo\">" : "";
        ?>',
        '{crm_link}': '<?php echo Router::url(array("plugin"=>"admin", "controller"=>"leads", "action"=>"leadview", 3923), true);?>',
        '{inquiry_date}': '<?php echo date("d-M-Y h:i A");?>'
    };

    function replaceAllTags(str) {
        if(!str) return '';
        var result = str;
        for (var tag in demoTags) {
            if (demoTags.hasOwnProperty(tag)) {
                var re = new RegExp(tag.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1"), 'g');
                result = result.replace(re, demoTags[tag]);
            }
        }
        return result;
    }

    // Preview button handler
    $('.btn-preview-email').on('click', function() {
        var target = $(this).data('template-target');
        var rawHtml = '';
        var rawSubj = '';
        var title = '';

        if(target === 'client') {
            rawHtml = $('#clientEmailTextarea').val();
            rawSubj = $('#clientEmailSubjectInput').val();
            title = '<i class="fa fa-paper-plane text-info"></i> Lead Client Welcome Email Preview';
        } else {
            rawHtml = $('#agentEmailTextarea').val();
            rawSubj = $('#agentEmailSubjectInput').val();
            title = '<i class="fa fa-envelope text-primary"></i> Assigned Agent Email Alert Preview';
        }

        var renderedHtml = replaceAllTags(rawHtml);
        var renderedSubj = replaceAllTags(rawSubj);

        $('#previewModalTitle').html(title);
        $('#previewModalSubject').html('<strong>Subject:</strong> ' + renderedSubj);

        var iframe = document.getElementById('previewEmailIframe');
        var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        iframeDoc.open();
        iframeDoc.write(renderedHtml);
        iframeDoc.close();

        // Reset to desktop view
        $('#previewContainerWrapper').css({'max-width': '700px'});
        $('#btnPreviewDesktop').addClass('active');
        $('#btnPreviewMobile').removeClass('active');

        $('#emailPreviewModal').modal('show');
    });

    // Device switcher in preview modal
    $('#btnPreviewDesktop').on('click', function() {
        $(this).addClass('active');
        $('#btnPreviewMobile').removeClass('active');
        $('#previewContainerWrapper').css({'max-width': '700px'});
    });

    $('#btnPreviewMobile').on('click', function() {
        $(this).addClass('active');
        $('#btnPreviewDesktop').removeClass('active');
        $('#previewContainerWrapper').css({'max-width': '380px'});
    });

    // Reset to Default Light Template handler
    var defaultLightTemplateText = <?php echo json_encode(!empty($defaultAgentTemplate) ? $defaultAgentTemplate : ''); ?>;
    $('#btnResetLightTemplate').on('click', function() {
        if(defaultLightTemplateText && confirm('<?php echo __('Reset agent email template to the default modern light template?');?>')) {
            $('#agentEmailTextarea').val(defaultLightTemplateText);
            $('#agentEmailSubjectInput').val('🔔 New Facebook Lead Assigned: {lead_name} ({project_name})');
        }
    });
});
</script>