<?php 

defined ('_JEXEC' ) or die();

include_once 'Krumo/class.krumo.php';

/**
 * Context System
*/
class PayplansAppFirstdata extends PayplansAppPayment {
	// helps Payplans get location of the file
	protected $_location = __FILE__;


	/**
	 * (non-PHPdoc)
	 * @see PayplansAppPayment::isApplicable()
	 */
	function isApplicable($refObject = null, $eventName='')
	{
		// return true for event onPayplansControllerCreation
		if($eventName == 'onPayplansControllerCreation'){
			return true;
		}

		return parent::isApplicable($refObject, $eventName);
	}

	/**
	 * (non-PHPdoc)
	 * @see PayplansAppPayment::onPayplansControllerCreation()
	 */
	function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		if($view != 'payment' || ($task != 'notify') ){
			return true;
		}
		
		$paymentKey = JRequest::getVar('invoice', null);
		if(!empty($paymentKey)){
			JRequest::setVar('payment_key', $paymentKey, 'POST');
			return true;
		}

		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see PayplansAppPayment::onPayplansPaymentForm()
	 */
	function onPayplansPaymentForm(PayplansPayment $payment, $data = null)
	{
		if(is_object($data)){
			$data = (array)$data;
		}
		
		krumo($_POST);
		
		if($this->getAppParam('test') == true) {
// 			$url = "https://www.staging.linkpointcentral.com/lpc/servlet/lppay"; // for test
			$url = "https://connect.merchanttest.firstdataglobalgateway.com/IPGConnect/gateway/processing"; // for test
		} else{
			//$url = "https://www.linkpointcentral.com/lpc/servlet/lppay"; // for live
			$url = "https://connect.merchanttest.firstdataglobalgateway.com/IPGConnect/gateway/processing"; // for test
		}

		$invoice = $payment->getInvoice(PAYPLANS_INSTANCE_REQUIRE);
    $amount = $invoice->getTotal();
    $time = $this->_getDateTime();
    $hash = $this->_createHash($this->getAppParam('store_name'), $amount, $time);
    $root = JURI::root();
    $timezone = date('T');
    
    //build url
    if($_SERVER['HTTPS'] == 'on'){
    	$post_url = 'https://';
    }
    else {
    	$post_url = 'http://';
    }
    
    $post_url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $this->assign('post_url', $post_url);

    //replace invalid timezone strings
    if ($timezone == 'EDT') {
    	$timezone == 'EST';
    }
//     if ($_REQUEST['identifier'] == TRUE) {
			$this->assign('identifier', TRUE);    	
			$postFields = array(
				"txntype" => "sale",
				"timezone" => 'EST',
				"txndatetime" => $time,
				"hash" => $hash,
				"storename" => $this->getAppParam('store_name'),
				"mode" => $this->getAppParam('pay_mode'),
				"chargetotal" => $amount,
				"subtotal" => $amount,
				"paymentMethod" => $_POST['paymentMethod'],
				"trxOrigin" => "ECI",
	
				//payment information
				"cardnumber" => $_POST['cardnumber'],
				'expmonth' => $_POST['expmonth'],
				'expyear' => $_POST['expyear'],
				
				//payplans information
				'order_id' => $invoice->getKey(),
				'invoice' => $payment->getKey(), //*
				'item_name' => $invoice->getTitle(),
				'item_number' => $invoice->getKey(),
					
				//return information
// 				'responseSuccessURL' => $root.'index.php?option=com_payplans&gateway=firstdata&view=payment&task=complete&action=success&payment_key=' . $payment->getKey(),
				'responseSuccessURL' => 'https://google.com',
								
// 				'responseFailURL' => $root.'index.php?option=com_payplans&gateway=firstdata&view=payment&task=complete&action=cancel&payment_key='.$payment->getKey(),
			);
			
			echo $postFields['responseSuccessURL'];
			
			$this->assign('postFields', $postFields);
			
			//@TODO build real referrer url
			$referer = 'http://webdev01.devmags.com/~nbhacom/zietlow_test/curl%20test/referer.php';

			if ($_REQUEST['identifier'] == TRUE) {
					
			$ch = curl_init();
			curl_setopt_array($ch, array(
	  		CURLOPT_URL => $url,
	  		CURLOPT_FOLLOWLOCATION => TRUE,
	//   		CURLOPT_RETURNTRANSFER => TRUE, //debug
	//   		CURLOPT_HEADER => TRUE,//debug 
				CURLOPT_POST => TRUE,
				CURLOPT_POSTFIELDS => $postFields,
			 	CURLOPT_REFERER => $referer,
				CURLOPT_SSL_VERIFYPEER => FALSE,
				CURLOPT_FOLLOWLOCATION => TRUE,
				)
			);
	    
			$this->assign('ch', $ch);
		
    }

		return $this->_render('form');


	}
	
