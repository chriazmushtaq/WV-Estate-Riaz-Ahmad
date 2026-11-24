<style type="text/css">
.contact-view-wrap .table > tbody > tr > td{
    vertical-align:middle;
}
.contact-view-wrap .contact-head{
    margin:0 0 12px 0;
    font-size:18px;
    font-weight:600;
}
.contact-view-wrap .contact-label{
    color:#e24a4a;
    font-size:12px;
    font-weight:600;
}
.contact-media-grid{
    margin-top:10px;
}
.contact-media-card{
    border:1px solid #d9e2ef;
    border-radius:8px;
    background:#f9fbff;
    padding:10px;
    margin-bottom:10px;
    min-height:220px;
}
.contact-media-title{
    font-size:12px;
    font-weight:700;
    color:#2f4363;
    margin-bottom:8px;
}
.contact-media-preview{
    height:130px;
    background:#fff;
    border:1px solid #e5ebf4;
    border-radius:6px;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    padding:4px;
}
.contact-media-preview img{
    max-height:122px;
    max-width:100%;
    object-fit:contain;
}
.contact-media-empty{
    color:#8a97ab;
    font-size:12px;
}
.contact-media-actions{
    margin-top:8px;
}
.contact-media-actions .btn{
    margin-right:4px;
    margin-bottom:4px;
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
$mediaColClass = $hasSidePhoto ? 'col-sm-6 col-md-3' : 'col-sm-4';
?>

<div class="container contact-view-wrap">
    <div class="panel panel-custom mrg">
        <div class="panel-heading"><?php echo __('Details');?><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button></div>
        <div class="panel-body">
            <?php echo $this->Session->flash();?>

            <h4 class="contact-head"><?php echo __('Contact Information');?></h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tr>
                        <td width="16%"><span class="contact-label"><?php echo __('Name');?></span></td>
                        <td width="34%">
                            <?php echo h($post['Contact']['name']);?>
                            <?php if(!empty($post['Contact']['app_login_status']) || !empty($post['Contact']['app_last_login'])){ ?>
                                <span class="label label-success" style="background-color:#10B981!important;color:#ffffff;padding:2px 7px;border-radius:4px;font-size:9.5px;font-weight:bold;margin-left:6px;display:inline-block;vertical-align:middle;">Login</span>
                            <?php } else { ?>
                                <span class="label label-danger" style="background-color:#EF4444!important;color:#ffffff;padding:2px 7px;border-radius:4px;font-size:9.5px;font-weight:bold;margin-left:6px;display:inline-block;vertical-align:middle;">Logout</span>
                            <?php } ?>
                            <?php
                            $hasCnicNo  = !empty(trim((string)$post['Contact']['pincode']));
                            $hasPhoto   = !empty(trim((string)$post['Contact']['photo']));
                            $hasIdFront = !empty(trim((string)$post['Contact']['id_front']));
                            $hasIdBack  = !empty(trim((string)$post['Contact']['id_back']));
                            $docScore   = ($hasCnicNo ? 1 : 0) + ($hasPhoto ? 1 : 0) + ($hasIdFront ? 1 : 0) + ($hasIdBack ? 1 : 0);
                            ?>
                            <?php if($docScore === 4): ?>
                                <span class="label label-success" style="background-color:#10B981!important;color:#ffffff;padding:2px 7px;border-radius:4px;font-size:9.5px;font-weight:bold;margin-left:6px;display:inline-block;vertical-align:middle;"><i class="fa fa-check-circle"></i> <?php echo __('KYC Verified');?></span>
                            <?php elseif($docScore > 0): ?>
                                <span class="label label-warning" style="background-color:#F59E0B!important;color:#ffffff;padding:2px 7px;border-radius:4px;font-size:9.5px;font-weight:bold;margin-left:6px;display:inline-block;vertical-align:middle;"><i class="fa fa-exclamation-circle"></i> <?php echo sprintf(__('KYC Partial (%d/4)'), $docScore);?></span>
                            <?php else: ?>
                                <span class="label label-danger" style="background-color:#EF4444!important;color:#ffffff;padding:2px 7px;border-radius:4px;font-size:9.5px;font-weight:bold;margin-left:6px;display:inline-block;vertical-align:middle;"><i class="fa fa-clock-o"></i> <?php echo __('KYC Pending');?></span>
                            <?php endif; ?>
                        </td>
                        <td width="16%"><span class="contact-label"><?php echo !empty($post['Contact']['relation_type']) ? h($post['Contact']['relation_type']).' '.__('Name') : __('Father / Guardian Name');?></span></td>
                        <td width="34%">
                            <?php if(!empty($post['Contact']['fathername'])): ?>
                                <strong><?php echo h($post['Contact']['fathername']);?></strong>
                                <?php if(!empty($post['Contact']['relation_type'])): ?>
                                    <span class="label label-info" style="margin-left:6px;font-size:10px;"><?php echo h($post['Contact']['relation_type']);?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted"><?php echo __('N/A');?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="contact-label"><?php echo __('Address');?></span></td>
                        <td><?php echo !empty($post['Contact']['address']) ? h($post['Contact']['address']) : __('N/A');?></td>
                        <td><span class="contact-label"><?php echo __('Location / Area');?></span></td>
                        <td><?php echo !empty($post['Contact']['address_hierarchy_path']) ? h($post['Contact']['address_hierarchy_path']) : (!empty($post['Contact']['district']) ? h($post['Contact']['district']).(!empty($post['Contact']['state']) ? ', '.h($post['Contact']['state']) : '') : __('N/A'));?></td>
                    </tr>
                    <tr>
                        <td><span class="contact-label"><?php echo __('Category');?></span></td>
                        <td><?php echo !empty($post['Category']['name']) ? h($post['Category']['name']) : __('N/A');?></td>
                        <td><span class="contact-label"><?php echo __('Gender');?></span></td>
                        <td><?php echo !empty($post['Contact']['gender']) ? h($post['Contact']['gender']) : __('N/A');?></td>
                    </tr>
                    <tr>
                        <td><span class="contact-label"><?php echo __('CNIC');?></span></td>
                        <td><?php echo !empty($post['Contact']['pincode']) ? h($post['Contact']['pincode']) : __('N/A');?></td>
                        <td><span class="contact-label"><?php echo __('Email');?></span></td>
                        <td><?php echo !empty($post['Contact']['email']) ? h($post['Contact']['email']) : __('N/A');?></td>
                    </tr>
                    <tr>
                        <td><span class="contact-label"><?php echo __('Cell Phone');?></span></td>
                        <td><?php echo !empty($post['Contact']['mobile']) ? h($post['Contact']['mobile']) : __('N/A');?></td>
                        <td><span class="contact-label"><?php echo __('Mobile SMS');?></span></td>
                        <td><?php echo !empty($post['Contact']['phone']) ? h($post['Contact']['phone']) : __('N/A');?></td>
                    </tr>
                </table>
            </div>

            <h4 class="contact-head"><?php echo __('Uploaded Images & CNIC / Identity Documents');?></h4>
            <div class="row contact-media-grid">
                <div class="<?php echo $mediaColClass;?>">
                    <div class="contact-media-card">
                        <div class="contact-media-title"><i class="fa fa-user"></i> <?php echo __('Profile Photo');?></div>
                        <div class="contact-media-preview">
                            <?php if(!empty($post['Contact']['photo'])){ echo $this->Html->image($profileImages['thumb'], array('alt' => __('Profile Photo'))); } else { ?><span class="contact-media-empty"><?php echo __('N/A');?></span><?php }?>
                        </div>
                        <div class="contact-media-actions">
                            <?php if(!empty($post['Contact']['photo'])){ ?>
                                <?php echo $this->Html->link('<i class="fa fa-eye"></i> '.__('View Full'), $profileImages['original'], array('class' => 'btn btn-xs btn-primary', 'target' => '_blank', 'escape' => false));?>
                                <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download'), $profileImages['original'], array('class' => 'btn btn-xs btn-default', 'target' => '_blank', 'download' => $post['Contact']['photo'], 'escape' => false));?>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="<?php echo $mediaColClass;?>">
                    <div class="contact-media-card">
                        <div class="contact-media-title"><i class="fa fa-id-card-o"></i> <?php echo __('ID Front (CNIC Front)');?></div>
                        <div class="contact-media-preview">
                            <?php if(!empty($post['Contact']['id_front'])){ echo $this->Html->image($idFrontImages['thumb'], array('alt' => __('ID Front'))); } else { ?><span class="contact-media-empty"><?php echo __('N/A');?></span><?php }?>
                        </div>
                        <div class="contact-media-actions">
                            <?php if(!empty($post['Contact']['id_front'])){ ?>
                                <?php echo $this->Html->link('<i class="fa fa-eye"></i> '.__('View Full'), $idFrontImages['original'], array('class' => 'btn btn-xs btn-primary', 'target' => '_blank', 'escape' => false));?>
                                <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download'), $idFrontImages['original'], array('class' => 'btn btn-xs btn-default', 'target' => '_blank', 'download' => $post['Contact']['id_front'], 'escape' => false));?>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="<?php echo $mediaColClass;?>">
                    <div class="contact-media-card">
                        <div class="contact-media-title"><i class="fa fa-id-card"></i> <?php echo __('ID Back (CNIC Back)');?></div>
                        <div class="contact-media-preview">
                            <?php if(!empty($post['Contact']['id_back'])){ echo $this->Html->image($idBackImages['thumb'], array('alt' => __('ID Back'))); } else { ?><span class="contact-media-empty"><?php echo __('N/A');?></span><?php }?>
                        </div>
                        <div class="contact-media-actions">
                            <?php if(!empty($post['Contact']['id_back'])){ ?>
                                <?php echo $this->Html->link('<i class="fa fa-eye"></i> '.__('View Full'), $idBackImages['original'], array('class' => 'btn btn-xs btn-primary', 'target' => '_blank', 'escape' => false));?>
                                <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download'), $idBackImages['original'], array('class' => 'btn btn-xs btn-default', 'target' => '_blank', 'download' => $post['Contact']['id_back'], 'escape' => false));?>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <?php if($hasSidePhoto): ?>
                <div class="<?php echo $mediaColClass;?>">
                    <div class="contact-media-card">
                        <div class="contact-media-title"><i class="fa fa-picture-o"></i> <?php echo __('Gallery / Side Photo');?></div>
                        <div class="contact-media-preview">
                            <?php echo $this->Html->image($sidePhotoImages['thumb'], array('alt' => __('Gallery / Side Photo'))); ?>
                        </div>
                        <div class="contact-media-actions">
                            <?php echo $this->Html->link('<i class="fa fa-eye"></i> '.__('View Full'), $sidePhotoImages['original'], array('class' => 'btn btn-xs btn-primary', 'target' => '_blank', 'escape' => false));?>
                            <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Download'), $sidePhotoImages['original'], array('class' => 'btn btn-xs btn-default', 'target' => '_blank', 'download' => $post['Contact']['side_photo'], 'escape' => false));?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
