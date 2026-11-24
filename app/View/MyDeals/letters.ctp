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
.wv-letters-page{background:#f4f7fb;margin:-8px -5px 0;padding:18px;}
.wv-letters-hero{background:linear-gradient(135deg,#07152a 0%,#0d233f 72%,#ff0a46 165%);border-radius:16px;box-shadow:0 18px 38px rgba(15,23,42,.16);color:#fff;margin-bottom:16px;overflow:hidden;padding:22px;position:relative;}
.wv-letters-hero:after{background:radial-gradient(circle at 92% 4%,rgba(124,249,255,.24),rgba(124,249,255,0) 38%);content:"";inset:0;position:absolute;}
.wv-letters-hero-inner{align-items:center;display:flex;gap:16px;justify-content:space-between;position:relative;z-index:2;}
.wv-letters-kicker{color:#7cf9ff!important;font-size:11px;font-weight:900;letter-spacing:.7px;margin-bottom:5px;text-transform:uppercase;}
.wv-letters-hero h2{color:#fff!important;font-size:28px;font-weight:900;line-height:1.15;margin:0;text-shadow:0 2px 12px rgba(0,0,0,.24);}
.wv-letters-hero h2 span{color:#7cf9ff!important;}
.wv-letters-meta{align-items:center;color:#dce8f7!important;display:flex;flex-wrap:wrap;font-size:12px;font-weight:800;gap:8px;margin-top:10px;}
.wv-letters-meta span{background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.16);border-radius:999px;color:#fff!important;display:inline-flex;gap:6px;padding:7px 10px;}
.wv-letters-count{background:#fff;border:1px solid rgba(255,255,255,.26);border-radius:14px;box-shadow:0 12px 26px rgba(0,0,0,.16);color:#07152a;min-width:118px;padding:14px;text-align:center;}
.wv-letters-count strong{color:#ff0a46!important;display:block;font-size:28px;font-weight:900;line-height:1;}
.wv-letters-count span{color:#64748b!important;font-size:11px;font-weight:900;letter-spacing:.45px;text-transform:uppercase;}
.wv-letters-toolbar{align-items:center;display:flex;gap:12px;justify-content:space-between;margin-bottom:14px;}
.wv-letters-back{background:#fff!important;border:1px solid #dfe9f5!important;border-radius:999px!important;box-shadow:0 10px 22px rgba(15,23,42,.07);color:#07152a!important;font-size:12px!important;font-weight:900!important;padding:10px 14px!important;text-transform:uppercase;}
.wv-letters-back i,.wv-letters-back span{color:#07152a!important;}
.wv-letters-note{color:#64748b!important;font-size:12px;font-weight:800;}
.wv-letters-card{background:#fff;border:1px solid #dfe9f5;border-radius:16px;box-shadow:0 14px 30px rgba(15,23,42,.08);overflow:hidden;}
.wv-letters-card-head{align-items:center;background:#fbfdff;border-bottom:1px solid #edf2f7;display:flex;gap:12px;justify-content:space-between;padding:16px 18px;}
.wv-letters-card-title{color:#07152a!important;font-size:17px;font-weight:900;margin:0;}
.wv-letters-card-sub{color:#64748b!important;font-size:12px;font-weight:800;margin-top:3px;}
.wv-letters-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;}
.wv-letters-table{margin:0!important;min-width:850px;}
.wv-letters-table thead tr{background:#07152a!important;}
.wv-letters-table th{border:0!important;color:#fff!important;font-size:11px!important;font-weight:800!important;letter-spacing:.3px;padding:13px 14px!important;text-transform:uppercase;white-space:nowrap;}
.wv-letters-table td{border-color:#edf2f7!important;color:#334155!important;font-size:12px!important;font-weight:800!important;padding:13px 14px!important;vertical-align:middle!important;}
.wv-letters-table tbody tr:hover td{background:#fbfdff!important;}
.wv-letter-ref{background:#f1f7ff;border:1px solid #dcecff;border-radius:999px;color:#07152a!important;display:inline-block;font-weight:900;padding:5px 9px;}
.wv-letter-type{color:#07152a!important;font-weight:900;}
.wv-letter-auto{background:#e6fbff!important;border:1px solid #b9f4ff!important;border-radius:999px;color:#036173!important;font-size:10px!important;font-weight:900!important;margin-left:5px;padding:4px 7px!important;text-transform:uppercase;}
.wv-letter-status{border-radius:999px;display:inline-block;font-size:10px;font-weight:900;letter-spacing:.35px;padding:6px 9px;text-transform:uppercase;}
.wv-letter-status.is-published{background:#ecfdf5;color:#047857!important;}
.wv-letter-status.is-draft{background:#f1f5f9;color:#475569!important;}
.wv-letter-actions{display:flex;gap:7px;justify-content:flex-end;white-space:nowrap;}
.wv-letter-icon-btn{align-items:center;border:0!important;border-radius:10px!important;box-shadow:0 9px 18px rgba(15,23,42,.10);display:inline-flex;height:34px;justify-content:center;padding:0!important;text-decoration:none!important;width:34px;}
.wv-letter-icon-btn i,.wv-letter-icon-btn span{color:inherit!important;font-size:14px!important;margin:0!important;}
.wv-letter-icon-btn.print{background:#ff0a46!important;color:#fff!important;}
.wv-letter-empty{color:#64748b!important;font-size:13px!important;font-weight:900!important;padding:28px!important;text-align:center;}
@media(max-width:767px){.wv-letters-page{margin:0;padding:12px;}.wv-letters-hero-inner,.wv-letters-card-head,.wv-letters-toolbar{align-items:flex-start;display:block;}.wv-letters-hero h2{font-size:24px;}.wv-letters-count{margin-top:14px;text-align:left;}.wv-letters-note{margin-top:10px;}.wv-letters-table{min-width:780px;}}
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
