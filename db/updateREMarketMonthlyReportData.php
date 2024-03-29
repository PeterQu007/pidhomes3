<?php

/**
 * UPDATE THE STAT DATE POINTERS OF WP_PID_STATS_DATE_POINTER
 * SET THE CURRENT MONTH 
 */
require_once(dirname(dirname(__FILE__)) . '/pid-wp-db-config.php');
$report_dwelling_type_converter = array(
    "Single House" => "Detached",
    "Town House" => "TownHouse",
    "Apartment" => "Apartment",
    "All Types" => "All"
);

// if JS send data by Fetch POST, run a decoding line
$_POST = json_decode(file_get_contents('php://input'), true);

// Prepare the default data in case of NULL $_POST
$month_ini = new DateTime("first day of last month"); // SET to last month as current STAT DATA MONTH
$current_date_pointer = $month_ini->format('Y-m-d'); // 2021-05-01

$previous_first_date = strtotime('first day of this month', strtotime('-2 Months'));
$previous_date_pointer = date("Y-m-d", $previous_first_date);

$start_first_dater =
    strtotime('first day of this month', strtotime('-13 Months'));
$start_date_pointer = date("Y-m-d", $start_first_dater);

// Add Dwelling Type
$report_dwelling_type = "Townhouse";


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

if (isset($_POST['dwellingType'])) {
    $report_dwelling_type = $report_dwelling_type_converter[$_POST['dwellingType']];
}


?> 
<?php

//$mysqli = new mysqli("localhost", "root", "root", "local");
$mysqli = new mysqli(PID_DB_HOST, PID_DB_USER, PID_DB_PASSWORD, PID_DB_NAME);
// UPDATE SQL for pointer id = 1, the current_pointer
// Do updates in one query
// use INSERT ... ON DUPLICATE KEY UPDATE
// Depends on the $php_major_version, use different statements

$strSql = " INSERT INTO wp_pid_market_monthly_report (`Date`, Neighborhood_ID, cur_month_data, prev_month_data, monthly_change, monthly_change_perc, start_month_data, `12_monthly_change`, `12_month_change_perc`, property_type, Data_Type )
  SELECT * FROM
  (SELECT 
		? AS `Date`,
        `m`.`Neighborhood_ID` AS `Neighborhood_ID`,
        MAX((CASE
            WHEN (`m`.`Date` = ?) THEN `m`.`$report_dwelling_type`
            ELSE NULL
        END)) AS `Current HPI`,
        MAX((CASE
            WHEN (`m`.`Date` = ?) THEN `m`.`$report_dwelling_type`
            ELSE NULL
        END)) AS `Last Month HPI`,
        (MAX((CASE
            WHEN (`m`.`Date` = ?) THEN `m`.`$report_dwelling_type`
            ELSE NULL
        END)) - MAX((CASE
            WHEN (`m`.`Date` = ?) THEN `m`.`$report_dwelling_type`
            ELSE NULL
        END))) AS `This Month Change$`,
        ((MAX((CASE
            WHEN (`m`.`Date` = ?) THEN `m`.`$report_dwelling_type`
            ELSE NULL
        END)) - MAX((CASE
            WHEN (`m`.`Date` = ?) THEN `m`.`$report_dwelling_type`
            ELSE NULL
        END))) / MAX((CASE
            WHEN (`m`.`Date` = ?) THEN `m`.`$report_dwelling_type`
            ELSE NULL
        END))) AS `This Month Change%`,
        MAX((CASE
            WHEN (`m`.`Date` =?) THEN `m`.`$report_dwelling_type`
            ELSE NULL
        END)) AS `January HPI`,
        (MAX((CASE
            WHEN (`m`.`Date` = ?) THEN `m`.`$report_dwelling_type`
            ELSE NULL
        END)) - MAX((CASE
            WHEN (`m`.`Date` = ?) THEN `m`.`$report_dwelling_type`
            ELSE NULL
        END))) AS `Change`,
        ((MAX((CASE
            WHEN (`m`.`Date` =?) THEN `m`.`$report_dwelling_type`
            ELSE NULL
        END)) - MAX((CASE
            WHEN (`m`.`Date` = ?) THEN `m`.`$report_dwelling_type`
            ELSE NULL
        END))) / MAX((CASE
            WHEN (`m`.`Date` = ?) THEN `m`.`$report_dwelling_type`
            ELSE NULL
        END))) AS `Change%`,
        '$report_dwelling_type' AS `Property_Type`,
        'HPI' AS `Data_Type`
    FROM
        `wp_pid_market_pivot` `m`
    WHERE
        (((`m`.`Date` = ?)
            OR (`m`.`Date` = ?)
            OR (`m`.`Date` = ?))
            AND (`m`.`Data_Type` = 'HPI')
            AND (`m`.`$report_dwelling_type` > 100))
    GROUP BY `m`.`Neighborhood_ID` ) As INSERT_MONTHLY_DATA
    ON DUPLICATE KEY UPDATE  
