<?php
namespace PHORAX\Mydashboard\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2008 Tim Lochmueller <webmaster@fruit-lab.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

#$GLOBALS['BE_USER']->modAccess($MCONF, 1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Fluid\ViewHelpers\Be\InfoboxViewHelper;


/**
 * Module 'Dashboard' for the 'mydashboard' extension.
 */
class MydashboardController extends \TYPO3\CMS\Backend\Module\BaseScriptClass
{

    /* Page Info */
    public $pageinfo;

    /**
     * @var \tx_mydashboard_widgetmgm
     */
    protected $mgm;

    private $jsLoaded = [];

    /**
     * @var string
     */
    protected $moduleName = 'user_txmydashboardM1';

    public function __construct()
    {
        $GLOBALS['LANG']->includeLLFile('EXT:mydashboard/mod1/locallang.xml');
    }

    /*
     * Initializes the Module
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        // header("Content-Type: text/html; charset=utf-8");

        $this->mgm = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\tx_mydashboard_widgetmgm::class);
    }

    /*
     * Adds items to the ->MOD_MENU array. Used for the function menu selector.
     *
     * @return	void
     */
    public function menuConfig()
    {
        $this->MOD_MENU = [
            'function' => [
                '1' => $GLOBALS['LANG']->getLL('title'),
                '2' => $GLOBALS['LANG']->getLL('config')
            ]
        ];
        parent::menuConfig();
    }

    /*
     * Main function of the module. Write the content to $this->content
     * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
     *
     */
    public function main()
    {

        //global $GLOBALS['BE_USER'];
        //
        //// Access check!
        //$this->id = 1;
        //$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
        //$access = is_array($this->pageinfo) ? 1 : 0;
        //
        //if (($this->id && $access) || $GLOBALS['BE_USER']->user['admin'])	{
            if (isset($_REQUEST['ajax'])) {
                $this->renderAJAX();
            } else {
                $this->renderContent();
            }
        //} else {
        //	$this->content .= 'No Access to this Module';
        //} # if
    }

    /**
     * Injects the request object for the current request or subrequest
     * Then checks for module functions that have hooked in, and renders menu etc.
     *
     * @param ServerRequestInterface $request the current request
     * @param ResponseInterface $response
     * @return ResponseInterface the response with the content
     */
    public function mainAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $GLOBALS['SOBE'] = $this;
        $this->init();

        // Checking for first level external objects
        $this->checkExtObj();

        // Checking second level external objects
        $this->checkSubExtObj();
        $this->main();

        $this->moduleTemplate->setContent($this->content);

