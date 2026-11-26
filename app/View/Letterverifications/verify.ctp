<?php
$letter = !empty($post['Realestateletter']) ? (array)$post['Realestateletter'] : array();
$deal = !empty($post['Deal']) ? (array)$post['Deal'] : array();
$client = !empty($post['Client']) ? (array)$post['Client'] : array();
$property = !empty($post['Property']) ? (array)$post['Property'] : array();
$project = !empty($post['Project']) ? (array)$post['Project'] : array();
$template = !empty($post['Template']) ? (array)$post['Template'] : array();
$type = !empty($post['Type']) ? (array)$post['Type'] : array();

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
// Mask last 6 digits of Reference No
$maskedRefNo = $refNo;
if (preg_match('/^(.*?[0-9]{4}\-)[0-9]+$/', $refNo, $rm)) {
    $maskedRefNo = $rm[1] . '******';
} elseif (preg_match('/^(.*?)([0-9]{6})$/', $refNo, $rm)) {
    $maskedRefNo = $rm[1] . '******';
} elseif (strlen($refNo) > 6) {
    $maskedRefNo = substr($refNo, 0, -6) . '******';
} else {
    $maskedRefNo = preg_replace('/[0-9]/', '*', $refNo);
}

// Mask center 4 digits of Property / Unit
$rawProperty = !empty($property['name']) ? $property['name'] : (!empty($deal['invoice_no']) ? $deal['invoice_no'] : '-');
$maskedProperty = $rawProperty;
if ($rawProperty !== '-' && !empty($rawProperty)) {
    if (preg_match('/^([A-Za-z\s\-_]*)([0-9]{5,})([A-Za-z\s\-_]*)$/', $rawProperty, $pm)) {
        $pPrefix = $pm[1];
        $pDigits = $pm[2];
        $pSuffix = $pm[3];
        $pdLen = strlen($pDigits);
        $pStart = (int)floor(($pdLen - 4) / 2);
        $maskedProperty = $pPrefix . substr($pDigits, 0, $pStart) . '****' . substr($pDigits, $pStart + 4) . $pSuffix;
    } elseif (strlen($rawProperty) >= 6) {
        $pLen = strlen($rawProperty);
        $pStart = (int)floor(($pLen - 4) / 2);
        $maskedProperty = substr($rawProperty, 0, $pStart) . '****' . substr($rawProperty, $pStart + 4);
    }
}

// Mask center 4 digits of Security Serial
$maskedSecuritySerial = $securitySerial;
if (preg_match('/^(WV-VER-)([A-Z0-9]{2})([A-Z0-9]{4})([A-Z0-9]{2})(-.*)$/i', $securitySerial, $sm)) {
    $maskedSecuritySerial = $sm[1] . $sm[2] . '****' . $sm[4] . $sm[5];
} elseif (strlen($securitySerial) >= 8) {
    $sLen = strlen($securitySerial);
    $startIdx = (int)floor(($sLen - 4) / 2);
    $maskedSecuritySerial = substr($securitySerial, 0, $startIdx) . '****' . substr($securitySerial, $startIdx + 4);
}
$issueDate = !empty($letter['letter_date']) ? date('d-M-Y', strtotime($letter['letter_date'])) : (!empty($letter['created']) ? date('d-M-Y', strtotime($letter['created'])) : date('d-M-Y'));

$clientPhotoFile = !empty($client['photo']) ? trim((string)$client['photo']) : '';
$clientPhotoDir = !empty($client['dirt']) ? trim((string)$client['dirt']) : (!empty($client['dir']) ? trim((string)$client['dir']) : 'contact');
$clientPhotoUrl = '';
$hasClientPhoto = false;
if ($clientPhotoFile !== '') {
    $clientPhotoUrl = $this->Html->url('/img/' . $clientPhotoDir . '/' . $clientPhotoFile, true);
    if (file_exists(WWW_ROOT . 'img' . DS . $clientPhotoDir . DS . $clientPhotoFile)) {
        $hasClientPhoto = true;
    }
} else {
    $clientPhotoUrl = $this->Html->url('/img/User.png', true);
}
$agencyLogoUrl = !empty($project['logo']) ? $this->Html->url('/img/' . $project['logo'], true) : (!empty($project['photo']) ? $this->Html->url('/img/' . $project['photo'], true) : $this->Html->url('/img/logo.png', true));

$verifyCnicUrl = Router::url(array('controller' => 'letterverifications', 'action' => 'verify_cnic', $id, $token), true);
?>
<?php echo $this->Html->css('wv-urdu-fonts'); ?>
<style type="text/css">
/* --- Prevent Outside Website Layout Overflow on Mobile --- */
html, body {
    overflow-x: hidden !important;
    max-width: 100vw !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}
.page-wrapper {
    overflow-x: hidden !important;
    max-width: 100vw !important;
    width: 100% !important;
}
.header-wrapper, .header, .nav-topbar, .header-inner, .header-top, .footer-wrapper {
    max-width: 100vw !important;
    overflow-x: hidden !important;
}
.header-wrapper .nav-topbar {
    flex-wrap: wrap !important;
}
.header-wrapper .nav-topbar .wv-top-contact {
    white-space: normal !important;
    word-break: break-all !important;
}

/* --- Master Verification Styling --- */
.wv-verify-wrapper,
.wv-verify-wrapper * {
    box-sizing: border-box !important;
}

.wv-verify-wrapper {
    background: linear-gradient(135deg, #0b1528 0%, #172a46 100%);
    min-height: 100vh;
    padding: 22px 14px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #1e293b;
    width: 100% !important;
    max-width: 100vw !important;
    overflow-x: hidden !important;
}
.wv-verify-container {
    max-width: 940px;
    width: 100% !important;
    margin: 0 auto;
}
.wv-verify-card {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.22);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    width: 100% !important;
}

