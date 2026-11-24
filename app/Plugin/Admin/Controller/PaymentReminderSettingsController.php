<?php
App::uses('PaymentremindersController', 'Admin.Controller');

class PaymentReminderSettingsController extends PaymentremindersController
{
    public function index()
    {
        return $this->redirect(array('controller' => 'Paymentreminders', 'action' => 'settings'));
    }

    public function settings($id = null)
    {
        $this->viewPath = 'Paymentreminders';
        return parent::settings();
    }
}
?>
