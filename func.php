<?php
function json_input($data){
  global $app;
  $app->status(200);
    $app->contentType('application/json');
    echo json_encode($data,JSON_NUMERIC_CHECK);
}
function getConnection() {
    $dbhost="localhost";
    $dbuser="root";
    $dbpass="mysql2v68cx3a7";
    $dbname="mh";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}
function check_dev($dev) {
  $sens_list = array(
    'id' => array(
      '28_00000448abc3',
      '28_000004e4fd40',
      '28_000005475d6d',
      '28_0415a30f63ff',
      '35_000002793ac7',
      '35_000002793ac8',
      '35_0000034fab32',
      '35_0000034fab33',
      '47_00000451dcf4',
      '81_000000000001',
      '81_000000000002',
      '28_0a020b08010d',
      '28_0415a30fdfff'
    )
  );
  if (in_array($dev, $sens_list['id'])) {
    return 1;
  } else {
    echo json_encode(array('code' => '404'));
  }
}
function check_gpio($gpio) {
  if (in_array($gpio, array('1','2','3','4','pomp'))) {
    return 1;
  } else {
    echo json_encode(array('code' => '404'));
  }
};
function check_tooken($tooken) {
  $pdo = getConnection();
  $mysql_results = $pdo->prepare("SELECT * FROM users WHERE tooken=:tooken");
  $mysql_results->bindParam(':tooken', $tooken);
  $mysql_results->execute();
  if($mysql_results->fetch(PDO::FETCH_ASSOC)){
    return 1;
  } else {
    echo json_encode(array('code' => '403'));
  }
  $pdo = null;
};
function get_user($tooken) {
  $pdo = getConnection();
  $mysql_results = $pdo->prepare("SELECT * FROM users WHERE tooken=:tooken");
  $mysql_results->bindParam(':tooken', $tooken);
  $mysql_results->execute();
  while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
  if(count($rr) > 0){
    //print_r($rr);
    return $rr['name'];
    //return 1;
  } else {
    echo json_encode(array('code' => '403'));
  }
}
  $pdo = null;
}
function set_hs($type,$data) {
  try {
    $pdo = getConnection();
    $mysql_results = $pdo->prepare("UPDATE `heating_system` SET {$type}=:data WHERE id=1");
    //$mysql_results->bindParam(':name', 'max_temp');
    $mysql_results->bindParam(':data',  $data);
    $mysql_results->execute();
    $pdo = null;
  } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
  }

}
function gpio_log($gpio,$action,$user){
  $pdo = getConnection();
  $mysql_results = $pdo->prepare("INSERT INTO `gpio_logs`(`gpio_num`, `time`, `ip`, `user`, `action`) VALUES (:gpio_num,:time,:ip,:user,:action)");
  $mysql_results->bindParam(':gpio_num', $gpio);
  $mysql_results->bindParam(':time',  time());
  $mysql_results->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
  $mysql_results->bindParam(':user', $user);
  $mysql_results->bindParam(':action', $action);
  $mysql_results->execute();
  $pdo = null;
}
 ?>
