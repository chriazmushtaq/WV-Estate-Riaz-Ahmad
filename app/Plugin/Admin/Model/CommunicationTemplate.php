<?php
App::uses('ConnectionManager', 'Model');

class CommunicationTemplate extends AppModel
{
    public $useTable = 'communication_templates';
    public $cacheSources = false;

    public function ensureSchema()
    {
        $db = ConnectionManager::getDataSource($this->useDbConfig);
        $db->rawQuery("CREATE TABLE IF NOT EXISTS `communication_templates` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL DEFAULT 0,
            `name` varchar(191) NOT NULL DEFAULT '',
            `code` varchar(60) NOT NULL DEFAULT '',
            `channel` varchar(30) NOT NULL DEFAULT 'sms',
            `event_key` varchar(60) NOT NULL DEFAULT 'custom_bulk',
            `subject` varchar(191) DEFAULT '',
            `body` longtext,
            `status` varchar(20) NOT NULL DEFAULT 'Published',
            `is_default` tinyint(1) NOT NULL DEFAULT 0,
            `created_by` int(11) NOT NULL DEFAULT 0,
            `modified_by` int(11) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `project_id` (`project_id`),
            KEY `channel` (`channel`),
            KEY `event_key` (`event_key`),
            KEY `status` (`status`),
            UNIQUE KEY `communication_template_code_unique` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        $this->_ensureColumns(array(
            'project_id' => "ALTER TABLE `communication_templates` ADD `project_id` int(11) NOT NULL DEFAULT 0 AFTER `id`",
            'code' => "ALTER TABLE `communication_templates` ADD `code` varchar(60) NOT NULL DEFAULT '' AFTER `name`",
            'channel' => "ALTER TABLE `communication_templates` ADD `channel` varchar(30) NOT NULL DEFAULT 'sms' AFTER `code`",
            'event_key' => "ALTER TABLE `communication_templates` ADD `event_key` varchar(60) NOT NULL DEFAULT 'custom_bulk' AFTER `channel`",
            'subject' => "ALTER TABLE `communication_templates` ADD `subject` varchar(191) DEFAULT '' AFTER `event_key`",
            'body' => "ALTER TABLE `communication_templates` ADD `body` longtext AFTER `subject`",
            'is_default' => "ALTER TABLE `communication_templates` ADD `is_default` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`",
            'created_by' => "ALTER TABLE `communication_templates` ADD `created_by` int(11) NOT NULL DEFAULT 0 AFTER `is_default`",
            'modified_by' => "ALTER TABLE `communication_templates` ADD `modified_by` int(11) NOT NULL DEFAULT 0 AFTER `created_by`"
        ));
        $this->seedDefaults();
    }

    protected function _ensureColumns($columns)
    {
        foreach((array)$columns as $columnName => $sql)
        {
            try
            {
                $rows = (array)$this->query("SHOW COLUMNS FROM `communication_templates` LIKE '".addslashes($columnName)."'");
                if(empty($rows))
                {
                    $this->query($sql);
                }
            }
            catch(Exception $e)
            {
            }
        }
    }

    public function normalizeSaveData($data, $userId = 0)
    {
        $data = (array)$data;
        $channel = strtolower(trim((string)(isset($data['channel']) ? $data['channel'] : 'sms')));
        if(!in_array($channel, array('sms', 'whatsapp', 'email', 'letter'), true))
        {
            $channel = 'sms';
        }
        $status = trim((string)(isset($data['status']) ? $data['status'] : 'Published'));
        if(!in_array($status, array('Published', 'Unpublished'), true))
        {
            $status = 'Published';
        }
        $eventKey = trim((string)(isset($data['event_key']) ? $data['event_key'] : 'custom_bulk'));
        if($eventKey === '')
        {
            $eventKey = 'custom_bulk';
        }
        $name = trim((string)(isset($data['name']) ? $data['name'] : ''));
        $code = strtoupper(trim((string)(isset($data['code']) ? $data['code'] : '')));
        if($code === '')
        {
            $code = strtoupper(substr($channel, 0, 3)).'-'.strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $eventKey)).'-'.time();
        }
        $saveData = array(
            'project_id' => !empty($data['project_id']) ? (int)$data['project_id'] : 0,
            'name' => $name,
            'code' => substr($code, 0, 60),
            'channel' => $channel,
            'event_key' => substr($eventKey, 0, 60),
            'subject' => isset($data['subject']) ? trim((string)$data['subject']) : '',
            'body' => isset($data['body']) ? (string)$data['body'] : '',
            'status' => $status,
            'is_default' => !empty($data['is_default']) ? 1 : 0,
            'modified_by' => (int)$userId
        );
        if(empty($data['id']))
        {
            $saveData['created_by'] = (int)$userId;
        }
        else
        {
            $saveData['id'] = (int)$data['id'];
        }
        return $saveData;
    }

    public function seedDefaults()
    {
        $defaults = $this->defaultTemplates();
        foreach($defaults as $row)
        {
            $existing = (array)$this->find('first', array(
                'fields' => array('CommunicationTemplate.id'),
                'conditions' => array('CommunicationTemplate.code' => $row['code']),
                'recursive' => -1
            ));
            if(!empty($existing['CommunicationTemplate']['id']))
            {
                continue;
            }
            $row['is_default'] = 1;
            $row['status'] = 'Published';
            $this->create();
            $this->save(array('CommunicationTemplate' => $row), array('validate' => false));
        }
    }

    public function defaultTemplates()
    {
        return array(
            array(
                'name' => 'Overdue Installment SMS',
                'code' => 'AUTO-OVERDUE-SMS',
                'channel' => 'sms',
                'event_key' => 'installment_overdue',
                'subject' => '',
                'body' => 'Dear {client_name}, your installment for {deal_invoice_no} is overdue. Due date: {due_date}. Amount: {amount}. Please contact {agency_name} at {agency_phone}.'
            ),
            array(
                'name' => 'Payment Received WhatsApp',
                'code' => 'AUTO-PAYMENT-WHATSAPP',
                'channel' => 'whatsapp',
                'event_key' => 'payment_received',
                'subject' => '',
                'body' => "Dear *{client_name}*,\n\nYour payment has been received for *{deal_invoice_no}*.\nAmount: *{amount}*\nReceipt: {receipt_url}\n\nRegards,\n*{agency_name}*"
            ),
            array(
                'name' => 'Agency Branded Installment Email',
                'code' => 'AUTO-INSTALLMENT-EMAIL',
                'channel' => 'email',
                'event_key' => 'installment_overdue',
                'subject' => 'Overdue Installment Notice - {deal_invoice_no}',
                'body' => $this->_defaultAgencyEmailHtml()
            ),
            array(
                'name' => 'Letter Issued Email',
                'code' => 'AUTO-LETTER-EMAIL',
                'channel' => 'email',
                'event_key' => 'letter_issued',
                'subject' => 'Letter Issued - {letter_title}',
                'body' => $this->_defaultLetterEmailHtml()
            ),
            array(
                'name' => 'Final Notice Letter Task',
                'code' => 'AUTO-FINAL-NOTICE-LETTER',
                'channel' => 'letter',
                'event_key' => 'installment_overdue',
                'subject' => 'Final Notice Letter - {deal_invoice_no}',
                'body' => '<p><strong>Date:</strong> {today}</p><p>Dear {client_name},</p><p>This is a final reminder for overdue installment against deal <strong>{deal_invoice_no}</strong>.</p><p><strong>Amount:</strong> {amount}<br><strong>Due Date:</strong> {due_date}</p><p>Regards,<br>{agency_name}</p>'
            )
        );
    }

    protected function _defaultAgencyEmailHtml()
    {
        return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>{subject}</title></head>
<body style="margin:0;padding:0;background:#eef3f7;font-family:Arial,Helvetica,sans-serif;color:#172033;">
  <div style="max-width:760px;margin:0 auto;background:#ffffff;border:1px solid #dce7f2;">
    <div style="background:#082f49;color:#ffffff;padding:24px 28px;text-align:center;">
      <img src="{agency_logo_url}" alt="{agency_name}" style="max-height:76px;max-width:160px;background:#fff;border-radius:8px;padding:6px;margin-bottom:10px;">
      <h1 style="margin:0;font-size:25px;line-height:1.2;">{agency_name}</h1>
      <p style="margin:8px 0 0;color:#cde7ff;font-size:13px;">{agency_address}</p>
    </div>
    <div style="padding:28px;">
      <h2 style="margin:0 0 14px;color:#b91c1c;font-size:22px;">Overdue Installment Notice</h2>
      <p style="font-size:15px;line-height:1.6;">Dear <strong>{client_name}</strong>,</p>
      <p style="font-size:15px;line-height:1.6;">Your installment for the following deal is overdue. Please arrange payment or contact our office for assistance.</p>
      <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse;font-size:14px;margin:18px 0;">
        <tr><td style="border:1px solid #dbe5ef;background:#f8fafc;"><strong>Deal / File No</strong></td><td style="border:1px solid #dbe5ef;">{deal_invoice_no}</td></tr>
        <tr><td style="border:1px solid #dbe5ef;background:#f8fafc;"><strong>Property</strong></td><td style="border:1px solid #dbe5ef;">{property_name}</td></tr>
        <tr><td style="border:1px solid #dbe5ef;background:#f8fafc;"><strong>Due Date</strong></td><td style="border:1px solid #dbe5ef;">{due_date}</td></tr>
        <tr><td style="border:1px solid #dbe5ef;background:#f8fafc;"><strong>Amount</strong></td><td style="border:1px solid #dbe5ef;"><strong>{amount}</strong></td></tr>
      </table>
      <p style="font-size:14px;line-height:1.6;">PDF / attachment link: <a href="{attachment_url}" style="color:#0f766e;">{attachment_url}</a></p>
      <p style="font-size:15px;line-height:1.6;">Regards,<br><strong>{agency_name}</strong></p>
    </div>
    <div style="background:#07152a;color:#dbeafe;text-align:center;padding:16px 22px;font-size:12px;line-height:1.6;">
      {agency_name} | {agency_phone} | {agency_email}<br>{agency_address}
    </div>
  </div>
</body>
</html>';
    }

    protected function _defaultLetterEmailHtml()
    {
        return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>{subject}</title></head>
<body style="margin:0;padding:0;background:#eef3f7;font-family:Arial,Helvetica,sans-serif;color:#172033;">
  <div style="max-width:720px;margin:0 auto;background:#ffffff;border:1px solid #dce7f2;">
    <div style="background:#082f49;color:#ffffff;padding:22px 26px;text-align:center;">
      <h1 style="margin:0;font-size:24px;">{agency_name}</h1>
      <p style="margin:8px 0 0;color:#cde7ff;">Official Letter Notification</p>
    </div>
    <div style="padding:26px;">
      <p>Dear <strong>{client_name}</strong>,</p>
      <p>Your letter has been issued.</p>
      <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse;font-size:14px;margin:18px 0;">
        <tr><td style="border:1px solid #dbe5ef;background:#f8fafc;"><strong>Letter</strong></td><td style="border:1px solid #dbe5ef;">{letter_title}</td></tr>
        <tr><td style="border:1px solid #dbe5ef;background:#f8fafc;"><strong>Status</strong></td><td style="border:1px solid #dbe5ef;">{letter_status}</td></tr>
        <tr><td style="border:1px solid #dbe5ef;background:#f8fafc;"><strong>Deal</strong></td><td style="border:1px solid #dbe5ef;">{deal_invoice_no}</td></tr>
      </table>
      <p>Print / verify link: <a href="{letter_url}" style="color:#0f766e;">{letter_url}</a></p>
      <p>Regards,<br><strong>{agency_name}</strong></p>
    </div>
    <div style="background:#07152a;color:#dbeafe;text-align:center;padding:14px;font-size:12px;">{agency_name} | {agency_phone} | {agency_email}</div>
  </div>
</body>
</html>';
    }
}
?>
