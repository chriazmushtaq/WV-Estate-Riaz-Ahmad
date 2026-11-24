<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo h(__('Balance Invoices'));?></title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-width: 1080px; background: #fff; color: #334155; font-family: Arial, sans-serif; }
        #pdf-content { width: 1080px; margin: 0 auto; padding: 14px; background: #fff; }
        .exporting #pdf-content { margin-left: 0; margin-right: 0; }
        .report-header { width: 100%; margin-bottom: 10px; border-collapse: collapse; border-bottom: 1px solid #d8e2ee; }
        .report-header td { border: 0; padding: 8px 4px; vertical-align: middle; }
        .brand-logo, .brand-spacer { width: 180px; }
        .brand-logo img { display: block; width: auto; max-width: 165px; max-height: 95px; }
        .brand-details { text-align: center; }
        .brand-details h2 { margin: 0 0 4px; color: #0f172a; font-size: 24px; font-weight: 800; }
        .brand-line { margin: 0 auto; color: #475569; font-size: 12px; line-height: 1.45; }
        .table-wrap { border: 1px solid #d8e2ee; background: #fff; overflow: visible; }
        .data-table { width: 100%; margin: 0; border-collapse: collapse; table-layout: fixed; font-size: 10px; }
        .data-table th, .data-table td { border: 1px solid #e5edf6; padding: 5px 4px; vertical-align: middle; word-wrap: break-word; }
        .data-table th { background: #eef3f8; color: #0f172a; font-size: 10px; font-weight: 800; text-align: left; text-transform: uppercase; }
        .data-table tbody tr:nth-child(odd) td { background: #f9f9f9; }
        .invoice-no { color: #1d4ed8; font-weight: 800; }
        .number { text-align: right; white-space: nowrap; }
        .late-fee { color: #d9534f; font-size: 9px; }
        .total-row td { background: #f1f5f9 !important; color: #0f172a; font-weight: 800; }
        .empty { padding: 30px !important; text-align: center; font-size: 14px; }
        .pdf-actions { position: fixed; right: 18px; top: 18px; z-index: 10; }
        .pdf-actions button { border: 0; border-radius: 4px; background: #15803d; color: #fff; cursor: pointer; padding: 10px 15px; font-weight: bold; }
        .exporting .pdf-actions { display: none; }
    </style>
</head>
<body>
<?php
$reportAgency = isset($reportAgency) ? (array)$reportAgency : array();
$headerAgencyName = !empty($reportAgency['name']) ? $reportAgency['name'] : $siteOrganization;
$headerAgencyAddress = !empty($reportAgency['address']) ? $reportAgency['address'] : $siteAddress;
$headerAgencyPhone = !empty($reportAgency['phone']) ? $reportAgency['phone'] : '';
$headerAgencyMobile = !empty($reportAgency['mobile']) ? $reportAgency['mobile'] : '';
$headerAgencyEmail = !empty($reportAgency['email']) ? $reportAgency['email'] : $siteEmail;
$headerAgencyLogo = '';
$logoCandidates = array(
    !empty($reportAgency['photo']) ? 'agency_thumb/'.$reportAgency['photo'] : '',
    !empty($reportAgency['photo']) ? $reportAgency['photo'] : '',
    !empty($frontLogo) ? $frontLogo : ''
);
foreach($logoCandidates as $logoCandidate)
{
    $logoCandidate = trim(str_replace('\\','/',(string)$logoCandidate),'/');
    if($logoCandidate!=='' && file_exists(WWW_ROOT.'img'.DS.str_replace('/',DS,$logoCandidate)))
    {
        $headerAgencyLogo = $logoCandidate;
        break;
    }
}
$contactParts = array();
if(trim($headerAgencyPhone)!=='')$contactParts[] = __('Phone').': '.$headerAgencyPhone;
if(trim($headerAgencyMobile)!=='')$contactParts[] = __('Mobile').': '.$headerAgencyMobile;
if(trim($headerAgencyEmail)!=='')$contactParts[] = __('Email').': '.$headerAgencyEmail;
$totalBalance = 0;
$totalLateFees = 0;
$totalOverdueWithLate = 0;
?>
<div class="pdf-actions"><button type="button" onclick="downloadInvpastduesPdf()"><?php echo h(__('Download PDF'));?></button></div>
<main id="pdf-content">
    <table class="report-header">
        <tr>
            <td class="brand-logo" width="180" style="width:180px;"><?php if($headerAgencyLogo!=='')echo $this->Html->image($headerAgencyLogo,array('alt'=>$headerAgencyName));?></td>
            <td class="brand-details" align="center" style="text-align:center;">
                <h2><?php echo h($headerAgencyName);?></h2>
                <p class="brand-line"><?php echo h($headerAgencyAddress);?></p>
                <p class="brand-line"><?php echo h(implode(' | ',$contactParts));?></p>
            </td>
            <td class="brand-spacer" width="180" style="width:180px;">&nbsp;</td>
        </tr>
    </table>
    <div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:15%"><?php echo h(__('Client Name'));?></th>
                <th style="width:10%"><?php echo h(__('Phone'));?></th>
                <th style="width:12%"><?php echo h(__('Property'));?></th>
                <th style="width:10%"><?php echo h(__('Invoice No.'));?></th>
                <th style="width:15%"><?php echo h(__('Payment Due On'));?></th>
                <th style="width:10%"><?php echo h(__('Balance'));?></th>
                <th style="width:14%"><?php echo h(__('Overdue+Late Fees'));?></th>
                <th style="width:10%"><?php echo h(__('Due Date'));?></th>
            </tr>
        </thead>
        <tbody>
        <?php if(empty($deal)):?>
            <tr><td colspan="9" class="empty"><?php echo h(__('No records found.'));?></td></tr>
        <?php else:?>
            <?php foreach($deal as $index=>$post):
                $overdueCount = isset($post['Invpastdue']['overdue_count']) ? (int)$post['Invpastdue']['overdue_count'] : 0;
                $paymentDueOn = $overdueCount>1 ? __('Overdue Installments').': '.$overdueCount : $post['Invpastdue']['name'];
                $balance = 0;
                if($post['Deal']['plan_id']!=1)
                {
                    $balance = isset($post['Invpastdue']['total_overdue']) ? (float)$post['Invpastdue']['total_overdue'] : (float)$post['Invpastdue']['amount'];
                }
                $lateFees = isset($post['Invpastdue']['total_late_fee']) ? (float)$post['Invpastdue']['total_late_fee'] : 0;
                $overdueWithLate = isset($post['Invpastdue']['total_overdue_with_late']) ? (float)$post['Invpastdue']['total_overdue_with_late'] : ($balance+$lateFees);
                $totalBalance += $balance;
                $totalLateFees += $lateFees;
                $totalOverdueWithLate += $overdueWithLate;
                $phone = trim((string)$post['Client']['mobile'])!=='' ? $post['Client']['mobile'] : $post['Client']['phone'];
            ?>
            <tr>
                <td><?php echo $index+1;?></td>
                <td><?php echo h($post['Client']['name']);?></td>
                <td><?php echo h($phone);?></td>
                <td><?php echo h($post['Property']['name']);?></td>
                <td class="invoice-no"><?php echo h($post['Deal']['invoice_no']);?></td>
                <td><?php echo h($paymentDueOn);?></td>
                <td class="number"><?php if($post['Deal']['plan_id']!=1)echo h($currency.$this->Number->format($balance));?></td>
                <td class="number"><?php if($post['Deal']['plan_id']!=1):?><?php echo h($currency.$this->Number->format($overdueWithLate));?><br><span class="late-fee"><?php echo h(__('Late Fees').': '.$currency.$this->Number->format($lateFees));?></span><?php endif;?></td>
                <td><?php echo h($this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,$post['Invpastdue']['date']));?></td>
            </tr>
            <?php endforeach;?>
            <tr class="total-row">
                <td colspan="6" class="number"><?php echo h(__('Total Balance'));?></td>
                <td class="number"><?php echo h($currency.$this->Number->format($totalBalance));?></td>
                <td class="number"><?php echo h($currency.$this->Number->format($totalOverdueWithLate));?><br><span class="late-fee"><?php echo h(__('Late Fees').': '.$currency.$this->Number->format($totalLateFees));?></span></td>
                <td>&nbsp;</td>
            </tr>
        <?php endif;?>
        </tbody>
    </table>
    </div>
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadInvpastduesPdf()
{
    var content = document.getElementById('pdf-content');
    if(!content || typeof html2pdf === 'undefined')
    {
        alert('PDF export library could not load. Please try again.');
        return;
    }
    document.body.classList.add('exporting');
    var filename = <?php echo json_encode($pdfFilename);?>;
    var margin = 0.18;
    var options = {
        margin: 0,
        filename: filename,
        image: {type:'jpeg',quality:0.98},
        html2canvas: {scale:2,useCORS:true,backgroundColor:'#ffffff',scrollX:0,scrollY:0,x:0,y:0,width:1080,windowWidth:1080},
        jsPDF: {unit:'in',format:'a4',orientation:'landscape'}
    };
    var worker = html2pdf().set(options).from(content).toCanvas();
    worker.get('canvas').then(function(sourceCanvas){
        return worker.toPdf().get('pdf').then(function(pdf){
            var originalPageCount = pdf.getNumberOfPages();
            var pageWidth = pdf.internal.pageSize.getWidth();
            var pageHeight = pdf.internal.pageSize.getHeight();
            var availableWidth = pageWidth-(margin*2);
            var availableHeight = pageHeight-(margin*2);
            var sliceHeight = Math.max(1,Math.floor(availableHeight*sourceCanvas.width/availableWidth));
            var offsetY = 0;
            var pageIndex = 0;
            pdf.addPage();
            for(var oldPage=originalPageCount;oldPage>=1;oldPage--)
            {
                pdf.deletePage(oldPage);
            }
            while(offsetY<sourceCanvas.height)
            {
                var currentSliceHeight = Math.min(sliceHeight,sourceCanvas.height-offsetY);
                var pageCanvas = document.createElement('canvas');
                pageCanvas.width = sourceCanvas.width;
                pageCanvas.height = currentSliceHeight;
                var pageContext = pageCanvas.getContext('2d');
                pageContext.fillStyle = '#ffffff';
                pageContext.fillRect(0,0,pageCanvas.width,pageCanvas.height);
                pageContext.drawImage(sourceCanvas,0,offsetY,sourceCanvas.width,currentSliceHeight,0,0,pageCanvas.width,currentSliceHeight);
                if(pageIndex>0)pdf.addPage();
                var renderedHeight = currentSliceHeight*availableWidth/sourceCanvas.width;
                pdf.addImage(pageCanvas.toDataURL('image/jpeg',0.98),'JPEG',margin,margin,availableWidth,renderedHeight,undefined,'FAST');
                offsetY += currentSliceHeight;
                pageIndex++;
            }
            pdf.save(filename);
        });
    }).then(function(){
        document.body.classList.remove('exporting');
    }).catch(function(){
        document.body.classList.remove('exporting');
        alert('Unable to create the PDF. Please try again.');
    });
}
window.addEventListener('load',function(){window.setTimeout(downloadInvpastduesPdf,300);});
</script>
</body>
</html>
