<?php
App::uses('AppController', 'Controller');
App::uses('Security', 'Utility');
App::uses('CakeNumber', 'Utility');

class LetterverificationsController extends AppController
{
    public $helpers = array('Html', 'Session', 'Time', 'Number');
    public $uses = array('Admin.Realestateletter');

    public function beforeFilter()
    {
        parent::beforeFilter();
        if (isset($this->Auth))
        {
            $this->Auth->allow('verify', 'view_document', 'download_pdf');
        }
    }

    public function verify($id = null, $token = null)
    {
        $this->layout = 'default';
        if ($id === null && !empty($this->request->params['id'])) {
            $id = $this->request->params['id'];
        } elseif ($id === null && !empty($this->request->params['pass'][0])) {
            $id = $this->request->params['pass'][0];
        }

        if ($token === null && !empty($this->request->params['token'])) {
            $token = $this->request->params['token'];
        } elseif ($token === null && !empty($this->request->params['pass'][1])) {
            $token = $this->request->params['pass'][1];
        }

        $id = (int)$id;
        $token = trim((string)$token);
        $verified = false;
        $post = array();
        $securitySerial = '';
        $qrCodeUrl = '';
        $renderedLetterHtml = '';

        if ($id > 0)
        {
            $post = $this->_letterDetails($id);
            if (!empty($post['Realestateletter']['id']))
            {
                $letterId = (int)$post['Realestateletter']['id'];
                $dealId = !empty($post['Realestateletter']['deal_id']) ? (int)$post['Realestateletter']['deal_id'] : 0;
                $clientId = !empty($post['Realestateletter']['client_id']) ? (int)$post['Realestateletter']['client_id'] : (!empty($post['Client']['id']) ? (int)$post['Client']['id'] : 0);

                $token1 = $this->_verificationToken($letterId, $dealId, $clientId);
                $token2 = $this->_verificationToken($letterId, $dealId, 0);

                $verified = ($token !== '' && (
                    hash_equals($token1, $token) || substr($token1, 0, strlen($token)) === $token ||
                    hash_equals($token2, $token) || substr($token2, 0, strlen($token)) === $token
                ));

                if ($verified)
                {
                    $matchedToken = (hash_equals($token1, $token) || substr($token1, 0, strlen($token)) === $token) ? $token1 : $token2;
                    $securitySerial = 'WV-VER-' . strtoupper(substr(hash('sha256', $id . '|' . $matchedToken), 0, 8)) . '-' . date('Y', strtotime($post['Realestateletter']['created']));
                    
                    $verifyUrl = Router::url(array('controller' => 'letterverifications', 'action' => 'verify', $id, $matchedToken), true);
                    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&ecc=H&margin=4&format=png&data=' . rawurlencode($verifyUrl);
                    
                    $renderedLetterHtml = $this->_renderLetterContent($post, $securitySerial, $verifyUrl, $qrCodeUrl);
                }
            }
        }

        // Determine audit scan status
        $scanStatus = 'NOT_FOUND';
        if (!empty($post['Realestateletter']['id'])) {
            $scanStatus = $verified ? 'VERIFIED' : 'INVALID_TOKEN';
            if ($verified && !empty($post['Realestateletter']['status']) && $post['Realestateletter']['status'] === 'Unpublished') {
                $scanStatus = 'EXPIRED';
            }
        }

        // Record scan audit log
        $this->_logQrScan($id, $token, $scanStatus, $post);

        $this->set(compact('post', 'verified', 'securitySerial', 'qrCodeUrl', 'renderedLetterHtml', 'id', 'token'));
    }

    protected function _logQrScan($letterId, $token, $status, $post = array())
    {
        try {
            $this->loadModel('QrScanLog');
            $ref = '';
            if (!empty($post['Deal']['invoice_no'])) {
                $ref = (stripos($post['Deal']['invoice_no'], 'ID.') === 0) ? trim($post['Deal']['invoice_no']) : ('ID. No: ' . trim($post['Deal']['invoice_no']));
            } elseif (!empty($post['Realestateletter']['title']) && preg_match('/WV-[A-Z0-9\/\-\s]+/i', $post['Realestateletter']['title'], $m)) {
                $ref = 'ID. No: ' . trim($m[0]);
            } elseif (!empty($post['Realestateletter']['reference_no'])) {
                $ref = 'ID. No: ' . trim((string)$post['Realestateletter']['reference_no']);
            }
            $meta = array(
                'reference_no' => $ref,
                'deal_id' => !empty($post['Realestateletter']['deal_id']) ? $post['Realestateletter']['deal_id'] : 0,
                'client_id' => !empty($post['Realestateletter']['client_id']) ? $post['Realestateletter']['client_id'] : (!empty($post['Client']['id']) ? $post['Client']['id'] : 0),
                'client_name' => !empty($post['Client']['name']) ? $post['Client']['name'] : null,
                'property_name' => !empty($post['Property']['name']) ? $post['Property']['name'] : null,
            );
            $this->QrScanLog->logScan($letterId, $token, $status, $meta);
        } catch (Exception $e) {
            // Keep verification completely uninterrupted
        }
    }

