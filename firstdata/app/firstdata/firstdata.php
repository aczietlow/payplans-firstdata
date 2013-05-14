<?php

defined('_JEXEC') or die();

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
		$service = $this->getAppParam('service');
		switch($service){
			case ('api'):
				//do we need this?
				break;
			case ('connect1.0'):
				$url = ($this->getAppParam('test')) ? "https://www.staging.linkpointcentral.com/lpc/servlet/lppay" : "https://www.linkpointcentral.com/lpc/servlet/lppay";
				break;
			case ('connect2.0'):
				$url = ($this->getAppParam('test')) ? "https://connect.merchanttest.firstdataglobalgateway.com/IPGConnect/gateway/processing" : "https://www.linkpointcentral.com/lpc/servlet/lppay";
				break;
			default:
				//localhost testing address for testing posting data, and curl requests.
				break;
		}
		
		
		$invoice = $payment->getInvoice(PAYPLANS_INSTANCE_REQUIRE);
		$amount = $invoice->getTotal();
		$time = $this->_getDateTime();
		$hash = $this->_createHash($this->getAppParam('store_name'), $amount, $time);
		$root = JURI::root();
		$timezone = date('T');
		
		$subscription = PayplansApi::getSubscription($invoice->getReferenceObject());
		$subscription2 = PayplansApi::getSubscription(203);
		
		
		$params = $subscription2->getParams();
		$results = $params->toArray();
		krumo($results);
		
		//debug 
		$methods = get_class_methods($this);
		krumo($methods);
		$subData = (array) $subscription;
		krumo($subData);
		krumo($subscription->isRecurring());
		krumo($invoice->isRecurring());
		//build url
		$protocol = ($_SERVER['HTTPS']) ? 'https://' : 'https://';
		$post_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		//replace invalid timezone strings
		if ($timezone == 'EDT') {
			$timezone == 'EST';
		}
		
		//TODO: do these to be initialized before the form post back?
		$postFields = array(
				//personal user data
				'bname' => $_POST['name'],
				'baddr1' => $_POST['address'],
				'city' => $_POST['city'],
				'state' => $_POST['state'],
				'zip' => $_POST['zip'],
				'phone' => $_POST['phone'],
				
				//nbha info
				'sex' => $_POST['sex'],
				'dob' => $_POST['dob'],
				'ssn' => $_POST['ssn'],
				'district' => $_POST['compDistrict'],
				'compState' => $_POST['compstate'],
				
				'txntype' => 'sale', 
				'timezone' => 'EST', //@TODO use timezone variable 
				'txndatetime' => $time, 
				'hash' => $hash, 
				'storename' => $this->getAppParam('store_name'), 
		    
		    	'mode' => $this->getAppParam('pay_mode'), 
				'chargetotal' => $amount, 
				'subtotal' => $amount, 
				'trxOrigin' => "ECI",
				//'cctpye' => 'v', //connect 1.0
				'paymentMethod' => $_POST['paymentMethod'],
				
				//payment information
				'cardnumber' => $_POST['cardnumber'], 
				'expmonth' => $_POST['expmonth'], 
				'expyear' => $_POST['expyear'],
				
				//user info
				'bname' => $_POST['bname'],
				'baddr1' => $_POST['baddr1'],
				'bcity' => $_POST['bcity'],
				'bstate' => $_POST['bstate'],
				'bzip' => $_POST['bzip'],
				'phone' => $_POST['phone'],
				
				//payplans information
				'order_id' => $invoice->getKey(), 
				'invoice' => $payment->getKey(),
				'item_name' => $invoice->getTitle(), 
				'item_number' => $invoice->getKey(),
				 
				//return information
				'responseSuccessURL' => $root . 'index.php?option=com_payplans&gateway=firstdata&view=payment&task=complete&action=success&payment_key=' . $payment->getKey(),
				'responseFailURL' => $root . 'index.php?option=com_payplans&gateway=firstdata&view=payment&task=complete&action=cancel&payment_key=' . $payment->getKey(),	
		);
		
// 		if($this->getAppParam('service') == 'connect2.0') {
// 			$postFields['paymentMethod'] = $_POST['paymentMethod'];
// 		}
		
		$postFields = http_build_query($postFields);
		if ($invoice->isRecurring() != FALSE) {
			
			if ($service == 'connect1.0') {
				$recurringFields = array(
					'submode' => 'periodic', 
					'periodicity' => 'd1', 
					'startdate' => $this->_getDate(),
					'installments' => '10', //@TODO pulling recurrence from subscription
					'threshold' => '1',);
			}
			if ($service == 'connect2.0') {
				$recurringFields = array(
					'submode' => 'periodic', 
					'periodicity' => 'd', 
					'frequency' => '1', 
					'startdate' => $this->_getDate(),
					'installments' => '10', //@TODO pulling recurrence from subscription
					'threshold' => '1',);
			}
			
			$count = 0;
			foreach ($recurringFields as $key => $value) {
				$postFields[$key] = $value;
				$count++;
			}
		}
    
		$this->assign('identifier', TRUE);
		if ($_POST['identifier'] == TRUE && empty($_POST['status'])) {
			
			$subscriptionDetails = $this->_getSubscriptionDetails();
			foreach ($subscriptionDetails as $key => $value) {
				$subscription->setParam($key,$value);
				$postFields[$key] = $value;
			}
			
			$subscription->save();
			
			//@TODO build real referrer url
			$referer = $root . 'firstdata/referer.php';
			$ch = curl_init();
			curl_setopt_array($ch, array(
			  CURLOPT_URL => $url,
			  CURLOPT_FOLLOWLOCATION => TRUE, 
			  CURLOPT_POST => TRUE,
			  CURLOPT_POSTFIELDS => $postFields, 
			  CURLOPT_REFERER => $referer, 
			  CURLOPT_SSL_VERIFYPEER => FALSE,));

			$this->assign('ch', $ch);

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
		if ($invoice->isRecurring()) {
// 			$this->_processRecurringRequest($payment, $data);
		}
		
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
	 * @return string $dateTime
	 * current time in the correct date string
	 */
	protected function _getDateTime() {
		$format = "Y:m:d-H:i:s";
		$dateTime = date($format, $timestamp = time());
		return $dateTime;
	}

	protected function _getDate() {
		$format = "Ymd";
		$date = date($format, $timestampe = time());
		return $date;
	}

	/**
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
		for ($i = 0; $i < strlen($str); $i++) {
			$hex_str .= dechex(ord($str[$i]));
		}
		return hash('sha256', $hex_str);
	}
	
	protected function _getSubscriptionDetails() {
		$subscriptionDetails = array(
				'bname' => $_POST['name'],
				'baddr1' => $_POST['address'],
				'bcity' => $_POST['bcity'],
				'bstate' => $_POST['state'],
				'bzip' => $_POST['zip'],
				'phone' => $_POST['phone'],
				'sex' => $_POST['sex'],
				'dob' => $_POST['dob'],
				'ssn' => $_POST['ssn'], //encrypt this!!!!!!!
				'compDistrict' => $_POST['compDistrict'],
				'compState' => $_POST['compState'],
		);
		return $subscriptionDetails;
	}
}
