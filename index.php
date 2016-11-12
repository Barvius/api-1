<?php
 header('Access-Control-Allow-Origin: *');
 header('Content-Type: application/json');
require 'vendor/autoload.php';
require 'func.php';
//use Psr\Http\Message\ServerRequestInterface;
//use Psr\Http\Message\ResponseInterface;
error_reporting(E_ALL);
ini_set("display_errors", 1);

//$app = new \Slim\App;
$config = ['settings' => [
    'addContentLengthHeader' => false,
]];
$app = new Slim\App($config);

//$app->response->headers->set('Content-Type', 'application/json');
$app->get('/gpio/{tooken}/{lamp_num}/{act}', function ($request, $response, $args) {
  $lamp_list = array(
    '1' => '23',
    '2' => '24',
    '3' => '25',
    '4' => '8',
    'pomp' => '9'
);
if (check_tooken($args['tooken']) and check_gpio($args['lamp_num'])) {
  switch ($args['act']){
    case 'on':
      //exec('echo 0 > /sys/class/gpio/gpio'.$lamp_list[$args['lamp_num']].'/value');
      echo json_encode(array('code' => '200'));
      gpio_log($lamp_list[$args['lamp_num']], 'on', get_user($args['tooken']));
    break;
    case 'off':
      //exec('echo 1 > /sys/class/gpio/'.$lamp_list[$args['lamp_num']].'/value');
      echo json_encode(array('code' => '200'));
      gpio_log($lamp_list[$args['lamp_num']], 'off', get_user($args['tooken']));
    break;
    case 'status':
      echo json_encode(array('code' => '200', 'data' => array('name' => 'dev-'.$args['lamp_num'], 'value' => '')));
      $motion = 1;
    break;
    case 'log':
    $pdo = getConnection();
    $mysql_results = $pdo->prepare("SELECT * FROM `gpio_logs` WHERE gpio_num=:gpio ORDER BY id DESC LIMIT 20");
    $mysql_results->bindParam(':gpio', $lamp_list[$args['lamp_num']]);
    $mysql_results->execute();
    while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
            $data[] = array($rr['time']+10800, $rr['user'], $rr['action'], $rr['ip']);
      }
    echo json_encode(array('code' => '200', 'name' => 'dev-'.$args['lamp_num'], 'log' => $data));
    $pdo = null;
    break;
    default:
      echo json_encode(array('code' => '404'));
    break;
  }
}
});
$app->get('/dev/{tooken}/list', function ($request, $response, $args) {
  if (check_tooken($args['tooken'])) {
    $pdo = getConnection();
    $mysql_results = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'stats' AND table_schema = 'mh' AND column_name LIKE '%\_%'");
    while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
      $data[] = $rr['COLUMN_NAME'];
    }
    $mysql_results = $pdo->query("SELECT * FROM `name_aliases`");
    while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
      $name[$rr['sensor_id']] = array($rr['name'],$rr['prefix'],$rr['enable']);
    }
    $response->write(json_encode(array('code' => '200', 'id' => $data, 'atr'=> $name),JSON_NUMERIC_CHECK));
    $response = $response->withHeader('Content-Type', 'application/json');
    return $response;
    $pdo = null;
  };
});

