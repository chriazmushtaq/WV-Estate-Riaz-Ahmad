<?php
$urduFontFaceCss = <<<'CSS'
<style type="text/css" id="wv-urdu-font-definitions">
@font-face {
    font-family: 'Jameel Noori Nastaleeq';
    src: url('/whitevillas/fonts/JameelNooriNastaleeq.ttf') format('truetype'),
         url('/fonts/JameelNooriNastaleeq.ttf') format('truetype'),
         url('../fonts/JameelNooriNastaleeq.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}
@font-face {
    font-family: 'Jameel Noori Nastaleeq Kasheeda';
    src: url('/whitevillas/fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype'),
         url('/fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype'),
         url('../fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}
*, *:before, *:after {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
}
#client-photo, img[alt*="Applicant Photo"], img[alt*="Client Photo"] {
    object-fit: contain !important;
    object-position: center !important;
    background-color: #ffffff !important;
}
[lang="ur"], [dir="rtl"], .urdu-text, .ashtam-page, .letter-ur, .urdu-body, .urdu-legal {
    font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', 'Nafees Nastaleeq', serif !important;
    line-height: 1.45;
    letter-spacing: 0;
    word-spacing: 0;
}
[lang="ur"] h1, [lang="ur"] h2, [lang="ur"] h3, [lang="ur"] h4, [lang="ur"] h5, [lang="ur"] h6,
[dir="rtl"] h1, [dir="rtl"] h2, [dir="rtl"] h3, [dir="rtl"] h4, [dir="rtl"] h5, [dir="rtl"] h6,
.ashtam-page h1, .ashtam-page h2, .ashtam-page h3, .ashtam-page h4,
.urdu-heading, .ashtam-heading, .document-title, .document-subtitle, .section-title, .sign-title, .bismillah {
    font-family: 'Jameel Noori Nastaleeq Kasheeda', 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', serif !important;
    font-weight: normal;
    line-height: 1.25;
}
[lang="ur"] p, [lang="ur"] li, [lang="ur"] td, [lang="ur"] th,
[dir="rtl"] p, [dir="rtl"] li, [dir="rtl"] td, [dir="rtl"] th,
.ashtam-page p, .ashtam-page li, .ashtam-page td, .ashtam-page th,
.urdu-body p {
    font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', 'Nafees Nastaleeq', serif !important;
    line-height: 1.45;
}
</style>
CSS;

$clientDuplicateWatermarkCss = '<style id="wv-client-duplicate-watermark-style">'
    . '.wv-client-duplicate-watermark{'
    . 'bottom:0!important;display:block!important;left:0!important;overflow:hidden!important;pointer-events:none!important;position:absolute!important;right:0!important;top:0!important;z-index:2147483647!important;'
    . '}'
    . '.wv-client-duplicate-watermark span{'
    . 'color:rgba(0,0,0,.35)!important;display:block!important;font-family:Arial,Helvetica,sans-serif!important;font-size:clamp(56px,8vw,118px)!important;font-weight:900!important;left:50%!important;letter-spacing:4px!important;line-height:1!important;opacity:.35!important;position:absolute!important;text-align:center!important;text-transform:uppercase!important;top:50%!important;transform:translate(-50%,-50%) rotate(-32deg)!important;white-space:nowrap!important;width:100%!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;'
    . '}'
    . '.wv-client-watermark-host{position:relative!important;}'
    . 'body>.wv-client-duplicate-watermark{position:fixed!important;}'
    . 'body>.wv-client-duplicate-watermark span{font-size:clamp(78px,12vw,150px)!important;width:120%!important;}'
    . '@media print{'
    . '.wv-client-duplicate-watermark{display:block!important;}'
    . '.wv-client-duplicate-watermark span{color:rgba(0,0,0,.35)!important;opacity:.35!important;}'
    . '}'
    . '</style>';
$clientDuplicateWatermarkHtml = '<div class="wv-client-duplicate-watermark"><span>DUPLICATE COPY</span></div>';
$clientDuplicateWatermarkScript = '<script id="wv-client-duplicate-watermark-script">(function(){function hasOwnWatermark(el){for(var i=0;i<el.children.length;i++){if((" "+el.children[i].className+" ").indexOf(" wv-client-duplicate-watermark ")>-1){return true;}}return false;}function addClass(el,name){if((" "+el.className+" ").indexOf(" "+name+" ")===-1){el.className=(el.className?el.className+" ":"")+name;}}function firstTargets(){var groups=[".page",".ashtam-page",".form-container",".letter-preview-print",".container"];for(var i=0;i<groups.length;i++){var nodes=document.querySelectorAll(groups[i]);if(nodes.length){return nodes;}}return [];}function place(){var source=document.querySelector(".wv-client-duplicate-watermark");if(!source){return;}var targets=firstTargets();if(!targets.length){document.body.appendChild(source);return;}for(var i=0;i<targets.length;i++){var host=targets[i];addClass(host,"wv-client-watermark-host");if(hasOwnWatermark(host)){continue;}var wm=(i===0)?source:source.cloneNode(true);host.appendChild(wm);}}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",place);}else{place();}}());</script>';

$printScript = <<<'JS'
<script type="text/javascript" id="wv-smart-print-trigger">
(function(){
    function triggerPrint() {
        if (typeof window.print !== 'function') return;
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function() {
                setTimeout(function(){ window.print(); }, 400);
            });
        } else {
            setTimeout(function(){ window.print(); }, 700);
        }
    }
    if (document.readyState === 'complete') {
        triggerPrint();
    } else {
        window.addEventListener('load', triggerPrint);
    }
}());
</script>
JS;

if (!empty($isFullHtmlTemplate)) {
    $clientLetterHtml = (string)$renderedDescription;
    if (stripos($clientLetterHtml, 'wv-urdu-font-definitions') === false) {
        if (stripos($clientLetterHtml, '</head>') !== false) {
            $clientLetterHtml = str_ireplace('</head>', $urduFontFaceCss . '</head>', $clientLetterHtml);
        } else {
            $clientLetterHtml = $urduFontFaceCss . $clientLetterHtml;
        }
    }
    if (stripos($clientLetterHtml, 'wv-client-duplicate-watermark-style') === false) {
        if (stripos($clientLetterHtml, '</head>') !== false) {
            $clientLetterHtml = str_ireplace('</head>', $clientDuplicateWatermarkCss . '</head>', $clientLetterHtml);
        } else {
            $clientLetterHtml = $clientDuplicateWatermarkCss . $clientLetterHtml;
        }
        if (stripos($clientLetterHtml, '</body>') !== false) {
            $clientLetterHtml = str_ireplace('</body>', $clientDuplicateWatermarkHtml . $clientDuplicateWatermarkScript . $printScript . '</body>', $clientLetterHtml);
        } else {
            $clientLetterHtml .= $clientDuplicateWatermarkHtml . $clientDuplicateWatermarkScript . $printScript;
        }
    }
    echo $clientLetterHtml;
    return;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<?php echo $urduFontFaceCss; ?>
<style type="text/css">
@page { size: auto; margin: 8mm; }
* { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
html, body { margin: 0; padding: 0; background: #fff; color: #111; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; }
.letter-preview-print { width: 100%; max-width: 100%; margin: 0 auto; background: #fff; min-height: 80vh; position: relative; }
.letter-preview-print img { max-width: 100%; height: auto; }
.letter-preview-print table { max-width: 100%; }
@media print {
    html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; }
    .letter-preview-print { margin: 0 !important; padding: 0 !important; }
}
</style>
<?php echo $clientDuplicateWatermarkCss; ?>
</head>
<body>
<div class="letter-preview-print">
    <?php echo $renderedDescription; ?>
    <?php echo $clientDuplicateWatermarkHtml; ?>
</div>
<?php echo $clientDuplicateWatermarkScript; ?>
<?php if (!isset($autoPrint) || $autoPrint) { echo $printScript; } ?>
</body>
</html>
