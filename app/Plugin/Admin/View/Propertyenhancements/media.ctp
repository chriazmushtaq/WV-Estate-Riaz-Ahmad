<?php echo $this->element('property_enhancement_nav', array('property'=>$property));?>
<?php echo $this->Session->flash();?>
<div class="wv-enhance-card">
    <h3><?php echo __('Media Metadata');?></h3>
    <p class="wv-muted"><?php echo __('This form controls titles, image alt text, gallery order, and public document visibility only. File upload/delete remains in the existing media screens.');?></p>
    <?php echo $this->Form->create(false, array('url'=>array('action'=>'media',(int)$property['Property']['id'])));?>
    <h4><?php echo __('Photos');?></h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped wv-enhance-table">
            <tr><th><?php echo __('Photo');?></th><th><?php echo __('Title');?></th><th><?php echo __('Alt Text');?></th><th><?php echo __('Order');?></th></tr>
            <?php foreach((array)$photos as $photo){ $p=$photo['PropertiesPhoto']; ?>
            <tr>
                <td><?php echo $this->Html->image($p['dir'].'_thumb/'.$p['photo'], array('style'=>'height:58px;width:82px;object-fit:cover;'));?> <?php echo $p['home']==='Yes' ? '<span class="label label-success">'.__('Front').'</span>' : '';?></td>
                <td><input type="text" name="data[PropertiesPhoto][<?php echo (int)$p['id'];?>][title]" class="form-control" value="<?php echo h($p['title']);?>"></td>
                <td><input type="text" name="data[PropertiesPhoto][<?php echo (int)$p['id'];?>][alt_text]" class="form-control" value="<?php echo h($p['alt_text']);?>"></td>
                <td><input type="number" name="data[PropertiesPhoto][<?php echo (int)$p['id'];?>][sort_order]" class="form-control" value="<?php echo (int)$p['sort_order'];?>"></td>
            </tr>
            <?php } ?>
            <?php if(empty($photos)){ ?><tr><td colspan="4"><?php echo __('No photos uploaded.');?></td></tr><?php } ?>
        </table>
    </div>
    <h4><?php echo __('Documents');?></h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped wv-enhance-table">
            <tr><th><?php echo __('Document');?></th><th><?php echo __('Title');?></th><th><?php echo __('Public');?></th><th><?php echo __('Order');?></th></tr>
            <?php foreach((array)$documents as $doc){ $d=$doc['PropertiesDocument']; ?>
            <tr>
                <td><?php echo $this->Html->link(h($d['photo']), array('controller'=>'../img','action'=>$d['dir'],$d['photo']), array('target'=>'_blank'));?></td>
                <td><input type="text" name="data[PropertiesDocument][<?php echo (int)$d['id'];?>][title]" class="form-control" value="<?php echo h($d['title']);?>"></td>
                <td><input type="hidden" name="data[PropertiesDocument][<?php echo (int)$d['id'];?>][public_visible]" value="0"><label><input type="checkbox" name="data[PropertiesDocument][<?php echo (int)$d['id'];?>][public_visible]" value="1" <?php echo !empty($d['public_visible']) ? 'checked' : '';?>> <?php echo __('Show on frontend');?></label></td>
                <td><input type="number" name="data[PropertiesDocument][<?php echo (int)$d['id'];?>][sort_order]" class="form-control" value="<?php echo (int)$d['sort_order'];?>"></td>
            </tr>
            <?php } ?>
            <?php if(empty($documents)){ ?><tr><td colspan="4"><?php echo __('No documents uploaded.');?></td></tr><?php } ?>
        </table>
    </div>
    <h4><?php echo __('Floor Plans');?></h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped wv-enhance-table">
            <tr><th><?php echo __('Plan');?></th><th><?php echo __('Title');?></th><th><?php echo __('Alt Text');?></th><th><?php echo __('Order');?></th></tr>
            <?php foreach((array)$floorplans as $floorplan){ $f=$floorplan['PropertiesFloorplan']; ?>
            <tr>
                <td><?php echo $this->Html->image($f['dir'].'_thumb/'.$f['photo'], array('style'=>'height:58px;width:82px;object-fit:cover;'));?></td>
                <td><input type="text" name="data[PropertiesFloorplan][<?php echo (int)$f['id'];?>][title]" class="form-control" value="<?php echo h($f['title']);?>"></td>
                <td><input type="text" name="data[PropertiesFloorplan][<?php echo (int)$f['id'];?>][alt_text]" class="form-control" value="<?php echo h($f['alt_text']);?>"></td>
                <td><input type="number" name="data[PropertiesFloorplan][<?php echo (int)$f['id'];?>][sort_order]" class="form-control" value="<?php echo (int)$f['sort_order'];?>"></td>
            </tr>
            <?php } ?>
            <?php if(empty($floorplans)){ ?><tr><td colspan="4"><?php echo __('No floor plans uploaded.');?></td></tr><?php } ?>
        </table>
    </div>
    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?php echo __('Save Media Details');?></button>
    <?php echo $this->Form->end();?>
</div>
