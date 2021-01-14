<?php
require_once(dirname(dirname(__FILE__)) . '/pid-wp-db-config.php');

// if JS send data by Fetch POST, run a decoding line
$_POST = json_decode(file_get_contents('php://input'), true);

if (isset($_POST["neighborhood"])) {
  $neighborhood = $_POST["neighborhood"];
  echo $_POST;
  // echo " is your tab title";
  //print_r($address);
};

if (isset($_POST['areaCode'])) {
  $areaCode = $_POST['areaCode'];
} else {
  $areaCode = 'F20';
}

if (isset($_POST['propertyType'])) {
  $propertyType = $_POST['propertyType'];
} else {
  $propertyType = 'all';
}

if (isset($_POST['monthlyUpdate'])) {
  $monthlyUpdate = $_POST['monthlyUpdate'];
} else {
  $monthlyUpdate = false;
}

if (isset($_POST['statMonth'])) {
  $statMonth = $_POST['statMonth'];
} else {
  $statMonth =  date('m');
}

if (isset($_POST['statYear'])) {
  $statYear = $_POST['statYear'];
} else {
  $statYear = date('Y');
}
?> 
<?php

//$mysqli = new mysqli("localhost", "root", "root", "local");
$mysqli = new mysqli(PID_DB_HOST, PID_DB_USER, PID_DB_PASSWORD, PID_DB_NAME);

// if ($monthlyUpdate) {
//   $strSql_stat_exist = "SELECT count(Neighborhood_ID) AS Quan FROM wp_pid_market " .
//     "WHERE Neighborhood_ID = '$areaCode' " .
//     "AND Property_Type = '$propertyType' " .
//     "AND MONTH(`Date`) = '$statMonth' AND YEAR(`Date`) = '$statYear'";
//   $mysqli->real_query($strSql_stat_exist);
//   $res = $mysqli->use_result();
//   if ($res) {
//     while ($row = $res->fetch_assoc()) {
//       if ($row['Quan'] > 0) {
//         echo 'records exists';
//         exit();
//       }
//     }
//   }
// }

$strSql = "SELECT stat_code, `All`, Detached, Townhouse, Apartment FROM wp_pid_stats_code WHERE area_code='$areaCode'";
$mysqli->real_query($strSql);
$res = $mysqli->use_result();

if ($res) {
  while ($row = $res->fetch_assoc()) {
    echo json_encode($row); // send back stat_code, All, Detached, Townhouse, Apartment
  }
} else {
  echo json_encode("error in MySQL Query: $strSql"); //'error in mySQL query';
}


?>

