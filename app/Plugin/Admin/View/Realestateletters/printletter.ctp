<?php
$renderedDescription = isset($renderedDescription) ? (string)$renderedDescription : '';
$isBookingLetter = (stripos($renderedDescription, '12.18in') !== false || stripos($renderedDescription, 'form-container') !== false || stripos($renderedDescription, 'wv-welcome') !== false);
$printPageSize = $isBookingLetter ? 'size: 8.5in 12.18in;' : 'size: auto;';

$urduFontFaceCss = <<<CSS
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
.wv-welcome-a4 {
    width: 8.5in !important;
    max-width: 8.5in !important;
    margin: 0 auto !important;
    box-shadow: none !important;
    box-sizing: border-box !important;
    position: relative !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    overflow: visible !important;
    border-width: 24px !important;
    border-image-slice: 24 !important;
    padding: 0.25rem 0.85rem !important;
    font-size: 11.5px !important;
    line-height: 1.55 !important;
}
.wv-welcome-a4 header {
    margin-bottom: 0.2rem !important;
    padding: 0.25rem 0.5rem !important;
    padding-bottom: 0.25rem !important;
}
.wv-welcome-a4 header .w-24 { width: 75px !important; }
.wv-welcome-a4 header .h-32 { height: 98px !important; }
.wv-welcome-a4 h1 { font-size: 1.35rem !important; line-height: 1.3 !important; margin: 0 !important; }
.wv-welcome-a4 h2 { font-size: 0.85rem !important; line-height: 1.3 !important; margin: 0 !important; }
.wv-welcome-a4 .form-content-wrapper { height: auto !important; min-height: auto !important; display: block !important; }
.wv-welcome-a4 .bg-stone-50\/50 { padding: 0.35rem 0.55rem !important; margin-bottom: 0.25rem !important; }
.wv-welcome-a4 .space-y-4>:not([hidden])~:not([hidden]) { margin-top: 0.25rem !important; }
.wv-welcome-a4 .space-y-2>:not([hidden])~:not([hidden]) { margin-top: 0.15rem !important; }
.wv-welcome-a4 p { margin-top: 0 !important; margin-bottom: 0.2rem !important; line-height: 1.5 !important; font-size: 11px !important; }
.wv-welcome-a4 p.pt-4, .wv-welcome-a4 p.pt-2, .wv-welcome-a4 p.pt-3 { padding-top: 0.15rem !important; }
.wv-welcome-a4 .my-4 { margin-top: 0.25rem !important; margin-bottom: 0.25rem !important; }
.wv-welcome-a4 .mb-6 { margin-bottom: 0.25rem !important; }
.wv-welcome-a4 .mb-4 { margin-bottom: 0.2rem !important; }
.wv-welcome-a4 .mt-4 { margin-top: 0.25rem !important; }
.wv-welcome-a4 .p-4 { padding: 0.35rem !important; }
.wv-welcome-a4 .p-3 { padding: 0.25rem !important; }
.wv-welcome-a4 .p-2\.5 { padding: 0.2rem 0.4rem !important; min-height: 28px !important; }
.wv-welcome-a4 .form-section-title { font-size: 0.78rem !important; padding: 0.12rem 0.5rem !important; margin-bottom: 0.15rem !important; }
.wv-welcome-a4 footer {
    margin-top: 0.35rem !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
}
.wv-welcome-a4 footer .grid { padding-top: 0.15rem !important; gap: 0.25rem !important; }
.wv-welcome-a4 footer .h-16 { height: 24px !important; }
.wv-welcome-a4 footer .mt-6 { margin-top: 0.15rem !important; }
.wv-welcome-a4 footer .mb-1 { margin-bottom: 0.05rem !important; }
.wv-welcome-a4 #secure-barcode { height: 12px !important; width: 140px !important; }
@media print {
    @page {
        {$printPageSize}
        margin: 0;
    }
    html, body {
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
        text-align: center !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    .ashtam-page, .page, .container, .letter-preview-print {
        margin: 4mm auto !important;
        box-shadow: none !important;
        border: none !important;
        overflow: visible !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    .form-container, .wv-welcome-a4 {
        width: 8.5in !important;
        max-width: 8.5in !important;
        margin: 0 auto !important;
        box-shadow: none !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        overflow: visible !important;
        border-width: 24px !important;
        border-image-slice: 24 !important;
        padding: 0.25rem 0.85rem !important;
        font-size: 11.5px !important;
        line-height: 1.55 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    .wv-welcome-a4 header {
        margin-bottom: 0.2rem !important;
        padding: 0.25rem 0.5rem !important;
        padding-bottom: 0.25rem !important;
    }
    .wv-welcome-a4 header .w-24 { width: 75px !important; }
    .wv-welcome-a4 header .h-32 { height: 98px !important; }
    .wv-welcome-a4 h1 { font-size: 1.35rem !important; line-height: 1.3 !important; margin: 0 !important; }
    .wv-welcome-a4 h2 { font-size: 0.85rem !important; line-height: 1.3 !important; margin: 0 !important; }
    .wv-welcome-a4 .form-content-wrapper { height: auto !important; min-height: auto !important; display: block !important; }
    .wv-welcome-a4 .bg-stone-50\/50 { padding: 0.35rem 0.55rem !important; margin-bottom: 0.25rem !important; }
    .wv-welcome-a4 .space-y-4>:not([hidden])~:not([hidden]) { margin-top: 0.25rem !important; }
    .wv-welcome-a4 .space-y-2>:not([hidden])~:not([hidden]) { margin-top: 0.15rem !important; }
    .wv-welcome-a4 p { margin-top: 0 !important; margin-bottom: 0.2rem !important; line-height: 1.5 !important; font-size: 11px !important; }
    .wv-welcome-a4 p.pt-4, .wv-welcome-a4 p.pt-2, .wv-welcome-a4 p.pt-3 { padding-top: 0.15rem !important; }
    .wv-welcome-a4 .my-4 { margin-top: 0.25rem !important; margin-bottom: 0.25rem !important; }
    .wv-welcome-a4 .mb-6 { margin-bottom: 0.25rem !important; }
    .wv-welcome-a4 .mb-4 { margin-bottom: 0.2rem !important; }
    .wv-welcome-a4 .mt-4 { margin-top: 0.25rem !important; }
    .wv-welcome-a4 .p-4 { padding: 0.35rem !important; }
    .wv-welcome-a4 .p-3 { padding: 0.25rem !important; }
    .wv-welcome-a4 .p-2\.5 { padding: 0.2rem 0.4rem !important; min-height: 28px !important; }
    .wv-welcome-a4 .form-section-title { font-size: 0.78rem !important; padding: 0.12rem 0.5rem !important; margin-bottom: 0.15rem !important; }
    .wv-welcome-a4 footer {
        margin-top: 0.35rem !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    .wv-welcome-a4 footer .grid { padding-top: 0.15rem !important; gap: 0.25rem !important; }
    .wv-welcome-a4 footer .h-16 { height: 24px !important; }
    .wv-welcome-a4 footer .mt-6 { margin-top: 0.15rem !important; }
    .wv-welcome-a4 footer .mb-1 { margin-bottom: 0.05rem !important; }
    .wv-welcome-a4 #secure-barcode { height: 12px !important; width: 140px !important; }
    .no-print, .action-dashboard, .page-separator {
        display: none !important;
    }
}
</style>
CSS;

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
    $out = (string)$renderedDescription;
    if (stripos($out, '</head>') !== false) {
        $out = str_ireplace('</head>', $urduFontFaceCss . '</head>', $out);
    } else {
        $out = $urduFontFaceCss . $out;
    }
    if (stripos($out, '</body>') !== false) {
        $out = str_ireplace('</body>', $printScript . '</body>', $out);
    } else {
        $out .= $printScript;
    }
    echo $out;
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
.letter-preview-print { width: 100%; max-width: 100%; margin: 0 auto; background: #fff; }
.letter-preview-print img { max-width: 100%; height: auto; }
.letter-preview-print table { max-width: 100%; }
@media print {
    html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; }
    .letter-preview-print { margin: 0 !important; padding: 0 !important; }
}
</style>
</head>
<body>
<div class="letter-preview-print">
    <?php echo $renderedDescription; ?>
</div>
<?php echo $printScript; ?>
</body>
</html>
