<script type="text/javascript">
$(document).ready(function(){
    function normalizeCategory(text){
        return $.trim(String(text || ''));
    }
    function applyCategoryFilter(){
        var selectedCategory = normalizeCategory($('#moduleCategoryFilter').val());
        var $rows = $('.js-right-row');
        var $headers = $('.js-right-category');
        if(selectedCategory === ''){
            $rows.show();
            $headers.show();
            return;
        }
        var visibleCategories = {};
        $rows.hide().each(function(){
            var $row = $(this);
            var rowCategory = normalizeCategory($row.attr('data-category'));
            if(rowCategory === selectedCategory){
                $row.show();
                visibleCategories[rowCategory] = 1;
            }
        });
        $headers.hide().each(function(){
            var $header = $(this);
            var headerCategory = normalizeCategory($header.attr('data-category'));
            if(visibleCategories[headerCategory]){
                $header.show();
            }
        });
    }
    function syncRowByView($row){
        var viewChecked = $row.find('.js-right-view').is(':checked');
        if(!viewChecked){
            $row.find('.js-right-add,.js-right-edit,.js-right-delete').prop('checked', false);
        }
    }
    function syncRowByAction($row){
        var anyAction = $row.find('.js-right-add,.js-right-edit,.js-right-delete').is(':checked');
        if(anyAction){
            $row.find('.js-right-view').prop('checked', true);
        }
    }
    $('#selectAllView').on('change', function(){
        $('.js-right-view').prop('checked', this.checked).trigger('change');
    });
    $('#selectAllAdd').on('change', function(){
        $('.js-right-add').prop('checked', this.checked).trigger('change');
    });
    $('#selectAllEdit').on('change', function(){
        $('.js-right-edit').prop('checked', this.checked).trigger('change');
    });
    $('#selectAllDelete').on('change', function(){
        $('.js-right-delete').prop('checked', this.checked).trigger('change');
    });
    $(document).on('change', '.js-right-view', function(){
        syncRowByView($(this).closest('tr'));
    });
    $(document).on('change', '.js-right-add,.js-right-edit,.js-right-delete', function(){
        syncRowByAction($(this).closest('tr'));
    });
    $('#moduleCategoryFilter').on('change', applyCategoryFilter);
    $('#moduleCategoryFilterReset').on('click', function(e){
        e.preventDefault();
        $('#moduleCategoryFilter').val('');
        applyCategoryFilter();
    });
    applyCategoryFilter();
});
</script>
<div class="container">
    <div class="panel panel-custom mrg">
        <div class="panel-heading"><?php echo __('Assign Form Rights');?><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button></div>
        <div class="panel-body">
            <?php echo $this->Session->flash();?>
            <?php
                $categoryFilterOptions = array('' => __('All Modules'));
                foreach((array)$PageRight as $filterRow)
                {
                    $filterCategoryName = trim((string)$filterRow['Page']['model_name']);
                    if($filterCategoryName === '')
                    {
                        $filterCategoryName = __('Other');
                    }
                    if(!isset($categoryFilterOptions[$filterCategoryName]))
                    {
                        $categoryFilterOptions[$filterCategoryName] = $filterCategoryName;
                    }
                }
            ?>
            <?php echo $this->Form->create('User', array('controller' => 'Users','action'=>"assignrightsedit/$id",'name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
            <div class="row" style="margin-bottom:12px;">
                <div class="col-sm-4">
                    <label for="moduleCategoryFilter" style="margin-bottom:4px;"><?php echo __('Module Category');?></label>
                    <select id="moduleCategoryFilter" class="form-control">
                        <?php foreach($categoryFilterOptions as $catValue => $catLabel){ ?>
                        <option value="<?php echo h($catValue);?>"><?php echo h($catLabel);?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-sm-2" style="padding-top:25px;">
                    <a href="#" id="moduleCategoryFilterReset"><?php echo __('Reset');?></a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <tr>
                        <th><?php echo __('#');?></th>
                        <th><?php echo __('Module Name');?></th>
                        <th><?php echo __('Page Name');?></th>
                        <th style="width:70px;text-align:center;"><?php echo __('View');?><br><?php echo $this->Form->checkbox(false, array('id'=>'selectAllView','hiddenField'=>false));?></th>
                        <th style="width:70px;text-align:center;"><?php echo __('Add');?><br><?php echo $this->Form->checkbox(false, array('id'=>'selectAllAdd','hiddenField'=>false));?></th>
                        <th style="width:70px;text-align:center;"><?php echo __('Edit');?><br><?php echo $this->Form->checkbox(false, array('id'=>'selectAllEdit','hiddenField'=>false));?></th>
                        <th style="width:70px;text-align:center;"><?php echo __('Delete');?><br><?php echo $this->Form->checkbox(false, array('id'=>'selectAllDelete','hiddenField'=>false));?></th>
                    </tr>
                    <?php
                        $currentCategory = '';
                        $srNo = 0;
                        foreach ($PageRight as $k=>$post):
                        $categoryName = trim((string)$post['Page']['model_name']);
                        if($categoryName === '')
                        {
                            $categoryName = __('Other');
                        }
                        if($categoryName !== $currentCategory)
                        {
                            $currentCategory = $categoryName;
                    ?>
                    <tr class="info js-right-category" data-category="<?php echo h($currentCategory);?>">
                        <td colspan="7"><strong><?php echo __('Category');?>: <?php echo h($currentCategory); ?></strong></td>
                    </tr>
                    <?php } ?>
                    <?php
                        $prId=(int)$post['Page']['id'];
                        $pageId=(int)$post['PageRight']['page_id'];
                        $ugroupId=(int)$post['PageRight']['ugroup_id'];
                        $hasRow = ($prId>0 && $prId===$pageId && (int)$id===$ugroupId);
                        $viewChecked = $hasRow && !empty($post['PageRight']['view_right']);
                        $addChecked = $hasRow && !empty($post['PageRight']['save_right']);
                        $editChecked = $hasRow && !empty($post['PageRight']['update_right']);
                        $deleteChecked = $hasRow && !empty($post['PageRight']['search_right']);
                    ?>
                    <tr class="js-right-row" data-category="<?php echo h($categoryName);?>">
                        <td><?php echo ++$srNo; ?></td>
                        <td><?php echo h($post['Page']['model_name']); ?></td>
                        <td><?php echo h($post['Page']['page_name']); ?></td>
                        <td style="text-align:center;">
                            <input type="hidden" name="data[PageRight][page_id][]" value="<?php echo $prId; ?>" />
                            <?php echo $this->Form->checkbox(false,array('value' =>1,'name'=>'data[PageRight][view_right]['.$prId.']','class'=>'js-right-view','checked'=>$viewChecked,'hiddenField'=>false));?>
                        </td>
                        <td style="text-align:center;">
                            <?php echo $this->Form->checkbox(false,array('value' =>1,'name'=>'data[PageRight][save_right]['.$prId.']','class'=>'js-right-add','checked'=>$addChecked,'hiddenField'=>false));?>
                        </td>
                        <td style="text-align:center;">
                            <?php echo $this->Form->checkbox(false,array('value' =>1,'name'=>'data[PageRight][update_right]['.$prId.']','class'=>'js-right-edit','checked'=>$editChecked,'hiddenField'=>false));?>
                        </td>
                        <td style="text-align:center;">
                            <?php echo $this->Form->checkbox(false,array('value' =>1,'name'=>'data[PageRight][delete_right]['.$prId.']','class'=>'js-right-delete','checked'=>$deleteChecked,'hiddenField'=>false));?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php unset($post); ?>
                    <tr>
                        <td colspan="7"><?php echo$this->Form->button('<span class="fa fa-refresh"></span>&nbsp;'.__('Update'),array('class'=>'btn btn-success','escape'=>false));?></td>
                    </tr>
                </table>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>