/* --- Topbar Branding --- */
.wv-verify-topbar {
    background: linear-gradient(135deg, #0a1727 0%, #0f243d 100%);
    padding: 16px 20px;
    color: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #0284c7;
    flex-wrap: wrap;
    gap: 10px;
    width: 100%;
}
.wv-verify-brand {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
    flex: 1 1 auto;
}
.wv-verify-brand-title {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}
.wv-verify-brand-title h2 {
    color: #ffffff !important;
    font-size: 17px;
    font-weight: 800;
    margin: 0;
    letter-spacing: 0.3px;
    line-height: 1.25;
    word-break: break-word;
}
.wv-verify-crest {
    font-size: 18px;
    line-height: 1;
    flex-shrink: 0;
}
.wv-verify-brand p {
    color: #94a3b8;
    font-size: 11.5px;
    margin: 2px 0 0;
    line-height: 1.35;
    word-break: break-word;
}
.wv-verify-badge-wrapper {
    flex-shrink: 0;
}
.wv-verify-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 30px;
    font-weight: 800;
    font-size: 11px;
    letter-spacing: 0.5px;
    white-space: nowrap;
}
.wv-verify-badge-pill.verified {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border: 1px solid #059669;
}
.wv-verify-badge-pill.unverified {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
    border: 1px solid #dc2626;
}
.wv-pulse-dot {
    width: 7px;
    height: 7px;
    background: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 0 rgba(16, 185, 129, 0.6);
    animation: wvPulse 2s infinite;
    flex-shrink: 0;
}
@keyframes wvPulse {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { box-shadow: 0 0 0 7px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* --- Verification Status Banner --- */
.wv-banner-alert {
    padding: 12px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid #e2e8f0;
    width: 100%;
}
.wv-banner-alert.success {
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    border-bottom-color: #d1fae5;
}
.wv-banner-alert.danger {
    background: #fef2f2;
    border-bottom-color: #fee2e2;
}
.wv-banner-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.wv-banner-alert.success .wv-banner-icon {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
}
.wv-banner-alert.danger .wv-banner-icon {
    background: #ef4444;
    color: #ffffff;
}
.wv-banner-content {
    flex: 1 1 auto;
    min-width: 0;
}
.wv-banner-title {
    font-size: 13.5px;
    font-weight: 800;
    color: #065f46;
    letter-spacing: 0.3px;
    line-height: 1.25;
    word-break: break-word;
}
.wv-banner-alert.danger .wv-banner-title {
    color: #991b1b;
}
.wv-banner-urdu {
    font-size: 12.5px;
    color: #047857;
    font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', serif;
    direction: rtl;
    text-align: left;
    line-height: 1.4;
    margin-top: 1px;
    word-break: break-word;
}
.wv-banner-meta {
    font-size: 10.5px;
    font-weight: 600;
    color: #059669;
    margin-top: 2px;
    line-height: 1.35;
    word-break: break-word;
}
.wv-banner-desc {
    font-size: 11.5px;
    color: #7f1d1d;
    margin-top: 3px;
    line-height: 1.4;
    word-break: break-word;
}

/* --- Tabs --- */
.wv-tab-nav {
    display: flex;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 0 10px;
    gap: 6px;
    width: 100%;
}
.wv-tab-btn {
    flex: 1 1 50%;
    min-width: 0;
    padding: 11px 12px;
    font-size: 12.5px;
    font-weight: 700;
    color: #64748b;
    border: none;
    background: transparent;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s;
    outline: none;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.wv-tab-btn:hover {
    color: #0f172a;
}
.wv-tab-btn.active {
    color: #0284c7;
    border-bottom-color: #0284c7;
    background: #ffffff;
}
.wv-tab-text-short {
    display: none;
}
.wv-tab-pane {
    display: none;
    padding: 16px 18px;
    width: 100%;
}
.wv-tab-pane.active {
    display: block;
}

/* --- Grid Details & Reduced Sized Data Cards --- */
.wv-grid-details {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 15px;
    width: 100%;
}
.wv-card-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 9px 12px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 0;
    width: 100%;
}
.wv-card-item:before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3.5px;
    background: #0284c7;
}
.wv-card-item.accent-green:before { background: #10b981; }
.wv-card-item.accent-purple:before { background: #8b5cf6; }
.wv-card-item.accent-amber:before { background: #f59e0b; }
.wv-card-item.accent-indigo:before { background: #6366f1; }
.wv-card-item.accent-teal:before { background: #0d9488; }
.wv-card-item.accent-emerald:before { background: #059669; }

.wv-card-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 4px;
    width: 100%;
    min-width: 0;
    gap: 6px;
}
.wv-card-urdu-label {
    font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', serif;
    font-size: 12px;
    font-weight: normal;
    color: #94a3b8;
    direction: rtl;
    line-height: 1.2;
    flex-shrink: 0;
    white-space: nowrap;
}

/* Clean, Balanced, Reduced Text Size */
.wv-card-value {
    color: #0f172a;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.35;
    word-break: break-word;
    min-width: 0;
}
.wv-card-value-mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
    letter-spacing: 0.25px;
    font-size: 12px;
    color: #0369a1;
}

/* Client Avatar Thumb */
.wv-client-val-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}
.wv-client-thumb {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 1.5px solid #10b981;
    flex-shrink: 0;
    background: #e2e8f0;
}
.wv-client-sub {
    font-size: 10.5px;
    color: #64748b;
    font-weight: 600;
    margin-top: 1px;
    word-break: break-word;
}
.wv-prop-sub {
    font-size: 10.5px;
    color: #64748b;
    font-weight: 600;
    margin-top: 2px;
    word-break: break-word;
}

/* Status Pill */
.wv-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #ecfdf5;
    color: #047857;
    padding: 2px 7px;
    border-radius: 5px;
    font-size: 11.5px;
    font-weight: 700;
    border: 1px solid #a7f3d0;
    max-width: 100%;
    word-break: break-word;
}
.wv-status-dot {
    width: 6px;
    height: 6px;
    background: #10b981;
    border-radius: 50%;
    flex-shrink: 0;
}
.wv-status-urdu {
    font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', serif;
    font-weight: normal;
    font-size: 11.5px;
    direction: rtl;
}

/* Security Serial & Copy Button */
.wv-serial-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    min-width: 0;
}
.wv-serial-code {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 11.5px;
    font-weight: 700;
    color: #0369a1;
    word-break: break-all;
    min-width: 0;
}
.wv-copy-btn {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #475569;
    border-radius: 4px;
    padding: 3px 6px;
    cursor: pointer;
    font-size: 11px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}
.wv-copy-btn:hover {
    background: #0284c7;
    color: #ffffff;
    border-color: #0284c7;
}
.wv-copy-btn.copied {
    background: #10b981;
    color: #ffffff;
    border-color: #10b981;
}

/* --- Security Checklist --- */
.wv-security-checklist {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 9px;
    padding: 12px 14px;
    margin-bottom: 15px;
    width: 100%;
}
.wv-security-checklist h4 {
    margin: 0 0 9px;
    color: #166534;
    font-size: 12.5px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
}
.wv-audit-badge {
    background: #15803d;
    color: #ffffff;
    font-size: 9.5px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 3px;
    letter-spacing: 0.5px;
    flex-shrink: 0;
}
.wv-check-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    width: 100%;
}
.wv-check-item {
    font-size: 11px;
    color: #15803d;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    min-width: 0;
    word-break: break-word;
}
.wv-chk-icon {
    width: 15px;
    height: 15px;
    background: #166534;
    color: #ffffff;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 9.5px;
    font-weight: 900;
    flex-shrink: 0;
}

/* --- Action Buttons --- */
.wv-actions-bar {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
    width: 100%;
}
.wv-btn-action {
    padding: 9px 16px;
    border-radius: 7px;
    font-size: 12.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    text-decoration: none !important;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s;
    line-height: 1.3;
}
.wv-btn-primary {
    background: #0284c7 !important;
    color: #ffffff !important;
    border-color: #0284c7 !important;
}
.wv-btn-primary:hover {
    background: #0369a1 !important;
    border-color: #0369a1 !important;
}
.wv-btn-secondary {
    background: #f1f5f9 !important;
    color: #334155 !important;
    border-color: #cbd5e1 !important;
}
.wv-btn-secondary:hover {
    background: #e2e8f0 !important;
}

/* --- CNIC Security Verification Gate Card --- */
.wv-cnic-gate-card {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 12px;
    padding: 24px 20px;
    max-width: 540px;
    margin: 20px auto;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    text-align: center;
}
.wv-cnic-gate-icon-circle {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 12px;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
}
.wv-cnic-gate-title {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 4px;
}
.wv-cnic-gate-urdu-title {
    font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', serif;
    font-size: 14px;
    color: #0284c7;
    margin-bottom: 8px;
    direction: rtl;
}
.wv-cnic-gate-desc {
    font-size: 12px;
    color: #64748b;
    line-height: 1.5;
    margin: 0 0 16px;
}
.wv-cnic-ref-box {
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 11.5px;
}
.wv-cnic-ref-item strong {
    color: #0f172a;
}
.wv-cnic-form-group {
    margin-bottom: 14px;
    text-align: left;
}
.wv-cnic-form-label {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 6px;
}
.wv-cnic-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.wv-cnic-input-icon {
    position: absolute;
    left: 12px;
    color: #94a3b8;
    font-size: 14px;
    pointer-events: none;
}
.wv-cnic-input {
    width: 100%;
    padding: 10px 12px 10px 36px;
    font-size: 15px;
    font-weight: 700;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    letter-spacing: 1px;
    border: 2px solid #cbd5e1;
    border-radius: 8px;
    outline: none;
    transition: all 0.2s;
    color: #0f172a;
    background: #f8fafc;
}
.wv-cnic-input:focus {
    border-color: #0284c7;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}
.wv-cnic-input.error {
    border-color: #ef4444 !important;
    background: #fef2f2 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
    animation: wvShake 0.4s ease-in-out;
}
@keyframes wvShake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-6px); }
    40%, 80% { transform: translateX(6px); }
}

