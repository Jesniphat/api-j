<?php 
    ////////////////////////////////////////////////////////////
    // Bootstrap
    ////////////////////////////////////////////////////////////
    $req = explode('/', $_SERVER['PATH_INFO']);
    array_shift($req);
    // echo "req = " ; print_r($req);

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

	if($_FILES){
		$file = $_FILES['file']['tmp_name'];
		// $content = addslashes(file_get_contents($file));
		$name = $_FILES['file']['name'];
		$type = $_FILES['file']['type'];
		$size = $_FILES['file']['size'];

		switch ($req[1]) {
			case 'product':
                saveProductPic($file, /*$content,*/ $name, $type, $size);
                break;
            case 'slider':
                saveSliderPic();
                break;
		}
	}else{
        responseJson(array(
            'status' => false,
            'error' => "No file Upload."
        ));
    }

    ///////////////////////////////////// Funtion //////////////////////////////////////////

    function saveProductPic(){
        try {
            global $pdo, $file, /*$content,*/ $name, $type, $size;

            $pdo->beginTransaction();

            $new_name = date("Ymdhisa");

            $taget = "project_shop_api/product-img/" . $new_name . "_" . $name;
            move_uploaded_file($file, $taget);

            $sql = "INSERT INTO product_pic(productpic_name, productpic_type, productpic_size, productpic_path, uuid) 
                    VALUES(:productpic_name, :productpic_type, :productpic_size, :productpic_path, uuid())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                ':productpic_name' => $name,
                ':productpic_type' => $type,
                ':productpic_size' => $size,
                ':productpic_path' => $taget
            ));
            $pic_id = $pdo->lastInsertId();

            $getImg = "SELECT * FROM product_pic WHERE id = '$pic_id'";
            $stmt = $pdo->prepare($getImg);
            $stmt->execute();
            $pic = $stmt->fetch();

            $pdo->commit();
            responseJson(array(
                'status' => true,
                'data' => $pic
            ));
        } catch (PDOException $e) {
            $pdo->rollback();
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }

    function saveSliderPic(){
        try {
            global $pdo, $file, /*$content,*/ $name, $type, $size;

            $pdo->beginTransaction();

            $new_name = date("Ymdhisa");

            $taget = "project_shop_api/slider-img/" . $new_name . "_" . $name;
            move_uploaded_file($file, $taget);

            // $sql = "INSERT INTO slider_pic(pic_name, pic_path) 
            //         VALUES(:pic_name, :pic_path)";
            // $stmt = $pdo->prepare($sql);
            // $stmt->execute(array(
            //     ':pic_name' => $name,
            //     ':pic_path' => substr($taget, 2)
            // ));
            // $pic_id = $pdo->lastInsertId();

            // $getImg = "SELECT * FROM slider_pic WHERE id = '$pic_id'";
            // $stmt = $pdo->prepare($getImg);
            // $stmt->execute();

            $pic = array('pic_name' => $name, 'pic_path' => $taget, 2);

            $pdo->commit();
            responseJson(array(
                'status' => true,
                'data' => $pic
            ));
        } catch (PDOException $e) {
            $pdo->rollback();
            responseJson(array(
                'status' => false,
                'error' => $e -> getMessage()
            ));
        }
    }
?>