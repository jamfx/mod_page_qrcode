<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_page_qr_code
 *
 * @copyright   www.nik-o-mat.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Nikomate\Module\PageQrCode\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * Helper for mod_articles_latest
 *
 * @since  2.0.0
 */
class PageQrCodeHelper
{
    /**
     * Retrieve the QR-Code image url from API
     *
     * @param   Registry  $params  The module parameters.
     *
     * @return  string
     *
     * @since   2.0.0
     */
    public function getApiQrCodeImageUrl(Registry $params)
    {
        $farbe      = ltrim($params->get('farbe', '#000000'), '#');
        $bgfarbe    = ltrim($params->get('bgfarbe', '#ffffff'), '#');
        $margin     = (int) $params->get('margin', 1);
        $currenturl = urlencode(URI::current());
        $url        = "https://api.qrserver.com/v1/create-qr-code/?data=$currenturl&amp;color=$farbe&amp;bgcolor=$bgfarbe&amp;qzone=$margin&amp;format=svg";

        return $url;
    }

    /**
     * Retrieve the QR-Code image url
     *
     * @param   Registry  $params  The module parameters.
     *
     * @return  string
     *
     * @since   2.0.0
     */
    public function getQrCodeImageUrl(Registry $params)
    {
        return $this->getApiQrCodeImageUrl($params);
    }
}
