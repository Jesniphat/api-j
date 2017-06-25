<?php
	class Cart {
		public $lock = "customer_cart";
        public $cookieName = "cartdata";

        public function __construct(){

        }

        public function readAllCart(){
        	$cart_list;
        	$cookieCart = $_COOKIE[$this->cookieName];

        	if ($cookieCart) {
                $jwt = JWT::decode($_COOKIE[$this->cookieName], $this->lock, array('HS256'));
                $cart_list = (array) $jwt;
            }
            else {
                $cart_list = array("prod_list" => array(), "prod_qty" => 0, "prod_total" => 0);
            }
        }

        public function writeCart($product){
        	$cart_list = $product;
            $cookieCart = JWT::encode($cart_list, $this->lock);
            setcookie($this->cookieName, $cookieCart, time() + (86400 * 30), "/");
        }

        public function clearCart(){
        	$cart_list = array("prod_list" => array(), "prod_qty" => 0, "prod_total" => 0);
        	$cookieCart = JWT::encode($cart_list, $this->lock);
            setcookie($this->cookieName, $cookieCart, time() + (86400 * 30), "/");
        }

        public function getCart(){
        	$cart_list = $this->readAllCart();
        	return $cart_list;
        }

        public function isCart(){
        	$cart_list = $this->readAllCart();

        	if($cart_lidt["prod_qty"] != 0){
        		return true;
        	}else{
        		return false;
        	}
        }
	}
?>