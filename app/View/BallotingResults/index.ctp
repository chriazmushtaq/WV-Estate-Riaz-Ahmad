<style type="text/css">
.wv-ballot-page {
    background: #f4f7fb;
    margin: -10px -5px 0;
    padding: 14px;
    min-height: 85vh;
}
.wv-ballot-hero {
    background: linear-gradient(135deg, #07152a 0%, #0e294b 60%, #ff0a46 160%);
    border-radius: 16px;
    padding: 28px 32px;
    color: #ffffff;
    margin-bottom: 24px;
    box-shadow: 0 16px 36px rgba(7, 21, 42, 0.14);
    position: relative;
    overflow: hidden;
}
.wv-ballot-hero h1 {
    font-size: 26px;
    font-weight: 900;
    margin: 0 0 8px;
    color: #ffffff !important;
    display: flex;
    align-items: center;
    gap: 12px;
}
.wv-ballot-hero p {
    font-size: 13.5px;
    color: #cbd5e1;
    margin: 0;
    max-width: 720px;
    line-height: 1.5;
}
.wv-announcement-box {
    background: rgba(124, 249, 255, 0.1);
    border: 1px solid rgba(124, 249, 255, 0.25);
    border-radius: 10px;
    padding: 12px 16px;
    margin-top: 16px;
    font-size: 13px;
    color: #e0f2fe;
    display: flex;
    align-items: center;
    gap: 10px;
}
.wv-search-card {
    background: #ffffff;
    border: 1px solid #dfe9f5;
    border-radius: 14px;
    padding: 22px 24px;
    margin-bottom: 24px;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
}
.wv-search-title {
    font-size: 16px;
    font-weight: 800;
    color: #07152a;
    margin: 0 0 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.wv-search-input-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.wv-search-input {
    flex: 1 1 280px;
    height: 48px;
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    padding: 0 16px;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
}
.wv-search-input:focus {
    border-color: #ff0a46;
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 10, 70, 0.1);
}
.wv-search-btn {
    background: #ff0a46;
    color: #ffffff !important;
    border: none;
    border-radius: 10px;
    padding: 0 24px;
    height: 48px;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(255, 10, 70, 0.25);
    transition: all 0.2s ease;
}
.wv-search-btn:hover {
    background: #e0003c;
    transform: translateY(-1px);
}
.wv-allotment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}
.wv-allotment-card {
    background: #ffffff;
    border: 1px solid #dfe9f5;
    border-radius: 16px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    overflow: hidden;
    position: relative;
    transition: all 0.25s ease;
}
.wv-allotment-card:hover {
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.12);
    transform: translateY(-2px);
}
.wv-allotment-header {
    background: linear-gradient(135deg, #07152a 0%, #102746 100%);
    padding: 18px 20px;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.wv-allotment-title {
    font-size: 15px;
    font-weight: 800;
    margin: 0;
    color: #ffffff !important;
}
.wv-allotment-badge {
    background: #10b981;
    color: #ffffff;
    font-size: 11px;
    font-weight: 900;
    padding: 4px 10px;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.wv-allotment-body {
    padding: 20px;
}
.wv-allotment-highlight {
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    padding: 14px 16px;
    text-align: center;
    margin-bottom: 16px;
}
.wv-plot-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 4px;
}
.wv-plot-value {
    font-size: 26px;
    font-weight: 900;
    color: #07152a;
    line-height: 1.1;
}
.wv-spec-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}
.wv-spec-row:last-child {
    border-bottom: none;
}
.wv-spec-label {
    color: #64748b;
    font-weight: 700;
}
.wv-spec-val {
    color: #0f172a;
    font-weight: 800;
}
.wv-cert-btn {
    background: #07152a;
    color: #ffffff !important;
    display: block;
    width: 100%;
    text-align: center;
    border-radius: 10px;
    padding: 12px;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none !important;
    margin-top: 16px;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(7, 21, 42, 0.2);
}
.wv-cert-btn:hover {
    background: #ff0a46;
    color: #ffffff !important;
}
.wv-search-result-box {
    margin-top: 18px;
    padding: 18px;
    border-radius: 12px;
}
.wv-result-allotted {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
}
.wv-result-pending {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
}
.wv-result-none {
    background: #fff1f2;
    border: 1px solid #fecdd3;
}
</style>

<div class="wv-ballot-page">
    <?php echo $this->Session->flash(); ?>

    <!-- Hero Banner -->
    <div class="wv-ballot-hero">
        <h1><i class="fa fa-cube text-info"></i> <?php echo __('e-Balloting Results & Digital Plot Allocation'); ?></h1>
        <p><?php echo __('Official digital allocation portal for White Villas. Verify your allotted plot coordinates, block and street dimensions, and download your official QR-verified allocation certificate.'); ?></p>

        <?php if(!empty($announcements)): ?>
        <?php foreach($announcements as $ann): ?>
        <div class="wv-announcement-box">
            <i class="fa fa-bullhorn text-warning"></i>
            <span><?php echo h($ann); ?></span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Live Allotment Search Bar -->
    <div class="wv-search-card">
        <h3 class="wv-search-title"><i class="fa fa-search text-danger"></i> <?php echo __('Live Balloting Allotment Status Search'); ?></h3>
        <p style="font-size:12.5px; color:#64748b; margin:0 0 14px;"><?php echo __('Enter your Deal / Invoice Number, Registration No, or 13-digit CNIC to check your real-time balloting allotment status:'); ?></p>
        <form method="get" action="<?php echo $this->Html->url(array('action' => 'index')); ?>">
            <div class="wv-search-input-group">
                <input type="text" name="q" class="wv-search-input" placeholder="<?php echo __('e.g. WV-155, 35201-XXXXXXX-X, or Plot No'); ?>" value="<?php echo h($searchQuery); ?>" required="required">
                <button type="submit" class="wv-search-btn">
                    <i class="fa fa-search"></i> <?php echo __('Check Balloting Result'); ?>
                </button>
            </div>
        </form>

        <!-- Search Results Card -->
        <?php if($searchPerformed && !empty($searchResult)): ?>
            <?php if($searchResult['status'] === 'ALLOTTED'): ?>
            <div class="wv-search-result-box wv-result-allotted">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                    <div>
                        <h4 style="margin:0 0 4px; font-weight:900; color:#065f46;">
                            <i class="fa fa-check-circle"></i> <?php echo __('Plot Allotted Successfully!'); ?>
                        </h4>
                        <p style="margin:0; font-size:13px; color:#047857;">
                            <?php echo __('Allottee:'); ?> <strong><?php echo h($searchResult['client_name']); ?></strong> | <?php echo __('Project:'); ?> <strong><?php echo h($searchResult['project_name']); ?></strong>
                        </p>
                    </div>
                    <div>
                        <a href="<?php echo h($searchResult['certificate_url']); ?>" target="_blank" class="btn btn-success" style="font-weight:800; border-radius:8px;">
                            <i class="fa fa-file-text-o"></i> <?php echo __('Download QR Certificate'); ?>
                        </a>
                    </div>
                </div>
                <div class="row" style="margin-top:14px;">
                    <div class="col-sm-3 col-xs-6"><strong>Plot / Unit:</strong> <?php echo h($searchResult['property_no']); ?></div>
                    <div class="col-sm-3 col-xs-6"><strong>Block / Sector:</strong> <?php echo h($searchResult['block_name']); ?></div>
                    <div class="col-sm-3 col-xs-6"><strong>Street No:</strong> <?php echo h($searchResult['street_no']); ?></div>
                    <div class="col-sm-3 col-xs-6"><strong>Size / Area:</strong> <?php echo h($searchResult['unit_area']); ?></div>
                </div>
            </div>
            <?php elseif($searchResult['status'] === 'PENDING_BALLOTING'): ?>
            <div class="wv-search-result-box wv-result-pending">
                <h4 style="margin:0 0 6px; font-weight:800; color:#1e40af;">
                    <i class="fa fa-info-circle"></i> <?php echo __('Deal Verified - Queued for Next Balloting'); ?>
                </h4>
                <p style="margin:0; font-size:13px; color:#1d4ed8;"><?php echo h($searchResult['message']); ?></p>
            </div>
            <?php else: ?>
            <div class="wv-search-result-box wv-result-none">
                <h4 style="margin:0 0 6px; font-weight:800; color:#9f1239;">
                    <i class="fa fa-exclamation-circle"></i> <?php echo __('No Allotment Record Found'); ?>
                </h4>
                <p style="margin:0; font-size:13px; color:#be123c;"><?php echo h($searchResult['message']); ?></p>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Client's Allotted Units Section (Logged in View) -->
    <?php if(!empty($clientId)): ?>
    <h3 style="font-size:18px; font-weight:900; color:#07152a; margin:28px 0 16px;">
        <i class="fa fa-key text-warning"></i> <?php echo __('My Allotted Properties & Plots'); ?>
    </h3>

    <?php if(!empty($allotments)): ?>
    <div class="wv-allotment-grid">
        <?php foreach($allotments as $a):
            $allot = $a['BallotingAllotment'];
            $projName = !empty($a['Project']['name']) ? $a['Project']['name'] : 'White Villas';
            $propNo = !empty($allot['property_no']) ? $allot['property_no'] : (!empty($allot['allotment_no']) ? 'Unit #' . $allot['allotment_no'] : 'Allotted Unit');
            $block = !empty($allot['block_name']) ? $allot['block_name'] : 'Main Block';
            $street = !empty($allot['street_no']) ? $allot['street_no'] : 'Main';
            $size = $allot['unit_area'] . ' ' . (!empty($allot['area_unit']) ? $allot['area_unit'] : 'Marla');
            $category = !empty($allot['permanent_type']) ? $allot['permanent_type'] : 'Standard';
            $allotDate = !empty($allot['allotment_date']) ? date('d-M-Y', strtotime($allot['allotment_date'])) : date('d-M-Y');
        ?>
        <div class="wv-allotment-card">
            <div class="wv-allotment-header">
                <h4 class="wv-allotment-title"><?php echo h($projName); ?></h4>
                <span class="wv-allotment-badge"><?php echo __('Allotted'); ?></span>
            </div>
            <div class="wv-allotment-body">
                <div class="wv-allotment-highlight">
                    <div class="wv-plot-label"><?php echo __('Allotted Plot / Unit Number'); ?></div>
                    <div class="wv-plot-value"><?php echo h($propNo); ?></div>
                </div>
                <div class="wv-spec-row">
                    <span class="wv-spec-label"><?php echo __('Block / Sector:'); ?></span>
                    <span class="wv-spec-val"><?php echo h($block); ?></span>
                </div>
                <div class="wv-spec-row">
                    <span class="wv-spec-label"><?php echo __('Street:'); ?></span>
                    <span class="wv-spec-val"><?php echo h($street); ?></span>
                </div>
                <div class="wv-spec-row">
                    <span class="wv-spec-label"><?php echo __('Plot Size:'); ?></span>
                    <span class="wv-spec-val"><?php echo h($size); ?></span>
                </div>
                <div class="wv-spec-row">
                    <span class="wv-spec-label"><?php echo __('Category:'); ?></span>
                    <span class="wv-spec-val"><span class="badge" style="background:#07152a;"><?php echo h($category); ?></span></span>
                </div>
                <div class="wv-spec-row">
                    <span class="wv-spec-label"><?php echo __('Allotment Date:'); ?></span>
                    <span class="wv-spec-val"><?php echo h($allotDate); ?></span>
                </div>

                <a href="<?php echo $this->Html->url(array('action' => 'certificate', $allot['id'])); ?>" target="_blank" class="wv-cert-btn">
                    <i class="fa fa-qrcode"></i> <?php echo __('Download / Print Official QR Certificate'); ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="background:#ffffff; border:1px solid #dfe9f5; border-radius:14px; padding:28px; text-align:center; color:#64748b; margin-bottom:24px;">
        <i class="fa fa-cube" style="font-size:36px; color:#cbd5e1; margin-bottom:10px;"></i>
        <h4 style="font-weight:800; color:#0f172a; margin-bottom:4px;"><?php echo __('No Allotments Recorded Yet'); ?></h4>
        <p style="margin:0; font-size:13px;"><?php echo __('Your bookings are queued for upcoming balloting runs. Ensure your installments are up to date to qualify.'); ?></p>
    </div>
    <?php endif; ?>

    <!-- Other Deals Pending Balloting -->
    <?php if(!empty($clientDeals)): ?>
    <h3 style="font-size:16px; font-weight:800; color:#07152a; margin:24px 0 14px;">
        <i class="fa fa-hourglass-half text-info"></i> <?php echo __('Other Bookings Queued for Next Balloting'); ?>
    </h3>
    <div class="row">
        <?php foreach($clientDeals as $cd):
            $d = $cd['MyDeal'];
            $pName = !empty($cd['Property']['name']) ? $cd['Property']['name'] : 'Plot/Villa';
            $invoice = !empty($d['invoice_no']) ? $d['invoice_no'] : ('DEAL-' . $d['id']);
        ?>
        <div class="col-md-4 col-sm-6" style="margin-bottom:16px;">
            <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <strong style="color:#07152a; font-size:14px;"><?php echo h($pName); ?></strong>
                    <span class="badge badge-info" style="font-size:10px;"><?php echo __('QUEUED'); ?></span>
                </div>
                <div style="font-size:12px; color:#64748b; margin-bottom:4px;">Invoice: <strong><?php echo h($invoice); ?></strong></div>
                <div style="font-size:12px; color:#64748b;">Booking Date: <strong><?php echo !empty($d['date']) ? date('d-M-Y', strtotime($d['date'])) : ''; ?></strong></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>
