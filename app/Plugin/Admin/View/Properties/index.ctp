<?php
 $this->Js->JqueryEngine->jQueryObject = 'jQuery';
// Paginator options
$this->Paginator->options(array(
  'update' => '#resultDiv',
  'evalScripts' => true,
));
$currency = isset($currency) ? $currency : '';
$propertyLimitUrl = '/admin/Properties/index';
if(isset($this->params['named']) && is_array($this->params['named']))
{
    foreach($this->params['named'] as $propertyLimitKey=>$propertyLimitValue)
    {
        if(in_array($propertyLimitKey,array('limit','page'),true))
        {
            continue;
        }
        $propertyLimitUrl .= '/'.rawurlencode($propertyLimitKey).':'.rawurlencode($propertyLimitValue);
    }
    unset($propertyLimitKey,$propertyLimitValue);
}
?>
<style>
.properties-status-toggle {
    display: inline-flex;
    gap: 6px;
    align-items: center;
    flex-wrap: nowrap;
}
.property-flag-btn {
    min-width: 66px;
    border: 0;
    border-radius: 16px;
    padding: 5px 10px;
    color: #ffffff !important;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    box-shadow: 0 5px 12px rgba(15, 23, 42, 0.12);
}
.property-flag-yes,
.property-flag-yes:hover,
.property-flag-yes:focus {
    background: #15803d !important;
    border-color: #15803d !important;
}
.property-flag-no,
.property-flag-no:hover,
.property-flag-no:focus {
    background: #dc2626 !important;
    border-color: #dc2626 !important;
}
.property-action-button,
.property-action-button:hover,
.property-action-button:focus {
    background: #082f49 !important;
    border-color: #082f49 !important;
    color: #ffffff !important;
    font-weight: 700;
}
.property-action-header {
    background: #082f49 !important;
    color: #ffffff !important;
}
.property-action-cell > .btn,
.property-action-cell .dropdown-toggle {
    background: #082f49 !important;
    border-color: #082f49 !important;
    color: #ffffff !important;
    font-weight: 700;
}
.property-edit-link {
    font-weight: 700;
}
.property-price-cell {
    color: #0f3b63;
    font-weight: 700;
    white-space: nowrap;
}
.property-quality-wrap {
    min-width: 120px;
}
.property-quality-bar {
    background: #e2e8f0;
    border-radius: 999px;
    height: 9px;
    overflow: hidden;
}
.property-quality-bar span {
    background: #16a34a;
    display: block;
    height: 100%;
}
.property-quality-text {
    color: #07152a;
    font-size: 11px;
    font-weight: 900;
    margin-top: 4px;
}
/* Executive Topbar Toolbar & Inline Bulk Action */
.panel-heading.wv-properties-topbar {
    background: #ffffff !important;
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 12px 15px !important;
}

.wv-properties-actions-group {
    align-items: center !important;
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 8px 10px !important;
    width: 100% !important;
}

.wv-properties-actions-group .btn-group {
    align-items: center !important;
    display: inline-flex !important;
    flex-wrap: wrap !important;
    gap: 6px !important;
}

.wv-properties-actions-group .btn-group .btn {
    border-radius: 7px !important;
    float: none !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    height: 35px !important;
    line-height: 1.3 !important;
    margin: 0 !important;
    padding: 7px 12px !important;
    transition: all 0.15s ease !important;
}

.wv-bulk-action-inline {
    align-items: center !important;
    border-left: 1px solid #cbd5e1 !important;
    display: inline-flex !important;
    gap: 6px !important;
    margin-left: 6px !important;
    padding-left: 12px !important;
}

.wv-bulk-action-select {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 7px !important;
    box-shadow: none !important;
    color: #1e293b !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    height: 35px !important;
    min-height: 35px !important;
    min-width: 165px !important;
    padding: 6px 10px !important;
    transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    width: 180px !important;
}

.wv-bulk-action-select:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18) !important;
    outline: none !important;
}

.wv-bulk-action-apply-btn {
    align-items: center !important;
    background: #2563eb !important;
    border: 1px solid #1d4ed8 !important;
    border-radius: 7px !important;
    box-shadow: 0 2px 4px rgba(37, 99, 235, 0.22) !important;
    color: #ffffff !important;
    display: inline-flex !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    height: 35px !important;
    justify-content: center !important;
    min-height: 35px !important;
    padding: 0 14px !important;
    transition: background 0.15s ease, transform 0.15s ease !important;
    white-space: nowrap !important;
}

