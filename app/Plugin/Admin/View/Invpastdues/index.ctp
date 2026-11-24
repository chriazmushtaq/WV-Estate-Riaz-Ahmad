<script type="text/javascript">
var invpastdueHtml2PdfLoader = null;

    $(document).ready(function(){        
        $('#start_date').datetimepicker({locale:'<?php echo$configLanguage;?>',format:'<?php echo $dpFormat;?>'});
        $('#end_date').datetimepicker({locale:'<?php echo$configLanguage;?>',format:'<?php echo $dpFormat;?>',useCurrent: false //Important! See issue #1075
        });
        $("#start_date").on("dp.change", function (e) {
            $('#end_date').data("DateTimePicker").minDate(e.date);
        });
        $("#end_date").on("dp.change", function (e) {
            $('#start_date').data("DateTimePicker").maxDate(e.date);
        });
	$('.demand_letter').tooltip();
});
function submitInvpastdueBulk(actionType)
{
    var numberOfChecked = $('.chkselect:checked').length;
    if(numberOfChecked==0)
    {
        alert(getLocale("Please Select Any Record"));
        return false;
    }
    $('#bulk-action').val(actionType);
    var form = document.forms['deleteallfrm'] || $('form[name="deleteallfrm"]').get(0);
    if(!form)
    {
        alert('Bulk action form is not available. Please reload the page.');
        return false;
    }
    form.submit();
    return false;
}
function ensureInvpastdueHtml2Pdf()
{
    if(typeof html2pdf!=='undefined')
    {
        return jQuery.Deferred().resolve().promise();
    }
    if(invpastdueHtml2PdfLoader)
    {
        return invpastdueHtml2PdfLoader.promise();
    }
    invpastdueHtml2PdfLoader = jQuery.Deferred();
    var script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
    script.onload = function(){invpastdueHtml2PdfLoader.resolve();};
    script.onerror = function(){
        alert('Unable to load PDF library.');
        invpastdueHtml2PdfLoader.reject();
        invpastdueHtml2PdfLoader = null;
    };
    document.getElementsByTagName('head')[0].appendChild(script);
    return invpastdueHtml2PdfLoader.promise();
}
function invpastduePdfStamp()
{
    var now = new Date();
    var pad = function(n){ return n<10 ? '0'+n : n; };
    return now.getFullYear()+''+pad(now.getMonth()+1)+''+pad(now.getDate())+'_'+pad(now.getHours())+''+pad(now.getMinutes())+''+pad(now.getSeconds());
}
function exportInvpastdueRecordsPdf()
{
    var pdfLoader = ensureInvpastdueHtml2Pdf();
    pdfLoader.done(function(){
        var sourceElement = document.getElementById('printable_content');
        if(!sourceElement)
        {
            alert('No records found for PDF export.');
            return;
        }
        var $sourceClone = jQuery(sourceElement).clone();
        $sourceClone.find('.pbutton').css('display','none');
        $sourceClone.find('input[type="checkbox"], button, .btn, .fa').each(function(){
            if(jQuery(this).closest('td,th').hasClass('pbutton'))
            {
                jQuery(this).remove();
            }
        });
        var $wrapper = jQuery('<div id="invpastdue-pdf-export-wrapper"></div>').css({
            position: 'absolute',
            left: '-100000px',
            top: '0',
            width: '1200px',
            background: '#fff',
            padding: '5px'
        });
        $wrapper.append($sourceClone);
        jQuery('body').append($wrapper);
        var opt = {
            margin:       [0.20,0.20,0.20,0.20],
            filename:     'due_invoices_'+invpastduePdfStamp()+'.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };
        var worker = html2pdf().set(opt).from($wrapper[0]).save();
        var cleanup = function(){ $wrapper.remove(); };
        if(worker && typeof worker.then==='function')
        {
            worker.then(function(){cleanup();}).catch(function(){cleanup();});
        }
        else
        {
            cleanup();
        }
    });
    pdfLoader.fail(function(){
        if(typeof(window.print)!=='undefined')
        {
            window.print();
        }
    });
    return false;
}
function bulkDemandLettersPdf()
{
    var selected = [];
    var seen = {};
    $('.chkselect:checked').each(function(){
        var rawVal = $(this).val() || '';
        var item = rawVal.split(',');
        if(item.length<3)
        {
            return;
        }
        var key = item[0]+'-'+item[1];
        if(seen[key])
        {
            return;
        }
        seen[key] = true;
        selected.push(rawVal);
    });
    if(selected.length===0)
    {
        alert(getLocale("Please Select Any Record"));
        return false;
    }
    var actionUrl = '<?php echo $this->Html->url(array('controller'=>'Invpastdues','action'=>'bulkletterpdf')); ?>';
    var $form = $('<form method="post" target="_blank"></form>').attr('action',actionUrl);
    $.each(selected,function(_,val){
        $form.append($('<input type="hidden" name="ids[]">').val(val));
    });
    $('body').append($form);
    $form.trigger('submit');
    setTimeout(function(){$form.remove();},1000);
    return false;
}
</script>
<style type="text/css">
.invoice-report-brand-header {
    width: 100%;
    border-bottom: 1px solid #e5e5e5;
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
.invoice-report-brand-details h2 {
    margin: 0 0 4px 0;
    color: #0f172a;
    font-weight: 800;
}
.invoice-report-brand-line {
    margin: 0 auto;
    color: #475569;
}
.invpastdue-page-panel {
    background: #f5f7fb !important;
    border: 1px solid #d8e2ee !important;
    border-radius: 8px;
    box-shadow: none;
    padding: 14px;
}
.invpastdue-toolbar-card {
    align-items: center;
    background: linear-gradient(110deg,#0f172a,#312e81) !important;
    border: 1px solid #d8e2ee !important;
    border-radius: 8px;
    color: #ffffff !important;
    display: flex;
    flex-wrap: nowrap;
    gap: 12px;
    margin-bottom: 14px;
    overflow-x: auto;
    padding: 12px !important;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
}
.invpastdue-toolbar-card form {
    flex: 1 1 auto;
    margin: 0;
    min-width: 0;
}
.invpastdue-toolbar-card .btn-group {
    align-items: center;
    display: flex;
    flex: 0 0 auto;
    flex-wrap: nowrap;
    gap: 6px;
    white-space: nowrap;
}
.invpastdue-toolbar-card .btn {
    border: 0 !important;
    border-radius: 6px;
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.10);
    float: none;
    font-size: 11px;
    font-weight: 800;
    line-height: 1.2;
    padding: 8px 10px;
    text-shadow: none;
}
.invpastdue-toolbar-card .btn-default {
    background: #1d4ed8 !important;
    color: #ffffff !important;
    min-width: 92px;
    text-align: center;
}
.invpastdue-toolbar-card .btn-primary {
    background: #1d4ed8 !important;
    color: #ffffff !important;
}
.invpastdue-toolbar-card .btn-danger {
    background: #dc2626 !important;
    color: #ffffff !important;
}
.invpastdue-toolbar-card .btn-info {
    background: #0f766e !important;
    color: #ffffff !important;
}
.invpastdue-toolbar-card .btn-success {
    background: #312e81 !important;
    color: #ffffff !important;
}
.invpastdue-toolbar-card .btn-warning {
    background: #f59e0b !important;
    color: #07152a !important;
}
.invpastdue-toolbar-card .invpastdue-print-button {
    background: #1d4ed8 !important;
    color: #ffffff !important;
    display: inline-block !important;
    min-width: 78px;
    opacity: 1 !important;
    text-align: center;
    visibility: visible !important;
}
.invpastdue-toolbar-card .btn .fa {
    color: inherit !important;
}
.invpastdue-toolbar-card #printme,
.invpastdue-toolbar-card #printme span,
.invpastdue-toolbar-card #printme .fa {
    color: #ffffff !important;
    opacity: 1 !important;
    visibility: visible !important;
}
.invpastdue-filter-row {
    align-items: center;
    border-top: 0;
    display: flex;
    flex-wrap: nowrap;
    gap: 7px;
    margin: 0 !important;
    min-width: max-content;
    padding-top: 0;
    white-space: nowrap;
}
.invpastdue-filter-row:before,
.invpastdue-filter-row:after {
    content: none !important;
    display: none !important;
}
.invpastdue-filter-label {
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
.invpastdue-filter-row > div {
    flex: 0 0 auto;
    float: none !important;
    margin: 0;
    min-width: 132px;
    padding: 0 !important;
    width: auto !important;
}
.invpastdue-filter-actions {
    align-items: center;
    display: flex;
    flex: 0 0 auto;
    flex-wrap: nowrap;
    gap: 6px;
    min-width: auto !important;
}
.invpastdue-date-field {
    min-width: 132px !important;
    width: 132px !important;
}
.invpastdue-date-field .input-group {
    width: 132px;
}
.invpastdue-date-field .form-control {
    width: 96px !important;
}
.invpastdue-filter-row .form-control,
.invpastdue-filter-row .input-group-addon {
    border-color: #d8e2ee;
    box-shadow: none;
}
.invpastdue-filter-row .form-control {
    background: #fbfdff;
    border-radius: 6px;
    color: #07152a !important;
    font-weight: 700;
    height: 38px;
}
.invpastdue-filter-row .input-group .form-control {
    border-bottom-right-radius: 0;
    border-top-right-radius: 0;
}
.invpastdue-filter-row .input-group-addon {
    background: #ffffff !important;
    border-color: #cbd8e8 !important;
    border-bottom-right-radius: 6px;
    border-top-right-radius: 6px;
    color: #07152a !important;
    cursor: pointer;
    min-width: 36px;
    padding-left: 8px;
    padding-right: 8px;
    text-align: center;
}
.invpastdue-filter-row .input-group-addon .fa-calendar {
    color: #07152a !important;
}
.invpastdue-filter-row .invpastdue-agency-field {
    align-items: center;
    display: flex;
    gap: 6px;
    min-width: 310px !important;
}
.invpastdue-filter-row select.invpastdue-agency-select {
    background: #0f172a !important;
    background-color: #0f172a !important;
    background-image: none !important;
    border-color: #334155 !important;
    color: #ffffff !important;
    min-width: 190px;
    width: 190px;
    -webkit-text-fill-color: #ffffff !important;
}
.invpastdue-filter-row select.invpastdue-agency-select option {
    background: #0f172a;
    color: #ffffff;
}
.invpastdue-selected-agency-label {
    color: #ffffff;
    flex: 0 1 auto;
    font-size: 11px;
    font-weight: 800;
    line-height: 1.3;
    margin-top: 0;
    max-width: 115px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.invpastdue-table-wrap {
    background: #fff;
    border: 1px solid #d8e2ee;
    border-radius: 8px;
    overflow-x: auto;
}
.invpastdue-data-table {
    margin-bottom: 0;
}
.invpastdue-data-table > tbody > tr:first-child > th {
    background: #eef3f8;
    border-color: #d8e2ee;
    color: #0f172a;
    font-size: 12px;
    font-weight: 800;
    padding-bottom: 11px;
    padding-top: 11px;
    text-transform: uppercase;
    white-space: nowrap;
}
.invpastdue-data-table > tbody > tr > td {
    border-color: #e5edf6;
    color: #334155;
    padding-bottom: 10px;
    padding-top: 10px;
    vertical-align: middle;
}
.invpastdue-data-table > tbody > tr:hover > td {
    background: #f8fbff;
}
.invpastdue-data-table .pbutton {
    white-space: nowrap;
}
.invpastdue-data-table .demand_letter {
    background: #eef3f8;
    border-radius: 6px;
    color: #1d4ed8 !important;
    display: inline-block;
    height: 28px;
    line-height: 28px;
    margin: 0 2px 2px 0;
    text-align: center;
    width: 28px;
}
.invpastdue-data-table .demand_letter:hover {
    background: #1d4ed8;
    color: #fff !important;
}
@media(max-width: 991px) {
    .invpastdue-filter-row > div,
    .invpastdue-filter-actions {
        min-width: 100% !important;
        width: 100% !important;
    }
    .invpastdue-toolbar-card .btn-group,
    .invpastdue-filter-actions {
        align-items: stretch;
    }
    .invpastdue-toolbar-card .btn-group .btn,
    .invpastdue-filter-actions .btn {
        flex: 1 1 130px;
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
 $exportCsvUrl=array_merge(array('controller'=>'Invpastdues','action'=>'exportcsv'),$exportNamedParams);
 $exportPdfUrl=array_merge(array('controller'=>'Invpastdues','action'=>'exportpdf'),$exportNamedParams);
 $invpastdueLimitUrl = array('admin'=>true,'plugin'=>'admin','controller'=>'Invpastdues','action'=>'index');
 if(isset($this->params['named']) && is_array($this->params['named']))
 {
    foreach($this->params['named'] as $limitUrlKey=>$limitUrlValue)
    {
        if(in_array($limitUrlKey,array('limit','page'),true))
        {
            continue;
        }
        $invpastdueLimitUrl[$limitUrlKey] = $limitUrlValue;
    }
    unset($limitUrlKey,$limitUrlValue);
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
<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Balance Invoices');?></h1></div></div>
<div class="panel invpastdue-page-panel"><?php echo $this->Session->flash();?>
    <div class="panel-heading invpastdue-toolbar-card">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-print"></span>&nbsp;<span>Print</span>','#',array('id'=>'printme','escape'=>false,'class'=>'btn btn-primary invpastdue-print-button','style'=>'display:inline-block !important;background:#1d4ed8 !important;color:#ffffff !important;min-width:78px;text-align:center;opacity:1 !important;visibility:visible !important;'));?>
	    <?php echo $this->Html->link('<span class="fa fa-send"></span>&nbsp;'.__('Email'),'#',array('onclick'=>"return submitInvpastdueBulk('Email');",'escape'=>false,'class'=>'btn btn-primary'));?>
	    <?php echo $this->Html->link('<span class="fa fa-mobile"></span>&nbsp;'.__('SMS'),'#',array('onclick'=>"return submitInvpastdueBulk('SMS');",'escape'=>false,'class'=>'btn btn-danger'));?>
	    <?php echo $this->Html->link('<span class="fa fa-whatsapp"></span>&nbsp;'.__('WhatsApp'),'#',array('onclick'=>"return submitInvpastdueBulk('WhatsApp');",'escape'=>false,'class'=>'btn btn-success','style'=>'background:#16a34a !important;border-color:#15803d !important;color:#ffffff !important;'));?>
	    <?php echo $this->Html->link('<span class="fa fa-file-excel-o"></span>&nbsp;'.__('Export CSV'),$exportCsvUrl,array('escape'=>false,'class'=>'btn btn-info'));?>
	    <?php echo $this->Html->link('<span class="fa fa-file-pdf-o"></span>&nbsp;'.__('Export PDF'),$exportPdfUrl,array('escape'=>false,'class'=>'btn btn-success','target'=>'_blank'));?>
	    <?php echo $this->Html->link('<span class="fa fa-files-o"></span>&nbsp;'.__('Bulk Demand PDF'),'#',array('onclick'=>"return bulkDemandLettersPdf();",'escape'=>false,'class'=>'btn btn-warning'));?>
         </div>
	 <?php echo $this->Form->create(array('name'=>'searchfrm','action' => 'index'));?>
		<div class="mrg invpastdue-filter-row">
		    <label for="group_name" class="control-label invpastdue-filter-label"><strong><?php echo __('Date');?></strong></label>
		     <div class="invpastdue-date-field">
			<div class="input-group date" id="start_date">                        
                            <?php echo $this->Form->input('start_date',array('type'=>'text','label' => false,'class'=>'form-control','div'=>false));?>
                            <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                        </div>
		    </div>
		    <div class="invpastdue-date-field">
			<div class="input-group date" id="end_date">
                           <?php echo $this->Form->input('end_date',array('type'=>'text','id'=>'end_date_input','label' => false,'class'=>'form-control','div'=>false));?>
                           <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                        </div>
		    </div>
                    <?php if($canUseAgencyFilters):?>
                    <label for="project_id" class="control-label invpastdue-filter-label"><strong><?php echo __('Agency');?></strong></label>
                    <div class="invpastdue-agency-field">
                        <?php echo $this->Form->select('project_id',$agencyOptions,array('empty'=>__('All Agency'),'value'=>$selectedAgencyId,'label'=>false,'class'=>'form-control invpastdue-agency-select','div'=>false,'style'=>'background:#0f172a !important;background-color:#0f172a !important;color:#ffffff !important;border-color:#334155 !important;-webkit-text-fill-color:#ffffff !important;min-width:190px;width:190px;'));?>
                        <div class="invpastdue-selected-agency-label"><?php echo __('Selected');?>: <?php echo h($selectedAgencyLabel);?></div>
                    </div>
                    <?php else:?>
                        <?php echo $this->Form->hidden('project_id',array('value'=>$selectedAgencyId));?>
                    <?php endif;?>
		    <div class="invpastdue-filter-actions">
			<button type="submit" class="btn btn-success btn-sm"><span class="fa fa-search"></span> <?php echo __('Search');?></button>
			<?php echo$this->Form->hidden('isSearch');?>
			<?php echo$this->Html->link('<span class="fa fa-refresh"></span>&nbsp;'.__('Reset'),array('controller'=>'Invpastdues','action'=>'index'),array('class'=>'btn btn-warning btn-sm','escape'=>false));?>
		    </div>
		</div>
		<?php echo$this->Form->end();?>

    </div>
        <?php echo $this->element('pagination',array('IsSearch'=>'No','UseNamedLimit'=>true,'NamedLimitBaseUrl'=>$invpastdueLimitUrl));
        $pageParams = $this->Paginator->params();
        $limit = $pageParams['limit'];
        $page = $pageParams['page'];
        $serialNo = 1*$limit*($page-1);?>
        <?php echo $this->Form->create(array('name'=>'deleteallfrm','action' => 'deleteall'));?>
<?php echo $this->Session->flash();?>
<div id="printable_content">
<?php echo $this->Html->css('print');?>
    <table width="100%" class="table" style="border-top:0;">
        <tr class="invoice-report-brand-header">
            <td class="invoice-report-brand-logo-cell">
                <?php if(strlen($headerAgencyLogo)>0){ echo $this->Html->image($headerAgencyLogo,array('alt'=>$headerAgencyName)); }?>
            </td>
            <td class="invoice-report-brand-details">
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
        </tr>
    </table>
    <div class="panel-body">
			</div>
                <div class="table-responsive invpastdue-table-wrap">
                    <table class="table table-striped table-bordered invpastdue-data-table">
		    <tr>
			<th class="pbutton"><?php echo $this->Form->checkbox('checkbox', array('value'=>'deleteall','name'=>'selectAll','label'=>false,'id'=>'selectAll','hiddenField'=>false));?></th>
			<th><?php echo __('#');?></th>
			<th><?php echo __('Client Name');?></th>
			<th><?php echo __('Father / Guardian');?></th>
			<th><?php echo __('Phone');?></th>
			<th><?php echo __('Property');?></th>
			<th><?php echo __('Invoice No.');?></th>
			<th><?php echo __('Payment Due On');?></th>
			<th><?php echo __('Balance');?></th>
			<th><?php echo __('Overdue+Late Fees');?></th>
			<th><?php echo __('Due Date');?></th>
			<th class="pbutton"><?php echo __('Action');?></th>
		    </tr>
		    <?php $totalFullPayment=0;$totalLateFees=0;$totalOverdueWithLate=0;
		    foreach ($deal as $post):$serialNo++;
		    $id=$post['Invpastdue']['id'];
                    $totalOverdue = 0;
                    if($post['Deal']['plan_id']!=1)
                    {
                        if(isset($post['Invpastdue']['total_overdue']))
                        $totalOverdue=(float)$post['Invpastdue']['total_overdue'];
                        else
                        $totalOverdue=(float)$post['Invpastdue']['amount'];
                    }
		    $lateFeesAmount = isset($post['Invpastdue']['total_late_fee']) ? (float)$post['Invpastdue']['total_late_fee'] : 0;
		    $overdueWithLate = isset($post['Invpastdue']['total_overdue_with_late']) ? (float)$post['Invpastdue']['total_overdue_with_late'] : ($totalOverdue + $lateFeesAmount);
		    $totalFullPayment=$totalFullPayment+$totalOverdue;
		    $totalLateFees=$totalLateFees+$lateFeesAmount;
		    $totalOverdueWithLate=$totalOverdueWithLate+$overdueWithLate;
		    ?>
		    <tr>
			<td class="pbutton"><?php echo $this->Form->checkbox(false,array('value' =>$post['Client']['id'].','.$post['Deal']['id'].','.$post['Invpastdue']['id'],'name'=>'data[Invpastdue][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
			<td><?php echo$serialNo;?></td>
			<td><?php echo h($post['Client']['name']);?></td>
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
			<td>
                            <?php
                            $pastDueMobileDisplay = trim((string)$post['Client']['mobile']) !== '' ? $post['Client']['mobile'] : (isset($post['Client']['phone']) ? $post['Client']['phone'] : '');
                            $pastDueCallNo = preg_replace('/[^0-9\+]/', '', (string)$pastDueMobileDisplay);
                            if(trim((string)$pastDueMobileDisplay) === '')
                            {
                                echo '-';
                            }
                            elseif(trim((string)$pastDueCallNo) !== '')
                            {
                                echo $this->Html->link(h($pastDueMobileDisplay), 'tel:'.$pastDueCallNo, array('escape' => false));
                            }
                            else
                            {
                                echo h($pastDueMobileDisplay);
                            }
                            ?>
                        </td>
			<td><?php echo h($post['Property']['name']);?></td>
			<td><?php echo h($post['Deal']['invoice_no']);?></td>
			<td>
                            <?php
                            $overdueCount = isset($post['Invpastdue']['overdue_count']) ? (int)$post['Invpastdue']['overdue_count'] : 0;
                            if($overdueCount>1)
                            echo __('Overdue Installments').': '.$overdueCount;
                            else
                            echo h($post['Invpastdue']['name']);
                            ?>
                        </td>
			<td><?php if($post['Deal']['plan_id']!=1)echo $currency.$this->Number->format($totalOverdue);?></td>
			<td>
                            <?php if($post['Deal']['plan_id']!=1): ?>
                                <?php echo $currency.$this->Number->format($overdueWithLate);?><br>
                                <small style="color:#d9534f;"><?php echo __('Late Fees');?>: <?php echo $currency.$this->Number->format($lateFeesAmount);?></small>
                            <?php endif; ?>
                        </td>
			<td><?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,h($post['Invpastdue']['date']));?></td>

			<td class="pbutton"><?php echo $this->Html->link('<span class="fa fa-print"></span>',array('controller'=>'Invpastdues','action'=>'printletter',$post['Client']['id'],$post['Deal']['id'],$post['Invpastdue']['id']),array('class'=>'demand_letter','target'=>'_blank','data-toggle'=>'tooltip','title'=>'Print Demand Letter','escape'=>false));
			echo $this->Html->link('  <span class="fa fa-send"></span>',array('controller'=>'Invpastdues','action'=>'invoicemail',$post['Client']['id'],$post['Deal']['id'],$post['Invpastdue']['id']),array('class'=>'demand_letter','data-toggle'=>'tooltip','title'=>'E-Mail Demand Letter','escape'=>false));
			echo $this->Html->link('  <span class="fa fa-mobile"></span>',array('controller'=>'Invpastdues','action'=>'invoicesms',$post['Client']['id'],$post['Deal']['id'],$post['Invpastdue']['id']),array('class'=>'demand_letter','data-toggle'=>'tooltip','title'=>'SMS Demand Letter','escape'=>false));
			echo $this->Html->link('  <span class="fa fa-whatsapp"></span>',array('controller'=>'Invpastdues','action'=>'invoicewhatsapp',$post['Client']['id'],$post['Deal']['id'],$post['Invpastdue']['id']),array('class'=>'demand_letter','data-toggle'=>'tooltip','title'=>'WhatsApp Demand Notice','escape'=>false,'style'=>'color:#16a34a;'));
			echo $this->Html->link('  <span class="fa fa-file-pdf-o"></span>',array('controller'=>'Invpastdues','action'=>'downloadletterpdf',$post['Client']['id'],$post['Deal']['id'],$post['Invpastdue']['id']),array('class'=>'demand_letter','target'=>'_blank','data-toggle'=>'tooltip','title'=>'Download Demand Letter PDF','escape'=>false));?></td>
		    </tr>
		    <?php endforeach;?>
		    <?php unset($post);?>
		    <tr><td class="pbutton">&nbsp;</td>
		    <td colspan="7" align="right"><strong><?php echo __('Total Balance');?></strong></td><td><strong><?php echo $currency.$this->Number->format($totalFullPayment);?></strong></td><td><strong><?php echo $currency.$this->Number->format($totalOverdueWithLate);?></strong><br><small style="color:#d9534f;"><?php echo __('Late Fees');?>: <?php echo $currency.$this->Number->format($totalLateFees);?></small></td><td>&nbsp;</td><td class="pbutton">&nbsp;</td></tr>
		</table>
                </div>
        </div>
<?php echo$this->Form->hidden('action',array('name'=>'action','id'=>'bulk-action'));?>
<?php echo $this->Form->end();?>
<?php echo $this->element('pagination',array('IsSearch'=>'No','UseNamedLimit'=>true,'NamedLimitBaseUrl'=>$invpastdueLimitUrl));?>
</div>
