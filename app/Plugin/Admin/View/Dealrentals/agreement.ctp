<?php
$agreementHtml = (string)$html;
$trimmedHtml = ltrim($agreementHtml);
$isFullDocument = stripos(substr($trimmedHtml, 0, 700), '<!doctype html') !== false || stripos(substr($trimmedHtml, 0, 700), '<html') !== false;
$printButton = '<div class="wv-agreement-print-actions"><button type="button" onclick="window.print();">'.__('Print Agreement').'</button></div>';
$actionCss = '<style id="wv-agreement-print-action">.wv-agreement-print-actions{position:fixed;right:14px;top:14px;z-index:99999}.wv-agreement-print-actions button{background:#087f5b;border:1px solid #066a4c;border-radius:5px;color:#fff;cursor:pointer;font:700 13px Arial;padding:9px 14px}@media print{.wv-agreement-print-actions{display:none!important}}</style>';
$printCss = $actionCss.'<style id="wv-agreement-print-shell">@page{size:A4;margin:12mm}html,body{margin:0;padding:0;background:#edf2f7;color:#111}.wv-agreement-print-page{background:#fff;box-shadow:0 12px 35px rgba(15,23,42,.18);box-sizing:border-box;margin:20px auto 24px;max-width:210mm;min-height:273mm;padding:12mm;overflow:hidden}@media print{html,body{background:#fff}.wv-agreement-print-page{box-shadow:none;margin:0;max-width:none;min-height:0;overflow:visible;padding:0}}</style>';
if($isFullDocument)
{
    if(stripos($agreementHtml, '</head>') !== false)
    {
        $agreementHtml = str_ireplace('</head>', $actionCss.'</head>', $agreementHtml);
    }
    else
    {
        $agreementHtml = $actionCss.$agreementHtml;
    }
    if(preg_match('/<body[^>]*>/i', $agreementHtml))
    {
        $agreementHtml = preg_replace('/<body([^>]*)>/i', '<body$1>'.$printButton, $agreementHtml, 1);
    }
    else
    {
        $agreementHtml = $printButton.$agreementHtml;
    }
    echo $agreementHtml;
}
else
{
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h(__('Rental Agreement').' - '.$rental['DealRental']['agreement_no']);?></title>
    <?php echo $printCss;?>
</head>
<body>
    <?php echo $printButton;?>
    <div class="wv-agreement-print-page"><?php echo $agreementHtml;?></div>
</body>
</html>
<?php } ?>
