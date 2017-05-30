<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Openpay  
 * @package    OP_Openpayalipay
 * @copyright  Copyright (c) 2013 Openpay (http://www.twv.com.tw)
 */

class OP_Openpayalipay_ProcessingController extends Mage_Core_Controller_Front_Action
{
    protected $_successBlockType = 'openpayalipay/success';
    protected $_failureBlockType = 'openpayalipay/failure';
    protected $_cancelBlockType = 'openpayalipay/cancel';

    protected $_order = NULL;
    protected $_paymentInst = NULL;


    /**
     * Get singleton of Checkout Session Model
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * 連線到openpay
     */
    public function redirectAction()
    {
      
            $session = $this->_getCheckout();
			      $order = Mage::getModel('sales/order');	
            $order->loadByIncrementId($session->getLastRealOrderId());
			
			             
            if (!$order->getId()) {
                Mage::throwException('無訂單可供處理');
            }
            if ($order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                
				    $order->sendNewOrderEmail();
				    $order->setEmailSent(true);
            }

             $this->loadLayout();
            $this->renderLayout(); 
         
    }

    /**
     * openpay返回購物車
     */
    public function responseAction()
    {
           // get request variables
        $request = $this->getRequest()->getPost();
	  
        if (empty($request))
            Mage::throwException('Request doesn\'t contain POST elements.');
			//Check verify

		$p1 = Mage::getModel('OP_Openpayalipay_Model_Main')->getConfigData('p1');	
    $p2 = Mage::getModel('OP_Openpayalipay_Model_Main')->getConfigData('p2');	
    $mid = Mage::getModel('OP_Openpayalipay_Model_Main')->getConfigData('mid');	
	  
  
    $plain_text = implode( '|', array($p1, $request['txid'], $request['amount'], '13', $request['status'], $request['tid'],$p2) );
        	$verify = md5( $plain_text );
    
		if($verify!=$request['verify']){
        	Mage::throwException('交易檢查碼有誤');	
        	}
          
        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($request['txid']);
        if (!$this->_order->getId())
            Mage::throwException('Order not found');
        $this->_paymentInst = $this->_order->getPayment()->getMethodInstance();  
            if ($this->_paymentInst->getConfigData('use_store_currency')) {
            $price      = number_format($this->_order->getGrandTotal(),0,'.','');
            $currency   = $this->_order->getOrderCurrencyCode();
        } else {
            $price      = number_format($this->_order->getBaseGrandTotal(),0,'.','');
            $currency   = $this->_order->getBaseCurrencyCode();
        }
        // 檢查交易金額
        if ($price != $request['amount'])
            Mage::throwException('交易金額不符');
			 //寄送發票
        if ($this->_order->canInvoice()) {					
            $invoice = $this->_order->prepareInvoice();
            $invoice->register()->capture();	
			      $invoice->sendEmail(); 							
            Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();
        }
        if($request['status']=='3'){
        $this->_order->addStatusToHistory($this->_paymentInst->getConfigData('order_status'), '訂單成立，等待付款');
        $this->_order->save();
        }else if($request['status']=='1'){
        $this->_order->addStatusToHistory($this->_paymentInst->getConfigData('order_status1'), '完成付款');
        $this->_order->save();
        }else if($request['status']=='2'){
        $this->_order->addStatusToHistory($this->_paymentInst->getConfigData('order_status2'), '交易失敗');
        $this->_order->save();
        }
        $this->_redirect('checkout/onepage/success');
            
    }

   


    protected function _getPendingPaymentStatus()
    {
        return Mage::helper('openpayalipay')->getPendingPaymentStatus();
    }
}
