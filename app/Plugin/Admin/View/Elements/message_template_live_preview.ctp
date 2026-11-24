<?php
$previewMode = !empty($previewMode) ? strtolower(trim((string)$previewMode)) : 'auto';
$defaultMode = !empty($defaultMode) ? strtolower(trim((string)$defaultMode)) : ($previewMode === 'auto' ? 'email' : $previewMode);
$sourceSelector = isset($sourceSelector) ? trim((string)$sourceSelector) : '';
$typeSelector = isset($typeSelector) ? trim((string)$typeSelector) : '';
$initialType = isset($initialType) ? strtoupper(trim((string)$initialType)) : '';
$sample = !empty($templatePreviewSample) && is_array($templatePreviewSample) ? $templatePreviewSample : array();
$sample += array(
    'client_name' => 'SANA ULLAH S/O MUHAMMAD SHARIF',
    'invoice_no' => 'WV-L /AB/2024-4012',
    'receipt_no' => 'WV-L-4012-18',
    'property_name' => 'ML2138492-SF',
    'payment_name' => '16th Installment',
    'payment_date' => date('d-M-Y'),
    'receipt_url' => $this->Html->url('/paid_receipts/view/1010/sample-token', true),
    'installment_status' => 'Overdue Installments (16)',
    'due_date' => date('d-M-Y', strtotime('+5 days')),
    'original_due_date' => date('d-M-Y'),
    'amount' => 'Rs. 640,000',
    'amount_words' => 'Six Lakh Forty Thousand Rupees Only',
    'paid_amount' => 'Rs. 640,000',
    'paid_amount_words' => 'Six Lakh Forty Thousand Rupees Only',
    'paid_amount_number' => '640,000',
    'total_amount' => 'Rs. 640,000',
    'total_amount_number' => '640,000',
    'tax_amount' => 'Rs. 0',
    'tax_amount_number' => '0',
    'currency' => 'Rs.',
    'site_name' => 'White Villas',
    'url' => '/',
    'total_overdue_installments' => '16',
    'total_overdue_amount' => 'Rs. 640,000',
    'oldest_overdue_age' => '472 days',
    'overdue_age' => '472',
    'agency_name' => 'White Villas',
    'agency_logo_url' => '',
    'agency_tagline' => '',
    'agency_address' => '',
    'agency_contact_no' => '',
    'agency_email' => '',
    'agency_uan' => '',
    'agency_website' => '',
    'bank_accounts' => '',
    'primary_bank_account' => '',
    'bank_name' => '',
    'account_title' => '',
    'account_no' => '',
    'iban' => '',
    'bank_branch' => '',
    'swift_code' => ''
);
$sampleJson = json_encode($sample, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<?php if(empty($GLOBALS['wvMessageTemplateLivePreviewAssets'])){ $GLOBALS['wvMessageTemplateLivePreviewAssets'] = true; ?>
<style type="text/css">
.wv-template-preview{background:#f8fafc;border:1px solid #d9e3ef;border-radius:8px;margin-top:12px;overflow:hidden}
.wv-template-preview-head{align-items:center;background:#eef4fb;border-bottom:1px solid #d9e3ef;display:flex;gap:8px;justify-content:space-between;padding:9px 10px}
.wv-template-preview-title{color:#0f172a;font-size:12px;font-weight:800;text-transform:uppercase}
.wv-template-preview-actions{display:flex;flex-wrap:wrap;gap:5px}
.wv-template-preview-actions button{background:#fff;border:1px solid #cbd5e1;border-radius:5px;color:#334155;font-size:11px;font-weight:700;line-height:1;padding:7px 8px}
.wv-template-preview-actions button.active{background:#0b3d2e;border-color:#0b3d2e;color:#fff}
.wv-template-preview-meta{background:#fff;border-bottom:1px solid #e5edf5;color:#64748b;font-size:11px;padding:7px 10px}
.wv-template-preview-body{background:#fff;min-height:180px;padding:10px}
.wv-template-preview-frame{background:#fff;border:1px solid #dce5ef;border-radius:6px;height:430px;width:100%}
.wv-template-preview-text{background:#f8fafc;border:1px solid #dce5ef;border-radius:6px;color:#111827;font-family:Consolas,Menlo,Monaco,monospace;font-size:12px;line-height:1.55;margin:0;min-height:150px;padding:12px;white-space:pre-wrap;word-break:break-word}
.wv-template-preview-phone{background:#e5ddd5;border:1px solid #d7ccc2;border-radius:12px;margin:0 auto;max-width:390px;padding:14px}
.wv-template-preview-bubble{background:#dcf8c6;border-radius:8px;box-shadow:0 1px 1px rgba(0,0,0,.12);color:#111;font-family:Arial,sans-serif;font-size:13px;line-height:1.45;padding:10px 12px;white-space:normal;word-break:break-word}
.wv-template-preview-empty{color:#94a3b8;font-size:12px;font-weight:700;padding:22px;text-align:center}
</style>
<script type="text/javascript">
(function(window, $){
    if(!$){ return; }

    function htmlEscape(value){
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function emojiFromCodePoint(code){
        code = Number(code);
        if(window.String && String.fromCodePoint){ return String.fromCodePoint(code); }
        if(code <= 0xffff){ return String.fromCharCode(code); }
        code -= 0x10000;
        return String.fromCharCode((code >> 10) + 0xd800, (code % 0x400) + 0xdc00);
    }

    function buildMap(sample, type){
        sample = sample || {};
        type = String(type || '').toUpperCase();
        var oldestLabel = sample.oldest_overdue_age || ((sample.overdue_age || '0') + ' days');
        var amountValue = (type === 'PAY' || type === 'PAW') ? (sample.paid_amount_number || sample.amount || '') : sample.amount;
        var map = {
            '$siteName': sample.site_name || sample.agency_name,
            '$url': sample.url || '/',
            '{{EMOJI_CHECK}}': emojiFromCodePoint(0x2705),
            '{{EMOJI_RECEIPT}}': emojiFromCodePoint(0x1f9fe),
            '{{EMOJI_MONEY}}': emojiFromCodePoint(0x1f4b0),
            '{{EMOJI_PIN}}': emojiFromCodePoint(0x1f4cc),
            '{{EMOJI_CALENDAR}}': emojiFromCodePoint(0x1f4c5),
            '{{EMOJI_LINK}}': emojiFromCodePoint(0x1f517),
            '{{EMOJI_PHONE}}': emojiFromCodePoint(0x1f4de),
            '$date': (type === 'PAY' || type === 'PAW') ? (sample.payment_date || sample.due_date) : sample.due_date,
            '$currency': sample.currency || 'Rs.',
            '$clientName': sample.client_name,
            '$memberName': sample.client_name,
            '$name': sample.client_name,
            '$invoiceNo': sample.invoice_no,
            '$membershipNo': sample.invoice_no,
            '$receiptNo': sample.receipt_no,
            '$propertyName': sample.property_name,
            '$paymentName': sample.payment_name,
            '$paymentDate': sample.payment_date,
            '$receiptUrl': sample.receipt_url,
            '$printReceiptUrl': sample.receipt_url,
            '$installment': sample.installment_status,
            '$installmentStatus': sample.installment_status,
            '$amount': amountValue,
            '$paidAmount': sample.paid_amount_number || sample.paid_amount,
            '$amountWords': sample.amount_words || sample.paid_amount_words,
            '$paidAmountWords': sample.paid_amount_words || sample.amount_words,
            '$totalAmount': sample.total_amount_number || sample.total_amount,
            '$taxAmount': sample.tax_amount_number || sample.tax_amount,
            '$installmentAmount': sample.amount,
            '$dueDate': sample.due_date,
            '$originalDueDate': sample.original_due_date,
            '$overdueInstallmentCount': sample.total_overdue_installments,
            '$totalOverdueInstallmentCount': sample.total_overdue_installments,
            '$totalOverdueAmount': sample.total_overdue_amount,
            '$totalOverdue': sample.total_overdue_amount,
            '$overdueAge': sample.overdue_age,
            '$agencyName': sample.agency_name,
            '$projectName': sample.agency_name,
            '$agencyPhone': sample.agency_contact_no,
            '$contactNo': sample.agency_contact_no,
            '$bankAccounts': sample.bank_accounts,
            '$paymentAccounts': sample.bank_accounts,
            '$primaryBankAccount': sample.primary_bank_account,
            '$bankName': sample.bank_name,
            '$accountTitle': sample.account_title,
            '$accountNo': sample.account_no,
            '$iban': sample.iban,
            '$bankIban': sample.iban,
            '$bankBranch': sample.bank_branch,
            '$swiftCode': sample.swift_code,
            '$agencyLogoUrl': sample.agency_logo_url,
            '$agencyTagline': sample.agency_tagline,
            '$agencyAddress': sample.agency_address,
            '$agencyEmail': sample.agency_email,
            '$agencyUan': sample.agency_uan,
            '$agencyWebsite': sample.agency_website,
            '{{CLIENT_NAME}}': sample.client_name,
            '{{MEMBERSHIP_NO}}': sample.invoice_no,
            '{{INVOICE_NO}}': sample.invoice_no,
            '{{RECEIPT_NO}}': sample.receipt_no,
            '{{PROPERTY_FILE_NO}}': sample.property_name,
            '{{PROPERTY_NAME}}': sample.property_name,
            '{{PAYMENT_NAME}}': sample.payment_name,
            '{{PAYMENT_DATE}}': sample.payment_date,
            '{{RECEIPT_URL}}': sample.receipt_url,
            '{{PRINT_RECEIPT_URL}}': sample.receipt_url,
            '{{INSTALLMENT_STATUS}}': sample.installment_status,
            '{{DUE_DATE}}': sample.due_date,
            '{{ORIGINAL_DUE_DATE}}': sample.original_due_date,
            '{{INSTALLMENT_AMOUNT}}': sample.amount,
            '{{AMOUNT}}': sample.amount,
            '{{PAID_AMOUNT}}': sample.paid_amount,
            '{{AMOUNT_WORDS}}': sample.amount_words || sample.paid_amount_words,
            '{{PAID_AMOUNT_WORDS}}': sample.paid_amount_words || sample.amount_words,
            '{{TOTAL_AMOUNT}}': sample.total_amount,
            '{{TAX_AMOUNT}}': sample.tax_amount,
            '{{AMOUNT_WITH_CURRENCY}}': sample.paid_amount,
            '{{PAID_AMOUNT_WITH_CURRENCY}}': sample.paid_amount,
            '{{TOTAL_AMOUNT_WITH_CURRENCY}}': sample.total_amount,
            '{{TAX_AMOUNT_WITH_CURRENCY}}': sample.tax_amount,
            '{{CURRENCY}}': sample.currency || 'Rs.',
            '{{TOTAL_OVERDUE_INSTALLMENTS}}': sample.total_overdue_installments,
            '{{TOTAL_OVERDUE_AMOUNT}}': sample.total_overdue_amount,
            '{{OLDEST_OVERDUE_AGE}}': oldestLabel,
            '{{AGENCY_NAME}}': sample.agency_name,
            '{{PROJECT_NAME}}': sample.agency_name,
            '{{AGENCY_LOGO_URL}}': sample.agency_logo_url,
            '{{AGENCY_TAGLINE}}': sample.agency_tagline,
            '{{AGENCY_ADDRESS}}': sample.agency_address,
            '{{AGENCY_CONTACT_NO}}': sample.agency_contact_no,
            '{{AGENCY_PHONE}}': sample.agency_contact_no,
            '{{AGENCY_EMAIL}}': sample.agency_email,
            '{{AGENCY_UAN}}': sample.agency_uan,
            '{{AGENCY_WEBSITE}}': sample.agency_website,
            '{{BANK_ACCOUNTS}}': sample.bank_accounts,
            '{{PRIMARY_BANK_ACCOUNT}}': sample.primary_bank_account,
            '{{BANK_NAME}}': sample.bank_name,
            '{{ACCOUNT_TITLE}}': sample.account_title,
            '{{ACCOUNT_NO}}': sample.account_no,
            '{{IBAN}}': sample.iban,
            '{{BANK_BRANCH}}': sample.bank_branch,
            '{{SWIFT_CODE}}': sample.swift_code,
            '{{client_name}}': sample.client_name,
            '{{invoice_no}}': sample.invoice_no,
            '{{membership_no}}': sample.invoice_no,
            '{{receipt_no}}': sample.receipt_no,
            '{{property_name}}': sample.property_name,
            '{{property_file_no}}': sample.property_name,
            '{{payment_name}}': sample.payment_name,
            '{{payment_date}}': sample.payment_date,
            '{{receipt_url}}': sample.receipt_url,
            '{{print_receipt_url}}': sample.receipt_url,
            '{{installment_status}}': sample.installment_status,
            '{{amount}}': sample.amount,
            '{{paid_amount}}': sample.paid_amount,
            '{{amount_words}}': sample.amount_words || sample.paid_amount_words,
            '{{paid_amount_words}}': sample.paid_amount_words || sample.amount_words,
            '{{total_amount}}': sample.total_amount,
            '{{tax_amount}}': sample.tax_amount,
            '{{installment_amount}}': sample.amount,
            '{{due_date}}': sample.due_date,
            '{{original_due_date}}': sample.original_due_date,
            '{{total_overdue_amount}}': sample.total_overdue_amount,
            '{{total_overdue_installments}}': sample.total_overdue_installments,
            '{{overdue_count}}': sample.total_overdue_installments,
            '{{oldest_overdue_age}}': oldestLabel,
            '{{overdue_age}}': sample.overdue_age,
            '{{agency_name}}': sample.agency_name,
            '{{project_name}}': sample.agency_name,
            '{{agency_logo_url}}': sample.agency_logo_url,
            '{{agency_tagline}}': sample.agency_tagline,
            '{{agency_address}}': sample.agency_address,
            '{{contact_no}}': sample.agency_contact_no,
            '{{agency_contact_no}}': sample.agency_contact_no,
            '{{agency_phone}}': sample.agency_contact_no,
            '{{agency_email}}': sample.agency_email,
            '{{agency_uan}}': sample.agency_uan,
            '{{agency_website}}': sample.agency_website,
            '{{bank_accounts}}': sample.bank_accounts,
            '{{payment_accounts}}': sample.bank_accounts,
            '{{primary_bank_account}}': sample.primary_bank_account,
            '{{bank_name}}': sample.bank_name,
            '{{account_title}}': sample.account_title,
            '{{account_no}}': sample.account_no,
            '{{iban}}': sample.iban,
            '{{bank_iban}}': sample.iban,
            '{{bank_branch}}': sample.bank_branch,
            '{{swift_code}}': sample.swift_code,
            '{{lead_name}}': sample.client_name,
            '{{lead_mobile}}': sample.agency_contact_no,
            '{{lead_email}}': sample.agency_email,
            '{{lead_source}}': 'Website',
            '{{remarks}}': 'Interested in payment plan',
            '{{last_contact_date}}': sample.original_due_date,
            '{{follow_up}}': sample.due_date
        };
        return map;
    }

    function replaceTokens(template, sample, type){
        var map = buildMap(sample, type);
        var keys = Object.keys(map).sort(function(a, b){ return b.length - a.length; });
        var rendered = String(template == null ? '' : template);
        $.each(keys, function(_, key){
            rendered = rendered.split(key).join(map[key] == null ? '' : String(map[key]));
        });
        return rendered;
    }

    function parseSample($box){
        try {
            return JSON.parse($box.attr('data-sample') || '{}');
        } catch(e) {
            return {};
        }
    }

    function sourceValue($source){
        if(!$source || !$source.length){ return ''; }
        var id = $source.attr('id');
        if(id && window.tinymce && window.tinymce.get(id)){
            return window.tinymce.get(id).getContent();
        }
        return $source.val() || '';
    }

    function findSource($box){
        var selector = $box.attr('data-source') || '';
        if(selector && $(selector).length){ return $(selector).first(); }
        var $scope = $box.closest('.js-template-preview-scope');
        var $source = $scope.find('.js-template-preview-source').first();
        if($source.length){ return $source; }
        return $box.prevAll('textarea,input[type="text"],input:not([type])').first();
    }

    function currentType($box){
        var selector = $box.attr('data-type-source') || '';
        if(selector && $(selector).length){ return String($(selector).val() || '').toUpperCase(); }
        var $form = $box.closest('form');
        var $type = $form.find('input[name$="[type]"],input[name="data[Emailtemplate][type]"],input[name="data[Smstemplate][type]"]').first();
        if($type.length){ return String($type.val() || '').toUpperCase(); }
        return String($box.attr('data-initial-type') || '').toUpperCase();
    }

    function resolveMode($box){
        var manual = $box.data('manualMode') || '';
        var mode = manual || String($box.attr('data-mode') || 'auto').toLowerCase();
        if(mode !== 'auto'){ return mode; }
        var type = currentType($box);
        if(type === 'PRE' || type === 'PDE'){ return 'email'; }
        if(type === 'PRW' || type === 'PDW' || type === 'PAW'){ return 'whatsapp'; }
        if(type === 'PRS' || type === 'PDS'){ return 'sms'; }
        if($box.attr('data-default-mode')){ return String($box.attr('data-default-mode')).toLowerCase(); }
        return 'email';
    }

    function looksHtml(value){
        return /<(html|body|table|div|p|br|h[1-6]|span|strong|img|a)\b/i.test(String(value || ''));
    }

    function whatsappFormat(value){
        var escaped = htmlEscape(value);
        escaped = escaped.replace(/\*([^*\n][^*]*?)\*/g, '<strong>$1</strong>');
        return escaped.replace(/\n/g, '<br>');
    }

    function renderPreview($box){
        var $source = findSource($box);
        var template = sourceValue($source);
        var sample = parseSample($box);
        var type = currentType($box);
        var rendered = replaceTokens(template, sample, type);
        var mode = resolveMode($box);
        var signature = mode + '|' + rendered;
        if($box.data('lastSignature') === signature){ return; }
        $box.data('lastSignature', signature);
        $box.find('.js-preview-mode-label').text(mode.toUpperCase());
        $box.find('.js-preview-count').text(rendered.length + ' chars');
        $box.find('[data-preview-mode]').removeClass('active');
        $box.find('[data-preview-mode="' + ($box.data('manualMode') || 'auto') + '"]').addClass('active');
        var $body = $box.find('.js-template-preview-body');
        if($.trim(rendered) === ''){
            $body.html('<div class="wv-template-preview-empty">Template preview will appear here.</div>');
            return;
        }
        if(mode === 'email' && looksHtml(rendered)){
            $body.html('<iframe class="wv-template-preview-frame" sandbox=""></iframe>');
            $body.find('iframe').attr('srcdoc', rendered);
            return;
        }
        if(mode === 'whatsapp'){
            $body.html('<div class="wv-template-preview-phone"><div class="wv-template-preview-bubble">' + whatsappFormat(rendered) + '</div></div>');
            return;
        }
        $body.html('<pre class="wv-template-preview-text"></pre>');
        $body.find('pre').text(rendered);
    }

    function initBox($box){
        if($box.data('previewReady')){ return; }
        $box.data('previewReady', true);
        $box.on('click', '[data-preview-mode]', function(e){
            e.preventDefault();
            var mode = $(this).attr('data-preview-mode') || 'auto';
            $box.data('manualMode', mode);
            $box.data('lastSignature', '');
            renderPreview($box);
        });
        var $source = findSource($box);
        $source.on('input keyup change', function(){
            $box.data('lastSignature', '');
            renderPreview($box);
        });
        $box.closest('form').on('input keyup change', 'input[name$="[type]"],input[name="data[Emailtemplate][type]"],input[name="data[Smstemplate][type]"]', function(){
            $box.data('lastSignature', '');
            renderPreview($box);
        });
        renderPreview($box);
    }

    function initAll(){
        $('.js-wv-template-preview').each(function(){ initBox($(this)); });
    }

    $(document).ready(initAll);
    window.setInterval(function(){
        $('.js-wv-template-preview').each(function(){ renderPreview($(this)); });
    }, 900);
})(window, window.jQuery);
</script>
<?php } ?>
<div class="wv-template-preview js-wv-template-preview" data-mode="<?php echo h($previewMode);?>" data-default-mode="<?php echo h($defaultMode);?>" data-source="<?php echo h($sourceSelector);?>" data-type-source="<?php echo h($typeSelector);?>" data-initial-type="<?php echo h($initialType);?>" data-sample="<?php echo h($sampleJson);?>">
    <div class="wv-template-preview-head">
        <div class="wv-template-preview-title"><?php echo __('Live Preview');?> <span class="text-muted js-preview-mode-label"></span></div>
        <div class="wv-template-preview-actions">
            <button type="button" class="active" data-preview-mode="auto"><?php echo __('Auto');?></button>
            <button type="button" data-preview-mode="email"><?php echo __('Email');?></button>
            <button type="button" data-preview-mode="sms"><?php echo __('SMS');?></button>
            <button type="button" data-preview-mode="whatsapp"><?php echo __('WhatsApp');?></button>
        </div>
    </div>
    <div class="wv-template-preview-meta"><span class="js-preview-count">0 chars</span></div>
    <div class="wv-template-preview-body js-template-preview-body"></div>
</div>
