<style type="text/css">
.contact-view-wrap {
    max-width: 1040px !important;
    width: 95% !important;
    margin: 25px auto !important;
    font-family: inherit;
}
.contact-view-wrap .panel {
    border-radius: 12px !important;
    overflow: hidden !important;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.45) !important;
    border: 1px solid rgba(226, 232, 240, 0.8) !important;
    background: #ffffff !important;
}

/* ============================================================
   EXECUTIVE MODAL HEADER
   ============================================================ */
.contact-view-header {
    background: linear-gradient(135deg, #07152a 0%, #0f274a 60%, #1e3a8a 100%) !important;
    padding: 14px 20px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
}
.contact-view-header .header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.contact-view-header .header-icon-box {
    width: 38px;
    height: 38px;
    border-radius: 9px;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.22);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #7cf9ff;
    font-size: 17px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    flex-shrink: 0;
}
.contact-view-header .header-title-box h3 {
    color: #ffffff !important;
    font-size: 15px;
    font-weight: 800;
    margin: 0 0 2px 0;
    line-height: 1.2;
    letter-spacing: -0.01em;
}
.contact-view-header .header-sub-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.meta-badge-id {
    background: rgba(255, 255, 255, 0.16);
    color: #ffffff;
    font-size: 10px;
    font-weight: 800;
    padding: 1.5px 6px;
    border-radius: 4px;
    letter-spacing: 0.4px;
}
.meta-badge-cat {
    background: rgba(59, 130, 246, 0.3);
    border: 1px solid rgba(147, 197, 253, 0.4);
    color: #bfdbfe;
    font-size: 10px;
    font-weight: 700;
    padding: 1.5px 7px;
    border-radius: 4px;
}
.meta-badge-kyc {
    font-size: 10px;
    font-weight: 700;
    padding: 1.5px 7px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.meta-badge-kyc.kyc-verified {
    background: rgba(16, 185, 129, 0.25);
    border: 1px solid rgba(52, 211, 153, 0.45);
    color: #6ee7b7;
}
.meta-badge-kyc.kyc-partial {
    background: rgba(245, 158, 11, 0.25);
    border: 1px solid rgba(251, 191, 36, 0.45);
    color: #fde68a;
}
.meta-badge-kyc.kyc-pending {
    background: rgba(239, 68, 68, 0.25);
    border: 1px solid rgba(248, 113, 113, 0.45);
    color: #fca5a5;
}

.contact-view-header .header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}
.btn-header-print {
    background: rgba(255, 255, 255, 0.14) !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    padding: 5px 11px !important;
    border-radius: 6px !important;
    font-size: 11.5px !important;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease;
}
.btn-header-print:hover {
    background: rgba(255, 255, 255, 0.25) !important;
    color: #ffffff !important;
    transform: translateY(-1px);
}
.contact-view-header .close {
    background: rgba(255, 255, 255, 0.16) !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    border-radius: 50% !important;
    color: #ffffff !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 18px !important;
    height: 30px !important;
    width: 30px !important;
    opacity: 0.9 !important;
    transition: all 0.15s ease !important;
    text-shadow: none !important;
    line-height: 1 !important;
}
.contact-view-header .close:hover {
    opacity: 1 !important;
    background: rgba(239, 68, 68, 0.4) !important;
    border-color: rgba(239, 68, 68, 0.6) !important;
}

/* ============================================================
   PANEL BODY & HERO BANNER
   ============================================================ */
.contact-view-body {
    background: #f8fafc !important;
    padding: 16px 20px 20px !important;
}

.contact-hero-strip {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 14px;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
}
.hero-avatar-wrap {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    flex-shrink: 0;
}
.hero-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.hero-avatar-monogram {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: #ffffff;
    font-size: 18px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    letter-spacing: 0.5px;
}
.hero-info-wrap {
    flex: 1;
    min-width: 220px;
}
.hero-name-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.hero-name {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    line-height: 1.2;
    letter-spacing: -0.01em;
}
.hero-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 12px;
    line-height: 1.3;
}
.hero-status-pill.status-active {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.hero-status-pill.status-blocked {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}
.hero-meta-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 4px;
    font-size: 11.5px;
    color: #475569;
    flex-wrap: wrap;
}
.hero-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.hero-meta-item strong {
    color: #0f172a;
}
.hero-cnic-pill {
    background: #f1f5f9;
    padding: 1.5px 7px;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
    font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
}

