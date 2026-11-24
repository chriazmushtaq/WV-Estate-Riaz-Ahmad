<style>
/* --- 100% Full-Width Stacked Template Editor Styling --- */
.subm-template-editor-wrapper {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 100% !important;
    clear: both !important;
    display: block !important;
    float: none !important;
    box-sizing: border-box !important;
}

.subm-template-editor-wrapper form {
    width: 100% !important;
    display: block !important;
    clear: both !important;
}

.subm-editor-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 8px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important;
    width: 100% !important;
    max-width: 100% !important;
    clear: both !important;
    display: block !important;
    float: none !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
}

.subm-editor-card-header {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 14px 22px !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
}

.subm-editor-card-header::before {
    content: "" !important;
    display: inline-block !important;
    width: 4px !important;
    height: 16px !important;
    background: #0284c7 !important;
    border-radius: 2px !important;
}

.subm-editor-card-body {
    padding: 24px 26px !important;
    width: 100% !important;
    display: block !important;
    clear: both !important;
    box-sizing: border-box !important;
}

.subm-editor-card-body .form-control {
    font-weight: 400 !important;
    font-size: 13.5px !important;
    color: #1e293b !important;
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 6px !important;
    padding: 8px 12px !important;
    box-shadow: none !important;
}

.subm-editor-card-body .form-control:focus {
    border-color: #0284c7 !important;
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12) !important;
}

.subm-editor-card-body label.control-label {
    font-weight: 600 !important;
    font-size: 13px !important;
    color: #334155 !important;
    text-align: left !important;
    padding-top: 8px !important;
}

/* Giant Full-Width Dark Code Editor */
.subm-code-editor {
    width: 100% !important;
    min-height: 620px !important;
    height: 650px !important;
    background: #090d16 !important;
    color: #38bdf8 !important;
    font-family: 'Consolas', 'Fira Code', 'Courier New', monospace !important;
    font-size: 13.5px !important;
    line-height: 1.6 !important;
    padding: 20px 24px !important;
    border: 1px solid #1e293b !important;
    border-radius: 6px !important;
    tab-size: 4 !important;
    white-space: pre !important;
    overflow: auto !important;
    resize: vertical !important;
    box-sizing: border-box !important;
    display: block !important;
}

/* Placeholders Pills */
.subm-tag-pill {
    display: inline-block !important;
    background: #f1f5f9 !important;
    color: #0369a1 !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 4px !important;
    padding: 4px 10px !important;
    margin: 4px 6px 4px 0 !important;
    font-family: monospace !important;
    font-size: 12.5px !important;
    cursor: pointer !important;
    transition: all 0.15s ease-in-out !important;
    user-select: none !important;
}

.subm-tag-pill:hover {
    background: #0284c7 !important;
    color: #ffffff !important;
    border-color: #0284c7 !important;
    transform: translateY(-1px) !important;
}

/* Bottom Action Bar */
.subm-editor-bottom-bar {
    background: #f8fafc !important;
    padding: 20px 26px !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 8px !important;
    margin-top: 24px !important;
    margin-bottom: 40px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100% !important;
    clear: both !important;
}
</style>