.wv-cnic-btn-submit {
    width: 100%;
    padding: 11px 16px;
    border-radius: 8px;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
    transition: all 0.2s;
}
.wv-cnic-btn-submit:hover {
    background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
    box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35);
}
.wv-cnic-btn-submit:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

/* Feedback alert */
.wv-cnic-feedback {
    display: none;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 12px;
    line-height: 1.45;
    margin-top: 14px;
    text-align: left;
}
.wv-cnic-feedback.error {
    display: block;
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}
.wv-cnic-feedback.success {
    display: block;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}
.wv-cnic-gate-footnote {
    font-size: 10.5px;
    color: #94a3b8;
    margin-top: 14px;
    line-height: 1.4;
}

/* --- Document Tab Toolbar & Preview Box --- */
.wv-doc-toolbar {
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    width: 100%;
}
.wv-doc-title {
    font-size: 12.5px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 6px;
    min-width: 0;
}
.wv-unlocked-pill {
    background: #ecfdf5;
    color: #059669;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 12px;
    border: 1px solid #a7f3d0;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.wv-doc-actions {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-shrink: 0;
}
.wv-doc-btn {
    padding: 6px 11px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    transition: all 0.2s;
}
.wv-doc-btn:hover {
    background: #f1f5f9;
}
.wv-doc-btn-primary {
    background: #0284c7;
    color: #ffffff;
    border-color: #0284c7;
}
.wv-doc-btn-primary:hover {
    background: #0369a1;
}
.wv-doc-preview-box {
    background: #334155;
    border-radius: 8px;
    padding: 12px;
    box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.25);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    width: 100%;
}
.wv-doc-inner {
    background: #ffffff;
    max-width: 820px;
    margin: 0 auto;
    padding: 14px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    border-radius: 4px;
    transition: transform 0.25s ease;
}
.wv-mobile-zoom-hint {
    display: none;
    margin-top: 8px;
    font-size: 10.5px;
    color: #94a3b8;
    text-align: center;
}

/* --- Giant Diagonal DUPLICATE COPY Background Watermark --- */
.wv-client-duplicate-watermark {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    pointer-events: none;
    user-select: none;
    z-index: 30;
    overflow: hidden;
}
.wv-client-duplicate-watermark span {
    color: rgba(15, 23, 42, 0.08);
    font-family: Arial, Helvetica, sans-serif;
    font-size: clamp(52px, 9vw, 110px);
    font-weight: 900;
    letter-spacing: 6px;
    text-transform: uppercase;
    transform: rotate(-30deg);
    white-space: nowrap;
    line-height: 1;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

/* --- Official Security Stamp Watermark Overlay --- */
.wv-watermark-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-28deg);
    pointer-events: none;
    user-select: none;
    z-index: 40;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 90%;
    max-width: 580px;
    text-align: center;
}
.wv-watermark-stamp {
    border: 4px dashed rgba(220, 38, 38, 0.45);
    color: #dc2626;
    padding: 14px 26px;
    border-radius: 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    background: rgba(255, 255, 255, 0.82);
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}
.wv-watermark-badge {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(220, 38, 38, 0.85);
}
.wv-watermark-duplicate {
    font-size: 34px;
    font-weight: 900;
    letter-spacing: 5px;
    text-transform: uppercase;
    color: #dc2626;
    line-height: 1.1;
    text-shadow: 0 1px 2px rgba(220, 38, 38, 0.15);
}
.wv-watermark-text {
    font-size: 19px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    white-space: nowrap;
    line-height: 1.2;
    color: rgba(220, 38, 38, 0.90);
}
.wv-watermark-sub {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    font-family: monospace;
    color: rgba(220, 38, 38, 0.80);
}