.wv-bulk-action-apply-btn:hover,
.wv-bulk-action-apply-btn:focus {
    background: #1d4ed8 !important;
    border-color: #1e40af !important;
    color: #ffffff !important;
    transform: translateY(-1px);
}

@media (max-width: 767px) {
    .wv-bulk-action-inline {
        border-left: 0 !important;
        margin-left: 0 !important;
        margin-top: 4px !important;
        padding-left: 0 !important;
        width: 100% !important;
    }
    .wv-bulk-action-select {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        width: 100% !important;
    }
}
/* Properties Modern Search Filters Layout */
.wv-property-filters-container {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin: 10px 15px 16px;
    padding: 14px 16px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
}

/* Mobile Toggle - Hidden on PC */
.wv-filter-mobile-toggle-wrap {
    display: none;
}

/* Form Controls & Labels */
.wv-filter-field {
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.wv-filter-label {
    color: #475569 !important;
    display: block !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 0.3px !important;
    margin: 0 0 4px 0 !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
}

.wv-filter-label-empty {
    visibility: hidden !important;
}

.wv-filter-field .form-control {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 7px !important;
    box-shadow: none !important;
    color: #1e293b !important;
    font-size: 12px !important;
    height: 35px !important;
    min-height: 35px !important;
    padding: 6px 9px !important;
    transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    width: 100% !important;
}

.wv-filter-field .form-control:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18) !important;
    outline: none !important;
}

.wv-filter-field .form-control::placeholder {
    color: #94a3b8 !important;
    font-size: 11.5px !important;
}

/* Action Button */
.wv-filter-btn-wrap {
    align-items: center !important;
    display: flex !important;
    gap: 6px !important;
    height: 35px !important;
    width: 100% !important;
}

.wv-btn-search {
    align-items: center !important;
    background: #2563eb !important;
    border: 1px solid #1d4ed8 !important;
    border-radius: 7px !important;
    box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25) !important;
    color: #ffffff !important;
    display: inline-flex !important;
    flex: 1 1 auto !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    height: 35px !important;
    justify-content: center !important;
    min-height: 35px !important;
    padding: 0 12px !important;
    transition: background 0.15s ease, transform 0.15s ease !important;
}

.wv-btn-search:hover,
.wv-btn-search:focus {
    background: #1d4ed8 !important;
    border-color: #1e40af !important;
    color: #ffffff !important;
    transform: translateY(-1px);
}

.wv-btn-reset {
    align-items: center !important;
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 7px !important;
    color: #64748b !important;
    display: inline-flex !important;
    flex: 0 0 35px !important;
    font-size: 12px !important;
    height: 35px !important;
    justify-content: center !important;
    min-height: 35px !important;
    padding: 0 !important;
    transition: all 0.15s ease !important;
    width: 35px !important;
}

.wv-btn-reset:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
}

