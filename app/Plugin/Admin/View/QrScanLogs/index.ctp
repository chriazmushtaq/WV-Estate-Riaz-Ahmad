<style>
.wv-kpi-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 18px 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: transform 0.2s ease;
}
.wv-kpi-card:hover {
    transform: translateY(-2px);
}
.wv-kpi-val {
    font-size: 26px;
    font-weight: 800;
    line-height: 1.1;
    margin-top: 4px;
}
.wv-kpi-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
}
.wv-kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.pulse-alert {
    animation: alertPulse 1.8s infinite;
}
@keyframes alertPulse {
    0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
.badge-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.badge-status.verified { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.badge-status.invalid { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.badge-status.expired { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
.badge-status.not_found { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.badge-suspicious {
    background: #7f1d1d;
    color: #ffffff;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
</style>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-shield text-success"></i> <?php echo __('QR Scan Audit & Security Surveillance');?></h1>
        <p class="description"><?php echo __('Real-time tamper audit, scan location tracking, device analytics, and suspicious attempt detection.');?></p>
    </div>
    <div class="breadcrumb-env">
        <div class="btn-group">
            <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Export CSV'), array('action'=>'export'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#clearLogsModal">
                <i class="fa fa-trash"></i> <?php echo __('Clear Logs');?>
            </button>
        </div>
    </div>
</div>

<!-- Suspicious Alert Banner -->
<?php if (!empty($suspiciousAlerts) && $suspiciousAlerts > 0) { ?>
<div class="alert alert-danger pulse-alert" style="background:#fef2f2;border:1px solid #dc2626;color:#991b1b;border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <span style="font-size:26px;">🚨</span>
        <div>
            <strong style="font-size:15px;display:block;"><?php echo __('CRITICAL SECURITY ALERT: Suspicious QR Verification Attempts Detected!');?></strong>
            <span style="font-size:13px;opacity:0.95;">
                <?php echo sprintf(__('%d suspicious/repeated failed scans have been flagged by the system. Potential forgery or altered QR token detected.'), $suspiciousAlerts);?>
            </span>
        </div>
    </div>
    <div>
        <a href="<?php echo $this->Html->url(array('action'=>'index', '?'=>array('alert'=>1)));?>" class="btn btn-danger btn-sm" style="font-weight:700;">
            <i class="fa fa-filter"></i> <?php echo __('View Flagged Scans Only');?>
        </a>
    </div>
</div>
<?php } ?>

<!-- KPI Row -->
<div class="row" style="margin-bottom:20px;">
    <div class="col-sm-6 col-md-3" style="margin-bottom:12px;">
        <div class="wv-kpi-card">
            <div>
                <div class="wv-kpi-label"><?php echo __('Total Scans');?></div>
                <div class="wv-kpi-val" style="color:#0f172a;"><?php echo number_format($totalScans);?></div>
            </div>
            <div class="wv-kpi-icon" style="background:#f1f5f9;color:#334155;">
                <i class="fa fa-qrcode"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3" style="margin-bottom:12px;">
        <div class="wv-kpi-card">
            <div>
                <div class="wv-kpi-label"><?php echo __('Authentic / Verified');?></div>
                <div class="wv-kpi-val" style="color:#059669;"><?php echo number_format($verifiedScans);?></div>
            </div>
            <div class="wv-kpi-icon" style="background:#ecfdf5;color:#059669;">
                <i class="fa fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3" style="margin-bottom:12px;">
        <div class="wv-kpi-card">
            <div>
                <div class="wv-kpi-label"><?php echo __('Failed / Invalid');?></div>
                <div class="wv-kpi-val" style="color:#dc2626;"><?php echo number_format($invalidScans);?></div>
            </div>
            <div class="wv-kpi-icon" style="background:#fef2f2;color:#dc2626;">
                <i class="fa fa-times-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3" style="margin-bottom:12px;">
        <div class="wv-kpi-card" style="<?php echo $suspiciousAlerts > 0 ? 'border-color:#dc2626;background:#fff5f5;' : '';?>">
            <div>
                <div class="wv-kpi-label"><?php echo __('Security Alerts');?></div>
                <div class="wv-kpi-val" style="<?php echo $suspiciousAlerts > 0 ? 'color:#dc2626;' : 'color:#64748b;';?>">
                    <?php echo number_format($suspiciousAlerts);?>
                </div>
            </div>
            <div class="wv-kpi-icon" style="<?php echo $suspiciousAlerts > 0 ? 'background:#dc2626;color:#ffffff;' : 'background:#f1f5f9;color:#94a3b8;';?>">
                <i class="fa fa-shield"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters Panel -->
<div class="panel panel-default" style="border-radius:12px;margin-bottom:20px;">
    <div class="panel-body" style="padding:16px 20px;">
        <form method="get" action="<?php echo $this->Html->url(array('action'=>'index'));?>" class="form-inline" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
            
            <div class="form-group" style="flex:1;min-width:220px;">
                <input type="text" name="q" value="<?php echo h($searchQuery);?>" class="form-control" placeholder="<?php echo __('Search reference, client, IP, token...');?>" style="width:100%;">
            </div>

            <div class="form-group">
                <select name="status" class="form-control">
                    <option value="ALL"><?php echo __('All Statuses');?></option>
                    <option value="VERIFIED" <?php echo $statusFilter === 'VERIFIED' ? 'selected' : '';?>><?php echo __('✓ Verified Authentic');?></option>
                    <option value="INVALID_TOKEN" <?php echo $statusFilter === 'INVALID_TOKEN' ? 'selected' : '';?>><?php echo __('✕ Invalid Token');?></option>
                    <option value="NOT_FOUND" <?php echo $statusFilter === 'NOT_FOUND' ? 'selected' : '';?>><?php echo __('? Not Found');?></option>
                    <option value="EXPIRED" <?php echo $statusFilter === 'EXPIRED' ? 'selected' : '';?>><?php echo __('⏰ Expired / Unpublished');?></option>
                </select>
            </div>

            <div class="form-group">
                <select name="alert" class="form-control">
                    <option value=""><?php echo __('All Scans (Normal & Alerts)');?></option>
                    <option value="1" <?php echo $alertFilter === 1 ? 'selected' : '';?>><?php echo __('🚨 Only Suspicious Alerts');?></option>
                    <option value="0" <?php echo $alertFilter === 0 ? 'selected' : '';?>><?php echo __('Clean Scans Only');?></option>
                </select>
            </div>

            <div class="form-group">
                <select name="device" class="form-control">
                    <option value="ALL"><?php echo __('All Devices');?></option>
                    <option value="Mobile" <?php echo $deviceFilter === 'Mobile' ? 'selected' : '';?>><?php echo __('📱 Mobile');?></option>
                    <option value="Desktop" <?php echo $deviceFilter === 'Desktop' ? 'selected' : '';?>><?php echo __('💻 Desktop');?></option>
                    <option value="Tablet" <?php echo $deviceFilter === 'Tablet' ? 'selected' : '';?>><?php echo __('📟 Tablet');?></option>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="font-weight:700;">
                    <i class="fa fa-filter"></i> <?php echo __('Filter');?>
                </button>
                <a href="<?php echo $this->Html->url(array('action'=>'index'));?>" class="btn btn-default">
                    <i class="fa fa-refresh"></i> <?php echo __('Reset');?>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Main Logs Table -->
<div class="panel panel-default" style="border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.04);">
    <div class="panel-heading" style="background:#f8fafc;padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:800;color:#0f172a;font-size:15px;">
            <i class="fa fa-list text-primary"></i> <?php echo __('QR Code Verification Scan Logs');?>
        </span>
        <span class="text-muted" style="font-size:12px;">
            <?php echo sprintf(__('Showing %d scan records'), count($logs));?>
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover" style="margin-bottom:0;vertical-align:middle;font-size:13px;">
            <thead>
                <tr style="background:#f1f5f9;color:#334155;font-weight:800;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">
                    <th style="width:60px;"><?php echo __('#ID');?></th>
                    <th style="width:160px;"><?php echo __('Scan Timestamp');?></th>
                    <th style="width:140px;"><?php echo __('Audit Status');?></th>
                    <th><?php echo __('Document & Client Info');?></th>
                    <th><?php echo __('Device, Browser & OS');?></th>
                    <th><?php echo __('IP & Location');?></th>
                    <th style="width:110px;text-align:center;"><?php echo __('Actions');?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)) { ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding:40px 20px;color:#64748b;">
                            <i class="fa fa-shield" style="font-size:36px;color:#cbd5e1;display:block;margin-bottom:10px;"></i>
                            <strong style="font-size:16px;display:block;color:#334155;"><?php echo __('No QR scan audit records found.');?></strong>
                            <span><?php echo __('Scan logs will automatically populate when someone scans a letter verification QR code.');?></span>
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php foreach ($logs as $log) { 
                        $item = $log['QrScanLog'];
                        $statusClass = 'invalid';
                        if ($item['scan_status'] === 'VERIFIED') $statusClass = 'verified';
                        elseif ($item['scan_status'] === 'EXPIRED') $statusClass = 'expired';
                        elseif ($item['scan_status'] === 'NOT_FOUND') $statusClass = 'not_found';

                        $deviceIcon = 'fa-desktop';
                        if ($item['device_type'] === 'Mobile') $deviceIcon = 'fa-mobile';
                        elseif ($item['device_type'] === 'Tablet') $deviceIcon = 'fa-tablet';
                    ?>
                    <tr style="<?php echo !empty($item['is_suspicious']) ? 'background:#fff1f2;' : '';?>">
                        <td style="font-weight:700;color:#64748b;">
                            #<?php echo $item['id'];?>
                        </td>
                        <td>
                            <strong style="color:#0f172a;display:block;"><?php echo date('d M Y, h:i A', strtotime($item['created']));?></strong>
                            <small class="text-muted"><?php echo date('H:i:s', strtotime($item['created']));?> PKT</small>
                        </td>
                        <td>
                            <span class="badge-status <?php echo $statusClass;?>">
                                <?php if ($item['scan_status'] === 'VERIFIED') { ?>
                                    <i class="fa fa-check-circle"></i> VERIFIED
                                <?php } elseif ($item['scan_status'] === 'INVALID_TOKEN') { ?>
                                    <i class="fa fa-exclamation-triangle"></i> INVALID TOKEN
                                <?php } elseif ($item['scan_status'] === 'EXPIRED') { ?>
                                    <i class="fa fa-ban"></i> EXPIRED
                                <?php } else { ?>
                                    <i class="fa fa-question-circle"></i> NOT FOUND
                                <?php } ?>
                            </span>

                            <?php if (!empty($item['is_suspicious'])) { ?>
                                <div style="margin-top:6px;">
                                    <span class="badge-suspicious" title="<?php echo h($item['suspicious_reason']);?>">
                                        🚨 ALERT: SUSPICIOUS
                                    </span>
                                </div>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if (!empty($item['reference_no'])) { ?>
                                <div style="font-weight:800;color:#0284c7;">
                                    <?php if (!empty($item['letter_id'])) { ?>
                                        <a href="<?php echo $this->Html->url(array('controller'=>'realestateletters', 'action'=>'view', $item['letter_id']));?>" target="_blank" style="text-decoration:none;">
                                            <i class="fa fa-file-text-o"></i> <?php echo h($item['reference_no']);?>
                                        </a>
                                    <?php } else { ?>
                                        <?php echo h($item['reference_no']);?>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <span class="text-muted"><i class="fa fa-minus"></i> Unknown Document</span>
                            <?php } ?>

                            <?php if (!empty($item['client_name'])) { ?>
                                <div style="font-weight:700;color:#1e293b;margin-top:2px;">
                                    <i class="fa fa-user-circle text-muted"></i> <?php echo h($item['client_name']);?>
                                </div>
                            <?php } ?>

                            <?php if (!empty($item['property_name'])) { ?>
                                <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                    <i class="fa fa-home"></i> <?php echo h($item['property_name']);?>
                                </div>
                            <?php } ?>

                            <?php if (!empty($item['is_suspicious']) && !empty($item['suspicious_reason'])) { ?>
                                <div style="margin-top:4px;padding:4px 8px;background:#fee2e2;border-left:3px solid #dc2626;border-radius:4px;font-size:11px;color:#991b1b;font-weight:600;">
                                    <i class="fa fa-warning"></i> <?php echo h($item['suspicious_reason']);?>
                                </div>
                            <?php } ?>
                        </td>
                        <td>
                            <div style="font-weight:700;color:#0f172a;display:flex;align-items:center;gap:6px;">
                                <i class="fa <?php echo $deviceIcon;?> text-primary" style="font-size:15px;"></i>
                                <span><?php echo h($item['device_type']);?></span>
                            </div>
                            <div style="font-size:12px;color:#475569;margin-top:2px;">
                                <span><?php echo h(!empty($item['browser']) ? $item['browser'] : 'Unknown Browser');?></span>
                                &bull;
                                <span><?php echo h(!empty($item['os']) ? $item['os'] : 'Unknown OS');?></span>
                            </div>
                        </td>
                        <td>
                            <div style="font-family:monospace;font-weight:700;color:#0f172a;">
                                <i class="fa fa-globe text-info"></i> <?php echo h($item['ip_address']);?>
                            </div>
                            <div style="font-size:12px;color:#64748b;margin-top:2px;">
                                <span><?php echo h(!empty($item['city']) ? $item['city'] : 'Pakistan Area');?></span>,
                                <span><?php echo h(!empty($item['country']) ? $item['country'] : 'Pakistan');?></span>
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm" onclick="showScanModal(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');?>)" title="<?php echo __('View Audit Details');?>">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <?php echo $this->Html->link('<i class="fa fa-trash"></i>', array('action'=>'delete', $item['id']), array('class'=>'btn btn-danger btn-sm', 'escape'=>false, 'confirm'=>__('Are you sure you want to delete this scan audit log entry?')), null);?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($this->Paginator->hasPage(null, 2)) { ?>
    <div class="panel-footer" style="background:#f8fafc;padding:14px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div class="text-muted" style="font-size:13px;">
            <?php echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total')));?>
        </div>
        <div>
            <ul class="pagination pagination-sm" style="margin:0;">
                <?php 
                    echo $this->Paginator->prev('« ' . __('Previous'), array('tag' => 'li'), null, array('tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a'));
                    echo $this->Paginator->numbers(array('separator' => '', 'currentTag' => 'a', 'currentClass' => 'active', 'tag' => 'li', 'first' => 1));
                    echo $this->Paginator->next(__('Next') . ' »', array('tag' => 'li'), null, array('tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a'));
                ?>
            </ul>
        </div>
    </div>
    <?php } ?>
</div>

<!-- Modal: View Detailed Scan Forensics -->
<div class="modal fade" id="scanDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header" style="background:#091424;color:#ffffff;display:flex;justify-content:space-between;align-items:center;">
                <h4 class="modal-title" style="color:#ffffff;font-weight:800;">
                    <i class="fa fa-shield text-success"></i> <?php echo __('QR Scan Security Audit Forensics');?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <div id="modal-alert-container"></div>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered" style="font-size:13px;">
                            <tr><th style="width:40%;background:#f8fafc;"><?php echo __('Log ID');?></th><td id="m-id" style="font-weight:700;"></td></tr>
                            <tr><th style="background:#f8fafc;"><?php echo __('Scan Timestamp');?></th><td id="m-time"></td></tr>
                            <tr><th style="background:#f8fafc;"><?php echo __('Scan Status');?></th><td id="m-status"></td></tr>
                            <tr><th style="background:#f8fafc;"><?php echo __('Document Ref');?></th><td id="m-ref" style="font-weight:700;color:#0284c7;"></td></tr>
                            <tr><th style="background:#f8fafc;"><?php echo __('Client Name');?></th><td id="m-client" style="font-weight:700;"></td></tr>
                            <tr><th style="background:#f8fafc;"><?php echo __('Property');?></th><td id="m-property"></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered" style="font-size:13px;">
                            <tr><th style="width:40%;background:#f8fafc;"><?php echo __('IP Address');?></th><td id="m-ip" style="font-family:monospace;font-weight:700;"></td></tr>
                            <tr><th style="background:#f8fafc;"><?php echo __('City & Country');?></th><td id="m-location"></td></tr>
                            <tr><th style="background:#f8fafc;"><?php echo __('Device Type');?></th><td id="m-device"></td></tr>
                            <tr><th style="background:#f8fafc;"><?php echo __('Browser');?></th><td id="m-browser"></td></tr>
                            <tr><th style="background:#f8fafc;"><?php echo __('Operating System');?></th><td id="m-os"></td></tr>
                            <tr><th style="background:#f8fafc;"><?php echo __('Token Scanned');?></th><td id="m-token" style="font-family:monospace;font-size:11px;word-break:break-all;"></td></tr>
                        </table>
                    </div>
                </div>

                <div style="margin-top:12px;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;"><?php echo __('Full HTTP User-Agent:');?></label>
                    <pre id="m-ua" style="font-size:11px;background:#f8fafc;border:1px solid #e2e8f0;padding:10px;border-radius:6px;max-height:90px;overflow-y:auto;"></pre>
                </div>
            </div>
            <div class="modal-footer" style="background:#f8fafc;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Clear Logs Confirmation -->
<div class="modal fade" id="clearLogsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="post" action="<?php echo $this->Html->url(array('action'=>'clear_all'));?>">
            <div class="modal-content" style="border-radius:12px;overflow:hidden;">
                <div class="modal-header" style="background:#dc2626;color:#ffffff;">
                    <h4 class="modal-title" style="color:#ffffff;font-weight:800;">
                        <i class="fa fa-trash"></i> <?php echo __('Clear QR Scan Logs');?>
                    </h4>
                </div>
                <div class="modal-body" style="padding:20px;">
                    <p><?php echo __('Select which logs you would like to purge:');?></p>
                    <div class="radio">
                        <label>
                            <input type="radio" name="clear_type" value="non_suspicious" checked>
                            <strong><?php echo __('Clear routine verified logs only');?></strong> (Recommended: keeps all suspicious alerts intact)
                        </label>
                    </div>
                    <div class="radio" style="margin-top:10px;">
                        <label>
                            <input type="radio" name="clear_type" value="all">
                            <strong class="text-danger"><?php echo __('Purge all scan logs completely');?></strong>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel');?></button>
                    <button type="submit" class="btn btn-danger" style="font-weight:700;"><?php echo __('Confirm Purge');?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function showScanModal(item) {
    document.getElementById('m-id').textContent = '#' + item.id;
    document.getElementById('m-time').textContent = item.created + ' PKT';
    document.getElementById('m-status').innerHTML = item.scan_status === 'VERIFIED' ? 
        '<span class="badge-status verified"><i class="fa fa-check-circle"></i> VERIFIED AUTHENTIC</span>' : 
        '<span class="badge-status invalid"><i class="fa fa-times-circle"></i> ' + item.scan_status + '</span>';
    document.getElementById('m-ref').textContent = item.reference_no ? item.reference_no : 'N/A';
    document.getElementById('m-client').textContent = item.client_name ? item.client_name : 'N/A';
    document.getElementById('m-property').textContent = item.property_name ? item.property_name : 'N/A';
    document.getElementById('m-ip').textContent = item.ip_address;
    document.getElementById('m-location').textContent = (item.city || 'Pakistan Area') + ', ' + (item.country || 'Pakistan');
    document.getElementById('m-device').textContent = item.device_type;
    document.getElementById('m-browser').textContent = item.browser;
    document.getElementById('m-os').textContent = item.os;
    document.getElementById('m-token').textContent = item.token_attempted;
    document.getElementById('m-ua').textContent = item.user_agent;

    var alertContainer = document.getElementById('modal-alert-container');
    if (item.is_suspicious == 1) {
        alertContainer.innerHTML = '<div class="alert alert-danger" style="font-weight:700;padding:10px 14px;border-radius:8px;margin-bottom:16px;">🚨 SUSPICIOUS SCAN ALERT: ' + (item.suspicious_reason || 'Repeated invalid attempts') + '</div>';
    } else {
        alertContainer.innerHTML = '';
    }

    if (window.$ && $.fn.modal) {
        $('#scanDetailModal').modal('show');
    } else {
        var el = document.getElementById('scanDetailModal');
        el.style.display = 'block';
        el.classList.add('in');
    }
}
</script>