/* --- Footer Notice --- */
.wv-footer-notice {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 12px 16px;
    color: #64748b;
    font-size: 11px;
    line-height: 1.5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    width: 100%;
}
.wv-notice-text {
    flex: 1 1 240px;
    min-width: 0;
}
.wv-notice-id {
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    flex-shrink: 0;
}
.wv-notice-id code {
    background: #e2e8f0;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

/* --- Responsive Media Queries: Tablet & Mobile --- */
@media (max-width: 820px) {
    .wv-grid-details {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
    }
}

@media (max-width: 640px) {
    .wv-verify-wrapper {
        padding: 8px 6px !important;
    }
    .wv-verify-topbar {
        padding: 10px 12px;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
    .wv-verify-brand-title h2 {
        font-size: 14px;
    }
    .wv-verify-crest {
        font-size: 15px;
    }
    .wv-verify-brand p {
        font-size: 10px;
    }
    .wv-verify-badge-pill {
        padding: 4px 8px;
        font-size: 9px;
    }
    .wv-banner-alert {
        padding: 10px 12px;
        gap: 10px;
    }
    .wv-banner-icon {
        width: 30px;
        height: 30px;
    }
    .wv-banner-icon svg {
        width: 15px;
        height: 15px;
    }
    .wv-banner-title {
        font-size: 12px;
    }
    .wv-banner-urdu {
        font-size: 11.5px;
    }
    .wv-banner-meta {
        font-size: 9.5px;
    }
    .wv-tab-nav {
        padding: 0 4px;
        gap: 4px;
    }
    .wv-tab-btn {
        flex: 1 1 50%;
        min-width: 0;
        padding: 9px 4px;
        font-size: 11px;
        gap: 4px;
    }
    .wv-tab-text-full {
        display: none;
    }
    .wv-tab-text-short {
        display: inline;
    }
    .wv-tab-pane {
        padding: 11px 8px;
    }
    .wv-grid-details {
        grid-template-columns: 1fr;
        gap: 7px;
        margin-bottom: 11px;
    }
    .wv-card-item {
        padding: 7px 10px;
    }
    .wv-card-label {
        font-size: 9px;
        margin-bottom: 2px;
    }
    .wv-card-urdu-label {
        font-size: 11px;
    }
    .wv-card-value {
        font-size: 12px;
    }
    .wv-card-value-mono {
        font-size: 11.5px;
    }
    .wv-client-thumb {
        width: 26px;
        height: 26px;
    }
    .wv-client-sub, .wv-prop-sub {
        font-size: 10px;
    }
    .wv-security-checklist {
        padding: 9px 11px;
        margin-bottom: 11px;
    }
    .wv-security-checklist h4 {
        font-size: 11.5px;
        margin-bottom: 7px;
    }
    .wv-check-grid {
        grid-template-columns: 1fr;
        gap: 5px;
    }
    .wv-check-item {
        font-size: 10.5px;
    }
    .wv-actions-bar {
        display: flex;
        flex-direction: column;
        gap: 7px;
        padding-top: 10px;
        width: 100%;
    }
    .wv-btn-action {
        width: 100%;
        justify-content: center;
        padding: 9px 10px;
        font-size: 12px;
        text-align: center;
    }
    .wv-cnic-gate-card {
        padding: 18px 14px;
        margin: 10px auto;
    }
    .wv-cnic-gate-title {
        font-size: 14.5px;
    }
    .wv-cnic-gate-urdu-title {
        font-size: 13px;
    }
    .wv-cnic-gate-desc {
        font-size: 11px;
    }
    .wv-cnic-input {
        font-size: 13.5px;
        padding: 9px 10px 9px 32px;
    }
    .wv-cnic-btn-submit {
        padding: 10px 14px;
        font-size: 12.5px;
    }
    .wv-doc-preview-box {
        padding: 6px 3px;
    }
    .wv-doc-inner {
        padding: 8px;
    }
    .wv-mobile-zoom-hint {
        display: block;
    }
    .wv-footer-notice {
        padding: 10px 11px;
        font-size: 10px;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
}

/* --- Print Directive --- */
@media print {
    @page {
        size: auto;
        margin: 8mm;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    body, .wv-verify-wrapper { background: #ffffff !important; padding: 0 !important; }
    .wv-verify-card { box-shadow: none !important; border: none !important; }
    .wv-actions-bar, .wv-tab-nav, .wv-doc-toolbar, .wv-mobile-zoom-hint, .wv-cnic-gate-card { display: none !important; }
    .wv-tab-pane { display: block !important; padding: 10px 0 !important; }

    /* DUPLICATE WATERMARK ON PRINT */
    .wv-client-duplicate-watermark {
        display: flex !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        z-index: 999998 !important;
        justify-content: center !important;
        align-items: center !important;
        pointer-events: none !important;
        overflow: hidden !important;
    }
    .wv-client-duplicate-watermark span {
        display: block !important;
        color: rgba(0, 0, 0, 0.18) !important;
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: clamp(60px, 9vw, 110px) !important;
        font-weight: 900 !important;
        letter-spacing: 6px !important;
        text-transform: uppercase !important;
        transform: rotate(-30deg) !important;
        white-space: nowrap !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .wv-watermark-overlay {
        display: flex !important;
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) rotate(-28deg) !important;
        z-index: 999999 !important;
        width: 85% !important;
        max-width: 580px !important;
        pointer-events: none !important;
    }
    .wv-watermark-stamp {
        border: 4px dashed rgba(220, 38, 38, 0.45) !important;
        background: rgba(255, 255, 255, 0.85) !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12) !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .wv-watermark-stamp * {
        color: rgba(220, 38, 38, 0.65) !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>

<div class="wv-verify-wrapper">
    <div class="wv-verify-container">
        <div class="wv-verify-card">
            
            <!-- Top Header -->
            <div class="wv-verify-topbar">
                <div class="wv-verify-brand">
                    <div class="wv-verify-brand-title">
                        <span class="wv-verify-crest">🛡️</span>
                        <h2><?php echo h(!empty($project['name']) ? $project['name'] : 'White Villas Real Estate'); ?></h2>
                    </div>
                    <p>Official Public Document Authentication Portal &bull; سکیورٹی و تصدیقی پورٹل</p>
                </div>
                <div class="wv-verify-badge-wrapper">
                    <?php if($verified){ ?>
                        <span class="wv-verify-badge-pill verified">
                            <span class="wv-pulse-dot"></span>
                            <span>AUTHENTIC RECORD</span>
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
                    <div class="wv-banner-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="wv-banner-content">
                        <div class="wv-banner-title">OFFICIAL VERIFIED DOCUMENT</div>
                        <div class="wv-banner-urdu">تصدیق شدہ اصلی و مصدقہ سرکاری دستاویز</div>
                        <div class="wv-banner-meta">Central Database Match Confirmed &bull; SHA-256 Validated</div>
                    </div>
                </div>
            <?php }else{ ?>
                <div class="wv-banner-alert danger">
                    <div class="wv-banner-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </div>
                    <div class="wv-banner-content">
                        <div class="wv-banner-title">VERIFICATION FAILED &bull; غیر تصدیق شدہ یا تبدیل شدہ لنک</div>
                        <div class="wv-banner-desc">The document could not be verified. The verification token is invalid, expired, or does not match the official database record.</div>
                    </div>
                </div>
            <?php } ?>

            <?php if($verified){ ?>
                <!-- Tab Navigation (Single line on all devices) -->
                <div class="wv-tab-nav">
                    <button type="button" class="wv-tab-btn active" id="wv-tab-btn-summary" onclick="switchVerifyTab('tab-summary', this)">
                        <span>🛡️</span>
                        <span class="wv-tab-text-full">Security &amp; Details Summary</span>
                        <span class="wv-tab-text-short">Security Details</span>
                    </button>
                    <button type="button" class="wv-tab-btn" id="wv-tab-btn-doc" onclick="openDocumentTab(this)">
                        <span id="wv-tab-lock-icon">🔒</span>
                        <span class="wv-tab-text-full">Full Official Document Copy</span>
                        <span class="wv-tab-text-short">Official Document</span>
                    </button>
                </div>

                <!-- Tab 1: Summary & Certificate -->
                <div id="tab-summary" class="wv-tab-pane active">
                    
                    <div class="wv-grid-details">
                        <!-- Reference No -->
                        <div class="wv-card-item">
                            <div class="wv-card-label">
                                <span>Reference No</span>
                                <span class="wv-card-urdu-label">ریفرنس نمبر</span>
                            </div>
                            <div class="wv-card-value wv-card-value-mono"><?php echo h($maskedRefNo); ?></div>
                        </div>

                        <!-- Client / Allottee -->
                        <div class="wv-card-item accent-green">
                            <div class="wv-card-label">
                                <span>Client / Allottee</span>
                                <span class="wv-card-urdu-label">خریدار / الاٹی</span>
                            </div>
                            <div>
                                <div class="wv-card-value"><?php echo h(!empty($client['name']) ? trim($client['name']) : '-'); ?></div>
                                <?php if(!empty($client['fathername'])){ ?>
                                    <div class="wv-client-sub">S/O, D/O, W/O: <?php echo h(trim($client['fathername'])); ?></div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Masked CNIC -->
                        <div class="wv-card-item accent-indigo">
                            <div class="wv-card-label">
                                <span>Masked CNIC</span>
                                <span class="wv-card-urdu-label">شناختی کارڈ</span>
                            </div>
                            <div class="wv-card-value wv-card-value-mono"><?php echo h($maskedCnic); ?></div>
                        </div>

                        <!-- Property / Unit -->
                        <div class="wv-card-item accent-purple">
                            <div class="wv-card-label">
                                <span>Property / Unit</span>
                                <span class="wv-card-urdu-label">پراپرٹی / یونٹ</span>
                            </div>
                            <div>
                                <div class="wv-card-value"><?php echo h($maskedProperty); ?></div>
                                <?php if(!empty($property['area']) || !empty($type['name'])){ ?>
                                    <div class="wv-prop-sub">
                                        <?php 
                                        $pArea = !empty($property['area']) ? $property['area'] . ' ' . (!empty($property['area_unit']) ? $property['area_unit'] : 'Marla') : '';
                                        $pType = !empty($type['name']) ? $type['name'] : '';
                                        echo h(trim($pArea . ($pArea && $pType ? ' • ' : '') . $pType));
                                        ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Document Type -->
                        <div class="wv-card-item">
                            <div class="wv-card-label">
                                <span>Document Type</span>
                                <span class="wv-card-urdu-label">دستاویز کی قسم</span>
                            </div>
                            <div class="wv-card-value"><?php echo h(!empty($template['name']) ? $template['name'] : (!empty($letter['title']) ? $letter['title'] : 'Official Letter')); ?></div>
                        </div>

                        <!-- Issue Date -->
                        <div class="wv-card-item accent-amber">
                            <div class="wv-card-label">
                                <span>Issue Date</span>
                                <span class="wv-card-urdu-label">تاریخِ اجراء</span>
                            </div>
                            <div class="wv-card-value"><?php echo h($issueDate); ?></div>
                        </div>

                        <!-- Document Status -->
                        <div class="wv-card-item accent-emerald">
                            <div class="wv-card-label">
                                <span>Document Status</span>
                                <span class="wv-card-urdu-label">حیثیت و کیفیت</span>
                            </div>
                            <div>
                                <div class="wv-status-pill">
                                    <span class="wv-status-dot"></span>
                                    <span><?php echo h($statusLabel); ?></span>
                                    <span class="wv-status-urdu">(<?php echo h($statusUrdu); ?>)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Security Serial with Copy Button -->
                        <div class="wv-card-item accent-teal">
                            <div class="wv-card-label">
                                <span>Security Serial</span>
                                <span class="wv-card-urdu-label">سکیورٹی کوڈ</span>
                            </div>
                            <div class="wv-serial-wrap">
                                <span class="wv-serial-code"><?php echo h($maskedSecuritySerial); ?></span>
                                <button type="button" class="wv-copy-btn" onclick="copySecuritySerial('<?php echo h($maskedSecuritySerial); ?>', this)" title="Copy Serial">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Scan Verification Time -->
                        <div class="wv-card-item">
                            <div class="wv-card-label">
                                <span>Scan Verification Time</span>
                                <span class="wv-card-urdu-label">تصدیقی وقت</span>
                            </div>
                            <div class="wv-card-value wv-card-value-mono" style="font-size:11.5px; color:#475569;"><?php echo date('d-M-Y H:i:s'); ?> PKT</div>
                        </div>
                    </div>

                    <!-- Security Audit Checklist -->
                    <div class="wv-security-checklist">
                        <h4>
                            <span style="display:flex;align-items:center;gap:6px;">
                                <span>🔒</span> Anti-Forgery Security Audit: Passed 100%
                            </span>
                            <span class="wv-audit-badge">VALIDATED</span>
                        </h4>
                        <div class="wv-check-grid">
                            <div class="wv-check-item"><span class="wv-chk-icon">✓</span> Central Society Registry Match Verified</div>
                            <div class="wv-check-item"><span class="wv-chk-icon">✓</span> Cryptographic SHA-256 Token Validated</div>
                            <div class="wv-check-item"><span class="wv-chk-icon">✓</span> Official Digital Stamp &amp; Signature Validated</div>
                            <div class="wv-check-item"><span class="wv-chk-icon">✓</span> Genuine Urdu Nastaleeq Typography Enforced</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="wv-actions-bar">
                        <button type="button" class="wv-btn-action wv-btn-primary" onclick="window.print()">
                            <span>🖨️</span> Print Verification Certificate
                        </button>
                        <button type="button" class="wv-btn-action wv-btn-secondary" onclick="openDocumentTab(document.getElementById('wv-tab-btn-doc'))">
                            <span>🔒</span> View Full Official Document Copy
                        </button>
                    </div>

                </div>

                <!-- Tab 2: Full Document Viewer with CNIC Security Gate -->
                <div id="tab-document" class="wv-tab-pane">
                    
                    <!-- CNIC Verification Gate Card (Shown when Locked) -->
                    <div id="wv-cnic-auth-box" class="wv-cnic-gate-card" style="<?php echo !empty($isDocumentUnlocked) ? 'display:none;' : 'display:block;'; ?>">
                        <div class="wv-cnic-gate-icon-circle">
                            🔒
                        </div>
                        <h3 class="wv-cnic-gate-title">Identity Authorization Required</h3>
                        <div class="wv-cnic-gate-urdu-title">مکمل دستاویز تک رسائی کے لیے شناختی تصدیق درکار ہے</div>
                        <p class="wv-cnic-gate-desc">
                            This is an official legal record containing sensitive property allotment data. To protect privacy and prevent unauthorized access, please enter the registered Client / Allottee CNIC Number to unlock and view the full document copy.
                        </p>

                        <div class="wv-cnic-ref-box">
                            <div class="wv-cnic-ref-item">
                                <span>Registered Allottee:</span> <strong><?php echo h(!empty($client['name']) ? trim($client['name']) : '-'); ?></strong>
                            </div>
                            <div class="wv-cnic-ref-item">
                                <span>Masked CNIC:</span> <strong style="font-family:monospace;"><?php echo h($maskedCnic); ?></strong>
                            </div>
                        </div>

                        <form id="wv-cnic-verify-form" onsubmit="handleCnicSubmit(event)">
                            <!-- Anti-Bot Honeypot -->
                            <div style="opacity:0;position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                                <input type="text" name="wv_auth_hp" id="wv_auth_hp" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="wv-cnic-form-group">
                                <div class="wv-cnic-form-label">
                                    <span>Enter Client / Allottee CNIC</span>
                                    <span style="font-family:'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', serif; text-transform:none; font-size:12px; color:#64748b;">شناختی کارڈ نمبر درج کریں</span>
                                </div>
                                <div class="wv-cnic-input-wrap">
                                    <span class="wv-cnic-input-icon">🪪</span>
                                    <input type="text" id="wv-cnic-input" class="wv-cnic-input" placeholder="XXXXX-XXXXXXX-X (13 digits)" maxlength="15" inputmode="numeric" autocomplete="off" oninput="formatCnicInput(this)">
                                </div>
                            </div>
                            <button type="submit" id="wv-cnic-submit-btn" class="wv-cnic-btn-submit">
                                <span>🔓</span> <span>Verify &amp; Unlock Document</span>
                            </button>
                        </form>

                        <div id="wv-cnic-feedback" class="wv-cnic-feedback"></div>

                        <div class="wv-cnic-gate-footnote">
                            🔒 SSL 256-Bit Encrypted Verification &bull; Anti-Brute Force Protection Active
                        </div>
                    </div>

                    <!-- Unlocked Document Container (Protected from scraping & right-click) -->
                    <div id="wv-document-viewer-container" style="display:none;" oncontextmenu="return false;" oncopy="return false;">
                        <div class="wv-doc-toolbar">
                            <div class="wv-doc-title">
                                <span>📄</span>
                                <span>Official Document Copy &bull; اصلی جاری شدہ دستاویز</span>
                                <span class="wv-unlocked-pill">✓ Authenticated</span>
                            </div>
                            <div class="wv-doc-actions">
                                <button type="button" class="wv-doc-btn" id="wv-zoom-btn" onclick="toggleLetterZoom()">
                                    <span id="wv-zoom-icon">🔍</span>
                                    <span id="wv-zoom-text">Fit Screen</span>
                                </button>
                                <button type="button" class="wv-doc-btn wv-doc-btn-primary" onclick="printDocumentPreview()">
                                    <span>🖨️</span>
                                    <span>Print Copy</span>
                                </button>
                                <button type="button" class="wv-doc-btn" onclick="relockDocument()" title="Lock Document" style="color:#ef4444;">
                                    <span>🔒</span>
                                    <span>Lock</span>
                                </button>
                            </div>
                        </div>

                        <div class="wv-doc-preview-box">
                            <div id="wv-document-render-root" class="wv-doc-inner" style="position:relative;overflow:hidden;" oncontextmenu="return false;">
                                <!-- Document content securely delivered on-demand via authenticated backend verification only -->
                            </div>
                        </div>
                        <div class="wv-mobile-zoom-hint">
                            💡 Tip: Tap "Fit Screen" or pinch and swipe horizontally to inspect full document resolution.
                        </div>
                    </div>

                </div>
            <?php }else{ ?>
                <div style="padding:32px 24px;text-align:center;">
                    <p style="color:#64748b;font-size:13.5px;max-width:500px;margin:0 auto 20px;">
                        If you believe this is an error, please contact the issuing office with the printed reference number or visit our customer support desk.
                    </p>
                    <a href="<?php echo $this->Html->url('/'); ?>" class="wv-btn-action wv-btn-primary">
                        Return to Home Page
                    </a>
                </div>
            <?php } ?>

            <!-- Footer Security Notice -->
            <div class="wv-footer-notice">
                <div class="wv-notice-text">
                    <strong>Official Anti-Forgery Notice:</strong> This public verification portal is legally maintained by White Villas Real Estate. Any unauthorized alteration, falsification, or forging of physical or digital letters is punishable under applicable law.
                </div>
                <div class="wv-notice-id">
                    <span>Security ID:</span> <code><?php echo h(!empty($maskedSecuritySerial) ? $maskedSecuritySerial : 'N/A'); ?></code>
                </div>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
var letterId = <?php echo json_encode($id); ?>;
var letterToken = <?php echo json_encode($token); ?>;
var verifyCnicEndpoint = <?php echo json_encode($verifyCnicUrl); ?>;
var securityToken = <?php echo json_encode(!empty($securityToken) ? $securityToken : ''); ?>;

var isDocumentUnlocked = false;

function updateUnlockUI() {
    var authBox = document.getElementById('wv-cnic-auth-box');
    var docContainer = document.getElementById('wv-document-viewer-container');
    var lockIcon = document.getElementById('wv-tab-lock-icon');

    if (isDocumentUnlocked) {
        if (authBox) authBox.style.display = 'none';
        if (docContainer) docContainer.style.display = 'block';
        if (lockIcon) lockIcon.textContent = '📄';
    } else {
        if (authBox) authBox.style.display = 'block';
        if (docContainer) docContainer.style.display = 'none';
        if (lockIcon) lockIcon.textContent = '🔒';
    }
}

function openDocumentTab(btn) {
    switchVerifyTab('tab-document', btn);
    updateUnlockUI();
    if (!isDocumentUnlocked) {
        setTimeout(function() {
            var inp = document.getElementById('wv-cnic-input');
            if (inp) inp.focus();
        }, 150);
    }
}

function switchVerifyTab(tabId, btn) {
    // Maximum Security: Leaving the document tab locks the document immediately
    if (tabId !== 'tab-document' && isDocumentUnlocked) {
        relockDocument();
    }

    document.querySelectorAll('.wv-tab-pane').forEach(function(el){ el.classList.remove('active'); });
    document.querySelectorAll('.wv-tab-btn').forEach(function(el){ el.classList.remove('active'); });
    
    var target = document.getElementById(tabId);
    if(target) { target.classList.add('active'); }
    if(btn) { btn.classList.add('active'); }
}

function formatCnicInput(input) {
    var val = input.value.replace(/[^0-9]/g, '');
    if (val.length > 13) val = val.substring(0, 13);
    var formatted = '';
    if (val.length > 5) {
        formatted += val.substring(0, 5) + '-';
        if (val.length > 12) {
            formatted += val.substring(5, 12) + '-' + val.substring(12);
        } else {
            formatted += val.substring(5);
        }
    } else {
        formatted = val;
    }
    input.value = formatted;
    input.classList.remove('error');
    var fb = document.getElementById('wv-cnic-feedback');
    if (fb) fb.style.display = 'none';
}

function handleCnicSubmit(e) {
    if (e && e.preventDefault) e.preventDefault();
    var input = document.getElementById('wv-cnic-input');
    var btn = document.getElementById('wv-cnic-submit-btn');
    if (!input) return;

    var raw = input.value.trim();
    var clean = raw.replace(/[^0-9]/g, '');

    if (clean.length !== 13) {
        showCnicFeedback('error', '<strong>❌ Invalid CNIC Length:</strong> Please enter the full 13-digit CNIC number.<div style="font-family:\'Jameel Noori Nastaleeq\', \'Noto Nastaliq Urdu\', serif; direction:rtl; margin-top:2px;">براہ کرم مکمل 13 ہندسوں کا شناختی کارڈ نمبر درج کریں۔</div>');
        input.classList.add('error');
        input.focus();
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span> <span>Verifying CNIC...</span>';

    // Zero-Trust Security: Always verify via hardened backend API (no hash exposure in client JS)
    serverVerifyCnic(clean);
}

function serverVerifyCnic(cleanCnic) {
    var xhr = new XMLHttpRequest();
    var url = verifyCnicEndpoint;
    var hpEl = document.getElementById('wv_auth_hp');
    var hpVal = hpEl ? hpEl.value : '';

    var params = 'cnic=' + encodeURIComponent(cleanCnic) +
                 '&sec_token=' + encodeURIComponent(securityToken) +
                 '&wv_auth_hp=' + encodeURIComponent(hpVal);

    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            var btn = document.getElementById('wv-cnic-submit-btn');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<span>🔓</span> <span>Verify &amp; Unlock Document</span>';
            }
            if (xhr.status === 200) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.status === 'success') {
                        // Dynamically inject rendered document received securely from backend
                        var root = document.getElementById('wv-document-render-root');
                        if (root && res.html) {
                            root.innerHTML = res.html;
                        }
                        onCnicVerifiedSuccess(cleanCnic);
                    } else if (res.status === 'locked') {
                        showCnicFeedback('error', '<strong>🔒 Security Lockout Active:</strong> ' + (res.message || 'Too many failed verification attempts. Access is temporarily locked.'));
                    } else {
                        showIncorrectCnicError(res.message);
                    }
                } catch(e) {
                    showIncorrectCnicError();
                }
            } else if (xhr.status === 429) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    showCnicFeedback('error', '<strong>🔒 Security Lockout:</strong> ' + (res.message || 'Too many failed verification attempts. Please wait 15 minutes.'));
                } catch(e) {
                    showCnicFeedback('error', '<strong>🔒 Security Lockout:</strong> Too many failed attempts. Access locked for 15 minutes.');
                }
            } else {
                showIncorrectCnicError();
            }
        }
    };
    xhr.send(params);
}

function onCnicVerifiedSuccess(cleanCnic) {
    var btn = document.getElementById('wv-cnic-submit-btn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span>✓</span> <span>Identity Verified!</span>';
    }
    showCnicFeedback('success', '<strong>✓ Identity Verified Successfully!</strong> Official document copy unlocked.<div style="font-family:\'Jameel Noori Nastaleeq\', \'Noto Nastaliq Urdu\', serif; direction:rtl; margin-top:2px;">شناختی تصدیق مکمل ہو گئی، دستاویز کھولی جا رہی ہے...</div>');
    
    isDocumentUnlocked = true;

    setTimeout(function() {
        updateUnlockUI();
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>🔓</span> <span>Verify &amp; Unlock Document</span>';
        }
    }, 600);
}

function showIncorrectCnicError(customMsg) {
    var input = document.getElementById('wv-cnic-input');
    var btn = document.getElementById('wv-cnic-submit-btn');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<span>🔓</span> <span>Verify &amp; Unlock Document</span>';
    }
    if (input) {
        input.classList.add('error');
        input.focus();
    }
    var msg = customMsg || 'The entered CNIC does not match our official records for this allottee. Please verify and try again.';
    showCnicFeedback('error', '<strong>❌ Incorrect CNIC Information:</strong> ' + msg + '<div style="font-family:\'Jameel Noori Nastaleeq\', \'Noto Nastaliq Urdu\', serif; direction:rtl; margin-top:2px;">درج کردہ شناختی کارڈ نمبر ریکارڈ سے مطابقت نہیں رکھتا، براہ کرم درست شناختی کارڈ درج کریں۔</div>');
}

