<?php
$receiptAmount = !empty($receipt['DealRentalReceipt']['rent_amount']) ? (float)$receipt['DealRentalReceipt']['rent_amount'] : 0;
$receiptReplacements['{{rent_amount_words}}'] = h($this->NumToWord->NumberToWord($receiptAmount));
$html = strtr((string)$receiptTemplateBody, (array)$receiptReplacements);
$trimmedHtml = ltrim($html);
$isFullDocument = (stripos(substr($trimmedHtml, 0, 700), '<!doctype html') !== false || stripos(substr($trimmedHtml, 0, 700), '<html') !== false);
$printButton = '<div class="wv-rental-print-actions"><button type="button" onclick="window.print();">'.__('Print Receipt').'</button></div>';
$printCss = '<style id="wv-rental-print-shell">@page{size:A4;margin:10mm}html,body{margin:0;padding:0;background:#e9edf3}.wv-rental-print-actions{margin:14px auto;max-width:190mm;text-align:right}.wv-rental-print-actions button{background:#087f5b;border:1px solid #066a4c;border-radius:5px;color:#fff;cursor:pointer;font:700 13px Arial;padding:9px 14px}@media print{html,body{background:#fff}.wv-rental-print-actions{display:none!important}}</style>';
if ($isFullDocument) {
    if (stripos($html, '</head>') !== false) {
        $html = str_ireplace('</head>', $printCss.'</head>', $html);
    } else {
        $html = $printCss.$html;
    }
    $html = preg_replace('/<body([^>]*)>/i', '<body$1>'.$printButton, $html, 1);
    echo $html;
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h(__('Rental Receipt').' - '.$receipt['DealRentalReceipt']['receipt_no']);?></title>
    <?php echo $printCss;?>
    <style>.wv-rental-print-page{background:#fff;box-shadow:0 12px 35px rgba(15,23,42,.18);margin:0 auto 24px;max-width:210mm;overflow:hidden}@media print{.wv-rental-print-page{box-shadow:none;margin:0;max-width:none;overflow:visible}}</style>
</head>
<body>
    <?php echo $printButton;?>
    <div class="wv-rental-print-page"><?php echo $html;?></div>
</body>
</html>
<?php } ?>
