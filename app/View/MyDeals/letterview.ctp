<style type="text/css">
.wv-letterview-wrap {
  margin: 0;
  padding: 0;
}
.wv-letterview-hero {
  background: linear-gradient(135deg, #07152a 0%, #102746 72%, #ff0a46 170%);
  border-radius: 16px;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.14);
  color: #ffffff;
  margin-bottom: 20px;
  overflow: hidden;
  padding: 24px;
  position: relative;
}
.wv-letterview-hero:after {
  background: radial-gradient(circle at 85% 15%, rgba(124, 249, 255, 0.22), rgba(124, 249, 255, 0) 40%);
  content: "";
  inset: 0;
  position: absolute;
}
.wv-letterview-hero-inner {
  align-items: center;
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  justify-content: space-between;
  position: relative;
  z-index: 2;
}
.wv-letterview-kicker {
  color: #7cf9ff;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: 0.8px;
  margin-bottom: 4px;
  text-transform: uppercase;
}
.wv-letterview-title {
  color: #ffffff !important;
  font-size: 26px;
  font-weight: 900;
  line-height: 1.2;
  margin: 0 0 6px;
}
.wv-letterview-meta {
  color: #cbd5e1;
  display: flex;
  flex-wrap: wrap;
  font-size: 13px;
  font-weight: 800;
  gap: 12px;
}
.wv-letterview-meta span {
  align-items: center;
  background: rgba(255, 255, 255, 0.10);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 999px;
  display: inline-flex;
  gap: 6px;
  padding: 4px 10px;
}
.wv-letterview-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.wv-letterview-card {
  background: #ffffff;
  border: 1px solid #dfe9f5;
  border-radius: 14px;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.06);
  margin-bottom: 20px;
  overflow: hidden;
  padding: 20px;
}
.wv-letterview-specs-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  margin-bottom: 20px;
}
.wv-letterview-spec {
  background: #f8fbff;
  border: 1px solid #dfe9f5;
  border-radius: 10px;
  padding: 12px 14px;
}
.wv-letterview-spec small {
  color: #64748b;
  display: block;
  font-size: 10px;
  font-weight: 900;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}
.wv-letterview-spec strong {
  color: #07152a;
  display: block;
  font-size: 14px;
  font-weight: 900;
  margin-top: 4px;
  word-break: break-word;
}
.wv-letterview-preview-wrap {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  overflow: hidden;
  padding: 12px;
}
.wv-letterview-iframe {
  background: #ffffff;
  border: 1px solid #dfe9f5;
  border-radius: 8px;
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
  display: block;
  min-height: 850px;
  width: 100%;
}
.wv-letterview-body-box {
  background: #ffffff;
  border: 1px solid #dfe9f5;
  border-radius: 8px;
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
  font-size: 14px;
  line-height: 1.6;
  min-height: 400px;
  padding: 28px;
}

@media(max-width:991px){
  .wv-letterview-specs-grid{grid-template-columns:repeat(2, 1fr);}
}
@media(max-width:767px){
  .wv-letterview-hero{padding:18px 14px;}
  .wv-letterview-hero-inner{align-items:flex-start;flex-direction:column;}
  .wv-letterview-title{font-size:22px;}
  .wv-letterview-actions{width:100%;}
  .wv-letterview-actions .btn{flex:1 1 auto;justify-content:center;}
  .wv-letterview-specs-grid{grid-template-columns:1fr;}
  .wv-letterview-iframe{min-height:600px;}
}
</style>

<div class="wv-letterview-wrap">
  <?php echo $this->Session->flash(); ?>

  <!-- Document Header Hero -->
  <div class="wv-letterview-hero">
    <div class="wv-letterview-hero-inner">
      <div>
        <div class="wv-letterview-kicker"><?php echo __('Verified Official Document'); ?></div>
        <h1 class="wv-letterview-title"><?php echo h(!empty($post['Realestateletter']['title']) ? $post['Realestateletter']['title'] : __('Letter')); ?></h1>
        <div class="wv-letterview-meta">
          <span><i class="fa fa-barcode"></i> <?php echo __('Ref:'); ?> <?php echo h($post['Realestateletter']['reference_no']); ?></span>
          <span><i class="fa fa-tag"></i> <?php echo h($letterTypeLabel); ?></span>
          <span><i class="fa fa-calendar"></i> <?php echo !empty($post['Realestateletter']['letter_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $post['Realestateletter']['letter_date']) : ''; ?></span>
        </div>
      </div>
      <div class="wv-letterview-actions">
        <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> ' . __('Back to Letters'), array('plugin' => false, 'admin' => false, 'controller' => 'MyDeals', 'action' => 'letters', $dealId), array('escape' => false, 'class' => 'btn btn-default')); ?>
        <?php echo $this->Html->link('<i class="fa fa-print"></i> ' . __('Print Document'), array('plugin' => false, 'admin' => false, 'controller' => 'MyDeals', 'action' => 'printletter', $dealId, $post['Realestateletter']['id']), array('escape' => false, 'class' => 'btn btn-primary', 'target' => '_blank')); ?>
      </div>
    </div>
  </div>

  <div class="wv-letterview-card">
    <!-- Metadata Grid -->
    <div class="wv-letterview-specs-grid">
      <div class="wv-letterview-spec">
        <small><?php echo __('Client Name'); ?></small>
        <strong><?php echo h($post['Client']['name']); ?></strong>
      </div>
      <div class="wv-letterview-spec">
        <small><?php echo __('Property Unit'); ?></small>
        <strong><?php echo h($post['Property']['name']); ?></strong>
      </div>
      <div class="wv-letterview-spec">
        <small><?php echo __('Deal / Invoice'); ?></small>
        <strong><?php echo h(!empty($post['Deal']['invoice_no']) ? $post['Deal']['invoice_no'] : ('#'.$dealId)); ?></strong>
      </div>
      <div class="wv-letterview-spec">
        <small><?php echo __('Document Status'); ?></small>
        <strong><span class="badge" style="background:#047857; color:#fff; font-weight:900; padding:4px 8px;"><?php echo h($post['Realestateletter']['status']); ?></span></strong>
      </div>
    </div>

    <!-- Letter Body / Document Preview -->
    <div class="wv-letterview-preview-wrap">
      <?php if(!empty($isFullHtmlTemplate)){ ?>
        <iframe src="<?php echo $this->Html->url(array('plugin' => false, 'admin' => false, 'controller' => 'MyDeals', 'action' => 'printletter', $dealId, $post['Realestateletter']['id'])); ?>" class="wv-letterview-iframe" title="<?php echo __('Document Preview'); ?>"></iframe>
      <?php } else { ?>
        <div class="wv-letterview-body-box">
          <?php echo $renderedDescription; ?>
        </div>
      <?php } ?>
    </div>
  </div>
</div>
