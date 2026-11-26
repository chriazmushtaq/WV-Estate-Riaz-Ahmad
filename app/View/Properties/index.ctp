<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array(
    'update' => '#resultDiv',
    'evalScripts' => true,
));
$photoUrl=$this->Html->url(array('controller'=>'img'));
$formatListPrice=function($price) use ($currency){
    $price=(float)$price;
    if($price>=10000000)
    {
        return 'PKR '.number_format($price/10000000,2).' Crore';
    }
    if($price>=100000)
    {
        return 'PKR '.number_format($price/100000,2).' Lac';
    }
    return $currency.' '.$this->Number->format($price);
};
$activeSearchLabels=array();
$namedParams=!empty($this->params['named']) && is_array($this->params['named']) ? $this->params['named'] : array();
if(!empty($namedParams['keyword']))$activeSearchLabels[]=__('Keyword').': '.$namedParams['keyword'];
if(!empty($namedParams['type']) && !empty($type[$namedParams['type']]))$activeSearchLabels[]=__('Type').': '.$type[$namedParams['type']];
if(!empty($namedParams['contract']) && !empty($contract[$namedParams['contract']]))$activeSearchLabels[]=__('Contract').': '.$contract[$namedParams['contract']];
if(!empty($namedParams['location']) && !empty($location[$namedParams['location']]))$activeSearchLabels[]=__('Location').': '.$location[$namedParams['location']];
if(!empty($namedParams['start_price']) || !empty($namedParams['end_price']))
{
    $priceFrom=!empty($namedParams['start_price']) ? $namedParams['start_price'] : '0';
    $priceTo=!empty($namedParams['end_price']) ? $namedParams['end_price'] : __('Any');
    $activeSearchLabels[]=__('Price').': '.$priceFrom.' - '.$priceTo;
}
if(!empty($namedParams['bedroom']))$activeSearchLabels[]=__('Beds').': '.$namedParams['bedroom'];
if(!empty($namedParams['bathroom']))$activeSearchLabels[]=__('Baths').': '.$namedParams['bathroom'];
if(!empty($namedParams['uniq_id']))$activeSearchLabels[]=__('Property ID').': '.$namedParams['uniq_id'];
$hasActiveSearch=!empty($activeSearchLabels);
$publicContact=!empty($contact) ? $contact : '';
$publicContactClean=preg_replace('/[^0-9\+]/','',$publicContact);
$savedSearchCriteriaJson=json_encode($namedParams);
$frontUserForSearch=(array)$this->Session->read('frontUser');
$savedSearchEmail=!empty($frontUserForSearch['Client']['email']) ? $frontUserForSearch['Client']['email'] : '';
?>
<style>
.wv-list-page{background:linear-gradient(180deg,#f6f9fd 0%,#eef3f9 100%);padding-bottom:42px;touch-action:auto;overflow-x:auto;-webkit-overflow-scrolling:touch;width:100%;}
.wv-list-page .wv-wide-container{max-width:1420px;width:calc(100% - 40px);}
.wv-list-hero{background:linear-gradient(135deg,#07152a 0%,#102746 68%,#ff0a46 155%);color:#fff;margin-bottom:0;overflow:hidden;padding:34px 0 86px;position:relative;}
.wv-list-hero:after{background:rgba(255,255,255,.08);border-radius:999px;content:"";height:170px;position:absolute;right:-60px;top:-70px;width:360px;}
.wv-list-hero h1{color:#fff;font-size:32px;font-weight:900;line-height:1.15;margin:0 0 7px;}
.wv-list-hero p{color:#dbe7f5;font-size:13px;font-weight:700;line-height:1.6;margin:0;max-width:760px;}
.wv-list-shell{display:block;margin-top:-58px;position:relative;z-index:2;}
.wv-search-card{background:#fff;border:1px solid #dfe7f1;border-radius:10px;box-shadow:0 16px 34px rgba(15,23,42,.12);margin-bottom:18px;overflow:hidden;position:static;}
.wv-search-head{align-items:center;background:#07152a;color:#fff;display:flex;gap:12px;justify-content:space-between;padding:14px 16px;}
.wv-search-head h3{color:#fff;font-size:16px;font-weight:900;margin:0;}
.wv-search-head span{background:rgba(143,247,255,.12);border:1px solid rgba(143,247,255,.28);border-radius:999px;color:#8ff7ff;display:inline-flex;font-size:10px;font-weight:900;padding:5px 9px;text-transform:uppercase;}
.wv-search-body{padding:14px 16px 16px;}
.wv-search-body .form-horizontal{margin:0;}
.wv-filter-grid{display:grid;grid-template-columns:minmax(96px,1fr) minmax(96px,1fr) minmax(112px,1.15fr) minmax(120px,1.25fr) minmax(96px,.9fr) minmax(164px,1.7fr) minmax(124px,1.25fr) minmax(144px,1.45fr);gap:8px;align-items:end;}
.wv-filter-field{min-width:0;}
.wv-filter-field.is-wide,.wv-filter-field.is-actions{grid-column:auto;}
.wv-filter-pair,.wv-filter-actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px;}
.wv-search-body .form-control{border-color:#d8e0ea;border-radius:7px;box-shadow:none;font-size:12px;height:38px;margin:0;padding-left:9px;padding-right:9px;width:100%;}
.wv-search-body .btn{align-items:center;border-radius:7px;display:flex;font-size:12px;font-weight:900;height:38px;justify-content:center;margin:0;padding-left:8px;padding-right:8px;width:100%;}
.wv-search-body .btn-primary{background:#ff0a46;border-color:#ff0a46;}
.wv-search-body .btn-danger{background:#07152a;border-color:#07152a;}
.wv-active-filter-row{border-top:1px solid #eef2f7;display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;padding-top:12px;}
.wv-active-filter-row span{background:#f8fafc;border:1px solid #dfe7f1;border-radius:999px;color:#07152a;font-size:11px;font-weight:900;padding:7px 10px;}
.wv-save-search{background:#f8fafc;border-top:1px solid #eef2f7;display:grid;gap:8px;grid-template-columns:minmax(130px,1fr) minmax(180px,1.4fr) 150px;margin-top:12px;padding-top:12px;}
.wv-save-search input{border:1px solid #d8e0ea;border-radius:7px;font-size:12px;font-weight:700;height:38px;padding:0 9px;width:100%;}
.wv-save-search button{background:#07152a;border:0;border-radius:7px;color:#fff;font-size:12px;font-weight:900;height:38px;width:100%;}
.wv-results-card{background:#fff;border:1px solid #dfe7f1;border-radius:10px;box-shadow:0 12px 28px rgba(15,23,42,.06);padding:14px;margin-bottom:18px;}
.wv-results-toolbar{align-items:center;display:flex;justify-content:space-between;gap:12px;}
.wv-results-count{color:#07152a;font-size:13px;font-weight:900;}
.wv-sort-links{color:#64748b;font-size:12px;font-weight:800;}
.wv-sort-links a{background:#f8fafc;border:1px solid #dfe7f1;border-radius:999px;color:#07152a!important;display:inline-block;margin-left:6px;padding:6px 10px;text-decoration:none!important;}
.wv-sort-links a:hover{border-color:#ff0a46;color:#ff0a46!important;}
.wv-property-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;}
.wv-property-card{background:#fff;border:1px solid #f4a400;border-radius:10px;box-shadow:0 12px 26px rgba(15,23,42,.08);overflow:hidden;transition:box-shadow .2s ease,transform .2s ease;}
.wv-property-card:hover{box-shadow:0 18px 34px rgba(15,23,42,.14);transform:translateY(-2px);}
.wv-property-media{background:#dbe3ec;height:168px;position:relative;}
.wv-property-media a{display:block;height:100%;}
.wv-property-media-img{display:block;height:100%;object-fit:cover;width:100%;}
.wv-list-badge{border-radius:999px;color:#fff;font-size:10px;font-weight:900;left:10px;padding:6px 10px;position:absolute;top:10px;}
.wv-list-badge.gold{background:#f5a400;}
.wv-photo-count{background:#172033;border-radius:999px;color:#fff;font-size:10px;font-weight:800;padding:6px 10px;position:absolute;right:10px;top:10px;}
.wv-card-price{background:linear-gradient(135deg,#ff0a46,#b00035);border-radius:999px;bottom:12px;box-shadow:0 10px 22px rgba(176,0,53,.28);color:#fff;font-size:16px;font-weight:900;left:12px;line-height:1;max-width:calc(100% - 24px);overflow:hidden;padding:9px 14px;position:absolute;text-overflow:ellipsis;text-shadow:none;white-space:nowrap;}
.wv-property-body{padding:13px;}
.wv-property-name{color:#07152a!important;display:block;font-size:14px;font-weight:900;line-height:1.35;min-height:38px;text-decoration:none!important;}
.wv-property-name:hover{color:#ff0a46!important;}
.wv-location-tools{align-items:center;display:flex;gap:8px;margin-top:5px;min-width:0;}
.wv-property-location{color:#64748b;flex:1;font-size:11px;font-weight:700;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.wv-property-specs{border-bottom:1px solid #eef2f7;color:#64748b;display:flex;flex-wrap:wrap;font-size:11px;gap:10px;margin:12px 0 0;padding-bottom:12px;}
.wv-property-specs strong{color:#193255;font-weight:900;}
.wv-card-tools{display:inline-flex;flex:0 0 auto;gap:6px;}
.wv-card-tools > span,
.wv-card-tools > span > span{display:inline-flex;}
.wv-card-tools .nav-link{align-items:center;background:#f8fafc;border:1px solid #dfe7f1;border-radius:7px;color:#07152a!important;display:flex;height:30px;justify-content:center;padding:0;text-align:center;text-decoration:none!important;width:30px;}
.wv-card-tools .nav-link:hover{background:#ff0a46;border-color:#ff0a46;color:#fff!important;}
.wv-card-tools .nav-link i{font-size:14px;margin:0;}
.wv-card-tools .nav-link img{height:14px;margin:0;width:14px;}
.wv-card-tools .nav-link span{display:none!important;}
.wv-property-footer{align-items:center;display:flex;gap:9px;padding-top:11px;}
.wv-owner-icon{background:#ef1717;border-radius:50%;color:#fff;height:25px;line-height:25px;text-align:center;width:25px;}
.wv-owner-name{color:#07152a;font-size:11px;font-weight:900;line-height:1.1;max-width:115px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.wv-owner-label{color:#ff0a46;font-size:8px;font-weight:900;text-transform:uppercase;}
.wv-owner-commission{color:#059669;font-size:9px;font-weight:800;margin-top:2px;}
.wv-card-action{background:#fff1f4;border-radius:999px;color:#ff0a46!important;font-size:11px;font-weight:900;margin-left:auto;padding:7px 10px;text-decoration:none!important;white-space:nowrap;}
.wv-card-action:hover{background:#ff0a46;color:#fff!important;}
.wv-empty{background:#fff;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b;font-weight:800;padding:28px;text-align:center;}
.wv-no-results{background:#fff;border:1px solid #dfe7f1;border-radius:10px;box-shadow:0 18px 38px rgba(15,23,42,.08);overflow:hidden;}
.wv-no-results-main{background:linear-gradient(135deg,#07152a 0%,#102746 70%,#ff0a46 150%);color:#fff;padding:28px;}
.wv-no-results-kicker{color:#8ff7ff;font-size:11px;font-weight:900;letter-spacing:.3px;text-transform:uppercase;}
.wv-no-results-main h2{color:#fff;font-size:28px;font-weight:900;line-height:1.15;margin:8px 0;}
.wv-no-results-main p{color:#dbeafe;font-size:13px;font-weight:700;line-height:1.7;margin:0;max-width:760px;}
.wv-no-results-body{padding:22px 24px 24px;}
.wv-search-tags{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px;}
.wv-search-tags span{background:#f8fafc;border:1px solid #dfe7f1;border-radius:999px;color:#07152a;font-size:11px;font-weight:900;padding:8px 11px;}
.wv-no-results-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:20px;}
.wv-no-results-tip{background:#f8fafc;border:1px solid #e3ebf4;border-radius:8px;padding:14px;}
.wv-no-results-tip i{background:#ff0a46;border-radius:8px;color:#fff;display:inline-block;height:30px;line-height:30px;margin-bottom:10px;text-align:center;width:30px;}
.wv-no-results-tip strong{color:#07152a;display:block;font-size:13px;font-weight:900;margin-bottom:4px;}
.wv-no-results-tip span{color:#64748b;display:block;font-size:11px;font-weight:800;line-height:1.55;}
.wv-no-results-actions{display:flex;flex-wrap:wrap;gap:10px;}
.wv-no-results-actions a{border-radius:7px;font-size:12px;font-weight:900;padding:10px 14px;text-decoration:none!important;}
.wv-no-results-primary{background:#ff0a46;color:#fff!important;}
.wv-no-results-secondary{background:#07152a;color:#fff!important;}
.wv-no-results-light{background:#fff;border:1px solid #dfe7f1;color:#07152a!important;}
.wv-bottom-pagination{margin-top:18px;}
@media(max-width:1399px){.wv-property-grid{grid-template-columns:repeat(3,minmax(0,1fr));}.wv-filter-grid{grid-template-columns:repeat(4,minmax(0,1fr));}.wv-filter-field.is-wide,.wv-filter-field.is-actions{grid-column:auto;}}
@media(max-width:991px){.wv-property-grid{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-filter-field.is-wide,.wv-filter-field.is-actions{grid-column:auto;}}
@media(max-width:767px){.wv-list-page .wv-wide-container{width:calc(100% - 24px);}.wv-list-hero{padding:28px 0 74px}.wv-list-hero h1{font-size:24px;}.wv-search-card{margin-bottom:14px}.wv-search-head{align-items:flex-start;display:block;padding:11px 12px}.wv-search-head span{margin-top:7px}.wv-search-body{padding:10px 12px 12px}.wv-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:7px}.wv-filter-field.is-wide,.wv-filter-field.is-actions{grid-column:span 2}.wv-filter-pair,.wv-filter-actions{grid-template-columns:repeat(2,minmax(0,1fr));gap:7px}.wv-search-body .form-control,.wv-search-body .btn{font-size:11px;height:34px;padding-left:7px;padding-right:7px}.wv-active-filter-row{gap:6px;margin-top:9px;padding-top:9px}.wv-active-filter-row span{font-size:10px;padding:6px 8px}.wv-save-search{grid-template-columns:1fr}.wv-save-search input,.wv-save-search button{height:34px}.wv-results-toolbar{align-items:flex-start;display:block}.wv-sort-links{margin-top:10px}.wv-property-grid{grid-template-columns:1fr}.wv-no-results-main h2{font-size:22px}.wv-no-results-grid{grid-template-columns:1fr}.wv-no-results-body{padding:18px}.wv-no-results-actions a{display:block;text-align:center;width:100%;}}
@media(max-width:480px){.wv-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-search-body .btn i{display:none}.wv-search-body .btn{font-size:10px}}
</style>

<div id="resultDiv">
    <div class="page-wrapper wv-list-page">
        <div class="wv-list-hero">
            <div class="container wv-wide-container">
                <h1><?php echo __('Search Properties');?></h1>
                <p><?php echo __('Browse verified published listings from White Villas with fast filters and clean property details.');?></p>
            </div>
        </div>
        <div class="container wv-wide-container">
            <?php echo $this->Session->flash();?>
            <div class="wv-list-shell">
                <aside class="wv-search-card">
                    <div class="wv-search-head">
                        <h3><?php echo __('Quick Search');?></h3>
                        <span><?php echo __('Find matching listings');?></span>
                    </div>
                    <div class="wv-search-body">
                        <?php echo $this->Form->create(array('controller'=>'Properties','action'=>'index','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
                        <div class="wv-filter-grid">
                            <div class="wv-filter-field"><?php echo $this->Form->select('type',$type,array('empty'=>__('Type'),'label'=>false,'class'=>'form-control','div'=>false));?></div>
                            <div class="wv-filter-field"><?php echo $this->Form->select('contract',$contract,array('empty'=>__('Contract'),'label'=>false,'class'=>'form-control','div'=>false));?></div>
                            <div class="wv-filter-field"><?php echo $this->Form->select('location',$location,array('empty'=>__('Location'),'label'=>false,'class'=>'form-control','div'=>false));?></div>
                            <div class="wv-filter-field"><input type="text" name="data[keyword]" class="form-control" placeholder="<?php echo h(__('Keyword'));?>" value="<?php echo !empty($namedParams['keyword']) ? h($namedParams['keyword']) : '';?>"></div>
                            <div class="wv-filter-field"><input type="text" name="data[uniq_id]" class="form-control" placeholder="<?php echo h(__('Property ID'));?>" value="<?php echo !empty($namedParams['uniq_id']) ? h($namedParams['uniq_id']) : '';?>"></div>
                            <div class="wv-filter-field is-wide"><div class="wv-filter-pair"><input type="number" name="data[start_price]" class="form-control" placeholder="<?php echo h(__('Price From'));?>" value="<?php echo !empty($namedParams['start_price']) ? h($namedParams['start_price']) : '';?>"><input type="number" name="data[end_price]" class="form-control" placeholder="<?php echo h(__('Price To'));?>" value="<?php echo !empty($namedParams['end_price']) ? h($namedParams['end_price']) : '';?>"></div></div>
                            <div class="wv-filter-field"><div class="wv-filter-pair"><input type="text" name="data[bedroom]" class="form-control" placeholder="<?php echo h(__('Beds'));?>" value="<?php echo !empty($namedParams['bedroom']) ? h($namedParams['bedroom']) : '';?>"><input type="text" name="data[bathroom]" class="form-control" placeholder="<?php echo h(__('Baths'));?>" value="<?php echo !empty($namedParams['bathroom']) ? h($namedParams['bathroom']) : '';?>"></div></div>
                            <div class="wv-filter-field is-actions"><div class="wv-filter-actions"><?php echo $this->Form->button('<i class="fa fa-search"></i>&nbsp;'.__('Search'),array('class'=>'btn btn-primary btn-block'));?><?php echo $this->Html->link('<i class="fa fa-close"></i>&nbsp;'.__('Reset'),array('controller'=>'Properties'),array('class'=>'btn btn-danger btn-block','escape'=>false));?></div></div>
                        </div>
                        <?php echo $this->Form->end();?>
                        <?php if($hasActiveSearch){ ?>
                        <div class="wv-active-filter-row">
                            <?php foreach($activeSearchLabels as $activeSearchLabel){ ?>
                            <span><?php echo h($activeSearchLabel);?></span>
                            <?php } ?>
                        </div>
                        <?php } ?>
                        <?php echo $this->Form->create('PropertySavedSearch', array('controller'=>'Properties','action'=>'savesearch','class'=>'wv-save-search'));?>
                            <input type="text" name="data[PropertySavedSearch][name]" value="<?php echo h(__('My Property Search'));?>" placeholder="<?php echo h(__('Search Name'));?>">
                            <input type="email" name="data[PropertySavedSearch][email]" value="<?php echo h($savedSearchEmail);?>" placeholder="<?php echo h(__('Email for property alerts'));?>" required>
                            <input type="hidden" name="data[PropertySavedSearch][criteria_json]" value="<?php echo h($savedSearchCriteriaJson);?>">
                            <button type="submit"><i class="fa fa-bell"></i> <?php echo __('Save Search');?></button>
                        <?php echo $this->Form->end();?>
                    </div>
                </aside>
                <main>
                    <div class="wv-results-card">
                        <div class="wv-results-toolbar">
                            <div class="wv-results-count">
                                <?php echo !empty($property) ? $this->Paginator->counter(array('format'=>__('Showing {:start}-{:end} of {:count} properties'))) : ($hasActiveSearch ? __('No matching properties found') : __('No published properties available'));?>
                            </div>
                            <div class="wv-sort-links">
                                <strong><?php echo __('Sort By');?></strong>
                                <?php echo $this->Paginator->sort('price', __('Price'), array('direction'=>'asc'));?>
                                <?php echo $this->Paginator->sort('created', __('Date Added'), array('direction'=>'desc'));?>
                            </div>
                        </div>
                    </div>
                    <?php if(!empty($property)){ ?>
                    <div class="wv-property-grid">
                        <?php foreach($property as $post){
                            $propertyId=!empty($post['Property']['id']) ? (int)$post['Property']['id'] : 0;
                            $propertyName=!empty($post['Property']['name']) ? $post['Property']['name'] : '-';
                            $propertyPrice=isset($post['Property']['price']) ? $formatListPrice($post['Property']['price']) : '-';
                            $propertyPhoto=$photoUrl.'/nia.png';
                            $photoCount=!empty($post['PropertiesPhoto']) && is_array($post['PropertiesPhoto']) ? count($post['PropertiesPhoto']) : 0;
                            if(!empty($post['PropertiesPhoto']) && is_array($post['PropertiesPhoto']))
                            {
                                foreach($post['PropertiesPhoto'] as $photoRow)
                                {
                                    if(empty($photoRow['photo']))
                                    {
                                        continue;
                                    }
                                    $photoDir=!empty($photoRow['dir']) ? $photoRow['dir'] : (!empty($photoRow['dirt']) ? $photoRow['dirt'] : '');
                                    $candidatePhoto=$photoUrl.'/'.trim($photoDir).'_thumb/'.$photoRow['photo'];
                                    if(!empty($photoRow['home']) && $photoRow['home']==='Yes')
                                    {
                                        $propertyPhoto=$candidatePhoto;
                                        break;
                                    }
                                    $propertyPhoto=$candidatePhoto;
                                }
                            }
                            $ownerName=!empty($post['User']['name']) ? $post['User']['name'] : __('Owner');
                            $locationText=!empty($post['Location']['name']) ? $post['Location']['name'] : (!empty($post['Property']['address']) ? $post['Property']['address'] : __('Location not specified'));
                            $beds=!empty($post['Property']['bedroom']) ? $post['Property']['bedroom'] : '-';
                            $baths=!empty($post['Property']['bathroom']) ? $post['Property']['bathroom'] : '-';
                            $area=!empty($post['Property']['area']) ? $post['Property']['area'] : '-';
                            if(!empty($post['Unit']['name']) && $area !== '-')
                            {
                                $area.=' '.$post['Unit']['name'];
                            }
                            $contractName=!empty($post['Contract']['name']) ? $post['Contract']['name'] : __('Sale');
                            $propertyUrl=$this->Function->propertyViewUrl($propertyName,$propertyId);
                        ?>
                        <article class="wv-property-card">
                            <div class="wv-property-media">
                                <a href="<?php echo h($propertyUrl);?>">
                                    <img class="wv-property-media-img" src="<?php echo h($propertyPhoto);?>" alt="<?php echo h($propertyName);?>" loading="lazy" decoding="async">
                                </a>
                                <span class="wv-list-badge gold"><span class="fa fa-star"></span> <?php echo h($contractName);?></span>
                                <span class="wv-photo-count"><span class="fa fa-camera"></span> <?php echo (int)$photoCount;?> <?php echo __('photos');?></span>
                                <div class="wv-card-price"><?php echo h($propertyPrice);?></div>
                            </div>
                            <div class="wv-property-body">
                                <?php echo $this->Html->link(h($propertyName),$propertyUrl,array('escape'=>false,'class'=>'wv-property-name'));?>
                                <div class="wv-location-tools">
                                    <div class="wv-property-location"><span class="fa fa-map-marker"></span> <?php echo h($locationText);?></div>
                                    <div class="wv-card-tools">
                                        <span><?php echo $this->Function->favouriteProperty($propertyId);?></span>
                                        <span><?php echo $this->Function->compareProperty($propertyId);?></span>
                                    </div>
                                </div>
                                <div class="wv-property-specs">
                                    <span><span class="fa fa-bed"></span> <strong><?php echo h($beds);?></strong> <?php echo __('Beds');?></span>
                                    <span><span class="fa fa-bath"></span> <strong><?php echo h($baths);?></strong> <?php echo __('Baths');?></span>
                                    <span><span class="fa fa-expand"></span> <strong><?php echo h($area);?></strong></span>
                                </div>
                                <div class="wv-property-footer">
                                    <div class="wv-owner-icon"><span class="fa fa-user"></span></div>
                                    <div>
                                        <div class="wv-owner-name"><?php echo h($ownerName);?></div>
                                        <div class="wv-owner-label"><?php echo __('Owner');?></div>
                                        <div class="wv-owner-commission"><span class="fa fa-check-circle"></span> <?php echo __('Direct Owner - 0% Commission');?></div>
                                    </div>
                                    <?php echo $this->Html->link(__('View Details').' <span class="fa fa-angle-right"></span>',$propertyUrl,array('escape'=>false,'class'=>'wv-card-action'));?>
                                </div>
                            </div>
                        </article>
                        <?php } ?>
                    </div>
                    <div class="wv-results-card wv-bottom-pagination">
                        <?php echo $this->element('pagination',array('IsSearch'=>'No','IsDropdown'=>'No'));?>
                    </div>
                    <?php } else { ?>
                    <section class="wv-no-results">
                        <div class="wv-no-results-main">
                            <div class="wv-no-results-kicker"><?php echo h($siteName);?> <?php echo __('Property Search');?></div>
                            <h2><?php echo $hasActiveSearch ? __('No exact property match found') : __('No published properties are available right now');?></h2>
                            <p><?php echo __('Our available inventory changes frequently. Share your requirement with White Villas and our team can help match you with the closest verified property, file, apartment, or project option.');?></p>
                        </div>
                        <div class="wv-no-results-body">
                            <?php if($hasActiveSearch){ ?>
                            <div class="wv-search-tags">
                                <?php foreach($activeSearchLabels as $activeSearchLabel){ ?>
                                <span><?php echo h($activeSearchLabel);?></span>
                                <?php } ?>
                            </div>
                            <?php } ?>
                            <div class="wv-no-results-grid">
                                <div class="wv-no-results-tip">
                                    <i class="fa fa-sliders"></i>
                                    <strong><?php echo __('Try wider filters');?></strong>
                                    <span><?php echo __('Remove one filter, increase your price range, or search by area/project name only.');?></span>
                                </div>
                                <div class="wv-no-results-tip">
                                    <i class="fa fa-check-circle"></i>
                                    <strong><?php echo __('Ask for verified options');?></strong>
                                    <span><?php echo __('Our office can confirm availability, latest price, owner details, and booking status.');?></span>
                                </div>
                                <div class="wv-no-results-tip">
                                    <i class="fa fa-building"></i>
                                    <strong><?php echo __('Review latest plans');?></strong>
                                    <span><?php echo __('Compare current project payment plans while we prepare matching property suggestions.');?></span>
                                </div>
                            </div>
                            <div class="wv-no-results-actions">
                                <?php echo $this->Html->link('<i class="fa fa-list"></i> '.__('View All Properties'),array('controller'=>'Properties','action'=>'index'),array('escape'=>false,'class'=>'wv-no-results-primary'));?>
                                <?php echo $this->Html->link('<i class="fa fa-calendar"></i> '.__('Payment Plans'),array('controller'=>'paymentplans','action'=>'index'),array('escape'=>false,'class'=>'wv-no-results-secondary'));?>
                                <?php echo $this->Html->link('<i class="fa fa-envelope"></i> '.__('Contact White Villas'),array('controller'=>'Contacts','action'=>'index'),array('escape'=>false,'class'=>'wv-no-results-light'));?>
                                <?php if($publicContactClean !== ''){ ?><a class="wv-no-results-light" href="tel:<?php echo h($publicContactClean);?>"><i class="fa fa-phone"></i> <?php echo h($publicContact);?></a><?php } ?>
                            </div>
                        </div>
                    </section>
                    <?php } ?>
                </main>
            </div>
        </div>
    </div>
</div>