.hero-quick-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.btn-quick-contact {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s ease;
}
.btn-quick-contact.call {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
.btn-quick-contact.call:hover {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
}
.btn-quick-contact.email {
    background: #f8fafc;
    color: #475569;
    border: 1px solid #cbd5e1;
}
.btn-quick-contact.email:hover {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
}

/* ============================================================
   TIGHT 2-COLUMN STRUCTURED DATA GRID
   ============================================================ */
.contact-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 14px;
}
@media (max-width: 767px) {
    .contact-details-grid {
        grid-template-columns: 1fr;
    }
}
.detail-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}
.detail-card-head {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 9px 14px;
    font-size: 11.5px;
    font-weight: 800;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.detail-card-head i {
    color: #2563eb;
    font-size: 13px;
}
.detail-card-body {
    padding: 4px 0;
}
.detail-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 7px 14px;
    border-bottom: 1px solid #f1f5f9;
    gap: 12px;
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    width: 36%;
    flex-shrink: 0;
    padding-top: 1px;
}
.detail-value {
    font-size: 12.5px;
    font-weight: 600;
    color: #0f172a;
    width: 64%;
    text-align: right;
    word-break: break-word;
}
.detail-value.highlight {
    font-weight: 800;
    color: #0f172a;
}
.detail-value.monospace {
    font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
    font-weight: 700;
}
.badge-cat-pill {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #dbeafe;
    padding: 1.5px 7px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
}
.status-badge-inline {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 4px;
    line-height: 1.3;
}
.status-badge-inline.verified,
.status-badge-inline.active {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}
.status-badge-inline.partial {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}
.status-badge-inline.pending,
.status-badge-inline.blocked {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
.link-tel,
.link-email {
    color: #2563eb;
    text-decoration: none;
    font-weight: 700;
}
.link-tel:hover,
.link-email:hover {
    color: #1d4ed8;
    text-decoration: underline;
}
.address-text,
.location-text {
    line-height: 1.35;
    color: #1e293b;
}

/* ============================================================
   TIGHT IDENTITY DOCUMENTS SECTION
   ============================================================ */
.contact-media-section {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}
.media-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f1f5f9;
}
.section-title-left {
    font-size: 12px;
    font-weight: 800;
    color: #0f172a;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.section-title-left i {
    color: #2563eb;
    font-size: 14px;
}
.doc-badge-pill {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
}

.media-cards-row {
    margin-left: -6px;
    margin-right: -6px;
}
.media-cards-row > div {
    padding-left: 6px;
    padding-right: 6px;
}
.doc-card {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #f8fafc;
    padding: 8px;
    margin-bottom: 8px;
    transition: all 0.15s ease;
}
.doc-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}
.doc-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}
.doc-title {
    font-size: 11px;
    font-weight: 800;
    color: #334155;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.doc-title i {
    color: #2563eb;
}
.doc-state {
    font-size: 9.5px;
    font-weight: 800;
    padding: 1px 6px;
    border-radius: 4px;
    line-height: 1.2;
}
.doc-state.ready {
    background: #dcfce7;
    color: #166534;
}
.doc-state.empty {
    background: #f1f5f9;
    color: #94a3b8;
}

.doc-preview-box {
    height: 112px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 3px;
    position: relative;
}
.doc-img {
    max-height: 104px;
    max-width: 100%;
    object-fit: contain;
    display: block;
    border-radius: 3px;
}
.empty-doc-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    color: #94a3b8;
}
.empty-doc-placeholder i {
    font-size: 26px;
    opacity: 0.6;
}
.empty-doc-placeholder span {
    font-size: 10.5px;
    font-weight: 700;
}

