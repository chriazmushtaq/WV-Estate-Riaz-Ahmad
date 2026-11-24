<?php
$media = isset($media) ? (array)$media : array();
$m = !empty($media['MediaFile']) ? (array)$media['MediaFile'] : array();
?>
<style>
.wv-media-detail{background:#f7f9fc;padding-bottom:38px;}
.wv-media-detail-hero{background:linear-gradient(135deg,#07152a 0%,#102746 68%,#ff0a46 150%);color:#fff;margin-bottom:26px;padding:24px 0;}
.wv-media-detail-hero h1{color:#fff;font-size:28px;font-weight:900;margin:0 0 6px;}
.wv-media-detail-hero p{color:#cbd5e1;font-size:13px;font-weight:700;margin:0;}
.wv-media-detail-card{background:#fff;border:1px solid #dfe7f1;border-radius:10px;box-shadow:0 12px 28px rgba(15,23,42,.08);overflow:hidden;}
.wv-media-detail-preview{align-items:center;background:#eef3f8;display:flex;justify-content:center;min-height:420px;padding:16px;}
.wv-media-detail-preview img{border-radius:8px;max-height:680px;max-width:100%;object-fit:contain;}
.wv-media-detail-preview i{color:#07152a;font-size:82px;}
.wv-media-detail-body{align-items:center;display:flex;gap:14px;justify-content:space-between;padding:16px;}
.wv-media-detail-body h2{color:#07152a;font-size:20px;font-weight:900;margin:0 0 5px;}
.wv-media-detail-body p{color:#64748b;font-size:12px;font-weight:800;margin:0;}
.wv-media-detail-actions{display:flex;gap:8px;}
.wv-media-detail-actions .btn{border-radius:999px;font-weight:900;}
@media(max-width:767px){.wv-media-detail-body{align-items:flex-start;display:block}.wv-media-detail-actions{margin-top:14px}.wv-media-detail-preview{min-height:260px;}}
</style>

<div class="wv-media-detail">
    <div class="wv-media-detail-hero">
        <div class="container">
            <h1><?php echo __('Media Preview');?></h1>
            <p><?php echo __('Published White Villas media file.');?></p>
        </div>
    </div>
    <div class="container">
        <div class="wv-media-detail-card">
            <div class="wv-media-detail-preview">
                <?php if(!empty($m['file_type']) && $m['file_type']==='image' && !empty($m['exists'])){ ?>
                    <img src="<?php echo h($m['url']);?>" alt="<?php echo h($m['title']);?>">
                <?php }else{ ?>
                    <i class="<?php echo h(!empty($m['icon']) ? $m['icon'] : 'fa fa-file-o');?>"></i>
                <?php } ?>
            </div>
            <div class="wv-media-detail-body">
                <div>
                    <h2><?php echo h(!empty($m['title']) ? $m['title'] : __('Media File'));?></h2>
                    <p><?php echo h(strtoupper(!empty($m['extension']) ? $m['extension'] : ''));?> · <?php echo h(!empty($m['size_label']) ? $m['size_label'] : '0 KB');?></p>
                </div>
                <div class="wv-media-detail-actions">
                    <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> '.__('Back'), array('action'=>'index'), array('escape'=>false,'class'=>'btn btn-danger'));?>
                    <?php echo $this->Html->link('<i class="fa fa-external-link"></i> '.__('Open File'), !empty($m['url']) ? $m['url'] : '#', array('escape'=>false,'target'=>'_blank','class'=>'btn btn-primary'));?>
                </div>
            </div>
        </div>
    </div>
</div>