<div class="row">
    <div class="col-md-12" style="width:100% !important;clear:both !important;">
        <div class="subm-template-editor-wrapper">

            <!-- Page Title Bar -->
            <div class="page-title" style="margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div class="title-env">
                    <h1 class="title" style="font-weight:800;color:#0f172a;font-size:22px;margin:0;">
                        <span class="fa fa-file-code-o text-primary"></span> <?php echo __('Edit Bill Template'); ?>: <span style="color:#0284c7;"><?php echo h($template['BillingTemplate']['name']); ?></span>
                    </h1>
                    <p class="description" style="color:#64748b;font-size:13px;margin-top:4px;">
                        <?php echo __('Customize responsive HTML layout, header branding, meter blocks, and placeholder tags.'); ?>
                    </p>
                </div>
                <div style="display:flex;gap:10px;">
                    <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'print_bill', '?' => array('template_id' => $template['BillingTemplate']['id'], 'preview' => 1))); ?>" target="_blank" class="btn btn-info" style="font-weight:600;background:#0369a1;border-color:#0284c7;padding:8px 18px;">
                        <span class="fa fa-eye"></span> <?php echo __('Live Preview with Demo Data'); ?>
                    </a>
                    <a href="<?php echo $this->Html->url(array('controller' => 'BillingSettings', 'action' => 'index', '?' => array('tab' => 'templates'))); ?>" class="btn btn-default" style="font-weight:600;padding:8px 18px;">
                        &larr; <?php echo __('Back to Templates'); ?>
                    </a>
                </div>
            </div>

            <?php echo $this->Session->flash(); ?>

            <div class="alert alert-warning" style="background:#fffbeb;border-color:#fef3c7;color:#92400e;font-size:12.5px;border-radius:6px;padding:10px 14px;margin-bottom:22px;">
                <span class="fa fa-info-circle"></span> <?php echo __('WooCommerce SMS Notification requires WooCommerce to be installed and active.'); ?>
            </div>

            <?php echo $this->Form->create('BillingTemplate', array('class' => 'form-horizontal', 'style' => 'width:100% !important;display:block !important;clear:both !important;')); ?>
            <?php echo $this->Form->input('id', array('type' => 'hidden')); ?>

            <!-- ========================================== -->
            <!-- CARD 1: TEMPLATE SETTING                   -->
            <!-- ========================================== -->
            <div class="subm-editor-card">
                <div class="subm-editor-card-header"><?php echo __('Template Setting'); ?></div>
                <div class="subm-editor-card-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('NAME'); ?> <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            <?php echo $this->Form->input('name', array('class' => 'form-control', 'label' => false, 'div' => false, 'required' => true, 'placeholder' => 'e.g. Full Bill')); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('TEMPLATE TYPE'); ?></label>
                        <div class="col-sm-4">
                            <?php 
                            $typeOptions = array(
                                'print_bill' => 'Print Bill',
                                'full_paid' => 'Full Paid',
                                'partial_paid' => 'Partial Paid'
                            );
                            echo $this->Form->input('template_type', array('type' => 'select', 'options' => $typeOptions, 'class' => 'form-control', 'label' => false, 'div' => false)); 
                            ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('IS DEFAULT FOR SELECTED TYPE?'); ?></label>
                        <div class="col-sm-10">
                            <label style="margin-top:8px;font-weight:400;font-size:13px;color:#334155;">
                                <?php echo $this->Form->checkbox('is_default', array('div' => false)); ?> <?php echo __('SET THIS TEMPLATE AS PRIMARY DEFAULT'); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- CARD 2: TEMPLATE ASSETS & HTML BLOCKS      -->
            <!-- ========================================== -->
            <div class="subm-editor-card">
                <div class="subm-editor-card-header"><?php echo __('Template Assets & HTML Blocks'); ?></div>
                <div class="subm-editor-card-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('TEMPLATE SOCIETY LOGO'); ?></label>
                        <div class="col-sm-7">
                            <div class="input-group">
                                <input type="text" name="data[BillingTemplate][logo_url]" id="tplLogoUrl" value="<?php echo !empty($template['BillingTemplate']['logo_url']) ? h($template['BillingTemplate']['logo_url']) : ''; ?>" class="form-control" placeholder="https://.../logo.png" />
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary upload-logo-btn" data-target="#tplLogoUrl"><?php echo __('Upload Logo'); ?></button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('PAID RECEIPT STAMP IMAGE'); ?></label>
                        <div class="col-sm-7">
                            <div class="input-group">
                                <input type="text" name="data[BillingTemplate][stamp_url]" id="tplStampUrl" value="<?php echo !empty($template['BillingTemplate']['stamp_url']) ? h($template['BillingTemplate']['stamp_url']) : ''; ?>" class="form-control" placeholder="https://.../paid-stamp.png" />
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary upload-stamp-btn" data-target="#tplStampUrl"><?php echo __('Upload Stamp'); ?></button>
                                </span>
                            </div>
                            <small class="text-muted" style="font-size:11px;display:block;margin-top:4px;">
                                <?php echo __('Rendered using tag:'); ?> <code>{{paid_stamp}}</code>
                            </small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('METER ROWS TOP HTML (ALL)'); ?></label>
                        <div class="col-sm-10">
                            <textarea name="data[BillingTemplate][meter_top_html]" rows="3" class="form-control" style="font-family:monospace;font-size:12px;"><?php echo !empty($template['BillingTemplate']['meter_top_html']) ? h($template['BillingTemplate']['meter_top_html']) : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('METER ROWS MIDDLE HTML'); ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="data[BillingTemplate][meter_mid_html]" value="<?php echo !empty($template['BillingTemplate']['meter_mid_html']) ? h($template['BillingTemplate']['meter_mid_html']) : ''; ?>" class="form-control" placeholder="Custom row separator or utility sub-headers" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('METER ROWS BOTTOM HTML (ALL)'); ?></label>
                        <div class="col-sm-10">
                            <textarea name="data[BillingTemplate][meter_bot_html]" rows="3" class="form-control" style="font-family:monospace;font-size:12px;"><?php echo !empty($template['BillingTemplate']['meter_bot_html']) ? h($template['BillingTemplate']['meter_bot_html']) : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('NOTES / TERMS (URDU / ENGLISH)'); ?></label>
                        <div class="col-sm-10">
                            <textarea name="data[BillingTemplate][terms_notes]" rows="3" class="form-control" style="font-size:13px;"><?php echo !empty($template['BillingTemplate']['terms_notes']) ? h($template['BillingTemplate']['terms_notes']) : '1. بل کی مقررہ تاریخ کے بعد ادائیگی کی صورت میں لیٹ فیس لاگو ہوگی۔\n2. بجلی کی ترسیل میں تعطل کی صورت میں متعلقہ ہیلپ لائن سے رابطہ کریں۔'; ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- CARD 3: TEMPLATE HTML CODE EDITOR (FULL)   -->
            <!-- ========================================== -->
            <div class="subm-editor-card">
                <div class="subm-editor-card-header">
                    <div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
                        <span><span class="fa fa-code"></span> <?php echo __('Template HTML'); ?></span>
                        <small style="font-weight:400;color:#64748b;font-size:12.5px;"><?php echo __('Click any placeholder pill below to automatically insert into code editor at cursor position'); ?></small>
                    </div>
                </div>
                <div class="subm-editor-card-body" style="padding:16px 20px;">
                    <?php echo $this->Form->input('html_content', array('type' => 'textarea', 'id' => 'templateHtmlEditor', 'class' => 'subm-code-editor', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- CARD 4: TEMPLATE PLACEHOLDERS              -->
            <!-- ========================================== -->
            <div class="subm-editor-card">
                <div class="subm-editor-card-header"><?php echo __('Template Placeholders (Click to Copy / Insert)'); ?></div>
                <div class="subm-editor-card-body" style="font-size:13px;">

                    <!-- 1. Society & Basic Info -->
                    <div style="margin-bottom:16px;">
                        <strong style="color:#0f172a;display:block;margin-bottom:8px;font-size:13px;"><?php echo __('1. Society & Consumer Details:'); ?></strong>
                        <span class="subm-tag-pill" data-tag="{{society_name}}">{{society_name}}</span>
                        <span class="subm-tag-pill" data-tag="{{society_logo}}">{{society_logo}}</span>
                        <span class="subm-tag-pill" data-tag="{{society_address}}">{{society_address}}</span>
                        <span class="subm-tag-pill" data-tag="{{society_phone}}">{{society_phone}}</span>
                        <span class="subm-tag-pill" data-tag="{{bill_no}}">{{bill_no}}</span>
                        <span class="subm-tag-pill" data-tag="{{billing_month}}">{{billing_month}}</span>
                        <span class="subm-tag-pill" data-tag="{{consumer_no}}">{{consumer_no}}</span>
                        <span class="subm-tag-pill" data-tag="{{property_no}}">{{property_no}}</span>
                        <span class="subm-tag-pill" data-tag="{{client_name}}">{{client_name}}</span>
                        <span class="subm-tag-pill" data-tag="{{client_phone}}">{{client_phone}}</span>
                        <span class="subm-tag-pill" data-tag="{{client_cnic}}">{{client_cnic}}</span>
                        <span class="subm-tag-pill" data-tag="{{issue_date}}">{{issue_date}}</span>
                        <span class="subm-tag-pill" data-tag="{{due_date}}">{{due_date}}</span>
                        <span class="subm-tag-pill" data-tag="{{validity_date}}">{{validity_date}}</span>
                    </div>

                    <!-- 2. Meter Readings -->
                    <div style="margin-bottom:16px;">
                        <strong style="color:#0f172a;display:block;margin-bottom:8px;font-size:13px;"><?php echo __('2. Meter & Consumption:'); ?></strong>
                        <span class="subm-tag-pill" data-tag="{{meter_no}}">{{meter_no}}</span>
                        <span class="subm-tag-pill" data-tag="{{meter_type}}">{{meter_type}}</span>
                        <span class="subm-tag-pill" data-tag="{{prev_reading}}">{{prev_reading}}</span>
                        <span class="subm-tag-pill" data-tag="{{curr_reading}}">{{curr_reading}}</span>
                        <span class="subm-tag-pill" data-tag="{{units_consumed}}">{{units_consumed}}</span>
                        <span class="subm-tag-pill" data-tag="{{unit_rate}}">{{unit_rate}}</span>
                        <span class="subm-tag-pill" data-tag="{{consumption_amount}}">{{consumption_amount}}</span>
                        <span class="subm-tag-pill" data-tag="{{meter_rows}}">{{meter_rows}}</span>
                    </div>

                    <!-- 3. Financials & Billing -->
                    <div style="margin-bottom:16px;">
                        <strong style="color:#0f172a;display:block;margin-bottom:8px;font-size:13px;"><?php echo __('3. Financials & Total Payable:'); ?></strong>
                        <span class="subm-tag-pill" data-tag="{{maintenance_charges}}">{{maintenance_charges}}</span>
                        <span class="subm-tag-pill" data-tag="{{extra_charges}}">{{extra_charges}}</span>
                        <span class="subm-tag-pill" data-tag="{{arrears_amount}}">{{arrears_amount}}</span>
                        <span class="subm-tag-pill" data-tag="{{late_fee}}">{{late_fee}}</span>
                        <span class="subm-tag-pill" data-tag="{{total_amount}}">{{total_amount}}</span>
                        <span class="subm-tag-pill" data-tag="{{payable_within_due}}">{{payable_within_due}}</span>
                        <span class="subm-tag-pill" data-tag="{{payable_after_due}}">{{payable_after_due}}</span>
                        <span class="subm-tag-pill" data-tag="{{paid_amount}}">{{paid_amount}}</span>
                        <span class="subm-tag-pill" data-tag="{{balance_amount}}">{{balance_amount}}</span>
                        <span class="subm-tag-pill" data-tag="{{payment_status}}">{{payment_status}}</span>
                        <span class="subm-tag-pill" data-tag="{{maintenance_breakdown_rows}}">{{maintenance_breakdown_rows}}</span>
                    </div>

                    <!-- 4. Bank, QR & Receipt -->
                    <div>
                        <strong style="color:#0f172a;display:block;margin-bottom:8px;font-size:13px;"><?php echo __('4. Payment Channels & QR Verification:'); ?></strong>
                        <span class="subm-tag-pill" data-tag="{{bank_details}}">{{bank_details}}</span>
                        <span class="subm-tag-pill" data-tag="{{qr_code}}">{{qr_code}}</span>
                        <span class="subm-tag-pill" data-tag="{{paid_stamp}}">{{paid_stamp}}</span>
                        <span class="subm-tag-pill" data-tag="{{online_payment_reference}}">{{online_payment_reference}}</span>
                    </div>

                </div>
            </div>

            <!-- Sticky Bottom Action Bar -->
            <div class="subm-editor-bottom-bar">
                <button type="submit" class="btn btn-primary btn-lg" style="font-weight:700;padding:12px 48px;font-size:15px;background:#0284c7;border-color:#0369a1;">
                    <span class="fa fa-save"></span> <?php echo __('Save Template'); ?>
                </button>
                <div style="display:flex;gap:12px;">
                    <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'print_bill', '?' => array('template_id' => $template['BillingTemplate']['id'], 'preview' => 1))); ?>" target="_blank" class="btn btn-info btn-lg" style="font-weight:600;font-size:14px;background:#0369a1;border-color:#0284c7;">
                        <span class="fa fa-eye"></span> <?php echo __('Preview Template'); ?>
                    </a>
                    <a href="<?php echo $this->Html->url(array('controller' => 'BillingSettings', 'action' => 'index', '?' => array('tab' => 'templates'))); ?>" class="btn btn-default btn-lg" style="font-weight:600;font-size:14px;">
                        <?php echo __('Cancel'); ?>
                    </a>
                </div>
            </div>

            <?php echo $this->Form->end(); ?>

        </div>
    </div>
</div>

<script type="text/javascript">
// Click Tag Pill to insert into Editor at cursor
jQuery(document).on('click', '.subm-tag-pill', function() {
    var tag = jQuery(this).data('tag');
    var editor = document.getElementById('templateHtmlEditor');
    if (!editor) return;

    var startPos = editor.selectionStart;
    var endPos = editor.selectionEnd;
    var currentText = editor.value;

    editor.value = currentText.substring(0, startPos) + tag + currentText.substring(endPos, currentText.length);
    editor.selectionStart = editor.selectionEnd = startPos + tag.length;
    editor.focus();
});
</script>
