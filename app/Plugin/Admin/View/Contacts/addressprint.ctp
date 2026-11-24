<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo __('Address Print');?></title>
    <style>
        @page{size:A4;margin:0;}
        *{box-sizing:border-box;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
        body{background:#e5e7eb;color:#07152a;font-family:Inter,Arial,sans-serif;margin:0;padding:0;}
        .controls{display:flex;gap:10px;left:20px;position:fixed;top:20px;z-index:100;}
        .controls button{background:#07152a;border:0;border-radius:8px;box-shadow:0 8px 18px rgba(15,23,42,.18);color:#fff;cursor:pointer;font-size:13px;font-weight:800;padding:11px 18px;}
        .controls .light{background:#fff;border:1px solid #cbd5e1;color:#07152a;}
        .page{background:#fff;box-shadow:0 10px 25px rgba(0,0,0,.12);margin:10mm auto;min-height:297mm;padding:8mm;width:210mm;}
        .label-grid{column-gap:6mm;display:grid;grid-template-columns:1fr 1fr;row-gap:5mm;}
        .label-card{background:#fff;border:1.7px solid #000;display:grid;grid-template-rows:1fr 20mm;height:65.25mm;overflow:hidden;position:relative;}
        .recipient-body{display:grid;grid-template-rows:auto auto 1fr auto;min-height:0;padding:9px 14px 6px;}
        .to-tag{color:#64748b;display:block;font-size:8.5pt;font-weight:900;line-height:1;margin-bottom:4px;text-transform:uppercase;}
        .customer-name{color:#000;font-size:13.4pt;font-weight:900;letter-spacing:-.02em;line-height:1.03;margin-bottom:4px;max-height:28pt;overflow:hidden;text-transform:uppercase;}
        .address-text{color:#172033;font-size:9.4pt;font-weight:600;line-height:1.22;max-height:48pt;overflow:hidden;white-space:pre-line;}
        .contact-row{align-items:center;align-self:end;color:#000;display:flex;font-size:9pt;font-weight:900;gap:6px;padding-top:4px;}
        .contact-row .phone-icon{color:#d1002f;font-size:11pt;}
        .sender-footer{align-items:center;background:#f8fafc;border-top:1.5px solid #000;display:grid;gap:6px;grid-template-columns:1fr 110px;height:20mm;padding:4px 8px;}
        .sender-info{color:#334155;font-size:5.8pt;font-weight:800;line-height:1.12;min-width:0;overflow:hidden;text-transform:uppercase;}
        .sender-label{color:#000;display:block;font-size:6pt;font-weight:900;line-height:1;margin-bottom:4px;text-decoration:underline;}
        .sender-name{color:#000;display:block;font-size:7.4pt;font-weight:900;line-height:1.05;margin-bottom:2px;}
        .sender-address{display:block;font-size:5.6pt;font-weight:700;line-height:1.1;}
        .sender-phone{display:block;font-size:5.6pt;font-weight:800;line-height:1.1;margin-top:1px;}
        .sender-text{max-height:38px;overflow:hidden;}
        .company-logo-box{align-items:center;background:transparent;border:0;display:flex;height:58px;justify-content:center;overflow:hidden;padding:0;text-align:center;width:110px;}
        .company-logo-box img{display:block;max-height:56px;max-width:108px;object-fit:contain;}
        .logo-text{color:#111827;font-size:7pt;font-weight:900;letter-spacing:.4px;text-transform:uppercase;}
        .logo-subtext{color:#94a3b8;font-size:5pt;font-weight:700;}
        .empty-state{align-items:center;border:2px dashed #cbd5e1;color:#64748b;display:flex;font-size:16px;font-weight:900;justify-content:center;min-height:180px;text-align:center;}
        @media print{
            body{background:#fff;}
            .controls{display:none!important;}
            .page{box-shadow:none;margin:0;padding:8mm;}
            .label-card{border:2px solid #000;}
        }
    </style>
</head>
<body>
<?php
$contacts = isset($contacts) && is_array($contacts) ? $contacts : array();
$agencyHeaders = isset($agencyHeaders) && is_array($agencyHeaders) ? $agencyHeaders : array();
$siteFallback = array(
    'name' => isset($siteOrganization) ? (string)$siteOrganization : (isset($siteName) ? (string)$siteName : ''),
    'address' => isset($siteAddress) ? (string)$siteAddress : '',
    'phone' => isset($contact) ? (string)$contact : '',
    'mobile' => '',
    'email' => isset($siteEmail) ? (string)$siteEmail : '',
    'logo' => isset($frontLogo) ? (string)$frontLogo : ''
);
$view = $this;
$logoUrlFromPath = function($logo) use ($view) {
    $logo = trim((string)$logo);
    if($logo === '')
    {
        return '';
    }
    if(preg_match('#^https?://#i', $logo) || strpos($logo, '/') === 0)
    {
        return $logo;
    }
    return $view->Html->url('/img/'.ltrim($logo, '/'));
};
?>
<div class="controls no-print">
    <button onclick="window.print()"><?php echo __('Print Final Labels');?></button>
    <button class="light" onclick="window.close()"><?php echo __('Close');?></button>
</div>

<div class="page">
    <?php if(empty($contacts)){ ?>
        <div class="empty-state"><?php echo __('Please select contacts from the Contacts page before using Address Print.');?></div>
    <?php }else{ ?>
        <div class="label-grid">
            <?php foreach($contacts as $row):
                $c = !empty($row['Contact']) ? (array)$row['Contact'] : array();
                $projectId = !empty($c['project_id']) ? (int)$c['project_id'] : 0;
                $sender = !empty($agencyHeaders[$projectId]) ? $agencyHeaders[$projectId] : (!empty($agencyHeaders[0]) ? $agencyHeaders[0] : $siteFallback);
                $recipientName = trim((string)(isset($c['name']) ? $c['name'] : ''));
                $addressParts = array();
                foreach(array('address','district','state') as $addressField)
                {
                    if(!empty($c[$addressField]) && !in_array(trim((string)$c[$addressField]), $addressParts, true))
                    {
                        $addressParts[] = trim((string)$c[$addressField]);
                    }
                }
                $recipientAddress = implode(",\n", $addressParts);
                $recipientPhone = !empty($c['mobile']) ? trim((string)$c['mobile']) : (!empty($c['phone']) ? trim((string)$c['phone']) : '');
                $senderName = !empty($sender['name']) ? trim((string)$sender['name']) : $siteFallback['name'];
                $senderAddress = !empty($sender['address']) ? trim((string)$sender['address']) : $siteFallback['address'];
                $senderPhone = !empty($sender['mobile']) ? trim((string)$sender['mobile']) : (!empty($sender['phone']) ? trim((string)$sender['phone']) : $siteFallback['phone']);
                $senderLogo = !empty($sender['logo']) ? $logoUrlFromPath($sender['logo']) : $logoUrlFromPath($siteFallback['logo']);
            ?>
            <div class="label-card">
                <div class="recipient-body">
                    <div class="to-tag"><?php echo __('Recipient');?></div>
                    <div class="customer-name"><?php echo h($recipientName !== '' ? $recipientName : __('Customer'));?></div>
                    <div class="address-text"><?php echo h($recipientAddress !== '' ? $recipientAddress : __('Address not specified'));?></div>
                    <?php if($recipientPhone !== ''){ ?>
                    <div class="contact-row"><span class="phone-icon">&#9742;</span> <?php echo __('Ph:');?> <?php echo h($recipientPhone);?></div>
                    <?php } ?>
                </div>
                <div class="sender-footer">
                    <div class="sender-info">
                        <span class="sender-label"><?php echo __('From (Return Address):');?></span>
                        <div class="sender-text">
                            <span class="sender-name"><?php echo h($senderName);?></span>
                            <span class="sender-address"><?php echo h($senderAddress);?></span>
                            <?php if($senderPhone !== ''){ ?><span class="sender-phone"><?php echo __('Ph:');?> <?php echo h($senderPhone);?></span><?php } ?>
                        </div>
                    </div>
                    <div class="company-logo-box">
                        <?php if($senderLogo !== ''){ ?>
                            <img src="<?php echo h($senderLogo);?>" alt="<?php echo h($senderName);?>">
                        <?php }else{ ?>
                            <div>
                                <div class="logo-text"><?php echo __('LOGO');?></div>
                                <div class="logo-subtext"><?php echo __('BRANDING');?></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php } ?>
</div>

<?php if(!empty($contacts)){ ?>
<script type="text/javascript">
setTimeout(function(){
    if(typeof window.print !== 'undefined'){
        window.print();
    }
}, 350);
</script>
<?php } ?>
</body>
</html>
