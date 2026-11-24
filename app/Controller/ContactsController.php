<?php
App::uses('CakeEmail', 'Network/Email');

class ContactsController extends AppController
{
    public function index()
    {
        $this->loadModel('Content');
        $this->loadModel('WebsiteContact');
        $this->loadModel('Lead');

        $contactValueArr = $this->Content->find('first', array('conditions' => array('id' => 2)));
        if (!empty($contactValueArr['Content']['main_content'])) {
            $this->set('contactValue', $contactValueArr['Content']['main_content']);
        }

        if ($this->request->is('post')) {
            return $this->_processContactSubmission();
        }
    }

    /**
     * Dedicated Inquiry Submission Endpoint (Handles both AJAX & Standard Form Submissions)
     */
    public function send_inquiry()
    {
        $this->autoRender = false;
        if ($this->request->is('post')) {
            return $this->_processContactSubmission();
        }
        return $this->redirect(array('controller' => 'Fronts', 'action' => 'display', 'home'));
    }

    /**
     * Internal processor to store inquiries in WebsiteContact & Leads table and send notification
     */
    protected function _processContactSubmission()
    {
        $this->loadModel('WebsiteContact');
        $this->loadModel('Lead');

        $contactData = isset($this->request->data['Contact']) ? (array)$this->request->data['Contact'] : (array)$this->request->data;
        $name = trim(isset($contactData['name']) ? $contactData['name'] : '');
        $lastName = trim(isset($contactData['last_name']) ? $contactData['last_name'] : '');
        if ($lastName !== '') {
            $name = trim($name . ' ' . $lastName);
        }
        $email = trim(isset($contactData['email']) ? $contactData['email'] : '');
        $phone = trim(isset($contactData['phone']) ? $contactData['phone'] : '');
        $subject = trim(isset($contactData['subject']) ? $contactData['subject'] : '');
        if ($subject === '') {
            $subject = 'Website Web Inquiry / Property Consultation';
        }
        $bodyMessage = trim(isset($contactData['message']) ? $contactData['message'] : (isset($contactData['content']) ? $contactData['content'] : ''));
        $source = trim(isset($contactData['source']) ? $contactData['source'] : 'Web Inquiry');
        $propertyId = isset($contactData['property_id']) ? (int)$contactData['property_id'] : null;

        $isAjax = $this->request->is('ajax') || !empty($this->request->data['is_ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($name === '' || $phone === '') {
            $errorMsg = __('Please provide your name and phone number so our team can reach you.');
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'error', 'message' => $errorMsg));
                exit();
            }
            $this->Session->setFlash($errorMsg, 'flash', array('alert' => 'danger'));
            return $this->redirect($this->referer(array('controller' => 'Fronts', 'action' => 'display', 'home')));
        }

        try {
            $ipAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

            // 1. Save into website_contacts table
            $this->WebsiteContact->create();
            $contactRecord = array(
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $bodyMessage,
                'source' => $source,
                'ip_address' => $ipAddress,
                'status' => 'New',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            );
            $this->WebsiteContact->save(array('WebsiteContact' => $contactRecord), false);

            // 2. Also register into CRM leads table for Super Admin & Agents
            $this->Lead->create();
            $leadRecord = array(
                'user_id' => 1, // Default to Super Admin
                'status_id' => 1, // Default active/new lead status
                'lead_name' => $name,
                'lead_email' => $email,
                'lead_mobile' => $phone,
                'lead_source' => 'Web Inquiry',
                'remarks' => ($subject ? "[{$subject}] " : '') . $bodyMessage,
                'property_id' => $propertyId,
                'follow_up' => date('Y-m-d H:i:s', strtotime('+1 day')),
                'lead_temperature' => 'hot',
                'last_contact_date' => date('Y-m-d')
            );
            $this->Lead->save(array('Lead' => $leadRecord), false);

            // 3. Send email notification if siteEmail is configured
            $toEmail = $this->siteEmail;
            if ($toEmail) {
                $emailBody = __('New Web Inquiry Received from Website Frontend:') . '<br><br>' .
                    '<strong>' . __('Name') . ':</strong> ' . h($name) . '<br>' .
                    '<strong>' . __('Email') . ':</strong> ' . h($email) . '<br>' .
                    '<strong>' . __('Phone') . ':</strong> ' . h($phone) . '<br>' .
                    '<strong>' . __('Subject') . ':</strong> ' . h($subject) . '<br>' .
                    '<strong>' . __('Source') . ':</strong> ' . h($source) . '<br>' .
                    '<strong>' . __('Message') . ':</strong> ' . nl2br(h($bodyMessage)) . '<br><br>' .
                    '<em>' . __('Time: ') . date('Y-m-d H:i:s') . ' | IP: ' . h($ipAddress) . '</em>';

                try {
                    $Email = new CakeEmail();
                    $Email->transport($this->emailSettype);
                    if ($this->emailSettype == "Smtp") {
                        $Email->config(array(
                            'host' => $this->emailHost,
                            'port' => $this->emailPort,
                            'username' => $this->emailUsername,
                            'password' => $this->emailPassword,
                            'timeout' => 5
                        ));
                    }
                    $fromEmail = $email != '' ? $email : $this->siteEmail;
                    $Email->from(array($fromEmail => $name));
                    $Email->to($toEmail);
                    $Email->template('default');
                    $Email->emailFormat('html');
                    $Email->subject("[Web Inquiry] " . $subject . " - " . $name);
                    $Email->send($emailBody);
                } catch (Exception $emailEx) {
                    $this->log('Web contact inquiry email error: ' . $emailEx->getMessage(), 'error');
                }
            }

            $successMsg = __('Thank you! Your inquiry has been received successfully. Our team will contact you shortly.');

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'success', 'message' => $successMsg));
                exit();
            }

            $this->Session->setFlash($successMsg, 'flash', array('alert' => 'success'));
            return $this->redirect($this->referer(array('controller' => 'Fronts', 'action' => 'display', 'home')));
        } catch (Exception $e) {
            $errorMsg = __('Failed to submit inquiry: ') . $e->getMessage();
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'error', 'message' => $errorMsg));
                exit();
            }
            $this->Session->setFlash($errorMsg, 'flash', array('alert' => 'danger'));
            return $this->redirect($this->referer(array('controller' => 'Fronts', 'action' => 'display', 'home')));
        }
    }
}
