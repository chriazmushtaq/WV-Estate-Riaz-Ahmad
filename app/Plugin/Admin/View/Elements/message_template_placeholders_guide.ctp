<?php
$guideByCategory = isset($placeholderGuideByCategory) ? (array)$placeholderGuideByCategory : array();
$typeHints = isset($placeholderTypeHints) ? (array)$placeholderTypeHints : array();
?>
<div class="panel panel-default" style="margin-top:12px;">
    <div class="panel-heading">
        <strong><?php echo __('Placeholder Guide');?></strong>
    </div>
    <div class="panel-body">
        <p class="text-muted" style="margin-bottom:10px;">
            <?php echo __('Use variables exactly as shown below.');?> 
            <strong><?php echo __('Note');?>:</strong>
            <?php echo __('Use {{...}} tokens for LED templates, and $variables for other system templates.');?>
        </p>

        <?php if(!empty($typeHints)){ ?>
        <div style="margin-bottom:12px;">
            <?php foreach($typeHints as $typeCode => $hintText){ ?>
            <div style="margin-bottom:4px;">
                <code><?php echo h($typeCode);?></code>
                <span class="text-muted">- <?php echo h($hintText);?></span>
            </div>
            <?php } ?>
        </div>
        <?php } ?>

        <?php if(!empty($guideByCategory)){ ?>
        <div class="row">
            <?php foreach($guideByCategory as $categoryLabel => $tokens){ ?>
            <?php $tokens = array_values(array_unique(array_filter((array)$tokens))); if(empty($tokens)){ continue; } ?>
            <div class="col-sm-6" style="margin-bottom:10px;">
                <div style="background:#fbfbfb;border:1px solid #ddd;border-radius:4px;padding:8px 10px;">
                    <div style="font-weight:700;margin-bottom:6px;"><?php echo h($categoryLabel);?></div>
                    <div>
                        <?php foreach($tokens as $token){ ?>
                        <code style="display:inline-block;margin:2px 3px 2px 0;"><?php echo h($token);?></code>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
</div>