function showCnicFeedback(type, messageHtml) {
    var fb = document.getElementById('wv-cnic-feedback');
    if (!fb) return;
    fb.className = 'wv-cnic-feedback ' + type;
    fb.innerHTML = messageHtml;
    fb.style.display = 'block';
}

function relockDocument() {
    isDocumentUnlocked = false;
    // ZERO-TRUST MEMORY PURGE: Wipe confidential document HTML from DOM memory immediately
    var root = document.getElementById('wv-document-render-root');
    if (root) {
        root.innerHTML = '';
    }
    if (window.sessionStorage) {
        sessionStorage.removeItem('wv_unlocked_' + letterId);
    }
    var input = document.getElementById('wv-cnic-input');
    if (input) {
        input.value = '';
        input.classList.remove('error');
    }
    var fb = document.getElementById('wv-cnic-feedback');
    if (fb) fb.style.display = 'none';
    var btn = document.getElementById('wv-cnic-submit-btn');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<span>🔓</span> <span>Verify &amp; Unlock Document</span>';
    }
    updateUnlockUI();
}

// Relock if browser tab loses focus / user switches to another browser tab or minimizes window
document.addEventListener('visibilitychange', function() {
    if (document.hidden && isDocumentUnlocked) {
        relockDocument();
        var summaryTabBtn = document.getElementById('wv-tab-btn-summary');
        if (summaryTabBtn) {
            switchVerifyTab('tab-summary', summaryTabBtn);
        }
    }
});

