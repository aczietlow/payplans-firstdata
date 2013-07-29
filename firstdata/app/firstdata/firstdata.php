<?php

defined('_JEXEC') or die();

//using for debugging only
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
	function isApplicable($refObject = null, $eventName = '') {
		// return true for event onPayplansControllerCreation
		if ($eventName == 'onPayplansControllerCreation') {
			return true;
		}

		return parent::isApplicable($refObject, $eventName);
	}

	/**
	 * (non-PHPdoc)
	 * @see PayplansAppPayment::onPayplansControllerCreation()
	 */
	function onPayplansControllerCreation($view, $controller, $task, $format) {
		if ($view != 'payment' || ($task != 'notify')) {
			return true;
		}

		$paymentKey = JRequest::getVar('invoice', null);
		if (!empty($paymentKey)) {
			JRequest::setVar('payment_key', $paymentKey, 'POST');
			return true;
		}

		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see PayplansAppPayment::onPayplansPaymentForm()
	 */
	function onPayplansPaymentForm(PayplansPayment $payment, $data = null) {
		//typecast data object to array
		if (is_object($data)) {
			$data = (array) $data;
		}
		
		//get the type of firstdata service we are using (connect 1.0, api, etc)
		$service = $this->getAppParam('service');
		switch($service){
			case ('api'):
				//@TODO do we need this?
				break;
			case ('connect1.0'):
				$url = ($this->getAppParam('test') === "1") ? "https://www.staging.linkpointcentral.com/lpc/servlet/lppay" : "https://www.linkpointcentral.com/lpc/servlet/lppay";
				break;
			case ('connect2.0'):
				$url = ($this->getAppParam('test') === "1") ? "https://connect.merchanttest.firstdataglobalgateway.com/IPGConnect/gateway/processing" : "https://www.linkpointcentral.com/lpc/servlet/lppay";
				break;
		}
		
		$invoice = $payment->getInvoice(PAYPLANS_INSTANCE_REQUIRE);
		
		
		$root = JURI::root();
		$timezone = date('T');
		
		$subscription = PayplansApi::getSubscription($invoice->getReferenceObject());
		$user = PayplansApi::getUser($subscription->getBuyer());
		
		//build url
		$protocol = ($_SERVER['HTTPS']) ? 'https://' : 'http://';
		$post_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		//debug ****************************
		// end debug ***********************
		
		//if invoice is recurring
		if ($invoice->isRecurring() != FALSE) {
			//recurring fields for connect 1.0
			if ($service == 'connect1.0') {
				$recurringFields = array(
					'submode' => 'periodic', 
					'periodicity' => 'd1', 
					'startdate' => $this->_getDate(),
					'installments' => '100', //@TODO pulling recurrence from subscription
					'threshold' => '1');
			}
			//recurring fields for connect 2.0
			if ($service == 'connect2.0') {
				$recurringFields = array(
					'submode' => 'periodic', 
					'periodicity' => 'd', 
					'frequency' => '1', 
					'startdate' => $this->_getDate(),
					'installments' => '100', //@TODO pulling recurrence from subscription
					'threshold' => '1');
			}
			
			$count = 0;
			foreach ($recurringFields as $key => $value) {
				$postFields[$key] = $value;
				$count++;
			}
		}
		
		$this->assign('identifier', TRUE);
		
		//if our form data has been posted back to controller
		if ($_POST['identifier'] == TRUE && empty($_POST['status'])) {
			
			//replace invalid timezone strings
			if ($timezone == 'EDT') {
				$timezone == 'EST';
			}
			
			$amount = $invoice->getTotal();
			$time = $this->_getDateTime();
			$hash = $this->_createHash($this->getAppParam('store_name'), $amount, $time);
			
			$postFields = array(
					//firstdata fields
					'txntype' => 'sale',
					'timezone' => 'EST', //@TODO use timezone variable
					'txndatetime' => $time,
					'hash' => $hash,
					'storename' => $this->getAppParam('store_name'),
					'mode' => $this->getAppParam('pay_mode'),
					'chargetotal' => $amount,
					'subtotal' => $amount,
					'trxOrigin' => "ECI",
					
					//nbha specific fields
					'sex' => $_POST['sex'],
					'dob' => $_POST['dob'],
					'ssn' => $_POST['ssn'],
					'district' => $_POST['compDistrict'],
					'compState' => $_POST['compstate'],
			
					//payplans info
					'order_id' => $invoice->getKey(),
					'invoice' => $payment->getKey(),
					'item_name' => $invoice->getTitle(),
					'item_number' => $invoice->getKey(),
						
					//return info
					'responseSuccessURL' => $root . 'index.php?option=com_payplans&gateway=firstdata&view=payment&task=complete&action=success&payment_key=' . $payment->getKey(),
					'responseFailURL' => $root . 'index.php?option=com_payplans&gateway=firstdata&view=payment&task=complete&action=cancel&payment_key=' . $payment->getKey(),
			);
			
			
			
			//Capture all the data from the post fields and addes it to the array for the curl request
			foreach ($_POST as $key => $value) {
				if ($key != 'payplans_payment_btn') {
					if ($key != 'identifier'){
	  	  	  $postFields[$key] = $value;
	  	  	  if ($key != 'cardnumber') {
	  	  	  	if ($key != 'expmonth') {
	  	  	  		if ($key != 'expyear') {
			      			$subscription->setParam($key,$value);
			      			$user->setParam($key, $value);
	  	  	  		}
	  	  	  	}
	  	  	  }
					}
				}
			}
			
			//adds user address information to payplans user object
			$user->setAddress($_POST['baddr1']);
			$user->setState($_POST['bstate']);
			$user->setCity($_POST['bcity']);
			$user->setCountry($_POST['bcountry']);
			$user->setZipcode($_POST['bzip']);
			$user->save();
			
			//save data to subscription object
			$subscription->save();
			
			//create copy of array for logging purposes
			$formData = $postFields;
			
			//sanitize array for http query
			$postFields = http_build_query($postFields);
			
			//@TODO build real referrer page
			$referer = $root . 'firstdata/referer.php';
			
			$this->assign('referer', $referer);
			$this->assign('url', $url);
			$this->assign('postFields', $postFields);
			
			//log all post data for tracking purposes.
			$this->_trackPostFields($formData);
			
			return $this->_render('curl_form');
		}

		return $this->_render('form');
	}

	/**
	 * (non-PHPdoc)
	 * @see PayplansAppPayment::onPayplansPaymentAfter()
	 */ 
	function onPayplansPaymentAfter(PayplansPayment $payment, $action, $data, $controller) {
		if ($action == 'cancel') {
			return true;
		}
		
		$error = array();
		
		$invoice = $payment->getInvoice(PAYPLANS_INSTANCE_REQUIRE);
		
		foreach ($_POST as $key => $value) {
			$responseFields[$key] = $value;
		}

		$invoice = $payment->getInvoice(PAYPLANS_INSTANCE_REQUIRE);
		$transaction = PayplansTransaction::getInstance();
		

		//set up subscription profile for recurring payments
		if (isset($data['x_subscription_id']) && $data['x_subscription_id']) {
			$transaction->set('user_id', $payment->getBuyer())
						->set('gateway_subscr_id', isset($data['x_subscription_id']) ? $data['x_subscription_id'] : 0)
						->set('gateway_parent_txn', isset($data['parent_txn_id']) ? $data['parent_txn_id'] : 0);
		}

		$transaction->set('user_id', $payment->getBuyer())
					->set('invoice_id', $invoice->getId())
					->set('payment_id', $payment->getId())
					->set('gateway_txn_id', isset($data['x_trans_id']) ? $data['x_trans_id'] : 0)
					->set('params', PayplansHelperParam::arrayToIni($data));

		$status = rtrim($responseFields['status']);

		switch ($status) {
		case 'APPROVED':
			$transaction->set('amount', $responseFields['chargetotal'])->set('message', 'Payment processed correctly!');
			break;
		case 'DECLINED':
			$errors['response_status'] = $status;
			$errors['fail_reason'] = $responseFields['fail_reason'];
			$transaction->set('message', $errors['fail_reason'])->set('amount', 0);
			break;
		case 'FRAUD':
			$errors['response_status'] = $status;
			$errors['fail_reason'] = $responseFields['r-error'];
			$transaction->set('message', $errors['fail_reason'])->set('amount', 0);
			break;
		default:
			break;
		}

		$transaction->save();

		return count($errors) ? implode("\n", $errors) : ' No Errors';
	}
	
	public function onPayplansPaymentTerminate(PayplansPayment $payment, $controller)
	{
		$transactions = $payment->getTransactions();
		foreach($transactions as $value){
			$subscriptionId = $value->get('gateway_subscr_id', 0);
			if(!empty($subscriptionId)){
				break; //Not sure why we are checking for this 
			}
		}
	
		$invoice = $payment->getInvoice(PAYPLANS_INSTANCE_REQUIRE);
		$transaction = PayplansTransaction::getInstance();
		$transaction->set('user_id', $payment->getBuyer())
		->set('invoice_id', $invoice->getId())
		->set('payment_id', $payment->getId())
		->set('gateway_txn_id', isset($data['x_trans_id']) ? $data['x_trans_id'] : 0)
		->set('gateway_subscr_id', $subscriptionId)
		->set('gateway_parent_txn', isset($data['parent_txn_id']) ? $data['parent_txn_id'] : 0);
	
		$transaction->set('message', 'COM_PAYPLANS_PAYMENT_FOR_CANCEL_ORDER')->save();
		
		$params = new XiParameter();
		$params->set('pending_recur_count', 0);
		$payment->set('gateway_params', $params)
								->save();
		$user = PayplansUser::getInstance($transaction->getBuyer());
		$realName = $user->getRealName();
		
		$subscription = PayplansApi::getSubscription($invoice->getReferenceObject());
		$subscriptionId = $subscription->getId();
		$message = "$realName has requested to cancel their subscription." .
					"Use the link below to view their subscription information." .
					"http://webdev01.devmags.com/~nbhacom/administrator/index.php?option=com_payplans&view=subscription&task=edit&id=$subscriptionId";
		$subject = "CANCEL ORDER FOR NBHA";
		$headers =  $headers = 'From: chris.zietlow@morris.com' . "\r\n" .
				    'Reply-To: chris.zietlow@morris.com' . "\r\n" .
				    'X-Mailer: PHP/' . phpversion();
		
		//comma seperate list of email addresses
		$emailAddresses = $this->getAppParam('emails');
		$emailAddresses = explode(',', $emailAddresses);
		
		foreach($emailAddresses as $value) {
			mail($value, $subject, $message, $headers);
		}
				
		
		
		return $this->_render('cancel_success');
	}
	
	/**
	 * Hook that is triggered when subscription changes to expired status
	 * 
	 * @param PayplansPayment $payment
	 * @param int $invoiceCount
	 */
	public function processPayment(PayplansPayment $payment, $invoiceCount) {
		
		$invoice = $payment->getInvoice(PAYPLANS_INSTANCE_REQUIRE);
		$invoice_count = $invoiceCount + 1;
		$amount = $invoice->getTotal();
		
		//Check that invoice is recurring
		if($invoice->isRecurring())
		{	
			//get all transaction against the invoice
			$transactions = $invoice->getTransactions();
	
			if (!empty($transactions)) {
				$transaction = end($transactions); //original transaction
				$transParams = $this->_getTransactionParams($transaction, $payment->getKey());
				$this->_processRecurringPayment($payment, $invoice, $amount, $transParams);
			}
		}
	}
	
	/**
	 * 
	 * @param PayplansPayment $payment
	 * @param PayplansInvoice $invoice
	 * @param int $amount
	 * @param Array $transParams
	 */
	protected function _processRecurringPayment($payment, $invoice, $amount, $transParams) {
		//$recurrence_count 	= $recurrence_count - 1;
		$transaction = PayplansTransaction::getInstance();
		
		$transaction->set('user_id', $invoice->getBuyer())
		->set('invoice_id', $invoice->getId())
		->set('payment_id', $payment->getId())
		->set('amount', $amount)
		->set('gateway_txn_id', isset($record['param6']) ? $record['param6'] : 0)
		->set('message', 'SUCCESS');
		
		foreach ($transParams as $key => $value) {
			$transaction->setParam($key, $value);
		}
		$transaction->save();
	}
	
	/**
	 * Process the response sent from fristdata.
	 * 
	 * @param array $responseFields
	 * An array of the response fields from firstdata
	 */
	protected function _processResponse($responseFields) {

		//remove trailng whitespace from firstdata response
		$status = rtrim($responseFields['status']);

		switch ($status) {
		case "APPROVED":
			break;

		case ($status == "DECLINED"):
			break;

		case "FRAUD":
			break;

		default:
			break;
		}

	}

	/**
	 * Gets the current date and converts it to the correct date string for First Data
	 * 
	 * @return string $dateTime
	 * current time in the correct date string
	 */
	protected function _getDateTime() {
		$format = "Y:m:d-H:i:s";
		$dateTime = date($format, $timestamp = time());
		return $dateTime;
	}

	/**
	 * Gets the current data and converts it to the correct date string for First Data recurring fields
	 * 
	 * @return string
	 */
	protected function _getDate() {
		$format = "Ymd";
		$date = date($format, $timestampe = time());
		return $date;
	}

	/**
	 * Creates secure hash for First Data connect 2.0 secured hash field
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
	protected function _createHash($storename, $chargetotal, $time) {
		$sharedSecret = $this->getAppParam('shared_secret');
		//$stringToHash = $storeId . $txndatetime . $chargetotal . $currency . $sharedSecret;
		//$ascii = bin2hex($stringToHash);
		//return sha1($ascii);

		$str = $storename . $time . $chargetotal . $sharedSecret;
		$hex_str = '';
		for ($i = 0; $i < strlen($str); $i++) {
			$hex_str .= dechex(ord($str[$i]));
		}
		return hash('sha256', $hex_str);
	}
	
	
	/**
	 * Gets subscription details from posted form data and converts them into an array
	 * 
	 * @return array:subscriptionDetails
	 */
	protected function _getSubscriptionDetails() {
		$subscriptionDetails = array(
				'bname' => $_POST['name'],
				'baddr1' => $_POST['address'],
				'bcity' => $_POST['city'],
				'bstate' => $_POST['state'],
				'bzip' => $_POST['zip'],
				'phone' => $_POST['phone'],
		);
		return $subscriptionDetails;
	}
	
	/**
	 * Gets params from current tranaction
	 * 
	 * @param PayplansTransaction $transaction
	 * @param PayplansPayment->key $paymentKey
	 * @return array:params
	 */
	protected function _getTransactionParams($transaction, $paymentKey) {
		$params = $transaction->getParams()->toArray();
		$params['txndatetime'] = $this->_getDateTime();
		$params['payment_key'] = $paymentKey;
		return $params;
	}
	
	/**
	 * Prepares all form data to be logged
	 * @param array $formData
	 */
	protected function _trackPostFields($formData) {
		//Unset sensative information.
		if(isset($formData['cardnumber'])) {
			unset($formData['cardnumber']);
		}
		if(isset($formData['expmonth'])) {
			unset($formData['expmonth']);
		}
		if(isset($formData['expyear'])) {
			unset($formData['expyear']);
		}
		
		$this->_trackPostFieldsCsv($formData);
		//$this->_trackPostFieldsDb($formData);
	}
	
	/**
	 * Writes all formdata to csv file
	 * @param array $formData
	 */
	protected function _trackPostFieldsCsv($formData) {
		$logFile = dirname(getcwd());
		$logFile .= '/public_html/plugins/payplans/firstdata/firstdataAttempts.csv';
		
		$fh = fopen($logFile, 'a+');
		
		//If file is empty, write csv headers from formData array keys.
		if (filesize($logFile) == 0) {
			$headers = array();
			foreach($formData as $key => $value) {
				$headers[] = $key;
			}
			fputcsv($fh, $headers);
		}
		
		fputcsv($fh, $formData);
		fclose($fh);
	}
	
	/**
	 * Writes all formdata to DB table
	 * @param array $formData
	 */
	protected function _trackPostFieldsDb($formData) {
		//check if table already exists
		$db = JFactory::getDBO();
		$query = "SHOW tables;";
		$db->setQuery($query);
		$tables = $db->loadAssocList();
		
		$check = 'dne';
		foreach($tables as $table) {
			foreach($table as $value) {
				$dumpTables[] = $value;
				if($value == $db->getPrefix(). 'test') {
					$check = 'exists';
					krumo($db->getPrefix(). "test table exists");
				}
			}
		}
		krumo($dumpTables);
		//If table does not exist, create it.
		if($check == 'dne') {
			//Get name to be used for column names in table.
			$column_names = array();
			foreach($formData as $key => $value) {
				$column_names[] = $key;
			}
			krumo('created table');
			$table = $db->getPrefix() . 'test';
			$db = JFactory::getDBO();
			$query = "CREATE TABLE $table ;";
			$db->setQuery($query);
			$db->query();
		}
	}
}