	/**
	 * (non-PHPdoc)
	 * @see PayplansAppPayment::onPayplansPaymentAfter()
	*/	 
	function onPayplansPaymentAfter(PayplansPayment $payment, $action, $data, $controller)
	{
		echo "THIS IS SPARTA";
		if($action == 'cancel'){
			return true;
		}
		if($invoice->isRecurring()){

			//Will create recurring profile and then post the data to the payment gateway.
			//$this->_processRecurringRequest($payment, $data);
		}
		else {
			//Will post the the data to the desired payment gateway.
			//$this->_processNonRecurringRequest($payment, $data);
		}
		$payment->save();

		//calling of parent event is required to check whether
		// there is any error during the above process.
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * (non-PHPdoc)
	 * @see PayplansAppPayment::onPayplansPaymentNotify()
	 */
	function onPayplansPaymentNotify($payment, $data, $controller)
	{
		$invoice = $payment->getInvoice(PAYPLANS_INSTANCE_REQUIRE);
			
		// if its a recurring subscription
		if(isset($data['x_subscription_id']) && $data['x_subscription_id'] ){

			// get the transaction instace of lib
			$transaction = PayplansTransaction::getInstance();
			$transaction->set('user_id', $payment->getBuyer())
			->set('invoice_id', $invoice->getId())
			->set('payment_id', $payment->getId())
			->set('gateway_txn_id', isset($data['x_trans_id']) ? $data['x_trans_id'] : 0)
			->set('gateway_subscr_id', isset($data['x_subscription_id']) ? $data['x_subscription_id'] : 0)
			->set('gateway_parent_txn', isset($data['parent_txn_id']) ? $data['parent_txn_id'] : 0)
			->set('params', PayplansHelperParam::arrayToIni($data));

			$errors = $this->_processNotification($transaction, $data, $payment);
			$transaction->save();
			return count($errors) ? implode("\n", $errors) : ' No Errors';
		}
			
	}
	
	/**
	 * (non-PHPdoc)
	 * @see PayplansAppPayment::onPayplansPaymentTerminate()
	 */
	public function onPayplansPaymentTerminate(PayplansPayment $payment, $controller)
	{
		$transactions = $payment->getTransactions();
		foreach($transactions as $transaction){
			$subscriptionId = $transaction->get('gateway_subscr_id', 0);
			if(!empty($subscriptionId)){
				break;
			}
		}
		$arbInstance->setRefId($payment->getKey());
		$response = $arbInstance->cancelSubscription($subscriptionId);

		$invoice = $payment->getInvoice(PAYPLANS_INSTANCE_REQUIRE);
		$txn = PayplansTransaction::getInstance();
		$txn->set('user_id', $payment->getBuyer())
		->set('invoice_id', $invoice->getId())
		->set('payment_id', $payment->getId())
		->set('gateway_txn_id', isset($data['x_trans_id']) ? $data['x_trans_id'] : 0)
		->set('gateway_subscr_id', $subscriptionId)
		->set('gateway_parent_txn', isset($data['parent_txn_id']) ? $data['parent_txn_id'] : 0);

		$txn->set('message', 'COM_PAYPLANS_PAYMENT_FOR_CANCEL_ORDER')->save();
		return $this->_render('cancel_success');
	}
	
	/**
	 * Gets the current date and converts it to the correct date string for First Data
	 * @return string $dateTime
	 * current time in the correct date string
	 */
	protected function _getDateTime() {
		$format = "Y:m:d-H:i:s";
		$dateTime = date($format, $timestamp = time());
		return $dateTime;
	}

	/**
	 * 
	 * @param int $storeId
	 * The store name given to you by first data. Set during app install
	 * @param float $chargetotal
	 * The amount of the invoice
	 * @param int $currency
	 * The currency code. (840 => US, 987 => EU)
	 * @return string $hash
	 * secured hash for authenicating with First Data Connect
	 */
	protected function _createHash($storename, $chargetotal, $time ) {
		$sharedSecret = $this->getAppParam('shared_secret');
		//$stringToHash = $storeId . $txndatetime . $chargetotal . $currency . $sharedSecret;
		//$ascii = bin2hex($stringToHash);
		//return sha1($ascii);
		
		$str = $storename . $time . $chargetotal . $sharedSecret;
		for ($i = 0; $i < strlen($str); $i++){
 	 		$hex_str.=dechex(ord($str[$i]));
	 	}
	 	return hash('sha256', $hex_str);
	}

}

