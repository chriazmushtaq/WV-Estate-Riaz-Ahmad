<?php
App::uses('AdminAppModel', 'Admin.Model');
class WhatsappLog extends AdminAppModel {
    public $useTable = 'whatsapp_logs';
    public $order = array('WhatsappLog.id' => 'DESC');
}