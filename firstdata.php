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
			
		return true;
	}
	
}