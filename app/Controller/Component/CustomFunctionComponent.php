<?php
App::uses('Component','Controller');
App::uses('CakeTime', 'Utility');
App::uses('HttpSocket', 'Network/Http');
App::uses('CakeSession', 'Model/Datasource');
App::uses('LeadbotGatewayClient', 'Lib');
class CustomfunctionComponent extends Component
{
    public $components = array('Session');
    public $HttpSocket = null;
    protected $_smsGatewayReachability = array();

    public function secondsToWords($seconds)
    {
        $ret = "";
        $hours = intval(intval($seconds) / 3600);
        if($hours > 0)
        {
            $ret .= "$hours Hours ";
        }
        $minutes = bcmod((intval($seconds) / 60),60);
        if($hours > 0 || $minutes > 0)
        {
            $ret .= "$minutes Mins. ";
        }
        return $ret;
    }

    public function generate_rand($digit=6)
    {
        $no = substr(strtoupper(md5(uniqid(rand()))), 0, $digit);
        return $no;
    }

    public function upload($tmpFile, $file, $path, $width=150, $height=150, $fileName="", $isThumb=true, $extArr=array("jpg","gif","png","jpeg","webp","pdf","mp4","webm","mov","JPG","PNG","PDF","MP4"))
    {
        if(strlen($file) > 0)
        {
            $pathinfo = pathinfo(trim($file));
            $ext = isset($pathinfo['extension']) ? strtolower($pathinfo['extension']) : '';
            $allowedExts = array_map('strtolower', (array)$extArr);
            if(strlen($fileName) == 0 && in_array($ext, $allowedExts))
            {
                $imageDir = APP.'webroot'.DS;
                $fileName = date('Y-m-d').'-'.rand().'.'.$ext;
                $uploadPath = 'img'.DS.$path.DS;
                if(move_uploaded_file($tmpFile, $uploadPath.$fileName))
                {
                    if(in_array($ext, array('jpg','jpeg','png','gif','webp')))
                    {
                        $imgSizeArr = @getimagesize($uploadPath.$fileName);
                        if(!empty($imgSizeArr) && $imgSizeArr[0] > 800 && $imgSizeArr[1] > 600){
                            $this->resizedUrl($fileName, $uploadPath, $uploadPath, '800', '600');
                        }
                        if($isThumb == true)
                        {
                            $thumbPath = 'img'.DS."$path"."_thumb".DS;
                            $this->resizedUrl($fileName, $uploadPath, $thumbPath, $width, $height);
                        }
                    }
                    return $fileName;
                }
            }
        }
        return "";
    }

