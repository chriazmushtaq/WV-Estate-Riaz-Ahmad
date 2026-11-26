<style type="text/css">
.wv-verify-wrap {
    max-width: 680px;
    margin: 40px auto;
    padding: 0 16px;
}
.wv-verify-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.1);
    overflow: hidden;
    border: 1px solid #dfe9f5;
}
.wv-verify-banner {
    padding: 32px 24px;
    text-align: center;
    color: #ffffff;
}
.wv-verify-banner.success {
    background: linear-gradient(135deg, #065f46 0%, #047857 60%, #10b981 100%);
}
.wv-verify-banner.failed {
    background: linear-gradient(135deg, #9f1239 0%, #be123c 60%, #f43f5e 100%);
}
.wv-verify-icon {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    margin-bottom: 12px;
    border: 2px solid rgba(255, 255, 255, 0.35);
}
.wv-verify-title {
    font-size: 22px;
    font-weight: 900;
    margin: 0 0 6px;
    color: #ffffff !important;
}
.wv-verify-sub {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.88);
    margin: 0;
}
.wv-verify-body {
    padding: 28px 32px;
}
.wv-verify-table {
    width: 100%;
    margin-bottom: 20px;
}
.wv-verify-table tr td {
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
}
.wv-verify-table tr:last-child td {
    border-bottom: none;
}
.wv-verify-table td.label {
    color: #64748b;
    font-weight: 700;
    width: 40%;
}
.wv-verify-table td.val {
    color: #0f172a;
    font-weight: 800;
}
</style>

<div class="wv-verify-wrap">
    <?php if(!empty($isValid) && !empty($allotment['BallotingAllotment'])):
        $a = $allotment['BallotingAllotment'];
        $projName = !empty($allotment['Project']['name']) ? $allotment['Project']['name'] : 'White Villas';
        $clientName = !empty($allotment['Client']['name']) ? $allotment['Client']['name'] : 'Verified Allottee';
        $plotNo = !empty($a['property_no']) ? $a['property_no'] : 'Allotted';
        $block = !empty($a['block_name']) ? $a['block_name'] : 'General Block';
        $street = !empty($a['street_no']) ? $a['street_no'] : 'Main';
        $allotNo = !empty($a['allotment_no']) ? $a['allotment_no'] : ('WV-' . $a['id']);
        $allotDate = !empty($a['allotment_date']) ? date('d-M-Y', strtotime($a['allotment_date'])) : date('d-M-Y');
    ?>
    <div class="wv-verify-card">
        <div class="wv-verify-banner success">
            <div class="wv-verify-icon">
                <i class="fa fa-shield"></i>
            </div>
            <h2 class="wv-verify-title"><?php echo __('OFFICIALLY VERIFIED ALLOTMENT'); ?></h2>
            <p class="wv-verify-sub"><?php echo __('This plot allocation certificate is authentic and recorded in society registry.'); ?></p>
        </div>
        <div class="wv-verify-body">
            <table class="wv-verify-table">
                <tr>
                    <td class="label"><?php echo __('Project / Society:'); ?></td>
                    <td class="val"><?php echo h($projName); ?></td>
                </tr>
                <tr>
                    <td class="label"><?php echo __('Allotment Certificate Ref:'); ?></td>
                    <td class="val"><span class="badge" style="background:#07152a; font-size:12px;"><?php echo h($allotNo); ?></span></td>
                </tr>
                <tr>
                    <td class="label"><?php echo __('Plot / Unit Number:'); ?></td>
                    <td class="val" style="color:#b45309; font-size:17px;"><?php echo h($plotNo); ?></td>
                </tr>
                <tr>
                    <td class="label"><?php echo __('Block / Sector:'); ?></td>
                    <td class="val"><?php echo h($block); ?></td>
                </tr>
                <tr>
                    <td class="label"><?php echo __('Street:'); ?></td>
                    <td class="val"><?php echo h($street); ?></td>
                </tr>
                <tr>
                    <td class="label"><?php echo __('Allottee Name:'); ?></td>
                    <td class="val"><?php echo h($clientName); ?></td>
                </tr>
                <tr>
                    <td class="label"><?php echo __('Allotment Date:'); ?></td>
                    <td class="val"><?php echo h($allotDate); ?></td>
                </tr>
                <tr>
                    <td class="label"><?php echo __('Registry Status:'); ?></td>
                    <td class="val" style="color:#059669;"><i class="fa fa-check-circle"></i> <?php echo __('Confirmed & Active'); ?></td>
                </tr>
            </table>

            <div style="text-align:center; margin-top:20px;">
                <a href="<?php echo $this->Html->url(array('action' => 'certificate', $a['id'], '?' => array('token' => $this->request->query['token']))); ?>" target="_blank" class="btn btn-primary" style="background:#07152a; border-radius:999px; font-weight:800; padding:10px 24px;">
                    <i class="fa fa-print"></i> <?php echo __('View Printable Official Certificate'); ?>
                </a>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="wv-verify-card">
        <div class="wv-verify-banner failed">
            <div class="wv-verify-icon">
                <i class="fa fa-times"></i>
            </div>
            <h2 class="wv-verify-title"><?php echo __('INVALID OR UNVERIFIED CERTIFICATE'); ?></h2>
            <p class="wv-verify-sub"><?php echo __('The security token provided does not match our official records.'); ?></p>
        </div>
        <div class="wv-verify-body" style="text-align:center;">
            <p style="color:#64748b; font-size:14px; margin-bottom:20px;">
                <?php echo __('Please verify the certificate QR code or contact White Villas Management for verification assistance.'); ?>
            </p>
            <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default" style="border-radius:999px; font-weight:700;">
                <?php echo __('Go to e-Balloting Portal'); ?>
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>
