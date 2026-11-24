<?php
/**
 * Shared Master Real Estate Letter Print & PDF Wrapper Element (App level proxy)
 */
echo $this->element('Admin.print_letter_master', isset($this->viewVars) ? $this->viewVars : array());
?>
