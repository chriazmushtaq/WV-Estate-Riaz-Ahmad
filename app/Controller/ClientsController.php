<?php
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');
App::uses('CakeTime', 'Utility');
App::uses('CakeSession', 'Model/Datasource');
class ClientsController extends AppController
{
    var $helpers = array('Form');
    public function beforeFilter()
    {
        parent::beforeFilter();
        if ($this->action != 'download_apk') {
            $this->authenticate();
        }
        $this->currentDateTime=CakeTime::format('Y-m-d H:i:s',CakeTime::convert(time(),$this->siteTimezone));
    }
    public function login()
    {
        if ($this->Session->check('frontUser'))
        {
            return $this->redirect(array('plugin' => false, 'admin' => false, 'controller' => 'Dashboards', 'action' => 'index'));
        }

        if ($this->request->is('post'))
        {
            $loginInput = '';
            if(!empty($this->request->data['Client']['email']))
            {
                $loginInput = trim((string)$this->request->data['Client']['email']);
            }
            elseif(!empty($this->request->data['Client']['login_id']))
            {
                $loginInput = trim((string)$this->request->data['Client']['login_id']);
            }
            elseif(!empty($this->request->data['Client']['user_id']))
            {
                $loginInput = trim((string)$this->request->data['Client']['user_id']);
            }
            elseif(!empty($this->request->data['Client']['username']))
            {
                $loginInput = trim((string)$this->request->data['Client']['username']);
            }

            if($loginInput === '')
            {
                $this->Session->setFlash(__('Please enter your Mobile number, CNIC, or Email to sign in.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'login'));
            }

            $plainPassword = isset($this->request->data['Client']['password']) ? (string)$this->request->data['Client']['password'] : '';
            if($plainPassword === '')
            {
                $this->Session->setFlash(__('Please enter your password.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'login'));
            }

            $searchConditions = $this->_buildClientSearchConditions($loginInput);

            $candidates = (array)$this->Client->find('all', array(
                'conditions' => $searchConditions,
                'order' => array('Client.status' => 'ASC', 'Client.id' => 'DESC'),
                'recursive' => -1
            ));

            $authenticatedUser = null;
            foreach($candidates as $candidate)
            {
                if(!empty($candidate['Client']['password']) && $this->_verifyPassword($plainPassword, $candidate['Client']['password']))
                {
                    $authenticatedUser = $candidate;
                    break;
                }
            }

            if($authenticatedUser !== null)
            {
                $clientStatus = !empty($authenticatedUser['Client']['status']) ? trim((string)$authenticatedUser['Client']['status']) : 'Active';
                if(strcasecmp($clientStatus, 'Active') === 0)
                {
                    if($this->_passwordNeedsRefresh($authenticatedUser['Client']['password']))
                    {
                        $this->_rehashPasswordForModel($this->Client, (int)$authenticatedUser['Client']['id'], $plainPassword);
                    }
                    $record_arr = array('Client' => array('id' => $authenticatedUser['Client']['id'], 'last_login' => $this->currentDateTime));
                    $this->Client->save($record_arr);
                    CakeSession::renew();
                    $this->Session->setFlash(__('Thank you for logging in!'), 'flash', array('alert' => 'success'));
                    $this->Session->write('frontUser', $authenticatedUser);
                    return $this->redirect(array('plugin' => false, 'admin' => false, 'controller' => 'Dashboards', 'action' => 'index'));
                }
                else
                {
                    if (strcasecmp($clientStatus, 'Block') === 0 || strcasecmp($clientStatus, 'Blocked') === 0) {
                        $this->Session->setFlash(__('Your account has been blocked. Please contact administrator to restore login access.'), 'flash', array('alert' => 'danger'));
                    } else {
                        $this->Session->setFlash(__d('default', "You are %s Member! Please contact administrator", $clientStatus), 'flash', array('alert' => 'danger'));
                    }
                    return $this->redirect(array('action' => 'login'));
                }
            }
            else
            {
                $this->Session->setFlash(__('Incorrect User ID (Mobile Number / CNIC / Email) or Password!'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'login'));
            }
        }
    }

    protected function _buildClientSearchConditions($input)
    {
        $input = trim((string)$input);
        if($input === '')
        {
            return array('Client.id' => 0);
        }

        $variants = array($input);
        $cleanDigits = preg_replace('/\D/', '', $input);

        // Email address
        if(strpos($input, '@') !== false)
        {
            $variants[] = strtolower($input);
            $variants[] = strtoupper($input);
        }

        // CNIC formatting (13 digits: XXXXX-XXXXXXX-X)
        if(strlen($cleanDigits) === 13)
        {
            $variants[] = $cleanDigits;
            $formattedCnic = substr($cleanDigits, 0, 5) . '-' . substr($cleanDigits, 5, 7) . '-' . substr($cleanDigits, 12, 1);
            $variants[] = $formattedCnic;
        }

        // Phone / Mobile variants (9 to 15 digits)
        if(strlen($cleanDigits) >= 9 && strlen($cleanDigits) <= 15)
        {
            $variants[] = $cleanDigits;
            $variants[] = '+' . $cleanDigits;

            if(strpos($cleanDigits, '03') === 0 && strlen($cleanDigits) === 11)
            {
                $withoutZero = substr($cleanDigits, 1);
                $variants[] = $withoutZero;
                $variants[] = '92' . $withoutZero;
                $variants[] = '+92' . $withoutZero;
                $variants[] = '0' . substr($withoutZero, 0, 3) . '-' . substr($withoutZero, 3);
            }
            elseif(strpos($cleanDigits, '923') === 0 && strlen($cleanDigits) === 12)
            {
                $withoutCountry = substr($cleanDigits, 2);
                $variants[] = $withoutCountry;
                $variants[] = '0' . $withoutCountry;
                $variants[] = '+92' . $withoutCountry;
                $variants[] = '0' . substr($withoutCountry, 0, 3) . '-' . substr($withoutCountry, 3);
            }
            elseif(strpos($cleanDigits, '3') === 0 && strlen($cleanDigits) === 10)
            {
                $variants[] = '0' . $cleanDigits;
                $variants[] = '92' . $cleanDigits;
                $variants[] = '+92' . $cleanDigits;
                $variants[] = '0' . substr($cleanDigits, 0, 3) . '-' . substr($cleanDigits, 3);
            }
        }

        $variants = array_values(array_unique(array_filter($variants)));

        $orConditions = array(
            'Client.email' => $variants,
            'Client.mobile' => $variants,
            'Client.phone' => $variants,
            'Client.pincode' => $variants
        );

        if(strlen($cleanDigits) >= 10)
        {
            $safeDigits = addslashes($cleanDigits);
            $safeLast10 = addslashes(substr($cleanDigits, -10));
            $orConditions[] = "REPLACE(COALESCE(Client.pincode, ''), '-', '') = '{$safeDigits}'";
            $orConditions[] = "RIGHT(REPLACE(REPLACE(REPLACE(COALESCE(Client.mobile, ''), '+', ''), '-', ''), ' ', ''), 10) = '{$safeLast10}'";
            $orConditions[] = "RIGHT(REPLACE(REPLACE(REPLACE(COALESCE(Client.phone, ''), '+', ''), '-', ''), ' ', ''), 10) = '{$safeLast10}'";
        }

        return array('OR' => $orConditions);
    }
    public function logout()
    {
        $this -> Session -> destroy();
        $this -> Session -> setFlash(__('You have logged out'),'flash', array('alert'=> 'success'));
        $this -> Redirect(array('plugin' => false, 'admin' => false, 'action' => 'login'));
        exit();
    }

    public function download_apk()
    {
        $file = APP . 'tmp' . DS . 'whitevillas.apk';
        if (file_exists($file)) {
            $this->response->file($file, array('download' => true, 'name' => 'WhiteVillas.apk'));
            return $this->response;
        }
        $webrootFile = WWW_ROOT . 'files' . DS . 'whitevillas.apk';
        if (file_exists($webrootFile)) {
            $this->response->file($webrootFile, array('download' => true, 'name' => 'WhiteVillas.apk'));
            return $this->response;
        }
        $this->Session->setFlash(__('APK file not found.'), 'flash', array('alert' => 'danger'));
        return $this->redirect(array('action' => 'login'));
    }
}
