<?php
/**
 * Real Estate Letter Export to PDF View
 * Standardized 1:1 Shared Master Engine matching Browser Print
 */
$this->set('mode', 'pdf');
echo $this->element('Admin.print_letter_master', isset($this->viewVars) ? $this->viewVars : array());
?>
