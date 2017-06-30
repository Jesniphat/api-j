<?php 
	session_start();

    ////////////////////////////////////////////////////////////
    // Bootstrap
    ////////////////////////////////////////////////////////////
    $req = explode('/', $_SERVER['PATH_INFO']);
    // echo "req = " ; print_r($req);
    array_shift($req);
    // echo "req shift = "; print_r($req);

    ////////////////////////////////////////////////////////////
    // REQUEST
    ////////////////////////////////////////////////////////////
    $input = file_get_contents('php://input');
    $param = array();
    try {
      $param = json_decode($input, true);
    } catch (Exception $e) {
      print_r($e);
    }
    //echo "param = ";print_r($param);
    ////////////////////////////////////////////////////////////
    // CONTROLLER
    ////////////////////////////////////////////////////////////
    function responseJson($data) {
      header('Content-Type: application/json; charset=UTF-8');
      echo json_encode($data, JSON_UNESCAPED_UNICODE);
      exit;
    }

    ///////////////////////////////////////////////////////////
    //  inclued setting
    ///////////////////////////////////////////////////////////
    include_once 'config.php';
    include_once './jwt/JWT.php';
    include_once './permission/permission.php';
    include_once 'base.php';
    include_once './cart/cart.php';

    ///////////////////////////////////////////////////////////
    // start function
    ///////////////////////////////////////////////////////////
    switch ($req[1]) {
	    case 'ping':
	        // ping($param);
	        break;
	    case 'getTheerProduct':
	    	getTheerProduct($param);
	    	break;
        case 'getRecommendProduct':
            getRecommendProduct($param);
            break;
        case 'getNewProduct':
            gerNewProduct($param);
            break;
        case 'addtocart':
            addToCart($param);
            break;
        case 'getCart':
            getCart();
            break;
	}

	//////////////////////////////////////////////////////////
    // START
    //////////////////////////////////////////////////////////

    function getTheerProduct($param) {
    	try{
            global $pdo;

            $sql = "SELECT * FROM slider WHERE status = 'Y'";
            
            $three_product = DB::QueryAll($sql);

            responseJson(array(
                'status' => true,
                'data' => $three_product
            ));
        }catch(PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function getRecommendProduct($param){
        try{
            global $pdo;

            $sql = "SELECT p.*,max(pp.productpic_path) AS img FROM product p INNER JOIN product_pic pp ON p.id = pp.product_id WHERE p.status = 'Y' AND pp.cover = 'Y' AND p.recommend = 'Y' group by p.id ";;
            
            $recommend = DB::QueryAll($sql);

            responseJson(array(
                'status' => true,
                'data' => $recommend
            ));
        }catch(PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function gerNewProduct($param){
        try {
            global $pdo;

            $sql = "SELECT p.*,max(pp.productpic_path) AS img FROM product p INNER JOIN product_pic pp ON p.id = pp.product_id WHERE p.status = 'Y' AND pp.cover = 'Y' group by p.id ORDER BY id DESC";
            
            $new_product = DB::QueryAll($sql);
            
            responseJson(array(
                'status' => true,
                'data' => $new_product
            ));
        } catch (PDOException $e) {
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function addToCart($param){
        try {
            $cart = new Cart();

            $prod_qty = $param['qty'];
            $prod_price = $param['qty'] * $param['price'];

            $cart_list = array();

            $cart_list = $cart->getCart();
            // print_r($cart_list);
            if(in_array($param['id'], array_keys($cart_list['prod_list']))) {
                $cart_list['prod_list'][$param['id']]['qty'] += $prod_qty;
                $cart_list['prod_list'][$param['id']]['price'] += $prod_price;
            }else{
                // print_r($cart_list);
                $cart_list['prod_list'][$param['id']] = array(
                    'id' => $param['id'],
                    'name' => $param['name'],
                    'img' => $param['img'],
                    'qty' => $param['qty'],
                    'price' => $prod_price
                );
            }
            $cart_list['prod_qty'] += $prod_qty;
            $cart_list['prod_total'] += $prod_price;

            $cart->writeCart($cart_list);
            // print_r($cart_list);
            responseJson(array(
                'status' => true,
                'data' => array(
                    'prod_qty' => $cart_list['prod_qty'],
                    'prod_total' => $cart_list['prod_total']
                )
            ));
        } catch (Exception $e) {
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function getCart(){
        try{
            $cart = new Cart();
            $cart_list = $cart->getCart();
            responseJson(array(
                'status' => true,
                'data' => $cart_list
            ));
        } catch (Exception $e) {
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }
?>