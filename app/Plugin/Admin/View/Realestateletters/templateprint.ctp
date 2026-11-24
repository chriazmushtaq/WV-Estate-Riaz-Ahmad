<?php
/**
 * Real Estate Letter Template Print & Preview View
 * Standardized 1:1 Shared Master Engine with PDF Export
 */
$this->set('mode', 'print');
echo $this->element('Admin.print_letter_master', isset($this->viewVars) ? $this->viewVars : array());
?>
