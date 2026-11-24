<?php
$links = !empty($whatsappLinks) ? (array)$whatsappLinks : array();
$returnUrl = !empty($returnUrl) ? $returnUrl : $this->Html->url(array('action'=>'index'));
?>

<style type="text/css">
.wv-whatsapp-bulk-page { color:#06162b; }
.wv-whatsapp-bulk-hero {
    background:linear-gradient(135deg,#07152a 0%,#0b5f56 64%,#128c7e 130%);
    border-radius:8px;
    color:#fff;
    margin-bottom:14px;
    padding:18px 20px;
}
.wv-whatsapp-bulk-hero h2 { font-size:24px;font-weight:900;margin:0 0 4px; }
.wv-whatsapp-bulk-hero p { color:#dffcf7;font-weight:800;margin:0; }
.wv-whatsapp-bulk-toolbar,
.wv-whatsapp-bulk-card {
    background:#fff;
    border:1px solid #dbe4ee;
    border-radius:8px;
    box-shadow:0 10px 24px rgba(7,21,42,.06);
    margin-bottom:12px;
    padding:12px;
}
.wv-whatsapp-bulk-toolbar {
    align-items:center;
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    justify-content:space-between;
}
.wv-whatsapp-bulk-card h4 {
    color:#07152a;
    font-size:15px;
    font-weight:900;
    margin:0 0 8px;
}
.wv-whatsapp-bulk-card textarea {
    background:#f8fbff;
    border:1px solid #dbe4ee;
    border-radius:8px;
    color:#24384f;
    font-size:12px;
    min-height:150px;
    resize:vertical;
}
.wv-whatsapp-bulk-meta {
    color:#60758e;
    font-size:12px;
    font-weight:800;
    margin-bottom:8px;
}
@media (max-width:767px) {
    .wv-whatsapp-bulk-toolbar { align-items:stretch; flex-direction:column; }
}
</style>

<div class="wv-whatsapp-bulk-page">
    <?php echo $this->Session->flash(); ?>

    <div class="wv-whatsapp-bulk-hero">
        <h2><?php echo __('Bulk WhatsApp Reminders'); ?></h2>
        <p><?php echo __('Manual WhatsApp mode opens chats with the message ready; WhatsApp still requires the final Send click.'); ?></p>
    </div>

    <div class="wv-whatsapp-bulk-toolbar">
        <div>
            <strong><?php echo sprintf(__('%s WhatsApp reminders ready.'), count($links)); ?></strong>
            <?php if(!empty($failed) || !empty($skipped)){ ?>
                <span><?php echo sprintf(__(' %s failed, %s skipped.'), (int)$failed, (int)$skipped); ?></span>
            <?php } ?>
        </div>
        <div>
            <button type="button" class="btn btn-success" id="OpenAllWhatsAppLinks">
                <span class="fa fa-whatsapp"></span>&nbsp;<?php echo __('Open All WhatsApp Chats'); ?>
            </button>
            <a href="<?php echo h($returnUrl); ?>" class="btn btn-default">
                <span class="fa fa-arrow-left"></span>&nbsp;<?php echo __('Back'); ?>
            </a>
        </div>
    </div>

    <?php if(!empty($errors)){ ?>
        <div class="alert alert-warning">
            <?php echo h(implode(' ', array_slice(array_unique($errors), 0, 5))); ?>
        </div>
    <?php } ?>

    <?php foreach($links as $i => $link){ ?>
        <div class="wv-whatsapp-bulk-card">
            <h4><?php echo h(($i + 1).'. '.$link['client_name'].' - '.$link['membership_no']); ?></h4>
            <div class="wv-whatsapp-bulk-meta">
                <?php echo __('Mobile'); ?>: <?php echo h($link['mobile']); ?>
            </div>
            <p>
                <a class="btn btn-primary js-whatsapp-link" target="_blank" href="<?php echo h($link['url']); ?>">
                    <span class="fa fa-whatsapp"></span>&nbsp;<?php echo __('Open WhatsApp'); ?>
                </a>
            </p>
            <textarea class="form-control" readonly="readonly"><?php echo h($link['body']); ?></textarea>
        </div>
    <?php } ?>
</div>

<script type="text/javascript">
(function($){
    $('#OpenAllWhatsAppLinks').on('click', function(){
        var links = $('.js-whatsapp-link');
        if(!links.length){
            return;
        }
        links.each(function(index, item){
            window.setTimeout(function(){
                window.open($(item).attr('href'), '_blank');
            }, index * 700);
        });
    });
})(jQuery);
</script>