    public function resizedUrl($file, $uploadPath, $thumbPath, $width, $height, $quality = 100)
    {
        $imageDir = APP.'webroot'.DS;
        $pathinfo = pathinfo(trim($file, '/'));
        $targetFile = $imageDir . $uploadPath . $file;
        $output = $imageDir . $thumbPath . $file;
        if (file_exists($targetFile)) {
            $info = @getimagesize($targetFile);
            if (empty($info)) {
                return false;
            }
            list($width_old, $height_old) = $info;
            switch ( $info[2] ) {
                case IMAGETYPE_GIF:   $image = imagecreatefromgif($targetFile);   break;
                case IMAGETYPE_JPEG:  $image = imagecreatefromjpeg($targetFile);  break;
                case IMAGETYPE_PNG:   $image = imagecreatefrompng($targetFile);   break;
                default: return false;
            }
            $heightRatio = $height_old / $height;
            $widthRatio  = $width_old /  $width;
            $optimalRatio = $widthRatio;
            if ($heightRatio < $widthRatio) {
                $optimalRatio = $heightRatio;
            }
            $height_crop = ($height_old / $optimalRatio);
            $width_crop  = ($width_old  / $optimalRatio);
            $image_crop = imagecreatetruecolor( $width_crop, $height_crop );
            $image_resized = imagecreatetruecolor($width, $height);
            if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
                $transparency = imagecolortransparent($image);
                if ($transparency >= 0) {
                    $trnprt_indx = imagecolortransparent($image);
                    $transparent_color  = imagecolorsforindex($image, $trnprt_indx);
                    $transparency       = imagecolorallocate($image_crop, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                    imagefill($image_crop, 0, 0, $transparency);
                    imagecolortransparent($image_crop, $transparency);
                    imagefill($image_resized, 0, 0, $transparency);
                    imagecolortransparent($image_resized, $transparency);
                } elseif ($info[2] == IMAGETYPE_PNG) {
                    imagealphablending($image_crop, false);
                    imagealphablending($image_resized, false);
                    $color = imagecolorallocatealpha($image_crop, 0, 0, 0, 127);
                    imagefill($image_crop, 0, 0, $color);
                    imagesavealpha($image_crop, true);
                    imagefill($image_resized, 0, 0, $color);
                    imagesavealpha($image_resized, true);
                }
            }
            imagecopyresampled($image_crop, $image, 0, 0, 0, 0, $width_crop, $height_crop, $width_old, $height_old);
            imagecopyresampled($image_resized, $image_crop, 0, 0, ($width_crop - $width) / 2, ($height_crop - $height) / 2, $width, $height, $width, $height);
            switch ( $info[2] ) {
              case IMAGETYPE_GIF:   imagegif($image_resized, $output, $quality);    break;
              case IMAGETYPE_JPEG:  imagejpeg($image_resized, $output, $quality);   break;
              case IMAGETYPE_PNG:   imagepng($image_resized, $output, 9);    break;
              default: return false;
            }
        }
        return $output;
    }
    public function fileDelete($fileName,$imageDir,$isThumb=true,$path=null)
    {
        $fileThumb=null;
        if($path==null)
        {
            $file=APP.WEBROOT_DIR.DS.'img'.DS.$imageDir.DS.$fileName;
            if($isThumb==true)
            $fileThumb=APP.WEBROOT_DIR.DS.'img'.DS.$imageDir.'_thumb'.DS.$fileName;
        }
        else
        {
            $file=$path.$fileName;
        }
        if(file_exists($file))
        {
            unlink($file);
        }
        if(file_exists($fileThumb) && $isThumb==true)
        {
            unlink($fileThumb);
        }
    }
    public function showGroupName($gropArr,$string=" , ")
    {
        $groupNameArr=array();
        foreach($gropArr as $groupName)
        {
            $groupNameArr[]=$groupName['name'];
        }
        unset($groupName);
        $showGroup= implode($string,$groupNameArr);
        unset($groupNameArr);
        return h($showGroup);
    }
    public function awalletInsert($agentId,$amount,$type,$remarks=null,$dealsPaymentId=null,$isLog=true)
    {
        $recordArr=array();$arecordArr=array();
        $Awallet=ClassRegistry::init('Awallet');
        $User=ClassRegistry::init('User');
        if($amount<=0)
        {
            return false;
        }
        else
        {
            $userArr=$User->findById($agentId);
            $walletArr=$Awallet->findByUserId($agentId);
            if($walletArr)
            {
                if($type=="CR")
                {
                    $fieldType="credit";
                    $netAmount=$walletArr['Awallet']['credit']+$amount;
                    $balance=$walletArr['Awallet']['balance']+$amount;
                }
                else
                {
                    $fieldType="debit";
                    $netAmount=$walletArr['Awallet']['debit']+$amount;
                    $balance=$walletArr['Awallet']['balance']-$amount;
                }
                $recordArr=array('id'=>$walletArr['Awallet']['id'],'user_id'=>$agentId,'project_id'=>$userArr['User']['project_id'],$fieldType=>$netAmount,'balance'=>$balance);
            }
            else
            {
                if($type=="CR")
                $fieldType="credit";
                else
                $fieldType="debit";
                $recordArr=array('user_id'=>$agentId,'project_id'=>$userArr['User']['project_id'],$fieldType=>$amount,'balance'=>$amount);
                $Awallet->create();
            }
            if($Awallet->save($recordArr))
            {
                if($isLog==true)
                {
                    $AwalletHistory=ClassRegistry::init('AwalletHistory');
                    $this->currentDateTime=CakeTime::format('Y-m-d H:i:s',CakeTime::convert(time(),$this->siteTimezone));
                    $arecordArr=array('AwalletHistory'=>array('user_id'=>$agentId,'project_id'=>$userArr['User']['project_id'],'deals_payment_id'=>$dealsPaymentId,'amount'=>$amount,'type'=>$type,'date'=>$this->currentDateTime,'remarks'=>$remarks));
                    $AwalletHistory->create();
                    $AwalletHistory->save($arecordArr);
                }
                return true;
            }
            else
            {
                return false;
            }
        }
    }
    public function hasDealCommissionCredit($dealId,$agentId=null)
    {
        $dealId=(int)$dealId;
        $agentId=(int)$agentId;
        if($dealId<=0)
        {
            return false;
        }
        $AwalletHistory=ClassRegistry::init('AwalletHistory');
        if(!$AwalletHistory)
        {
            return false;
        }
        $conditions=array(
            'AwalletHistory.type'=>'CR',
            'AwalletHistory.amount >'=>0,
            'AwalletHistory.remarks LIKE'=>'%Deal Commission #'.$dealId.'%'
        );
        if($agentId>0)
        {
            $conditions['AwalletHistory.user_id']=$agentId;
        }
        $directPost=$AwalletHistory->find('first',array(
            'fields'=>array('AwalletHistory.id'),
            'conditions'=>$conditions,
            'recursive'=>-1
        ));
        if(!empty($directPost['AwalletHistory']['id']))
        {
            return true;
        }
        $legacyConditions=array(
            'AwalletHistory.type'=>'CR',
            'AwalletHistory.amount >'=>0,
            'DealsPayment.deal_id'=>$dealId,
            'OR'=>array(
                array('AwalletHistory.remarks LIKE'=>'%Added for generating commission%'),
                array('AwalletHistory.remarks LIKE'=>'%commission%')
            )
        );
        if($agentId>0)
        {
            $legacyConditions['AwalletHistory.user_id']=$agentId;
        }
        $legacyPost=$AwalletHistory->find('first',array(
            'joins'=>array(
                array(
                    'table'=>'deals_payments',
                    'alias'=>'DealsPayment',
                    'type'=>'INNER',
                    'conditions'=>array('DealsPayment.id = AwalletHistory.deals_payment_id')
                )
            ),
            'fields'=>array('AwalletHistory.id'),
            'conditions'=>$legacyConditions,
            'recursive'=>-1
        ));
        return !empty($legacyPost['AwalletHistory']['id']);
    }
    public function awalletInsertDealCommissionOnce($agentId,$amount,$dealId,$remarks=null,$dealsPaymentId=null)
    {
        $agentId=(int)$agentId;
        $dealId=(int)$dealId;
        $amount=round((float)$amount,2);
        if($agentId<=0 || $dealId<=0 || $amount<=0)
        {
            return false;
        }
        if($this->hasDealCommissionCredit($dealId,$agentId))
        {
            return true;
        }
        $remarks=trim((string)$remarks);
        if($remarks==='')
        {
            $remarks='Deal Commission #'.$dealId;
        }
        elseif(stripos($remarks,'Deal Commission #'.$dealId)===false)
        {
            $remarks='Deal Commission #'.$dealId.' | '.$remarks;
        }
        if(strlen($remarks)>100)
        {
            $remarks=substr($remarks,0,100);
        }
        return $this->awalletInsert($agentId,$amount,'CR',$remarks,$dealsPaymentId,true);
    }
    public function awalletBalance($id)
    {
        $Awallet=ClassRegistry::init('Awallet');
        $walletArr=$Awallet->findByUserId($id);
        if($walletArr)
        $balance=$walletArr['Awallet']['balance'];
        else
        $balance=" 0.00";
        return $balance;
    }
    public function awalletDelete($agentId,$amount)
    {
        $Awallet=ClassRegistry::init('Awallet');
        $walletArr=$Awallet->findByUserId($agentId);
        $netAmount=$walletArr['Awallet']['credit']-$amount;
        $balance=$walletArr['Awallet']['balance']-$amount;
        $recordArr=array('id'=>$walletArr['Awallet']['id'],'user_id'=>$agentId,'project_id'=>$walletArr['Awallet']['project_id'],'credit'=>$netAmount,'balance'=>$balance);
        if($Awallet->save($recordArr))
        {
            return true;
        }
    }
    public function favouriteProperty($favUrl,$propertyId,$status=null)
    {
        $sessionId=session_id();
        $Property=ClassRegistry::init('Property');
        $Property->unbindModel(array('belongsTo'=>array('Type','Project','Unit','Location','User','Contract'),
                                     'hasMany'=>array('PropertiesPhoto','PropertiesDocument','PropertiesFloorplan')));
        $Favourite=ClassRegistry::init('Favourite');
        $post=$Property->findById($propertyId);
        if(isset($_SESSION['User']))
        {
           $fpost=$Favourite->findByPropertyIdAndUserId($propertyId,$_SESSION['User']['User']['id']);
        }
        else
        {
            $fpost=$Favourite->findByPropertyIdAndSessionId($propertyId,$sessionId);
        }
        if($post)
        {
            if($status=="add")
            {
                if(!$fpost)
                {
                    if($this->Session->check('User'))
                    {
                        $userArr=$this->Session->read('User');
                        $userId=$userArr['User']['id'];
                        $favArr=array('user_id'=>$userId,'session_id'=>$sessionId,'property_id'=>$propertyId);
                    }
                    else
                    {
                        $favArr=array('session_id'=>$sessionId,'property_id'=>$propertyId);
                    }
                    $Favourite->save($favArr);
                    return '<span id="'.$propertyId.'printajax"><a href="javascript:void(0);" class="nav-link" onclick="favouriteProperties(\''.$favUrl.'\',\''.$propertyId.'\',\'remove\');"><i class="fa fa-heart"></i>&nbsp;<span>'.__('Remove to favorites').'</span></a></span>';
                }
            }
            else
            {
                if($fpost)
                {
                    $id=$fpost['Favourite']['id'];
                    $Favourite->delete($id);
                    return '<span id="'.$propertyId.'printajax"><a href="javascript:void(0);" class="nav-link" onclick="favouriteProperties(\''.$favUrl.'\',\''.$propertyId.'\',\'add\');"><i class="fa fa-heart-o"></i>&nbsp;<span>'.__('Add to favorites').'</span></a></span>';
                }
            }
        }
    }
    public function compareProperty($compUrl,$imgUrl,$propertyId,$status=null)
    {
        $Property=ClassRegistry::init('Property');
        $Property->unbindModel(array('belongsTo'=>array('Type','Project','Unit','Location','User','Contract'),
                                     'hasMany'=>array('PropertiesPhoto','PropertiesDocument','PropertiesFloorplan')));
        $post=$Property->findById($propertyId);
        if($post)
        {
            if($status=="add")
            {
                if(count($this->readPackage())<3)
                {
                    $this->addPackage($propertyId);
                    return '<span id="'.$propertyId.'cprintajax"><a href="javascript:void(0);" class="nav-link" onclick="compareProperties(\''.$compUrl.'\',\''.$propertyId.'\',\'remove\');"><img src="'.$imgUrl.'/compare-selected.png">&nbsp;<span>'.__('Remove to compare').'</span></a></span>';
                }
                else
                {
                    ?><script>alert(getLocale("You can not compare more than 3 properties"));</script><?php
                    return '<span id="'.$propertyId.'cprintajax"><a href="javascript:void(0);" class="nav-link" onclick="compareProperties(\''.$compUrl.'\',\''.$propertyId.'\',\'add\');"><img src="'.$imgUrl.'/compare-unselected.png">&nbsp;<span>'.__('Add to compare').'</span></a></span>';
                }
            }
            else
            {
                $carts = $this->readPackage();
                unset($carts[$propertyId]);
                $this->savePackage($carts);
                return '<span id="'.$propertyId.'cprintajax"><a href="javascript:void(0);" class="nav-link" onclick="compareProperties(\''.$compUrl.'\',\''.$propertyId.'\',\'add\');"><img src="'.$imgUrl.'/compare-unselected.png">&nbsp;<span>'.__('Add to compare').'</span></a></span>';
            }
        }
    }
    public function addPackage($productId) {
        $allPackages = $this->readPackage();
        $allPackages[$productId] = $productId;
        $this->savePackage($allPackages);
    }
    public function readPackage() {
        return CakeSession::read('compare');
    }
    public function savePackage($data) {
        return CakeSession::write('compare',$data);
    }
    public function readCompare($propertyId)
    {
        $compArr=$this->readPackage();
        if($compArr[$propertyId]==$propertyId){
            return true;
        }
        else{
            return false;
        }
    }
    public function starRating($rating)
    {
        $starRating=__('Not Specified');
        if($rating)
        {
            $starRating=null;
            $ratingArr=explode(".",$rating);
            for($i=0;$i<$ratingArr[0];$i++)
            {
                $starRating.='<i class="fa fa-star"></i>';
            }
            if(count($ratingArr)>1 && $ratingArr[1])
            {
                $starRating.='<i class="fa fa-star-half-o"></i>';
            }
            $remRating=substr(5-$rating,0,1);
            if($remRating>0)
            {
                for($i=0;$i<$remRating;$i++)
                {
                    $starRating.='<i class="fa fa-star-o"></i>';
                }
            }
        }
        return$starRating;
    }
    public function userWisePermission($id,$luserId,$userType,$agencyId,$field1,$field2,$fieldType=1){
        $condition=array();
        $userTypeKey = strtoupper(trim((string)$userType));
        $isGlobalAdmin = ($userTypeKey === 'ADR');
        if($id!=null){
            if($fieldType==1)
            {
                $condition=array($field1=>$id);
            }
            if($fieldType==2)
            {
                $condition=array($field2=>$id);
            }            
	}
	if(!$isGlobalAdmin){
	    if(!in_array($userTypeKey,array('AGT','RCO'),true)){
		if((int)$agencyId>0){
		    $condition=array($field1=>$agencyId);
		}
	    }
	    elseif((int)$luserId>0){
		$condition=array($field2=>$luserId);
	    }
	    elseif((int)$agencyId>0){
		$condition=array($field1=>$agencyId);
	    }
	}
        return $condition;
    }
    public function agencyWisePermission($id,$userType,$agencyId,$field1){
        $condition=array();
        $userTypeKey = strtoupper(trim((string)$userType));
        $isGlobalAdmin = ($userTypeKey === 'ADR');
        if($id!=null){
	    $condition=array($field1=>$id);
	}
        if(!$isGlobalAdmin && (int)$agencyId>0){
            $condition=array($field1=>$agencyId);
	}
        return $condition;
    }
    public function agencyWiseList($userType,$agencyId)
    {
        $condition=array();
        $userTypeKey = strtoupper(trim((string)$userType));
        if($userTypeKey === 'ADR'){
            $condition=array();
        }
        else{
            $condition=array('Project.id'=>$agencyId);
        }
        $Project=ClassRegistry::init('Project');
        $listArr=$Project->find('list',array('conditions'=>$condition));
        return$listArr;
    }
    public function sendSms($mobileNo,$message,$smsArr=array())
    {
        $smsRow = !empty($smsArr['Smssetting']) ? (array)$smsArr['Smssetting'] : (array)$smsArr;

        // Check if LeadBot CRM is configured as default SMS gateway
        $isLeadbot = false;
        if (!empty($smsRow['gateway_type']) && strtolower(trim((string)$smsRow['gateway_type'])) === 'leadbot') {
            $isLeadbot = true;
        } elseif (!empty($smsRow['is_leadbot_default'])) {
            $isLeadbot = true;
        } else {
            try {
                $SmssettingModel = ClassRegistry::init('Admin.Smssetting');
                if ($SmssettingModel) {
                    $row1 = $SmssettingModel->find('first', array(
                        'conditions' => array('Smssetting.id' => 1),
                        'fields'     => array('Smssetting.gateway_type', 'Smssetting.is_leadbot_default'),
                        'recursive'  => -1
                    ));
                    if (!empty($row1['Smssetting']['gateway_type']) && strtolower(trim((string)$row1['Smssetting']['gateway_type'])) === 'leadbot') {
                        $isLeadbot = true;
                    } elseif (!empty($row1['Smssetting']['is_leadbot_default'])) {
                        $isLeadbot = true;
                    }
                }
            } catch (Exception $eSmsChk) {}
        }

        if ($isLeadbot) {
            return $this->_sendSmsViaLeadbot($mobileNo, $message, $smsRow);
        }

        $url = isset($smsRow['api']) ? trim((string)$smsRow['api']) : '';
        if($url === '')
        {
            throw new Exception(__('SMS API URL is missing.'));
        }
        $parsedUrl = parse_url($url);
        $host = !empty($parsedUrl['host']) ? $parsedUrl['host'] : '';
        if($host === '')
        {
            throw new Exception(__('SMS API URL is invalid.'));
        }
        if(!filter_var($host, FILTER_VALIDATE_IP) && gethostbyname($host) === $host)
        {
            throw new Exception(sprintf(__('SMS gateway host could not be resolved: %s'), $host));
        }
        $this->_assertSmsGatewayReachable($parsedUrl);

        $query = array(
            $this->_smsParamName($smsRow, 'husername', 'username') => isset($smsRow['username']) ? (string)$smsRow['username'] : '',
            $this->_smsParamName($smsRow, 'hpassword', 'password') => isset($smsRow['password']) ? (string)$smsRow['password'] : '',
            $this->_smsParamName($smsRow, 'hsenderid', 'sender') => isset($smsRow['senderid']) ? (string)$smsRow['senderid'] : '',
            $this->_smsParamName($smsRow, 'hmobile', 'mobile') => (string)$mobileNo,
            $this->_smsParamName($smsRow, 'hmessage', 'message') => (string)$message,
            'smstype' => 'TRANS'
        );

        if (!$this->HttpSocket) {
            $this->HttpSocket = new HttpSocket(array('timeout' => 12));
        }
        try
        {
            if(strtolower(trim((string)(isset($smsRow['type']) ? $smsRow['type'] : 'Get'))) === 'get')
            $response=$this->HttpSocket->get($url, $query);
            else
            $response=$this->HttpSocket->post($url, $query);
        }
        catch(Exception $e)
        {
            throw new Exception($this->_friendlySmsGatewayException($e->getMessage(), $host));
        }

        if($response === false)
        {
            throw new Exception(__('SMS gateway did not return a response.'));
        }
        if(isset($response->code) && (int)$response->code >= 400)
        {
            throw new Exception(sprintf(__('SMS gateway returned HTTP %s.'), (int)$response->code));
        }

        // Inspect response body for JSON errors from providers returning HTTP 200 on failure
        $bodyText = isset($response->body) ? (string)$response->body : (is_string($response) ? $response : '');
        if ($bodyText !== '') {
            $bodyDecoded = json_decode($bodyText, true);
            if (is_array($bodyDecoded) && isset($bodyDecoded['success']) && $bodyDecoded['success'] === false) {
                $errDetail = !empty($bodyDecoded['error']['message']) ? $bodyDecoded['error']['message'] : (!empty($bodyDecoded['message']) ? $bodyDecoded['message'] : 'Provider returned an error');
                throw new Exception(sprintf(__('SMS Provider Error: %s'), $errDetail));
            }
        }

        return $response;
    }

