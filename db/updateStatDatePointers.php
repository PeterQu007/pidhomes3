<?php

/**
 * UPDATE THE STAT DATE POINTERS OF WP_PID_STATS_DATE_POINTER
 * SET THE CURRENT MONTH 
 */
require_once(dirname(dirname(__FILE__)) . '/pid-wp-db-config.php');

// if JS send data by Fetch POST, run a decoding line
$_POST = json_decode(file_get_contents('php://input'), true);

// Prepare the default data in case of NULL $_POST
$month_ini = new DateTime("first day of last month"); // SET to last month as current STAT DATA MONTH
$current_date_pointer = $month_ini->format('Y-m-d'); // 2021-05-01
$current_year_pointer = date("Y", strtotime($current_date_pointer));
$current_month_pointer = date("m", strtotime($current_date_pointer));

$previous_date_pointer = date("Y-m-d", strtotime("-2 Months"));
$previous_year_pointer = date("Y", strtotime($previous_date_pointer));
$previous_month_pointer = date("m", strtotime($previous_date_pointer));

$start_date_pointer = date("Y-m-d", strtotime("-13 Months"));
$start_year_pointer = date("Y", strtotime($start_date_pointer));
$start_month_pointer = date("m", strtotime($start_date_pointer));

if (isset($_POST['datePointer1'])) {
  $datePointer1 = $_POST['datePointer1']; // eg. 2021-05-01
  $date_pointer_1 = new DateTime($datePointer1);
  $current_date_pointer = $date_pointer_1->format('Y-m-d');
  $current_year_pointer = date("Y", strtotime($current_date_pointer));
  $current_month_pointer = date("m", strtotime($current_date_pointer));
}

if (isset($_POST['datePointer2'])) {
  $datePointer2 = $_POST['datePointer2']; // eg. 2021
  $date_pointer_2 = new DateTime($datePointer2);
  $previous_date_pointer = $date_pointer_2->format('Y-m-d');
  $previous_year_pointer = date("Y", strtotime($previous_date_pointer));
  $previous_month_pointer = date("m", strtotime($previous_date_pointer));
}

if (isset($_POST['datePointer3'])) {
  $datePointer3 = $_POST['datePointer3']; // eg. 05
  $date_pointer_3 = new DateTime($datePointer3);
  $start_date_pointer = $date_pointer_3->format('Y-m-d');
  $start_year_pointer = date("Y", strtotime($start_date_pointer));
  $start_month_pointer = date("m", strtotime($start_date_pointer));
}


?> 
<?php

//$mysqli = new mysqli("localhost", "root", "root", "local");
$mysqli = new mysqli(PID_DB_HOST, PID_DB_USER, PID_DB_PASSWORD, PID_DB_NAME);
// UPDATE SQL for pointer id = 1, the current_pointer
// Do updates in one query
// use INSERT ... ON DUPLICATE KEY UPDATE
if ($mysqli->server_version > 80000) {
  // MYSQL 8.0
  $strSql = "INSERT INTO wp_pid_stats_date_pointer (pointer_id, date_pointer, month_pointer, year_pointer) 
                    VALUES (1,?,?,?), (2,?,?,?), (3,?,?,?) AS INSERT_DATE_POINTERS
                     ON DUPLICATE KEY UPDATE date_pointer = INSERT_DATE_POINTERS.date_pointer, 
                     month_pointer = INSERT_DATE_POINTERS.month_pointer,
                     year_pointer = INSERT_DATE_POINTERS.year_pointer";
} else {
  // MYSQL 5.6 / 5.7
  $strSql = "INSERT INTO wp_pid_stats_date_pointer (pointer_id, date_pointer, month_pointer, year_pointer) 
                  VALUES (1,?,?,?), (2,?,?,?), (3,?,?,?) 
                   ON DUPLICATE KEY UPDATE date_pointer = VALUES(date_pointer), 
                   month_pointer = VALUES(month_pointer),
                   year_pointer = VALUES(year_pointer)";
}

// prepare the statement                   
$stmt = $mysqli->prepare($strSql);
if ($stmt === false) {
  // trigger_error($mysqli->error, E_USER_ERROR);
  echo $mysqli->error;
  exit();
}
// binding the parameters
$stmt->bind_param('sssssssss', $current_date_pointer, $current_month_pointer, $current_year_pointer, $previous_date_pointer, $previous_month_pointer, $previous_year_pointer, $start_date_pointer, $start_month_pointer, $start_year_pointer);
// execute the statement
$status = $stmt->execute();

if ($status === false) {
  trigger_error($stmt->error, E_USER_ERROR);
  exit();
} else {
  echo json_encode("Rows Affected: $stmt->affected_rows | MySQL Version: $mysqli->server_version | SQL Query: $strSql");
}

$stmt->close();
$thread = $mysqli->thread_id;
$mysqli->kill($thread);
$mysqli->close();

?>

