<?php
$t = isset($transfer['Dealtransfer']) ? $transfer['Dealtransfer'] : array();
$sourceDealNo = !empty($sourceDeal['Deal']['invoice_no']) ? $sourceDeal['Deal']['invoice_no'] : '#'.(int)$t['source_deal_id'];
$targetDealNo = !empty($targetDeal['Deal']['invoice_no']) ? $targetDeal['Deal']['invoice_no'] : ((int)$t['target_deal_id'] > 0 ? '#'.(int)$t['target_deal_id'] : '-');
$payer = isset($payerClient['Client']) ? $payerClient['Client'] : array();
$seller = isset($sellerClient['Client']) ? $sellerClient['Client'] : array();
$sourceClientName = !empty($seller['name']) ? $seller['name'] : (!empty($sourceDeal['Client']['name']) ? $sourceDeal['Client']['name'] : '-');
$sellerPhone = !empty($seller['mobile']) ? $seller['mobile'] : (!empty($seller['phone']) ? $seller['phone'] : (!empty($sourceDeal['Client']['mobile']) ? $sourceDeal['Client']['mobile'] : (!empty($sourceDeal['Client']['phone']) ? $sourceDeal['Client']['phone'] : '-')));
$sellerCnic = !empty($seller['pincode']) ? $seller['pincode'] : (!empty($sourceDeal['Client']['pincode']) ? $sourceDeal['Client']['pincode'] : '-');
$sellerAddress = !empty($seller['address']) ? $seller['address'] : '-';
$targetClientName = !empty($targetDeal['Client']['name']) ? $targetDeal['Client']['name'] : $sourceClientName;
$payerName = !empty($payer['name']) ? $payer['name'] : (!empty($targetDeal['Client']['name']) ? $targetDeal['Client']['name'] : (!empty($sourceDeal['Client']['name']) ? $sourceDeal['Client']['name'] : '-'));
$payerPhone = !empty($payer['mobile']) ? $payer['mobile'] : (!empty($payer['phone']) ? $payer['phone'] : '-');
$payerCnic = !empty($payer['pincode']) ? $payer['pincode'] : '-';
$payerAddress = !empty($payer['address']) ? $payer['address'] : '-';
$buyerLabel = (!empty($t['transfer_type']) && $t['transfer_type'] === 'Merge') ? __('Target Client / Buyer') : __('Buyer / Transferee');
$amount = isset($t['processing_fee']) ? (float)$t['processing_fee'] : 0;
$currencyLabel = isset($currency) ? trim(strip_tags((string)$currency)) : '';
if($currencyLabel === '') { $currencyLabel = 'Rs'; }
$numberToWords = function($number) use (&$numberToWords) {
    $number = (int)$number;
    $ones = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
    $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');
    if($number < 20) { return $ones[$number]; }
    if($number < 100) { return trim($tens[(int)floor($number / 10)].' '.$ones[$number % 10]); }
    if($number < 1000) { return trim($ones[(int)floor($number / 100)].' Hundred '.$numberToWords($number % 100)); }
    if($number < 100000) { return trim($numberToWords((int)floor($number / 1000)).' Thousand '.$numberToWords($number % 1000)); }
    if($number < 10000000) { return trim($numberToWords((int)floor($number / 100000)).' Lac '.$numberToWords($number % 100000)); }
    return trim($numberToWords((int)floor($number / 10000000)).' Crore '.$numberToWords($number % 10000000));
};
$wholeAmount = (int)floor($amount);
$paisaAmount = (int)round(($amount - $wholeAmount) * 100);
$amountWords = trim($numberToWords($wholeAmount));
if($amountWords === '') { $amountWords = 'Zero'; }
$amountWords = $amountWords.' Rupees';
if($paisaAmount > 0)
{
    $amountWords .= ' and Paisa '.$numberToWords($paisaAmount);
}
$amountWords .= ' Only';
$statusText = !empty($feePaid) ? __('PAID') : __('UNPAID');
$statusClass = !empty($feePaid) ? 'paid' : 'unpaid';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo h($feeVoucherNo);?></title>
    <style type="text/css">
        @page{size:A4;margin:10mm;}
        body{font-family:Arial,Helvetica,sans-serif;background:#eef3f8;margin:0;padding:12px;color:#15233b;}
        .voucher{max-width:190mm;margin:0 auto;background:#fff;border:1px solid #c9d6e6;box-shadow:0 8px 20px rgba(15,35,58,.12);}
        .top{display:table;width:100%;border-bottom:2px solid #0b3a75;padding:12px 16px;box-sizing:border-box;background:#fbfdff;}
        .brand,.meta{display:table-cell;vertical-align:middle;}
        .brand{width:60%;}
        .meta{text-align:right;font-size:11px;color:#54657d;line-height:1.45;}
        .logo{width:56px;height:56px;object-fit:contain;vertical-align:middle;margin-right:10px;border:1px solid #dbe6f3;}
        .name{display:inline-block;vertical-align:middle;font-size:22px;font-weight:900;color:#08213f;}
        .title{text-align:center;font-size:21px;font-weight:900;letter-spacing:0;margin:12px 0 5px;text-transform:uppercase;color:#08213f;}
        .status{display:inline-block;padding:5px 12px;border-radius:3px;color:#fff;font-size:12px;font-weight:900;letter-spacing:0;}
        .status.unpaid{background:#c0392b;}
        .status.paid{background:#168a53;}
        .section{padding:8px 16px;}
        table{width:100%;border-collapse:collapse;}
        td,th{border:1px solid #d7e2ef;padding:7px 9px;font-size:12px;text-align:left;}
        th{background:#f4f8fc;color:#43546b;text-transform:uppercase;font-size:10px;}
        .amount{font-size:22px;font-weight:900;color:#08213f;}
        .amount-words{font-size:11px;font-weight:800;color:#52657d;margin-top:4px;line-height:1.35;text-transform:capitalize;}
        .party-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
        .party-box{border:1px solid #d7e2ef;background:#fbfdff;}
        .party-title{background:#f4f8fc;border-bottom:1px solid #d7e2ef;padding:7px 9px;font-size:11px;font-weight:900;text-transform:uppercase;color:#43546b;}
        .party-body{padding:8px 9px;font-size:12px;line-height:1.55;}
        .party-body strong{color:#08213f;}
        .terms{border:1px solid #d7e2ef;background:#fbfdff;padding:8px 12px;}
        .terms-title{font-size:12px;font-weight:900;text-transform:uppercase;color:#08213f;margin-bottom:4px;}
        .terms ol{margin:0;padding-left:16px;font-size:11px;color:#465870;line-height:1.42;}
        .terms li{margin-bottom:2px;}
        .sign{display:table;width:100%;padding:24px 16px 12px;box-sizing:border-box;}
        .sign div{display:table-cell;width:33.33%;font-size:12px;color:#26384f;}
        .line{border-top:1px solid #26384f;padding-top:6px;width:82%;}
        .center{text-align:center;}
        .right{text-align:right;}
        .right .line{margin-left:auto;}
        .printbar{text-align:center;margin:0 0 10px;}
        .printbar button{border:0;background:#1f35f0;color:#fff;padding:9px 18px;border-radius:4px;font-weight:800;cursor:pointer;}
        @media(max-width:760px){.party-grid{grid-template-columns:1fr;}.brand,.meta{display:block;width:100%;text-align:left;}.meta{margin-top:10px;}}
        @media print{
            body{background:#fff;padding:0;}
            .voucher{box-shadow:none;border:0;margin:0;max-width:none;}
            .printbar{display:none;}
        }
    </style>
</head>
<body>
<div class="printbar"><button type="button" onclick="window.print()"><?php echo __('Print Voucher');?></button></div>
<div class="voucher">
    <div class="top">
        <div class="brand">
            <?php if(!empty($agencyInfo['logo_url'])){ ?><img class="logo" src="<?php echo h($agencyInfo['logo_url']);?>" alt="<?php echo h($agencyInfo['name']);?>"><?php } ?>
            <span class="name"><?php echo h($agencyInfo['name']);?></span>
        </div>
        <div class="meta">
            <div><?php echo h(!empty($agencyInfo['address']) ? $agencyInfo['address'] : '-');?></div>
            <div><?php echo h(!empty($agencyInfo['mobile']) ? $agencyInfo['mobile'] : '-');?></div>
            <div><?php echo h(!empty($agencyInfo['email']) ? $agencyInfo['email'] : '-');?></div>
        </div>
    </div>

    <div class="title"><?php echo __('Deal Transfer Fee Voucher');?></div>
    <div class="center"><span class="status <?php echo $statusClass;?>"><?php echo h($statusText);?></span></div>

    <div class="section">
        <table>
            <tr>
                <th><?php echo __('Voucher No');?></th>
                <td><?php echo h($feeVoucherNo);?></td>
                <th><?php echo __('Date');?></th>
                <td><?php echo h($voucherDate);?></td>
            </tr>
            <tr>
                <th><?php echo __('Transfer No');?></th>
                <td><?php echo h($t['transfer_no']);?></td>
                <th><?php echo __('Transfer Type');?></th>
                <td><?php echo h($t['transfer_type']);?></td>
            </tr>
            <tr>
                <th><?php echo __('Source Deal');?></th>
                <td><?php echo h($sourceDealNo);?></td>
                <th><?php echo __('Target Deal');?></th>
                <td><?php echo h($targetDealNo);?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="party-grid">
            <div class="party-box">
                <div class="party-title"><?php echo __('Seller');?></div>
                <div class="party-body">
                    <div><strong><?php echo __('Name');?>:</strong> <?php echo h($sourceClientName);?></div>
                    <div><strong><?php echo __('Membership');?>:</strong> <?php echo h($sourceDealNo);?></div>
                    <div><strong><?php echo __('Mobile');?>:</strong> <?php echo h($sellerPhone);?></div>
                    <div><strong><?php echo __('CNIC');?>:</strong> <?php echo h($sellerCnic);?></div>
                    <div><strong><?php echo __('Address');?>:</strong> <?php echo h($sellerAddress);?></div>
                    <div><strong><?php echo __('Deal');?>:</strong> <?php echo h($sourceDealNo.' | '.$t['transfer_type']);?></div>
                </div>
            </div>
            <div class="party-box">
                <div class="party-title"><?php echo h($buyerLabel);?></div>
                <div class="party-body">
                    <div><strong><?php echo __('Name');?>:</strong> <?php echo h($payerName);?></div>
                    <div><strong><?php echo __('Membership');?>:</strong> <?php echo h($targetDealNo);?></div>
                    <div><strong><?php echo __('Mobile');?>:</strong> <?php echo h($payerPhone);?></div>
                    <div><strong><?php echo __('CNIC');?>:</strong> <?php echo h($payerCnic);?></div>
                    <div><strong><?php echo __('Address');?>:</strong> <?php echo h($payerAddress);?></div>
                    <div><strong><?php echo __('Deal');?>:</strong> <?php echo h($targetDealNo.' | '.$t['transfer_type']);?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <table>
            <tr>
                <th><?php echo __('Description');?></th>
                <th style="width:230px;"><?php echo __('Amount');?></th>
            </tr>
            <tr>
                <td><?php echo __('Deal transfer / merge processing fee');?></td>
                <td>
                    <div class="amount"><?php echo h($currencyLabel.' '.number_format($amount,2));?></div>
                    <div class="amount-words"><?php echo h($amountWords);?></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="terms">
            <div class="terms-title"><?php echo __('Terms & Conditions');?></div>
            <ol>
                <li><?php echo __('Transfer / merge processing fee is non-refundable once the request is processed.');?></li>
                <li><?php echo __('Fee payment does not transfer ownership by itself; final approval and required documents remain mandatory.');?></li>
                <li><?php echo __('After the deal transfer / merge is finalized, it cannot be reversed through normal office process except by authorized admin approval according to company policy.');?></li>
                <li><?php echo __('Buyer / transferee accepts all future dues, installments, charges, and society obligations attached to the target deal.');?></li>
                <li><?php echo __('This voucher must be presented with valid client identification and transfer reference number.');?></li>
            </ol>
        </div>
    </div>

    <div class="sign">
        <div><div class="line"><?php echo __('Prepared By');?></div></div>
        <div class="center"><div class="line" style="margin:0 auto;"><?php echo __('Received By');?></div></div>
        <div class="right"><div class="line"><?php echo __('Authorized Signature');?></div></div>
    </div>
</div>
</body>
</html>
