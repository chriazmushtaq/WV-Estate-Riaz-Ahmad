<?php
$clientLoginLogo = strlen(trim((string)$frontLogo)) > 0 ? $frontLogo : 'logo-website.fw.png';
$clientLoginTitle = !empty($siteOrganization) ? $siteOrganization : $siteName;
$clientContactClean = preg_replace('/[^0-9\+]/', '', (string)$contact);
?>
<style type="text/css">
/* ==========================================================================
   White Villas - Professional Client Portal Login (Enterprise Modern UX)
   ========================================================================== */
.wv-client-login-page {
    background: linear-gradient(135deg, #f0f4f9 0%, #e8eef5 100%);
    min-height: calc(100vh - 150px);
    padding: 36px 0 50px;
    font-family: inherit;
}
.wv-client-login-page * {
    box-sizing: border-box;
}
.wv-client-login-shell {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(390px, 450px);
    max-width: 1080px;
    margin: 0 auto;
    padding: 0 16px;
    box-shadow: 0 25px 55px -12px rgba(15, 23, 42, 0.16), 0 4px 14px rgba(15, 23, 42, 0.06);
    border-radius: 20px;
    background: #ffffff;
    overflow: hidden;
    border: 1px solid rgba(226, 232, 240, 0.85);
}

/* Left Brand Panel (Desktop) */
.wv-client-login-brand {
    background: linear-gradient(165deg, #0a192f 0%, #0f2c4d 50%, #07192f 100%);
    color: #ffffff;
    padding: 44px 40px;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.wv-client-login-brand:before {
    content: "";
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(rgba(255, 255, 255, 0.07) 1px, transparent 1px),
        radial-gradient(rgba(14, 165, 233, 0.15) 1.5px, transparent 1.5px);
    background-size: 24px 24px, 48px 48px;
    background-position: 0 0, 24px 24px;
    opacity: 0.7;
    pointer-events: none;
}
.wv-client-login-brand:after {
    content: "";
    position: absolute;
    bottom: -130px;
    right: -110px;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(14, 165, 233, 0.28) 0%, rgba(15, 76, 129, 0) 70%);
    pointer-events: none;
}
.wv-brand-content-top {
    position: relative;
    z-index: 2;
}
.wv-client-login-logo {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 34px;
}
.wv-client-login-logo img {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    max-height: 58px;
    max-width: 140px;
    object-fit: contain;
    padding: 8px 12px;
}
.wv-brand-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(14, 165, 233, 0.16);
    border: 1px solid rgba(14, 165, 233, 0.35);
    color: #38bdf8;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 20px;
    margin-bottom: 6px;
}
.wv-client-login-logo strong {
    color: #ffffff;
    display: block;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.25;
}
.wv-client-login-logo span {
    color: #94a3b8;
    display: block;
    font-size: 12px;
    font-weight: 600;
    margin-top: 2px;
}
.wv-client-login-brand h1 {
    color: #ffffff;
    font-size: 32px;
    font-weight: 900;
    letter-spacing: -0.5px;
    line-height: 1.18;
    margin: 0 0 14px;
}
.wv-client-login-brand p.wv-brand-lead {
    color: #cbd5e1;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.65;
    margin: 0 0 32px;
    max-width: 470px;
}

/* Feature points */
.wv-client-login-points {
    display: grid;
    gap: 16px;
    position: relative;
    z-index: 2;
    margin-bottom: 30px;
}
.wv-client-login-point {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    color: #e2e8f0;
    font-size: 13.5px;
    font-weight: 600;
}
.wv-point-icon {
    flex: 0 0 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.14);
    color: #38bdf8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    margin-top: 2px;
}
.wv-point-text strong {
    display: block;
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 700;
    line-height: 1.3;
}
.wv-point-text span {
    display: block;
    color: #94a3b8;
    font-size: 12px;
    font-weight: 500;
    margin-top: 2px;
}

