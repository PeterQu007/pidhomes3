<?php
// https://pidrealty4.local/wp-content/themes/realhomes-child-3/db/dataAssessInfo.php
date_default_timezone_set("America/Vancouver");
$today = date("Y-m-d");
$assessInfos = array(
  [
    "assessID" => "000-439-371-2019", // pid + added year
    "landValue" => "$959,000.00",
    "improvementValue" => "$20,600.00",
    "totalValue" => "$979,600.00",
    "pid" => "000-439-371",
    "taxYear" => "2017",
    "address" => "10640 138 ST BC",
    "legal" => "",
    "taxRollNumber" => "2237725020", //
    "grossTaxes" => "$3,112.22",
    "planNum" => "NWP64471",
    "houseType" => "Detached",
    "lotSize" => '7794 SQUARE FEET',
    "bcaDataUpdateDate" => "01/11/2019",
    "bcaDescription" => "1 STY SFD - AFTER 1930 - FAIR",
    "floorArea" => 2100,
    "bcaFloorArea" => null,
    "remarks" => 'dev test',
    "addedDate" => date("Y-m-d", strtotime("+30 days"),),
    "update" => true
  ],
  [
    "assessID" => "000-439-321-2021", // pid + added year
    "landValue" => "$743,000.00",
    "improvementValue" => "$61,900.00",
    "totalValue" => "$804,900.00",
    "pid" => "000-439-321",
    "taxYear" => "2020",
    "address" => "10063 160 ST BC",
    "legal" => null,
    "taxRollNumber" => "1270899302", //
    "grossTaxes" => "$3,189.81",
    "planNum" => "NWP61218",
    "houseType" => "Attached",
    "lotSize" => '',
    "bcaDataUpdateDate" => "04/12/2021",
    "bcaDescription" => "",
    "floorArea" => 890,
    "bcaFloorArea" => null,
    "remarks" => 'dev test',
    "addedDate" => date("Y-m-d", strtotime("+30 days")),
    "update" => true
  ]
);

if (isset($_POST["assessInfos"])) {
  $assessInfos = $_POST["assessInfos"];
};

include_once('pdoConn.php');

function Search($assess_ID, $assesses)
{
  if (empty($assesses)) {
    return false;
  };
  $existed_Assess = false;
  foreach ($assesses as $assess) {
    $assess_Existed = array_search($assess_ID, $assess);
    if ($assess_Existed) {
      $existed_Assess = $assess; //send out the assess found matching with the strata_plan_id
      break;
    }
  }
  return $existed_Assess;
}

function trimSpaces($item)
{
  return trim($item);
}

function fetchFirstTwoWords($item)
{
  $item_Array = explode(' ', $item);
  $item_Array_To_Keep = array_slice($item_Array, 0, 2);
  $item = trim(implode(' ', $item_Array_To_Keep));
  return $item;
}

function mergeList($newList, $oldList)
{
  $newList = trim(strtoupper($newList));
  $oldList = trim(strtoupper($oldList));
  $newList_Array = json_decode($newList) != null ? json_decode($newList) : [];
  $oldList_Array = json_decode($oldList) != null ? json_decode($oldList) : [];
  $mergeList_Array = array_merge($newList_Array, $oldList_Array);
  $mergeList_Array = array_unique(array_map("trimSpaces", $mergeList_Array));
  sort($mergeList_Array);
  return json_encode($mergeList_Array);
}

function mergeList_ManagementCo($newList, $oldList)
{
  $newList = trim(strtoupper($newList));
  $oldList = trim(strtoupper($oldList));
  $newList_Array = json_decode($newList) != null ? json_decode($newList) : [];
  $oldList_Array = json_decode($oldList) != null ? json_decode($oldList) : [];
  $mergeList_Array = array_merge($newList_Array, $oldList_Array);
  $mergeList_Array = array_unique(array_map("trimSpaces", $mergeList_Array));
  sort($mergeList_Array);
  if (count($mergeList_Array) >= 3) {
    $mergeList_Array = array_map("trimSpaces", array_map("fetchFirstTwoWords", $mergeList_Array));
    $mergeList_Array = array_unique($mergeList_Array);
    sort($mergeList_Array);
  }
  return json_encode($mergeList_Array);
}

function moneyStr2float($moneyString)
{
  return floatval(preg_replace('/[^\d\.]+/', '', $moneyString));
}

$existing_assesses = array();

//read the assess from pid_assess by strata_Plan_IDs
$sql_existing_assesses = "SELECT * from wp_pid_assess WHERE assess_ID IN ";
$sql_existing_assesses_condition = "(";
foreach ($assessInfos as $assessInfo) {
  $sql_existing_assesses_condition .= "'" . $assessInfo["assessID"] . "',";
}
$sql_existing_assesses_condition = trim($sql_existing_assesses_condition, ",") . ")";
$sql_existing_assesses .= $sql_existing_assesses_condition;

