<style type="text/css">
.wv-chpass-wrap {
  margin: 0;
  padding: 0;
}
.wv-chpass-hero {
  background: linear-gradient(135deg, #07152a 0%, #102746 72%, #ff0a46 170%);
  border-radius: 16px;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.14);
  color: #ffffff;
  margin-bottom: 20px;
  overflow: hidden;
  padding: 24px;
  position: relative;
}
.wv-chpass-hero:after {
  background: radial-gradient(circle at 85% 15%, rgba(124, 249, 255, 0.22), rgba(124, 249, 255, 0) 40%);
  content: "";
  inset: 0;
  position: absolute;
}
.wv-chpass-hero-inner {
  align-items: center;
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  justify-content: space-between;
  position: relative;
  z-index: 2;
}
.wv-chpass-kicker {
  color: #7cf9ff;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: 0.8px;
  margin-bottom: 4px;
  text-transform: uppercase;
}
.wv-chpass-title {
  color: #ffffff !important;
  font-size: 26px;
  font-weight: 900;
  line-height: 1.2;
  margin: 0 0 6px;
}
.wv-chpass-desc {
  color: #e2e8f0;
  font-size: 13px;
  font-weight: 700;
  margin: 0;
}
.wv-chpass-grid {
  display: grid;
  gap: 20px;
  grid-template-columns: 1fr 340px;
}
.wv-chpass-card {
  background: #ffffff;
  border: 1px solid #dfe9f5;
  border-radius: 14px;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.06);
  overflow: hidden;
}
.wv-chpass-card-head {
  align-items: center;
  background: #07152a;
  border-bottom: 1px solid #102746;
  color: #ffffff;
  display: flex;
  gap: 10px;
  padding: 14px 18px;
}
.wv-chpass-card-head i {
  color: #7cf9ff;
  font-size: 16px;
}
.wv-chpass-card-head h3 {
  color: #ffffff !important;
  font-size: 15px;
  font-weight: 900;
  margin: 0;
}
.wv-chpass-card-body {
  padding: 22px;
}
.wv-chpass-form .form-group {
  margin-bottom: 18px;
}
.wv-chpass-form label {
  color: #07152a;
  display: block;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.3px;
  margin-bottom: 6px;
  text-transform: uppercase;
}
.wv-chpass-input-icon-wrap {
  position: relative;
}
.wv-chpass-input-icon-wrap i {
  color: #94a3b8;
  font-size: 15px;
  left: 14px;
  pointer-events: none;
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
}
.wv-chpass-input-icon-wrap .form-control {
  background: #f8fbff;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  box-shadow: none;
  color: #07152a;
  font-size: 14px;
  font-weight: 700;
  height: 44px;
  padding-left: 40px;
  transition: all 0.2s ease;
}
.wv-chpass-input-icon-wrap .form-control:focus {
  background: #ffffff;
  border-color: #ff0a46;
  box-shadow: 0 0 0 3px rgba(255, 10, 70, 0.12);
  outline: none;
}
.wv-chpass-tips-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.wv-chpass-tip-item {
  align-items: flex-start;
  display: flex;
  gap: 10px;
}
.wv-chpass-tip-item i {
  align-items: center;
  background: #ecfdf5;
  border-radius: 50%;
  color: #047857;
  display: inline-flex;
  flex: 0 0 24px;
  font-size: 11px;
  height: 24px;
  justify-content: center;
  margin-top: 2px;
}
.wv-chpass-tip-item span {
  color: #475569;
  font-size: 12px;
  font-weight: 700;
  line-height: 1.45;
}