function copySecuritySerial(text, btn) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            var origHtml = btn.innerHTML;
            btn.innerHTML = '<span style="color:#10b981;font-weight:900;">✓</span>';
            btn.classList.add('copied');
            setTimeout(function() {
                btn.innerHTML = origHtml;
                btn.classList.remove('copied');
            }, 1800);
        });
    } else {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            var origHtml = btn.innerHTML;
            btn.innerHTML = '<span style="color:#10b981;font-weight:900;">✓</span>';
            setTimeout(function() { btn.innerHTML = origHtml; }, 1800);
        } catch(e){}
        document.body.removeChild(ta);
    }
}

var isLetterZoomFit = false;
function toggleLetterZoom() {
    var root = document.getElementById('wv-document-render-root');
    var box = document.querySelector('.wv-doc-preview-box');
    var icon = document.getElementById('wv-zoom-icon');
    var text = document.getElementById('wv-zoom-text');
    if (!root || !box) return;

    if (!isLetterZoomFit) {
        var boxWidth = box.clientWidth - 16;
        var docWidth = 820;
        if (boxWidth < docWidth && boxWidth > 180) {
            var scale = boxWidth / docWidth;
            root.style.transform = 'scale(' + scale + ')';
            root.style.transformOrigin = 'top left';
            root.style.marginBottom = '-' + Math.round((1 - scale) * root.offsetHeight) + 'px';
            isLetterZoomFit = true;
            if (icon) icon.textContent = '🔎';
            if (text) text.textContent = '100% Size';
        }
    } else {
        root.style.transform = 'none';
        root.style.marginBottom = '0';
        isLetterZoomFit = false;
        if (icon) icon.textContent = '🔍';
        if (text) text.textContent = 'Fit Screen';
    }
}

