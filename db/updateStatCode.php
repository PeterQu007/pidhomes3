<?php
require_once(dirname(dirname(__FILE__)) . '/pid-wp-db-config.php');

// if JS send data by Fetch POST, run a decoding line
$_POST = json_decode(file_get_contents('php://input'), true);

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

if (isset($_POST['hasStatData'])) {
  $hasStatData = $_POST['hasStatData'];
} else {
  $hasStatData = true;
}

?> 
<?php

//$mysqli = new mysqli("localhost", "root", "root", "local");
$mysqli = new mysqli(PID_DB_HOST, PID_DB_USER, PID_DB_PASSWORD, PID_DB_NAME);

$strSql = "UPDATE wp_pid_stats_code SET `$propertyType` = ? WHERE area_code = ?";
$stmt = $mysqli->prepare($strSql);
if ($stmt === false) {
  // trigger_error($mysqli->error, E_USER_ERROR);
  echo $mysqli->error;
  exit();
}

$stmt->bind_param('is', $hasStatData, $areaCode);

$status = $stmt->execute();

if ($status === false) {
  trigger_error($stmt->error, E_USER_ERROR);
  exit();
} else {
  echo json_encode("Rows Affected: $stmt->affected_rows");
}
// $mysqli->real_query($strSql);
// $mysqli->commit();

// if ($mysqli->error) {
//   echo json_encode("error in MySQL Query: $strSql"); //'error in mySQL query';
// } else {
//   echo json_encode("Update Done! $areaCode : $propertyType : $hasStatData");
// }

$stmt->close();
$thread = $mysqli->thread_id;
$mysqli->kill($thread);
$mysqli->close();

?>

