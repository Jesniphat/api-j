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

    function saveProductPic($file, $content, $name, $type, $size){
        try {
            global $pdo;

            $pdo->beginTransaction();
            $sql = "INSERT INTO product_pic(productpic_name, productpic_type, productpic_size, productpic_content, uuid) 
                    VALUES(:productpic_name, :productpic_type, :productpic_size, :productpic_content, uuid())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                ':productpic_name' => $name,
                ':productpic_type' => $type,
                ':productpic_size' => $size,
                ':productpic_content' => $content
            ));
            $pic_id = $pdo->lastInsertId();

            $pdo->commit();
            responseJson(array(
                'status' => true,
                'data' => $pic_id
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