        $response->getBody()->write($this->moduleTemplate->renderContent());
        return $response;
    }

    /*
     * Generates the module content
     *
     * @return	void
     */
    public function moduleContent()
    {
        $content = '';
        switch ((string)$this->MOD_SETTINGS['function']) {
            case 1:
                $content .= $this->doc->section('', $this->showDashboard(), 0, 1);
                break;
            case 2:
                $content .= $this->doc->section('', $this->showConfig(), 0, 1);
                break;
        }

        return $content;
    }

    /*
     * Check if the current user have access to the configuration of the dashboard
     *
     * @return boolean
     */
    public function currentUserHaveConfigAccess()
    {
        if (!is_array($GLOBALS['BE_USER']->user)) {
            return false;
        }
        return true;
    }

    /**
     * Render the AJAX Content for widgets
     * @return bool
     */
    public function renderAJAX()
    {
        $user = $GLOBALS['BE_USER']->user['uid'];
        $this->mgm->loadUserConf($user);
        $_REQUEST['key'] = trim($_REQUEST['key'], '#');
        switch ($_REQUEST['action']) {
            case 'refresh':
                $widget = $this->mgm->getWidget($_REQUEST['key']);
                $this->content = $widget->getContent();
                break;

            case 'refresh_config':
                parse_str($_REQUEST['value'], $output);
                if ($this->mgm->setWidgetConf($_REQUEST['key'], $output)) {
                    $this->mgm->safeUserConf($user);
                }
                $widget = $this->mgm->getWidget($_REQUEST['key']);
                $this->content = $widget->getContent();
                break;

            case 'config':
                $widget = $this->mgm->getWidget($_REQUEST['key']);
                $this->content = $widget->getConfig();
                break;

            case 'delete':
                if ($this->mgm->removeWidget($_REQUEST['key'])) {
                    $this->mgm->safeUserConf($user);
                }
                $this->content = '';
                break;

            case 'reorder':
                parse_str($_REQUEST['data'], $output);
                if ($this->mgm->setNewOrder($output)) {
                    $this->mgm->safeUserConf($user);
                }
                break;

            default:
                $this->content = 'No Action';
                break;
        }
        return true;
    }

    /*
     * Show the Dashboard
     */
    public function showDashboard()
    {
        // Config an preload
        $this->mgm->loadUserConf($GLOBALS['BE_USER']->user['uid']);
        $userConf = $this->mgm->getUserConf();
        $content = ['', '', '', ''];
        $rows = intval($userConf['config']['rows']);

        // Generate the Widgets
        for ($i = 0; $i < $rows; $i++) {
            if (!is_array($userConf['position'][$i])) {
                $userConf['position'][$i] = [];
            }

            foreach ($userConf['position'][$i] as $widgetKey) {

                // Create the Widget Object
                $widget = $this->mgm->getWidget($widgetKey);

                // Continue if it is no Object
                if (!is_object($widget)) {
                    continue;
                }

                $widget_c = '<div class="widget" id="widget_' . $widgetKey . '"><h2 onmouseover="showOptions(\'widget_' . $widgetKey . '\')" onmouseout="hideOptions(\'widget_' . $widgetKey . '\');"><span class="icon">' . $this->mgm->renderIcon($widget) . '</span><span class="text">' . $widget->getTitle() . '</span>';
                $widget_c .= '<a href="#" class="delete" id="widget_' . $widgetKey . '_delete" style="display: none" onclick="deleteWidget(\'' . $widgetKey . '\')">Delete</a>';
                if ($widget->isConfig()) {
                    $widget_c .= '<a href="#" class="config" id="widget_' . $widgetKey . '_config" style="display: none" onclick="configWidget(\'' . $widgetKey . '\')">Config</a>';
                }
                $widget_c .= '<a href="#" class="refresh" id="widget_' . $widgetKey . '_refresh" style="display: none" onclick="refreshWidget(\'' . $widgetKey . '\')">Refresh</a>';

                $widget_c .= '</h2>
				<div class="content" id="widget_' . $widgetKey . '_content">' . $widget->getContent() . '</div>
				</div>';

                foreach ($widget->getJSFiles() as $file) {
                    if (!in_array($file, $this->jsLoaded)) {
                        $this->jsLoaded[] = $file;
                        $this->doc->JScode .= '<script type="text/javascript" src="' . $file . '"></script>';
                    }
                }

                $content[$i] .= $widget_c;
            }

            $content[$i] = '<div id="container' . $i . '" class="container-rows-' . $rows . '">' . $content[$i] . '</div>';
        }

        $content = '<div class="widgets">' . implode($content) . '<div class="clearer"></div></div>';

        // Javascript
        $js = '';
        $container = [];
        $newOrder = [];
        for ($i = 0; $i < intval($userConf['config']['rows']); $i++) {
            if (!is_array($userConf['position'][$i])) {
                continue;
            }

//			$js .= 'Sortable.create("container'.$i.'",{tag: \'div\',dropOnEmpty:true,containment:[###CONTAINER###],constraint:false, onUpdate:sendNewOrder});'."\n";
            $container[] = '"container' . $i . '"';
            $newOrder[] = 'parms = Sortable.serialize("container' . $i . '", {name: \'' . $i . '\'})+"&"+parms;';
        }

        $js = "<script type=\"text/javascript\">\n" . str_replace('###CONTAINER###', implode(',', $container), $js) . "
		
		function sendNewOrder(){
			var parms = '';
			" . implode("\n", $newOrder) . "
			new Ajax.Request('index.php', {
				parameters: { ajax: 1, action: 'reorder', data: parms },
			});
		}
		</script>";

        return $content . $js;
    }

    /*
     * Render the Main Content
     */
    public function renderContent()
    {
        $user = $GLOBALS['BE_USER']->user['uid'];
        $this->mgm->loadUserConf($user);
//		$config = $this->mgm->getUserConf('config');

        // ############# Set the right Content type here

//		$this->currentContentSize = (isset($config['layout']) && in_array($config['layout'], $this->contentSize))?$config['layout']:$this->contentSize[1];

        $this->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Template\DocumentTemplate::class);
        $this->doc->backPath = $GLOBALS['BACK_PATH'];
        $this->doc->docType = 'xhtml_trans';
        $this->doc->form = '';

        // ############# Set the right Theme here
        $this->doc->styleSheetFile2 = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('mydashboard') . 'mod1/css/theme-default.css';

        // required for the widgets

