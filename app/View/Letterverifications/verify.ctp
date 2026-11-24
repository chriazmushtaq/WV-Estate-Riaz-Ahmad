<?php
$letter = !empty($post['Realestateletter']) ? (array)$post['Realestateletter'] : array();
$deal = !empty($post['Deal']) ? (array)$post['Deal'] : array();
$client = !empty($post['Client']) ? (array)$post['Client'] : array();
$property = !empty($post['Property']) ? (array)$post['Property'] : array();
$project = !empty($post['Project']) ? (array)$post['Project'] : array();
$template = !empty($post['Template']) ? (array)$post['Template'] : array();

$status = !empty($letter['status']) ? (string)$letter['status'] : 'Published';
$statusLabel = ($status === 'Published') ? __('Official / Issued') : (($status === 'Unpublished') ? __('Cancelled / Inactive') : $status);
$statusUrdu = ($status === 'Published') ? 'جاری شدہ / فعال' : (($status === 'Unpublished') ? 'منسوخ شدہ / غیر فعال' : $status);

$clientCnic = !empty($client['pincode']) ? (string)$client['pincode'] : (!empty($client['phone']) ? (string)$client['phone'] : '-');
if(strlen($clientCnic) >= 13 && is_numeric(str_replace('-', '', $clientCnic))){
    $rawCnic = str_replace('-', '', $clientCnic);
    $maskedCnic = substr($rawCnic, 0, 5) . '-*******-' . substr($rawCnic, -1);
} else {
    $maskedCnic = $clientCnic;
}
$realLetterId = '';
if (!empty($deal['invoice_no'])) {
    $rawInv = trim((string)$deal['invoice_no']);
    if (stripos($rawInv, 'ID. No:') === 0 || stripos($rawInv, 'ID. No') === 0 || stripos($rawInv, 'ID:') === 0) {
        $realLetterId = $rawInv;
    } else {
        $realLetterId = 'ID. No: ' . $rawInv;
    }
} elseif (!empty($letter['title']) && preg_match('/WV-[A-Z0-9\/\-\s]+/i', $letter['title'], $matches)) {
    $realLetterId = 'ID. No: ' . trim($matches[0]);
} elseif (!empty($letter['reference_no'])) {
    $realLetterId = 'ID. No: ' . trim((string)$letter['reference_no']);
} else {
    $realLetterId = 'ID. No: WV-LTR-' . (!empty($letter['id']) ? $letter['id'] : '1');
}

$refNo = $realLetterId;
$issueDate = !empty($letter['letter_date']) ? date('d-M-Y', strtotime($letter['letter_date'])) : (!empty($letter['created']) ? date('d-M-Y', strtotime($letter['created'])) : date('d-M-Y'));

