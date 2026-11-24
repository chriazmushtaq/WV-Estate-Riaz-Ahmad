<?php
App::uses('ConfigurationsController', 'Admin.Controller');

class PaymentPlanSettingsController extends ConfigurationsController
{
    public function index()
    {
        $this->set('paymentPlanSettingsController', 'PaymentPlanSettings');
        $this->set('paymentPlanSettingsAction', 'index');
        return $this->paymentplans();
    }

    public function installtable()
    {
        $this->loadModel('AgencyPaymentPlanPage');
        if($this->AgencyPaymentPlanPage->ensureSchema())
        {
            $this->Session->setFlash(__('Payment plan settings table is ready.'),'flash',array('alert'=>'success'));
        }
        else
        {
            $table = method_exists($this->AgencyPaymentPlanPage, 'paymentPlanTableName') ? $this->AgencyPaymentPlanPage->paymentPlanTableName() : 'agency_payment_plan_pages';
            $this->Session->setFlash(sprintf(__('Unable to create %s. Please import app/Config/payment_plan_settings_table.sql in phpMyAdmin or grant CREATE permission once.'), $table),'flash',array('alert'=>'danger'));
        }
        return $this->redirect(array('controller'=>'PaymentPlanSettings','action'=>'index','?' => array('refresh_menu'=>1)));
    }
}
?>
