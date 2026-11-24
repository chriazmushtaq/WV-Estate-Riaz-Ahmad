<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array(
    'update' => '#resultDiv',
    'evalScripts' => true,
));
$bulkSmsUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'leadbulkmessage', 'sms'), true);
$bulkEmailUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'leadbulkmessage', 'email'), true);
$bulkScheduleSmsUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'schedulesms'), true);
$exportCsvUrl = isset($exportCsvUrl) ? $exportCsvUrl : array('controller' => 'Leads', 'action' => 'exportcsv');
$canUseAgencyFilters = isset($canUseAgencyFilters) ? (bool)$canUseAgencyFilters : false;
$canUseAgentFilters = isset($canUseAgentFilters) ? (bool)$canUseAgentFilters : false;
$selectedProjectFilter = isset($selectedProjectFilter) ? (int)$selectedProjectFilter : 0;
$selectedAgentFilter = isset($selectedAgentFilter) ? (int)$selectedAgentFilter : 0;
$selectedPeriodFilter = isset($selectedPeriodFilter) ? (string)$selectedPeriodFilter : '';
$selectedPhoneRegion = isset($selectedPhoneRegion) ? (string)$selectedPhoneRegion : 'all';
$selectedStartDateInput = isset($selectedStartDateInput) ? (string)$selectedStartDateInput : '';
$selectedEndDateInput = isset($selectedEndDateInput) ? (string)$selectedEndDateInput : '';
$selectedAgencyName = isset($selectedAgencyName) ? (string)$selectedAgencyName : __('All Agency');
$selectedAgentName = isset($selectedAgentName) ? (string)$selectedAgentName : __('All Agents');
$selectedFilterSummary = isset($selectedFilterSummary) ? (string)$selectedFilterSummary : '';
$periodOptions = isset($periodOptions) ? (array)$periodOptions : array();
$projectName = isset($projectName) ? (array)$projectName : array();
$agentFilterOptions = isset($agentFilterOptions) ? (array)$agentFilterOptions : array();
$reportAgency = isset($reportAgency) ? (array)$reportAgency : array();
$showAssignedAgentColumn = !empty($showAssignedAgentColumn);
$assignedAgentMap = isset($assignedAgentMap) ? (array)$assignedAgentMap : array();
$assignedAgentRoleMap = isset($assignedAgentRoleMap) ? (array)$assignedAgentRoleMap : array();
$statusOptions = isset($statusOptions) ? (array)$statusOptions : array();
$statusMetaMap = isset($statusMetaMap) ? (array)$statusMetaMap : array();
$detectRegion = function($phone){
    $raw = trim((string)$phone);
    if($raw === '') return null;
    $digits = preg_replace('/[^0-9]/', '', $raw);
    if($digits === '') return null;
    if(substr($digits, 0, 2) === '00') $digits = substr($digits, 2);
    if(substr($digits, 0, 3) === '092') $digits = substr($digits, 1);
    if(preg_match('/^92[0-9]{10}$/', $digits) || preg_match('/^03[0-9]{9}$/', $digits) || preg_match('/^3[0-9]{9}$/', $digits) || preg_match('/^0[2-9][0-9]{8,9}$/', $digits)) {
        return array('is_overseas' => false, 'badge' => '🇵🇰 PK', 'country' => 'Pakistan');
    }
    if(substr($digits, 0, 1) === '0' && strlen($digits) >= 11) $digits = substr($digits, 1);
    if(substr($digits, 0, 3) === '971') return array('is_overseas' => true, 'badge' => '🇦🇪 UAE', 'country' => 'UAE');
    if(substr($digits, 0, 3) === '966') return array('is_overseas' => true, 'badge' => '🇸🇦 KSA', 'country' => 'Saudi Arabia');
    if(substr($digits, 0, 2) === '44') return array('is_overseas' => true, 'badge' => '🇬🇧 UK', 'country' => 'UK');
    if(substr($digits, 0, 1) === '1' && strlen($digits) === 11) return array('is_overseas' => true, 'badge' => '🇺🇸 USA', 'country' => 'USA');
    if(substr($digits, 0, 3) === '968') return array('is_overseas' => true, 'badge' => '🇴🇲 Oman', 'country' => 'Oman');
    if(substr($digits, 0, 3) === '974') return array('is_overseas' => true, 'badge' => '🇶🇦 Qatar', 'country' => 'Qatar');
    if(substr($digits, 0, 3) === '973') return array('is_overseas' => true, 'badge' => '🇧🇭 Bahrain', 'country' => 'Bahrain');
    if(substr($digits, 0, 3) === '965') return array('is_overseas' => true, 'badge' => '🇰🇼 Kuwait', 'country' => 'Kuwait');
    if(substr($digits, 0, 2) === '61') return array('is_overseas' => true, 'badge' => '🇦🇺 Australia', 'country' => 'Australia');
    if(substr($digits, 0, 2) === '39') return array('is_overseas' => true, 'badge' => '🇮🇹 Italy', 'country' => 'Italy');
    if(substr($digits, 0, 2) === '49') return array('is_overseas' => true, 'badge' => '🇩🇪 Germany', 'country' => 'Germany');
    return array('is_overseas' => true, 'badge' => '🌍 Overseas', 'country' => 'Overseas');
};
$resolveImage = function($candidates){
    foreach((array)$candidates as $candidate)
    {
        $candidate = trim((string)$candidate);
        if($candidate === '')
        {
            continue;
        }
        $candidate = trim(str_replace('\\', '/', $candidate), '/');
        $filePath = WWW_ROOT.'img'.DS.str_replace('/', DS, $candidate);
        if(file_exists($filePath))
        {
            return $candidate;
        }
    }
    return '';
};
$headerAgencyName = !empty($reportAgency['name']) ? $reportAgency['name'] : $siteOrganization;
$headerAgencyAddress = !empty($reportAgency['address']) ? $reportAgency['address'] : $siteAddress;
$headerAgencyPhone = !empty($reportAgency['phone']) ? $reportAgency['phone'] : '';
$headerAgencyMobile = !empty($reportAgency['mobile']) ? $reportAgency['mobile'] : '';
$headerAgencyEmail = !empty($reportAgency['email']) ? $reportAgency['email'] : $siteEmail;
$headerAgencyLogo = $resolveImage(array(
    !empty($reportAgency['photo']) ? 'agency_thumb/'.$reportAgency['photo'] : '',
    !empty($reportAgency['photo']) ? $reportAgency['photo'] : '',
    !empty($frontLogo) ? $frontLogo : ''
));
$searchFormUrl = array('controller' => 'Leads', 'action' => 'manage');
$resetUrl = $searchFormUrl;
$leadInlineUpdateUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'updatestatus'));
$canInlineEdit = !empty($allowEditAction);
$leadInlineDateFormat = (isset($dpFormat) && trim((string)$dpFormat) !== '') ? trim((string)$dpFormat) : 'DD-MMM-YYYY';
$leadInlineDateTimeFormat = trim($leadInlineDateFormat.' hh:mm A');
?>
<script type="text/javascript">
var leadInlineUpdateUrl = '<?php echo $leadInlineUpdateUrl;?>';
var leadInlineCanEdit = <?php echo $canInlineEdit ? 'true' : 'false';?>;
var leadInlineDateFormat = '<?php echo addslashes($leadInlineDateFormat);?>';
var leadInlineDateTimeFormat = '<?php echo addslashes($leadInlineDateTimeFormat);?>';
var leadStatusMetaMap = <?php echo json_encode($statusMetaMap);?>;
var leadsAppRootPath = '<?php echo addslashes(rtrim($this->Html->url('/'), '/'));?>';

