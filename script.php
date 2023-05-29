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

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Script file of Joomla CMS
 *
 * @since  1.6.4
 */
class Mod_Page_Qr_CodeInstallerScript
{
    /**
     * The version we are updating from
     *
     * @var    string
     * @since  2.0.0
     */
    public static $fromVersion = null;

    /**
     * Function to act prior to installation process begins
     *
     * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
     * @param   Installer  $installer  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   2.0.0
     */
    public function preflight($action, $installer)
    {
        if ($action === 'update') {
            // Get the version we are updating from
            if (empty($installer->extension->manifest_cache)) {
                $manifestValues = json_decode($this->getExtensionValue('manifest_cache'), true);
            } else {
                $manifestValues = json_decode($installer->extension->manifest_cache, true);
            }

            if (array_key_exists('version', $manifestValues)) {
                self::$fromVersion = $manifestValues['version'];
            }
        }

        return true;
    }

    /**
     * Called after any type of action
     *
     * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
     * @param   Installer  $installer  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   2.0.0
     */
    public function postflight($action, $installer)
    {
        if ($action !== 'update') {
            return true;
        }

        if (!empty(self::$fromVersion) && version_compare(self::$fromVersion, '2.0.0', 'lt')) {
            $this->deleteUnexistingFiles();
            $this->fixDefaultParams();
            $this->fixModulesParams();
        }

        return true;
    }

    /**
     * Delete files that should not exist
     *
     * @param   bool  $dryRun          If set to true, will not actually delete files, but just report their status for use in CLI
     * @param   bool  $suppressOutput  Set to true to suppress echoing any errors, and just return the $status array
     *
     * @return  array
     *
     * @since   2.0.0
     */
    private function deleteUnexistingFiles($dryRun = false, $suppressOutput = false)
    {
        $status = [
            'files_exist'     => [],
            'files_deleted'   => [],
            'files_errors'    => [],
            'files_checked'   => [],
        ];

        $files = [
            // From prior 2.0.0
            '/modules/mod_page_qr_code/mod_page_qr_code.php',
            '/language/de-DE/de-DE.mod_page_qr_code.ini',
            '/language/en-GB/en-GB.mod_page_qr_code.ini',
        ];

        $status['files_checked']   = $files;

        foreach ($files as $file) {
            if ($fileExists = File::exists(JPATH_ROOT . $file)) {
                $status['files_exist'][] = $file;

                if ($dryRun === false) {
                    if (File::delete(JPATH_ROOT . $file)) {
                        $status['files_deleted'][] = $file;
                    } else {
                        $status['files_errors'][] = Text::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file);
                    }
                }
            }
        }

        if ($suppressOutput === false && count($status['files_errors'])) {
            echo implode('<br>', $status['files_errors']);
        }

        return $status;
    }

    /**
     * Fix the params for the extension itself.
     *
     * @return  void
     *
     * @since   2.0.0
     */
    private function fixDefaultParams(): void
    {
        /** @var $db \Joomla\Database\DatabaseDriver */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $oldParams = json_decode($this->getExtensionValue('params'), true);
        $params    = new Registry($oldParams);
        $groesse   = $params->get('groesse');

        list($width) = explode('x', $groesse);

        $params->set('groesse', (int) $width);
        $params->remove('moduleclass_sfx');

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . '=' . $db->quote($params->toString()))
            ->where($db->quoteName('type') . '=' . $db->quote('module'))
            ->andWhere($db->quoteName('element') . '=' . $db->quote('mod_page_qr_code'));

        try {
            $db->setQuery($query)->execute();
        } catch (Exception $e) {
            echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';
        }
    }

    /**
     * Fix the params for all site modules in use.
     *
     * @return  void
     *
     * @since   2.0.0
     */
    private function fixModulesParams(): void
    {
        /** @var $db \Joomla\Database\DatabaseDriver */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);
        $query->select($db->quoteName(['id', 'params']))
            ->from('#__modules')
            ->where($db->quoteName('module') . '=' . $db->quote('mod_page_qr_code'));

        try {
            $modules = $db->setQuery($query)->loadRowList();
        } catch (Exception $e) {
            echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

            return;
        }

        foreach ($modules as $module) {
            $moduleId     = $module[0];
            $moduleParams = json_decode($module[1], true);
            $moduleParams = new Registry($moduleParams);
            $groesse      = $moduleParams->get('groesse');

            list($width) = explode('x', $groesse);

            $moduleParams->set('groesse', (int) $width);
            $moduleParams->remove('moduleclass_sfx');

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__modules'))
                ->set($db->quoteName('params') . '=' . $db->quote($moduleParams->toString()))
                ->where($db->quoteName('id') . '=' . $db->quote($moduleId));

            try {
                $db->setQuery($query)->execute();
            } catch (Exception $e) {
                echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';
            }
        }
    }

    /**
     * Get the column value from database for the extension
     *
     * @param   string  $column  The column name.
     *
     * @return string|integer|null
     */
    private function getExtensionValue($column) {
        /** @var $db \Joomla\Database\DatabaseDriver */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);
        $query->select($db->quoteName($column))
            ->from('#__extensions')
            ->where($db->quoteName('type') . '=' . $db->quote('module'))
            ->andWhere($db->quoteName('element') . '=' . $db->quote('mod_page_qr_code'));

        try {
            return $db->setQuery($query)->loadResult();
        } catch (Exception $e) {
            echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

            return null;
        }
    }
}
