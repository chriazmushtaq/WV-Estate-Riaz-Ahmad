<style>
body{background:#eef3f7;font-family:Arial,Helvetica,sans-serif;margin:0;padding:18px;color:#172033;}
.preview-shell{background:#fff;border:1px solid #dbe7f3;border-radius:8px;margin:0 auto;max-width:920px;padding:16px;}
.preview-meta{background:#f8fafc;border:1px solid #dbe7f3;border-radius:6px;margin-bottom:14px;padding:10px;}
</style>
<div class="preview-shell">
    <div class="preview-meta">
        <strong><?php echo h($template['name']); ?></strong><br>
        <?php echo __('Channel'); ?>: <?php echo h($template['channel']); ?> |
        <?php echo __('Subject'); ?>: <?php echo h($subject); ?>
    </div>
    <?php echo $body; ?>
</div>
