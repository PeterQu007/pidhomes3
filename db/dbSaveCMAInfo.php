<?php
if (isset($_POST["cmaData"])) {
  $cmaData = $_POST["cmaData"];
  array_shift($cmaData);
  $cmaID = $cmaData[0][36]; // old value is 28
  echo 'remote test';
} else {
  // $cmaData = array([
  //   'MLS' => 'R2476903',
  //   'Status' => 'A',
  //   'SubArea' => 'Guildford',
  //   'Price0' => '$380,000',
  //   'PrcSqft' => '394.19',
  //   'ListDate' => '7/16/2020',
  //   'CDOM' => '4',
  //   'Complex' => 'Lincolns Gate',
  //   'TotBR' => 2,
  //   'TotBath' => 2,
  //   'FlArTotFin' => '1006',
  //   'Age' => '40',
  //   'StratMtFee' => '273.12',
  //   'TypeDwel' => 'Townhouse',
  //   'LotSz' => 0,
  //   'LandValue' => '$298,000',
  //   'ImproveValue' => '$20,600',
  //   'BCAValue' => '$318,600',
  //   'ChangePercentage' => '19%',
  //   'PlanNum' => 'NWS1581',
  //   'Address2' => '10620 150 ST',
  //   'UnitNo' => '#1014',
  //   'City' => 'Surrey',
  //   'ListPrice' => '$380,000',
  //   'SP_Sqft' => '0',
  //   'YrBlt' => '1980',
  //   'TotFlArea' => '1006',
  //   'cma_ID' => 999
  // ]);

  $cmaData = array(array(
    1,
    'R2476903',
    'A',
    'Guildford',
    '$380,000',
    '394.19',
    '7/16/2020',
    '4',
    'Lincolns Gate',
    2,
    2,
    '1006',
    '40',
    '273.12',
    'Townhouse',
    0,
    '$298,000',
    '$20,600',
    '$318,600',
    '19%',
    'NWS1581',
    '10620 150 ST',
    '#1014',
    'Surrey',
    '$380,000',
    '0',
    '1980',
    '1006',
    999
  ));
  $cmaID = 999;
}

// Get Cma Subject ID
require_once(dirname(dirname(__FILE__)) . '/pid-wp-db-config.php');
$mysqli = new mysqli(PID_DB_HOST, PID_DB_USER, PID_DB_PASSWORD, PID_DB_NAME);
// Check connection
if ($mysqli->connect_error) {
  die("Connection failed: " . $mysqli->connect_error);
}

// Delete old cma data
$strSqlDeleteOldCMAData = "DELETE FROM wp_pid_cma WHERE cma_ID ='$cmaID'";
if ($mysqli->query($strSqlDeleteOldCMAData) === TRUE) {
  // echo "Record deleted successfully";
} else {
  echo "Error deleting record: " . $mysqli->error;
}
$thread = $mysqli->thread_id;
$mysqli->kill($thread);
$mysqli->close();


// PUSH cma data to table wp_pid_cma
require_once('pdoConn.php');

$sql = "INSERT INTO " . PID_DB_NAME . ".wp_pid_cma
(
MLS,
Status,
SubArea,
Price0,
PrcSqft,
ListDate,
CDOM,
Complex,
TotBR,
TotBath,
FlArTotFin,
Age,
StratMtFee,
TypeDwel,
LotSz,
LandValue,
ImproveValue,
BCAValue,
ChangePercentage,
PlanNum,
Address2,
UnitNo,
City,
ListPrice,
SP_Sqft,
YrBlt,
TotFlArea,
cma_ID)
VALUES
(
?, 
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?,
?)
";

$stmt_insert_cma = $pdo->prepare($sql);

try {
  $pdo->beginTransaction();
  foreach ($cmaData as $cma) {
    // $Year = substr($monthYear, 0, 4);
    // $HPI = array_shift($data);
    $iStart = 1;
    $stmt_insert_cma->execute(
      array(
        $cma[$iStart++],
        $cma[$iStart++],
        $cma[$iStart++],
        str_replace(',', '', ltrim($cma[$iStart++], '$')),
        str_replace(',', '', ltrim($cma[$iStart++], '$')),
        date('Y-m-d', strtotime($cma[$iStart++])),
        $cma[$iStart++],
        $cma[$iStart++],
        $cma[$iStart++],
        $cma[$iStart++],
        str_replace(',', '', $cma[$iStart++]),
        $cma[$iStart++],
        str_replace(',', '', ltrim($cma[$iStart++], '$')),
        $cma[$iStart++],
        str_replace(
          ',',
          '',
          $cma[$iStart++]
        ),
        str_replace(',', '', ltrim($cma[$iStart++], '$')),
        str_replace(',', '', ltrim($cma[$iStart++], '$')),
        str_replace(',', '', ltrim($cma[$iStart++], '$')),
        $cma[$iStart++],
        $cma[$iStart++],
        $cma[$iStart++],
        $cma[$iStart++],
        $cma[$iStart++],
        str_replace(',', '', ltrim($cma[$iStart++], '$')),
        str_replace(',', '', ltrim($cma[$iStart++], '$')),
        $cma[$iStart++],
        str_replace(
          ',',
          '',
          $cma[$iStart++]
        ),
        $cma[36] // old $cma[$iStart++], update to 36
      )
    );
  }
  $pdo->commit();
  echo 'CMA Data Inserted to DB!';
} catch (Exception $e) {
  $pdo->rollback();
  echo '["PDO error"]';
  $stmt_insert_cma = null;
  $pdo = null;
  throw $e;
}
$stmt_insert_cma = null;
$pdo = null;
