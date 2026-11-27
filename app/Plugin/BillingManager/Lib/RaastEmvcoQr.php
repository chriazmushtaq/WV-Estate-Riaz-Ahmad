<?php
/**
 * State Bank of Pakistan (Raast) & 1Bill Dynamic Payment QR Code Generator
 */
class RaastEmvcoQr
{
    /**
     * Generate Payment QR Payload (Supports Raast IBAN, Smart Web Portal, and EMVCo 1Bill)
     */
    public static function generatePayload($params = array())
    {
        $mode    = !empty($params['qr_mode']) ? trim($params['qr_mode']) : 'raast_iban';
        $raastId = !empty($params['raast_id']) ? trim($params['raast_id']) : 'PK36MEZN000100773310485346';
        $billNo  = !empty($params['bill_number']) ? trim($params['bill_number']) : 'WB9000';
        $amount  = isset($params['amount']) ? sprintf('%.2f', (float)$params['amount']) : '0.00';
        $name    = !empty($params['merchant_name']) ? trim($params['merchant_name']) : 'White Villas';
        $billerCode = !empty($params['biller_code']) ? trim($params['biller_code']) : '100773';

        // MODE 1: Direct Raast IBAN Payment String (Zero API Key Required - Scans in Bank Apps)
        if ($mode === 'raast_iban') {
            return "raast://pay?iban={$raastId}&title=" . urlencode($name) . "&amount={$amount}&ref={$billNo}";
        }

        // MODE 2: Smart Web Payment Portal QR (Scans with Phone Camera & Banking Browsers)
        if ($mode === 'smart_portal') {
            return "https://whitevillas.com.pk/billing/?q={$billNo}";
        }

        // MODE 3: Full SBP Raast / 1Bill EMVCo Utility Payload (Requires 1Link Biller Agreement)
        $cleanBillNo = preg_replace('/[^0-9]/', '', $billNo);
        if (empty($cleanBillNo)) $cleanBillNo = '9000';
        $consumerRef = $billerCode . str_pad($cleanBillNo, 6, '0', STR_PAD_LEFT);

        $payload = '';
        $payload .= self::formatTlv('00', '01'); // Format Indicator
        $payload .= self::formatTlv('01', '12'); // Dynamic QR

        $mInfo = '';
        $mInfo .= self::formatTlv('00', 'RAAST');
        $mInfo .= self::formatTlv('01', $raastId);
        $mInfo .= self::formatTlv('02', $consumerRef);
        $payload .= self::formatTlv('26', $mInfo);

        $payload .= self::formatTlv('52', '0000'); // MCC
        $payload .= self::formatTlv('53', '586');  // Currency: PKR
        $payload .= self::formatTlv('54', $amount); // Amount
        $payload .= self::formatTlv('58', 'PK');    // Country
        $payload .= self::formatTlv('59', substr($name, 0, 25)); // Merchant Name
        $payload .= self::formatTlv('60', 'Lahore'); // City

        $addInfo = self::formatTlv('05', $billNo);
        $payload .= self::formatTlv('62', $addInfo);

        $payloadForCrc = $payload . '6304';
        $crc = self::calculateCrc16($payloadForCrc);
        $payload .= '6304' . strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));

        return $payload;
    }

    /**
     * Generate QR Code Image URL (Offline-Safe Embedded Data URI or Remote Fallback)
     */
    public static function getQrImageUrl($payload, $size = 200)
    {
        // 1. Attempt pure local offline Base64 PNG generation (zero network latency, never empty in PDF/print)
        $appDir = defined('APP') ? APP : (dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR);
        $vendorQrPath = $appDir . 'Vendor' . DIRECTORY_SEPARATOR . 'php-qrcode' . DIRECTORY_SEPARATOR . 'qrcode.php';
        
        if (file_exists($vendorQrPath)) {
            require_once $vendorQrPath;
            if (class_exists('QRCode') && extension_loaded('gd')) {
                try {
                    $qr = new QRCode($payload, array('w' => $size, 'h' => $size, 's' => 'qrl'));
                    $img = $qr->render_image();
                    if ($img) {
                        ob_start();
                        imagepng($img);
                        $png = ob_get_clean();
                        if (function_exists('imagedestroy')) {
                            @imagedestroy($img);
                        }
                        if (!empty($png)) {
                            return 'data:image/png;base64,' . base64_encode($png);
                        }
                    }
                } catch (Exception $e) {
                    // Fall through to external URL fallback
                }
            }
        }

        // 2. Fallback to external API if local GD is unavailable
        $encoded = urlencode($payload);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded}";
    }

    private static function formatTlv($tag, $val)
    {
        $len = str_pad(strlen($val), 2, '0', STR_PAD_LEFT);
        return $tag . $len . $val;
    }

    private static function calculateCrc16($str)
    {
        $crc = 0xFFFF;
        for ($c = 0; $c < strlen($str); $c++) {
            $crc ^= (ord($str[$c]) << 8);
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }
        return $crc;
    }
}
