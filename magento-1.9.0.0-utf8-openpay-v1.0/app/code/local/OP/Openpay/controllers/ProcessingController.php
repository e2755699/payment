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
 * @package    OP_Openpayvacc
 * @copyright  Copyright (c) 2013 Openpay (http://www.twv.com.tw)
 */

class OP_Openpay_ProcessingController extends Mage_Core_Controller_Front_Action
{


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
     * openpay返回購物車
     */
    public function responseAction()
    {
           // get request variables
        $request = $this->getRequest()->getPost();
	  
        if (empty($request))
            Mage::throwException('Request doesn\'t contain POST elements.');
			//Check verify

		 switch( $request['pay_type'] ):
    case 1:   $pay_type = 'openpayccard'.$request[iid]; break;
    case 2:   $pay_type ='openpayvacc'; break;
    case 4:   $pay_type ='openpaywebatm'; break;
    case 8:   $pay_type = 'openpaycstore'; break;
    case 9:   $pay_type = 'openpayibon'; break;
    case 10:  $pay_type = 'openpaychinapay'; break;
    case 11:  $pay_type = 'openpaypaypal'; break;
    case 12:  $pay_type = 'openpayfami'; break;
    case 13:  $pay_type = 'openpayalipay'; break;
    case 14:  $pay_type = 'openpaytenpay'; break;
    default:  $pay_type = MODULE_PAYMENT_OPENPAY_TEXT_PAYTYPE_UNKNOWN .'('.$request['pay_type'].')';
    endswitch;
         $reurl =Mage::getUrl('').$pay_type.'/processing/response';
    $this->data['def_url']  = "<form name='form1' method='post' action='".$reurl."'>";

            $this->data['def_url'] .= "<input type='hidden' name='amount' value='".$request['amount']."'>";
           
            
            $this->data['def_url'] .= "<input type='hidden' name='txid' value='".$request['txid']."'>";
            
            $this->data['def_url'] .= "<input type='hidden' name='verify' value='".$request['verify']."'>";
            $this->data['def_url'] .= "<input type='hidden' name='pay_type' value='".$request['pay_type']."'>";
            $this->data['def_url'] .= "<input type='hidden' name='tid' value='".$request['tid']."'>";

            $this->data['def_url'] .= "<input type='hidden' name='status' value='".$request['status']."'>";
            
            $this->data['def_url'] .= "</form>".'<script type="text/javascript">
                				document.forms["form1"].submit();
                				</script> ';
                        
             echo  $this->data['def_url'];        
    }

   


    protected function _getPendingPaymentStatus()
    {
        return Mage::helper('openpay')->getPendingPaymentStatus();
    }
}