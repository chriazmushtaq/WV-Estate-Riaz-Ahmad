<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingTemplatesController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingTemplate',
        'BillingManager.BillingSociety'
    );

    public function index()
    {
        $societyId = $this->_getActiveSocietyId();
        $templates = $this->BillingTemplate->find('all', array(
            'conditions' => array('BillingTemplate.society_id' => array(1, $societyId)),
            'order' => array('BillingTemplate.is_default' => 'DESC', 'BillingTemplate.id' => 'ASC')
        ));
        $this->set(compact('templates', 'societyId'));
    }

    public function set_default($id = null)
    {
        $societyId = $this->_getActiveSocietyId();
        $tpl = $this->BillingTemplate->findById($id);
        if(!empty($tpl))
        {
            $tType = $tpl['BillingTemplate']['template_type'];
            $this->BillingTemplate->updateAll(
                array('BillingTemplate.is_default' => 0),
                array('BillingTemplate.template_type' => $tType, 'BillingTemplate.society_id' => $societyId)
            );
            $this->BillingTemplate->id = (int)$id;
            $this->BillingTemplate->saveField('is_default', 1);
            $this->Session->setFlash(sprintf(__('Template "%s" set as default.'), h($tpl['BillingTemplate']['name'])), 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('controller' => 'BillingSettings', 'action' => 'index', '?' => array('tab' => 'templates')));
    }

    public function add()
    {
        $societyId = $this->_getActiveSocietyId();
        if($this->request->is('post'))
        {
            $this->BillingTemplate->create();
            $data = $this->request->data;
            if(!isset($data['BillingTemplate']['society_id']))
            {
                $data['BillingTemplate']['society_id'] = $societyId;
            }
            if(!empty($data['BillingTemplate']['is_default']))
            {
                $this->BillingTemplate->updateAll(
                    array('BillingTemplate.is_default' => 0),
                    array('BillingTemplate.template_type' => $data['BillingTemplate']['template_type'], 'BillingTemplate.society_id' => $societyId)
                );
            }
            if($this->BillingTemplate->save($data))
            {
                $this->Session->setFlash(__('New template created successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'edit', $this->BillingTemplate->id));
            }
            else
            {
                $this->Session->setFlash(__('Could not create template. Please check form errors.'), 'flash', array('alert' => 'danger'));
            }
        }
        $societies = $this->BillingSociety->find('all');
        $this->set(compact('societies', 'societyId'));
    }

    public function edit($id = null)
    {
        $this->BillingTemplate->id = (int)$id;
        $template = $this->BillingTemplate->read();
        if(empty($template))
        {
            $this->Session->setFlash(__('Template not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('controller' => 'BillingSettings', 'action' => 'index', '?' => array('tab' => 'templates')));
        }

        $societyId = !empty($template['BillingTemplate']['society_id']) ? (int)$template['BillingTemplate']['society_id'] : 1;

        if($this->request->is(array('post', 'put')))
        {
            $data = $this->request->data;
            if(!empty($data['BillingTemplate']['is_default']))
            {
                $this->BillingTemplate->updateAll(
                    array('BillingTemplate.is_default' => 0),
                    array('BillingTemplate.template_type' => $data['BillingTemplate']['template_type'], 'BillingTemplate.society_id' => $societyId)
                );
            }

            if($this->BillingTemplate->save($data))
            {
                $this->Session->setFlash(__('Template saved successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'edit', $id));
            }
            else
            {
                $this->Session->setFlash(__('Could not save template.'), 'flash', array('alert' => 'danger'));
            }
        }
        else
        {
            $this->request->data = $template;
        }

        $societies = $this->BillingSociety->find('all');
        $this->set(compact('template', 'societies', 'societyId'));
    }

    public function delete($id = null)
    {
        $template = $this->BillingTemplate->findById($id);
        if(!empty($template))
        {
            if(!empty($template['BillingTemplate']['is_default']))
            {
                $this->Session->setFlash(__('Cannot delete a primary template. Set another template as primary first.'), 'flash', array('alert' => 'danger'));
            }
            else
            {
                $this->BillingTemplate->delete((int)$id);
                $this->Session->setFlash(__('Template deleted successfully.'), 'flash', array('alert' => 'success'));
            }
        }
        return $this->redirect(array('controller' => 'BillingSettings', 'action' => 'index', '?' => array('tab' => 'templates')));
    }

    public function preview($id = null)
    {
        $this->layout = false;
        $this->BillingTemplate->id = (int)$id;
        $template = $this->BillingTemplate->read();

        if (empty($template)) {
            $this->autoRender = false;
            echo "Template ID #{$id} not found.";
            return;
        }

        $tplContent = !empty($template['BillingTemplate']['html_content']) ? $template['BillingTemplate']['html_content'] : (!empty($template['BillingTemplate']['content']) ? $template['BillingTemplate']['content'] : '');

        if (empty($tplContent)) {
            // Load default full template from DB fallback (Template #19 or #17)
            $fallbackTpl = $this->BillingTemplate->find('first', array(
                'conditions' => array('NOT' => array('BillingTemplate.html_content' => array(null, ''))),
                'order' => array('BillingTemplate.is_default' => 'DESC', 'BillingTemplate.id' => 'DESC')
            ));
            if (!empty($fallbackTpl['BillingTemplate']['html_content'])) {
                $tplContent = $fallbackTpl['BillingTemplate']['html_content'];
            }
        }

        // 12 Months Demo Payment History Rows
        $historyRows = '';
        $basePeriod = date('Y-m-01');
        for ($i = 0; $i < 12; $i++) {
            $pLabel = date('M-Y', strtotime("-{$i} months", strtotime($basePeriod)));
            $hUnits = ($i == 0) ? '44.5' : rand(30, 80) . '.0';
            $hTotal = ($i == 0) ? '4,876.30' : number_format(rand(3500, 6000), 2);
            $hPaid = ($i == 0) ? '0.00' : $hTotal;
            $hAdj = '0.00';
            $hSt = ($i == 0) ? 'Unpaid' : 'Paid';
            $historyRows .= "<tr><td>{$pLabel}</td><td>{$hUnits}</td><td>{$hTotal}</td><td>{$hPaid}</td><td>{$hAdj}</td><td>{$hSt}</td></tr>";
        }

        // Demo data replacements matching all template placeholders
        $replacements = array(
            '{{society_logo}}' => '<img src="https://whitevillas.com.pk/wp-content/uploads/2026/02/WHITE-VILLAS-LOGO.png" style="max-height:60px;object-fit:contain;" alt="Logo" />',
            '{{society_name}}' => 'White Villas Lahore',
            '{{society_code}}' => 'WVAB',
            '{{society_address}}' => 'Awami Block White Villas Bahria Town Lahore, PAKISTAN',
            '{{society_mobile}}' => '+92 339 1101156',
            '{{society_phone}}' => '+92 339 1101156',
            '{{bill_no}}' => 'WV-BILL-2026-08-0018',
            '{{bill_number}}' => 'WV-BILL-2026-08-0018',
            '{{consumer_id}}' => 'WV7006',
            '{{client_name}}' => 'MUHAMMAD ANSAR',
            '{{property_id}}' => 'NG0215086-GF (5 Marla House)',
            '{{client_address}}' => 'House # 104, Awami Block, White Villas, Bahria Town Lahore',
            '{{client_phone}}' => '+92 300 1234567',
            '{{client_cnic}}' => '35201-1234567-1',
            '{{billing_month}}' => 'Aug-2026',
            '{{issue_date}}' => '17-Aug-2026',
            '{{due_date}}' => '20-Aug-2026',
            '{{validity_date}}' => '30-Aug-2026',
            '{{utility_type}}' => 'Electricity',
            '{{previous_reading}}' => '1420.0',
            '{{current_reading}}' => '1464.5',
            '{{units_consumed}}' => '44.5',
            '{{unit_rate}}' => '58.34',
            '{{consumption_amount}}' => '2,596.13',
            '{{utility_rows}}' => '<tr><td class="p-1 px-3 border-r border-slate-200 font-bold">Electricity Meter #EL-9921 (Prev: 1420.0 | Curr: 1464.5 | Consumed: 44.5 Units @ 58.34)</td><td class="p-1 px-3 text-right font-bold">2,596.13</td></tr>',
            '{{maintenance_rows}}' => '<tr class="bg-blue-50/20"><td colspan="2" class="p-0.5 px-3 border-r border-blue-300 font-black text-[9px] uppercase text-blue-900">SOCIETY MAINTENANCE BREAKDOWN</td></tr>' .
                                     '<tr><td class="p-1 px-3 border-r border-blue-300">Services Chargers</td><td class="p-1 px-3 text-right">500.00</td></tr>' .
                                     '<tr><td class="p-1 px-3 border-r border-blue-300">Water Supply Bill</td><td class="p-1 px-3 text-right">1,101.00</td></tr>' .
                                     '<tr><td class="p-1 px-3 border-r border-blue-300">Garbage Collection Charge</td><td class="p-1 px-3 text-right">350.00</td></tr>' .
                                     '<tr><td class="p-1 px-3 border-r border-blue-300">Security Guard Charge</td><td class="p-1 px-3 text-right">300.00</td></tr>',
            '{{other_rows}}' => '',
            '{{arrears}}' => '0.00',
            '{{arrears_amount}}' => '0.00',
            '{{extra_charges}}' => '2,251.00',
            '{{extra_charges_amount}}' => '2,251.00',
            '{{total_amount}}' => '4,847.13',
            '{{late_fee}}' => '339.30',
            '{{late_fee_percent}}' => '7',
            '{{payable_after_due}}' => '5,186.43',
            '{{adjustment_debit}}' => '0.00',
            '{{adjustment_credit}}' => '0.00',
            '{{adjustment_note}}' => 'N/A',
            '{{bank_details}}' => '<div style="display:flex;justify-content:space-between;align-items:center;gap:15px;padding:8px;background:#f8fafc;border:1px solid #cbd5e1;border-radius:6px;">' .
                                  '<div style="font-size:11px;color:#0f172a;"><strong>United Bank Limited (UBL)</strong><br/>Account Title: <strong>WHITE VILLAS LAHORE</strong><br/>Account No: <strong>0773310485346</strong><br/>IBAN: PK36UNIL0109000293817112</div>' .
                                  '<div style="text-align:right;"><img src="https://whitevillas.com.pk/wp-content/uploads/2026/02/WHITE-VILLAS-BANK-ACCOUNT-QR-CODE.jpeg" style="width:70px;height:70px;object-fit:contain;border:1px solid #cbd5e1;border-radius:4px;padding:2px;background:#fff;" alt="Raast QR" /></div></div>',
            '{{paid_slip_block}}' => '',
            '{{history_rows}}' => $historyRows,
            '{{advertisement}}' => '<div style="font-size:11px;color:#64748b;font-weight:700;">WHITE VILLAS RESIDENT PORTAL ADVERTISING SPACE</div>',
            '{{urdu_notice}}' => 'احتیاط: ادائیگی سے پہلے براہِ کرم بل کی تفصیلات کی تصدیق کر لیں۔ بل پر درج اکاؤنٹ اور کیو آر کوڈ کی معلومات استعمال کریں۔'
        );

        $html = str_replace(array_keys($replacements), array_values($replacements), $tplContent);
        $this->set('previewHtml', $html);
        $this->set('template', $template);
        $this->render('preview');
    }
}
