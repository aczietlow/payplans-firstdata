<?php 

defined ('_JEXEC' ) or die();

/**
 * Context System
*/
class PayplansAppFirstdata extends PayplansApp {
	// helps Payplans get location of the file
	protected $_location = __FILE__;

	/** Let the system know if your app-instance should be triggered for given event and reference object.
	 $refObject : reference object of any type, check its type on which you want to work. It is generally a type Transaction / Invoice / Payment /Plan / Subscription
	 $eventName : a string which starts from onPayplans

	 === IMP : ==
	 This function ensures your app is triggered for certain plans (as defined by user during app instance creation)
	 Therefore
	 1. Do not override this function until it is essential.
	 2. Better to override function _isApplicable

	 */
	function isApplicable($refObject = null, $eventName='')
	{
		//if you want to decide to trigger app as per event name
		// then return true from here
		if($eventName == 'onPayplansControllerCreation'){
			return true;
		}

		// make sure to let system handle default behaviour
		return parent::isApplicable($refObject,$eventName);
	}


	// if you need additional checks of refObject, then do it here
	function _isApplicable($refObject = null, $eventName='')
	{
		return true;
	}

	/**
	 * onPayplansControllerCreation,this event is triggered just before creation of controller instance.
	 * @param unknown $view
	 * @param unknown $controller
	 * @param unknown $task
	 * @param unknown $format
	 * @return boolean
	 */
	function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		if($view == 'payment' && $task == 'notify')
		{
			$paymentKey = JRequest::getVar('invoice_num', null);
			if($paymentKey){
				$prefix = JString::substr($paymentKey, 0,3);
				if($prefix !== 'PK_')
				{
					//get payment key from order key
					$orderId = XiHelperUtils::getIdFromKey($paymentKey);
					$order = PayplansOrder::getInstance($orderId);
					$paymentId = $order->getParam('payment_id');
					$paymentKey = XiHelperUtils::getKeyFromId($paymentId);
				}
				else
				{
					$paymentKey = JString::substr($paymentKey, 3);
				}
				JRequest::setVar('payment_key', $paymentKey, 'POST');
				return true;
			}

		}
	}
	
	/**
	 * Renders the html when user selects payment gateway to pay and clicks on checkout button and is redirected to a new page.
	 * @param PayplansPayment $payment
	 * @param string $data
	 * @return string
	 */
	function onPayplansPaymentForm(PayplansPayment $payment, $data = null)
	{
		$invoice = $payment->getInvoice(PAYPLANS_INSTANCE_REQUIRE);
		$amount = $invoice->getTotal();
		$this->assign('post_url', XiRoute::_("index.php?option=com_payplans&view=payment&task=complete&payment_key=".$payment->getKey()));
		$this->assign('payment', $payment);
		$this->assign('invoice', $invoice);
		$this->assign('amount', $amount);
		return $this->_render('form');
	}
	
	/**
	 * This event can be use to post the data(as filled in the form) to their respective payment gateways.
	 * @param PayplansPayment $payment
	 * @param unknown $action
	 * @param unknown $data
	 * @param unknown $controller
	 * @return boolean
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
	 * Whenever payment notification arrives, onPayplansPaymentNotify event can be triggered
	 * @param unknown $payment
	 * @param unknown $data
	 * @param unknown $controller
	 * @return string
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
	 * When you want to terminate any payment, then onPayplansPaymentTerminate event is triggered. This event is triggered when the plan is of recurring 
	 * @param PayplansPayment $payment
	 * @param unknown $controller
	 * @return string
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
}
