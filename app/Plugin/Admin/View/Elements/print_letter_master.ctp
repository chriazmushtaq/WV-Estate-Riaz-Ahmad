<?php
/**
 * Shared Master Real Estate Letter Print & PDF Wrapper Element
 * 1:1 Identical Output for Browser Print Preview and PDF Export
 */
$renderedDescription = isset($renderedDescription) ? (string)$renderedDescription : (isset($templateDescription) ? (string)$templateDescription : '');
$letterType = isset($letterType) ? (string)$letterType : (isset($post['Realestateletter']['letter_type']) ? (string)$post['Realestateletter']['letter_type'] : (isset($post['Template']['type']) ? (string)$post['Template']['type'] : ''));
$letterTitle = isset($post['Realestateletter']['title']) ? (string)$post['Realestateletter']['title'] : (isset($post['Template']['name']) ? (string)$post['Template']['name'] : (isset($letterTypeLabel) ? (string)$letterTypeLabel : 'Real Estate Letter'));
$pdfFileName = isset($pdfFileName) ? (string)$pdfFileName : ($letterTitle !== '' ? preg_replace('/[^A-Za-z0-9_-]+/', '-', $letterTitle) . '.pdf' : 'Real-Estate-Letter.pdf');
if (substr(strtolower($pdfFileName), -4) !== '.pdf') {
    $pdfFileName .= '.pdf';
}
$mode = isset($mode) ? (string)$mode : 'print'; // 'print' or 'pdf'
$showWatermark = !empty($clientWatermark) || !empty($showClientDuplicateWatermark);

// Detection of booking / welcome letters to preserve their exact view styles
$isBookingOrWelcome = (
    in_array($letterType, array('RLT_BOOKING', 'RLT_WELCOME'), true) ||
    stripos($renderedDescription, '13in') !== false ||
    stripos($renderedDescription, '12.18in') !== false ||
    stripos($renderedDescription, 'form-container') !== false ||
    stripos($renderedDescription, 'wv-welcome') !== false
);

// Page format configuration: default Legal portrait with toggle for A4
$requestedPaper = isset($_GET['paper']) ? strtolower(trim($_GET['paper'])) : '';
if ($isBookingOrWelcome) {
    $currentPaper = 'booking';
    $pageRule = '@page { size: 8.5in 13in; margin: 0; }';
    $jsPdfFormat = array(8.5, 13);
} elseif ($requestedPaper === 'a4') {
    $currentPaper = 'a4';
    $pageRule = '@page { size: A4 portrait; margin: 12mm 14mm; }';
    $jsPdfFormat = 'a4';
} else {
    $currentPaper = 'legal';
    $pageRule = '@page { size: Legal portrait; margin: 12mm 14mm; }';
    $jsPdfFormat = 'legal';
}

// Local font URLs and absolute filesystem URIs for PDF engines (Dompdf/mPDF/Puppeteer)
$fontUrl = $this->Html->url('/fonts/JameelNooriNastaleeq.ttf');
$fontKasheedaUrl = $this->Html->url('/fonts/JameelNooriNastaleeqKasheeda.ttf');
$fontFileUri = 'file:///' . str_replace('\\', '/', WWW_ROOT) . 'fonts/JameelNooriNastaleeq.ttf';
$fontKasheedaFileUri = 'file:///' . str_replace('\\', '/', WWW_ROOT) . 'fonts/JameelNooriNastaleeqKasheeda.ttf';

$cssUrl = $this->Html->url('/css/print_letter.css');
$cssFilePath = WWW_ROOT . 'css' . DS . 'print_letter.css';
$cssInline = file_exists($cssFilePath) ? file_get_contents($cssFilePath) : '';

