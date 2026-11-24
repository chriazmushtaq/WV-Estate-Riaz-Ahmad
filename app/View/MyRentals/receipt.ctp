<?php
$receiptAmount = !empty($receipt['DealRentalReceipt']['rent_amount']) ? (float)$receipt['DealRentalReceipt']['rent_amount'] : 0;
$receiptReplacements['{{rent_amount_words}}'] = h($this->NumToWord->NumberToWord($receiptAmount));
$html = strtr((string)$receiptTemplateBody, (array)$receiptReplacements);
$trimmedHtml = ltrim($html);
$isFullDocument = stripos(substr($trimmedHtml, 0, 700), '<!doctype html') !== false || stripos(substr($trimmedHtml, 0, 700), '<html') !== false;
$printButton = '<div class="wv-rental-print-actions"><button type="button" onclick="window.print();">'.__('Print Receipt').'</button></div>';
$watermark = '<div class="wv-duplicate-watermark" aria-hidden="true">DUPLICATE</div>';
$printCss = '<style id="wv-rental-print-shell">@page{size:A4;margin:10mm}html,body{margin:0;padding:0;background:#e9edf3}.wv-rental-print-actions{margin:14px auto;max-width:190mm;text-align:right}.wv-rental-print-actions button{background:#087f5b;border:1px solid #066a4c;border-radius:5px;color:#fff;cursor:pointer;font:700 13px Arial;padding:9px 14px}.wv-duplicate-watermark{color:#b91c1c;font:900 76px/1 Arial,sans-serif;left:50%;opacity:.14;pointer-events:none;position:fixed;top:50%;transform:translate(-50%,-50%) rotate(-35deg);white-space:nowrap;z-index:9999}@media print{html,body{background:#fff}.wv-rental-print-actions{display:none!important}.wv-duplicate-watermark{display:block!important;position:fixed}}</style>';
if ($isFullDocument) {
    if (stripos($html, '</head>') !== false) {
        $html = str_ireplace('</head>', $printCss.'</head>', $html);
    } else {
        $html = $printCss.$html;
    }
    $html = preg_replace('/<body([^>]*)>/i', '<body$1>'.$printButton.$watermark, $html, 1);
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
    <style>.wv-rental-print-page{background:#fff;box-shadow:0 12px 35px rgba(15,23,42,.18);margin:0 auto 24px;max-width:210mm;overflow:hidden;position:relative}@media print{.wv-rental-print-page{box-shadow:none;margin:0;max-width:none;overflow:visible}}</style>
</head>
<body>
    <?php echo $printButton.$watermark;?>
    <div class="wv-rental-print-page"><?php echo $html;?></div>
</body>
</html>
<?php } ?>
