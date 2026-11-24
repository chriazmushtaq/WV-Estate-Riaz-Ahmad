<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo __('METER DISCONNECTION WORK ORDER'); ?> - <?php echo h($bill['BillingBill']['bill_number']); ?></title>
    <style>
        @page { size: A4 portrait; margin: 15mm 15mm 15mm 15mm; }
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; color: #0f172a; margin: 0; padding: 20px; background: #f8fafc; font-size: 13px; line-height: 1.4; }
        .order-container { max-width: 800px; margin: 0 auto; background: #ffffff; border: 2px solid #0f172a; border-radius: 8px; padding: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .header-bar { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 16px; margin-bottom: 20px; gap: 16px; }
        .logo-box img { max-height: 65px; max-width: 140px; object-fit: contain; }
        .soc-title { text-align: center; flex: 1; }
        .soc-title h2 { margin: 0 0 4px 0; font-size: 20px; font-weight: 900; letter-spacing: 0.5px; color: #0f172a; text-transform: uppercase; }
        .soc-title p { margin: 0; font-size: 11.5px; color: #475569; font-weight: 600; }
        .badge-wo { background: #dc2626; color: #ffffff; padding: 8px 14px; border-radius: 6px; font-weight: 900; font-size: 12px; text-transform: uppercase; text-align: right; line-height: 1.3; }
        
        .alert-disabled { background: #fee2e2; border: 1.5px solid #ef4444; color: #991b1b; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; }
        
        .section-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: #f1f5f9; padding: 8px 12px; border-left: 4px solid #dc2626; margin: 18px 0 10px 0; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 24px; margin-bottom: 14px; }
        .info-item { display: flex; font-size: 12.5px; }
        .info-label { width: 140px; font-weight: 700; color: #475569; }
        .info-val { flex: 1; font-weight: 700; color: #0f172a; }
        
        .financial-table { width: 100%; border-collapse: collapse; margin: 12px 0 16px 0; }
        .financial-table th { background: #0f172a; color: #ffffff; padding: 8px 10px; font-size: 11.5px; font-weight: 800; text-align: left; }
        .financial-table td { padding: 8px 10px; border: 1px solid #cbd5e1; font-size: 12.5px; font-weight: 600; }
        .financial-table .total-row td { background: #fef2f2; font-weight: 900; color: #dc2626; font-size: 14px; border-top: 2px solid #dc2626; }
        
        .instructions-box { background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 6px; padding: 12px 16px; margin: 16px 0; }
        .instructions-box h4 { margin: 0 0 6px 0; color: #92400e; font-size: 12.5px; font-weight: 800; text-transform: uppercase; }
        .instructions-box ol { margin: 0; padding-left: 20px; font-size: 12px; color: #78350f; font-weight: 600; }
        .instructions-box li { margin-bottom: 4px; }
        
        .lineman-block { border: 1.5px dashed #94a3b8; border-radius: 6px; padding: 14px; margin: 16px 0; background: #f8fafc; }
        .lineman-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-top: 8px; }
        .lineman-field { border-bottom: 1.5px dotted #64748b; padding-bottom: 4px; }
        .lineman-field label { font-size: 11px; font-weight: 700; color: #64748b; display: block; }
        .lineman-field span { font-size: 12px; font-weight: 600; color: #0f172a; min-height: 18px; display: block; }
        
        .signatures-row { display: flex; justify-content: space-between; margin-top: 36px; padding-top: 10px; gap: 24px; }
        .sig-col { flex: 1; text-align: center; }
        .sig-line { border-top: 1.5px solid #0f172a; margin-bottom: 6px; margin-top: 40px; }
        .sig-col p { margin: 0; font-size: 11.5px; font-weight: 700; color: #334155; }
        
        .no-print { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        @media print {
            body { background: #ffffff; padding: 0; }
            .order-container { border: none; box-shadow: none; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <a href="javascript:history.back();" style="text-decoration:none;font-weight:700;color:#0284c7;font-size:13px;">&larr; <?php echo __('Back to Reports'); ?></a>
        <div>
            <button onclick="window.print();" style="background:#dc2626;color:#fff;border:none;padding:8px 20px;border-radius:6px;font-weight:800;cursor:pointer;">
                <i class="fa fa-print"></i> <?php echo __('Print Work Order'); ?>
            </button>
        </div>
    </div>

    <div class="order-container">
        <?php if($enabled === '0' || $enabled === 0){ ?>
            <div class="alert-disabled">
                <strong><?php echo __('Notice:'); ?></strong> <?php echo __('Defaulter Enforcement & Disconnection Module is currently toggled OFF in society settings. This document is in preview mode.'); ?>
            </div>
        <?php } ?>

        <!-- Header -->
        <div class="header-bar">
            <div class="logo-box">
                <?php if(!empty($logoBase64)){ ?>
                    <img src="<?php echo $logoBase64; ?>" alt="Logo" />
                <?php } ?>
            </div>
            <div class="soc-title">
                <h2><?php echo h($socData['name']); ?></h2>
                <p><?php echo h($socData['address']); ?></p>
                <p>Phone: <?php echo h($socData['phone']); ?> | Email: <?php echo h($socData['email']); ?></p>
            </div>
            <div class="badge-wo">
                <div><?php echo __('DISCONNECTION ORDER'); ?></div>
                <div style="font-size:10px;font-weight:600;margin-top:2px;">WO-<?php echo date('Ymd'); ?>-<?php echo (int)$bill['BillingBill']['id']; ?></div>
            </div>
        </div>

        <div style="text-align:center;margin-bottom:16px;">
            <div style="display:inline-block;background:#fee2e2;border:1px solid #fca5a5;color:#b91c1c;padding:5px 14px;border-radius:20px;font-weight:800;font-size:12px;letter-spacing:0.5px;">
                STATUS: <?php echo h($stage); ?> (<?php echo (int)$overdueDays; ?> DAYS OVERDUE)
            </div>
        </div>

        <!-- Consumer & Property Details -->
        <div class="section-title"><?php echo __('1. Defaulter Consumer & Property Particulars'); ?></div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label"><?php echo __('Consumer Name:'); ?></span>
                <span class="info-val"><?php echo h(!empty($bill['BillingClient']['name']) ? $bill['BillingClient']['name'] : 'N/A'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?php echo __('Bill Number:'); ?></span>
                <span class="info-val"><?php echo h($bill['BillingBill']['bill_number']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?php echo __('Contact Phone:'); ?></span>
                <span class="info-val"><?php echo h(!empty($bill['BillingClient']['phone']) ? $bill['BillingClient']['phone'] : 'N/A'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?php echo __('Billing Period:'); ?></span>
                <span class="info-val"><?php echo h($bill['BillingBill']['billing_period']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?php echo __('Property Unit:'); ?></span>
                <span class="info-val"><?php echo h(!empty($bill['BillingProperty']['title']) ? $bill['BillingProperty']['title'] : 'N/A'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?php echo __('Unit / Plot No:'); ?></span>
                <span class="info-val"><?php echo h(!empty($bill['BillingProperty']['property_no']) ? $bill['BillingProperty']['property_no'] : 'N/A'); ?></span>
            </div>
        </div>

        <!-- Meter Particulars -->
        <div class="section-title"><?php echo __('2. Meter Technical Specifications'); ?></div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label"><?php echo __('Meter Number:'); ?></span>
                <span class="info-val" style="color:#0284c7;"><?php echo h(!empty($meter['BillingMeter']['meter_number']) ? $meter['BillingMeter']['meter_number'] : ('MTR-' . $bill['BillingBill']['billing_property_id'])); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?php echo __('Utility Service:'); ?></span>
                <span class="info-val"><?php echo h(!empty($bill['BillingBill']['utility_type']) ? $bill['BillingBill']['utility_type'] : 'Electricity'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?php echo __('Feeder / Transformer:'); ?></span>
                <span class="info-val"><?php echo h(!empty($bill['BillingProperty']['transformer_name']) ? $bill['BillingProperty']['transformer_name'] : 'Transformer T-01 (Main Feeder)'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?php echo __('Last Recorded Index:'); ?></span>
                <span class="info-val"><?php echo number_format((float)$bill['BillingBill']['current_reading'], 2); ?> kWh</span>
            </div>
        </div>

        <!-- Financial Default Summary -->
        <div class="section-title"><?php echo __('3. Outstanding Arrears & Surcharges Breakdown'); ?></div>
        <?php 
            $tot = (float)$bill['BillingBill']['total_amount'];
            $paid = (float)$bill['BillingBill']['paid_amount'];
            $bal = (float)$bill['BillingBill']['balance_amount'];
            if($bal <= 0) $bal = max(0, $tot - $paid);
            $arrears = (float)$bill['BillingBill']['arrears_amount'];
            $lateFee = (float)$bill['BillingBill']['late_fee'];
            $currentCharge = max(0, $tot - $arrears - $lateFee);
        ?>
        <table class="financial-table">
            <thead>
                <tr>
                    <th><?php echo __('Line Item Description'); ?></th>
                    <th style="text-align:right;"><?php echo __('Payable Amount (PKR)'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo __('Current Month Consumption Charges'); ?></td>
                    <td style="text-align:right;"><?php echo number_format($currentCharge, 2); ?></td>
                </tr>
                <tr>
                    <td><?php echo __('Prior Unpaid Arrears & Balances'); ?></td>
                    <td style="text-align:right;"><?php echo number_format($arrears, 2); ?></td>
                </tr>
                <tr>
                    <td><?php echo __('Late Payment Surcharges & Fines'); ?></td>
                    <td style="text-align:right;"><?php echo number_format($lateFee, 2); ?></td>
                </tr>
                <tr>
                    <td><?php echo __('Total Partial Payments Credited'); ?></td>
                    <td style="text-align:right;color:#059669;">- <?php echo number_format($paid, 2); ?></td>
                </tr>
                <tr class="total-row">
                    <td><strong><?php echo __('TOTAL OUTSTANDING ARREARS PAYABLE FOR RESTORATION'); ?></strong></td>
                    <td style="text-align:right;"><strong>PKR <?php echo number_format($bal, 2); ?></strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Field Lineman Instructions -->
        <div class="instructions-box">
            <h4><?php echo __('Standard Operating Procedure (SOP) for Disconnection Team:'); ?></h4>
            <ol>
                <li><?php echo __('Verify physical property number and meter serial before disconnecting supply.'); ?></li>
                <li><?php echo __('Inspect meter terminal cover and casing for any anti-theft seal breach or illegal jumper bypass.'); ?></li>
                <li><?php echo __('Record the live reading at the exact minute of cut-off below in presence of consumer / guard.'); ?></li>
                <li><?php echo __('Physically disconnect service wire from distributor pole / main service box and wrap with safety cap.'); ?></li>
                <li><?php echo __('Apply official Red Tamper-Evident Security Seal on the meter terminals.'); ?></li>
                <li><?php echo __('Hand over occupant notice copy. Reconnection requires full settlement plus PKR 2,000 restoration fee.'); ?></li>
            </ol>
        </div>

        <!-- Lineman Field Execution Record -->
        <div class="lineman-block">
            <div style="font-weight:800;font-size:12px;color:#0f172a;text-transform:uppercase;margin-bottom:8px;">
                <?php echo __('Field Lineman Execution Sign-Off (To be filled on-site):'); ?>
            </div>
            <div class="lineman-grid">
                <div class="lineman-field">
                    <label><?php echo __('Date & Time of Cut-Off:'); ?></label>
                    <span>______/______/2026 at ______:______</span>
                </div>
                <div class="lineman-field">
                    <label><?php echo __('Final Meter Reading (kWh):'); ?></label>
                    <span>_________________________ kWh</span>
                </div>
                <div class="lineman-field">
                    <label><?php echo __('Security Seal Serial Applied:'); ?></label>
                    <span># _________________________</span>
                </div>
                <div class="lineman-field">
                    <label><?php echo __('Lineman Name & ID:'); ?></label>
                    <span>_________________________</span>
                </div>
                <div class="lineman-field">
                    <label><?php echo __('Lineman Signature:'); ?></label>
                    <span>_________________________</span>
                </div>
                <div class="lineman-field">
                    <label><?php echo __('Resident / Witness Signature:'); ?></label>
                    <span>_________________________</span>
                </div>
            </div>
        </div>

        <!-- Authorizations -->
        <div class="signatures-row">
            <div class="sig-col">
                <div class="sig-line"></div>
                <p><strong><?php echo __('Revenue Officer / Incharge'); ?></strong></p>
                <p style="font-size:10.5px;color:#64748b;"><?php echo h($socData['name']); ?></p>
            </div>
            <div class="sig-col">
                <div class="sig-line"></div>
                <p><strong><?php echo __('Estate Security Officer'); ?></strong></p>
                <p style="font-size:10.5px;color:#64748b;"><?php echo __('Enforcement & Line Control'); ?></p>
            </div>
            <div class="sig-col">
                <div class="sig-line"></div>
                <p><strong><?php echo __('Director of Administration'); ?></strong></p>
                <p style="font-size:10.5px;color:#64748b;"><?php echo __('Official Society Stamp'); ?></p>
            </div>
        </div>
    </div>

</body>
</html>
