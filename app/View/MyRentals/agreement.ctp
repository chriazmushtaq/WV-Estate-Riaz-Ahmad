<?php
$agreementHtml = (string)$html;
$trimmedHtml = ltrim($agreementHtml);
$isFullDocument = stripos(substr($trimmedHtml, 0, 700), '<!doctype html') !== false || stripos(substr($trimmedHtml, 0, 700), '<html') !== false;
$printButton = '<div class="wv-agreement-print-actions"><button type="button" onclick="window.print();">'.__('Print Agreement').'</button></div>';
$watermark = '<div class="wv-duplicate-watermark">DUPLICATE</div>';
$printCss = '<style id="wv-client-agreement-print">@page{size:A4;margin:12mm}html,body{margin:0;padding:0;background:#edf2f7;color:#111}.wv-agreement-print-actions{position:fixed;right:14px;top:14px;z-index:99999}.wv-agreement-print-actions button{background:#087f5b;border:1px solid #066a4c;border-radius:5px;color:#fff;cursor:pointer;font:700 13px Arial;padding:9px 14px}.wv-duplicate-watermark{color:#b42318;font:900 72px Arial;left:50%;letter-spacing:8px;opacity:.13;pointer-events:none;position:fixed;top:48%;transform:translate(-50%,-50%) rotate(-35deg);white-space:nowrap;z-index:9998}.wv-agreement-print-page{background:#fff;box-shadow:0 12px 35px rgba(15,23,42,.18);box-sizing:border-box;margin:20px auto 24px;max-width:210mm;min-height:273mm;padding:12mm;overflow:hidden}@media print{html,body{background:#fff}.wv-agreement-print-actions{display:none!important}.wv-agreement-print-page{box-shadow:none;margin:0;max-width:none;min-height:0;overflow:visible;padding:0}.wv-duplicate-watermark{position:fixed}}</style>';
if($isFullDocument) {
    $agreementHtml = stripos($agreementHtml, '</head>') !== false ? str_ireplace('</head>', $printCss.'</head>', $agreementHtml) : $printCss.$agreementHtml;
    $agreementHtml = preg_match('/<body[^>]*>/i', $agreementHtml) ? preg_replace('/<body([^>]*)>/i', '<body$1>'.$printButton.$watermark, $agreementHtml, 1) : $printButton.$watermark.$agreementHtml;
    echo $agreementHtml;
} else {
?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?php echo h(__('Rental Agreement').' - '.$rental['DealRental']['agreement_no']);?></title><?php echo $printCss;?></head><body><?php echo $printButton.$watermark;?><div class="wv-agreement-print-page"><?php echo $agreementHtml;?></div></body></html>
<?php } ?>
