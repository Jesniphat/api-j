<?php
	class DB {
		public function __construct(){}

		public static function QueryField($sql, $data=array()){
			global $pdo;
			$stmt = $pdo->prepare($sql);
			if (count($data) > 0) {
				$stmt->execute($data);
			} else {
				$stmt->execute();
			}
			return $stmt->fetchColumn();
		}

		public static function QueryRow($sql, $data=array()){
			global $pdo;
			$stmt = $pdo->prepare($sql);
			if (count($data) > 0) {
				$stmt->execute($data);
			} else {
				$stmt->execute();
			}
			return $stmt->fetch();
		}

		public static function QueryAll($sql, $data=array()){
			global $pdo;
			$stmt = $pdo->prepare($sql);
			if (count($data) > 0) {
				$stmt->execute($data);
			} else {
				$stmt->execute();
			}
			return $stmt->fetchAll();
		}

		public static function Insert($sql, $data=array()){
			global $pdo;

			$stmt = $pdo->prepare($sql);
            // $stmt->execute($data);
            if (count($data) > 0) {
				$stmt->execute($data);
			} else {
				$stmt->execute();
			}

            $res = array('insertId' => $pdo->lastInsertId() );
            return $res;
            // $staff_id = $pdo->lastInsertId();
		}

		public static function Update($sql, $data=array()){
			global $pdo;

			$stmt = $pdo->prepare($sql);
            // $stmt->execute($data);
            if (count($data) > 0) {
				$stmt->execute($data);
			} else {
				$stmt->execute();
			}

            $res = $stmt->rowCount();
            return $res;
		}

		public static function Delete($sql, $data=array()){
			global $pdo;

			$stmt = $pdo->prepare($sql);
            // $stmt->execute($data);
            if (count($data) > 0) {
				$stmt->execute($data);
			} else {
				$stmt->execute();
			}

            $res = $stmt->rowCount();
            return $res;
		}
	}
?>