@media (max-width: 991px) {
  .wv-chpass-grid {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 767px) {
  .wv-chpass-hero {
    padding: 18px 14px;
  }
  .wv-chpass-hero-inner {
    align-items: flex-start;
    flex-direction: column;
  }
  .wv-chpass-title {
    font-size: 20px;
  }
  .wv-chpass-card-body {
    padding: 16px;
  }
}
</style>

<div class="wv-chpass-wrap">
  <?php echo $this->Session->flash(); ?>

  <!-- Header Banner -->
  <div class="wv-chpass-hero">
    <div class="wv-chpass-hero-inner">
      <div>
        <div class="wv-chpass-kicker"><?php echo __('Security Settings'); ?></div>
        <h1 class="wv-chpass-title"><?php echo __('Change Password'); ?></h1>
        <p class="wv-chpass-desc"><?php echo __('Update your client portal access password to ensure your account security.'); ?></p>
      </div>
      <div>
        <?php echo $this->Html->link('<i class="fa fa-user"></i> ' . __('Back to My Profile'), array('controller' => 'MyProfiles', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-default')); ?>
      </div>
    </div>
  </div>

  <!-- Content Grid -->
  <div class="wv-chpass-grid">
    <!-- Form Card -->
    <div class="wv-chpass-card">
      <div class="wv-chpass-card-head">
        <i class="fa fa-key"></i>
        <h3><?php echo __('Update Credentials'); ?></h3>
      </div>
      <div class="wv-chpass-card-body">
        <?php echo $this->Form->create('Changepassword', array('controller' => 'Changepasswords', 'action' => 'index', 'name' => 'post_req', 'id' => 'post_req', 'class' => 'wv-chpass-form')); ?>
        
        <div class="form-group">
          <label for="oldPassword"><?php echo __('Current / Old Password'); ?></label>
          <div class="wv-chpass-input-icon-wrap">
            <i class="fa fa-lock"></i>
            <?php echo $this->Form->input('oldPassword', array('type' => 'password', 'id' => 'oldPassword', 'value' => '', 'label' => false, 'class' => 'form-control validate[required,minSize[4],maxSize[15]]', 'placeholder' => __('Enter current password'), 'div' => false)); ?>
          </div>
        </div>

        <div class="form-group">
          <label for="password"><?php echo __('New Password'); ?></label>
          <div class="wv-chpass-input-icon-wrap">
            <i class="fa fa-key"></i>
            <?php echo $this->Form->input('password', array('type' => 'password', 'id' => 'password', 'value' => '', 'label' => false, 'class' => 'form-control validate[required,minSize[4],maxSize[15]]', 'placeholder' => __('Enter new secure password'), 'div' => false)); ?>
          </div>
        </div>

        <div class="form-group">
          <label for="con_password"><?php echo __('Confirm New Password'); ?></label>
          <div class="wv-chpass-input-icon-wrap">
            <i class="fa fa-check-circle"></i>
            <?php echo $this->Form->input('con_password', array('type' => 'password', 'id' => 'con_password', 'value' => '', 'label' => false, 'class' => 'form-control validate[equals[password]]', 'placeholder' => __('Re-enter new password'), 'div' => false)); ?>
          </div>
        </div>

        <div style="margin-top: 24px;">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="fa fa-refresh"></i> <?php echo __('Update Password'); ?>
          </button>
        </div>

        <?php echo $this->Form->end(); ?>
      </div>
    </div>

    <!-- Security Tips Sidebar -->
    <div class="wv-chpass-card">
      <div class="wv-chpass-card-head">
        <i class="fa fa-shield"></i>
        <h3><?php echo __('Password Guidelines'); ?></h3>
      </div>
      <div class="wv-chpass-card-body">
        <div class="wv-chpass-tips-list">
          <div class="wv-chpass-tip-item">
            <i class="fa fa-check"></i>
            <span><?php echo __('Use at least 6 to 15 characters for strong protection.'); ?></span>
          </div>
          <div class="wv-chpass-tip-item">
            <i class="fa fa-check"></i>
            <span><?php echo __('Combine letters, numbers, and special symbols.'); ?></span>
          </div>
          <div class="wv-chpass-tip-item">
            <i class="fa fa-check"></i>
            <span><?php echo __('Never share your client portal login with unauthorized third parties.'); ?></span>
          </div>
          <div class="wv-chpass-tip-item">
            <i class="fa fa-check"></i>
            <span><?php echo __('If you suspect unauthorized activity, change your password immediately.'); ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
