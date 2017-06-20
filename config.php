<?php
  set_time_limit(0);
  $opt = array(
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  );
    try {
      $pdo = new PDO('mysql:host=db;dbname=project-jphp;charset=utf8','root','rootp@ssw0rd',$opt);
    } catch (PDOException $e) {
      echo "ต่อฐานข้อมูบไม่ได้เหอะ ".$e;
    }
?>