<?php

/**
 * http://localhost/pidrealty4/wp-content/themes/realhomes-child-3/db/saveStatData.php
 */

require_once(dirname(dirname(__FILE__)) . '/pid-wp-db-config.php');

// if JS send data by Fetch POST, run a decoding line
$_POST = json_decode(file_get_contents('php://input'), true);

if (isset($_POST['statData'])) {
  $statData = $_POST['statData'];
  $areaCode = $_POST['areaCode'];
  $propertyGroup = $_POST['propertyGroup'];
  if (count($statData) == 4) {
    array_shift($statData);
    array_shift($statData);
  } else {
    if (!isset($statData[0]['AxisLabels'])) {
      echo json_encode("No Stats In the Array Data, Data Return is only 2 Collection!");
      exit();
    }
  }
  $data = $statData[1]['Data'];
  if ($data != null) {
    if ($data[count($data) - 1] === "") {
      $dCount = count($data);
      echo "No Stats In the Array Data($dCount)!";
      exit();
    }
  } else {
    echo "No Stats In the Array Data, Data is NULL!";
    exit();
  }
  $AxisLabels = $statData[0]['AxisLabels'];
  $deleteOldData = $_POST['deleteOldData'] == 'false' ? false : true;
} else {
  $statData = 'F20';
  $areaCode = 'F20';
  $propertyGroup = 'All';
  $deleteOldData = true;
  $AxisLabels = [
    "1-2017",
    "2-2017",
    "3-2017",
    "4-2017",
    "5-2017",
    "6-2017",
    "7-2017",
    "8-2017",
    "9-2017",
    "10-2017",
    "11-2017",
    "12-2017",
    "1-2018",
    "2-2018",
    "3-2018",
    "4-2018",
    "5-2018",
    "6-2018",
    "7-2018",
    "8-2018",
    "9-2018",
    "10-2018",
    "11-2018",
    "12-2018",
    "1-2019",
    "2-2019",
    "3-2019",
    "4-2019",
    "5-2019",
    "6-2019",
    "7-2019",
    "8-2019",
    "9-2019",
    "10-2019",
    "11-2019",
    "12-2019",
    "1-2020",
    "2-2020",
    "3-2020",
    "4-2020",
    "5-2020",
    "6-2020"
  ];
  $data = [
    866000,
    871200,
    882900,
    906100,
    934700,
    960900,
    988000,
    998100,
    999600,
    1006000,
    1012700,
    1014600,
    1013500,
    1019500,
    1031500,
    1040900,
    1051000,
    1053600,
    1054800,
    1045000,
    1035600,
    1034500,
    1019500,
    1007800,
    998100,
    1003000,
    1006300,
    1003700,
    1002200,
    1004100,
    1004100,
    998500,
    1003000,
    1005200,
    1002200,
    998800,
    1007100,
    1012400,
    1028900,
    1041300,
    1043500,
    1047300
  ];
}
$monthYears = array();
foreach ($AxisLabels as $axisLabel) {
  $monthYear = date_create_from_format('d-m-Y', '1-' . $axisLabel);
  $monthYears[] = $monthYear->format('Y-m-d');
}
/* <div > <?php echo $title . $ID ?> </div> */
?> 
<?php

//$mysqli = new mysqli("localhost", "root", "root", "local");
$mysqli = new mysqli(PID_DB_HOST, PID_DB_USER, PID_DB_PASSWORD, PID_DB_NAME);

if (!$deleteOldData) {
  $strSql = "SELECT MAX(Date) AS max_date FROM wp_pid_market WHERE Neighborhood_ID='$areaCode' AND Property_Type = '$propertyGroup'";
  $mysqli->real_query($strSql);
  $res = $mysqli->use_result();

  while ($row = $res->fetch_assoc()) {
    $maxDate_areaCode_Property_type = $row['max_date'];
  }

  if (!$maxDate_areaCode_Property_type) {
    $maxDate_areaCode_Property_type = '2005-01-01';
  }
  // dump the repeated data
  foreach ($monthYears as $monthYear) {
    if ($monthYear <= $maxDate_areaCode_Property_type) {
      array_shift($monthYears);
      array_shift($data);
    }
  }
} else {
  // Have to delete old data first
  $deleteStartDate = $monthYears[0];
  $strSql = "DELETE FROM wp_pid_market WHERE Neighborhood_ID='$areaCode' AND Property_Type = '$propertyGroup' AND `Date` >= '$deleteStartDate'";
  $mysqli->real_query($strSql);
  $msg = "Records Delete: $mysqli->affected_rows;";
}
$thread = $mysqli->thread_id;
$mysqli->kill($thread);
$mysqli->close();

if (count($monthYears) == 0) {
  echo "No Stats Data To Insert INTO mySQL";
  exit();
}

require_once('pdoConn.php');

$sql = "INSERT INTO wp_pid_market
            (
            Date, 
            Year,
            Neighborhood_ID,
            Property_Type,
            HPI,
            Ave_Sales_Price,
            Med_Sales_Price
) 
            VALUES (?,?,?,?,?,?,?)";
$stmt_insert_stats = $pdo->prepare($sql);

try {
  $pdo->beginTransaction();
  foreach ($monthYears as $monthYear) {
    $Year = substr($monthYear, 0, 4);
    $HPI = array_shift($data);
    if ($HPI > 0) {
      $stmt_insert_stats->execute(
        array(
          $monthYear,
          $Year,
          $areaCode,
          $propertyGroup,
          $HPI,
          100, // 1 for test
          100
        )
      );
    }
  }
  $pdo->commit();
  echo json_encode('Stats Inserted to DB!');
} catch (Exception $e) {
  if ($e->errorInfo[1] == 1062) {
    // duplicate entry, do something else

  } else {
    // an error other than duplicate entry occurred
    $pdo->rollback();
    echo '["PDO error"]';
    throw $e;
  }
}
$stmt_insert_stats = null;
$pdo = null;


?>

