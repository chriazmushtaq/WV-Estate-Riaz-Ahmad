<?php
echo $this->Html->css('print.css');
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array(
    'update' => '#resultDiv',
    'evalScripts' => true,
));
$clientLedgerPreview = !empty($clientLedgerPreview);

$firstRow = !empty($dealPayment[0]) ? $dealPayment[0] : (!empty($dealHeaderRow) ? $dealHeaderRow : array());
$receiptNo = !empty($firstRow['DealsPayment']['receipt_no']) ? $firstRow['DealsPayment']['receipt_no'] : (!empty($firstRow['Deal']['invoice_no']) ? $firstRow['Deal']['invoice_no'] : 'ledger');
$safeReceiptNo = preg_replace('/[^A-Za-z0-9_-]/', '_', $receiptNo);
$url = $this->Html->url(array('controller' => 'deals_payments'));
$dealPropertyId = !empty($firstRow['Deal']['property_id']) ? (int)$firstRow['Deal']['property_id'] : 0;
$paymentPlanTabUrl = $this->Html->url(array('controller'=>'plans_payments','action'=>'index',$dealId));
$documentTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'documents',$dealId));
$lettersTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'letters',$dealId));
$communicationTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'communication',$dealId));

$resolveImage = function($candidates) {
    foreach ($candidates as $candidate) {
        if (!is_string($candidate)) {
            continue;
        }
        $candidate = trim(str_replace('\\', '/', $candidate), '/');
        if ($candidate === '') {
            continue;
        }
        $filePath = WWW_ROOT . 'img' . DS . str_replace('/', DS, $candidate);
        if (file_exists($filePath)) {
            return $candidate;
        }
    }
    return '';
};

$agencyName = !empty($firstRow['Project']['name']) ? $firstRow['Project']['name'] : $siteOrganization;
$agencyAddress = !empty($firstRow['Project']['address']) ? $firstRow['Project']['address'] : $siteAddress;
$agencyPhone = !empty($firstRow['Project']['phone']) ? $firstRow['Project']['phone'] : '';
$agencyMobile = !empty($firstRow['Project']['mobile']) ? $firstRow['Project']['mobile'] : '';
$agencyEmail = !empty($firstRow['Project']['email']) ? $firstRow['Project']['email'] : '';

$agencyLogo = $resolveImage(array(
    !empty($agencyLogoFile) ? 'user_thumb/' . $agencyLogoFile : '',
    !empty($agencyLogoFile) ? $agencyLogoFile : '',
    !empty($firstRow['Project']['logo']) ? 'agency_thumb/' . $firstRow['Project']['logo'] : '',
    !empty($firstRow['Project']['logo']) ? $firstRow['Project']['logo'] : '',
    !empty($firstRow['Project']['image']) ? 'agency_thumb/' . $firstRow['Project']['image'] : '',
    !empty($firstRow['Project']['image']) ? $firstRow['Project']['image'] : '',
    !empty($firstRow['Project']['photo']) ? 'agency_thumb/' . $firstRow['Project']['photo'] : '',
    !empty($firstRow['Project']['photo']) ? $firstRow['Project']['photo'] : '',
));

$clientPhoto = '';
if (!empty($firstRow['Client']['photo'])) {
    $photo = $firstRow['Client']['photo'];
    $clientPhoto = $resolveImage(array(
        !empty($firstRow['Client']['dir']) ? trim($firstRow['Client']['dir'], '/') . '_thumb/' . $photo : '',
        !empty($firstRow['Client']['dir']) ? trim($firstRow['Client']['dir'], '/') . '/' . $photo : '',
        !empty($firstRow['Client']['dirt']) ? trim($firstRow['Client']['dirt'], '/') . '_thumb/' . $photo : '',
        !empty($firstRow['Client']['dirt']) ? trim($firstRow['Client']['dirt'], '/') . '/' . $photo : '',
        'client_thumb/' . $photo,
        'contact_thumb/' . $photo,
        'user_thumb/' . $photo,
        $photo
    ));
}
if (strlen($clientPhoto) <= 0) {
    $clientPhoto = $resolveImage(array('User.png'));
}

$areaLabel = trim((!empty($firstRow['Property']['area']) ? $firstRow['Property']['area'] : '') . ' ' . (!empty($firstRow['Unit']['name']) ? $firstRow['Unit']['name'] : ''));
$allotmentUnitNumberLabel = isset($allottedUnitNumber) ? trim((string)$allottedUnitNumber) : '';
$hasAllotmentUnitNumber = (strlen($allotmentUnitNumberLabel) > 0);
$reportDate = date('d-M-Y H:i:s');

$nomineeName = !empty($firstRow['Deal']['nominee_name']) ? $firstRow['Deal']['nominee_name'] : '-';
$nomineeCnic = !empty($firstRow['Deal']['nominee_cnic']) ? $firstRow['Deal']['nominee_cnic'] : '-';
$nomineeMobile = !empty($firstRow['Deal']['nominee_mobile']) ? $firstRow['Deal']['nominee_mobile'] : '-';
$nomineeRelation = !empty($firstRow['Deal']['nominee_relation']) ? $firstRow['Deal']['nominee_relation'] : '-';

$clientName = !empty($firstRow['Client']['name']) ? $firstRow['Client']['name'] : '-';
$clientFatherName = !empty($firstRow['Client']['fathername']) ? trim((string)$firstRow['Client']['fathername']) : '';
$clientRelationType = !empty($firstRow['Client']['relation_type']) ? trim((string)$firstRow['Client']['relation_type']) : 'Father';
$clientPhone = !empty($firstRow['Client']['phone']) ? $firstRow['Client']['phone'] : '-';
$clientContactNumbers = array();
foreach (array('phone', 'mobile') as $clientContactField) {
    if (!empty($firstRow['Client'][$clientContactField])) {
        $clientContactValue = trim((string)$firstRow['Client'][$clientContactField]);
        if ($clientContactValue !== '' && $clientContactValue !== '-' && !in_array($clientContactValue, $clientContactNumbers, true)) {
            $clientContactNumbers[] = $clientContactValue;
        }
    }
}
$clientMobileDisplay = !empty($clientContactNumbers) ? implode(' / ', $clientContactNumbers) : '-';
$clientCnic = !empty($firstRow['Client']['pincode']) ? $firstRow['Client']['pincode'] : '-';
$clientAddress = !empty($firstRow['Client']['address']) ? $firstRow['Client']['address'] : '-';
$membershipNo = !empty($firstRow['Deal']['invoice_no']) ? $firstRow['Deal']['invoice_no'] : '-';
$ledgerPdfClientName = preg_replace('/[^A-Za-z0-9]+/', '_', trim((string)$clientName));
$ledgerPdfClientName = trim($ledgerPdfClientName, '_');
if ($ledgerPdfClientName === '') {
    $ledgerPdfClientName = 'Client';
}
$ledgerPdfMembershipDigits = preg_replace('/\D+/', '', (string)$membershipNo);
if ($ledgerPdfMembershipDigits !== '') {
    $ledgerPdfMembershipLast4 = substr($ledgerPdfMembershipDigits, -4);
} else {
    $ledgerPdfMembershipClean = preg_replace('/[^A-Za-z0-9]+/', '', (string)$membershipNo);
    $ledgerPdfMembershipLast4 = $ledgerPdfMembershipClean !== '' ? substr($ledgerPdfMembershipClean, -4) : (string)$dealId;
}
$ledgerPdfFilename = $ledgerPdfClientName . '-' . $ledgerPdfMembershipLast4 . '.pdf';
$bookingDateLabel = !empty($firstRow['Deal']['date']) ? $this->Time->format($sysDay . $dateSep . $sysMonth . $dateSep . $sysYear, $firstRow['Deal']['date']) : '-';
$headerCenterContactParts = array();
if (strlen($agencyMobile) > 0) { $headerCenterContactParts[] = __('Mobile') . ': ' . $agencyMobile; }
if (strlen($agencyEmail) > 0) { $headerCenterContactParts[] = __('Email') . ': ' . $agencyEmail; }
if (empty($headerCenterContactParts) && strlen($agencyPhone) > 0) { $headerCenterContactParts[] = __('Phone') . ': ' . $agencyPhone; }
$dealStatusLabel = '-';
if (!empty($firstRow['Deal']['deal_status'])) {
    $dealStatusLabel = $firstRow['Deal']['deal_status'];
} elseif (!empty($firstRow['Deal']['status'])) {
    $dealStatusLabel = $firstRow['Deal']['status'];
}
$dealStatusColor = '#1f3f5f';
$dealStatusKey = strtolower(trim((string)$dealStatusLabel));
if (strpos($dealStatusKey, 'active') !== false) {
    $dealStatusColor = '#2e7d32';
} elseif (strpos($dealStatusKey, 'refund') !== false) {
    $dealStatusColor = '#f0ad4e';
} elseif (strpos($dealStatusKey, 'transfer') !== false || strpos($dealStatusKey, 'block') !== false || strpos($dealStatusKey, 'cancel') !== false || strpos($dealStatusKey, 'close') !== false) {
    $dealStatusColor = '#d9534f';
}
$qrPropertyFile = !empty($firstRow['Property']['name']) ? $firstRow['Property']['name'] : '-';
$qrPropertyType = !empty($firstRow['Type']['name']) ? $firstRow['Type']['name'] : (!empty($firstRow['Property']['type']) ? $firstRow['Property']['type'] : '-');
$qrContractType = '-';
foreach (array('contract', 'contract_type', 'available_for', 'available') as $contractFieldKey) {
    if (!empty($firstRow['Property'][$contractFieldKey])) {
        $qrContractType = $firstRow['Property'][$contractFieldKey];
        break;
    }
}
$qrPropertyArea = strlen($areaLabel) > 0 ? $areaLabel : '-';
$qrPropertyTypeDisplay = $qrPropertyType;
if (!empty($firstRow['Type']['name'])) {
    $qrPropertyTypeDisplay = $firstRow['Type']['name'];
}
$qrContractTypeDisplay = !empty($firstRow['Contract']['name']) ? $firstRow['Contract']['name'] : '-';
if ($qrContractTypeDisplay === '-' && $qrContractType !== '-') {
    $qrContractTypeDisplay = $qrContractType;
}
$qrContactParts = array();
if (strlen(trim($agencyMobile)) > 0) {
    $qrContactParts[] = __('Mobile') . ': ' . $agencyMobile;
}
if (strlen(trim($agencyPhone)) > 0) {
    $qrContactParts[] = __('Phone') . ': ' . $agencyPhone;
}
$qrContactLine = implode(' | ', $qrContactParts);
if (strlen(trim($qrContactLine)) <= 0) {
    $qrContactLine = '-';
}
$dealQrLines = array(
    strtoupper(__('Agency Details')),
    $agencyName,
    __('Contact') . ': ' . $qrContactLine,
    '',
    strtoupper(__('Deal Details')),
    __('Membership No') . ': ' . $membershipNo,
    __('Client Name') . ': ' . $clientName,
    __('CNIC') . ': ' . $clientCnic,
    __('Deal Status') . ': ' . $dealStatusLabel,
    '',
    strtoupper(__('Property Details')),
    __('Property File #') . ': ' . $qrPropertyFile,
    __('Property Area') . ': ' . $qrPropertyArea,
    __('Property Type') . ': ' . $qrPropertyTypeDisplay,
    __('Contract Type') . ': ' . $qrContractTypeDisplay
);
$dealQrText = implode("\n", $dealQrLines);
$dealQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&ecc=L&qzone=2&margin=8&format=png&charset-source=UTF-8&charset-target=UTF-8&data=' . rawurlencode($dealQrText) . '&v=' . rawurlencode((string)$dealId) . '&cb=' . rawurlencode(date('YmdHis'));

$dealChargeSummary = (isset($dealChargeSummary) && is_array($dealChargeSummary)) ? $dealChargeSummary : array();
$dealChargeCount = isset($dealChargeSummary['charge_count']) ? (int)$dealChargeSummary['charge_count'] : 0;
$dealChargeTotal = isset($dealChargeSummary['charge_total']) ? (float)$dealChargeSummary['charge_total'] : 0;
$hasDealCharges = ($dealChargeCount > 0 && round((float)$dealChargeTotal, 2) > 0);
$dealChargeNamesRaw = isset($dealChargeSummary['charge_names']) ? trim((string)$dealChargeSummary['charge_names']) : '';
$dealBaseAmount = isset($dealChargeSummary['base_amount']) ? (float)$dealChargeSummary['base_amount'] : 0;
$dealTotalAmountCard = isset($dealChargeSummary['grand_total']) && (float)$dealChargeSummary['grand_total'] > 0
    ? (float)$dealChargeSummary['grand_total']
    : (isset($firstRow['Deal']['total_amount']) ? (float)$firstRow['Deal']['total_amount'] : 0);
$dealGrandAmount = $dealTotalAmountCard;
if($dealGrandAmount < 0)
{
    $dealGrandAmount = 0;
}
$chargeTypeLabel = ($dealChargeNamesRaw !== '') ? $dealChargeNamesRaw : __('N/A');
$dealDiscountCardRaw = isset($firstRow['Deal']['discount']) ? (float)$firstRow['Deal']['discount'] : 0;
$propertyPriceCard = isset($firstRow['Property']['price']) ? (float)$firstRow['Property']['price'] : 0;
if($propertyPriceCard <= 0)
{
    $propertyPriceCard = round((float)$dealTotalAmountCard + (float)$dealDiscountCardRaw - (float)$dealChargeTotal, 2);
}
if($propertyPriceCard < 0)
{
    $propertyPriceCard = 0;
}
$propertyPriceCard = round($propertyPriceCard,2);
$dealDiscountAmountCard = round(max(0, (float)$dealDiscountCardRaw), 2);
$hasDealDiscount = ($dealDiscountAmountCard > 0);
$propertyCardTitle = __('Property Price');
$metricCardsCount = 5 + ($hasDealCharges ? 1 : 0) + ($hasDealDiscount ? 1 : 0);
$metricCardsClass = 'metric-count-' . (int)$metricCardsCount;
$paidEquity = 0;
foreach($dealPayment as $paymentRow)
{
    $paidEquity += isset($paymentRow['DealsPayment']['emi']) ? (float)$paymentRow['DealsPayment']['emi'] : 0;
}
$receiptGroups = array();
foreach((array)$dealPayment as $paymentRow)
{
    $rowId = isset($paymentRow['DealsPayment']['id']) ? (int)$paymentRow['DealsPayment']['id'] : 0;
    if($rowId<=0)
    {
        continue;
    }
    $receiptNoKey = isset($paymentRow['DealsPayment']['receipt_no']) ? trim((string)$paymentRow['DealsPayment']['receipt_no']) : '';
    if($receiptNoKey === '')
    {
        $receiptNoKey = 'ROW-'.$rowId;
    }
    if(!isset($receiptGroups[$receiptNoKey]))
    {
        $receiptGroups[$receiptNoKey] = array(
            'receipt_key' => $receiptNoKey,
            'first_row' => $paymentRow,
            'first_id' => $rowId,
            'ids' => array(),
            'paid_amount' => 0,
            'late_fees' => 0,
            'installment_late_amount' => 0,
            'payment_names' => array(),
            'slip_row_id' => $rowId,
            'slip_file' => '',
            'slip_dir' => 'paymentslips',
            'slip_is_image' => 0
        );
    }
    $receiptGroups[$receiptNoKey]['ids'][] = $rowId;
    $rowPaidAmount = isset($paymentRow['DealsPayment']['emi']) ? (float)$paymentRow['DealsPayment']['emi'] : 0;
    $rowLateFees = isset($paymentRow['DealsPayment']['extra_payment']) ? (float)$paymentRow['DealsPayment']['extra_payment'] : 0;
    $receiptGroups[$receiptNoKey]['paid_amount'] += $rowPaidAmount;
    $receiptGroups[$receiptNoKey]['late_fees'] += $rowLateFees;
    $rowPaymentName = !empty($paymentRow['PlansPayment']['name']) ? trim((string)$paymentRow['PlansPayment']['name']) : '';
    if($rowPaymentName === '' && !empty($paymentRow['DealsPayment']['name']))
    {
        $rowPaymentName = trim((string)$paymentRow['DealsPayment']['name']);
    }
    if($rowPaymentName !== '' && !in_array($rowPaymentName, $receiptGroups[$receiptNoKey]['payment_names'], true))
    {
        $receiptGroups[$receiptNoKey]['payment_names'][] = $rowPaymentName;
    }
    if(!empty($paymentRow['DealsPayment']['payment_slip']))
    {
        $receiptGroups[$receiptNoKey]['slip_row_id'] = $rowId;
        if($receiptGroups[$receiptNoKey]['slip_file'] === '')
        {
            $receiptGroups[$receiptNoKey]['slip_file'] = trim((string)$paymentRow['DealsPayment']['payment_slip']);
            $receiptGroups[$receiptNoKey]['slip_dir'] = !empty($paymentRow['DealsPayment']['payment_slip_dir']) ? trim((string)$paymentRow['DealsPayment']['payment_slip_dir']) : 'paymentslips';
            $receiptGroups[$receiptNoKey]['slip_is_image'] = !empty($paymentRow['DealsPayment']['payment_slip_is_image']) ? 1 : 0;
        }
    }
}
foreach($receiptGroups as $groupKey => $groupData)
{
    $receiptGroups[$groupKey]['paid_amount'] = round((float)$groupData['paid_amount'],2);
    $receiptGroups[$groupKey]['late_fees'] = round((float)$groupData['late_fees'],2);
    $receiptGroups[$groupKey]['installment_late_amount'] = round((float)$groupData['paid_amount'] + (float)$groupData['late_fees'],2);
    $receiptGroups[$groupKey]['id_list'] = implode(',', array_unique((array)$groupData['ids']));
}
$displayPaymentRows = array_values($receiptGroups);
$balanceDueCard = round($dealTotalAmountCard - $paidEquity,2);
$dealTotalAmountCard = round($dealTotalAmountCard,2);
$overdueAmountCard = isset($overdueAmount) ? (float)$overdueAmount : 0;
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

