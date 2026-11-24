<?php
 $this->Js->JqueryEngine->jQueryObject = 'jQuery';
// Paginator options
$this->Paginator->options(array(
  'update' => '#resultDiv',
  'evalScripts' => true,
));
$walletAgentNameLocal = isset($walletAgentName) ? trim((string)$walletAgentName) : '';
$walletAgentMobileLocal = isset($walletAgentMobile) ? trim((string)$walletAgentMobile) : '';
$walletAgentPhotoLocal = isset($walletAgentPhoto) ? trim((string)$walletAgentPhoto) : '';
$walletAgentEmailLocal = isset($walletAgentEmail) ? trim((string)$walletAgentEmail) : '';
$walletAgentAddressLocal = isset($walletAgentAddress) ? trim((string)$walletAgentAddress) : '';
$walletAgentPhoneLocal = isset($walletAgentPhone) ? trim((string)$walletAgentPhone) : '';
$activeWalletTabLocal = isset($activeWalletTab) ? strtolower(trim((string)$activeWalletTab)) : 'deal';
$walletAgentCardsLocal = !empty($walletAgentCards) ? (array)$walletAgentCards : array();
$currentAgentSummaryLocal = !empty($currentAgentSummary) ? (array)$currentAgentSummary : array();
$commissionLedgerRowsLocal = !empty($commissionLedgerRows) ? (array)$commissionLedgerRows : array();
if(!in_array($activeWalletTabLocal, array('deal','paid'), true))
{
    $activeWalletTabLocal = 'deal';
}
$walletPageTitle = __('Agent Wallet History');
if($walletAgentNameLocal !== '')
{
    $walletPageTitle .= ' - '.$walletAgentNameLocal;
}
?>
<div id="resultDiv">
<style type="text/css">
.wallet-kpi-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin:0;}
.wallet-kpi-card{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);border-radius:12px;padding:14px 16px;box-shadow:none;}
.wallet-kpi-label{display:block;color:rgba(255,255,255,.78);font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;}
.wallet-kpi-value{display:block;color:#ffffff;font-size:28px;font-weight:900;line-height:1.15;margin-top:8px;}
.wallet-kpi-meta{display:block;color:rgba(255,255,255,.82);font-size:12px;line-height:1.45;margin-top:6px;}
.wallet-agent-card-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin:0 0 18px;}
.wallet-agent-card{background:#fff;border:1px solid #dbe6f4;border-radius:14px;padding:16px;box-shadow:0 14px 32px rgba(15,23,42,.07);}
.wallet-agent-card-head{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
.wallet-agent-photo{width:58px;height:58px;border-radius:16px;overflow:hidden;background:#edf4ff;display:flex;align-items:center;justify-content:center;border:1px solid #dbe6f4;}
.wallet-agent-photo img{width:100%;height:100%;object-fit:cover;}
.wallet-agent-photo span{font-size:22px;color:#335d92;font-weight:900;}
.wallet-agent-name{color:#0b2240;font-size:18px;font-weight:900;line-height:1.2;margin:0;}
.wallet-agent-sub{color:#5f7288;font-size:12px;line-height:1.45;margin-top:4px;}
.wallet-agent-metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:12px 0;}
.wallet-agent-metric{background:#f8fbff;border:1px solid #e1e8f2;border-radius:10px;padding:10px;}
.wallet-agent-metric strong{display:block;color:#0b2240;font-size:18px;font-weight:900;}
.wallet-agent-metric span{display:block;color:#60748c;font-size:11px;font-weight:700;text-transform:uppercase;margin-bottom:4px;}
.wallet-agent-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;}
.wallet-hero{background:linear-gradient(135deg,#112643 0%,#1d4a7a 55%,#3b2c63 100%);border-radius:18px;color:#fff;padding:18px 20px;margin:0 0 18px;box-shadow:0 18px 40px rgba(15,23,42,.18);}
.wallet-hero-grid{display:grid;grid-template-columns:minmax(0,1.3fr) minmax(0,1fr);gap:18px;align-items:start;}
.wallet-hero-head{display:flex;align-items:flex-start;gap:16px;}
.wallet-agent-banner{width:2in;height:2in;min-width:2in;border-radius:16px;overflow:hidden;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.16);box-shadow:0 12px 30px rgba(0,0,0,.14);}
.wallet-agent-banner img{width:100%;height:100%;object-fit:cover;display:block;}
.wallet-agent-banner-empty{width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#ffffff;font-size:34px;font-weight:900;letter-spacing:0;background:linear-gradient(135deg,rgba(255,255,255,.18),rgba(255,255,255,.05));}
.wallet-hero-title{font-size:30px;font-weight:900;line-height:1.1;margin:0 0 8px;}
.wallet-hero-subline{font-size:14px;line-height:1.5;color:rgba(255,255,255,.92);font-weight:700;margin:0 0 6px;}
.wallet-hero-meta{margin-top:8px;display:grid;gap:4px;}
.wallet-hero-meta div{font-size:13px;line-height:1.55;color:rgba(255,255,255,.88);}
.wallet-hero-copy{font-size:13px;line-height:1.55;color:rgba(255,255,255,.86);}
.wallet-hero-chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;}
.wallet-hero-chip{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);border-radius:999px;color:#fff;padding:6px 12px;font-size:12px;font-weight:700;}
.wallet-agency-band{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:0;}
.wallet-agency-card{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.16);border-radius:12px;padding:14px;}
.wallet-agency-card h4{margin:0 0 6px;font-size:15px;font-weight:900;color:#ffffff;}
.wallet-agency-card div{font-size:12px;line-height:1.55;color:rgba(255,255,255,.86);}
.wallet-hero-summary{display:grid;grid-template-columns:1fr;gap:14px;}
.wallet-ledger-shell{background:#fff;border:1px solid #dbe6f4;border-radius:16px;padding:16px 16px 10px;box-shadow:0 14px 32px rgba(15,23,42,.07);}
.wallet-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin:0 0 12px;}
.wallet-toolbar-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.wallet-toolbar-actions .btn{min-width:118px;justify-content:center;}
.wallet-filter-bar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 12px;padding:12px;border:1px solid #dbe6f4;border-radius:12px;background:#f8fbff;}
.wallet-ledger-title{font-size:18px;font-weight:900;color:#0b2240;margin:0;}
.wallet-ledger-sub{font-size:12px;color:#60748c;margin:3px 0 0;}
.wallet-ledger-table th{background:#132a46 !important;color:#fff !important;border-color:#274564 !important;font-size:12px;vertical-align:middle;}
.wallet-ledger-table td{vertical-align:top;font-size:12px;line-height:1.45;color:#12263f;}
.wallet-ledger-table th:last-child,.wallet-ledger-table td:last-child{min-width:92px;width:92px;}
.wallet-ledger-table .text-success-strong{color:#0d8f53;font-weight:900;}
.wallet-ledger-table .text-danger-strong{color:#c43838;font-weight:900;}
.wallet-ledger-balance{font-weight:900;color:#0b2240;}
.wallet-entry-tag{display:inline-block;border-radius:999px;padding:4px 10px;font-size:11px;font-weight:800;line-height:1.2;}
.wallet-entry-credit{background:#e8fff3;color:#0d8f53;border:1px solid #bce8cf;}
.wallet-entry-debit{background:#fff1f1;color:#c43838;border:1px solid #f2c6c6;}
.wallet-ledger-detail strong,.wallet-ledger-detail div{color:#12263f;}
.wallet-action-icons{display:flex;align-items:center;gap:6px;flex-wrap:wrap;}
.wallet-action-icon{width:28px;height:28px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;padding:0;}
.wallet-hero-title,.wallet-hero-title a,.wallet-hero .wallet-hero-title{color:#ffffff !important;}
.wallet-hero-chip{color:#ffffff !important;}
.wallet-print-agent-box{margin:0 0 10px 0;padding:10px 12px;border:1px solid #d7e1ef;border-radius:10px;background:#f8fbff;text-align:center;}
.wallet-print-agent-box h3{margin:0;font-size:18px;font-weight:900;color:#17324f;}
.wallet-print-agent-box div{margin-top:4px;font-size:12px;color:#425466;}
.wallet-print-only{position:absolute;left:-99999px;top:auto;width:1100px;max-width:1100px;overflow:hidden;}
@media print{
 .wallet-print-hide{display:none !important;}
 .wallet-ledger-table th.wallet-print-hide,
 .wallet-ledger-table td.wallet-print-hide{display:none !important;}
 .wallet-print-only{position:static !important;left:auto !important;top:auto !important;width:auto !important;max-width:none !important;overflow:visible !important;display:block !important;}
}
@media (max-width: 1199px){
 .wallet-kpi-grid,.wallet-agent-card-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
 .wallet-hero-grid{grid-template-columns:1fr;}
 .wallet-agency-band{grid-template-columns:1fr 1fr;}
}
@media (max-width: 767px){
 .wallet-kpi-grid,.wallet-agent-card-grid,.wallet-agency-band{grid-template-columns:1fr;}
 .wallet-hero-head{flex-direction:column;}
 .wallet-agent-banner{width:min(100%,2in);min-width:0;height:auto;aspect-ratio:1/1;}
 .wallet-kpi-value{font-size:24px;}
 .wallet-hero-title{font-size:24px;}
}
</style>
<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo h($walletPageTitle);?></h1></div></div>
<div class="panel"><?php echo $this->Session->flash();?>
    <div class="panel-heading">
        <?php if($userType!='AGT'){?>
	<div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;'.__('Back'),array('controller'=>'Agents','action'=>'index'),array('escape'=>false,'class'=>'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-percent"></span>&nbsp;'.__('Commission Settings'),array('controller'=>'Ardaccounts','action'=>'settings','?'=>array('tab'=>'commission')),array('escape'=>false,'class'=>'btn btn-default'));?>
        </div>
	<?php }?>
	
    </div>
        <?php
        $isAgentView = (isset($id) && (int)$id>0);
        $pageParams = $this->Paginator->params();
        $limit = isset($pageParams['limit']) ? (int)$pageParams['limit'] : 20;
        $page = isset($pageParams['page']) ? (int)$pageParams['page'] : 1;
        $serialNo = 1*$limit*($page-1)+1;
        ?>
<?php echo $this->Session->flash();?>
<div id="printable_content">
<?php echo $this->Html->css('print');?>
    <?php if(!$isAgentView){ ?>
    <?php echo $this->element('pagination');?>
    <?php echo $this->Form->create(array('name'=>'deleteallfrm','action' => 'deleteall'));?>
    <div class="panel-body"></div>
    <div class="table-responsive">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <tr>
                    <th><?php echo $this->Paginator->sort('id', __('#'), array('direction' => 'desc'));?></th>
                    <th><?php echo $this->Paginator->sort('User.name', __('Name'), array('direction' => 'asc'));?></th>
                    <th><?php echo $this->Paginator->sort('amount', __('Amount'), array('direction' => 'asc'));?></th>
                    <th><?php echo $this->Paginator->sort('type', __('Type'), array('direction' => 'asc'));?></th>
                    <th><?php echo $this->Paginator->sort('date', __('Date & Time'), array('direction' => 'asc'));?></th>
                    <th><?php echo $this->Paginator->sort('AwalletHistory.remarks', __('Remarks'), array('direction' => 'asc'));?></th>
                    <th><?php echo __('Show Payment');?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php foreach ($record as $post):
                $url=$this->Html->url(array('controller'=>'awallet_histories','action'=>'view',$post['AwalletHistory']['deals_payment_id']));
                $agentLedgerUrl = $this->Html->url(array('controller'=>'AwalletHistories','action'=>'index',(int)$post['User']['id']));
                $agentPaidVoucherUrl = $this->Html->url(array('controller'=>'AwalletHistories','action'=>'index',(int)$post['User']['id'],'?' => array('tab' => 'paid')));?>
                <tr>
                    <td><?php echo $serialNo++; ?></td>
                    <td><?php echo h($post['User']['name']);?></td>
                    <td><?php echo $currency.$this->Number->format($post['AwalletHistory']['amount']);?></td>
                    <td><?php echo h($post['AwalletHistory']['type']);?></td>
                    <td><?php echo $this->Time->Format('d-M-Y h:i:s A',$post['AwalletHistory']['date']);?></td>
                    <td><?php echo nl2br(h(!empty($post['AwalletHistory']['remarks_display']) ? $post['AwalletHistory']['remarks_display'] : $post['AwalletHistory']['remarks']));?></td>
                    <td><?php if($post['AwalletHistory']['deals_payment_id']){ echo $this->Html->link('Show Payment','javascript:void(0);',array('onclick'=>"show_modal('$url');",'escape'=>false));}?></td>
                    <td>
                        <a href="<?php echo $agentLedgerUrl;?>" class="btn btn-xs btn-primary"><span class="fa fa-list-alt"></span>&nbsp;<?php echo __('Open Ledger');?></a>
                        <a href="<?php echo $agentPaidVoucherUrl;?>" class="btn btn-xs btn-default"><span class="fa fa-money"></span>&nbsp;<?php echo __('Paid Vouchers');?></a>
                    </td>
                </tr>
                <?php endforeach;?>
                <?php unset($post);?>
                <?php if($aWallet){?>
                <tr>
                    <th><?php echo __('Total Credit');?></th>
                    <th><?php echo __('Total Debit');?> </th>
                    <th colspan="6"><?php echo __('Balance');?></th>
                </tr>
                <tr>
                    <th><?php echo$currency.$this->Number->format($aWallet['Awallet']['credit']);?></th>
                    <th><?php echo$currency.$this->Number->format($aWallet['Awallet']['debit']);?></th>
                    <th colspan="6"><?php echo$currency.$this->Number->format($aWallet['Awallet']['balance']);?></th>
                </tr>
                <?php }?>
            </table>
        </div>
    </div>
    <?php echo $this->Form->end();?>
    <?php echo $this->element('pagination');?>
    <?php } ?>
		<?php if($isAgentView){ ?>
		<?php
		$summaryAgencyList = isset($currentAgentSummaryLocal['agency_labels']) ? (array)$currentAgentSummaryLocal['agency_labels'] : array();
		$summaryAgencyText = !empty($currentAgentSummaryLocal['agency_summary']) ? (string)$currentAgentSummaryLocal['agency_summary'] : __('No agency linked');
		$walletAgentPhotoUrl = '';
		if($walletAgentPhotoLocal !== '')
		{
		    foreach(array('user_thumb/'.$walletAgentPhotoLocal,'user/'.$walletAgentPhotoLocal,$walletAgentPhotoLocal) as $candidateImage)
		    {
			$imagePath = WWW_ROOT.'img'.DS.str_replace('/', DS, trim((string)$candidateImage, '/'));
			if(file_exists($imagePath))
			{
			    $walletAgentPhotoUrl = $this->Html->url('/img/'.trim((string)$candidateImage, '/'));
			    break;
			}
		    }
		}
		$walletAgentInitials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)$walletAgentNameLocal), 0, 2));
		if($walletAgentInitials === '')
		{
		    $walletAgentInitials = 'AG';
		}
		?>
		<div class="wallet-hero">
		    <div class="wallet-hero-grid">
			<div>
			    <div class="wallet-hero-head">
				<div class="wallet-agent-banner">
				    <?php if($walletAgentPhotoUrl !== ''){ ?>
				    <img src="<?php echo h($walletAgentPhotoUrl);?>" alt="<?php echo h($walletAgentNameLocal !== '' ? $walletAgentNameLocal : __('Agent'));?>" />
				    <?php }else{ ?>
				    <div class="wallet-agent-banner-empty"><?php echo h($walletAgentInitials);?></div>
				    <?php } ?>
				</div>
				<div>
				    <h2 class="wallet-hero-title"><?php echo h($walletAgentNameLocal !== '' ? $walletAgentNameLocal : __('Agent Wallet Ledger'));?></h2>
				    <?php if($walletAgentMobileLocal !== ''){ ?><div class="wallet-hero-subline"><?php echo h($walletAgentMobileLocal);?></div><?php } ?>
				    <?php if($walletAgentEmailLocal !== '' || $walletAgentAddressLocal !== '' || ($walletAgentPhoneLocal !== '' && $walletAgentPhoneLocal !== $walletAgentMobileLocal)){ ?>
				    <div class="wallet-hero-meta">
					<?php if($walletAgentPhoneLocal !== '' && $walletAgentPhoneLocal !== $walletAgentMobileLocal){ ?><div><strong><?php echo __('Phone');?>:</strong> <?php echo h($walletAgentPhoneLocal);?></div><?php } ?>
					<?php if($walletAgentEmailLocal !== ''){ ?><div><strong><?php echo __('Email');?>:</strong> <?php echo h($walletAgentEmailLocal);?></div><?php } ?>
					<?php if($walletAgentAddressLocal !== ''){ ?><div><strong><?php echo __('Address');?>:</strong> <?php echo h($walletAgentAddressLocal);?></div><?php } ?>
				    </div>
				    <?php } ?>
				    <div class="wallet-hero-chips">
					<?php if($summaryAgencyText !== ''){ ?><span class="wallet-hero-chip"><?php echo __('Linked Agencies');?>: <?php echo h($summaryAgencyText);?></span><?php } ?>
				    </div>
				</div>
			    </div>
			</div>
			<div class="wallet-hero-summary">
			    <div class="wallet-kpi-grid">
				<div class="wallet-kpi-card"><span class="wallet-kpi-label"><?php echo __('Deal Value');?></span><strong class="wallet-kpi-value"><?php echo $currency.$this->Number->format(isset($currentAgentSummaryLocal['total_deal_amount']) ? $currentAgentSummaryLocal['total_deal_amount'] : 0);?></strong><span class="wallet-kpi-meta"><?php echo __('Total Deals');?>: <?php echo (int)(isset($currentAgentSummaryLocal['total_deals']) ? $currentAgentSummaryLocal['total_deals'] : 0);?></span></div>
				<div class="wallet-kpi-card"><span class="wallet-kpi-label"><?php echo __('Installments Paid');?></span><strong class="wallet-kpi-value"><?php echo $currency.$this->Number->format(isset($currentAgentSummaryLocal['total_installment_paid']) ? $currentAgentSummaryLocal['total_installment_paid'] : 0);?></strong><span class="wallet-kpi-meta"><?php echo __('Receipts count');?>: <?php echo (int)(isset($currentAgentSummaryLocal['total_installment_count']) ? $currentAgentSummaryLocal['total_installment_count'] : 0);?></span></div>
				<div class="wallet-kpi-card"><span class="wallet-kpi-label"><?php echo __('Paid Commission');?></span><strong class="wallet-kpi-value"><?php echo $currency.$this->Number->format(isset($currentAgentSummaryLocal['total_paid_commission']) ? $currentAgentSummaryLocal['total_paid_commission'] : 0);?></strong><span class="wallet-kpi-meta"><?php echo __('Wallet Debit Total');?>: <?php echo $currency.$this->Number->format(isset($currentAgentSummaryLocal['wallet_debit_total']) ? $currentAgentSummaryLocal['wallet_debit_total'] : 0);?></span></div>
				<div class="wallet-kpi-card"><span class="wallet-kpi-label"><?php echo __('Wallet Due');?></span><strong class="wallet-kpi-value"><?php echo $currency.$this->Number->format(isset($currentAgentSummaryLocal['commission_due']) ? $currentAgentSummaryLocal['commission_due'] : 0);?></strong><span class="wallet-kpi-meta"><?php echo __('Wallet Credit Total');?>: <?php echo $currency.$this->Number->format(isset($currentAgentSummaryLocal['wallet_credit_total']) ? $currentAgentSummaryLocal['wallet_credit_total'] : 0);?></span></div>
			    </div>
			</div>
		    </div>
		</div>
		<div class="panel panel-default" style="margin-top:15px;">
		    <div class="panel-heading">
			<strong><?php echo __('Deal-wise Commission Details');?></strong>
			<?php if($walletAgentNameLocal !== ''){ ?><span class="pull-right"><?php echo __('Agent');?>: <strong><?php echo h($walletAgentNameLocal);?></strong></span><?php } ?>
		    </div>
		    <div class="panel-body">
			<?php
			$agencyFilterOptions = array(0 => __('All Agencies'));
			if(!empty($projectFilterOptions))
			{
			    $agencyFilterOptions += (array)$projectFilterOptions;
			}
			$payoutProjectOptionsLocal = !empty($payoutProjectOptions) ? (array)$payoutProjectOptions : array();
			$payoutAgencyOptionCount = count($payoutProjectOptionsLocal);
			$isSinglePayoutAgency = ($payoutAgencyOptionCount === 1);
			$payoutSourceLedgerByProjectLocal = !empty($payoutSourceLedgerByProject) ? (array)$payoutSourceLedgerByProject : array();
			$payoutPaymentTypesLocal = !empty($payoutPaymentTypes) ? (array)$payoutPaymentTypes : array();
			$payoutForm = !empty($commissionPayoutDefaults) ? (array)$commissionPayoutDefaults : array();
			$payoutProjectId = isset($payoutForm['project_id']) ? (int)$payoutForm['project_id'] : 0;
			$payoutLedgerId = isset($payoutForm['ledger_id']) ? (int)$payoutForm['ledger_id'] : 0;
			$payoutPaymentTypeId = isset($payoutForm['paymenttype_id']) ? (int)$payoutForm['paymenttype_id'] : 0;
			$payoutAmount = isset($payoutForm['amount']) ? (string)$payoutForm['amount'] : '';
			$payoutEntryDate = isset($payoutForm['entry_date']) ? (string)$payoutForm['entry_date'] : date('Y-m-d');
			$payoutRemarks = isset($payoutForm['remarks']) ? (string)$payoutForm['remarks'] : '';
			$payoutLedgerOptions = array();
			if($payoutProjectId > 0 && !empty($payoutSourceLedgerByProjectLocal[$payoutProjectId]))
			{
			    $payoutLedgerOptions = (array)$payoutSourceLedgerByProjectLocal[$payoutProjectId];
			}
			elseif(!empty($payoutSourceLedgerByProjectLocal))
			{
			    $firstProjectLedger = reset($payoutSourceLedgerByProjectLocal);
			    if(is_array($firstProjectLedger))
			    {
				$payoutLedgerOptions = $firstProjectLedger;
			    }
			}
			if($payoutLedgerId <= 0 && !empty($payoutLedgerOptions))
			{
			    $payoutLedgerKeys = array_keys($payoutLedgerOptions);
			    if(!empty($payoutLedgerKeys))
			    {
				$payoutLedgerId = (int)$payoutLedgerKeys[0];
			    }
			}
			if($isSinglePayoutAgency && $payoutProjectId <= 0)
			{
			    $singleAgencyKeys = array_keys($payoutProjectOptionsLocal);
			    if(!empty($singleAgencyKeys))
			    {
				$payoutProjectId = (int)$singleAgencyKeys[0];
			    }
			}
			$resetFilterUrl = $this->Html->url(array('controller'=>'AwalletHistories','action'=>'index',$id));
			?>
			<?php if(!empty($payoutCanManage)){ ?>
			<div class="modal fade" id="commissionPayoutModal" tabindex="-1" role="dialog" aria-labelledby="commissionPayoutModalLabel">
			    <div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
				    <?php echo $this->Form->create(false,array('url'=>array('controller'=>'AwalletHistories','action'=>'index',$id),'class'=>'commission-payout-form'));?>
				    <input type="hidden" name="data[CommissionPayout][submit]" value="1" />
				    <input type="hidden" name="data[CommissionPayout][filter_project_id]" value="<?php echo (int)$selectedProjectId;?>" />
				    <input type="hidden" name="data[CommissionPayout][filter_start]" value="<?php echo h((string)$dealFilterStart);?>" />
				    <input type="hidden" name="data[CommissionPayout][filter_end]" value="<?php echo h((string)$dealFilterEnd);?>" />
				    <input type="hidden" name="data[CommissionPayout][filter_tab]" value="<?php echo h($activeWalletTabLocal);?>" />
				    <div class="modal-header" style="background:#f8f5e8;">
					<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo __('Close');?>"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="commissionPayoutModalLabel"><?php echo __('Paid Commission (Wallet Debit + Expense)');?></h4>
				    </div>
				    <div class="modal-body">
					<div class="row" style="margin-bottom:10px;">
					    <div class="col-md-4 col-sm-6">
						<label><?php echo __('Agency');?></label>
						<?php if($isSinglePayoutAgency && $payoutProjectId > 0 && !empty($payoutProjectOptionsLocal[$payoutProjectId])){ ?>
						<input type="hidden" name="data[CommissionPayout][project_id]" id="CommissionPayoutProjectId" value="<?php echo (int)$payoutProjectId;?>" />
						<input type="text" class="form-control" value="<?php echo h($payoutProjectOptionsLocal[$payoutProjectId]);?>" readonly="readonly" />
						<?php }else{ ?>
						<select name="data[CommissionPayout][project_id]" id="CommissionPayoutProjectId" class="form-control">
						    <?php foreach((array)$payoutProjectOptionsLocal as $pProjectId => $pProjectName){ ?>
						    <option value="<?php echo (int)$pProjectId;?>" <?php echo ((int)$payoutProjectId === (int)$pProjectId) ? 'selected="selected"' : '';?>><?php echo h($pProjectName);?></option>
						    <?php } ?>
						</select>
						<?php } ?>
					    </div>
					    <div class="col-md-4 col-sm-6">
						<label><?php echo __('From Account');?></label>
						<select name="data[CommissionPayout][ledger_id]" id="CommissionPayoutLedgerId" class="form-control">
						    <?php foreach((array)$payoutLedgerOptions as $ledgerKey => $ledgerLabel){ ?>
						    <option value="<?php echo (int)$ledgerKey;?>" <?php echo ((int)$payoutLedgerId === (int)$ledgerKey) ? 'selected="selected"' : '';?>><?php echo h($ledgerLabel);?></option>
						    <?php } ?>
						</select>
					    </div>
					    <div class="col-md-4 col-sm-6">
						<label><?php echo __('Payment Type');?></label>
						<select name="data[CommissionPayout][paymenttype_id]" class="form-control">
						    <?php foreach((array)$payoutPaymentTypesLocal as $payTypeId => $payTypeName){ ?>
						    <option value="<?php echo (int)$payTypeId;?>" <?php echo ((int)$payoutPaymentTypeId === (int)$payTypeId) ? 'selected="selected"' : '';?>><?php echo h($payTypeName);?></option>
						    <?php } ?>
						</select>
					    </div>
					</div>
					<div class="row" style="margin-bottom:10px;">
					    <div class="col-md-3 col-sm-6">
						<label><?php echo __('Amount');?></label>
						<input type="number" min="0" step="0.01" name="data[CommissionPayout][amount]" value="<?php echo h($payoutAmount);?>" class="form-control" />
					    </div>
					    <div class="col-md-3 col-sm-6">
						<label><?php echo __('Date');?></label>
						<input type="date" name="data[CommissionPayout][entry_date]" value="<?php echo h($payoutEntryDate);?>" class="form-control" />
					    </div>
					    <div class="col-md-6 col-sm-12">
						<label><?php echo __('Remarks');?></label>
						<input type="text" name="data[CommissionPayout][remarks]" value="<?php echo h($payoutRemarks);?>" class="form-control" placeholder="<?php echo __('Remarks');?>"/>
					    </div>
					</div>
				    </div>
				    <div class="modal-footer">
					<span class="pull-left" style="color:#5a5a5a;padding-top:8px;">
					    <?php echo __('Current Wallet Balance');?>:
					    <strong><?php echo !empty($aWallet['Awallet']['balance']) ? ($currency.$this->Number->format($aWallet['Awallet']['balance'])) : ($currency.'0.00');?></strong>
					</span>
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
					<button type="submit" class="btn btn-danger"><span class="fa fa-minus-circle"></span>&nbsp;<?php echo __('Pay Commission');?></button>
				    </div>
				    <?php echo $this->Form->end();?>
				</div>
			    </div>
			</div>
			<?php } ?>
			<div class="wallet-ledger-shell">
			<?php
			$walletPrintAgencyHeadersLocal = !empty($walletPrintAgencyHeaders) ? (array)$walletPrintAgencyHeaders : array();
			$walletHeaderAgencyId = (int)$selectedProjectId;
			if($walletHeaderAgencyId <= 0 && !empty($payoutProjectOptionsLocal))
			{
			    $walletHeaderAgencyKeys = array_keys($payoutProjectOptionsLocal);
			    if(!empty($walletHeaderAgencyKeys))
			    {
				$walletHeaderAgencyId = (int)$walletHeaderAgencyKeys[0];
			    }
			}
			if($walletHeaderAgencyId <= 0)
			{
			    foreach((array)$agencyFilterOptions as $agencyIdKey => $agencyNameValue)
			    {
				if((int)$agencyIdKey > 0)
				{
				    $walletHeaderAgencyId = (int)$agencyIdKey;
				    break;
				}
			    }
			}
			$walletHeaderAgency = ($walletHeaderAgencyId > 0 && !empty($walletPrintAgencyHeadersLocal[$walletHeaderAgencyId])) ? (array)$walletPrintAgencyHeadersLocal[$walletHeaderAgencyId] : array();
			$walletHeaderAgencyName = trim((string)(!empty($walletHeaderAgency['name']) ? $walletHeaderAgency['name'] : (isset($siteOrganization) ? $siteOrganization : (isset($siteName) ? $siteName : ''))));
			$walletHeaderAgencyAddress = trim((string)(!empty($walletHeaderAgency['address']) ? $walletHeaderAgency['address'] : (isset($siteAddress) ? $siteAddress : '')));
			$walletHeaderAgencyPhone = trim((string)(!empty($walletHeaderAgency['phone']) ? $walletHeaderAgency['phone'] : ''));
			$walletHeaderAgencyMobile = trim((string)(!empty($walletHeaderAgency['mobile']) ? $walletHeaderAgency['mobile'] : ''));
			$walletHeaderAgencyEmail = trim((string)(!empty($walletHeaderAgency['email']) ? $walletHeaderAgency['email'] : (isset($siteEmail) ? $siteEmail : '')));
			$walletResolveHeaderImage = function($candidates)
			{
			    foreach((array)$candidates as $candidate)
			    {
				$candidate = trim((string)$candidate);
				if($candidate === '')
				{
				    continue;
				}
				if(preg_match('/^https?:\/\//i', $candidate))
				{
				    return $candidate;
				}
				$normalized = trim(str_replace('\\', '/', $candidate), '/');
				if($normalized === '')
				{
				    continue;
				}
				$filePath = WWW_ROOT.'img'.DS.str_replace('/', DS, $normalized);
				if(file_exists($filePath))
				{
				    return $normalized;
				}
			    }
			    return '';
			};
			$walletHeaderLogo = $walletResolveHeaderImage(array(
			    (!empty($walletHeaderAgency['photo']) ? 'agency_thumb/'.trim((string)$walletHeaderAgency['photo']) : ''),
			    (!empty($walletHeaderAgency['logo']) ? 'agency_thumb/'.trim((string)$walletHeaderAgency['logo']) : ''),
			    (!empty($walletHeaderAgency['image']) ? 'agency_thumb/'.trim((string)$walletHeaderAgency['image']) : ''),
			    (!empty($walletHeaderAgency['photo']) ? trim((string)$walletHeaderAgency['photo']) : ''),
			    (!empty($walletHeaderAgency['logo']) ? trim((string)$walletHeaderAgency['logo']) : ''),
			    (!empty($walletHeaderAgency['image']) ? trim((string)$walletHeaderAgency['image']) : ''),
			    (isset($frontLogo) ? $frontLogo : '')
			));
			$walletHeaderContactParts = array();
			if($walletHeaderAgencyPhone !== '')$walletHeaderContactParts[] = __('Phone').': '.$walletHeaderAgencyPhone;
			if($walletHeaderAgencyMobile !== '')$walletHeaderContactParts[] = __('Mobile').': '.$walletHeaderAgencyMobile;
			if($walletHeaderAgencyEmail !== '')$walletHeaderContactParts[] = __('Email').': '.$walletHeaderAgencyEmail;
			?>
			<div class="wallet-toolbar no-print">
			    <div>
				<h3 class="wallet-ledger-title"><?php echo __('Commission Ledger');?></h3>
				<div class="wallet-ledger-sub"><?php echo __('Credits come from deal commission creation, debits come from paid commission vouchers, and balance runs live per entry.');?></div>
			    </div>
			    <div class="wallet-toolbar-actions">
				<?php if(!empty($payoutCanManage)){ ?>
				<a href="javascript:void(0);" class="btn btn-danger" data-toggle="modal" data-target="#commissionPayoutModal">
				    <span class="fa fa-minus-circle"></span>&nbsp;<?php echo __('Pay Commission');?>
				</a>
				<?php } ?>
				<a href="javascript:void(0);" class="btn btn-primary" onclick="return printWalletDealCommission();"><span class="fa fa-print"></span>&nbsp;<?php echo __('Print Ledger');?></a>
				<?php echo $this->Html->link('<span class="fa fa-file-excel-o"></span>&nbsp;'.__('Export CSV'),$exportCsvUrl,array('escape'=>false,'class'=>'btn btn-info'));?>
				<a href="javascript:void(0);" class="btn btn-danger" onclick="return exportWalletDealCommissionPdf();"><span class="fa fa-file-pdf-o"></span>&nbsp;<?php echo __('Export PDF');?></a>
			    </div>
			</div>
			<div class="wallet-filter-bar no-print">
			    <?php echo $this->Form->create(false,array('type'=>'get','url'=>array('controller'=>'AwalletHistories','action'=>'index',$id),'class'=>'form-inline','style'=>'display:flex;align-items:center;gap:8px;flex-wrap:wrap;width:100%;'));?>
			    <label style="margin:0 4px 0 0;"><?php echo __('Agency');?></label>
			    <select name="project_id" class="form-control" style="min-width:180px;">
				<?php foreach((array)$agencyFilterOptions as $agencyKey => $agencyValue){ ?>
				<option value="<?php echo (int)$agencyKey;?>" <?php echo ((int)$selectedProjectId === (int)$agencyKey) ? 'selected="selected"' : '';?>><?php echo h($agencyValue);?></option>
				<?php } ?>
			    </select>
			    <label style="margin:0 4px 0 8px;"><?php echo __('From');?></label>
			    <input type="date" name="start" value="<?php echo h((string)$dealFilterStart);?>" class="form-control" />
			    <label style="margin:0 4px 0 8px;"><?php echo __('To');?></label>
			    <input type="date" name="end" value="<?php echo h((string)$dealFilterEnd);?>" class="form-control" />
			    <button type="submit" class="btn btn-success"><span class="fa fa-filter"></span>&nbsp;<?php echo __('Apply');?></button>
			    <a href="<?php echo $resetFilterUrl;?>" class="btn btn-default"><span class="fa fa-refresh"></span>&nbsp;<?php echo __('Reset');?></a>
			    <span style="margin-left:auto;color:#5a5a5a;">
				<?php echo __('Current Wallet Balance');?>:
				<strong><?php echo !empty($aWallet['Awallet']['balance']) ? ($currency.$this->Number->format($aWallet['Awallet']['balance'])) : ($currency.'0.00');?></strong>
			    </span>
			    <?php echo $this->Form->end();?>
			</div>
			<style type="text/css">
			.wallet-print-brand-header{margin:0 0 10px 0;padding:0 0 8px 0;border-bottom:1px solid #d7e1ef;}
			.wallet-print-brand-table{width:100%;border-collapse:collapse;}
			.wallet-print-brand-logo{width:90px;text-align:center;vertical-align:middle;}
			.wallet-print-brand-logo img{max-height:68px;max-width:72px;object-fit:contain;display:inline-block;}
			.wallet-print-brand-center{text-align:center;vertical-align:middle;}
			.wallet-print-brand-center h2{margin:0 0 2px 0;font-size:24px;color:#17324f;font-weight:700;}
			.wallet-print-brand-center div{font-size:12px;line-height:1.35;color:#2a2a2a;}
			.wallet-print-brand-spacer{width:30px;}
			</style>
			<div id="wallet-deal-commission-export-area">
			    <div class="wallet-print-only">
			    <div class="wallet-print-brand-header">
				<table class="wallet-print-brand-table">
				    <tr>
					<td class="wallet-print-brand-logo">
					    <?php if(strlen((string)$walletHeaderLogo) > 0){ echo $this->Html->image($walletHeaderLogo, array('alt' => $walletHeaderAgencyName)); } ?>
					</td>
					<td class="wallet-print-brand-center">
					    <?php if($walletHeaderAgencyName !== ''){ ?><h2><?php echo h($walletHeaderAgencyName);?></h2><?php } ?>
					    <?php if($walletHeaderAgencyAddress !== ''){ ?><div><?php echo h($walletHeaderAgencyAddress);?></div><?php } ?>
					    <?php if(!empty($walletHeaderContactParts)){ ?><div><?php echo h(implode(' | ', $walletHeaderContactParts));?></div><?php } ?>
					</td>
					<td class="wallet-print-brand-spacer">&nbsp;</td>
				    </tr>
				</table>
			    </div>
			    <?php if($walletAgentNameLocal !== '' || $walletAgentMobileLocal !== ''){ ?>
			    <div class="wallet-print-agent-box">
				<h3><?php echo h($walletAgentNameLocal !== '' ? $walletAgentNameLocal : __('Agent'));?></h3>
				<div>
				    <strong><?php echo __('Agency');?>:</strong> <?php echo h($walletHeaderAgencyName !== '' ? $walletHeaderAgencyName : '-');?>
				    <?php if($walletAgentMobileLocal !== ''){ ?><span style="margin-left:12px;"><strong><?php echo __('Mobile');?>:</strong> <?php echo h($walletAgentMobileLocal);?></span><?php } ?>
				</div>
			    </div>
			    <?php } ?>
			    <div style="margin:0 0 10px 0;font-size:12px;color:#4a4a4a;">
				<strong><?php echo __('Filters');?>:</strong>
				<?php
				$agencyLabel = __('All Agencies');
				if((int)$selectedProjectId > 0 && !empty($agencyFilterOptions[(int)$selectedProjectId]))
				{
				    $agencyLabel = $agencyFilterOptions[(int)$selectedProjectId];
				}
				echo __('Agency').': '.h($agencyLabel).' | ';
				echo __('From').': '.h($dealFilterStart ? $dealFilterStart : __('All')).' | ';
				echo __('To').': '.h($dealFilterEnd ? $dealFilterEnd : __('All'));
				?>
			    </div>
			    </div>
			<?php if(empty($commissionLedgerRowsLocal)){ ?>
			<div class="alert alert-info"><?php echo __('No commission ledger entry found for this agent in current scope.');?></div>
			<?php }else{ ?>
			<div class="table-responsive">
			    <table class="table table-striped table-bordered wallet-ledger-table">
				<tr>
				    <th><?php echo __('#');?></th>
				    <th><?php echo __('Date');?></th>
				    <th class="wallet-print-hide"><?php echo __('Type');?></th>
				    <th class="wallet-print-hide"><?php echo __('Reference');?></th>
				    <th class="wallet-print-hide"><?php echo __('Agency');?></th>
				    <th><?php echo __('Details');?></th>
				    <th><?php echo __('Credit');?></th>
				    <th><?php echo __('Debit');?></th>
				    <th><?php echo __('Balance');?></th>
				    <th class="no-print wallet-print-hide"><?php echo __('Action');?></th>
				</tr>
				<?php
				$ledgerSr=1;
				$totalCreditAmount=0.0;
				$totalDebitAmount=0.0;
				foreach((array)$commissionLedgerRowsLocal as $ledgerRow){
				    $totalCreditAmount += (float)$ledgerRow['credit'];
				    $totalDebitAmount += (float)$ledgerRow['debit'];
				    $ledgerType = isset($ledgerRow['row_type']) ? (string)$ledgerRow['row_type'] : 'credit';
				    $ledgerTagClass = ($ledgerType === 'debit') ? 'wallet-entry-tag wallet-entry-debit' : 'wallet-entry-tag wallet-entry-credit';
				    $printActionUrl = $this->Html->url(array('controller'=>'AwalletHistories','action'=>'printentry',(int)$ledgerRow['history_id']));
				    if($ledgerType === 'debit' && !empty($ledgerRow['transaction_id']) && !empty($ledgerRow['project_id']))
				    {
					$printActionUrl = $this->Html->url(array('controller'=>'Ardaccounts','action'=>'printvoucher',(int)$ledgerRow['transaction_id'],(int)$ledgerRow['project_id']));
				    }
				?>
				<tr>
				    <td><?php echo $ledgerSr++;?></td>
				    <td><?php echo !empty($ledgerRow['entry_date']) ? $this->Time->Format('d-M-Y h:i:s A',$ledgerRow['entry_date']) : '-';?></td>
				    <td class="wallet-print-hide"><span class="<?php echo $ledgerTagClass;?>"><?php echo ($ledgerType === 'debit') ? __('Debit') : __('Credit');?></span></td>
				    <td class="wallet-print-hide"><strong><?php echo h(!empty($ledgerRow['reference']) ? $ledgerRow['reference'] : '-');?></strong></td>
				    <td class="wallet-print-hide"><?php echo h(!empty($ledgerRow['project_name']) ? $ledgerRow['project_name'] : '-');?></td>
				    <td class="wallet-ledger-detail">
					<div><strong><?php echo h(!empty($ledgerRow['detail']) ? $ledgerRow['detail'] : '-');?></strong></div>
					<?php if(!empty($ledgerRow['remarks']) && $ledgerRow['remarks'] !== $ledgerRow['detail']){ ?><div><?php echo h($ledgerRow['remarks']);?></div><?php } ?>
				    </td>
				    <td class="text-success-strong"><?php echo ((float)$ledgerRow['credit'] > 0) ? ($currency.$this->Number->format($ledgerRow['credit'])) : '-';?></td>
				    <td class="text-danger-strong"><?php echo ((float)$ledgerRow['debit'] > 0) ? ($currency.$this->Number->format($ledgerRow['debit'])) : '-';?></td>
				    <td class="wallet-ledger-balance"><?php echo $currency.$this->Number->format(isset($ledgerRow['balance']) ? $ledgerRow['balance'] : 0);?></td>
				    <td class="no-print wallet-print-hide">
					<div class="wallet-action-icons">
					<a href="<?php echo $printActionUrl;?>" target="_blank" class="btn btn-xs btn-default wallet-action-icon" title="<?php echo h(__('Print'));?>">
					    <span class="fa fa-print"></span>
					</a>
					<?php if(!empty($payoutCanManage) && !empty($ledgerRow['can_delete']) && (int)$ledgerRow['history_id'] > 0 && $ledgerType === 'debit'){ ?>
					<form method="post" action="<?php echo $this->Html->url(array('controller'=>'AwalletHistories','action'=>'index',$id));?>" style="display:inline-block;margin:0;">
					    <input type="hidden" name="data[PaidCommissionDelete][submit]" value="1" />
					    <input type="hidden" name="data[PaidCommissionDelete][history_id]" value="<?php echo (int)$ledgerRow['history_id'];?>" />
					    <input type="hidden" name="data[PaidCommissionDelete][transaction_id]" value="<?php echo (int)$ledgerRow['transaction_id'];?>" />
					    <input type="hidden" name="data[PaidCommissionDelete][project_id]" value="<?php echo (int)$ledgerRow['project_id'];?>" />
					    <input type="hidden" name="data[PaidCommissionDelete][voucher_ref]" value="<?php echo h((string)$ledgerRow['voucher_ref']);?>" />
					    <input type="hidden" name="data[PaidCommissionDelete][filter_project_id]" value="<?php echo (int)$selectedProjectId;?>" />
					    <input type="hidden" name="data[PaidCommissionDelete][filter_start]" value="<?php echo h((string)$dealFilterStart);?>" />
					    <input type="hidden" name="data[PaidCommissionDelete][filter_end]" value="<?php echo h((string)$dealFilterEnd);?>" />
					    <input type="hidden" name="data[PaidCommissionDelete][filter_tab]" value="paid" />
					    <button type="submit" class="btn btn-xs btn-danger wallet-action-icon" title="<?php echo h(__('Delete Latest'));?>" onclick="return confirm('<?php echo addslashes(__('Delete the latest payout entry?'));?>');">
						<span class="fa fa-trash"></span>
					    </button>
					</form>
					<?php } ?>
					</div>
				    </td>
				</tr>
				<?php } ?>
				<tfoot>
				    <tr>
					<th><?php echo __('Totals');?></th>
					<th>&nbsp;</th>
					<th class="wallet-print-hide">&nbsp;</th>
					<th class="wallet-print-hide">&nbsp;</th>
					<th>&nbsp;</th>
					<th class="text-success-strong"><?php echo $currency.$this->Number->format($totalCreditAmount);?></th>
					<th class="text-danger-strong"><?php echo $currency.$this->Number->format($totalDebitAmount);?></th>
					<th class="wallet-ledger-balance"><?php echo $currency.$this->Number->format(($totalCreditAmount - $totalDebitAmount));?></th>
					<th class="no-print wallet-print-hide">&nbsp;</th>
				    </tr>
				</tfoot>
			    </table>
			</div>
			<?php } ?>
			</div>
		    </div>
		</div>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
		<script type="text/javascript">
		function buildWalletPrintHtml(target){
		    if(!target){
			return '';
		    }
		    var clone = target.cloneNode(true);
		    var printOnlyNodes = clone.querySelectorAll('.wallet-print-only');
		    for(var i = 0; i < printOnlyNodes.length; i++){
			printOnlyNodes[i].style.position = 'static';
			printOnlyNodes[i].style.left = 'auto';
			printOnlyNodes[i].style.top = 'auto';
			printOnlyNodes[i].style.width = 'auto';
			printOnlyNodes[i].style.maxWidth = 'none';
			printOnlyNodes[i].style.overflow = 'visible';
			printOnlyNodes[i].style.display = 'block';
		    }
		    var hideNodes = clone.querySelectorAll('.wallet-print-hide,.no-print');
		    for(var j = 0; j < hideNodes.length; j++){
			hideNodes[j].parentNode.removeChild(hideNodes[j]);
		    }
		    return clone.innerHTML;
		}
		function printWalletDealCommission(){
		    var target = document.getElementById('wallet-deal-commission-export-area');
		    if(!target){
			window.print();
			return false;
		    }
		    var printableHtml = buildWalletPrintHtml(target);
		    if(!printableHtml){
			window.print();
			return false;
		    }
		    var popup = window.open('', '_blank');
		    if(!popup){
			window.print();
			return false;
		    }
		    popup.document.write('<!doctype html><html><head><title>Agent Wallet Commission</title><style>body{font-family:Arial,sans-serif;font-size:12px;padding:12px;color:#12263f;-webkit-print-color-adjust:exact;print-color-adjust:exact;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ccc;padding:6px;text-align:left;}h4{margin:0 0 8px 0;} .label{padding:2px 6px;border-radius:3px;background:#eee;display:inline-block;}.wallet-print-brand-header{margin:0 0 10px 0;padding:0 0 8px 0;border-bottom:1px solid #d7e1ef;}.wallet-print-brand-table{width:100%;border-collapse:collapse;}.wallet-print-brand-table td{border:0 !important;padding:0 !important;}.wallet-print-brand-logo{width:90px;text-align:center;vertical-align:middle;}.wallet-print-brand-logo img{max-height:68px;max-width:72px;object-fit:contain;display:inline-block;}.wallet-print-brand-center{text-align:center;vertical-align:middle;}.wallet-print-brand-center h2{margin:0 0 2px 0;font-size:24px;color:#17324f;font-weight:700;}.wallet-print-brand-center div{font-size:12px;line-height:1.35;color:#2a2a2a;}.wallet-print-brand-spacer{width:30px;}.wallet-print-agent-box{margin:0 0 10px 0;padding:10px 12px;border:1px solid #d7e1ef;border-radius:10px;background:#f8fbff;text-align:center;}.wallet-print-agent-box h3{margin:0;font-size:18px;font-weight:900;color:#17324f;}.wallet-print-agent-box div{margin-top:4px;font-size:12px;color:#425466;}.wallet-entry-credit{background:#e8fff3 !important;color:#0d8f53 !important;border:1px solid #bce8cf !important;}.wallet-entry-debit{background:#fff1f1 !important;color:#c43838 !important;border:1px solid #f2c6c6 !important;}.wallet-entry-tag{display:inline-block;border-radius:999px;padding:4px 10px;font-size:11px;font-weight:800;line-height:1.2;}.wallet-ledger-table th{background:#132a46 !important;color:#fff !important;border-color:#274564 !important;font-size:12px;vertical-align:middle;}.wallet-ledger-table td{vertical-align:top;font-size:12px;line-height:1.45;color:#12263f;}.wallet-ledger-table tfoot th{background:#132a46 !important;color:#fff !important;}.text-success-strong{color:#0d8f53 !important;font-weight:900;}.text-danger-strong{color:#c43838 !important;font-weight:900;}.wallet-ledger-balance{font-weight:900;color:#0b2240 !important;}</style></head><body>'+printableHtml+'</body></html>');
		    popup.document.close();
		    popup.focus();
		    popup.print();
		    popup.close();
		    return false;
		}
		function exportWalletDealCommissionPdf(){
		    var target = document.getElementById('wallet-deal-commission-export-area');
		    if(!target || typeof html2pdf === 'undefined'){
			window.print();
			return false;
		    }
		    var printableHtml = buildWalletPrintHtml(target);
		    if(!printableHtml){
			window.print();
			return false;
		    }
		    var stamp = (new Date()).toISOString().replace(/[-:T]/g,'').slice(0,14);
		    var popup = window.open('', '_blank');
		    if(!popup){
			window.print();
			return false;
		    }
		    var pdfScript = ''
			+ '<script>'
			+ '(function(){'
			+ 'function run(){'
			+ 'var engine = (window.opener && window.opener.html2pdf) ? window.opener.html2pdf : null;'
			+ 'if(!engine){ window.print(); return; }'
			+ 'engine().set({'
			+ 'margin:8,'
			+ 'filename:\"agent_wallet_deal_commission_' + stamp + '.pdf\",'
			+ 'image:{type:\"jpeg\",quality:0.98},'
			+ 'html2canvas:{scale:2,useCORS:true,backgroundColor:\"#ffffff\"},'
			+ 'jsPDF:{unit:\"mm\",format:\"a4\",orientation:\"landscape\"}'
			+ '}).from(document.getElementById(\"wallet-pdf-root\")).save().then(function(){ setTimeout(function(){ window.close(); }, 150); });'
			+ '}'
			+ 'if(document.readyState === \"complete\"){ setTimeout(run, 120); } else { window.addEventListener(\"load\", function(){ setTimeout(run, 120); }); }'
			+ '})();'
			+ '<\/script>';
		    popup.document.write('<!doctype html><html><head><title>Agent Wallet Commission PDF</title><style>body{font-family:Arial,sans-serif;font-size:12px;padding:12px;color:#12263f;background:#ffffff;-webkit-print-color-adjust:exact;print-color-adjust:exact;}#wallet-pdf-root{width:1120px;margin:0 auto;background:#ffffff;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ccc;padding:6px;text-align:left;}h4{margin:0 0 8px 0;} .label{padding:2px 6px;border-radius:3px;background:#eee;display:inline-block;}.wallet-print-brand-header{margin:0 0 10px 0;padding:0 0 8px 0;border-bottom:1px solid #d7e1ef;}.wallet-print-brand-table{width:100%;border-collapse:collapse;}.wallet-print-brand-table td{border:0 !important;padding:0 !important;}.wallet-print-brand-logo{width:90px;text-align:center;vertical-align:middle;}.wallet-print-brand-logo img{max-height:68px;max-width:72px;object-fit:contain;display:inline-block;}.wallet-print-brand-center{text-align:center;vertical-align:middle;}.wallet-print-brand-center h2{margin:0 0 2px 0;font-size:24px;color:#17324f;font-weight:700;}.wallet-print-brand-center div{font-size:12px;line-height:1.35;color:#2a2a2a;}.wallet-print-brand-spacer{width:30px;}.wallet-print-agent-box{margin:0 0 10px 0;padding:10px 12px;border:1px solid #d7e1ef;border-radius:10px;background:#f8fbff;text-align:center;}.wallet-print-agent-box h3{margin:0;font-size:18px;font-weight:900;color:#17324f;}.wallet-print-agent-box div{margin-top:4px;font-size:12px;color:#425466;}.wallet-entry-credit{background:#e8fff3 !important;color:#0d8f53 !important;border:1px solid #bce8cf !important;}.wallet-entry-debit{background:#fff1f1 !important;color:#c43838 !important;border:1px solid #f2c6c6 !important;}.wallet-entry-tag{display:inline-block;border-radius:999px;padding:4px 10px;font-size:11px;font-weight:800;line-height:1.2;}.wallet-ledger-table{width:100%;border-collapse:collapse;}.wallet-ledger-table th,.wallet-ledger-table td{border:1px solid #ccc;padding:6px;text-align:left;}.wallet-ledger-table th{background:#132a46 !important;color:#fff !important;border-color:#274564 !important;font-size:12px;vertical-align:middle;}.wallet-ledger-table td{vertical-align:top;font-size:12px;line-height:1.45;color:#12263f;}.wallet-ledger-table tfoot th{background:#132a46 !important;color:#fff !important;}.text-success-strong{color:#0d8f53 !important;font-weight:900;}.text-danger-strong{color:#c43838 !important;font-weight:900;}.wallet-ledger-balance{font-weight:900;color:#0b2240 !important;}</style></head><body><div id=\"wallet-pdf-root\">'+printableHtml+'</div>'+pdfScript+'</body></html>');
		    popup.document.close();
		    return false;
		}
		(function(){
		    var byProject = <?php echo json_encode($payoutSourceLedgerByProjectLocal);?>;
		    var reopenPayoutModal = <?php echo (!empty($this->request->data['CommissionPayout']['submit']) ? 'true' : 'false');?>;
		    var projectSelect = document.getElementById('CommissionPayoutProjectId');
		    var ledgerSelect = document.getElementById('CommissionPayoutLedgerId');
		    function refillLedgerOptions(){
			if(!projectSelect || !ledgerSelect){ return; }
			var projectId = projectSelect.value ? String(projectSelect.value) : '';
			var options = (projectId && byProject[projectId]) ? byProject[projectId] : {};
			ledgerSelect.innerHTML = '';
			var hasOption = false;
			for(var key in options){
			    if(!Object.prototype.hasOwnProperty.call(options, key)){ continue; }
			    hasOption = true;
			    var opt = document.createElement('option');
			    opt.value = key;
			    opt.text = options[key];
			    ledgerSelect.appendChild(opt);
			}
			if(!hasOption){
			    var empty = document.createElement('option');
			    empty.value = '';
			    empty.text = '<?php echo addslashes(__('No Account Found'));?>';
			    ledgerSelect.appendChild(empty);
			}
		    }
		    if(projectSelect){
			projectSelect.addEventListener('change', refillLedgerOptions);
		    }
		    if(reopenPayoutModal && typeof jQuery !== 'undefined' && jQuery('#commissionPayoutModal').length){
			jQuery('#commissionPayoutModal').modal('show');
		    }
		})();
		</script>
		<?php } ?>
</div>
