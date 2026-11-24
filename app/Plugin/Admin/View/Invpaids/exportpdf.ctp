<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo h(__('Paid Invoices'));?></title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-width: 1080px; background: #fff; color: #334155; font-family: Arial, sans-serif; }
        #pdf-content { width: 1080px; margin: 0 auto; padding: 14px; background: #fff; }
        .exporting #pdf-content { margin-left: 0; margin-right: 0; }
        .invpaid-print-card { background: #fff; padding: 0; }
        .report-header { width: 100%; margin-bottom: 10px; border-collapse: collapse; border-bottom: 1px solid #d8e2ee; }
        .report-header td { border: 0; padding: 8px 4px; vertical-align: middle; }
        .invoice-report-brand-logo-cell { width: 180px; }
        .invoice-report-brand-logo-cell img { display: block; width: auto; max-width: 165px; max-height: 95px; }
        .invoice-report-brand-details { text-align: center; }
        .invoice-report-brand-spacer { width: 180px; }
        .invoice-report-brand-details h2 { margin: 0 0 4px; color: #0f172a; font-size: 24px; font-weight: 800; }
        .invoice-report-brand-line { margin: 0 auto; color: #475569; font-size: 12px; line-height: 1.45; }
        .invpaid-table-wrap { border: 1px solid #d8e2ee; background: #fff; overflow: visible; }
        .invpaid-data-table { width: 100%; margin: 0; border-collapse: collapse; table-layout: fixed; font-size: 10px; }
        .invpaid-data-table th, .invpaid-data-table td { border: 1px solid #e5edf6; padding: 5px 4px; vertical-align: middle; word-wrap: break-word; }
        .invpaid-data-table th { background: #eef3f8; color: #0f172a; font-size: 10px; font-weight: 800; text-align: left; text-transform: uppercase; }
        .invpaid-data-table tbody tr:nth-child(odd) td { background: #f9f9f9; }
        .invpaid-data-table .receipt-no { color: #1d4ed8; font-weight: 800; white-space: nowrap; }
        .number { text-align: right; white-space: nowrap; }
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
$totalAmount = 0;
$totalTaxAmount = 0;
$totalPaidAmount = 0;
?>
<div class="pdf-actions"><button type="button" onclick="downloadPaidInvoicesPdf()"><?php echo h(__('Download PDF'));?></button></div>
<main id="pdf-content">
<div class="invpaid-print-card">
    <table class="report-header invoice-report-brand-header">
        <tr>
            <td class="invoice-report-brand-logo-cell" width="180" style="width:180px;"><?php if($headerAgencyLogo!=='')echo $this->Html->image($headerAgencyLogo,array('alt'=>$headerAgencyName));?></td>
            <td class="invoice-report-brand-details" align="center" style="text-align:center;">
                <h2><?php echo h($headerAgencyName);?></h2>
                <p class="invoice-report-brand-line"><?php echo h($headerAgencyAddress);?></p>
                <p class="invoice-report-brand-line"><?php echo h(implode(' | ',$contactParts));?></p>
            </td>
            <td class="invoice-report-brand-spacer" width="180" style="width:180px;">&nbsp;</td>
        </tr>
    </table>
    <div class="invpaid-table-wrap">
    <table class="invpaid-data-table">
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:14%"><?php echo h(__('Client Name'));?></th>
                <th style="width:10%"><?php echo h(__('Phone'));?></th>
                <th style="width:12%"><?php echo h(__('Property'));?></th>
                <th style="width:10%"><?php echo h(__('Receipt No'));?></th>
                <th style="width:13%"><?php echo h(__('Payment On'));?></th>
                <th style="width:9%"><?php echo h(__('Payment Date'));?></th>
                <th style="width:9%"><?php echo h(__('Amount'));?></th>
                <th style="width:9%"><?php echo h(__('Tax Amount'));?></th>
                <th style="width:10%"><?php echo h(__('Total Amount'));?></th>
            </tr>
        </thead>
        <tbody>
        <?php if(empty($deal)):?>
            <tr><td colspan="10" class="empty"><?php echo h(__('No records found.'));?></td></tr>
        <?php else:?>
            <?php foreach($deal as $index=>$post):
                $totalAmount += $post['DealsPayment']['total_amount'];
                $totalTaxAmount += $post['DealsPayment']['tax_amount'];
                $totalPaidAmount += $post['DealsPayment']['amount'];
            ?>
            <tr>
                <td><?php echo $index+1;?></td>
                <td><?php echo h($post['Client']['name']);?></td>
                <td><?php echo h($post['Client']['phone']);?></td>
                <td><?php echo h($post['Property']['name']);?></td>
                <td class="receipt-no"><?php echo h($post['DealsPayment']['receipt_no']);?></td>
                <td><?php echo h($post['PlansPayment']['name']);?></td>
                <td><?php echo h($this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,$post['DealsPayment']['payment_date']));?></td>
                <td class="number"><?php echo h($currency.$this->Number->format($post['DealsPayment']['total_amount']));?></td>
                <td class="number"><?php echo h($currency.$this->Number->format($post['DealsPayment']['tax_amount']));?></td>
                <td class="number"><?php echo h($currency.$this->Number->format($post['DealsPayment']['amount']));?></td>
            </tr>
            <?php endforeach;?>
            <tr class="total-row">
                <td colspan="7" class="number"><?php echo h(__('Total'));?></td>
                <td class="number"><?php echo h($currency.$this->Number->format($totalAmount));?></td>
                <td class="number"><?php echo h($currency.$this->Number->format($totalTaxAmount));?></td>
                <td class="number"><?php echo h($currency.$this->Number->format($totalPaidAmount));?></td>
            </tr>
        <?php endif;?>
        </tbody>
    </table>
    </div>
</div>
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadPaidInvoicesPdf()
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
                pageContext.drawImage(
                    sourceCanvas,
                    0,offsetY,sourceCanvas.width,currentSliceHeight,
                    0,0,pageCanvas.width,currentSliceHeight
                );
                if(pageIndex>0)
                {
                    pdf.addPage();
                }
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
window.addEventListener('load',function(){window.setTimeout(downloadPaidInvoicesPdf,300);});
</script>
</body>
</html>