    /**
     * Dispatch system SMS via LeadBot CRM Android Mobile Gateway
     */
    protected function _sendSmsViaLeadbot($mobileNo, $message, $smsRow = array())
    {
        App::uses('LeadbotGatewayClient', 'Lib');
        $cleanPhone = LeadbotGatewayClient::cleanPhone($mobileNo);
        if ($cleanPhone === '') {
            throw new Exception(__('Invalid recipient mobile phone number.'));
        }

        $GatewayModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
        $settings = $GatewayModel ? $GatewayModel->getSettings(0, 0, true) : array();

        if (empty($settings['api_key'])) {
            throw new Exception(__('LeadBot API Key is not configured. Please configure LeadBot in /admin/LeadbotGateways.'));
        }

        $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
        $apiKey     = $settings['api_key'];
        $deviceId   = !empty($settings['selected_sms_device_id']) ? (int)$settings['selected_sms_device_id'] : null;
        $simSlot    = !empty($settings['selected_sms_sim_slot']) ? (int)$settings['selected_sms_sim_slot'] : 1;

        $desc = !empty($deviceId) ? ('Device #' . $deviceId . ' (SIM ' . $simSlot . ')') : 'LeadBot Android Gateway';

        $res = LeadbotGatewayClient::sendSms($gatewayUrl, $apiKey, array(
            'recipient' => $cleanPhone,
            'message'   => $message,
            'device_id' => $deviceId,
            'sim_slot'  => $simSlot
        ));

        // Record in LeadbotNotificationLog
        try {
            $LogModel = ClassRegistry::init('Admin.LeadbotNotificationLog');
            if ($LogModel) {
                $LogModel->recordLog(array(
                    'project_id'                => 0,
                    'user_id'                   => null,
                    'channel'                   => 'sms',
                    'event_key'                 => 'system_sms',
                    'recipient_phone'           => $cleanPhone,
                    'recipient_name'            => 'System Recipient',
                    'message'                   => $message,
                    'gateway_profile_or_device' => $desc,
                    'status'                    => !empty($res['success']) ? 'queued' : 'failed',
                    'response_code'             => isset($res['latency_ms']) ? $res['latency_ms'] : 0,
                    'response_payload'          => isset($res['raw_response']) ? $res['raw_response'] : '',
                    'error_message'             => !empty($res['error']) ? $res['error'] : null,
                    'is_test'                   => 0
                ));
            }
        } catch (Exception $eLog) {}

        if (empty($res['success'])) {
            $errMsg = !empty($res['error']) ? $res['error'] : __('LeadBot Android SMS dispatch failed.');
            throw new Exception($errMsg);
        }

        return $res;
    }

