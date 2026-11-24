<?php echo $this->Html->css('print.css'); ?>
<?php
$resolveImage = function($candidates) {
    foreach ((array)$candidates as $candidate) {
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

$project = !empty($agencyProject) ? (array)$agencyProject : array();
$agencyName = !empty($project['name']) ? $project['name'] : $siteOrganization;
$agencyAddress = !empty($project['address']) ? $project['address'] : $siteAddress;
$agencyPhone = !empty($project['phone']) ? $project['phone'] : '';
$agencyMobile = !empty($project['mobile']) ? $project['mobile'] : '';
$agencyEmail = !empty($project['email']) ? $project['email'] : '';
$headerCenterContactParts = array();
if (strlen(trim($agencyMobile)) > 0) {
    $headerCenterContactParts[] = __('Mobile') . ': ' . $agencyMobile;
}
if (strlen(trim($agencyEmail)) > 0) {
    $headerCenterContactParts[] = __('Email') . ': ' . $agencyEmail;
}
if (empty($headerCenterContactParts) && strlen(trim($agencyPhone)) > 0) {
    $headerCenterContactParts[] = __('Phone') . ': ' . $agencyPhone;
}

$agencyLogo = $resolveImage(array(
    !empty($agencyLogoFile) ? 'user_thumb/' . $agencyLogoFile : '',
    !empty($agencyLogoFile) ? $agencyLogoFile : '',
    !empty($project['logo']) ? 'agency_thumb/' . $project['logo'] : '',
    !empty($project['logo']) ? $project['logo'] : '',
    !empty($project['image']) ? 'agency_thumb/' . $project['image'] : '',
    !empty($project['image']) ? $project['image'] : '',
    !empty($project['photo']) ? 'agency_thumb/' . $project['photo'] : '',
    !empty($project['photo']) ? $project['photo'] : ''
));

$membershipNo = !empty($post['Deal']['invoice_no']) ? $post['Deal']['invoice_no'] : '-';
$clientName = !empty($post['Client']['name']) ? $post['Client']['name'] : '-';
$clientCnic = !empty($post['Client']['pincode']) ? $post['Client']['pincode'] : '-';
$dealStatusLabel = '-';
if (!empty($post['Deal']['deal_status'])) {
    $dealStatusLabel = $post['Deal']['deal_status'];
} elseif (!empty($post['Deal']['status'])) {
    $dealStatusLabel = $post['Deal']['status'];
}
$qrPropertyFile = !empty($post['Property']['name']) ? $post['Property']['name'] : '-';
$qrPropertyArea = trim((!empty($post['Property']['area']) ? $post['Property']['area'] : '') . ' ' . (!empty($post['Unit']['name']) ? $post['Unit']['name'] : ''));
if ($qrPropertyArea === '') {
    $qrPropertyArea = '-';
}
$qrPropertyType = !empty($post['Type']['name']) ? $post['Type']['name'] : (!empty($post['Property']['type']) ? $post['Property']['type'] : '-');
$qrContractType = !empty($post['Contract']['name']) ? $post['Contract']['name'] : '-';
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
    __('Property Type') . ': ' . $qrPropertyType,
    __('Contract Type') . ': ' . $qrContractType
);
$dealQrText = implode("\n", $dealQrLines);
$dealQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&ecc=L&qzone=2&margin=8&format=png&charset-source=UTF-8&charset-target=UTF-8&data=' . rawurlencode($dealQrText) . '&v=' . rawurlencode((string)$post['Deal']['id']) . '&cb=' . rawurlencode(date('YmdHis'));
$dealChargeNames = isset($dealChargeNames) && is_array($dealChargeNames) ? $dealChargeNames : array();
$dealChargeTotal = isset($dealChargeTotal) ? round((float)$dealChargeTotal, 2) : 0;
$hasDealCharges = ($dealChargeTotal > 0 && !empty($dealChargeNames));
$dealChargeNameText = $hasDealCharges ? implode(', ', $dealChargeNames) : '';
$translateDealChargeUrdu = function($chargeName) {
    $chargeName = trim((string)$chargeName);
    if($chargeName === '')
    {
        return '';
    }
    $key = strtolower(trim(preg_replace('/\s+/', ' ', str_replace(array('-', '_'), ' ', $chargeName))));
    $map = array(
        'extra work' => 'اضافی کام',
        'additional work' => 'اضافی کام',
        'development charges' => 'ترقیاتی چارجز',
        'development charge' => 'ترقیاتی چارجز',
        'development' => 'ترقیاتی چارجز',
        'processing fee' => 'پروسیسنگ فیس',
        'deal transfer processing fee' => 'ڈیل ٹرانسفر پروسیسنگ فیس',
        'transfer fee' => 'ٹرانسفر فیس',
        'maintenance charges' => 'مینٹیننس چارجز',
        'maintenance charge' => 'مینٹیننس چارجز',
        'late fee' => 'لیٹ فیس',
        'corner charges' => 'کارنر چارجز',
        'corner charge' => 'کارنر چارجز',
        'floor charges' => 'فلور چارجز',
        'floor charge' => 'فلور چارجز',
        'possession charges' => 'قبضہ چارجز',
        'possession charge' => 'قبضہ چارجز'
    );
    return isset($map[$key]) ? $map[$key] : $chargeName;
};
$dealChargeUrduNames = array();
foreach($dealChargeNames as $dealChargeName)
{
    $translatedChargeName = $translateDealChargeUrdu($dealChargeName);
    if($translatedChargeName !== '')
    {
        $dealChargeUrduNames[$translatedChargeName] = $translatedChargeName;
    }
}
$dealChargeUrduNameText = !empty($dealChargeUrduNames) ? implode('، ', array_values($dealChargeUrduNames)) : $dealChargeNameText;
$dealChargeCurrencyLabel = trim((string)$currency);
if(stripos($dealChargeCurrencyLabel, 'rs') === 0)
{
    $dealChargeCurrencyLabel = 'Rs.';
}
$dealChargeAgreementAmount = trim($dealChargeCurrencyLabel . ' ' . $this->Number->format($dealChargeTotal)) . '/-';
$dealGrandTotalAmount = isset($post['Deal']['total_amount']) ? round((float)$post['Deal']['total_amount'], 2) : 0;
$dealChargeAgreementGrandTotal = trim($dealChargeCurrencyLabel . ' ' . $this->Number->format($dealGrandTotalAmount)) . '/-';
$numToWordHelper = isset($this->NumToWord) ? $this->NumToWord : null;
$formatUrduAmountWords = function($amount) use ($numToWordHelper) {
    if(!is_object($numToWordHelper) || !method_exists($numToWordHelper, 'NumberToUrduWord'))
    {
        return '';
    }
    return trim((string)$numToWordHelper->NumberToUrduWord($amount));
};
$dealChargeAgreementAmountWordsUrdu = $formatUrduAmountWords($dealChargeTotal);
$dealChargeAgreementGrandTotalWordsUrdu = $formatUrduAmountWords($dealGrandTotalAmount);
?>
<style type="text/css">
.table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td { border-top: 0px solid #ddd; }
.recipt_footer { position:relative; top: 0; }
.plan-status-text {
    background: transparent !important;
    padding: 0 !important;
    border: 0 !important;
    font-size: 12px;
    font-weight: 600;
}
.plan-status-paid { color: #1f7a32; }
.plan-status-overdue { color: #a94442; }
.plan-status-unpaid { color: #8a6d3b; }
.footer-signature-table td,
.footer-signature-table p,
.footer-signature-table strong {
    font-size: 13px;
    line-height: 1.45;
}
.footer-signature-left,
.footer-signature-left p,
.footer-signature-left strong { text-align: left !important; }
.footer-signature-right,
.footer-signature-right p,
.footer-signature-right strong { text-align: left !important; }
.footer-signature-table {
    margin-top: 6px;
    border-top: 1px solid #d5deea;
    padding-top: 4px;
}
.footer-signature-heading {
    font-size: 14px;
    font-weight: 700;
    color: #1f2f40;
    text-align: left;
    padding: 2px 0 4px;
}
.footer-signature-col {
    width: 50%;
    vertical-align: top;
    text-align: left !important;
}
.footer-signature-col-left {
    padding-right: 16px;
}
.footer-signature-col-right {
    padding-left: 16px;
}
.footer-signature-lines {
    margin: 0;
    text-align: left !important;
    line-height: 1.45;
}
.footer-signature-title {
    margin: 0 0 4px;
    font-size: 14px;
    font-weight: 700;
    color: #1f2f40;
    text-transform: uppercase;
    text-align: left !important;
}
.footer-signature-title strong {
    text-decoration: underline;
    text-underline-offset: 2px;
}
.footer-signature-address {
    margin: 4px 0 0;
    text-align: left !important;
    line-height: 1.45;
    word-break: break-word;
}
.footer-signature-spacer-row td {
    height: 84px;
    padding: 0 !important;
    border: 0 !important;
}
.urdu-note {
    font-family: "Jameel Noori Nastaleeq", "Jameel Noori Nastaleeq Regular", "Noto Nastaliq Urdu", serif !important;
    font-size: 20px;
    line-height: 1.9;
    direction: rtl;
    unicode-bidi: plaintext;
    text-align: center;
    margin: 0 auto;
}
.invoice-header {
    --qr-size: 1in;
    --qr-box-pad: 4px;
    --qr-box-border: 1px;
    --qr-total-height: calc(var(--qr-size) + (var(--qr-box-pad) * 2) + (var(--qr-box-border) * 2));
    border-bottom: 1px solid #dde4ec;
    padding: 4px 0 8px;
    margin-bottom: 8px;
    display: table;
    width: 100%;
    table-layout: fixed;
}
.agency-col {
    display: table-cell;
    width: 78%;
    vertical-align: middle;
    white-space: nowrap;
}
.deal-qr-col {
    display: table-cell;
    width: 22%;
    text-align: right;
    vertical-align: top;
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
    margin-right: 14px;
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
    display: inline-block;
    vertical-align: middle;
    width: calc(100% - 146px);
    overflow: hidden;
    text-align: left;
}
.agency-details h2 {
    margin: 0 0 3px;
    color: #000;
    font-size: 22px;
    font-weight: 800;
    line-height: 1.08;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.agency-details p {
    margin: 0 0 2px;
    color: #000;
    font-size: 11px;
}
.agency-contact-line {
    color: #000;
    font-size: 11px;
    font-weight: 600;
}
.invoice-header .deal-qr-caption {
    color: #000;
}
.invoice-summary-card {
    border: 1px solid #d4dee9;
    border-radius: 10px;
    background: linear-gradient(180deg, #fdfefe 0%, #f8fbff 100%);
    padding: 10px 12px 8px;
    margin: 10px 0 12px;
    box-shadow: 0 2px 8px rgba(14, 31, 53, 0.06);
    page-break-inside: avoid;
    overflow-x: visible;
}
.invoice-summary-title {
    text-align: center;
    font-size: 30px;
    font-weight: 800;
    letter-spacing: 0.5px;
    color: #17324f;
    margin: 0 0 7px;
    text-transform: uppercase;
    line-height: 1.1;
}
.invoice-summary-table {
    margin-bottom: 0;
    table-layout: fixed;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.invoice-summary-grid {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: nowrap;
    width: 100%;
    min-width: 0;
}
.invoice-summary-left-col,
.invoice-summary-right-col {
    display: block;
    vertical-align: top;
    min-width: 0;
}
.invoice-summary-left-col {
    flex: 0 0 74%;
    max-width: 74%;
    padding-right: 2px;
}
.invoice-summary-right-col {
    flex: 0 0 26%;
    max-width: 26%;
    margin-left: auto;
}
.invoice-summary-table > tbody > tr > td {
    border-top: 0 !important;
    padding: 5px 7px;
    vertical-align: top;
    line-height: 1.3;
}
.invoice-summary-left td.summary-left-label-cell {
    width: 29%;
    text-align: left;
    padding-left: 2px;
}
.invoice-summary-left td.summary-left-value-cell {
    width: 71%;
    text-align: left;
    padding-right: 2px;
}
.invoice-summary-right td.summary-right-label-cell {
    width: 46%;
    text-align: right;
    white-space: nowrap;
    border-left: 1px solid #d7e2ee;
    padding-left: 6px;
    padding-right: 3px;
}
.invoice-summary-right td.summary-right-value-cell {
    width: 54%;
    text-align: left;
    white-space: nowrap;
    padding-left: 2px;
    padding-right: 2px;
}
.invoice-summary-table > tbody > tr:nth-child(odd):not(.invoice-summary-divider) > td {
    background: rgba(237, 244, 251, 0.42);
}
.summary-value-client {
    display: block;
    white-space: normal;
    word-break: break-word;
    line-height: 1.25;
    text-align: left;
}
.summary-label {
    display: inline-block;
    font-size: 13px;
    color: #1d3f62;
    font-weight: 800;
    letter-spacing: 0.2px;
    white-space: nowrap;
}
.summary-value {
    display: inline-block;
    font-size: 13px;
    color: #1f2f40;
    font-weight: 600;
}
.summary-value-strong {
    font-size: 15px;
    font-weight: 800;
    color: #17324f;
}
.summary-amount-row {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    flex-wrap: nowrap;
    margin: 1px 0 0;
}
.summary-amount-card {
    border: 0;
    border-radius: 6px;
    background: #eaf2ff;
    min-width: 0;
    max-width: none;
    flex: 1 1 0;
    padding: 6px 8px;
    text-align: center;
    box-shadow: none;
}
.summary-charge-card {
    background: #fff4df;
}
.summary-amount-row-full {
    width: 100%;
    margin-top: 6px;
}
.summary-amount-row-full .summary-property-card {
    flex: .95 1 0;
}
.summary-amount-row-full .summary-charge-card {
    flex: 1 1 0;
}
.summary-total-card {
    flex: 1.35 1 0;
}
.summary-total-card .summary-amount-title,
.summary-total-card .summary-amount-value {
    white-space: nowrap;
}
.summary-charge-card .summary-amount-title {
    max-width: 58%;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}
.charge-agreement-note {
    margin-bottom: 7px;
    font-size: 18px;
    line-height: 1.8;
}
.charge-agreement-amount {
    direction: ltr;
    unicode-bidi: isolate;
    display: inline-block;
    white-space: nowrap;
}
.charge-agreement-words {
    direction: rtl;
    unicode-bidi: isolate;
    font-weight: 800;
    color: #000;
}
.summary-amount-title {
    display: inline-block;
    font-size: 12px;
    color: #1d3f62;
    font-weight: 800;
    letter-spacing: 0.2px;
    text-transform: none;
    margin: 0 4px 0 0;
}
.summary-amount-value {
    display: inline-block;
    font-size: 14px;
    color: #13273d;
    font-weight: 700;
    line-height: 1.25;
    white-space: nowrap;
}
.summary-left-amounts-cell {
    padding-left: 2px !important;
    padding-right: 2px !important;
}
.invoice-summary-divider td {
    padding: 3px 0 5px !important;
    background: transparent !important;
}
.invoice-summary-divider .line {
    height: 1px;
    background: #cdd9e6;
}
@media (max-width: 767px) {
    .invoice-summary-card {
        padding: 8px 8px 6px;
    }
    .invoice-summary-title {
        font-size: 20px;
    }
    .invoice-summary-table > tbody > tr > td {
        padding: 4px 5px;
    }
    .summary-label,
    .summary-value {
        font-size: 11px;
    }
    .summary-amount-row {
        gap: 6px;
    }
    .summary-amount-card {
        width: auto;
        padding: 5px 6px;
    }
    .summary-charge-card {
        background: #f7f7f7 !important;
    }
    .charge-agreement-note {
        font-size: 16px !important;
        color: #000 !important;
    }
    .charge-agreement-amount,
    .charge-agreement-words {
        color: #000 !important;
    }
    .summary-amount-title {
        font-size: 10px;
    }
    .summary-amount-value {
        font-size: 12px;
    }
}
@media print {
    .invoice-summary-card {
        border: 1px solid #6b7480 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        background: #fff !important;
        margin: 8px 0 10px !important;
        padding: 8px 8px 6px !important;
    }
    .invoice-summary-title {
        font-size: 24px !important;
        color: #000 !important;
        margin-bottom: 5px !important;
    }
    .invoice-summary-table > tbody > tr:nth-child(odd):not(.invoice-summary-divider) > td {
        background: transparent !important;
    }
    .invoice-summary-table > tbody > tr > td {
        padding: 4px 6px !important;
    }
    .invoice-summary-table > tbody > tr > td.summary-right-label-cell {
        border-left: 1px solid #818a95 !important;
        text-align: right !important;
        white-space: nowrap !important;
        padding-left: 4px !important;
        padding-right: 3px !important;
    }
    .invoice-summary-table > tbody > tr > td.summary-right-value-cell {
        text-align: left !important;
        white-space: nowrap !important;
        padding-left: 2px !important;
        padding-right: 2px !important;
    }
    .summary-value-client {
        display: block !important;
        white-space: normal !important;
        word-break: break-word !important;
        line-height: 1.25 !important;
    }
    .summary-label,
    .summary-value {
        font-size: 12px !important;
        color: #000 !important;
    }
    .summary-amount-card {
        border: 0 !important;
        border-radius: 0 !important;
        background: #f3f3f3 !important;
        box-shadow: none !important;
        min-width: 0 !important;
        padding: 4px 6px !important;
    }
    .summary-amount-row-full {
        margin-top: 5px !important;
    }
    .summary-total-card {
        flex: 1.45 1 0 !important;
    }
    .summary-amount-title {
        font-size: 11px !important;
        color: #000 !important;
        margin: 0 4px 0 0 !important;
    }
    .summary-amount-value {
        font-size: 12px !important;
        color: #000 !important;
    }
    .summary-value-strong {
        font-size: 13px !important;
    }
}
</style>

<div class="invoice-header">
    <div class="agency-col">
        <div class="agency-logo-box">
            <?php if (strlen($agencyLogo) > 0) { ?>
                <?php echo $this->Html->image($agencyLogo, array('alt' => $agencyName)); ?>
            <?php } elseif (!empty($frontLogo)) { ?>
                <?php echo $this->Html->image($frontLogo, array('alt' => $agencyName)); ?>
            <?php } ?>
        </div>
        <div class="agency-details">
            <h2><?php echo h($agencyName); ?></h2>
            <?php if (strlen(trim($agencyAddress)) > 0) { ?>
                <p><?php echo h($agencyAddress); ?></p>
            <?php } ?>
            <?php if (!empty($headerCenterContactParts)) { ?>
                <p class="agency-contact-line"><?php echo h(implode(' | ', $headerCenterContactParts)); ?></p>
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

<div class="invoice-summary-card">
    <h3 class="invoice-summary-title"><?php echo __('AGREED PAYMENT PLAN'); ?></h3>
    <div class="invoice-summary-grid">
        <div class="invoice-summary-left-col">
            <table width="100%" class="table invoice-summary-table invoice-summary-left">
                <tr>
                    <td valign="top" align="left" class="summary-left-label-cell"><span class="summary-label"><?php echo __('MEMBERSHIP NO'); ?></span></td>
                    <td valign="top" align="left" class="summary-left-value-cell"><span class="summary-value"><?php echo $post['Deal']['invoice_no']; ?></span></td>
                </tr>
                <tr>
                    <td align="left" valign="top" class="summary-left-label-cell"><span class="summary-label"><?php echo __('Client Name'); ?></span></td>
                    <td align="left" valign="top" class="summary-left-value-cell"><span class="summary-value summary-value-client"><?php echo h($post['Client']['name']); ?></span></td>
                </tr>
                <?php if (!empty($post['Client']['fathername'])) { ?>
                <tr>
                    <td align="left" valign="top" class="summary-left-label-cell"><span class="summary-label"><?php echo !empty($post['Client']['relation_type']) ? h($post['Client']['relation_type']).' '.__('Name') : __('Father / Guardian'); ?></span></td>
                    <td align="left" valign="top" class="summary-left-value-cell"><span class="summary-value"><?php echo h($post['Client']['fathername']); ?></span></td>
                </tr>
                <?php } ?>
                <tr>
                    <td class="summary-left-label-cell"><span class="summary-label"><?php echo __('C.N.I.C NO'); ?></span></td>
                    <td class="summary-left-value-cell"><span class="summary-value"><?php echo h($post['Client']['pincode']); ?></span></td>
                </tr>
                <tr class="invoice-summary-divider">
                    <td colspan="2"><div class="line"></div></td>
                </tr>
                <tr>
                    <td align="left" valign="top" class="summary-left-label-cell"><span class="summary-label"><?php echo __('Property File #'); ?></span></td>
                    <td align="left" valign="top" class="summary-left-value-cell"><span class="summary-value"><?php echo h($post['Property']['name']); ?></span></td>
                </tr>
                <tr>
                    <td align="left" valign="top" class="summary-left-label-cell"><span class="summary-label"><?php echo __('Property Type'); ?></span></td>
                    <td align="left" valign="top" class="summary-left-value-cell"><span class="summary-value"><?php echo h($post['Type']['name']); ?>&nbsp;<?php echo __('For'); ?>&nbsp;<?php echo h($post['Contract']['name']); ?></span></td>
                </tr>
                <?php if(!$hasDealCharges) { ?>
                <tr>
                    <td colspan="2" valign="top" align="left" class="summary-left-value-cell summary-left-amounts-cell">
                        <div class="summary-amount-row">
                            <div class="summary-amount-card summary-property-card">
                                <span class="summary-amount-title"><?php echo __('Property Price'); ?></span>
                                <span class="summary-amount-value"><?php echo $currency . $this->Number->format($post['Property']['price']); ?></span>
                            </div>
                            <div class="summary-amount-card summary-total-card">
                                <span class="summary-amount-title"><?php echo __('Deal Total Amount'); ?></span>
                                <span class="summary-amount-value summary-value-strong"><?php echo $currency . $this->Number->format($post['Deal']['total_amount']); ?></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
        <div class="invoice-summary-right-col">
            <table width="100%" class="table invoice-summary-table invoice-summary-right">
                <tr>
                    <td align="left" valign="top" class="summary-right-label-cell"><span class="summary-label"><?php echo __('Booking Date'); ?></span></td>
                    <td align="left" valign="top" class="summary-right-value-cell"><span class="summary-value"><?php echo $this->Time->format($sysDay . $dateSep . $sysMonth . $dateSep . $sysYear, h($post['Deal']['date'])); ?></span></td>
                </tr>
                <tr>
                    <td align="left" valign="top" class="summary-right-label-cell"><span class="summary-label"><?php echo __('Mobile#'); ?></span></td>
                    <td align="left" valign="top" class="summary-right-value-cell"><span class="summary-value"><?php echo h($post['Client']['phone']); ?></span></td>
                </tr>
                <tr>
                    <td align="left" valign="top" class="summary-right-label-cell"><span class="summary-label"><?php echo __('Cell#'); ?></span></td>
                    <td align="left" valign="top" class="summary-right-value-cell"><span class="summary-value"><?php echo h($post['Client']['mobile']); ?></span></td>
                </tr>
                <tr class="invoice-summary-divider">
                    <td colspan="2"><div class="line"></div></td>
                </tr>
                <tr>
                    <td align="left" valign="top" class="summary-right-label-cell"><span class="summary-label"><?php echo __('Floor/Area'); ?></span></td>
                    <td align="left" valign="top" class="summary-right-value-cell"><span class="summary-value"><?php echo $post['Property']['area'] . ' ' . $post['Unit']['name']; ?></span></td>
                </tr>
                <tr>
                    <td align="left" valign="top" class="summary-right-label-cell"><span class="summary-label"><?php echo __('Discount'); ?></span></td>
                    <td valign="top" align="left" class="summary-right-value-cell"><span class="summary-value"><?php echo $currency . $this->Number->format($post['Deal']['discount']); ?></span></td>
                </tr>
            </table>
        </div>
    </div>
    <?php if($hasDealCharges) { ?>
    <div class="summary-amount-row summary-amount-row-full">
        <div class="summary-amount-card summary-property-card">
            <span class="summary-amount-title"><?php echo __('Property Price'); ?></span>
            <span class="summary-amount-value"><?php echo $currency . $this->Number->format($post['Property']['price']); ?></span>
        </div>
        <div class="summary-amount-card summary-charge-card">
            <span class="summary-amount-title" title="<?php echo h($dealChargeNameText); ?>"><?php echo h($dealChargeNameText); ?></span>
            <span class="summary-amount-value"><?php echo $currency . $this->Number->format($dealChargeTotal); ?></span>
        </div>
        <div class="summary-amount-card summary-total-card">
            <span class="summary-amount-title"><?php echo __('Deal Total Amount'); ?></span>
            <span class="summary-amount-value summary-value-strong"><?php echo $currency . $this->Number->format($post['Deal']['total_amount']); ?></span>
        </div>
    </div>
    <?php } ?>
</div>
<?php if($plansPaymentArr) { ?>
<table width="100%" class="table" border="1">
    <tr>
        <td colspan="7" align="Center"><strong><?php echo __('PAYMENT DETAILS'); ?></strong></td>
    </tr>
    <tr>
        <th align="left" valign="top"><?php echo __('#'); ?></th>
        <th align="left" valign="top"><?php echo __('Installment'); ?></th>
        <th align="left" valign="top"><?php echo __('Scheduled Amount'); ?></th>
        <th align="left" valign="top"><?php echo __('Paid Amount'); ?></th>
        <th align="left" valign="top"><?php echo __('Balance'); ?></th>
        <th align="left" valign="top"><?php echo __('Due Date'); ?></th>
        <th align="left" valign="top"><?php echo __('Status'); ?></th>
    </tr>
    <?php
    $serialNo = 0;
    $totalScheduled = 0;
    $totalPaid = 0;
    $totalPending = 0;
    foreach($plansPaymentArr as $value):
        $serialNo++;
        $planId = isset($value['PlansPayment']['id']) ? (int)$value['PlansPayment']['id'] : 0;
        $scheduledAmt = (float)$value['PlansPayment']['amount'];
        $paidAmt = isset($planStatusMap[$planId]['paid']) ? (float)$planStatusMap[$planId]['paid'] : 0;
        $pendingAmt = isset($planStatusMap[$planId]['pending']) ? (float)$planStatusMap[$planId]['pending'] : max(0, $scheduledAmt - $paidAmt);

        $totalScheduled += $scheduledAmt;
        $totalPaid += $paidAmt;
        $totalPending += $pendingAmt;

        $statusText = 'Un-Paid';
        $statusClass = 'plan-status-unpaid';
        if($planId > 0 && !empty($planStatusMap[$planId]['status']))
        {
            $statusText = $planStatusMap[$planId]['status'];
        }
        if($statusText === 'Paid')
        {
            $statusClass = 'plan-status-paid';
        }
        elseif($statusText === 'Partial')
        {
            $statusClass = 'plan-status-partial';
        }
        elseif($statusText === 'Overdue')
        {
            $statusClass = 'plan-status-overdue';
        }
    ?>
    <tr>
        <td align="left" valign="top"><?php echo $serialNo; ?></td>
        <td align="left" valign="top"><?php echo h($value['PlansPayment']['name']); ?></td>
        <td align="left" valign="top"><?php echo $currency . $this->Number->format($scheduledAmt); ?></td>
        <td align="left" valign="top"><strong><?php echo $currency . $this->Number->format($paidAmt); ?></strong></td>
        <td align="left" valign="top"><?php echo $currency . $this->Number->format($pendingAmt); ?></td>
        <td align="left" valign="top"><?php echo $this->Time->format($sysDay . $dateSep . $sysMonth . $dateSep . $sysYear, h($value['PlansPayment']['date'])); ?></td>
        <td align="left" valign="top"><span class="plan-status-text <?php echo $statusClass; ?>"><?php echo __($statusText); ?></span></td>
    </tr>
    <?php endforeach; unset($value); ?>
    <tr style="font-weight:bold;background:#f9f9f9;">
        <td colspan="2" align="right"><strong><?php echo __('Total'); ?></strong></td>
        <td><strong><?php echo $currency . $this->Number->format($totalScheduled); ?></strong></td>
        <td><strong><?php echo $currency . $this->Number->format($totalPaid); ?></strong></td>
        <td><strong><?php echo $currency . $this->Number->format($totalPending); ?></strong></td>
        <td colspan="2"></td>
    </tr>
</table>
<?php } ?>

<table width="100%" class="table footer-signature-table">
    <tr>
        <td colspan="4" align="center" valign="top">
            <?php if($hasDealCharges) { ?>
            <p class="urdu-note charge-agreement-note"><strong><?php echo h($dealChargeUrduNameText); ?> کی مد میں <span class="charge-agreement-amount"><?php echo h($dealChargeAgreementAmount); ?></span><?php if($dealChargeAgreementAmountWordsUrdu !== ''): ?> <span class="charge-agreement-words">(<?php echo h($dealChargeAgreementAmountWordsUrdu); ?>)</span><?php endif; ?> فریقین کی باہمی رضامندی اور مکمل سمجھ بوجھ کے ساتھ طے پائے، جو اصل ڈیل کی رقم کے علاوہ خریدار / کلائنٹ ادا کرنے کا پابند ہوگا۔ ڈیل بمع چارجز کل رقم <span class="charge-agreement-amount"><?php echo h($dealChargeAgreementGrandTotal); ?></span><?php if($dealChargeAgreementGrandTotalWordsUrdu !== ''): ?> <span class="charge-agreement-words">(<?php echo h($dealChargeAgreementGrandTotalWordsUrdu); ?>)</span><?php endif; ?> ہوگی۔</strong></p>
            <?php } ?>
            <p class="urdu-note"><strong>اپنی قسط ہر ماہ کی مقررہ تاریخ تک جمع کروائیں۔ مقررہ تاریخ کے بعد لیٹ فیس/جرمانے کے ساتھ قسط جمع ہوگی۔</strong></p>
        </td>
    </tr>
    <tr class="footer-signature-spacer-row">
        <td colspan="4"></td>
    </tr>
    <tr>
        <td colspan="2" valign="top" align="LEFT" class="footer-signature-col footer-signature-col-left">
            <div class="recipt_footer footer-signature-left">
                <p class="footer-signature-title"><strong><?php echo __('Signture'); ?></strong></p>
                <p class="footer-signature-lines">
                <?php echo __('Thanking You,'); ?><br>
                <?php echo __('Yours faithfully,'); ?><br>
                <strong><?php echo __('For M/s.') . ' ' . $siteOrganization; ?></strong>
                </p>
            </div>
        </td>
        <td colspan="2" valign="top" align="LEFT" class="footer-signature-col footer-signature-col-right">
            <div class="recipt_footer footer-signature-right">
                <p class="footer-signature-title"><strong><?php echo __('APPLICANT Signature'); ?></strong></p>
                <p class="footer-signature-lines"><strong><?php echo ($post['Client']['name']); ?></strong></p>
                <p class="footer-signature-address"><?php echo ($post['Client']['address']); ?></p>
            </div>
        </td>
    </tr>
</table>

<script type="text/javascript">
setTimeout(function(){if (typeof(window.print) != 'undefined') {
    window.print();
}}, 300);
</script>

