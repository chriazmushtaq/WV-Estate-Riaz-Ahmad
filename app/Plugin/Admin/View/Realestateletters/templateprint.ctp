<?php
$templateDescription = isset($templateDescription) ? (string)$templateDescription : '';
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
@media print {
    @page { size: auto; margin: 0; }
    html, body {
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
        text-align: center !important;
    }
    .ashtam-page, .page, .form-container, .container, .letter-template-print {
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
    .no-print, .action-dashboard, .page-separator { display: none !important; }
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
    if (stripos($templateDescription, '</head>') !== false) {
        $templateDescription = str_ireplace('</head>', $urduFontFaceCss . '</head>', $templateDescription);
    } else {
        $templateDescription = $urduFontFaceCss . $templateDescription;
    }
    if (stripos($templateDescription, '</body>') !== false) {
        echo str_ireplace('</body>', $printScript . '</body>', $templateDescription);
    } else {
        echo $templateDescription . $printScript;
    }
    return;
}
?>
<?php echo $this->Html->css('print.css'); ?>
<?php echo $urduFontFaceCss; ?>
<style type="text/css">
body { background: #fff; color: #111; margin: 20px; }
.letter-template-print { max-width: 900px; margin: 0 auto; font-size: 14px; line-height: 1.6; }
</style>
<div class="letter-template-print">
    <?php echo $templateDescription; ?>
</div>
<?php echo $printScript; ?>
