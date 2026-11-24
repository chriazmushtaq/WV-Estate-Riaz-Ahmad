<?php
$guideByCategory = isset($placeholderGuideByCategory) ? (array)$placeholderGuideByCategory : array();
$guideFlat = isset($placeholderGuide) ? (array)$placeholderGuide : array();
$categoryTypeMap = isset($placeholderCategoryTypeMap) ? (array)$placeholderCategoryTypeMap : array();
$selectedType = isset($selectedLetterTypeForGuide) ? (string)$selectedLetterTypeForGuide : '';
$letterTypeMap = isset($letterTypeArr) ? (array)$letterTypeArr : array();
?>
<style type="text/css">
.wv-placeholders-widget {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    margin-top: 14px;
}
.wv-ph-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e2e8f0;
}
.wv-ph-title-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.wv-ph-title {
    font-size: 13px;
    font-weight: 800;
    color: #1e293b;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: .03em;
}
.wv-ph-badge {
    background: #e0e7ff;
    color: #3730a3;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 999px;
}
.wv-ph-controls {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.wv-ph-search {
    width: 220px !important;
    height: 28px !important;
    font-size: 12px !important;
    padding: 3px 8px !important;
    border-radius: 4px !important;
}
.wv-ph-btn-toggle {
    font-size: 11px !important;
    padding: 3px 8px !important;
    font-weight: 700 !important;
}
.wv-ph-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}
@media (max-width: 991px) {
    .wv-ph-grid { grid-template-columns: 1fr; }
    .wv-ph-search { width: 100% !important; }
}
.wv-ph-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 10px;
    transition: box-shadow .15s ease, border-color .15s ease;
}
.wv-ph-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 4px rgba(0,0,0,0.03);
}
.wv-ph-card.hidden-by-type,
.wv-ph-card.hidden-by-search {
    display: none !important;
}
.wv-ph-card-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
    font-weight: 800;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .03em;
    margin-bottom: 6px;
    padding-bottom: 4px;
    border-bottom: 1px dashed #edf2f7;
}
.wv-ph-card-count {
    color: #94a3b8;
    font-size: 10px;
    font-weight: 600;
}
.wv-ph-tokens {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}
.wv-ph-token {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    color: #0f172a;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 2px 6px;
    font-family: Consolas, Monaco, monospace;
    font-size: 11px;
    line-height: 1.3;
    cursor: pointer;
    user-select: none;
    transition: all .12s ease;
}
.wv-ph-token:hover {
    background: #e2e8f0;
    border-color: #94a3b8;
    color: #1e293b;
    transform: translateY(-1px);
}
.wv-ph-token:active {
    transform: translateY(0);
}
.wv-ph-token.copied {
    background: #dcfce7 !important;
    border-color: #86efac !important;
    color: #166534 !important;
}
.wv-ph-empty-notice {
    grid-column: 1 / -1;
    text-align: center;
    padding: 16px;
    color: #64748b;
    font-size: 12px;
    display: none;
}
.wv-ph-tip {
    font-size: 11px;
    color: #64748b;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}
</style>

<div class="wv-placeholders-widget" id="wvPlaceholdersWidget">
    <div class="wv-ph-header">
        <div class="wv-ph-title-wrap">
            <h4 class="wv-ph-title"><span class="fa fa-tags"></span>&nbsp;<?php echo __('Available Placeholders');?></h4>
            <span class="wv-ph-badge" id="wvPhTypeBadge"><?php echo __('All Types');?></span>
        </div>
        <div class="wv-ph-controls">
            <input type="text" id="wvPhSearchInput" class="form-control wv-ph-search" placeholder="<?php echo __('Filter placeholders (e.g. invoice, cnic)...');?>">
            <button type="button" class="btn btn-default wv-ph-btn-toggle" id="wvPhToggleScope" data-scope="relevant">
                <span class="fa fa-filter"></span>&nbsp;<span id="wvPhToggleScopeText"><?php echo __('Show All Placeholders');?></span>
            </button>
        </div>
    </div>

    <div class="wv-ph-grid" id="wvPhGrid">
        <?php foreach ($guideByCategory as $categoryLabel => $tokens): ?>
            <?php
            $tokens = array_values(array_unique(array_filter((array)$tokens)));
            if (empty($tokens)) { continue; }
            ?>
            <div class="wv-ph-card" data-category="<?php echo h($categoryLabel);?>">
                <div class="wv-ph-card-title">
                    <span><?php echo h($categoryLabel);?></span>
                    <span class="wv-ph-card-count"><?php echo count($tokens);?></span>
                </div>
                <div class="wv-ph-tokens">
                    <?php foreach ($tokens as $token): ?>
                        <span class="wv-ph-token" data-token="<?php echo h($token);?>" title="<?php echo __('Click to insert or copy');?>"><?php echo h($token);?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="wv-ph-empty-notice" id="wvPhEmptyNotice">
            <span class="fa fa-search"></span>&nbsp;<?php echo __('No matching placeholders found for this search/filter.');?>
        </div>
    </div>

    <div class="wv-ph-tip">
        <span class="fa fa-info-circle text-info"></span>
        <span><?php echo __('Click any placeholder pill to insert it directly into your template body (or copy to clipboard).');?></span>
    </div>
</div>

<script type="text/javascript">
(function($){
    var categoryTypeMap = <?php echo json_encode($categoryTypeMap);?> || {};
    var letterTypeLabels = <?php echo json_encode($letterTypeMap);?> || {};
    var currentScope = 'relevant'; // 'relevant' or 'all'

    function getSelectedTypeCode(){
        // Check select element in templateadd / templateedit
        var sel = $('#TemplateType, select[name="data[Template][type]"]');
        if(sel.length && sel.val()){
            return $.trim(sel.val());
        }
        return '<?php echo h($selectedType);?>';
    }

    function updateTypeBadge(typeCode){
        if(typeCode && letterTypeLabels[typeCode]){
            $('#wvPhTypeBadge').text(letterTypeLabels[typeCode]);
        }else if(typeCode){
            $('#wvPhTypeBadge').text(typeCode);
        }else{
            $('#wvPhTypeBadge').text('<?php echo __('All Types');?>');
        }
    }

    function applyFilter(){
        var typeCode = getSelectedTypeCode();
        var search = $.trim($('#wvPhSearchInput').val()).toLowerCase();
        var allowedCategories = (currentScope === 'relevant' && typeCode && categoryTypeMap[typeCode]) ? categoryTypeMap[typeCode] : null;
        var visibleCardCount = 0;

        updateTypeBadge(typeCode);

        $('.wv-ph-card').each(function(){
            var $card = $(this);
            var category = $card.attr('data-category');

            // 1. Type scope filter
            var typeMatch = true;
            if(allowedCategories && allowedCategories.length > 0){
                typeMatch = (allowedCategories.indexOf(category) !== -1);
            }

            if(!typeMatch){
                $card.addClass('hidden-by-type');
                return;
            }else{
                $card.removeClass('hidden-by-type');
            }

            // 2. Search filter
            var cardHasSearchMatch = false;
            var tokens = $card.find('.wv-ph-token');

            if(!search){
                tokens.show();
                cardHasSearchMatch = true;
            }else{
                var catMatch = category.toLowerCase().indexOf(search) !== -1;
                tokens.each(function(){
                    var tok = $(this).attr('data-token').toLowerCase();
                    if(catMatch || tok.indexOf(search) !== -1){
                        $(this).show();
                        cardHasSearchMatch = true;
                    }else{
                        $(this).hide();
                    }
                });
            }

            if(cardHasSearchMatch){
                $card.removeClass('hidden-by-search');
                visibleCardCount++;
            }else{
                $card.addClass('hidden-by-search');
            }
        });

        if(visibleCardCount === 0){
            $('#wvPhEmptyNotice').show();
        }else{
            $('#wvPhEmptyNotice').hide();
        }
    }

    function insertTokenIntoEditor(token){
        var mode = $('#TemplateEditorMode').val() || 'html';
        if(mode === 'html' || mode === 'preview'){
            var textarea = document.getElementById('TemplateDescriptionCode');
            if(textarea){
                if(document.selection){
                    textarea.focus();
                    var sel = document.selection.createRange();
                    sel.text = token;
                }else if(typeof textarea.selectionStart === 'number' && typeof textarea.selectionEnd === 'number'){
                    var start = textarea.selectionStart;
                    var end = textarea.selectionEnd;
                    var val = textarea.value;
                    textarea.value = val.substring(0, start) + token + val.substring(end);
                    textarea.selectionStart = textarea.selectionEnd = start + token.length;
                    textarea.focus();
                }else{
                    textarea.value += token;
                }
            }
        }else if(typeof tinymce !== 'undefined'){
            var ed = tinymce.get('TemplateDescriptionVisual');
            if(ed && !ed.isHidden()){
                ed.execCommand('mceInsertContent', false, token);
            }
        }

        // Copy to clipboard fallback/bonus
        try {
            if(navigator.clipboard && navigator.clipboard.writeText){
                navigator.clipboard.writeText(token);
            }
        }catch(e){}
    }

    $(function(){
        // Initial filter run
        applyFilter();

        // Listen for type changes on the form select
        $(document).on('change', '#TemplateType, select[name="data[Template][type]"]', function(){
            applyFilter();
        });

        // Search input keyup
        $('#wvPhSearchInput').on('input keyup', function(){
            applyFilter();
        });

        // Toggle Scope button (relevant vs all)
        $('#wvPhToggleScope').on('click', function(){
            if(currentScope === 'relevant'){
                currentScope = 'all';
                $('#wvPhToggleScopeText').text('<?php echo __('Show Type Placeholders Only');?>');
                $(this).addClass('btn-primary').removeClass('btn-default');
            }else{
                currentScope = 'relevant';
                $('#wvPhToggleScopeText').text('<?php echo __('Show All Placeholders');?>');
                $(this).addClass('btn-default').removeClass('btn-primary');
            }
            applyFilter();
        });

        // Token click -> insert into editor & show copy effect
        $(document).on('click', '.wv-ph-token', function(e){
            e.preventDefault();
            var $this = $(this);
            var token = $this.attr('data-token');
            insertTokenIntoEditor(token);

            $this.addClass('copied');
            var origText = $this.text();
            $this.text('✓ Copied');
            setTimeout(function(){
                $this.removeClass('copied').text(origText);
            }, 800);
        });
    });
})(jQuery);
</script>
