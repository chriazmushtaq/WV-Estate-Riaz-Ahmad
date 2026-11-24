<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$pageTitleCustom = ($activeKind === 'income') ? __('Bulk Income Vouchers') : __('Bulk Expense Vouchers');
$this->set('title_for_layout', $pageTitleCustom);
$todayIso = date('Y-m-d');
$todayDisplay = date('d-M-Y');
$currentProjectId = !empty($currentProjectId) ? (int)$currentProjectId : 0;
$tabProjectId = ($currentProjectId > 0) ? $currentProjectId : null;
$presetRows = ($activeKind === 'income') ? $presetIncomeRows : $presetExpenseRows;
$allLedgers = ($activeKind === 'income') ? $allIncomeLedgers : $allExpenseLedgers;
?>
<style type="text/css">
.ard-bulk-wrap .panel-body { background: #f8fafc; padding: 20px; }
.ard-bulk-header { background: #07152a; color: #fff; padding: 16px 20px; border-radius: 10px 10px 0 0; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; }
.ard-bulk-title { font-size: 18px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 10px; }
.ard-bulk-tabs { display: inline-flex; background: rgba(255,255,255,0.12); padding: 3px; border-radius: 8px; }
.ard-bulk-tab-btn { padding: 6px 16px; border-radius: 6px; font-size: 13px; font-weight: 700; color: #cbd5e1; text-decoration: none!important; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px; }
.ard-bulk-tab-btn:hover { color: #fff; background: rgba(255,255,255,0.15); }
.ard-bulk-tab-btn.active.expense { background: #dc2626; color: #fff; box-shadow: 0 2px 4px rgba(220,38,38,0.4); }
.ard-bulk-tab-btn.active.income { background: #16a34a; color: #fff; box-shadow: 0 2px 4px rgba(22,163,74,0.4); }

.ard-bulk-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px; overflow: hidden; }
.ard-bulk-card-head { background: #f1f5f9; padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #1e293b; font-size: 13.5px; display: flex; justify-content: space-between; align-items: center; }

.ard-table-bulk thead th { background: #0f172a!important; color: #f8fafc!important; font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 11px 10px!important; vertical-align: middle!important; border: none!important; }
.ard-table-bulk tbody td { vertical-align: middle!important; padding: 8px 10px!important; border-color: #f1f5f9!important; }
.ard-table-bulk tbody tr:hover { background: #f8fafc!important; }
.ard-table-bulk .form-control { border-radius: 6px; border-color: #cbd5e1; }
.ard-table-bulk .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.15); }

.bulk-row-amount { font-family: 'Consolas', 'Courier New', monospace; font-size: 13.5px; font-weight: 700; text-align: right; color: #0f172a; }
.bulk-row-amount:focus { background: #eff6ff; }
.bulk-row-amount.has-val { background: #f0fdf4; border-color: #86efac; color: #15803d; }

.ard-summary-bar { background: #0f172a; color: #fff; border-radius: 10px; padding: 16px 20px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px; margin-top: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
.ard-stat-box { display: flex; align-items: center; gap: 10px; }
.ard-stat-num { font-size: 20px; font-weight: 800; line-height: 1; }
.ard-stat-label { font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 600; }
.ard-grand-total { font-size: 24px; font-weight: 900; color: #38bdf8; font-family: 'Consolas', 'Courier New', monospace; }

.safeguard-badge { display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 6px; }
.agency-pill { background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); color: #fff; border-radius: 20px; padding: 4px 12px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
</style>

<div class="row ard-bulk-wrap">
    <div class="col-md-12">
        <div class="panel panel-default" style="border:none; box-shadow:none; background:transparent;">
            
            <!-- Top Controls Toolbar -->
            <div style="margin-bottom:14px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:10px;">
                <div class="btn-group">
                    <?php echo $this->Html->link('<span class="fa fa-home"></span> '.__('Dashboard'), array('controller'=>'Ardaccounts','action'=>'dashboard',$tabProjectId), array('escape'=>false,'class'=>'btn btn-default'));?>
                    <?php echo $this->Html->link('<span class="fa fa-exchange"></span> '.__('All Transactions'), array('controller'=>'Ardaccounts','action'=>'transactions',$tabProjectId), array('escape'=>false,'class'=>'btn btn-default'));?>
                    <?php echo $this->Html->link('<span class="fa fa-credit-card"></span> '.__('Expense Transactions'), array('controller'=>'Ardaccounts','action'=>'expensetransactions',$tabProjectId), array('escape'=>false,'class'=>'btn '.($activeKind==='expense' ? 'btn-danger' : 'btn-default')));?>
                    <?php echo $this->Html->link('<span class="fa fa-arrow-circle-down"></span> '.__('Income Transactions'), array('controller'=>'Ardaccounts','action'=>'incometransactions',$tabProjectId), array('escape'=>false,'class'=>'btn '.($activeKind==='income' ? 'btn-success' : 'btn-default')));?>
                    <?php echo $this->Html->link('<span class="fa fa-list-alt"></span> '.__('Bulk Vouchers'), array('controller'=>'Ardaccounts','action'=>'bulkvouchers',$tabProjectId, '?'=>array('type'=>$activeKind)), array('escape'=>false,'class'=>'btn btn-primary active', 'style'=>'font-weight:700;'));?>
                </div>

                <div style="display:flex; align-items:center; gap:8px;">
                    <?php echo $this->Html->link('<span class="fa fa-cog"></span> '.__('Configure Heads in Settings'), array('controller'=>'Ardaccounts','action'=>'settings',$tabProjectId,'?'=>array('tab'=>'bulk')), array('escape'=>false,'class'=>'btn btn-default btn-sm', 'style'=>'font-weight:600;'));?>
                </div>
            </div>

            <!-- Main Shell -->
            <div class="ard-bulk-card">
                
                <!-- Card Header with Type Switch and Agency Isolation -->
                <div class="ard-bulk-header">
                    <div>
                        <h2 class="ard-bulk-title">
                            <i class="fa <?php echo ($activeKind==='income') ? 'fa-arrow-circle-down text-success' : 'fa-credit-card text-danger';?>"></i>
                            <?php echo ($activeKind==='income') ? __('Bulk Income Voucher Entry') : __('Bulk Expense Voucher Entry');?>
                        </h2>
                        <div style="font-size:12px; color:#94a3b8; margin-top:3px;">
                            <?php echo __('Enter multiple daily transactions at once. Unused accounts with 0.00 amount are safely skipped.');?>
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; gap:12px;">
                        <!-- Super Admin Agency Selector or Isolated Agency Badge -->
                        <?php if($isGlobalAdmin): ?>
                            <div style="display:inline-flex; align-items:center; gap:6px;">
                                <span style="font-size:11.5px; color:#cbd5e1; font-weight:700; text-transform:uppercase;"><?php echo __('Agency:');?></span>
                                <select id="agencySelector" class="form-control input-sm" style="width:200px; display:inline-block; font-weight:700; background:#0f172a; color:#fff; border-color:#334155;">
                                    <?php foreach((array)$projectName as $pId => $pTitle): ?>
                                        <option value="<?php echo $pId;?>" <?php echo ($currentProjectId == $pId) ? 'selected' : '';?>><?php echo h($pTitle);?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <span class="agency-pill">
                                <i class="fa fa-shield"></i>
                                <?php echo isset($projectName[$currentProjectId]) ? h($projectName[$currentProjectId]) : __('My Agency');?>
                            </span>
                        <?php endif; ?>

                        <!-- Mode Switch: Expense vs Income -->
                        <div class="ard-bulk-tabs">
                            <a href="<?php echo $this->Html->url(array('action'=>'bulkvouchers', $currentProjectId, '?'=>array('type'=>'expense')));?>" class="ard-bulk-tab-btn expense <?php echo ($activeKind==='expense') ? 'active' : '';?>">
                                <i class="fa fa-credit-card"></i> <?php echo __('Expense Vouchers');?>
                            </a>
                            <a href="<?php echo $this->Html->url(array('action'=>'bulkvouchers', $currentProjectId, '?'=>array('type'=>'income')));?>" class="ard-bulk-tab-btn income <?php echo ($activeKind==='income') ? 'active' : '';?>">
                                <i class="fa fa-arrow-circle-down"></i> <?php echo __('Income Vouchers');?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Bulk Form Body -->
                <?php echo $this->Form->create('BulkVoucher', array('url' => array('action' => 'bulkvouchers', $currentProjectId), 'id' => 'bulkForm', 'class' => 'form-horizontal')); ?>
                <?php echo $this->Form->hidden('project_id', array('value' => $currentProjectId)); ?>
                <?php echo $this->Form->hidden('type', array('value' => $activeKind)); ?>

                <div class="panel-body">
                    
                    <!-- Top Form Attributes: Date and Source Account -->
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:18px;">
                        <div class="row">
                            <!-- Voucher Date -->
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800; font-size:12px; margin-bottom:4px; color:#334155;">
                                    <i class="fa fa-calendar text-primary"></i> <?php echo __('Voucher Date');?>
                                    <?php if(!$allowCustomDate): ?>
                                        <span class="badge" style="background:#64748b; font-size:10px; margin-left:4px;"><?php echo __('Locked by Setting');?></span>
                                    <?php endif; ?>
                                </label>
                                <?php if($allowCustomDate): ?>
                                    <input type="date" name="data[BulkVoucher][entry_date]" id="bulkEntryDate" class="form-control" value="<?php echo $todayIso;?>" max="<?php echo date('Y-m-d', strtotime('+1 day'));?>" style="font-weight:700;">
                                    <span class="help-block" style="font-size:11px; margin-top:3px; margin-bottom:0; color:#64748b;">
                                        <?php echo __('Changeable voucher posting date.');?>
                                    </span>
                                <?php else: ?>
                                    <input type="text" readonly class="form-control" value="<?php echo $todayDisplay;?> (Today)" style="background:#f1f5f9; font-weight:700;">
                                    <input type="hidden" name="data[BulkVoucher][entry_date]" value="<?php echo $todayIso;?>">
                                    <span class="help-block" style="font-size:11px; margin-top:3px; margin-bottom:0; color:#64748b;">
                                        <?php echo __('Date locked to today. Can be unlocked in Settings.');?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Payment/Receipt Source Account -->
                            <div class="col-sm-5">
                                <label style="display:block; font-weight:800; font-size:12px; margin-bottom:4px; color:#334155;">
                                    <i class="fa fa-university text-primary"></i> 
                                    <?php echo ($activeKind==='income') ? __('Deposit To (Cash / Bank Account)') : __('Disburse From (Cash / Bank Account)');?>
                                </label>
                                <select name="data[BulkVoucher][source_ledger_id]" id="bulkSourceLedger" class="form-control" style="font-weight:700;">
                                    <?php if(empty($sourceOptions)): ?>
                                        <option value="0"><?php echo __('No Cash/Bank accounts found');?></option>
                                    <?php else: ?>
                                        <?php foreach($sourceOptions as $sId => $sName): ?>
                                            <option value="<?php echo $sId;?>" <?php echo ($sId == $defaultSourceLedgerId) ? 'selected' : '';?>>
                                                <?php echo h($sName);?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div id="sourceBalanceNote" style="font-size:11.5px; margin-top:4px; color:#0369a1; font-weight:700;">
                                    <i class="fa fa-info-circle"></i> <?php echo __('Current Balance:');?> <span id="sourceBalanceDisplay">PKR 0.00</span>
                                </div>
                            </div>

                            <!-- Safeguard Notice Banner -->
                            <div class="col-sm-3" style="display:flex; align-items:center;">
                                <div class="safeguard-badge" style="width:100%; padding:10px;">
                                    <i class="fa fa-shield" style="font-size:18px; color:#10b981;"></i>
                                    <div>
                                        <div style="font-weight:800; font-size:11.5px; color:#065f46;"><?php echo __('Zero-Amount Protection Active');?></div>
                                        <div style="font-size:10.5px; color:#047857; line-height:1.2;"><?php echo __('Rows with 0.00 or empty amounts are automatically skipped. No blank ledgers are posted.');?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- The Bulk Vouchers Table -->
                    <div class="table-responsive" style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
                        <table class="table ard-table-bulk" id="bulkTable" style="margin-bottom:0;">
                            <thead>
                                <tr>
                                    <th style="width:40px; text-align:center;">#</th>
                                    <th style="width:250px;"><?php echo ($activeKind==='income') ? __('Income Account Head') : __('Expense Account Head');?></th>
                                    <th style="width:170px; text-align:right;"><?php echo __('Amount (PKR)');?> *</th>
                                    <th><?php echo __('Narration / Description');?></th>
                                    <th style="width:140px;"><?php echo __('Bill / Ref #');?></th>
                                    <th style="width:160px;"><?php echo __('Site / Project (Optional)');?></th>
                                    <th style="width:50px; text-align:center;"><i class="fa fa-trash"></i></th>
                                </tr>
                            </thead>
                            <tbody id="bulkTableBody">
                                <?php
                                $rowIndex = 0;
                                if(!empty($presetRows)):
                                    foreach($presetRows as $pLedger):
                                        $plId = (int)$pLedger['id'];
                                        $plName = trim((string)$pLedger['name']);
                                        $plCat = trim((string)$pLedger['category']);
                                ?>
                                    <tr class="bulk-row" data-index="<?php echo $rowIndex;?>">
                                        <td style="text-align:center; font-weight:700; color:#64748b;" class="row-num">
                                            <?php echo ($rowIndex + 1);?>
                                        </td>
                                        <td>
                                            <select name="data[BulkVoucher][rows][<?php echo $rowIndex;?>][ledger_id]" class="form-control input-sm bulk-row-ledger" style="font-weight:700;">
                                                <option value="<?php echo $plId;?>" selected>
                                                    <?php echo h($plName);?><?php echo ($plCat !== '') ? ' ('.h($plCat).')' : '';?>
                                                </option>
                                                <?php foreach($allLedgers as $al): ?>
                                                    <?php if((int)$al['Ardledger']['id'] !== $plId): ?>
                                                        <option value="<?php echo (int)$al['Ardledger']['id'];?>">
                                                            <?php echo h($al['Ardledger']['name']);?><?php echo (!empty($al['Ardledger']['category'])) ? ' ('.h($al['Ardledger']['category']).')' : '';?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" placeholder="0.00" name="data[BulkVoucher][rows][<?php echo $rowIndex;?>][amount]" class="form-control input-sm bulk-row-amount" <?php echo ($rowIndex === 0) ? 'autofocus' : '';?>>
                                        </td>
                                        <td>
                                            <input type="text" placeholder="<?php echo ($activeKind==='income') ? __('e.g. Booking installment receipt') : __('e.g. Monthly office supplies, daily tea/mess');?>" name="data[BulkVoucher][rows][<?php echo $rowIndex;?>][description]" class="form-control input-sm bulk-row-desc">
                                        </td>
                                        <td>
                                            <input type="text" placeholder="<?php echo __('Invoice/Bill #');?>" name="data[BulkVoucher][rows][<?php echo $rowIndex;?>][ref_no]" class="form-control input-sm bulk-row-ref">
                                        </td>
                                        <td>
                                            <select name="data[BulkVoucher][rows][<?php echo $rowIndex;?>][construction_site_id]" class="form-control input-sm bulk-row-site">
                                                <option value="0"><?php echo __('None / General');?></option>
                                                <?php if(!empty($siteOptions)): ?>
                                                    <?php foreach($siteOptions as $sId => $sName): ?>
                                                        <option value="<?php echo $sId;?>"><?php echo h($sName);?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </td>
                                        <td style="text-align:center;">
                                            <button type="button" class="btn btn-xs btn-default text-danger js-remove-row" title="<?php echo __('Remove this record');?>">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php
                                        $rowIndex++;
                                    endforeach;
                                endif;
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Row Controls: [+ Add One More Record] -->
                    <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
                        <button type="button" id="addBulkRowBtn" class="btn btn-primary" style="font-weight:700; background:#4338ca; border-color:#3730a3; padding:8px 16px; border-radius:6px;">
                            <i class="fa fa-plus-circle"></i> <?php echo __('+ Add One More Record');?>
                        </button>

                        <div style="font-size:12px; color:#64748b;">
                            <i class="fa fa-info-circle"></i> <?php echo __('Tip: You can add as many dynamic rows as needed or remove unneeded rows using the (X) button.');?>
                        </div>
                    </div>

                    <!-- Live Summary & Submission Bar -->
                    <div class="ard-summary-bar">
                        <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                            <div class="ard-stat-box">
                                <div>
                                    <div class="ard-stat-label"><?php echo __('Total Records');?></div>
                                    <div class="ard-stat-num" id="statTotalRows">0</div>
                                </div>
                            </div>
                            <div class="ard-stat-box">
                                <div>
                                    <div class="ard-stat-label"><?php echo __('To Be Posted (> 0)');?></div>
                                    <div class="ard-stat-num" style="color:#4ade80;" id="statToPost">0</div>
                                </div>
                            </div>
                            <div class="ard-stat-box">
                                <div>
                                    <div class="ard-stat-label"><?php echo __('Ignored / Skipped (0 / Blank)');?></div>
                                    <div class="ard-stat-num" style="color:#94a3b8;" id="statToSkip">0</div>
                                </div>
                            </div>
                            <div style="border-left:1px solid #334155; padding-left:18px;">
                                <div class="ard-stat-label"><?php echo __('Grand Total PKR');?></div>
                                <div class="ard-grand-total" id="statGrandTotal">PKR 0.00</div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" id="submitBulkBtn" class="btn btn-lg btn-success" style="font-weight:800; padding:12px 28px; border-radius:8px; box-shadow:0 4px 6px rgba(22,163,74,0.3);">
                                <i class="fa fa-check-circle"></i> 
                                <?php echo ($activeKind==='income') ? __('Post Bulk Income Vouchers') : __('Post Bulk Expense Vouchers');?>
                            </button>
                        </div>
                    </div>

                </div>
                <?php echo $this->Form->end(); ?>

            </div>

        </div>
    </div>
</div>

<!-- Template for Dynamic Row Addition -->
<script type="text/template" id="rowTemplate">
    <tr class="bulk-row" data-index="__INDEX__">
        <td style="text-align:center; font-weight:700; color:#64748b;" class="row-num">
            __NUM__
        </td>
        <td>
            <select name="data[BulkVoucher][rows][__INDEX__][ledger_id]" class="form-control input-sm bulk-row-ledger" style="font-weight:700;">
                <option value=""><?php echo __('Select Account Head...');?></option>
                <?php foreach($allLedgers as $al): ?>
                    <option value="<?php echo (int)$al['Ardledger']['id'];?>">
                        <?php echo h($al['Ardledger']['name']);?><?php echo (!empty($al['Ardledger']['category'])) ? ' ('.h($al['Ardledger']['category']).')' : '';?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" min="0" placeholder="0.00" name="data[BulkVoucher][rows][__INDEX__][amount]" class="form-control input-sm bulk-row-amount">
        </td>
        <td>
            <input type="text" placeholder="<?php echo ($activeKind==='income') ? __('e.g. Booking installment receipt') : __('e.g. Office daily expenses, fuel receipt');?>" name="data[BulkVoucher][rows][__INDEX__][description]" class="form-control input-sm bulk-row-desc">
        </td>
        <td>
            <input type="text" placeholder="<?php echo __('Invoice/Bill #');?>" name="data[BulkVoucher][rows][__INDEX__][ref_no]" class="form-control input-sm bulk-row-ref">
        </td>
        <td>
            <select name="data[BulkVoucher][rows][__INDEX__][construction_site_id]" class="form-control input-sm bulk-row-site">
                <option value="0"><?php echo __('None / General');?></option>
                <?php if(!empty($siteOptions)): ?>
                    <?php foreach($siteOptions as $sId => $sName): ?>
                        <option value="<?php echo $sId;?>"><?php echo h($sName);?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </td>
        <td style="text-align:center;">
            <button type="button" class="btn btn-xs btn-default text-danger js-remove-row" title="<?php echo __('Remove this record');?>">
                <i class="fa fa-times"></i>
            </button>
        </td>
    </tr>
</script>

<script type="text/javascript">
jQuery(function($){
    var sourceBalances = <?php echo json_encode($sourceBalances);?> || {};
    var rowCounter = <?php echo max(1, $rowIndex);?>;

    // Format currency
    function formatPKR(val) {
        return 'PKR ' + parseFloat(val || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Update Source Balance Badge
    function updateSourceBalance() {
        var sId = parseInt($('#bulkSourceLedger').val(), 10) || 0;
        var bal = sourceBalances[sId] !== undefined ? parseFloat(sourceBalances[sId]) : 0;
        $('#sourceBalanceDisplay').text(formatPKR(bal));
    }
    $('#bulkSourceLedger').on('change', updateSourceBalance);
    updateSourceBalance();

    // Agency selector change (Super Admin)
    $('#agencySelector').on('change', function(){
        var targetProj = $(this).val();
        window.location = '<?php echo $this->Html->url(array("action"=>"bulkvouchers"));?>/' + targetProj + '?type=<?php echo $activeKind;?>';
    });

    // Recalculate Live Summary
    function recalculateSummary() {
        var totalRows = 0;
        var toPost = 0;
        var toSkip = 0;
        var grandTotal = 0.0;

        $('#bulkTableBody tr.bulk-row').each(function(idx){
            totalRows++;
            $(this).find('.row-num').text(idx + 1);

            var ledgerId = parseInt($(this).find('.bulk-row-ledger').val(), 10) || 0;
            var amountRaw = $(this).find('.bulk-row-amount').val();
            var amount = parseFloat(amountRaw) || 0;

            if(amount > 0 && ledgerId > 0) {
                toPost++;
                grandTotal += amount;
                $(this).find('.bulk-row-amount').addClass('has-val');
            } else {
                toSkip++;
                $(this).find('.bulk-row-amount').removeClass('has-val');
            }
        });

        $('#statTotalRows').text(totalRows);
        $('#statToPost').text(toPost);
        $('#statToSkip').text(toSkip);
        $('#statGrandTotal').text(formatPKR(grandTotal));

        if(toPost === 0) {
            $('#submitBulkBtn').prop('disabled', true).css('opacity', '0.6');
        } else {
            $('#submitBulkBtn').prop('disabled', false).css('opacity', '1');
        }
    }

    // Dynamic Row Event Listeners
    $(document).on('input keyup change', '.bulk-row-amount, .bulk-row-ledger', function(){
        recalculateSummary();
    });

    // Add One More Record Button
    $('#addBulkRowBtn').on('click', function(){
        var tmpl = $('#rowTemplate').html();
        var num = $('#bulkTableBody tr.bulk-row').length + 1;
        var rendered = tmpl.replace(/__INDEX__/g, rowCounter).replace(/__NUM__/g, num);
        rowCounter++;

        var $newRow = $(rendered);
        $('#bulkTableBody').append($newRow);
        $newRow.find('.bulk-row-amount').focus();
        recalculateSummary();
    });

    // Remove Row Button
    $(document).on('click', '.js-remove-row', function(){
        var $rows = $('#bulkTableBody tr.bulk-row');
        if($rows.length <= 1) {
            // If only 1 row left, just clear it instead of deleting
            var $row = $(this).closest('tr');
            $row.find('.bulk-row-amount').val('');
            $row.find('.bulk-row-desc').val('');
            $row.find('.bulk-row-ref').val('');
            recalculateSummary();
            return;
        }
        $(this).closest('tr').remove();
        recalculateSummary();
    });

    // Form Submit Confirmation
    $('#bulkForm').on('submit', function(e){
        var toPost = parseInt($('#statToPost').text(), 10) || 0;
        var toSkip = parseInt($('#statToSkip').text(), 10) || 0;
        var grandTotal = $('#statGrandTotal').text();

        if(toPost <= 0) {
            alert('<?php echo __("Please enter an amount greater than 0 on at least one row before submitting.");?>');
            e.preventDefault();
            return false;
        }

        var confirmMsg = 'Confirm posting ' + toPost + ' vouchers totaling ' + grandTotal + '?\n\n' +
                         '(' + toSkip + ' rows with 0 or blank amounts will be safely ignored with no database entry).';
        if(!confirm(confirmMsg)) {
            e.preventDefault();
            return false;
        }
    });

    // Initial summary calculation
    recalculateSummary();
});
</script>
