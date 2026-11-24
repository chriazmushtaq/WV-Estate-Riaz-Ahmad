<?php
$dealOptions = isset($dealOptions) ? (array)$dealOptions : array();
$methodOptions = isset($methodOptions) ? (array)$methodOptions : array();
$deal = isset($deal) ? (array)$deal : array();
$dealId = isset($dealId) ? (int)$dealId : 0;
$totalPaid = isset($totalPaid) ? (float)$totalPaid : 0;
$appealMode = !empty($appealMode);
$appealRequestId = isset($appealRequestId) ? (int)$appealRequestId : 0;
$appealRequestNo = isset($appealRequestNo) ? (string)$appealRequestNo : '';
$existingDocs = isset($existingDocs) && is_array($existingDocs) ? $existingDocs : array();
$formUrl = $appealMode ? array('controller' => 'MyRefundrequests', 'action' => 'appeal', $appealRequestId) : array('controller' => 'MyRefundrequests', 'action' => 'apply', $dealId);
$selectedDealName = '';
if(!empty($deal['MyDeal']['id']))
{
    $selectedDealName = !empty($deal['MyDeal']['invoice_no']) ? $deal['MyDeal']['invoice_no'] : 'Deal #'.$deal['MyDeal']['id'];
    if(!empty($deal['Property']['name']))
    {
        $selectedDealName .= ' - '.$deal['Property']['name'];
    }
}
$refundDocs = array(
    'client_photo' => array('label' => __('Client Photo'), 'field' => 'client_photo_file', 'accept' => 'image/*', 'aspect' => '1'),
    'cnic_front' => array('label' => __('CNIC Front'), 'field' => 'cnic_front_file', 'accept' => 'image/*', 'aspect' => '1.58'),
    'cnic_back' => array('label' => __('CNIC Back'), 'field' => 'cnic_back_file', 'accept' => 'image/*', 'aspect' => '1.58'),
    'file_front' => array('label' => __('File Front'), 'field' => 'file_front_file', 'accept' => 'image/*,application/pdf', 'aspect' => '1.58'),
    'file_back' => array('label' => __('File Back'), 'field' => 'file_back_file', 'accept' => 'image/*,application/pdf', 'aspect' => '1.58'),
    'client_signature' => array('label' => __('Client Signature'), 'field' => 'client_signature_file', 'accept' => 'image/*', 'aspect' => '3')
);
echo $this->Html->css('https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css');
echo $this->Html->script('https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js');
?>
<style type="text/css">
.wv-refund-apply{background:#f4f7fb;margin:-10px -5px 0;padding:14px;}
.wv-refund-apply *{box-sizing:border-box;}
.wv-refund-apply-head{align-items:center;background:linear-gradient(135deg,#07152a 0%,#123252 72%,#047857 150%);border-radius:10px;color:#fff;display:flex;gap:12px;justify-content:space-between;margin-bottom:12px;padding:17px 18px;}
.wv-refund-apply-head h1{color:#fff!important;font-size:25px;font-weight:900;margin:0 0 4px;}
.wv-refund-apply-head p{color:#e6f2ff!important;font-size:12px;font-weight:800;margin:0;}
.wv-refund-apply-card{background:#fff;border:1px solid #dfe9f5;border-radius:10px;box-shadow:0 8px 20px rgba(15,23,42,.07);padding:14px;}
.wv-refund-summary{background:#f8fbff;border:1px solid #dfe9f5;border-radius:10px;margin-bottom:12px;padding:12px;}
.wv-refund-summary strong{color:#07152a;font-weight:900;}
.wv-refund-steps{display:grid;gap:8px;grid-template-columns:repeat(3,minmax(0,1fr));margin-bottom:14px;}
.wv-refund-step-tab{align-items:center;background:#f8fbff;border:1px solid #d8e4f1;border-radius:8px;color:#334155;display:flex;gap:8px;padding:10px;text-align:left;width:100%;}
.wv-refund-step-tab span{align-items:center;background:#dbeafe;border-radius:999px;color:#075985;display:inline-flex;font-size:12px;font-weight:900;height:26px;justify-content:center;min-width:26px;}
.wv-refund-step-tab strong{display:block;font-size:12px;font-weight:900;line-height:1.15;text-transform:uppercase;}
.wv-refund-step-tab small{color:#64748b;display:block;font-size:10px;font-weight:800;margin-top:2px;}
.wv-refund-step-tab.active{background:#07152a;border-color:#07152a;color:#fff;}
.wv-refund-step-tab.active span{background:#10b981;color:#fff;}
.wv-refund-step-tab.active small{color:#dbeafe;}
.wv-refund-step-panel{display:none;}
.wv-refund-step-panel.active{display:block;}
.wv-refund-section-title{border-bottom:1px solid #e2eaf4;color:#07152a;font-size:14px;font-weight:900;margin:6px 0 12px;padding-bottom:8px;text-transform:uppercase;}
.wv-refund-form-grid{display:grid;gap:12px;grid-template-columns:repeat(3,minmax(0,1fr));}
.wv-refund-field{min-width:0;}
.wv-refund-field.full{grid-column:1/-1;}
.wv-refund-field.double{grid-column:span 2;}
.wv-refund-field label{color:#334155;display:block;font-size:11px;font-weight:900;margin:0 0 5px;text-transform:uppercase;}
.wv-required-star{color:#dc2626;display:inline-block;font-size:14px;font-weight:900;line-height:1;margin-left:4px;vertical-align:middle;}
.wv-required-star.is-filled{color:#16a34a;}
.wv-refund-field .form-control{border:1px solid #cbd8ea;border-radius:8px;box-shadow:none;color:#07152a;font-weight:800;min-height:38px;}
.wv-refund-field textarea.form-control{min-height:88px;resize:vertical;}
.wv-refund-doc-grid{display:grid;gap:12px;grid-template-columns:repeat(3,minmax(0,1fr));}
.wv-refund-doc{background:#f8fbff;border:1px solid #dbe7f3;border-radius:10px;padding:10px;}
.wv-refund-doc h3{color:#07152a;font-size:13px;font-weight:900;margin:0 0 8px;text-transform:uppercase;}
.wv-refund-drop{background:#fff;border:2px dashed #b9c9da;border-radius:9px;cursor:pointer;min-height:160px;padding:9px;position:relative;transition:all .18s ease;}
.wv-refund-drop:hover,.wv-refund-drop.drag-over{background:#eef6ff;border-color:#0ea5e9;}
.wv-refund-drop input[type=file]{left:-9999px;position:absolute;top:-9999px;}
.wv-refund-drop-preview{align-items:center;background:#edf3fb;border-radius:8px;color:#64748b;display:flex;font-size:12px;font-weight:900;justify-content:center;min-height:150px;overflow:hidden;padding:8px;position:relative;text-align:center;}
.wv-refund-drop-preview img{border:1px solid #d4dde8;border-radius:6px;height:auto;max-height:260px;max-width:100%;object-fit:contain;width:auto;}
.wv-refund-drop-preview .fa{display:block;font-size:28px;margin-bottom:7px;}
.wv-refund-drop-message{color:#64748b;font-size:11px;font-weight:800;margin-top:8px;text-align:center;}
.wv-refund-drop-name{color:#0f766e;display:none;font-size:11px;font-weight:900;margin-top:7px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.wv-refund-doc.has-file .wv-refund-drop-name{display:block;}
.wv-refund-existing-doc{background:#ecfdf5;border:1px solid #b7ead0;border-radius:8px;color:#065f46;font-size:11px;font-weight:900;line-height:1.35;margin-top:8px;padding:8px;}
.wv-refund-existing-doc a{color:#075985;font-weight:900;margin-left:6px;}
.wv-refund-existing-doc small{color:#047857;display:block;font-size:10px;font-weight:800;margin-top:3px;}
.wv-refund-crop-tools{display:none;gap:8px;grid-template-columns:1fr;margin-top:8px;}
.wv-refund-doc.has-image .wv-refund-crop-tools{display:grid;}
.wv-refund-crop-tools label{color:#475569;font-size:10px;font-weight:900;margin:0;text-transform:uppercase;}
.wv-refund-crop-tools input{width:100%;}
.wv-refund-crop-actions{display:grid;gap:6px;grid-column:1/-1;grid-template-columns:1fr;}
.wv-refund-crop-actions .btn{border-radius:7px;font-size:10px;font-weight:900;padding:7px 6px;text-transform:uppercase;}
.wv-refund-crop-status{color:#64748b;font-size:10px;font-weight:900;grid-column:1/-1;line-height:1.35;min-height:15px;text-align:center;text-transform:uppercase;}
.wv-refund-crop-status.ok{color:#08753b;}
.wv-refund-crop-status.warn{color:#8a6100;}
#refundCropModal .modal-dialog{max-width:96%;width:920px;}
#refundCropModal .modal-content{border:0;border-radius:10px;overflow:hidden;}
#refundCropModal .modal-header{background:#07152a;border:0;color:#fff;padding:12px 15px;}
#refundCropModal .modal-title{color:#fff;font-size:15px;font-weight:900;margin:0;text-transform:uppercase;}
#refundCropModal .close{color:#fff;opacity:.9;text-shadow:none;}
#refundCropModal .modal-body{background:#f7fafc;padding:12px;}
#refundCropModal .refund-crop-wrap{align-items:center;background:#111;border-radius:8px;display:flex;justify-content:center;max-height:68vh;min-height:360px;overflow:hidden;width:100%;}
#refundCropModal .refund-crop-wrap img{display:block;max-height:66vh;max-width:100%;}
#refundCropModal .modal-footer{align-items:center;background:#fff;border-top:1px solid #dbe7f3;display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;padding:10px 12px;}
#refundCropModal .modal-footer .btn{border-radius:8px;font-size:12px;font-weight:900;text-transform:uppercase;}
#refundCropModal.refund-crop-fallback{background:rgba(7,21,42,.72);display:none;inset:0;overflow:auto;position:fixed;z-index:99999;}
#refundCropModal.refund-crop-fallback.in{display:block;}
.wv-refund-match-box{background:#f8fbff;border:1px solid #dbe7f3;border-radius:10px;margin-bottom:12px;padding:12px;}
.wv-refund-match-row{align-items:end;display:grid;gap:10px;grid-template-columns:1fr 1fr 180px;}
.wv-refund-match-state{border-radius:8px;font-size:12px;font-weight:900;min-height:38px;padding:10px;text-align:center;text-transform:uppercase;}
.wv-refund-match-state.waiting{background:#eef2f7;color:#475569;}
.wv-refund-match-state.scanning{background:#e7f0ff;color:#174ea6;}
.wv-refund-match-state.ok{background:#dff7e8;color:#08753b;}
.wv-refund-match-state.bad{background:#ffe4e6;color:#b91c1c;}
.wv-refund-actions{display:flex;flex-wrap:wrap;gap:8px;justify-content:space-between;margin-top:14px;}
.wv-refund-actions .btn{border-radius:8px;font-size:12px;font-weight:900;padding:9px 13px;text-transform:uppercase;}
.wv-refund-actions-right{display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;}
.wv-refund-note-mini{color:#64748b;font-size:11px;font-weight:800;line-height:1.5;margin:8px 0 0;}
@media(max-width:991px){.wv-refund-form-grid,.wv-refund-doc-grid,.wv-refund-match-row{grid-template-columns:1fr}.wv-refund-field.double{grid-column:1/-1}.wv-refund-apply-head{display:block}.wv-refund-apply-head .btn{margin-top:10px;width:100%;}.wv-refund-steps{grid-template-columns:1fr;}}
@media(max-width:520px){.wv-refund-apply{margin:0;padding:10px}.wv-refund-actions,.wv-refund-actions-right{display:block}.wv-refund-actions .btn{margin-top:7px;width:100%;}}
</style>

<div class="wv-refund-apply">
    <?php echo $this->Session->flash();?>
    <div class="wv-refund-apply-head">
        <div>
            <h1><?php echo $appealMode ? __('Appeal Refund Request') : __('Apply Refund');?></h1>
            <p><?php echo $appealMode ? __('Edit your rejected application, keep existing documents, or replace any document before resubmitting.') : __('Complete the required steps and upload all refund verification documents.');?></p>
        </div>
        <?php echo $this->Html->link('<span class="fa fa-list"></span> '.__('My Requests'), array('controller' => 'MyRefundrequests', 'action' => 'index'), array('class' => 'btn btn-info', 'escape' => false));?>
    </div>

    <div class="wv-refund-apply-card">
        <?php if(empty($dealOptions)){ ?>
            <div class="alert alert-warning"><?php echo __('No eligible deals are available for refund request. If a request is already in process, you can apply again after it is rejected or cancelled.');?></div>
        <?php } else { ?>
            <?php if($selectedDealName !== ''){ ?>
                <div class="wv-refund-summary">
                    <strong><?php echo __('Selected Deal');?>:</strong> <?php echo h($selectedDealName);?>
                    <br><strong><?php echo __('Paid Amount');?>:</strong> <?php echo h($currency.' '.$this->Number->format($totalPaid));?>
                    <br><strong><?php echo __('Refund Request No');?>:</strong> <?php echo $appealMode ? h($appealRequestNo) : __('Auto generated after submit');?>
                </div>
            <?php } ?>

            <?php echo $this->Form->create('Dealrefundrequest', array('id' => 'wvRefundApplyForm', 'type' => 'file', 'url' => $formUrl));?>

            <div class="wv-refund-steps">
                <button type="button" class="wv-refund-step-tab active" data-step-target="1"><span>1</span><div><strong><?php echo __('Deal Details');?></strong><small><?php echo __('Refund and applicant');?></small></div></button>
                <button type="button" class="wv-refund-step-tab" data-step-target="2"><span>2</span><div><strong><?php echo __('Documents');?></strong><small><?php echo __('Required uploads');?></small></div></button>
                <button type="button" class="wv-refund-step-tab" data-step-target="3"><span>3</span><div><strong><?php echo __('Payment');?></strong><small><?php echo __('Review and submit');?></small></div></button>
            </div>

            <div class="wv-refund-step-panel active" data-step="1">
                <div class="wv-refund-section-title"><?php echo __('Deal & Refund');?></div>
                <div class="wv-refund-form-grid">
                    <div class="wv-refund-field double">
                        <label><?php echo __('Deal');?> <span class="wv-required-star">*</span></label>
                        <?php echo $this->Form->select('deal_id', $dealOptions, array('empty' => __('Please Select'), 'value' => $dealId > 0 ? $dealId : null, 'class' => 'form-control js-step-required js-required-check', 'disabled' => $appealMode ? 'disabled' : false));?>
                        <?php if($appealMode){ echo $this->Form->hidden('deal_id', array('value' => $dealId)); } ?>
                    </div>
                    <div class="wv-refund-field">
                        <label><?php echo __('Deal Paid Amount');?></label>
                        <?php echo $this->Form->text('requested_amount', array('class' => 'form-control', 'placeholder' => __('Auto calculated'), 'readonly' => 'readonly', 'value' => $dealId > 0 ? number_format($totalPaid, 2, '.', '') : null));?>
                    </div>
                    <div class="wv-refund-field">
                        <label><?php echo __('Preferred Method');?></label>
                        <?php echo $this->Form->select('preferred_method', $methodOptions, array('class' => 'form-control', 'empty' => false));?>
                    </div>
                    <div class="wv-refund-field full">
                        <label><?php echo __('Reason');?> <span class="wv-required-star">*</span></label>
                        <?php echo $this->Form->textarea('reason', array('class' => 'form-control js-step-required js-required-check', 'rows' => 4, 'placeholder' => __('Write refund reason')));?>
                    </div>
                    <div class="wv-refund-field full">
                        <label><?php echo __('Client Notes');?></label>
                        <?php echo $this->Form->textarea('client_notes', array('class' => 'form-control', 'rows' => 3, 'placeholder' => __('Any additional details for admin')));?>
                    </div>
                </div>

                <div class="wv-refund-section-title"><?php echo __('Applicant Details');?></div>
                <div class="wv-refund-form-grid">
                    <div class="wv-refund-field">
                        <label><?php echo __('Applicant Name');?></label>
                        <?php echo $this->Form->text('applicant_name', array('class' => 'form-control'));?>
                    </div>
                    <div class="wv-refund-field">
                        <label><?php echo __('Father Name');?></label>
                        <?php echo $this->Form->text('father_name', array('class' => 'form-control'));?>
                    </div>
                    <div class="wv-refund-field">
                        <label><?php echo __('CNIC');?> <span class="wv-required-star">*</span></label>
                        <?php echo $this->Form->text('cnic', array('id' => 'refundApplicantCnic', 'class' => 'form-control js-step-required js-required-check', 'placeholder' => '00000-0000000-0'));?>
                    </div>
                    <div class="wv-refund-field">
                        <label><?php echo __('Passport');?></label>
                        <?php echo $this->Form->text('passport_no', array('class' => 'form-control'));?>
                    </div>
                    <div class="wv-refund-field">
                        <label><?php echo __('Mobile');?></label>
                        <?php echo $this->Form->text('mobile_no', array('class' => 'form-control'));?>
                    </div>
                    <div class="wv-refund-field">
                        <label><?php echo __('Email');?></label>
                        <?php echo $this->Form->text('email', array('class' => 'form-control'));?>
                    </div>
                    <div class="wv-refund-field full">
                        <label><?php echo __('Address');?></label>
                        <?php echo $this->Form->textarea('address', array('class' => 'form-control', 'rows' => 3));?>
                    </div>
                </div>

                <div class="wv-refund-actions">
                    <?php echo $this->Html->link(__('Cancel'), array('controller' => 'MyRefundrequests', 'action' => 'index'), array('class' => 'btn btn-default'));?>
                    <div class="wv-refund-actions-right">
                        <button type="button" class="btn btn-primary js-refund-next" data-next-step="2"><span class="fa fa-arrow-right"></span> <?php echo __('Next Documents');?></button>
                    </div>
                </div>
            </div>

            <div class="wv-refund-step-panel" data-step="2">
                <div class="wv-refund-section-title"><?php echo __('CNIC Match Verification');?></div>
                <div class="wv-refund-match-box">
                    <div class="wv-refund-match-row">
                        <div class="wv-refund-field">
                            <label><?php echo __('Applicant CNIC');?></label>
                            <input type="text" class="form-control" id="refundApplicantCnicMirror" readonly="readonly">
                        </div>
                        <div class="wv-refund-field">
                            <label><?php echo __('CNIC Read From Front Image');?> <span class="wv-required-star">*</span></label>
                            <?php echo $this->Form->text('cnic_front_number', array('id' => 'refundCnicFrontNumber', 'class' => 'form-control js-required-check', 'placeholder' => 'Auto scan or enter CNIC'));?>
                        </div>
                        <div id="refundCnicMatchState" class="wv-refund-match-state waiting"><?php echo __('Waiting');?></div>
                    </div>
                    <p class="wv-refund-note-mini"><?php echo __('Upload CNIC Front first. The page will try to scan the CNIC number automatically; if the scan cannot read it clearly, enter the visible CNIC number manually.');?></p>
                </div>

                <div class="wv-refund-section-title"><?php echo __('Required Documents');?></div>
                <div class="wv-refund-doc-grid">
                    <?php foreach($refundDocs as $docKey => $doc){
                        $inputId = 'refund_doc_'.$docKey;
                        $existingDoc = isset($existingDocs[$docKey]) ? (array)$existingDocs[$docKey] : array();
                        $hasExistingDoc = !empty($existingDoc['file']);
                    ?>
                        <div class="wv-refund-doc<?php echo $hasExistingDoc ? ' has-existing' : ''; ?>" data-doc-key="<?php echo h($docKey);?>" data-aspect="<?php echo h($doc['aspect']);?>" data-existing-file="<?php echo $hasExistingDoc ? '1' : '0'; ?>">
                            <h3><?php echo h($doc['label']);?> <span class="wv-required-star">*</span></h3>
                            <div class="wv-refund-drop" data-input-id="<?php echo h($inputId);?>">
                                <?php echo $this->Form->file($doc['field'], array('id' => $inputId, 'class' => 'wv-refund-doc-input', 'accept' => $doc['accept'], 'data-doc-key' => $docKey));?>
                                <div class="wv-refund-drop-preview"><div><span class="fa fa-cloud-upload"></span><?php echo __('Drag & drop or browse');?></div></div>
                                <div class="wv-refund-drop-message"><?php echo __('Required');?>: <?php echo h($doc['label']);?></div>
                                <div class="wv-refund-drop-name"></div>
                            </div>
                            <?php if($hasExistingDoc){ ?>
                                <div class="wv-refund-existing-doc">
                                    <?php echo __('Current');?>: <?php echo h($existingDoc['file']);?>
                                    <?php echo $this->Html->link(__('Download'), array('controller' => 'MyRefundrequests', 'action' => 'download', $appealRequestId, $docKey));?>
                                    <small><?php echo __('Upload a new file only if you want to replace this document.');?></small>
                                </div>
                            <?php } ?>
                            <div class="wv-refund-crop-tools">
                                <div class="wv-refund-crop-actions">
                                    <button type="button" class="btn btn-success js-refund-open-crop"><span class="fa fa-crop"></span> <?php echo __('Open Crop Window');?></button>
                                </div>
                                <div class="wv-refund-crop-status"></div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="wv-refund-actions">
                    <button type="button" class="btn btn-default js-refund-prev" data-prev-step="1"><span class="fa fa-arrow-left"></span> <?php echo __('Back');?></button>
                    <div class="wv-refund-actions-right">
                        <button type="button" class="btn btn-primary js-refund-next" data-next-step="3"><span class="fa fa-arrow-right"></span> <?php echo __('Next Payment');?></button>
                    </div>
                </div>
            </div>

            <div class="wv-refund-step-panel" data-step="3">
                <div class="wv-refund-section-title"><?php echo __('Payment Details');?></div>
                <div class="wv-refund-form-grid">
                    <div class="wv-refund-field">
                        <label><?php echo __('Account Title');?></label>
                        <?php echo $this->Form->text('account_title', array('class' => 'form-control'));?>
                    </div>
                    <div class="wv-refund-field">
                        <label><?php echo __('Account No');?></label>
                        <?php echo $this->Form->text('account_no', array('class' => 'form-control'));?>
                    </div>
                    <div class="wv-refund-field">
                        <label><?php echo __('IBAN');?></label>
                        <?php echo $this->Form->text('iban', array('class' => 'form-control'));?>
                    </div>
                    <div class="wv-refund-field">
                        <label><?php echo __('Bank Name');?></label>
                        <?php echo $this->Form->text('bank_name', array('class' => 'form-control'));?>
                    </div>
                    <div class="wv-refund-field">
                        <label><?php echo __('Branch / City');?></label>
                        <?php echo $this->Form->text('bank_branch_city', array('class' => 'form-control'));?>
                    </div>
                    <div class="wv-refund-field">
                        <label><?php echo __('Wallet Name');?></label>
                        <?php echo $this->Form->text('wallet_name', array('class' => 'form-control', 'placeholder' => __('Optional')));?>
                    </div>
                    <div class="wv-refund-field">
                        <label><?php echo __('Wallet No');?></label>
                        <?php echo $this->Form->text('wallet_no', array('class' => 'form-control', 'placeholder' => __('Optional')));?>
                    </div>
                </div>

                <div class="wv-refund-actions">
                    <button type="button" class="btn btn-default js-refund-prev" data-prev-step="2"><span class="fa fa-arrow-left"></span> <?php echo __('Back');?></button>
                    <div class="wv-refund-actions-right">
                        <button type="submit" class="btn btn-success"><span class="fa fa-send"></span> <?php echo $appealMode ? __('Submit Appeal') : __('Submit Request');?></button>
                    </div>
                </div>
            </div>
            <?php echo $this->Form->end();?>
        <?php } ?>
    </div>
</div>

<div class="modal fade" id="refundCropModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo h(__('Close'));?>"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo __('Crop Document Image');?></h4>
            </div>
            <div class="modal-body">
                <div class="refund-crop-wrap">
                    <img id="refundCropImage" src="" alt="<?php echo h(__('Crop Document Image'));?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="refundCropRotateLeft"><span class="fa fa-rotate-left"></span> <?php echo __('Rotate Left');?></button>
                <button type="button" class="btn btn-default" id="refundCropRotateRight"><span class="fa fa-rotate-right"></span> <?php echo __('Rotate Right');?></button>
                <button type="button" class="btn btn-default" id="refundCropReset"><span class="fa fa-refresh"></span> <?php echo __('Reset');?></button>
                <button type="button" class="btn btn-warning" id="refundCropOriginal"><?php echo __('Use Original');?></button>
                <button type="button" class="btn btn-success" id="refundCropApply"><span class="fa fa-check"></span> <?php echo __('Apply Crop');?></button>
            </div>
        </div>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script type="text/javascript">
(function($){
    var currentStep = 1;
    var refundSubmitReady = false;
    var refundCropState = {
        cropper: null,
        input: null,
        card: null,
        file: null,
        originalName: ''
    };

    function normalizeCnic(value){
        return String(value || '').replace(/\D+/g, '');
    }

    function formatCnic(value){
        var digits = normalizeCnic(value).substr(0, 13);
        if(digits.length === 13){
            return digits.substr(0, 5) + '-' + digits.substr(5, 7) + '-' + digits.substr(12, 1);
        }
        return digits;
    }

    function updateCnicMatch(stateText){
        var applicant = $('#refundApplicantCnic').val();
        var scanned = $('#refundCnicFrontNumber').val();
        $('#refundApplicantCnicMirror').val(applicant);
        var applicantDigits = normalizeCnic(applicant);
        var scannedDigits = normalizeCnic(scanned);
        var $state = $('#refundCnicMatchState');
        $state.removeClass('waiting scanning ok bad');
        if(stateText){
            $state.addClass('scanning').text(stateText);
            return false;
        }
        if(!applicantDigits || !scannedDigits){
            $state.addClass('waiting').text('<?php echo h(__('Waiting'));?>');
            return false;
        }
        if(applicantDigits === scannedDigits){
            $state.addClass('ok').text('<?php echo h(__('Matched'));?>');
            return true;
        }
        $state.addClass('bad').text('<?php echo h(__('Not Matched'));?>');
        return false;
    }

    function refreshRequiredMarkers(){
        $('.js-required-check').each(function(){
            var $field = $(this);
            var hasValue = $.trim(String($field.val() || '')) !== '';
            $field.closest('.wv-refund-field').find('label .wv-required-star').first().toggleClass('is-filled', hasValue);
        });
        $('.wv-refund-doc-input').each(function(){
            var $card = $(this).closest('.wv-refund-doc');
            var hasFile = !!(this.files && this.files.length);
            var hasExisting = String($card.attr('data-existing-file') || '') === '1';
            $card.find('h3 .wv-required-star').first().toggleClass('is-filled', hasFile || hasExisting);
        });
    }

    function showStep(step){
        currentStep = parseInt(step, 10);
        $('.wv-refund-step-panel').removeClass('active').filter('[data-step="' + currentStep + '"]').addClass('active');
        $('.wv-refund-step-tab').removeClass('active').filter('[data-step-target="' + currentStep + '"]').addClass('active');
        updateCnicMatch();
        refreshRequiredMarkers();
        $('html,body').animate({scrollTop: $('.wv-refund-apply-card').offset().top - 20}, 160);
    }

    function validateStepOne(){
        var ok = true;
        $('[data-step="1"] .js-step-required').each(function(){
            if($.trim($(this).val()) === ''){
                $(this).focus();
                ok = false;
                return false;
            }
        });
        if(!ok){
            alert('<?php echo h(__('Please complete required deal, reason, and CNIC fields.'));?>');
        }
        return ok;
    }

    function validateDocuments(){
        var ok = true;
        $('.wv-refund-doc-input').each(function(){
            var $card = $(this).closest('.wv-refund-doc');
            var hasExisting = String($card.attr('data-existing-file') || '') === '1';
            if((!this.files || !this.files.length) && !hasExisting){
                var title = $card.find('h3').text();
                alert(title + ' <?php echo h(__('is required.'));?>');
                this.focus();
                ok = false;
                return false;
            }
        });
        if(!ok){
            return false;
        }
        if(!updateCnicMatch()){
            alert('<?php echo h(__('CNIC front image number must match applicant CNIC.'));?>');
            $('#refundCnicFrontNumber').focus();
            return false;
        }
        return true;
    }

    function extractCnicFromText(text){
        var match = String(text || '').match(/\b\d{5}[-\s]?\d{7}[-\s]?\d\b/);
        if(match && match[0]){
            return formatCnic(match[0]);
        }
        var digits = normalizeCnic(text);
        if(digits.length >= 13){
            return formatCnic(digits.substr(0, 13));
        }
        return '';
    }

    function scanCnicFront(file){
        if(!file){
            return;
        }
        var filenameMatch = extractCnicFromText(file.name);
        if(filenameMatch){
            $('#refundCnicFrontNumber').val(filenameMatch);
            updateCnicMatch();
            return;
        }
        if(!window.Tesseract || !file.type || file.type.indexOf('image/') !== 0){
            updateCnicMatch();
            return;
        }
        updateCnicMatch('<?php echo h(__('Scanning'));?>');
        Tesseract.recognize(file, 'eng').then(function(result){
            var text = result && result.data ? result.data.text : '';
            var found = extractCnicFromText(text);
            if(found){
                $('#refundCnicFrontNumber').val(found);
            }
            updateCnicMatch();
        }).catch(function(){
            updateCnicMatch();
        });
    }

    function updateCropStatus($card, text, mode){
        var $status = $card.find('.wv-refund-crop-status');
        $status.removeClass('ok warn').text(text || '');
        if(mode){
            $status.addClass(mode);
        }
    }

    function isImageFile(file){
        if(!file){
            return false;
        }
        var name = file.name ? file.name.toLowerCase() : '';
        return (file.type && file.type.toLowerCase().indexOf('image/') === 0) || /\.(jpg|jpeg|png|webp)$/i.test(name);
    }

    function hideRefundCropModal(){
        var $modal = $('#refundCropModal');
        if($.fn.modal){
            $modal.modal('hide');
        }else{
            $modal.removeClass('in').hide();
            $('body').removeClass('modal-open');
            resetRefundCropper();
        }
    }

    function resetRefundCropper(){
        if(refundCropState.cropper){
            refundCropState.cropper.destroy();
            refundCropState.cropper = null;
        }
        refundCropState.input = null;
        refundCropState.card = null;
        refundCropState.file = null;
        refundCropState.originalName = '';
        $('#refundCropImage').attr('src', '');
    }

    function initRefundCropper(){
        var image = document.getElementById('refundCropImage');
        if(!image || !image.src || typeof Cropper === 'undefined'){
            return;
        }
        if(refundCropState.cropper){
            refundCropState.cropper.destroy();
            refundCropState.cropper = null;
        }
        refundCropState.cropper = new Cropper(image, {
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.96,
            aspectRatio: NaN,
            responsive: true,
            background: false,
            rotatable: true,
            zoomable: true
        });
    }

    function showRefundCropModal(){
        var $modal = $('#refundCropModal');
        if($.fn.modal){
            $modal.modal('show');
        }else{
            $modal.addClass('refund-crop-fallback in').show();
            $('body').addClass('modal-open');
            setTimeout(initRefundCropper, 60);
        }
    }

    function openRefundCropWindow($card, file){
        file = file || $card.data('refundCurrentFile');
        if(!isImageFile(file) || typeof Cropper === 'undefined'){
            return;
        }
        var $input = $card.find('.wv-refund-doc-input');
        var reader = new FileReader();
        reader.onload = function(e){
            refundCropState.input = $input;
            refundCropState.card = $card;
            refundCropState.file = file;
            refundCropState.originalName = file.name || ('refund_document_'+Date.now()+'.png');
            $('#refundCropImage').attr('src', e.target.result);
            showRefundCropModal();
        };
        reader.readAsDataURL(file);
    }

    function applyRefundCropFromModal(){
        if(!refundCropState.cropper || !refundCropState.input || !refundCropState.card){
            hideRefundCropModal();
            return;
        }
        var canvas = refundCropState.cropper.getCroppedCanvas({
            maxWidth: 2200,
            maxHeight: 2200,
            fillColor: '#fff'
        });
        if(!canvas){
            hideRefundCropModal();
            return;
        }
        canvas.toBlob(function(blob){
            if(!blob){
                hideRefundCropModal();
                return;
            }
            var originalName = refundCropState.originalName || ('refund_document_'+Date.now()+'.png');
            var croppedName = originalName.replace(/\.[^.]+$/, '') + '_cropped.png';
            var croppedFile;
            try{
                croppedFile = new File([blob], croppedName, {type: 'image/png', lastModified: Date.now()});
            }catch(err){
                croppedFile = blob;
                croppedFile.name = croppedName;
            }
            if(assignFileToInput(refundCropState.input, croppedFile)){
                renderFilePreview(refundCropState.card, croppedFile);
                refundCropState.card.data('refundCropApplied', true);
                updateCropStatus(refundCropState.card, '<?php echo h(__('Crop applied. Cropped image will upload.'));?>', 'ok');
                if(refundCropState.card.attr('data-doc-key') === 'cnic_front'){
                    scanCnicFront(croppedFile);
                }
            }
            hideRefundCropModal();
        }, 'image/png', 0.95);
    }

    function resetCropControls($card){
        $card.find('.js-refund-zoom').val('1');
        $card.find('.js-refund-move-x').val('0');
        $card.find('.js-refund-move-y').val('0');
        $card.find('.js-refund-rotate').val('0');
    }

    function cropValues($card){
        return {
            zoom: parseFloat($card.find('.js-refund-zoom').val() || 1),
            moveX: parseInt($card.find('.js-refund-move-x').val() || 0, 10),
            moveY: parseInt($card.find('.js-refund-move-y').val() || 0, 10),
            rotate: parseInt($card.find('.js-refund-rotate').val() || 0, 10)
        };
    }

    function isCropEdited($card){
        var values = cropValues($card);
        return values.zoom !== 1 || values.moveX !== 0 || values.moveY !== 0 || values.rotate !== 0;
    }

    function markCropPending($card){
        if($card.hasClass('has-image')){
            $card.data('refundCropApplied', false);
            updateCropStatus($card, '<?php echo h(__('Selected crop will apply automatically'));?>', 'warn');
        }
    }

    function dataUrlToBlob(dataUrl){
        var parts = dataUrl.split(',');
        var meta = parts[0].match(/:(.*?);/);
        var mime = meta && meta[1] ? meta[1] : 'image/jpeg';
        var binary = atob(parts[1]);
        var length = binary.length;
        var array = new Uint8Array(length);
        for(var i = 0; i < length; i++){
            array[i] = binary.charCodeAt(i);
        }
        return new Blob([array], {type: mime});
    }

    function canvasToBlob(canvas, callback){
        if(canvas.toBlob){
            canvas.toBlob(callback, 'image/jpeg', 0.9);
            return;
        }
        callback(dataUrlToBlob(canvas.toDataURL('image/jpeg', 0.9)));
    }

    function assignFileToInput($input, file){
        if(!window.DataTransfer || !window.File){
            return false;
        }
        try{
            var dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            $input[0].files = dataTransfer.files;
            return true;
        }catch(err){
            return false;
        }
    }

    function cropOutputSize($card){
        var aspect = parseFloat($card.attr('data-aspect') || 1);
        if(!aspect || aspect <= 0){
            aspect = 1;
        }
        var width = aspect >= 2.5 ? 1500 : (aspect >= 1.2 ? 1600 : 1000);
        var height = Math.round(width / aspect);
        if(height > 1200){
            height = 1200;
            width = Math.round(height * aspect);
        }
        return {width: width, height: height, aspect: aspect};
    }

    function replacePreviewWithCroppedImage($card, dataUrl, fileName){
        $card.find('.wv-refund-drop-preview').html('<img src="' + dataUrl + '" alt="Preview">');
        $card.find('.wv-refund-drop-name').text(fileName);
        $card.addClass('has-file has-image');
        applyPreviewCrop($card, $card.find('.wv-refund-drop-preview img'));
    }

    function applyRealCrop($card, silent){
        return new Promise(function(resolve, reject){
            if(!$card.hasClass('has-image')){
                resolve();
                return;
            }
            var dataUrl = $card.data('refundImageDataUrl');
            var $input = $card.find('.wv-refund-doc-input');
            if(!dataUrl || !$input.length){
                resolve();
                return;
            }
            updateCropStatus($card, '<?php echo h(__('Applying crop'));?>', 'warn');
            var image = new Image();
            image.onload = function(){
                var size = cropOutputSize($card);
                var values = cropValues($card);
                var canvas = document.createElement('canvas');
                canvas.width = size.width;
                canvas.height = size.height;
                var ctx = canvas.getContext('2d');
                var angle = values.rotate * Math.PI / 180;
                var sin = Math.abs(Math.sin(angle));
                var cos = Math.abs(Math.cos(angle));
                var rotatedWidth = (image.width * cos) + (image.height * sin);
                var rotatedHeight = (image.width * sin) + (image.height * cos);
                var baseScale = Math.max(size.width / rotatedWidth, size.height / rotatedHeight);
                var finalScale = baseScale * values.zoom;

                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, size.width, size.height);
                ctx.save();
                ctx.translate((size.width / 2) + ((values.moveX / 100) * size.width), (size.height / 2) + ((values.moveY / 100) * size.height));
                ctx.rotate(angle);
                ctx.scale(finalScale, finalScale);
                ctx.drawImage(image, -image.width / 2, -image.height / 2);
                ctx.restore();

                var croppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
                canvasToBlob(canvas, function(blob){
                    if(!blob){
                        reject(new Error('Crop failed'));
                        return;
                    }
                    var docKey = $card.attr('data-doc-key') || 'document';
                    var fileName = 'cropped_' + docKey + '_' + Date.now() + '.jpg';
                    var croppedFile;
                    try{
                        croppedFile = new File([blob], fileName, {type: 'image/jpeg', lastModified: Date.now()});
                    }catch(err){
                        reject(err);
                        return;
                    }
                    if(!assignFileToInput($input, croppedFile)){
                        reject(new Error('Browser cannot attach cropped file'));
                        return;
                    }
                    resetCropControls($card);
                    $card.data('refundImageDataUrl', croppedDataUrl);
                    $card.data('refundCropApplied', true);
                    replacePreviewWithCroppedImage($card, croppedDataUrl, fileName);
                    updateCropStatus($card, '<?php echo h(__('Crop applied'));?>', 'ok');
                    if(docKey === 'cnic_front' && !silent){
                        scanCnicFront(croppedFile);
                    }
                    resolve(croppedFile);
                });
            };
            image.onerror = function(){
                reject(new Error('Image could not be loaded'));
            };
            image.src = dataUrl;
        });
    }

    function applyAllPendingCrops(callback){
        callback();
    }

    function renderFilePreview($card, file){
        var $preview = $card.find('.wv-refund-drop-preview');
        var $name = $card.find('.wv-refund-drop-name');
        var $img;
        $card.addClass('has-file').removeClass('has-image');
        resetCropControls($card);
        $card.removeData('refundImageDataUrl');
        $card.removeData('refundCurrentFile');
        $card.data('refundCropApplied', true);
        updateCropStatus($card, '');
        $name.text(file ? file.name : '');
        if(!file){
            refreshRequiredMarkers();
            return;
        }
        $card.data('refundCurrentFile', file);
        refreshRequiredMarkers();
        if(isImageFile(file)){
            var reader = new FileReader();
            reader.onload = function(e){
                $card.data('refundImageDataUrl', e.target.result);
                $preview.html('<img src="' + e.target.result + '" alt="Preview">');
                $card.addClass('has-image');
                updateCropStatus($card, '<?php echo h(__('Full image selected. Crop window is optional.'));?>', 'ok');
            };
            reader.readAsDataURL(file);
        }else{
            $card.data('refundCropApplied', true);
            $preview.html('<div><span class="fa fa-file-pdf-o"></span><?php echo h(__('PDF Selected'));?></div>');
        }
    }

    function applyPreviewCrop($card, $img){
        $img = $img && $img.length ? $img : $card.find('.wv-refund-drop-preview img');
        if(!$img.length){
            return;
        }
        $img.css('transform', '');
    }

    function bindDropUploads(){
        $('.wv-refund-drop').each(function(){
            var $box = $(this);
            var inputId = $box.attr('data-input-id');
            var $input = $('#' + inputId);
            if(!$input.length){
                return;
            }
            $box.off('click.refundDrop').on('click.refundDrop', function(){
                $input[0].click();
            });
            $input.off('click.refundDrop').on('click.refundDrop', function(e){
                e.stopPropagation();
            });
            $input.off('change.refundDrop').on('change.refundDrop', function(){
                var file = this.files && this.files.length ? this.files[0] : null;
                var $card = $box.closest('.wv-refund-doc');
                renderFilePreview($card, file);
                if(isImageFile(file)){
                    openRefundCropWindow($card, file);
                }
                if($card.attr('data-doc-key') === 'cnic_front'){
                    scanCnicFront(file);
                }
            });
            $box.off('dragenter.refundDrop dragover.refundDrop').on('dragenter.refundDrop dragover.refundDrop', function(e){
                e.preventDefault();
                e.stopPropagation();
                $box.addClass('drag-over');
            });
            $box.off('dragleave.refundDrop drop.refundDrop').on('dragleave.refundDrop drop.refundDrop', function(e){
                e.preventDefault();
                e.stopPropagation();
                $box.removeClass('drag-over');
            });
            $box.off('drop.refundDrop').on('drop.refundDrop', function(e){
                e.preventDefault();
                e.stopPropagation();
                $box.removeClass('drag-over');
                var dt = e.originalEvent.dataTransfer;
                if(dt && dt.files && dt.files.length){
                    try{
                        $input[0].files = dt.files;
                    }catch(err){}
                    if($input[0].files && $input[0].files.length){
                        $input.trigger('change');
                    }else{
                        var $card = $box.closest('.wv-refund-doc');
                        renderFilePreview($card, dt.files[0]);
                        if(isImageFile(dt.files[0])){
                            openRefundCropWindow($card, dt.files[0]);
                        }
                    }
                }
            });
        });
    }

    $(document).on('click', '.wv-refund-step-tab', function(){
        var target = parseInt($(this).attr('data-step-target'), 10);
        if(target === currentStep){
            return;
        }
        if(target > 1 && !validateStepOne()){
            return;
        }
        if(target > 2){
            if(!validateDocuments()){
                return;
            }
            applyAllPendingCrops(function(){
                showStep(target);
            });
            return;
        }
        showStep(target);
    });

    $(document).on('click', '.js-refund-next', function(){
        var nextStep = parseInt($(this).attr('data-next-step'), 10);
        if(currentStep === 1 && !validateStepOne()){
            return;
        }
        if(currentStep === 2){
            if(!validateDocuments()){
                return;
            }
            applyAllPendingCrops(function(){
                showStep(nextStep);
            });
            return;
        }
        showStep(nextStep);
    });

    $(document).on('click', '.js-refund-prev', function(){
        showStep($(this).attr('data-prev-step'));
    });

    $(document).on('input change', '#refundApplicantCnic,#refundCnicFrontNumber', function(){
        if(this.id === 'refundApplicantCnic'){
            $('#refundApplicantCnicMirror').val($(this).val());
        }
        updateCnicMatch();
        refreshRequiredMarkers();
    });

    $(document).on('input change', '.js-required-check', function(){
        refreshRequiredMarkers();
    });

    $('#refundCropModal').off('shown.bs.modal.refundcrop').on('shown.bs.modal.refundcrop', function(){
        initRefundCropper();
    }).off('hidden.bs.modal.refundcrop').on('hidden.bs.modal.refundcrop', function(){
        resetRefundCropper();
    });

    $(document).on('click', '#refundCropModal .close,#refundCropOriginal', function(e){
        e.preventDefault();
        hideRefundCropModal();
    });

    $(document).on('click', '#refundCropRotateLeft', function(){
        if(refundCropState.cropper){
            refundCropState.cropper.rotate(-90);
        }
    });

    $(document).on('click', '#refundCropRotateRight', function(){
        if(refundCropState.cropper){
            refundCropState.cropper.rotate(90);
        }
    });

    $(document).on('click', '#refundCropReset', function(){
        if(refundCropState.cropper){
            refundCropState.cropper.reset();
        }
    });

    $(document).on('click', '#refundCropApply', function(){
        applyRefundCropFromModal();
    });

    $(document).on('click', '.js-refund-open-crop', function(e){
        e.preventDefault();
        e.stopPropagation();
        var $card = $(this).closest('.wv-refund-doc');
        openRefundCropWindow($card, $card.data('refundCurrentFile'));
    });

    $(document).on('input change', '.js-refund-zoom,.js-refund-move-x,.js-refund-move-y,.js-refund-rotate', function(){
        var $card = $(this).closest('.wv-refund-doc');
        applyPreviewCrop($card);
        markCropPending($card);
    });

    $(document).on('click', '.js-refund-rotate-left,.js-refund-rotate-right', function(){
        var $card = $(this).closest('.wv-refund-doc');
        var $rotate = $card.find('.js-refund-rotate');
        var delta = $(this).hasClass('js-refund-rotate-left') ? -90 : 90;
        var nextValue = parseInt($rotate.val() || 0, 10) + delta;
        if(nextValue > 180){ nextValue -= 360; }
        if(nextValue < -180){ nextValue += 360; }
        $rotate.val(nextValue);
        applyPreviewCrop($card);
        markCropPending($card);
    });

    $(document).on('click', '.js-refund-apply-crop', function(){
        var $card = $(this).closest('.wv-refund-doc');
        applyRealCrop($card, false).catch(function(){
            alert('<?php echo h(__('Unable to apply crop in this browser. Please try another image or browser.'));?>');
        });
    });

    $('#wvRefundApplyForm').on('submit', function(e){
        if(refundSubmitReady){
            return true;
        }
        if(!validateStepOne()){
            e.preventDefault();
            showStep(1);
            return false;
        }
        if(!validateDocuments()){
            e.preventDefault();
            showStep(2);
            return false;
        }
        e.preventDefault();
        applyAllPendingCrops(function(){
            refundSubmitReady = true;
            document.getElementById('wvRefundApplyForm').submit();
        });
        return false;
    });

    $(function(){
        bindDropUploads();
        updateCnicMatch();
        refreshRequiredMarkers();
    });
})(jQuery);
</script>