/* 2 Lines Grid on PC (>= 992px) */
@media (min-width: 992px) {
    .wv-property-filters-body {
        display: block !important;
        height: auto !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    .wv-property-filters-grid {
        display: grid !important;
        grid-template-columns: 1.35fr 1.35fr 1fr 1fr 1fr 1fr !important;
        gap: 10px 10px !important;
        align-items: flex-end !important;
        width: 100% !important;
    }
}

/* Mobile Collapsible Filters (< 992px) */
@media (max-width: 991px) {
    .wv-property-filters-container {
        margin: 8px 10px 14px;
        padding: 0;
        overflow: hidden;
    }

    .wv-filter-mobile-toggle-wrap {
        display: block !important;
        width: 100% !important;
    }

    .wv-filter-mobile-toggle-btn {
        align-items: center !important;
        background: #f8fafc !important;
        border: 0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        color: #1e293b !important;
        cursor: pointer !important;
        display: flex !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        justify-content: space-between !important;
        padding: 11px 14px !important;
        transition: background 0.15s ease !important;
        width: 100% !important;
    }

    .wv-filter-mobile-toggle-btn:hover,
    .wv-filter-mobile-toggle-btn:focus {
        background: #f1f5f9 !important;
        outline: none !important;
    }

    .wv-filter-toggle-left {
        align-items: center !important;
        display: inline-flex !important;
        gap: 8px !important;
    }

    .wv-filter-toggle-left i {
        color: #2563eb !important;
        font-size: 14px !important;
    }

    .wv-filter-active-pill {
        background: #2563eb !important;
        border-radius: 999px !important;
        color: #ffffff !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        line-height: 1 !important;
        padding: 3px 7px !important;
    }

    .wv-filter-toggle-right {
        align-items: center !important;
        color: #64748b !important;
        display: inline-flex !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        gap: 6px !important;
    }

    .wv-filter-chevron {
        font-size: 11px !important;
        transition: transform 0.22s ease !important;
    }

    .wv-filter-mobile-toggle-btn.is-open .wv-filter-chevron {
        transform: rotate(180deg) !important;
    }

    .wv-property-filters-body {
        padding: 12px 14px !important;
    }

    .wv-property-filters-body[data-mobile-collapsed="true"] {
        display: none;
    }

    .wv-property-filters-grid {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 9px !important;
        width: 100% !important;
    }

    .wv-filter-col-wide {
        grid-column: span 2 !important;
    }

    .wv-filter-action-cell {
        grid-column: span 2 !important;
        margin-top: 4px !important;
    }

    .wv-filter-label-empty {
        display: none !important;
    }
}

@media (max-width: 575px) {
    .wv-property-filters-grid {
        grid-template-columns: 1fr !important;
    }

    .wv-filter-col-wide,
    .wv-filter-action-cell {
        grid-column: span 1 !important;
    }
}
</style>
<div id="resultDiv">
<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Properties');?></h1></div></div>
<div class="panel"><?php echo $this->Session->flash();?>
    <?php $bulkUrl=$this->Html->url(array('controller'=>'Properties','action'=>'bulkupdate'));?>
    <div class="panel-heading wv-properties-topbar">
        <div class="wv-properties-actions-group">
            <div class="btn-group">
                <?php $url=$this->Html->url(array('controller'=>'Properties')); echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add New Property'),array('controller'=>'Properties','action'=>'add'),array('escape'=>false,'class'=>'btn btn-success'));?>
                <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),'javascript:void(0);',array('name'=>'editallfrm','id'=>'editallfrm','onclick'=>"check_perform_editfull('$url');",'escape'=>false,'class'=>'btn btn-warning'));?>
                <?php echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'javascript:void(0);',array('name'=>'deleteallfrm','id'=>'deleteallfrm','onclick'=>'check_perform_delete();','escape'=>false,'class'=>'btn btn-danger'));?>
                <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('Enhancement Overview'),array('controller'=>'Propertyenhancements','action'=>'index'),array('escape'=>false,'class'=>'btn btn-info'));?>
                <?php echo $this->Html->link('<span class="fa fa-map-marker"></span>&nbsp;'.__('Missing Map'),array('controller'=>'Propertyenhancements','action'=>'missinglocations'),array('escape'=>false,'class'=>'btn btn-primary'));?>
            </div>
            <!-- Bulk Action Toolbar moved to top right after Missing Map -->
            <div class="wv-bulk-action-inline">
                <?php echo $this->Form->select('Property.bulk_action_top',array(
                    'publish_yes'=>__('Publish Selected'),
                    'publish_no'=>__('Unpublish Selected'),
                    'featured_yes'=>__('Feature Selected'),
                    'featured_no'=>__('Unfeature Selected'),
                    'status_available'=>__('Mark Available'),
                    'status_sold'=>__('Mark Sold')
                ),array('empty'=>__('Bulk Action'),'id'=>'propertyBulkActionSelect','class'=>'form-control wv-bulk-action-select','div'=>false,'label'=>false));?>
                <button type="button" class="btn btn-primary wv-bulk-action-apply-btn" id="propertyBulkActionApplyBtn" onclick="performBulkUpdate();">
                    <span class="fa fa-check"></span> <?php echo __('Apply');?>
                </button>
            </div>
        </div>
    </div>
    <?php
    $activeFilterCount = 0;
    $filterKeys = array('general', 'location', 'type', 'contract', 'start_price', 'end_price', 'bedroom', 'bathroom', 'Area', 'publish', 'skeyword');
    foreach ($filterKeys as $fk) {
        if (!empty($this->params['named'][$fk]) || !empty($this->request->data[$fk])) {
            $activeFilterCount++;
        }
    }
    $isMobileFilterOpen = ($activeFilterCount > 0);
    ?>
    <?php echo $this->Form->create(array('name'=>'searchfrm','id'=>'searchfrmactive','action' => 'index'));?>
    <div class="wv-property-filters-container">
        <!-- Mobile Toggle Header (Visible on Mobile Only) -->
        <div class="wv-filter-mobile-toggle-wrap">
            <button type="button" class="wv-filter-mobile-toggle-btn<?php echo $isMobileFilterOpen ? ' is-open' : ''; ?>" id="wvPropertyFilterMobileToggle" aria-expanded="<?php echo $isMobileFilterOpen ? 'true' : 'false'; ?>">
                <span class="wv-filter-toggle-left">
                    <i class="fa fa-sliders"></i>
                    <strong><?php echo __('Search & Filters'); ?></strong>
                    <?php if($activeFilterCount > 0){ ?>
                        <span class="wv-filter-active-pill"><?php echo $activeFilterCount; ?> <?php echo __('active'); ?></span>
                    <?php } ?>
                </span>
                <span class="wv-filter-toggle-right">
                    <span class="wv-filter-toggle-state-text"><?php echo $isMobileFilterOpen ? __('Hide Filters') : __('Show Filters'); ?></span>
                    <i class="fa fa-chevron-down wv-filter-chevron"></i>
                </span>
            </button>
        </div>

        <!-- Filter Form Body (2 Lines on PC, Collapsible on Mobile) -->
        <div class="wv-property-filters-body" id="wvPropertyFiltersBody"<?php if(!$isMobileFilterOpen){ echo ' data-mobile-collapsed="true"'; } ?>>
            <div class="wv-property-filters-grid">
                <!-- LINE 1 (PC) -->
                <div class="wv-filter-field wv-filter-col-wide">
                    <label class="wv-filter-label"><strong><?php echo __('General Search');?></strong></label>
                    <?php echo $this->Form->input('general',array('label' => false,'class'=>'form-control','placeholder'=> __('Search Title, Address, Office ID (e.g. WV-LD698DE)'),'div'=>false));?>
                </div>
                <div class="wv-filter-field wv-filter-col-wide">
                    <label class="wv-filter-label"><strong><?php echo __('Location');?></strong></label>
                    <?php echo $this->Form->input('location',array('label' => false,'class'=>'form-control','placeholder'=> __('Colony, Block, Sector, City or State'),'div'=>false));?>
                </div> 
                <div class="wv-filter-field">
                    <label class="wv-filter-label"><strong><?php echo __('Kind of property');?></strong></label>
                    <?php echo $this->Form->select('type',$type,array('label' => false,'class'=>'form-control','empty'=>__('All Property Types'),'div'=>false));?>
                </div> 
                <div class="wv-filter-field">
                    <label class="wv-filter-label"><strong><?php echo __('Contract');?></strong></label>
                    <?php echo $this->Form->select('contract',$contract,array('label' => false,'class'=>'form-control','empty'=>__('All Contracts'),'div'=>false));?>
                </div> 
                <div class="wv-filter-field">
                    <label class="wv-filter-label"><strong><?php echo __('Minimum price');?></strong></label>
                    <?php echo $this->Form->input('start_price',array('label' => false,'class'=>'form-control','placeholder'=> __('Min Price (e.g. 500000)'),'div'=>false));?>
                </div> 
                <div class="wv-filter-field">
                    <label class="wv-filter-label"><strong><?php echo __('Maximum price');?></strong></label>
                    <?php echo $this->Form->input('end_price',array('label' => false,'class'=>'form-control','placeholder'=> __('Max Price (e.g. 15000000)'),'div'=>false));?>
                </div> 

                <!-- LINE 2 (PC) -->
                <div class="wv-filter-field">
                    <label class="wv-filter-label"><strong><?php echo __('Bedroom');?></strong></label>
                    <?php echo $this->Form->input('bedroom',array('label' => false,'class'=>'form-control','placeholder'=> __('Bedrooms (e.g. 2, 3)'),'div'=>false));?>
                </div> 
                <div class="wv-filter-field">
                    <label class="wv-filter-label"><strong><?php echo __('Bathroom');?></strong></label>
                    <?php echo $this->Form->input('bathroom',array('label' => false,'class'=>'form-control','placeholder'=> __('Bathrooms (e.g. 2, 3)'),'div'=>false));?>
                </div> 
                <div class="wv-filter-field">
                    <label class="wv-filter-label"><strong><?php echo __('Area');?></strong></label>
                    <?php echo $this->Form->input('Area',array('label' => false,'class'=>'form-control','placeholder'=> __('Area (e.g. 3, 5, 10)'),'div'=>false));?>
                </div> 
                <div class="wv-filter-field">
                    <label class="wv-filter-label"><strong><?php echo __('Published');?></strong></label>
                    <?php $sex=array('Yes'=>'Yes','No'=>'No');
                    echo $this->Form->select('publish',$sex,array('label' => false,'class'=>'form-control','empty'=>__('All Status'),'div'=>false));?>
                </div> 
                <div class="wv-filter-field">
                    <label class="wv-filter-label"><strong><?php echo __('Status');?></strong></label>
                    <?php $status=array(__('Available')=>__('Available'),__('Sold')=>__('Sold'));
                    echo $this->Form->select('skeyword',$status,array('label' => false,'class'=>'form-control','empty'=>__('All Status'),'div'=>false));?>
                </div> 
                <div class="wv-filter-field wv-filter-action-cell">
                    <label class="wv-filter-label wv-filter-label-empty" aria-hidden="true">&nbsp;</label>
                    <div class="wv-filter-btn-wrap">
                        <?php echo $this->Form->input('searchfrm',array('type'=>'hidden','value'=>'active'));?>
                        <?php echo $this->Form->button('<span class="fa fa-search"></span>&nbsp;'.__('Search'),array('class'=>'btn btn-primary wv-btn-search','escape'=>false));?>
                        <?php if($activeFilterCount > 0){ ?>
                            <a href="<?php echo $this->Html->url(array('action'=>'index')); ?>" class="btn btn-default wv-btn-reset" title="<?php echo __('Reset Filters'); ?>"><span class="fa fa-refresh"></span></a>
                        <?php } ?>
                    </div>
                </div>              
            </div>
        </div>
    </div>
    <?php echo $this->Form->end();?>