//		$this->doc->loadJavascriptLib('contrib/prototype/prototype.js');
//		$this->doc->getPageRenderer()->loadScriptaculous('effects,dragdrop');
        $pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
        $pageRenderer->loadJquery();
        $this->doc->loadJavascriptLib(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('mydashboard') . 'mod1/functions.js');

        // JavaScript
        $this->doc->JScode = '
			<script language="javascript" type="text/javascript">
			script_ended = 0;
			function jumpToUrl(URL)	{
				document.location = URL;
			}
			</script>
		';
        $this->doc->postCode = '
			<script language="javascript" type="text/javascript">
				script_ended = 1;
				if (top.fsMod) top.fsMod.recentIds["web"] = 0;
			</script>
		';

        $headerSection = 'Hallo <strong>';
        $headerSection .= (trim($GLOBALS['BE_USER']->user['realName']))?htmlspecialchars($GLOBALS['BE_USER']->user['realName']):'<i>No User Name</i>';
        $headerSection .= ' (' . $GLOBALS['BE_USER']->user['username'] . ')</strong> - <i>' . date('F j, Y, g:i a', time()) . '</i>';
        $menu = \TYPO3\CMS\Backend\Utility\BackendUtility::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function'], 'index.php');

        $moduleContent = $this->moduleContent();

        $GLOBALS['LANG']->charSet = 'utf-8';
        $this->content.=$this->doc->startPage($GLOBALS['LANG']->getLL('title'));
        $this->content.=$this->doc->header($GLOBALS['LANG']->getLL('title'));
        $this->content.=$this->doc->spacer(5);
        $this->content.=$this->doc->section('', $this->doc->funcMenu($headerSection, $menu));
        $this->content.=$this->doc->divider(5);

        // Render content:
        $this->content .= $moduleContent;

        // ShortCut
        if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
            $this->content.=$this->doc->spacer(20) . $this->doc->section('', $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']));
        }

        $this->content.=$this->doc->spacer(10);
        $this->content.=$this->doc->endPage();
    }

    /*
     * Build up a select form item
     */
    public function buildSelect($name, $data, $current = false)
    {
        if (empty($data)) {
            return '';
        }
        $out = '<select name="' . $name . '">';
        foreach ($data as $value) {
            $out .= '<option value="' . $value . '"' . (($current && $current == $value)?' selected="selected"':'') . '>' . $GLOBALS['LANG']->getLL($name . '_' . $value) . '</option>';
        }
        return $out . '</select>';
    }

    /*
     * Render the Configuration module
     */
    public function showConfig()
    {

        // Init Config Page & load the User data
        $user = $GLOBALS['BE_USER']->user['uid'];
        $this->mgm->loadUserConf($user);
        $config = $this->mgm->getUserConf('config');

        $content = '';

        // Add Widget
        if (isset($_REQUEST['addWidget'])) {
            if ($this->mgm->addWidget($_REQUEST['addWidget'])) {
                $this->mgm->safeUserConf($user);
                $content .= '<span class="notice">Notice:</span> The Widget with the Key "' . $_REQUEST['addWidget'] . '" is added to the Dashboard.<hr />';
            }
        }

        // Set the Dashboard as Home
        if (isset($_REQUEST['dashHome'])) {
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'be_users', 'uid=' . intval($user), '', '', 1);
            if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
                $content .= 'Error<hr />';
            } else {
                $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                $uc = unserialize($row['uc']);
                if (is_array($uc)) {
                    $uc['startModule'] = 'user_txmydashboardM1';
                    $GLOBALS['BE_USER']->uc['startModule'] = 'user_txmydashboardM1';
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery('be_users', 'uid=' . intval($user), ['uc' => serialize($uc)]);
                    $content .= '<span class="notice">Notice:</span> The Dashboard is set as startpage (current user)!<hr />';
                }
            }
        }

        // Set the Dashboard as Home
        if (isset($_REQUEST['configForm'])) {

            // Set the new Value
            if (isset($_REQUEST['config_rows'])) {
                $config['rows'] = intval($_REQUEST['config_rows']);
            }
            if (isset($_REQUEST['config_layout']) && in_array($_REQUEST['config_layout'], $this->contentSize)) {
                $config['layout'] = $_REQUEST['config_layout'];
            }

            if ($this->mgm->setUserConf('config', $config)) {
                $this->mgm->safeUserConf($user);
            }
            $content .= '<span class="notice">Notice:</span> Config update!<hr />';
        }

        // Check if the Dashboard is the home page
        if (trim($GLOBALS['BE_USER']->uc['startModule']) != 'user_txmydashboardM1') {
            $href = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl(\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('M'),
                        []) . '&SET[function]=2&dashHome=1';
            $content .= '<span class="notice">Notice:</span> The Dashboard is not the startpage (current user)! Click <a style="text-decoration: underline;" href="' . $href . '">here</a> to set the Dashboard as startpage.<hr />';
        }

        $content .= '<h1>' . $GLOBALS['LANG']->getLL('config') . '</h1>';

        $href = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl(\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('M'), []);
        $content .= '
		<form action="' . $href . '" method="post">
		<input type="hidden" name="configForm" value="1" />
		<table>
			<!--tr>
				<td>Dashboard Layout:</td>
				<td>' . $this->buildSelect('config_layout', $this->contentSize, $config['layout']) . '</td>
			</tr-->
			<tr>
				<td>Widget Cols:</td>
				<td>' . $this->buildSelect('config_rows', ['2', '3', '4'], $config['rows']) . '</td>
			</tr>
			<tr>
				<td>Dashboard Theme:</td>
				<td>' . $this->buildSelect('config_theme', ['default'], $config['theme']) . '</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" /></td>
			</tr>
		</table>
		</form>
		';

        $content .= '<h3>Add Widgets (always to the left row)</h3>';
        $widgets = $this->mgm->getAllWidgets();
        $content .= '<table>';
        /**
         * @var string $key
         * @var \tx_mydashboard_template $widget
         */
        foreach ($widgets as $key => $widget) {
            $href = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl(\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('M'), []) . '&SET[function]=2&addWidget=' . $widget->getWidgetKey();
            $content .= '<tr><td>' .
                    $this->mgm->renderIcon($widget) .
                    '</td><td>' . $widget->getTitle() .
                    '</td><td><a style="text-decoration: underline;" href="' . $href . '">add Widget</a></td></tr>';
        }
        $content .= '</table>';

        return $content;
    }
}