    public function view_document($id = null, $token = null)
    {
        $this->layout = false;
        $this->autoRender = false;
        $id = (int)$id;
        $token = trim((string)$token);

        if ($id <= 0)
        {
            throw new NotFoundException(__('Document not found.'));
        }

        $post = $this->_letterDetails($id);
        if (empty($post['Realestateletter']['id']))
        {
            throw new NotFoundException(__('Document record not found.'));
        }

        $letterId = (int)$post['Realestateletter']['id'];
        $dealId = !empty($post['Realestateletter']['deal_id']) ? (int)$post['Realestateletter']['deal_id'] : 0;
        $clientId = !empty($post['Realestateletter']['client_id']) ? (int)$post['Realestateletter']['client_id'] : (!empty($post['Client']['id']) ? (int)$post['Client']['id'] : 0);

        $token1 = $this->_verificationToken($letterId, $dealId, $clientId);
        $token2 = $this->_verificationToken($letterId, $dealId, 0);

        $verified = ($token !== '' && (
            hash_equals($token1, $token) || substr($token1, 0, strlen($token)) === $token ||
            hash_equals($token2, $token) || substr($token2, 0, strlen($token)) === $token
        ));

        if (!$verified)
        {
            throw new ForbiddenException(__('Invalid verification token.'));
        }

        $matchedToken = (hash_equals($token1, $token) || substr($token1, 0, strlen($token)) === $token) ? $token1 : $token2;
        $securitySerial = 'WV-VER-' . strtoupper(substr(hash('sha256', $id . '|' . $matchedToken), 0, 8)) . '-' . date('Y', strtotime($post['Realestateletter']['created']));
        $verifyUrl = Router::url(array('controller' => 'letterverifications', 'action' => 'verify', $id, $matchedToken), true);
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&ecc=H&margin=4&format=png&data=' . rawurlencode($verifyUrl);

        $html = $this->_renderLetterContent($post, $securitySerial, $verifyUrl, $qrCodeUrl, true);
        return $this->response->body($html);
    }

