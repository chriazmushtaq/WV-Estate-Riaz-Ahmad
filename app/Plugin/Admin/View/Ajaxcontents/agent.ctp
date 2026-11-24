<?php
$selectId = isset($selectId) && trim((string)$selectId) !== '' ? trim((string)$selectId) : 'agentId';
$fieldName = isset($fieldName) && trim((string)$fieldName) !== '' ? trim((string)$fieldName) : 'user_id';
$isMultiple = !empty($isMultiple);
$selectedValues = isset($selectedValues) ? (array)$selectedValues : array();
$selectOptions = array(
    'empty' => $isMultiple ? false : __('Please Select'),
    'class' => 'form-control',
    'div' => false,
    'id' => $selectId,
    'label' => false
);
if($isMultiple)
{
    $selectOptions['multiple'] = 'multiple';
    $selectOptions['value'] = $selectedValues;
    $selectOptions['style'] = 'min-height:220px;';
}
echo $this->Form->select($fieldName,$agentName,$selectOptions);
?>
