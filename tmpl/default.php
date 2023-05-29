<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_page_qr_code
 *
 * @copyright   www.nik-o-mat.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\Registry;

/**
 * Layout variables
 * -----------------
 *
 * @var   string    $imgUrl  The URL the image to show.
 * @var   Registry  $params  The module params.
 */

if (empty($imgUrl)) {
    return;
}

$ausrichten = $params->get('ausrichten');
$groesse    = (int) $params->get('groesse', 120);
$link       = $params->get('link');

?>
<div style="text-align:<?php echo $ausrichten; ?>">
	<img src="<?php echo $imgUrl; ?>" width="<?php echo $groesse ?>" height="<?php echo $groesse ?>" alt="QR-Code dieser Seite" title="QR-Code dieser Seite" />
    <?php if ($link == "1") {
        echo '<br /><a href="https://www.nik-o-mat.de" target="_blank" title="www.nik-o-mat.de" alt="www.nik-o-mat.de" style="font-size: 9px;">www.nik-o-mat.de</a>';
    }; ?>
</div>
