<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-facebook-square text-primary"></i> <?php echo __('Facebook Accounts & 1-Click Page Auto-Connect');?></h1>
        <p class="description"><?php echo __('Connect your Facebook Pages with 1 click or add App IDs for automated real-time lead auto-fetching.');?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-heading" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div class="btn-group" style="display:flex;flex-wrap:wrap;gap:4px;">
            <?php echo $this->Html->link('<i class="fa fa-dashboard"></i> '.__('Overview'), array('action'=>'index'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-cogs"></i> '.__('API Accounts'), array('action'=>'settings'), array('class'=>'btn btn-primary', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-users"></i> '.__('Agent Looper'), array('action'=>'agents'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-list-alt"></i> '.__('Form Mapping'), array('action'=>'forms'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-bell"></i> '.__('Notifications'), array('action'=>'notifications'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-whatsapp"></i> '.__('WhatsApp API'), array('action'=>'whatsapp'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-history"></i> '.__('Sync Logs'), array('action'=>'logs'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-book"></i> '.__('Documentation'), array('action'=>'docs'), array('class'=>'btn btn-default', 'escape'=>false));?>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <!-- 1-Click Fast Connect Button -->
            <button type="button" class="btn btn-primary" id="btn1ClickFbLogin" style="background:#1877f2;border-color:#1877f2;font-weight:700;">
                <i class="fa fa-facebook-official fa-lg"></i> <?php echo __('1-Click Connect Facebook Page');?>
            </button>
            <button type="button" class="btn btn-success" id="btnOpenAddModal">
                <i class="fa fa-plus-circle"></i> <?php echo __('Add Manual Page / App ID');?>
            </button>
            <?php echo $this->Html->link('<i class="fa fa-refresh fa-spin-hover"></i> '.__('Sync All Accounts Now'), array('action'=>'sync_now'), array('class'=>'btn btn-info', 'escape'=>false));?>
        </div>
    </div>

    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <!-- 1-Click Easy Connect Callout Card -->
        <div style="background:linear-gradient(135deg, #1877f2 0%, #0d47a1 100%);color:#fff;border-radius:12px;padding:20px;margin-bottom:24px;box-shadow:0 4px 16px rgba(24,119,242,0.25);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;">
            <div style="max-width:700px;">
                <h3 style="margin:0 0 6px 0;font-weight:800;color:#fff;font-size:20px;">
                    <i class="fa fa-magic"></i> <?php echo __('Easiest Way: Auto-Discover & Connect Your Facebook Pages');?>
                </h3>
                <p style="margin:0;font-size:13px;opacity:0.92;line-height:1.5;">
                    <?php echo __('Click "1-Click Connect Facebook Page" to log into Facebook or paste your User/Page Token. The system will automatically fetch all your managed business pages so you can connect and auto-sync leads with a single click!');?>
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-default btn-lg" id="btnHeroFbConnect" style="color:#1877f2;font-weight:800;border-radius:8px;box-shadow:0 4px 10px rgba(0,0,0,0.15);">
                    <i class="fa fa-facebook-official"></i> <?php echo __('Connect My Facebook Page');?>
                </button>
            </div>
        </div>

        <!-- Configured Accounts Table -->
        <div class="panel panel-default" style="border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:24px;">
            <div class="panel-heading" style="background:#f8fafc;font-weight:700;display:flex;justify-content:space-between;align-items:center;">
                <span><i class="fa fa-list text-primary"></i> <?php echo __('Configured Facebook Pages & Apps');?> (<?php echo count($accounts);?>)</span>
                <span class="text-muted" style="font-size:12px;font-weight:normal;">Multi-Page Auto Lead Ingestion</span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;vertical-align:middle;">
                    <thead>
                        <tr style="background:#f1f5f9;color:#334155;">
                            <th style="width:60px;"><?php echo __('Status');?></th>
                            <th><?php echo __('Account / Page Name');?></th>
                            <th><?php echo __('Facebook Page ID');?></th>
                            <th><?php echo __('App ID');?></th>
                            <th><?php echo __('Target Project');?></th>
                            <th><?php echo __('Synced Leads');?></th>
                            <th><?php echo __('Last Synced');?></th>
                            <th style="width:260px;"><?php echo __('Actions');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($accounts)){ foreach($accounts as $acc){ $a = $acc['FacebookAccount']; ?>
                        <tr>
                            <td class="text-center">
                                <?php if(!empty($a['is_active'])){ ?>
                                    <span class="label label-success" style="font-size:11px;padding:4px 8px;"><i class="fa fa-check"></i> <?php echo __('Active');?></span>
                                <?php } else { ?>
                                    <span class="label label-danger" style="font-size:11px;padding:4px 8px;"><i class="fa fa-pause"></i> <?php echo __('Paused');?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <strong style="font-size:14px;color:#0f172a;"><?php echo h($a['account_name']);?></strong>
                                <?php if(!empty($a['page_name']) && $a['page_name'] !== $a['account_name']){ ?>
                                    <div style="font-size:11px;color:#64748b;"><i class="fa fa-tag"></i> <?php echo h($a['page_name']);?></div>
                                <?php } ?>
                            </td>
                            <td><code><?php echo h($a['page_id']);?></code></td>
                            <td><span class="text-muted"><?php echo !empty($a['app_id']) ? h($a['app_id']) : 'N/A';?></span></td>
                            <td>
                                <?php if(!empty($acc['Project']['name'])){ ?>
                                    <span class="badge badge-primary" style="background:#3b82f6;"><?php echo h($acc['Project']['name']);?></span>
                                <?php } else { ?>
                                    <span class="text-muted"><?php echo __('All Projects');?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <span class="badge badge-info" style="font-size:12px;padding:4px 8px;background:#0284c7;">
                                    <i class="fa fa-user-plus"></i> <?php echo (int)$a['total_leads_synced'];?> leads
                                </span>
                            </td>
                            <td style="font-size:12px;">
                                <?php echo !empty($a['last_sync_at']) ? h($a['last_sync_at']) : '<span class="text-muted">Never</span>';?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-xs">
                                    <button type="button" class="btn btn-default btn-test-row-token" 
                                            data-token="<?php echo h($a['page_access_token']);?>" 
                                            data-pageid="<?php echo h($a['page_id']);?>"
                                            title="<?php echo __('Test Graph API Connection');?>">
                                        <i class="fa fa-bolt text-warning"></i> <?php echo __('Test API');?>
                                    </button>

                                    <?php echo $this->Html->link('<i class="fa fa-refresh"></i> '.__('Sync'), array('action'=>'sync_account', $a['id']), array('class'=>'btn btn-info', 'escape'=>false, 'title'=>__('Fetch leads now')));?>

                                    <button type="button" class="btn btn-primary btn-edit-account"
                                            data-id="<?php echo (int)$a['id'];?>"
                                            data-name="<?php echo h($a['account_name']);?>"
                                            data-appid="<?php echo h($a['app_id']);?>"
                                            data-appsecret="<?php echo h($a['app_secret']);?>"
                                            data-pageid="<?php echo h($a['page_id']);?>"
                                            data-token="<?php echo h($a['page_access_token']);?>"
                                            data-verifytoken="<?php echo h($a['webhook_verify_token']);?>"
                                            data-projectid="<?php echo (int)$a['project_id'];?>"
                                            data-statusid="<?php echo (int)$a['default_status_id'];?>"
                                            data-active="<?php echo (int)$a['is_active'];?>"
                                            title="<?php echo __('Edit in popup');?>">
                                        <i class="fa fa-edit"></i> <?php echo __('Edit');?>
                                    </button>

                                    <?php echo $this->Html->link('<i class="fa fa-power-off"></i>', array('action'=>'toggle_account', $a['id']), array('class'=>'btn btn-warning', 'escape'=>false, 'title'=>__('Toggle Active/Pause')));?>

                                    <?php echo $this->Html->link('<i class="fa fa-trash"></i>', array('action'=>'delete_account', $a['id']), array('class'=>'btn btn-danger', 'escape'=>false, 'confirm'=>__('Are you sure you want to delete this Facebook account integration?'), 'title'=>__('Delete')));?>
                                </div>
                            </td>
                        </tr>
                        <?php }} else { ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted" style="padding:30px;">
                                <i class="fa fa-facebook-official fa-3x" style="color:#94a3b8;margin-bottom:10px;"></i><br/>
                                <p style="font-size:15px;font-weight:600;"><?php echo __('No Facebook Pages or API Accounts added yet.');?></p>
                                <button type="button" class="btn btn-primary btn-lg" id="btnOpenAddModalEmpty" style="background:#1877f2;border-color:#1877f2;margin-top:8px;">
                                    <i class="fa fa-facebook-official"></i> <?php echo __('1-Click Connect Facebook Page');?>
                                </button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Global Webhook Instructions Card -->
        <div class="row">
            <div class="col-md-12">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px;">
                    <h4 style="margin-top:0;font-weight:800;color:#1e293b;"><i class="fa fa-link text-primary"></i> <?php echo __('Meta Webhook & Cron Auto-Sync Details');?></h4>
                    <p style="font-size:13px;color:#64748b;"><?php echo __('Subscribe your Meta Apps to the Webhook URL below to receive live leads instantaneously from all configured Facebook pages:');?></p>

                    <div class="row" style="margin-top:14px;">
                        <div class="col-md-6">
                            <label style="font-size:11px;text-transform:uppercase;color:#475569;font-weight:700;"><?php echo __('Callback URL:');?></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="wbUrlGlobal" value="<?php echo h($webhookUrl);?>" readonly style="background:#fff;font-weight:600;">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="copyInput('wbUrlGlobal')"><i class="fa fa-copy"></i> Copy</button>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label style="font-size:11px;text-transform:uppercase;color:#475569;font-weight:700;"><?php echo __('Global Verify Token:');?></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="wbTokenGlobal" value="whitevillas_fb_verify_token_2026" readonly style="background:#fff;font-weight:600;">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="copyInput('wbTokenGlobal')"><i class="fa fa-copy"></i> Copy</button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div style="background:#eff6ff;border:1.5px solid #93c5fd;border-radius:10px;padding:16px;margin-top:16px;font-size:12px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <strong style="color:#1e40af;font-size:13px;"><i class="fa fa-clock-o"></i> <?php echo __('Automated Scheduled Polling (cPanel Cron Job):');?></strong>
                            <span class="label label-info"><?php echo __('Every 5 Minutes');?></span>
                        </div>
                        
                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-weight:700;color:#1e40af;font-size:12px;margin-bottom:4px;">
                                <i class="fa fa-terminal"></i> <?php echo __('Option 1 (Recommended): cPanel Direct PHP CLI Command:');?>
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="cpanelCliCronCmd" value="/usr/local/bin/php -q <?php echo h(ROOT . DS . 'cron_facebook_autofetch.php'); ?> > /dev/null 2>&1" readonly style="background:#fff;font-family:monospace;font-size:12px;font-weight:700;color:#0f172a;">
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" onclick="copyInput('cpanelCliCronCmd')" style="font-weight:700;"><i class="fa fa-copy"></i> <?php echo __('Copy CLI Command');?></button>
                                </span>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-weight:700;color:#1e40af;font-size:12px;margin-bottom:4px;">
                                <i class="fa fa-globe"></i> <?php echo __('Option 2: cPanel cURL Web Cron URL:');?>
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="cpanelCronCmd" value="curl -s &quot;<?php echo h(Router::url('/cron/facebook_leads', true));?>&quot; > /dev/null 2>&1" readonly style="background:#fff;font-family:monospace;font-size:12px;font-weight:700;color:#0f172a;">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="copyInput('cpanelCronCmd')" style="font-weight:700;"><i class="fa fa-copy"></i> <?php echo __('Copy Web Command');?></button>
                                </span>
                            </div>
                        </div>

                        <div style="background:#fff;border-left:4px solid #3b82f6;padding:10px 14px;border-radius:4px;font-size:11.5px;color:#1e3a8a;line-height:1.6;">
                            <strong><i class="fa fa-check-circle text-success"></i> <?php echo __('How to add in cPanel Cron Jobs:');?></strong><br/>
                            1. In cPanel &rarr; <strong>Cron Jobs</strong> &rarr; Under <strong>Common Settings</strong>, choose <em>"Once Per 5 Minutes (*/5 * * * *)"</em>.<br/>
                            2. In the <strong>"Command"</strong> box, paste either Option 1 (Direct PHP CLI) or Option 2 (cURL Web Cron). Both execute seamlessly in the background even when logged out.<br/>
                            <span style="color:#b91c1c;font-weight:700;"><i class="fa fa-exclamation-triangle"></i> Note: Do NOT type "*/5 * * * *" inside the Command box itself (cPanel already sets the schedule from the dropdown above).</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= 1-CLICK DISCOVER & SELECT FACEBOOK PAGE MODAL ================= -->
<div class="modal fade" id="fbAutoConnectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background:#1877f2;color:#fff;padding:16px 24px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" style="font-weight:800;">
                    <i class="fa fa-facebook-official"></i> <?php echo __('1-Click Facebook Page Auto-Connector');?>
                </h4>
            </div>

            <div class="modal-body" style="padding:24px;">
                <!-- Step 1: Token / Login Input -->
                <div id="autoConnectStep1">
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:18px;margin-bottom:20px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <h5 style="margin:0;font-weight:800;color:#1e40af;font-size:14px;">
                                <i class="fa fa-key"></i> <?php echo __('Paste your Meta User Token or Page Token:');?>
                            </h5>
                            <a href="https://developers.facebook.com/tools/explorer/" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-primary" style="background:#1877f2;border-color:#1877f2;font-weight:700;">
                                <i class="fa fa-external-link"></i> <?php echo __('Open Graph API Explorer');?>
                            </a>
                        </div>
                        <p style="margin:0 0 12px 0;font-size:12px;color:#1e3a8a;line-height:1.5;">
                            <?php echo __('Enter any Meta User Access Token with page permissions or a Page Token. The system will automatically discover and list all your managed Facebook Pages!');?>
                        </p>
                        <div class="input-group">
                            <input type="text" id="inputAutoToken" class="form-control" placeholder="Paste User or Page Token from developers.facebook.com/tools/explorer/">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="button" id="btnFetchUserPages" style="background:#1877f2;font-weight:700;">
                                    <i class="fa fa-search"></i> <?php echo __('Auto-Fetch My Pages');?>
                                </button>
                            </span>
                        </div>
                        <div id="fetchPagesFeedback" style="margin-top:8px;font-size:12px;font-weight:700;"></div>

                        <!-- Expandable Step-by-Step Guide -->
                        <div style="background:#fff;border:1px solid #cbd5e1;border-radius:8px;padding:14px;margin-top:14px;">
                            <strong style="color:#0f172a;font-size:13px;"><i class="fa fa-question-circle text-primary"></i> <?php echo __('How to get this token in 30 seconds:');?></strong>
                            <ol style="margin:8px 0 0 0;padding-left:20px;font-size:12px;color:#334155;line-height:1.6;">
                                <li>Open <a href="https://developers.facebook.com/tools/explorer/" target="_blank" rel="noopener noreferrer" style="font-weight:700;color:#1877f2;text-decoration:underline;">Meta Graph API Explorer <i class="fa fa-external-link"></i></a>.</li>
                                <li>Under <strong>Meta App</strong>, select your App.</li>
                                <li>Under <strong>User or Page</strong>, click <em>Get User Access Token</em> (or choose your Page).</li>
                                <li>Click <strong>Add a Permission</strong> and select these 5 permissions:
                                    <div style="margin:4px 0;">
                                        <span class="label label-info">pages_show_list</span>
                                        <span class="label label-info">pages_read_engagement</span>
                                        <span class="label label-info">pages_manage_metadata</span>
                                        <span class="label label-info">pages_manage_ads</span>
                                        <span class="label label-info">leads_retrieval</span>
                                    </div>
                                </li>
                                <li>Click <span class="label label-primary">Generate Access Token</span>, approve the Facebook popup, copy the token, paste it above, and click <strong>Auto-Fetch My Pages</strong>!</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Discovered Pages List -->
                <div id="autoConnectStep2" style="display:none;">
                    <h5 style="font-weight:800;color:#0f172a;margin-top:0;">
                        <i class="fa fa-check-circle text-success"></i> <?php echo __('Select Facebook Page to Connect:');?>
                    </h5>
                    <div id="discoveredPagesList" style="max-height:350px;overflow-y:auto;"></div>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;padding:14px 24px;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MANUAL ADD / EDIT FACEBOOK ACCOUNT MODAL ================= -->
<div class="modal fade" id="fbAccountModal" tabindex="-1" role="dialog" aria-labelledby="fbAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.2);">
            <?php echo $this->Form->create('FacebookAccount', array('url'=>array('plugin'=>'admin', 'controller'=>'facebook_integrations', 'action'=>'save_account'), 'id'=>'formFbAccount'));?>
            <input type="hidden" name="data[FacebookAccount][id]" id="accModalId" value="">

            <div class="modal-header" style="background:#1877f2;color:#fff;padding:16px 24px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="fbAccountModalLabel" style="font-weight:800;">
                    <i class="fa fa-facebook-square"></i> <span id="modalTitleText"><?php echo __('Add New Facebook Page / App Account');?></span>
                </h4>
            </div>

            <div class="modal-body" style="padding:24px;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><?php echo __('Account / Page Display Name:');?> *</strong></label>
                            <input type="text" name="data[FacebookAccount][account_name]" id="accModalName" class="form-control" placeholder="e.g. White Villas Official Page" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><?php echo __('Facebook Page ID:');?> *</strong></label>
                            <input type="text" name="data[FacebookAccount][page_id]" id="accModalPageId" class="form-control" placeholder="e.g. 1029384756 or me" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><?php echo __('Meta App ID (Optional):');?></strong></label>
                            <input type="text" name="data[FacebookAccount][app_id]" id="accModalAppId" class="form-control" placeholder="e.g. 123456789012345">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><?php echo __('Meta App Secret (Optional):');?></strong></label>
                            <input type="password" name="data[FacebookAccount][app_secret]" id="accModalAppSecret" class="form-control" placeholder="App Secret">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><strong><?php echo __('Page Access Token:');?> *</strong></label>
                    <textarea name="data[FacebookAccount][page_access_token]" id="accModalToken" class="form-control" rows="3" placeholder="Paste your Never-Expiring Meta Page Access Token here..." required></textarea>
                    <div style="margin-top:6px;display:flex;align-items:center;gap:10px;">
                        <button type="button" class="btn btn-xs btn-info" id="btnTestModalToken">
                            <i class="fa fa-bolt"></i> <?php echo __('Test Token & Fetch Page Name');?>
                        </button>
                        <span id="modalTokenFeedback" style="font-weight:700;font-size:12px;"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><?php echo __('Target Project / Property:');?></strong></label>
                            <select name="data[FacebookAccount][project_id]" id="accModalProjectId" class="form-control">
                                <option value=""><?php echo __('All Projects (Default)');?></option>
                                <?php foreach($projects as $pid=>$pname){ ?>
                                    <option value="<?php echo $pid;?>"><?php echo h($pname);?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><?php echo __('Default Pipeline Status:');?></strong></label>
                            <select name="data[FacebookAccount][default_status_id]" id="accModalStatusId" class="form-control">
                                <?php foreach($statuses as $sid=>$sname){ ?>
                                    <option value="<?php echo $sid;?>"><?php echo h($sname);?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><?php echo __('Custom Webhook Verify Token:');?></strong></label>
                            <input type="text" name="data[FacebookAccount][webhook_verify_token]" id="accModalVerifyToken" class="form-control" value="whitevillas_fb_verify_token_2026">
                        </div>
                    </div>

                    <div class="col-md-6" style="padding-top:28px;">
                        <label class="checkbox-inline" style="font-weight:700;font-size:14px;color:#0f172a;">
                            <input type="checkbox" name="data[FacebookAccount][is_active]" id="accModalActive" value="1" checked>
                            <?php echo __('Enable Active Auto-Fetching for this Page');?>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;padding:16px 24px;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel');?></button>
                <button type="submit" class="btn btn-primary" style="font-weight:700;padding:8px 20px;">
                    <i class="fa fa-save"></i> <span id="btnSaveText"><?php echo __('Save Facebook Account');?></span>
                </button>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>

<script type="text/javascript">
function copyInput(id) {
    var copyText = document.getElementById(id);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Copied: " + copyText.value);
}

$(document).ready(function(){
    // 1-Click Connect Button
    $('#btn1ClickFbLogin, #btnHeroFbConnect, #btnOpenAddModalEmpty').on('click', function(){
        $('#inputAutoToken').val('');
        $('#fetchPagesFeedback').text('').removeClass('text-success text-danger');
        $('#autoConnectStep2').hide();
        $('#discoveredPagesList').empty();
        $('#fbAutoConnectModal').modal('show');
    });

    // Fetch user managed pages from token
    $('#btnFetchUserPages').on('click', function(){
        var token = $('#inputAutoToken').val().trim();
        var $btn = $(this);
        var $fb = $('#fetchPagesFeedback');

        if(token === '') {
            $fb.addClass('text-danger').text('Please paste a token.');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Discovering Pages...');
        $fb.text('').removeClass('text-success text-danger');

        // Fetch /me/accounts from Graph API
        $.ajax({
            url: 'https://graph.facebook.com/v19.0/me/accounts?fields=id,name,category,access_token,link&access_token=' + encodeURIComponent(token),
            type: 'GET',
            dataType: 'json',
            success: function(res){
                $btn.prop('disabled', false).html('<i class="fa fa-search"></i> <?php echo __("Auto-Fetch My Pages");?>');
                if(res.data && res.data.length > 0) {
                    $fb.addClass('text-success').html('✓ Found ' + res.data.length + ' managed Facebook Page(s)!');
                    renderDiscoveredPages(res.data);
                } else {
                    // Try fallback to /me if it is already a Page token
                    $.ajax({
                        url: 'https://graph.facebook.com/v19.0/me?fields=id,name,category&access_token=' + encodeURIComponent(token),
                        type: 'GET',
                        dataType: 'json',
                        success: function(pageRes){
                            if(pageRes.id) {
                                $fb.addClass('text-success').html('✓ Connected to Page: ' + pageRes.name);
                                renderDiscoveredPages([{
                                    id: pageRes.id,
                                    name: pageRes.name,
                                    category: pageRes.category || 'Facebook Business Page',
                                    access_token: token
                                }]);
                            } else {
                                $fb.addClass('text-danger').text('No pages found with this token.');
                            }
                        },
                        error: function(){
                            $fb.addClass('text-danger').text('Invalid or expired token.');
                        }
                    });
                }
            },
            error: function(xhr){
                $btn.prop('disabled', false).html('<i class="fa fa-search"></i> <?php echo __("Auto-Fetch My Pages");?>');
                var err = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error.message : 'Failed to reach Meta Graph API.';
                $fb.addClass('text-danger').text(err);
            }
        });
    });

    function renderDiscoveredPages(pages) {
        var $list = $('#discoveredPagesList');
        $list.empty();

        $.each(pages, function(idx, page){
            var html = '<div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 18px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 6px rgba(0,0,0,0.04);">' +
                '<div>' +
                    '<div style="font-weight:800;font-size:15px;color:#0f172a;"><i class="fa fa-flag text-primary"></i> ' + $('<div>').text(page.name).html() + '</div>' +
                    '<div style="font-size:12px;color:#64748b;margin-top:2px;">ID: <code>' + $('<div>').text(page.id).html() + '</code> | ' + $('<div>').text(page.category || 'Business Page').html() + '</div>' +
                '</div>' +
                '<div>' +
                    '<button type="button" class="btn btn-success btn-connect-discovered" ' +
                            'data-id="' + $('<div>').text(page.id).html() + '" ' +
                            'data-name="' + $('<div>').text(page.name).html() + '" ' +
                            'data-token="' + $('<div>').text(page.access_token).html() + '" ' +
                            'style="font-weight:700;">' +
                        '<i class="fa fa-link"></i> <?php echo __("Connect & Auto-Sync Leads");?>' +
                    '</button>' +
                '</div>' +
            '</div>';
            $list.append(html);
        });

        $('#autoConnectStep2').show();
    }

    // Connect Discovered Page via AJAX
    $(document).on('click', '.btn-connect-discovered', function(){
        var $btn = $(this);
        var pageId = $btn.data('id');
        var pageName = $btn.data('name');
        var token = $btn.data('token');

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Connecting...');

        $.ajax({
            url: '<?php echo $this->Html->url(array("action"=>"connect_page_ajax"));?>',
            type: 'POST',
            data: {
                page_id: pageId,
                page_name: pageName,
                access_token: token
            },
            dataType: 'json',
            success: function(res){
                if(res.success){
                    $btn.removeClass('btn-success').addClass('btn-primary').html('<i class="fa fa-check"></i> Connected!');
                    alert('✓ ' + res.message);
                    window.location.reload();
                } else {
                    $btn.prop('disabled', false).html('<i class="fa fa-link"></i> Connect & Auto-Sync');
                    alert('✕ Error: ' + res.message);
                }
            },
            error: function(){
                $btn.prop('disabled', false).html('<i class="fa fa-link"></i> Connect & Auto-Sync');
                alert('Connection request failed.');
            }
        });
    });

    // Open Manual Add Modal
    $('#btnOpenAddModal').on('click', function(){
        $('#formFbAccount')[0].reset();
        $('#accModalId').val('');
        $('#modalTitleText').text('<?php echo __("Add New Facebook Page / App Account");?>');
        $('#btnSaveText').text('<?php echo __("Save Facebook Account");?>');
        $('#modalTokenFeedback').text('').removeClass('text-success text-danger');
        $('#accModalVerifyToken').val('whitevillas_fb_verify_token_2026');
        $('#accModalActive').prop('checked', true);
        $('#fbAccountModal').modal('show');
    });

    // Open Edit Modal with Pre-populated data
    $('.btn-edit-account').on('click', function(){
        var $btn = $(this);
        $('#accModalId').val($btn.data('id'));
        $('#accModalName').val($btn.data('name'));
        $('#accModalAppId').val($btn.data('appid'));
        $('#accModalAppSecret').val($btn.data('appsecret'));
        $('#accModalPageId').val($btn.data('pageid'));
        $('#accModalToken').val($btn.data('token'));
        $('#accModalVerifyToken').val($btn.data('verifytoken'));
        $('#accModalProjectId').val($btn.data('projectid'));
        $('#accModalStatusId').val($btn.data('statusid'));
        $('#accModalActive').prop('checked', $btn.data('active') == 1);

        $('#modalTitleText').text('<?php echo __("Edit Facebook Page / App Account");?>: ' + $btn.data('name'));
        $('#btnSaveText').text('<?php echo __("Update Facebook Account");?>');
        $('#modalTokenFeedback').text('').removeClass('text-success text-danger');
        $('#fbAccountModal').modal('show');
    });

    // Test Token in Modal
    $('#btnTestModalToken').on('click', function(){
        var $btn = $(this);
        var token = $('#accModalToken').val().trim();
        var pageId = $('#accModalPageId').val().trim();
        var $fb = $('#modalTokenFeedback');

        if(token === '') {
            $fb.addClass('text-danger').removeClass('text-success').text('Please paste a token first.');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Testing...');
        $fb.text('').removeClass('text-success text-danger');

        $.ajax({
            url: '<?php echo $this->Html->url(array("action"=>"test_account_token"));?>',
            type: 'POST',
            data: { page_access_token: token, page_id: pageId },
            dataType: 'json',
            success: function(res){
                $btn.prop('disabled', false).html('<i class="fa fa-bolt"></i> <?php echo __("Test Token & Fetch Page Name");?>');
                if(res.success){
                    $fb.addClass('text-success').html('<i class="fa fa-check-circle"></i> ' + res.message);
                    if($('#accModalName').val().trim() === '' || $('#accModalName').val().indexOf('Page') !== -1){
                        $('#accModalName').val(res.page_name);
                    }
                    if($('#accModalPageId').val().trim() === '' || $('#accModalPageId').val() === 'me'){
                        $('#accModalPageId').val(res.page_id);
                    }
                } else {
                    $fb.addClass('text-danger').html('<i class="fa fa-times-circle"></i> ' + res.message);
                }
            },
            error: function(){
                $btn.prop('disabled', false).html('<i class="fa fa-bolt"></i> <?php echo __("Test Token & Fetch Page Name");?>');
                $fb.addClass('text-danger').text('Connection failed.');
            }
        });
    });

    // Test Token directly from Table Row
    $('.btn-test-row-token').on('click', function(){
        var $btn = $(this);
        var token = $btn.data('token');
        var pageId = $btn.data('pageid');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '<?php echo $this->Html->url(array("action"=>"test_account_token"));?>',
            type: 'POST',
            data: { page_access_token: token, page_id: pageId },
            dataType: 'json',
            success: function(res){
                $btn.prop('disabled', false).html('<i class="fa fa-bolt text-warning"></i> <?php echo __("Test API");?>');
                if(res.success){
                    alert("✓ " + res.message);
                } else {
                    alert("✕ Connection Error: " + res.message);
                }
            },
            error: function(){
                $btn.prop('disabled', false).html('<i class="fa fa-bolt text-warning"></i> <?php echo __("Test API");?>');
                alert("Connection request failed.");
            }
        });
    });
});
</script>