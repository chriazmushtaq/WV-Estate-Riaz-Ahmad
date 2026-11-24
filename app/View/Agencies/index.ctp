<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array('update'=>'#resultDiv','evalScripts'=>true));
$photoUrl=$this->Html->url(array('controller'=>'img'));
?>
<style>
.wv-directory-page{background:#f4f7fb;padding-bottom:48px;}
.wv-directory-hero{background:linear-gradient(135deg,#07152a 0%,#102746 64%,#ff0a46 155%);color:#fff;margin-bottom:0;padding:38px 0 64px;position:relative;text-align:center;overflow:hidden;}
.wv-directory-hero:after{background:linear-gradient(90deg,#ff0a46 0%,#7cf9ff 58%,rgba(124,249,255,0) 100%);bottom:0;content:"";height:3px;left:0;position:absolute;right:0;}
.wv-directory-hero h1{color:#fff;font-size:34px;font-weight:900;letter-spacing:0;margin:0 0 8px;}
.wv-directory-hero p{color:#d8e3f2;font-size:14px;font-weight:700;line-height:1.55;margin:0 auto;max-width:720px;}
.wv-directory-grid{align-items:stretch;display:grid;gap:22px;grid-template-columns:repeat(auto-fit,minmax(270px,310px));justify-content:center;margin-top:-34px;position:relative;z-index:2;}
.wv-directory-card{background:#fff;border:1px solid #dde7f3;border-radius:12px;box-shadow:0 16px 34px rgba(15,23,42,.10);overflow:hidden;position:relative;text-align:center;transition:border-color .2s ease,box-shadow .2s ease,transform .2s ease;}
.wv-directory-card:before{background:linear-gradient(90deg,#ff0a46 0%,#7cf9ff 100%);content:"";height:3px;left:0;position:absolute;right:0;top:0;}
.wv-directory-card:hover{border-color:rgba(255,10,70,.28);box-shadow:0 22px 42px rgba(15,23,42,.16);transform:translateY(-4px);}
.wv-directory-media{background:linear-gradient(135deg,#07152a 0%,#101f36 100%);height:126px;position:relative;}
.wv-directory-media:after{background:radial-gradient(circle at 50% 0%,rgba(124,249,255,.18),rgba(124,249,255,0) 58%);content:"";inset:0;position:absolute;}
.wv-directory-photo{background:#fff;background-position:center;background-repeat:no-repeat;background-size:contain;border:5px solid #fff;border-radius:18px;bottom:-44px;box-shadow:0 14px 28px rgba(15,23,42,.24);height:96px;left:50%;position:absolute;transform:translateX(-50%);width:96px;z-index:2;}
.wv-directory-badge{background:#ff0a46;border-radius:999px;box-shadow:0 10px 22px rgba(255,10,70,.22);color:#fff;font-size:10px;font-weight:900;left:50%;letter-spacing:.4px;padding:6px 12px;position:absolute;top:13px;transform:translateX(-50%);text-transform:uppercase;z-index:2;}
.wv-directory-body{padding:56px 18px 18px;}
.wv-directory-name{color:#07152a!important;display:block;font-size:18px;font-weight:900;line-height:1.25;min-height:46px;text-decoration:none!important;}
.wv-directory-name:hover{color:#ff0a46!important;}
.wv-directory-meta{display:grid;gap:8px;margin-top:14px;text-align:left;}
.wv-directory-meta a,.wv-directory-meta span{align-items:center;background:#fbfdff;border:1px solid #e3ebf5;border-radius:8px;color:#334155!important;display:flex;font-size:12px;font-weight:800;gap:9px;min-height:38px;min-width:0;padding:9px 10px;text-decoration:none!important;}
.wv-directory-meta a:hover{background:#fff1f4;border-color:rgba(255,10,70,.20);color:#07152a!important;}
.wv-directory-meta i{align-items:center;background:#fff1f4;border-radius:7px;color:#ff0a46;display:flex;flex:0 0 26px;height:26px;justify-content:center;text-align:center;width:26px;}
.wv-directory-meta small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.wv-directory-footer{align-items:center;border-top:1px solid #edf2f7;display:flex;gap:10px;justify-content:space-between;margin-top:15px;padding-top:14px;}
.wv-directory-tag{align-items:center;color:#047857;display:flex;font-size:11px;font-weight:900;gap:5px;text-align:left;}
.wv-directory-action{background:#07152a;border-radius:999px;color:#fff!important;font-size:11px;font-weight:900;padding:8px 12px;text-decoration:none!important;white-space:nowrap;}
.wv-directory-action:hover{background:#ff0a46;color:#fff!important;}
.wv-directory-empty{background:#fff;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b;font-weight:800;margin-top:-30px;padding:28px;position:relative;text-align:center;z-index:2;}
.wv-directory-pagination{background:#fff;border:1px solid #dfe7f3;border-radius:10px;box-shadow:0 10px 24px rgba(15,23,42,.06);margin:22px auto 0;max-width:760px;padding:12px;}
@media(max-width:600px){.wv-directory-grid{grid-template-columns:minmax(0,1fr);margin-top:-28px}.wv-directory-hero{padding:30px 0 54px}.wv-directory-hero h1{font-size:26px;}.wv-directory-footer{display:block}.wv-directory-action{display:block;margin-top:10px;text-align:center;}}
</style>
<div id="resultDiv">
    <div class="page-wrapper wv-directory-page">
        <section class="wv-directory-hero">
            <div class="container">
                <h1><?php echo __('Agencies');?></h1>
                <p><?php echo __('Verified agencies connected with White Villas published property inventory.');?></p>
            </div>
        </section>
        <div class="container">
            <?php echo $this->Session->flash();?>
            <?php if(!empty($Agency)){ ?>
            <div class="wv-directory-grid">
                <?php foreach($Agency as $post){
                    $id=(int)$post['Agency']['id'];
                    $name=!empty($post['User']['name']) ? $post['User']['name'] : __('Agency');
                    $email=!empty($post['User']['email']) ? $post['User']['email'] : '';
                    $phone=!empty($post['User']['phone']) ? $post['User']['phone'] : '';
                    $mobile=!empty($post['User']['mobile']) ? $post['User']['mobile'] : '';
                    $urlPhoto=empty($post['User']['photo']) ? $photoUrl.'/logo-website.fw.png' : $photoUrl.'/user_thumb/'.$post['User']['photo'];
                ?>
                <article class="wv-directory-card">
                    <div class="wv-directory-media">
                        <span class="wv-directory-badge"><?php echo __('Agency');?></span>
                        <a href="<?php echo $this->Html->url(array('controller'=>'Agencies','action'=>'view',$id));?>" class="wv-directory-photo" style="background-image:url('<?php echo h($urlPhoto);?>');"></a>
                    </div>
                    <div class="wv-directory-body">
                        <?php echo $this->Html->link(h($name),array('controller'=>'Agencies','action'=>'view',$id),array('class'=>'wv-directory-name','escape'=>false));?>
                        <div class="wv-directory-meta">
                            <?php if($email!=''){ ?><a href="mailto:<?php echo h($email);?>"><i class="fa fa-at"></i><small><?php echo h($email);?></small></a><?php } else { ?><span><i class="fa fa-at"></i><small><?php echo __('Not Specified');?></small></span><?php } ?>
                            <?php if($phone!=''){ ?><a href="tel:<?php echo h(preg_replace('/[^0-9\+]/','',$phone));?>"><i class="fa fa-phone"></i><small><?php echo h($phone);?></small></a><?php } else { ?><span><i class="fa fa-phone"></i><small><?php echo __('Not Specified');?></small></span><?php } ?>
                            <?php if($mobile!=''){ ?><a href="tel:<?php echo h(preg_replace('/[^0-9\+]/','',$mobile));?>"><i class="fa fa-mobile"></i><small><?php echo h($mobile);?></small></a><?php } ?>
                        </div>
                        <div class="wv-directory-footer">
                            <span class="wv-directory-tag"><i class="fa fa-check-circle"></i> <?php echo __('Verified');?></span>
                            <?php echo $this->Html->link(__('View Details').' <i class="fa fa-angle-right"></i>',array('controller'=>'Agencies','action'=>'view',$id),array('class'=>'wv-directory-action','escape'=>false));?>
                        </div>
                    </div>
                </article>
                <?php } ?>
            </div>
            <div class="wv-directory-pagination">
                <?php echo $this->element('pagination',array('IsSearch'=>'No','IsDropdown'=>'No'));?>
            </div>
            <?php } else { ?>
            <div class="wv-directory-empty"><?php echo __('No agencies found.');?></div>
            <?php } ?>
        </div>
    </div>
</div>
