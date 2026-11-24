<?php
/**
 * Client Portal Deal Letter Print View
 * Standardized 1:1 Shared Master Engine with PDF Export & Duplicate Watermark
 */
$this->set('clientWatermark', true);
$this->set('mode', (isset($autoPrint) && $autoPrint) ? 'print' : 'view');
echo $this->element('Admin.print_letter_master', isset($this->viewVars) ? $this->viewVars : array());
?>
