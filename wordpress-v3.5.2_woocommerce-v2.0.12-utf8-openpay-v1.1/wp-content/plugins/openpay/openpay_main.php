<?php


class openpay_main {

        function get_request_params($order,$select_paymethod,$iid='0') {
            global $woocommerce;
		
                
            $order_id = $order->id;
            
			$txid = $order_id;
			
            $mid=$this->settings['mid'];
            
            $key1=$this->settings['key1'];
            
            $key2=$this->settings['key2'];
            
            $amount = (int)$order->order_total;
			
			$verify = md5(implode( '|', array($key1, $mid, $txid, $amount, $key2) ));    	
		
            $xname = $order->billing_last_name . $order->billing_first_name;
			
            $xaddress = $order->billing_address_1;
            
            $cname = $order->billing_last_name . $order->billing_first_name;
            
            $caddress = $order->shipping_address_1;
            
            $return_url = $this->get_return_url($order);
           
            $ctel = $order->billing_phone;
            
            $cemail = $order->billing_email;
            
            $this->data['def_url']  = "<form name='form1' method='post' action='https://www.twv.com.tw/openpay/pay.php'>";
            $this->data['def_url'] .= "<input type='hidden' name='version' value='2.1'>";
            $this->data['def_url'] .= "<input type='hidden' name='mid' value='".$mid."'>";
            $this->data['def_url'] .= "<input type='hidden' name='amount' value='".$amount."'>";
            $this->data['def_url'] .= "<input type='hidden' name='iid' value='".$iid."'>";
            $this->data['def_url'] .= "<input type='hidden' name='select_paymethod' value='".$select_paymethod."'>";
            $this->data['def_url'] .= "<input type='hidden' name='txid' value='".$txid."'>";
            $this->data['def_url'] .= "<input type='hidden' name='return_url' value='".$return_url."'>";
            $this->data['def_url'] .= "<input type='hidden' name='verify' value='".$verify."'>";
            $this->data['def_url'] .= "<input type='hidden' name='cname' value='".$cname."'>";
            $this->data['def_url'] .= "<input type='hidden' name='caddress' value='".$caddress."'>";
            $this->data['def_url'] .= "<input type='hidden' name='language' value='tchinese'>";
            $this->data['def_url'] .= "<input type='hidden' name='charset' value='UTF-8'>";
            $this->data['def_url'] .= "<input type='hidden' name='xname' value='".$xname."'>";
            $this->data['def_url'] .= "<input type='hidden' name='xaddress' value='".$xaddress."'>";
            $this->data['def_url'] .= "<input type='hidden' name='ctel' value='".$ctel."'>";
            $this->data['def_url'] .= "<input type='hidden' name='cemail' value='".$cemail."'>";
            
            $this->data['def_url'] .= "</form>";
            return $this->data['def_url'];

           
            
         
        }
        
        function verification($res,$order){
			
        	
        	
        	$key1=$this->settings['key1'];
            
            $key2=$this->settings['key2'];
           
            $amount = (int)$order->order_total;
          
        	//render/check verify string
        	$plain_text = implode( '|', array($key1, $res['txid'], $amount, $res['pay_type'], $res['status'], $res['tid'],$key2) );
        	$verify = md5( $plain_text );
        	
        	if($verify==$res['verify']){
        		return  true;
        	}else{
        		return false;
        	}
        	
        }
	
   
       
}        
?>