$fontFaceCss = <<<CSS
<style type="text/css" id="wv-master-font-definitions">
@font-face {
    font-family: 'Jameel Noori Nastaleeq';
    src: url('{$fontUrl}') format('truetype'),
         url('{$fontFileUri}') format('truetype'),
         url('/fonts/JameelNooriNastaleeq.ttf') format('truetype'),
         url('/whitevillas/fonts/JameelNooriNastaleeq.ttf') format('truetype'),
         url('../fonts/JameelNooriNastaleeq.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}
@font-face {
    font-family: 'Jameel Noori Nastaleeq Kasheeda';
    src: url('{$fontKasheedaUrl}') format('truetype'),
         url('{$fontKasheedaFileUri}') format('truetype'),
         url('/fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype'),
         url('/whitevillas/fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype'),
         url('../fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}
</style>
CSS;

$pageSizeCss = <<<CSS
<style type="text/css" id="wv-dynamic-page-setup">
{$pageRule}
</style>
CSS;

$inlineMasterCss = <<<CSS
<style type="text/css" id="wv-master-inline-stylesheet">
{$cssInline}
</style>
CSS;

$watermarkCss = '';
$watermarkHtml = '';
if ($showWatermark) {
    $watermarkCss = <<<CSS
<style type="text/css" id="wv-client-duplicate-watermark-style">
.wv-client-duplicate-watermark {
    bottom: 0 !important; display: block !important; left: 0 !important; overflow: hidden !important;
    pointer-events: none !important; position: absolute !important; right: 0 !important; top: 0 !important;
    z-index: 2147483647 !important;
}
.wv-client-duplicate-watermark span {
    color: rgba(0, 0, 0, 0.22) !important; display: block !important;
    font-family: Arial, Helvetica, sans-serif !important; font-size: clamp(56px, 8vw, 118px) !important;
    font-weight: 900 !important; left: 50% !important; letter-spacing: 4px !important; line-height: 1 !important;
    opacity: 0.22 !important; position: absolute !important; text-align: center !important;
    text-transform: uppercase !important; top: 50% !important;
    transform: translate(-50%, -50%) rotate(-32deg) !important; white-space: nowrap !important;
    width: 100% !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
}
.wv-client-watermark-host { position: relative !important; }
body > .wv-client-duplicate-watermark { position: fixed !important; }
@media print {
    .wv-client-duplicate-watermark { display: block !important; }
    .wv-client-duplicate-watermark span { color: rgba(0, 0, 0, 0.22) !important; opacity: 0.22 !important; }
}
</style>
CSS;
    $watermarkHtml = '<div class="wv-client-duplicate-watermark"><span>DUPLICATE COPY</span></div>';
}

// Interactive Smart Toolbar Styles & HTML for Preview & Print
$toolbarCss = <<<CSS
<style type="text/css" id="wv-smart-toolbar-global-style">
#wv-smart-toolbar {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    min-height: 44px !important;
    background: #0b192c !important;
    color: #ffffff !important;
    padding: 8px 18px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
    z-index: 2147483647 !important;
    box-shadow: 0 4px 14px rgba(0,0,0,0.3) !important;
    font-family: Arial, sans-serif !important;
    font-size: 13px !important;
    box-sizing: border-box !important;
    margin: 0 !important;
}
@media screen {
    body {
        padding-top: 54px !important;
    }
}
@media print {
    #wv-smart-toolbar,
    .no-print,
    .wv-smart-toolbar {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
    body {
        padding-top: 0 !important;
        margin: 0 !important;
    }
}
</style>
CSS;

$toolbarHtml = <<<HTML
<div class="no-print wv-smart-toolbar" id="wv-smart-toolbar" style="background:#0b192c;color:#fff;padding:8px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;position:fixed;top:0;left:0;right:0;width:100%;z-index:2147483647;box-shadow:0 4px 14px rgba(0,0,0,0.3);font-family:Arial,sans-serif;font-size:13px;box-sizing:border-box;margin:0;">
    <div style="display:flex;align-items:center;gap:12px;">
        <strong style="color:#7cf9ff;font-size:14px;letter-spacing:0.5px;">White Villas Letter System</strong>
        <span style="background:rgba(255,255,255,0.12);padding:3px 8px;border-radius:4px;font-size:12px;">{$letterTitle}</span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
HTML;

if (!$isBookingOrWelcome) {
    $legalActive = ($currentPaper === 'legal') ? 'background:#00bcd4;color:#000;font-weight:bold;' : 'background:transparent;color:#fff;';
    $a4Active = ($currentPaper === 'a4') ? 'background:#00bcd4;color:#000;font-weight:bold;' : 'background:transparent;color:#fff;';
    $toolbarHtml .= <<<HTML
        <div style="display:inline-flex;border:1px solid #334e68;border-radius:4px;overflow:hidden;">
            <button type="button" onclick="setPaperSize('legal')" style="{$legalActive}border:none;padding:6px 12px;cursor:pointer;font-size:12px;">Legal (8.5×14")</button>
            <button type="button" onclick="setPaperSize('a4')" style="{$a4Active}border:none;padding:6px 12px;cursor:pointer;font-size:12px;">A4 (8.3×11.7")</button>
        </div>
HTML;
}

$toolbarHtml .= <<<HTML
        <button type="button" onclick="triggerBrowserPrint()" style="background:#22c55e;color:#fff;border:none;border-radius:4px;padding:6px 14px;font-weight:bold;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
            <span>🖨️</span> Print Preview
        </button>
        <button type="button" onclick="triggerPdfExport()" style="background:#ef4444;color:#fff;border:none;border-radius:4px;padding:6px 14px;font-weight:bold;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
            <span>📥</span> Download PDF
        </button>
        <button type="button" onclick="window.close()" style="background:#475569;color:#fff;border:none;border-radius:4px;padding:6px 10px;cursor:pointer;">
            Close
        </button>
    </div>
</div>
HTML;

// Universal Scripts for Print and PDF Synchronization
$autoMode = json_encode($mode);
$pdfFileNameJson = json_encode($pdfFileName);
$jsPdfFormatJson = is_array($jsPdfFormat) ? json_encode($jsPdfFormat) : json_encode($jsPdfFormat);

$masterScript = <<<JS
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script type="text/javascript" id="wv-master-print-script">
(function(){
    var currentMode = {$autoMode};
    var pdfName = {$pdfFileNameJson};
    var currentFormat = {$jsPdfFormatJson};

    window.setPaperSize = function(size) {
        var url = new URL(window.location.href);
        url.searchParams.set('paper', size);
        window.location.href = url.toString();
    };

    window.triggerBrowserPrint = function() {
        if (typeof window.print !== 'function') return;
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function() {
                setTimeout(function(){ window.print(); }, 350);
            });
        } else {
            setTimeout(function(){ window.print(); }, 600);
        }
    };

    window.triggerPdfExport = function() {
        if (typeof html2pdf === 'undefined') {
            alert('PDF generator library is loading, please wait a moment...');
            return;
        }
        var node = document.querySelector('.form-container') ||
                   document.querySelector('.letter-master-wrapper') ||
                   document.querySelector('#letter-pdf-export-root') ||
                   document.querySelector('.letter-preview-print') ||
                   document.querySelector('.page') ||
                   document.querySelector('.ashtam-page') ||
                   document.body;

        var isCustomInches = Array.isArray(currentFormat);

        if (typeof window.scrollTo === 'function') {
            window.scrollTo(0, 0);
        }

        var opt = {
            margin: isCustomInches ? 0 : [12, 14, 12, 14], // [top, left, bottom, right] mm (standardized 12mm 14mm)
            filename: pdfName,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                letterRendering: true,
                backgroundColor: '#ffffff',
                scrollX: 0,
                scrollY: 0,
                onclone: function(clonedDoc) {
                    var tb = clonedDoc.querySelector('#wv-smart-toolbar');
                    if (tb && tb.parentNode) {
                        tb.parentNode.removeChild(tb);
                    }
                    var tbs = clonedDoc.querySelectorAll('.no-print, .wv-smart-toolbar');
                    for (var i = 0; i < tbs.length; i++) {
                        if (tbs[i] && tbs[i].parentNode) {
                            tbs[i].parentNode.removeChild(tbs[i]);
                        }
                    }
                    if (clonedDoc.documentElement) {
                        clonedDoc.documentElement.style.margin = '0';
                        clonedDoc.documentElement.style.padding = '0';
                    }
                    if (clonedDoc.body) {
                        clonedDoc.body.style.padding = '0';
                        clonedDoc.body.style.margin = '0';
                        clonedDoc.body.style.backgroundColor = '#ffffff';
                        clonedDoc.body.style.display = 'block';
                    }
                    var formEl = clonedDoc.querySelector('.form-container') || clonedDoc.querySelector('.letter-master-wrapper');
                    if (formEl) {
                        formEl.style.margin = '0';
                        formEl.style.boxShadow = 'none';
                    }
                }
            },
            pagebreak: {
                mode: ['avoid-all', 'css', 'legacy'],
                avoid: ['.sign-area', '.sign-box', '.witness-area', '.signatures-section', '.table-container', '.alert-box', 'table', 'tr', '.form-container']
            },
            jsPDF: {
                unit: isCustomInches ? 'in' : 'mm',
                format: currentFormat,
                orientation: 'portrait'
            }
        };

        html2pdf().set(opt).from(node).save();
    };

    function autoStart() {
        if (currentMode === 'pdf') {
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(function() { setTimeout(window.triggerPdfExport, 600); });
            } else {
                setTimeout(window.triggerPdfExport, 900);
            }
        } else if (currentMode === 'print') {
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(function() { setTimeout(window.triggerBrowserPrint, 400); });
            } else {
                setTimeout(window.triggerBrowserPrint, 700);
            }
        }
    }

    if (document.readyState === 'complete') {
        autoStart();
    } else {
        window.addEventListener('load', autoStart);
    }
}());
</script>
JS;

// If $renderedDescription is already a full HTML document:
$isFullDoc = (stripos($renderedDescription, '<html') !== false && stripos($renderedDescription, '</html>') !== false);

if ($isFullDoc) {
    $out = $renderedDescription;

    // Inject styles in <head>
    // For booking & welcome letters, avoid injecting print_letter.css to preserve their exact tailored layout
    if ($isBookingOrWelcome) {
        $headInjections = $fontFaceCss . "\n" . $pageSizeCss . "\n" . $toolbarCss . "\n" . ($watermarkCss ? $watermarkCss . "\n" : '');
    } else {
        $headInjections = $fontFaceCss . "\n" . $pageSizeCss . "\n" . $inlineMasterCss . "\n" . $toolbarCss . "\n" . ($watermarkCss ? $watermarkCss . "\n" : '') . '<link rel="stylesheet" type="text/css" href="' . $cssUrl . '">' . "\n";
    }

    if (stripos($out, '</head>') !== false) {
        $out = str_ireplace('</head>', $headInjections . '</head>', $out);
    } else {
        $out = $headInjections . $out;
    }

    // Inject toolbar after <body>
    if (stripos($out, '<body') !== false) {
        $out = preg_replace('/(<body[^>]*>)/i', '$1' . "\n" . $toolbarHtml . "\n" . ($watermarkHtml ? $watermarkHtml . "\n" : ''), $out, 1);
    } else {
        $out = $toolbarHtml . ($watermarkHtml ? "\n" . $watermarkHtml : '') . "\n" . $out;
    }

    // Inject script before </body>
    if (stripos($out, '</body>') !== false) {
        $out = str_ireplace('</body>', $masterScript . '</body>', $out);
    } else {
        $out .= $masterScript;
    }

    echo $out;
    return;
}
?>
<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($letterTitle); ?></title>
    <?php echo $fontFaceCss; ?>
    <?php echo $pageSizeCss; ?>
    <?php echo $inlineMasterCss; ?>
    <?php echo $toolbarCss; ?>
    <?php if ($watermarkCss) { echo $watermarkCss; } ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $cssUrl; ?>">
</head>
<body class="<?php echo ($currentPaper === 'a4') ? 'paper-a4' : 'paper-legal'; ?>">
    <?php echo $toolbarHtml; ?>
    <div class="letter-master-wrapper" id="letter-pdf-export-root">
        <?php echo $renderedDescription; ?>
        <?php if ($watermarkHtml) { echo $watermarkHtml; } ?>
    </div>
    <?php echo $masterScript; ?>
</body>
</html>