$app->get('/set_atr/{tooken}/{dev}/{name}/{prefix}/{enable}', function ($request, $response, $args) {
  if (check_tooken($args['tooken'])) {
    $pdo = getConnection();
    $mysql_results = $pdo->prepare("SELECT `id` FROM `name_aliases` WHERE `sensor_id`=:sensor_id");
    $mysql_results->bindParam(':sensor_id', $args['dev']);
    $mysql_results->execute();
    $id = $mysql_results->fetch(PDO::FETCH_ASSOC);
    if(!$id) {
      $mysql_results = $pdo->prepare("INSERT INTO `name_aliases`(`sensor_id`, `name`, `prefix`, `enable`) VALUES (:sensor_id, :name, :prefix, :enable)");
      $mysql_results->bindParam(':sensor_id', $args['dev']);
      $mysql_results->bindParam(':name', $args['name']);
      $mysql_results->bindParam(':prefix', $args['prefix']);
      $mysql_results->bindParam(':enable', $args['enable']);
      $mysql_results->execute();
    } else {
      $mysql_results = $pdo->prepare("UPDATE `name_aliases` SET `sensor_id`=:sensor_id,`name`=:name, `prefix`=:prefix, `enable`=:enable WHERE id=:id");
      $mysql_results->bindParam(':id', $id['id']);
      $mysql_results->bindParam(':sensor_id', $args['dev']);
      $mysql_results->bindParam(':name', $args['name']);
      $mysql_results->bindParam(':prefix', $args['prefix']);
      $mysql_results->bindParam(':enable', $args['enable']);
      $mysql_results->execute();
    }
    $pdo = null;
  };
});
$app->get('/dev/{tooken}/{dev}/{data}', function ($request, $response, $args) {
  if (check_tooken($args['tooken']) and check_dev($args['dev'])) {
    switch ($args['data']) {
      case 'all':
        $pdo = getConnection();
        $mysql_results = $pdo->query("SELECT {$args['dev']} ,date FROM `stats` order by `id` desc");
        while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
          $data[] = array( ($rr['date']+10800)*1000, $rr[$args['dev']]);
        }
        $response->write(json_encode(array('code' => '200', 'id' => $args['dev'], 'data' => $data),JSON_NUMERIC_CHECK));
        $response = $response->withHeader('Content-Type', 'application/json');
        return $response;
        $pdo = null;
      break;
      case 'day':
        $pdo = getConnection();
        $mysql_results = $pdo->query("SELECT {$args['dev']} ,date FROM `stats` ORDER BY `id` DESC LIMIT 288");
        while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
                $data[] = array( ($rr['date']+10800)*1000, $rr[$args['dev']]);
          }
        $mysql_results = $pdo->query("SELECT {$args['dev']} , id FROM stats WHERE {$args['dev']}=(SELECT MIN({$args['dev']}) FROM stats ORDER BY id DESC LIMIT 288)");
        $rr = $mysql_results->fetch(PDO::FETCH_ASSOC);
        $min = $rr[$args['dev']];
        $mysql_results = $pdo->query("SELECT {$args['dev']} FROM `stats` WHERE {$args['dev']}=(SELECT MAX({$args['dev']}) FROM stats ORDER BY id DESC LIMIT 288)");
        $rr = $mysql_results->fetch(PDO::FETCH_ASSOC);
        $max = $rr[$args['dev']];
        $response->write(json_encode(array('code' => '200', 'id' => $args['dev'], 'min' => $min, 'max' => $max, 'data' => $data),JSON_NUMERIC_CHECK));
        $response = $response->withHeader('Content-Type', 'application/json');
        return $response;
        $pdo = null;
      break;
      case 'now':
        $pdo = getConnection();
        $mysql_results = $pdo->query("SELECT {$args['dev']} FROM `stats` WHERE id=(SELECT MAX(id) FROM stats)");
        $rr = $mysql_results->fetch(PDO::FETCH_ASSOC);
        $response->write(json_encode(array('code' => '200', 'id' => $args['dev'], 'data' => $rr[$args['dev']])));
        $response = $response->withHeader('Content-Type', 'application/json');
        return $response;
        $pdo = null;
      break;
      default:
        echo json_encode(array('code' => '404'),JSON_NUMERIC_CHECK);
      break;

    }
  }
});
$app->get('/hs/{tooken}/{act}', function ($request, $response, $args) {
if (check_tooken($args['tooken'])) {
  switch ($args['act']) {
    case 'get_max_temp':
    $pdo = getConnection();
    $mysql_results = $pdo->query("SELECT max_temp FROM `heating_system` WHERE id=1");
    $rr = $mysql_results->fetch(PDO::FETCH_ASSOC);
    $data = $rr['max_temp'];
    echo json_encode(array('code' => '200', 'data' => $data));
    $pdo = null;
      break;
    case 'get_min_temp':
    $pdo = getConnection();
    $mysql_results = $pdo->query("SELECT min_temp FROM `heating_system` WHERE id=1");
    $rr = $mysql_results->fetch(PDO::FETCH_ASSOC);
    $data = $rr['min_temp'];
    echo json_encode(array('code' => '200', 'data' => $data));
    $pdo = null;
      break;
    case 'get_mode':
    $pdo = getConnection();
    $mysql_results = $pdo->query("SELECT mode FROM `heating_system` WHERE id=1");
    $rr = $mysql_results->fetch(PDO::FETCH_ASSOC);
    $data = $rr['mode'];
    echo json_encode(array('code' => '200', 'data' => $data));
    $pdo = null;
      break;

    default:
      # code...
      break;
  }
} else echo "string";
});
$app->get('/hs/{tooken}/{act}/{data}', function ($request, $response, $args) { //добавить валидацию данных
if (check_tooken($args['tooken'])) {
  switch ($args['act']) {
    case 'set_max_temp':
    set_hs('max_temp',$args['data']);
      break;
    case 'set_min_temp':
    set_hs('min_temp',$args['data']);
      break;
    case 'set_mode':
    set_hs('mode',$args['data']);
      break;

    default:
      # code...
      break;
  }
}
});