function normalizeLeadUrl(targetUrl)
{
    var url = jQuery.trim(String(targetUrl || ''));
    if(url === '')
    {
        return url;
    }
    if(/^https?:\/\//i.test(url) || url.indexOf('//') === 0)
    {
        return url;
    }
    if(url.charAt(0) !== '/')
    {
        url = '/' + url.replace(/^\/+/, '');
    }
    var appRoot = jQuery.trim(String(leadsAppRootPath || ''));
    if(appRoot !== '' && appRoot !== '/')
    {
        var escapedRoot = appRoot.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        var duplicateRootRegex = new RegExp('^(' + escapedRoot + '){2,}');
        if(duplicateRootRegex.test(url))
        {
            url = url.replace(duplicateRootRegex, appRoot);
        }
    }
    return url;
}

function setLeadInlineState($cell, text, state)
{
    var $state = $cell.find('.lead-inline-save-state');
    if(!$state.length)
    {
        return;
    }
    $state.removeClass('state-saving state-ok state-error').addClass(state ? ('state-' + state) : '').text(text || '');
    if(state === 'ok')
    {
        window.clearTimeout($state.data('clearTimer'));
        $state.data('clearTimer', window.setTimeout(function(){
            $state.removeClass('state-ok').text('');
        }, 1500));
    }
}

function resizeLeadRemarks(scope)
{
    var $scope = scope ? jQuery(scope) : jQuery(document);
    $scope.find('.lead-inline-remarks').each(function(){
        var el = this;
        var $el = jQuery(el);
        $el.css('height', 'auto');
        $el.css('height', Math.max(36, el.scrollHeight + 2) + 'px');
    });
}

function applyLeadInlinePickerRaw($picker, rawValue, rawFormat)
{
    var pickerApi = $picker.data('DateTimePicker');
    var $input = $picker.find('input');
    rawValue = jQuery.trim(String(rawValue || ''));
    if(!pickerApi)
    {
        return;
    }
    $picker.data('suppressInlineChange', 1);
    if(rawValue === '')
    {
        pickerApi.clear();
        $input.val('');
    }
    else if(typeof moment === 'function')
    {
        pickerApi.date(moment(rawValue, rawFormat));
    }
    else
    {
        pickerApi.date(rawValue);
    }
    $picker.attr('data-raw-value', rawValue);
    $input.data('saved-value', jQuery.trim(String($input.val() || '')));
    $picker.data('suppressInlineChange', 0);
}

function buildLeadInlineLastContactValue($picker)
{
    var $input = $picker.find('.lead-inline-last-contact-input');
    var inputValue = jQuery.trim(String($input.val() || ''));
    if(inputValue === '')
    {
        return '';
    }
    if(typeof moment === 'function')
    {
        var parsed = moment(inputValue, [leadInlineDateFormat, 'YYYY-MM-DD', 'DD-MM-YYYY', 'DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY/MM/DD'], true);
        if(parsed.isValid())
        {
            return parsed.format('YYYY-MM-DD');
        }
    }
    var pickerApi = $picker.data('DateTimePicker');
    if(pickerApi && typeof pickerApi.date === 'function')
    {
        var pickerDate = pickerApi.date();
        if(pickerDate && typeof pickerDate.format === 'function')
        {
            return pickerDate.format('YYYY-MM-DD');
        }
    }
    return inputValue;
}

function saveLeadInlineField($cell, payload, onSuccess, onError)
{
    if(!leadInlineCanEdit)
    {
        return;
    }
    var leadId = parseInt($cell.attr('data-lead-id'), 10) || 0;
    if(leadId <= 0 || $cell.data('inlineSaving'))
    {
        return;
    }
    payload = jQuery.extend({id: leadId}, payload || {});
    $cell.data('inlineSaving', 1);
    setLeadInlineState($cell, '<?php echo addslashes(__('Saving...'));?>', 'saving');
    jQuery.ajax({
        url: leadInlineUpdateUrl,
        type: 'POST',
        dataType: 'json',
        data: payload,
        success: function(resp){
            $cell.data('inlineSaving', 0);
            if(resp && resp.success)
            {
                setLeadInlineState($cell, '<?php echo addslashes(__('Saved'));?>', 'ok');
                if(typeof onSuccess === 'function')
                {
                    onSuccess(resp.lead || {});
                }
            }
            else
            {
                setLeadInlineState($cell, (resp && resp.message) ? resp.message : '<?php echo addslashes(__('Unable to update lead'));?>', 'error');
                if(typeof onError === 'function')
                {
                    onError(resp || {});
                }
            }
        },
        error: function(){
            $cell.data('inlineSaving', 0);
            setLeadInlineState($cell, '<?php echo addslashes(__('Unable to update lead'));?>', 'error');
            if(typeof onError === 'function')
            {
                onError({});
            }
        }
    });
}

function updateLeadStatusVisual($cell, statusId)
{
    statusId = parseInt(statusId, 10) || 0;
    var statusMeta = leadStatusMetaMap && leadStatusMetaMap[statusId] ? leadStatusMetaMap[statusId] : null;
    var statusName = statusMeta && statusMeta.name ? String(statusMeta.name) : '';
    var statusColor = statusMeta && statusMeta.color ? String(statusMeta.color) : '#337ab7';
    var $row = $cell.closest('tr');
    $row.find('.lead-name-status-chip').css('background-color', statusColor);
    $cell.find('.lead-inline-status-label').text(statusName);
    $cell.find('.lead-inline-status-select').css({backgroundColor: statusColor, color: '#ffffff', borderColor: statusColor});
}

function initLeadManageInlineEditors(scope)
{
    if(!leadInlineCanEdit || typeof jQuery.fn.datetimepicker !== 'function')
    {
        return;
    }
    var $scope = scope ? jQuery(scope) : jQuery(document);

    $scope.find('.lead-inline-last-contact-picker').each(function(){
        var $picker = jQuery(this);
        if($picker.data('pickerReady'))
        {
            return;
        }
        $picker.datetimepicker({
            locale: '<?php echo addslashes($configLanguage);?>',
            format: leadInlineDateFormat
        });
        applyLeadInlinePickerRaw($picker, $picker.attr('data-raw-value'), 'YYYY-MM-DD');
        $picker.on('dp.change.leadsInline', function(){
            if($picker.data('suppressInlineChange'))
            {
                return;
            }
            var $cell = $picker.closest('td');
            var $input = $picker.find('.lead-inline-last-contact-input');
            var currentValue = jQuery.trim(String($input.val() || ''));
            if(currentValue === String($input.data('saved-value') || ''))
            {
                return;
            }
            saveLeadInlineField($cell, {last_contact_date: currentValue}, function(lead){
                applyLeadInlinePickerRaw($picker, lead.last_contact_date || '', 'YYYY-MM-DD');
            });
        });
        $picker.data('pickerReady', 1);
    });

    $scope.find('.lead-inline-followup-picker').each(function(){
        var $picker = jQuery(this);
        if($picker.data('pickerReady'))
        {
            return;
        }
        $picker.datetimepicker({
            locale: '<?php echo addslashes($configLanguage);?>',
            format: leadInlineDateTimeFormat,
            sideBySide: true
        });
        applyLeadInlinePickerRaw($picker, $picker.attr('data-raw-value'), 'YYYY-MM-DD HH:mm:ss');
        $picker.on('dp.change.leadsInline', function(){
            if($picker.data('suppressInlineChange'))
            {
                return;
            }
            var $cell = $picker.closest('td');
            var $input = $picker.find('.lead-inline-followup-input');
            var currentValue = jQuery.trim(String($input.val() || ''));
            if(currentValue === String($input.data('saved-value') || ''))
            {
                return;
            }
            saveLeadInlineField($cell, {follow_up: currentValue}, function(lead){
                applyLeadInlinePickerRaw($picker, lead.follow_up || '', 'YYYY-MM-DD HH:mm:ss');
            });
        });
        $picker.data('pickerReady', 1);
    });
}

jQuery(document).ready(function(){
    if(jQuery('#lead_start_date').length && jQuery('#lead_end_date').length)
    {
        jQuery('#lead_start_date').datetimepicker({locale:'<?php echo $configLanguage;?>',format:'<?php echo $dpFormat;?>'});
        jQuery('#lead_end_date').datetimepicker({locale:'<?php echo $configLanguage;?>',format:'<?php echo $dpFormat;?>',useCurrent:false});
        jQuery('#lead_start_date').on('dp.change', function(e){
            jQuery('#lead_end_date').data('DateTimePicker').minDate(e.date);
        });
        jQuery('#lead_end_date').on('dp.change', function(e){
            jQuery('#lead_start_date').data('DateTimePicker').maxDate(e.date);
        });
    }
    initLeadManageInlineEditors('#resultDiv');
    resizeLeadRemarks('#resultDiv');
    jQuery(document).off('input.leadsRemarkSize', '.lead-inline-remarks').on('input.leadsRemarkSize', '.lead-inline-remarks', function(){
        resizeLeadRemarks(jQuery(this).closest('td'));
    });
    jQuery('.lead-inline-status-select').each(function(){
        var $select = jQuery(this);
        updateLeadStatusVisual($select.closest('td'), parseInt($select.val(), 10) || 0);
    });
    jQuery(document).off('blur.leadsInlineDate', '.lead-inline-last-contact-input').on('blur.leadsInlineDate', '.lead-inline-last-contact-input', function(){
        if(!leadInlineCanEdit)
        {
            return;
        }
        var $input = jQuery(this);
        var $picker = $input.closest('.lead-inline-last-contact-picker');
        if($picker.data('suppressInlineChange'))
        {
            return;
        }
        var currentValue = jQuery.trim(String($input.val() || ''));
        if(currentValue === String($input.data('saved-value') || ''))
        {
            return;
        }
        saveLeadInlineField($input.closest('td'), {last_contact_date: currentValue}, function(lead){
            applyLeadInlinePickerRaw($picker, lead.last_contact_date || '', 'YYYY-MM-DD');
        });
    });
    jQuery(document).off('blur.leadsInlineFollowUp', '.lead-inline-followup-input').on('blur.leadsInlineFollowUp', '.lead-inline-followup-input', function(){
        if(!leadInlineCanEdit)
        {
            return;
        }
        var $input = jQuery(this);
        var $picker = $input.closest('.lead-inline-followup-picker');
        if($picker.data('suppressInlineChange'))
        {
            return;
        }
        var currentValue = jQuery.trim(String($input.val() || ''));
        if(currentValue === String($input.data('saved-value') || ''))
        {
            return;
        }
        saveLeadInlineField($input.closest('td'), {follow_up: currentValue}, function(lead){
            applyLeadInlinePickerRaw($picker, lead.follow_up || '', 'YYYY-MM-DD HH:mm:ss');
        });
    });
    jQuery(document).off('blur.leadsInline change.leadsInline', '.lead-inline-remarks').on('blur.leadsInline change.leadsInline', '.lead-inline-remarks', function(){
        if(!leadInlineCanEdit)
        {
            return;
        }
        var $input = jQuery(this);
        var currentValue = jQuery.trim(String($input.val() || ''));
        if(currentValue === String($input.data('saved-value') || ''))
        {
            return;
        }
        saveLeadInlineField($input.closest('td'), {remarks: currentValue}, function(lead){
            var savedValue = lead && typeof lead.remarks !== 'undefined' ? String(lead.remarks) : currentValue;
            $input.val(savedValue);
            $input.data('saved-value', savedValue);
            $input.closest('td').attr('title', savedValue);
            resizeLeadRemarks($input.closest('td'));
        });
    });
    jQuery(document).off('change.leadsInlineStatus', '.lead-inline-status-select').on('change.leadsInlineStatus', '.lead-inline-status-select', function(){
        if(!leadInlineCanEdit)
        {
            return;
        }
        var $select = jQuery(this);
        var newStatusId = parseInt($select.val(), 10) || 0;
        var savedStatusId = parseInt($select.data('saved-value'), 10) || 0;
        var $cell = $select.closest('td');
        if(newStatusId <= 0 || newStatusId === savedStatusId)
        {
            return;
        }
        saveLeadInlineField($cell, {status_id: newStatusId}, function(lead){
            var appliedStatusId = lead && lead.status_id ? parseInt(lead.status_id, 10) || newStatusId : newStatusId;
            $select.val(String(appliedStatusId));
            $select.data('saved-value', appliedStatusId);
            updateLeadStatusVisual($cell, appliedStatusId);
        }, function(){
            $select.val(String(savedStatusId));
            updateLeadStatusVisual($cell, savedStatusId);
        });
    });
});

function openLeadMessagePopup(targetUrl, selectedIds)
{
    targetUrl = normalizeLeadUrl(targetUrl);
    selectedIds = jQuery.isArray(selectedIds) ? selectedIds : [];
    if(selectedIds.length === 0)
    {
        alert(getLocale('Please Select Any Record'));
        return false;
    }
    var popupWindow = null;
    try
    {
        popupWindow = window.open('about:blank', 'leadBulkMessagePopup', 'width=1100,height=760,scrollbars=yes,resizable=yes');
    }
    catch(err)
    {
        popupWindow = null;
    }
    var targetWindow = '_self';
    if(popupWindow && !popupWindow.closed)
    {
        targetWindow = 'leadBulkMessagePopup';
    }
    var formHtml = '<form method="post" action="' + targetUrl + '" target="' + targetWindow + '" style="display:none;">';
    for(var i = 0; i < selectedIds.length; i++)
    {
        formHtml += '<input type="hidden" name="data[Lead][id][]" value="' + selectedIds[i] + '">';
    }
    formHtml += '</form>';
    var $form = jQuery(formHtml);
    jQuery('body').append($form);
    $form.submit();
    window.setTimeout(function(){
        if(targetWindow === 'leadBulkMessagePopup')
        {
            try
            {
                popupWindow.focus();
            }
            catch(err){}
        }
        $form.remove();
    }, 150);
    return false;
}

function lead_bulk_message(targetUrl)
{
    var selectedIds = [];
    jQuery('.chkselect:checked').each(function(){
        selectedIds.push(jQuery(this).val());
    });
    return openLeadMessagePopup(targetUrl, selectedIds);
}

function send_lead_quick_notification(leadId, type, el)
{
    type = type || 'all';
    var $btn = jQuery(el);
    var origHtml = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> <?php echo __('Sending...');?>').css('pointer-events', 'none');

    var postUrl = '<?php echo $this->Html->url(array("controller" => "Leads", "action" => "send_notification"));?>/' + leadId + '/' + type;

    jQuery.ajax({
        url: postUrl,
        type: 'POST',
        dataType: 'json',
        success: function(resp){
            $btn.html(origHtml).css('pointer-events', '');
            if(resp && resp.success){
                var msg = resp.message || 'Notification dispatched successfully!';
                alert('✓ ' + msg);
            } else {
                var err = (resp && resp.message) ? resp.message : 'Could not dispatch notification.';
                alert('✗ ' + err);
            }
        },
        error: function(){
            $btn.html(origHtml).css('pointer-events', '');
            alert('Server or network error while dispatching notification.');
        }
    });
    return false;
}

function lead_bulk_send_notifications(type)
{
    type = type || 'all';
    var selectedIds = [];
    jQuery('.chkselect:checked').each(function(){
        var v = parseInt(jQuery(this).val(), 10);
        if(v > 0) selectedIds.push(v);
    });

    if(selectedIds.length === 0){
        alert('<?php echo __("Please select at least one lead from the table using checkboxes.");?>');
        return false;
    }

    var confirmMsg = '<?php echo __("Are you sure you want to send automated notifications for ");?>' + selectedIds.length + '<?php echo __(" selected lead(s) according to your saved notification settings (Agent Alert, Admin Alert, Client Email, SMS)?");?>';
    if(!confirm(confirmMsg)){
        return false;
    }

    var $btn = jQuery('#btnBulkLeadNotification');
    var origBtnHtml = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> <?php echo __("Sending Bulk Notifications...");?>').addClass('disabled').css('pointer-events', 'none');

    var bulkUrl = '<?php echo $this->Html->url(array("controller" => "Leads", "action" => "bulk_notifications"));?>/' + type;

    jQuery.ajax({
        url: bulkUrl,
        type: 'POST',
        data: {
            'data[Lead][id]': selectedIds,
            'selected_ids': selectedIds
        },
        dataType: 'json',
        success: function(resp){
            $btn.html(origBtnHtml).removeClass('disabled').css('pointer-events', '');
            if(resp && resp.success){
                var msg = resp.message || 'Bulk notifications processed successfully!';
                alert('✓ ' + msg);
                window.location.reload();
            } else {
                var errMsg = (resp && resp.message) ? resp.message : 'Bulk notification processing failed.';
                alert('✗ ' + errMsg);
            }
        },
        error: function(){
            $btn.html(origBtnHtml).removeClass('disabled').css('pointer-events', '');
            document.deleteallfrm.action = bulkUrl;
            document.deleteallfrm.submit();
        }
    });
    return false;
}

var leadsHtml2PdfLoader = null;
function ensureLeadsHtml2Pdf()
{
    if(typeof html2pdf !== 'undefined')
    {
        return jQuery.Deferred().resolve().promise();
    }
    if(leadsHtml2PdfLoader)
    {
        return leadsHtml2PdfLoader.promise();
    }
    leadsHtml2PdfLoader = jQuery.Deferred();
    var script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
    script.onload = function(){ leadsHtml2PdfLoader.resolve(); };
    script.onerror = function(){
        alert('Unable to load PDF library.');
        leadsHtml2PdfLoader.reject();
        leadsHtml2PdfLoader = null;
    };
    document.getElementsByTagName('head')[0].appendChild(script);
    return leadsHtml2PdfLoader.promise();
}

function leadsPdfStamp()
{
    var now = new Date();
    var pad = function(n){ return n < 10 ? '0' + n : n; };
    return now.getFullYear() + '' + pad(now.getMonth() + 1) + '' + pad(now.getDate()) + '_' + pad(now.getHours()) + '' + pad(now.getMinutes()) + '' + pad(now.getSeconds());
}

function exportLeadRecordsPdf()
{
    ensureLeadsHtml2Pdf().done(function(){
        var sourceElement = document.getElementById('printable_content');
        if(!sourceElement)
        {
            alert('No records found for PDF export.');
            return;
        }
        var exportNode = sourceElement.cloneNode(true);
        var $exportNode = jQuery(exportNode);
        $exportNode.find('.pbutton,.no-print').remove();
        var $wrapper = jQuery('<div id="leads-pdf-export-wrapper"></div>').css({
            position: 'fixed',
            left: '0',
            top: '0',
            width: '1400px',
            maxWidth: '1400px',
            background: '#fff',
            padding: '8px',
            opacity: '0.01',
            pointerEvents: 'none',
            zIndex: '-1'
        });
        $wrapper.append($exportNode);
        jQuery('body').append($wrapper);
        var opt = {
            margin: [0.20,0.20,0.20,0.20],
            filename: 'leads_' + leadsPdfStamp() + '.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
        };
        var worker = html2pdf().set(opt).from($exportNode[0]).save();
        var cleanup = function(){ $wrapper.remove(); };
        if(worker && typeof worker.then === 'function')
        {
            worker.then(function(){ cleanup(); }).catch(function(){ cleanup(); });
        }
        else
        {
            cleanup();
        }
    });
    return false;
}
</script>
<style type="text/css">
@page {
    size: landscape;
    margin: 8mm;
}
.leads-report-brand-header {
    width: 100%;
    border-bottom: 1px solid #e5e5e5;
    margin-bottom: 8px;
}
.leads-report-brand-logo-cell {
    width: 180px;
    vertical-align: middle;
}
.leads-report-brand-logo-cell img {
    max-height: 95px;
    width: auto;
}
.leads-report-brand-details {
    vertical-align: middle;
    text-align: center;
}
.leads-report-brand-details h2 {
    margin: 0 0 4px 0;
}
.leads-report-brand-line {
    margin: 0 auto;
    color: #333;
}
.leads-report-title {
    margin: 10px 0 12px 0;
    text-align: center;
}
.leads-report-title h3 {
    margin: 0;
    font-size: 30px;
    text-decoration: underline;
}
.leads-filter-summary {
    text-align: center;
    margin-bottom: 12px;
    font-size: 13px;
    color: #333;
}
.leads-filter-summary p {
    margin: 2px 0;
}
.leads-print-only-row,
.leads-print-only-block {
    display: none;
}
.leads-report-footer {
    margin-top: 14px;
    border-top: 1px solid #ddd;
    padding-top: 8px;
    text-align: center;
    font-size: 12px;
    color: #444;
}
.leads-table-wrap {
    border: 1px solid #e6e6e6;
    overflow-x: auto;
}
.leads-data-table {
    width: max-content;
    min-width: 100%;
    table-layout: auto;
    margin-bottom: 0;
}
.leads-data-table > thead > tr > th {
    background: #f7f9fb;
    font-weight: 600;
}
.leads-data-table > thead > tr > th,
.leads-data-table > tbody > tr > td {
    white-space: nowrap;
    vertical-align: middle !important;
    padding: 6px 8px !important;
    line-height: 1.2;
    font-size: 12px;
}
.leads-data-table > tbody > tr {
    height: auto;
}
.leads-data-table .btn-group,
.leads-data-table .input-group {
    white-space: nowrap;
}
.lead-name-status-chip {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 14px;
    color: #ffffff;
    font-weight: 600;
    line-height: 1.2;
    transition: all 0.15s ease;
}
.lead-name-link {
    text-decoration: none !important;
    display: inline-block;
}
.lead-name-link:hover .lead-name-status-chip {
    opacity: 0.92;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.22);
}
.lead-inline-cell {
    min-width: 150px;
    max-width: 260px;
    position: relative;
}
.lead-inline-cell .form-control {
    height: 30px;
    padding: 4px 8px;
    font-size: 12px;
    line-height: 1.2;
}
.lead-inline-followup-picker {
    position: relative !important;
    display: block !important;
    min-width: 180px;
    width: 100%;
}
.lead-inline-followup-picker .lead-inline-followup-input {
    display: block !important;
    width: 100% !important;
    height: 30px !important;
    padding: 4px 32px 4px 8px !important;
    font-size: 12px !important;
    line-height: 1.2 !important;
    border-radius: 4px !important;
    float: none !important;
    background-color: #ffffff !important;
}
.lead-inline-followup-picker .input-group-addon {
    position: absolute !important;
    right: 1px !important;
    top: 1px !important;
    bottom: 1px !important;
    width: 28px !important;
    height: 28px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: transparent !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    cursor: pointer !important;
    z-index: 5 !important;
    color: #ef4444 !important;
    pointer-events: auto !important;
    border-radius: 0 4px 4px 0 !important;
}
.lead-inline-followup-picker .input-group-addon .fa-calendar {
    font-size: 13px !important;
    color: #ef4444 !important;
    opacity: 0.9;
}
.lead-inline-followup-picker .input-group-addon:hover .fa-calendar {
    opacity: 1;
}
#lead_start_date, #lead_end_date {
    position: relative !important;
    display: block !important;
    width: 100% !important;
}
#lead_start_date input, #lead_end_date input {
    display: block !important;
    width: 100% !important;
    height: 34px !important;
    padding: 6px 32px 6px 10px !important;
    border-radius: 4px !important;
    float: none !important;
}
#lead_start_date .input-group-addon, #lead_end_date .input-group-addon {
    position: absolute !important;
    right: 1px !important;
    top: 1px !important;
    bottom: 1px !important;
    width: 30px !important;
    height: 32px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: transparent !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    cursor: pointer !important;
    z-index: 5 !important;
    color: #64748b !important;
}
.lead-inline-status-select {
    min-width: 150px;
}
.lead-remarks-cell {
    min-width: 280px;
    max-width: 440px;
    white-space: normal !important;
    vertical-align: top !important;
    word-break: break-word;
}
.lead-remarks-text {
    white-space: pre-wrap;
    line-height: 1.35;
    color: #1f2937;
    word-break: break-word;
}
.lead-inline-remarks {
    width: 100%;
    min-width: 260px;
    max-width: 420px;
    height: auto;
    min-height: 30px;
    max-height: none;
    overflow: hidden;
    resize: vertical;
    white-space: pre-wrap;
    line-height: 1.35;
}
.lead-remarks-cell .lead-inline-save-state {
    position: static;
    height: auto;
    min-height: 14px;
    margin-top: 4px;
    line-height: 14px;
}
.lead-inline-save-state {
    position: absolute;
    left: 8px;
    bottom: -10px;
    min-height: 0;
    height: 10px;
    margin-top: 0;
    font-size: 11px;
    font-weight: 600;
    line-height: 10px;
    pointer-events: none;
}
.lead-inline-save-state.state-saving {
    color: #b45309;
}
.lead-inline-save-state.state-ok {
    color: #15803d;
}
.lead-inline-save-state.state-error {
    color: #b91c1c;
}
@media print {
    .pbutton,
    .no-print {
        display: none !important;
    }
    .leads-print-only-row {
        display: table-row !important;
    }
    .leads-print-only-block {
        display: block !important;
    }
    body {
        background: #fff !important;
    }
}
</style>

