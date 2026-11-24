<?php
$receiptNo=isset($post['DealsPayment']['receipt_no']) ? $post['DealsPayment']['receipt_no'] : '';
$agencyName = !empty($post['Project']['name']) ? (string)$post['Project']['name'] : (string)$siteOrganization;
$agencyAddress = !empty($post['Project']['address']) ? (string)$post['Project']['address'] : (string)$siteAddress;
$agencyPhone = !empty($post['Project']['phone']) ? trim((string)$post['Project']['phone']) : '';
$agencyMobile = !empty($post['Project']['mobile']) ? trim((string)$post['Project']['mobile']) : '';
$agencyEmail = !empty($post['Project']['email']) ? trim((string)$post['Project']['email']) : '';
$agencyLogoFile = isset($agencyLogoFile) ? trim((string)$agencyLogoFile) : '';
$agencyContactParts = array();
if($agencyMobile !== '')
{
    $agencyContactParts[] = __('Mobile').': '.$agencyMobile;
}
if($agencyEmail !== '')
{
    $agencyContactParts[] = __('Email').': '.$agencyEmail;
}
if(empty($agencyContactParts) && $agencyPhone !== '')
{
    $agencyContactParts[] = __('Phone').': '.$agencyPhone;
}
$agencyContactLine = !empty($agencyContactParts) ? implode(' | ', $agencyContactParts) : '';
$receiptPropertyText = !empty($post['Property']['name']) ? trim((string)$post['Property']['name']) : '-';
$receiptAreaText = trim((!empty($post['Property']['area']) ? (string)$post['Property']['area'] : '').' '.(!empty($post['Unit']['name']) ? (string)$post['Unit']['name'] : ''));
$receiptPropertyTypeText = !empty($post['Type']['name']) ? trim((string)$post['Type']['name']) : (!empty($post['Property']['type']) ? trim((string)$post['Property']['type']) : '-');
$qrPaymentLabel = !empty($post['PlansPayment']['name']) ? trim((string)$post['PlansPayment']['name']) : __('Installment Payment');
$qrTotalPaid = !empty($receiptSummary['deal_paid_amount']) ? (float)$receiptSummary['deal_paid_amount'] : (float)$post['DealsPayment']['amount'];
$receiptQrText = implode("\n", array(
    'Project: '.$agencyName,
    'Client: '.(!empty($post['Client']['name']) ? (string)$post['Client']['name'] : '-'),
    'Deal: '.(!empty($post['Deal']['invoice_no']) ? (string)$post['Deal']['invoice_no'] : '-'),
    'Payment: '.$qrPaymentLabel.' - '.$currency.$this->Number->format($post['DealsPayment']['amount']),
    'Total Paid: '.$currency.$this->Number->format($qrTotalPaid)
));
$receiptQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=420x420&ecc=M&qzone=2&margin=1&format=png&charset-source=UTF-8&charset-target=UTF-8&data=' . rawurlencode($receiptQrText) . '&v=' . rawurlencode((string)$post['DealsPayment']['id']) . '&cb=' . rawurlencode(date('YmdHis'));
$resolveHeaderImage = function($candidates)
{
    foreach((array)$candidates as $candidate)
    {
        $candidate = trim((string)$candidate);
        if($candidate === '')
        {
            continue;
        }
        if(preg_match('/^https?:\/\//i', $candidate))
        {
            return $candidate;
        }
        $normalized = trim(str_replace('\\', '/', $candidate), '/');
        if($normalized === '')
        {
            continue;
        }
        $filePath = WWW_ROOT.'img'.DS.str_replace('/', DS, $normalized);
        if(file_exists($filePath))
        {
            return $normalized;
        }
    }
    return '';
};
$headerLogo = $resolveHeaderImage(array(
    ($agencyLogoFile !== '' ? 'user_thumb/'.$agencyLogoFile : ''),
    $agencyLogoFile,
    (!empty($post['Project']['logo']) ? 'agency_thumb/'.trim((string)$post['Project']['logo']) : ''),
    (!empty($post['Project']['logo']) ? trim((string)$post['Project']['logo']) : ''),
    (!empty($post['Project']['image']) ? 'agency_thumb/'.trim((string)$post['Project']['image']) : ''),
    (!empty($post['Project']['image']) ? trim((string)$post['Project']['image']) : ''),
    (!empty($post['Project']['photo']) ? 'agency_thumb/'.trim((string)$post['Project']['photo']) : ''),
    (!empty($post['Project']['photo']) ? trim((string)$post['Project']['photo']) : ''),
    (isset($frontLogo) ? $frontLogo : '')
));
$showPrintDateOnReceipt = !isset($showReceiptPrintDate) ? 1 : (int)$showReceiptPrintDate;
$receiptAmountWordsEnglish = $this->NumToWord->NumberToWord($post['DealsPayment']['amount']);
$receiptAmountWordsUrdu = $this->NumToWord->NumberToUrduWord($post['DealsPayment']['amount']);
$receiptTextLength = function($text)
{
    $matches = array();
    return preg_match_all('/./u', (string)$text, $matches);
};
$receiptAmountWordsLength = strlen((string)$receiptAmountWordsEnglish) + (int)$receiptTextLength($receiptAmountWordsUrdu);
$receiptAmountWordsClass = 'receipt-amount-words-normal';
if($receiptAmountWordsLength > 105)
{
    $receiptAmountWordsClass = 'receipt-amount-words-tiny';
}
elseif($receiptAmountWordsLength > 82)
{
    $receiptAmountWordsClass = 'receipt-amount-words-compact';
}
?>
<html>
<head>
<meta charset="utf-8">
<title>Payment Receipt</title>
<?php echo $this->Html->css('print.css'); ?>
<style type="text/css">
.table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td {
border-top: 0px solid #ddd;}
html,body{margin:0;padding:0;background:#e9edf3;}
body,.table,p,td,strong{color:#000;font-family:Arial,Helvetica,sans-serif;}
body{font-size:13px;line-height:1.35;}
.receipt-page table{border-collapse:collapse;}
.receipt-page td{font-size:13px;line-height:1.35;padding:3px 5px;}
.receipt-page p{font-size:13px;line-height:1.35;margin:2px 0;}
.receipt-page strong{font-weight:900;}
.recipt_footer{position:relative;top: 180px;text-align: right;}
.recipt{text-align: right;}
.print-toolbar{box-sizing:border-box;margin:10px auto 8px auto;max-width:210mm;text-align:right;width:210mm;}
.print-toolbar button{background:#fff;border:1px solid #111;border-radius:4px;color:#000;cursor:pointer;font-size:13px;font-weight:800;padding:7px 14px;}
.wv-duplicate-watermark {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 42px;
    font-weight: 900;
    color: rgba(15, 23, 42, 0.10);
    border: 3px dashed rgba(15, 23, 42, 0.14);
    padding: 12px 26px;
    border-radius: 12px;
    letter-spacing: 4px;
    text-transform: uppercase;
    white-space: nowrap;
    pointer-events: none;
    z-index: 99999;
    font-family: Arial, Helvetica, sans-serif;
    text-align: center;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
@media print {
    .wv-duplicate-watermark {
        color: rgba(0, 0, 0, 0.13) !important;
        border-color: rgba(0, 0, 0, 0.15) !important;
        display: block !important;
        position: fixed !important;
    }
}
.receipt-page{background:#fff;box-shadow:0 12px 35px rgba(15,23,42,.18);box-sizing:border-box;margin:0 auto 22px auto;max-width:calc(100vw - 24px);min-height:auto;overflow:hidden;padding:6mm 8mm 6mm 8mm;width:210mm;}
.receipt-header-table-main{width:100%;table-layout:fixed;margin:0 0 8px 0;border-bottom:2px solid #111;border-top:0 !important;}
.receipt-header-logo-cell{width:145px;vertical-align:middle;text-align:left;}
.receipt-header-content-cell{vertical-align:middle;text-align:center;}
.receipt-header-qr-cell{width:145px;vertical-align:middle;text-align:right;}
.receipt-logo-image{display:block;height:112px;max-height:112px;max-width:112px;object-fit:contain;width:112px;}
.receipt-qr-box{border:1px solid #1f2f40;display:inline-block;padding:3px;background:#fff;line-height:1;}
.receipt-qr-box img{display:block;width:112px;height:112px;}
.receipt-header-meta{display:inline-table;margin:0 auto;text-align:center;}
.receipt-header-meta td{text-align:center!important;}
.receipt-title{border-bottom:1px solid #111;border-top:1px solid #111;font-size:14px;letter-spacing:.2px;padding:4px 0!important;text-align:center;}
.receipt-meta-table{font-family:"Times New Roman",Times,serif;margin:0 0 5px 0;}
.receipt-meta-table td{border:0!important;font-family:"Times New Roman",Times,serif;font-size:18px!important;line-height:1.3!important;padding:3px 3px!important;}
.receipt-meta-table p{font-family:"Times New Roman",Times,serif;font-size:18px!important;line-height:1.3!important;margin:0!important;}
.receipt-meta-table strong{font-family:"Times New Roman",Times,serif;font-size:18px!important;font-weight:900;}
.receipt-date-row td{padding-top:8px!important;}
.receipt-details-table{border:1px solid #111;margin-top:5px;table-layout:fixed;}
.receipt-details-table td{border:1px solid #777!important;font-family:"Times New Roman",Times,serif;font-size:18px!important;line-height:1.28!important;padding:4px 6px!important;vertical-align:top;}
.receipt-details-table p{font-family:"Times New Roman",Times,serif;font-size:18px!important;line-height:1.28!important;margin:0!important;}
.receipt-details-table strong{font-family:"Times New Roman",Times,serif;font-size:18px!important;font-weight:900;}
.receipt-detail-card{word-break:normal;}
.receipt-detail-property{width:45%;}
.receipt-detail-area{width:18%;white-space:nowrap;}
.receipt-detail-type{width:37%;}
.receipt-detail-card .detail-label{display:inline;font-weight:900;margin:0 5px 0 0;}
.receipt-detail-card .detail-value{display:inline;font-weight:400;}
.receipt-details-table .receipt-detail-card p,.receipt-details-table .receipt-detail-card .detail-label,.receipt-details-table .receipt-detail-card .detail-value{font-size:16px!important;line-height:1.2!important;}
.receipt-label{font-weight:900;}
.receipt-notes-table{background:#fff;border:1px solid #111;margin-top:8px;padding:3px 0;}
.receipt-notes-table td{border:0!important;padding:2px 9px!important;}
.receipt-original-note p{font-size:14px!important;line-height:1.3!important;margin:1px 0 2px 0!important;}
.receipt-signature-table td{border:0!important;}
.receipt-summary-wrap{margin:9px auto 7px auto;width:80%;max-width:760px;border:1px solid #7d8a99;border-radius:5px;background:#fff;}
.receipt-summary-title{text-align:center;font-weight:800;font-size:13px;color:#000;padding:7px 8px 2px 8px;}
.receipt-summary-grid{display:table;width:100%;table-layout:fixed;padding:0 8px 10px 8px;box-sizing:border-box;}
.receipt-summary-item{display:table-cell;text-align:center;border-right:1px solid #d8e4ef;}
.receipt-summary-item:last-child{border-right:none;}
.receipt-summary-label{display:block;font-size:12px;color:#000;font-weight:800;text-transform:uppercase;line-height:1.3;}
.receipt-summary-value{display:block;font-size:15px;color:#000;font-weight:800;line-height:1.3;margin-top:2px;}
.receipt-amount-strong{font-size:16px;font-weight:900;color:#000;}
.receipt-amount-highlight{background:#f2f5f8;border:2px solid #111!important;}
.receipt-amount-highlight p{font-size:18px!important;font-weight:400;line-height:1.28;margin:0;text-align:left;}
.receipt-amount-highlight .receipt-amount-strong{border-bottom:2px solid #111;display:inline-block;font-size:22px!important;font-weight:900;line-height:1.08;padding:0 7px;}
.receipt-amount-highlight strong{font-size:18px!important;}
.receipt-amount-label{display:inline-block;font-weight:900;min-width:138px;}
.receipt-amount-purpose{display:inline-block;margin-left:16px;}
.receipt-amount-late{display:inline-block;margin-left:16px;}
.receipt-amount-late .late-label{font-weight:900;}
.receipt-amount-late .late-value{font-weight:400;}
.receipt-details-table p.receipt-amount-words-line{display:table;font-size:15px!important;line-height:1.25!important;table-layout:fixed;white-space:nowrap;width:100%;}
.receipt-amount-words-english{display:table-cell;font-size:15px!important;font-weight:400;text-align:left;vertical-align:middle;white-space:nowrap;width:55%;}
.receipt-amount-words-urdu{display:table-cell;direction:rtl;font-family:'Jameel Noori Nastaleeq','Noto Nastaliq Urdu','Noto Naskh Arabic','Arial',sans-serif!important;font-size:18px!important;font-weight:400;line-height:1.2!important;text-align:right;unicode-bidi:embed;vertical-align:middle;white-space:nowrap;width:45%;}
.receipt-amount-words-compact .receipt-amount-words-english{font-size:13px!important;}
.receipt-amount-words-compact .receipt-amount-words-urdu{font-size:16px!important;}
.receipt-amount-words-tiny .receipt-amount-words-english{font-size:12px!important;}
.receipt-amount-words-tiny .receipt-amount-words-urdu{font-size:15px!important;}
.receipt-amount-words-urdu .urdu-label{font-weight:900;margin-left:6px;}
.receipt-footer-contact{border-top:1px solid #b6c5d6;color:#111;font-size:11px;font-weight:700;line-height:1.4;margin-top:8px;padding-top:5px;text-align:center;}
@font-face{
font-family:'Jameel Noori Nastaleeq';
src:local('Jameel Noori Nastaleeq'),local('Jameel Noori Nastaleeq Regular'),local('Jameel Noori Nastaleeq Kasheeda');
font-style:normal;
font-weight:700;
}
.receipt-urdu-note{
direction:rtl;
unicode-bidi:embed;
text-align:right !important;
font-family:'Jameel Noori Nastaleeq','Noto Nastaliq Urdu','Noto Naskh Arabic','Arial',sans-serif !important;
font-size:25px;
font-weight:800;
line-height:1.38;
margin:0 0 3px 0;
color:#000;
}
.receipt-urdu-note strong{
font-family:'Jameel Noori Nastaleeq','Noto Nastaliq Urdu','Noto Naskh Arabic','Arial',sans-serif !important;
font-weight:800;
color:#000;
}
.receipt-header-meta h2{
margin:0 0 4px 0;
color:#000;
font-size:23px;
font-weight:900;
letter-spacing:.1px;
line-height:1.15;
text-align:center;
white-space:nowrap;
}
.receipt-header-meta p{
margin:0 0 2px 0;
font-size:11px;
line-height:1.3;
color:#000;
font-weight:700;
text-align:center;
}
.receipt-signature-cell{padding-top:55px !important;}
@media print {
@page { size:A4 portrait; margin: 4mm 6mm 4mm 6mm; }
html,body{background:#fff !important; margin:0 !important; padding:0 !important;}
.navbar, .sidebar, .panel-heading, #sidebar-wrapper, .admin-rights, .breadcrumb, [class*="debug"], [id*="debug"], .print-toolbar{display:none !important;}
.receipt-page{box-shadow:none !important; margin:0 auto !important; max-width:100% !important; min-height:0 !important; height:auto !important; overflow:visible !important; padding:1mm 4mm 2mm 4mm !important; width:100% !important; page-break-after:avoid !important; page-break-inside:avoid !important; break-inside:avoid !important;}
.receipt-header-table-main{margin:0 0 5px 0 !important;}
.receipt-logo-image{height:100px;max-height:100px;max-width:100px;width:100px;}
.receipt-qr-box img{width:100px;height:100px;}
.receipt-meta-table{margin:0 0 4px 0 !important;}
.receipt-meta-table td{font-size:16.5px !important; line-height:1.24 !important; padding:2px 3px !important;}
.receipt-meta-table p{font-size:16.5px !important; line-height:1.24 !important;}
.receipt-meta-table strong{font-size:16.5px !important;}
.receipt-details-table{margin-top:4px !important;}
.receipt-details-table td{font-size:16.5px !important; line-height:1.22 !important; padding:3px 5px !important;}
.receipt-details-table p{font-size:16.5px !important; line-height:1.22 !important;}
.receipt-details-table strong{font-size:16.5px !important;}
.receipt-amount-highlight p{font-size:16.5px !important;}
.receipt-amount-highlight .receipt-amount-strong{font-size:20px !important;}
.receipt-notes-table{margin-top:5px !important; padding:2px 0 !important;}
.receipt-original-note p{font-size:13px !important; margin:1px 0 1px 0 !important;}
.receipt-urdu-note{font-size:19px !important; line-height:1.26 !important; margin:0 0 2px 0 !important;}
.receipt-signature-cell{padding-top:45px !important;}
.receipt-footer-contact{margin-top:5px !important; padding-top:3px !important; font-size:10.5px !important;}
table, tr, td { page-break-inside: avoid !important; break-inside: avoid !important; }
}
@media screen and (max-width:900px){
.print-toolbar{max-width:calc(100vw - 24px);width:calc(100vw - 24px);}
}
</style>
</head>
<body>
<div class="print-toolbar">
    <button type="button" onclick="window.print();"><?php echo __('Print Receipt');?></button>
</div>
<div class="wv-duplicate-watermark">
    DUPLICATE BY CLIENT PORTAL
</div>
<div class="receipt-page">
<table width="100%" class="table receipt-header-table-main">
    <tr>
        <td class="receipt-header-logo-cell" width="145">
            <?php if(strlen(trim((string)$headerLogo))>0){?>
                <?php echo $this->Html->image($headerLogo,array('alt'=>$agencyName,'class'=>'receipt-logo-image'));?>
            <?php }?>
        </td>
        <td class="receipt-header-content-cell">
            <table class="receipt-header-meta">
                <tr><td><h2><?php echo h($agencyName);?></h2></td></tr>
                <?php if(strlen(trim((string)$agencyAddress))>0){?><tr><td><p><?php echo h($agencyAddress);?></p></td></tr><?php }?>
                <?php if(!empty($agencyContactParts)){?><tr><td><p><?php echo h(implode(' | ', $agencyContactParts));?></p></td></tr><?php }?>
            </table>
        </td>
        <td class="receipt-header-qr-cell" width="145">
            <div class="receipt-qr-box">
                <img src="<?php echo h($receiptQrUrl);?>" alt="<?php echo h(__('Receipt QR Code'));?>">
            </div>
        </td>
    </tr>
</table>
<table width="100%" class="table receipt-meta-table" style="max-height:960px;border-top: 0px">
    <tr><td colspan="2" class="receipt-title"><strong><?php echo __('PAYMENT RECEIPT');?></strong></td></tr>
    <tr class="receipt-date-row">
        <td align="left" valign="top"><p><span class="receipt-label"><?php echo __('Payment Date');?>:</span> <?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,h($post['DealsPayment']['payment_date']));?></p></td>
        <td align="right" valign="top"><p><span class="receipt-label"><?php echo __('Next Due Date');?>:</span> <?php if(!empty($isNextDueOverdue) && $dueDate){echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,h($dueDate));?> <strong style="color:#b30000;font-weight:900;">(<?php echo __('OVERDUE');?>)</strong><?php }elseif($dueDate){echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,h($dueDate));}else{echo __('PAID');}?></p></td>
    </tr>
    <tr><td colspan="2" align="left" valign="top"><p><span class="receipt-label"><?php echo __('Receipt Number');?>:</span> <?php echo h($receiptNo);?></p></td></tr>
    <tr><td colspan="2" valign="top" align="left"><p><?php echo __('Dear Mr./Mrs./Ms ');?><strong><?php echo h($post['Client']['name']);?></strong><?php if(!empty($post['Client']['fathername'])){ ?> <span style="font-weight:normal;"><?php echo !empty($post['Client']['relation_type']) && $post['Client']['relation_type']==='Husband' ? 'W/O' : 'S/O'; ?></span> <strong><?php echo h($post['Client']['fathername']);?></strong><?php } ?></p></td></tr>
    <tr><td colspan="2" align="left" valign="top"><p><span class="receipt-label"><?php echo __('Membership NO');?>:</span> <?php echo h($post['Deal']['invoice_no']);?></p></td></tr>
    <tr><td colspan="2" valign="top" align="left"><p><?php echo __('Thank you for your payment against the following details.');?></p></td></tr>
</table>
<table border="1" width="100%" class="table receipt-details-table">
    <tr>
        <td align="left" valign="top" class="receipt-detail-card receipt-detail-property"><p><span class="detail-label"><?php echo __('Property Name');?>:</span><span class="detail-value"><?php echo h($receiptPropertyText);?></span></p></td>
        <td align="left" valign="top" class="receipt-detail-card receipt-detail-area"><p><span class="detail-label"><?php echo __('Area');?>:</span><span class="detail-value"><?php echo h($receiptAreaText);?></span></p></td>
        <td align="left" valign="top" class="receipt-detail-card receipt-detail-type"><p><span class="detail-label"><?php echo __('Property Type');?>:</span><span class="detail-value"><?php echo h($receiptPropertyTypeText);?></span></p></td>
    </tr>
    <?php if($post['DealsPayment']['extra_payment']>0){?>
    <tr>
        <td align="left" valign="top" class="receipt-detail-card"><p><span class="detail-label"><?php echo __('Extra/Late Amount');?>:</span><span class="detail-value"><?php echo $currency.$this->Number->format($post['DealsPayment']['extra_payment']);?></span></p></td>
        <td align="left" valign="top" class="receipt-detail-card">&nbsp;</td>
        <td align="left" valign="top" class="receipt-detail-card">&nbsp;</td>
    </tr>
    <?php } ?>
    <tr>
        <td colspan="3" align="left" valign="top" class="receipt-amount-highlight"><p><span class="receipt-amount-label"><?php echo __('Amount in Figures').': ';?></span><span class="receipt-amount-strong"><?php echo $currency.$this->Number->format($post['DealsPayment']['amount']);?></span><span class="receipt-amount-purpose"><strong><?php echo __('For');?></strong>&nbsp;&nbsp;<?php echo h($post['PlansPayment']['name']);?></span><span class="receipt-amount-late"><span class="late-label"><?php echo __('Late Fee/Tax');?>:</span> <span class="late-value"><?php echo $currency.$this->Number->format($post['DealsPayment']['tax_amount']);?></span></span></p></td>
    </tr>
    <tr>
        <td colspan="3" align="left" valign="top">
            <p class="receipt-amount-words-line <?php echo h($receiptAmountWordsClass);?>">
                <span class="receipt-amount-words-english"><span class="receipt-label"><?php echo __('Amount (word)');?>:</span> <?php echo h($receiptAmountWordsEnglish);?></span>
                <span class="receipt-amount-words-urdu" dir="rtl"><?php echo h($receiptAmountWordsUrdu);?></span>
            </p>
        </td>
    </tr>
    
    <tr>
        <td colspan="3" align="left" valign="top"><p><span class="receipt-label"><?php echo __('Payment Through');?>:</span> <?php echo $post['Paymenttype']['name'];?>&nbsp;&nbsp;<span class="receipt-label"><?php echo __('Reference#');?></span>&nbsp;&nbsp;<?php echo$post['DealsPayment']['remarks'];?></p></td>
    </tr>
</table>
<?php if(!empty($showReceiptSummaryCard) && !empty($receiptSummary)){ ?>
<div class="receipt-summary-wrap">
    <div class="receipt-summary-grid">
        <div class="receipt-summary-item">
            <span class="receipt-summary-label"><?php echo __('Deal Due Amt'); ?></span>
            <span class="receipt-summary-value"><?php echo $currency.$this->Number->format($receiptSummary['deal_due_amount']); ?></span>
        </div>
        <div class="receipt-summary-item">
            <span class="receipt-summary-label"><?php echo __('Deal Paid Amount'); ?></span>
            <span class="receipt-summary-value"><?php echo $currency.$this->Number->format($receiptSummary['deal_paid_amount']); ?></span>
        </div>
        <div class="receipt-summary-item">
            <span class="receipt-summary-label"><?php echo __('Overdue'); ?></span>
            <span class="receipt-summary-value"><?php echo $currency.$this->Number->format($receiptSummary['deal_overdue_amount']); ?></span>
        </div>
    </div>
</div>
<?php } ?>
<table width="100%" class="table receipt-notes-table">
    <tr class="receipt-original-note">
        <td align="right" valign="top"><p><strong><?php echo __('No payment will be accepted without the original receipt');?></strong></p></td>
    </tr>
    <tr>
        <td align="right" valign="top"><p class="receipt-urdu-note"><strong>نوٹ: اصل رسید کے بغیر کسی بھی قسم کی ادائیگی قابل قبول نہیں ہو گی۔</strong></p></td>
    </tr>
    <tr>
        <td align="right" valign="top"><p class="receipt-urdu-note"><strong>اپنی قسط ہر ماہ کی مقررہ تاریخ تک جمع کروائیں۔ مقررہ تاریخ کے بعد لیٹ فیس/جرمانے کے ساتھ قسط جمع ہو گی۔</strong></p></td>
    </tr>
    <tr>
        <td align="right" valign="top"><p class="receipt-urdu-note"><strong>قسط جمع کروانے کے بعد اپنا پورٹل لاگ اِن کر کے اپنی رقم جمع ہونے کی تصدیق کریں۔</strong></p></td>
    </tr>
    <tr>
        <td align="right" valign="top"><p class="receipt-urdu-note"><strong>قسط جمع ادائیگی اور رہنمائی کے لیے رابطہ نمبر 03391101156</strong></p></td>
    </tr>
</table>
<table width="100%" class="table receipt-signature-table">
    <tr>
        <td valign="top" class="receipt-signature-cell">
            <table width="100%" style="border-collapse:collapse;border:0;">
                <tr>
                    <td align="left" valign="top"><strong style="text-decoration:underline;"><?php echo __('Authorised Signature');?></strong></td>
                    <td valign="top" style="width:300px;">&nbsp;</td>
                    <?php if($showPrintDateOnReceipt === 1){ ?>
                    <td align="right" valign="top"><strong><?php echo __('Print Date');?>: <?php echo date('F j, Y'); ?></strong></td>
                    <?php }else{ ?>
                    <td align="right" valign="top">&nbsp;</td>
                    <?php } ?>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div class="receipt-footer-contact">
    <?php echo h($agencyName);?><?php if($agencyContactLine !== ''){ ?> &nbsp; | &nbsp; <?php echo h($agencyContactLine);?><?php } ?>
</div>
</div>


<script type="text/javascript">
// Keep receipt visible. Print manually using the button above.
</script>
</body>
</html>

