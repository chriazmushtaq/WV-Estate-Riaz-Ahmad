<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-book text-primary"></i> <?php echo __('Facebook Integration Setup Guide & Token Instructions');?></h1>
        <p class="description"><?php echo __('Step-by-step instructions, direct Meta portal links, token generation guide, and webhook setup for White Villas CRM.');?></p>
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
            <?php echo $this->Html->link('<i class="fa fa-whatsapp"></i> '.__('WhatsApp API'), array('action'=>'whatsapp'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-history"></i> '.__('Sync Logs'), array('action'=>'logs'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-book"></i> '.__('Documentation'), array('action'=>'docs'), array('class'=>'btn btn-primary', 'escape'=>false));?>
        </div>

        <div>
            <?php echo $this->Html->link('<i class="fa fa-facebook-official"></i> '.__('1-Click Connect Facebook Page'), array('action'=>'settings'), array('class'=>'btn btn-primary', 'escape'=>false, 'style'=>'background:#1877f2;border-color:#1877f2;'));?>
        </div>
    </div>

    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <!-- Quick Access Meta Direct Links Bar -->
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:18px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
            <h4 style="margin:0 0 12px 0;font-weight:800;color:#0f172a;">
                <i class="fa fa-external-link text-primary"></i> <?php echo __('Official Meta Developer Portals (Direct Backlinks):');?>
            </h4>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <a href="https://developers.facebook.com/tools/explorer/" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm" style="background:#1877f2;border-color:#1877f2;font-weight:800;">
                    <i class="fa fa-key"></i> 1. Graph API Explorer (Get Token) <i class="fa fa-external-link"></i>
                </a>
                <a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener noreferrer" class="btn btn-default btn-sm" style="font-weight:700;color:#0284c7;border-color:#cbd5e1;">
                    <i class="fa fa-th-large"></i> 2. Meta Apps Dashboard <i class="fa fa-external-link"></i>
                </a>
                <a href="https://developers.facebook.com/tools/lead-ads-testing" target="_blank" rel="noopener noreferrer" class="btn btn-default btn-sm" style="font-weight:700;color:#10b981;border-color:#cbd5e1;">
                    <i class="fa fa-flask"></i> 3. Lead Ads Testing Tool <i class="fa fa-external-link"></i>
                </a>
                <a href="https://business.facebook.com/" target="_blank" rel="noopener noreferrer" class="btn btn-default btn-sm" style="font-weight:700;color:#6366f1;border-color:#cbd5e1;">
                    <i class="fa fa-briefcase"></i> 4. Meta Business Suite <i class="fa fa-external-link"></i>
                </a>
                <a href="https://developers.facebook.com/tools/debug/accesstoken/" target="_blank" rel="noopener noreferrer" class="btn btn-default btn-sm" style="font-weight:700;color:#d97706;border-color:#cbd5e1;">
                    <i class="fa fa-shield"></i> 5. Token Debugger <i class="fa fa-external-link"></i>
                </a>
            </div>
        </div>

                <!-- ================= FAST 60-SECOND NEVER-EXPIRING TOKEN GUIDE ================= -->
        <div style="background:#fffbeb;border:2px solid #f59e0b;border-radius:12px;padding:24px;margin-bottom:24px;box-shadow:0 4px 16px rgba(245,158,11,0.15);">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                <h3 style="margin:0;font-weight:900;color:#92400e;font-size:20px;">
                    <i class="fa fa-bolt text-warning"></i> <?php echo __('🚀 3 Quick Steps to Get a "Never-Expiring" Permanent Token (Takes 60 Seconds):');?>
                </h3>
                <a href="https://developers.facebook.com/tools/debug/accesstoken/" target="_blank" rel="noopener noreferrer" class="btn btn-warning btn-sm" style="font-weight:800;background:#f59e0b;border-color:#f59e0b;color:#fff;">
                    <i class="fa fa-shield"></i> <?php echo __('Open Token Debugger Tool');?>
                </a>
            </div>

            <p style="font-size:13px;color:#78350f;margin-bottom:16px;line-height:1.6;">
                <?php echo __('Follow this secret Meta Token Debugger method to convert any temporary Graph API Explorer token into a 100% permanent Page token that NEVER expires:');?>
            </p>

            <div class="row">
                <div class="col-md-4" style="margin-bottom:12px;">
                    <div style="background:#fff;border:1px solid #fde68a;border-radius:8px;padding:16px;height:100%;">
                        <h5 style="margin-top:0;font-weight:800;color:#92400e;">
                            <span class="badge" style="background:#f59e0b;">Step 1</span> Copy Current Token
                        </h5>
                        <p style="font-size:12px;color:#475569;margin:0;line-height:1.5;">
                            In <a href="https://developers.facebook.com/tools/explorer/" target="_blank" style="color:#f59e0b;font-weight:700;">Graph API Explorer</a>, click the <strong>Copy icon</strong> next to the Access Token box (starting with <code>EAAT4J...</code>).
                        </p>
                    </div>
                </div>

                <div class="col-md-4" style="margin-bottom:12px;">
                    <div style="background:#fff;border:1px solid #fde68a;border-radius:8px;padding:16px;height:100%;">
                        <h5 style="margin-top:0;font-weight:800;color:#92400e;">
                            <span class="badge" style="background:#f59e0b;">Step 2</span> Extend in Debugger
                        </h5>
                        <p style="font-size:12px;color:#475569;margin:0;line-height:1.5;">
                            Open <a href="https://developers.facebook.com/tools/debug/accesstoken/" target="_blank" style="color:#f59e0b;font-weight:700;">Access Token Debugger</a> ➔ Paste token ➔ Click <strong>Debug</strong> ➔ Scroll to bottom &amp; click <strong>"Extend Access Token"</strong>. Copy the new Long-Lived Token.
                        </p>
                    </div>
                </div>

                <div class="col-md-4" style="margin-bottom:12px;">
                    <div style="background:#fff;border:1px solid #fde68a;border-radius:8px;padding:16px;height:100%;">
                        <h5 style="margin-top:0;font-weight:800;color:#92400e;">
                            <span class="badge" style="background:#10b981;">Step 3</span> Get Never-Expiring Token
                        </h5>
                        <p style="font-size:12px;color:#475569;margin:0;line-height:1.5;">
                            Return to Graph API Explorer ➔ Paste extended token ➔ Under <strong>User or Page</strong>, select your Page (<em>White Alpine Lodges</em> or <em>White ICE Cottages</em>) ➔ Copy the token!
                        </p>
                    </div>
                </div>
            </div>

            <div style="background:#fff;border:1.5px solid #86efac;border-radius:8px;padding:14px 18px;margin-top:14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                <div style="font-size:13px;color:#166534;">
                    <strong><i class="fa fa-shield text-success"></i> Verify in Debugger:</strong> When pasted into Token Debugger, it will show: <span class="label label-success" style="background:#16a34a;font-size:12px;">Expires: Never</span>
                </div>
                <a href="<?php echo $this->Html->url(array('action'=>'settings'));?>" class="btn btn-success btn-sm" style="font-weight:800;background:#16a34a;border-color:#16a34a;">
                    <i class="fa fa-save"></i> Paste &amp; Save in CRM API Accounts
                </a>
            </div>
        </div>

        <!-- SPECIAL HIGHLIGHTED SECTION: HOW TO GET THE ACCESS TOKEN -->
        <div style="background:#eff6ff;border:2px solid #3b82f6;border-radius:12px;padding:24px;margin-bottom:24px;box-shadow:0 4px 14px rgba(59,130,246,0.15);">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                <h3 style="margin:0;font-weight:900;color:#1e40af;font-size:20px;">
                    <i class="fa fa-key"></i> <?php echo __('How to Get Your Meta User / Page Access Token (Step-by-Step):');?>
                </h3>
                <a href="https://developers.facebook.com/tools/explorer/" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm" style="font-weight:800;background:#10b981;border-color:#10b981;">
                    <i class="fa fa-external-link"></i> <?php echo __('Click Here to Open Graph API Explorer');?>
                </a>
            </div>

            <p style="font-size:13px;color:#1e3a8a;margin-bottom:16px;line-height:1.6;">
                <?php echo __('Follow these exact steps to generate the token so White Villas CRM can automatically discover and connect all your Facebook Pages in 1 click:');?>
            </p>

            <div class="row">
                <div class="col-md-6">
                    <div style="background:#fff;border:1px solid #bfdbfe;border-radius:8px;padding:16px;margin-bottom:12px;">
                        <h5 style="margin-top:0;font-weight:800;color:#1e40af;"><span class="badge" style="background:#1877f2;">1</span> Open Graph API Explorer</h5>
                        <p style="font-size:12px;color:#475569;margin:0;">
                            Go to <a href="https://developers.facebook.com/tools/explorer/" target="_blank" style="font-weight:700;color:#1877f2;text-decoration:underline;">developers.facebook.com/tools/explorer/</a>. Log in with your Facebook account that manages your business pages.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div style="background:#fff;border:1px solid #bfdbfe;border-radius:8px;padding:16px;margin-bottom:12px;">
                        <h5 style="margin-top:0;font-weight:800;color:#1e40af;"><span class="badge" style="background:#1877f2;">2</span> Select Your Meta App</h5>
                        <p style="font-size:12px;color:#475569;margin:0;">
                            In the top right corner dropdown labeled <strong>"Meta App"</strong>, choose your App (or create one at <a href="https://developers.facebook.com/apps/" target="_blank" style="font-weight:700;">developers.facebook.com/apps</a>).
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div style="background:#fff;border:1px solid #bfdbfe;border-radius:8px;padding:16px;margin-bottom:12px;">
                        <h5 style="margin-top:0;font-weight:800;color:#1e40af;"><span class="badge" style="background:#1877f2;">3</span> Select User or Page Token</h5>
                        <p style="font-size:12px;color:#475569;margin:0;">
                            Under the <strong>"User or Page"</strong> dropdown, click <strong>"Get User Access Token"</strong> (or choose your specific Facebook Page directly).
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div style="background:#fff;border:1px solid #bfdbfe;border-radius:8px;padding:16px;margin-bottom:12px;">
                        <h5 style="margin-top:0;font-weight:800;color:#1e40af;"><span class="badge" style="background:#1877f2;">4</span> Add Required Permissions</h5>
                        <p style="font-size:12px;color:#475569;margin:0;">
                            Click <strong>"Add a Permission"</strong> dropdown and add these 5 permissions:
                        </p>
                        <div style="margin-top:6px;">
                            <span class="label label-info" style="font-size:11px;">pages_show_list</span>
                            <span class="label label-info" style="font-size:11px;">pages_read_engagement</span>
                            <span class="label label-info" style="font-size:11px;">pages_manage_metadata</span>
                            <span class="label label-info" style="font-size:11px;">pages_manage_ads</span>
                            <span class="label label-info" style="font-size:11px;">leads_retrieval</span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="background:#fff;border:1px solid #bfdbfe;border-radius:8px;padding:16px;margin-top:4px;">
                <h5 style="margin-top:0;font-weight:800;color:#10b981;"><span class="badge" style="background:#10b981;">5</span> Click "Generate Access Token" & Paste in CRM</h5>
                <p style="font-size:12px;color:#334155;margin:0;line-height:1.6;">
                    Click the blue <strong>"Generate Access Token"</strong> button. Approve the Facebook permission dialog. Then copy the token text, return to White Villas CRM ➔ <a href="<?php echo $this->Html->url(array('action'=>'settings'));?>" style="font-weight:700;color:#1877f2;text-decoration:underline;">API Accounts</a> ➔ Click <strong>"1-Click Connect Facebook Page"</strong> ➔ Paste the token and click <strong>"Auto-Fetch My Pages"</strong>!
                </p>
            </div>
        </div>

        <!-- 4-STEP COMPLETE SETUP GUIDE -->
        <div class="row">
            <div class="col-md-8">
                <!-- STEP 1 -->
                <div style="background:#fff;border:1px solid #e2e8f0;border-left:5px solid #1877f2;border-radius:8px;padding:20px;margin-bottom:18px;box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                    <h4 style="margin-top:0;font-weight:800;color:#1877f2;">
                        <span class="badge badge-primary" style="background:#1877f2;font-size:14px;padding:5px 10px;margin-right:6px;">Step 1</span>
                        <?php echo __('Create Meta App on developers.facebook.com');?>
                    </h4>
                    <p style="font-size:13px;color:#475569;line-height:1.6;">
                        1. Visit <a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener noreferrer" style="font-weight:700;text-decoration:underline;">developers.facebook.com/apps <i class="fa fa-external-link"></i></a>.<br/>
                        2. Click <strong>Create App</strong> ➔ Choose <strong>Other</strong> ➔ Select <strong>Business</strong>.<br/>
                        3. Name your app (e.g. <code>White Villas CRM Ingestor</code>) and click <strong>Create App</strong>.
                    </p>
                </div>

                <!-- STEP 2 -->
                <div style="background:#fff;border:1px solid #e2e8f0;border-left:5px solid #0284c7;border-radius:8px;padding:20px;margin-bottom:18px;box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                    <h4 style="margin-top:0;font-weight:800;color:#0284c7;">
                        <span class="badge badge-info" style="background:#0284c7;font-size:14px;padding:5px 10px;margin-right:6px;">Step 2</span>
                        <?php echo __('Configure Webhooks on Meta App Dashboard');?>
                    </h4>
                    <p style="font-size:13px;color:#475569;line-height:1.6;">
                        1. In your Meta App Dashboard, click <strong>Add Product</strong> ➔ <strong>Webhooks</strong> ➔ <strong>Set Up</strong>.<br/>
                        2. Choose <strong>Page</strong> from the dropdown and click <strong>Subscribe to this object</strong>.<br/>
                        3. Enter the following exact Callback URL & Verify Token:
                    </p>

                    <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:6px;padding:12px;margin:10px 0;">
                        <div style="margin-bottom:8px;">
                            <strong>Callback URL:</strong><br/>
                            <div class="input-group" style="margin-top:4px;">
                                <input type="text" class="form-control input-sm" id="docWbUrl" value="<?php echo h($webhookUrl);?>" readonly>
                                <span class="input-group-btn">
                                    <button class="btn btn-default btn-sm" type="button" onclick="copyDocInput('docWbUrl')"><i class="fa fa-copy"></i> Copy</button>
                                </span>
                            </div>
                        </div>
                        <div>
                            <strong>Verify Token:</strong><br/>
                            <div class="input-group" style="margin-top:4px;">
                                <input type="text" class="form-control input-sm" id="docWbToken" value="whitevillas_fb_verify_token_2026" readonly>
                                <span class="input-group-btn">
                                    <button class="btn btn-default btn-sm" type="button" onclick="copyDocInput('docWbToken')"><i class="fa fa-copy"></i> Copy</button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <p style="font-size:13px;color:#475569;margin-bottom:0;">
                        4. Click <strong>Verify and Save</strong>.<br/>
                        5. In the Page Subscription Fields table, find <strong><code>leadgen</code></strong> and click <strong>Subscribe</strong>.
                    </p>
                </div>

                <!-- STEP 3 -->
                <div style="background:#fff;border:1px solid #e2e8f0;border-left:5px solid #10b981;border-radius:8px;padding:20px;margin-bottom:18px;box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                    <h4 style="margin-top:0;font-weight:800;color:#10b981;">
                        <span class="badge badge-success" style="background:#10b981;font-size:14px;padding:5px 10px;margin-right:6px;">Step 3</span>
                        <?php echo __('Auto-Discover & Connect Pages in 1 Click');?>
                    </h4>
                    <p style="font-size:13px;color:#475569;line-height:1.6;">
                        1. In White Villas CRM, go to <a href="<?php echo $this->Html->url(array('action'=>'settings'));?>" style="font-weight:700;">API Accounts</a>.<br/>
                        2. Click the blue button <strong>"1-Click Connect Facebook Page"</strong>.<br/>
                        3. Paste the token you generated from Graph API Explorer and click <strong>Auto-Fetch My Pages</strong>.<br/>
                        4. Click <strong>Connect & Auto-Sync Leads</strong> on your desired Facebook Page!
                    </p>
                </div>

                <!-- STEP 4 -->
                <div style="background:#fff;border:1px solid #e2e8f0;border-left:5px solid #8b5cf6;border-radius:8px;padding:20px;margin-bottom:18px;box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                    <h4 style="margin-top:0;font-weight:800;color:#8b5cf6;">
                        <span class="badge" style="background:#8b5cf6;color:#fff;font-size:14px;padding:5px 10px;margin-right:6px;">Step 4</span>
                        <?php echo __('Test Real-Time Ingestion with Meta Testing Tool');?>
                    </h4>
                    <p style="font-size:13px;color:#475569;line-height:1.6;">
                        1. Open the <a href="https://developers.facebook.com/tools/lead-ads-testing" target="_blank" rel="noopener noreferrer" style="font-weight:700;text-decoration:underline;">Meta Lead Ads Testing Tool <i class="fa fa-external-link"></i></a>.<br/>
                        2. Select your Facebook Page and Form name.<br/>
                        3. Click <strong>Create Lead</strong>.<br/>
                        4. Check <a href="<?php echo $this->Html->url(array('action'=>'logs'));?>" style="font-weight:700;">Sync Logs</a> or <a href="<?php echo $this->Html->url(array('controller'=>'Leads', 'action'=>'manage'));?>" style="font-weight:700;">Manage Leads</a> in your CRM: the test lead will appear immediately, assigned to the next rotating sales agent!
                    </p>
                </div>
            </div>

            <!-- Side Help Box -->
            <div class="col-md-4">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px;margin-bottom:20px;">
                    <h4 style="margin-top:0;font-weight:800;color:#0f172a;"><i class="fa fa-refresh text-primary"></i> <?php echo __('1-by-1 Agent Looper');?></h4>
                    <p style="font-size:12px;color:#64748b;line-height:1.5;">
                        Incoming Facebook leads are automatically rotated and assigned sequentially to active sales agents:
                    </p>
                    <ul style="font-size:12px;color:#334155;padding-left:18px;">
                        <li>Agent A ➔ Lead 1</li>
                        <li>Agent B ➔ Lead 2</li>
                        <li>Agent C ➔ Lead 3</li>
                        <li>Agent A ➔ Lead 4 (Loop restarts)</li>
                    </ul>
                    <?php echo $this->Html->link('<i class="fa fa-users"></i> '.__('Configure Agent Queue'), array('action'=>'agents'), array('class'=>'btn btn-default btn-block btn-sm', 'escape'=>false, 'style'=>'font-weight:700;margin-top:10px;'));?>
                </div>

                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:20px;">
                    <h4 style="margin-top:0;font-weight:800;color:#1e40af;"><i class="fa fa-clock-o"></i> <?php echo __('Background Auto-Sync Cron (cPanel)');?></h4>
                    <p style="font-size:12px;color:#1e3a8a;line-height:1.5;margin-bottom:8px;">
                        Under cPanel &rarr; Cron Jobs, set schedule to <strong>Every 5 Minutes (*/5 * * * *)</strong> and paste ONLY this into the <strong>Command</strong> box:
                    </p>
                    <div class="input-group" style="margin-bottom:6px;">
                        <input type="text" class="form-control input-sm" id="cpanelDocCron" value="curl -s &quot;<?php echo h($cronUrl);?>&quot; > /dev/null 2>&1" readonly style="background:#fff;font-family:monospace;font-size:11px;">
                        <span class="input-group-btn">
                            <button class="btn btn-primary btn-sm" type="button" onclick="copyDocInput('cpanelDocCron')" style="font-weight:700;"><i class="fa fa-copy"></i> Copy</button>
                        </span>
                    </div>
                    <small style="color:#b91c1c;font-weight:600;font-size:10.5px;display:block;"><i class="fa fa-info-circle"></i> Do not type "*/5 * * * *" in the Command field.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function copyDocInput(id) {
    var copyText = document.getElementById(id);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Copied: " + copyText.value);
}
</script>