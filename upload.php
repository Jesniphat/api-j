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
		$file = $_FILES['producPic']['tmp_name'];
		$content = addslashes(file_get_contents($file));
		$name = $_FILES['producPic']['name'];
		$type = $_FILES['producPic']['type'];
		$size = $_FILES['producPic']['size'];

		switch ($req[1]) {
			case 'product':
                saveProductPic($file, $content, $name, $type, $size);
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
            global $pdo, $file, $content, $name, $type, $size;

            $new_name = date("Ymdhisa");

            $taget = "./product-img/" . $new_name . "_" . $name;
            move_uploaded_file($file, $taget);

            $pdo->beginTransaction();
            $sql = "INSERT INTO product_pic(productpic_name, productpic_type, productpic_size, productpic_path, uuid) 
                    VALUES(:productpic_name, :productpic_type, :productpic_size, :productpic_path, uuid())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                ':productpic_name' => $name,
                ':productpic_type' => $type,
                ':productpic_size' => $size,
                ':productpic_path' => substr($taget, 2)
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
?>