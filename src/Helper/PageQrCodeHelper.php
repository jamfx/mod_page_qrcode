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

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Exception\FilesystemException;
use Joomla\Registry\Registry;

/**
 * Helper for mod_articles_latest
 *
 * @since  2.0.0
 */
class PageQrCodeHelper
{
    /**
     * The module params
     *
     * @var   string
     *
     * @since  2.0.4
     */
    private $cachePath = JPATH_ADMINISTRATOR . '/cache/mod_page_qr_code';

   /**
     * The module params
     *
     * @var   Registry
     *
     * @since  2.0.4
     */
    private $params;

    /**
     * The QR-Code image url from API
     *
     * @var   string
     *
     * @since  2.0.4
     */
    private $apiQrCodeImageUrl;

    /**
     * Set the QR-Code image url from API
     *
     * @return  void
     *
     * @since   2.0.4
     */
    private function setApiQrCodeImageUrl()
    {
        $farbe                   = ltrim($this->params->get('farbe', '#000000'), '#');
        $bgfarbe                 = ltrim($this->params->get('bgfarbe', '#ffffff'), '#');
        $margin                  = (int) $this->params->get('margin', 1);
        $currenturl              = urlencode(URI::current());
        $this->apiQrCodeImageUrl = "https://api.qrserver.com/v1/create-qr-code/?data=$currenturl&amp;color=$farbe&amp;bgcolor=$bgfarbe&amp;qzone=$margin&amp;format=svg";
    }

    /**
     * Get the response data from URL
     *
     * @param   array|\ArrayAccess  $options  Client options array.
     *
     * @return  object
     *
     * @since   2.0.4
     */
    private function getHttpResponseData($options = array())
    {
        $url      = $this->apiQrCodeImageUrl;
        $response = new \stdClass();

        if (empty($options)) {
            $options = array('sslverify' => false,);
        }

        $options = new Registry($options);
        $http    = HttpFactory::getHttp($options);

        try {
            $response = $http->get($url);
        } catch (\RuntimeException $e) {
            $response->code = 500;
            $response->body = $e->getMessage();
        }

        return $response;
    }

    /**
     * Set the QR-Code base64 encoded image from cache
     *
     * @return  string|null
     *
     * @since   2.0.4
     */
    private function getCacheQrCode()
    {
        $currentUrl = URI::current();
        $uniqueId   = md5($currentUrl);
        $cachedFile = $this->cachePath .'/' . $uniqueId . '.svg';

        if (file_exists($cachedFile)) {
            $base64Image = base64_encode(file_get_contents($cachedFile));

            return 'data:image/svg+xml;base64,' . $base64Image;
        }

        $response              = $this->getHttpResponseData();
        $statusCode            = $response->code;
        $apiQrCodeImageContent = $response->body;

        if ($statusCode < 200 || $statusCode >= 400 || empty($apiQrCodeImageContent)) {
            if ($this->params->get('debug', 0)) {
                Factory::getApplication()
                    ->enqueueMessage(
                        Text::sprintf(
                            'MOD_PAGE_QR_CODE_ERROR_LOADING_FROM_API',
                            'QR-Code API (goqr.me)',
                            $this->apiQrCodeImageUrl,
                            $apiQrCodeImageContent
                        ),
                        'error'
                    );
            }

            return null;
        }

        $base64Image = base64_encode($this->saveFile($cachedFile, $apiQrCodeImageContent));

        return 'data:image/svg+xml;base64,' . $base64Image;
    }

    /**
     * Save file
     *
     * @param   string  $filename  The file name to save the buffer.
     * @param   string  $buffer    The content to save.
     *
     * @return  string      The content of the saved file.
     * @throws  \Exception  If the file couldn't be saved.
     *
     * @since   2.0.4
     */
    private function saveFile($filename, $buffer)
    {
        if (!file_exists($filename)
            && false === File::write($filename, $buffer)
        ) {
            throw new FilesystemException(
                sprintf('Could not write the file: %s', $filename)
            );
        }

        return $buffer;
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
        $this->params = $params;
        $cacheOnOff   = $this->params->get('cacheOnOff', 1);

        $this->setApiQrCodeImageUrl();

        if ($cacheOnOff) {
            return $this->getCacheQrCode();
        }

        return $this->apiQrCodeImageUrl;
    }
}
