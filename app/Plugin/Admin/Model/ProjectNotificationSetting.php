<?php
App::uses('AdminAppModel', 'Admin.Model');

class ProjectNotificationSetting extends AdminAppModel {
    public $useTable = 'project_notification_settings';

    public $belongsTo = array(
        'Project' => array(
            'className' => 'Admin.Project',
            'foreignKey' => 'project_id'
        )
    );

    public function ensureSchema()
    {
        try {
            $this->query("CREATE TABLE IF NOT EXISTS `project_notification_settings` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `project_id` int(11) NOT NULL DEFAULT '0',
              `in_portal_notifications` tinyint(1) NOT NULL DEFAULT '1',
              `whatsapp_reminders` tinyint(1) NOT NULL DEFAULT '1',
              `whatsapp_receipts` tinyint(1) NOT NULL DEFAULT '1',
              `whatsapp_letters` tinyint(1) NOT NULL DEFAULT '1',
              `whatsapp_bot` tinyint(1) NOT NULL DEFAULT '1',
              `whatsapp_gateway_id` int(11) NOT NULL DEFAULT '0',
              `reminder_days_before` varchar(50) NOT NULL DEFAULT '7,1',
              `template_reminder_7d` text,
              `template_reminder_1d` text,
              `template_receipt` text,
              `template_letter` text,
              `template_bot_welcome` text,
              `created` datetime DEFAULT NULL,
              `modified` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `idx_project` (`project_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        } catch(Exception $e) {}
    }

    public function getDefaultSettings($projectId = 0)
    {
        return array(
            'id' => null,
            'project_id' => (int)$projectId,
            'in_portal_notifications' => 1,
            'whatsapp_reminders' => 1,
            'whatsapp_receipts' => 1,
            'whatsapp_letters' => 1,
            'whatsapp_bot' => 1,
            'whatsapp_gateway_id' => 0,
            'reminder_days_before' => '7,1',
            'template_reminder_7d' => "⏰ *Installment Reminder (Due in 7 Days)*\n\nDear *{client_name}*,\n\nThis is a friendly reminder that your installment of *Rs {due_amount}* for *{property_name}* is scheduled on *{due_date}*.\n\n📄 *Statement & Pay:* {pay_link}\n\nThank you for choosing {project_name}!",
            'template_reminder_1d' => "⚠️ *URGENT: Installment Due Tomorrow!*\n\nDear *{client_name}*,\n\nYour installment of *Rs {due_amount}* for *{property_name}* is due on *{due_date}*.\n\nTo avoid late surcharge, please clear your installment on time.\n📄 *Direct Link:* {pay_link}\n\nHelpline: {project_phone}",
            'template_receipt' => "✅ *Payment Receipt Issued - {project_name}*\n\nDear *{client_name}*,\n\nWe have successfully received your payment of *Rs {amount}* for *{property_name}*.\n\n🧾 *Receipt No:* {receipt_no}\n📅 *Payment Date:* {paid_date}\n💰 *Remaining Due:* Rs {due_amount}\n\n📄 *Download Official Receipt:* {receipt_link}\n\nThank you!",
            'template_letter' => "📜 *Official Real Estate Letter Issued*\n\nDear *{client_name}*,\n\nAn official *{letter_type}* has been issued for your property *{property_name}*.\n\n🔖 *Document Ref:* {ref_no}\n📅 *Issue Date:* {issue_date}\n\n📄 *View & Download Verified Letter:* {letter_link}\n\n{project_name} Management",
            'template_bot_welcome' => "👋 *Welcome to {project_name} Assistant!*\n\nHow can I help you today? Reply with any of the following keywords:\n\n👉 *LEDGER* - Check your total paid & remaining balance\n👉 *NEXT DUE* - View your upcoming installment details\n👉 *STATEMENT* - Get your live statement of account\n👉 *BALLOT* - Check your plot allotment details\n👉 *HELP* - Contact support or human advisor"
        );
    }

    public function getSettings($projectId = 0)
    {
        $this->ensureSchema();
        $row = $this->find('first', array(
            'conditions' => array('ProjectNotificationSetting.project_id' => (int)$projectId),
            'recursive' => -1
        ));

        if (!empty($row['ProjectNotificationSetting'])) {
            return $row['ProjectNotificationSetting'];
        }

        // If not found for specific project, fall back to global Project ID 0
        if ($projectId > 0) {
            $globalRow = $this->find('first', array(
                'conditions' => array('ProjectNotificationSetting.project_id' => 0),
                'recursive' => -1
            ));
            if (!empty($globalRow['ProjectNotificationSetting'])) {
                $settings = $globalRow['ProjectNotificationSetting'];
                $settings['id'] = null;
                $settings['project_id'] = (int)$projectId;
                return $settings;
            }
        }

        return $this->getDefaultSettings($projectId);
    }
}
