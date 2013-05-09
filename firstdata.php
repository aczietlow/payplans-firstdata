<?php

/**
 * @license		GNU/GPL, see LICENSE.php
 * @package		Payplans
 * @contact		aczietlow@gmail.com
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Payplans Firstdata Plugin
 *
 * @author Chris Zietlow
*/
class plgPayplansFirstdata extends XiPlugin
{
	/**
	 * loader function, used to load apps, stylesheets, and javascript
	 * @return boolean
	 */
	public function onPayplansSystemStart()
	{
		//add firstdata app path to app loader
		$appPath = dirname(__FILE__).DS.'firstdata'.DS.'app';
		PayplansHelperApp::addAppsPath($appPath);

		//Adding CSS & JS sheets
		$document = &JFactory::getDocument();
		$css = JURI::base() . 'plugins' . DS . 'payplans' .
				DS .'firstdata' . DS . 'firstdata' . DS . 'app' . DS . 'firstdata' . DS . 'firstdata.css';
		$js = JURI::base() . 'plugins' . DS . 'payplans' .
				DS .'firstdata' . DS . 'firstdata' . DS . 'app' . DS . 'firstdata' . DS . 'firstdata.js';
		$jsVal = JURI::base() . 'plugins' . DS . 'payplans' .
				DS .'firstdata' . DS . 'firstdata' . DS . 'app' . DS . 'firstdata' . DS . 'lib' . DS. 'jquery-validate.min.js';
		
		
		$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');
		$document->addScript('http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/jquery-ui.min.js');
		$document->addCustomTag( '<script type="text/javascript">jQuery.noConflict();</script>' );
		$document->addStyleSheet($css);
		$document->addScript($js);
		$document->addScript($jsVal);

		return true;
	}
	
}