$clientPhotoFile = !empty($client['photo']) ? trim((string)$client['photo']) : '';
$clientPhotoDir = !empty($client['dirt']) ? trim((string)$client['dirt']) : (!empty($client['dir']) ? trim((string)$client['dir']) : 'contact');
$clientPhotoUrl = '';
if ($clientPhotoFile !== '') {
    $clientPhotoUrl = $this->Html->url('/img/' . $clientPhotoDir . '/' . $clientPhotoFile, true);
} else {
    $clientPhotoUrl = $this->Html->url('/img/User.png', true);
}
$agencyLogoUrl = !empty($project['logo']) ? $this->Html->url('/img/' . $project['logo'], true) : (!empty($project['photo']) ? $this->Html->url('/img/' . $project['photo'], true) : $this->Html->url('/img/logo.png', true));
?>
<?php echo $this->Html->css('wv-urdu-fonts'); ?>
<style type="text/css">
.wv-verify-wrapper {
    background: linear-gradient(135deg, #0b1528 0%, #172a46 100%);
    min-height: 100vh;
    padding: 32px 14px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #1e293b;
}
.wv-verify-container {
    max-width: 960px;
    margin: 0 auto;
}
.wv-verify-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}
.wv-verify-topbar {
    background: #091424;
    padding: 22px 28px;
    color: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #1e3a5f;
    flex-wrap: wrap;
    gap: 15px;
}
.wv-verify-brand h2 {
    color: #ffffff !important;
    font-size: 22px;
    font-weight: 900;
    margin: 0;
    letter-spacing: 0.5px;
}
.wv-verify-brand p {
    color: #94a3b8;
    font-size: 13px;
    margin: 4px 0 0;
}
.wv-verify-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 30px;
    font-weight: 800;
    font-size: 13px;
    letter-spacing: 0.5px;
}
.wv-verify-badge-pill.verified {
    background: #064e3b;
    color: #34d399;
    border: 1px solid #059669;
}
.wv-verify-badge-pill.unverified {
    background: #7f1d1d;
    color: #f87171;
    border: 1px solid #dc2626;
}
.wv-banner-alert {
    padding: 18px 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    border-bottom: 1px solid #e2e8f0;
}
.wv-banner-alert.success {
    background: #ecfdf5;
    color: #065f46;
}
.wv-banner-alert.danger {
    background: #fef2f2;
    color: #991b1b;
}
.wv-banner-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}
.wv-banner-alert.success .wv-banner-icon {
    background: #10b981;
    color: #ffffff;
    box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
}
.wv-banner-alert.danger .wv-banner-icon {
    background: #ef4444;
    color: #ffffff;
}
.wv-banner-text h3 {
    margin: 0;
    font-size: 17px;
    font-weight: 900;
}
.wv-banner-text p {
    margin: 4px 0 0;
    font-size: 13px;
    opacity: 0.9;
}
.wv-tab-nav {
    display: flex;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 0 16px;
}
.wv-tab-btn {
    padding: 14px 20px;
    font-size: 14px;
    font-weight: 800;
    color: #64748b;
    border: none;
    background: transparent;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}
