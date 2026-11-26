<?php
$client = !empty($post['MyProfile']) ? $post['MyProfile'] : array();
$clientName = !empty($client['name']) ? $client['name'] : __('Client');
$clientEmail = !empty($client['email']) ? $client['email'] : '-';
$clientPhone = !empty($client['phone']) ? $client['phone'] : (!empty($client['mobile']) ? $client['mobile'] : '-');
$clientAddress = !empty($client['address']) ? $client['address'] : '-';

$photoUrl = $this->Html->url(array('controller'=>'img'));
$clientPhoto = !empty($frontClientPhotoUrl) ? $frontClientPhotoUrl : $photoUrl.'/user-icon.png';
if(empty($frontClientPhotoUrl) && !empty($client['photo'])) {
    $clientPhotoDir = !empty($client['dir']) ? $client['dir'] : (!empty($client['dirt']) ? $client['dirt'] : 'client');
    $clientPhoto = $photoUrl.'/'.trim($clientPhotoDir).'_thumb/'.$client['photo'];
}
$lastLoginStr = !empty($client['last_login']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$timeSep.$sysSec.$dateGap.$sysMer, $client['last_login']) : __('Active Session');
?>

<style type="text/css">
.wv-profile-wrap {
  margin: 0;
  padding: 0;
}
.wv-profile-hero {
  background: linear-gradient(135deg, #07152a 0%, #102746 72%, #ff0a46 170%);
  border-radius: 16px;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.14);
  color: #ffffff;
  margin-bottom: 20px;
  overflow: hidden;
  padding: 24px;
  position: relative;
}
.wv-profile-hero:after {
  background: radial-gradient(circle at 85% 15%, rgba(124, 249, 255, 0.22), rgba(124, 249, 255, 0) 40%);
  content: "";
  inset: 0;
  position: absolute;
}
.wv-profile-hero-inner {
  align-items: center;
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: space-between;
  position: relative;
  z-index: 2;
}
.wv-profile-identity {
  align-items: center;
  display: flex;
  gap: 18px;
}
.wv-profile-avatar {
  background: #ffffff center/cover no-repeat;
  border: 3px solid rgba(124, 249, 255, 0.85);
  border-radius: 50%;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.28);
  flex: 0 0 80px;
  height: 80px;
  width: 80px;
}
.wv-profile-kicker {
  color: #7cf9ff;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: 0.8px;
  margin-bottom: 4px;
  text-transform: uppercase;
}
.wv-profile-name {
  color: #ffffff !important;
  font-size: 26px;
  font-weight: 900;
  line-height: 1.2;
  margin: 0 0 6px;
}
.wv-profile-meta-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.wv-profile-pill {
  align-items: center;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 999px;
  color: #ffffff !important;
  display: inline-flex;
  font-size: 12px;
  font-weight: 800;
  gap: 6px;
  padding: 4px 12px;
}
.wv-profile-pill.verified {
  background: rgba(16, 185, 129, 0.25);
  border-color: rgba(16, 185, 129, 0.45);
  color: #a7f3d0 !important;
}
.wv-profile-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.wv-profile-grid {
  display: grid;
  gap: 20px;
  grid-template-columns: 1fr 1fr;
}
.wv-profile-card {
  background: #ffffff;
  border: 1px solid #dfe9f5;
  border-radius: 14px;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.06);
  overflow: hidden;
}
.wv-profile-card-header {
  align-items: center;
  background: #07152a;
  border-bottom: 1px solid #102746;
  color: #ffffff;
  display: flex;
  gap: 10px;
  padding: 14px 18px;
}
.wv-profile-card-header i {
  color: #7cf9ff;
  font-size: 16px;
}
.wv-profile-card-header h3 {
  color: #ffffff !important;
  font-size: 15px;
  font-weight: 900;
  margin: 0;
}
.wv-profile-card-body {
  padding: 18px;
}
.wv-profile-field-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.wv-profile-field {
  align-items: flex-start;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  padding-bottom: 12px;
}
.wv-profile-field:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}
.wv-profile-field-label {
  align-items: center;
  color: #64748b;
  display: inline-flex;
  font-size: 12px;
  font-weight: 800;
  gap: 8px;
  text-transform: uppercase;
}
.wv-profile-field-label i {
  color: #0f4c81;
  font-size: 14px;
  width: 16px;
}
.wv-profile-field-value {
  color: #07152a;
  font-size: 13px;
  font-weight: 900;
  text-align: right;
  word-break: break-word;
}
.wv-profile-security-box {
  background: #f8fbff;
  border: 1px solid #dfe9f5;
  border-radius: 10px;
  margin-top: 14px;
  padding: 14px;
}
.wv-profile-security-title {
  align-items: center;
  color: #07152a;
  display: flex;
  font-size: 13px;
  font-weight: 900;
  gap: 8px;
  margin-bottom: 6px;
}
.wv-profile-security-title i {
  color: #10b981;
}
.wv-profile-security-desc {
  color: #475569;
  font-size: 12px;
  font-weight: 700;
  line-height: 1.45;
  margin: 0 0 12px;
}

