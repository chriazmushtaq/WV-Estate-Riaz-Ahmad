<?php
echo $this->Html->script('/design800/js/plugins/fullcalendar/js/fullcalendar.min.js');
echo $this->Html->script('/design800/js/plugins/fullcalendar/js/lang-all.js');
echo $this->Html->css('/design800/js/plugins/fullcalendar/css/fullcalendar.min.css');
echo $this->Html->css('select2/select2');
echo $this->Html->css('select2/select2-bootstrap');
echo $this->Html->script('select2.min');

$dateArr = array();
$calendarDateFormat = (isset($dtFormat) && trim((string)$dtFormat) !== '') ? trim((string)$dtFormat) : 'd-M-Y';
$leadViewBaseUrl = rtrim($this->Html->url(array('controller' => 'Leads', 'action' => 'leadview')), '/');
foreach($Schedule as $value):
    if($value['Calendar']['is_repeat'])
    {
        if($value['Calendar']['repeat_type'] == 'WEE')
        {
            $dueDays = '1 week';
            $start = $value['Calendar']['start_date'];
            $end = $value['Calendar']['end_date'];
            for($i = 0; $i < $value['Calendar']['repeat_no']; $i++)
            {
                if($value['Calendar']['end_date'] != null)
                {
                    $dateArr[] = array('id' => $value['Calendar']['id'], 'start' => $start, 'end' => $end, 'color' => $value['Calendar']['color'], 'title' => $value['Calendar']['message_type'], 'tip' => $value['Calendar']['comments']);
                    $dateArr[] = array('id' => $value['Calendar']['id'], 'start' => $start, 'end' => $end, 'color' => $value['Calendar']['color'], 'title' => $value['User']['name'], 'tip' => $value['User']['name']);
                    $start = date('Y-m-d H:i:s', strtotime($start . "+$dueDays"));
                    $end = date('Y-m-d H:i:s', strtotime($end . "+$dueDays"));
                }
                else
                {
                    $dateArr[] = array('id' => $value['Calendar']['id'], 'start' => $start, 'color' => $value['Calendar']['color'], 'title' => $value['Calendar']['message_type'], 'tip' => $value['Calendar']['comments']);
                    $dateArr[] = array('id' => $value['Calendar']['id'], 'start' => $start, 'end' => $end, 'color' => $value['Calendar']['color'], 'title' => $value['User']['name'], 'tip' => $value['User']['name']);
                    $start = date('Y-m-d H:i:s', strtotime($start . "+$dueDays"));
                }
            }
        }
        elseif($value['Calendar']['repeat_type'] == 'MON')
        {
            $dueDays = '1 month';
            $start = $value['Calendar']['start_date'];
            $end = $value['Calendar']['end_date'];
            for($i = 0; $i < $value['Calendar']['repeat_no']; $i++)
            {
                if($value['Calendar']['end_date'] != null)
                {
                    $dateArr[] = array('id' => $value['Calendar']['id'], 'start' => $start, 'end' => $end, 'color' => $value['Calendar']['color'], 'title' => $value['Calendar']['message_type'], 'tip' => $value['Calendar']['comments']);
                    $dateArr[] = array('id' => $value['Calendar']['id'], 'start' => $start, 'end' => $end, 'color' => $value['Calendar']['color'], 'title' => $value['User']['name'], 'tip' => $value['User']['name']);
                    $start = date('Y-m-d H:i:s', strtotime($start . "+$dueDays"));
                    $end = date('Y-m-d H:i:s', strtotime($end . "+$dueDays"));
                }
                else
                {
                    $dateArr[] = array('id' => $value['Calendar']['id'], 'start' => $start, 'color' => $value['Calendar']['color'], 'title' => $value['Calendar']['message_type'], 'tip' => $value['Calendar']['comments']);
                    $dateArr[] = array('id' => $value['Calendar']['id'], 'start' => $start, 'end' => $end, 'color' => $value['Calendar']['color'], 'title' => $value['User']['name'], 'tip' => $value['User']['name']);
                    $start = date('Y-m-d H:i:s', strtotime($start . "+$dueDays"));
                }
            }
        }
        elseif($value['Calendar']['repeat_type'] == 'ANU')
        {
            $dueDays = '1 year';
            $start = $value['Calendar']['start_date'];
            $end = $value['Calendar']['end_date'];
            for($i = 0; $i < $value['Calendar']['repeat_no']; $i++)
            {
                if($value['Calendar']['end_date'] != null)
                {
                    $dateArr[] = array('id' => $value['Calendar']['id'], 'start' => $start, 'end' => $end, 'color' => $value['Calendar']['color'], 'title' => $value['Calendar']['message_type'], 'tip' => $value['Calendar']['comments']);
                    $dateArr[] = array('id' => $value['Calendar']['id'], 'start' => $start, 'end' => $end, 'color' => $value['Calendar']['color'], 'title' => $value['User']['name'], 'tip' => $value['User']['name']);
                    $start = date('Y-m-d H:i:s', strtotime($start . "+$dueDays"));
                    $end = date('Y-m-d H:i:s', strtotime($end . "+$dueDays"));
                }
                else
                {
                    $dateArr[] = array('id' => $value['Calendar']['id'], 'start' => $start, 'color' => $value['Calendar']['color'], 'title' => $value['Calendar']['message_type'], 'tip' => $value['Calendar']['comments']);
                    $dateArr[] = array('id' => $value['Calendar']['id'], 'start' => $start, 'end' => $end, 'color' => $value['Calendar']['color'], 'title' => $value['User']['name'], 'tip' => $value['User']['name']);
                    $start = date('Y-m-d H:i:s', strtotime($start . "+$dueDays"));
                }
            }
        }
    }
    elseif($value['Calendar']['end_date'] != null)
    {
        $dateArr[] = array('start' => $value['Calendar']['start_date'], 'end' => $value['Calendar']['end_date'], 'color' => $value['Calendar']['color'], 'title' => $value['Calendar']['message_type'], 'tip' => $value['Calendar']['comments']);
        $dateArr[] = array('start' => $value['Calendar']['start_date'], 'end' => $value['Calendar']['end_date'], 'color' => $value['Calendar']['color'], 'title' => $value['User']['name'], 'tip' => $value['User']['name']);
    }
    else
    {
        $dateArr[] = array('start' => $value['Calendar']['start_date'], 'color' => $value['Calendar']['color'], 'title' => $value['Calendar']['message_type'], 'tip' => $value['Calendar']['comments']);
        $dateArr[] = array('start' => $value['Calendar']['start_date'], 'color' => $value['Calendar']['color'], 'title' => $value['User']['name'], 'tip' => $value['User']['name']);
    }
