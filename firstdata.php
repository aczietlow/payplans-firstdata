<?php

/**
 * @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * @package		Payplans
 * @subpackage	Discount
 * @contact		shyam@joomlaxi.com
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Payplans Bundle Plugin
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
		//add bundle app path to app loader
		$appPath = dirname(__FILE__).DS.'firstdata'.DS.'app';
		PayplansHelperApp::addAppsPath($appPath);


		//Add style sheet for input fields
		$document = &JFactory::getDocument();
		$css = JURI::base() . 'plugins' . DS . 'payplans' .
				DS .'firstdata' . DS . 'firstdata' . DS . 'app' . DS . 'firstdata' . DS . 'firstdata.css';
		$document->addStyleSheet($css);
		$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');
		$document->addScript('http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/jquery-ui.min.js');

			
		return true;
	}

	
	/**
	 * Hooks invoice before view is rendered.
	 *
	 * @param XiView $view
	 * @param unknown $task
	 * @return multitype:string
	 */
	public function onPayplansViewBeforeRender(XiView $view, $task)
	{
		
		
	}
	
}