<script type="text/javascript">
(function($){
    $(document).off('click.wvFilterMobileToggle', '#wvPropertyFilterMobileToggle')
        .on('click.wvFilterMobileToggle', '#wvPropertyFilterMobileToggle', function(e){
            e.preventDefault();
            var $btn = $(this);
            var $body = $('#wvPropertyFiltersBody');
            var isVisible = $body.is(':visible');
            if(isVisible){
                $body.stop(true, true).slideUp(200, function(){
                    $body.attr('data-mobile-collapsed', 'true');
                    $btn.removeClass('is-open').attr('aria-expanded', 'false');
                    $btn.find('.wv-filter-toggle-state-text').text('<?php echo __('Show Filters'); ?>');
                });
            } else {
                $body.removeAttr('data-mobile-collapsed');
                $body.stop(true, true).slideDown(200, function(){
                    $btn.addClass('is-open').attr('aria-expanded', 'true');
                    $btn.find('.wv-filter-toggle-state-text').text('<?php echo __('Hide Filters'); ?>');
                });
            }
        });

    $(document).off('change.wvBulkActionSync', '#propertyBulkActionSelect')
        .on('change.wvBulkActionSync', '#propertyBulkActionSelect', function(){
            $('#propertyBulkActionHidden').val($(this).val());
        });
})(jQuery);

