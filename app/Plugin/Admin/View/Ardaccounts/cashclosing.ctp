<?php
$currency = isset($currency) ? $currency : 'Rs ';
$fmt = function($amount) use ($currency) {
    return $currency . number_format((float)$amount, 0);
};
?>
<style>
.cash-close-wrap .panel-body{background:#f5f8fc;}
.cash-close-card{background:#fff;border:1px solid #d8e3f1;border-radius:8px;padding:14px;margin-bottom:14px;}
.cash-close-title{font-weight:700;color:#153456;margin:0 0 10px;}
.cash-close-grid{display:grid;grid-template-columns:repeat(4,minmax(160px,1fr));gap:10px;margin-bottom:12px;}
.cash-close-stat{border:1px solid #dce7f5;background:#fbfdff;border-radius:8px;padding:10px;}
.cash-close-stat small{display:block;color:#667b99;text-transform:uppercase;font-weight:700;font-size:10px;}
.cash-close-stat strong{display:block;margin-top:5px;color:#0b2545;font-size:18px;}
.cash-close-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
@media(max-width:900px){.cash-close-grid{grid-template-columns:repeat(2,minmax(150px,1fr));}}
</style>
<div id="resultDiv" class="cash-close-wrap">
    <div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Daily Cash Closing');?></h1></div></div>
    <div class="panel">
        <?php echo $this->Session->flash();?>
        <div class="panel-heading">
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span> '.__('Back to Accounts'), array('action'=>'dashboard',$currentProjectId), array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-list"></span> '.__('Vouchers'), array('action'=>'transactions',$currentProjectId), array('escape'=>false,'class'=>'btn btn-primary'));?>
        </div>
        <div class="panel-body">
            <div class="cash-close-card">
                <h3 class="cash-close-title"><?php echo __('Closing Sheet');?></h3>
                <?php echo $this->Form->create('Ardcashclosing', array('class'=>'form-horizontal'));?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Date');?></label>
                    <div class="col-sm-3"><?php echo $this->Form->input('closing_date', array('type'=>'date','label'=>false,'div'=>false,'class'=>'form-control'));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Cash Ledger');?></label>
                    <div class="col-sm-5"><?php echo $this->Form->select('cash_ledger_id', $cashLedgerOptions, array('class'=>'form-control','label'=>false,'div'=>false));?></div>
                </div>
                <div class="cash-close-grid">
                    <div class="cash-close-stat"><small><?php echo __('Opening Cash');?></small><strong><?php echo $fmt($this->request->data['Ardcashclosing']['opening_cash']);?></strong></div>
                    <div class="cash-close-stat"><small><?php echo __('Received Cash');?></small><strong><?php echo $fmt($this->request->data['Ardcashclosing']['received_cash']);?></strong></div>
                    <div class="cash-close-stat"><small><?php echo __('Expenses');?></small><strong><?php echo $fmt($this->request->data['Ardcashclosing']['expense_cash']);?></strong></div>
                    <div class="cash-close-stat"><small><?php echo __('System Closing');?></small><strong><?php echo $fmt($this->request->data['Ardcashclosing']['closing_cash']);?></strong></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Bank Deposit');?></label>
                    <div class="col-sm-2"><?php echo $this->Form->input('bank_deposit', array('label'=>false,'div'=>false,'class'=>'form-control'));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Adjustment');?></label>
                    <div class="col-sm-2"><?php echo $this->Form->input('adjustment_amount', array('label'=>false,'div'=>false,'class'=>'form-control'));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Counted Cash');?></label>
                    <div class="col-sm-2"><?php echo $this->Form->input('counted_cash', array('label'=>false,'div'=>false,'class'=>'form-control'));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Status');?></label>
                    <div class="col-sm-3"><?php echo $this->Form->select('status', array('Draft'=>__('Draft'),'Approved'=>__('Approved'),'Posted'=>__('Posted')), array('class'=>'form-control','label'=>false,'div'=>false));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Note');?></label>
                    <div class="col-sm-5"><?php echo $this->Form->input('note', array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Optional note')));?></div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10 cash-close-actions">
                        <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Closing');?></button>
                        <?php if(!empty($this->request->data['Ardcashclosing']['id'])){ echo $this->Html->link('<span class="fa fa-print"></span> '.__('Print Closing Sheet'), array('action'=>'printcashclosing',(int)$this->request->data['Ardcashclosing']['id']), array('escape'=>false,'class'=>'btn btn-info','target'=>'_blank')); }?>
                    </div>
                </div>
                <?php echo $this->Form->end();?>
            </div>

            <div class="cash-close-card">
                <h3 class="cash-close-title"><?php echo __('Recent Closings');?></h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <tr><th><?php echo __('Date');?></th><th><?php echo __('Ledger');?></th><th><?php echo __('Opening');?></th><th><?php echo __('Received');?></th><th><?php echo __('Expenses');?></th><th><?php echo __('Closing');?></th><th><?php echo __('Status');?></th><th><?php echo __('Action');?></th></tr>
                        <?php if(empty($recentClosings)){?><tr><td colspan="8" class="text-center text-muted"><?php echo __('No closing has been saved yet.');?></td></tr><?php }?>
                        <?php foreach((array)$recentClosings as $closingRow): $r=$closingRow['Ardcashclosing']; ?>
                        <tr>
                            <td><?php echo h(date('d-M-Y', strtotime($r['closing_date'])));?></td>
                            <td><?php echo !empty($cashLedgerOptions[(int)$r['cash_ledger_id']]) ? h($cashLedgerOptions[(int)$r['cash_ledger_id']]) : '#'.(int)$r['cash_ledger_id'];?></td>
                            <td><?php echo $fmt($r['opening_cash']);?></td>
                            <td><?php echo $fmt($r['received_cash']);?></td>
                            <td><?php echo $fmt($r['expense_cash']);?></td>
                            <td><?php echo $fmt($r['closing_cash']);?></td>
                            <td><span class="label label-<?php echo ($r['status']==='Draft' ? 'warning' : 'success');?>"><?php echo h($r['status']);?></span></td>
                            <td><?php echo $this->Html->link(__('Print'), array('action'=>'printcashclosing',(int)$r['id']), array('class'=>'btn btn-xs btn-info','target'=>'_blank'));?></td>
                        </tr>
                        <?php endforeach;?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