@media (max-width: 991px) {
  .wv-profile-grid {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 767px) {
  .wv-profile-hero {
    padding: 18px 14px;
  }
  .wv-profile-hero-inner {
    align-items: flex-start;
    flex-direction: column;
  }
  .wv-profile-identity {
    align-items: center;
  }
  .wv-profile-avatar {
    flex: 0 0 62px;
    height: 62px;
    width: 62px;
  }
  .wv-profile-name {
    font-size: 20px;
  }
  .wv-profile-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    width: 100%;
  }
  .wv-profile-actions .btn {
    justify-content: center;
    width: 100%;
  }
  .wv-profile-field {
    flex-direction: column;
    gap: 4px;
  }
  .wv-profile-field-value {
    text-align: left;
  }
}
@media (max-width: 480px) {
  .wv-profile-actions {
    grid-template-columns: 1fr;
  }
}
</style>

<div class="wv-profile-wrap">
  <?php echo $this->Session->flash(); ?>

  <!-- Profile Hero Header -->
  <div class="wv-profile-hero">
    <div class="wv-profile-hero-inner">
      <div class="wv-profile-identity">
        <div class="wv-profile-avatar" style="background-image: url('<?php echo h($clientPhoto); ?>');"></div>
        <div>
          <div class="wv-profile-kicker"><?php echo __('White Villas Client Profile'); ?></div>
          <h1 class="wv-profile-name"><?php echo h($clientName); ?></h1>
          <div class="wv-profile-meta-pills">
            <span class="wv-profile-pill verified"><i class="fa fa-check-circle"></i> <?php echo __('Verified Client'); ?></span>
            <span class="wv-profile-pill"><i class="fa fa-shield"></i> <?php echo __('ID #').h($post['MyProfile']['id']); ?></span>
          </div>
        </div>
      </div>
      <div class="wv-profile-actions">
        <?php echo $this->Html->link('<i class="fa fa-key"></i> ' . __('Change Password'), array('controller' => 'Changepasswords', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-primary')); ?>
        <?php echo $this->Html->link('<i class="fa fa-handshake-o"></i> ' . __('My Deals'), array('controller' => 'MyDeals', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-info')); ?>
        <?php echo $this->Html->link('<i class="fa fa-bell"></i> ' . __('Notifications'), array('controller' => 'ClientNotifications', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-default')); ?>
      </div>
    </div>
  </div>

  <!-- Detail Cards Grid -->
  <div class="wv-profile-grid">
    <!-- Personal & Contact Information -->
    <div class="wv-profile-card">
      <div class="wv-profile-card-header">
        <i class="fa fa-user-circle"></i>
        <h3><?php echo __('Personal & Contact Details'); ?></h3>
      </div>
      <div class="wv-profile-card-body">
        <div class="wv-profile-field-list">
          <div class="wv-profile-field">
            <span class="wv-profile-field-label"><i class="fa fa-user"></i> <?php echo __('Full Name'); ?></span>
            <span class="wv-profile-field-value"><?php echo h($clientName); ?></span>
          </div>
          <div class="wv-profile-field">
            <span class="wv-profile-field-label"><i class="fa fa-phone"></i> <?php echo __('Contact Number'); ?></span>
            <span class="wv-profile-field-value"><?php echo h($clientPhone); ?></span>
          </div>
          <div class="wv-profile-field">
            <span class="wv-profile-field-label"><i class="fa fa-envelope"></i> <?php echo __('Email Address'); ?></span>
            <span class="wv-profile-field-value"><?php echo h($clientEmail); ?></span>
          </div>
          <div class="wv-profile-field">
            <span class="wv-profile-field-label"><i class="fa fa-map-marker"></i> <?php echo __('Postal Address'); ?></span>
            <span class="wv-profile-field-value"><?php echo h($clientAddress); ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Account & Security Information -->
    <div class="wv-profile-card">
      <div class="wv-profile-card-header">
        <i class="fa fa-lock"></i>
        <h3><?php echo __('Account & Security Center'); ?></h3>
      </div>
      <div class="wv-profile-card-body">
        <div class="wv-profile-field-list">
          <div class="wv-profile-field">
            <span class="wv-profile-field-label"><i class="fa fa-shield"></i> <?php echo __('Account Status'); ?></span>
            <span class="wv-profile-field-value"><span class="badge" style="background:#047857; color:#ffffff; font-weight:900; padding:4px 8px;"><?php echo __('Active & Verified'); ?></span></span>
          </div>
          <div class="wv-profile-field">
            <span class="wv-profile-field-label"><i class="fa fa-history"></i> <?php echo __('Last Login Activity'); ?></span>
            <span class="wv-profile-field-value"><?php echo h($lastLoginStr); ?></span>
          </div>
        </div>

        <div class="wv-profile-security-box">
          <div class="wv-profile-security-title">
            <i class="fa fa-check-circle"></i>
            <span><?php echo __('Password Security Recommendation'); ?></span>
          </div>
          <p class="wv-profile-security-desc">
            <?php echo __('To safeguard your investments, property records, and financial statements, we recommend updating your password every 90 days and using a strong password.'); ?>
          </p>
          <?php echo $this->Html->link('<i class="fa fa-key"></i> ' . __('Update Password Now'), array('controller' => 'Changepasswords', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-primary btn-sm')); ?>
        </div>
      </div>
    </div>
  </div>
</div>
