<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-shield text-success"></i> <?php echo __('QR Scan Forensic Details');?></h1>
        <p class="description"><?php echo __('Deep inspection of scan event #') . $log['QrScanLog']['id'];?></p>
    </div>
    <div class="breadcrumb-env">
        <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> '.__('Back to Scan Logs'), array('action'=>'index'), array('class'=>'btn btn-default', 'escape'=>false));?>
    </div>
</div>

<div class="panel panel-default" style="border-radius:12px;overflow:hidden;">
    <div class="panel-heading" style="background:#f8fafc;font-weight:800;">
        <span><i class="fa fa-info-circle text-primary"></i> <?php echo __('Verification Forensic Record');?></span>
    </div>
    <div class="panel-body" style="padding:24px;">
        <?php if (!empty($log['QrScanLog']['is_suspicious'])) { ?>
            <div class="alert alert-danger" style="font-weight:700;padding:14px 18px;border-radius:8px;margin-bottom:20px;">
                🚨 <?php echo __('CRITICAL SUSPICIOUS ALERT: ') . h($log['QrScanLog']['suspicious_reason']);?>
            </div>
        <?php } ?>

        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th style="width:35%;background:#f8fafc;"><?php echo __('Scan Log ID');?></th><td>#<?php echo $log['QrScanLog']['id'];?></td></tr>
                    <tr><th style="background:#f8fafc;"><?php echo __('Scan Time');?></th><td><?php echo date('d M Y, h:i:s A', strtotime($log['QrScanLog']['created']));?> PKT</td></tr>
                    <tr><th style="background:#f8fafc;"><?php echo __('Status');?></th><td><strong><?php echo h($log['QrScanLog']['scan_status']);?></strong></td></tr>
                    <tr><th style="background:#f8fafc;"><?php echo __('Reference No');?></th><td style="font-weight:700;color:#0284c7;"><?php echo h($log['QrScanLog']['reference_no']);?></td></tr>
                    <tr><th style="background:#f8fafc;"><?php echo __('Client Name');?></th><td style="font-weight:700;"><?php echo h($log['QrScanLog']['client_name']);?></td></tr>
                    <tr><th style="background:#f8fafc;"><?php echo __('Property Unit');?></th><td><?php echo h($log['QrScanLog']['property_name']);?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th style="width:35%;background:#f8fafc;"><?php echo __('IP Address');?></th><td style="font-family:monospace;font-weight:700;"><?php echo h($log['QrScanLog']['ip_address']);?></td></tr>
                    <tr><th style="background:#f8fafc;"><?php echo __('Approx Location');?></th><td><?php echo h($log['QrScanLog']['city']);?>, <?php echo h($log['QrScanLog']['country']);?></td></tr>
                    <tr><th style="background:#f8fafc;"><?php echo __('Device Type');?></th><td><?php echo h($log['QrScanLog']['device_type']);?></td></tr>
                    <tr><th style="background:#f8fafc;"><?php echo __('Browser');?></th><td><?php echo h($log['QrScanLog']['browser']);?></td></tr>
                    <tr><th style="background:#f8fafc;"><?php echo __('Operating System');?></th><td><?php echo h($log['QrScanLog']['os']);?></td></tr>
                    <tr><th style="background:#f8fafc;"><?php echo __('Cryptographic Token');?></th><td style="font-family:monospace;font-size:11px;"><?php echo h($log['QrScanLog']['token_attempted']);?></td></tr>
                </table>
            </div>
        </div>

        <div style="margin-top:16px;">
            <label style="font-weight:700;color:#64748b;"><?php echo __('Full HTTP User-Agent:');?></label>
            <pre style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px;border-radius:6px;"><?php echo h($log['QrScanLog']['user_agent']);?></pre>
        </div>
    </div>
</div>
