<?php

/**
 * UPDATE THE STAT DATE POINTERS OF WP_PID_STATS_DATE_POINTER
 * SET THE CURRENT MONTH 
 */
require_once(dirname(dirname(__FILE__)) . '/pid-wp-db-config.php');

// if JS send data by Fetch POST, run a decoding line
$_POST = json_decode(file_get_contents('php://input'), true);
$month_ini = new DateTime("first day of last month");
$date_pointer = $month_ini->format('Y-m-d'); // 2021-05-01
$year_pointer = date("Y", strtotime($date_pointer));
$month_pointer = date("m", strtotime($date_pointer));

if (isset($_POST['date_pointer'])) {
  $date_pointer = $_POST['date_pointer']; // eg. 2021-05-01
}

if (isset($_POST['year_pointer'])) {
  $year_pointer = $_POST['year_pointer']; // eg. 2021
}

if (isset($_POST['month_pointer'])) {
  $month_pointer = $_POST['month_pointer']; // eg. 05
}

$previous_date_pointer = date("y:F:d", strtotime("-2 Months"));
$previous_year_pointer = date("Y", strtotime($previous_date_pointer));
$previous_month_pointer = date("m", strtotime($previous_date_pointer));

$start_date_pointer = date("y:F:d", strtotime("-12 Months"));
$start_year_pointer = date("Y", strtotime($start_date_pointer));
$start_month_pointer = date("m", strtotime($start_date_pointer));

?> 
<?php

//$mysqli = new mysqli("localhost", "root", "root", "local");
$mysqli = new mysqli(PID_DB_HOST, PID_DB_USER, PID_DB_PASSWORD, PID_DB_NAME);
// UPDATE SQL for pointer id = 1, the current_pointer
// Do updates in one query
// use INSERT ... ON DUPLICATE KEY UPDATE
// $strSql = `INSERT INTO wp_pid_stats_date_pointer (pointer_id, date_pointer, month_pointer, year_pointer) 
//                   VALUES (1,$date_pointer,$month_pointer,$year_pointer), (2,$previous_date_pointer,$previous_month_pointer,$previous_year_pointer), (3,$start_date_pointer,$start_month_pointer,$start_year_pointer) AS INSERT_DATE_POINTERS
//                   ON DUPLICATE KEY UPDATE date_pointer = INSERT_DATE_POINTERS.data_pointer, 
//                   month_pointer = INSERT_DATE_POINTERS.month_pointer,
//                   year_pointer = INSERT_DATE_POINTERS.year_pointer`;
$strSql = `INSERT INTO wp_pid_stats_date_pointer (pointer_id, date_pointer, month_pointer, year_pointer) 
                  VALUES (1,?,?,?), (2,?,?,?), (3,?,?,?) AS INSERT_DATE_POINTERS
                  ON DUPLICATE KEY UPDATE date_pointer = INSERT_DATE_POINTERS.data_pointer, 
                  month_pointer = INSERT_DATE_POINTERS.month_pointer,
                  year_pointer = INSERT_DATE_POINTERS.year_pointer`;
$stmt = $mysqli->prepare($strSql);
if ($stmt === false) {
  // trigger_error($mysqli->error, E_USER_ERROR);
  echo $mysqli->error;
  exit();
}

$stmt->bind_param('sssssssss', $date_pointer, $month_pointer, $year_pointer, $previous_date_pointer, $previous_month_pointer, $previous_year_pointer, $start_date_pointer, $start_month_pointer, $start_year_pointer);

$status = $stmt->execute();

if ($status === false) {
  trigger_error($stmt->error, E_USER_ERROR);
  exit();
} else {
  echo json_encode("Rows Affected: $stmt->affected_rows");
}

$stmt->close();
$thread = $mysqli->thread_id;
$mysqli->kill($thread);
$mysqli->close();

?>

