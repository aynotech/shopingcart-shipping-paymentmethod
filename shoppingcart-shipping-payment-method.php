<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Plugin\ShoppingCart\Order;
use RocketTheme\Toolbox\Event\Event;
use Grav\ShoppingcartShippingPaymentMethod\Service\configService;

class ShoppingcartShippingPaymentMethodPlugin extends Plugin
{

    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    public function onPluginsInitialized()
    {

        if (!$this->isAdmin()) {
            $this->enable([
                'onShoppingCartPay'     => ['onShoppingCartPay', 1],
            ]);
            $uri = $this->grav['uri'];
            $assets = $this->grav['assets'];
            if('/checkout' == $uri->path()){
                $this->enable([
                    'onTwigSiteVariables'   => ['onTwigSiteVariables', 0],
                ]);
                $settings = $this->config->get('plugins.shoppingcart-shipping-payment-method');
                $settings_js = 'var shipping_payment_mapping = [';
                if(array_key_exists('mapping',$settings) && $settings['mapping'] && count($settings['mapping']) > 0 ){
                    foreach ($settings['mapping'] as $rule){
                        $shipping = $rule['shipping'];
                        $payment = $rule['payment'];
                        $settings_js .= "{'shipping': '$shipping', 'payment': '$payment'}";
                        if (next($settings['mapping'])==true) $settings_js .= ",";
                    }
                }
                $settings_js .= '];';
                $assets->addInlineJs($settings_js);
            }
        }else {
            // require_once service class;
            require_once __DIR__.'/service/ConfigService.php';
        }

    }

    /**
     * Add css for this plugin
     */
    public function onTwigSiteVariables()
    {
        $this->grav['assets']->addJs('plugin://shoppingcart-shipping-payment-method/service/script.js');
    }

    /**
     * @param $event
     */
    public function onShoppingCartPay(Event $event)
    {
        
        // check that shipping method has allowed to use this gateway
        $selected_gateway = $event['gateway']; // exmpl: manual_checkout
        /** @var Order $order */
        $order = $event['order'];  // method name
        $selected_shipping_name = $order->getData()['shipping']['method'];
        $supported_shippings = $this->config->get('plugins.shoppingcart.shipping.methods');
        $mapping = $this->config->get('plugins.shoppingcart-shipping-payment-method.mapping');

        // check it is a supported shipping method
        $is_shipping_supported = false;
        foreach ($supported_shippings as $shipping) {
            if($shipping['name'] == $selected_shipping_name){
                $is_shipping_supported = true;
            }
        }

        if(!$is_shipping_supported){
            throw new \RuntimeException('Sorry, there was an error processing your payment: shipping method is not supported');
        }

        // check there is a mapping entry for this shipping, if there is no any mapping for this one, let everuthing goes forward
        $is_payment_permitted = false;
        $has_mapping = false;
        foreach ($mapping as $rule) {
            if($rule['shipping'] == $selected_shipping_name){
                $has_mapping = true;
                if($rule['payment'] == $selected_gateway){
                    $is_payment_permitted = true;
                }
            }
        }

        if($has_mapping && !$is_payment_permitted){
            throw new \RuntimeException('Sorry, there was an error processing your payment: payment method for this shipping is not permitted');
        }
    }

}
