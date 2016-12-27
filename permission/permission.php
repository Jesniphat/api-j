<?php 
    class Permission {
        public $lock = "p@ssw0rd";
        public $cookieName = "user";

        public function __construct(){

        }

        public function readToken(){
            $token;
            $cookieUser = $_COOKIE[$this->cookieName];
            if ($cookieUser) {
                $jwt = JWT::decode($_COOKIE[$this->cookieName], $this->lock, array('HS256'));
                $token = (array) $jwt;
            }
            else {
                $token = array("id" => 0);
            }
            return $token;
        }

        public function writeToken($id){
            $token = array("id" => $id);
            $cookieUser = JWT::encode($token, $this->lock);
            setcookie($this->cookieName, $cookieUser, time() + (86400 * 30), "/");
        }

        public function clearToken(){
            $this->writeToken(0);
        }

        public function getID(){
            $token = $this->readToken();
            return $token;
        }

        public function isLogin() {
            $token = $this->readToken();
            // echo $token['id'];
            if ($token['id'] != "0" || $token['id'] != 0) {
                return true;
            }
            else {
                return false;
            }
        }
    }
?>