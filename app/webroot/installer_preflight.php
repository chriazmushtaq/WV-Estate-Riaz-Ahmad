<?php
/**
 * Standalone installer requirement guard.
 *
 * This file intentionally avoids CakePHP classes so /install can display a
 * useful message even when the selected cPanel PHP version/extensions cannot
 * boot the application.
 */
function whitevillas_installer_required_checks()
{
	$phpMin = '7.2.0';
	$phpMax = '8.4.0';
	$imageOk = extension_loaded('gd') || extension_loaded('imagick');
	return array(
		array('label' => 'PHP Version', 'ok' => (version_compare(PHP_VERSION, $phpMin, '>=') && version_compare(PHP_VERSION, $phpMax, '<')), 'value' => PHP_VERSION, 'fix' => 'Select PHP 7.2 up to PHP 8.3 in cPanel MultiPHP Manager. Recommended: PHP 8.1 or 8.2.'),
		array('label' => 'PDO Extension', 'ok' => class_exists('PDO') && extension_loaded('pdo'), 'value' => (extension_loaded('pdo') ? 'Enabled' : 'Disabled'), 'fix' => 'Enable the pdo extension in cPanel PHP Extensions.'),
		array('label' => 'PDO MySQL Extension', 'ok' => extension_loaded('pdo_mysql'), 'value' => (extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled'), 'fix' => 'Enable pdo_mysql / mysqlnd in cPanel PHP Extensions.'),
		array('label' => 'MySQLi Extension', 'ok' => extension_loaded('mysqli'), 'value' => (extension_loaded('mysqli') ? 'Enabled' : 'Disabled'), 'fix' => 'Enable mysqli / mysqlnd in cPanel PHP Extensions.'),
		array('label' => 'JSON Extension', 'ok' => extension_loaded('json'), 'value' => (extension_loaded('json') ? 'Enabled' : 'Disabled'), 'fix' => 'Enable json extension.'),
		array('label' => 'MBString Extension', 'ok' => extension_loaded('mbstring'), 'value' => (extension_loaded('mbstring') ? 'Enabled' : 'Disabled'), 'fix' => 'Enable mbstring extension.'),
		array('label' => 'OpenSSL Extension', 'ok' => extension_loaded('openssl'), 'value' => (extension_loaded('openssl') ? 'Enabled' : 'Disabled'), 'fix' => 'Enable openssl extension.'),
		array('label' => 'XML + DOM Extension', 'ok' => (extension_loaded('xml') && class_exists('DOMDocument')), 'value' => ((extension_loaded('xml') && class_exists('DOMDocument')) ? 'Enabled' : 'Disabled'), 'fix' => 'Enable xml/dom extension.'),
		array('label' => 'Image Library (GD or Imagick)', 'ok' => $imageOk, 'value' => ($imageOk ? 'Enabled' : 'Disabled'), 'fix' => 'Enable gd or imagick extension.')
	);
}

function whitevillas_installer_html($value)
{
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function whitevillas_installer_preflight_or_continue()
{
	$checks = whitevillas_installer_required_checks();
	$failed = array();
	foreach ($checks as $check) {
		if (empty($check['ok'])) {
			$failed[] = $check;
		}
	}
	if (empty($failed)) {
		return;
	}

	if (!headers_sent()) {
		header('Content-Type: text/html; charset=UTF-8');
		header('HTTP/1.1 200 OK');
	}
	?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>White Villas Installer Requirements</title>
	<style>
		body{font-family:Arial,Helvetica,sans-serif;background:#f4f7fb;color:#102033;margin:0;padding:30px;}
		.wrap{max-width:980px;margin:0 auto;background:#fff;border:1px solid #d8e2ef;border-radius:8px;box-shadow:0 8px 28px rgba(15,33,55,.08);overflow:hidden;}
		.head{background:#102a43;color:#fff;padding:18px 24px;}
		.head h1{font-size:22px;margin:0 0 4px;}
		.body{padding:24px;}
		.alert{background:#fff3cd;border:1px solid #ffe08a;color:#624500;border-radius:6px;padding:14px;margin-bottom:18px;}
		table{width:100%;border-collapse:collapse;margin-top:14px;}
		th,td{border-bottom:1px solid #e7edf5;padding:10px;text-align:left;vertical-align:top;}
		th{background:#f7fafc;}
		.badge{display:inline-block;border-radius:12px;padding:4px 9px;font-size:12px;font-weight:bold;}
		.ok{background:#dcfce7;color:#166534;}
		.fail{background:#fee2e2;color:#991b1b;}
		.note{background:#eef6ff;border:1px solid #bfdbfe;border-radius:6px;padding:12px;margin-top:18px;}
		code{background:#f1f5f9;padding:2px 5px;border-radius:4px;}
	</style>
</head>
<body>
<div class="wrap">
	<div class="head">
		<h1>White Villas Installer Requirements</h1>
		<div>Current PHP: <?php echo whitevillas_installer_html(PHP_VERSION); ?></div>
	</div>
	<div class="body">
		<div class="alert">
			The installer cannot continue with the selected PHP version/extensions. Please fix the items below in cPanel, then refresh <code>/install</code>.
		</div>
		<table>
			<thead>
			<tr>
				<th>Check</th>
				<th>Status</th>
				<th>Current</th>
				<th>How to Fix</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($checks as $check) { ?>
				<tr>
					<td><?php echo whitevillas_installer_html($check['label']); ?></td>
					<td><span class="badge <?php echo !empty($check['ok']) ? 'ok' : 'fail'; ?>"><?php echo !empty($check['ok']) ? 'OK' : 'Required'; ?></span></td>
					<td><?php echo whitevillas_installer_html($check['value']); ?></td>
					<td><?php echo whitevillas_installer_html($check['fix']); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<div class="note">
			In cPanel, open <strong>MultiPHP Manager</strong> and select PHP <strong>8.1 or 8.2</strong> for this domain. Then open <strong>Select PHP Version / PHP Extensions</strong> and enable <strong>pdo</strong>, <strong>pdo_mysql</strong>, <strong>mysqli</strong>, <strong>mbstring</strong>, <strong>openssl</strong>, <strong>xml/dom</strong>, and <strong>gd or imagick</strong>.
		</div>
	</div>
</div>
</body>
</html>
	<?php
	exit();
}
?>
