<?php
$a = $allotment['BallotingAllotment'];
$client = !empty($allotment['Client']) ? $allotment['Client'] : array();
$deal = !empty($allotment['Deal']) ? $allotment['Deal'] : array();
$project = !empty($allotment['Project']) ? $allotment['Project'] : array();
$unit = !empty($allotment['BallotingUnit']) ? $allotment['BallotingUnit'] : array();

$clientName = !empty($client['name']) ? $client['name'] : 'Allottee';
$clientFather = !empty($client['guardian_name']) ? $client['guardian_name'] : (!empty($client['father_name']) ? $client['father_name'] : '');
$clientCnic = !empty($client['cnic']) ? $client['cnic'] : 'N/A';
$clientMobile = !empty($client['mobile']) ? $client['mobile'] : (!empty($client['phone']) ? $client['phone'] : '');

$projectName = !empty($project['name']) ? $project['name'] : 'White Villas';
$allotmentNo = !empty($a['allotment_no']) ? $a['allotment_no'] : ('WV-' . $a['id']);
$plotNo = !empty($a['property_no']) ? $a['property_no'] : (!empty($unit['property_no']) ? $unit['property_no'] : 'Plot Assigned');
$block = !empty($a['block_name']) ? $a['block_name'] : (!empty($unit['block_name']) ? $unit['block_name'] : 'General Block');
$street = !empty($a['street_no']) ? $a['street_no'] : (!empty($unit['street_no']) ? $unit['street_no'] : 'Main');
$area = $a['unit_area'] . ' ' . (!empty($a['area_unit']) ? $a['area_unit'] : 'Marla');
$category = !empty($a['permanent_type']) ? $a['permanent_type'] : (!empty($unit['permanent_type']) ? $unit['permanent_type'] : 'Standard Residential');
$allotmentDate = !empty($a['allotment_date']) ? date('d-M-Y', strtotime($a['allotment_date'])) : date('d-M-Y');
$dealInvoice = !empty($deal['invoice_no']) ? $deal['invoice_no'] : ('DEAL-' . $deal['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo __('Official Plot Allocation Certificate - ') . h($allotmentNo); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Raleway:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style type="text/css">
    * { box-sizing: border-box; }
    body {
        margin: 0;
        padding: 24px;
        background: #e2e8f0;
        font-family: 'Raleway', sans-serif;
        color: #0f172a;
    }
    .cert-toolbar {
        max-width: 840px;
        margin: 0 auto 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .cert-sheet {
        max-width: 840px;
        margin: 0 auto;
        background: #ffffff;
        padding: 20px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.15);
        border-radius: 6px;
        position: relative;
    }
    .cert-border-outer {
        border: 4px solid #07152a;
        padding: 6px;
    }
    .cert-border-inner {
        border: 2px solid #b45309;
        padding: 28px 32px;
        background: #ffffff;
        position: relative;
    }
    .cert-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 80px;
        font-family: 'Cinzel', serif;
        font-weight: 900;
        color: rgba(7, 21, 42, 0.035);
        white-space: nowrap;
        pointer-events: none;
        z-index: 1;
        letter-spacing: 6px;
    }
    .cert-header {
        text-align: center;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 18px;
        margin-bottom: 22px;
        position: relative;
        z-index: 2;
    }
    .cert-title-org {
        font-family: 'Cinzel', serif;
        font-size: 28px;
        font-weight: 800;
        color: #07152a;
        letter-spacing: 2px;
        margin: 0 0 4px;
    }
    .cert-sub-org {
        font-size: 13px;
        color: #64748b;
        letter-spacing: 1px;
        font-weight: 700;
        text-transform: uppercase;
        margin: 0 0 14px;
    }
    .cert-badge-ribbon {
        display: inline-block;
        background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
        color: #ffffff;
        font-family: 'Cinzel', serif;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: 1.5px;
        padding: 6px 24px;
        border-radius: 4px;
        text-transform: uppercase;
    }
    .cert-meta-row {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #64748b;
        margin-bottom: 18px;
        font-weight: 700;
        position: relative;
        z-index: 2;
    }
    .cert-meta-row strong {
        color: #07152a;
    }
    .cert-narrative {
        font-size: 13.5px;
        line-height: 1.7;
        color: #334155;
        text-align: justify;
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
    }
    .cert-narrative strong {
        color: #07152a;
        font-weight: 800;
    }
    .cert-plot-box {
        background: #f8fafc;
        border: 2px solid #07152a;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 22px;
        position: relative;
        z-index: 2;
    }
    .cert-plot-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }
    .cert-plot-item {
        border-right: 1px solid #e2e8f0;
        padding-right: 12px;
    }
    .cert-plot-item:last-child {
        border-right: none;
    }
    .cert-plot-item-label {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 2px;
    }
    .cert-plot-item-val {
        font-size: 16px;
        font-weight: 800;
        color: #07152a;
    }
    .cert-plot-item-val.highlight {
        color: #b45309;
        font-size: 20px;
        font-family: 'Cinzel', serif;
    }
    .cert-bottom-grid {
        display: grid;
        grid-template-columns: 120px 1fr 200px;
        gap: 20px;
        align-items: center;
        margin-top: 24px;
        padding-top: 18px;
        border-top: 2px solid #e2e8f0;
        position: relative;
        z-index: 2;
    }
    .cert-qr-wrap {
        text-align: center;
    }
    .cert-qr-img {
        width: 100px;
        height: 100px;
        border: 2px solid #07152a;
        border-radius: 6px;
        padding: 2px;
    }
    .cert-qr-caption {
        font-size: 9px;
        font-weight: 700;
        color: #64748b;
        margin-top: 4px;
    }
    .cert-security-info {
        font-size: 11px;
        color: #64748b;
        line-height: 1.5;
    }
    .cert-security-info strong {
        color: #07152a;
    }
    .cert-sig-wrap {
        text-align: center;
    }
    .cert-seal-icon {
        width: 64px;
        height: 64px;
        border: 2px solid #b45309;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #b45309;
        font-size: 28px;
        margin-bottom: 8px;
    }
    .cert-sig-line {
        border-top: 1.5px solid #07152a;
        margin-top: 36px;
        padding-top: 6px;
        font-size: 11px;
        font-weight: 800;
        color: #07152a;
        text-transform: uppercase;
    }
    .btn-print {
        background: #07152a;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        padding: 10px 20px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-print:hover { background: #ff0a46; }
    @media print {
        body { background: #ffffff; padding: 0; }
        .cert-toolbar { display: none !important; }
        .cert-sheet { box-shadow: none; padding: 0; }
        @page { size: A4 portrait; margin: 10mm; }
    }
    </style>
</head>
<body>

<div class="cert-toolbar">
    <a href="javascript:window.history.back();" style="color:#07152a; text-decoration:none; font-weight:800; font-size:13px;">
        <i class="fa fa-arrow-left"></i> <?php echo __('Back to Results'); ?>
    </a>
    <button onclick="window.print();" class="btn-print">
        <i class="fa fa-print"></i> <?php echo __('Print / Save as PDF'); ?>
    </button>
</div>

<div class="cert-sheet">
    <div class="cert-border-outer">
        <div class="cert-border-inner">
            <div class="cert-watermark"><?php echo h(strtoupper($projectName)); ?></div>

            <div class="cert-header">
                <h1 class="cert-title-org"><?php echo h(strtoupper($projectName)); ?></h1>
                <p class="cert-sub-org"><?php echo __('Directorate of Town Planning, Land Allotment & Development'); ?></p>
                <div class="cert-badge-ribbon"><?php echo __('Provisional Plot Allocation Certificate'); ?></div>
            </div>

            <div class="cert-meta-row">
                <div>Certificate Ref: <strong><?php echo h($allotmentNo); ?></strong></div>
                <div>Deal No: <strong><?php echo h($dealInvoice); ?></strong></div>
                <div>Issue Date: <strong><?php echo h($allotmentDate); ?></strong></div>
            </div>

            <div class="cert-narrative">
                This is to officially certify that pursuant to the computerized master balloting run conducted under the authority of <strong><?php echo h($projectName); ?></strong>, the provisional allotment of the real estate property detailed below has been formally confirmed in favor of:
                <br><br>
                Allottee Name: <strong><?php echo h(strtoupper($clientName)); ?></strong><?php if(!empty($clientFather)){ echo ' S/O / D/O / W/O <strong>' . h(strtoupper($clientFather)) . '</strong>'; } ?>
                &nbsp;|&nbsp; CNIC: <strong><?php echo h($clientCnic); ?></strong>
            </div>

            <!-- Plot Specifications Box -->
            <div class="cert-plot-box">
                <div class="cert-plot-grid">
                    <div class="cert-plot-item">
                        <div class="cert-plot-item-label"><?php echo __('Allotted Plot / Unit No:'); ?></div>
                        <div class="cert-plot-item-val highlight"><?php echo h($plotNo); ?></div>
                    </div>
                    <div class="cert-plot-item">
                        <div class="cert-plot-item-label"><?php echo __('Block / Sector:'); ?></div>
                        <div class="cert-plot-item-val"><?php echo h($block); ?></div>
                    </div>
                    <div class="cert-plot-item">
                        <div class="cert-plot-item-label"><?php echo __('Street / Road:'); ?></div>
                        <div class="cert-plot-item-val"><?php echo h($street); ?></div>
                    </div>
                </div>
                <div class="cert-plot-grid" style="margin-top:12px; padding-top:12px; border-top:1px solid #e2e8f0;">
                    <div class="cert-plot-item">
                        <div class="cert-plot-item-label"><?php echo __('Plot Size / Area:'); ?></div>
                        <div class="cert-plot-item-val"><?php echo h($area); ?></div>
                    </div>
                    <div class="cert-plot-item">
                        <div class="cert-plot-item-label"><?php echo __('Category / Classification:'); ?></div>
                        <div class="cert-plot-item-val"><?php echo h($category); ?></div>
                    </div>
                    <div class="cert-plot-item">
                        <div class="cert-plot-item-label"><?php echo __('Allotment Status:'); ?></div>
                        <div class="cert-plot-item-val" style="color:#059669;"><?php echo __('Confirmed & Allotted'); ?></div>
                    </div>
                </div>
            </div>

            <div style="font-size:11px; color:#64748b; line-height:1.5; margin-bottom:14px; position:relative; z-index:2;">
                * This certificate is provisionally allotted based on payment schedule clearance. Final possession and title deed transfer remain subject to full clearance of development charges and scheduled installments as per society bylaws.
            </div>

            <!-- Bottom QR & Verification Block -->
            <div class="cert-bottom-grid">
                <div class="cert-qr-wrap">
                    <img src="<?php echo h($qrCodeUrl); ?>" alt="Scan to Verify" class="cert-qr-img">
                    <div class="cert-qr-caption"><?php echo __('Scan to Verify Online'); ?></div>
                </div>

                <div class="cert-security-info">
                    <strong>Digital Verification & Security Seal</strong><br>
                    Sec Token: <?php echo substr($expectedToken, 0, 16); ?>...<br>
                    Verify Portal: <?php echo h($verifyUrl); ?><br>
                    This is a secure system-generated certificate. Any alteration or tampering voids this document.
                </div>

                <div class="cert-sig-wrap">
                    <div class="cert-sig-line">
                        <?php echo __('Authorized Signature'); ?><br>
                        <span style="font-size:10px; color:#64748b; font-weight:600; text-transform:none;"><?php echo h($projectName); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
