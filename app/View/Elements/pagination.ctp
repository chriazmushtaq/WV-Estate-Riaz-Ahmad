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
$limitSelectAttrs = array('value'=>$limit,'default'=>$limit,'empty'=>FALSE,'onchange'=>'this.form.submit();','class'=>'input-sm');
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
<div class="col-md-2 mrg-1">
<?php if(!isset($IsDropdown)){?>
    <?php echo $this->Form->create('', array('url' => array_merge(array('action' =>$caction,), $this->params['pass'])));?>
    <label><small><?php echo __('Show');?>&nbsp;</small>
    <?php echo $this->Form->select('limit', $options, $limitSelectAttrs);?>
    <small>&nbsp;<?php echo __('entries');?></small></label>
    <?php if(isset($this->params['named'])){foreach($this->params['named'] as $key=>$value):if($key!="limit"){echo$this->Form->input($key,array('type'=>'hidden','value'=>$value));}endforeach;unset($key,$value);}
    echo $this->Form->end();?>
<?php }?>
</div>
<div class="col-md-10">
    <div class="row">
        <div class="col-md-6 text-right">
            <ul class="pagination pagination-sm">
                <?php echo $this->Paginator->prev('&larr; '.__('Prev'),array('tag'=>'li','escape'=>false),'<a>&larr;'.__('Prev').'</a>',array('class'=>'disabled','tag'=>'li','escape'=>false));
                echo $this->Paginator->numbers(array('tag'=>'li','separator'=>null,'currentClass'=>'active','currentTag'=>'a','modulus'=>'4','first' => 2, 'last' => 2,'ellipsis'=>'<li><a>...</a></li>'));
                echo $this->Paginator->next(__('Next').' &rarr;',array('tag'=>'li','escape'=>false),'<a>&rarr; '.__('Next').'</a>',array('class'=>'disabled','tag'=>'li','escape'=>false));?>
            </ul>
            </div>
            <div class="col-md-3 text-left pad">
                <small><?php echo $this->Paginator->counter(__d('default','Showing {:start} to {:end} of {:count} entries'));?></small>
            </div>
            <?php if(!isset($IsSearch)){?>
            <div class="col-md-3">
                <?php echo $this->Form->create('', array('url' => array_merge(array('action' => $caction,), $this->params['pass'])));?>
                <div class="input-group">
                <?php echo $this->Form->input('keyword', array('div' => false,'label'=>false,'class'=>'form-control','placeholder'=>__('Search'),'empty' => true));?>
                <span class="input-group-btn "><button class="btn btn-success" type="submit" style="height:30px;"><span class="fa fa-search"></span></button></span>
                </div>
                <?php echo $this->Form->end();?>
            </div>
            <?php }?>
    </div>
</div>
