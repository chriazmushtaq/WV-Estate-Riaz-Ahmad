<?php
$selected = !empty($selectedRental['DealRental']) ? $selectedRental['DealRental'] : array();
$selected['start_date'] = !empty($selected['start_date']) ? date('d-M-Y',strtotime($selected['start_date'])) : '';
$selected['end_date'] = !empty($selected['end_date']) ? date('d-M-Y',strtotime($selected['end_date'])) : '';
foreach((array)$receipts as $receiptKey=>$receiptRow){
    if(!empty($receiptRow['DealRentalReceipt']['receipt_date'])){$receipts[$receiptKey]['DealRentalReceipt']['receipt_date']=date('d-M-Y',strtotime($receiptRow['DealRentalReceipt']['receipt_date']));}
    if(!empty($receiptRow['DealRentalReceipt']['rent_month'])){$receipts[$receiptKey]['DealRentalReceipt']['rent_month']=date('M Y',strtotime($receiptRow['DealRentalReceipt']['rent_month'].'-01'));}
}
$summary = isset($rentalSummary) ? $rentalSummary : array('scheduled'=>0,'paid'=>0,'outstanding'=>0,'upcoming'=>0,'overdue'=>0,'owner_charges'=>0,'net_received'=>0);
?>

<style type="text/css">
.wv-rentals-wrap {
  margin: 0;
  padding: 0;
}
.wv-rentals-hero {
  background: linear-gradient(135deg, #07152a 0%, #102746 72%, #ff0a46 170%);
  border-radius: 16px;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.14);
  color: #ffffff;
  margin-bottom: 20px;
  overflow: hidden;
  padding: 24px;
  position: relative;
}
.wv-rentals-hero:after {
  background: radial-gradient(circle at 85% 15%, rgba(124, 249, 255, 0.22), rgba(124, 249, 255, 0) 40%);
  content: "";
  inset: 0;
  position: absolute;
}
.wv-rentals-hero-inner {
  align-items: center;
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  justify-content: space-between;
  position: relative;
  z-index: 2;
}
.wv-rentals-kicker {
  color: #7cf9ff;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: 0.8px;
  margin-bottom: 4px;
  text-transform: uppercase;
}
.wv-rentals-title {
  color: #ffffff !important;
  font-size: 26px;
  font-weight: 900;
  line-height: 1.2;
  margin: 0 0 6px;
}
.wv-rentals-desc {
  color: #e2e8f0;
  font-size: 13px;
  font-weight: 700;
  margin: 0;
}
.wv-rentals-panel {
  background: #ffffff;
  border: 1px solid #dfe9f5;
  border-radius: 14px;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.06);
  margin-bottom: 20px;
  overflow: hidden;
  padding: 20px;
}
.wv-rentals-header {
  align-items: center;
  border-bottom: 1px solid #edf2f7;
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  justify-content: space-between;
  padding-bottom: 16px;
}
.wv-rentals-header-info h2 {
  color: #07152a;
  font-size: 20px;
  font-weight: 900;
  margin: 0 0 4px;
}
.wv-rentals-header-info small {
  color: #64748b;
  font-size: 12px;
  font-weight: 800;
}
.wv-rentals-header-actions {
  align-items: center;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.wv-rentals-stats {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(6, minmax(0, 1fr));
  margin: 18px 0;
}
.wv-rentals-stat {
  background: #f8fbff;
  border: 1px solid #dfe9f5;
  border-radius: 10px;
  min-height: 84px;
  padding: 12px 14px;
  position: relative;
  transition: all 0.2s ease;
}
.wv-rentals-stat:hover {
  background: #ffffff;
  box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
}
.wv-rentals-stat small {
  color: #64748b;
  display: block;
  font-size: 10px;
  font-weight: 900;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}
.wv-rentals-stat strong {
  color: #07152a;
  display: block;
  font-size: 16px;
  font-weight: 900;
  margin-top: 4px;
  overflow-wrap: anywhere;
}
.wv-rentals-stat-period strong {
  font-size: 11px;
  line-height: 1.35;
  white-space: normal;
}
.wv-rentals-stat-period em {
  color: #64748b;
  display: block;
  font-size: 10px;
  font-style: normal;
  font-weight: 800;
  margin-top: 4px;
}
.my-rental-status {
  border-radius: 999px;
  display: inline-block;
  font-size: 11px;
  font-weight: 900;
  padding: 5px 10px;
  text-transform: uppercase;
}
.status-active, .status-paid, .status-resolved, .status-completed {
  background: #dcfce7;
  color: #166534;
}
.status-inactive, .status-closed {
  background: #f1f5f9;
  color: #475569;
}
.status-overdue, .status-urgent, .status-rejected {
  background: #fee2e2;
  color: #b91c1c;
}
.status-upcoming, .status-scheduled, .status-submitted {
  background: #e0f2fe;
  color: #0369a1;
}
.status-partially-paid, .status-in-review, .status-in-progress, .status-approved {
  background: #fef3c7;
  color: #92400e;
}

.wv-rentals-tabs {
  border-bottom: 2px solid #e2e8f0;
  margin-bottom: 16px;
}
.wv-rentals-tabs > li > a {
  border-radius: 8px 8px 0 0;
  color: #475569;
  font-size: 13px;
  font-weight: 800;
  padding: 10px 16px;
  transition: all 0.2s ease;
}
.wv-rentals-tabs > li.active > a,
.wv-rentals-tabs > li.active > a:focus,
.wv-rentals-tabs > li.active > a:hover {
  background: #07152a !important;
  border-color: #07152a !important;
  color: #ffffff !important;
}

.my-rental-form {
  background: #f8fbff;
  border: 1px solid #dfe9f5;
  border-radius: 12px;
  display: grid;
  gap: 14px;
  grid-template-columns: 1fr 1fr;
  margin-bottom: 18px;
  padding: 16px;
}
.my-rental-form .full {
  grid-column: 1 / -1;
}
.my-rental-form label {
  color: #07152a;
  display: block;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: 0.3px;
  margin-bottom: 5px;
  text-transform: uppercase;
}
.my-rental-form .form-control {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  box-shadow: none;
  font-weight: 700;
}

@media(max-width:1199px){
  .wv-rentals-stats{grid-template-columns:repeat(3,1fr);}
}
@media(max-width:767px){
  .wv-rentals-hero{padding:18px 14px;}
  .wv-rentals-hero-inner{align-items:flex-start;flex-direction:column;}
  .wv-rentals-title{font-size:22px;}
  .wv-rentals-stats{grid-template-columns:1fr 1fr;}
  .my-rental-form{grid-template-columns:1fr;}
  .my-rental-form .full{grid-column:auto;}
  .wv-rentals-header{align-items:flex-start;flex-direction:column;}
  .wv-rentals-header-actions{width:100%;}
  .wv-rentals-header-actions select{width:100%;}
  .wv-rentals-header-actions .btn{flex:1 1 auto;justify-content:center;}
}
@media(max-width:480px){
  .wv-rentals-stats{grid-template-columns:1fr;}
}
</style>

<div class="wv-rentals-wrap">
  <?php echo $this->Session->flash(); ?>

  <!-- Rentals Hero Header -->
  <div class="wv-rentals-hero">
    <div class="wv-rentals-hero-inner">
      <div>
        <div class="wv-rentals-kicker"><?php echo __('Property Asset Management'); ?></div>
        <h1 class="wv-rentals-title"><?php echo __('My Rentals'); ?></h1>
        <p class="wv-rentals-desc"><?php echo __('Review your active agreements, rent payout schedule, maintenance requests, and inspection reports.'); ?></p>
      </div>
      <div>
        <?php echo $this->Html->link('<i class="fa fa-dollar"></i> ' . __('Payment Ledger'), array('controller' => 'MyPayments', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-default')); ?>
      </div>
    </div>
  </div>

  <section class="wv-rentals-panel">
    <?php if(empty($rentals)): ?>
      <div style="padding:28px 16px;text-align:center;color:#64748b;font-size:14px;font-weight:800;">
        <i class="fa fa-info-circle" style="font-size:24px;color:#0f4c81;display:block;margin-bottom:8px;"></i>
        <?php echo __('No rental agreements are currently linked to your profile.'); ?>
      </div>
    <?php endif; ?>

    <?php if(!empty($selected['id'])): 
      $propertyName = !empty($selectedRental['PortalProperty']['name']) ? $selectedRental['PortalProperty']['name'] : '-';
    ?>
      <!-- Rental Property Header Bar -->
      <div class="wv-rentals-header">
        <div class="wv-rentals-header-info">
          <h2><?php echo h($propertyName); ?></h2>
          <small><i class="fa fa-file-text-o"></i> <?php echo h($selected['agreement_no'] . ' | ' . $selected['start_date'] . ' ' . __('to') . ' ' . $selected['end_date']); ?></small>
        </div>
        <div class="wv-rentals-header-actions">
          <?php if(count((array)$rentals) > 1): ?>
            <select class="form-control" aria-label="<?php echo h(__('Select Rental')); ?>" onchange="if(this.value){window.location.href=this.value;}">
              <?php foreach((array)$rentals as $rentalOption): 
                $optionRental = $rentalOption['DealRental'];
                $optionProperty = !empty($rentalOption['PortalProperty']['name']) ? $rentalOption['PortalProperty']['name'] : $optionRental['agreement_no'];
              ?>
                <option value="<?php echo h($this->Html->url(array('action'=>'index',(int)$optionRental['id']))); ?>" <?php echo (int)$optionRental['id'] === (int)$selected['id'] ? 'selected' : ''; ?>>
                  <?php echo h($optionProperty . ' | ' . $optionRental['agreement_no']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
          <span class="my-rental-status status-<?php echo h(strtolower(str_replace(' ','-',$selected['status']))); ?>"><?php echo h($selected['status']); ?></span>
          <?php echo $this->Html->link('<i class="fa fa-file-text-o"></i> ' . __('Print Agreement'), array('action'=>'agreement',(int)$selected['id']), array('class'=>'btn btn-info','target'=>'_blank','escape'=>false)); ?>
          <?php echo $this->Html->link('<i class="fa fa-print"></i> ' . __('Print Statement'), array('action'=>'statement',(int)$selected['id']), array('class'=>'btn btn-primary','target'=>'_blank','escape'=>false)); ?>
        </div>
      </div>

      <!-- Quick Summary Stats Cards -->
      <div class="wv-rentals-stats">
        <div class="wv-rentals-stat wv-rentals-stat-period">
          <small><?php echo __('Rent Period'); ?></small>
          <strong><?php echo h((!empty($selected['start_date']) ? date('d-M-Y',strtotime($selected['start_date'])) : '-') . ' ' . __('to') . ' ' . (!empty($selected['end_date']) ? date('d-M-Y',strtotime($selected['end_date'])) : '-')); ?></strong>
          <em><?php echo h(__('Total') . ': ' . $currency . ' ' . number_format($summary['scheduled'], 2)); ?></em>
        </div>
        <div class="wv-rentals-stat">
          <small><?php echo __('Paid to You'); ?></small>
          <strong style="color:#047857;"><?php echo h($currency . ' ' . number_format($summary['paid'], 2)); ?></strong>
        </div>
        <div class="wv-rentals-stat">
          <small><?php echo __('Up-Coming'); ?></small>
          <strong style="color:#0284c7;"><?php echo h($currency . ' ' . number_format($summary['upcoming'], 2)); ?></strong>
        </div>
        <div class="wv-rentals-stat">
          <small><?php echo __('Overdue'); ?></small>
          <strong style="color:#dc2626;"><?php echo h($currency . ' ' . number_format($summary['overdue'], 2)); ?></strong>
        </div>
        <div class="wv-rentals-stat">
          <small><?php echo __('Owner Charges'); ?></small>
          <strong style="color:#7c3aed;"><?php echo h($currency . ' ' . number_format($summary['owner_charges'], 2)); ?></strong>
        </div>
        <div class="wv-rentals-stat">
          <small><?php echo __('Net Received'); ?></small>
          <strong style="color:#0f172a;"><?php echo h($currency . ' ' . number_format($summary['net_received'], 2)); ?></strong>
        </div>
      </div>

      <!-- Rental Management Tabs -->
      <ul class="nav nav-tabs wv-rentals-tabs">
        <li class="active"><a href="#owner-schedule" data-toggle="tab"><i class="fa fa-calendar"></i> <?php echo __('Rent Schedule'); ?></a></li>
        <li><a href="#owner-payouts" data-toggle="tab"><i class="fa fa-money"></i> <?php echo __('Payout History'); ?></a></li>
        <li><a href="#maintenance" data-toggle="tab"><i class="fa fa-wrench"></i> <?php echo __('Maintenance'); ?></a></li>
        <li><a href="#inspections" data-toggle="tab"><i class="fa fa-clipboard"></i> <?php echo __('Inspections'); ?></a></li>
      </ul>

      <div class="tab-content" style="padding-top:10px;">
        <!-- Tab 1: Schedule -->
        <div class="tab-pane active" id="owner-schedule">
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th><?php echo __('Month'); ?></th>
                  <th><?php echo __('Due Date'); ?></th>
                  <th><?php echo __('Rent Amount'); ?></th>
                  <th><?php echo __('Paid'); ?></th>
                  <th><?php echo __('Balance'); ?></th>
                  <th><?php echo __('Status'); ?></th>
                  <th><?php echo __('Receipt'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($schedules)): ?>
                  <tr><td colspan="7" class="text-center"><?php echo __('No rent schedule available.'); ?></td></tr>
                <?php endif; ?>
                <?php foreach((array)$schedules as $row): 
                  $s = $row['DealRentalSchedule'];
                  $linkedReceipts = !empty($scheduleReceiptMap[(int)$s['id']]) ? $scheduleReceiptMap[(int)$s['id']] : array();
                ?>
                  <tr>
                    <td><strong><?php echo h(date('M Y', strtotime($s['rent_month'].'-01'))); ?></strong></td>
                    <td><?php echo h(date('d-M-Y', strtotime($s['due_date']))); ?></td>
                    <td><?php echo h($currency . ' ' . number_format($s['rent_amount'], 2)); ?></td>
                    <td style="color:#047857;font-weight:900;"><?php echo h($currency . ' ' . number_format($s['paid_amount'], 2)); ?></td>
                    <td style="color:<?php echo ((float)$s['balance_amount'] > 0) ? '#dc2626' : '#64748b'; ?>;font-weight:900;">
                      <?php echo h($currency . ' ' . number_format($s['balance_amount'], 2)); ?>
                    </td>
                    <td><span class="my-rental-status status-<?php echo h(strtolower(str_replace(' ','-',$s['status']))); ?>"><?php echo h($s['status']); ?></span></td>
                    <td>
                      <?php if((float)$s['balance_amount'] <= 0.009): 
                        foreach($linkedReceipts as $receiptId => $receiptNo):
                          echo $this->Html->link('<i class="fa fa-print"></i> ' . h($receiptNo), array('action'=>'receipt',(int)$receiptId), array('class'=>'btn btn-xs btn-info','target'=>'_blank','escape'=>false,'title'=>__('Print Receipt').' '.$receiptNo,'style'=>'margin-right:4px;'));
                        endforeach;
                      endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tab 2: Payouts -->
        <div class="tab-pane" id="owner-payouts">
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th><?php echo __('Receipt No'); ?></th>
                  <th><?php echo __('Rent Month'); ?></th>
                  <th><?php echo __('Payment Date'); ?></th>
                  <th><?php echo __('Amount'); ?></th>
                  <th><?php echo __('Payment Mode'); ?></th>
                  <th><?php echo __('Action'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($receipts)): ?>
                  <tr><td colspan="6" class="text-center"><?php echo __('No payouts recorded.'); ?></td></tr>
                <?php endif; ?>
                <?php foreach((array)$receipts as $row): 
                  $x = $row['DealRentalReceipt'];
                ?>
                  <tr>
                    <td><strong><?php echo h($x['receipt_no']); ?></strong></td>
                    <td><?php echo h($x['rent_month']); ?></td>
                    <td><?php echo h($x['receipt_date']); ?></td>
                    <td style="color:#047857;font-weight:900;"><?php echo h($currency . ' ' . number_format($x['rent_amount'], 2)); ?></td>
                    <td><?php echo h($x['payment_mode']); ?></td>
                    <td><?php echo $this->Html->link('<i class="fa fa-print"></i> ' . __('Receipt'), array('action'=>'receipt',(int)$x['id']), array('class'=>'btn btn-xs btn-info','target'=>'_blank','escape'=>false)); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tab 3: Maintenance -->
        <div class="tab-pane" id="maintenance">
          <?php echo $this->Form->create('DealRentalMaintenance', array('url' => array('action'=>'index',(int)$selected['id']))); ?>
            <div class="my-rental-form">
              <div>
                <label><?php echo __('Category'); ?></label>
                <?php echo $this->Form->select('category', array('General'=>'General','Plumbing'=>'Plumbing','Electrical'=>'Electrical','Structural'=>'Structural','Appliance'=>'Appliance','Security'=>'Security'), array('class'=>'form-control','empty'=>false)); ?>
              </div>
              <div>
                <label><?php echo __('Priority'); ?></label>
                <?php echo $this->Form->select('priority', array('Low'=>'Low','Normal'=>'Normal','High'=>'High','Urgent'=>'Urgent'), array('class'=>'form-control','empty'=>false)); ?>
              </div>
              <div class="full">
                <label><?php echo __('Title / Brief Issue'); ?></label>
                <?php echo $this->Form->text('title', array('class'=>'form-control','required'=>true,'placeholder'=>__('e.g. Water leak in main bathroom'))); ?>
              </div>
              <div class="full">
                <label><?php echo __('Detailed Description'); ?></label>
                <?php echo $this->Form->textarea('description', array('class'=>'form-control','rows'=>3,'placeholder'=>__('Describe the maintenance requirement in detail...'))); ?>
              </div>
              <div class="full">
                <button type="submit" class="btn btn-primary">
                  <i class="fa fa-paper-plane"></i> <?php echo __('Submit Maintenance Request'); ?>
                </button>
              </div>
            </div>
          <?php echo $this->Form->end(); ?>

          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th><?php echo __('Request No'); ?></th>
                  <th><?php echo __('Date'); ?></th>
                  <th><?php echo __('Issue & Category'); ?></th>
                  <th><?php echo __('Priority'); ?></th>
                  <th><?php echo __('Status'); ?></th>
                  <th><?php echo __('Owner Charge'); ?></th>
                  <th><?php echo __('Resolution Notes'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($maintenanceRequests)): ?>
                  <tr><td colspan="7" class="text-center"><?php echo __('No maintenance requests recorded.'); ?></td></tr>
                <?php endif; ?>
                <?php foreach((array)$maintenanceRequests as $row): 
                  $m = $row['DealRentalMaintenance'];
                ?>
                  <tr>
                    <td><strong><?php echo h($m['request_no']); ?></strong></td>
                    <td><?php echo h($m['request_date']); ?></td>
                    <td>
                      <strong><?php echo h($m['title']); ?></strong><br>
                      <small style="color:#64748b;font-weight:800;"><?php echo h($m['category']); ?></small>
                    </td>
                    <td><?php echo h($m['priority']); ?></td>
                    <td><span class="my-rental-status status-<?php echo h(strtolower(str_replace(' ','-',$m['status']))); ?>"><?php echo h($m['status']); ?></span></td>
                    <td><?php echo h($currency . ' ' . number_format($m['owner_charge_amount'], 2)); ?></td>
                    <td><?php echo nl2br(h($m['resolution_notes'])); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tab 4: Inspections -->
        <div class="tab-pane" id="inspections">
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th><?php echo __('Type'); ?></th>
                  <th><?php echo __('Inspection Date'); ?></th>
                  <th><?php echo __('Status'); ?></th>
                  <th><?php echo __('Inspector'); ?></th>
                  <th><?php echo __('Meters'); ?></th>
                  <th><?php echo __('Keys'); ?></th>
                  <th><?php echo __('Deposit'); ?></th>
                  <th><?php echo __('Condition & Evidence'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($inspections)): ?>
                  <tr><td colspan="8" class="text-center"><?php echo __('No inspection records available.'); ?></td></tr>
                <?php endif; ?>
                <?php foreach((array)$inspections as $row): 
                  $i = $row['DealRentalInspection'];
                  $photos = json_decode(isset($i['photos_json']) ? $i['photos_json'] : '', true);
                ?>
                  <tr>
                    <td><strong><?php echo h($i['inspection_type']); ?></strong></td>
                    <td><?php echo h($i['inspection_date']); ?></td>
                    <td><span class="my-rental-status status-<?php echo h(strtolower($i['status'])); ?>"><?php echo h($i['status']); ?></span></td>
                    <td><?php echo h($i['inspector_name']); ?></td>
                    <td><small><?php echo h('E:'.$i['electricity_meter'].' | G:'.$i['gas_meter'].' | W:'.$i['water_meter']); ?></small></td>
                    <td><?php echo (int)$i['keys_count']; ?></td>
                    <td><?php echo h($i['security_deposit_status']); ?></td>
                    <td>
                      <?php echo nl2br(h($i['condition_notes'])); ?>
                      <?php if(!empty($photos)){ ?>
                        <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:4px;">
                          <?php foreach((array)$photos as $photo): ?>
                            <a href="<?php echo h($this->Html->url('/files/rental_inspections/' . rawurlencode($photo))); ?>" target="_blank" rel="noopener">
                              <img src="<?php echo h($this->Html->url('/files/rental_inspections/' . rawurlencode($photo))); ?>" alt="" style="width:44px;height:44px;object-fit:cover;border-radius:6px;border:1px solid #cbd5e1;">
                            </a>
                          <?php endforeach; ?>
                        </div>
                      <?php } ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </section>
</div>