CUR_MONTH_DATA = INSERT_MONTHLY_DATA.`CURRENT HPI`,
PREV_MONTH_DATA = INSERT_MONTHLY_DATA.`LAST MONTH HPI`,
START_MONTH_DATA = INSERT_MONTHLY_DATA.`JANUARY HPI`,
MONTHLY_CHANGE = INSERT_MONTHLY_DATA.`THIS MONTH CHANGE$`,
MONTHLY_CHANGE_PERC = INSERT_MONTHLY_DATA.`THIS MONTH CHANGE%`,
`12_MONTHLY_CHANGE`=INSERT_MONTHLY_DATA.`CHANGE`,
`12_MONTH_CHANGE_PERC` = INSERT_MONTHLY_DATA.`CHANGE%`
;";

// prepare the statement                   
$stmt = $mysqli->prepare($strSql);
if ($stmt === false) {
    // trigger_error($mysqli->error, E_USER_ERROR);
    echo $mysqli->error;
    exit();
}
// binding the parameters
$stmt->bind_param('sssssssssssssssss', $current_date_pointer, $current_date_pointer, $previous_date_pointer, $current_date_pointer, $previous_date_pointer, $current_date_pointer, $previous_date_pointer, $previous_date_pointer, $start_date_pointer, $current_date_pointer, $start_date_pointer, $current_date_pointer, $start_date_pointer, $start_date_pointer, $start_date_pointer, $previous_date_pointer, $current_date_pointer);
// execute the statement
$status = $stmt->execute();

if ($status === false) {
    echo `error`;
    trigger_error($stmt->error, E_USER_ERROR);
    exit();
} else {
    $return_msg = ("$report_dwelling_type | $current_date_pointer | $previous_date_pointer | $start_date_pointer - Rows Affected: $stmt->affected_rows - MySQL Version: $mysqli->server_version | Query: $strSql");
}

// Update the total neighborhoods number
$strSql_update = "update wp_pid_market_monthly_report set total_nbh = wp_pid_counter_nbh_by_type('$current_date_pointer', '$report_dwelling_type') where date = '$current_date_pointer' and property_type = '$report_dwelling_type';";

$stmt2 = $mysqli->prepare($strSql_update);
if ($stmt2 === false) {
    // trigger_error($mysqli->error, E_USER_ERROR);
    echo PHP_EOL;
    echo $mysqli->error;
    echo PHP_EOL;
    echo $strSql_update;
    exit();
}

$status2 = $stmt2->execute();

if ($status2 === false) {
    echo `error`;
    trigger_error($stmt2->error, E_USER_ERROR);
    exit();
} else {
    $return_msg2 = ("$report_dwelling_type | $current_date_pointer | $previous_date_pointer | $start_date_pointer - Rows Affected: $stmt2->affected_rows - MySQL Version: $mysqli->server_version | Query: $strSql_update");
}

echo json_encode($return_msg . PHP_EOL . $return_msg2);

$stmt->close();
$stmt2->close();
$thread = $mysqli->thread_id;
$mysqli->kill($thread);
$mysqli->close();

?>