/* Brand Footer */
.wv-brand-footer {
    position: relative;
    z-index: 2;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    padding-top: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.wv-brand-ssl-note {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #10b981;
    font-size: 12px;
    font-weight: 700;
}
.wv-brand-helpline {
    color: #e2e8f0;
    font-size: 12px;
    font-weight: 600;
}
.wv-brand-helpline a {
    color: #38bdf8 !important;
    text-decoration: none !important;
    font-weight: 800;
}

/* Right Card (Login Form) */
.wv-client-login-card {
    background: #ffffff;
    padding: 44px 38px 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.wv-card-header {
    margin-bottom: 24px;
}
.wv-portal-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #0f4c81;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 16px;
    margin-bottom: 10px;
}
.wv-client-login-card h2 {
    color: #0f172a;
    font-size: 26px;
    font-weight: 900;
    line-height: 1.2;
    margin: 0 0 6px;
    letter-spacing: -0.4px;
}
.wv-client-login-subtitle {
    color: #64748b;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.55;
    margin: 0;
}

/* Identifier Chips / Hint Bar */
.wv-login-chips-bar {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 12px;
    flex-wrap: wrap;
}
.wv-login-chips-label {
    font-size: 11.5px;
    font-weight: 700;
    color: #475569;
    margin-right: 2px;
}
.wv-chip-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    transition: all 0.2s ease;
}
.wv-chip-pill.active {
    background: #0f4c81;
    border-color: #0f4c81;
    color: #ffffff;
}
.wv-chip-pill i {
    font-size: 11px;
}

