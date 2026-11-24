<?php
$agentOptions = '<option value="">' . __('Please Select') . '</option>';
if (!empty($agentName)) {
    foreach ($agentName as $k => $v) {
        $agentOptions .= '<option value="' . (int)$k . '">' . h($v) . '</option>';
    }
}
$postArr = array(
    '#DealArea' => (isset($post['Property']['area']) ? $post['Property']['area'] : '') . ' ' . (isset($post['Unit']['name']) ? $post['Unit']['name'] : ''),
    '#DealAmount' => (isset($post['Property']['price']) ? $post['Property']['price'] : 0),
    '#contractId' => (isset($post['Property']['contract_id']) ? $post['Property']['contract_id'] : ''),
    '#agentName' => $agentOptions,
    '#DealInvoiceNo' => (isset($invoiceNo) ? $invoiceNo : '')
);
echo json_encode($postArr);
?>
