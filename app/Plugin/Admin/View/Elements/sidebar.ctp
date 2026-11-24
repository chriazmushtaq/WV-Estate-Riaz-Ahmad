<?php
$adminSidebarSearchId = !empty($searchInputId) ? (string)$searchInputId : 'adminSidebarSearch';
?>
<div id="sidebar-main" class="sidebar sidebar-default">
    <div class="sidebar-content">
        <div class="wv-admin-sidebar-shell">
            <button type="button" class="wv-admin-sidebar-mobile-close" id="sidebar-mobile-close" onclick="window.closeAdminMobileSidebar && window.closeAdminMobileSidebar(event); return false;" aria-label="<?php echo h(__('Close menu'));?>" title="<?php echo h(__('Close menu'));?>">
                <i class="fa fa-times"></i>
            </button>
            <div class="wv-admin-sidebar-search-wrap">
                <label class="sr-only" for="<?php echo h($adminSidebarSearchId);?>"><?php echo __('Search menu');?></label>
                <span class="wv-admin-sidebar-search-icon"><i class="fa fa-search"></i></span>
                <input type="text" id="<?php echo h($adminSidebarSearchId);?>" class="wv-admin-sidebar-search" placeholder="<?php echo h(__('Search menu'));?>" autocomplete="off">
                <button type="button" class="wv-admin-sidebar-search-clear" id="sidebarSearchClear" aria-label="<?php echo h(__('Clear search'));?>" title="<?php echo h(__('Clear search'));?>" style="display:none;">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="wv-admin-sidebar-menu" data-admin-menu-search>
                <?php echo $this->MenuBuilder->build('slide-out');?>
            </div>
        </div>
    </div>
</div>