.doc-card-actions {
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.btn-doc-action {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 3.5px 6px;
    border-radius: 4px;
    font-size: 10.5px;
    font-weight: 700;
    text-decoration: none;
    line-height: 1.2;
    transition: all 0.12s ease;
}
.btn-doc-action.primary {
    background: #2563eb;
    color: #ffffff;
    border: 1px solid #2563eb;
}
.btn-doc-action.primary:hover {
    background: #1d4ed8;
    color: #ffffff;
}
.btn-doc-action.secondary {
    background: #ffffff;
    color: #334155;
    border: 1px solid #cbd5e1;
}
.btn-doc-action.secondary:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.no-doc-note {
    font-size: 10.5px;
    color: #94a3b8;
    font-weight: 600;
    padding: 3.5px 0;
    display: block;
    width: 100%;
    text-align: center;
}
</style>

<?php
if(!function_exists('wvResolveContactImage')){
    function wvResolveContactImage($filename, $customDir = ''){
        if(empty($filename)){
            return array('original' => '', 'thumb' => '');
        }
        $file = basename(trim((string)$filename));
        $dir = !empty($customDir) ? basename(trim((string)$customDir)) : 'contact';
        
        $candidates = array(
            'contact_thumb/' . $file,
            'contact/' . $file,
            $dir . '_thumb/' . $file,
            $dir . '/' . $file,
            'client_thumb/' . $file,
            'client/' . $file,
            'Uploads/clients/' . $file,
            'Uploads/images/' . $file
        );
        
        $original = '';
        $thumb = '';
        
        foreach(array_unique($candidates) as $candidate){
            $realPath = WWW_ROOT . 'img' . DS . str_replace('/', DS, $candidate);
            if(file_exists($realPath)){
                if($thumb === ''){
                    $thumb = $candidate;
                }
                if($original === '' && strpos($candidate, '_thumb') === false){
                    $original = '/img/' . $candidate;
                }
            }
        }
        
        if($thumb === ''){
            $thumb = 'contact_thumb/' . $file;
        }
        if($original === ''){
            $original = '/img/contact/' . $file;
        }
        
        return array('original' => $original, 'thumb' => $thumb);
    }
}

$profileImages = wvResolveContactImage(!empty($post['Contact']['photo']) ? $post['Contact']['photo'] : '', !empty($post['Contact']['dirt']) ? $post['Contact']['dirt'] : '');
$idFrontImages = wvResolveContactImage(!empty($post['Contact']['id_front']) ? $post['Contact']['id_front'] : '', !empty($post['Contact']['id_front_dir']) ? $post['Contact']['id_front_dir'] : '');
$idBackImages = wvResolveContactImage(!empty($post['Contact']['id_back']) ? $post['Contact']['id_back'] : '', !empty($post['Contact']['id_back_dir']) ? $post['Contact']['id_back_dir'] : '');
$sidePhotoImages = wvResolveContactImage(!empty($post['Contact']['side_photo']) ? $post['Contact']['side_photo'] : '', !empty($post['Contact']['side_photo_dir']) ? $post['Contact']['side_photo_dir'] : '');

$hasSidePhoto = !empty($post['Contact']['side_photo']);
$mediaColClass = $hasSidePhoto ? 'col-xs-6 col-sm-3' : 'col-xs-6 col-sm-4';

$hasCnicNo  = !empty(trim((string)$post['Contact']['pincode']));
$hasPhoto   = !empty(trim((string)$post['Contact']['photo']));
$hasIdFront = !empty(trim((string)$post['Contact']['id_front']));
$hasIdBack  = !empty(trim((string)$post['Contact']['id_back']));
$docScore   = ($hasCnicNo ? 1 : 0) + ($hasPhoto ? 1 : 0) + ($hasIdFront ? 1 : 0) + ($hasIdBack ? 1 : 0);
?>

<div class="container contact-view-wrap">
    <div class="panel panel-custom mrg">
        <!-- EXECUTIVE MODAL HEADER -->
        <div class="panel-heading contact-view-header">
            <div class="header-left">
                <div class="header-icon-box">
                    <i class="fa fa-id-badge"></i>
                </div>
                <div class="header-title-box">
                    <h3><?php echo __('Contact Profile & Verification'); ?></h3>
                    <div class="header-sub-meta">
                        <span class="meta-badge-id">ID #<?php echo h($post['Contact']['id']); ?></span>
                        <span class="meta-badge-cat"><?php echo h(!empty($post['Category']['name']) ? $post['Category']['name'] : __('Customer')); ?></span>
                        <?php if($docScore === 4): ?>
                            <span class="meta-badge-kyc kyc-verified"><i class="fa fa-check-circle"></i> <?php echo __('KYC Verified'); ?></span>
                        <?php elseif($docScore > 0): ?>
                            <span class="meta-badge-kyc kyc-partial"><i class="fa fa-exclamation-circle"></i> <?php echo sprintf(__('KYC Partial (%d/4)'), $docScore); ?></span>
                        <?php else: ?>
                            <span class="meta-badge-kyc kyc-pending"><i class="fa fa-clock-o"></i> <?php echo __('KYC Pending'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="header-actions">
                <a href="<?php echo $this->Html->url(array('controller' => 'Contacts', 'action' => 'printclient', $post['Contact']['id'])); ?>" target="_blank" class="btn btn-xs btn-header-print" title="<?php echo __('Print Client Dossier'); ?>">
                    <i class="fa fa-print"></i> <?php echo __('Print'); ?>
                </a>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" title="<?php echo __('Close'); ?>">&times;</button>
            </div>
        </div>

        <div class="panel-body contact-view-body">
            <?php echo $this->Session->flash(); ?>

            <!-- COMPACT HERO IDENTITY STRIP -->
            <div class="contact-hero-strip">
                <div class="hero-avatar-wrap">
                    <?php if(!empty($post['Contact']['photo'])){ ?>
                        <?php echo $this->Html->image($profileImages['thumb'], array('alt' => h($post['Contact']['name']), 'class' => 'hero-avatar-img')); ?>
                    <?php } else { ?>
                        <div class="hero-avatar-monogram"><?php echo strtoupper(mb_substr(trim((string)$post['Contact']['name']), 0, 2)); ?></div>
                    <?php } ?>
                </div>
                <div class="hero-info-wrap">
                    <div class="hero-name-row">
                        <h2 class="hero-name"><?php echo h($post['Contact']['name']); ?></h2>
                        <?php if(!empty($post['Contact']['app_login_status']) || !empty($post['Contact']['app_last_login'])){ ?>
                            <span class="hero-status-pill status-active"><i class="fa fa-circle"></i> <?php echo __('Login Active'); ?></span>
                        <?php } else { ?>
                            <span class="hero-status-pill status-blocked"><i class="fa fa-circle-o"></i> <?php echo __('Login Inactive'); ?></span>
                        <?php } ?>
                    </div>
                    <div class="hero-meta-row">
                        <?php if(!empty($post['Contact']['fathername'])): ?>
                            <span class="hero-meta-item">
                                <i class="fa fa-user-circle"></i> 
                                <?php echo !empty($post['Contact']['relation_type']) ? h($post['Contact']['relation_type']) : __('S/O'); ?>: 
                                <strong><?php echo h($post['Contact']['fathername']); ?></strong>
                            </span>
                        <?php endif; ?>
                        <?php if(!empty($post['Contact']['gender'])): ?>
                            <span class="hero-meta-item"><i class="fa fa-venus-mars"></i> <?php echo h($post['Contact']['gender']); ?></span>
                        <?php endif; ?>
                        <?php if(!empty($post['Contact']['pincode'])): ?>
                            <span class="hero-meta-item hero-cnic-pill"><i class="fa fa-id-card"></i> CNIC: <strong><?php echo h($post['Contact']['pincode']); ?></strong></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="hero-quick-actions">
                    <?php if(!empty($post['Contact']['mobile'])): ?>
                        <a href="tel:<?php echo h($post['Contact']['mobile']); ?>" class="btn-quick-contact call" title="<?php echo __('Call Mobile'); ?>">
                            <i class="fa fa-phone"></i> <?php echo h($post['Contact']['mobile']); ?>
                        </a>
                    <?php endif; ?>
                    <?php if(!empty($post['Contact']['email'])): ?>
                        <a href="mailto:<?php echo h($post['Contact']['email']); ?>" class="btn-quick-contact email" title="<?php echo __('Send Email'); ?>">
                            <i class="fa fa-envelope"></i> <?php echo h($post['Contact']['email']); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TIGHT 2-COLUMN STRUCTURED DATA GRID -->
            <div class="contact-details-grid">
                <!-- CARD 1: PERSONAL & IDENTIFICATION -->
                <div class="detail-card">
                    <div class="detail-card-head">
                        <i class="fa fa-address-card-o"></i> <?php echo __('Identity & Demographics'); ?>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-row">
                            <span class="detail-label"><?php echo __('Full Name'); ?></span>
                            <span class="detail-value highlight"><?php echo h($post['Contact']['name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><?php echo !empty($post['Contact']['relation_type']) ? h($post['Contact']['relation_type']).' '.__('Name') : __('Father / Guardian'); ?></span>
                            <span class="detail-value"><?php echo !empty($post['Contact']['fathername']) ? h($post['Contact']['fathername']) : '<span class="text-muted">'.__('N/A').'</span>'; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><?php echo __('CNIC / ID Number'); ?></span>
                            <span class="detail-value monospace"><?php echo !empty($post['Contact']['pincode']) ? h($post['Contact']['pincode']) : '<span class="text-muted">'.__('N/A').'</span>'; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><?php echo __('Category / Type'); ?></span>
                            <span class="detail-value"><span class="badge-cat-pill"><?php echo !empty($post['Category']['name']) ? h($post['Category']['name']) : __('Customer'); ?></span></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><?php echo __('Gender'); ?></span>
                            <span class="detail-value"><?php echo !empty($post['Contact']['gender']) ? h($post['Contact']['gender']) : '<span class="text-muted">'.__('N/A').'</span>'; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><?php echo __('KYC Verification'); ?></span>
                            <span class="detail-value">
                                <?php if($docScore === 4): ?>
                                    <span class="status-badge-inline verified"><i class="fa fa-check"></i> <?php echo __('Verified (4/4 Documents)'); ?></span>
                                <?php elseif($docScore > 0): ?>
                                    <span class="status-badge-inline partial"><i class="fa fa-info-circle"></i> <?php echo sprintf(__('Partial (%d/4 Uploaded)'), $docScore); ?></span>
                                <?php else: ?>
                                    <span class="status-badge-inline pending"><i class="fa fa-clock-o"></i> <?php echo __('Pending Uploads'); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: CONTACT & ADDRESS DETAILS -->
                <div class="detail-card">
                    <div class="detail-card-head">
                        <i class="fa fa-phone-square"></i> <?php echo __('Communication & Location'); ?>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-row">
                            <span class="detail-label"><?php echo __('Primary Mobile'); ?></span>
                            <span class="detail-value">
                                <?php if(!empty($post['Contact']['mobile'])): ?>
                                    <a href="tel:<?php echo h($post['Contact']['mobile']); ?>" class="link-tel"><i class="fa fa-phone"></i> <?php echo h($post['Contact']['mobile']); ?></a>
                                <?php else: ?>
                                    <span class="text-muted"><?php echo __('N/A'); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><?php echo __('Mobile SMS / Tel'); ?></span>
                            <span class="detail-value">
                                <?php if(!empty($post['Contact']['phone'])): ?>
                                    <a href="tel:<?php echo h($post['Contact']['phone']); ?>" class="link-tel"><i class="fa fa-commenting-o"></i> <?php echo h($post['Contact']['phone']); ?></a>
                                <?php else: ?>
                                    <span class="text-muted"><?php echo __('N/A'); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><?php echo __('Email Address'); ?></span>
                            <span class="detail-value">
                                <?php if(!empty($post['Contact']['email'])): ?>
                                    <a href="mailto:<?php echo h($post['Contact']['email']); ?>" class="link-email"><i class="fa fa-envelope-o"></i> <?php echo h($post['Contact']['email']); ?></a>
                                <?php else: ?>
                                    <span class="text-muted"><?php echo __('N/A'); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><?php echo __('Street Address'); ?></span>
                            <span class="detail-value address-text">
                                <i class="fa fa-home"></i> <?php echo !empty($post['Contact']['address']) ? h($post['Contact']['address']) : '<span class="text-muted">'.__('N/A').'</span>'; ?>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><?php echo __('District / Area'); ?></span>
                            <span class="detail-value location-text">
                                <i class="fa fa-map-marker"></i> <?php echo !empty($post['Contact']['address_hierarchy_path']) ? h($post['Contact']['address_hierarchy_path']) : (!empty($post['Contact']['district']) ? h($post['Contact']['district']).(!empty($post['Contact']['state']) ? ', '.h($post['Contact']['state']) : '') : '<span class="text-muted">'.__('N/A').'</span>'); ?>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><?php echo __('Login Access'); ?></span>
                            <span class="detail-value">
                                <?php if(!empty($post['Contact']['app_login_status']) || !empty($post['Contact']['app_last_login'])){ ?>
                                    <span class="status-badge-inline active"><i class="fa fa-check-circle"></i> <?php echo __('Active / Allowed'); ?></span>
                                <?php } else { ?>
                                    <span class="status-badge-inline blocked"><i class="fa fa-ban"></i> <?php echo __('Blocked / Inactive'); ?></span>
                                <?php } ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TIGHT IDENTITY DOCUMENTS SECTION -->
            <div class="contact-media-section">
                <div class="media-section-head">
                    <div class="section-title-left">
                        <i class="fa fa-shield"></i> <?php echo __('Uploaded Identity Documents & KYC Media'); ?>
                    </div>
                    <div class="section-title-right">
                        <span class="doc-badge-pill"><?php echo sprintf(__('%d of 4 Documents Attached'), $docScore); ?></span>
                    </div>
                </div>

                <div class="row media-cards-row">
                    <!-- PROFILE PHOTO -->
                    <div class="<?php echo $mediaColClass; ?>">
                        <div class="doc-card <?php echo !empty($post['Contact']['photo']) ? 'has-doc' : 'is-empty'; ?>">
                            <div class="doc-card-top">
                                <span class="doc-title"><i class="fa fa-user"></i> <?php echo __('Profile Photo'); ?></span>
                                <?php if(!empty($post['Contact']['photo'])): ?>
                                    <span class="doc-state ready"><i class="fa fa-check"></i> <?php echo __('Uploaded'); ?></span>
                                <?php else: ?>
                                    <span class="doc-state empty"><?php echo __('Missing'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="doc-preview-box">
                                <?php if(!empty($post['Contact']['photo'])){ ?>
                                    <?php echo $this->Html->image($profileImages['thumb'], array('alt' => __('Profile Photo'), 'class' => 'doc-img')); ?>
                                <?php } else { ?>
                                    <div class="empty-doc-placeholder">
                                        <i class="fa fa-user-circle-o"></i>
                                        <span><?php echo __('No Photo'); ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="doc-card-actions">
                                <?php if(!empty($post['Contact']['photo'])){ ?>
                                    <?php echo $this->Html->link('<i class="fa fa-expand"></i> '.__('View Full'), $profileImages['original'], array('class' => 'btn-doc-action primary', 'target' => '_blank', 'escape' => false)); ?>
                                    <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download'), $profileImages['original'], array('class' => 'btn-doc-action secondary', 'target' => '_blank', 'download' => $post['Contact']['photo'], 'escape' => false)); ?>
                                <?php } else { ?>
                                    <span class="no-doc-note"><?php echo __('Not available'); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <!-- CNIC FRONT -->
                    <div class="<?php echo $mediaColClass; ?>">
                        <div class="doc-card <?php echo !empty($post['Contact']['id_front']) ? 'has-doc' : 'is-empty'; ?>">
                            <div class="doc-card-top">
                                <span class="doc-title"><i class="fa fa-id-card-o"></i> <?php echo __('CNIC Front'); ?></span>
                                <?php if(!empty($post['Contact']['id_front'])): ?>
                                    <span class="doc-state ready"><i class="fa fa-check"></i> <?php echo __('Uploaded'); ?></span>
                                <?php else: ?>
                                    <span class="doc-state empty"><?php echo __('Missing'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="doc-preview-box">
                                <?php if(!empty($post['Contact']['id_front'])){ ?>
                                    <?php echo $this->Html->image($idFrontImages['thumb'], array('alt' => __('CNIC Front'), 'class' => 'doc-img')); ?>
                                <?php } else { ?>
                                    <div class="empty-doc-placeholder">
                                        <i class="fa fa-id-card"></i>
                                        <span><?php echo __('No CNIC Front'); ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="doc-card-actions">
                                <?php if(!empty($post['Contact']['id_front'])){ ?>
                                    <?php echo $this->Html->link('<i class="fa fa-expand"></i> '.__('View Full'), $idFrontImages['original'], array('class' => 'btn-doc-action primary', 'target' => '_blank', 'escape' => false)); ?>
                                    <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download'), $idFrontImages['original'], array('class' => 'btn-doc-action secondary', 'target' => '_blank', 'download' => $post['Contact']['id_front'], 'escape' => false)); ?>
                                <?php } else { ?>
                                    <span class="no-doc-note"><?php echo __('Not available'); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <!-- CNIC BACK -->
                    <div class="<?php echo $mediaColClass; ?>">
                        <div class="doc-card <?php echo !empty($post['Contact']['id_back']) ? 'has-doc' : 'is-empty'; ?>">
                            <div class="doc-card-top">
                                <span class="doc-title"><i class="fa fa-id-card"></i> <?php echo __('CNIC Back'); ?></span>
                                <?php if(!empty($post['Contact']['id_back'])): ?>
                                    <span class="doc-state ready"><i class="fa fa-check"></i> <?php echo __('Uploaded'); ?></span>
                                <?php else: ?>
                                    <span class="doc-state empty"><?php echo __('Missing'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="doc-preview-box">
                                <?php if(!empty($post['Contact']['id_back'])){ ?>
                                    <?php echo $this->Html->image($idBackImages['thumb'], array('alt' => __('CNIC Back'), 'class' => 'doc-img')); ?>
                                <?php } else { ?>
                                    <div class="empty-doc-placeholder">
                                        <i class="fa fa-id-card"></i>
                                        <span><?php echo __('No CNIC Back'); ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="doc-card-actions">
                                <?php if(!empty($post['Contact']['id_back'])){ ?>
                                    <?php echo $this->Html->link('<i class="fa fa-expand"></i> '.__('View Full'), $idBackImages['original'], array('class' => 'btn-doc-action primary', 'target' => '_blank', 'escape' => false)); ?>
                                    <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download'), $idBackImages['original'], array('class' => 'btn-doc-action secondary', 'target' => '_blank', 'download' => $post['Contact']['id_back'], 'escape' => false)); ?>
                                <?php } else { ?>
                                    <span class="no-doc-note"><?php echo __('Not available'); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <!-- OPTIONAL GALLERY / SIDE PHOTO -->
                    <?php if($hasSidePhoto): ?>
                    <div class="<?php echo $mediaColClass; ?>">
                        <div class="doc-card has-doc">
                            <div class="doc-card-top">
                                <span class="doc-title"><i class="fa fa-picture-o"></i> <?php echo __('Gallery Photo'); ?></span>
                                <span class="doc-state ready"><i class="fa fa-check"></i> <?php echo __('Uploaded'); ?></span>
                            </div>
                            <div class="doc-preview-box">
                                <?php echo $this->Html->image($sidePhotoImages['thumb'], array('alt' => __('Gallery Photo'), 'class' => 'doc-img')); ?>
                            </div>
                            <div class="doc-card-actions">
                                <?php echo $this->Html->link('<i class="fa fa-expand"></i> '.__('View Full'), $sidePhotoImages['original'], array('class' => 'btn-doc-action primary', 'target' => '_blank', 'escape' => false)); ?>
                                <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download'), $sidePhotoImages['original'], array('class' => 'btn-doc-action secondary', 'target' => '_blank', 'download' => $post['Contact']['side_photo'], 'escape' => false)); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
