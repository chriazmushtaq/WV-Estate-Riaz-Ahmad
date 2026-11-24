<?php
/**
 *
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts.Email.html
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<?php
/**
 * Modern Responsive CakePHP HTML Email Layout
 * Compatible with Gmail, Outlook, Apple Mail, Webmail, Thunderbird.
 */
$bodyContent = $this->fetch('content');

// If content is already a full HTML document, render as-is to avoid duplicate <html>/<body> wrappers
if (stripos($bodyContent, '<!DOCTYPE') !== false || stripos($bodyContent, '<html') !== false) {
    echo $bodyContent;
    return;
}

// Otherwise, wrap standard system emails (welcome emails, credentials, lead alerts) in a clean, modern responsive template
$agencyName = !empty($this->viewVars['siteName']) ? $this->viewVars['siteName'] : 'White Villas';
$agencyEmail = !empty($this->viewVars['siteEmail']) ? $this->viewVars['siteEmail'] : '';
$agencyPhone = !empty($this->viewVars['contactNo']) ? $this->viewVars['contactNo'] : '';
$agencyAddress = '';
$agencyDomain = Router::url('/', true);
$agencyLogoUrl = '';

App::uses('ClassRegistry', 'Utility');

try {
    $cfg = ClassRegistry::init('Configuration')->findById(1);
    if (!empty($cfg['Configuration'])) {
        $c = $cfg['Configuration'];
        if (!empty($c['organization_name'])) $agencyName = $c['organization_name'];
        elseif (!empty($c['name'])) $agencyName = $c['name'];
        if (empty($agencyEmail) && !empty($c['email'])) $agencyEmail = $c['email'];
        if (empty($agencyPhone) && !empty($c['contact'])) $agencyPhone = $c['contact'];
        if (!empty($c['address'])) $agencyAddress = $c['address'];
        if (!empty($c['domain_name'])) $agencyDomain = $c['domain_name'];
        if (!empty($c['photo'])) {
            $agencyLogoUrl = Router::url('/', true) . 'img/' . $c['photo'];
        }
    }
} catch (Exception $e) {
    // Fallback gracefully
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo h($title_for_layout); ?></title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;color:#334155;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f1f5f9;padding:30px 10px;border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:620px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,0.06);border:1px solid #e2e8f0;border-collapse:separate;">
                    <!-- Top Accent Bar -->
                    <tr>
                        <td style="height:4px;background-color:#0284c7;line-height:4px;font-size:4px;">&nbsp;</td>
                    </tr>
                    
                    <!-- Header Section -->
                    <tr>
                        <td align="center" style="padding:28px 30px 20px 30px;background-color:#ffffff;border-bottom:1px solid #f1f5f9;">
                            <?php if (!empty($agencyLogoUrl)) { ?>
                                <img src="<?php echo h($agencyLogoUrl); ?>" alt="<?php echo h($agencyName); ?>" style="max-height:50px;width:auto;display:inline-block;border:0;outline:none;margin-bottom:8px;" />
                            <?php } ?>
                            <h2 style="margin:4px 0 0 0;font-size:20px;font-weight:800;color:#0f172a;letter-spacing:-0.3px;"><?php echo h($agencyName); ?></h2>
                        </td>
                    </tr>

                    <!-- Main Email Content -->
                    <tr>
                        <td style="padding:28px 30px 24px 30px;font-size:14px;line-height:1.65;color:#334155;">
                            <?php echo $bodyContent; ?>
                        </td>
                    </tr>

                    <!-- Footer Section -->
                    <tr>
                        <td style="background-color:#f8fafc;padding:22px 30px;text-align:center;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;line-height:1.6;">
                            <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:4px;"><?php echo h($agencyName); ?></div>
                            <?php if (!empty($agencyAddress)) { ?>
                                <div style="color:#64748b;margin-bottom:4px;"><?php echo h($agencyAddress); ?></div>
                            <?php } ?>
                            <div style="margin-top:6px;color:#64748b;">
                                <?php if (!empty($agencyPhone)) { ?>
                                    <strong>Phone:</strong> <a href="tel:<?php echo h($agencyPhone); ?>" style="color:#0284c7;text-decoration:none;"><?php echo h($agencyPhone); ?></a>
                                <?php } ?>
                                <?php if (!empty($agencyPhone) && !empty($agencyEmail)) { echo ' &bull; '; } ?>
                                <?php if (!empty($agencyEmail)) { ?>
                                    <strong>Email:</strong> <a href="mailto:<?php echo h($agencyEmail); ?>" style="color:#0284c7;text-decoration:none;"><?php echo h($agencyEmail); ?></a>
                                <?php } ?>
                            </div>
                            <?php if (!empty($agencyDomain)) { ?>
                                <div style="margin-top:6px;">
                                    <strong>Website:</strong> <a href="<?php echo h($agencyDomain); ?>" target="_blank" style="color:#0284c7;text-decoration:none;"><?php echo h($agencyDomain); ?></a>
                                </div>
                            <?php } ?>
                            <div style="border-top:1px solid #e2e8f0;margin-top:14px;padding-top:10px;font-size:11px;color:#94a3b8;">
                                &copy; <?php echo date('Y'); ?> <?php echo h($agencyName); ?>. All rights reserved.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>