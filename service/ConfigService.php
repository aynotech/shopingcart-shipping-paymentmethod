<?php

namespace Grav\ShoppingcartShippingPaymentMethod\Service;

use Grav\Common\GravTrait;

class ConfigService
{
	use GravTrait;

	protected static $instance = null;
	

	public static function get_instance()
	{
		if (!static::$instance) {
			static::$instance = new static();
		}

		return static::$instance;
	}
	
	public function __construct()
	{
	}

	public static function getOptions(){
        $methods = static::getGrav()['config']->get('plugins.shoppingcart.shipping.methods');
        $result = [];
        foreach ($methods as $method) {
        	$name = $method['name'];
        	$result[$name] = $name;
        }
        return $result;
    }

}