/* Flash alerts */
.wv-client-login-card .alert {
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 700;
    margin-bottom: 20px;
    border: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* Form Fields */
.wv-client-login-field {
    margin-bottom: 20px;
}
.wv-field-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 7px;
}
.wv-field-header label {
    color: #1e293b;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    margin: 0;
}
.wv-field-detect-badge {
    font-size: 11px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 4px;
    display: none;
    align-items: center;
    gap: 4px;
}
.wv-field-detect-badge.badge-email {
    display: inline-flex;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
.wv-field-detect-badge.badge-cnic {
    display: inline-flex;
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}
.wv-field-detect-badge.badge-mobile {
    display: inline-flex;
    background: #faf5ff;
    color: #7e22ce;
    border: 1px solid #e9d5ff;
}
.wv-forgot-link {
    color: #0f4c81 !important;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none !important;
    transition: color 0.15s ease;
}
.wv-forgot-link:hover {
    color: #0284c7 !important;
    text-decoration: underline !important;
}

/* Input Containers */
.wv-client-login-input {
    display: flex;
    align-items: center;
    background: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    min-height: 52px;
    overflow: hidden;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.wv-client-login-input:hover {
    border-color: #94a3b8;
    background: #ffffff;
}
.wv-client-login-input:focus-within {
    border-color: #0f4c81;
    background: #ffffff;
    box-shadow: 0 0 0 3.5px rgba(15, 76, 129, 0.14);
}
.wv-input-lead-icon {
    flex: 0 0 46px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 15px;
    transition: color 0.2s ease;
}
.wv-client-login-input:focus-within .wv-input-lead-icon {
    color: #0f4c81;
}
.wv-client-login-input .form-control {
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    color: #0f172a;
    font-size: 14.5px;
    font-weight: 600;
    height: 52px;
    padding: 0 14px 0 0;
    flex: 1;
}
.wv-client-login-input .form-control::placeholder {
    color: #94a3b8;
    font-weight: 500;
    font-size: 13.5px;
}
.wv-client-login-password-toggle {
    background: transparent;
    border: 0;
    color: #64748b;
    display: flex;
    flex: 0 0 48px;
    height: 52px;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    cursor: pointer;
    transition: color 0.15s ease;
    padding: 0;
}
.wv-client-login-password-toggle:hover,
.wv-client-login-password-toggle:focus {
    color: #0f4c81;
    outline: 0;
}

/* Submit Button */
.wv-client-login-submit {
    background: linear-gradient(135deg, #0f4c81 0%, #0369a1 100%) !important;
    border: 0 !important;
    border-radius: 10px !important;
    box-shadow: 0 10px 22px rgba(15, 76, 129, 0.24);
    color: #ffffff !important;
    font-size: 14.5px;
    font-weight: 800 !important;
    height: 52px;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    width: 100%;
    margin-top: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.wv-client-login-submit:hover {
    background: linear-gradient(135deg, #0a3357 0%, #024b75 100%) !important;
    box-shadow: 0 14px 28px rgba(15, 76, 129, 0.32);
    transform: translateY(-1px);
    color: #ffffff !important;
}
.wv-client-login-submit:active {
    transform: translateY(1px);
    box-shadow: 0 6px 14px rgba(15, 76, 129, 0.2);
}

/* Secondary Actions / Registration */
.wv-secondary-actions {
    margin-top: 20px;
    display: grid;
    gap: 10px;
}
.wv-register-banner {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: border-color 0.2s ease;
}
.wv-register-banner:hover {
    border-color: #cbd5e1;
}
.wv-register-banner a {
    color: #0f4c81 !important;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}
.wv-register-banner a:hover {
    color: #0284c7 !important;
}
.wv-support-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 2px;
    font-size: 12px;
    color: #64748b;
}
.wv-support-bar a {
    color: #0f4c81 !important;
    font-weight: 700;
    text-decoration: none !important;
}
.wv-support-bar a:hover {
    text-decoration: underline !important;
}

/* Security Trust Badge */
.wv-client-login-security {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #ecfdf5;
    border: 1px solid #d1fae5;
    border-radius: 10px;
    color: #065f46;
    font-size: 12px;
    font-weight: 700;
    padding: 10px 14px;
    margin-top: 20px;
}
.wv-client-login-security i {
    color: #10b981;
    font-size: 14px;
}

/* App Download Banner (Desktop & Mobile) */
.wv-app-callout-card {
    background: linear-gradient(135deg, #07152a 0%, #0d284a 100%);
    border: 1px solid #1e3a5f;
    border-radius: 12px;
    padding: 16px;
    margin-top: 20px;
    color: #ffffff;
    box-shadow: 0 8px 20px rgba(7, 21, 42, 0.16);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}
.wv-app-callout-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.wv-app-callout-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
}
.wv-app-callout-info strong {
    display: block;
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 800;
    line-height: 1.25;
}
.wv-app-callout-info span {
    display: block;
    color: #94a3b8;
    font-size: 11.5px;
    font-weight: 500;
    margin-top: 2px;
}
.wv-btn-app-dl {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff !important;
    font-size: 11.5px;
    font-weight: 800;
    padding: 9px 14px;
    border-radius: 8px;
    text-decoration: none !important;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
    transition: all 0.2s ease;
    flex-shrink: 0;
}
.wv-btn-app-dl:hover {
    background: #047857;
    color: #ffffff !important;
    transform: translateY(-1px);
}

/* Mobile Top Pill Announcement */
.wv-mobile-app-strip {
    display: none;
    background: linear-gradient(135deg, #07152a 0%, #0f2c4d 100%);
    border: 1px solid #1e3a5f;
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 20px;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    color: #ffffff;
}
.wv-mobile-app-strip .wv-strip-text {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 700;
}
.wv-mobile-app-strip .wv-strip-text i {
    color: #10b981;
    font-size: 16px;
}
.wv-mobile-app-strip a {
    background: #10b981;
    color: #ffffff !important;
    font-size: 11px;
    font-weight: 800;
    padding: 5px 10px;
    border-radius: 6px;
    text-decoration: none !important;
    text-transform: uppercase;
}

/* Responsive Styles */
@media (max-width: 860px) {
    .wv-client-login-page {
        padding: 20px 0 32px;
    }
    .wv-client-login-shell {
        grid-template-columns: 1fr;
        max-width: 480px;
        border-radius: 16px;
    }
    .wv-client-login-brand {
        display: none !important;
    }
    .wv-client-login-card {
        padding: 30px 24px;
    }
    .wv-mobile-app-strip {
        display: flex;
    }
    .wv-app-callout-card {
        flex-direction: column;
        align-items: flex-start;
    }
    .wv-btn-app-dl {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="main-wrapper">
    <div class="main">
        <div class="main-inner">
            <div class="content wv-client-login-page">
                <div class="wv-client-login-shell">
                    
                    <!-- Left Brand Panel (Desktop) -->
                    <section class="wv-client-login-brand">
                        <div class="wv-brand-content-top">
                            <div class="wv-client-login-logo">
                                <?php echo $this->Html->image($clientLoginLogo, array('alt' => $siteName)); ?>
                                <div>
                                    <span class="wv-brand-badge"><i class="fa fa-shield"></i> Official Portal</span>
                                    <strong><?php echo h($clientLoginTitle); ?></strong>
                                    <span>Client Management Portal</span>
                                </div>
                            </div>
                            
                            <h1><?php echo __('Client Portal Sign In'); ?></h1>
                            <p class="wv-brand-lead">
                                <?php echo __('Securely access your verified property deals, payment ledger, official allotment letters, and installment schedules from your unified %s client account.', h($clientLoginTitle)); ?>
                            </p>

                            <div class="wv-client-login-points">
                                <div class="wv-client-login-point">
                                    <div class="wv-point-icon"><i class="fa fa-file-text-o"></i></div>
                                    <div class="wv-point-text">
                                        <strong><?php echo __('Official Allotment & Deal Letters'); ?></strong>
                                        <span><?php echo __('Instant verified access to deal documents & agreements'); ?></span>
                                    </div>
                                </div>
                                <div class="wv-client-login-point">
                                    <div class="wv-point-icon"><i class="fa fa-list-alt"></i></div>
                                    <div class="wv-point-text">
                                        <strong><?php echo __('Real-Time Payment Ledger & Receipts'); ?></strong>
                                        <span><?php echo __('Track credit transactions, verified receipts & balances'); ?></span>
                                    </div>
                                </div>
                                <div class="wv-client-login-point">
                                    <div class="wv-point-icon"><i class="fa fa-calendar-check-o"></i></div>
                                    <div class="wv-point-text">
                                        <strong><?php echo __('Installment Overdues & Schedules'); ?></strong>
                                        <span><?php echo __('Keep track of upcoming dues and settlement history'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="wv-brand-footer">
                            <div class="wv-brand-ssl-note">
                                <i class="fa fa-lock"></i>
                                <span><?php echo __('256-Bit SSL Encrypted & Confidential'); ?></span>
                            </div>
                            <?php if(!empty($clientContactClean)): ?>
                            <div class="wv-brand-helpline">
                                <span><?php echo __('Helpline:'); ?></span>
                                <a href="tel:<?php echo h($clientContactClean); ?>"><?php echo h($contact); ?></a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Right Card: Streamlined, Ultra-Clean Login Form -->
                    <section class="wv-client-login-card">
                        <div>
                            <!-- Mobile-Only App Banner -->
                            <div class="wv-mobile-app-strip">
                                <div class="wv-strip-text">
                                    <i class="fa fa-android"></i>
                                    <span><?php echo __('Android App Available'); ?></span>
                                </div>
                                <a href="<?php echo $this->Html->url('/files/whitevillas.apk'); ?>" download="WhiteVillas.apk">
                                    <i class="fa fa-download"></i> <?php echo __('Get App (.APK)'); ?>
                                </a>
                            </div>

                            <div class="wv-card-header">
                                <span class="wv-portal-pill"><i class="fa fa-user-circle"></i> <?php echo __('Client Account'); ?></span>
                                <h2><?php echo __('Sign In to Portal'); ?></h2>
                                <p class="wv-client-login-subtitle"><?php echo __('Use your registered Mobile Number, CNIC, or Email Address.'); ?></p>
                                
                                <div class="wv-login-chips-bar">
                                    <span class="wv-login-chips-label"><?php echo __('Sign in with:'); ?></span>
                                    <span class="wv-chip-pill" id="chipMobile"><i class="fa fa-phone"></i> <?php echo __('Mobile'); ?></span>
                                    <span class="wv-chip-pill" id="chipCnic"><i class="fa fa-id-card-o"></i> <?php echo __('CNIC'); ?></span>
                                    <span class="wv-chip-pill" id="chipEmail"><i class="fa fa-envelope-o"></i> <?php echo __('Email'); ?></span>
                                </div>
                            </div>

                            <?php echo $this->Session->flash(); ?>

                            <?php echo $this->Form->create('Client', array(
                                'url' => array('controller' => 'Clients', 'action' => 'login'),
                                'name' => 'post_req',
                                'id' => 'clientLoginForm',
                                'autocomplete' => 'on'
                            )); ?>

                            <!-- Multi-Identifier Input (Mobile / CNIC / Email) -->
                            <div class="wv-client-login-field">
                                <div class="wv-field-header">
                                    <label for="ClientLoginId"><?php echo __('User ID / Identifier'); ?></label>
                                    <span class="wv-field-detect-badge" id="detectedTypeBadge"></span>
                                </div>
                                <div class="wv-client-login-input">
                                    <span class="wv-input-lead-icon" id="loginIdIcon"><i class="fa fa-user"></i></span>
                                    <?php echo $this->Form->input('email', array(
                                        'id' => 'ClientLoginId',
                                        'type' => 'text',
                                        'required' => true,
                                        'label' => false,
                                        'class' => 'form-control',
                                        'placeholder' => __('Mobile (0300...), CNIC, or Email'),
                                        'div' => false,
                                        'autocomplete' => 'username',
                                        'autocapitalize' => 'none',
                                        'autocorrect' => 'off',
                                        'spellcheck' => 'false'
                                    )); ?>
                                </div>
                            </div>

                            <!-- Password Input with Toggle & Forgot Password -->
                            <div class="wv-client-login-field">
                                <div class="wv-field-header">
                                    <label for="ClientPassword"><?php echo __('Password'); ?></label>
                                    <?php echo $this->Html->link(__('Forgot Password?'), array('controller' => 'Forgots', 'action' => 'password'), array('class' => 'wv-forgot-link')); ?>
                                </div>
                                <div class="wv-client-login-input">
                                    <span class="wv-input-lead-icon"><i class="fa fa-lock"></i></span>
                                    <?php echo $this->Form->input('password', array(
                                        'id' => 'ClientPassword',
                                        'type' => 'password',
                                        'required' => true,
                                        'label' => false,
                                        'class' => 'form-control',
                                        'placeholder' => __('Enter your account password'),
                                        'div' => false,
                                        'autocomplete' => 'current-password'
                                    )); ?>
                                    <button type="button" class="wv-client-login-password-toggle" id="ClientPasswordToggle" aria-label="<?php echo h(__('Show password')); ?>" title="<?php echo h(__('Toggle password visibility')); ?>">
                                        <span class="fa fa-eye"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn wv-client-login-submit" id="btnSubmitLogin">
                                <span class="fa fa-sign-in"></span> 
                                <span id="btnSubmitText"><?php echo __('Sign In to Portal'); ?></span>
                            </button>
                            <?php echo $this->Form->end(null); ?>

                            <!-- Account Registration & Verification Links -->
                            <div class="wv-secondary-actions">
                                <?php if($frontRegistration == 1): ?>
                                    <div class="wv-register-banner">
                                        <?php echo $this->Html->link('<span><i class="glyphicon glyphicon-user" style="margin-right:6px;"></i> '.__('New Client? Register Account').'</span><i class="fa fa-arrow-right"></i>', array('controller' => 'Registers', 'action' => 'index'), array('escape' => false)); ?>
                                    </div>
                                    <div class="wv-register-banner">
                                        <?php echo $this->Html->link('<span><i class="glyphicon glyphicon-share-alt" style="margin-right:6px;"></i> '.__('Re-Send Email Verification Code').'</span><i class="fa fa-arrow-right"></i>', array('controller' => 'Emailverifications', 'action' => 'resend'), array('escape' => false)); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if(!empty($clientContactClean)): ?>
                                    <div class="wv-support-bar">
                                        <span><?php echo __('Need portal login assistance?'); ?></span>
                                        <a href="tel:<?php echo h($clientContactClean); ?>"><i class="fa fa-phone"></i> <?php echo __('Call Support: '); ?><?php echo h($contact); ?></a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="wv-client-login-security">
                                <i class="fa fa-shield"></i>
                                <span><?php echo __('Protected by 256-bit encryption. Authorized client portal for %s.', h($clientLoginTitle)); ?></span>
                            </div>
                        </div>

                        <!-- Desktop App Download Box -->
                        <div class="wv-app-callout-card">
                            <div class="wv-app-callout-left">
                                <div class="wv-app-callout-icon"><i class="fa fa-android"></i></div>
                                <div class="wv-app-callout-info">
                                    <strong><?php echo __('Official Android App'); ?></strong>
                                    <span><?php echo __('Install for instant payment receipts & push alerts'); ?></span>
                                </div>
                            </div>
                            <a href="<?php echo $this->Html->url('/files/whitevillas.apk'); ?>" class="wv-btn-app-dl" download="WhiteVillas.apk">
                                <i class="fa fa-download"></i>
                                <span><?php echo __('Download .APK'); ?></span>
                            </a>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function($){
    $(function(){
        var $loginInput = $('#ClientLoginId');
        var $leadIcon = $('#loginIdIcon').find('.fa');
        var $badge = $('#detectedTypeBadge');
        var $chipMobile = $('#chipMobile');
        var $chipCnic = $('#chipCnic');
        var $chipEmail = $('#chipEmail');

        function detectIdentifierType(val) {
            val = $.trim(val);
            if (!val) {
                return 'empty';
            }

            // Email detection: contains @
            if (val.indexOf('@') !== -1) {
                return 'email';
            }

            var cleanDigits = val.replace(/\D/g, '');

            // CNIC detection: 13 digits, or pattern matching 5-7-1 e.g. 35201-1234567-1
            if (val.indexOf('-') !== -1 && /^\d{5}-\d{7}-\d{1}$/.test(val)) {
                return 'cnic';
            }
            if (cleanDigits.length === 13 && (val.charAt(0) >= '1' && val.charAt(0) <= '7')) {
                return 'cnic';
            }

            // Mobile detection: starts with 03, +92, 92, or digits 10-12
            if (/^(\+?92|0)?3\d{2}/.test(val) || (cleanDigits.length >= 10 && cleanDigits.length <= 12)) {
                return 'mobile';
            }

            // Fallback if long digits
            if (cleanDigits.length >= 13) {
                return 'cnic';
            }
            if (cleanDigits.length >= 9) {
                return 'mobile';
            }

            return 'unknown';
        }

        function updateIdentifierUI() {
            var val = $loginInput.val();
            var type = detectIdentifierType(val);

            // Reset chips
            $chipMobile.removeClass('active');
            $chipCnic.removeClass('active');
            $chipEmail.removeClass('active');

            // Reset badge classes
            $badge.removeClass('badge-email badge-cnic badge-mobile').hide();

            if (type === 'email') {
                $leadIcon.attr('class', 'fa fa-envelope text-primary');
                $chipEmail.addClass('active');
                $badge.addClass('badge-email').html('<i class="fa fa-check"></i> <?php echo h(__('Email')); ?>').show();
            } else if (type === 'cnic') {
                $leadIcon.attr('class', 'fa fa-id-card text-success');
                $chipCnic.addClass('active');
                $badge.addClass('badge-cnic').html('<i class="fa fa-check"></i> <?php echo h(__('CNIC')); ?>').show();
            } else if (type === 'mobile') {
                $leadIcon.attr('class', 'fa fa-phone text-info');
                $chipMobile.addClass('active');
                $badge.addClass('badge-mobile').html('<i class="fa fa-check"></i> <?php echo h(__('Mobile')); ?>').show();
            } else {
                $leadIcon.attr('class', 'fa fa-user');
            }
        }

        $loginInput.on('input keyup blur change', updateIdentifierUI);
        updateIdentifierUI();

        // Password visibility toggle
        $(document).on('click', '#ClientPasswordToggle', function(){
            var $password = $('#ClientPassword');
            var isShowing = $password.attr('type') === 'text';
            $password.attr('type', isShowing ? 'password' : 'text');
            $(this).attr('aria-label', isShowing ? '<?php echo h(__('Show password')); ?>' : '<?php echo h(__('Hide password')); ?>');
            $(this).find('.fa').toggleClass('fa-eye', isShowing).toggleClass('fa-eye-slash', !isShowing);
        });

        // Form submit spinner
        $('#clientLoginForm').on('submit', function(){
            var $btn = $('#btnSubmitLogin');
            var $txt = $('#btnSubmitText');
            $btn.prop('disabled', true).css('opacity', '0.85');
            $txt.text('<?php echo h(__('Authenticating...')); ?>');
            $btn.find('.fa').attr('class', 'fa fa-circle-o-notch fa-spin');
            return true;
        });
    });
})(jQuery);
</script>
