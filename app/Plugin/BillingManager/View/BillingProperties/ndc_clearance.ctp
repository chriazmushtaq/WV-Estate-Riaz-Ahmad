<?php if(!empty($isPrint)){ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo __('UTILITY NO DEMAND CERTIFICATE (NDC)'); ?> - <?php echo h($property['BillingProperty']['title']); ?></title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color: #0f172a; margin: 0; padding: 25px; background: #fff; line-height: 1.5; font-size: 13px; }
        .ndc-container { max-width: 820px; margin: 0 auto; border: 3px double #0f172a; border-radius: 12px; padding: 36px 32px; position: relative; background: #ffffff; }
        .ndc-container:before { content: ""; position: absolute; top: 8px; left: 8px; right: 8px; bottom: 8px; border: 1px solid #94a3b8; border-radius: 8px; pointer-events: none; }
        .header-bar { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 16px; margin-bottom: 24px; gap: 16px; }
        .logo-box img { max-height: 70px; max-width: 150px; object-fit: contain; }
        .soc-title { text-align: center; flex: 1; }
        .soc-title h2 { margin: 0 0 4px 0; font-size: 22px; font-weight: 900; letter-spacing: 1px; color: #0f172a; text-transform: uppercase; }
        .soc-title p { margin: 0; font-size: 12px; color: #475569; font-weight: 600; }
        
        .ndc-badge { background: #059669; color: #ffffff; padding: 8px 16px; border-radius: 6px; font-weight: 900; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .ndc-ref { font-size: 11px; font-family: monospace; font-weight: 700; color: #065f46; text-align: right; margin-top: 4px; }
        
        .cert-title-box { text-align: center; margin: 26px 0 20px 0; }
        .cert-title-box h1 { margin: 0; font-size: 20px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; color: #0f172a; text-decoration: underline; text-underline-offset: 6px; }
        .cert-subtitle { font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase; margin-top: 6px; letter-spacing: 1px; }

        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th, .details-table td { padding: 8px 12px; border: 1px solid #cbd5e1; font-size: 12.5px; }
        .details-table th { background: #f8fafc; color: #334155; font-weight: 800; width: 22%; }
        .details-table td { font-weight: 700; color: #0f172a; width: 28%; }
        
        .declaration-box { background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px; padding: 16px 20px; margin: 24px 0; text-align: justify; font-size: 13px; line-height: 1.6; color: #14532d; font-weight: 600; }
        
        .signatures-row { display: flex; justify-content: space-between; margin-top: 55px; padding-top: 10px; gap: 30px; }
        .sig-col { flex: 1; text-align: center; }
        .sig-line { border-top: 1.5px solid #0f172a; margin-bottom: 6px; margin-top: 45px; }
        .sig-col p { margin: 0; font-size: 12px; font-weight: 700; color: #334155; }
        
        .no-print { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; max-width: 820px; margin: 0 auto 20px auto; }
        @media print {
            body { padding: 0; }
            .ndc-container { border: 2px solid #0f172a; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="<?php echo $this->Html->url(array('action' => 'ndc_clearance', $property['BillingProperty']['id'])); ?>" style="text-decoration:none;font-weight:700;color:#0284c7;font-size:13px;">&larr; <?php echo __('Back to Clearance Workspace'); ?></a>
        <button onclick="window.print();" style="background:#059669;color:#fff;border:none;padding:8px 22px;border-radius:6px;font-weight:800;cursor:pointer;">
            <i class="fa fa-print"></i> <?php echo __('Print Official NDC Certificate'); ?>
        </button>
    </div>

    <div class="ndc-container">
        <!-- Society Header -->
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
            <div>
                <div class="ndc-badge">&#10004; <?php echo __('NDC ISSUED'); ?></div>
                <div class="ndc-ref">NDC-<?php echo date('Ymd'); ?>-<?php echo str_pad($property['BillingProperty']['id'], 4, '0', STR_PAD_LEFT); ?></div>
            </div>
        </div>

        <!-- Title -->
        <div class="cert-title-box">
            <h1><?php echo __('UTILITY NO DEMAND CERTIFICATE (NDC)'); ?></h1>
            <div class="cert-subtitle"><?php echo __('Official Estate Utility Dues & Meter Clearance Record'); ?></div>
        </div>

        <!-- Table of Credentials -->
        <table class="details-table">
            <tr>
                <th><?php echo __('Property Unit:'); ?></th>
                <td><?php echo h($property['BillingProperty']['title']); ?></td>
                <th><?php echo __('Unit / Plot No:'); ?></th>
                <td><?php echo h($property['BillingProperty']['property_no']); ?></td>
            </tr>
            <tr>
                <th><?php echo __('Owner / Landlord:'); ?></th>
                <td><?php echo h(!empty($property['BillingClient']['name']) ? $property['BillingClient']['name'] : 'N/A'); ?></td>
                <th><?php echo __('Owner Contact:'); ?></th>
                <td><?php echo h(!empty($property['BillingClient']['phone']) ? $property['BillingClient']['phone'] : 'N/A'); ?></td>
            </tr>
            <tr>
                <th><?php echo __('Tenant Name:'); ?></th>
                <td><strong style="color:#0284c7;"><?php echo h(!empty($property['BillingProperty']['tenant_name']) ? $property['BillingProperty']['tenant_name'] : 'N/A (Owner Occupied)'); ?></strong></td>
                <th><?php echo __('Tenant Phone / CNIC:'); ?></th>
                <td><?php echo h(!empty($property['BillingProperty']['tenant_phone']) ? $property['BillingProperty']['tenant_phone'] : '-'); ?> / <?php echo h(!empty($property['BillingProperty']['tenant_cnic']) ? $property['BillingProperty']['tenant_cnic'] : '-'); ?></td>
            </tr>
            <tr>
                <th><?php echo __('Meter Serial No:'); ?></th>
                <td><?php echo h(!empty($property['BillingMeter']['meter_number']) ? $property['BillingMeter']['meter_number'] : ('MTR-' . $property['BillingProperty']['id'])); ?></td>
                <th><?php echo __('Utility Service:'); ?></th>
                <td><?php echo h(!empty($property['BillingMeter']['utility_type']) ? $property['BillingMeter']['utility_type'] : 'Electricity'); ?></td>
            </tr>
            <tr>
                <th><?php echo __('Final Exit Reading:'); ?></th>
                <td><strong style="color:#059669;"><?php echo !empty($latestReading) ? number_format((float)$latestReading['BillingReading']['current_reading'], 2) : number_format((float)$property['BillingMeter']['current_reading'], 2); ?> kWh</strong></td>
                <th><?php echo __('Clearance Date:'); ?></th>
                <td><strong><?php echo !empty($property['BillingProperty']['ndc_date']) ? date('d-M-Y H:i', strtotime($property['BillingProperty']['ndc_date'])) : date('d-M-Y H:i'); ?></strong></td>
            </tr>
        </table>

        <!-- Formal Legal Certificate Declaration -->
        <div class="declaration-box">
            <strong>CERTIFICATE DECLARATION:</strong> This is to certify that an exhaustive reconciliation of all utility invoices, maintenance heads, electricity consumption charges, and late payment surcharges has been conducted for the aforementioned property unit. As of the clearance timestamp stated above, <strong>ZERO (0.00) OUTSTANDING DUES OR ARREARS</strong> remain payable to <strong><?php echo h($socData['name']); ?></strong>. The outgoing occupant / tenant is hereby cleared of all utility financial liabilities for this tenure.
        </div>

        <!-- Signature Sign-Offs -->
        <div class="signatures-row">
            <div class="sig-col">
                <div class="sig-line"></div>
                <p><strong><?php echo __('Billing & Revenue Officer'); ?></strong></p>
                <p style="font-size:11px;color:#64748b;"><?php echo h($socData['name']); ?></p>
            </div>
            <div class="sig-col">
                <div class="sig-line"></div>
                <p><strong><?php echo __('Estate Manager / Incharge'); ?></strong></p>
                <p style="font-size:11px;color:#64748b;"><?php echo __('Operations & Maintenance'); ?></p>
            </div>
            <div class="sig-col">
                <div class="sig-line"></div>
                <p><strong><?php echo __('Official Society Seal'); ?></strong></p>
                <p style="font-size:11px;color:#64748b;"><?php echo __('Authorized Stamp'); ?></p>
            </div>
        </div>
    </div>
</body>
</html>
<?php } else { ?>

<!-- INTERACTIVE CLEARANCE WORKSPACE -->
<div class="subm-admin-wrap" style="padding:18px;background:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
    <div style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h3 style="font-weight:900;color:#0f172a;margin:0 0 4px 0;font-size:20px;">
                <i class="fa fa-certificate text-primary"></i> <?php echo __('Tenant Move-Out Clearance & NDC Hub'); ?>
            </h3>
            <span style="font-size:13px;color:#64748b;font-weight:600;">
                <?php echo __('Clearance workspace for property unit:'); ?> <strong><?php echo h($property['BillingProperty']['title']); ?></strong> (<?php echo h($property['BillingProperty']['property_no']); ?>)
            </span>
        </div>
        <div>
            <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default" style="font-weight:700;">
                &larr; <?php echo __('Back to Properties List'); ?>
            </a>
            <?php if(!empty($property['BillingProperty']['ndc_status']) && $property['BillingProperty']['ndc_status'] === 'CLEARED'){ ?>
                <a href="<?php echo $this->Html->url(array('action' => 'ndc_clearance', $property['BillingProperty']['id'], '?' => array('print' => 1))); ?>" target="_blank" class="btn btn-success" style="font-weight:800;margin-left:6px;">
                    <i class="fa fa-print"></i> <?php echo __('Print Stamped NDC Certificate'); ?>
                </a>
            <?php } ?>
        </div>
    </div>

    <?php echo $this->Session->flash(); ?>

    <?php if($enabled === '0' || $enabled === 0){ ?>
        <div class="alert alert-warning" style="font-weight:600;">
            <i class="fa fa-exclamation-triangle"></i> <?php echo __('Note: Tenant NDC Clearance is currently toggled OFF in society Feature Toggles. You can enable it anytime in Billing Settings.'); ?>
        </div>
    <?php } ?>

    <div class="row">
        <!-- Left Column: Tenant & Meter Departure Form -->
        <div class="col-md-6">
            <div class="panel panel-default" style="border:1.5px solid #cbd5e1;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.04);overflow:hidden;">
                <div class="panel-heading" style="background:#0f172a;color:#fff;font-weight:800;padding:12px 18px;">
                    <i class="fa fa-user text-info"></i> <?php echo __('Tenant & Meter Move-Out Particulars'); ?>
                </div>
                <div class="panel-body" style="padding:20px;">
                    <?php echo $this->Form->create('BillingProperty', array('class' => 'form-horizontal')); ?>

                    <div class="form-group">
                        <label class="col-sm-4 control-label" style="font-size:12.5px;"><?php echo __('Landlord (Owner)'); ?></label>
                        <div class="col-sm-8" style="padding-top:7px;font-weight:800;color:#0f172a;">
                            <?php echo h(!empty($property['BillingClient']['name']) ? $property['BillingClient']['name'] : 'N/A'); ?>
                            <small class="text-muted" style="display:block;"><?php echo h(!empty($property['BillingClient']['phone']) ? $property['BillingClient']['phone'] : ''); ?></small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label" style="font-size:12.5px;"><?php echo __('Tenant Full Name'); ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="tenant_name" value="<?php echo h(!empty($property['BillingProperty']['tenant_name']) ? $property['BillingProperty']['tenant_name'] : ''); ?>" class="form-control" placeholder="Enter tenant name" style="font-weight:bold;" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label" style="font-size:12.5px;"><?php echo __('Tenant Mobile No'); ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="tenant_phone" value="<?php echo h(!empty($property['BillingProperty']['tenant_phone']) ? $property['BillingProperty']['tenant_phone'] : ''); ?>" class="form-control" placeholder="03XXXXXXXXX" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label" style="font-size:12.5px;"><?php echo __('Tenant CNIC'); ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="tenant_cnic" value="<?php echo h(!empty($property['BillingProperty']['tenant_cnic']) ? $property['BillingProperty']['tenant_cnic'] : ''); ?>" class="form-control" placeholder="35201-XXXXXXX-X" />
                        </div>
                    </div>

                    <hr style="margin:16px 0;border-color:#e2e8f0;" />

                    <div class="form-group">
                        <label class="col-sm-4 control-label" style="font-size:12.5px;"><?php echo __('Meter Serial No'); ?></label>
                        <div class="col-sm-8" style="padding-top:7px;font-weight:800;color:#0284c7;">
                            <?php echo h(!empty($property['BillingMeter']['meter_number']) ? $property['BillingMeter']['meter_number'] : ('MTR-' . $property['BillingProperty']['id'])); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label" style="font-size:12.5px;"><?php echo __('Last Recorded Index'); ?></label>
                        <div class="col-sm-8" style="padding-top:7px;font-weight:800;color:#475569;">
                            <?php echo !empty($latestReading) ? number_format((float)$latestReading['BillingReading']['current_reading'], 2) : number_format((float)$property['BillingMeter']['current_reading'], 2); ?> kWh
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label" style="font-size:12.5px;"><?php echo __('Departure Final Reading'); ?></label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" name="departure_reading" value="" class="form-control" placeholder="Enter exit inspection reading (kWh)" style="font-weight:bold;color:#059669;" />
                            <small class="text-muted"><?php echo __('Leave blank if using latest recorded meter reading.'); ?></small>
                        </div>
                    </div>

                    <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
                        <button type="submit" name="action_type" value="update_info" class="btn btn-default" style="font-weight:700;">
                            <i class="fa fa-save"></i> <?php echo __('Save Tenant Info'); ?>
                        </button>
                        <button type="submit" name="action_type" value="issue_ndc" class="btn btn-success" style="font-weight:800;" <?php if($totalOutstanding > 0) echo 'onclick="return confirm(\'Warning: This property has outstanding dues of PKR ' . number_format($totalOutstanding, 2) . '. Are you sure you want to proceed?\');"'; ?>>
                            <i class="fa fa-check-circle"></i> <?php echo __('Issue & Stamp NDC Certificate'); ?>
                        </button>
                    </div>

                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Unpaid Bills Audit & NDC Status -->
        <div class="col-md-6">
            <!-- NDC Status Badge Card -->
            <div class="panel panel-default" style="border:1.5px solid #cbd5e1;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.04);margin-bottom:20px;overflow:hidden;">
                <div class="panel-body" style="padding:18px;display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;"><?php echo __('Current Clearance Status'); ?></div>
                        <div style="font-size:18px;font-weight:900;margin-top:2px;color:<?php echo (!empty($property['BillingProperty']['ndc_status']) && $property['BillingProperty']['ndc_status'] === 'CLEARED') ? '#059669' : '#d97706'; ?>;">
                            <?php if(!empty($property['BillingProperty']['ndc_status']) && $property['BillingProperty']['ndc_status'] === 'CLEARED'){ ?>
                                <i class="fa fa-check-circle"></i> <?php echo __('CLEARED / NDC ISSUED'); ?>
                            <?php } else { ?>
                                <i class="fa fa-clock-o"></i> <?php echo __('CLEARANCE PENDING'); ?>
                            <?php } ?>
                        </div>
                        <?php if(!empty($property['BillingProperty']['ndc_date'])){ ?>
                            <small class="text-muted" style="font-size:11.5px;">Issued on <?php echo date('d-M-Y H:i', strtotime($property['BillingProperty']['ndc_date'])); ?></small>
                        <?php } ?>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;"><?php echo __('Total Dues Payable'); ?></div>
                        <div style="font-size:22px;font-weight:900;color:<?php echo ($totalOutstanding > 0) ? '#dc2626' : '#059669'; ?>;">
                            PKR <?php echo number_format($totalOutstanding, 2); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bills Audit Table -->
            <div class="panel panel-default" style="border:1.5px solid #cbd5e1;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.04);overflow:hidden;">
                <div class="panel-heading" style="background:#f8fafc;color:#0f172a;font-weight:800;padding:12px 18px;display:flex;justify-content:space-between;align-items:center;">
                    <span><i class="fa fa-list text-primary"></i> <?php echo __('Outstanding Bills Audit'); ?></span>
                    <span class="badge" style="background:#dc2626;"><?php echo count($unpaidBills); ?> <?php echo __('Unpaid'); ?></span>
                </div>
                <div class="panel-body" style="padding:0;overflow-x:auto;">
                    <table class="table table-bordered table-striped" style="margin:0;font-size:12px;">
                        <thead>
                            <tr style="background:#0f172a;color:#fff;">
                                <th><?php echo __('Bill No'); ?></th>
                                <th><?php echo __('Month'); ?></th>
                                <th><?php echo __('Due Date'); ?></th>
                                <th><?php echo __('Total (PKR)'); ?></th>
                                <th><?php echo __('Balance (PKR)'); ?></th>
                                <th><?php echo __('Action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($unpaidBills)){ ?>
                                <tr>
                                    <td colspan="6" class="text-center text-success" style="padding:22px;font-weight:700;">
                                        <i class="fa fa-check-circle" style="font-size:18px;"></i><br/>
                                        <?php echo __('All utility bills for this property are completely cleared (0.00 balance)! Ready for NDC.'); ?>
                                    </td>
                                </tr>
                            <?php } else { foreach($unpaidBills as $ub){ 
                                $bTot = (float)$ub['BillingBill']['total_amount'];
                                $bBal = (float)$ub['BillingBill']['balance_amount'];
                                if($bBal <= 0) $bBal = $bTot - (float)$ub['BillingBill']['paid_amount'];
                            ?>
                                <tr>
                                    <td><strong style="color:#0f172a;"><?php echo h($ub['BillingBill']['bill_number']); ?></strong></td>
                                    <td><?php echo h($ub['BillingBill']['billing_period']); ?></td>
                                    <td><span style="color:#dc2626;font-weight:700;"><?php echo h($ub['BillingBill']['due_date']); ?></span></td>
                                    <td><?php echo number_format($bTot, 2); ?></td>
                                    <td><strong style="color:#dc2626;"><?php echo number_format($bBal, 2); ?></strong></td>
                                    <td>
                                        <a href="<?php echo $this->Html->url(array('controller' => 'billing_bills', 'action' => 'print_bill', $ub['BillingBill']['id'])); ?>" target="_blank" class="btn btn-xs btn-primary">
                                            <i class="fa fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
