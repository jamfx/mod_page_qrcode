<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_page_qr_code
 *
 * @copyright   www.nik-o-mat.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Nikomate\Module\PageQrCode\Site\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Nikomate\Module\PageQrCode\Site\Helper\PageQrCodeHelper;

/**
 * Dispatcher class for mod_page_qr_code
 *
 * @since  2.0.0
 */
class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   2.0.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $data['imgUrl'] = (new PageQrCodeHelper)->getQrCodeImageUrl($data['params']);

        return $data;
    }
}
