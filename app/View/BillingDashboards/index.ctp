<?php
$target = APP . 'Plugin' . DS . 'BillingManager' . DS . 'View' . DS . 'BillingDashboards' . DS . 'index.ctp';
if (file_exists($target)) {
    include $target;
}
