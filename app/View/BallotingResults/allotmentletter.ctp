<?php
/**
 * Official Backend Allotment Letter Print View (Duplicate Copy)
 * Standardized Shared Master Print Engine
 */
$this->set('clientWatermark', true);
$this->set('showClientDuplicateWatermark', true);
$this->set('mode', (isset($autoPrint) && $autoPrint) ? 'print' : 'view');
echo $this->element('Admin.print_letter_master', isset($this->viewVars) ? $this->viewVars : array());
?>