endforeach;
unset($value);
foreach($leads as $value):
    $leadId = !empty($value['Lead']['id']) ? (int)$value['Lead']['id'] : 0;
    $leadTitle = !empty($value['Client']['name']) ? $value['Client']['name'] : (!empty($value['Lead']['lead_name']) ? $value['Lead']['lead_name'] : __('Lead Follow Up'));
    $followUpText = '';
    if(!empty($value['Lead']['follow_up']) && $value['Lead']['follow_up'] !== '0000-00-00 00:00:00')
    {
        $followUpText = date($calendarDateFormat . ' h:i A', strtotime($value['Lead']['follow_up']));
    }
    $leadRemarks = trim((string)$value['Lead']['remarks']);
    $tipHtml = '';
    if($followUpText !== '')
    {
        $tipHtml .= '<div><strong>' . h(__('Follow Up')) . ':</strong> ' . h($followUpText) . '</div>';
    }
    if($leadRemarks !== '')
    {
        $tipHtml .= '<div><strong>' . h(__('Remarks')) . ':</strong> ' . h($leadRemarks) . '</div>';
    }
    $leadEvent = array(
        'start' => $value['Lead']['follow_up'],
        'color' => $value['Status']['color'],
        'title' => $leadTitle,
        'tip' => $tipHtml
    );
    if($leadId > 0)
    {
        $leadEvent['view_url'] = $leadViewBaseUrl . '/' . $leadId;
    }
    $dateArr[] = $leadEvent;
endforeach;
unset($value);
$dataArr = json_encode($dateArr);

