<?php
$this->Html->addCrumb(__('Leads'), array('controller' => 'Leads', 'action' => 'index'));
$this->Html->addCrumb(__('Digital Quotation Generator'));

$lead = isset($lead) ? (array)$lead : array();
$leadRow = !empty($lead['Lead']) ? $lead['Lead'] : array();
$clientRow = !empty($lead['Client']) ? $lead['Client'] : array();
$settings = isset($settings) ? (array)$settings : array();
$reportAgency = isset($reportAgency) ? (array)$reportAgency : array();

$leadName = !empty($leadRow['lead_name']) ? $leadRow['lead_name'] : (!empty($clientRow['name']) ? $clientRow['name'] : 'Valued Client');
$leadMobile = !empty($leadRow['lead_mobile']) ? $leadRow['lead_mobile'] : (!empty($clientRow['mobile']) ? $clientRow['mobile'] : '');
$leadEmail = !empty($leadRow['lead_email']) ? $leadRow['lead_email'] : (!empty($clientRow['email']) ? $clientRow['email'] : '');

$savedTotal = !empty($leadRow['quotation_total']) ? (float)$leadRow['quotation_total'] : 15000000;
$defaultDownPct = isset($settings['default_downpayment_pct']) ? (int)$settings['default_downpayment_pct'] : 20;
$defaultDuration = isset($settings['default_duration_years']) ? (int)$settings['default_duration_years'] : 3;

