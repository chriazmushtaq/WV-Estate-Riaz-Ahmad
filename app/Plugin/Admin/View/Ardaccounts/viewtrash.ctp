<?php
$trash = isset($trash) && is_array($trash) ? $trash : array();
$recordData = isset($recordData) && is_array($recordData) ? $recordData : array();
$extraData = isset($extraData) && is_array($extraData) ? $extraData : array();
$displayTitle = isset($displayTitle) ? trim((string)$displayTitle) : '';
$displayMeta = isset($displayMeta) ? trim((string)$displayMeta) : '';
$deletedByName = isset($deletedByName) ? trim((string)$deletedByName) : '-';
$currentProjectId = isset($currentProjectId) ? (int)$currentProjectId : 0;
$trashId = !empty($trash['id']) ? (int)$trash['id'] : 0;
$deletedAt = '-';
if(!empty($trash['deleted_at']) && $trash['deleted_at'] !== '0000-00-00 00:00:00')
{
    $deletedAt = date('d-M-Y h:i A', strtotime((string)$trash['deleted_at']));
}
$humanizeKey = function($key) {
    $key = preg_replace('/[^a-zA-Z0-9_]+/', ' ', (string)$key);
    $key = str_replace('_', ' ', $key);
    return ucwords(trim($key));
};
$renderValue = null;
$renderValue = function($value) use (&$renderValue, $humanizeKey) {
    if(is_array($value))
    {
        if(empty($value))
        {
            return '<span class="text-muted">-</span>';
        }
        $html = '<table class="table table-condensed table-bordered ard-trash-nested-table"><tbody>';
        foreach($value as $key => $nestedValue)
        {
            $html .= '<tr><th>'.h($humanizeKey($key)).'</th><td>'.$renderValue($nestedValue).'</td></tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }
    if($value === null || $value === '')
    {
        return '<span class="text-muted">-</span>';
    }
    if(is_bool($value))
    {
        $value = $value ? 'Yes' : 'No';
    }
    $text = (string)$value;
    if(strlen($text) > 220 || strpos($text, "\n") !== false || strpos($text, "\r") !== false || strpos($text, '<') !== false)
    {
        return '<pre class="ard-trash-long-value">'.h($text).'</pre>';
    }
    return h($text);
};
$renderDetailTable = function($data) use ($humanizeKey, $renderValue) {
    if(empty($data))
    {
        return '<div class="ard-trash-empty-detail">'.h(__('No stored details found for this section.')).'</div>';
    }
    $html = '<div class="table-responsive"><table class="table table-bordered ard-trash-detail-table"><tbody>';
    foreach($data as $key => $value)
    {
        $html .= '<tr><th>'.h($humanizeKey($key)).'</th><td>'.$renderValue($value).'</td></tr>';
    }
    $html .= '</tbody></table></div>';
    return $html;
};
?>
<style type="text/css">
.ard-trash-view-container{width:100%;max-width:1120px;}
.ard-trash-view .panel-heading{background:#071a33;color:#fff;font-weight:700;border-radius:4px 4px 0 0;}
.ard-trash-view .close{color:#fff;opacity:.9;}
.ard-trash-view-title{font-size:18px;font-weight:800;margin:0 0 5px;color:#10233f;}
.ard-trash-view-meta{color:#667085;font-size:12px;line-height:1.45;margin-bottom:12px;}
.ard-trash-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-bottom:14px;}
.ard-trash-summary-item{border:1px solid #dbe7f5;background:#f8fbff;border-radius:6px;padding:9px 10px;min-height:54px;}
.ard-trash-summary-label{display:block;font-size:10px;text-transform:uppercase;color:#5b6b84;font-weight:800;margin-bottom:4px;}
.ard-trash-summary-value{font-size:13px;color:#10233f;font-weight:700;word-break:break-word;}
.ard-trash-section{border:1px solid #dbe7f5;border-radius:6px;margin-bottom:12px;overflow:hidden;background:#fff;}
.ard-trash-section h4{margin:0;padding:10px 12px;background:#f1f6fc;border-bottom:1px solid #dbe7f5;font-size:13px;font-weight:800;color:#10233f;}
.ard-trash-detail-table{margin:0;}
.ard-trash-detail-table th{width:210px;background:#fbfdff;color:#21314d;font-size:12px;vertical-align:top !important;}
.ard-trash-detail-table td{font-size:12px;color:#172033;vertical-align:top !important;word-break:break-word;}
.ard-trash-nested-table{margin:0;background:#fff;}
.ard-trash-nested-table th{width:180px;background:#f8fafc;font-size:11px;}
.ard-trash-long-value{max-height:240px;overflow:auto;white-space:pre-wrap;background:#fbfdff;border:1px solid #dbe7f5;border-radius:4px;padding:8px;margin:0;font-size:11px;color:#172033;}
.ard-trash-empty-detail{padding:12px;color:#667085;font-size:12px;}
.ard-trash-error{padding:18px;border:1px solid #f0c36d;background:#fff8e6;color:#7a4b00;border-radius:6px;font-weight:700;}
.ard-trash-view-actions{border-top:1px solid #e5edf7;padding-top:12px;margin-top:12px;}
@media(max-width:900px){.ard-trash-summary{grid-template-columns:repeat(2,minmax(0,1fr));}.ard-trash-detail-table th{width:160px;}}
@media(max-width:520px){.ard-trash-summary{grid-template-columns:1fr;}.ard-trash-detail-table th,.ard-trash-detail-table td{display:block;width:100%;}}
</style>

<div class="container ard-trash-view-container">
    <div class="panel panel-custom mrg ard-trash-view">
        <div class="panel-heading">
            <?php echo __('Deleted Record Details');?>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        </div>
        <div class="panel-body">
            <?php if(!empty($isError)){ ?>
                <div class="ard-trash-error"><?php echo h(isset($errorMessage) ? $errorMessage : __('Unable to load trash record.'));?></div>
                <div class="ard-trash-view-actions">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><span class="fa fa-remove"></span> <?php echo __('Close');?></button>
                </div>
            <?php } else { ?>
                <div class="ard-trash-view-title"><?php echo h($displayTitle !== '' ? $displayTitle : (isset($trash['entry_title']) ? $trash['entry_title'] : __('Deleted Record')));?></div>
                <?php if($displayMeta !== ''){ ?><div class="ard-trash-view-meta"><?php echo h($displayMeta);?></div><?php } ?>

                <div class="ard-trash-summary">
                    <div class="ard-trash-summary-item">
                        <span class="ard-trash-summary-label"><?php echo __('Deleted At');?></span>
                        <span class="ard-trash-summary-value"><?php echo h($deletedAt);?></span>
                    </div>
                    <div class="ard-trash-summary-item">
                        <span class="ard-trash-summary-label"><?php echo __('Deleted By');?></span>
                        <span class="ard-trash-summary-value"><?php echo h($deletedByName);?><?php if(!empty($trash['deleted_by'])){ ?> (#<?php echo (int)$trash['deleted_by'];?>)<?php } ?></span>
                    </div>
                    <div class="ard-trash-summary-item">
                        <span class="ard-trash-summary-label"><?php echo __('Source');?></span>
                        <span class="ard-trash-summary-value"><?php echo h(isset($trash['source_model']) ? $trash['source_model'] : '-');?> / <?php echo h(isset($trash['source_table']) ? $trash['source_table'] : '-');?></span>
                    </div>
                    <div class="ard-trash-summary-item">
                        <span class="ard-trash-summary-label"><?php echo __('Original ID');?></span>
                        <span class="ard-trash-summary-value"><?php echo (int)(isset($trash['source_id']) ? $trash['source_id'] : 0);?></span>
                    </div>
                </div>

                <div class="ard-trash-section">
                    <h4><?php echo __('Deleted Record Fields');?></h4>
                    <?php echo $renderDetailTable($recordData);?>
                </div>

                <div class="ard-trash-section">
                    <h4><?php echo __('Linked / Extra Details');?></h4>
                    <?php echo $renderDetailTable($extraData);?>
                </div>

                <div class="ard-trash-view-actions">
                    <?php
                    echo $this->Html->link(
                        '<span class="fa fa-undo"></span> '.__('Restore'),
                        array('controller' => 'Ardaccounts', 'action' => 'restoretrash', $trashId, $currentProjectId),
                        array('escape' => false, 'class' => 'btn btn-success', 'onclick' => "return confirm('".__('Restore this record?')."');")
                    );
                    echo ' ';
                    echo $this->Html->link(
                        '<span class="fa fa-trash"></span> '.__('Delete Permanently'),
                        array('controller' => 'Ardaccounts', 'action' => 'deletetrash', $trashId, $currentProjectId),
                        array('escape' => false, 'class' => 'btn btn-danger', 'onclick' => "return confirm('".__('Delete permanently from trash?')."');")
                    );
                    echo ' ';
                    ?>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-remove"></span> <?php echo __('Close');?></button>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