$projectFilterOptions = array();
if(!empty($isGlobalDashboardAdmin))
{
    $projectFilterOptions['0'] = __('All Agency');
}
elseif(!empty($isSystemManagerDashboardUser))
{
    $projectFilterOptions['0'] = __('All Agencies');
}
if(!empty($agencyList) && is_array($agencyList))
{
    foreach($agencyList as $agencyId => $agencyName)
    {
        $projectFilterOptions[(string)$agencyId] = $agencyName;
    }
}
$selectedProjectIdText = isset($selectedProjectId) ? (string)$selectedProjectId : '0';
$selectedPeriodText = isset($selectedPeriod) ? (string)$selectedPeriod : 'calendar_month';
$filterStartDateText = isset($filterStartDate) ? (string)$filterStartDate : '';
$filterEndDateText = isset($filterEndDate) ? (string)$filterEndDate : '';
$periodOptions = array(
    'day' => __('Day'),
    'month' => __('Last 30 Days'),
    'calendar_month' => __('Current Month'),
    'year' => __('Year'),
    'lifetime' => __('Lifetime'),
    'custom' => __('Custom')
);
$dashboardCurrencySymbol = (isset($currency) && trim((string)$currency) !== '') ? trim((string)$currency) : 'Rs';
$dashboardQuickActions = (isset($dashboardQuickActions) && is_array($dashboardQuickActions)) ? $dashboardQuickActions : array();
$dashboardApprovalActions = (isset($dashboardApprovalActions) && is_array($dashboardApprovalActions)) ? $dashboardApprovalActions : array();
$quickDealOptions = !empty($dashboardQuickActions['deal_options']) ? (array)$dashboardQuickActions['deal_options'] : array();
$selectedQuickDealId = !empty($dashboardQuickActions['selected_deal_id']) ? (int)$dashboardQuickActions['selected_deal_id'] : 0;
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    function escapeHtml(text) {
        return $('<div/>').text(text || '').html();
    }
    var currentLangCode = '<?php echo $configLanguage; ?>';
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,basicWeek,basicDay'
        },
        defaultDate: '<?php echo h(date('Y-m-d', strtotime($currentDateTime))); ?>',
        lang: currentLangCode,
        editable: false,
        eventLimit: true,
        events: <?php echo $dataArr; ?>,
        eventClick: function(event) {
            if(event && event.view_url) {
                window.location.href = event.view_url;
                return false;
            }
            return true;
        },
        eventRender: function(event, element) {
            var tipTitle = '<h6>' + escapeHtml(event.title) + '</h6>';
            if(event.tip) {
                tipTitle += event.tip;
            }
            if(event.view_url) {
                tipTitle += '<div style="margin-top:8px;"><a class="btn btn-xs btn-primary" href="' + event.view_url + '"><?php echo addslashes(__('View'));?></a></div>';
            }
            $(element).tooltip({
                title: tipTitle,
                placement: 'bottom',
                delay: 50,
                container: 'body',
                html: true
            });
        }
    });

    function toggleCustomDates() {
        var periodVal = $('#dashboardPeriod').val();
        if(periodVal === 'custom') {
            $('.dashboard-custom-date').removeClass('hidden');
            $('#dashboardStartDate,#dashboardEndDate').prop('disabled', false);
        } else {
            $('.dashboard-custom-date').addClass('hidden');
            $('#dashboardStartDate,#dashboardEndDate').prop('disabled', true);
        }
    }
    $('#dashboardPeriod').on('change', toggleCustomDates);
    toggleCustomDates();

    function updateDashboardPayInstallmentButton() {
        var $dealSelect = $('#dashboardQuickDealId');
        var $button = $('#dashboardQuickPayInstallment');
        if($dealSelect.length === 0 || $button.length === 0) {
            return;
        }
        var dealId = parseInt($dealSelect.val(), 10);
        var baseUrl = $button.data('base-url') || '';
        if(!isNaN(dealId) && dealId > 0 && baseUrl !== '') {
            $button.removeClass('disabled').attr('href', baseUrl.replace(/\/$/, '') + '/' + dealId);
        } else {
            $button.addClass('disabled').attr('href', '#');
        }
    }
    $('#dashboardQuickDealId').on('change', updateDashboardPayInstallmentButton);
    if($.fn.select2 && $('#dashboardQuickDealId').length) {
        $('#dashboardQuickDealId').select2({
            placeholder: '<?php echo addslashes(__('Search Client / Deal')); ?>',
            allowClear: true,
            width: '100%'
        });
    }
    $('#dashboardQuickPayInstallment').on('click', function(e) {
        if($(this).hasClass('disabled')) {
            e.preventDefault();
            return false;
        }
    });
    updateDashboardPayInstallmentButton();

    var pieData = <?php echo json_encode(array(
        (float)(isset($financePieData['sales']) ? $financePieData['sales'] : 0),
        (float)(isset($financePieData['expenses']) ? $financePieData['expenses'] : 0),
        (float)(isset($financePieData['purchase']) ? $financePieData['purchase'] : 0),
    )); ?>;
    var monthlyData = <?php echo json_encode(isset($chartMonthly) ? $chartMonthly : array()); ?>;
    var propertyTypeSalesData = <?php echo json_encode(isset($propertyTypeSales) ? $propertyTypeSales : array()); ?>;
    if(typeof Chart !== 'undefined') {
        var pieEl = document.getElementById('dashboardFinancePie');
        if(pieEl) {
            new Chart(pieEl, {
                type: 'doughnut',
                data: {
                    labels: ['<?php echo addslashes(__('Sales')); ?>', '<?php echo addslashes(__('Expenses')); ?>', '<?php echo addslashes(__('Purchase')); ?>'],
                    datasets: [{
                        data: pieData,
                        backgroundColor: ['#43a047', '#e53935', '#fb8c00'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        var barEl = document.getElementById('dashboardFinanceBars');
        if(barEl && monthlyData && monthlyData.labels) {
            new Chart(barEl, {
                type: 'bar',
                data: {
                    labels: monthlyData.labels,
                    datasets: [
                        {
                            label: '<?php echo addslashes(__('Sales')); ?>',
                            data: monthlyData.sales || [],
                            backgroundColor: 'rgba(67, 160, 71, 0.80)'
                        },
                        {
                            label: '<?php echo addslashes(__('Expenses')); ?>',
                            data: monthlyData.expenses || [],
                            backgroundColor: 'rgba(229, 57, 53, 0.80)'
                        },
                        {
                            label: '<?php echo addslashes(__('Purchase')); ?>',
                            data: monthlyData.purchase || [],
                            backgroundColor: 'rgba(251, 140, 0, 0.80)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        var typeSalesEl = document.getElementById('dashboardPropertyTypeSalesGraph');
        if(typeSalesEl && propertyTypeSalesData && propertyTypeSalesData.labels && propertyTypeSalesData.labels.length) {
            new Chart(typeSalesEl, {
                type: 'bar',
                data: {
                    labels: propertyTypeSalesData.labels || [],
                    datasets: [
                        {
                            label: '<?php echo addslashes(__('Sold')); ?>',
                            data: propertyTypeSalesData.sold || [],
                            yAxisID: 'y',
                            backgroundColor: 'rgba(33, 150, 243, 0.80)'
                        },
                        {
                            label: '<?php echo addslashes(__('Revenue')); ?>',
                            data: propertyTypeSalesData.revenue || [],
                            yAxisID: 'y1',
                            type: 'line',
                            borderColor: '#43a047',
                            backgroundColor: 'rgba(67, 160, 71, 0.12)',
                            borderWidth: 2,
                            tension: 0.25,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: '<?php echo addslashes(__('Sold')); ?>' }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: '<?php echo addslashes(__('Revenue')); ?>' }
                        }
                    }
                }
            });
        }
    }
});
</script>

<style type="text/css">
.dashboard-filter-panel .panel-body {
    padding: 12px;
}
.dashboard-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-end;
}
.dashboard-filter-item {
    min-width: 180px;
}
.dashboard-filter-item label {
    display: block;
    margin-bottom: 4px;
    font-weight: 600;
    font-size: 12px;
}
.dashboard-filter-item.dashboard-filter-actions {
    flex: 0 1 auto;
    min-width: 0;
}
.dashboard-quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-end;
}
.dashboard-quick-actions .btn {
    white-space: nowrap;
}
.dashboard-quick-actions .dashboard-compact-action {
    padding-left: 10px;
    padding-right: 10px;
}
.dashboard-quick-actions .dashboard-quick-deal {
    min-width: 280px;
    flex: 1 1 340px;
    max-width: 420px;
}
.dashboard-quick-deal .select2-container {
    width: 100% !important;
}
.dashboard-quick-deal .select2-container .select2-choice {
    background: #fff;
    border: 1px solid #cbd8e8;
    border-radius: 8px;
    color: #0f172a;
    font-weight: 600;
    height: 40px;
    line-height: 38px;
}
.dashboard-quick-deal .select2-container .select2-choice .select2-arrow b {
    background-position: 0 7px;
}
.dashboard-quick-actions .dashboard-quick-help {
    width: 100%;
    font-size: 12px;
    color: #7a8898;
    margin-top: 2px;
}
.dashboard-approval-strip {
    align-items: stretch;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
    margin-left: auto;
}
.dashboard-approval-action {
    align-items: center;
    background:#fff;
    border:1px solid #d9e4f1;
    border-left: 4px solid #2563eb;
    color: #10233f !important;
    display: flex;
    flex: 0 0 230px;
    gap: 10px;
    min-height: 40px;
    padding: 6px 9px;
    transition:background .15s ease,border-color .15s ease,box-shadow .15s ease,transform .15s ease;
}
.dashboard-approval-action:hover,
.dashboard-approval-action:focus { background: #f8fbff; border-color:#b9c9dc; box-shadow:0 4px 12px rgba(15,35,63,.10); text-decoration: none; transform:translateY(-1px); }
.dashboard-approval-action.success { border-left-color: #16a34a; }
.dashboard-approval-action.warning { border-left-color: #d97706; }
.dashboard-approval-action.primary { border-left-color: #2563eb; }
.dashboard-approval-action.requested { border-left-color:#7c3aed; }
.dashboard-approval-action > .fa { font-size: 22px; width: 24px; }
.dashboard-approval-copy { flex: 1 1 auto; min-width: 0; }
.dashboard-approval-copy strong,
.dashboard-approval-copy small { display: block; }
.dashboard-approval-copy strong { font-size: 13px; }
.dashboard-approval-copy small { color: #64748b; font-size: 10px; font-weight: 700; margin-top: 3px; }
.dashboard-approval-count { align-items:center;background:#dc2626;border-radius:999px;color:#fff;display:inline-flex;font-size:12px;font-weight:900;height:26px;justify-content:center;min-width:26px;padding:0 7px;text-align:center; }
.dashboard-approval-filter-item { flex:0 0 auto; margin-left:auto; min-width:230px; }
.dashboard-approval-filter-item > label { display:none; }
.dashboard-filter-actions > label {
    display: none;
}
.dashboard-cards .db-card-wrap {
    margin-bottom: 14px;
}
.db-fin-card {
    border-radius: 8px;
    color: #fff;
    cursor: pointer;
    display: block;
    min-height: 144px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    text-decoration: none;
    transition: box-shadow .15s ease, transform .15s ease;
}
.db-fin-card,
.db-fin-card:link,
.db-fin-card:visited,
.db-fin-card:hover,
.db-fin-card:focus,
.db-fin-card .db-card-title,
.db-fin-card .db-card-value,
.db-fin-card .db-card-sub,
.db-fin-card .db-card-icon,
.db-fin-card .db-card-open {
    color: #fff !important;
}
.db-fin-card:hover,
.db-fin-card:focus {
    color: #fff;
    box-shadow: 0 6px 16px rgba(0,0,0,0.16);
    outline: none;
    text-decoration: none;
    transform: translateY(-2px);
}
.db-fin-card:focus-visible {
    outline: 3px solid #111;
    outline-offset: 2px;
}
.db-fin-card .db-card-body {
    padding: 12px 14px 40px;
}
.db-fin-card .db-card-title {
    font-size: 13px;
    opacity: 0.95;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0;
}
.db-fin-card .db-card-icon {
    position: absolute;
    right: 12px;
    top: 10px;
    font-size: 30px;
    opacity: 0.28;
}
.db-fin-card .db-card-value {
    margin-top: 8px;
    font-size: 31px;
    line-height: 1.1;
    font-weight: 800;
    font-family: Arial, Helvetica, sans-serif;
    font-variant-numeric: tabular-nums;
    word-break: break-word;
}
.db-fin-card .db-card-sub {
    margin-top: 3px;
    min-height: 28px;
    padding-right: 24px;
    font-size: 11px;
    line-height: 1.3;
    font-family: Arial, Helvetica, sans-serif;
    font-variant-numeric: tabular-nums;
    opacity: 0.9;
}
.db-fin-card .db-card-link {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    text-align: center;
    padding: 8px 10px;
    background: rgba(0,0,0,0.15);
    font-weight: 700;
    font-size: 12px;
}
.db-fin-card .db-card-link a {
    color: #fff;
}
.db-fin-card .db-card-open {
    bottom: 10px;
    font-size: 14px;
    opacity: .85;
    position: absolute;
    right: 12px;
}
.db-card-primary { background: linear-gradient(135deg, #3498db, #1976d2); }
.db-card-danger { background: linear-gradient(135deg, #ef5350, #e53935); }
.db-card-success { background: linear-gradient(135deg, #66bb6a, #43a047); }
.db-card-info { background: linear-gradient(135deg, #4fc3f7, #039be5); }
.db-card-warning { background: linear-gradient(135deg, #ffb74d, #f57c00); }
.db-card-profit { background: linear-gradient(135deg, #26a69a, #00897b); }
.db-card-loss { background: linear-gradient(135deg, #ef5350, #d32f2f); }
.db-card-purple { background: linear-gradient(135deg, #ab47bc, #7b1fa2); }
.db-card-teal { background: linear-gradient(135deg, #26c6da, #0097a7); }
.db-card-indigo { background: linear-gradient(135deg, #5c6bc0, #3949ab); }
.dashboard-panel {
    margin-bottom: 14px;
}
.dashboard-section-title {
    margin: 10px 0 10px;
    font-weight: 700;
    font-size: 18px;
}
.dashboard-panel .panel-heading h4 {
    margin: 0;
}
.dashboard-chart-wrap {
    min-height: 280px;
}
.dashboard-chart-small {
    min-height: 280px;
}
.dashboard-chart-bars {
    min-height: 310px;
}
.dashboard-chart-types {
    min-height: 320px;
}
.db-fin-card .db-card-link {
    display: none;
}
.db-fin-card .db-card-body {
    padding: 12px 36px 16px 14px;
}
.db-fin-card {
    min-height: 150px;
}
.dashboard-panel .panel-heading h4 {
    display: none;
}
@media (min-width: 1700px) {
    .dashboard-filter-panel .panel-body {
        padding: 10px;
    }
    .dashboard-filter-row {
        align-items: flex-end;
        flex-wrap: nowrap;
        gap: 6px;
    }
    .dashboard-filter-item {
        min-width: 0;
    }
    .dashboard-agency-filter {
        flex: 0 1 170px;
    }
    .dashboard-range-filter {
        flex: 0 1 145px;
    }
    .dashboard-apply-filter {
        display: flex;
        flex: 0 0 auto;
        gap: 4px;
    }
    .dashboard-filter-row .form-control,
    .dashboard-filter-row .btn {
        font-size: 11px;
        height: 36px;
    }
    .dashboard-filter-row .btn {
        padding: 7px 9px;
    }
    .dashboard-quick-actions .dashboard-compact-action {
        padding-left: 7px;
        padding-right: 7px;
    }
    .dashboard-filter-item label {
        font-size: 10px;
        margin-bottom: 3px;
    }
    .dashboard-filter-item.dashboard-filter-actions {
        flex: 1 1 560px;
    }
    .dashboard-quick-actions {
        flex-wrap: nowrap;
        gap: 5px;
    }
    .dashboard-quick-actions .dashboard-quick-deal {
        flex: 1 1 190px;
        min-width: 170px;
        max-width: 250px;
    }
    .dashboard-quick-deal .select2-container .select2-choice {
        font-size: 11px;
        height: 36px;
        line-height: 34px;
    }
    .dashboard-quick-deal .select2-container .select2-choice .select2-arrow b {
        background-position: 0 5px;
    }
    .dashboard-approval-filter-item {
        flex: 1 1 440px;
        margin-left: 0;
        min-width: 390px;
    }
    .dashboard-approval-strip {
        flex-wrap: nowrap;
        gap: 5px;
        margin-left: 0;
    }
    .dashboard-approval-action {
        flex: 1 1 0;
        gap: 5px;
        min-height: 36px;
        min-width: 0;
        padding: 4px 6px;
    }
    .dashboard-approval-action > .fa {
        font-size: 16px;
        width: 17px;
    }
    .dashboard-approval-copy strong {
        font-size: 10px;
        white-space: nowrap;
    }
    .dashboard-approval-copy small {
        font-size: 9px;
        line-height: 1.15;
        margin-top: 1px;
        white-space: nowrap;
    }
    .dashboard-approval-count {
        font-size: 10px;
        height: 21px;
        min-width: 21px;
        padding: 0 5px;
    }
}
@media (max-width: 767px) {
    .dashboard-filter-item {
        width: 100%;
        min-width: 100%;
    }
    .dashboard-filter-item.dashboard-filter-actions {
        min-width: 100%;
    }
    .dashboard-approval-filter-item { min-width:100%; }
    .dashboard-approval-strip { justify-content:flex-start; }
    .dashboard-approval-action { flex:1 1 210px; }
    .dashboard-quick-actions .dashboard-quick-deal {
        min-width: 100%;
        flex: 1 1 100%;
    }
}
</style>

<?php echo $this->Session->flash(); ?>

<div class="panel panel-default dashboard-filter-panel">
    <div class="panel-body">
        <form method="get" action="<?php echo $this->Html->url(array('controller' => 'Dashboards', 'action' => 'index')); ?>">
            <div class="dashboard-filter-row">
                <?php if(!empty($isGlobalDashboardAdmin) || !empty($isCeoDashboardUser) || !empty($isSystemManagerDashboardUser)): ?>
                    <div class="dashboard-filter-item dashboard-agency-filter">
                        <label><?php echo __('Agency'); ?></label>
                        <select name="project_id" class="form-control">
                            <?php foreach($projectFilterOptions as $agencyIdVal => $agencyNameVal): ?>
                                <option value="<?php echo h($agencyIdVal); ?>" <?php echo ($selectedProjectIdText === (string)$agencyIdVal) ? 'selected="selected"' : ''; ?>>
                                    <?php echo h($agencyNameVal); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="project_id" value="<?php echo h($selectedProjectIdText); ?>">
                <?php endif; ?>
                <div class="dashboard-filter-item dashboard-range-filter">
                    <label><?php echo __('Range'); ?></label>
                    <select id="dashboardPeriod" name="period" class="form-control">
                        <?php foreach($periodOptions as $periodValue => $periodLabel): ?>
                            <option value="<?php echo h($periodValue); ?>" <?php echo ($selectedPeriodText === $periodValue) ? 'selected="selected"' : ''; ?>>
                                <?php echo h($periodLabel); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="dashboard-filter-item dashboard-custom-date">
                    <label><?php echo __('Start Date'); ?></label>
                    <input id="dashboardStartDate" type="date" name="start_date" class="form-control" value="<?php echo h($filterStartDateText); ?>">
                </div>
                <div class="dashboard-filter-item dashboard-custom-date">
                    <label><?php echo __('End Date'); ?></label>
                    <input id="dashboardEndDate" type="date" name="end_date" class="form-control" value="<?php echo h($filterEndDateText); ?>">
                </div>
                <div class="dashboard-filter-item dashboard-apply-filter">
                    <button type="submit" class="btn btn-success"><span class="fa fa-search"></span> <?php echo __('Apply'); ?></button>
                </div>
                <?php if(!empty($dashboardQuickActions['show'])): ?>
                <div class="dashboard-filter-item dashboard-filter-actions">
                    <label><?php echo __('Quick Actions'); ?></label>
                    <div class="dashboard-quick-actions">
                        <?php if(!empty($dashboardQuickActions['can_add_income'])): ?>
                            <?php echo $this->Html->link('<span class="fa fa-plus"></span> ' . __('Income'), $dashboardQuickActions['income_url'], array('class' => 'btn btn-success dashboard-compact-action', 'escape' => false)); ?>
                        <?php else: ?>
                            <a href="javascript:void(0);" class="btn btn-success dashboard-compact-action disabled"><span class="fa fa-plus"></span> <?php echo __('Income'); ?></a>
                        <?php endif; ?>

                        <?php if(!empty($dashboardQuickActions['can_add_expense'])): ?>
                            <?php echo $this->Html->link('<span class="fa fa-minus"></span> ' . __('Expense'), $dashboardQuickActions['expense_url'], array('class' => 'btn btn-danger dashboard-compact-action', 'escape' => false)); ?>
                        <?php else: ?>
                            <a href="javascript:void(0);" class="btn btn-danger dashboard-compact-action disabled"><span class="fa fa-minus"></span> <?php echo __('Expense'); ?></a>
                        <?php endif; ?>

                        <?php if(!empty($dashboardQuickActions['can_journal_voucher'])): ?>
                            <?php echo $this->Html->link('<span class="fa fa-exchange"></span> ' . __('J V'), $dashboardQuickActions['journal_url'], array('class' => 'btn btn-primary dashboard-compact-action', 'escape' => false, 'title' => __('Journal Voucher'))); ?>
                        <?php else: ?>
                            <a href="javascript:void(0);" class="btn btn-primary dashboard-compact-action disabled" title="<?php echo h(__('Journal Voucher')); ?>"><span class="fa fa-exchange"></span> <?php echo __('J V'); ?></a>
                        <?php endif; ?>

                        <?php if(!empty($dashboardQuickActions['can_pay_installment'])): ?>
                            <div class="dashboard-quick-deal">
                                <select id="dashboardQuickDealId" name="quick_deal_id" class="form-control">
                                    <option value=""><?php echo !empty($dashboardQuickActions['needs_project']) ? __('Select Agency First') : __('Select Client / Deal'); ?></option>
                                    <?php foreach($quickDealOptions as $quickDealId => $quickDealLabel): ?>
                                        <option value="<?php echo (int)$quickDealId; ?>" <?php echo ($selectedQuickDealId === (int)$quickDealId) ? 'selected="selected"' : ''; ?>>
                                            <?php echo h($quickDealLabel); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <a id="dashboardQuickPayInstallment" data-base-url="<?php echo h($this->Html->url((array)$dashboardQuickActions['pay_installment_base_route'])); ?>" href="#" class="btn btn-warning <?php echo ($selectedQuickDealId > 0) ? '' : 'disabled'; ?>"><span class="fa fa-money"></span> <?php echo __('Pay Installment'); ?></a>
                        <?php else: ?>
                            <a href="javascript:void(0);" class="btn btn-warning disabled"><span class="fa fa-money"></span> <?php echo __('Pay Installment'); ?></a>
                        <?php endif; ?>

                        <?php if(empty($dashboardQuickActions['needs_project']) && !empty($dashboardQuickActions['can_pay_installment']) && empty($quickDealOptions)): ?>
                            <div class="dashboard-quick-help"><?php echo __('No deals found for the selected agency yet.'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if(!empty($dashboardApprovalActions['show'])): ?>
                <div class="dashboard-filter-item dashboard-approval-filter-item">
                    <label><?php echo __('Expense Workflow'); ?></label>
                    <div class="dashboard-approval-strip">
                        <?php foreach((array)$dashboardApprovalActions['actions'] as $approvalAction): ?>
                        <a class="dashboard-approval-action <?php echo h($approvalAction['class']); ?>" href="<?php echo h($this->Html->url($approvalAction['url'])); ?>">
                            <span class="fa <?php echo h($approvalAction['icon']); ?>"></span>
                            <span class="dashboard-approval-copy"><strong><?php echo h($approvalAction['label']); ?></strong><small><?php echo h($approvalAction['role']); ?></small></span>
                            <?php if($approvalAction['count'] !== null): ?><span class="dashboard-approval-count"><?php echo (int)$approvalAction['count']; ?></span><?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php
$dashboardCardSections = (isset($dashboardCardSections) && is_array($dashboardCardSections)) ? $dashboardCardSections : array();
if(empty($dashboardCardSections) && !empty($dashboardCards))
{
    $dashboardCardSections[] = array('title' => __('Business Overview'), 'cards' => $dashboardCards);
}
foreach($dashboardCardSections as $dashboardCardSection):
    if(empty($dashboardCardSection['cards'])){ continue; }
?>
<div class="row"><div class="col-md-12"><h4 class="dashboard-section-title"><?php echo h($dashboardCardSection['title']);?></h4></div></div>
<div class="row dashboard-cards">
<?php foreach($dashboardCardSection['cards'] as $card): ?>
        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 db-card-wrap">
            <a href="<?php echo h($this->Html->url($card['url'])); ?>" class="db-fin-card db-card-<?php echo h($card['color']); ?>" aria-label="<?php echo h(__('Open').' '.$card['title']); ?>" title="<?php echo h(__('Open').' '.$card['title']); ?>">
                <span class="db-card-icon fa <?php echo h($card['icon']); ?>"></span>
                <div class="db-card-body">
                    <div class="db-card-title"><?php echo h($card['title']); ?></div>
                    <div class="db-card-sub"><?php echo h($card['subtitle']); ?></div>
                    <div class="db-card-value"><?php echo h($card['value']); ?></div>
                </div>
                <span class="db-card-open fa fa-arrow-right" aria-hidden="true"></span>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

<?php if(!empty($accountsCards)): ?>
<div class="row">
    <div class="col-md-12">
        <h4 class="dashboard-section-title"><?php echo __('Accounts & Vouchers'); ?></h4>
    </div>
</div>
<div class="row dashboard-cards">
    <?php foreach($accountsCards as $card): ?>
        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 db-card-wrap">
            <a href="<?php echo h($this->Html->url($card['url'])); ?>" class="db-fin-card db-card-<?php echo h($card['color']); ?>" aria-label="<?php echo h(__('Open').' '.$card['title']); ?>" title="<?php echo h(__('Open').' '.$card['title']); ?>">
                <span class="db-card-icon fa <?php echo h($card['icon']); ?>"></span>
                <div class="db-card-body">
                    <div class="db-card-title"><?php echo h($card['title']); ?></div>
                    <div class="db-card-sub"><?php echo h($card['subtitle']); ?></div>
                    <div class="db-card-value"><?php echo h($card['value']); ?></div>
                </div>
                <span class="db-card-open fa fa-arrow-right" aria-hidden="true"></span>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if(!empty($constructionCards)): ?>
<div class="row">
    <div class="col-md-12">
        <h4 class="dashboard-section-title"><?php echo __('Construction & Inventory'); ?></h4>
    </div>
</div>
<div class="row dashboard-cards">
    <?php foreach($constructionCards as $card): ?>
        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 db-card-wrap">
            <a href="<?php echo h($this->Html->url($card['url'])); ?>" class="db-fin-card db-card-<?php echo h($card['color']); ?>" aria-label="<?php echo h(__('Open').' '.$card['title']); ?>" title="<?php echo h(__('Open').' '.$card['title']); ?>">
                <span class="db-card-icon fa <?php echo h($card['icon']); ?>"></span>
                <div class="db-card-body">
                    <div class="db-card-title"><?php echo h($card['title']); ?></div>
                    <div class="db-card-sub"><?php echo h($card['subtitle']); ?></div>
                    <div class="db-card-value"><?php echo h($card['value']); ?></div>
                </div>
                <span class="db-card-open fa fa-arrow-right" aria-hidden="true"></span>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if(!empty($showRecentPaymentTransactions)): ?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-midnightblue dashboard-panel">
            <div class="panel-heading clearfix">
                <h4 class="pull-left"><?php echo __('Last 5 Payment Transactions'); ?></h4>
                <button type="button" class="btn btn-xs btn-default pull-right" data-toggle="collapse" data-target="#dashboardRecentTxBody">
                    <span class="fa fa-chevron-up"></span> <?php echo __('Collapse / Expand'); ?>
                </button>
            </div>
            <div id="dashboardRecentTxBody" class="panel-collapse collapse in">
                <div class="panel-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 110px;"><?php echo __('Date'); ?></th>
                                <th style="width: 100px;"><?php echo __('Source'); ?></th>
                                <th><?php echo __('Agency'); ?></th>
                                <th><?php echo __('Reference'); ?></th>
                                <th><?php echo __('Details'); ?></th>
                                <th style="width: 140px;" class="text-right"><?php echo __('Amount'); ?></th>
                                <th style="width: 130px;" class="text-center"><?php echo __('Print'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($recentPaymentTransactions)): ?>
                                <?php foreach($recentPaymentTransactions as $txRow): ?>
                                    <tr>
                                        <td><?php echo h(isset($txRow['date_text']) ? $txRow['date_text'] : '-'); ?></td>
                                        <td><?php echo h(isset($txRow['source_label']) ? $txRow['source_label'] : '-'); ?></td>
                                        <td><?php echo h(isset($txRow['agency_name']) ? $txRow['agency_name'] : '-'); ?></td>
                                        <td><?php echo h(isset($txRow['reference']) ? $txRow['reference'] : '-'); ?></td>
                                        <td><?php echo h(isset($txRow['description']) ? $txRow['description'] : '-'); ?></td>
                                        <td class="text-right"><?php echo h($dashboardCurrencySymbol); ?> <?php echo $this->Number->format(isset($txRow['amount']) ? (float)$txRow['amount'] : 0); ?></td>
                                        <td class="text-center">
                                            <?php
                                            if(!empty($txRow['print_route']))
                                            {
                                                echo $this->Html->link(
                                                    '<span class="fa fa-print"></span> ' . h(isset($txRow['print_label']) ? $txRow['print_label'] : __('Print')),
                                                    $txRow['print_route'],
                                                    array('escape' => false, 'class' => 'btn btn-xs btn-primary', 'target' => '_blank')
                                                );
                                            }
                                            else
                                            {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center"><?php echo __('No recent transactions found.'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(!empty($showFinancialCharts)){ ?>
<div class="row">
    <div class="col-md-8">
        <div class="panel panel-midnightblue dashboard-panel">
            <div class="panel-heading clearfix">
                <h4 class="pull-left"><?php echo __('Financial Trend'); ?></h4>
                <button type="button" class="btn btn-xs btn-default pull-right" data-toggle="collapse" data-target="#dashboardTrendBody">
                    <span class="fa fa-chevron-up"></span> <?php echo __('Toggle'); ?>
                </button>
            </div>
            <div id="dashboardTrendBody" class="panel-collapse collapse in">
                <div class="panel-body dashboard-chart-wrap">
                    <div id="mywrappersr"></div>
                    <?php echo $this->HighCharts->render("My Chartsr"); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-midnightblue dashboard-panel">
            <div class="panel-heading"><h4><?php echo __('Current Split'); ?></h4></div>
            <div class="panel-body dashboard-chart-small">
                <canvas id="dashboardFinancePie"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-midnightblue dashboard-panel">
            <div class="panel-heading"><h4><?php echo __('Monthly Comparison'); ?></h4></div>
            <div class="panel-body dashboard-chart-bars">
                <canvas id="dashboardFinanceBars"></canvas>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php if(!empty($showPropertyTypeSales)): ?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-midnightblue dashboard-panel">
            <div class="panel-heading clearfix">
                <div class="pull-left" style="font-weight:700;"><?php echo __('Property Type Sales'); ?></div>
                <button type="button" class="btn btn-xs btn-default pull-right" data-toggle="collapse" data-target="#dashboardPropertyTypeSalesBody">
                    <span class="fa fa-bar-chart"></span> <?php echo __('Collapse / Expand'); ?>
                </button>
            </div>
            <div id="dashboardPropertyTypeSalesBody" class="panel-collapse collapse in">
                <div class="panel-body">
                    <?php $propertyTypeRows = (isset($propertyTypeSales['rows']) && is_array($propertyTypeSales['rows'])) ? $propertyTypeSales['rows'] : array(); ?>
                    <?php if(!empty($propertyTypeRows)): ?>
                        <div class="dashboard-chart-types">
                            <canvas id="dashboardPropertyTypeSalesGraph"></canvas>
                        </div>
                        <div class="table-responsive" style="margin-top:12px;">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo __('Property Type'); ?></th>
                                        <th class="text-right"><?php echo __('Sold'); ?></th>
                                        <th class="text-right"><?php echo __('Revenue'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($propertyTypeRows as $typeRow): ?>
                                        <tr>
                                            <td><?php echo h(isset($typeRow['type']) ? $typeRow['type'] : '-'); ?></td>
                                            <td class="text-right"><?php echo (int)(isset($typeRow['sold']) ? $typeRow['sold'] : 0); ?></td>
                                            <td class="text-right"><?php echo h($dashboardCurrencySymbol); ?> <?php echo $this->Number->format(isset($typeRow['revenue']) ? (float)$typeRow['revenue'] : 0); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info" style="margin-bottom:0;">
                            <?php echo __('No property type sales found in selected range.'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(!empty($showCalendarSection)): ?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-midnightblue dashboard-panel">
            <div class="panel-heading clearfix">
                <h4 class="pull-left"><?php echo __('Calender Schedules'); ?></h4>
                <button type="button" class="btn btn-xs btn-default pull-right" data-toggle="collapse" data-target="#dashboardCalendarBody">
                    <span class="fa fa-calendar"></span> <?php echo __('Collapse / Expand'); ?>
                </button>
            </div>
            <div id="dashboardCalendarBody" class="panel-collapse collapse in">
                <div class="panel-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