    protected function _renderLetterContent($post, $securitySerial = '', $verifyUrl = '', $qrCodeUrl = '', $includeStandaloneWatermark = false)
    {
        $letter = isset($post['Realestateletter']) ? (array)$post['Realestateletter'] : array();
        $deal = isset($post['Deal']) ? (array)$post['Deal'] : array();
        $client = isset($post['Client']) ? (array)$post['Client'] : array();
        $property = isset($post['Property']) ? (array)$post['Property'] : array();
        $project = isset($post['Project']) ? (array)$post['Project'] : array();
        $template = isset($post['Template']) ? (array)$post['Template'] : array();
        $type = isset($post['Type']) ? (array)$post['Type'] : array();
        $unit = isset($post['Unit']) ? (array)$post['Unit'] : array();

        // 1. Determine raw HTML body
        $html = '';
        if (!empty($letter['description']) && strpos($letter['description'], '{{') !== false)
        {
            $html = (string)$letter['description'];
        }
        elseif (!empty($template['description']))
        {
            $html = (string)$template['description'];
        }
        elseif (!empty($letter['description']))
        {
            $html = (string)$letter['description'];
        }

        // 2. Fetch Balloting Snapshot if available
        $dealId = !empty($deal['id']) ? (int)$deal['id'] : 0;
        $ballot = $this->_ballotingSnapshotByDeal($dealId);

        // 3. Compile full placeholder dictionary
        $map = $this->_buildPlaceholderMap($post, $ballot, $securitySerial, $verifyUrl, $qrCodeUrl);

        // 4. Multi-pass placeholder substitution (Exact, Uppercase, Lowercase, Case-Insensitive)
        $replacements = array();
        foreach ($map as $key => $val)
        {
            $valStr = (string)$val;
            $cleanKey = trim($key, '{}');
            $replacements['{{' . strtolower($cleanKey) . '}}'] = $valStr;
            $replacements['{{' . strtoupper($cleanKey) . '}}'] = $valStr;
            $replacements['{{' . $cleanKey . '}}'] = $valStr;
            $replacements['{{ ' . strtolower($cleanKey) . ' }}'] = $valStr;
            $replacements['{{ ' . strtoupper($cleanKey) . ' }}'] = $valStr;
            $replacements['{{ ' . $cleanKey . ' }}'] = $valStr;
        }
        $html = strtr($html, $replacements);

        // Case-insensitive regex fallback for any spacing variants
        foreach ($map as $key => $val)
        {
            $cleanKey = trim($key, '{}');
            $pattern = '/\{\{\s*' . preg_quote($cleanKey, '/') . '\s*\}\}/i';
            $html = preg_replace($pattern, (string)$val, $html);
        }

        // 5. Clean up any remaining unresolved {{...}} placeholders safely
        $html = preg_replace('/\{\{\s*[^}]+\s*\}\}/', '', $html);

        // 6. Inject Urdu Nastaleeq font and letter styles
        $html = $this->_injectUrduFontsAndStyles($html);

        // 7. Inject Standalone Watermark if requested (e.g. for view_document)
        if ($includeStandaloneWatermark)
        {
            $watermarkHtml = <<<HTML
<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-30deg);pointer-events:none;user-select:none;z-index:9999;opacity:0.15;display:flex;justify-content:center;align-items:center;width:85%;text-align:center;">
    <div style="border:5px solid #059669;color:#059669;padding:18px 36px;border-radius:16px;display:flex;flex-direction:column;align-items:center;gap:6px;background:rgba(255,255,255,0.4);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">
        <div style="font-size:13px;font-weight:800;letter-spacing:2px;text-transform:uppercase;">🛡️ OFFICIAL VERIFICATION</div>
        <div style="font-size:32px;font-weight:900;letter-spacing:4px;text-transform:uppercase;white-space:nowrap;line-height:1.2;">VERIFIED OFFICIAL DOCUMENT</div>
        <div style="font-size:12px;font-weight:700;letter-spacing:1.5px;font-family:monospace;">{$securitySerial}</div>
    </div>
</div>
HTML;
            if (stripos($html, '</body>') !== false)
            {
                $html = str_ireplace('</body>', $watermarkHtml . '</body>', $html);
            }
            else
            {
                $html .= $watermarkHtml;
            }
        }

