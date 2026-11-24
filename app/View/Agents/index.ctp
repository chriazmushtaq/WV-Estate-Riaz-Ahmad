<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array('update'=>'#resultDiv','evalScripts'=>true));
$photoUrl=$this->Html->url(array('controller'=>'img'));
?>
<style>
.wv-directory-page{background:#f7f9fc;padding-bottom:40px;}
.wv-directory-hero{background:linear-gradient(135deg,#07152a 0%,#102746 65%,#ff0a46 155%);color:#fff;margin-bottom:26px;padding:30px 0;}
.wv-directory-hero h1{color:#fff;font-size:30px;font-weight:900;margin:0 0 6px;}
.wv-directory-hero p{color:#cbd5e1;font-size:13px;font-weight:700;margin:0;}
.wv-agent-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;align-items:stretch;}
.wv-agent-card{background:#fff;border:1px solid #dfe7f1;border-radius:10px;box-shadow:0 12px 26px rgba(15,23,42,.08);display:flex;flex-direction:column;min-height:100%;overflow:hidden;transition:box-shadow .2s ease,transform .2s ease;}
.wv-agent-card:hover{box-shadow:0 18px 34px rgba(15,23,42,.14);transform:translateY(-2px);}
.wv-agent-media{background:linear-gradient(135deg,#07152a,#102746);height:138px;position:relative;}
.wv-agent-photo{background:#fff;background-position:center;background-size:cover;border:4px solid #fff;border-radius:50%;bottom:-38px;box-shadow:0 12px 24px rgba(15,23,42,.22);height:92px;left:50%;position:absolute;transform:translateX(-50%);width:92px;}
.wv-agent-badge{background:#ff0a46;border-radius:999px;color:#fff;font-size:10px;font-weight:900;padding:6px 10px;position:absolute;right:12px;top:12px;text-transform:uppercase;}
.wv-agent-body{display:flex;flex:1;flex-direction:column;padding:46px 15px 15px;text-align:center;}
.wv-agent-name{align-items:center;color:#07152a!important;display:inline-flex;font-size:17px;font-weight:900;gap:7px;justify-content:center;line-height:1.25;min-height:42px;text-decoration:none!important;}
.wv-agent-name:hover{color:#ff0a46!important;}
.wv-agent-verified{align-items:center;background:#1677d2;border:2px solid #fff;border-radius:50%;box-shadow:0 0 0 1px #1677d2;color:#fff;display:inline-flex;flex:0 0 18px;font-size:9px;height:18px;justify-content:center;vertical-align:middle;width:18px;}
.wv-agent-count{background:#ecfdf5;border:1px solid #bbf7d0;border-radius:999px;color:#0b4d36;display:inline-block;font-size:11px;font-weight:900;margin:8px auto 12px;padding:6px 10px;}
.wv-agent-meta{display:grid;gap:7px;margin-bottom:13px;text-align:left;}
.wv-agent-meta a,.wv-agent-meta span{align-items:center;background:#f8fafc;border:1px solid #edf2f7;border-radius:7px;color:#334155!important;display:flex;font-size:12px;font-weight:800;gap:8px;min-height:34px;min-width:0;padding:8px;text-decoration:none!important;}
.wv-agent-meta i{color:#ff0a46;width:14px;text-align:center;}
.wv-agent-meta small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.wv-agent-footer{align-items:center;border-top:1px solid #eef2f7;display:flex;gap:8px;justify-content:space-between;margin-top:auto;padding-top:12px;}
.wv-agent-link{background:#fff1f4;border-radius:999px;color:#ff0a46!important;font-size:11px;font-weight:900;padding:7px 10px;text-decoration:none!important;white-space:nowrap;}
.wv-agent-link:hover{background:#ff0a46;color:#fff!important;}
.wv-agent-properties{color:#0b4d36!important;font-size:11px;font-weight:900;text-decoration:none!important;}
.wv-agent-empty{background:#fff;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b;font-weight:800;padding:28px;text-align:center;}
.wv-agent-pagination{background:#fff;border:1px solid #dfe7f1;border-radius:10px;box-shadow:0 10px 24px rgba(15,23,42,.06);margin-top:18px;padding:12px;}
@media(max-width:1199px){.wv-agent-grid{grid-template-columns:repeat(3,minmax(0,1fr));}}
@media(max-width:991px){.wv-agent-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:600px){.wv-agent-grid{grid-template-columns:1fr}.wv-directory-hero h1{font-size:24px;}}
</style>
<div id="resultDiv">
    <div class="page-wrapper wv-directory-page">
        <section class="wv-directory-hero">
            <div class="container">
                <h1><?php echo __('Agents');?></h1>
                <p><?php echo __('Meet active White Villas agents and open their published property inventory.');?></p>
            </div>
        </section>
        <div class="container">
            <?php echo $this->Session->flash();?>
            <?php if(!empty($agent)){ ?>
            <div class="wv-agent-grid">
                <?php foreach($agent as $post){
                    $id=(int)$post['Agent']['id'];
                    $name=!empty($post['Agent']['name']) ? $post['Agent']['name'] : __('Agent');
                    $email=!empty($post['Agent']['email']) ? $post['Agent']['email'] : '';
                    $phone=!empty($post['Agent']['phone']) ? $post['Agent']['phone'] : '';
                    $total=!empty($post['Agent']['total_count']) ? (int)$post['Agent']['total_count'] : 0;
                    $urlPhoto=empty($post['Agent']['photo']) ? $photoUrl.'/user-icon.png' : $photoUrl.'/user_thumb/'.$post['Agent']['photo'];
                ?>
                <article class="wv-agent-card">
                    <div class="wv-agent-media">
                        <span class="wv-agent-badge"><?php echo __('Agent');?></span>
                        <a href="<?php echo $this->Html->url(array('controller'=>'Agents','action'=>'view',$id));?>" class="wv-agent-photo" style="background-image:url('<?php echo h($urlPhoto);?>');"></a>
                    </div>
                    <div class="wv-agent-body">
                        <?php
                        echo $this->Html->link(
                            '<span>'.h($name).'</span><span class="wv-agent-verified" title="'.h(__('Verified Agent')).'"><i class="fa fa-check"></i></span>',
                            array('controller'=>'Agents','action'=>'view',$id),
                            array('class'=>'wv-agent-name','escape'=>false)
                        );
                        ?>
                        <?php echo $this->Html->link('<strong>'.$total.'</strong> '.__('properties in catalogue'),array('controller'=>'Properties','action'=>'index','agent:'.$id),array('escape'=>false,'class'=>'wv-agent-count'));?>
                        <div class="wv-agent-meta">
                            <?php if($email!=''){ ?><a href="mailto:<?php echo h($email);?>"><i class="fa fa-at"></i><small><?php echo h($email);?></small></a><?php } else { ?><span><i class="fa fa-at"></i><small><?php echo __('Not Specified');?></small></span><?php } ?>
                            <?php if($phone!=''){ ?><a href="tel:<?php echo h(preg_replace('/[^0-9\+]/','',$phone));?>"><i class="fa fa-phone"></i><small><?php echo h($phone);?></small></a><?php } else { ?><span><i class="fa fa-phone"></i><small><?php echo __('Not Specified');?></small></span><?php } ?>
                        </div>
                        <div class="wv-agent-footer">
                            <?php echo $this->Html->link('<i class="fa fa-building"></i> '.__('Properties'),array('controller'=>'Properties','action'=>'index','agent:'.$id),array('class'=>'wv-agent-properties','escape'=>false));?>
                            <?php echo $this->Html->link(__('View Details').' <i class="fa fa-angle-right"></i>',array('controller'=>'Agents','action'=>'view',$id),array('class'=>'wv-agent-link','escape'=>false));?>
                        </div>
                    </div>
                </article>
                <?php } ?>
            </div>
            <div class="wv-agent-pagination">
                <?php echo $this->element('pagination',array('IsSearch'=>'No','IsDropdown'=>'No'));?>
            </div>
            <?php } else { ?>
            <div class="wv-agent-empty"><?php echo __('No agents found.');?></div>
            <?php } ?>
        </div>
    </div>
</div>
