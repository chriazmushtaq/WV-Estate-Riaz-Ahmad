<?php
$userName = !empty($post['User']['name']) ? $post['User']['name'] : __('User');
$username = !empty($post['User']['username']) ? $post['User']['username'] : __('Not Specified');
$userLevel = !empty($post['Ugroup']['name']) ? $post['Ugroup']['name'] : __('Not Specified');
$locationName = !empty($post['Location']['name']) ? $post['Location']['name'] : __('Not Specified');
$email = !empty($post['User']['email']) ? $post['User']['email'] : '';
$phone = !empty($post['User']['phone']) ? $post['User']['phone'] : '';
$mobile = !empty($post['User']['mobile']) ? $post['User']['mobile'] : '';
$address = !empty($post['User']['address']) ? $post['User']['address'] : '';
$description = !empty($post['User']['description']) ? $post['User']['description'] : '';
$skype = !empty($post['User']['skype']) ? $post['User']['skype'] : '';
$twitter = !empty($post['User']['twitter']) ? $post['User']['twitter'] : '';
$facebook = !empty($post['User']['facebook']) ? $post['User']['facebook'] : '';
$linkedin = !empty($post['User']['linkedin']) ? $post['User']['linkedin'] : '';
$userInitial = strtoupper(substr(trim((string)$userName), 0, 1));
if($userInitial === '')
{
    $userInitial = 'U';
}
$phoneClean = preg_replace('/[^0-9\+]/', '', $phone);
$mobileClean = preg_replace('/[^0-9\+]/', '', $mobile);
?>
<style>
.wv-profile-page{background:#f3f6fa;margin:-10px -10px 0;padding:18px;}
.wv-profile-hero{background:linear-gradient(135deg,#07152a 0%,#102746 72%,#ff0a46 170%);border-radius:12px;box-shadow:0 18px 36px rgba(15,23,42,.16);color:#fff;overflow:hidden;position:relative;}
.wv-profile-hero:after{background:radial-gradient(circle at 78% 10%,rgba(124,249,255,.22),rgba(124,249,255,0) 38%);content:"";inset:0;position:absolute;}
.wv-profile-hero-inner{align-items:center;display:grid;gap:22px;grid-template-columns:auto minmax(0,1fr) auto;padding:28px;position:relative;z-index:2;}
.wv-profile-avatar{background:#fff;border:5px solid rgba(255,255,255,.35);border-radius:22px;box-shadow:0 16px 32px rgba(2,8,23,.22);height:118px;overflow:hidden;width:118px;}
.wv-profile-avatar img{display:block;height:100%;object-fit:cover;width:100%;}
.wv-profile-avatar-fallback{align-items:center;background:linear-gradient(135deg,#ff0a46,#102746);color:#fff;display:flex;font-size:42px;font-weight:900;height:100%;justify-content:center;width:100%;}
.wv-profile-title h1{color:#fff;font-size:30px;font-weight:900;line-height:1.15;margin:0 0 8px;}
.wv-profile-sub{align-items:center;display:flex;flex-wrap:wrap;gap:8px;}
.wv-profile-badge{background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.20);border-radius:999px;color:#d8e3f2;display:inline-flex;font-size:12px;font-weight:900;gap:7px;padding:7px 11px;}
.wv-profile-badge i{color:#7cf9ff;}
.wv-profile-actions .btn{border:0;border-radius:999px;font-weight:900;padding:11px 18px;}
.wv-profile-edit{background:#ff0a46!important;box-shadow:0 14px 28px rgba(255,10,70,.25);color:#fff!important;}
.wv-profile-content{display:grid;gap:18px;grid-template-columns:minmax(0,1fr) 330px;margin-top:18px;}
.wv-profile-card{background:#fff;border:1px solid #dde7f3;border-radius:12px;box-shadow:0 12px 28px rgba(15,23,42,.08);overflow:hidden;}
.wv-profile-card-head{align-items:center;border-bottom:1px solid #edf2f7;display:flex;gap:11px;padding:18px 20px;}
.wv-profile-card-head i{align-items:center;background:#fff1f4;border-radius:8px;color:#ff0a46;display:flex;height:36px;justify-content:center;width:36px;}
.wv-profile-card-head h2{color:#07152a;font-size:19px;font-weight:900;margin:0;}
.wv-profile-card-body{padding:18px 20px;}
.wv-profile-grid{display:grid;gap:12px;grid-template-columns:repeat(2,minmax(0,1fr));}
.wv-profile-field{background:#fbfdff;border:1px solid #e3ebf5;border-radius:9px;padding:13px;}
.wv-profile-field label{color:#64748b;display:block;font-size:10px;font-weight:900;letter-spacing:.4px;margin:0 0 6px;text-transform:uppercase;}
.wv-profile-field strong,.wv-profile-field a,.wv-profile-field span{color:#07152a!important;display:block;font-size:13px;font-weight:900;line-height:1.45;text-decoration:none!important;word-break:break-word;}
.wv-profile-field a:hover{color:#ff0a46!important;}
.wv-profile-contact-list{display:grid;gap:10px;}
.wv-profile-contact{align-items:flex-start;background:#fbfdff;border:1px solid #e3ebf5;border-radius:9px;display:flex;gap:10px;padding:12px;}
.wv-profile-contact i{align-items:center;background:#07152a;border-radius:8px;color:#7cf9ff;display:flex;flex:0 0 34px;height:34px;justify-content:center;width:34px;}
.wv-profile-contact label{color:#64748b;display:block;font-size:10px;font-weight:900;letter-spacing:.4px;margin:0 0 4px;text-transform:uppercase;}
.wv-profile-contact a,.wv-profile-contact span{color:#07152a!important;font-size:13px;font-weight:900;line-height:1.45;text-decoration:none!important;word-break:break-word;}
.wv-profile-note{color:#475569;font-size:13px;font-weight:700;line-height:1.65;margin:0;white-space:pre-line;}
.wv-profile-empty{color:#94a3b8!important;font-style:italic;font-weight:800!important;}
.wv-profile-social{display:flex;flex-wrap:wrap;gap:8px;}
.wv-profile-social span{background:#f8fafc;border:1px solid #e3ebf5;border-radius:999px;color:#334155;display:inline-flex;font-size:12px;font-weight:900;gap:7px;padding:8px 11px;}
.wv-profile-social i{color:#ff0a46;}
@media(max-width:991px){.wv-profile-hero-inner,.wv-profile-content{grid-template-columns:1fr}.wv-profile-actions{text-align:left}.wv-profile-grid{grid-template-columns:1fr}}
@media(max-width:575px){.wv-profile-page{margin:0;padding:12px}.wv-profile-hero-inner{padding:20px}.wv-profile-title h1{font-size:24px}.wv-profile-avatar{height:96px;width:96px}}
</style>
<div class="wv-profile-page">
    <?php echo $this->Session->flash();?>
    <section class="wv-profile-hero">
        <div class="wv-profile-hero-inner">
            <div class="wv-profile-avatar">
                <?php if(!empty($std_img)){ ?>
                    <?php echo $this->Html->image($std_img, array('alt'=>$userName));?>
                <?php } else { ?>
                    <div class="wv-profile-avatar-fallback"><?php echo h($userInitial);?></div>
                <?php } ?>
            </div>
            <div class="wv-profile-title">
                <h1><?php echo h($userName);?></h1>
                <div class="wv-profile-sub">
                    <span class="wv-profile-badge"><i class="fa fa-shield"></i> <?php echo h($userLevel);?></span>
                    <span class="wv-profile-badge"><i class="fa fa-user"></i> <?php echo h($username);?></span>
                    <span class="wv-profile-badge"><i class="fa fa-map-marker"></i> <?php echo h($locationName);?></span>
                </div>
            </div>
            <div class="wv-profile-actions">
                <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit Profile'),array('controller'=>'Users','action'=>'editprofile'),array('escape'=>false,'class'=>'btn wv-profile-edit'));?>
            </div>
        </div>
    </section>

    <div class="wv-profile-content">
        <main class="wv-profile-card">
            <div class="wv-profile-card-head">
                <i class="fa fa-id-card"></i>
                <h2><?php echo __('Profile Information');?></h2>
            </div>
            <div class="wv-profile-card-body">
                <div class="wv-profile-grid">
                    <div class="wv-profile-field">
                        <label><?php echo __('Name');?></label>
                        <strong><?php echo h($userName);?></strong>
                    </div>
                    <div class="wv-profile-field">
                        <label><?php echo __('Username');?></label>
                        <strong><?php echo h($username);?></strong>
                    </div>
                    <div class="wv-profile-field">
                        <label><?php echo __('User Level');?></label>
                        <strong><?php echo h($userLevel);?></strong>
                    </div>
                    <div class="wv-profile-field">
                        <label><?php echo __('Location');?></label>
                        <strong><?php echo h($locationName);?></strong>
                    </div>
                    <div class="wv-profile-field">
                        <label><?php echo __('Address');?></label>
                        <span class="<?php echo $address==='' ? 'wv-profile-empty' : '';?>"><?php echo $address!=='' ? h($address) : __('Not Specified');?></span>
                    </div>
                    <div class="wv-profile-field">
                        <label><?php echo __('Description');?></label>
                        <span class="<?php echo $description==='' ? 'wv-profile-empty' : '';?>"><?php echo $description!=='' ? h($description) : __('Not Specified');?></span>
                    </div>
                </div>
            </div>
        </main>

        <aside class="wv-profile-card">
            <div class="wv-profile-card-head">
                <i class="fa fa-phone"></i>
                <h2><?php echo __('Contact Details');?></h2>
            </div>
            <div class="wv-profile-card-body">
                <div class="wv-profile-contact-list">
                    <div class="wv-profile-contact">
                        <i class="fa fa-envelope"></i>
                        <div>
                            <label><?php echo __('Email');?></label>
                            <?php if($email!==''){ ?><a href="mailto:<?php echo h($email);?>"><?php echo h($email);?></a><?php } else { ?><span class="wv-profile-empty"><?php echo __('Not Specified');?></span><?php } ?>
                        </div>
                    </div>
                    <div class="wv-profile-contact">
                        <i class="fa fa-phone"></i>
                        <div>
                            <label><?php echo __('Phone');?></label>
                            <?php if($phone!==''){ ?><a href="tel:<?php echo h($phoneClean);?>"><?php echo h($phone);?></a><?php } else { ?><span class="wv-profile-empty"><?php echo __('Not Specified');?></span><?php } ?>
                        </div>
                    </div>
                    <div class="wv-profile-contact">
                        <i class="fa fa-mobile"></i>
                        <div>
                            <label><?php echo __('Mobile');?></label>
                            <?php if($mobile!==''){ ?><a href="tel:<?php echo h($mobileClean);?>"><?php echo h($mobile);?></a><?php } else { ?><span class="wv-profile-empty"><?php echo __('Not Specified');?></span><?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <?php if($skype!=='' || $twitter!=='' || $facebook!=='' || $linkedin!==''){ ?>
    <div class="wv-profile-card" style="margin-top:18px;">
        <div class="wv-profile-card-head">
            <i class="fa fa-share-alt"></i>
            <h2><?php echo __('Social Accounts');?></h2>
        </div>
        <div class="wv-profile-card-body">
            <div class="wv-profile-social">
                <?php if($skype!==''){ ?><span><i class="fa fa-skype"></i><?php echo h($skype);?></span><?php } ?>
                <?php if($twitter!==''){ ?><span><i class="fa fa-twitter"></i><?php echo h($twitter);?></span><?php } ?>
                <?php if($facebook!==''){ ?><span><i class="fa fa-facebook"></i><?php echo h($facebook);?></span><?php } ?>
                <?php if($linkedin!==''){ ?><span><i class="fa fa-linkedin"></i><?php echo h($linkedin);?></span><?php } ?>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
