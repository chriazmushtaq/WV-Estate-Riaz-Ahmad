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
 * @package       app.Config
 * @since         CakePHP(tm) v 2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * This is email configuration file.
 *
 * Use it to configure email transports of CakePHP.
 *
 * Email configuration class.
 * You can specify multiple configurations for production, development and testing.
 *
 * transport => The name of a supported transport; valid options are as follows:
 *  Mail - Send using PHP mail function
 *  Smtp - Send using SMTP
 *  Debug - Do not send the email, just return the result
 *
 * You can add custom transports (or override existing transports) by adding the
 * appropriate file to app/Network/Email. Transports should be named 'YourTransport.php',
 * where 'Your' is the name of the transport.
 *
 * from =>
 * The origin email. See CakeEmail::from() about the valid values
 *
 */
class EmailConfig {

	public $default = array(
		'transport' => 'Smtp',
		'from' => array('noreply@whitevillas.com.pk' => 'White Villas'),
		'host' => 'mail.whitevillas.com.pk',
		'port' => 465,
		'timeout' => 15,
		'username' => 'noreply@whitevillas.com.pk',
		'password' => 'Goodby@786',
		'protocol' => 'ssl',
		'ssl_allow_self_signed' => true,
		'context' => array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		),
		'charset' => 'utf-8',
		'headerCharset' => 'utf-8',
		'emailFormat' => 'html',
		'log' => false
	);

	public $smtp = array();

	public function __construct() {
		$this->smtp = $this->default;
		try {
			if (class_exists('DATABASE_CONFIG')) {
				$db = new DATABASE_CONFIG();
				if (!empty($db->default['host']) && !empty($db->default['database'])) {
					$dsn = 'mysql:host=' . $db->default['host'] . ';dbname=' . $db->default['database'];
					if (!empty($db->default['port'])) {
						$dsn .= ';port=' . $db->default['port'];
					}
					$pdo = new PDO($dsn, $db->default['login'], $db->default['password'], array(
						PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
						PDO::ATTR_TIMEOUT => 3
					));

					$stmt = $pdo->query("SELECT * FROM emailsettings WHERE id = 1 LIMIT 1");
					$emailSetting = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

					$stmtCfg = $pdo->query("SELECT name, email FROM configurations WHERE id = 1 LIMIT 1");
					$cfg = $stmtCfg ? $stmtCfg->fetch(PDO::FETCH_ASSOC) : null;

					$siteName = !empty($cfg['name']) ? $cfg['name'] : 'White Villas';
					$siteEmail = !empty($cfg['email']) ? $cfg['email'] : 'info@whitevillas.com.pk';

					if (!empty($emailSetting)) {
						$transport = (!empty($emailSetting['type'])) ? $emailSetting['type'] : 'Smtp';
						$host = (!empty($emailSetting['host'])) ? trim($emailSetting['host']) : 'mail.whitevillas.com.pk';
						$port = (!empty($emailSetting['port'])) ? (int)$emailSetting['port'] : 465;
						$username = (!empty($emailSetting['username'])) ? trim($emailSetting['username']) : 'noreply@whitevillas.com.pk';
						$password = (!empty($emailSetting['password'])) ? $emailSetting['password'] : '';

						$smtpSender = !empty($username) ? $username : $siteEmail;

						$config = array(
							'transport' => $transport,
							'from' => array($smtpSender => $siteName),
							'sender' => $smtpSender,
							'replyTo' => $siteEmail,
							'charset' => 'utf-8',
							'headerCharset' => 'utf-8',
							'emailFormat' => 'html',
							'log' => false
						);

						if ($transport === 'Smtp') {
							$config['host'] = $host;
							$config['port'] = $port;
							$config['timeout'] = 15;
							$config['username'] = $username;
							$config['password'] = $password;

							if ($port === 465) {
								$config['protocol'] = 'ssl';
								$config['ssl_allow_self_signed'] = true;
								$config['context'] = array(
									'ssl' => array(
										'verify_peer' => false,
										'verify_peer_name' => false,
										'allow_self_signed' => true
									)
								);
							} elseif ($port === 587) {
								$config['tls'] = true;
							}
						}

						$this->default = $config;
						$this->smtp = $config;
					}
				}
			}
		} catch (Throwable $e) {
			// Fallback remains as default
		}
	}
}