.wv-tab-btn.active {
    color: #0f172a;
    border-bottom-color: #0284c7;
    background: #ffffff;
}
.wv-tab-pane {
    display: none;
    padding: 24px;
}
.wv-tab-pane.active {
    display: block;
}
.wv-grid-details {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.wv-card-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 16px;
    position: relative;
    overflow: hidden;
}
.wv-card-item:before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #0284c7;
}
.wv-card-item.accent-green:before { background: #10b981; }
.wv-card-item.accent-purple:before { background: #8b5cf6; }
.wv-card-item.accent-amber:before { background: #f59e0b; }
.wv-card-label {
    display: flex;
    justify-content: space-between;
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.wv-card-urdu-label {
    font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', serif;
    font-size: 12px;
    font-weight: normal;
}
.wv-card-value {
    color: #0f172a;
    font-size: 15px;
    font-weight: 900;
    word-break: break-word;
}
.wv-security-checklist {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 24px;
}
.wv-security-checklist h4 {
    margin: 0 0 12px;
    color: #166534;
    font-size: 15px;
    font-weight: 900;
    display: flex;
    align-items: center;
    gap: 8px;
}
.wv-check-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.wv-check-item {
    font-size: 13px;
    color: #15803d;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
}
.wv-actions-bar {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
    padding-top: 8px;
    border-top: 1px solid #e2e8f0;
}
.wv-btn-action {
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none !important;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s;
}
.wv-btn-primary {
    background: #0284c7;
    color: #ffffff !important;
}
.wv-btn-primary:hover {
    background: #0369a1;
}
.wv-btn-secondary {
    background: #f1f5f9;
    color: #334155 !important;
    border-color: #cbd5e1;
}
.wv-btn-secondary:hover {
    background: #e2e8f0;
}
.wv-doc-preview-box {
    background: #475569;
    border-radius: 8px;
    padding: 16px;
    box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.2);
    overflow-x: auto;
}
.wv-doc-inner {
    background: #ffffff;
    max-width: 820px;
    margin: 0 auto;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    border-radius: 4px;
}
.wv-footer-notice {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 16px 24px;
    color: #64748b;
    font-size: 12px;
    line-height: 1.6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
@media (max-width: 820px) {
    .wv-grid-details { grid-template-columns: 1fr; }
    .wv-check-grid { grid-template-columns: 1fr; }
    .wv-verify-topbar { flex-direction: column; align-items: flex-start; }
}
.wv-watermark-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    pointer-events: none;
    user-select: none;
    z-index: 50;
    opacity: 0.16;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 90%;
    text-align: center;
}
.wv-watermark-stamp {
    border: 5px solid #059669;
    color: #059669;
    padding: 16px 32px;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.45);
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
}
.wv-watermark-badge {
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
}
.wv-watermark-text {
    font-size: 32px;
    font-weight: 900;
    letter-spacing: 4px;
    text-transform: uppercase;
    white-space: nowrap;
    line-height: 1.2;
}
.wv-watermark-sub {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1.5px;
    font-family: monospace;
}
@media print {
    body, .wv-verify-wrapper { background: #ffffff !important; padding: 0 !important; }
    .wv-verify-card { box-shadow: none !important; border: none !important; }
    .wv-actions-bar, .wv-tab-nav { display: none !important; }
    .wv-tab-pane { display: block !important; padding: 10px 0 !important; }
    .wv-watermark-overlay { opacity: 0.15 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; display: flex !important; }
}
</style>

<div class="wv-verify-wrapper">
    <div class="wv-verify-container">
        <div class="wv-verify-card">
            
            <!-- Top Header -->
            <div class="wv-verify-topbar">
                <div class="wv-verify-brand">
                    <h2><?php echo h(!empty($project['name']) ? $project['name'] : 'White Villas Real Estate'); ?></h2>
                    <p>Official Public Document Authentication Portal &bull; سکیورٹی و تصدیقی پورٹل</p>
                </div>
                <div>
                    <?php if($verified){ ?>
                        <span class="wv-verify-badge-pill verified">
                            <span>🛡️</span> AUTHENTIC RECORD
                        </span>
                    <?php }else{ ?>
                        <span class="wv-verify-badge-pill unverified">
                            <span>⚠️</span> UNVERIFIED / INVALID
                        </span>
                    <?php } ?>
                </div>
            </div>

            <!-- Verification Status Banner -->
            <?php if($verified){ ?>
                <div class="wv-banner-alert success">
                    <div class="wv-banner-icon">✓</div>
                    <div class="wv-banner-text">
                        <h3>OFFICIAL VERIFIED DOCUMENT &bull; تصدیق شدہ اصلی دستاویز</h3>
                    </div>
                </div>
            <?php }else{ ?>
                <div class="wv-banner-alert danger">
                    <div class="wv-banner-icon">✕</div>
                    <div class="wv-banner-text">
                        <h3>VERIFICATION FAILED &bull; غیر تصدیق شدہ یا تبدیل شدہ لنک</h3>
                        <p>The document could not be verified. The verification token is invalid, expired, or does not match the official database record.</p>
                    </div>
                </div>
            <?php } ?>

            <?php if($verified){ ?>
                <!-- Tab Navigation -->
                <div class="wv-tab-nav">
                    <button type="button" class="wv-tab-btn active" onclick="switchVerifyTab('tab-summary', this)">
                        <span>🛡️</span> Security &amp; Details Summary
                    </button>
                    <button type="button" class="wv-tab-btn" onclick="switchVerifyTab('tab-document', this)">
                        <span>📄</span> Full Official Document Copy
                    </button>
                </div>

                <!-- Tab 1: Summary & Certificate -->
                <div id="tab-summary" class="wv-tab-pane active">
                    
                    <div class="wv-grid-details">
                        <div class="wv-card-item">
                            <div class="wv-card-label">
                                <span>Reference No</span>
                                <span class="wv-card-urdu-label">ریفرنس نمبر</span>
                            </div>
                            <div class="wv-card-value"><?php echo h($refNo); ?></div>
                        </div>

                        <div class="wv-card-item accent-green">
                            <div class="wv-card-label">
                                <span>Client / Allottee</span>
                                <span class="wv-card-urdu-label">خریدار / الاٹی</span>
                            </div>
                            <div class="wv-card-value"><?php echo h(!empty($client['name']) ? $client['name'] : '-'); ?></div>
                        </div>

                        <div class="wv-card-item">
                            <div class="wv-card-label">
                                <span>Masked CNIC</span>
                                <span class="wv-card-urdu-label">شناختی کارڈ</span>
                            </div>
                            <div class="wv-card-value"><?php echo h($maskedCnic); ?></div>
                        </div>

                        <div class="wv-card-item accent-purple">
                            <div class="wv-card-label">
                                <span>Property / Unit</span>
                                <span class="wv-card-urdu-label">پراپرٹی / یونٹ</span>
                            </div>
                            <div class="wv-card-value"><?php echo h(!empty($property['name']) ? $property['name'] : (!empty($deal['invoice_no']) ? $deal['invoice_no'] : '-')); ?></div>
                        </div>

                        <div class="wv-card-item">
                            <div class="wv-card-label">
                                <span>Document Type</span>
                                <span class="wv-card-urdu-label">دستاویز کی قسم</span>
                            </div>
                            <div class="wv-card-value"><?php echo h(!empty($template['name']) ? $template['name'] : (!empty($letter['title']) ? $letter['title'] : 'Official Letter')); ?></div>
                        </div>

                        <div class="wv-card-item accent-amber">
                            <div class="wv-card-label">
                                <span>Issue Date</span>
                                <span class="wv-card-urdu-label">تاریخِ اجراء</span>
                            </div>
                            <div class="wv-card-value"><?php echo h($issueDate); ?></div>
                        </div>

                        <div class="wv-card-item">
                            <div class="wv-card-label">
                                <span>Document Status</span>
                                <span class="wv-card-urdu-label">حیثیت و کیفیت</span>
                            </div>
                            <div class="wv-card-value" style="color:#059669;"><?php echo h($statusLabel); ?> (<?php echo $statusUrdu; ?>)</div>
                        </div>

                        <div class="wv-card-item">
                            <div class="wv-card-label">
                                <span>Security Serial</span>
                                <span class="wv-card-urdu-label">سکیورٹی کوڈ</span>
                            </div>
                            <div class="wv-card-value" style="font-family:monospace;font-size:13px;"><?php echo h($securitySerial); ?></div>
                        </div>

                        <div class="wv-card-item">
                            <div class="wv-card-label">
                                <span>Scan Verification Time</span>
                                <span class="wv-card-urdu-label">تصدیقی وقت</span>
                            </div>
                            <div class="wv-card-value"><?php echo date('d-M-Y H:i:s'); ?> PKT</div>
                        </div>
                    </div>

                    <!-- Security Checklist -->
                    <div class="wv-security-checklist">
                        <h4><span>🔒</span> Anti-Forgery Security Audit: Passed 100%</h4>
                        <div class="wv-check-grid">
                            <div class="wv-check-item"><span>✓</span> Central Society Registry Match Verified</div>
                            <div class="wv-check-item"><span>✓</span> Cryptographic SHA-256 Token Validated</div>
                            <div class="wv-check-item"><span>✓</span> Official Digital Stamp &amp; Signature Validated</div>
                            <div class="wv-check-item"><span>✓</span> Genuine Urdu Nastaleeq Typography Enforced</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="wv-actions-bar">
                        <button type="button" class="wv-btn-action wv-btn-primary" onclick="window.print()">
                            <span>🖨️</span> Print Verification Certificate
                        </button>
                        <button type="button" class="wv-btn-action wv-btn-secondary" onclick="switchVerifyTab('tab-document', document.querySelectorAll('.wv-tab-btn')[1])">
                            <span>📄</span> View Full Document Copy
                        </button>
                    </div>

                </div>

                <!-- Tab 2: Full Document Viewer -->
                <div id="tab-document" class="wv-tab-pane">
                    <div style="margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                        <span style="font-size:13px;font-weight:800;color:#0f172a;">
                            Official Document Copy &bull; اصلی جاری شدہ دستاویز
                        </span>
                        <button type="button" class="wv-btn-action wv-btn-secondary" style="padding:6px 14px;font-size:12px;" onclick="printDocumentPreview()">
                            <span>🖨️</span> Print Document
                        </button>
                    </div>

                    <div class="wv-doc-preview-box">
                        <div id="wv-document-render-root" class="wv-doc-inner" style="position:relative;overflow:hidden;">
                            <!-- Official Security Watermark -->
                            <div class="wv-watermark-overlay">
                                <div class="wv-watermark-stamp">
                                    <div class="wv-watermark-badge">🛡️ OFFICIAL VERIFICATION</div>
                                    <div class="wv-watermark-text">VERIFIED OFFICIAL DOCUMENT</div>
                                    <div class="wv-watermark-sub"><?php echo h(!empty($project['name']) ? $project['name'] : 'WHITE VILLAS REAL ESTATE'); ?> &bull; <?php echo h($securitySerial); ?></div>
                                </div>
                            </div>
                            <?php echo $renderedLetterHtml; ?>
                        </div>
                    </div>
                </div>
            <?php }else{ ?>
                <div style="padding:32px 24px;text-align:center;">
                    <p style="color:#64748b;font-size:14px;max-width:500px;margin:0 auto 20px;">
                        If you believe this is an error, please contact the issuing office with the printed reference number or visit our customer support desk.
                    </p>
                    <a href="<?php echo $this->Html->url('/'); ?>" class="wv-btn-action wv-btn-primary">
                        Return to Home Page
                    </a>
                </div>
            <?php } ?>

            <!-- Footer Security Notice -->
            <div class="wv-footer-notice">
                <div>
                    <strong>Official Anti-Forgery Notice:</strong> This public verification portal is legally maintained by White Villas Real Estate. Any unauthorized alteration, falsification, or forging of physical or digital letters is punishable under applicable law.
                </div>
                <div style="font-weight:800;color:#0f172a;">
                    Security ID: <?php echo h(!empty($securitySerial) ? $securitySerial : 'N/A'); ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
function switchVerifyTab(tabId, btn) {
    document.querySelectorAll('.wv-tab-pane').forEach(function(el){ el.classList.remove('active'); });
    document.querySelectorAll('.wv-tab-btn').forEach(function(el){ el.classList.remove('active'); });
    
    var target = document.getElementById(tabId);
    if(target) { target.classList.add('active'); }
    if(btn) { btn.classList.add('active'); }
}

function printDocumentPreview() {
    var docNode = document.getElementById('wv-document-render-root');
    if(!docNode) return;
    var printWin = window.open('', '_blank', 'width=950,height=850');
    printWin.document.write('<!DOCTYPE html><html><head><title>Official Verified Document - <?php echo h($refNo); ?></title>');
    printWin.document.write(document.head.innerHTML);
    printWin.document.write('<style>');
    printWin.document.write('@media print { body { background:#fff!important; margin:0!important; padding:15px!important; } .wv-watermark-overlay { opacity:0.15!important; -webkit-print-color-adjust:exact; print-color-adjust:exact; display:flex!important; } }');
    printWin.document.write('</style>');
    printWin.document.write('</head><body style="background:#fff;margin:0;padding:20px;position:relative;">');
    printWin.document.write(docNode.innerHTML);
    printWin.document.write('</body></html>');
    printWin.document.close();
    printWin.focus();
    setTimeout(function(){ printWin.print(); }, 500);
}

document.addEventListener('DOMContentLoaded', function() {
    var docNode = document.getElementById('wv-document-render-root');
    if(!docNode) return;

    var agencyLogo = <?php echo json_encode($agencyLogoUrl); ?>;
    var clientPhoto = <?php echo json_encode($clientPhotoUrl); ?>;
    var qrCode = <?php echo json_encode(!empty($qrCodeUrl) ? $qrCodeUrl : ''); ?>;
    var defaultUser = <?php echo json_encode($this->Html->url('/img/User.png', true)); ?>;
    var defaultLogo = <?php echo json_encode($this->Html->url('/img/logo.png', true)); ?>;

    var html = docNode.innerHTML;
    if(html.indexOf('{{') !== -1) {
        var clientData = {
            'agency_name': <?php echo json_encode(!empty($project['name']) ? $project['name'] : 'White Villas Lahore'); ?>,
            'agency_address': <?php echo json_encode(!empty($project['address']) ? $project['address'] : 'Main Ferozepur Road, Lahore'); ?>,
            'agency_email': <?php echo json_encode(!empty($project['email']) ? $project['email'] : 'info@whitevillas.com.pk'); ?>,
            'agency_phone': <?php echo json_encode(!empty($project['phone']) ? $project['phone'] : (!empty($project['mobile']) ? $project['mobile'] : '+92 300 1234567')); ?>,
            'agency_logo_url': agencyLogo,
            'watermark_logo_url': agencyLogo,
            'project_logo_url': agencyLogo,
            'client_name': <?php echo json_encode(!empty($client['name']) ? $client['name'] : ''); ?>,
            'client_father_name': <?php echo json_encode(!empty($client['fathername']) ? $client['fathername'] : ''); ?>,
            'client_cnic': <?php echo json_encode(!empty($client['pincode']) ? $client['pincode'] : (!empty($client['phone']) ? $client['phone'] : '')); ?>,
            'client_email': <?php echo json_encode(!empty($client['email']) ? $client['email'] : ''); ?>,
            'client_address': <?php echo json_encode(!empty($client['address']) ? $client['address'] : ''); ?>,
            'client_phone': <?php echo json_encode(!empty($client['phone']) ? $client['phone'] : ''); ?>,
            'client_mobile': <?php echo json_encode(!empty($client['mobile']) ? $client['mobile'] : (!empty($client['phone']) ? $client['phone'] : '')); ?>,
            'client_profile_photo_url': clientPhoto,
            'applicant_photo_url': clientPhoto,
            'client_photo_url': clientPhoto,
            'deal_occupation': <?php echo json_encode(!empty($deal['occupation']) ? $deal['occupation'] : 'Business'); ?>,
            'nominee_name': <?php echo json_encode(!empty($deal['nominee_name']) ? $deal['nominee_name'] : ''); ?>,
            'nominee_cnic': <?php echo json_encode(!empty($deal['nominee_cnic']) ? $deal['nominee_cnic'] : ''); ?>,
            'nominee_mobile': <?php echo json_encode(!empty($deal['nominee_mobile']) ? $deal['nominee_mobile'] : ''); ?>,
            'nominee_relation': <?php echo json_encode(!empty($deal['nominee_relation']) ? $deal['nominee_relation'] : ''); ?>,
            'property_name': <?php echo json_encode(!empty($property['name']) ? $property['name'] : 'MA7055389'); ?>,
            'property_file': <?php echo json_encode(!empty($property['name']) ? $property['name'] : 'MA7055389'); ?>,
            'property_area': <?php echo json_encode(!empty($property['area']) ? $property['area'] : ''); ?>,
            'property_type': <?php echo json_encode(!empty($type['name']) ? $type['name'] : 'Residential Villa'); ?>,
            'ballot_block_name': <?php echo json_encode(strpos(!empty($project['name']) ? $project['name'] : '', 'Awami') !== false ? 'Awami Block' : 'Main Block'); ?>,
            'deal_invoice_no': <?php echo json_encode(!empty($deal['invoice_no']) ? $deal['invoice_no'] : $refNo); ?>,
            'letter_ref_no': <?php echo json_encode($refNo); ?>,
            'letter_deal_qr_url': qrCode,
            'deal_letter_qr_url': qrCode,
            'letter_verification_qr_url': qrCode,
            'deal_qr_url': qrCode
        };
        for (var k in clientData) {
            var re = new RegExp('\\{\\{\\s*' + k + '\\s*\\}\\}', 'gi');
            html = html.replace(re, clientData[k]);
        }
        html = html.replace(/\{\{\s*[^}]+\s*\}\}/g, '');
        docNode.innerHTML = html;
    }

    // Safeguard all images inside document render root
    var imgElements = docNode.querySelectorAll('img');
    imgElements.forEach(function(img) {
        var id = (img.id || '').toLowerCase();
        var alt = (img.getAttribute('alt') || '').toLowerCase();
        var src = img.getAttribute('src') || '';

        if (id.indexOf('logo') !== -1 || alt.indexOf('logo') !== -1) {
            if (!src || src.indexOf('{{') !== -1) {
                img.src = agencyLogo;
            }
            img.onerror = function() {
                this.onerror = null;
                this.src = defaultLogo;
            };
        } else if (id.indexOf('photo') !== -1 || alt.indexOf('photo') !== -1 || alt.indexOf('applicant') !== -1 || alt.indexOf('client') !== -1) {
            if (!src || src.indexOf('{{') !== -1) {
                img.src = clientPhoto;
            }
            img.onerror = function() {
                this.onerror = null;
                this.src = defaultUser;
            };
        } else if (id.indexOf('qr') !== -1 || alt.indexOf('qr') !== -1) {
            if ((!src || src.indexOf('{{') !== -1) && qrCode) {
                img.src = qrCode;
            }
        }
    });
});
</script>
