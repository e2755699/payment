<?php
/**
 * openpay_webatm Payment Gateway
 * Plugin URI: http://www.openpay_webatm.com.tw/
 * Description: OpenpayWebATM收款模組
 * Version: 1.0

 * Plugin Name: Openpay WebATM
 * @class 		openpay_webatm
 * @extends		WC_Payment_Gateway
 */
include_once(dirname(__FILE__).'/../openpay/openpay_main.php');

add_action('plugins_loaded', 'openpay_webatm_gateway_init', 0);

function openpay_webatm_gateway_init() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_openpay_webatm extends WC_Payment_Gateway {


            public function __construct() {
            $this->id = 'openpay_webatm';
            $this->icon = 'https://www.twv.com.tw/openpay/image/openpay_module_webatm.gif';
            $this->has_fields = false;
            $this->method_title = __('openpay_webatm', 'woocommerce');

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->settings['title'];
            $this->description = $this->settings['description'];
            $this->mid = $this->settings['mid'];
            $this->key1 = $this->settings['key1'];
            $this->key2 = $this->settings['key2'];
            $this->notify_url = trailingslashit(home_url());
            $this->gateway = 'https://www.twv.com.tw/openpay/pay.php';
            //加入hook
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                add_action('woocommerce_thankyou_'. $this->id, array($this, 'thankyou_page'));
            	add_action('woocommerce_receipt_'.$this->id, array($this, 'receipt_page'));
                                }

    function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('啟用/關閉', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('啟動 OpenpayWebATM 收款模組', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('標題', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('客戶在結帳時所看到的標題', 'woocommerce'),
                    'default' => __('OpenpayWebATM 收款', 'woocommerce')
                ),
                'description' => array(
                    'title' => __('客戶訊息', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('', 'woocommerce'),
                    'default' => __('台灣里 Openpay - <font color=red>WebATM 繳費</font>', 'woocommerce')
                ),
                'mid' => array(
                    'title' => __('商店編號', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('請填入您Openpay商店編號(MID)', 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'key1' => array(
                    'title' => __('參數檢查碼1', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('請填入您Openpay商店的參數檢查碼1', 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'key2' => array(
                    'title' => __('參數檢查碼2', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('請填入您Openpay商店的參數檢查碼2', 'woocommerce'),
                    'default' => __('', 'woocommerce')
                )
            );
        }

        public function admin_options() {
            ?>
            <h3><?php _e('台灣里 Openpay WebATM 收款模組', 'woocommerce'); ?></h3>
            <p><?php _e('此模組可以讓您使用台灣里 Openpay WebATM收款功能', 'woocommerce'); ?></p>
            <table class="form-table">
                <?php
                $this->generate_settings_html();
                ?>
            <?php
        }



function receipt_page($order_id) {
	global $woocommerce;

	$pay_type = '4';


    $order = new WC_Order($order_id);

	$request_form=openpay_main::get_request_params($order,$pay_type,$iid);

	$html_=$request_form.'<script type="text/javascript">
                				document.forms["form1"].submit();
                				</script> ';

	echo $html_;

}

    function thankyou_page($order_id) {
			global $woocommerce;
			$order = &new WC_Order($order_id);
			if ($description = $this->get_description())
				echo wpautop(wptexturize($description));


			$verify=openpay_main::verification($_POST,$order);

					$woocommerce->cart->empty_cart();
					if($verify==true){
					if($_POST['status']=='1'){
					$order->update_status('processing', __('完成付款', 'openpayvcc'));

					}else if($_POST['status']=='3'){
					$msg='您的交易單號:'.$_REQUEST['tid'];
					}
					else{
					$msg='付款失敗';
					}

					}else{
					$msg='驗證碼錯誤';
					}
					echo $msg;
			}

        function process_payment($order_id) {
            global $woocommerce;
            $order = new WC_Order($order_id);

            // Empty awaiting payment session
            unset($_SESSION['order_awaiting_payment']);
            //$this->receipt_page($order_id);
            return array(
                'result' => 'success',
                'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))))
            );
        }

        function payment_fields() {
            if ($this->description)
                echo wpautop(wptexturize($this->description));
        }

    }

    function add_openpay_webatm_gateway($methods) {
        $methods[] = 'WC_openpay_webatm';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_openpay_webatm_gateway');
}
?>