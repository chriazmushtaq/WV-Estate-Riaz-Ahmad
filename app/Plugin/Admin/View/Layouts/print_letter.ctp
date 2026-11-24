<?php
/**
 * Shared Master Layout for Print Preview and PDF Export (Admin Plugin)
 */
$renderedDescription = $this->fetch('content');
echo $this->element('print_letter_master', array_merge(
    isset($this->viewVars) ? $this->viewVars : array(),
    array('renderedDescription' => $renderedDescription)
));
?>