//get assess records:
$stmt = $pdo->query($sql_existing_assesses);
while ($assess = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $existing_assesses[] = $assess;
  // var_dump($assess);
}
$stmt = null;

//update the existing assess
$sql_update_existing_assess =
  "UPDATE wp_pid_assess 
          SET Address =:Address,
              Land_Value =:Land_Value,
              Improvement_Value =:Improvement_Value, 
              Total_Value =:Total_Value,
              Gross_Taxes =:Gross_Taxes,
              Lot_Size =:Lot_Size,
              Added_Date =:Added_Date ,
              Floor_Area =:Floor_Area,
              BCA_Data_Update_Date =:BCA_Data_Update_Date
            WHERE Assess_ID =:Assess_ID";
try {
  $stmt_update_assess = $pdo->prepare($sql_update_existing_assess);
} catch (Exception $e) {
  throw $e;
};

//insert the new assess
$sql = "INSERT INTO wp_pid_assess
            (
              assess_ID,
              Land_Value,
              Improvement_Value, 
              Total_Value,
              PID,
              Tax_Year,
              Address,
              Legal,
              Tax_Roll_Number,
              Gross_Taxes,
              Plan_Num, 
              House_Type,
              Lot_Size,
              BCA_Data_Update_Date,
              BCA_Description,
              Floor_Area,
              BCA_Floor_Area,
              Remarks,
              Added_Date
            ) 
            VALUES (?,?,?,?,?,?,?,?,?,?,/*10*/?,?,?,?,?,?,?,?,?/*18*/)";
$stmt_insert_assess = $pdo->prepare($sql);

try {
  foreach ($assessInfos as $assess) {
    // $pdo->beginTransaction();
    $assessInfo = (object)$assess;
    $existed_Assess = Search($assessInfo->assessID, $existing_assesses);
    if ($existed_Assess == false) {
      $stmt_insert_assess->execute(
        array(
          $assessInfo->assessID,
          moneyStr2float($assessInfo->landValue),
          moneyStr2float($assessInfo->improvementValue),
          moneyStr2float($assessInfo->totalValue),
          $assessInfo->pid,
          $assessInfo->taxYear,
          $assessInfo->address,
          $assessInfo->legal,
          $assessInfo->taxRollNumber,
          moneyStr2float($assessInfo->grossTaxes),
          $assessInfo->planNum,
          $assessInfo->houseType,
          $assessInfo->lotSize,
          date('Y-M-d', strtotime($assessInfo->bcaDataUpdateDate)),
          $assessInfo->bcaDescription,
          $assessInfo->floorArea,
          $assessInfo->bcaFloorArea,
          "No Remarks",
          date("Y-m-d")
        )
      );
      // $pdo->commit();
    } elseif ($existed_Assess["added_date"] != null or $assessInfo->update == true) {
      $assess_update = array(
        "Address" => $assessInfo->address,
        "Land_Value" => moneyStr2float($assessInfo->landValue),
        "Improvement_Value" => moneyStr2float($assessInfo->improvementValue),
        "Total_Value" => moneyStr2float($assessInfo->totalValue),
        "Gross_Taxes" => moneyStr2float($assessInfo->grossTaxes),
        "Lot_Size" => $assessInfo->lotSize,
        "Added_Date" => $today,
        "Floor_Area" => $assessInfo->floorArea,
        "Assess_ID" => $assessInfo->assessID,
        "BCA_Data_Update_Date" => date('Y-m-d', strtotime($assessInfo->bcaDataUpdateDate)),
      );
      $stmt_update_assess->execute($assess_update);
      // $pdo->commit();
    }
  }
} catch (Exception $e) {
  $pdo->rollback();
  echo '["PDO error"]';
  throw $e;
}
$stmt_insert_assess = null;
$stmt_update_assess = null;
$pdo = null;

foreach ($assessInfos as $assess) {
  $assessInfo = (object)$assess;
  $ret = array(
    $assessInfo->landValue,
    $assessInfo->improvementValue,
    $assessInfo->totalValue,
    $assessInfo->pid,
    $assessInfo->taxYear,
    $assessInfo->address,
    $assessInfo->legal,
    $assessInfo->taxRollNumber,
    $assessInfo->grossTaxes,
    $assessInfo->planNum,
    $assessInfo->houseType,
    $assessInfo->lotSize,
    $assessInfo->bcaDataUpdateDate,
    $assessInfo->bcaDescription,
    $assessInfo->floorArea,
    $assessInfo->bcaFloorArea,
    "No Remarks"
  );
  $ret_array[] = $ret;
  // var_dump($ret);
  // sort($ret[16]);
  // var_dump($ret[16]);
  // sort($ret[17]);
  // var_dump($ret[17]);
  // var_dump($ret);
}
$return_arr = json_encode($ret_array);
// var_dump($return_arr);
echo $return_arr;
