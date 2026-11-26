<?php
$dealRow = !empty($deal) ? $deal : array();
$dealInfo = !empty($dealRow['MyDeal']) ? $dealRow['MyDeal'] : array();
$propertyInfo = !empty($dealRow['Property']) ? $dealRow['Property'] : array();
$clientInfo = !empty($dealRow['Client']) ? $dealRow['Client'] : array();
$portalLetterTypeArr = !empty($portalLetterTypeArr) ? (array)$portalLetterTypeArr : array();
$portalAutoLetterTypeArr = !empty($portalAutoLetterTypeArr) ? (array)$portalAutoLetterTypeArr : array();
$letterCount = !empty($letters) ? count($letters) : 0;
?>

<style type="text/css">
.wv-letters-page{background:transparent;margin:0;padding:0;touch-action:auto;overflow-x:auto;-webkit-overflow-scrolling:touch;width:100%;}
.wv-letters-hero{background:#ffffff;border:1px solid #e2e8f0;border-top:4px solid #0284c7;border-radius:16px;box-shadow:0 4px 16px rgba(15,23,42,.04);color:#0f172a;margin-bottom:16px;overflow:hidden;padding:22px 24px;position:relative;}
.wv-letters-hero-inner{align-items:center;display:flex;gap:16px;justify-content:space-between;position:relative;z-index:2;}
.wv-letters-kicker{color:#0284c7!important;font-size:11px;font-weight:900;letter-spacing:.7px;margin-bottom:5px;text-transform:uppercase;}
.wv-letters-hero h2{color:#0f172a!important;font-size:26px;font-weight:900;line-height:1.2;margin:0;}
.wv-letters-hero h2 span{color:#0284c7!important;}
.wv-letters-meta{align-items:center;color:#475569!important;display:flex;flex-wrap:wrap;font-size:12px;font-weight:800;gap:8px;margin-top:10px;}
.wv-letters-meta span{background:#f8fafc;border:1px solid #e2e8f0;border-radius:999px;color:#334155!important;display:inline-flex;gap:6px;padding:6px 12px;}
.wv-letters-count{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 2px 8px rgba(15,23,42,.04);color:#0f172a;min-width:118px;padding:14px;text-align:center;}
.wv-letters-count strong{color:#0284c7!important;display:block;font-size:28px;font-weight:900;line-height:1;}
.wv-letters-count span{color:#64748b!important;font-size:11px;font-weight:900;letter-spacing:.45px;text-transform:uppercase;}
.wv-letters-toolbar{align-items:center;display:flex;gap:12px;justify-content:space-between;margin-bottom:14px;}
.wv-letters-back{background:#ffffff!important;border:1px solid #e2e8f0!important;border-radius:999px!important;box-shadow:0 2px 8px rgba(15,23,42,.04);color:#0f172a!important;font-size:12px!important;font-weight:900!important;padding:10px 16px!important;text-transform:uppercase;transition:all 0.15s;}
.wv-letters-back:hover{background:#f8fafc!important;border-color:#cbd5e1!important;}
.wv-letters-back i,.wv-letters-back span{color:#0f172a!important;}
.wv-letters-note{color:#64748b!important;font-size:12px;font-weight:800;}
.wv-letters-card{background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 4px 16px rgba(15,23,42,.04);overflow:hidden;}
.wv-letters-card-head{align-items:center;background:#ffffff;border-bottom:1px solid #e2e8f0;display:flex;gap:12px;justify-content:space-between;padding:16px 20px;}
.wv-letters-card-title{color:#0f172a!important;font-size:17px;font-weight:900;margin:0;}
.wv-letters-card-sub{color:#64748b!important;font-size:12px;font-weight:700;margin-top:3px;}
.wv-letters-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;touch-action:pan-x pan-y;}
.wv-letters-table{margin:0!important;min-width:760px;}
.wv-letters-table thead tr{background:#f8fafc!important;}
.wv-letters-table th{border:0!important;border-bottom:2px solid #e2e8f0!important;color:#475569!important;font-size:11px!important;font-weight:900!important;letter-spacing:.4px;padding:13px 14px!important;text-transform:uppercase;white-space:nowrap;}
.wv-letters-table td{border-top:1px solid #f1f5f9!important;border-bottom:1px solid #f1f5f9!important;color:#1e293b!important;font-size:13px!important;font-weight:700!important;padding:13px 14px!important;vertical-align:middle!important;}
.wv-letters-table tbody tr:hover td{background:#f8fafc!important;}
.wv-letter-ref{background:#f0f9ff;border:1px solid #bae6fd;border-radius:999px;color:#0369a1!important;display:inline-block;font-weight:900;padding:4px 10px;font-size:11px;}
.wv-letter-type{color:#0f172a!important;font-weight:800;}
.wv-letter-auto{background:#ecfdf5!important;border:1px solid #a7f3d0!important;border-radius:999px;color:#065f46!important;font-size:10px!important;font-weight:900!important;margin-left:5px;padding:3px 7px!important;text-transform:uppercase;}
.wv-letter-status{border-radius:999px;display:inline-block;font-size:10px;font-weight:900;letter-spacing:.35px;padding:5px 10px;text-transform:uppercase;}
.wv-letter-status.is-published{background:#ecfdf5;color:#047857!important;border:1px solid #a7f3d0;}
.wv-letter-status.is-draft{background:#f1f5f9;color:#475569!important;border:1px solid #e2e8f0;}
.wv-letter-actions{display:flex;gap:7px;justify-content:flex-end;white-space:nowrap;}
.wv-letter-icon-btn{align-items:center;border:1px solid #e2e8f0!important;border-radius:8px!important;background:#ffffff!important;color:#0284c7!important;box-shadow:0 2px 6px rgba(15,23,42,.05);display:inline-flex;height:34px;justify-content:center;padding:0!important;text-decoration:none!important;width:34px;transition:all 0.15s;}
.wv-letter-icon-btn:hover{background:#f0f9ff!important;border-color:#bae6fd!important;}
.wv-letter-icon-btn i{font-size:14px!important;color:inherit!important;}
.wv-letter-empty{color:#64748b!important;font-size:13px!important;font-weight:800!important;padding:32px!important;text-align:center;}
@media(max-width:767px){
  .wv-letters-hero-inner,.wv-letters-card-head,.wv-letters-toolbar{align-items:flex-start;display:block;}
  .wv-letters-hero h2{font-size:22px;}
  .wv-letters-count{margin-top:14px;text-align:left;}
  .wv-letters-note{margin-top:10px;}
  .wv-letters-table{min-width:680px;}
}
</style>

<div class="wv-letters-page">
    <?php echo $this->Session->flash();?>

    <div class="wv-letters-hero">
        <div class="wv-letters-hero-inner">
            <div>
                <div class="wv-letters-kicker"><?php echo __('Client Portal');?></div>
                <h2><?php echo __('Deal');?> <span><?php echo __('Letters');?></span></h2>
                <div class="wv-letters-meta">
                    <span><i class="fa fa-id-card-o"></i><?php echo h(!empty($dealInfo['invoice_no']) ? $dealInfo['invoice_no'] : '-'); ?></span>
                    <span><i class="fa fa-building"></i><?php echo h(!empty($propertyInfo['name']) ? $propertyInfo['name'] : '-'); ?></span>
                    <span><i class="fa fa-user"></i><?php echo h(!empty($clientInfo['name']) ? $clientInfo['name'] : '-'); ?></span>
                </div>
            </div>
            <div class="wv-letters-count">
                <strong><?php echo (int)$letterCount;?></strong>
                <span><?php echo __('Issued Letters');?></span>
            </div>
        </div>
    </div>

    <div class="wv-letters-toolbar">
        <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i>&nbsp;'.__('Back To Deals'),array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'index'),array('class'=>'btn wv-letters-back','escape'=>false));?>
        <div class="wv-letters-note"><?php echo __('Print issued deal letters from this ledger.');?></div>
    </div>

    <div class="wv-letters-card">
        <div class="wv-letters-card-head">
            <div>
                <h3 class="wv-letters-card-title"><?php echo __('Issued Letter Records');?></h3>
                <div class="wv-letters-card-sub"><?php echo __('Issued letters for this deal with reference, date, type and status.');?></div>
            </div>
        </div>
        <div class="wv-letters-table-wrap">
                <table class="table wv-letters-table">
                    <thead>
                        <tr>
                            <th style="width:50px;"><?php echo __('#'); ?></th>
                            <th style="width:140px;"><?php echo __('Ref No'); ?></th>
                            <th style="width:120px;"><?php echo __('Date'); ?></th>
                            <th style="width:180px;"><?php echo __('Type'); ?></th>
                            <th><?php echo __('Title'); ?></th>
                            <th style="width:110px;"><?php echo __('Status'); ?></th>
                            <th style="width:110px;"><?php echo __('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($letters)){ $sr=1; foreach($letters as $row){
                            $letter = !empty($row['Realestateletter']) ? $row['Realestateletter'] : array();
                            $letterId = !empty($letter['id']) ? (int)$letter['id'] : 0;
                            $typeCode = !empty($letter['letter_type']) ? (string)$letter['letter_type'] : '';
                            $typeLabel = isset($portalLetterTypeArr[$typeCode]) ? $portalLetterTypeArr[$typeCode] : $typeCode;
                            $isAuto = isset($portalAutoLetterTypeArr[$typeCode]);
                            $status = !empty($letter['status']) ? trim((string)$letter['status']) : '';
                            $statusLabel = ($status === 'Published') ? __('Issued') : ($status !== '' ? $status : '-');
                            $statusClass = ($status === 'Published') ? 'is-published' : 'is-draft';
                            ?>
                            <tr>
                                <td><?php echo $sr++; ?></td>
                                <td><span class="wv-letter-ref"><?php echo h(!empty($letter['reference_no']) ? $letter['reference_no'] : '-'); ?></span></td>
                                <td><?php echo !empty($letter['letter_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,$letter['letter_date']) : '-'; ?></td>
                                <td>
                                    <span class="wv-letter-type"><?php echo h($typeLabel); ?></span>
                                    <?php if($isAuto){ ?><span class="wv-letter-auto"><?php echo __('Auto'); ?></span><?php } ?>
                                </td>
                                <td><?php echo h(!empty($letter['title']) ? $letter['title'] : '-'); ?></td>
                                <td>
                                    <span class="wv-letter-status <?php echo $statusClass; ?>">
                                        <?php echo h($statusLabel); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="wv-letter-actions">
                                    <?php if($letterId > 0){ ?>
                                        <?php echo $this->Html->link('<i class="fa fa-print"></i>',array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'printletter',$dealId,$letterId),array('class'=>'wv-letter-icon-btn print','title'=>__('Print Letter'),'target'=>'_blank','escape'=>false));?>
                                    <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php }} else { ?>
                            <tr>
                                <td colspan="7" class="wv-letter-empty"><?php echo __('No issued letters available for this deal.'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
        </div>
    </div>
</div>
