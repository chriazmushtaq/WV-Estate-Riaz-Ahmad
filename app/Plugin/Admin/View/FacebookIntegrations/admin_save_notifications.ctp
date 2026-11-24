<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success">
            <i class="fa fa-check-circle"></i> <?php echo __('Facebook notification settings have been updated successfully.');?>
        </div>
        <p>
            <a href="<?php echo $this->Html->url(array('action' => 'notifications'));?>" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> <?php echo __('Return to Notification Settings');?>
            </a>
        </p>
    </div>
</div>
<script>
    window.location.href = "<?php echo $this->Html->url(array('action' => 'notifications'));?>";
</script>