//Make things happen
//echo json_encode($data,JSON_NUMERIC_CHECK);
$app->run();

// function getWines() {
//     $sql = "select * FROM wine ORDER BY name";
//     try {
//         $db = getConnection();
//         $stmt = $db->query($sql);
//         $wines = $stmt->fetchAll(PDO::FETCH_OBJ);
//         $db = null;
//         echo '{"wine": ' . json_encode($wines) . '}';
//     } catch(PDOException $e) {
//         echo '{"error":{"text":'. $e->getMessage() .'}}';
//     }
// }
//
// function getWine($id) {
//     $sql = "SELECT * FROM wine WHERE id=:id";
//     try {
//         $db = getConnection();
//         $stmt = $db->prepare($sql);
//         $stmt->bindParam("id", $id);
//         $stmt->execute();
//         $wine = $stmt->fetchObject();
//         $db = null;
//         echo json_encode($wine);
//     } catch(PDOException $e) {
//         echo '{"error":{"text":'. $e->getMessage() .'}}';
//     }
// }
//
// function addWine() {
//     $request = Slim::getInstance()->request();
//     $wine = json_decode($request->getBody());
//     $sql = "INSERT INTO wine (name, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";
//     try {
//         $db = getConnection();
//         $stmt = $db->prepare($sql);
//         $stmt->bindParam("name", $wine->name);
//         $stmt->bindParam("grapes", $wine->grapes);
//         $stmt->bindParam("country", $wine->country);
//         $stmt->bindParam("region", $wine->region);
//         $stmt->bindParam("year", $wine->year);
//         $stmt->bindParam("description", $wine->description);
//         $stmt->execute();
//         $wine->id = $db->lastInsertId();
//         $db = null;
//         echo json_encode($wine);
//     } catch(PDOException $e) {
//         echo '{"error":{"text":'. $e->getMessage() .'}}';
//     }
// }
//
// function updateWine($id) {
//     $request = Slim::getInstance()->request();
//     $body = $request->getBody();
//     $wine = json_decode($body);
//     $sql = "UPDATE wine SET name=:name, grapes=:grapes, country=:country, region=:region, year=:year, description=:description WHERE id=:id";
//     try {
//         $db = getConnection();
//         $stmt = $db->prepare($sql);
//         $stmt->bindParam("name", $wine->name);
//         $stmt->bindParam("grapes", $wine->grapes);
//         $stmt->bindParam("country", $wine->country);
//         $stmt->bindParam("region", $wine->region);
//         $stmt->bindParam("year", $wine->year);
//         $stmt->bindParam("description", $wine->description);
//         $stmt->bindParam("id", $id);
//         $stmt->execute();
//         $db = null;
//         echo json_encode($wine);
//     } catch(PDOException $e) {
//         echo '{"error":{"text":'. $e->getMessage() .'}}';
//     }
// }
//
// function deleteWine($id) {
//     $sql = "DELETE FROM wine WHERE id=:id";
//     try {
//         $db = getConnection();
//         $stmt = $db->prepare($sql);
//         $stmt->bindParam("id", $id);
//         $stmt->execute();
//         $db = null;
//     } catch(PDOException $e) {
//         echo '{"error":{"text":'. $e->getMessage() .'}}';
//     }
// }
//
// function findByName($query) {
//     $sql = "SELECT * FROM wine WHERE UPPER(name) LIKE :query ORDER BY name";
//     try {
//         $db = getConnection();
//         $stmt = $db->prepare($sql);
//         $query = "%".$query."%";
//         $stmt->bindParam("query", $query);
//         $stmt->execute();
//         $wines = $stmt->fetchAll(PDO::FETCH_OBJ);
//         $db = null;
//         echo '{"wine": ' . json_encode($wines) . '}';
//     } catch(PDOException $e) {
//         echo '{"error":{"text":'. $e->getMessage() .'}}';
//     }
// }

?>
