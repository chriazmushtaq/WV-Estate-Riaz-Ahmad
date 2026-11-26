<?php
$page_params = $this->Paginator->params();
$limit = $page_params['limit'];
$page = $page_params['page'];
if(isset($IsAction))
$caction=$IsAction;
else
$caction="index";
$options = array(5=>'5',10=>'10',20=>'20',25=>'25',30=>'30',50=>'50',100=>'100',200=>'200',500=>'500');
$limit = (int)$limit;
if($limit>0 && !isset($options[$limit]))
{
    $options[$limit] = (string)$limit;
    ksort($options);
}
$limitSelectAttrs = array('value'=>$limit,'default'=>$limit,'empty'=>FALSE,'onchange'=>'this.form.submit();','class'=>'form-control input-sm');
if(!empty($UseNamedLimit))
{
    $limitPass = array();
    foreach((array)$this->params['pass'] as $passValue)
    {
        if(!preg_match('/^limit[:=]\d+$/',(string)$passValue))
        {
            $limitPass[] = $passValue;
        }
    }
    $limitNamed = isset($this->params['named']) ? (array)$this->params['named'] : array();
    unset($limitNamed['limit'], $limitNamed['page']);
    if(!empty($NamedLimitBaseUrl))
    {
        $limitBaseUrl = rtrim($this->Html->url($NamedLimitBaseUrl), '/');
    }
    else
    {
        $limitBaseUrl = rtrim($this->Html->url(array_merge(array('action'=>$caction), $limitPass, $limitNamed)), '/');
    }
    $limitSelectAttrs['class'] = trim($limitSelectAttrs['class'].' js-pagination-limit');
    $limitSelectAttrs['data-limit-base-url'] = $limitBaseUrl;
    if(!empty($UseQueryLimit))
    {
        $limitSelectAttrs['onchange'] = 'var base=this.getAttribute("data-limit-base-url")||"";if(base){var joiner=base.indexOf("?")===-1?"?":"&";window.location.href=base+joiner+"limit="+encodeURIComponent(this.value);return false;}this.form.submit();';
    }
    else
    {
        $limitSelectAttrs['onchange'] = 'var base=this.getAttribute("data-limit-base-url")||"";if(base){window.location.href=base.replace(/\/$/,"")+"/limit:"+encodeURIComponent(this.value);return false;}this.form.submit();';
    }
}
?>

<div class="wv-pagination-shell">
    <div class="wv-pagination-left">
        <?php if(!isset($IsDropdown)){?>
        <div class="wv-pagination-limit-wrap">
            <?php echo $this->Form->create('', array('url' => array_merge(array('action' =>$caction,), $this->params['pass']),'class'=>'form-inline','style'=>'margin:0; display:inline-flex; align-items:center; gap:6px;'));?>
            <span class="text-muted" style="font-size:12.5px; font-weight:500;"><?php echo __('Show');?></span>
            <div style="width: 75px;">
                <?php echo $this->Form->select('limit', $options, $limitSelectAttrs);?>
            </div>
            <span class="text-muted" style="font-size:12.5px; font-weight:500;"><?php echo __('entries');?></span>
            <?php if(isset($this->params['named'])){foreach($this->params['named'] as $key=>$value):if($key!="limit"){echo$this->Form->input($key,array('type'=>'hidden','value'=>$value));}endforeach;unset($key,$value);}
            echo $this->Form->end();?>
        </div>
        <?php }?>
        <div class="wv-pagination-counter text-muted">
            <i class="fa fa-list-ol" style="margin-right: 4px; color: #94a3b8;"></i>
            <?php echo $this->Paginator->counter(__d('default','Showing {:start} to {:end} of {:count} records'));?>
        </div>
    </div>

    <div class="wv-pagination-right">
        <?php if(!isset($IsSearch)){?>
        <div class="wv-pagination-search">
            <?php echo $this->Form->create('', array('url' => array_merge(array('action' => $caction,), $this->params['pass']),'style'=>'margin:0;'));?>
            <div class="input-group" style="width: 220px;">
                <?php echo $this->Form->input('keyword', array('div' => false,'label'=>false,'class'=>'form-control input-sm','placeholder'=>__('Search table...'),'empty' => true,'style'=>'height:34px; border-radius:8px 0 0 8px;'));?>
                <span class="input-group-btn">
                    <button class="btn btn-primary btn-sm" type="submit" style="height:34px; padding:0 12px; border-radius:0 8px 8px 0;"><i class="fa fa-search"></i></button>
                </span>
            </div>
            <?php echo $this->Form->end();?>
        </div>
        <?php }?>

        <div class="wv-pagination-pages">
            <ul class="pagination pagination-sm" style="margin:0;">
                <?php echo $this->Paginator->prev('&larr; '.__('Prev'),array('tag'=>'li','escape'=>false),'<a>&larr; '.__('Prev').'</a>',array('class'=>'disabled','tag'=>'li','escape'=>false));
                echo $this->Paginator->numbers(array('tag'=>'li','separator'=>null,'currentClass'=>'active','currentTag'=>'a','modulus'=>'4','first' => 2, 'last' => 2,'ellipsis'=>'<li><a>...</a></li>'));
                echo $this->Paginator->next(__('Next').' &rarr;',array('tag'=>'li','escape'=>false),'<a>'.__('Next').' &rarr;</a>',array('class'=>'disabled','tag'=>'li','escape'=>false));?>
            </ul>
        </div>
    </div>
</div>

<style type="text/css">
.wv-pagination-shell {
    align-items: center;
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 14px;
    padding: 12px 18px;
    width: 100%;
}
.wv-pagination-left {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}
.wv-pagination-counter {
    font-size: 13px;
    font-weight: 500;
}
.wv-pagination-right {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}
@media (max-width: 767px) {
    .wv-pagination-shell {
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
        gap: 14px !important;
        padding: 12px 10px !important;
    }
    .wv-pagination-left {
        flex-direction: column !important;
        align-items: center !important;
        width: 100% !important;
        gap: 10px !important;
    }
    .wv-pagination-right {
        flex-direction: column !important;
        align-items: center !important;
        width: 100% !important;
        gap: 12px !important;
    }
    .wv-pagination-search {
        width: 100% !important;
    }
    .wv-pagination-search .input-group {
        width: 100% !important;
    }
    .wv-pagination-pages ul.pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
    }
}
</style>
