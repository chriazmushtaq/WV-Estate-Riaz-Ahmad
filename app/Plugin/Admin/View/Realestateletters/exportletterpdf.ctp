<?php
$renderedDescription = isset($renderedDescription) ? (string)$renderedDescription : '';
$pdfFileName = isset($pdfFileName) ? (string)$pdfFileName : 'Real-Estate-Letter.pdf';
$pdfPageFormat = isset($pdfPageFormat) ? (is_array($pdfPageFormat) ? $pdfPageFormat : (string)$pdfPageFormat) : 'a4';

$pdfMatchCss = <<<'CSS'
<style id="wv-letter-pdf-preview-match-css" type="text/css">
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
    body.wv-pdf-export-mode {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    body.wv-pdf-export-mode .action-dashboard,
    body.wv-pdf-export-mode .page-separator,
    body.wv-pdf-export-mode .no-print {
        display: none !important;
    }
    body.wv-pdf-export-mode .page,
    body.wv-pdf-export-mode .ashtam-page,
    body.wv-pdf-export-mode .container,
    body.wv-pdf-export-mode .letter-page,
    body.wv-pdf-export-mode .letter-preview-print,
    body.wv-pdf-export-mode #letter-pdf-export-root {
        margin: 0 auto !important;
        box-shadow: none !important;
    }
    body.wv-pdf-export-mode .form-container,
    body.wv-pdf-export-mode .wv-welcome-a4,
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
    body.wv-pdf-export-mode .wv-welcome-a4 header,
    .wv-welcome-a4 header {
        margin-bottom: 0.2rem !important;
        padding: 0.25rem 0.5rem !important;
        padding-bottom: 0.25rem !important;
    }
    body.wv-pdf-export-mode .wv-welcome-a4 header .w-24,
    .wv-welcome-a4 header .w-24 { width: 75px !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 header .h-32,
    .wv-welcome-a4 header .h-32 { height: 98px !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 h1,
    .wv-welcome-a4 h1 { font-size: 1.35rem !important; line-height: 1.3 !important; margin: 0 !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 h2,
    .wv-welcome-a4 h2 { font-size: 0.85rem !important; line-height: 1.3 !important; margin: 0 !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 .form-content-wrapper,
    .wv-welcome-a4 .form-content-wrapper { height: auto !important; min-height: auto !important; display: block !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 .bg-stone-50\/50,
    .wv-welcome-a4 .bg-stone-50\/50 { padding: 0.35rem 0.55rem !important; margin-bottom: 0.25rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 .space-y-4>:not([hidden])~:not([hidden]),
    .wv-welcome-a4 .space-y-4>:not([hidden])~:not([hidden]) { margin-top: 0.25rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 .space-y-2>:not([hidden])~:not([hidden]),
    .wv-welcome-a4 .space-y-2>:not([hidden])~:not([hidden]) { margin-top: 0.15rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 p,
    .wv-welcome-a4 p { margin-top: 0 !important; margin-bottom: 0.2rem !important; line-height: 1.5 !important; font-size: 11px !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 p.pt-4, body.wv-pdf-export-mode .wv-welcome-a4 p.pt-2, body.wv-pdf-export-mode .wv-welcome-a4 p.pt-3,
    .wv-welcome-a4 p.pt-4, .wv-welcome-a4 p.pt-2, .wv-welcome-a4 p.pt-3 { padding-top: 0.15rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 .my-4,
    .wv-welcome-a4 .my-4 { margin-top: 0.25rem !important; margin-bottom: 0.25rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 .mb-6,
    .wv-welcome-a4 .mb-6 { margin-bottom: 0.25rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 .mb-4,
    .wv-welcome-a4 .mb-4 { margin-bottom: 0.2rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 .mt-4,
    .wv-welcome-a4 .mt-4 { margin-top: 0.25rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 .p-4,
    .wv-welcome-a4 .p-4 { padding: 0.35rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 .p-3,
    .wv-welcome-a4 .p-3 { padding: 0.25rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 .p-2\.5,
    .wv-welcome-a4 .p-2\.5 { padding: 0.2rem 0.4rem !important; min-height: 28px !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 .form-section-title,
    .wv-welcome-a4 .form-section-title { font-size: 0.78rem !important; padding: 0.12rem 0.5rem !important; margin-bottom: 0.15rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 footer,
    .wv-welcome-a4 footer {
        margin-top: 0.35rem !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    body.wv-pdf-export-mode .wv-welcome-a4 footer .grid,
    .wv-welcome-a4 footer .grid { padding-top: 0.15rem !important; gap: 0.25rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 footer .h-16,
    .wv-welcome-a4 footer .h-16 { height: 24px !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 footer .mt-6,
    .wv-welcome-a4 footer .mt-6 { margin-top: 0.15rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 footer .mb-1,
    .wv-welcome-a4 footer .mb-1 { margin-bottom: 0.05rem !important; }
    body.wv-pdf-export-mode .wv-welcome-a4 #secure-barcode,
    .wv-welcome-a4 #secure-barcode { height: 12px !important; width: 140px !important; }

    @media print {
        @page {
            size: 8.5in 12.18in;
            margin: 0;
        }
        html, body {
            width: 8.5in !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        body {
            display: block !important;
        }
        .form-container, .wv-welcome-a4 {
            width: 8.5in !important;
            max-width: 8.5in !important;
            box-sizing: border-box !important;
            margin: 0 auto !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            border: 24px solid transparent !important;
            border-image-source: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='72' height='72' viewBox='0 0 72 72'%3E%3Cdefs%3E%3Cg id='flower'%3E%3Ccircle cx='12' cy='12' r='3.5' fill='%23064e3b'/%3E%3Cpath d='M12,12 C10,9 10,4 12,4 C14,4 14,9 12,12 Z' fill='%23064e3b'/%3E%3Cpath d='M12,12 C10,15 10,20 12,20 C14,20 14,15 12,12 Z' fill='%23064e3b'/%3E%3Cpath d='M12,12 C9,10 4,10 4,12 C4,14 9,14 12,12 Z' fill='%23064e3b'/%3E%3Cpath d='M12,12 C15,10 20,10 20,12 C20,14 15,14 12,12 Z' fill='%23064e3b'/%3E%3Cpath d='M12,12 C10,11 6.5,7.5 7.5,6.5 C8.5,5.5 11,10 12,12 Z' fill='%23064e3b'/%3E%3Cpath d='M12,12 C11,10 14.5,6.5 15.5,7.5 C16.5,8.5 12,11 12,12 Z' fill='%23064e3b'/%3E%3Cpath d='M12,12 C11,14 7.5,15.5 6.5,14.5 C5.5,13.5 10,11 12,12 Z' fill='%23064e3b'/%3E%3Cpath d='M12,12 C13,14 16.5,14.5 15.5,15.5 C14.5,16.5 11,13 12,12 Z' fill='%23064e3b'/%3E%3C/g%3E%3C/defs%3E%3Cpath d='M24,2 L2,2 L2,24' fill='none' stroke='%23064e3b' stroke-width='1.5'/%3E%3Cpath d='M24,22 L22,22 L22,24' fill='none' stroke='%23064e3b' stroke-width='1.5'/%3E%3Cuse href='%23flower' x='0' y='0'/><line x1='24' y1='2' x2='48' y2='2' stroke='%23064e3b' stroke-width='1.5'/><line x1='24' y1='22' x2='48' y2='22' stroke='%23064e3b' stroke-width='1.5'/><use href='%23flower' x='24' y='0'/><path d='M48,2 L70,2 L70,24' fill='none' stroke='%23064e3b' stroke-width='1.5'/><path d='M48,22 L50,22 L50,24' fill='none' stroke='%23064e3b' stroke-width='1.5'/><use href='%23flower' x='48' y='0'/><line x1='2' y1='24' x2='2' y2='48' stroke='%23064e3b' stroke-width='1.5'/><line x1='22' y1='24' x2='22' y2='48' stroke='%23064e3b' stroke-width='1.5'/><use href='%23flower' x='0' y='24'/><line x1='70' y1='24' x2='70' y2='48' stroke='%23064e3b' stroke-width='1.5'/><line x1='50' y1='24' x2='50' y2='48' stroke='%23064e3b' stroke-width='1.5'/><use href='%23flower' x='48' y='24'/><path d='M2,48 L2,70 L24,70' fill='none' stroke='%23064e3b' stroke-width='1.5'/><path d='M22,48 L22,50 L24,50' fill='none' stroke='%23064e3b' stroke-width='1.5'/><use href='%23flower' x='0' y='48'/><line x1='24' y1='50' x2='48' y2='50' stroke='%23064e3b' stroke-width='1.5'/><line x1='24' y1='70' x2='48' y2='70' stroke='%23064e3b' stroke-width='1.5'/><use href='%23flower' x='24' y='48'/><path d='M70,48 L70,70 L48,70' fill='none' stroke='%23064e3b' stroke-width='1.5'/><path d='M50,48 L50,50 L48,50' fill='none' stroke='%23064e3b' stroke-width='1.5'/><use href='%23flower' x='48' y='48'/></svg>") !important;
            border-image-slice: 24 !important;
            border-image-repeat: repeat !important;
            padding: 0.25rem 0.85rem !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            overflow: visible !important;
            font-size: 11.5px !important;
            line-height: 1.55 !important;
        }
        .form-content-wrapper {
            height: auto !important;
            min-height: auto !important;
            display: block !important;
        }
        .no-print, .action-dashboard, .page-separator {
            display: none !important;
        }
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

$pdfScript = '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>'
    . '<script id="wv-letter-pdf-exporter">(function(){'
    . 'var fileName=' . json_encode($pdfFileName) . ';'
    . 'var pageFormat=' . json_encode($pdfPageFormat) . ';'
    . 'function targetNode(){var pages=document.querySelectorAll(".page");if(pages.length>1){return document.body;}return document.querySelector(".form-container,.ashtam-page,.page,.container,.letter-page,#letter-pdf-export-root,.letter-preview-print")||document.body;}'
    . 'function download(){if(typeof html2pdf==="undefined"){alert("PDF export library could not load. Please try again.");return;}var node=targetNode();if(!node){return;}document.body.classList.add("wv-pdf-export-mode");var canvasWidth=Math.max(node.scrollWidth||0,document.documentElement.scrollWidth||0,1200);var opt={margin:0,filename:fileName,image:{type:"jpeg",quality:0.98},html2canvas:{scale:2,useCORS:true,scrollX:0,scrollY:0,windowWidth:canvasWidth,backgroundColor:"#ffffff"},pagebreak:{mode:["avoid-all","css","legacy"],avoid:[".form-container",".section-box","footer"]},jsPDF:{unit:"in",format:pageFormat,orientation:"portrait"}};html2pdf().set(opt).from(node).save().then(function(){document.body.classList.remove("wv-pdf-export-mode");}).catch(function(){document.body.classList.remove("wv-pdf-export-mode");});}'
    . 'function start(){if(document.fonts&&document.fonts.ready){document.fonts.ready.then(function(){setTimeout(download,500);});}else{setTimeout(download,800);}}'
    . 'if(document.readyState==="complete"){start();}else{window.addEventListener("load",start);}'
    . '}());</script>';

if (!empty($isFullHtmlTemplate)) {
    if (stripos($renderedDescription, '</head>') !== false) {
        $renderedDescription = str_ireplace('</head>', $pdfMatchCss . '</head>', $renderedDescription);
    } else {
        $renderedDescription = $pdfMatchCss . $renderedDescription;
    }
    if (stripos($renderedDescription, '</body>') !== false) {
        echo str_ireplace('</body>', $pdfScript . '</body>', $renderedDescription);
    } else {
        echo $renderedDescription . $pdfScript;
    }
    return;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style type="text/css">
        @page { size: auto; margin: 8mm; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        html, body { margin: 0; padding: 0; background: #fff; color: #111; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; }
        #letter-pdf-export-root { width: 100%; max-width: 100%; margin: 0 auto; background: #fff; }
        #letter-pdf-export-root img { max-width: 100%; height: auto; }
        #letter-pdf-export-root table { max-width: 100%; }
    </style>
    <?php echo $pdfMatchCss; ?>
</head>
<body>
    <div id="letter-pdf-export-root">
        <?php echo $renderedDescription; ?>
    </div>
    <?php echo $pdfScript; ?>
</body>
</html>
