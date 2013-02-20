<?php 

defined ('_JEXEC' ) or die();

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

		$invoice = $payment->getInvoice(PAYPLANS_INSTANCE_REQUIRE);
		$amount = $invoice->getTotal();
		//firstdata info
		$this->assign('txntype', 'sale'	);
		$this->assign('timezone', GMT	);
		$this->assign('txndatetime', $this->_getDateTime());
		$this->assign('hash', $this->_createHash($this->getAppParam('store_name'), $amount, '840'));
		$this->assign('storename', $this->getAppParam('store_name'));
		$this->assign('mode', $this->getAppParam('pay_mode'));
		$this->assign('chargetotal', $amount);
		$this->assign('currency', '840'); //USD

		//order info
		$this->assign('order_id', 		$invoice->getKey());
		$this->assign('invoice',		$payment->getKey());
		$this->assign('item_name',		$invoice->getTitle());
		$this->assign('item_number',	$invoice->getKey());

		$root = JURI::root();

		$this->assign('responseSuccessURL', $root.'index.php?option=com_payplans&gateway=paypal&view=payment&task=complete&action=success&payment_key='.$payment->getKey());
		$this->assign('responseFailURL', $root.'index.php?option=com_payplans&gateway=paypal&view=payment&task=complete&action=cancel&payment_key='.$payment->getKey());

		$this->assign('post_url', 'https://www.linkpointcentral.com/lpc/servlet/lppay');

		return $this->_render('form');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see PayplansAppPayment::onPayplansPaymentAfter()
	 */
	function onPayplansPaymentAfter(PayplansPayment $payment, $action, $data, $controller)
	{
		if($action == 'cancel'){
			return true;
		}
		if($invoice->isRecurring()){

			//Will create recurring profile and then post the data to the payment gateway.
			$this->_processRecurringRequest($payment, $data);
		}
		else {
			//Will post the the data to the desired payment gateway.
			$this->_processNonRecurringRequest($payment, $data);
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
	protected function _createHash($storeId, $chargetotal, $currency) {
		$sharedSecret = $this->getAppParam('shared_secret');
		$stringToHash = $storeId . $this->_getDateTime() . $chargetotal .
		$currency . $sharedSecret;
		$ascii = bin2hex($stringToHash);
		return sha1($ascii);
	}
}