$agencyName = !empty($reportAgency['name']) ? $reportAgency['name'] : 'White Villas';
$agencyPhone = !empty($reportAgency['phone']) ? $reportAgency['phone'] : (!empty($reportAgency['mobile']) ? $reportAgency['mobile'] : '');
$agencyEmail = !empty($reportAgency['email']) ? $reportAgency['email'] : '';
$agencyAddress = !empty($reportAgency['address']) ? $reportAgency['address'] : '';
?>
<style type="text/css">
.quotation-builder-panel {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 24px;
}
.quotation-sheet {
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 36px 40px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    max-width: 920px;
    margin: 0 auto;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.quotation-header {
    border-bottom: 3px solid #1e293b;
    padding-bottom: 20px;
    margin-bottom: 24px;
}
.quotation-title {
    font-size: 26px;
    font-weight: 800;
    color: #1e293b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
}
.quotation-badge {
    background: #4f46e5;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 999px;
    text-transform: uppercase;
    display: inline-block;
}
.client-info-box {
    background: #f1f5f9;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
}
.schedule-table thead th {
    background: #1e293b;
    color: #fff;
    font-size: 12px;
    text-transform: uppercase;
}
.schedule-table tbody tr.milestone-row {
    background: #f8fafc;
    font-weight: 600;
}
.summary-box {
    background: #ecfdf5;
    border: 1px solid #6ee7b7;
    border-radius: 8px;
    padding: 16px;
    margin-top: 20px;
}
@media print {
    .no-print { display: none !important; }
    .quotation-sheet { border: none; box-shadow: none; padding: 0; width: 100%; max-width: 100%; }
    body { background: #fff !important; }
}
</style>

<!-- html2pdf Library for 1-Click PDF Generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('📄 Digital Quotation & Payment Plan Generator');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading no-print">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;'.__('Back to Lead View'), array('controller' => 'Leads', 'action' => 'leadview', $leadRow['id']), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('CRM Dashboard'), array('controller' => 'Leads', 'action' => 'dashboard'), array('escape' => false, 'class' => 'btn btn-primary'));?>
            <button class="btn btn-success" id="btnDownloadPdf" onclick="downloadQuotationPdf();"><i class="fa fa-file-pdf-o"></i> <?php echo __('Download PDF');?></button>
            <button class="btn btn-info" id="btnShareWhatsApp" onclick="shareQuotationWhatsApp();"><i class="fa fa-whatsapp"></i> <?php echo __('Share on WhatsApp');?></button>
            <button class="btn btn-default" onclick="window.print();"><i class="fa fa-print"></i> <?php echo __('Print');?></button>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <!-- Dynamic Quotation Configuration Calculator Controls -->
        <div class="quotation-builder-panel no-print">
            <h4 style="margin-top:0;font-weight:700;color:#334155;"><i class="fa fa-sliders"></i> <?php echo __('Quotation Configuration & Installment Calculator');?></h4>
            <div class="row">
                <div class="col-md-3">
                    <label><?php echo __('Villa / Property Type:');?></label>
                    <select id="calcVillaType" class="form-control">
                        <option value="5 Marla Executive Villa">5 Marla Executive Villa</option>
                        <option value="5 Marla Luxury Villa">5 Marla Luxury Villa</option>
                        <option value="10 Marla Premium Villa">10 Marla Premium Villa</option>
                        <option value="10 Marla Executive Villa">10 Marla Executive Villa</option>
                        <option value="1 Kanal Royal Villa">1 Kanal Royal Villa</option>
                        <option value="Custom Villa / Plot">Custom Villa / Plot</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label><?php echo __('Currency:');?></label>
                    <select id="calcCurrency" class="form-control">
                        <option value="PKR">PKR (Rs.)</option>
                        <option value="AED">AED (د.إ) - Overseas</option>
                        <option value="USD">USD ($) - Overseas</option>
                        <option value="SAR">SAR (﷼) - Overseas</option>
                        <option value="GBP">GBP (£) - Overseas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label><?php echo __('Total Price:');?></label>
                    <input type="number" id="calcTotalPrice" class="form-control" value="<?php echo $savedTotal;?>" step="50000">
                </div>
                <div class="col-md-3">
                    <label><?php echo __('Down Payment / Booking %:');?></label>
                    <div class="input-group">
                        <input type="number" id="calcDownPct" class="form-control" value="<?php echo $defaultDownPct;?>" min="5" max="80">
                        <span class="input-group-addon">%</span>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top:14px;">
                <div class="col-md-3">
                    <label><?php echo __('Confirmation (within 30 days) %:');?></label>
                    <div class="input-group">
                        <input type="number" id="calcConfirmPct" class="form-control" value="10" min="0" max="30">
                        <span class="input-group-addon">%</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label><?php echo __('Installment Duration:');?></label>
                    <select id="calcDuration" class="form-control">
                        <option value="1" <?php echo $defaultDuration == 1 ? 'selected' : '';?>>1 Year (12 Months)</option>
                        <option value="2" <?php echo $defaultDuration == 2 ? 'selected' : '';?>>2 Years (24 Months)</option>
                        <option value="3" <?php echo $defaultDuration == 3 ? 'selected' : '';?>>3 Years (36 Months)</option>
                        <option value="4" <?php echo $defaultDuration == 4 ? 'selected' : '';?>>4 Years (48 Months)</option>
                        <option value="5" <?php echo $defaultDuration == 5 ? 'selected' : '';?>>5 Years (60 Months)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label><?php echo __('Installment Frequency:');?></label>
                    <select id="calcFrequency" class="form-control">
                        <option value="monthly">Monthly Installments</option>
                        <option value="quarterly" selected>Quarterly (Every 3 Months)</option>
                        <option value="biannual">Bi-Annual (Every 6 Months)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label><?php echo __('On Possession %:');?></label>
                    <div class="input-group">
                        <input type="number" id="calcPossessionPct" class="form-control" value="10" min="5" max="30">
                        <span class="input-group-addon">%</span>
                    </div>
                </div>
            </div>

            <div style="margin-top:16px;">
                <?php echo $this->Form->create(false, array('url' => array('controller' => 'Leads', 'action' => 'quotation', $leadRow['id']), 'id' => 'saveQuotationForm', 'style' => 'display:inline-block;'));?>
                <input type="hidden" name="quotation_total" id="hiddenQuotTotal" value="<?php echo $savedTotal;?>">
                <input type="hidden" name="quotation_plan" id="hiddenQuotPlan" value="">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?php echo __('Save Quotation to Lead Record');?></button>
                <?php echo $this->Form->end();?>
                <button type="button" class="btn btn-primary" onclick="recalculatePlan();"><i class="fa fa-calculator"></i> <?php echo __('Recalculate Schedule');?></button>
            </div>
        </div>

        <!-- Printable / PDF Target Quotation Sheet -->
        <div id="quotationSheet" class="quotation-sheet">
            <!-- Header -->
            <div class="quotation-header">
                <div class="row">
                    <div class="col-xs-7">
                        <h2 style="margin:0 0 4px 0;font-weight:800;color:#0f172a;"><?php echo h($agencyName);?></h2>
                        <div style="font-size:12px;color:#64748b;line-height:1.4;">
                            <?php if($agencyAddress !== '') echo h($agencyAddress).'<br/>';?>
                            <?php if($agencyPhone !== '') echo __('Phone/Mobile: ').h($agencyPhone).' | ';?>
                            <?php if($agencyEmail !== '') echo __('Email: ').h($agencyEmail);?>
                        </div>
                    </div>
                    <div class="col-xs-5 text-right">
                        <div class="quotation-title"><?php echo __('OFFICIAL QUOTATION');?></div>
                        <div class="quotation-badge">Ref: WV-Q-<?php echo str_pad($leadRow['id'], 5, '0', STR_PAD_LEFT);?></div>
                        <div style="font-size:12px;color:#64748b;margin-top:4px;"><?php echo __('Date: ').date('d-M-Y');?></div>
                    </div>
                </div>
            </div>

            <!-- Client & Villa Details -->
            <div class="client-info-box">
                <div class="row">
                    <div class="col-xs-6">
                        <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;"><?php echo __('Prepared For:');?></div>
                        <div style="font-size:16px;font-weight:700;color:#0f172a;margin-top:2px;"><?php echo h($leadName);?></div>
                        <div style="font-size:13px;color:#334155;"><?php echo h($leadMobile);?> <?php if($leadEmail !== '') echo ' | '.h($leadEmail);?></div>
                    </div>
                    <div class="col-xs-6 text-right">
                        <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;"><?php echo __('Selected Property:');?></div>
                        <div style="font-size:16px;font-weight:700;color:#4f46e5;margin-top:2px;" id="dispVillaType">5 Marla Executive Villa</div>
                        <div style="font-size:14px;font-weight:800;color:#0f172a;" id="dispTotalPrice">PKR 15,000,000</div>
                    </div>
                </div>
            </div>

            <!-- Payment Schedule Table -->
            <h4 style="font-weight:700;color:#1e293b;margin-bottom:12px;"><i class="fa fa-calendar-check-o"></i> <?php echo __('Payment & Installment Schedule');?></h4>
            <div class="table-responsive">
                <table class="table table-bordered schedule-table" id="scheduleTable">
                    <thead>
                        <tr>
                            <th style="width:40px;text-align:center;">#</th>
                            <th><?php echo __('Payment Milestone');?></th>
                            <th style="text-align:center;width:140px;"><?php echo __('Due Date / Stage');?></th>
                            <th style="text-align:center;width:90px;"><?php echo __('Percent');?></th>
                            <th style="text-align:right;width:170px;"><?php echo __('Amount');?></th>
                        </tr>
                    </thead>
                    <tbody id="scheduleBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                    <tfoot>
                        <tr style="background:#f1f5f9;font-weight:800;font-size:14px;">
                            <td colspan="3" style="text-align:right;"><?php echo __('TOTAL INVESTMENT:');?></td>
                            <td style="text-align:center;">100%</td>
                            <td style="text-align:right;color:#059669;" id="dispTableTotal">PKR 15,000,000</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Terms & Banking Notes -->
            <div class="summary-box">
                <div style="font-size:12px;font-weight:700;color:#065f46;text-transform:uppercase;margin-bottom:4px;"><i class="fa fa-info-circle"></i> <?php echo __('Important Booking Terms:');?></div>
                <div style="font-size:11px;color:#047857;line-height:1.5;">
                    1. All payments must be made in favor of "<?php echo h($agencyName);?>" via Pay Order, Cross Cheque, or Direct Online Banking.<br/>
                    2. Overseas clients can remit directly into our designated Roshan Digital / FDD accounts with instant computerized receipts.<br/>
                    3. Possession will be handed over upon clearance of the final possession installment.<br/>
                    4. This quotation is valid for 15 days from the date of issuance.
                </div>
            </div>

            <div style="margin-top:30px;border-top:1px solid #cbd5e1;padding-top:14px;" class="row">
                <div class="col-xs-6">
                    <div style="font-size:11px;color:#94a3b8;"><?php echo __('Generated by CRM System - White Villas');?><br/><?php echo date('Y-m-d H:i A');?></div>
                </div>
                <div class="col-xs-6 text-right">
                    <div style="font-size:12px;font-weight:700;color:#475569;"><?php echo __('Authorized Sales Officer');?></div>
                    <div style="margin-top:20px;border-bottom:1px dashed #94a3b8;width:160px;display:inline-block;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var clientPhone = "<?php echo addslashes($leadMobile);?>";
var clientName = "<?php echo addslashes($leadName);?>";

function formatMoney(amount, currency) {
    return currency + ' ' + Number(amount).toLocaleString('en-US', {maximumFractionDigits: 0});
}

function recalculatePlan() {
    var villaType = $('#calcVillaType').val();
    var currency = $('#calcCurrency').val();
    var totalPrice = parseFloat($('#calcTotalPrice').val()) || 0;
    var downPct = parseFloat($('#calcDownPct').val()) || 0;
    var confirmPct = parseFloat($('#calcConfirmPct').val()) || 0;
    var durationYears = parseInt($('#calcDuration').val()) || 3;
    var freq = $('#calcFrequency').val();
    var possessionPct = parseFloat($('#calcPossessionPct').val()) || 0;

    $('#dispVillaType').text(villaType);
    $('#dispTotalPrice').text(formatMoney(totalPrice, currency));
    $('#dispTableTotal').text(formatMoney(totalPrice, currency));
    $('#hiddenQuotTotal').val(totalPrice);

    var downAmount = (totalPrice * downPct) / 100;
    var confirmAmount = (totalPrice * confirmPct) / 100;
    var possessionAmount = (totalPrice * possessionPct) / 100;

    var installmentPctRemaining = 100 - (downPct + confirmPct + possessionPct);
    var installmentAmountRemaining = totalPrice - (downAmount + confirmAmount + possessionAmount);

    var totalInstallments = 12;
    var intervalMonths = 3;
    var freqLabel = "Quarterly";

    if (freq === 'monthly') {
        totalInstallments = durationYears * 12;
        intervalMonths = 1;
        freqLabel = "Monthly";
    } else if (freq === 'quarterly') {
        totalInstallments = durationYears * 4;
        intervalMonths = 3;
        freqLabel = "Quarterly";
    } else if (freq === 'biannual') {
        totalInstallments = durationYears * 2;
        intervalMonths = 6;
        freqLabel = "Bi-Annual";
    }

    var eachInstallmentAmount = totalInstallments > 0 ? (installmentAmountRemaining / totalInstallments) : 0;
    var eachInstallmentPct = totalInstallments > 0 ? (installmentPctRemaining / totalInstallments) : 0;

    var rowsHtml = '';
    var rowCounter = 1;
    var today = new Date();

    // 1. Downpayment
    rowsHtml += '<tr class="milestone-row">' +
        '<td style="text-align:center;">' + rowCounter++ + '</td>' +
        '<td><strong>Booking / Down Payment</strong></td>' +
        '<td style="text-align:center;"><span class="label label-success">At Booking</span></td>' +
        '<td style="text-align:center;">' + downPct + '%</td>' +
        '<td style="text-align:right;font-weight:700;color:#059669;">' + formatMoney(downAmount, currency) + '</td>' +
        '</tr>';

    // 2. Confirmation
    if (confirmPct > 0) {
        var confDate = new Date(today);
        confDate.setDate(confDate.getDate() + 30);
        rowsHtml += '<tr class="milestone-row">' +
            '<td style="text-align:center;">' + rowCounter++ + '</td>' +
            '<td><strong>Confirmation Amount</strong></td>' +
            '<td style="text-align:center;">Within 30 Days</td>' +
            '<td style="text-align:center;">' + confirmPct + '%</td>' +
            '<td style="text-align:right;font-weight:700;">' + formatMoney(confirmAmount, currency) + '</td>' +
            '</tr>';
    }

    // 3. Installments
    for (var i = 1; i <= totalInstallments; i++) {
        var instDate = new Date(today);
        instDate.setMonth(instDate.getMonth() + (i * intervalMonths));
        var dateStr = instDate.toLocaleDateString('en-US', {day:'2-digit', month:'short', year:'numeric'});

        rowsHtml += '<tr>' +
            '<td style="text-align:center;">' + rowCounter++ + '</td>' +
            '<td>Installment #' + i + ' (' + freqLabel + ')</td>' +
            '<td style="text-align:center;">' + dateStr + '</td>' +
            '<td style="text-align:center;">' + eachInstallmentPct.toFixed(2) + '%</td>' +
            '<td style="text-align:right;">' + formatMoney(eachInstallmentAmount, currency) + '</td>' +
            '</tr>';
    }

    // 4. Possession
    if (possessionPct > 0) {
        rowsHtml += '<tr class="milestone-row" style="background:#fef3c7;">' +
            '<td style="text-align:center;">' + rowCounter++ + '</td>' +
            '<td><strong>On Possession Handover</strong></td>' +
            '<td style="text-align:center;"><span class="label label-warning">At Possession</span></td>' +
            '<td style="text-align:center;">' + possessionPct + '%</td>' +
            '<td style="text-align:right;font-weight:700;color:#b45309;">' + formatMoney(possessionAmount, currency) + '</td>' +
            '</tr>';
    }

    $('#scheduleBody').html(rowsHtml);

    var planSummary = villaType + " | Total: " + formatMoney(totalPrice, currency) +
        " | Down: " + formatMoney(downAmount, currency) + " (" + downPct + "%)" +
        " | " + totalInstallments + " " + freqLabel + " Installments of " + formatMoney(eachInstallmentAmount, currency) +
        " | Possession: " + formatMoney(possessionAmount, currency);
    $('#hiddenQuotPlan').val(planSummary);
}

function downloadQuotationPdf() {
    var element = document.getElementById('quotationSheet');
    var opt = {
        margin: [10, 10, 10, 10],
        filename: 'Quotation_' + clientName.replace(/[^a-zA-Z0-9]/g, '_') + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(element).save();
}

function shareQuotationWhatsApp() {
    var cleanNumber = clientPhone.replace(/[^0-9]/g, '');
    if (cleanNumber.startsWith('03')) {
        cleanNumber = '92' + cleanNumber.substring(1);
    }
    var villaType = $('#calcVillaType').val();
    var totalPrice = $('#dispTotalPrice').text();
    var planText = $('#hiddenQuotPlan').val();

    var msg = "*🏡 WHITE VILLAS - OFFICIAL QUOTATION*\n" +
        "Dear *" + clientName + "*,\n\n" +
        "Thank you for your interest. Here is your customized quotation and payment plan:\n\n" +
        "📌 *Property:* " + villaType + "\n" +
        "💰 *Total Price:* " + totalPrice + "\n" +
        "📊 *Plan:* " + planText + "\n\n" +
        "Would you like to schedule a site visit or confirm your booking?\n" +
        "Best regards,\nWhite Villas Sales Team";

    var waUrl = "https://api.whatsapp.com/send?phone=" + encodeURIComponent(cleanNumber) + "&text=" + encodeURIComponent(msg);
    window.open(waUrl, '_blank');
}

$(document).ready(function(){
    recalculatePlan();
    $('#calcTotalPrice, #calcDownPct, #calcConfirmPct, #calcDuration, #calcFrequency, #calcPossessionPct, #calcVillaType, #calcCurrency').on('change keyup', function(){
        recalculatePlan();
    });
});
</script>
