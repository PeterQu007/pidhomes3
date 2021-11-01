<?php
// https://pidrealty.local/wp-content/themes/pidhomes-phaseI/db/dataComplexInfo.php
// https://pidrealty4.local/wp-content/themes/realhomes-child-3/db/dataComplexInfo.php
date_default_timezone_set("America/Vancouver");
$today = date("Y-m-d");
$complexInfos = array(
  [
    "DwellingType" => "Apartment/Condo",
    "PropertyType" => "Residential Attached",
    "Province" => "BC",
    "YearBuilt" => "1982",
    "Address" => "4373 HALIFAX ST",
    "BylawRentalRestriction" => " ",
    "City" => "Burnaby",
    "CityDistrict" => "Burnaby North",
    "ComplexName" => "Brent Garden", //
    "Neighborhood" => "Brentwood Park",
    "NeighborhoodCode" => "",
    "Postcode" => "V5C 5Z2",
    "Storeys" => '["24"]',
    "StrataPlan" => "NWS2036",
    "StrataPlanID" => "NWS2036-4373-HALIFAX-ST",
    "TitleToLand" => "Freehold Strata",
    "Units" => '["334"]',
    "Amenities" => '["Gym","Club House","Storage", "Sauna;Indoor"]',
    "BylawPetRestriction" => "",
    "BylawAgeRestriction" => "",
    "BylawRestriction" => "",
    "Construction" => "",
    "FloodPlain" => "",
    "MaintenanceFeeInclude" => "",
    "ManagementCoName" => "",
    "ManagementCoPhone" => "",
    "Parking" => "",
    "RainScreen" => "",
    "Region" => "",
    "SiteInfluences" => "",
    "StrataFeePSF" => "",
    "Zoning" => '["CD","CDMFS"]',
    "Audited" => false,
    "AddedDate" => date("Y-m-d", strtotime("+30 days"))
  ],
  [
    "DwellingType" => "Apartment/Condo",
    "PropertyType" => "Residential Attached",
    "Province" => "BC",
    "YearBuilt" => "1983",
    "Address" => "2041 BELLWOOD AV",
    "BylawRentalRestriction" => " ",
    "City" => "Burnaby",
    "CityDistrict" => "Burnaby North",
    "ComplexName" => "Anola Place", //
    "Neighborhood" => "Brentwood Park",
    "NeighborhoodCode" => "",
    "Postcode" => "V5B 4V5",
    "Storeys" => '[" 1"]',
    "StrataPlan" => "NWS2020",
    "StrataPlanID" => "NWS2020-2041-BELLWOOD-AV",
    "TitleToLand" => "Freehold Strata",
    "Units" => '[" 50"]',
    "Amenities" => '["Pool;Indoor","Tennis Court","Gym","Elevator"]',
    "BylawPetRestriction" => "",
    "BylawAgeRestriction" => "",
    "BylawRestriction" => "",
    "Construction" => "",
    "FloodPlain" => "",
    "MaintenanceFeeInclude" => "",
    "ManagementCoName" => '["Quaye", "Quaye Management Corp", "Quaye MNGT Group", "Quaye Property MNGT LMT"]',
    "ManagementCoPhone" => "",
    "Parking" => "",
    "RainScreen" => "",
    "Region" => "",
    "SiteInfluences" => "",
    "StrataFeePSF" => "",
    "Zoning" => '["Highrise","CD","MFS"]',
    "Audited" => false,
    "AddedDate" => date("Y-m-d", strtotime("+30 days"))
  ]
);

if (isset($_POST["complexInfos"])) {
  $complexInfos = $_POST["complexInfos"];
}

include_once('pdoConn.php');