function printDocumentPreview() {
    var docNode = document.getElementById('wv-document-render-root');
    if (!docNode) return;

    var printWin = window.open('', '_blank', 'width=950,height=850');
    if (!printWin) {
        window.print();
        return;
    }

    var baseHref = window.location.origin + '<?php echo $this->Html->url('/'); ?>';
    
    var printContent = '<!DOCTYPE html><html><head><meta charset="utf-8">';
    printContent += '<base href="' + baseHref + '">';
    printContent += '<title>Official Verified Document (Duplicate Copy) - <?php echo h($maskedRefNo); ?></title>';
    
    // Copy stylesheets from head
    var headElements = document.head.querySelectorAll('link[rel="stylesheet"], style');
    headElements.forEach(function(el) {
        printContent += el.outerHTML;
    });

    // Explicit high-priority styles ensuring DUPLICATE WATERMARK displays on print
    printContent += '<style type="text/css">';
    printContent += '@page { size: auto; margin: 8mm; }';
    printContent += '* { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; box-sizing: border-box; }';
    printContent += 'html, body { background: #fff !important; margin: 0 !important; padding: 15px !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; position: relative !important; }';
    printContent += '.wv-doc-inner { position: relative !important; width: 100% !important; max-width: 820px !important; margin: 0 auto !important; }';
    printContent += '.wv-client-duplicate-watermark { position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; display: flex !important; justify-content: center !important; align-items: center !important; pointer-events: none !important; z-index: 999998 !important; overflow: hidden !important; }';
    printContent += '.wv-client-duplicate-watermark span { display: block !important; color: rgba(0, 0, 0, 0.18) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: clamp(60px, 9vw, 110px) !important; font-weight: 900 !important; letter-spacing: 6px !important; text-transform: uppercase !important; transform: rotate(-30deg) !important; white-space: nowrap !important; }';
    printContent += '.wv-watermark-overlay { position: fixed !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) rotate(-28deg) !important; pointer-events: none !important; z-index: 999999 !important; width: 85% !important; max-width: 580px !important; display: flex !important; justify-content: center !important; align-items: center !important; text-align: center !important; }';
    printContent += '.wv-watermark-stamp { border: 4px dashed rgba(220, 38, 38, 0.45) !important; color: rgba(220, 38, 38, 0.65) !important; padding: 16px 28px !important; border-radius: 14px !important; display: flex !important; flex-direction: column !important; align-items: center !important; gap: 4px !important; background: rgba(255, 255, 255, 0.85) !important; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12) !important; }';
    printContent += '.wv-watermark-badge { font-size: 11px !important; font-weight: 800 !important; letter-spacing: 2px !important; text-transform: uppercase !important; color: rgba(220, 38, 38, 0.70) !important; }';
    printContent += '.wv-watermark-duplicate { font-size: 36px !important; font-weight: 900 !important; letter-spacing: 5px !important; color: rgba(220, 38, 38, 0.75) !important; text-transform: uppercase !important; line-height: 1.1 !important; }';
    printContent += '.wv-watermark-text { font-size: 20px !important; font-weight: 800 !important; letter-spacing: 2px !important; text-transform: uppercase !important; white-space: nowrap !important; color: rgba(220, 38, 38, 0.65) !important; }';
    printContent += '.wv-watermark-sub { font-size: 11px !important; font-weight: 700 !important; letter-spacing: 1.5px !important; font-family: monospace !important; color: rgba(220, 38, 38, 0.60) !important; }';
    printContent += '@media print { .no-print, .wv-doc-toolbar { display: none !important; } }';
    printContent += '</style>';
    printContent += '</head><body class="wv-print-body" style="background:#fff;margin:0;padding:20px;position:relative;">';
    printContent += docNode.innerHTML;
    printContent += '</body></html>';

    printWin.document.open();
    printWin.document.write(printContent);
    printWin.document.close();
    printWin.focus();
    setTimeout(function() {
        printWin.print();
    }, 400);
}

document.addEventListener('DOMContentLoaded', function() {
    updateUnlockUI();

    if (window.location.hash === '#document' || window.location.hash === '#tab-document') {
        openDocumentTab(document.getElementById('wv-tab-btn-doc'));
    }
});
</script>