function performBulkUpdate() {
    var bulkVal = jQuery('#propertyBulkActionSelect').val();
    if (!bulkVal) {
        alert('<?php echo h(__('Please select a bulk action')); ?>');
        jQuery('#propertyBulkActionSelect').focus();
        return false;
    }
    var checkedCount = jQuery('input.chkselect:checked').length;
    if (checkedCount === 0) {
        alert('<?php echo h(__('Please select at least one property')); ?>');
        return false;
    }
    var $hidden = jQuery('#propertyBulkActionHidden');
    if (!$hidden.length) {
        var $form = jQuery('form[name="deleteallfrm"]');
        $hidden = jQuery('<input type="hidden" name="data[Property][bulk_action]" id="propertyBulkActionHidden">').appendTo($form);
    }
    $hidden.val(bulkVal);
    document.deleteallfrm.action = '<?php echo h($bulkUrl);?>';
    document.deleteallfrm.submit();
    return true;
}
</script>
<?php echo $this->element('pagination',array('IsSearch'=>'No','UseNamedLimit'=>true,'UseQueryLimit'=>true,'NamedLimitBaseUrl'=>$propertyLimitUrl));
        $page_params = $this->Paginator->params();
        $limit = $page_params['limit'];
        $page = $page_params['page'];
        $serial_no = 1*$limit*($page-1)+1;?>
        <?php echo $this->Form->create(array('name'=>'deleteallfrm','action' => 'deleteall'));?>