function Search($Strata_Plan_ID, $complexes)
{
  if (empty($complexes)) {
    return false;
  };
  $existed_Complex = false;
  foreach ($complexes as $complex) {
    $complex_Existed = array_search($Strata_Plan_ID, $complex);
    if ($complex_Existed) {
      $existed_Complex = $complex; //send out the complex found matching with the strata_plan_id
      break;
    }
  }
  return $existed_Complex;
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

$existing_complexes = array();

//read the complex from pid_complex by strata_Plan_IDs
$sql_existing_complexes = "SELECT * from wp_pid_complex WHERE Strata_Plan_ID IN ";
$sql_existing_complexes_condition = "(";
foreach ($complexInfos as $complexInfo) {
  $sql_existing_complexes_condition .= "'" . $complexInfo["StrataPlanID"] . "',";
}
$sql_existing_complexes_condition = trim($sql_existing_complexes_condition, ",") . ")";
$sql_existing_complexes .= $sql_existing_complexes_condition;

//get complex records:
$stmt = $pdo->query($sql_existing_complexes);
while ($complex = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $existing_complexes[] = $complex;
  // var_dump($complex);
}
$stmt = null;

//update the existing complex
$sql_update_existing_complex =
  "UPDATE wp_pid_complex 
          SET Complex_Name =:Complex_Name,
              Amenities =:Amenities, 
              Bylaw_Rental_Restriction =:Bylaw_Rental_Restriction,
              Storeys =:Storeys,
              Units =:Units,
              Bylaw_Pet_Restriction =:Bylaw_Pet_Restriction ,
              Bylaw_Age_Restriction =:Bylaw_Age_Restriction ,
              Bylaw_Restriction =:Bylaw_Restriction ,
              Construction =:Construction ,
              Flood_Plain =:Flood_Plain ,
              Maintenance_Fee_Include =:Maintenance_Fee_Include ,
              Management_Co_Name =:Management_Co_Name ,
              Management_Co_Phone =:Management_Co_Phone ,
              Parking_Type =:Parking_Type ,
              Rain_Screen =:Rain_Screen ,
              Site_Influences =:Site_Influences ,
              Strata_Fee_PSF =:Strata_Fee_PSF ,
              Zoning =:Zoning, 
              Added_Date = :Added_Date    
            WHERE Strata_Plan_ID =:Strata_Plan_ID";
try {
  $stmt_update_complex = $pdo->prepare($sql_update_existing_complex);
} catch (Exception $e) {
  //throw $e;
};

//insert the new complex
$sql = "INSERT INTO wp_pid_complex
            (
            Dwelling_Type, 
            Property_Type,
            Province,
            Year_Built,
            Address,
            Bylaw_Rental_Restriction,
            City,
            City_District,
            Complex_Name,
            Neighborhood,
            Neighborhood_Code,
            Postcode,
            Storeys,
            Strata_Plan_Num,
            Strata_Plan_ID,
            Title_To_Land,
            Units,
            Amenities,
            Bylaw_Pet_Restriction,
            Bylaw_Age_Restriction,
            Bylaw_Restriction,
            Construction,
            Flood_Plain,
            Maintenance_Fee_Include,
            Management_Co_Name,
            Management_Co_Phone,
            Parking_Type,
            Rain_Screen,
            Region,
            Site_Influences,
            Strata_Fee_PSF,
            Zoning,     /*32*/
            Added_Date     ) 
            VALUES (?,?,?,?,?,?,?,?,?,?,/*10*/?,?,?,?,?,?,?,?,?,?,/*20*/?,?,?,?,?,?,?,?,?,?,/*30*/?,?,?)";
$stmt_insert_complex = $pdo->prepare($sql);

try {
  foreach ($complexInfos as $complex) {
    // $pdo->beginTransaction();
    $complexInfo = (object)$complex;
    $existed_Complex = Search($complexInfo->StrataPlanID, $existing_complexes);
    if ($existed_Complex == false) {
      $stmt_insert_complex->execute(
        array(
          $complexInfo->DwellingType,
          $complexInfo->PropertyType,
          $complexInfo->Province,
          $complexInfo->YearBuilt,
          $complexInfo->Address,
          $complexInfo->BylawRentalRestriction,
          $complexInfo->City,
          $complexInfo->CityDistrict,
          $complexInfo->ComplexName,
          $complexInfo->Neighborhood,
          $complexInfo->NeighborhoodCode,
          $complexInfo->Postcode,
          $complexInfo->Storeys,
          $complexInfo->StrataPlan,
          $complexInfo->StrataPlanID,
          $complexInfo->TitleToLand,
          $complexInfo->Units,
          $complexInfo->Amenities,
          $complexInfo->BylawPetRestriction,
          $complexInfo->BylawAgeRestriction,
          $complexInfo->BylawRentalRestriction,
          $complexInfo->Construction,
          $complexInfo->FloodPlain,
          $complexInfo->MaintenanceFeeInclude,
          $complexInfo->ManagementCoName,
          $complexInfo->ManagementCoPhone,
          $complexInfo->Parking,
          $complexInfo->RainScreen,
          $complexInfo->Region,
          $complexInfo->SiteInfluences,
          $complexInfo->StrataFeePSF,
          $complexInfo->Zoning,
          date("Y-m-d")
        )
      );
      // $pdo->commit();
    } elseif ($existed_Complex["Added_Date"] != $complexInfo->AddedDate or $existed_Complex["Complex_Name"] == "**") {
      $complex_update = array(
        "Complex_Name" => $complexInfo->ComplexName, /* ComplexName Could be Changed or be Updated*/
        "Amenities" => mergeList($existed_Complex["Amenities"], $complexInfo->Amenities),
        "Bylaw_Rental_Restriction" => mergeList($existed_Complex["Bylaw_Rental_Restriction"], $complexInfo->BylawRentalRestriction),
        "Storeys" => mergeList($existed_Complex["Storeys"], $complexInfo->Storeys),
        "Units" => mergeList($existed_Complex["Units"], $complexInfo->Units),
        "Bylaw_Pet_Restriction" => mergeList($existed_Complex["Bylaw_Pet_Restriction"], $complexInfo->BylawPetRestriction),
        "Bylaw_Age_Restriction" => mergeList($existed_Complex["Bylaw_Age_Restriction"], $complexInfo->BylawAgeRestriction),
        "Bylaw_Restriction" => mergeList($existed_Complex["Bylaw_Restriction"], $complexInfo->BylawRestriction),
        "Construction" => mergeList($existed_Complex["Construction"], $complexInfo->Construction),
        "Flood_Plain" => mergeList($existed_Complex["Flood_Plain"], $complexInfo->FloodPlain),
        "Maintenance_Fee_Include" => mergeList($existed_Complex["Maintenance_Fee_Include"], $complexInfo->MaintenanceFeeInclude),
        "Management_Co_Name" => mergeList_ManagementCo($existed_Complex["Management_Co_Name"], $complexInfo->ManagementCoName),
        "Management_Co_Phone" => mergeList($existed_Complex["Management_Co_Phone"], $complexInfo->ManagementCoPhone),
        "Parking_Type" => mergeList($existed_Complex["Parking_Type"], $complexInfo->Parking),
        "Rain_Screen" => mergeList($existed_Complex["Rain_Screen"], $complexInfo->RainScreen),
        "Site_Influences" => mergeList($existed_Complex["Site_Influences"], $complexInfo->SiteInfluences),
        "Strata_Fee_PSF" => mergeList($existed_Complex["Strata_Fee_PSF"], $complexInfo->StrataFeePSF),
        "Zoning" => mergeList($existed_Complex["Zoning"], $complexInfo->Zoning),
        "Strata_Plan_ID" => $complexInfo->StrataPlanID,
        "Added_Date" => $today
      );
      $stmt_update_complex->execute($complex_update);
      // $pdo->commit();
    }
  }
} catch (Exception $e) {
  $pdo->rollback();
  echo '["PDO error"]';
  throw $e;
}
$stmt_insert_complex = null;
$stmt_update_complex = null;
$pdo = null;

foreach ($complexInfos as $complex) {
  $complexInfo = (object)$complex;
  $ret = array(
    $complexInfo->DwellingType,
    $complexInfo->PropertyType,
    $complexInfo->Province,
    $complexInfo->YearBuilt,
    $complexInfo->Address,
    $complexInfo->BylawRentalRestriction,
    $complexInfo->City,
    $complexInfo->CityDistrict,
    $complexInfo->ComplexName,
    $complexInfo->Neighborhood,
    $complexInfo->Postcode,
    (int)$complexInfo->Storeys,
    $complexInfo->StrataPlan,
    $complexInfo->StrataPlanID,
    $complexInfo->TitleToLand,
    (int)$complexInfo->Units,
    json_decode($complexInfo->Amenities, true),
    json_decode($complexInfo->Zoning, true)
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
