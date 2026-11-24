<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo __('Property Updated');?></title>
    <meta http-equiv="refresh" content="2;url=<?php echo h($propertyIndexUrl);?>">
</head>
<body>
<script>
(function () {
    var indexUrl = <?php echo json_encode($propertyIndexUrl);?>;
    try {
        if (window.opener && !window.opener.closed) {
            window.opener.location.reload();
            window.close();
            return;
        }
        if (window.parent && window.parent !== window) {
            window.parent.location.reload();
            return;
        }
    } catch (ignore) {}
    window.location.replace(indexUrl);
})();
</script>
<p><?php echo __('Property has been updated. Returning to properties...');?></p>
</body>
</html>
