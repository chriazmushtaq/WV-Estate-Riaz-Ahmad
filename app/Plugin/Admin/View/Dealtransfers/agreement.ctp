<?php
$t = isset($transfer['Dealtransfer']) ? $transfer['Dealtransfer'] : array();
$deal = isset($agreementDeal['Deal']) ? $agreementDeal['Deal'] : array();
$property = isset($agreementDeal['Property']) ? $agreementDeal['Property'] : array();
$type = isset($agreementDeal['Type']) ? $agreementDeal['Type'] : array();
$unit = isset($agreementDeal['Unit']) ? $agreementDeal['Unit'] : array();
$contract = isset($agreementDeal['Contract']) ? $agreementDeal['Contract'] : array();
$location = isset($agreementDeal['Location']) ? $agreementDeal['Location'] : array();
$seller = isset($transferor['Client']) ? $transferor['Client'] : array();
$buyer = isset($transferee['Client']) ? $transferee['Client'] : array();
$sellerFile = !empty($sourceDeal['Deal']['invoice_no']) ? $sourceDeal['Deal']['invoice_no'] : (!empty($deal['invoice_no']) ? $deal['invoice_no'] : '-');
$buyerFile = (!empty($t['transfer_type']) && $t['transfer_type'] === 'Merge' && !empty($targetDeal['Deal']['invoice_no'])) ? $targetDeal['Deal']['invoice_no'] : $sellerFile;
$categoryText = !empty($type['name']) ? $type['name'] : (!empty($contract['name']) ? $contract['name'] : '-');
$propertyText = !empty($property['name']) ? $property['name'] : '-';
$typeSizeParts = array();
if(!empty($contract['name'])) { $typeSizeParts[] = $contract['name']; }
if(!empty($property['area']) || !empty($unit['name']))
{
    $typeSizeParts[] = trim((isset($property['area']) ? $property['area'] : '').' '.(isset($unit['name']) ? $unit['name'] : ''));
}
if(!empty($location['name'])) { $typeSizeParts[] = $location['name']; }
$typeSizeText = !empty($typeSizeParts) ? implode(', ', $typeSizeParts) : '-';
$sellerPhoto = !empty($agreementImages['transferor_photo']) ? $agreementImages['transferor_photo'] : '';
$buyerPhoto = !empty($agreementImages['transferee_photo']) ? $agreementImages['transferee_photo'] : '';
$sellerId = !empty($agreementImages['transferor_id_front']) ? $agreementImages['transferor_id_front'] : '';
$buyerId = !empty($agreementImages['transferee_id_front']) ? $agreementImages['transferee_id_front'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($agreementTitle);?></title>
    <style>
        *{box-sizing:border-box;}
        body{font-family:"Times New Roman",Times,serif;background:#f4f7f6;margin:0;padding:20px;color:#374151;}
        .print-actions{max-width:1000px;margin:0 auto 12px;display:flex;justify-content:flex-end;gap:8px;font-family:Arial,sans-serif;}
        .print-actions a,.print-actions button{border:1px solid #cfd8e3;background:#fff;color:#172033;padding:8px 12px;border-radius:6px;text-decoration:none;font-weight:700;cursor:pointer;}
        .print-actions button{background:#1b2cff;color:#fff;border-color:#1b2cff;}
        .agreement-card{max-width:1000px;margin:20px auto;background:#fff;padding:40px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -2px rgba(0,0,0,.1);border-radius:12px;}
        .header{margin-bottom:32px;padding-bottom:16px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;gap:20px;align-items:center;flex-wrap:wrap;}
        .brand{display:flex;align-items:center;gap:16px;}
        .brand img{width:64px;height:64px;border-radius:8px;object-fit:contain;box-shadow:0 2px 10px rgba(0,0,0,.15);}
        .brand-name{font-size:30px;font-weight:900;color:#111827;line-height:1.1;}
        .contact{text-align:right;font-size:14px;line-height:1.45;color:#4b5563;}
        h1{font-size:30px;font-weight:800;color:#1f2937;text-align:center;margin:0 0 40px;padding-bottom:8px;border-bottom:2px solid #d1d5db;}
        .meta-line{text-align:center;margin-top:-28px;margin-bottom:30px;font-size:13px;color:#6b7280;}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:32px;}
        .info-box{border:2px solid #e5e7eb;border-radius:8px;padding:12px;min-height:240px;display:flex;flex-direction:column;}
        .info-box h2{font-size:18px;font-weight:700;color:#374151;margin:0 0 12px;padding-bottom:6px;border-bottom:2px solid #d1d5db;text-transform:uppercase;}
        .client-row{display:flex;gap:16px;}
        .client-photo{width:75px;height:75px;border-radius:4px;object-fit:cover;border:1px solid #d1d5db;box-shadow:0 0 0 2px white,0 0 0 3px #d1d5db;flex:0 0 auto;}
        .client-data{font-size:14px;color:#4b5563;line-height:1.45;}
        .client-data p{margin:0 0 4px;}
        .id-card-placeholder{width:220px;height:140px;margin:15px auto 0;object-fit:contain;border-radius:4px;border:1px solid #d1d5db;box-shadow:0 0 0 2px white,0 0 0 3px #d1d5db;background:#f9fafb;display:flex;align-items:center;justify-content:center;color:#6b7280;font-size:13px;text-align:center;}
        .id-card-placeholder img{width:100%;height:100%;object-fit:contain;border-radius:4px;}
        .intro{margin:0 0 24px;color:#374151;font-style:italic;}
        .double-border-container{border:2px solid #d1d5db;box-shadow:0 0 0 2px white,0 0 0 4px #d1d5db;border-radius:6px;overflow:hidden;height:100%;}
        .table-title{background:#f3f4f6;padding:8px;border-bottom:2px solid #d1d5db;text-align:center;text-transform:uppercase;font-size:14px;font-weight:800;color:#374151;}
        .data-table{border-collapse:collapse;width:100%;font-size:14px;}
        .data-table th,.data-table td{border:2px solid #d1d5db;padding:8px 10px;text-align:left;vertical-align:top;}
        .data-table th{background:#e5e7eb;font-weight:700;color:#374151;}
        .green{color:#047857;font-weight:800;}
        .red{color:#dc2626;font-weight:800;}
        .terms{margin-bottom:40px;}
        .terms h2{font-size:18px;font-weight:800;color:#374151;margin:0 0 12px;text-decoration:underline;}
        .terms ol{font-size:12px;line-height:1.5;color:#374151;margin:0;padding-left:20px;}
        .signature-row{display:flex;justify-content:space-between;align-items:flex-end;border-top:1px solid #e5e7eb;padding-top:39px;font-size:14px;color:#374151;}
        .signature-row.second{border-top:0;padding-top:68px;}
        .sig{width:33.333%;}
        .sig.center{text-align:center;}
        .sig.right{text-align:right;}
        .sig-line{font-weight:800;font-size:18px;margin:0;}
        .sig-label{font-weight:800;margin:4px 0 0;}
        .sig-note{font-size:12px;color:#6b7280;font-style:italic;margin:2px 0 0;}
        .qr-section{margin-top:40px;display:flex;flex-direction:column;align-items:center;}
        .qr-section img{width:160px;height:160px;margin-bottom:16px;}
        .witness-row{width:100%;max-width:660px;font-size:12px;color:#374151;display:flex;justify-content:space-between;align-items:flex-end;gap:16px;}
        .witness-row div,.witness-row p{width:33.333%;margin:0;}
        .witness-row .center{text-align:center;color:#6b7280;font-style:italic;}
        .text-right{text-align:right;}
        @media(max-width:760px){
            body{padding:10px;}
            .agreement-card{padding:22px;margin:10px auto;}
            .header,.brand{align-items:flex-start;}
            .contact{text-align:left;}
            .grid-2{grid-template-columns:1fr;}
            .signature-row,.witness-row{gap:16px;}
        }
        @media print{
            body{background:#fff;padding:0;margin:0;}
            .print-actions{display:none!important;}
            .agreement-card{width:210mm;max-width:210mm;margin:0;padding:15mm 10mm;box-shadow:none;border-radius:0;}
            .header{border-bottom:1px solid #e5e7eb!important;}
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <a href="<?php echo $this->Html->url(array('controller' => 'Dealtransfers', 'action' => 'view', (int)$t['id']));?>"><?php echo __('Back');?></a>
        <button type="button" onclick="window.print()"><?php echo __('Print Agreement');?></button>
    </div>

    <div class="agreement-card">
        <div class="header">
            <div class="brand">
                <img src="<?php echo h($agencyInfo['logo_url']);?>" alt="<?php echo h($agencyInfo['name']);?>">
                <div class="brand-name"><?php echo h($agencyInfo['name']);?></div>
            </div>
            <div class="contact">
                <div><strong><?php echo __('Address');?>:</strong> <?php echo h(!empty($agencyInfo['address']) ? $agencyInfo['address'] : '-');?></div>
                <div><strong><?php echo __('Email');?>:</strong> <?php echo h(!empty($agencyInfo['email']) ? $agencyInfo['email'] : '-');?></div>
                <div><strong><?php echo __('Mobile');?>:</strong> <?php echo h(!empty($agencyInfo['mobile']) ? $agencyInfo['mobile'] : '-');?></div>
            </div>
        </div>

        <h1><?php echo h($agreementTitle);?></h1>
        <div class="meta-line">
            <?php echo __('Transfer No');?>: <strong><?php echo h(!empty($t['transfer_no']) ? $t['transfer_no'] : '-');?></strong>
            &nbsp; | &nbsp;
            <?php echo __('Request Date');?>: <strong><?php echo h($requestDate);?></strong>
        </div>

        <div class="grid-2">
            <div class="info-box">
                <h2><?php echo __('Transferor (Seller)');?></h2>
                <div class="client-row">
                    <?php if($sellerPhoto !== ''){ ?><img src="<?php echo h($sellerPhoto);?>" alt="<?php echo h(isset($seller['name']) ? $seller['name'] : '');?> " class="client-photo"><?php } ?>
                    <div class="client-data">
                        <p><strong><?php echo __('Name');?>:</strong> <?php echo h(!empty($seller['name']) ? $seller['name'] : '-');?></p>
                        <p><strong><?php echo __('S/O');?>:</strong> <?php echo h(!empty($seller['fathername']) ? $seller['fathername'] : '-');?></p>
                        <p><strong><?php echo __('File #');?>:</strong> <?php echo h($sellerFile);?></p>
                        <p><strong><?php echo __('CNIC');?>:</strong> <?php echo h(!empty($seller['pincode']) ? $seller['pincode'] : '-');?></p>
                        <p><strong><?php echo __('Mobile');?>:</strong> <?php echo h(!empty($seller['mobile']) ? $seller['mobile'] : (!empty($seller['phone']) ? $seller['phone'] : '-'));?></p>
                    </div>
                </div>
                <div class="id-card-placeholder">
                    <?php if($sellerId !== ''){ ?><img src="<?php echo h($sellerId);?>" alt="<?php echo __('Seller ID Card');?>"><?php } else { echo __('Seller ID Card'); } ?>
                </div>
            </div>

            <div class="info-box">
                <h2><?php echo __('Transferee (Buyer)');?></h2>
                <div class="client-row">
                    <?php if($buyerPhoto !== ''){ ?><img src="<?php echo h($buyerPhoto);?>" alt="<?php echo h(isset($buyer['name']) ? $buyer['name'] : '');?> " class="client-photo"><?php } ?>
                    <div class="client-data">
                        <p><strong><?php echo __('Name');?>:</strong> <?php echo h(!empty($buyer['name']) ? $buyer['name'] : '-');?></p>
                        <p><strong><?php echo __('S/O');?>:</strong> <?php echo h(!empty($buyer['fathername']) ? $buyer['fathername'] : '-');?></p>
                        <p><strong><?php echo __('File #');?>:</strong> <?php echo h($buyerFile);?></p>
                        <p><strong><?php echo __('CNIC');?>:</strong> <?php echo h(!empty($buyer['pincode']) ? $buyer['pincode'] : '-');?></p>
                        <p><strong><?php echo __('Mobile');?>:</strong> <?php echo h(!empty($buyer['mobile']) ? $buyer['mobile'] : (!empty($buyer['phone']) ? $buyer['phone'] : '-'));?></p>
                    </div>
                </div>
                <div class="id-card-placeholder">
                    <?php if($buyerId !== ''){ ?><img src="<?php echo h($buyerId);?>" alt="<?php echo __('Buyer ID Card');?>"><?php } else { echo __('Buyer ID Card'); } ?>
                </div>
            </div>
        </div>

        <p class="intro">
            <?php echo __('This agreement confirms the transfer of all rights, interests, and liabilities of the property detailed below from the Transferor to the Transferee.');?>
        </p>

        <div class="grid-2">
            <div class="double-border-container">
                <div class="table-title"><?php echo __('Property Information');?></div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?php echo __('File #');?></th>
                            <th><?php echo __('Category');?></th>
                            <th><?php echo __('Property');?></th>
                            <th><?php echo __('Type / Size');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo h(!empty($deal['invoice_no']) ? $deal['invoice_no'] : $sellerFile);?></td>
                            <td><strong><?php echo h($categoryText);?></strong></td>
                            <td><?php echo h($propertyText);?></td>
                            <td><?php echo h($typeSizeText);?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="double-border-container">
                <div class="table-title"><?php echo __('Payment Summary');?></div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?php echo __('Total Paid');?></th>
                            <th><?php echo __('Inst. #');?></th>
                            <th><?php echo __('Due Amt');?></th>
                            <th><?php echo __('Cycle');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="green"><?php echo h($paymentSummary['total_paid_text']);?></td>
                            <td><?php echo h($paymentSummary['installments']);?></td>
                            <td class="red"><?php echo h($paymentSummary['due_amount_text']);?></td>
                            <td>
                                <?php echo h($paymentSummary['cycle']);?><br>
                                <span style="font-size:10px;color:#6b7280;font-weight:800;">(<?php echo h($paymentSummary['next_label']);?>)</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="terms">
            <h2><?php echo __('Terms & Conditions:');?></h2>
            <ol>
                <li><?php echo __('Transferee accepts all financial liabilities and payment history of this property booking.');?></li>
                <li><?php echo __('All future installments and society charges are now the sole responsibility of the Transferee.');?></li>
                <li><?php echo __('Transferor confirms receipt of full settlement and relinquishes all claims.');?></li>
                <li><?php echo __('This transfer is contingent upon payment of official fees to the agency management.');?></li>
                <li><?php echo __('Transferee agrees to abide by all society by-laws and original booking terms.');?></li>
                <li><?php echo __('Agreement entered freely without undue influence or coercion.');?></li>
                <li><?php echo __('Binding upon heirs, successors, and assigns of both parties.');?></li>
            </ol>
        </div>

        <div class="signature-row">
            <div class="sig">
                <p class="sig-line">_________________________</p>
                <p class="sig-label"><?php echo __('Transferor Signature');?></p>
            </div>
            <div class="sig center">
                <p><strong><?php echo __('Date');?>:</strong> <?php echo h($requestDate);?></p>
            </div>
            <div class="sig right">
                <p class="sig-line">_________________________</p>
                <p class="sig-label"><?php echo __('Transferee Signature');?></p>
            </div>
        </div>

        <div class="signature-row second">
            <div class="sig">
                <p class="sig-line">_________________________</p>
                <p class="sig-label"><?php echo __('Operations Manager');?></p>
                <p class="sig-note"><?php echo h($agencyInfo['name']);?></p>
            </div>
            <div class="sig right">
                <p class="sig-line">_________________________</p>
                <p class="sig-label"><?php echo __('Accounts / Admin');?></p>
                <p class="sig-note"><?php echo h($agencyInfo['user_name']);?></p>
            </div>
        </div>

        <div class="qr-section">
            <img src="<?php echo h($qrUrl);?>" alt="<?php echo __('Agreement QR Code');?>">
            <div class="witness-row">
                <div>
                    <p><strong>_________________________</strong></p>
                    <p><strong><?php echo __('Witness 1');?></strong></p>
                </div>
                <p class="center"><?php echo __('Scan for full summary of parties, property category, and upcoming payment cycle status.');?></p>
                <div class="text-right">
                    <p><strong>_________________________</strong></p>
                    <p><strong><?php echo __('Witness 2');?></strong></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