<?php echo $this->Session->flash();?>
<?php echo $this->Form->hidden('Property.bulk_action', array('id' => 'propertyBulkActionHidden')); ?>
               <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <th class="pbutton"><?php echo $this->Form->checkbox('checkbox', array('value'=>'deleteall','name'=>'selectAll','label'=>false,'id'=>'selectAll','hiddenField'=>false));?></th>
                            <th><?php echo $this->Paginator->sort('id', __('#'), array('direction' => 'desc'));?></th>
			    <th><?php echo $this->Paginator->sort('uniq_id', __('Office ID'), array('direction' => 'asc'));?></th>
			    <th><?php echo $this->Paginator->sort('name', __('Name'), array('direction' => 'asc'));?></th>
			    <th><?php echo $this->Paginator->sort('price', __('Price'), array('direction' => 'asc'));?></th>
			    <th><?php echo $this->Paginator->sort('type', __('Type'), array('direction' => 'asc'));?></th>
			    <th><?php echo $this->Paginator->sort('Contract.name', __('Contract'), array('direction' => 'asc'));?></th>
                            <th><?php echo $this->Paginator->sort('status', __('Status'), array('direction' => 'asc'));?></th>
                            <th><?php echo __('Featured/Published');?></th>
                            <th><?php echo __('Quality');?></th>
			    <th class="pbutton property-action-header"><?php echo __('Action');?></th>
                        </tr>
                        <?php foreach ($Property as $post):
                        $id=$post['Property']['id'];
                        $editUrl=$url.'/edit/'.$id;
                        $propertySlug=trim(preg_replace('/[^A-Za-z0-9]+/','-',(string)$post['Property']['name']),'-');
                        if($propertySlug==='')$propertySlug='property-'.$id;
                        $frontPropertyUrl=$this->Html->url('/Properties/'.$propertySlug);?>
                        <tr>
                            <td class="pbutton"><?php if($userType!="AGT" || $post['Property']['user_id']==$luserId){ echo $this->Form->checkbox(false,array('value' => $post['Property']['id'],'name'=>'data[Property][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect')); } else echo "&nbsp;";?></td>
                            <td><?php echo $serial_no++; ?></td>
			    <td><?php echo $post['Property']['uniq_id'];?></td>
			    <td><?php echo $post['Property']['name'];?></td>
			    <td class="property-price-cell"><?php echo h($currency.number_format((float)$post['Property']['price'], 0));?></td>
			    <td><?php echo $post['Type']['name'];?></td>
			    <td><?php echo $post['Contract']['name'];?></td>
			    <td><span class="label label-<?php if($post['Property']['status']=="Available")echo"success";else echo"danger";?>"><?php echo __($post['Property']['status']);?></span></td>
			    <td><div class="properties-status-toggle"><?php if($userType!="AGT" || $post['Property']['user_id']==$luserId){ if($post['Property']['featured']=="Yes"){ echo $this->Html->link('<span class="fa fa-check"></span>&nbsp;'.__('Yes'),array('controller'=>'Properties','action'=>'featured',$id,'Yes'),array('escape'=>false,'class'=>'btn property-flag-btn property-flag-yes'));}
			    else{echo $this->Html->link('<span class="fa fa-times-circle"></span>&nbsp;'.__('No'),array('controller'=>'Properties','action'=>'featured',$id,'No'),array('escape'=>false,'class'=>'btn property-flag-btn property-flag-no'));}?>
			    <?php if($post['Property']['published']=="Yes"){ echo $this->Html->link('<span class="fa fa-check"></span>&nbsp;'.__('Yes'),array('controller'=>'Properties','action'=>'published',$id,'Yes'),array('escape'=>false,'class'=>'btn property-flag-btn property-flag-yes'));}
			    else{echo $this->Html->link('<span class="fa fa-times-circle"></span>&nbsp;'.__('No'),array('controller'=>'Properties','action'=>'published',$id,'No'),array('escape'=>false,'class'=>'btn property-flag-btn property-flag-no'));}?>
			    <?php }?></div></td>
                            <td>
                                <?php $quality=isset($propertyQualityScores[$id]) ? $propertyQualityScores[$id] : array('score'=>0,'missing'=>array());?>
                                <div class="property-quality-wrap">
                                    <div class="property-quality-bar"><span style="width:<?php echo (int)$quality['score'];?>%"></span></div>
                                    <div class="property-quality-text"><?php echo (int)$quality['score'];?>%</div>
                                    <?php echo $this->Html->link(__('Checklist'),array('controller'=>'Propertyenhancements','action'=>'quality',$id),array('class'=>'btn btn-xs btn-default'));?>
                                </div>
                            </td>
			    <td class="pbutton property-action-cell"><?php if($userType=="AGT" && $post['Property']['user_id']!=$luserId){?>
			    <?php echo $this->Html->link('<span class="fa fa-arrows-alt"></span>&nbsp;&nbsp;&nbsp;&nbsp;'.__('View'),'javascript:void(0);',array('class'=>'btn','onclick'=>"show_modal('$url/View/$id');",'escape'=>false));}
			    else {?>
			    <div class="btn-group">
			    <button type="button" class="btn btn-primary dropdown-toggle property-action-button" data-toggle="dropdown" aria-expanded="false">
			    <?php echo __('Action');?> <span class="caret"></span>
			    </button>
			    <ul class="dropdown-menu" role="menu">
			      <li><?php echo $this->Html->link('<span class="fa fa-image"></span>&nbsp;'.__('Photos'),array('controller'=>'properties_photos','action'=>"index/$id"),array('escape'=>false));?></li>
			      <li><?php echo $this->Html->link('<span class="fa fa-file"></span>&nbsp;'.__('Documents'),array('controller'=>'properties_documents','action'=>"index/$id"),array('escape'=>false));?></li>
			      <li><?php echo $this->Html->link('<span class="fa fa-building-o fa-fw"></span>&nbsp;'.__('Floor Plans'),array('controller'=>'properties_floorplans','action'=>"index/$id"),array('escape'=>false));?></li>
			      <li><?php echo $this->Html->link('<span class="fa fa-magic"></span>&nbsp;'.__('Enhancements'),array('controller'=>'Propertyenhancements','action'=>'quality',$id),array('escape'=>false));?></li>
			      <li><?php echo $this->Html->link('<span class="fa fa-tags"></span>&nbsp;'.__('Media Details'),array('controller'=>'Propertyenhancements','action'=>'media',$id),array('escape'=>false));?></li>
			      <li><?php echo $this->Html->link('<span class="fa fa-search"></span>&nbsp;'.__('SEO'),array('controller'=>'Propertyenhancements','action'=>'seo',$id),array('escape'=>false));?></li>
			      <li><?php echo $this->Html->link('<span class="fa fa-line-chart"></span>&nbsp;'.__('Lead Clicks'),array('controller'=>'Propertyenhancements','action'=>'leads',$id),array('escape'=>false));?></li>
			      <li><?php echo $this->Html->link('<span class="fa fa-history"></span>&nbsp;'.__('History'),array('controller'=>'Properties','action'=>'history',$id),array('escape'=>false));?></li>
			      <li><?php echo $this->Html->link('<span class="fa fa-copy"></span>&nbsp;'.__('Duplicate'),array('controller'=>'Properties','action'=>'duplicate',$id),array('escape'=>false,'onclick'=>"return confirm('".__('Duplicate this property as unpublished?')."');"));?></li>
			      <li><?php echo $this->Html->link('<span class="fa fa-external-link"></span>&nbsp;'.__('Visit'),$frontPropertyUrl,array('escape'=>false,'target'=>'_blank'));?></li>
			      <li class="divider"></li>
			      <li><?php echo $this->Html->link('<span class="fa fa-arrows-alt"></span>&nbsp;'.__('View'),'javascript:void(0);',array('onclick'=>"show_modal('$url/View/$id');",'escape'=>false));?></li>
			      <li><a class="property-edit-link" href="<?php echo h($editUrl);?>" onclick="window.location.href=this.href; return false;"><span class="fa fa-edit"></span>&nbsp;<?php echo __('Edit');?></a></li>
			      <li> <?php echo $this->Html->Link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'javascript:void(0);',array('onclick'=>"check_perform_sdelete('$id');",'escape'=>false));?></li>
			    </ul>
			  </div><?php }?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php unset($post); ?>
                        </table>
            </div>
</div>
<?php echo $this->Form->end();?>
<?php echo $this->element('pagination',array('UseNamedLimit'=>true,'UseQueryLimit'=>true,'NamedLimitBaseUrl'=>$propertyLimitUrl));?>
</div>
