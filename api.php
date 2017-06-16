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
    include_once 'jwt/JWT.php';
    include_once 'permission/permission.php';
    include_once 'base.php';

    ///////////////////////////////////////////////////////////
    // start function
    ///////////////////////////////////////////////////////////
    $isCheckLogin = false;
    if($req[1] == 'login' || $req[1] == 'clearlogin' || $req[1] == 'checklogin'){
        $isCheckLogin = true;
    }else{
        $logined = new Permission();
        $token = $logined->isLogin();
        $isCheckLogin = $token;
    }

    if($isCheckLogin){
        switch ($req[1]) {
            case 'ping':
                ping($param);
                break;
            case 'session':
                doSetSession($param);
                break;
            case 'test':
                test($param);
                break;
            case 'testjwt':
                jwt($param);
                break;
            case 'checklogin':
                checkLogin($param);
                break;
            case 'clearlogin':
                clearLogin($param);
                break;
            case 'login':
                login($param);
                break;
            case 'createstaff':
                createStaff($param);
                break;
            case 'updatestaff':
                updatestaff($param);
                break;
            case 'category_list':
                categoryList($param);
                break;
            case 'getcategorybyid':
                getCategorybyid($param);
                break;
            case 'savecategory':
                saveCategory($param);
                break;
            case 'product_list':
                getProductList($param);
                break;
            case 'getproductbyid':
                getProducctById($param);
                break;
            case 'saveproduct':
                saveproduct($param);
                break;
            case 'saveslider':
                saveslider($param);
                break;
            case 'getsliderbyid':
                getsliderbyid($param);
                break;
            case 'slider_list':
                getallslider($param);
                break;
            case 'delete_product':
                delete_product($param);
                break;
            case 'delete_slider':
                delete_slider($param);
                break;
        }
    }else{
        responseJson(array(
            'status' => true,
            'nologin' => true
        ));
    }
    
    //////////////////////////////////////////////////////////
    // START
    //////////////////////////////////////////////////////////

    function ping($param){
        responseJson(array(
            'status' => true,
            'data' => "1"
        ));
    }

    function test($param){
        try {
            global $pdo;
            // throw new PDOException('Division by zero.');
            $sql = "select * from staff where id = :id";
            $pr = array(':id' => '2');
            $product = DB::QueryRow($sql,$pr);
            echo "OK!!!" . "<br />";
            print_r($product);
            responseJson(array(
                'status' => true,
                'data' => "12345 test = " . " param = " . $param['id']
            ));
        } catch (PDOException $e){
            // echo $e -> getMessage() . "<br />";;
            responseJson(array(
                'status' => false,
                'error' => "test1 = " . $e -> getMessage()
            ));
        } 
    }

    function checkLogin($param){
        try{
            $isLogined = new Permission();
            $token = $isLogined->isLogin();
            // echo $token;
            responseJson(array(
                'status' => true,
                'data' => $token
            ));
        }catch (PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function clearLogin($param){
        try{
            $isClearLogin = new Permission();
            $isClear = $isClearLogin->clearToken();

            responseJson(array(
                'status' => true,
                'data' => "set0"
            ));
        }catch (PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function login($param){
        try{
            global $pdo;
            $logining = new Permission();

            $pdo->beginTransaction();
            $sql = "SELECT * FROM staff WHERE user = :user and password = :password";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                ':user' => $param['user'],
                ':password' => md5($param['password'])
            ));
            $staffID = $stmt->fetch();

            if($staffID){
                $logining->writeToken($staffID);
            } else {
                throw new PDOException("Invalid Login.");
            }

            responseJson(array(
                'status' => true,
                'data' => array(
                    "id"=>$staffID['id'], 
                    "display_name"=>$staffID['name'], 
                    "last_name" => $staffID['lastname'],
                    "login_name" => $staffID['user'], 
                    "password" => $staffID['password']
                )
            ));
        }catch (PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function createStaff ($param){
        try{
            global $pdo;
            $pdo->beginTransaction();
            $sql = "INSERT INTO staff SET name = :name, lastname = :lastName, user = :user, password = :password, uuid = uuid()";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                ':name' => $param['staffName'],
                ':lastName' => $param['staffLastName'],
                ':user' => $param['staffUserName'],
                ':password' => md5($param['staffPassword'])
            ));

            $staff_id = $pdo->lastInsertId();

            $pdo->commit();
            responseJson(array(
                'status' => true,
                'data' => $staff_id
            ));
        }catch(PDOException $e){
            $pdo->rollback();
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function updatestaff($param){
        try{
            global $pdo;
            $pdo->beginTransaction();

            $sql = "UPDATE staff SET name = :name, lastname = :lastname, user = :user WHERE id = :id";
            $pr = array(
                    ':name' => $param['name'],
                    ':lastname' => $param['lastName'],
                    ':user' => $param['user'],
                    ':id' => $param['id']
                );
            $res = DB::Update($sql, $pr);
            $staff_id = $param['id'];

            $pdo->commit();

            responseJson(array(
                'status' => true,
                'data' => array("id"=>$param['id'], "display_name"=>$param['name'], "login_name" => $param['user'], "password" => $param['password'], "last_name" => $param['lastName'])
            ));
        }catch (PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function categoryList($param){
        // print_r($param);
        try{
            global $pdo;

            $sql = "SELECT id, cate_name, cate_description, '' as product_qty FROM category";
            
            $category_list = DB::QueryAll($sql);

            responseJson(array(
                'status' => true,
                'data' => $category_list
            ));
        }catch(PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function getCategorybyid($param){
        try{
            global $pdo;

            $sql = "SELECT * FROM category WHERE id = :id";
            $pr = array(':id' => $param['cate_id']);
            $category_list_byid = DB::QueryRow($sql, $pr);

            responseJson(array(
                'status' => true,
                'data' => $category_list_byid
            ));
        }catch(PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function saveCategory($param){
        try{
            global $pdo;
            $pdo->beginTransaction();

            $cate_id = "";

            if($param['cateId'] != "create"){
                $sql = "UPDATE category SET cate_name = :cate_name, cate_description = :cate_description, status = :status "
                     . "WHERE id = :id";
                $pr = array(
                        ':cate_name' => $param['cateName'],
                        ':cate_description' => $param['cateDescription'],
                        ':status' => $param['selectedStatus'],
                        ':id' => $param['cateId']
                    );
                $res = DB::Update($sql, $pr);
                $cate_id = $param['cateId'];
            } else {
                $sql = "INSERT INTO category(cate_name, cate_description, status, created_by, updated_by, uuid) "
                     . "VALUES(:cate_name, :cate_description, :status, '1', '1', uuid())";

                $pr = (array(
                    ':cate_name' => $param['cateName'],
                    ':cate_description' => $param['cateDescription'],
                    ':status' => $param['selectedStatus']
                ));

                $res = DB::Insert($sql, $pr);

                $cate_id = $res['insertId'];
            }

            $pdo->commit();
            responseJson(array(
                'status' => true,
                'data' => $cate_id
            ));

        }catch(PDOException $e){
            $pdo->rollback();
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function getProductList($param){
        try{
            global $pdo;

            $sql = "select p.*,max(pp.productpic_path) as img from product p inner join product_pic pp on p.id = pp.product_id where p.status = 'Y' group by p.id ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $product_list = $stmt->fetchAll();

            responseJson(array(
                'status' => true,
                'data' => $product_list
            ));

        }catch(PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function getProducctById($param){
        try{
            global $pdo;

            $sql = "select * from product where id = :product_id";
            $pr = array(
                ":product_id" => $param["product_id"],
            );
            $product = DB::QueryRow($sql, $pr);

            $sql = "select * from product_pic where product_id = :product_id and status = 'Y'";
            $pr = array(
                ":product_id" => $param["product_id"]
            );
            $product['pic'] = DB::QueryAll($sql, $pr);
            responseJson(array(
                'status' => true,
                'data' => $product
            ));
        }catch(PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function saveproduct($param){
        try{
            global $pdo;
            $pdo->beginTransaction();

            $prod_id = "";

            if($param['id'] == "create"){
                $product_code = _getNextCode("product", "code", "P", 5, 1);

                $sql = "INSERT INTO product (code, product_name, product_description, status, product_price, product_qty, created_by, updated_by, uuid, category_id ) "
                     . "VALUES(:code, :product_name, :product_description, 'true', :product_price, :product_qty, :staff_id, :staff_id, uuid(), :category_id)";
                $pr = (array(
                    ':code' => $product_code,
                    ':product_name' => $param['name'],
                    ':product_description' => $param['desc'],
                    ':product_price' => $param['price'],
                    ':product_qty' => $param['qty'],
                    ':staff_id' => $param['staffid'],
                    ':category_id' => $param['category']
                ));

                $res = DB::Insert($sql, $pr);

                $prod_id = $res['insertId'];

                if($param['pic_id']){
                    $sqls = "UPDATE product_pic SET product_id = '$prod_id' WHERE id IN (" . implode(",",$param['pic_id']) . ")";
                    DB::Update($sqls);
                }
                
            }else{
                $sql = "UPDATE product SET product_name = :product_name, product_description = :product_description, status = :status, 
                        product_price = :product_price, product_qty = :product_qty, updated_by = :staff_id, category_id = :category_id 
                        WHERE id = :id";
                $pr = array(
                    ':id' => $param['id'],
                    ':product_name' => $param['name'],
                    ':product_description' => $param['desc'],
                    ':status' => "true",
                    ':product_price' => $param['price'],
                    ':product_qty' => $param['qty'],
                    ':staff_id' => $param['staffid'],
                    ':category_id' => $param['category']
                );

                DB::Update($sql, $pr);

                if($param['pic_id']){
                    $sql = "UPDATE product_pic SET status = 'N' WHERE product_id = '$param[id]'";
                    DB::Update($sql);

                    $sqls = "UPDATE product_pic SET product_id = '$param[id]', status = 'Y' WHERE id IN (" . implode(",",$param['pic_id']) . ")";

                    DB::Update($sqls);
                }
                
                $prod_id = $param['id'];
            }

            $pdo->commit();
            responseJson(array(
                'status' => true,
                'data' => $prod_id
            ));
        }catch(PDOException $e){
            $pdo->rollback();
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function saveslider($param){
        try{
            global $pdo;
            $pdo->beginTransaction();

            if($param['id'] == "create"){
                // $product_code = _getNextCode("product", "code", "P", 5, 1);

                $sql = "INSERT INTO slider (name, description, link_to, link_id, pic_name, pic_path, created_by, updated_by) "
                     . "VALUES(:name, :description, :link_to, :link_id, :pic_name, :pic_path, :created_by, :updated_by)";
                $pr = (array(
                    ':name' => $param['name'],
                    ':description' => $param['description'],
                    ':link_to' => $param['link_to'],
                    ':link_id' => $param['link_id'],
                    ':pic_name' => $param['image_data']['pic_name'],
                    ':pic_path' => $param['image_data']['pic_path'],
                    ':created_by' => $param['staff'],
                    ':updated_by' => $param['staff']
                ));

                $res = DB::Insert($sql, $pr);

                $prod_id = $res['insertId'];

                if($param['pic_id']){
                    $sqls = "UPDATE product_pic SET product_id = '$prod_id' WHERE id IN (" . implode(",",$param['pic_id']) . ")";
                    DB::Update($sqls);
                }
                
            }else{
                $sql = "UPDATE slider SET name = :name, description = :description, link_to = :link_to, link_id = :link_id, pic_name = :pic_name, pic_path = :pic_path, updated_by = :updated_by WHERE id = :id ";
                $pr = (array(
                    ':id' => $param['id'],
                    ':name' => $param['name'],
                    ':description' => $param['description'],
                    ':link_to' => $param['link_to'],
                    ':link_id' => $param['link_id'],
                    ':pic_name' => $param['image_data']['pic_name'],
                    ':pic_path' => $param['image_data']['pic_path'],
                    ':updated_by' => $param['staff']
                ));

                DB::Update($sql, $pr);
            }

            $pdo->commit();
            responseJson(array(
                'status' => true,
                'data' => $prod_id
            ));
        }catch(PDOException $e){
            $pdo->rollback();
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function getsliderbyid($param){
        try{
            global $pdo;

            $sql = "select * from slider where id = :slider_id";
            $pr = array(
                ":slider_id" => $param["id"],
            );
            $slider = DB::QueryRow($sql, $pr);

            responseJson(array(
                'status' => true,
                'data' => $slider
            ));
        }catch(PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function getallslider($param){
        try{
            global $pdo;

            $sql = "select * from slider where status = 'Y'";
            // $pr = array();
            $slider = DB::QueryAll($sql);

            responseJson(array(
                'status' => true,
                'data' => $slider
            ));
        }catch(PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function delete_product($param){
        try{
            global $pdo;

            $sql = "UPDATE product SET status = 'N' WHERE id = :id";
            $pr = array(
                ":id" => $param["id"],
            );
            $row = DB::Update($sql, $pr);

            responseJson(array(
                'status' => true,
                'data' => $row
            ));
        }catch(PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }  
    }


    function delete_slider($param){
        try{
            global $pdo;

            $sql = "UPDATE slider SET status = 'N' WHERE id = :id";
            $pr = array(
                ":id" => $param["id"],
            );
            $row = DB::Update($sql, $pr);

            responseJson(array(
                'status' => true,
                'data' => $row
            ));
        }catch(PDOException $e){
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }   
    }


    function _getNextCode($table, $fld, $prefix='', $size=5, $start=1) {
            global $pdo;

            $sql = "SELECT max(`" . $fld . "`) maxCode FROM `" . $table . "`";
            if ($prefix != '') {
                $sql .= " WHERE `" . $fld . "` LIKE '" . $prefix . "%'";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $maxCode = $stmt->fetchColumn();
            if ($maxCode===false) {
                $next = $start + 0;
            } else {
             $next = substr($maxCode, strlen($prefix)) + 1;
            }
            return $prefix . substr('0000000000000' . $next, -$size);
    }

?>