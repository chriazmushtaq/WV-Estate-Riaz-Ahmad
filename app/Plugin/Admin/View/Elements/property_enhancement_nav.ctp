<?php
$navPropertyId = 0;
if(!empty($property['Property']['id']))
{
    $navPropertyId = (int)$property['Property']['id'];
}
?>
<style>
.wv-enhance-wrap{background:#f8fafc;border:1px solid #dbe5ef;border-radius:10px;margin-bottom:16px;padding:14px;}
.wv-enhance-head{align-items:center;display:flex;gap:12px;justify-content:space-between;margin-bottom:12px;}
.wv-enhance-head h2{color:#07152a;font-size:20px;font-weight:900;margin:0;}
.wv-enhance-nav{display:flex;flex-wrap:wrap;gap:8px;}
.wv-enhance-nav a{background:#082f49;border-radius:7px;color:#fff!important;font-size:12px;font-weight:800;padding:8px 11px;text-decoration:none!important;}
.wv-enhance-nav a:hover{background:#ff0a46;color:#fff!important;}
.wv-enhance-nav .light{background:#fff;border:1px solid #cbd5e1;color:#082f49!important;}
.wv-enhance-nav .light:hover{background:#082f49;color:#fff!important;}
.wv-enhance-card{background:#fff;border:1px solid #dbe5ef;border-radius:10px;box-shadow:0 10px 24px rgba(15,23,42,.06);margin-bottom:16px;padding:16px;}
.wv-score{background:#e2e8f0;border-radius:999px;height:12px;overflow:hidden;width:160px;}
.wv-score span{background:#16a34a;display:block;height:100%;}
.wv-score-text{color:#07152a;font-weight:900;}
.wv-enhance-table th{background:#082f49!important;color:#fff!important;}
.wv-muted{color:#64748b;font-weight:700;}
@media(max-width:767px){.wv-enhance-head{align-items:flex-start;display:block}.wv-enhance-nav{margin-top:10px}.wv-score{width:100%;}}
</style>
<div class="wv-enhance-wrap">
    <div class="wv-enhance-head">
        <h2><?php echo $navPropertyId ? h($property['Property']['name']) : __('Property Enhancements');?></h2>
        <div class="wv-enhance-nav">
            <?php echo $this->Html->link('<i class="fa fa-list"></i> '.__('Properties'), array('controller'=>'Properties','action'=>'index'), array('escape'=>false,'class'=>'light'));?>
            <?php echo $this->Html->link('<i class="fa fa-dashboard"></i> '.__('Enhancement Overview'), array('controller'=>'Propertyenhancements','action'=>'index'), array('escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-map-marker"></i> '.__('Missing Map'), array('controller'=>'Propertyenhancements','action'=>'missinglocations'), array('escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-bell"></i> '.__('Saved Searches'), array('controller'=>'Propertyenhancements','action'=>'savedsearches'), array('escape'=>false));?>
            <?php if($navPropertyId){ ?>
                <?php echo $this->Html->link('<i class="fa fa-check-circle"></i> '.__('Quality'), array('controller'=>'Propertyenhancements','action'=>'quality',$navPropertyId), array('escape'=>false));?>
                <?php echo $this->Html->link('<i class="fa fa-search"></i> '.__('SEO'), array('controller'=>'Propertyenhancements','action'=>'seo',$navPropertyId), array('escape'=>false));?>
                <?php echo $this->Html->link('<i class="fa fa-image"></i> '.__('Media'), array('controller'=>'Propertyenhancements','action'=>'media',$navPropertyId), array('escape'=>false));?>
                <?php echo $this->Html->link('<i class="fa fa-line-chart"></i> '.__('Leads'), array('controller'=>'Propertyenhancements','action'=>'leads',$navPropertyId), array('escape'=>false));?>
                <?php echo $this->Html->link('<i class="fa fa-money"></i> '.__('Price History'), array('controller'=>'Propertyenhancements','action'=>'pricehistory',$navPropertyId), array('escape'=>false));?>
                <?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit Property'), array('controller'=>'Properties','action'=>'edit',$navPropertyId), array('escape'=>false,'class'=>'light'));?>
            <?php } ?>
        </div>
    </div>
    <?php if($navPropertyId){ ?>
        <div class="wv-muted"><?php echo __('Office ID');?>: <?php echo !empty($property['Property']['uniq_id']) ? h($property['Property']['uniq_id']) : '-';?></div>
    <?php } ?>
</div>
