<script type="text/javascript">
var invpaidHtml2PdfLoader = null;
    $(document).ready(function(){        
        $('#start_date').datetimepicker({locale:'<?php echo$configLanguage;?>',format:'<?php echo $dpFormat;?>',widgetPositioning:{horizontal:'auto',vertical:'bottom'}});
        $('#end_date').datetimepicker({locale:'<?php echo$configLanguage;?>',format:'<?php echo $dpFormat;?>',widgetPositioning:{horizontal:'auto',vertical:'bottom'},useCurrent: false //Important! See issue #1075
        });
        $("#start_date").on("dp.change", function (e) {
            $('#end_date').data("DateTimePicker").minDate(e.date);
        });
        $("#end_date").on("dp.change", function (e) {
            $('#start_date').data("DateTimePicker").maxDate(e.date);
        });
        $('.invpaid_action').tooltip();
});
function ensureInvpaidHtml2Pdf()
{
    if(typeof html2pdf!=='undefined')
    {
        return jQuery.Deferred().resolve().promise();
    }
    if(invpaidHtml2PdfLoader)
    {
        return invpaidHtml2PdfLoader.promise();
    }
    invpaidHtml2PdfLoader = jQuery.Deferred();
    var script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
    script.onload = function(){invpaidHtml2PdfLoader.resolve();};
    script.onerror = function(){
        alert('Unable to load PDF library.');
        invpaidHtml2PdfLoader.reject();
        invpaidHtml2PdfLoader = null;
    };
    document.getElementsByTagName('head')[0].appendChild(script);
    return invpaidHtml2PdfLoader.promise();
}
function invpaidPdfStamp()
{
    var now = new Date();
    var pad = function(n){ return n<10 ? '0'+n : n; };
    return now.getFullYear()+''+pad(now.getMonth()+1)+''+pad(now.getDate())+'_'+pad(now.getHours())+''+pad(now.getMinutes())+''+pad(now.getSeconds());
}
function exportInvpaidRecordsPdf()
{
    ensureInvpaidHtml2Pdf().done(function(){
        var sourceElement = document.getElementById('printable_content');
        if(!sourceElement)
        {
            alert('No records found for PDF export.');
            return;
        }
        var $sourceClone = jQuery(sourceElement).clone();
        $sourceClone.find('.pbutton').css('display','none');
        $sourceClone.find('.invoice-report-brand-header').css('display','table-row');
        var $wrapper = jQuery('<div id="invpaid-pdf-export-wrapper"></div>').css({
            position: 'fixed',
            left: '0',
            top: '0',
            width: '1120px',
            background: '#fff',
            padding: '5px',
            pointerEvents: 'none',
            zIndex: '99999'
        });
        $wrapper.addClass('invpaid-pdf-mode');
        $wrapper.append($sourceClone);
        jQuery('body').append($wrapper);
        var opt = {
            margin:       [0.20,0.20,0.20,0.20],
            filename:     'paid_invoices_'+invpaidPdfStamp()+'.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, backgroundColor: '#ffffff', scrollX: 0, scrollY: 0 },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };
        var cleanup = function(){ $wrapper.remove(); };
        window.setTimeout(function(){
            var worker = html2pdf().set(opt).from($wrapper[0]).save();
            if(worker && typeof worker.then==='function')
            {
                worker.then(function(){cleanup();}).catch(function(){cleanup();});
            }
            else
            {
                cleanup();
            }
        }, 150);
    });
    return false;
}
</script>
<style type="text/css">
.invpaid-page-shell {
    background: #f5f7fb;
    border: 1px solid #d8e2ee;
    border-radius: 8px;
    overflow: visible;
    padding: 14px;
}
.invpaid-page-panel,
.panel-heading.invpaid-toolbar-card,
.invpaid-filter-row,
.invpaid-filter-row > div {
    overflow: visible;
}
.bootstrap-datetimepicker-widget {
    z-index: 200000 !important;
}
.invpaid-page-panel {
    border: 0;
    background: transparent;
    box-shadow: none;
}
.invpaid-toolbar-card {
    align-items: center;
    background: linear-gradient(110deg,#0f172a,#312e81) !important;
    border: 1px solid #d8e2ee !important;
    border-radius: 8px;
    color: #ffffff !important;
    display: flex;
    flex-wrap: nowrap;
    gap: 16px;
    overflow: visible;
    padding: 14px;
    margin-bottom: 14px;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
}
.invpaid-toolbar-card form {
    flex: 1 1 auto;
    margin: 0;
    min-width: 0;
}
.invpaid-toolbar-card .btn-group {
    align-items: center;
    display: flex;
    flex: 0 0 auto;
    flex-wrap: nowrap;
    gap: 8px;
    white-space: nowrap;
}
.invpaid-toolbar-card .btn {
    border-radius: 6px;
    border: 0 !important;
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.10);
    float: none;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    padding: 10px 14px;
    text-shadow: none;
}
.invpaid-toolbar-card .btn-default {
    background: #1d4ed8 !important;
    color: #ffffff !important;
    min-width: 92px;
    text-align: center;
}
.invpaid-toolbar-card .btn-info {
    background: #1d4ed8 !important;
    color: #ffffff !important;
}
.invpaid-toolbar-card .btn-success {
    background: #312e81 !important;
    color: #ffffff !important;
}
.invpaid-toolbar-card .btn-warning {
    background: #f59e0b !important;
    color: #07152a !important;
}
.invpaid-toolbar-card .invpaid-print-button {
    background: #1d4ed8 !important;
    color: #ffffff !important;
    display: inline-block !important;
    min-width: 92px;
    opacity: 1 !important;
    text-align: center;
    visibility: visible !important;
}
.invpaid-toolbar-card .btn .fa {
    color: inherit !important;
}
.invpaid-toolbar-card #printme,
.invpaid-toolbar-card #printme span,
.invpaid-toolbar-card #printme .fa {
    color: #ffffff !important;
    opacity: 1 !important;
    visibility: visible !important;
}
.invpaid-filter-row {
    align-items: center;
    display: flex;
    flex-wrap: nowrap;
    gap: 8px;
    margin: 0 !important;
    min-width: max-content;
    padding-top: 0;
    border-top: 0;
    white-space: nowrap;
}
.invpaid-filter-label {
    color: #ffffff !important;
    display: block;
    float: none !important;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .03em;
    line-height: 38px;
    margin: 0;
    padding: 0 !important;
    text-transform: uppercase;
    width: auto !important;
}
.invpaid-filter-row > div {
    flex: 0 0 auto;
    float: none !important;
    margin: 0;
    min-width: 150px;
    padding: 0 !important;
    width: auto !important;
}
.invpaid-filter-actions {
    align-items: center;
    display: flex;
    flex: 0 0 auto;
    flex-wrap: nowrap;
    gap: 8px;
    min-width: auto !important;
}
.invpaid-filter-row .form-control,
.invpaid-filter-row .input-group-addon {
    border-color: #d8e2ee;
    box-shadow: none;
}
.invpaid-filter-row .form-control {
    border-radius: 6px;
    color: #07152a !important;
    font-weight: 700;
    height: 38px;
    min-width: 0;
}
.invpaid-filter-row .input-group .form-control {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
.invpaid-filter-row .input-group-addon {
    background: #ffffff !important;
    border-color: #cbd8e8 !important;
    color: #07152a !important;
    border-top-right-radius: 6px;
    border-bottom-right-radius: 6px;
    cursor: pointer;
    min-width: 42px;
    text-align: center;
}
.invpaid-filter-row .input-group-addon .fa-calendar {
    color: #07152a !important;
}
.invpaid-filter-row .invpaid-agency-field {
    align-items: center;
    display: flex;
    gap: 8px;
    min-width: 240px !important;
}
.invpaid-filter-row select.invpaid-agency-select {
    background: #0f172a !important;
    background-color: #0f172a !important;
    background-image: none !important;
    border-color: #334155 !important;
    color: #ffffff !important;
    min-width: 240px;
    -webkit-text-fill-color: #ffffff !important;
}
.invpaid-filter-row select.invpaid-agency-select option {
    background: #0f172a;
    color: #ffffff;
}
.invpaid-selected-agency-label {
    color: #ffffff;
    flex: 0 1 auto;
    font-size: 11px;
    font-weight: 800;
    line-height: 1.3;
    margin-top: 0;
    max-width: 170px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.invoice-report-brand-header {
    width: 100%;
    display: none;
    border-bottom: 1px solid #d8e2ee;
    margin-bottom: 10px;
}
.invoice-report-brand-logo-cell {
    width: 180px;
    vertical-align: middle;
}
.invoice-report-brand-logo-cell img {
    max-height: 95px;
    width: auto;
}
.invoice-report-brand-details {
    vertical-align: middle;
    text-align: center;
}
.invoice-report-brand-spacer {
    width: 180px;
}
.invoice-report-brand-details h2 {
    margin: 0 0 4px 0;
    color: #0f172a;
    font-weight: 800;
}
.invoice-report-brand-line {
    margin: 0 auto;
    color: #475569;
}
.invpaid-print-card {
    background: #fff;
    border: 1px solid #d8e2ee;
    border-radius: 8px;
    padding: 14px;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
}
.invpaid-table-wrap {
    border: 1px solid #d8e2ee;
    border-radius: 8px;
    overflow-x: auto;
    background: #fff;
}
.invpaid-data-table {
    margin-bottom: 0;
}
.invpaid-data-table > tbody > tr:first-child > th {
    background: #eef3f8;
    color: #0f172a;
    border-color: #d8e2ee;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    padding-top: 11px;
    padding-bottom: 11px;
    white-space: nowrap;
}
.invpaid-data-table > tbody > tr > td {
    color: #334155;
    border-color: #e5edf6;
    vertical-align: middle;
    padding-top: 10px;
    padding-bottom: 10px;
}
.invpaid-data-table > tbody > tr:hover > td {
    background: #f8fbff;
}
.invpaid-data-table .receipt-no {
    font-weight: 800;
    color: #1d4ed8;
    white-space: nowrap;
}
.invpaid-data-table .total-row td {
    background: #f1f5f9 !important;
    color: #0f172a;
    font-weight: 800;
}
.invpaid-actions-cell {
    white-space: nowrap;
}
.invpaid_action {
    align-items: center;
    display: inline-flex;
    width: 28px;
    height: 28px;
    justify-content: center;
    line-height: 28px;
    margin: 1px;
    text-align: center;
    border: 1px solid #dbe4ee;
    border-radius: 999px;
    background: #eef3f8;
    color: #1d4ed8;
}
.invpaid_action:hover {
    background: #f8fbff;
    border-color: #c6d3e1;
    box-shadow: 0 3px 8px rgba(7,21,42,.08);
    text-decoration: none;
}
.invpaid_action.print { color: #1d4ed8; }
.invpaid_action.pdf { color: #dc2626; }
.invpaid_action.sms { color: #008a7a; }
.invpaid_action.whatsapp { color: #1fa855; }
.invpaid_action.email { color: #2477c7; }
.invpaid-pdf-mode .invpaid-print-card {
    border: 0;
    border-radius: 0;
    box-shadow: none;
    padding: 0;
}
.invpaid-pdf-mode .invpaid-table-wrap {
    border-radius: 0;
    overflow: visible;
}
.invpaid-pdf-mode .invpaid-data-table {
    width: 100%;
}
.invpaid-pdf-mode .invpaid-data-table > tbody > tr > td,
.invpaid-pdf-mode .invpaid-data-table > tbody > tr:first-child > th {
    padding: 4px;
    font-size: 12px;
}
@media(max-width: 991px) {
    .invpaid-filter-row > div,
    .invpaid-filter-row > label {
        margin-bottom: 8px;
    }
    .invpaid-filter-row > div,
    .invpaid-filter-actions {
        min-width: 100% !important;
        width: 100% !important;
    }
    .invpaid-toolbar-card .btn-group,
    .invpaid-filter-actions {
        align-items: stretch;
    }
    .invpaid-toolbar-card .btn-group .btn,
    .invpaid-filter-actions .btn {
        flex: 1 1 130px;
    }
}
@media print {
    .invoice-report-brand-header {
        display: table-row !important;
    }
}
</style>
<?php
 $this->Js->JqueryEngine->jQueryObject = 'jQuery';
 $exportNamedParams=array();
 if(isset($this->request->params['named']['isSearch']))$exportNamedParams['isSearch']=$this->request->params['named']['isSearch'];
 if(isset($this->request->params['named']['start_date']))$exportNamedParams['start_date']=$this->request->params['named']['start_date'];
 if(isset($this->request->params['named']['end_date']))$exportNamedParams['end_date']=$this->request->params['named']['end_date'];
 if(isset($this->request->params['named']['project_id']))$exportNamedParams['project_id']=$this->request->params['named']['project_id'];
 $exportCsvUrl=array_merge(array('controller'=>'Invpaids','action'=>'exportcsv'),$exportNamedParams);
 $exportPdfUrl=array_merge(array('controller'=>'Invpaids','action'=>'exportpdf'),$exportNamedParams);
 $invpaidLimitUrl='/admin/Invpaids/index';
 foreach($exportNamedParams as $limitParamName=>$limitParamValue)
 {
    $invpaidLimitUrl.='/'.rawurlencode($limitParamName).':'.rawurlencode($limitParamValue);
 }
 $reportAgency = isset($reportAgency) ? (array)$reportAgency : array();
 $agencyOptions = isset($agencyOptions) ? (array)$agencyOptions : array();
 $selectedAgencyId = isset($selectedAgencyId) ? (int)$selectedAgencyId : 0;
 $canUseAgencyFilters = !empty($canUseAgencyFilters);
 $selectedAgencyLabel = ($selectedAgencyId>0 && isset($agencyOptions[$selectedAgencyId])) ? $agencyOptions[$selectedAgencyId] : __('All Agency');
 $resolveImage = function($candidates){
    foreach((array)$candidates as $candidate)
    {
        $candidate = trim((string)$candidate);
        if($candidate==='')
        {
            continue;
        }
        $candidate = trim(str_replace('\\','/',$candidate),'/');
        $filePath = WWW_ROOT.'img'.DS.str_replace('/',DS,$candidate);
        if(file_exists($filePath))
        {
            return $candidate;
        }
    }
    return '';
 };
 $headerAgencyName = !empty($reportAgency['name']) ? $reportAgency['name'] : $siteOrganization;
 $headerAgencyAddress = !empty($reportAgency['address']) ? $reportAgency['address'] : $siteAddress;
 $headerAgencyPhone = !empty($reportAgency['phone']) ? $reportAgency['phone'] : '';
 $headerAgencyMobile = !empty($reportAgency['mobile']) ? $reportAgency['mobile'] : '';
 $headerAgencyEmail = !empty($reportAgency['email']) ? $reportAgency['email'] : $siteEmail;
 $headerAgencyLogo = $resolveImage(array(
    !empty($reportAgency['photo']) ? 'agency_thumb/'.$reportAgency['photo'] : '',
    !empty($reportAgency['photo']) ? $reportAgency['photo'] : '',
    !empty($frontLogo) ? $frontLogo : ''
 ));
// Paginator options
$this->Paginator->options(array(
  'update' => '#resultDiv',
  'evalScripts' => true,
));
?>
<div id="resultDiv">
<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Paid Invoices');?></h1></div></div>
<div class="invpaid-page-shell">
<div class="panel invpaid-page-panel"><?php echo $this->Session->flash();?>
    <div class="panel-heading invpaid-toolbar-card">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-print"></span>&nbsp;<span>Print</span>','#',array('id'=>'printme','escape'=>false,'class'=>'btn btn-primary invpaid-print-button','style'=>'display:inline-block !important;background:#1d4ed8 !important;color:#ffffff !important;min-width:92px;text-align:center;opacity:1 !important;visibility:visible !important;'));?>
            <?php echo $this->Html->link('<span class="fa fa-file-excel-o"></span>&nbsp;'.__('Export CSV'),$exportCsvUrl,array('escape'=>false,'class'=>'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-file-pdf-o"></span>&nbsp;'.__('Export PDF'),$exportPdfUrl,array('escape'=>false,'class'=>'btn btn-success','target'=>'_blank'));?>
         </div>
<?php echo $this->Form->create(array('name'=>'searchfrm','action' => 'index'));?>
		<div class="row mrg invpaid-filter-row">
		    <label for="group_name" class="col-sm-1 control-label invpaid-filter-label"><strong><?php echo __('Date');?></strong></label>
		     <div class="col-md-2">
			<div class="input-group date" id="start_date">                        
                            <?php echo $this->Form->input('start_date',array('type'=>'text','label' => false,'class'=>'form-control','div'=>false));?>
                            <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                        </div>
		    </div>
		    <div class="col-md-2">
			<div class="input-group date" id="end_date">
                           <?php echo $this->Form->input('end_date',array('type'=>'text','id'=>'InvpaidEndDate','label' => false,'class'=>'form-control','div'=>false));?>
                           <span class="input-group-addon"><span class="fa fa-calendar"></span>
                        </div>
		    </div>
                    <?php if($canUseAgencyFilters):?>
                    <label for="project_id" class="control-label invpaid-filter-label"><strong><?php echo __('Agency');?></strong></label>
                    <div class="col-md-2 invpaid-agency-field">
                        <?php echo $this->Form->select('project_id',$agencyOptions,array('empty'=>__('All Agency'),'value'=>$selectedAgencyId,'label'=>false,'class'=>'form-control invpaid-agency-select','div'=>false,'style'=>'background:#0f172a !important;background-color:#0f172a !important;color:#ffffff !important;border-color:#334155 !important;-webkit-text-fill-color:#ffffff !important;min-width:240px;'));?>
                        <div class="invpaid-selected-agency-label"><?php echo __('Selected');?>: <?php echo h($selectedAgencyLabel);?></div>
                    </div>
                    <?php else:?>
                        <?php echo $this->Form->hidden('project_id',array('value'=>$selectedAgencyId));?>
                    <?php endif;?>
		    <div class="col-md-4 invpaid-filter-actions">
			<button type="submit" class="btn btn-success btn-sm"><span class="fa fa-search"></span> <?php echo __('Search');?></button>
			<?php echo$this->Form->hidden('isSearch');?>
			<?php echo$this->Html->link('<span class="fa fa-refresh"></span>&nbsp;'.__('Reset'),array('controller'=>'Invpaids','action'=>'index'),array('class'=>'btn btn-warning btn-sm','escape'=>false));?>
		    </div>
		</div>
		<?php echo$this->Form->end();?>
    </div>
        <?php echo $this->element('pagination',array('IsSearch'=>'No','UseNamedLimit'=>true,'UseQueryLimit'=>true,'NamedLimitBaseUrl'=>$invpaidLimitUrl));
        $pageParams = $this->Paginator->params();
        $limit = $pageParams['limit'];
        $page = $pageParams['page'];
        $serialNo = 1*$limit*($page-1)+1;?>
        <?php echo $this->Form->create(array('name'=>'deleteallfrm','action' => 'deleteall'));?>
<?php echo $this->Session->flash();?>
<div id="printable_content">
<?php echo $this->Html->css('print');?>
    <div class="invpaid-print-card">
    <table width="100%" class="table" style="border-top:0;">
        <tr class="invoice-report-brand-header">
            <td class="invoice-report-brand-logo-cell" width="180" style="width:180px;">
                <?php if(strlen($headerAgencyLogo)>0){ echo $this->Html->image($headerAgencyLogo,array('alt'=>$headerAgencyName)); }?>
            </td>
            <td class="invoice-report-brand-details" align="center" style="text-align:center;">
                <h2><?php echo h($headerAgencyName);?></h2>
                <p class="invoice-report-brand-line"><?php echo h($headerAgencyAddress);?></p>
                <p class="invoice-report-brand-line">
                    <?php
                    $headerContactParts = array();
                    if(strlen(trim($headerAgencyPhone))>0)$headerContactParts[] = __('Phone').': '.h($headerAgencyPhone);
                    if(strlen(trim($headerAgencyMobile))>0)$headerContactParts[] = __('Mobile').': '.h($headerAgencyMobile);
                    if(strlen(trim($headerAgencyEmail))>0)$headerContactParts[] = __('Email').': '.h($headerAgencyEmail);
                    echo implode(' | ',$headerContactParts);
                    ?>
                </p>
            </td>
            <td class="invoice-report-brand-spacer" width="180" style="width:180px;">&nbsp;</td>
        </tr>
    </table>
                <div class="table-responsive invpaid-table-wrap">
                    <table class="table table-striped table-bordered invpaid-data-table">
		    <tr>
			<th class="pbutton"><?php echo __('Action');?></th>
			<th><?php echo __('#');?></th>
			<th><?php echo __('Client Name');?></th>
			<th><?php echo __('Father / Guardian');?></th>
			<th><?php echo __('Phone');?></th>
			<th><?php echo __('Property');?></th>
			<th><?php echo __('Receipt No');?></th>
			<th><?php echo __('Payment On');?></th>
			<th><?php echo __('Payment Date');?></th>
			<th><?php echo __('Amount');?></th>
			<th><?php echo __('Tax Amount');?></th>
			<th><?php echo __('Total Amount');?></th>
		    </tr>
		    <?php $serialNo=0;$totalAmount=0;$totalPaidAmount=0;$totalTaxAmount=0;
		    foreach ($deal as $post):$serialNo++;
		    $totalAmount=$totalAmount+$post['DealsPayment']['total_amount'];
		    $totalTaxAmount=$totalTaxAmount+$post['DealsPayment']['tax_amount'];
		    $totalPaidAmount=$totalPaidAmount+$post['DealsPayment']['amount'];
		    ?>
		    <tr>
			<td class="pbutton invpaid-actions-cell">
                            <?php
                            $paidPaymentIds = $post['DealsPayment']['id'];
                            echo $this->Html->link('<span class="fa fa-print"></span>',array('controller'=>'deals_payments','action'=>'printreceipt',$paidPaymentIds),array('class'=>'invpaid_action print','target'=>'_blank','data-toggle'=>'tooltip','title'=>__('Print Receipt'),'escape'=>false));
                            echo $this->Html->link('<span class="fa fa-file-pdf-o"></span>',array('controller'=>'deals_payments','action'=>'printreceipt',$paidPaymentIds,'?'=>array('download'=>1)),array('class'=>'invpaid_action pdf','target'=>'_blank','data-toggle'=>'tooltip','title'=>__('Download Receipt PDF'),'escape'=>false));
                            echo $this->Html->link('<span class="fa fa-comment"></span>',array('controller'=>'Invpaids','action'=>'sendsms',$paidPaymentIds),array('class'=>'invpaid_action sms','data-toggle'=>'tooltip','title'=>__('Send SMS Notification'),'escape'=>false,'onclick'=>'return confirm('.json_encode(__('Send paid invoice SMS notification to this client?')).');'));
                            echo $this->Html->link('<span class="fa fa-whatsapp"></span>',array('controller'=>'Invpaids','action'=>'whatsapp',$paidPaymentIds),array('class'=>'invpaid_action whatsapp','target'=>'_blank','data-toggle'=>'tooltip','title'=>__('Send WhatsApp Notification (with Receipt PDF)'),'escape'=>false));
                            echo $this->Html->link('<span class="fa fa-envelope"></span>',array('controller'=>'Invpaids','action'=>'sendemail',$paidPaymentIds),array('class'=>'invpaid_action email','data-toggle'=>'tooltip','title'=>__('Send Email Notification'),'escape'=>false,'onclick'=>'return confirm('.json_encode(__('Send paid invoice email notification to this client?')).');'));
                            ?>
                        </td>
			<td><?php echo$serialNo;?></td>
			<td><?php echo !empty($post['DealsPayment']['deal_id']) ? $this->Html->link(h($post['Client']['name']),array('controller'=>'deals_payments','action'=>'index',$post['DealsPayment']['deal_id']),array('escape'=>false,'title'=>__('Show Deal Payments'))) : h($post['Client']['name']);?></td>
			<td>
                            <?php
                            $fatherName = !empty($post['Client']['fathername']) ? trim((string)$post['Client']['fathername']) : '';
                            $relationType = !empty($post['Client']['relation_type']) ? trim((string)$post['Client']['relation_type']) : '';
                            $gender = !empty($post['Client']['gender']) ? trim((string)$post['Client']['gender']) : '';
                            $clientFullName = !empty($post['Client']['name']) ? trim((string)$post['Client']['name']) : '';

                            // Smart fallback: extract if embedded in client name (e.g. "ALI AJWAD S/O MUHAMMAD AJWAD")
                            if ($fatherName === '' && $clientFullName !== '') {
                                if (preg_match('/\b(s\/o|d\/o|w\/o|so|do|wo)\b[:\s]*(.*)$/i', $clientFullName, $matches)) {
                                    $embeddedPrefix = strtoupper(str_replace('/', '', trim($matches[1])));
                                    $fatherName = trim($matches[2]);
                                    if ($relationType === '') {
                                        if ($embeddedPrefix === 'WO') {
                                            $relationType = 'Husband';
                                        } elseif ($embeddedPrefix === 'DO') {
                                            $relationType = 'Father';
                                            if ($gender === '') $gender = 'Female';
                                        } else {
                                            $relationType = 'Father';
                                        }
                                    }
                                }
                            }

                            if ($fatherName !== '') {
                                $relLower = strtolower($relationType);
                                $genderLower = strtolower($gender);
                                $prefix = 'S/O';
                                $relBadgeBg = '#e0f2fe';
                                $relBadgeColor = '#0369a1';
                                $relBadgeBorder = '#bae6fd';
                                $relLabel = ($relationType !== '') ? $relationType : 'Father';

                                if ($relLower === 'husband' || strpos($relLower, 'wife') !== false || strpos($relLower, 'w/o') !== false) {
                                    $prefix = 'W/O';
                                    $relLabel = 'Husband';
                                    $relBadgeBg = '#fce7f3';
                                    $relBadgeColor = '#9d174d';
                                    $relBadgeBorder = '#fbcfe8';
                                } elseif ($genderLower === 'female' || $genderLower === 'f' || $relLower === 'daughter' || strpos($relLower, 'd/o') !== false) {
                                    $prefix = 'D/O';
                                    $relLabel = 'Father';
                                    $relBadgeBg = '#fef3c7';
                                    $relBadgeColor = '#92400e';
                                    $relBadgeBorder = '#fde68a';
                                } elseif ($relLower === 'guardian') {
                                    $prefix = 'Guardian';
                                    $relLabel = 'Guardian';
                                    $relBadgeBg = '#f1f5f9';
                                    $relBadgeColor = '#334155';
                                    $relBadgeBorder = '#cbd5e1';
                                } elseif ($relLower === 'mother') {
                                    $prefix = 'Mother';
                                    $relLabel = 'Mother';
                                    $relBadgeBg = '#d1fae5';
                                    $relBadgeColor = '#065f46';
                                    $relBadgeBorder = '#a7f3d0';
                                }
                                ?>
                                <div style="font-weight:700;color:#0f172a;line-height:1.35;">
                                    <span class="badge" style="background:<?php echo $relBadgeBg;?>;color:<?php echo $relBadgeColor;?>;border:1px solid <?php echo $relBadgeBorder;?>;font-size:10px;font-weight:800;padding:2px 6px;margin-right:4px;border-radius:4px;letter-spacing:.02em;"><?php echo h($prefix);?></span>
                                    <?php echo h($fatherName);?>
                                </div>
                                <small class="text-muted" style="display:block;font-size:11px;font-weight:600;margin-top:3px;color:#64748b;">
                                    <?php echo __('Relation');?>: <strong style="color:#1e293b;"><?php echo h($relLabel);?></strong>
                                </small>
                                <?php
                            } else {
                                echo '<span class="text-muted">-</span>';
                            }
                            ?>
                        </td>
			<td><?php echo h($post['Client']['phone']);?></td>
			<td><?php echo h($post['Property']['name']);?></td>
			<td class="receipt-no"><?php echo h($post['DealsPayment']['receipt_no']);?></td>
			<td><?php echo h($post['PlansPayment']['name']);?></td>
			<td><?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,h($post['DealsPayment']['payment_date']));?></td>
			<td><?php echo $currency.$this->Number->format($post['DealsPayment']['total_amount']);?></td>
			<td><?php echo $currency.$this->Number->format($post['DealsPayment']['tax_amount']);?></td>
			<td><?php echo $currency.$this->Number->format($post['DealsPayment']['amount']);?></td>
		    </tr>
		    <?php endforeach;?>
		    <?php unset($post);?>
		    <tr class="total-row">
			<td class="pbutton">&nbsp;</td>
			<td colspan="8" align="right"><strong><?php echo __('Total');?></strong></td>
			<td><strong><?php echo$currency.$this->Number->format($totalAmount);?></strong></td>
			<td><strong><?php echo$currency.$this->Number->format($totalTaxAmount);?></strong></td>
			<td><strong><?php echo$currency.$this->Number->format($totalPaidAmount);?></strong></td>
		    </tr>
		</table>
                </div>
        </div>
        </div>
<?php echo $this->Form->end();?>
<?php echo $this->element('pagination',array('IsSearch'=>'No','UseNamedLimit'=>true,'UseQueryLimit'=>true,'NamedLimitBaseUrl'=>$invpaidLimitUrl));?>
</div>
</div>
