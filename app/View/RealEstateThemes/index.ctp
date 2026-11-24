<?php
$target = APP . 'Plugin' . DS . 'RealEstateTheme' . DS . 'View' . DS . 'RealEstateThemes' . DS . 'index.ctp';
if (file_exists($target)) {
    include $target;
}
