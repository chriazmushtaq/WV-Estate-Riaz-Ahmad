<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Demo Template Preview - <?php echo h($template['BillingTemplate']['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet" />
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
        }
        .urdu-font { font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Urdu Typesetting', serif !important; }
    </style>
</head>
<body class="bg-slate-800 p-6 min-h-screen">

    <!-- Control Header (Hidden on Print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-slate-900 text-white p-4 rounded-xl shadow-2xl flex justify-between items-center border border-slate-700">
        <div class="flex items-center gap-3">
            <span class="inline-block w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
            <div>
                <h3 class="font-black text-sm uppercase tracking-wider text-slate-100">Live Demo Voucher Preview</h3>
                <p class="text-xs text-slate-400">Template: <strong class="text-amber-400"><?php echo h($template['BillingTemplate']['name']); ?></strong> (ID #<?php echo (int)$template['BillingTemplate']['id']; ?>)</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-4 py-2 rounded-lg shadow transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h10z"></path></svg>
                Print Voucher
            </button>
            <button onclick="window.close()" class="bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs px-4 py-2 rounded-lg shadow transition">
                Close Preview
            </button>
        </div>
    </div>

    <!-- Rendered Demo HTML Voucher -->
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-2xl overflow-hidden">
        <?php echo $previewHtml; ?>
    </div>

</body>
</html>