        return $html;
    }

    protected function _buildPlaceholderMap($post, $ballot, $securitySerial, $verifyUrl, $qrCodeUrl)
    {
        $letter = isset($post['Realestateletter']) ? (array)$post['Realestateletter'] : array();
        $deal = isset($post['Deal']) ? (array)$post['Deal'] : array();
        $client = isset($post['Client']) ? (array)$post['Client'] : array();
        $property = isset($post['Property']) ? (array)$post['Property'] : array();
        $project = isset($post['Project']) ? (array)$post['Project'] : array();
        $type = isset($post['Type']) ? (array)$post['Type'] : array();
        $unit = isset($post['Unit']) ? (array)$post['Unit'] : array();

        // Agency details
        $agencyName = !empty($project['name']) ? trim((string)$project['name']) : 'White Villas Lahore';
        $agencyAddress = !empty($project['address']) ? trim((string)$project['address']) : 'Main Ferozepur Road, Lahore';
        $agencyEmail = !empty($project['email']) ? trim((string)$project['email']) : 'info@whitevillas.com.pk';
        $agencyPhone = '';
        foreach (array(!empty($project['phone']) ? $project['phone'] : '', !empty($project['mobile']) ? $project['mobile'] : '') as $phone)
        {
            if (trim((string)$phone) !== '') { $agencyPhone = trim((string)$phone); break; }
        }
        if ($agencyPhone === '') { $agencyPhone = '+92 300 1234567'; }

        // Client details
        $clientName = !empty($client['name']) ? trim((string)$client['name']) : '';
        $clientFather = !empty($client['fathername']) ? trim((string)$client['fathername']) : '';
        $clientCnic = !empty($client['pincode']) ? trim((string)$client['pincode']) : (!empty($client['phone']) ? trim((string)$client['phone']) : '');
        $clientPhone = !empty($client['phone']) ? trim((string)$client['phone']) : '';
        $clientMobile = !empty($client['mobile']) ? trim((string)$client['mobile']) : (!empty($client['phone']) ? trim((string)$client['phone']) : '');
        $clientEmail = !empty($client['email']) ? trim((string)$client['email']) : '';
        $clientAddress = !empty($client['address']) ? trim((string)$client['address']) : '';
        $clientOccupation = !empty($deal['occupation']) ? trim((string)$deal['occupation']) : 'Business';

        // Nominee details
        $nomineeName = !empty($deal['nominee_name']) ? trim((string)$deal['nominee_name']) : '';
        $nomineeCnic = !empty($deal['nominee_cnic']) ? trim((string)$deal['nominee_cnic']) : '';
        $nomineeMobile = !empty($deal['nominee_mobile']) ? trim((string)$deal['nominee_mobile']) : '';
        $nomineeRelation = !empty($deal['nominee_relation']) ? trim((string)$deal['nominee_relation']) : '';

        // Deal & Property details
        $rawInvoice = !empty($deal['invoice_no']) ? trim((string)$deal['invoice_no']) : '';
        if ($rawInvoice !== '') {
            $dealInvoiceNo = $rawInvoice;
            $letterRefNo = (stripos($rawInvoice, 'ID.') === 0) ? $rawInvoice : ('ID. No: ' . $rawInvoice);
        } elseif (!empty($letter['title']) && preg_match('/WV-[A-Z0-9\/\-\s]+/i', $letter['title'], $matches)) {
            $dealInvoiceNo = trim($matches[0]);
            $letterRefNo = 'ID. No: ' . $dealInvoiceNo;
        } elseif (!empty($letter['reference_no'])) {
            $dealInvoiceNo = trim((string)$letter['reference_no']);
            $letterRefNo = 'ID. No: ' . $dealInvoiceNo;
        } else {
            $dealInvoiceNo = 'WV-LTR-' . (!empty($letter['id']) ? $letter['id'] : '1');
            $letterRefNo = 'ID. No: ' . $dealInvoiceNo;
        }

        $propertyName = !empty($property['name']) ? trim((string)$property['name']) : (!empty($ballot['property_no']) ? trim((string)$ballot['property_no']) : 'MA7055389');
        $propertyArea = !empty($property['area']) ? trim((string)$property['area']) : (!empty($ballot['unit_area']) ? trim((string)$ballot['unit_area']) : '');
        if (!empty($unit['name']))
        {
            $propertyArea .= ' ' . trim((string)$unit['name']);
        }
        $propertyType = !empty($type['name']) ? trim((string)$type['name']) : 'Residential Villa';
        $ballotBlockName = !empty($ballot['block_name']) && $ballot['block_name'] !== 'N/A' ? trim((string)$ballot['block_name']) : 'Awami Block';
        if (strpos($agencyName, 'Awami') !== false || strpos($propertyName, 'Awami') !== false)
        {
            $ballotBlockName = 'Awami Block';
        }

        $dealTotalAmount = !empty($deal['total_amount']) ? (float)$deal['total_amount'] : 0;
        $dealBookingAmount = !empty($deal['booking_amount']) ? (float)$deal['booking_amount'] : 0;
        $dealBalanceAmount = max(0, $dealTotalAmount - $dealBookingAmount);

        $currency = 'PKR ';
        $dealTotalAmountFormatted = $currency . CakeNumber::format($dealTotalAmount);
        $dealBookingAmountFormatted = $currency . CakeNumber::format($dealBookingAmount);
        $dealBalanceAmountFormatted = $currency . CakeNumber::format($dealBalanceAmount);

        $letterDate = !empty($letter['letter_date']) ? date('d-M-Y', strtotime($letter['letter_date'])) : (!empty($letter['created']) ? date('d-M-Y', strtotime($letter['created'])) : date('d-M-Y'));

        // Client Photo resolution
        $clientPhotoFile = !empty($client['photo']) ? trim((string)$client['photo']) : '';
        $clientPhotoDir = !empty($client['dirt']) ? trim((string)$client['dirt']) : (!empty($client['dir']) ? trim((string)$client['dir']) : 'contact');

        $applicantPhotoUrl = '';
        if ($clientPhotoFile !== '')
        {
            if (file_exists(WWW_ROOT . 'img' . DS . $clientPhotoDir . DS . $clientPhotoFile))
            {
                $applicantPhotoUrl = Router::url('/img/' . $clientPhotoDir . '/' . $clientPhotoFile, true);
            }
            elseif (file_exists(WWW_ROOT . 'img' . DS . 'contact' . DS . $clientPhotoFile))
            {
                $applicantPhotoUrl = Router::url('/img/contact/' . $clientPhotoFile, true);
            }
            elseif (file_exists(WWW_ROOT . 'img' . DS . 'contact_thumb' . DS . $clientPhotoFile))
            {
                $applicantPhotoUrl = Router::url('/img/contact_thumb/' . $clientPhotoFile, true);
            }
            elseif (file_exists(WWW_ROOT . 'img' . DS . 'client' . DS . $clientPhotoFile))
            {
                $applicantPhotoUrl = Router::url('/img/client/' . $clientPhotoFile, true);
            }
            elseif (file_exists(WWW_ROOT . 'img' . DS . 'clients' . DS . $clientPhotoFile))
            {
                $applicantPhotoUrl = Router::url('/img/clients/' . $clientPhotoFile, true);
            }
            else
            {
                $applicantPhotoUrl = Router::url('/img/' . $clientPhotoDir . '/' . $clientPhotoFile, true);
            }
        }
        if ($applicantPhotoUrl === '')
        {
            $applicantPhotoUrl = Router::url('/img/User.png', true);
        }
        $fallbackUserUrl = Router::url('/img/User.png', true);
        $applicantPhotoTag = '<img src="' . htmlspecialchars($applicantPhotoUrl, ENT_QUOTES, 'UTF-8') . '" alt="Applicant Photo" style="width:100px;height:120px;object-fit:cover;border:1px solid #cbd5e1;border-radius:4px;" onerror="this.onerror=null;this.src=\'' . htmlspecialchars($fallbackUserUrl, ENT_QUOTES, 'UTF-8') . '\';" />';

        // Agency Logo resolution
        $agencyLogoUrl = '';
        if (!empty($project['logo']) && file_exists(WWW_ROOT . 'img' . DS . $project['logo']))
        {
            $agencyLogoUrl = Router::url('/img/' . $project['logo'], true);
        }
        elseif (!empty($project['photo']) && file_exists(WWW_ROOT . 'img' . DS . $project['photo']))
        {
            $agencyLogoUrl = Router::url('/img/' . $project['photo'], true);
        }
        elseif (file_exists(WWW_ROOT . 'img' . DS . 'logo.png'))
        {
            $agencyLogoUrl = Router::url('/img/logo.png', true);
        }
        elseif (file_exists(WWW_ROOT . 'img' . DS . 'logo-website.fw.png'))
        {
            $agencyLogoUrl = Router::url('/img/logo-website.fw.png', true);
        }
        else
        {
            $agencyLogoUrl = Router::url('/img/logo.png', true);
        }
        $fallbackLogoUrl = Router::url('/img/logo.png', true);
        $agencyLogoTag = '<img src="' . htmlspecialchars($agencyLogoUrl, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($agencyName, ENT_QUOTES, 'UTF-8') . '" style="max-height:60px;max-width:160px;object-fit:contain;" onerror="this.onerror=null;this.src=\'' . htmlspecialchars($fallbackLogoUrl, ENT_QUOTES, 'UTF-8') . '\';" />';

        // QR Code
        $qrImgTag = $qrCodeUrl !== '' ? '<img src="' . htmlspecialchars($qrCodeUrl, ENT_QUOTES, 'UTF-8') . '" alt="Deal QR Code" style="width:1in;height:1in;object-fit:contain;display:block;" />' : '';

        return array(
            'agency_name' => $agencyName,
            'agency_address' => $agencyAddress,
            'agency_email' => $agencyEmail,
            'agency_phone' => $agencyPhone,
            'agency_mobile' => $agencyPhone,
            'agency_logo' => $agencyLogoTag,
            'agency_logo_url' => $agencyLogoUrl,
            'watermark_logo' => $agencyLogoTag,
            'watermark_logo_url' => $agencyLogoUrl,
            'project_logo' => $agencyLogoTag,
            'project_logo_url' => $agencyLogoUrl,
            'client_name' => $clientName,
            'client_father_name' => $clientFather,
            'client_cnic' => $clientCnic,
            'client_email' => $clientEmail,
            'client_address' => $clientAddress,
            'client_phone' => $clientPhone,
            'client_mobile' => $clientMobile,
            'deal_occupation' => $clientOccupation,
            'client_occupation' => $clientOccupation,
            'occupation' => $clientOccupation,
            'nominee_name' => $nomineeName,
            'nominee_cnic' => $nomineeCnic,
            'nominee_mobile' => $nomineeMobile,
            'nominee_cell' => $nomineeMobile,
            'nominee_phone' => $nomineeMobile,
            'nominee_relation' => $nomineeRelation,
            'property_name' => $propertyName,
            'property_file' => $propertyName,
            'property_area' => $propertyArea,
            'property_type' => $propertyType,
            'ballot_block_name' => $ballotBlockName,
            'deal_invoice_no' => $dealInvoiceNo,
            'deal_date' => !empty($deal['date']) ? date('d-M-Y', strtotime($deal['date'])) : date('d-M-Y'),
            'deal_status' => !empty($deal['deal_status']) ? $deal['deal_status'] : (!empty($deal['status']) ? $deal['status'] : 'Active'),
            'deal_total_amount' => $dealTotalAmountFormatted,
            'deal_booking_amount' => $dealBookingAmountFormatted,
            'deal_balance_amount' => $dealBalanceAmountFormatted,
            'letter_ref_no' => $letterRefNo,
            'letter_date' => $letterDate,
            'today' => date('d-M-Y'),
            'applicant_photo' => $applicantPhotoTag,
            'applicant_photo_url' => $applicantPhotoUrl,
            'client_profile_photo' => $applicantPhotoTag,
            'client_profile_photo_url' => $applicantPhotoUrl,
            'client_photo' => $applicantPhotoTag,
            'client_photo_url' => $applicantPhotoUrl,
            'letter_deal_qr' => $qrImgTag,
            'letter_deal_qr_url' => $qrCodeUrl,
            'deal_letter_qr' => $qrImgTag,
            'deal_letter_qr_url' => $qrCodeUrl,
            'deal_qr' => $qrImgTag,
            'deal_qr_url' => $qrCodeUrl,
            'letter_verification_qr' => $qrImgTag,
            'letter_verification_qr_url' => $qrCodeUrl,
            'letter_verification_url' => $verifyUrl,
            'letter_security_hash' => $securitySerial,
            'letter_security_token' => $securitySerial,
            'security_serial' => $securitySerial,
        );
    }

    protected function _injectUrduFontsAndStyles($html)
    {
        $html = (string)$html;
        $urduFontFaceCss = <<<'CSS'
<style type="text/css" id="wv-urdu-font-definitions">
@font-face {
    font-family: 'Jameel Noori Nastaleeq';
    src: url('/whitevillas/fonts/JameelNooriNastaleeq.ttf') format('truetype'),
         url('/fonts/JameelNooriNastaleeq.ttf') format('truetype'),
         url('../fonts/JameelNooriNastaleeq.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}
@font-face {
    font-family: 'Jameel Noori Nastaleeq Kasheeda';
    src: url('/whitevillas/fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype'),
         url('/fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype'),
         url('../fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}
[lang="ur"], [dir="rtl"], .urdu-text, .ashtam-page, .letter-ur, .urdu-body {
    font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', 'Nafees Nastaleeq', serif !important;
}
[lang="ur"] h1, [lang="ur"] h2, [lang="ur"] h3, [lang="ur"] h4, [lang="ur"] h5, [lang="ur"] h6,
[dir="rtl"] h1, [dir="rtl"] h2, [dir="rtl"] h3, [dir="rtl"] h4, [dir="rtl"] h5, [dir="rtl"] h6,
.ashtam-page h1, .ashtam-page h2, .ashtam-page h3, .ashtam-page h4,
.urdu-heading, .ashtam-heading, .document-title, .document-subtitle, .section-title, .bismillah {
    font-family: 'Jameel Noori Nastaleeq Kasheeda', 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', serif !important;
    font-weight: normal;
}
[lang="ur"] p, [lang="ur"] li, [lang="ur"] td, [lang="ur"] th, [lang="ur"] span,
[dir="rtl"] p, [dir="rtl"] li, [dir="rtl"] td, [dir="rtl"] th, [dir="rtl"] span,
.ashtam-page p, .ashtam-page li, .ashtam-page td, .ashtam-page th,
.clause, .note-box, .amount-box {
    font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', 'Nafees Nastaleeq', serif !important;
}
</style>
CSS;

        if (stripos($html, '</head>') !== false)
        {
            $html = str_ireplace('</head>', $urduFontFaceCss . '</head>', $html);
        }
        else
        {
            $html = $urduFontFaceCss . $html;
        }

        return $html;
    }

    protected function _ballotingSnapshotByDeal($dealId)
    {
        $dealId = (int)$dealId;
        if ($dealId <= 0)
        {
            return array();
        }
        try
        {
            $rows = (array)$this->Realestateletter->query(
                "SELECT BA.*, U.permanent_type as unit_permanent_type, U.unit_area as unit_unit_area, U.area_unit as unit_area_unit,
                        T.name as type_name, UN.name as unit_name, C.name as category_name
                 FROM balloting_allotments BA
                 LEFT JOIN balloting_units U ON U.id=BA.balloting_unit_id
                 LEFT JOIN types T ON T.id=BA.property_type_id
                 LEFT JOIN units UN ON UN.id=BA.unit_id
                 LEFT JOIN categories C ON C.id=BA.category_id
                 WHERE BA.deal_id=" . $dealId . " AND BA.status='Allotted'
                 ORDER BY BA.id DESC LIMIT 1"
            );
            if (!empty($rows[0]))
            {
                $r = $rows[0];
                $ba = isset($r['BA']) ? (array)$r['BA'] : $r;
                return array(
                    'allotment_no' => isset($ba['allotment_no']) ? (string)$ba['allotment_no'] : '',
                    'property_no' => isset($ba['property_no']) ? (string)$ba['property_no'] : '',
                    'street_no' => isset($ba['street_no']) ? (string)$ba['street_no'] : '',
                    'block_name' => !empty($ba['block_name']) ? (string)$ba['block_name'] : 'Awami Block',
                    'street_address' => isset($ba['street_address']) ? (string)$ba['street_address'] : '',
                    'unit_area' => !empty($ba['unit_area']) ? (string)$ba['unit_area'] : (!empty($r['unit_unit_area']) ? (string)$r['unit_unit_area'] : ''),
                    'area_unit' => !empty($ba['area_unit']) ? (string)$ba['area_unit'] : (!empty($r['unit_area_unit']) ? (string)$r['unit_area_unit'] : '')
                );
            }
        }
        catch (Exception $e) {}
        return array();
    }

    protected function _letterDetails($id)
    {
        try
        {
            $post = (array)$this->Realestateletter->find('first', array(
                'fields' => array(
                    'Realestateletter.*',
                    'Deal.*',
                    'Client.*',
                    'Property.*',
                    'Project.*',
                    'Template.*',
                    'Type.name',
                    'Contract.name',
                    'Unit.name'
                ),
                'joins' => array(
                    array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Realestateletter.deal_id')),
                    array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=Deal.client_id')),
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id')),
                    array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Property.project_id')),
                    array('table' => 'types', 'alias' => 'Type', 'type' => 'LEFT', 'conditions' => array('Type.id=Property.type_id')),
                    array('table' => 'contracts', 'alias' => 'Contract', 'type' => 'LEFT', 'conditions' => array('Contract.id=Property.contract_id')),
                    array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Unit.id=Property.unit_id')),
                    array('table' => 'templates', 'alias' => 'Template', 'type' => 'LEFT', 'conditions' => array('Template.id=Realestateletter.template_id'))
                ),
                'conditions' => array('Realestateletter.id' => (int)$id),
                'recursive' => -1
            ));

            if (empty($post['Project']['id']) && !empty($post['Realestateletter']['project_id']))
            {
                $this->loadModel('Project');
                $proj = $this->Project->findById((int)$post['Realestateletter']['project_id']);
                if (!empty($proj['Project']))
                {
                    $post['Project'] = $proj['Project'];
                }
            }
            if (empty($post['Project']['id']) && !empty($post['Deal']['project_id']))
            {
                $this->loadModel('Project');
                $proj = $this->Project->findById((int)$post['Deal']['project_id']);
                if (!empty($proj['Project']))
                {
                    $post['Project'] = $proj['Project'];
                }
            }

            return $post;
        }
        catch (Exception $e)
        {
            return array();
        }
    }

    protected function _verificationToken($letterId, $dealId, $clientId)
    {
        return substr(Security::hash((int)$letterId . '|' . (int)$dealId . '|' . (int)$clientId . '|whitevillas-letter-verify', 'sha256', true), 0, 24);
    }
}