<style type="text/css">
.ledger-wrap {
    background: #f4f6f9;
    padding: 4px 0 16px;
}
.ledger-page {
    width: 210mm;
    max-width: calc(100% - 18px);
    margin: 0;
}
.ledger-card {
    background: #fff;
    border: 1px solid #dfe5ec;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    padding: 6px 12px 10px;
}
.ledger-header {
    --qr-size: 1in;
    --qr-box-pad: 4px;
    --qr-box-border: 1px;
    --qr-total-height: calc(var(--qr-size) + (var(--qr-box-pad) * 2) + (var(--qr-box-border) * 2));
    border-bottom: none;
    padding-bottom: 0;
    margin-bottom: 0;
    display: grid;
    width: 100%;
    grid-template-columns: var(--qr-total-height) minmax(0, 1fr) auto;
    align-items: center;
    column-gap: 12px;
}
.agency-col {
    display: contents;
}
.deal-qr-col {
    display: block;
    width: auto;
    text-align: right;
    justify-self: end;
    align-self: center;
    padding-top: 2px;
}
.deal-qr-wrap {
    display: inline-flex;
    align-items: stretch;
    gap: 6px;
}
.deal-qr-box {
    display: inline-block;
    border: 1px solid #dce3eb;
    border-radius: 4px;
    background: #fff;
    padding: 4px;
    box-sizing: border-box;
    height: var(--qr-total-height);
}
.deal-qr-box img {
    width: var(--qr-size);
    height: var(--qr-size);
    display: block;
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
}
.deal-qr-caption {
    margin-top: 0;
    font-size: 10px;
    color: #5d6d7d;
    font-weight: 600;
    line-height: 1;
    height: var(--qr-total-height);
    display: flex;
    align-items: center;
    justify-content: center;
    writing-mode: vertical-rl;
    text-orientation: mixed;
    white-space: normal;
}
.agency-logo-box {
    float: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: var(--qr-total-height);
    min-height: var(--qr-total-height);
    text-align: center;
    margin-right: 0;
    vertical-align: middle;
}
.agency-logo-box img {
    width: var(--qr-size);
    height: var(--qr-size);
    max-height: none;
    max-width: none;
    object-fit: contain;
    display: block;
}
.agency-details {
    display: block;
    vertical-align: middle;
    width: auto;
    overflow: hidden;
    text-align: center;
    margin: 0 auto;
}
.agency-details h2 {
    margin: 0 0 3px;
    color: #1f2d3d;
    font-size: 22px;
    font-weight: 800;
    line-height: 1.08;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.agency-details p {
    margin: 0 0 2px;
    color: #4d5b69;
    font-size: 11px;
}
.agency-contact-line {
    color: #204a73;
    font-size: 11px;
    font-weight: 600;
}
.deal-status-line {
    margin: 12px 0 0;
    color: #1f3f5f;
    font-size: 15px;
    font-weight: 700;
    line-height: 1.25;
}
.deal-status-value {
    font-weight: 800;
}
.client-photo-col {
    display: table-cell;
    width: 32%;
    text-align: right;
    vertical-align: middle;
}
.client-photo-col img {
    width: 78px;
    height: 78px;
    object-fit: cover;
    border: 1px solid #d6dde6;
    border-radius: 6px;
}
.client-photo-col .photo-meta {
    margin-top: 6px;
    text-align: left;
}
.client-photo-col .photo-meta-row {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 16px;
    max-width: 100%;
    flex-wrap: wrap;
}
.client-photo-col .photo-meta-line {
    font-size: 11px;
    color: #1f3f5f;
    font-weight: 700;
    line-height: 1.35;
    white-space: nowrap;
}
.ledger-title {
    margin: 0;
    text-align: center;
    font-size: 20px;
    color: #20344a;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
}
.ledger-title-row {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 10px;
    margin: 0 0 8px;
}
.ledger-title-meta-left,
.ledger-title-meta-right {
    font-size: 11px;
    color: #1f3f5f;
    font-weight: 700;
    line-height: 1.35;
}
.ledger-title-meta-right {
    text-align: right;
}
.detail-grid {
    display: flex;
    flex-wrap: nowrap;
    align-items: stretch;
    width: 100%;
    gap: 1px;
    margin-bottom: 8px;
}
.detail-card {
    display: block;
    border: 1px solid #dce3eb;
    background: #fbfcfe;
    padding: 8px;
    vertical-align: top;
    min-width: 0;
}
.ledger-title-row {
    background: linear-gradient(135deg,#f8fbff 0%,#eef6ff 100%);
    border: 1px solid #d7e4f2;
    border-radius: 8px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.85);
    padding: 8px 12px;
}
.ledger-title {
    color: #07152a;
    font-size: 23px;
    font-weight: 900;
    letter-spacing: 1px;
}
.ledger-title-meta-left,
.ledger-title-meta-right {
    color: #0b3762;
    font-size: 11px;
    font-weight: 800;
}
.detail-grid {
    gap: 8px;
}
.detail-card {
    background: linear-gradient(180deg,#ffffff 0%,#f9fcff 100%);
    border: 1px solid #d7e4f2;
    border-radius: 9px;
    box-shadow: 0 10px 22px rgba(15,35,61,.07);
    overflow: hidden;
    padding: 10px 10px 11px;
    position: relative;
}
.detail-card:before {
    background: linear-gradient(180deg,#ff0a46 0%,#7cf9ff 100%);
    bottom: 0;
    content: "";
    left: 0;
    position: absolute;
    top: 0;
    width: 3px;
}
.detail-card h4 {
    align-items: center;
    border-bottom: 1px solid #dfeaf5;
    color: #07152a;
    display: flex;
    font-size: 11px;
    font-weight: 900;
    gap: 7px;
    letter-spacing: .2px;
    margin: 0 0 9px;
    padding: 0 0 7px 2px;
}
.detail-card h4:before {
    background: #ff0a46;
    border-radius: 999px;
    box-shadow: 0 0 0 3px rgba(255,10,70,.10);
    content: "";
    display: inline-block;
    height: 7px;
    width: 7px;
}
.client-detail-body {
    gap: 12px;
}
.client-detail-photo {
    flex: 0 0 .92in;
}
.client-detail-photo img {
    background: #eef4fb;
    border: 1px solid #cfddec;
    border-radius: 10px;
    box-shadow: 0 8px 18px rgba(15,35,61,.12);
    height: 1.22in;
    padding: 2px;
    width: .92in;
}
.detail-item {
    background: rgba(248,251,255,.74);
    border: 1px solid #eef3f8;
    border-radius: 6px;
    margin-bottom: 5px;
    padding: 4px 6px;
}
.detail-label {
    color: #64748b;
    font-size: 9px;
    font-weight: 900;
    letter-spacing: .25px;
}
.detail-value {
    color: #172a3f;
    font-size: 11px;
    font-weight: 800;
    line-height: 1.28;
}
.detail-card-client {
    flex: 1.66 1 0;
}
.detail-card-nominee {
    flex: 0.84 1 0;
}
.detail-card-property {
    flex: 1.10 1 0;
}
.payment-slip-mini{
    display:inline-flex;
    align-items:center;
    gap:6px;
}
.payment-slip-mini img{
    width:46px;
    height:46px;
    object-fit:cover;
    border:1px solid #d9e1e8;
    border-radius:4px;
}
.detail-card h4 {
    margin: 0 0 6px;
    font-size: 12px;
    color: #1f4f78;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    border-bottom: 1px solid #e1e7ef;
    padding-bottom: 4px;
}
.client-detail-body {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.client-detail-photo {
    flex: 0 0 1in;
    text-align: center;
}
.client-detail-photo img {
    width: 1in;
    height: 1.30in;
    object-fit: cover;
    border: 1px solid #d6dde6;
    border-radius: 6px;
}
.client-detail-content {
    flex: 1 1 auto;
    min-width: 0;
}
.detail-card-nominee h4,
.detail-card-property h4 {
    white-space: nowrap;
}
.detail-item {
    margin-bottom: 4px;
}
.detail-item:last-child {
    margin-bottom: 0;
}
.detail-item-dual {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}
.detail-item-dual .detail-inline-col {
    flex: 1 1 50%;
    min-width: 0;
}
.detail-item-dual .detail-inline-col-right {
    text-align: right;
}
.detail-item-dual .detail-inline-col-right .detail-label,
.detail-item-dual .detail-inline-col-right .detail-value {
    text-align: right;
}
.detail-label {
    display: block;
    font-size: 10px;
    color: #6a7785;
    font-weight: 700;
    text-transform: uppercase;
    line-height: 1.2;
}
.detail-value {
    display: block;
    margin-top: 1px;
    font-size: 11px;
    color: #1f2f40;
    font-weight: 600;
    line-height: 1.25;
    word-break: break-word;
}
.ledger-section-title {
    margin: 0 0 6px;
    color: #1f3f5f;
    font-size: 14px;
    font-weight: 700;
    border-bottom: 1px solid #d8e1ea;
    padding-bottom: 4px;
    text-align: center;
}
.ledger-nav-tabs {
    margin: 0 0 8px;
    border-bottom: 1px solid #d8e1ea;
}
.ledger-nav-tabs > li > a {
    font-size: 12px;
    font-weight: 600;
    padding: 7px 14px;
    color: #2d4e6e;
}
.ledger-nav-tabs > li.active > a,
.ledger-nav-tabs > li.active > a:hover,
.ledger-nav-tabs > li.active > a:focus {
    color: #1f2f40;
    border: 1px solid #d8e1ea;
    border-bottom-color: transparent;
    background: #fff;
}
.ledger-tab-pane {
    display: none;
}
.ledger-tab-pane.active {
    display: block;
}
.ledger-tab-dynamic {
    border: 1px solid #dce3eb;
    background: #fff;
    padding: 10px;
}
.ledger-tab-dynamic .js-agreed-plan-title {
    margin: 0 0 2px !important;
    padding-bottom: 2px;
}
.ledger-tab-dynamic .panel-body {
    padding: 0 !important;
    margin: 0 !important;
    min-height: 0 !important;
}
.ledger-tab-dynamic .panel-body:empty {
    display: none !important;
}
.ledger-tab-dynamic .table-responsive {
    margin-top: 0 !important;
}
.ledger-tab-loading {
    padding: 22px 0;
    text-align: center;
    color: #4d6073;
    font-size: 13px;
    font-weight: 600;
}
.ledger-metrics {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -5px 8px;
}
.metric-card {
    width: 25%;
    padding: 0 5px;
    margin-bottom: 8px;
}
.ledger-metrics.metric-count-5 .metric-card { width: 20%; }
.ledger-metrics.metric-count-6 .metric-card { width: 16.6667%; }
.ledger-metrics.metric-count-7 .metric-card { width: 14.2857%; }
.metric-card-inner {
    border: 1px solid #dce3eb;
    border-radius: 8px;
    min-height: 78px;
    padding: 9px 8px 7px;
    text-align: center;
    background: #f7f9fc;
}
.metric-title {
    font-size: 10px;
    color: #5b6a79;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    margin-bottom: 5px;
}
.metric-note {
    font-size: 10px;
    color: #5b6a79;
    line-height: 1.2;
    margin-bottom: 4px;
    min-height: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.metric-value {
    font-size: 18px;
    color: #1f2f40;
    font-weight: 700;
    line-height: 1.2;
    white-space: nowrap;
    overflow: visible;
    direction: ltr;
    unicode-bidi: isolate;
}
.metric-net .metric-card-inner { background: #f7f9fc; }
.metric-charge .metric-card-inner { background: #fff1df; border-color: #f3d9b0; }
.metric-property .metric-card-inner { background: #edf8ff; border-color: #d3eaf9; }
.metric-discount .metric-card-inner { background: #fff6dc; border-color: #f3e0a9; }
.metric-total .metric-card-inner { background: #eee7ff; border-color: #d9c9ff; }
.metric-paid .metric-card-inner { background: #d9f1e1; border-color: #b9dfc8; }
.metric-balance .metric-card-inner { background: #dce9f9; border-color: #bfd4f1; }
.metric-overdue .metric-card-inner { background: #f7dfdf; border-color: #ebc2c2; }
.ledger-table {
    width: 100% !important;
    table-layout: auto !important;
}
.ledger-table > thead > tr > th {
    background: #edf3f8;
    color: #223a52;
    font-size: 10px;
    font-weight: 700;
    padding: 5px 6px;
    white-space: nowrap;
}
.ledger-table > tbody > tr > td {
    font-size: 10px;
    color: #27394d;
    padding: 5px 6px;
    vertical-align: top;
}
.ledger-table > tbody > tr > td:nth-child(5),
.ledger-table > tbody > tr > td:nth-child(8) {
    white-space: normal;
    word-break: break-word;
}
.ledger-table .summary-row td {
    background: #f2f6fb;
    font-weight: 700;
}
.currency-ltr {
    display: inline-block;
    direction: ltr;
    unicode-bidi: isolate;
    white-space: nowrap;
}
.currency-symbol {
    font-size: 65%;
    margin-right: 2px;
    vertical-align: baseline;
}
.currency-symbol img {
    height: 0.65em;
    width: auto;
    vertical-align: baseline;
}
.ledger-footer {
    margin-top: 6px;
    font-size: 10px;
    color: #4f5f71;
    border-top: 1px solid #d8e1ea;
    padding-top: 5px;
}

/* Deal Document tab */
.deal-documents-wrap {
    background: #f6f9fc;
    border: 1px solid #dbe4ee;
    padding: 12px;
}
.deal-documents-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 10px;
}
.deal-documents-meta {
    font-size: 12px;
    color: #31465b;
}
.deal-doc-upload-panel {
    border: 1px solid #dbe4ee;
    background: #fff;
    padding: 10px;
    margin-bottom: 10px;
}
#dealDocumentUploadModal .deal-doc-upload-panel {
    border: 0;
    background: transparent;
    padding: 0;
    margin-bottom: 0;
}
.deal-doc-drop-upload {
    border: 2px dashed #9bb0c4;
    background: #f9fbfd;
    border-radius: 6px;
    padding: 12px;
    text-align: center;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
    min-height: 130px;
}
.deal-doc-drop-upload.drag-over,
.deal-doc-drop-upload:hover {
    border-color: #2f6c9f;
    background: #eef5fb;
}
.deal-doc-browse-wrap {
    position: relative;
    overflow: hidden;
    cursor: pointer;
    margin-bottom: 0;
}
.deal-doc-browse-wrap input[type="file"] {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
.deal-doc-drop-message {
    font-size: 12px;
    color: #4b647e;
    margin-bottom: 8px;
}
.deal-doc-drop-preview {
    min-height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 6px;
}
.deal-doc-drop-preview img {
    max-height: 92px;
    max-width: 100%;
    border: 1px solid #d4dde8;
    border-radius: 4px;
}
.deal-doc-thumb {
    max-width: 75px;
    max-height: 75px;
    border: 1px solid #d4dde8;
    border-radius: 4px;
}
.deal-doc-action-group .btn {
    margin-right: 4px;
    margin-bottom: 3px;
}

/* Status Analysis Tab Styling */
.status-analysis-wrap {
    padding: 10px 0;
}
.status-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 10px;
}
.status-kpi-card {
    background: #ffffff;
    border: 1px solid #dfe7f1;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(15,23,42,.05);
    padding: 10px 12px;
    position: relative;
    overflow: hidden;
}
.status-kpi-card:before {
    position: absolute;
    right: -10px;
    top: -10px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    content: "";
    opacity: 0.12;
}
.status-kpi-card.kpi-overdue { border-left: 4px solid #dc2626; }
.status-kpi-card.kpi-overdue:before { background: #dc2626; }
.status-kpi-card.kpi-overdue strong { color: #dc2626 !important; }

.status-kpi-card.kpi-delay { border-left: 4px solid #f59e0b; }
.status-kpi-card.kpi-delay:before { background: #f59e0b; }
.status-kpi-card.kpi-delay strong { color: #d97706 !important; }

.status-kpi-card.kpi-score { border-left: 4px solid #10b981; }
.status-kpi-card.kpi-score:before { background: #10b981; }

.status-kpi-card.kpi-early { border-left: 4px solid #0284c7; }
.status-kpi-card.kpi-early:before { background: #0284c7; }
.status-kpi-card.kpi-early strong { color: #0284c7 !important; }

.status-kpi-card.kpi-ontime { border-left: 4px solid #059669; }
.status-kpi-card.kpi-ontime:before { background: #059669; }
.status-kpi-card.kpi-ontime strong { color: #059669 !important; }

.status-kpi-card.kpi-late { border-left: 4px solid #ef4444; }
.status-kpi-card.kpi-late:before { background: #ef4444; }
.status-kpi-card.kpi-late strong { color: #dc2626 !important; }

.status-kpi-card.kpi-total { border-left: 4px solid #6366f1; }
.status-kpi-card.kpi-total:before { background: #6366f1; }
.status-kpi-card.kpi-total strong { color: #4f46e5 !important; }

.status-kpi-card span {
    display: block;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 3px;
}
.status-kpi-card strong {
    display: block;
    font-size: 17px;
    font-weight: 900;
    color: #0f172a;
    line-height: 1.15;
}
.status-kpi-card small {
    display: block;
    font-size: 10px;
    font-weight: 700;
    color: #94a3b8;
    margin-top: 3px;
}

.sa-table {
    table-layout: auto !important;
    width: 100% !important;
    margin-bottom: 0 !important;
}
.sa-table > thead > tr > th {
    background: #f1f5f9 !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 10px !important;
    font-weight: 900 !important;
    padding: 5px 6px !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
    vertical-align: middle !important;
}
.sa-table > tbody > tr > td {
    border: 1px solid #e2e8f0 !important;
    font-size: 11px !important;
    padding: 4px 6px !important;
    vertical-align: middle !important;
}
.sa-table > tbody > tr:hover {
    background: #f8fafc !important;
}
.sa-table > tfoot > tr > td {
    border: 1px solid #cbd5e1 !important;
    padding: 5px 6px !important;
    font-size: 11px !important;
    vertical-align: middle !important;
}

.sa-badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    padding: 3px 7px !important;
    border-radius: 5px !important;
    font-size: 9.5px !important;
    font-weight: 900 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.3px !important;
    white-space: nowrap !important;
    box-shadow: 0 1px 3px rgba(0,0,0,.15) !important;
    color: #ffffff !important;
    line-height: 1.2 !important;
}
.sa-badge, .sa-badge *, .sa-badge i, .sa-badge span, .sa-badge strong, .sa-badge small {
    color: #ffffff !important;
    text-decoration: none !important;
}
.sa-badge-early { background: linear-gradient(135deg,#0284c7,#0369a1) !important; }
.sa-badge-ontime { background: linear-gradient(135deg,#10b981,#059669) !important; }
.sa-badge-late { background: linear-gradient(135deg,#ef4444,#dc2626) !important; }
.sa-badge-overdue { background: linear-gradient(135deg,#dc2626,#991b1b) !important; }
.sa-badge-duetoday { background: linear-gradient(135deg,#f59e0b,#d97706) !important; }
.sa-badge-upcoming { background: linear-gradient(135deg,#64748b,#475569) !important; }
.sa-badge-advance { background: linear-gradient(135deg,#6366f1,#4f46e5) !important; }

/* Professional layout refinement (view + print friendly) */
.ledger-wrap {
    background: #edf2f8;
    padding: 10px 0 18px;
}
.ledger-page {
    width: 210mm;
    max-width: calc(100% - 20px);
    margin: 0;
}
.ledger-card {
    border: 1px solid #d3dce7;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(16, 33, 55, 0.08);
    padding: 12px 14px 12px;
}
.ledger-header {
    display: grid;
    grid-template-columns: var(--qr-total-height) minmax(0, 1fr) auto;
    align-items: center;
    column-gap: 12px;
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}
.agency-col {
    display: contents;
}
.deal-qr-col {
    width: auto;
    justify-self: end;
    align-self: center;
    text-align: right;
}
.agency-logo-box {
    width: var(--qr-total-height);
    min-height: var(--qr-total-height);
    margin-right: 0;
}
.agency-logo-box img {
    width: var(--qr-size);
    height: var(--qr-size);
    max-height: none;
    max-width: none;
    object-fit: contain;
}
.agency-details {
    width: auto;
    text-align: center;
    margin: 0 auto;
}
.agency-details h2 {
    font-size: 24px;
    margin: 0 0 3px;
    color: #162b42;
    font-weight: 800;
}
.agency-details p {
    font-size: 11px;
    margin: 0 0 2px;
}
.agency-contact-line {
    color: #21466b;
    font-size: 11px;
    font-weight: 700;
}
.deal-qr-wrap {
    gap: 8px;
    flex-direction: column;
    align-items: center;
}
.deal-qr-box {
    border-radius: 6px;
    border-color: #cfd9e4;
}
.deal-qr-caption {
    margin-top: 0;
    font-size: 10px;
    color: #5d6d7d;
    font-weight: 700;
    line-height: 1.2;
    height: auto;
    writing-mode: horizontal-tb;
    text-orientation: mixed;
    white-space: nowrap;
    text-align: center;
}
.ledger-title-row {
    margin: 0 0 10px;
    padding: 6px 10px;
    border: 1px solid #d8e2ed;
    border-radius: 6px;
    background: #f8fbfe;
}
.ledger-title {
    font-size: 23px;
    letter-spacing: 0.7px;
    color: #17324f;
}
.ledger-title-meta-left,
.ledger-title-meta-right {
    font-size: 11px;
    color: #1b3d5f;
    font-weight: 700;
}
.detail-grid {
    display: flex;
    flex-wrap: nowrap;
    align-items: stretch;
    gap: 1px;
    margin-bottom: 10px;
}
.detail-card {
    display: block;
    background: #fbfdff;
    border: 1px solid #d9e3ee;
    border-radius: 8px;
    padding: 9px;
}
.detail-card h4 {
    margin: 0 0 7px;
    border-bottom: 1px solid #dbe5ef;
    padding-bottom: 4px;
    font-size: 12px;
    color: #1e4f7a;
    letter-spacing: 0.3px;
}
.client-detail-body {
    gap: 9px;
}
.client-detail-photo img {
    border-radius: 5px;
    border: 1px solid #cfd9e4;
}
.detail-label {
    font-size: 10px;
    color: #5a6a79;
}
.detail-value {
    font-size: 11px;
    color: #1f2f40;
    font-weight: 600;
    margin-top: 1px;
    line-height: 1.28;
}
.detail-item-split {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}
.detail-item-split .detail-split-col {
    flex: 1 1 50%;
    min-width: 0;
}
.detail-item-split .detail-split-col-right {
    text-align: right;
}
.detail-item-split .detail-split-col-right .detail-label,
.detail-item-split .detail-split-col-right .detail-value {
    text-align: right;
}
.detail-item-split.no-charge .detail-split-col {
    flex: 1 1 100%;
}
.ledger-nav-tabs {
    margin: 0 0 10px;
    border-bottom: 1px solid #d7e1ec;
}
.ledger-nav-tabs > li > a {
    padding: 7px 14px;
    border-radius: 6px 6px 0 0;
}
.ledger-nav-tabs > li.active > a,
.ledger-nav-tabs > li.active > a:hover,
.ledger-nav-tabs > li.active > a:focus {
    background: #f8fbff;
    border-color: #d8e3ee;
}
.ledger-metrics {
    margin: 0 -4px 9px;
}
.metric-card {
    padding: 0 4px;
    margin-bottom: 8px;
}
.metric-card-inner {
    min-height: 82px;
    border-radius: 8px;
    border: 1px solid #d3deea;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
}
.ledger-section-title {
    margin: 0 0 7px;
    font-size: 14px;
    color: #1c3f62;
    border-bottom: 1px solid #d8e3ee;
}
.table-responsive {
    border: 1px solid #d8e3ee;
    border-radius: 6px;
    overflow-x: auto;
}
.ledger-table {
    margin-bottom: 0;
}
.ledger-table > thead > tr > th {
    background: #edf4fb;
    border-color: #d6e0eb;
    color: #1e3a56;
    font-size: 10px;
    padding: 6px 6px;
}
.ledger-table > tbody > tr > td {
    border-color: #dfe7f0;
    font-size: 10px;
    padding: 6px 6px;
}
.ledger-table > tbody > tr:nth-child(even) > td {
    background: #fcfeff;
}
.ledger-table .summary-row td {
    background: #eef4fb !important;
    border-color: #d5e0ec;
}
.received-payment-history-wrap {
    overflow-x: auto !important;
    overflow-y: visible;
    width: 100%;
    -webkit-overflow-scrolling: touch;
}
.received-payment-history-table {
    table-layout: auto !important;
    width: max-content !important;
    min-width: 100% !important;
}
.received-payment-history-table > thead > tr > th,
.received-payment-history-table > tbody > tr > td {
    white-space: nowrap !important;
    word-break: normal !important;
    vertical-align: middle !important;
}
.received-payment-history-table > thead > tr > th {
    font-size: 8px !important;
    font-weight: 500 !important;
    letter-spacing: 0 !important;
    line-height: 1.2 !important;
    padding: 5px 4px !important;
    text-transform: none !important;
}
.received-payment-history-table > tbody > tr > td {
    padding: 5px 5px !important;
}
.received-payment-history-table > thead > tr > th.pbutton,
.received-payment-history-table > tbody > tr > td.pbutton {
    width: 1% !important;
}
.received-payment-history-table > thead > tr > th.ledger-seq-col,
.received-payment-history-table > tbody > tr > td.ledger-seq-col {
    min-width: 28px !important;
    text-align: center !important;
    width: 28px !important;
}
.received-payment-history-table .btn-group,
.received-payment-history-table .dropdown-toggle {
    white-space: nowrap;
}
.ledger-footer {
    margin-top: 8px;
    font-size: 10px;
    color: #4d5f72;
    border-top: 1px solid #d8e3ee;
    padding-top: 5px;
}

@media (max-width: 1199px) {
    .detail-grid {
        flex-wrap: wrap;
    }
    .detail-card-client,
    .detail-card-nominee { flex: 1 1 calc(50% - 8px); }
    .detail-card-property {
        flex: 1 1 100%;
    }
    .metric-card {
        width: 33.33%;
    }
}
@media (max-width: 767px) {
    .ledger-card {
        padding: 10px 10px;
    }
    .ledger-header,
    .agency-col,
    .deal-qr-col {
        display: block;
        width: 100%;
        text-align: center;
    }
    .agency-logo-box {
        display: block;
        width: auto;
        margin: 0 0 8px;
    }
    .agency-details {
        width: 100%;
        text-align: center;
    }
    .deal-qr-col {
        margin-top: 6px;
    }
    .deal-qr-wrap {
        justify-content: center;
    }
    .ledger-title-row {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 4px;
    }
    .ledger-title-meta-right,
    .ledger-title-meta-left {
        text-align: center;
    }
    .detail-grid {
        flex-wrap: wrap;
    }
    .detail-card-client,
    .detail-card-nominee,
    .detail-card-property { flex: 1 1 100%; }
    .metric-card {
        width: 50%;
    }
    .metric-value {
        font-size: 17px;
    }
}

@media print {
    @page {
        size: A4 portrait;
        margin: 8mm;
    }
    body * {
        visibility: hidden !important;
    }
    #printable_content,
    #printable_content * {
        visibility: visible !important;
    }
    #printable_content {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
    }
    body {
        background: #fff !important;
    }
    .no-print {
        display: none !important;
    }
    .pbutton {
        display: none !important;
    }
    .ledger-wrap {
        background: #fff !important;
        padding: 0 !important;
    }
    .ledger-page {
        width: auto !important;
        max-width: none !important;
        margin: 0 !important;
    }
    .ledger-card {
        border: 0 !important;
        box-shadow: none !important;
        padding: 0 4mm !important;
    }
    .ledger-title-row {
        padding: 0 1mm !important;
    }
    .ledger-table > thead > tr > th,
    .ledger-table > tbody > tr > td {
        border-color: #4b4b4b !important;
    }
    #ledger-tab-dynamic-content {
        border: 0 !important;
        padding: 0 !important;
        background: transparent !important;
    }
    #ledger-tab-dynamic-content .table-responsive,
    #ledger-tab-dynamic-pane .table-responsive {
        overflow: visible !important;
        border: 0 !important;
        width: 100% !important;
    }
    #ledger-tab-dynamic-content table,
    #ledger-tab-dynamic-pane table {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: auto !important;
    }
    #ledger-tab-dynamic-content table th,
    #ledger-tab-dynamic-content table td,
    #ledger-tab-dynamic-pane table th,
    #ledger-tab-dynamic-pane table td {
        white-space: normal !important;
        word-break: break-word !important;
        padding: 4px 5px !important;
    }
    #ledger-tab-dynamic-content a[href]:after,
    #ledger-tab-dynamic-pane a[href]:after {
        content: "" !important;
    }
    #ledger-tab-dynamic-content table th a,
    #ledger-tab-dynamic-pane table th a {
        color: inherit !important;
        text-decoration: none !important;
        pointer-events: none !important;
    }
    #ledger-tab-dynamic-pane[data-active-tab="plan"] table tr > th:first-child,
    #ledger-tab-dynamic-pane[data-active-tab="plan"] table tr > td:first-child {
        display: none !important;
    }
    .metric-card-inner {
        break-inside: avoid;
    }
    .ledger-card {
        border: 0 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 0 4mm !important;
    }
    .ledger-header {
        display: grid !important;
        width: 100% !important;
        grid-template-columns: var(--qr-total-height) minmax(0, 1fr) auto !important;
        align-items: center !important;
        column-gap: 10px !important;
        border-bottom: none !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
    .agency-col {
        display: contents !important;
    }
    .deal-qr-col {
        display: block !important;
        width: auto !important;
        text-align: right !important;
        justify-self: end !important;
        align-self: center !important;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    .deal-qr-wrap {
        justify-content: center !important;
        align-items: center !important;
        flex-direction: column !important;
        gap: 4px !important;
    }
    .deal-qr-caption {
        writing-mode: horizontal-tb !important;
        text-orientation: mixed !important;
        height: auto !important;
        font-size: 9px !important;
        line-height: 1.15 !important;
        white-space: nowrap !important;
        text-align: center !important;
    }
    .agency-logo-box {
        display: inline-flex !important;
        width: var(--qr-total-height) !important;
        min-height: var(--qr-total-height) !important;
        margin-right: 0 !important;
        vertical-align: middle !important;
    }
    .agency-logo-box img {
        width: var(--qr-size) !important;
        height: var(--qr-size) !important;
        max-height: none !important;
        max-width: none !important;
        object-fit: contain !important;
    }
    .agency-details {
        display: block !important;
        width: auto !important;
        text-align: center !important;
        vertical-align: middle !important;
        margin: 0 auto !important;
    }
    .agency-details h2 {
        font-size: 21px !important;
        font-weight: 800 !important;
    }
    .agency-details p,
    .agency-contact-line {
        font-size: 10px !important;
    }
    .deal-qr-box {
        border-color: #777 !important;
    }
    .ledger-title-row {
        display: grid !important;
        grid-template-columns: 1fr auto 1fr !important;
        align-items: center !important;
        gap: 6px !important;
        border: 1px solid #6b6b6b !important;
        background: transparent !important;
        border-radius: 0 !important;
        padding: 4px 5px !important;
        margin: 0 0 6px !important;
    }
    .ledger-title {
        font-size: 17px !important;
        letter-spacing: 0.3px !important;
        text-align: center !important;
        white-space: nowrap !important;
    }
    .ledger-title-meta-left,
    .ledger-title-meta-right {
        display: block !important;
        font-size: 10px !important;
        color: #000 !important;
        font-weight: 700 !important;
        line-height: 1.25 !important;
        white-space: nowrap !important;
        overflow: visible !important;
    }
    .ledger-title-meta-left {
        text-align: left !important;
        padding-right: 4px !important;
    }
    .ledger-title-meta-right {
        text-align: right !important;
        padding-left: 4px !important;
    }
    .detail-grid {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100% !important;
        gap: 1px !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    .detail-card {
        display: block !important;
        min-width: 0 !important;
        border: 1px solid #6f7f8f !important;
        border-radius: 0 !important;
        background: transparent !important;
        padding: 6px !important;
        vertical-align: top !important;
    }
    .detail-card-client {
        flex: 1.66 1 0 !important;
    }
    .detail-card-nominee {
        flex: 0.84 1 0 !important;
    }
    .detail-card-property {
        flex: 1.10 1 0 !important;
    }
    .detail-card h4 {
        font-size: 11px !important;
        color: #1f3f5f !important;
        border-bottom: 1px solid #8ea0b3 !important;
        margin-bottom: 4px !important;
        padding-bottom: 2px !important;
    }
    .detail-label,
    .detail-value {
        font-size: 9px !important;
    }
    .detail-item-split {
        display: flex !important;
        align-items: flex-start !important;
        justify-content: space-between !important;
        gap: 6px !important;
    }
    .detail-item-split .detail-split-col {
        flex: 1 1 50% !important;
        min-width: 0 !important;
    }
    .detail-item-split .detail-split-col-right,
    .detail-item-split .detail-split-col-right .detail-label,
    .detail-item-split .detail-split-col-right .detail-value {
        text-align: right !important;
    }
    .detail-item-split.no-charge .detail-split-col {
        flex: 1 1 100% !important;
    }
    .detail-item-dual {
        display: flex !important;
        align-items: flex-start !important;
        justify-content: space-between !important;
        gap: 6px !important;
    }
    .detail-item-dual .detail-inline-col {
        flex: 1 1 50% !important;
        min-width: 0 !important;
    }
    .detail-item-dual .detail-inline-col-right,
    .detail-item-dual .detail-inline-col-right .detail-label,
    .detail-item-dual .detail-inline-col-right .detail-value {
        text-align: right !important;
    }
    .metric-card-inner {
        border: 1px solid #7b8ea1 !important;
        background: transparent !important;
        min-height: 62px !important;
    }
    .ledger-metrics {
        display: flex !important;
        flex-wrap: nowrap !important;
        margin: 0 -2px 6px !important;
    }
    .metric-card {
        width: 25% !important;
        padding: 0 2px !important;
        margin-bottom: 0 !important;
        float: none !important;
    }
    .ledger-metrics.metric-count-5 .metric-card {
        width: 20% !important;
    }
    .ledger-metrics.metric-count-6 .metric-card {
        width: 16.6667% !important;
    }
    .ledger-metrics.metric-count-7 .metric-card {
        width: 14.2857% !important;
    }
    .metric-title {
        font-size: 9px !important;
    }
    .metric-value {
        font-size: 12px !important;
        white-space: normal !important;
        overflow: visible !important;
        word-break: break-word !important;
        line-height: 1.1 !important;
    }
    .ledger-section-title {
        font-size: 12px !important;
        margin: 0 0 4px !important;
        padding-bottom: 2px !important;
    }
    .table-responsive {
        border: 0 !important;
    }
    .ledger-table > thead > tr > th,
    .ledger-table > tbody > tr > td {
        font-size: 9px !important;
        padding: 3px 4px !important;
        border-color: #4b4b4b !important;
    }
    .ledger-footer {
        font-size: 9px !important;
        margin-top: 4px !important;
        padding-top: 4px !important;
    }
}

#printable_content.pdf-export-mode .metric-value {
    font-size: 12px !important;
    white-space: normal !important;
    overflow: visible !important;
    word-break: break-word !important;
    line-height: 1.12 !important;
}
#printable_content.pdf-export-mode {
    background: #fff !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
}
#printable_content.pdf-export-mode .ledger-page {
    width: 210mm !important;
    max-width: none !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
}
#printable_content.pdf-export-mode .ledger-card {
    border-radius: 0 !important;
    box-shadow: none !important;
    padding-left: 2mm !important;
    padding-right: 2mm !important;
}
#printable_content.pdf-export-mode .metric-card-inner {
    min-height: 66px !important;
}
#printable_content.pdf-export-mode .deal-qr-wrap {
    flex-direction: column !important;
    align-items: center !important;
    gap: 4px !important;
}
#printable_content.pdf-export-mode .deal-qr-caption {
    writing-mode: horizontal-tb !important;
    text-orientation: mixed !important;
    height: auto !important;
    white-space: nowrap !important;
    text-align: center !important;
}

/* Final White Villas ledger polish */
.panel-heading.no-print {
    background: #ffffff !important;
    border: 1px solid #dce7f3 !important;
    border-radius: 12px !important;
    box-shadow: 0 12px 28px rgba(15,35,61,.08) !important;
    margin: 0 auto 12px !important;
    max-width: 210mm;
    padding: 10px !important;
}
.panel-heading.no-print .btn-group {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.panel-heading.no-print .btn {
    border-radius: 999px !important;
    box-shadow: 0 8px 18px rgba(15,35,61,.08);
    font-size: 12px;
    font-weight: 900 !important;
    margin: 0 !important;
    padding: 8px 13px;
}
.ledger-preview-label {
    align-items: center;
    background: #f8fbff;
    border: 1px solid #dce7f3;
    border-radius: 999px;
    color: #07152a;
    display: inline-flex;
    font-size: 12px;
    font-weight: 900;
    gap: 7px;
    padding: 8px 13px;
}
.ledger-wrap {
    background: linear-gradient(180deg,#eef4fb 0%,#f8fbff 100%);
    padding: 14px 10px 22px;
}
.ledger-page {
    margin: 0 auto;
}
.ledger-card {
    background: #ffffff;
    border: 1px solid #d8e4f0;
    border-radius: 14px;
    box-shadow: 0 20px 46px rgba(15,35,61,.12);
    overflow: hidden;
    padding: 16px 18px 18px;
    position: relative;
}
.ledger-card:before {
    background: linear-gradient(90deg,#ff0a46 0%,#7cf9ff 100%);
    content: "";
    height: 4px;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
}
.ledger-header {
    background: linear-gradient(135deg,#f8fbff 0%,#eef6ff 100%);
    border: 1px solid #d8e5f1;
    border-radius: 12px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.92);
    margin: 0 0 10px;
    padding: 12px;
}
.agency-logo-box {
    background: #fff;
    border: 1px solid #e0e9f3;
    border-radius: 12px;
    box-shadow: 0 10px 22px rgba(15,35,61,.08);
}
.agency-details h2 {
    color: #07152a;
    font-size: 26px;
    font-weight: 900;
    letter-spacing: 0;
}
.agency-details p {
    color: #52647a;
    font-weight: 700;
}
.agency-contact-line {
    color: #0b3762;
    font-weight: 900;
}
.deal-status-line {
    background: rgba(255,255,255,.74);
    border: 1px solid #e4edf6;
    border-radius: 999px;
    display: inline-block;
    font-size: 12px;
    margin-top: 6px;
    padding: 4px 12px;
}
.deal-qr-wrap {
    align-items: stretch !important;
    display: inline-flex !important;
    flex-direction: row !important;
    gap: 6px !important;
}
.deal-qr-box {
    background: #fff;
    border: 1px solid #d7e3ef;
    border-radius: 10px;
    box-shadow: 0 10px 22px rgba(15,35,61,.10);
}
.deal-qr-caption {
    align-items: center !important;
    background: transparent !important;
    border-radius: 8px;
    color: #07152a !important;
    display: flex !important;
    font-size: 9px !important;
    font-weight: 900 !important;
    height: var(--qr-total-height) !important;
    justify-content: center !important;
    letter-spacing: .2px;
    line-height: 1 !important;
    min-height: var(--qr-total-height);
    order: -1;
    padding: 5px 3px;
    text-align: center !important;
    text-orientation: mixed !important;
    text-transform: uppercase;
    transform: rotate(180deg);
    white-space: nowrap !important;
    writing-mode: vertical-rl !important;
}
.ledger-title-row {
    background: linear-gradient(135deg,#ffffff 0%,#f2f7fd 100%);
    border: 1px solid #d8e5f1;
    border-radius: 12px;
    box-shadow: 0 8px 18px rgba(15,35,61,.05);
    margin: 0 0 10px;
    padding: 9px 12px;
}
.ledger-title {
    color: #07152a;
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 1px;
}
.ledger-title-meta-left,
.ledger-title-meta-right {
    color: #0b3762;
    font-size: 11px;
    font-weight: 900;
}
.detail-grid {
    gap: 8px;
    margin-bottom: 10px;
}
.detail-card {
    background: linear-gradient(180deg,#ffffff 0%,#f9fcff 100%);
    border: 1px solid #d7e4f2;
    border-radius: 12px;
    box-shadow: 0 12px 26px rgba(15,35,61,.08);
    overflow: hidden;
    padding: 11px 11px 12px;
    position: relative;
}
.detail-card:before {
    background: linear-gradient(180deg,#ff0a46 0%,#7cf9ff 100%);
    bottom: 0;
    content: "";
    left: 0;
    position: absolute;
    top: 0;
    width: 3px;
}
.detail-card h4 {
    align-items: center;
    border-bottom: 1px solid #dfeaf5;
    color: #07152a;
    display: flex;
    font-size: 11px;
    font-weight: 900;
    gap: 7px;
    letter-spacing: .2px;
    margin: 0 0 9px;
    padding: 0 0 7px 2px;
}
.detail-card h4:before {
    background: #ff0a46;
    border-radius: 999px;
    box-shadow: 0 0 0 3px rgba(255,10,70,.10);
    content: "";
    display: inline-block;
    height: 7px;
    width: 7px;
}
.client-detail-photo img {
    background: #eef4fb;
    border: 1px solid #cfddec;
    border-radius: 10px;
    box-shadow: 0 8px 18px rgba(15,35,61,.12);
    padding: 2px;
}
.detail-card-client .client-detail-body {
    align-items: flex-start !important;
    display: grid !important;
    gap: 8px 10px !important;
    grid-template-columns: 1in minmax(0,1fr) !important;
}
.detail-card-client .client-detail-photo {
    grid-column: 1 !important;
    grid-row: 1 !important;
}
.detail-card-client .client-detail-content {
    grid-column: 2 !important;
    grid-row: 1 !important;
    min-width: 0 !important;
}
.detail-card-client .client-address-detail {
    grid-column: 1 / -1 !important;
    margin: 0 !important;
}
.detail-card-client .client-address-detail .detail-value {
    line-height: 1.35 !important;
    white-space: normal !important;
    word-break: normal !important;
    overflow-wrap: anywhere !important;
}
.detail-item {
    background: rgba(248,251,255,.82);
    border: 1px solid #eef3f8;
    border-radius: 8px;
    margin-bottom: 5px;
    padding: 4px 7px;
}
.detail-label {
    color: #64748b;
    font-size: 9px;
    font-weight: 900;
    letter-spacing: .25px;
}
.detail-value {
    color: #172a3f;
    font-size: 11px;
    font-weight: 800;
    line-height: 1.28;
}
.ledger-nav-tabs {
    align-items: center;
    border-bottom: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin: 0 0 10px;
}
.ledger-nav-tabs > li {
    float: none;
}
.ledger-nav-tabs > li > a {
    background: #f8fbff;
    border: 1px solid #dce7f3 !important;
    border-radius: 999px !important;
    color: #172a3f;
    font-size: 11px;
    font-weight: 900;
    padding: 8px 14px;
}
.ledger-nav-tabs > li.active > a,
.ledger-nav-tabs > li.active > a:hover,
.ledger-nav-tabs > li.active > a:focus {
    background: #ff0a46 !important;
    border-color: #ff0a46 !important;
    color: #fff !important;
}
.ledger-metrics {
    background: #f8fbff;
    border: 1px solid #dce7f3;
    border-radius: 12px;
    margin: 0 0 12px;
    padding: 7px 5px 0;
}
.metric-card-inner {
    border-radius: 10px;
    box-shadow: 0 8px 18px rgba(15,35,61,.06), inset 0 1px 0 rgba(255,255,255,.85);
}
.metric-title {
    color: #52647a;
    font-size: 9px;
    font-weight: 900;
}
.metric-value {
    color: #07152a;
    font-weight: 900;
}
.ledger-section-title {
    border: 0;
    color: #07152a;
    font-size: 15px;
    font-weight: 900;
    margin: 5px 0 9px;
    padding: 0;
}
.received-payment-history-wrap {
    border: 1px solid #d7e4f2;
    border-radius: 12px;
    box-shadow: 0 12px 26px rgba(15,35,61,.07);
}
.received-payment-history-table > thead > tr > th {
    font-size: 8px !important;
    font-weight: 500 !important;
    letter-spacing: 0 !important;
    padding: 6px 5px !important;
    text-transform: none !important;
}
.received-payment-history-table > tbody > tr > td {
    font-size: 10px;
    padding: 7px 6px !important;
}
.ledger-footer {
    background: #f8fbff;
    border: 1px solid #dce7f3;
    border-radius: 10px;
    color: #52647a;
    font-weight: 800;
    margin-top: 12px;
    padding: 7px 10px;
}
#printable_content.pdf-export-mode .deal-qr-wrap {
    align-items: stretch !important;
    flex-direction: row !important;
    gap: 4px !important;
}
#printable_content.pdf-export-mode .deal-qr-caption {
    background: transparent !important;
    color: #07152a !important;
    height: var(--qr-total-height) !important;
    order: -1 !important;
    text-align: center !important;
    text-orientation: mixed !important;
    transform: rotate(180deg) !important;
    writing-mode: vertical-rl !important;
}
#printable_content.pdf-export-mode {
    background: #ffffff !important;
    box-sizing: border-box !important;
    margin: 0 !important;
    overflow: hidden !important;
    padding: 0 !important;
    width: 210mm !important;
}
#printable_content.pdf-export-mode .ledger-page {
    box-sizing: border-box !important;
    margin: 0 !important;
    max-width: 210mm !important;
    padding: 0 !important;
    width: 210mm !important;
}
#printable_content.pdf-export-mode .ledger-card {
    border-radius: 0 !important;
    box-shadow: none !important;
    box-sizing: border-box !important;
    padding: 5mm 5mm 4mm !important;
    width: 210mm !important;
}
#printable_content.pdf-export-mode .received-payment-history-wrap {
    border-radius: 8px !important;
    overflow: hidden !important;
    width: 100% !important;
}
#printable_content.pdf-export-mode .received-payment-history-table {
    table-layout: fixed !important;
    width: 100% !important;
    min-width: 0 !important;
}
#printable_content.pdf-export-mode .received-payment-history-table > thead > tr > th,
#printable_content.pdf-export-mode .received-payment-history-table > tbody > tr > td {
    font-size: 8px !important;
    line-height: 1.2 !important;
    padding: 4px 4px !important;
    white-space: normal !important;
    word-break: break-word !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .pbutton {
    display: none !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .ledger-seq-col {
    width: 24px !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .ledger-installment-col {
    width: 82px !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .ledger-payment-name-col {
    width: 78px !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .ledger-paid-date-col {
    width: 58px !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .ledger-transaction-col {
    width: auto !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .ledger-payment-type-col {
    width: 74px !important;
}

/* PDF export should mirror the print ledger without action controls. */
#printable_content.pdf-export-mode {
    background: #ffffff !important;
    box-sizing: border-box !important;
    margin: 0 !important;
    overflow: visible !important;
    padding: 0 !important;
    width: 194mm !important;
}
#printable_content.pdf-export-mode .ledger-page {
    box-sizing: border-box !important;
    margin: 0 !important;
    max-width: none !important;
    padding: 0 !important;
    width: 100% !important;
}
#printable_content.pdf-export-mode .ledger-card {
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    box-sizing: border-box !important;
    padding: 0 4mm !important;
    width: 100% !important;
}
#printable_content.pdf-export-mode .ledger-card:before,
#printable_content.pdf-export-mode .detail-card:before {
    display: none !important;
}
#printable_content.pdf-export-mode .ledger-title-row {
    border: 1px solid #6b6b6b !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    margin: 0 0 6px !important;
    padding: 4px 5px !important;
}
#printable_content.pdf-export-mode .detail-card {
    background: transparent !important;
    border: 1px solid #6f7f8f !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    padding: 6px !important;
}
#printable_content.pdf-export-mode .metric-card-inner {
    background: transparent !important;
    border: 1px solid #7b8ea1 !important;
    box-shadow: none !important;
    min-height: 62px !important;
}
#printable_content.pdf-export-mode .received-payment-history-wrap {
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    overflow: visible !important;
    width: 100% !important;
}
#printable_content.pdf-export-mode .received-payment-history-table {
    max-width: 100% !important;
    min-width: 0 !important;
    table-layout: fixed !important;
    width: 100% !important;
}
#printable_content.pdf-export-mode .received-payment-history-table > thead > tr > th,
#printable_content.pdf-export-mode .received-payment-history-table > tbody > tr > td {
    border-color: #4b4b4b !important;
    font-size: 7px !important;
    line-height: 1.15 !important;
    padding: 2px 3px !important;
    vertical-align: top !important;
    white-space: nowrap !important;
    overflow-wrap: normal !important;
    word-break: normal !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .ledger-transaction-col {
    white-space: normal !important;
    overflow-wrap: anywhere !important;
    word-break: normal !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .pbutton {
    display: none !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .ledger-seq-col {
    min-width: 32px !important;
    text-align: center !important;
    width: 8% !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .ledger-installment-col {
    width: 18% !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .ledger-payment-name-col {
    width: 19% !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .ledger-paid-date-col {
    width: 13% !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .ledger-transaction-col {
    width: 30% !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .ledger-payment-type-col {
    width: 12% !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .summary-row > td {
    padding: 3px 5px !important;
}
#printable_content.pdf-export-mode .received-payment-history-table .summary-row > td:first-child {
    text-align: left !important;
}
#printable_content.pdf-export-mode,
#printable_content.pdf-export-mode .ledger-page,
#printable_content.pdf-export-mode .ledger-card {
    break-after: auto !important;
    height: auto !important;
    min-height: 0 !important;
    page-break-after: auto !important;
}
#printable_content.pdf-export-mode .ledger-card {
    overflow: visible !important;
}
#printable_content.pdf-export-mode .ledger-footer {
    break-after: avoid !important;
    page-break-after: avoid !important;
}
@media screen {
    #printable_content:not(.pdf-export-mode) .received-payment-history-wrap {
        background: #fff !important;
        border: 1px solid #4b4b4b !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        overflow: visible !important;
        width: 100% !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table {
        border-collapse: collapse !important;
        margin-bottom: 0 !important;
        max-width: 100% !important;
        min-width: 0 !important;
        table-layout: fixed !important;
        width: 100% !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table > thead > tr > th,
    #printable_content:not(.pdf-export-mode) .received-payment-history-table > tbody > tr > td {
        border: 1px solid #4b4b4b !important;
        color: #000 !important;
        font-size: 10px !important;
        line-height: 1.2 !important;
        padding: 4px 4px !important;
        vertical-align: top !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table > thead > tr > th {
        background: #f4f4f4 !important;
        font-weight: 800 !important;
        text-align: center !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table > tbody > tr:nth-child(even) > td {
        background: #fff !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .pbutton:first-child {
        text-align: center !important;
        width: 4% !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .pbutton:last-child {
        overflow: visible !important;
        position: relative !important;
        text-align: center !important;
        width: 13% !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .ledger-seq-col {
        text-align: center !important;
        white-space: nowrap !important;
        width: 5% !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .ledger-installment-col {
        white-space: nowrap !important;
        width: 15% !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .ledger-payment-name-col {
        overflow-wrap: anywhere !important;
        white-space: normal !important;
        width: 15% !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .ledger-paid-date-col {
        text-align: center !important;
        white-space: nowrap !important;
        width: 11% !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .ledger-transaction-col {
        overflow-wrap: anywhere !important;
        white-space: normal !important;
        width: 24% !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .ledger-payment-type-col {
        overflow-wrap: anywhere !important;
        white-space: normal !important;
        width: 12% !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .summary-row > td {
        background: #f4f4f4 !important;
        font-weight: 800 !important;
        padding: 5px 6px !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .history-action-buttons {
        align-items: center;
        display: inline-flex;
        flex-wrap: nowrap;
        gap: 3px;
        justify-content: center;
        white-space: nowrap;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .history-action-btn {
        align-items: center;
        border-radius: 3px;
        color: #fff !important;
        display: inline-flex;
        font-size: 10px;
        height: 22px;
        justify-content: center;
        line-height: 1;
        padding: 0;
        text-decoration: none !important;
        width: 22px;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .history-action-btn-print { background: #1266a8; }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .history-action-btn-slip { background: #596a7a; }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .history-action-btn-edit { background: #d88916; }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .history-action-btn-delete { background: #c9302c; }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .history-action-btn:hover,
    #printable_content:not(.pdf-export-mode) .received-payment-history-table .history-action-btn:focus {
        color: #fff !important;
        filter: brightness(.92);
        text-decoration: none !important;
    }
}
@media screen and (max-width: 991px) {
    #printable_content:not(.pdf-export-mode) .received-payment-history-wrap {
        overflow-x: visible !important;
        overflow-y: visible !important;
    }
    #printable_content:not(.pdf-export-mode) .received-payment-history-table {
        min-width: 0 !important;
    }
}
@media print {
    .ledger-card:before,
    .detail-card:before {
        display: none !important;
    }
    .deal-qr-wrap {
        align-items: stretch !important;
        flex-direction: row !important;
        gap: 4px !important;
    }
    .deal-qr-caption {
        background: transparent !important;
        color: #07152a !important;
        height: var(--qr-total-height) !important;
        order: -1 !important;
        text-align: center !important;
        text-orientation: mixed !important;
        transform: rotate(180deg) !important;
        writing-mode: vertical-rl !important;
    }
    .received-payment-history-wrap {
        overflow: visible !important;
        width: 100% !important;
    }
    .received-payment-history-table {
        max-width: 100% !important;
        min-width: 0 !important;
        table-layout: fixed !important;
        width: 100% !important;
    }
    .received-payment-history-table > thead > tr > th,
    .received-payment-history-table > tbody > tr > td {
        font-size: 7px !important;
        line-height: 1.15 !important;
        padding: 2px 3px !important;
        white-space: nowrap !important;
        overflow-wrap: normal !important;
        word-break: normal !important;
    }
    .received-payment-history-table .ledger-transaction-col {
        white-space: normal !important;
        overflow-wrap: anywhere !important;
        word-break: normal !important;
    }
    .received-payment-history-table .pbutton {
        display: none !important;
    }
    .received-payment-history-table .ledger-seq-col {
        min-width: 32px !important;
        text-align: center !important;
        width: 8% !important;
    }
    .received-payment-history-table .ledger-installment-col {
        width: 18% !important;
    }
    .received-payment-history-table .ledger-payment-name-col {
        width: 19% !important;
    }
    .received-payment-history-table .ledger-paid-date-col {
        width: 13% !important;
    }
    .received-payment-history-table .ledger-transaction-col {
        width: 30% !important;
    }
    .received-payment-history-table .ledger-payment-type-col {
        width: 12% !important;
    }
    .received-payment-history-table .summary-row > td {
        padding: 3px 5px !important;
    }
    .received-payment-history-table .summary-row > td:first-child {
        text-align: left !important;
    }
}
.ledger-print-root {
    display: none;
}
@media print {
    html,
    body {
        height: auto !important;
        min-height: 0 !important;
        overflow: visible !important;
    }
    body.ledger-printing-body {
        background: #ffffff !important;
        margin: 0 !important;
    }
    body.ledger-printing-body > :not(.ledger-print-root) {
        display: none !important;
    }
    body.ledger-printing-body .ledger-print-root {
        display: block !important;
        height: auto !important;
        margin: 0 !important;
        min-height: 0 !important;
        overflow: visible !important;
        padding: 0 !important;
        visibility: visible !important;
        width: 100% !important;
    }
    body.ledger-printing-body .ledger-print-root * {
        visibility: visible !important;
    }
    body.ledger-printing-body .ledger-print-root #printable_content {
        break-after: auto !important;
        height: auto !important;
        left: auto !important;
        margin: 0 auto !important;
        min-height: 0 !important;
        overflow: visible !important;
        page-break-after: auto !important;
        position: static !important;
        top: auto !important;
        width: 194mm !important;
    }
    body.ledger-printing-body .ledger-print-root .ledger-page,
    body.ledger-printing-body .ledger-print-root .ledger-card {
        break-after: auto !important;
        height: auto !important;
        min-height: 0 !important;
        page-break-after: auto !important;
    }
    body.ledger-printing-body .ledger-print-root .ledger-card {
        overflow: visible !important;
    }
    body.ledger-printing-body .ledger-print-root .ledger-footer {
        break-after: avoid !important;
        page-break-after: avoid !important;
    }
}
</style>

<script type="text/javascript">
var ledgerPrintCleanup = null;
var ledgerAutoPdf = <?php echo !empty($ledgerAutoPdf) ? 'true' : 'false'; ?>;
var ledgerAutoPdfStarted = false;

function setLedgerTemporaryColspan(colspanNodes, node, value) {
    if (!node) {
        return;
    }
    colspanNodes.push({
        node: node,
        value: node.getAttribute('colspan')
    });
    node.setAttribute('colspan', value);
}

function prepareReceivedPaymentHistoryOutput(element) {
    var hiddenNodes = [];
    var colspanNodes = [];
    var i;
    if (!element) {
        return function() {};
    }
    var hideNodes = element.querySelectorAll('.received-payment-history-table .pbutton');
    for (i = 0; i < hideNodes.length; i++) {
        hiddenNodes.push({
            node: hideNodes[i],
            display: hideNodes[i].style.display || ''
        });
        hideNodes[i].style.display = 'none';
    }
    var summaryRows = element.querySelectorAll('.received-payment-history-table .summary-row');
    if (summaryRows.length > 0) {
        var paidSummaryCells = summaryRows[0].querySelectorAll('td');
        if (paidSummaryCells.length > 0) {
            setLedgerTemporaryColspan(colspanNodes, paidSummaryCells[0], 2);
            setLedgerTemporaryColspan(colspanNodes, paidSummaryCells[paidSummaryCells.length - 1], 3);
        }
    }
    if (summaryRows.length > 1) {
        var balanceSummaryCells = summaryRows[1].querySelectorAll('td');
        if (balanceSummaryCells.length > 1) {
            setLedgerTemporaryColspan(colspanNodes, balanceSummaryCells[0], 2);
            setLedgerTemporaryColspan(colspanNodes, balanceSummaryCells[1], 4);
        }
    }
    return function() {
        for (i = 0; i < hiddenNodes.length; i++) {
            hiddenNodes[i].node.style.display = hiddenNodes[i].display;
        }
        for (i = 0; i < colspanNodes.length; i++) {
            if (colspanNodes[i].value === null) {
                colspanNodes[i].node.removeAttribute('colspan');
            } else {
                colspanNodes[i].node.setAttribute('colspan', colspanNodes[i].value);
            }
        }
    };
}

function cleanupLedgerPrintView() {
    if (typeof ledgerPrintCleanup === 'function') {
        ledgerPrintCleanup();
        ledgerPrintCleanup = null;
    }
    var printRoot = document.getElementById('ledger-print-root');
    if (printRoot && printRoot.parentNode) {
        printRoot.parentNode.removeChild(printRoot);
    }
    document.body.classList.remove('ledger-printing-body');
    var element = document.getElementById('printable_content');
    if (element) {
        element.classList.remove('pdf-export-mode');
    }
    adjustMetricValueFont();
}

function prepareLedgerPrintView() {
    var element = document.getElementById('printable_content');
    cleanupLedgerPrintView();
    if (!element) {
        return;
    }
    var printRoot = document.createElement('div');
    var printClone = element.cloneNode(true);
    printRoot.id = 'ledger-print-root';
    printRoot.className = 'ledger-print-root';
    printClone.classList.add('pdf-export-mode');
    printRoot.appendChild(printClone);
    document.body.appendChild(printRoot);
    document.body.classList.add('ledger-printing-body');
    ledgerPrintCleanup = prepareReceivedPaymentHistoryOutput(printClone);
}

function printLedgerView() {
    prepareLedgerPrintView();
    window.print();
}

function exportLedgerPdf() {
    var element = document.getElementById('printable_content');
    if (!element || typeof html2pdf === 'undefined') {
        alert('PDF export library is not loaded.');
        return;
    }
    element.classList.add('pdf-export-mode');
    adjustMetricValueFont(18, 9);
    var planHideNodes = [];
    var cleanupPaymentHistory = prepareReceivedPaymentHistoryOutput(element);
    var i;
    var dynamicPane = document.getElementById('ledger-tab-dynamic-pane');
    if (dynamicPane && dynamicPane.className.indexOf('active') !== -1 && dynamicPane.getAttribute('data-active-tab') === 'plan') {
        var planCells = element.querySelectorAll('#ledger-tab-dynamic-content table tr > th:first-child, #ledger-tab-dynamic-content table tr > td:first-child');
        for (i = 0; i < planCells.length; i++) {
            planCells[i].setAttribute('data-prev-display', planCells[i].style.display || '');
            planCells[i].style.display = 'none';
            planHideNodes.push(planCells[i]);
        }
    }
    var opt = {
        margin: [0.31, 0.31, 0.31, 0.31],
        filename: <?php echo json_encode($ledgerPdfFilename); ?>,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, scrollX: 0, scrollY: 0, backgroundColor: '#ffffff' },
        pagebreak: { mode: ['css', 'legacy'] },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    var cleanupLedgerPdfExport = function() {
        element.classList.remove('pdf-export-mode');
        adjustMetricValueFont();
        cleanupPaymentHistory();
        for (i = 0; i < planHideNodes.length; i++) {
            planHideNodes[i].style.display = planHideNodes[i].getAttribute('data-prev-display');
        }
    };
    html2pdf().set(opt).from(element).save().then(function() {
        cleanupLedgerPdfExport();
    }).catch(function() {
        cleanupLedgerPdfExport();
    });
}

function startLedgerAutoPdf(attempt) {
    attempt = parseInt(attempt, 10);
    if (isNaN(attempt) || attempt < 0) {
        attempt = 0;
    }
    if (!ledgerAutoPdf || ledgerAutoPdfStarted) {
        return;
    }
    var element = document.getElementById('printable_content');
    if (!element || typeof html2pdf === 'undefined') {
        if (attempt < 30) {
            setTimeout(function() {
                startLedgerAutoPdf(attempt + 1);
            }, 300);
        }
        return;
    }
    ledgerAutoPdfStarted = true;
    setTimeout(function() {
        exportLedgerPdf();
    }, 400);
}
function adjustMetricValueFont(maxSize, minSize) {
    var nodes = document.querySelectorAll('.metric-value');
    var i;
    var localMax = parseInt(maxSize, 10);
    var localMin = parseInt(minSize, 10);
    if (isNaN(localMax) || localMax <= 0) {
        localMax = 22;
    }
    if (isNaN(localMin) || localMin <= 0 || localMin >= localMax) {
        localMin = 14;
    }
    for (i = 0; i < nodes.length; i++) {
        var node = nodes[i];
        var currentSize = localMax;
        node.style.fontSize = currentSize + 'px';
        while (node.scrollWidth > node.clientWidth && currentSize > localMin) {
            currentSize--;
            node.style.fontSize = currentSize + 'px';
        }
    }
}

var dealDocCropState = {
    cropper: null,
    input: null,
    box: null,
    originalName: ''
};

function resetDealDocCropper() {
    if (dealDocCropState.cropper) {
        dealDocCropState.cropper.destroy();
        dealDocCropState.cropper = null;
    }
    dealDocCropState.input = null;
    dealDocCropState.box = null;
    dealDocCropState.originalName = '';
    var cropImage = document.getElementById('dealDocCropImage');
    if (cropImage) {
        cropImage.src = '';
    }
}

function renderDealDocPreview($box, file) {
    if (!$box || !$box.length) {
        return;
    }
    var $preview = $box.find('.deal-doc-drop-preview');
    if (!$preview.length) {
        return;
    }
    if (!file) {
        $preview.html('');
        return;
    }
    var safeName = file.name || '<?php echo addslashes(__('Selected file')); ?>';
    if (file.type && file.type.toLowerCase().indexOf('image/') === 0) {
        var fileReader = new FileReader();
        fileReader.onload = function (e) {
            $preview.html(
                '<img src="' + e.target.result + '" alt="<?php echo addslashes(__('Preview')); ?>">' +
                '<div><small>' + safeName + '</small></div>'
            );
        };
        fileReader.readAsDataURL(file);
    } else {
        $preview.html('<div><span class="label label-default"><?php echo addslashes(__('File')); ?></span>&nbsp;<small>' + safeName + '</small></div>');
    }
}

function openDealDocCropper(file, $input, $box) {
    if (!file || !file.type || file.type.toLowerCase().indexOf('image/') !== 0) {
        renderDealDocPreview($box, file);
        return;
    }
    if (typeof Cropper === 'undefined' || !$('#dealDocCropModal').length) {
        renderDealDocPreview($box, file);
        return;
    }
    var fileReader = new FileReader();
    fileReader.onload = function (e) {
        var cropImage = document.getElementById('dealDocCropImage');
        if (!cropImage) {
            renderDealDocPreview($box, file);
            return;
        }
        dealDocCropState.input = $input;
        dealDocCropState.box = $box;
        dealDocCropState.originalName = file.name || 'cropped-image.png';
        cropImage.src = e.target.result;
        $('#dealDocCropModal').modal('show');
    };
    fileReader.readAsDataURL(file);
}

function bindDealDocCropEvents() {
    var $modal = $('#dealDocCropModal');
    if (!$modal.length) {
        return;
    }
    $modal.off('shown.bs.modal.dealdoc').on('shown.bs.modal.dealdoc', function () {
        var cropImage = document.getElementById('dealDocCropImage');
        if (!cropImage || !cropImage.src) {
            return;
        }
        if (typeof Cropper === 'undefined') {
            return;
        }
        if (dealDocCropState.cropper) {
            dealDocCropState.cropper.destroy();
            dealDocCropState.cropper = null;
        }
        dealDocCropState.cropper = new Cropper(cropImage, {
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            responsive: true,
            background: false
        });
    });
    $modal.off('hidden.bs.modal.dealdoc').on('hidden.bs.modal.dealdoc', function () {
        resetDealDocCropper();
    });

    $(document).off('click.dealdoccrop', '#dealDocCropApply').on('click.dealdoccrop', '#dealDocCropApply', function () {
        if (!dealDocCropState.cropper || !dealDocCropState.input || !dealDocCropState.box) {
            $('#dealDocCropModal').modal('hide');
            return;
        }
        var canvas = dealDocCropState.cropper.getCroppedCanvas({
            maxWidth: 1600,
            maxHeight: 1600,
            fillColor: '#fff'
        });
        if (!canvas) {
            $('#dealDocCropModal').modal('hide');
            return;
        }
        canvas.toBlob(function (blob) {
            if (!blob) {
                $('#dealDocCropModal').modal('hide');
                return;
            }
            var originalName = dealDocCropState.originalName || ('cropped_' + Date.now() + '.png');
            var extension = 'png';
            var dotIndex = originalName.lastIndexOf('.');
            if (dotIndex > 0) {
                extension = originalName.substr(dotIndex + 1).toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(extension) === -1) {
                    extension = 'png';
                }
            }
            var croppedName = originalName.replace(/\.[^.]+$/, '') + '_cropped.' + extension;
            var mime = blob.type || ('image/' + (extension === 'jpg' ? 'jpeg' : extension));
            var croppedFile;
            try {
                croppedFile = new File([blob], croppedName, { type: mime, lastModified: Date.now() });
            } catch (e) {
                croppedFile = blob;
                croppedFile.name = croppedName;
            }
            if (typeof DataTransfer !== 'undefined') {
                var dt = new DataTransfer();
                dt.items.add(croppedFile);
                dealDocCropState.input[0].files = dt.files;
            }
            renderDealDocPreview(dealDocCropState.box, croppedFile);
            $('#dealDocCropModal').modal('hide');
        }, 'image/png', 0.95);
    });
}

function initDealDocumentsUi(scope) {
    var $scope = scope && scope.length ? scope : $('#ledger-tab-dynamic-content');
    if (!$scope.length) {
        return;
    }
    bindDealDocCropEvents();

    $scope.find('.js-doc-upload-toggle').off('click.dealdoc').on('click.dealdoc', function (e) {
        e.preventDefault();
        var modalSelector = $(this).data('target') || '#dealDocumentUploadModal';
        $(modalSelector).modal('show');
    });

    $scope.find('.js-doc-date-picker').each(function () {
        if ($(this).attr('type') !== 'date' && typeof jQuery.fn.datepicker === 'function') {
            try {
                $(this).datepicker('destroy');
            } catch (e) {}
            $(this).datepicker({
                format: 'dd-M-yyyy',
                autoclose: true,
                todayHighlight: true
            });
        }
    });

    $scope.find('.js-doc-upload-submit').off('click.dealdoc').on('click.dealdoc', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var $panel = $btn.closest('#deal-doc-upload-panel');
        var uploadUrl = $panel.data('upload-url') || window.location.href;
        var $type = $panel.find('[name="data[DealDocument][document_type]"]').first();
        var $date = $panel.find('[name="data[DealDocument][document_date]"]').first();
        var $fileInput = $panel.find('input[name="data[DealDocument][upload_file]"]').first();
        var file = ($fileInput.length && $fileInput[0].files && $fileInput[0].files.length) ? $fileInput[0].files[0] : null;
        if (!file) {
            alert('<?php echo addslashes(__('Please select a file to upload.')); ?>');
            return false;
        }
        var formData = new FormData();
        formData.append('data[DealDocument][document_type]', ($type.val() || 'Others'));
        formData.append('data[DealDocument][document_date]', ($date.val() || ''));
        formData.append('data[DealDocument][upload_file]', file);
        var oldHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="fa fa-spinner fa-spin"></span>&nbsp;<?php echo addslashes(__('Uploading...')); ?>');
        $.ajax({
            url: uploadUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function (resp) {
                $('#dealDocumentUploadModal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                var parsed = $('<div>').html(resp);
                var html = '';
                if (parsed.find('#deal-documents-content').length) {
                    html = parsed.find('#deal-documents-content').first().html();
                } else if (typeof resp === 'string') {
                    html = resp;
                }
                if (!html) {
                    window.location.reload();
                    return;
                }
                $scope.html(html);
                initDealDocumentsUi($scope);
            },
            error: function () {
                alert('<?php echo addslashes(__('Unable to upload document. Please try again.')); ?>');
            },
            complete: function () {
                $btn.prop('disabled', false).html(oldHtml);
            }
        });
        return false;
    });

    $scope.find('.deal-doc-drop-upload').each(function () {
        var $box = $(this);
        var inputId = $box.data('input-id');
        if (!inputId) {
            return;
        }
        var $input = $('#' + inputId);
        if (!$input.length) {
            return;
        }
        var openPicker = function() {
            var nativeInput = $input.get(0);
            if (nativeInput && typeof nativeInput.click === 'function') {
                nativeInput.click();
            } else {
                $input.trigger('click');
            }
        };
        $box.off('dragenter.dealdoc dragover.dealdoc dragleave.dealdoc drop.dealdoc click.dealdoc');
        $box.on('dragenter.dealdoc dragover.dealdoc', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $box.addClass('drag-over');
        });
        $box.on('dragleave.dealdoc', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $box.removeClass('drag-over');
        });
        $box.on('click.dealdoc', function (e) {
            if ($(e.target).closest('button, label, a, .js-doc-browse').length) {
                return;
            }
            e.preventDefault();
            openPicker();
        });
        $box.on('drop.dealdoc', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $box.removeClass('drag-over');
            var files = e.originalEvent && e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : null;
            if (!files || !files.length) {
                return;
            }
            if (typeof DataTransfer !== 'undefined') {
                var dt = new DataTransfer();
                dt.items.add(files[0]);
                $input[0].files = dt.files;
                openDealDocCropper(dt.files[0], $input, $box);
            } else {
                openDealDocCropper(files[0], $input, $box);
            }
        });
        $box.find('.js-doc-browse').off('click.dealdoc').on('click.dealdoc', function (e) {
            e.stopPropagation();
        });
        $box.find('.js-doc-clear').off('click.dealdoc').on('click.dealdoc', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $input.val('');
            renderDealDocPreview($box, null);
        });
        $input.off('change.dealdoc').on('change.dealdoc', function () {
            var file = this.files && this.files.length ? this.files[0] : null;
            openDealDocCropper(file, $input, $box);
        });
    });

    $scope.find('.js-deal-document-delete').off('click.dealdoc').on('click.dealdoc', function (e) {
        if (!confirm('<?php echo addslashes(__('Are you sure you want to delete this document?')); ?>')) {
            e.preventDefault();
        }
    });
}

var ledgerTabCache = {};
function loadLedgerTabContent(tabKey, url, selector) {
    if (tabKey === 'paid') {
        $('#ledger-tab-paid-pane').addClass('active');
        $('#ledger-tab-status-pane').removeClass('active');
        $('#ledger-tab-dynamic-pane').removeClass('active').attr('data-active-tab', '');
        return false;
    }
    if (tabKey === 'status_analysis') {
        $('#ledger-tab-paid-pane').removeClass('active');
        $('#ledger-tab-status-pane').addClass('active');
        $('#ledger-tab-dynamic-pane').removeClass('active').attr('data-active-tab', '');
        return false;
    }
    $('#ledger-tab-paid-pane').removeClass('active');
    $('#ledger-tab-status-pane').removeClass('active');
    $('#ledger-tab-dynamic-pane').addClass('active').attr('data-active-tab', tabKey);
    if (!url || url === '#') {
        $('#ledger-tab-dynamic-content').html('<div class="alert alert-warning"><?php echo addslashes(__('No linked data found for this tab.')); ?></div>');
        return false;
    }
    if (ledgerTabCache[tabKey]) {
        $('#ledger-tab-dynamic-content').html(ledgerTabCache[tabKey]);
        if (tabKey === 'document') {
            initDealDocumentsUi($('#ledger-tab-dynamic-content'));
        }
        return false;
    }
    $('#ledger-tab-dynamic-content').html('<div class="ledger-tab-loading"><?php echo addslashes(__('Loading...')); ?></div>');
    $.get(url, function(resp) {
        var parsed = $('<div>').html(resp);
        var html = '';
        if (selector && parsed.find(selector).length) {
            html = parsed.find(selector).first().html();
        }
        if (html && tabKey === 'plan') {
            var planWrap = $('<div>').html(html);
            planWrap.find('th a').each(function() {
                var txt = $(this).text();
                $(this).replaceWith($('<span/>').text(txt));
            });
            if (!planWrap.find('.js-agreed-plan-title').length) {
                planWrap.prepend('<h3 class="ledger-section-title js-agreed-plan-title"><?php echo addslashes(__('Agreed Payment Plan')); ?></h3>');
            }
            html = planWrap.html();
        }
        if (!html) {
            html = '<div class="alert alert-warning"><?php echo addslashes(__('Unable to load tab content.')); ?></div>';
        }
        ledgerTabCache[tabKey] = html;
        $('#ledger-tab-dynamic-content').html(html);
        if (tabKey === 'document') {
            initDealDocumentsUi($('#ledger-tab-dynamic-content'));
        }
    }).fail(function() {
        $('#ledger-tab-dynamic-content').html('<div class="alert alert-danger"><?php echo addslashes(__('Failed to load tab content.')); ?></div>');
    });
    return false;
}

$(document).ready(function() {
    $('#printme').off('click').on('click', function(e) {
        e.preventDefault();
        printLedgerView();
    });
    $(window).off('beforeprint.ledger afterprint.ledger')
        .on('beforeprint.ledger', prepareLedgerPrintView)
        .on('afterprint.ledger', cleanupLedgerPrintView);
    adjustMetricValueFont();
    $('.ledger-tab-link').off('click').on('click', function(e) {
        e.preventDefault();
        var $link = $(this);
        var tabKey = $link.data('tab');
        var loadUrl = $link.data('url');
        var selector = $link.data('selector');
        $('.ledger-nav-tabs li').removeClass('active');
        $link.closest('li').addClass('active');
        loadLedgerTabContent(tabKey, loadUrl, selector);
    });
    startLedgerAutoPdf(0);
});
$(window).on('load', function() {
    startLedgerAutoPdf(0);
});
$(window).on('resize', function() {
    adjustMetricValueFont();
});
</script>

<div class="panel-heading no-print">
    <div class="btn-group">
        <?php if($clientLedgerPreview){ ?>
        <span class="ledger-preview-label"><span class="fa fa-shield"></span>&nbsp;<?php echo __('Verified Client Ledger'); ?></span>
        <button type="button" class="btn btn-info" onclick="printLedgerView();"><span class="fa fa-print"></span>&nbsp;<?php echo __('Print Ledger'); ?></button>
        <button type="button" class="btn btn-success" onclick="exportLedgerPdf();"><span class="fa fa-file-pdf-o"></span>&nbsp;<?php echo __('Export as PDF'); ?></button>
        <?php if(!empty($dealId)){ ?>
        <?php echo $this->Html->link('<span class="fa fa-download"></span>&nbsp;' . __('Download PDF'), array('plugin' => false, 'admin' => false, 'controller' => 'MyDeals', 'action' => 'ledgerpdf', $dealId), array('target' => '_blank', 'rel' => 'noopener', 'escape' => false, 'class' => 'btn btn-primary')); ?>
        <?php } ?>
        <?php }else{ ?>
        <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;' . __('Back To Deals'), array('controller' => 'Deals', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-info')); ?>
        <?php echo $this->Html->link('<span class="fa fa-dollar"></span>&nbsp;' . __('Add New Payment'), array('controller' => 'deals_payments', 'action' => 'add', $dealId), array('escape' => false, 'class' => 'btn btn-success')); ?>
        <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;' . __('Edit'), '#', array('name' => 'editallfrm', 'id' => 'editallfrm', 'onclick' => "check_perform_edit('$url');", 'escape' => false, 'class' => 'btn btn-warning')); ?>
        <?php echo $this->Form->button('<span class="fa fa-trash"></span>&nbsp;' . __('Delete Selected'), array('type' => 'submit', 'class' => 'btn btn-danger', 'escape' => false, 'onclick' => "return confirm('Are you sure you want to delete selected payments?');")); ?>
        <button type="button" class="btn btn-info" onclick="printLedgerView();"><span class="fa fa-print"></span>&nbsp;<?php echo __('Print Ledger'); ?></button>
        <button type="button" class="btn btn-success" onclick="exportLedgerPdf();"><span class="fa fa-file-pdf-o"></span>&nbsp;<?php echo __('Export as PDF'); ?></button>
        <?php } ?>
    </div>
</div>

<?php echo $this->Form->create(array('name' => 'deleteallfrm', 'action' => 'deleteall')); ?>
<?php echo $this->Session->flash(); ?>

<div id="printable_content" class="ledger-wrap">
    <div id="resultDiv" class="ledger-page">
        <div class="ledger-card">
            <div class="ledger-header">
                <div class="agency-col">
                    <div class="agency-logo-box">
                        <?php if (strlen($agencyLogo) > 0) { ?>
                            <?php echo $this->Html->image($agencyLogo, array('alt' => $agencyName)); ?>
                        <?php } ?>
                    </div>
                    <div class="agency-details">
                        <h2><?php echo h($agencyName); ?></h2>
                        <?php if (strlen(trim($agencyAddress)) > 0) { ?>
                            <p><?php echo h($agencyAddress); ?></p>
                        <?php } ?>
                        <?php if(!empty($headerCenterContactParts)){ ?>
                            <p class="agency-contact-line"><?php echo h(implode(' | ', $headerCenterContactParts)); ?></p>
                        <?php } ?>
                        <?php if(strlen(trim((string)$dealStatusLabel))>0 && trim((string)$dealStatusLabel)!=='-'){ ?>
                            <p class="deal-status-line"><?php echo __('Deal Status'); ?>: <span class="deal-status-value" style="color:<?php echo h($dealStatusColor); ?>;"><?php echo h($dealStatusLabel); ?></span></p>
                        <?php } ?>
                    </div>
                </div>
                <div class="deal-qr-col">
                    <div class="deal-qr-wrap">
                        <div class="deal-qr-box">
                            <img src="<?php echo h($dealQrUrl); ?>" alt="<?php echo __('Deal QR Code'); ?>">
                        </div>
                        <div class="deal-qr-caption"><?php echo __('Scan Deal QR'); ?></div>
                    </div>
                </div>
            </div>
            <div class="ledger-title-row">
                <div class="ledger-title-meta-left"><?php echo __('Membership No'); ?>: <?php echo h($membershipNo); ?></div>
                <div class="ledger-title"><?php echo __('Deal Detaile'); ?></div>
                <div class="ledger-title-meta-right"><?php echo __('Booking Date'); ?>: <?php echo h($bookingDateLabel); ?></div>
            </div>

            <div class="detail-grid">
                <div class="detail-card detail-card-client">
                    <h4><?php echo __('Client Details'); ?></h4>
                    <div class="client-detail-body">
                        <div class="client-detail-photo">
                            <?php echo $this->Html->image($clientPhoto, array('alt' => __('Contact Photo'))); ?>
                        </div>
                        <div class="client-detail-content">
                            <div class="detail-item"><span class="detail-label"><?php echo __('Client Name'); ?></span><span class="detail-value"><?php echo h($clientName); ?></span></div>
                            <?php if ($clientFatherName !== '') { ?>
                            <div class="detail-item"><span class="detail-label"><?php echo !empty($clientRelationType) ? h($clientRelationType) : __('Father / Guardian'); ?></span><span class="detail-value"><?php echo h($clientFatherName); ?></span></div>
                            <?php } ?>
                            <div class="detail-item"><span class="detail-label"><?php echo __('Mobile'); ?></span><span class="detail-value"><?php echo h($clientMobileDisplay); ?></span></div>
                            <div class="detail-item"><span class="detail-label"><?php echo __('CNIC'); ?></span><span class="detail-value"><?php echo h($clientCnic); ?></span></div>
                        </div>
                        <div class="detail-item client-address-detail"><span class="detail-label"><?php echo __('Address'); ?></span><span class="detail-value"><?php echo h($clientAddress); ?></span></div>
                    </div>
                </div>
                <div class="detail-card detail-card-nominee">
                    <h4><?php echo __('Nominee Details'); ?></h4>
                    <div class="detail-item"><span class="detail-label"><?php echo __('Nominee Name'); ?></span><span class="detail-value"><?php echo h($nomineeName); ?></span></div>
                    <div class="detail-item"><span class="detail-label"><?php echo __('N.CNIC'); ?></span><span class="detail-value"><?php echo h($nomineeCnic); ?></span></div>
                    <div class="detail-item"><span class="detail-label"><?php echo __('N.Mobile'); ?></span><span class="detail-value"><?php echo h($nomineeMobile); ?></span></div>
                    <div class="detail-item"><span class="detail-label"><?php echo __('Relation'); ?></span><span class="detail-value"><?php echo h($nomineeRelation); ?></span></div>
                </div>
                <div class="detail-card detail-card-property">
                    <h4><?php echo __('Property Details'); ?></h4>
                    <div class="detail-item">
                        <span class="detail-label"><?php echo __('Property / File'); ?></span>
                        <span class="detail-value"><?php echo h(!empty($firstRow['Property']['name']) ? $firstRow['Property']['name'] : '-'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?php echo __('Type'); ?></span>
                        <span class="detail-value"><?php echo h($qrPropertyTypeDisplay); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?php echo __('Area'); ?></span>
                        <span class="detail-value"><?php echo strlen($areaLabel) > 0 ? h($areaLabel) : '-'; ?></span>
                    </div>
                    <?php if($hasAllotmentUnitNumber){ ?>
                    <div class="detail-item">
                        <span class="detail-label"><?php echo __('Unit No'); ?></span>
                        <span class="detail-value"><?php echo h(trim($qrPropertyTypeDisplay.' '.__('No').': '.$allotmentUnitNumberLabel)); ?></span>
                    </div>
                    <?php } ?>
                    <div class="detail-item detail-item-split<?php echo $hasDealCharges ? '' : ' no-charge'; ?>">
                        <div class="detail-split-col">
                            <span class="detail-label"><?php echo __('Property Price'); ?></span>
                            <span class="detail-value"><?php echo $currency . $this->Number->format($propertyPriceCard); ?></span>
                        </div>
                        <?php if($hasDealCharges){ ?>
                        <div class="detail-split-col detail-split-col-right">
                            <span class="detail-label"><?php echo ($dealChargeNamesRaw !== '') ? h($dealChargeNamesRaw) : __('Charges'); ?></span>
                            <span class="detail-value"><?php echo $currency . $this->Number->format($dealChargeTotal); ?></span>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="detail-item"><span class="detail-label"><?php echo __('Deal Price'); ?></span><span class="detail-value"><?php echo $currency . $this->Number->format($dealGrandAmount); ?></span></div>
                </div>
            </div>

            <ul class="nav nav-tabs ledger-nav-tabs no-print">
                <li class="active"><a class="ledger-tab-link" data-tab="paid" data-url="" data-selector="" href="#ledger-tab-paid-pane"><?php echo __('Payment Receipts'); ?></a></li>
                <li><a class="ledger-tab-link" data-tab="status_analysis" data-url="" data-selector="" href="#ledger-tab-status-pane"><i class="fa fa-line-chart" style="color:#0284c7;margin-right:4px;"></i> <?php echo __('Status Analysis'); ?></a></li>
                <?php if(!$clientLedgerPreview){ ?>
                <li><a class="ledger-tab-link" data-tab="plan" data-url="<?php echo h($paymentPlanTabUrl); ?>" data-selector="#printable_content" href="<?php echo $paymentPlanTabUrl; ?>"><?php echo __('Payment Plan'); ?></a></li>
                <?php if(!empty($dealVaultEnabled) && !empty($canManageDocs)){ ?>
                <li><a class="ledger-tab-link" data-tab="document" data-url="<?php echo h($documentTabUrl); ?>" data-selector="#deal-documents-content" href="<?php echo $documentTabUrl; ?>"><i class="fa fa-folder-open" style="color:#d97706;margin-right:4px;"></i> <?php echo __('Document Vault'); ?><?php if(!empty($dealDocuments)){ echo ' <span class="badge" style="background:#9f002d;color:#fff;font-size:10px;padding:2px 6px;border-radius:10px;margin-left:3px;">'.count($dealDocuments).'</span>'; } ?></a></li>
                <?php } ?>
                <li><a class="ledger-tab-link" data-tab="letters" data-url="<?php echo h($lettersTabUrl); ?>" data-selector="#deal-letters-content" href="<?php echo $lettersTabUrl; ?>"><?php echo __('Letters'); ?></a></li>
                <li><a class="ledger-tab-link" data-tab="communication" data-url="<?php echo h($communicationTabUrl); ?>" data-selector=".deal-comm-content" href="<?php echo $communicationTabUrl; ?>"><?php echo __('Communication'); ?></a></li>
                <?php } ?>
            </ul>

            <div id="ledger-tab-paid-pane" class="ledger-tab-pane active">
            <div class="ledger-metrics <?php echo h($metricCardsClass); ?>">
                <div class="metric-card metric-property">
                    <div class="metric-card-inner">
                        <div class="metric-title"><?php echo h($propertyCardTitle); ?></div>
                        <div class="metric-value"><span class="currency-ltr"><span class="currency-symbol"><?php echo $currency; ?></span><?php echo $this->Number->format($propertyPriceCard); ?></span></div>
                    </div>
                </div>
                <?php if($hasDealDiscount){ ?>
                <div class="metric-card metric-discount">
                    <div class="metric-card-inner">
                        <div class="metric-title"><?php echo __('Discount'); ?></div>
                        <div class="metric-value"><span class="currency-ltr"><span class="currency-symbol"><?php echo $currency; ?></span><?php echo $this->Number->format($dealDiscountAmountCard); ?></span></div>
                    </div>
                </div>
                <?php } ?>
                <?php if($hasDealCharges){ ?>
                <div class="metric-card metric-charge">
                    <div class="metric-card-inner">
                        <div class="metric-title"><?php echo h($chargeTypeLabel); ?></div>
                        <div class="metric-value"><span class="currency-ltr"><span class="currency-symbol"><?php echo $currency; ?></span><?php echo $this->Number->format($dealChargeTotal); ?></span></div>
                    </div>
                </div>
                <?php } ?>
                <div class="metric-card metric-total">
                    <div class="metric-card-inner">
                        <div class="metric-title"><?php echo __('Deal Price'); ?></div>
                        <div class="metric-value"><span class="currency-ltr"><span class="currency-symbol"><?php echo $currency; ?></span><?php echo $this->Number->format($dealTotalAmountCard); ?></span></div>
                    </div>
                </div>
                <div class="metric-card metric-paid">
                    <div class="metric-card-inner">
                        <div class="metric-title"><?php echo __('Paid (Equity)'); ?></div>
                        <div class="metric-value"><span class="currency-ltr"><span class="currency-symbol"><?php echo $currency; ?></span><?php echo $this->Number->format($paidEquity); ?></span></div>
                    </div>
                </div>
                <div class="metric-card metric-balance">
                    <div class="metric-card-inner">
                        <div class="metric-title"><?php echo __('Due'); ?></div>
                        <div class="metric-value"><span class="currency-ltr"><span class="currency-symbol"><?php echo $currency; ?></span><?php echo $this->Number->format($balanceDueCard); ?></span></div>
                    </div>
                </div>
                <div class="metric-card metric-overdue">
                    <div class="metric-card-inner">
                        <div class="metric-title"><?php echo __('Overdue'); ?></div>
                        <div class="metric-value"><span class="currency-ltr"><span class="currency-symbol"><?php echo $currency; ?></span><?php echo $this->Number->format($overdueAmountCard); ?></span></div>
                    </div>
                </div>
            </div>

            <h3 class="ledger-section-title"><?php echo __('Received Payment Transaction History'); ?></h3>

            <div class="table-responsive received-payment-history-wrap">
                <table class="table table-striped table-bordered ledger-table received-payment-history-table">
                    <thead>
                        <tr>
                            <?php if(!$clientLedgerPreview){ ?><th class="pbutton"><?php echo $this->Form->checkbox('checkbox', array('value' => 'deleteall', 'name' => 'selectAll', 'label' => false, 'id' => 'selectAll', 'hiddenField' => false)); ?></th><?php } ?>
                            <th class="ledger-seq-col"><?php echo __('#'); ?></th>
                            <th class="ledger-installment-col"><?php echo __('(Installment+Late fee)'); ?></th>
                            <th class="ledger-payment-name-col"><?php echo __('Payment Name'); ?></th>
                            <th class="ledger-paid-date-col"><?php echo __('Paid Date'); ?></th>
                            <th class="ledger-transaction-col"><?php echo __('Transaction Reference'); ?></th>
                            <th class="ledger-payment-type-col"><?php echo __('Payment Type'); ?></th>
                            <th class="pbutton"><?php echo __('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $serialNo = 0;
                        $totalPaidAmount = 0;
                        $totalLateFeesPaid = 0;
                        $totalInstallmentWithLate = 0;
                        $totalRecord = count($displayPaymentRows);
                        foreach ($displayPaymentRows as $groupRow):
                            $row = isset($groupRow['first_row']) ? $groupRow['first_row'] : array();
                            $id = isset($groupRow['id_list']) ? $groupRow['id_list'] : '';
                            $primaryId = isset($groupRow['first_id']) ? (int)$groupRow['first_id'] : 0;
                            $paidAmount = isset($groupRow['paid_amount']) ? (float)$groupRow['paid_amount'] : 0;
                            $lateFeesPaid = isset($groupRow['late_fees']) ? (float)$groupRow['late_fees'] : 0;
                            $installmentWithLate = isset($groupRow['installment_late_amount']) ? (float)$groupRow['installment_late_amount'] : round($paidAmount + $lateFeesPaid, 2);
                            $totalPaidAmount += $paidAmount;
                            $totalLateFeesPaid += $lateFeesPaid;
                            $totalInstallmentWithLate += $installmentWithLate;
                            $serialNo++;
                            $paymentName = !empty($groupRow['payment_names']) ? implode(', ', (array)$groupRow['payment_names']) : (!empty($row['PlansPayment']['name']) ? $row['PlansPayment']['name'] : (!empty($row['DealsPayment']['name']) ? $row['DealsPayment']['name'] : '-'));
                            $slipFile = !empty($groupRow['slip_file']) ? trim((string)$groupRow['slip_file']) : '';
                            $slipDir = !empty($groupRow['slip_dir']) ? trim((string)$groupRow['slip_dir']) : 'paymentslips';
                            $slipRelPath = '';
                            $slipUrl = '';
                            $slipIsImage = !empty($groupRow['slip_is_image']);
                            if($slipFile !== '')
                            {
                                $safeSlipDir = trim(str_replace('\\', '/', $slipDir), '/');
                                if($safeSlipDir === '')
                                {
                                    $safeSlipDir = 'paymentslips';
                                }
                                $slipFilePath = WWW_ROOT . 'img' . DS . str_replace('/', DS, $safeSlipDir) . DS . $slipFile;
                                if(file_exists($slipFilePath))
                                {
                                    $slipRelPath = $safeSlipDir . '/' . $slipFile;
                                    $slipUrl = '/img/' . trim($slipRelPath, '/');
                                    if(!$slipIsImage)
                                    {
                                        $slipExt = strtolower(pathinfo($slipFile, PATHINFO_EXTENSION));
                                        $slipIsImage = in_array($slipExt, array('jpg','jpeg','png','gif','webp'));
                                    }
                                }
                            }
                                ?>
                            <tr>
                                <?php if(!$clientLedgerPreview){ ?><td class="pbutton"><?php echo $this->Form->checkbox(false, array('value' => $id, 'name' => 'data[DealsPayment][id][]', 'id' => "DeleteCheckbox".$primaryId, 'class' => 'chkselect')); ?></td><?php } ?>
                                <td class="ledger-seq-col"><?php echo $serialNo; ?></td>
                                <td class="ledger-installment-col">
                                    <span class="currency-ltr"><span class="currency-symbol"><?php echo $currency; ?></span><?php echo $this->Number->format($paidAmount); ?></span>
                                    <span style="color:#888;"> + </span>
                                    <span class="currency-ltr" style="color:<?php echo $lateFeesPaid > 0 ? '#d9534f' : '#333'; ?>;"><span class="currency-symbol"><?php echo $currency; ?></span><?php echo $this->Number->format($lateFeesPaid); ?></span>
                                </td>
                                <td class="ledger-payment-name-col"><?php echo h($paymentName); ?></td>
                                <td class="ledger-paid-date-col"><?php echo !empty($row['DealsPayment']['payment_date']) ? $this->Time->format($sysDay . $dateSep . $sysMonth . $dateSep . $sysYear, $row['DealsPayment']['payment_date']) : '-'; ?></td>
                                <td class="ledger-transaction-col"><?php echo !empty($row['DealsPayment']['remarks']) ? h($row['DealsPayment']['remarks']) : '-'; ?></td>
                                <td class="ledger-payment-type-col"><?php echo !empty($row['Paymenttype']['name']) ? h($row['Paymenttype']['name']) : '-'; ?></td>
                                <td class="pbutton">
                                    <div class="history-action-buttons">
                                        <?php 
                                        $printReceiptUrl = !empty($clientLedgerPreview) || !empty($isClientPortal) 
                                            ? array('plugin' => false, 'admin' => false, 'controller' => 'MyPayments', 'action' => 'printreceipt', $id) 
                                            : array('controller' => 'deals_payments', 'action' => 'printreceipt', $id);
                                        echo $this->Html->link('<span class="fa fa-print"></span>', $printReceiptUrl, array('target' => '_blank', 'escape' => false, 'class' => 'history-action-btn history-action-btn-print', 'title' => __('Print Receipt'))); 
                                        ?>
                                        <?php if(!empty($groupRow['slip_file'])){ 
                                            $slipUrl = !empty($clientLedgerPreview) || !empty($isClientPortal)
                                                ? array('plugin' => false, 'admin' => false, 'controller' => 'MyPayments', 'action' => 'viewpaymentslip', isset($groupRow['slip_row_id']) ? $groupRow['slip_row_id'] : $primaryId)
                                                : array('controller'=>'deals_payments','action'=>'viewpaymentslip',isset($groupRow['slip_row_id']) ? $groupRow['slip_row_id'] : $primaryId);
                                            echo $this->Html->link('<span class="fa fa-paperclip"></span>', $slipUrl, array('onclick' => "window.open(this.href,'paymentSlipPopup','width=1100,height=800,scrollbars=yes,resizable=yes'); return false;", 'escape' => false, 'class' => 'history-action-btn history-action-btn-slip', 'title' => __('Payment Slip'))); 
                                        } ?>
                                        <?php if(!$clientLedgerPreview){ ?>
                                            <?php echo $this->Html->link('<span class="fa fa-edit"></span>', '#', array('name' => 'editallfrm', 'onclick' => "check_perform_sedit('$url','$id');", 'escape' => false, 'class' => 'history-action-btn history-action-btn-edit', 'title' => __('Edit'))); ?>
                                            <?php if ($totalRecord == $serialNo) { ?>
                                                <?php echo $this->Html->link('<span class="fa fa-trash"></span>', '#', array('onclick' => "check_perform_sdelete('".$primaryId."');", 'escape' => false, 'class' => 'history-action-btn history-action-btn-delete', 'title' => __('Delete'))); ?>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="summary-row">
                            <td colspan="<?php echo $clientLedgerPreview ? 1 : 2; ?>" align="left"><strong><?php echo __('Paid'); ?></strong></td>
                            <td class="ledger-installment-col"><strong><span class="currency-ltr"><span class="currency-symbol"><?php echo $currency; ?></span><?php echo $this->Number->format($totalInstallmentWithLate); ?></span></strong></td>
                            <td colspan="5">
                                <strong><?php echo __('Total Applied Late Fees (Paid)'); ?>: <span class="currency-ltr"><span class="currency-symbol"><?php echo $currency; ?></span><?php echo $this->Number->format($totalLateFeesPaid); ?></span></strong>
                            </td>
                        </tr>
                        <tr class="summary-row">
                            <td colspan="<?php echo $clientLedgerPreview ? 1 : 2; ?>" align="left"><strong><?php echo __('Balance'); ?></strong></td>
                            <td colspan="6">
                                <strong>
                                    <?php
                                    $propertyPrice = isset($firstRow['Deal']['total_amount']) ? (float)$firstRow['Deal']['total_amount'] : 0;
                                    $balanceAmount = $propertyPrice - $totalPaidAmount;
                                    echo '<span class="currency-ltr"><span class="currency-symbol">'.$currency.'</span>'.$this->Number->format($balanceAmount).'</span>';
                                    ?>
                                </strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="ledger-footer">
                <?php echo __('Print Dated / Time'); ?>: <?php echo h($reportDate); ?>
            </div>
            </div>

            <!-- Status Analysis Tab Pane -->
            <div id="ledger-tab-status-pane" class="ledger-tab-pane">
                <div class="status-analysis-wrap">
                    <?php 
                    $sa = !empty($statusAnalysis) ? $statusAnalysis : array();
                    $saKpis = !empty($sa['kpis']) ? $sa['kpis'] : array();
                    $saSchedules = !empty($sa['schedules']) ? $sa['schedules'] : array();
                    $saUnlinked = !empty($sa['unlinked_receipts']) ? $sa['unlinked_receipts'] : array();

                    $saOverdueCount = isset($saKpis['overdue_count']) ? (int)$saKpis['overdue_count'] : 0;
                    $saOverdueBalance = isset($saKpis['overdue_balance']) ? (float)$saKpis['overdue_balance'] : 0;
                    $saMaxDelay = isset($saKpis['max_delay_days']) ? (int)$saKpis['max_delay_days'] : 0;
                    $saScore = isset($saKpis['compliance_score']) ? (int)$saKpis['compliance_score'] : 100;
                    $saEarly = isset($saKpis['early_count']) ? (int)$saKpis['early_count'] : 0;
                    $saOnTime = isset($saKpis['ontime_count']) ? (int)$saKpis['ontime_count'] : 0;
                    $saLate = isset($saKpis['late_count']) ? (int)$saKpis['late_count'] : 0;
                    $saReceipts = isset($saKpis['total_receipts']) ? (int)$saKpis['total_receipts'] : 0;
                    $saTotalPlan = isset($saKpis['total_plan_amount']) ? (float)$saKpis['total_plan_amount'] : 0;
                    $saTotalPaid = isset($saKpis['total_paid_amount']) ? (float)$saKpis['total_paid_amount'] : 0;
                    $saTotalBal = isset($saKpis['total_balance_amount']) ? (float)$saKpis['total_balance_amount'] : 0;
                    ?>

                    <!-- KPI Summary Cards -->
                    <div class="status-kpi-grid">
                        <div class="status-kpi-card kpi-overdue">
                            <span><i class="fa fa-exclamation-circle text-danger"></i> <?php echo __('Overdue Installments'); ?></span>
                            <strong><?php echo $saOverdueCount; ?></strong>
                            <small><?php echo __('Unpaid past due items'); ?></small>
                        </div>
                        <div class="status-kpi-card kpi-overdue">
                            <span><i class="fa fa-money text-danger"></i> <?php echo __('Total Overdue Balance'); ?></span>
                            <strong><?php echo $currency . $this->Number->format($saOverdueBalance); ?></strong>
                            <small><?php echo __('Cumulative past due'); ?></small>
                        </div>
                        <div class="status-kpi-card kpi-delay">
                            <span><i class="fa fa-clock-o text-warning"></i> <?php echo __('Maximum Delay'); ?></span>
                            <strong><?php echo $saMaxDelay > 0 ? $saMaxDelay . ' ' . __('Days') : __('None'); ?></strong>
                            <small><?php echo __('Longest overdue duration'); ?></small>
                        </div>
                        <div class="status-kpi-card kpi-score">
                            <span><i class="fa fa-shield text-success"></i> <?php echo __('On-Time Compliance'); ?></span>
                            <strong style="color:<?php echo $saScore >= 80 ? '#059669' : ($saScore >= 50 ? '#f59e0b' : '#dc2626'); ?>;"><?php echo $saScore; ?>%</strong>
                            <small><?php echo __('Payment discipline score'); ?></small>
                        </div>
                    </div>

                    <div class="status-kpi-grid">
                        <div class="status-kpi-card kpi-early">
                            <span><i class="fa fa-bolt"></i> <?php echo __('Paid Early Count'); ?></span>
                            <strong><?php echo $saEarly; ?></strong>
                            <small><?php echo __('Before scheduled due date'); ?></small>
                        </div>
                        <div class="status-kpi-card kpi-ontime">
                            <span><i class="fa fa-check-circle"></i> <?php echo __('Paid On Time Count'); ?></span>
                            <strong><?php echo $saOnTime; ?></strong>
                            <small><?php echo __('Exact due date payments'); ?></small>
                        </div>
                        <div class="status-kpi-card kpi-late">
                            <span><i class="fa fa-exclamation-triangle"></i> <?php echo __('Paid Late / Overdue'); ?></span>
                            <strong><?php echo $saLate; ?></strong>
                            <small><?php echo __('Settled after due date'); ?></small>
                        </div>
                        <div class="status-kpi-card kpi-total">
                            <span><i class="fa fa-file-text-o"></i> <?php echo __('Verified Receipts'); ?></span>
                            <strong><?php echo $saReceipts; ?></strong>
                            <small><?php echo __('Total payments received'); ?></small>
                        </div>
                    </div>

                    <!-- Detailed Schedule & Dynamic Delay Tracking Table -->
                    <h3 class="ledger-section-title" style="margin-top:14px;"><i class="fa fa-calendar-check-o"></i> <?php echo __('Installment Schedule & Dynamic Delay Tracking'); ?></h3>

                    <div class="table-responsive" style="margin-bottom:14px;">
                        <table class="table table-striped table-bordered sa-table">
                            <thead>
                                <tr>
                                    <th style="width:30px;text-align:center;"><?php echo __('#'); ?></th>
                                    <th><?php echo __('Installment'); ?></th>
                                    <th><?php echo __('Due Date'); ?></th>
                                    <th style="text-align:right;"><?php echo __('Amount'); ?></th>
                                    <th style="text-align:right;"><?php echo __('Paid'); ?></th>
                                    <th><?php echo __('Receipt / Paid Date'); ?></th>
                                    <th style="text-align:center;"><?php echo __('Timing / Status'); ?></th>
                                    <th><?php echo __('Delay / Duration'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sn = 0;
                                foreach($saSchedules as $item):
                                    $sn++;
                                    $st = $item['status'];
                                    $dueD = !empty($item['due_date']) ? $this->Time->format($sysDay . $dateSep . $sysMonth . $dateSep . $sysYear, $item['due_date']) : '-';
                                    $rcpts = !empty($item['receipts']) ? $item['receipts'] : array();
                                ?>
                                <tr>
                                    <td style="text-align:center;font-weight:900;color:#64748b;"><?php echo $sn; ?></td>
                                    <td><strong style="color:#0f172a;"><?php echo h($item['name']); ?></strong></td>
                                    <td style="white-space:nowrap;"><i class="fa fa-calendar text-muted"></i> <?php echo $dueD; ?></td>
                                    <td style="text-align:right;font-weight:800;white-space:nowrap;"><span class="currency-ltr"><?php echo $currency . $this->Number->format($item['amount']); ?></span></td>
                                    <td style="text-align:right;color:#059669;font-weight:800;white-space:nowrap;"><span class="currency-ltr"><?php echo $currency . $this->Number->format($item['paid_amount']); ?></span></td>
                                    <td>
                                        <?php if(!empty($rcpts)){ ?>
                                            <?php foreach($rcpts as $r){ ?>
                                                <div style="display:flex;align-items:center;gap:4px;margin-bottom:2px;flex-wrap:wrap;">
                                                    <span class="badge" style="background:#0284c7;color:#ffffff!important;font-weight:900;font-size:9px;padding:3px 6px;border-radius:4px;"><?php echo h($r['receipt_no']); ?></span>
                                                    <small style="color:#475569;font-weight:700;"><?php echo !empty($r['payment_date']) ? $this->Time->format($sysDay . $dateSep . $sysMonth . $dateSep . $sysYear, $r['payment_date']) : ''; ?></small>
                                                    <small style="color:#059669;font-weight:900;">(<?php echo $currency . $this->Number->format($r['amount']); ?>)</small>
                                                </div>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <span class="text-muted" style="font-style:italic;font-size:10px;"><?php echo __('Unpaid'); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align:center;white-space:nowrap;">
                                        <?php if(!empty($rcpts)){ ?>
                                            <?php foreach($rcpts as $r){ ?>
                                                <?php if($r['timing_status'] === 'early'){ ?>
                                                    <span class="sa-badge sa-badge-early"><i class="fa fa-bolt"></i> <?php echo __('Paid Early'); ?> (<?php echo $r['diff_days']; ?>d <?php echo __('before'); ?>)</span>
                                                <?php } elseif($r['timing_status'] === 'ontime'){ ?>
                                                    <span class="sa-badge sa-badge-ontime"><i class="fa fa-check-circle"></i> <?php echo __('Paid On Time'); ?></span>
                                                <?php } elseif($r['timing_status'] === 'late'){ ?>
                                                    <span class="sa-badge sa-badge-late"><i class="fa fa-exclamation-triangle"></i> <?php echo __('Paid Late'); ?> (<?php echo $r['diff_days']; ?>d <?php echo __('late'); ?>)</span>
                                                <?php } else { ?>
                                                    <span class="sa-badge sa-badge-advance"><i class="fa fa-shield"></i> <?php echo __('Advance / Direct'); ?></span>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <?php if($st === 'overdue'){ ?>
                                                <span class="sa-badge sa-badge-overdue"><i class="fa fa-exclamation-circle"></i> <?php echo __('OVERDUE'); ?></span>
                                            <?php } elseif($st === 'due_today'){ ?>
                                                <span class="sa-badge sa-badge-duetoday"><i class="fa fa-clock-o"></i> <?php echo __('DUE TODAY'); ?></span>
                                            <?php } else { ?>
                                                <span class="sa-badge sa-badge-upcoming"><i class="fa fa-hourglass-start"></i> <?php echo __('UPCOMING'); ?></span>
                                            <?php } ?>
                                        <?php } ?>
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <?php if($st === 'overdue'){ ?>
                                            <strong style="color:#dc2626;font-size:11px;"><i class="fa fa-clock-o"></i> <?php echo $item['delay_days']; ?> <?php echo __('days past due'); ?></strong>
                                        <?php } elseif($st === 'due_today'){ ?>
                                            <strong style="color:#f59e0b;font-size:11px;"><i class="fa fa-bell"></i> <?php echo __('Due Today'); ?></strong>
                                        <?php } elseif($st === 'upcoming'){ ?>
                                            <span style="color:#64748b;font-size:11px;"><i class="fa fa-calendar-o"></i> <?php echo __('Due in'); ?> <?php echo $item['delay_days']; ?> <?php echo __('days'); ?></span>
                                        <?php } else { ?>
                                            <span style="color:#059669;font-weight:900;font-size:11px;"><i class="fa fa-check"></i> <?php echo __('Settled'); ?></span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="summary-row" style="background:#f8fafc;">
                                    <td colspan="3" style="text-align:right;"><strong><?php echo __('Totals'); ?>:</strong></td>
                                    <td style="text-align:right;white-space:nowrap;"><strong><?php echo $currency . $this->Number->format($saTotalPlan); ?></strong></td>
                                    <td style="text-align:right;color:#059669;white-space:nowrap;"><strong><?php echo $currency . $this->Number->format($saTotalPaid); ?></strong></td>
                                    <td colspan="3">
                                        <strong><?php echo __('Overdue Outstanding'); ?>: <span style="color:#dc2626;"><?php echo $currency . $this->Number->format($saOverdueBalance); ?></span> (<?php echo $saOverdueCount; ?> <?php echo __('Installments'); ?>)</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php if(!empty($saUnlinked)){ ?>
                        <h4 class="ledger-section-title" style="margin-top:14px;"><i class="fa fa-shield"></i> <?php echo __('Direct / Advance Deposits'); ?></h4>
                        <div class="table-responsive" style="margin-bottom:14px;">
                            <table class="table table-striped table-bordered ledger-table">
                                <thead>
                                    <tr>
                                        <th><?php echo __('Receipt No.'); ?></th>
                                        <th><?php echo __('Payment Date'); ?></th>
                                        <th style="text-align:right;"><?php echo __('Amount'); ?></th>
                                        <th><?php echo __('Payment Type'); ?></th>
                                        <th><?php echo __('Remarks / Notes'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($saUnlinked as $unl): ?>
                                    <tr>
                                        <td><span class="badge" style="background:#6366f1;color:#fff;font-size:10px;"><?php echo h($unl['receipt_no']); ?></span></td>
                                        <td><?php echo !empty($unl['payment_date']) ? $this->Time->format($sysDay . $dateSep . $sysMonth . $dateSep . $sysYear, $unl['payment_date']) : '-'; ?></td>
                                        <td style="text-align:right;font-weight:700;"><span class="currency-ltr"><?php echo $currency . $this->Number->format($unl['amount']); ?></span></td>
                                        <td><?php echo h($unl['payment_type']); ?></td>
                                        <td><?php echo h($unl['remarks']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>

                    <div class="ledger-footer">
                        <?php echo __('Analysis Generated At'); ?>: <?php echo h($reportDate); ?>
                    </div>
                </div>
            </div>

            <div id="ledger-tab-dynamic-pane" class="ledger-tab-pane">
                <div id="ledger-tab-dynamic-content" class="ledger-tab-dynamic"></div>
            </div>
        </div>
    </div>
</div>

<?php echo $this->Form->input('dealId', array('type' => 'hidden', 'name' => 'dealId', 'value' => $dealId)); ?>
<?php echo $this->Form->end(); ?>
