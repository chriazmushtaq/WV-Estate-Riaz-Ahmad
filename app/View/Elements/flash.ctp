<?php
$flashAlert = isset($alert) ? strtolower(trim((string)$alert)) : 'info';
$flashMessage = isset($message) ? (string)$message : __('Something went wrong');

$plainMessage = strtolower(trim(strip_tags($flashMessage)));
$isDeleteNotice = (strpos($plainMessage, 'delete') !== false || strpos($plainMessage, 'removed') !== false);
$alreadyHasTrashHint = (strpos($plainMessage, 'trash') !== false || strpos($plainMessage, 'restore') !== false);
$isPermanentDelete = (strpos($plainMessage, 'permanent') !== false || strpos($plainMessage, 'permanently') !== false);

if($flashAlert === 'success' && $isDeleteNotice && !$alreadyHasTrashHint && !$isPermanentDelete)
{
    $flashMessage = rtrim($flashMessage, " .\t\n\r\0\x0B") . '. ' . __('You can restore it from Trash.');
}
?>
<div class="alert alert-<?php echo h($flashAlert); ?>">
<button type="button" class="close" data-dismiss="alert">x</button>
<center><?php echo $flashMessage; ?></center>
</div>
