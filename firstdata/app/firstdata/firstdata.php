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
		if (is_object($data)) {
			$data = (array) $data;
		}

		if ($this->getAppParam('test') == true) {
			$url = "https://connect.merchanttest.firstdataglobalgateway.com/IPGConnect/gateway/processing"; // for test
		} else {
			$url = "https://www.linkpointcentral.com/lpc/servlet/lppay"; // for live
		}

		$invoice = $payment->getInvoice(PAYPLANS_INSTANCE_REQUIRE);
		$amount = $invoice->getTotal();
		$time = $this->_getDateTime();
		$hash = $this->_createHash($this->getAppParam('store_name'), $amount, $time);
		$root = JURI::root();
		$timezone = date('T');

		//build url
		if ($_SERVER['HTTPS'] == 'on') {
			$post_url = 'https://';
		} else {
			$post_url = 'http://';
		}
		$post_url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		//replace invalid timezone strings
		if ($timezone == 'EDT') {
			$timezone == 'EST';
		}

		$postFields = array("txntype" => "sale", "timezone" => 'EST', "txndatetime" => $time, "hash" => $hash, "storename" => $this->getAppParam('store_name'), "mode" => $this->getAppParam('pay_mode'), "chargetotal" => $amount, "subtotal" => $amount, "paymentMethod" => $_POST['paymentMethod'],
				"trxOrigin" => "ECI", 
				//payment information
				"cardnumber" => $_POST['cardnumber'], 'expmonth' => $_POST['expmonth'], 'expyear' => $_POST['expyear'], 
				//payplans information
				'order_id' => $invoice->getKey(), 'invoice' => $payment->getKey(), //*
				'item_name' => $invoice->getTitle(), 'item_number' => $invoice->getKey(), 
				//return information
				'responseSuccessURL' => $root . 'index.php?option=com_payplans&gateway=firstdata&view=payment&task=complete&action=success&payment_key=' . $payment->getKey(),
				'responseFailURL' => $root . 'index.php?option=com_payplans&gateway=firstdata&view=payment&task=complete&action=cancel&payment_key=' . $payment->getKey(),);

		if ($invoice->isRecurring() != FALSE) {
			$recurringFields = array('submode' => 'periodic', 'periodicity' => 'd', 'frequency' => '1', 'startdate' => $this->_getDate(), 'installments' => '10', 'threshold' => '1',);
			$count = 0;
			foreach ($recurringFields as $key => $value) {
				$postFields[$key] = $value;
				$count++;
			}
		}
		krumo($this->_getDate());
		krumo($postFields);

		$this->assign('postFields', $postFields);
		$this->assign('identifier', TRUE);

		if ($_POST['identifier'] == TRUE && empty($_POST['status'])) {
			//@TODO build real referrer url
			// 			$referer = 'https://webdev01.devmags.com/~nbhacom/zietlow_test/curl%20test/referer.php';
			$referer = $root . 'firstdata/referer.php';

			$ch = curl_init();
			curl_setopt_array($ch, array(CURLOPT_URL => $url, CURLOPT_FOLLOWLOCATION => TRUE, CURLOPT_POST => TRUE, CURLOPT_POSTFIELDS => $postFields, CURLOPT_REFERER => $referer, CURLOPT_SSL_VERIFYPEER => FALSE,));

			$this->assign('ch', $ch);

			return $this->_render('curl_form');
		}

		if ($_POST['status'] && isset($_POST['status'])) {
			$responseFields = array();
			foreach ($_POST as $key => $value) {
				$responseFields[$key] = $value;
			}
			//     	$this->_processResponse($responseFields); //in progress
			$this->assign('responseFields', $responseFields);

			return $this->_render('response_form');
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

		foreach ($_POST as $key => $value) {
			$responseFields[$key] = $value;
		}

		$invoice = $payment->getInvoice(PAYPLANS_INSTANCE_REQUIRE);
		$transaction = PayplansTransaction::getInstance();
		$error = array();

		//set up subscription profile for recurring payments
		if (isset($data['x_subscription_id']) && $data['x_subscription_id']) {
			$transaction->set('user_id', $payment->getBuyer())->set('gateway_subscr_id', isset($data['x_subscription_id']) ? $data['x_subscription_id'] : 0)->set('gateway_parent_txn', isset($data['parent_txn_id']) ? $data['parent_txn_id'] : 0);
		}

		$transaction->set('user_id', $payment->getBuyer())->set('invoice_id', $invoice->getId())->set('payment_id', $payment->getId())->set('gateway_txn_id', isset($data['x_trans_id']) ? $data['x_trans_id'] : 0)->set('params', PayplansHelperParam::arrayToIni($data));

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
	 * @TODO update comments to PHP doc formatting
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
}