    protected function _assertSmsGatewayReachable($parsedUrl)
    {
        $host = !empty($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $scheme = !empty($parsedUrl['scheme']) ? strtolower((string)$parsedUrl['scheme']) : 'http';
        $port = !empty($parsedUrl['port']) ? (int)$parsedUrl['port'] : ($scheme === 'https' ? 443 : 80);
        if($host === '' || $port <= 0)
        {
            throw new Exception(__('SMS API URL is invalid.'));
        }
        $cacheKey = strtolower($host).':'.$port;
        if(isset($this->_smsGatewayReachability[$cacheKey]))
        {
            if($this->_smsGatewayReachability[$cacheKey] === true)
            {
                return true;
            }
            throw new Exception($this->_smsGatewayReachability[$cacheKey]);
        }
        $errno = 0;
        $errstr = '';
        $connection = @fsockopen($host, $port, $errno, $errstr, 6);
        if(is_resource($connection))
        {
            fclose($connection);
            $this->_smsGatewayReachability[$cacheKey] = true;
            return true;
        }
        $message = sprintf(__('SMS gateway is not reachable right now: %s:%s. Please check SMS settings, internet, firewall, or provider server.'), $host, $port);
        if($errstr !== '')
        {
            $message .= ' ' . $errstr;
        }
        $this->_smsGatewayReachability[$cacheKey] = $message;
        throw new Exception($message);
    }

    protected function _friendlySmsGatewayException($message, $host)
    {
        $message = (string)$message;
        if(stripos($message, 'getaddrinfo') !== false || stripos($message, 'php_network_getaddresses') !== false)
        {
            return sprintf(__('SMS gateway host could not be resolved: %s'), $host);
        }
        if(stripos($message, 'timed out') !== false || stripos($message, 'failed to respond') !== false || stripos($message, 'unable to connect') !== false || stripos($message, 'connection refused') !== false)
        {
            return sprintf(__('SMS gateway is not responding: %s. Please check SMS settings, internet, firewall, or provider server.'), $host);
        }
        return $message;
    }

    protected function _smsParamName($smsRow, $key, $fallback)
    {
        $name = isset($smsRow[$key]) ? trim((string)$smsRow[$key]) : '';
        return $name !== '' ? $name : $fallback;
    }
    public function dateFormatBeforeSave($dateString)
    {
      return date('Y-m-d', strtotime($dateString));
    }
    public function dateTimeFormatBeforeSave($dateString)
    {
      return date('Y-m-d H:i:s', strtotime($dateString));
    }
}
