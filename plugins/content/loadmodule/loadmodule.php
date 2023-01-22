<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.loadmodule
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin to enable loading modules into content (e.g. articles)
 * This uses the {loadmodule} syntax
 *
 * @since  1.5
 */
class PlgContentLoadmodule extends CMSPlugin
{
    protected static $modules = array();

    protected static $mods = array();

    /**
     * Plugin that loads module positions within content
     *
     * @param   string   $context   The context of the content being passed to the plugin.
     * @param   object   &$article  The article object.  Note $article->text is also available
     * @param   mixed    &$params   The article params
     * @param   integer  $page      The 'page' number
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        // Don't run this plugin when the content is being indexed
        if ($context === 'com_finder.indexer') {
            return;
        }

        // Only execute if $article is an object and has a text property
        if (!is_object($article) || !property_exists($article, 'text') || is_null($article->text)) {
            return;
        }

        // Simple performance check to determine whether bot should process further
        if (strpos($article->text, 'loadposition') === false && strpos($article->text, 'loadmodule') === false) {
            return;
        }

        // Expression to search for (positions)
        $regex = '/{loadposition\s(.*?)}/i';
        $style = $this->params->def('style', 'none');

        // Expression to search for(modules)
        $regexmod = '/{loadmodule\s(.*?)}/i';
        $stylemod = $this->params->def('style', 'none');

        // Expression to search for(id)
        $regexmodid = '/{loadmoduleid\s([1-9][0-9]*)}/i';

        // Find all instances of plugin and put in $matches for loadposition
        // $matches[0] is full pattern match, $matches[1] is the position
        preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

        // No matches, skip this
        if ($matches) {
            foreach ($matches as $match) {
                $matcheslist = explode(',', $match[1]);

                // We may not have a module style so fall back to the plugin default.
                if (!array_key_exists(1, $matcheslist)) {
                    $matcheslist[1] = $style;
                }

                $position = trim($matcheslist[0]);
                $style    = trim($matcheslist[1]);

                $output = $this->_load($position, $style);

                // We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
                if (($start = strpos($article->text, $match[0])) !== false) {
                    $article->text = substr_replace($article->text, $output, $start, strlen($match[0]));
                }

                $style = $this->params->def('style', 'none');
            }
        }

        // Find all instances of plugin and put in $matchesmod for loadmodule
        preg_match_all($regexmod, $article->text, $matchesmod, PREG_SET_ORDER);

        // If no matches, skip this
        if ($matchesmod) {
            foreach ($matchesmod as $matchmod) {
                $matchesmodlist = explode(',', $matchmod[1]);

                // We may not have a specific module so set to null
                if (!array_key_exists(1, $matchesmodlist)) {
                    $matchesmodlist[1] = null;
                }

                // We may not have a module style so fall back to the plugin default.
                if (!array_key_exists(2, $matchesmodlist)) {
                    $matchesmodlist[2] = $stylemod;
                }

                $module = trim($matchesmodlist[0]);
                $name   = htmlspecialchars_decode(trim($matchesmodlist[1]));
                $stylemod  = trim($matchesmodlist[2]);

                // $match[0] is full pattern match, $match[1] is the module,$match[2] is the title
                $output = $this->_loadmod($module, $name, $stylemod);

                // We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
                if (($start = strpos($article->text, $matchmod[0])) !== false) {
                    $article->text = substr_replace($article->text, $output, $start, strlen($matchmod[0]));
                }

                $stylemod = $this->params->def('style', 'none');
            }
        }

        // Find all instances of plugin and put in $matchesmodid for loadmoduleid
        preg_match_all($regexmodid, $article->text, $matchesmodid, PREG_SET_ORDER);

        // If no matches, skip this
        if ($matchesmodid) {
            foreach ($matchesmodid as $match) {
                $id     = trim($match[1]);
                $output = $this->_loadid($id);

                // We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
                if (($start = strpos($article->text, $match[0])) !== false) {
                    $article->text = substr_replace($article->text, $output, $start, strlen($match[0]));
                }

                $style = $this->params->def('style', 'none');
            }
        }
    }

    /**
     * Loads and renders the module
     *
     * @param   string  $position  The position assigned to the module
     * @param   string  $style     The style assigned to the module
     *
     * @return  mixed
     *
     * @since   1.6
     */
    protected function _load($position, $style = 'none')
    {
        self::$modules[$position] = '';
        $document = Factory::getDocument();
        $renderer = $document->loadRenderer('module');
        $modules  = ModuleHelper::getModules($position);
        $params   = array('style' => $style);
        ob_start();

        foreach ($modules as $module) {
            echo $renderer->render($module, $params);
        }

        self::$modules[$position] = ob_get_clean();

        return self::$modules[$position];
    }

    /**
     * This is always going to get the first instance of the module type unless
     * there is a title.
     *
     * @param   string  $module  The module title
     * @param   string  $title   The title of the module
     * @param   string  $style   The style of the module
     *
     * @return  mixed
     *
     * @since   1.6
     */
    protected function _loadmod($module, $title, $style = 'none')
    {
        self::$mods[$module] = '';
        $document = Factory::getDocument();
        $renderer = $document->loadRenderer('module');
        $mod      = ModuleHelper::getModule($module, $title);

        // If the module without the mod_ isn't found, try it with mod_.
        // This allows people to enter it either way in the content
        if (!isset($mod)) {
            $name = 'mod_' . $module;
            $mod  = ModuleHelper::getModule($name, $title);
        }

        $params = array('style' => $style);
        ob_start();

        if ($mod->id) {
            echo $renderer->render($mod, $params);
        }

        self::$mods[$module] = ob_get_clean();

        return self::$mods[$module];
    }

    /**
     * Loads and renders the module
     *
     * @param   string  $id  The id of the module
     *
     * @return  mixed
     *
     * @since   3.9.0
     */
    protected function _loadid($id)
    {
        self::$modules[$id] = '';
        $document = Factory::getDocument();
        $renderer = $document->loadRenderer('module');
        $modules  = ModuleHelper::getModuleById($id);
        $params   = array('style' => 'none');
        ob_start();

        if ($modules->id > 0) {
            echo $renderer->render($modules, $params);
        }

        self::$modules[$id] = ob_get_clean();

        return self::$modules[$id];
    }
}
