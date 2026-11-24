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
 * @package       app.View.Emails.html
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<?php
/**
 * CakePHP HTML Email View Template
 * Safely handles both rich HTML emails and plain text messages.
 */
if (strip_tags($content) === $content) {
    // Pure plain text: convert line breaks into clean paragraph tags
    $lines = explode("\n", (string)$content);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            echo '<p style="margin:0 0 14px 0;line-height:1.6;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#334155;">' . h($line) . "</p>\n";
        }
    }
} else {
    // Real HTML template view: output without corrupting with <p> tags
    echo $content;
}
?>