<div id="resultDiv">
    <div class="page-title">
        <div class="title-env">
            <h1 class="title"><?php echo !empty($customPageTitle) ? h($customPageTitle) : __('Leads');?></h1>
        </div>
    </div>
    <div class="panel"><?php echo $this->Session->flash();?>
        <div class="panel-heading">
            <div class="btn-group">
                <?php $url = $this->Html->url(array('controller' => 'Leads')); ?>
                <?php echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add Lead Contact'), array('controller' => 'Leads', 'action' => 'leadadd'), array('escape' => false, 'class' => 'btn btn-success'));?>
                <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'), 'javascript:void(0);', array('name' => 'editallfrm', 'id' => 'editallfrm', 'onclick' => "check_perform_edit('$url');", 'escape' => false, 'class' => 'btn btn-warning'));?>
                <?php echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'), 'javascript:void(0);', array('name' => 'deleteallfrm', 'id' => 'deleteallfrm', 'onclick' => 'check_perform_delete();', 'escape' => false, 'class' => 'btn btn-danger'));?>
                <?php if(!empty($allowAddAction)){ ?>
                <?php echo $this->Html->link('<span class="fa fa-comment"></span>&nbsp;'.__('Bulk SMS'), 'javascript:void(0);', array('escape' => false, 'class' => 'btn btn-info', 'onclick' => "return lead_bulk_message('".$bulkSmsUrl."');"));?>
                <?php echo $this->Html->link('<span class="fa fa-envelope"></span>&nbsp;'.__('Bulk Email'), 'javascript:void(0);', array('escape' => false, 'class' => 'btn btn-primary', 'onclick' => "return lead_bulk_message('".$bulkEmailUrl."');"));?>
                <?php echo $this->Html->link('<span class="fa fa-clock-o"></span>&nbsp;'.__('Schedule SMS'), 'javascript:void(0);', array('escape' => false, 'class' => 'btn btn-success', 'onclick' => "return lead_bulk_message('".$bulkScheduleSmsUrl."');"));?>
                <?php echo $this->Html->link('<span class="fa fa-bell"></span>&nbsp;'.__('Send Bulk Notification'), 'javascript:void(0);', array('escape' => false, 'class' => 'btn btn-warning', 'id' => 'btnBulkLeadNotification', 'style' => 'background:#f59e0b;border-color:#d97706;color:#fff;font-weight:700;', 'onclick' => "return lead_bulk_send_notifications();"));?>
                <?php echo $this->Html->link('<span class="fa fa-envelope-o"></span>&nbsp;'.__('Notification Logs'), array('controller' => 'facebook_integrations', 'action' => 'notification_logs'), array('escape' => false, 'class' => 'btn btn-default', 'style' => 'font-weight:600;'));?>
                <?php } ?>
                <?php echo $this->Html->link('<span class="fa fa-print"></span>&nbsp;'.__('Print'), '#', array('id' => 'printme', 'escape' => false, 'class' => 'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-file-excel-o"></span>&nbsp;'.__('Export CSV'), $exportCsvUrl, array('escape' => false, 'class' => 'btn btn-info'));?>
                <?php echo $this->Html->link('<span class="fa fa-file-pdf-o"></span>&nbsp;'.__('Export PDF'), '#', array('onclick' => "return exportLeadRecordsPdf();", 'escape' => false, 'class' => 'btn btn-success'));?>
                <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('CRM Dashboard'), array('controller' => 'Leads', 'action' => 'dashboard'), array('escape' => false, 'class' => 'btn btn-primary'));?>
                <?php echo $this->Html->link('<span class="fa fa-columns"></span>&nbsp;'.__('Kanban'), array('controller' => 'Leads', 'action' => 'kanban'), array('escape' => false, 'class' => 'btn btn-info'));?>
                <?php echo $this->Html->link('<span class="fa fa-trophy"></span>&nbsp;'.__('Leaderboard'), array('controller' => 'Leads', 'action' => 'leaderboard'), array('escape' => false, 'class' => 'btn btn-warning'));?>
                <?php echo $this->Html->link('<span class="fa fa-upload"></span>&nbsp;'.__('Import Leads'), array('controller' => 'Leads', 'action' => 'import'), array('escape' => false, 'class' => 'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-facebook-square"></span>&nbsp;'.__('Facebook Leads'), array('controller' => 'Leads', 'action' => 'manage', '?' => array('lead_source' => 'facebook')), array('escape' => false, 'class' => 'btn btn-primary', 'style' => 'background:#1877f2;border-color:#166fe5;'));?>
            </div>
            <?php echo $this->Form->create('Lead', array('name' => 'searchfrm', 'url' => $searchFormUrl, 'novalidate' => true));?>
            <div class="row mrg no-print" style="margin-top:10px;">
                <label class="col-md-1 control-label"><strong><?php echo __('Filters');?></strong></label>
                <?php if($canUseAgencyFilters){?>
                <div class="col-md-2">
                    <?php echo $this->Form->select('project_id', $projectName, array('empty' => __('All Agency'), 'label' => false, 'class' => 'form-control', 'div' => false, 'required' => false, 'value' => $selectedProjectFilter));?>
                </div>
                <?php } ?>
                <?php if($canUseAgentFilters){ ?>
                <div class="col-md-2">
                    <?php echo $this->Form->select('user_id', $agentFilterOptions, array('empty' => __('All Agents'), 'label' => false, 'class' => 'form-control', 'div' => false, 'required' => false, 'value' => $selectedAgentFilter));?>
                </div>
                <?php } ?>
                <div class="col-md-2">
                    <?php echo $this->Form->select('period', $periodOptions, array('empty' => false, 'label' => false, 'class' => 'form-control', 'div' => false, 'required' => false, 'value' => $selectedPeriodFilter));?>
                </div>
                <div class="col-md-2">
                    <?php
                    $phoneRegionOptions = array(
                        'all' => __('All Phone Numbers'),
                        'overseas' => __('🌍 Overseas Numbers'),
                        'local' => __('🇵🇰 Local (Pakistan)')
                    );
                    echo $this->Form->select('phone_region', $phoneRegionOptions, array('empty' => false, 'label' => false, 'class' => 'form-control', 'div' => false, 'required' => false, 'value' => $selectedPhoneRegion));
                    ?>
                </div>
                <div class="col-md-2">
                    <div class="input-group date" id="lead_start_date">
                        <?php echo $this->Form->input('start_date', array('type' => 'text', 'label' => false, 'class' => 'form-control', 'div' => false, 'required' => false, 'value' => $selectedStartDateInput, 'placeholder' => __('Start Date')));?>
                        <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group date" id="lead_end_date">
                        <?php echo $this->Form->input('end_date', array('type' => 'text', 'label' => false, 'class' => 'form-control', 'div' => false, 'required' => false, 'value' => $selectedEndDateInput, 'placeholder' => __('End Date')));?>
                        <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                    </div>
                </div>
                <div class="col-md-1" style="white-space:nowrap;">
                    <button type="submit" class="btn btn-success btn-sm"><span class="fa fa-search"></span> <?php echo __('Search');?></button>
                    <?php echo $this->Form->hidden('isSearch');?>
                    <?php echo $this->Html->link('<span class="fa fa-refresh"></span>&nbsp;'.__('Reset'), $resetUrl, array('class' => 'btn btn-warning btn-sm', 'escape' => false));?>
                </div>
            </div>
            <?php echo $this->Form->end();?>
        </div>
        <?php
        echo $this->element('pagination', array('IsAction' => 'manage'));
        $pageParams = $this->Paginator->params();
        $limit = $pageParams['limit'];
        $page = $pageParams['page'];
        $serialNo = 1 * $limit * ($page - 1) + 1;
        ?>
        <?php echo $this->Form->create(array('name' => 'deleteallfrm', 'action' => 'deleteall'));?>
        <div id="printable_content">
            <?php echo $this->Html->css('print');?>
            <div class="panel-body">
                <table width="100%" class="table" style="border-top:0;">
                    <tr class="leads-report-brand-header leads-print-only-row">
                        <td class="leads-report-brand-logo-cell">
                            <?php if(strlen($headerAgencyLogo) > 0){ echo $this->Html->image($headerAgencyLogo, array('alt' => $headerAgencyName)); }?>
                        </td>
                        <td class="leads-report-brand-details">
                            <h2><?php echo h($headerAgencyName);?></h2>
                            <p class="leads-report-brand-line"><?php echo h($headerAgencyAddress);?></p>
                            <p class="leads-report-brand-line">
                                <?php
                                $headerContactParts = array();
                                if(strlen(trim($headerAgencyPhone)) > 0)$headerContactParts[] = __('Phone').': '.h($headerAgencyPhone);
                                if(strlen(trim($headerAgencyMobile)) > 0)$headerContactParts[] = __('Mobile').': '.h($headerAgencyMobile);
                                if(strlen(trim($headerAgencyEmail)) > 0)$headerContactParts[] = __('Email').': '.h($headerAgencyEmail);
                                echo implode(' | ', $headerContactParts);
                                ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <div class="leads-report-title leads-print-only-block">
                    <h3><?php echo __('Leads Report');?></h3>
                </div>
                <div class="leads-filter-summary leads-print-only-block">
                    <p><strong><?php echo __('Agency');?>:</strong> <?php echo h($selectedAgencyName);?> | <strong><?php echo __('Agent Name');?>:</strong> <?php echo h($selectedAgentName);?></p>
                    <p><strong><?php echo __('Selected Filters');?>:</strong> <?php echo h($selectedFilterSummary);?></p>
                </div>
                <div class="table-responsive leads-table-wrap">
                    <table class="table table-striped table-bordered leads-data-table">
                        <thead>
                            <tr data-lead-id="<?php echo (int)$id;?>">
                                <th class="pbutton"><?php echo $this->Form->checkbox('checkbox', array('value' => 'deleteall', 'name' => 'selectAll', 'label' => false, 'id' => 'selectAll', 'hiddenField' => false));?></th>
                                <th><?php echo $this->Paginator->sort('id', __('#'), array('direction' => 'desc'));?></th>
                                <th><?php echo $this->Paginator->sort('Client.name', __('Client Name'), array('direction' => 'asc'));?></th>
                                <th><?php echo $this->Paginator->sort('Client.fathername', __('Father / Guardian'), array('direction' => 'asc'));?></th>
                                <th><?php echo $this->Paginator->sort('Client.phone', __('SMS Mobile #'), array('direction' => 'asc'));?></th>
                                <th><?php echo $this->Paginator->sort('Client.email', __('Email'), array('direction' => 'asc'));?></th>
                                <?php if($showAssignedAgentColumn){ ?>
                                <th><?php echo __('Assigned To');?></th>
                                <th><?php echo __('User Level');?></th>
                                <?php } ?>
                                <th><?php echo $this->Paginator->sort('last_contact_date', __('Last Contact Date'), array('direction' => 'desc'));?></th>
                                <th><?php echo $this->Paginator->sort('follow_up', __('Follow Up'), array('direction' => 'asc'));?></th>
                                <th><?php echo __('Remarks');?></th>
                                <th><?php echo $this->Paginator->sort('status', __('Status'), array('direction' => 'asc'));?></th>
                                <th class="pbutton"><?php echo __('Action');?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($Lead as $post):
                            $id = $post['Lead']['id'];
                            $leadClientName = !empty($post['Client']['name']) ? (string)$post['Client']['name'] : '';
                            $leadClientMobile = !empty($post['Client']['mobile']) ? (string)$post['Client']['mobile'] : '';
                            $leadClientEmail = !empty($post['Client']['email']) ? (string)$post['Client']['email'] : '';
                            $leadMobileDisplay = strlen(trim((string)$post['Lead']['lead_mobile'])) > 0 ? $post['Lead']['lead_mobile'] : $leadClientMobile;
                            $leadCallNo = preg_replace('/[^0-9\+]/', '', (string)$leadMobileDisplay);
                            $leadDisplayName = strlen(trim((string)$post['Lead']['lead_name'])) > 0 ? $post['Lead']['lead_name'] : $leadClientName;
                            $leadStatusColor = !empty($post['Status']['color']) ? $post['Status']['color'] : '#337ab7';
                            $leadLastContactRaw = (!empty($post['Lead']['last_contact_date']) && $post['Lead']['last_contact_date'] !== '0000-00-00') ? $post['Lead']['last_contact_date'] : '';
                            $leadLastContactDisplay = $leadLastContactRaw !== '' ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, h($leadLastContactRaw)) : '';
                            $leadFollowUpRaw = (!empty($post['Lead']['follow_up']) && $post['Lead']['follow_up'] !== '0000-00-00 00:00:00') ? $post['Lead']['follow_up'] : '';
                            $leadFollowUpDisplay = $leadFollowUpRaw !== '' ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer, h($leadFollowUpRaw)) : '';
                            $leadRemark = trim((string)$post['Lead']['remarks']);
                            $leadRemarkRows = 2;
                            if($leadRemark !== '')
                            {
                                $leadRemarkRows = max(2, min(8, substr_count($leadRemark, "\n") + (int)ceil(strlen($leadRemark) / 62)));
                            }
                            ?>
                            <tr>
                                <td class="pbutton"><?php echo $this->Form->checkbox(false, array('value' => $post['Lead']['id'], 'name' => 'data[Lead][id][]', 'id' => "DeleteCheckbox$id", 'class' => 'chkselect'));?></td>
                                <td><?php echo $serialNo++;?></td>
                                <td>
                                    <?php
                                    echo $this->Html->link(
                                        '<span class="lead-name-status-chip" style="background-color: ' . h($leadStatusColor) . ';"><i class="fa fa-user" style="font-size:10px;margin-right:4px;opacity:0.85;"></i> ' . h($leadDisplayName) . '</span>',
                                        array('controller' => 'Leads', 'action' => 'leadview', $id),
                                        array('escape' => false, 'class' => 'lead-name-link', 'title' => __('View Lead Profile & History (Lead #%s)', $id))
                                    );
                                    
                                    $isFbSync = (
                                        (!empty($post['Lead']['lead_source']) && (stripos($post['Lead']['lead_source'], 'Facebook') !== false || stripos($post['Lead']['lead_source'], 'FB') !== false)) ||
                                        (!empty($post['Lead']['remarks']) && (stripos($post['Lead']['remarks'], 'Facebook') !== false || stripos($post['Lead']['remarks'], 'White Alpine') !== false || stripos($post['Lead']['remarks'], 'White ICE') !== false || stripos($post['Lead']['remarks'], 'Form ID') !== false))
                                    );
                                    if($isFbSync){
                                        echo '<span class="label label-primary" style="background:#1877f2;border:none;color:#fff;font-size:10px;font-weight:700;padding:3px 6px;border-radius:4px;display:inline-block;margin-left:4px;vertical-align:middle;box-shadow:0 2px 4px rgba(24,119,242,0.3);" title="' . __('Synced from Facebook Lead Ads') . '"><i class="fa fa-facebook-square" style="font-size:12px;margin-right:2px;vertical-align:middle;"></i> FB Auto Sync</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $leadFatherName = !empty($post['Client']['fathername']) ? trim((string)$post['Client']['fathername']) : '';
                                    $leadRelation = !empty($post['Client']['relation_type']) ? trim((string)$post['Client']['relation_type']) : '';
                                    if($leadFatherName !== ''){
                                        echo '<strong style="color:#1e293b;">' . h($leadFatherName) . '</strong>';
                                        if($leadRelation !== ''){
                                            echo '<small class="text-muted" style="display:block;font-size:11px;font-weight:600;">' . h($leadRelation) . '</small>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if(trim((string)$leadMobileDisplay) === '')
                                    {
                                        echo '-';
                                    }
                                    elseif(trim((string)$leadCallNo) !== '')
                                    {
                                        echo $this->Html->link(h($leadMobileDisplay), 'tel:'.$leadCallNo, array('escape' => false));
                                        echo ' <span class="wv-leadbot-inline-actions" style="display:inline-block;margin-left:4px;white-space:nowrap;vertical-align:middle;">';
                                        echo '<a href="javascript:void(0);" class="btn btn-xs js-wv-leadbot-modal-open" data-lead-id="'.(int)$id.'" data-lead-name="'.h($post['Lead']['lead_name']).'" data-lead-phone="'.h($leadCallNo).'" data-channel="whatsapp" title="'.__('1-Click WhatsApp via LeadBot').'" style="padding:1px 5px;background:#25D366;color:#fff;border-radius:3px;font-size:11px;margin-right:2px;border:none;"><i class="fa fa-whatsapp"></i></a>';
                                        echo '<a href="javascript:void(0);" class="btn btn-xs js-wv-leadbot-modal-open" data-lead-id="'.(int)$id.'" data-lead-name="'.h($post['Lead']['lead_name']).'" data-lead-phone="'.h($leadCallNo).'" data-channel="sms" title="'.__('1-Click SMS via Android Gateway').'" style="padding:1px 5px;background:#0284c7;color:#fff;border-radius:3px;font-size:11px;border:none;"><i class="fa fa-comment"></i></a>';
                                        echo '</span>';
                                    }
                                    else
                                    {
                                        echo h($leadMobileDisplay);
                                    }
                                    $reg = $detectRegion($leadMobileDisplay);
                                    if($reg && $reg['is_overseas']){
                                        echo '<br/><span class="badge" style="background:#4f46e5;color:#fff;font-size:10px;padding:2px 6px;margin-top:2px;display:inline-block;" title="Overseas Client ('.$reg['country'].')"><i class="fa fa-globe"></i> '.$reg['badge'].'</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo h(strlen(trim((string)$post['Lead']['lead_email'])) > 0 ? $post['Lead']['lead_email'] : $leadClientEmail);?></td>
                                <?php if($showAssignedAgentColumn){ ?>
                                <td><?php
                                    $assignedAgentId = !empty($post['Lead']['user_id']) ? (int)$post['Lead']['user_id'] : (!empty($post['Client']['user_id']) ? (int)$post['Client']['user_id'] : 0);
                                    if($assignedAgentId > 0 && isset($assignedAgentMap[$assignedAgentId]) && trim((string)$assignedAgentMap[$assignedAgentId]) !== '')
                                    {
                                        echo '<span class="label label-info" style="font-weight:700;">' . h($assignedAgentMap[$assignedAgentId]) . '</span>';
                                    }
                                    else
                                    {
                                        echo '<span class="label label-default" style="background:#e2e8f0;color:#64748b;font-weight:700;">Unassigned</span>';
                                    }
                                ?></td>
                                <td><?php
                                    if($assignedAgentId > 0 && isset($assignedAgentRoleMap[$assignedAgentId]) && trim((string)$assignedAgentRoleMap[$assignedAgentId]) !== '')
                                    {
                                        echo h($assignedAgentRoleMap[$assignedAgentId]);
                                    }
                                    else
                                    {
                                        echo '-';
                                    }
                                ?></td>
                                <?php } ?>
                                <td class="lead-inline-cell" data-lead-id="<?php echo (int)$id;?>">
                                    <?php echo $leadLastContactDisplay !== '' ? h($leadLastContactDisplay) : '-'; ?>
                                </td>
                                <td class="lead-inline-cell" data-lead-id="<?php echo (int)$id;?>">
                                    <?php if($canInlineEdit){ ?>
                                    <div class="input-group date lead-inline-followup-picker" data-raw-value="<?php echo h($leadFollowUpRaw);?>">
                                        <input type="text" class="form-control input-sm lead-inline-followup-input" value="<?php echo h($leadFollowUpDisplay);?>" placeholder="<?php echo __('Select Follow Up');?>">
                                        <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                                    </div>
                                    <div class="lead-inline-save-state"></div>
                                    <?php } else {
                                        echo h($leadFollowUpDisplay);
                                    } ?>
                                </td>
                                <td class="lead-inline-cell lead-remarks-cell" data-lead-id="<?php echo (int)$id;?>" title="<?php echo h($leadRemark);?>">
                                    <?php if($canInlineEdit){ ?>
                                    <textarea class="form-control input-sm lead-inline-remarks" rows="<?php echo (int)$leadRemarkRows;?>" data-saved-value="<?php echo h($leadRemark);?>"><?php echo h($leadRemark);?></textarea>
                                    <div class="lead-inline-save-state"></div>
                                    <?php } else {
                                        echo $leadRemark !== '' ? '<div class="lead-remarks-text">'.nl2br(h($leadRemark)).'</div>' : '-';
                                    } ?>
                                </td>
                                <td class="lead-inline-cell" data-lead-id="<?php echo (int)$id;?>">
                                    <?php if($canInlineEdit){ ?>
                                    <select class="form-control input-sm lead-inline-status-select" data-saved-value="<?php echo (int)$post['Lead']['status_id'];?>">
                                        <?php foreach($statusOptions as $statusId => $statusName){ ?>
                                        <option value="<?php echo (int)$statusId;?>" <?php if((int)$statusId === (int)$post['Lead']['status_id']){ echo 'selected="selected"'; }?>><?php echo h($statusName);?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="lead-inline-save-state"></div>
                                    <?php } else { ?>
                                    <span class="lead-inline-status-label" style="background-color: <?php echo h($post['Status']['color']);?>;color: #ffffff;padding: 5px;"><?php echo h(__($post['Status']['name']));?></span>
                                    <?php } ?>
                                </td>
                                <td class="pbutton">
                                    <?php
                                        $hasRowAction = !empty($allowViewAction) || !empty($allowEditAction) || !empty($allowDeleteAction) || !empty($allowAddAction);
                                    ?>
                                    <?php if($hasRowAction){ ?>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">
                                            <?php echo __('Action');?> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <?php if(!empty($allowViewAction)){ ?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-arrows-alt"></span>&nbsp;'.__('View'), array('controller' => 'Leads', 'action' => 'leadview', $id), array('escape' => false));?></li>
                                            <li><?php echo $this->Html->link('<span class="fa fa-file-text-o"></span>&nbsp;'.__('Digital Quotation'), array('controller' => 'Leads', 'action' => 'quotation', $id), array('escape' => false));?></li>
                                            <li><?php echo $this->Html->link('<span class="fa fa-history"></span>&nbsp;'.__('History'), array('controller' => 'Leads', 'action' => 'history', $id), array('escape' => false));?></li>
                                            <?php } ?>
                                            <?php if(!empty($allowEditAction)){ ?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'), array('controller' => 'Leads', 'action' => 'leadedit', $id), array('escape' => false));?></li>
                                            <?php } ?>
                                            <?php if(!empty($allowAddAction)){ ?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-comment"></span>&nbsp;'.__('Send SMS'), array('controller' => 'Leads', 'action' => 'leadbulkmessage', 'sms', $id), array('escape' => false));?></li>
                                            <li><?php echo $this->Html->link('<span class="fa fa-envelope"></span>&nbsp;'.__('Send Email'), array('controller' => 'Leads', 'action' => 'leadbulkmessage', 'email', $id), array('escape' => false));?></li>
                                            <li><?php echo $this->Html->link('<span class="fa fa-clock-o"></span>&nbsp;'.__('Schedule SMS'), array('controller' => 'Leads', 'action' => 'schedulesms', $id), array('escape' => false));?></li>
                                            <li class="divider" style="margin:4px 0;"></li>
                                            <li class="dropdown-header" style="font-size:10px;text-transform:uppercase;color:#16a34a;padding:3px 15px;font-weight:700;"><i class="fa fa-bolt"></i> <?php echo __('LeadBot CRM Gateway');?></li>
                                            <li><a href="javascript:void(0);" class="js-wv-leadbot-modal-open" data-lead-id="<?php echo (int)$id;?>" data-lead-name="<?php echo h($post['Lead']['lead_name']);?>" data-lead-phone="<?php echo h($leadCallNo);?>" data-channel="whatsapp"><span class="fa fa-whatsapp text-success"></span>&nbsp;<?php echo __('Send WhatsApp (LeadBot)');?></a></li>
                                            <li><a href="javascript:void(0);" class="js-wv-leadbot-modal-open" data-lead-id="<?php echo (int)$id;?>" data-lead-name="<?php echo h($post['Lead']['lead_name']);?>" data-lead-phone="<?php echo h($leadCallNo);?>" data-channel="sms"><span class="fa fa-comment text-info"></span>&nbsp;<?php echo __('Send SMS (Android Gateway)');?></a></li>
                                            <li class="divider" style="margin:4px 0;"></li>
                                            <li class="dropdown-header" style="font-size:10px;text-transform:uppercase;color:#94a3b8;padding:3px 15px;font-weight:700;"><i class="fa fa-bell"></i> <?php echo __('Saved Notifications');?></li>
                                            <li><a href="javascript:void(0);" onclick="return send_lead_quick_notification(<?php echo (int)$id;?>, 'email', this);" title="<?php echo __('Send Email notification based on saved settings');?>"><span class="fa fa-envelope text-primary"></span>&nbsp;<?php echo __('Send Email Notification');?></a></li>
                                            <li><a href="javascript:void(0);" onclick="return send_lead_quick_notification(<?php echo (int)$id;?>, 'sms', this);" title="<?php echo __('Send SMS notification based on saved settings');?>"><span class="fa fa-comment text-success"></span>&nbsp;<?php echo __('Send SMS Notification');?></a></li>
                                            <li><a href="javascript:void(0);" onclick="return send_lead_quick_notification(<?php echo (int)$id;?>, 'all', this);" title="<?php echo __('Send all notifications (Email + SMS) based on saved settings');?>"><span class="fa fa-paper-plane text-warning"></span>&nbsp;<strong><?php echo __('Send All (Email + SMS)');?></strong></a></li>
                                            <?php } ?>
                                            <?php if(!empty($allowDeleteAction)){ ?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'), '#', array('onclick' => "check_perform_sdelete('$id');", 'escape' => false));?></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                    <?php } else { echo '-'; } ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php unset($post); ?>
                        </tbody>
                    </table>
                </div>
                <div class="leads-report-footer">
                    <strong><?php echo __('Dated / Time');?>:</strong> <?php echo date('d-M-Y h:i:s A');?>
                </div>
            </div>
        </div>
        <?php echo $this->Form->end();?>
        <?php echo $this->element('pagination', array('IsAction' => 'manage'));?>
    </div>
</div>

<!-- LeadBot CRM Gateway Quick Dispatch Modal -->
<div class="modal fade" id="wvLeadbotDispatchModal" tabindex="-1" role="dialog" aria-labelledby="wvLeadbotModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width:550px;">
        <div class="modal-content" style="border-radius:8px;box-shadow:0 12px 30px rgba(0,0,0,0.25);">
            <div class="modal-header" style="background:#0f172a;color:#fff;border-radius:7px 7px 0 0;padding:12px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#fff;opacity:0.8;">&times;</button>
                <h4 class="modal-title" id="wvLeadbotModalLabel" style="font-size:16px;font-weight:700;">
                    <i class="fa fa-bolt text-warning"></i> <?php echo __('LeadBot Gateway Dispatch');?>
                </h4>
            </div>
            <div class="modal-body" style="padding:20px;">
                <!-- Recipient Info Banner -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 14px;margin-bottom:15px;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <strong id="wvLbModalLeadName" style="color:#0f172a;font-size:14px;">-</strong><br>
                        <span id="wvLbModalLeadPhone" class="text-muted" style="font-size:12px;font-weight:600;"><i class="fa fa-phone"></i> -</span>
                    </div>
                    <div id="wvLbModalScopeBadge">
                        <span class="label label-info" style="font-size:10px;"><?php echo __('Resolving Gateway...');?></span>
                    </div>
                </div>

                <!-- Channel Selector Pills -->
                <div class="form-group">
                    <label style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;"><?php echo __('Select Channel:');?></label>
                    <div class="btn-group btn-group-justified" role="group">
                        <a href="javascript:void(0);" class="btn btn-default js-wv-channel-btn active" data-channel="whatsapp" style="font-weight:700;">
                            <i class="fa fa-whatsapp text-success" style="font-size:15px;"></i> WhatsApp
                            <span id="wvLbWaToggleBadge" class="label label-success" style="margin-left:5px;font-size:9px;">ON</span>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-default js-wv-channel-btn" data-channel="sms" style="font-weight:700;">
                            <i class="fa fa-comment text-info" style="font-size:15px;"></i> Android SMS
                            <span id="wvLbSmsToggleBadge" class="label label-success" style="margin-left:5px;font-size:9px;">ON</span>
                        </a>
                    </div>
                </div>

                <!-- Channel OFF Alert Banner (Shown if current channel is toggled OFF) -->
                <div id="wvLbChannelOffAlert" class="alert alert-warning" style="display:none;padding:10px 14px;font-size:12px;margin-bottom:15px;">
                    <i class="fa fa-exclamation-triangle"></i> <strong><?php echo __('Notice:');?></strong> <span id="wvLbChannelOffText"><?php echo __('This channel is toggled OFF.');?></span>
                    <a href="<?php echo $this->Html->url(array('controller'=>'LeadbotGateways','action'=>'index','?'=>array('tab'=>'settings')));?>" target="_blank" class="alert-link" style="margin-left:5px;"><?php echo __('Open Gateway Settings');?> &rarr;</a>
                </div>

                <!-- Template Selector -->
                <div class="form-group">
                    <label style="font-size:12px;font-weight:700;color:#334155;"><?php echo __('Message Template / Trigger:');?></label>
                    <select id="wvLbTemplateSelect" class="form-control input-sm">
                        <option value="client_welcome"><?php echo __('Client Welcome Message');?></option>
                        <option value="followup_reminder"><?php echo __('Upcoming Follow-up Reminder');?></option>
                        <option value="site_visit_scheduled"><?php echo __('Site Visit & Property Tour Confirmed');?></option>
                        <option value="lead_assignment"><?php echo __('New Lead Inquiry Notification');?></option>
                        <option value="custom"><?php echo __('Custom Message');?></option>
                    </select>
                </div>

                <!-- Message Textarea -->
                <div class="form-group">
                    <label style="font-size:12px;font-weight:700;color:#334155;"><?php echo __('Message Content:');?></label>
                    <textarea id="wvLbMessageText" class="form-control" rows="4" style="resize:vertical;font-size:13px;"></textarea>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                        <small class="text-muted"><?php echo __('Placeholders: {{client_name}}, {{agent_name}}, {{project_name}}');?></small>
                        <small id="wvLbCharCounter" class="text-muted">0 chars</small>
                    </div>
                </div>

                <!-- Inline Result Status Box -->
                <div id="wvLbResultBox" style="display:none;border-radius:5px;padding:10px 14px;font-size:12px;margin-top:12px;"></div>
            </div>
            <div class="modal-footer" style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:12px 20px;">
                <input type="hidden" id="wvLbCurrentLeadId" value="0">
                <input type="hidden" id="wvLbCurrentChannel" value="whatsapp">
                <a id="wvLbWaWebBtn" href="#" target="_blank" class="btn btn-default btn-sm pull-left" style="display:none;">
                    <i class="fa fa-external-link"></i> <?php echo __('Open in Web WhatsApp');?>
                </a>
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal"><?php echo __('Cancel');?></button>
                <button type="button" id="wvLbSendBtn" class="btn btn-primary btn-sm" style="font-weight:700;">
                    <i class="fa fa-paper-plane"></i> <span id="wvLbSendBtnText"><?php echo __('Send Message Now');?></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function($){
    var leadbotConfig = {
        has_api_key: false,
        auto_send_whatsapp: true,
        auto_send_sms: true,
        selected_wa_session: '',
        selected_sms_device: '',
        is_agent_specific: false,
        resolved_scope: 'agency',
        templates: {}
    };

    function loadLeadbotTemplates(){
        $.ajax({
            url: '<?php echo $this->Html->url(array('controller'=>'Leads','action'=>'leadbot_templates_ajax'));?>',
            type: 'GET',
            dataType: 'json'
        }).done(function(resp){
            if(resp && resp.success){
                leadbotConfig = resp;
                $('#wvLbWaToggleBadge').removeClass('label-success label-danger').addClass(resp.auto_send_whatsapp ? 'label-success' : 'label-danger').text(resp.auto_send_whatsapp ? 'ON' : 'OFF');
                $('#wvLbSmsToggleBadge').removeClass('label-success label-danger').addClass(resp.auto_send_sms ? 'label-success' : 'label-danger').text(resp.auto_send_sms ? 'ON' : 'OFF');
                
                var scopeText = resp.is_agent_specific ? 'Personal Agent Gateway' : (resp.resolved_scope === 'agency' ? 'Agency Default Gateway' : 'Global Hub');
                $('#wvLbModalScopeBadge').html('<span class="label ' + (resp.is_agent_specific ? 'label-success' : 'label-info') + '" style="font-size:10px;"><i class="fa fa-shield"></i> ' + scopeText + '</span>');
            }
        });
    }

    function updateTemplateText(){
        var ch = $('#wvLbCurrentChannel').val() || 'whatsapp';
        var tKey = $('#wvLbTemplateSelect').val();
        var clientName = $('#wvLbModalLeadName').text() || 'Valued Client';

        if(tKey === 'custom'){
            $('#wvLbMessageText').val('').focus();
            $('#wvLbCharCounter').text('0 chars');
            return;
        }

        var tData = (leadbotConfig.templates && leadbotConfig.templates[tKey]) ? leadbotConfig.templates[tKey] : null;
        var text = '';
        if(tData){
            text = (ch === 'whatsapp') ? (tData.whatsapp_template || '') : (tData.sms_template || '');
        }

        if(!text){
            if(tKey === 'client_welcome'){
                text = 'Hello ' + clientName + ', thank you for contacting White Villas. We have received your inquiry and our property advisor will assist you with plot details shortly.';
            } else if(tKey === 'followup_reminder'){
                text = 'Dear ' + clientName + ', this is a gentle reminder regarding our scheduled discussion about White Villas properties. Please let us know a convenient time to speak.';
            } else if(tKey === 'site_visit_scheduled'){
                text = 'Dear ' + clientName + ', your site visit to White Villas has been scheduled. Our team looks forward to welcoming you at the project office.';
            } else {
                text = 'Hello ' + clientName + ', notification update regarding your inquiry at White Villas.';
            }
        } else {
            text = text.replace(/\{\{client_name\}\}/g, clientName).replace(/\{\{lead_name\}\}/g, clientName);
        }

        $('#wvLbMessageText').val(text);
        $('#wvLbCharCounter').text(text.length + ' chars');
    }

    function checkChannelToggleStatus(){
        var ch = $('#wvLbCurrentChannel').val() || 'whatsapp';
        var isOff = (ch === 'whatsapp') ? !leadbotConfig.auto_send_whatsapp : !leadbotConfig.auto_send_sms;
        
        if(isOff){
            $('#wvLbChannelOffText').text((ch === 'whatsapp' ? 'WhatsApp messaging' : 'SMS messaging') + ' is toggled OFF in your Gateway settings.');
            $('#wvLbChannelOffAlert').slideDown(150);
            $('#wvLbSendBtn').prop('disabled', true);
        } else {
            $('#wvLbChannelOffAlert').slideUp(150);
            $('#wvLbSendBtn').prop('disabled', false);
        }
    }

    $(document).on('click', '.js-wv-leadbot-modal-open', function(e){
        e.preventDefault();
        var btn = $(this);
        var leadId = btn.data('lead-id');
        var leadName = btn.data('lead-name') || 'Lead Client';
        var leadPhone = btn.data('lead-phone') || '';
        var channel = btn.data('channel') || 'whatsapp';

        $('#wvLbCurrentLeadId').val(leadId);
        $('#wvLbModalLeadName').text(leadName);
        $('#wvLbModalLeadPhone').html('<i class="fa fa-phone"></i> ' + (leadPhone || '-'));
        $('#wvLbResultBox').hide().removeClass('alert-success alert-danger');
        $('#wvLbWaWebBtn').hide();

        // Set channel
        $('#wvLbCurrentChannel').val(channel);
        $('.js-wv-channel-btn').removeClass('active btn-primary').addClass('btn-default');
        $('.js-wv-channel-btn[data-channel="' + channel + '"]').addClass('active btn-primary').removeClass('btn-default');

        loadLeadbotTemplates();
        setTimeout(function(){
            updateTemplateText();
            checkChannelToggleStatus();
        }, 150);

        $('#wvLeadbotDispatchModal').modal('show');
    });

    $(document).on('click', '.js-wv-channel-btn', function(){
        var ch = $(this).data('channel');
        $('#wvLbCurrentChannel').val(ch);
        $('.js-wv-channel-btn').removeClass('active btn-primary').addClass('btn-default');
        $(this).addClass('active btn-primary').removeClass('btn-default');
        updateTemplateText();
        checkChannelToggleStatus();
    });

    $('#wvLbTemplateSelect').on('change', function(){
        updateTemplateText();
    });

    $('#wvLbMessageText').on('input', function(){
        $('#wvLbCharCounter').text($(this).val().length + ' chars');
    });

    $('#wvLbSendBtn').on('click', function(){
        var btn = $(this);
        var leadId = $('#wvLbCurrentLeadId').val();
        var channel = $('#wvLbCurrentChannel').val();
        var message = $('#wvLbMessageText').val();
        var eventKey = $('#wvLbTemplateSelect').val();

        if(!message || !$.trim(message)){
            alert('<?php echo __("Please enter message content.");?>');
            return;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo __("Sending...");?>');
        $('#wvLbResultBox').hide();

        $.ajax({
            url: '<?php echo $this->Html->url(array('controller'=>'Leads','action'=>'quick_leadbot_dispatch'));?>',
            type: 'POST',
            dataType: 'json',
            data: {
                lead_id: leadId,
                channel: channel,
                message: message,
                event_key: eventKey
            }
        }).done(function(res){
            btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?php echo __("Send Message Now");?>');
            var isOk = !!(res && res.success);
            var box = $('#wvLbResultBox');
            box.removeClass('alert-success alert-danger').addClass(isOk ? 'alert alert-success' : 'alert alert-danger');
            box.html('<i class="fa ' + (isOk ? 'fa-check-circle' : 'fa-times-circle') + '"></i> ' + (res && res.message ? res.message : (isOk ? 'Sent successfully' : 'Failed to send'))).fadeIn(150);
            
            if(isOk && res.url && channel === 'whatsapp'){
                $('#wvLbWaWebBtn').attr('href', res.url).fadeIn(150);
            }
        }).fail(function(xhr){
            btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?php echo __("Send Message Now");?>');
            $('#wvLbResultBox').removeClass('alert-success alert-danger').addClass('alert alert-danger').html('<i class="fa fa-times-circle"></i> <?php echo __("Server or network error occurred.");?>').fadeIn(150);
        });
    });
})(jQuery);
</script>
