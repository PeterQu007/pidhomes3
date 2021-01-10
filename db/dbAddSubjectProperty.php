<?php
if (isset($_POST['cmaID'])) {
  $cma_id = $_POST["cmaID"];
} else {
  $cma_id = "36";
}

if (isset($_POST['cmaType'])) {
  $cma_type = $_POST["cmaType"];
} else {
  $cma_type = "CMA";
}

if (isset($_POST["address"])) {
  $address = $_POST["address"];
} else {
  $address = "7813 Garfield Dr";
}

if (isset($_POST["unitNo"])) {
  $unitNo = $_POST["unitNo"];
} else {
  $unitNo = "";
}
if (isset($_POST["age"])) {
  $age = $_POST["age"];
} else {
  $age = 35;
}

if (isset($_POST["landSize"])) {
  $landSize = $_POST["landSize"];
} else {
  $landSize = "10000";
}

if (isset($_POST["floorArea"])) {
  $floorArea = $_POST["floorArea"];
} else {
  $floorArea = "4000";
}

if (isset($_POST["bcAssessImprove"])) {
  $bcAssessImprove = $_POST["bcAssessImprove"];
} else {
  $bcAssessImprove = 1200000;
}

if (isset($_POST["bcAssessLand"])) {
  $bcAssessLand = $_POST["bcAssessLand"];
} else {
  $bcAssessLand = 1300000;
}

if (isset($_POST["bcAssessTotal"])) {
  $bcAssessTotal = $_POST["bcAssessTotal"];
} else {
  $bcAssessTotal = 2500000;
}

if (isset($_POST["subjectHouseType"])) {
  $subjectHouseType = $_POST["subjectHouseType"];
  if (strpos("Detached", $subjectHouseType)) {
    $subjectHouseType = 'Detached';
  } else {
    $subjectHouseType = 'Attached';
  }
} else {
  $subjectHouseType = "Detached";
}

if (isset($_POST["subjectDwellingType"])) {
  $subjectDwellingType = $_POST["subjectDwellingType"];
  $subjectDwellingType = str_replace('Apartment/', '', $subjectDwellingType);
  switch ($subjectDwellingType) {
    case 'House/Single Family':
    case "Rowhouse":
    case "Row House (Non-Strata)":
    case "1/2 Duplex":
      break;
    case "Condo":
    case "Apartment":
    case "Townhouse":
      $landSize = null;
      break;
  }
} else {
  $subjectDwellingType = "Detached";
}

if (isset($_POST["maintenanceFee"])) {
  $maintenanceFee = $_POST["maintenanceFee"];
} else {
  $maintenanceFee = 200;
}

if (isset($_POST["city"])) {
  $city = $_POST["city"];
} else {
  $city = "Surrey";
}

if (isset($_POST["neighborhood"])) {
  $neighborhood = $_POST["neighborhood"];
} else {
  $neighborhood = "Guildford";
}

if (isset($_POST["listPrice"])) {
  $listPrice = $_POST["listPrice"];
} else {
  $listPrice = 900000;
}

if (isset($_POST["soldPrice"])) {
  $soldPrice = $_POST["soldPrice"];
} else {
  $soldPrice = 950000;
}

if (isset($_POST["bcaChange"])) {
  $bcaChange = $_POST["bcaChange"];
} else {
  $bcaChange = 0.05;
}

if (isset($_POST["updateSubject"])) {
  $updateSubject = $_POST["updateSubject"];
} else {
  $updateSubject = false;
}


$data = [
  'Subject_Address' => $address,
  'Unit_No' => $unitNo,
  'Age' => $age,
  'Land_Size' => (int)$landSize,
  'Floor_Area' => $floorArea,
  'BC_Assess_Land' => $bcAssessLand,
  'BC_Assess_Improve' => $bcAssessImprove,
  'BC_Assess_Total' => $bcAssessTotal,
  'Subject_Property_Type' => $subjectHouseType,
  'Maintenance_Fee' => $maintenanceFee,
  'City' => $city,
  'Neighborhood' => $neighborhood,
  'CMA_Action' => 1,
  'List_Price' => $listPrice,
  'Sold_Price' => $soldPrice,
  'BC_Assess_Change' => $bcaChange
];

print_r($data);

require_once(dirname(dirname(__FILE__)) . '/pid-wp-db-config.php');

$mysqli = new mysqli(PID_DB_HOST, PID_DB_USER, PID_DB_PASSWORD, PID_DB_NAME);

if ($updateSubject === 'true' ? true : false) {
  update_array($data, 'wp_pid_cma_subjects', $mysqli, " where id=$cma_id");
} else {
  $cma_id = store_array($data, 'wp_pid_cma_subjects', $mysqli);
}
$thread = $mysqli->thread_id;
$mysqli->kill($thread);
$mysqli->close();

// CREATE CMA POSt
// cma_id, post_title, post_content, custom fields, Dwelling type, Status, Sold Links
// property_city
// Excerpt:: cma report type(CMA, VPR)
define('WP_USE_THEMES', false);
require_once("../../../../wp-load.php");
$new_post_data = array(
  'post_title' => 'CMA' . $cma_id,
  'post_content' => $address,
  'post_excerpt' => $cma_type, // keep cma Type in the excerpt
  'post_status' => 'publish',
  'post_type' => 'cma',
  'meta_input' => array(
    'cma_id' => $cma_id,
    'dwelling_type' => $subjectDwellingType,
    'status' => ['Active', 'Sold'],
    'sold_link' => '/'
  )
);
$neighborhood_term = get_term_by('name', $neighborhood, 'property-city');
$tax_input = array(
  'property-city' => $neighborhood_term ? $neighborhood_term->term_id : 85
);

if ($updateSubject === 'true' ? true : false) {
  $post_id = get_cma_post_id_by_slug($cma_id);
  $new_post_data['ID'] = $post_id;
  update_cma_post($new_post_data, $tax_input);
  echo "cma post updated ";
} else {
  create_cma_post($new_post_data, $tax_input);
  echo "cma post created ";
}


die('end of script');

/////********** */ 
/////FUNCTIONS
function store_array(&$data, $table, $mysqli)
{
  $cols = implode(',', array_keys($data));
  foreach (array_values($data) as $value) {
    isset($vals) ? $vals .= ',' : $vals = '';
    $vals .= '\'' . $mysqli->real_escape_string($value) . '\'';
  }
  try {
    $mysqli->real_query('INSERT INTO ' . $table . ' (' . $cols . ') VALUES (' . $vals . ')');
    $latest_id =  mysqli_insert_id($mysqli);
  } catch (mysqli_sql_exception $e) {
    echo "Error Code <br>" . $e->getCode();
    echo "Error Message <br>" . $e->getMessage();
    echo "Strack Trace <br>" . nl2br($e->getTraceAsString());
  }
  return $latest_id;
}

function update_array(&$data, $table, $mysqli, $where)
{
  // $cols = implode(',', array_keys($data));
  foreach (array_keys($data) as $key) {
    isset($sets) ? $sets .= ',' : $sets = 'set ';
    $sets .= "$key='" . $mysqli->real_escape_string($data[$key]) . '\'';
  }
  try {
    $mysqli->real_query("UPDATE $table $sets $where");
  } catch (mysqli_sql_exception $e) {
    echo "Error Code <br>" . $e->getCode();
    echo "Error Message <br>" . $e->getMessage();
    echo "Stack Trace <br>" . nl2br($e->getTraceAsString());
  }
}

function create_cma_post($post_data, $tax_input)
{
  $wp_error = true;
  try {
    $post_id = wp_insert_post($post_data, $wp_error);
    if (is_wp_error($post_id)) {
      $errors = $post_id->get_error_messages();
      foreach ($errors as $error) {
        echo "- " . $error . "<br />";
      }
    } else {
      wp_set_object_terms($post_id, $tax_input['property-city'], 'property-city');
    }
  } catch (Exception $e) {
    echo $e;
  }
}

function update_cma_post($post_data, $tax_input)
{
  $wp_error = true;
  try {
    $post_id = wp_update_post($post_data, $wp_error);
    if (is_wp_error($post_id)) {
      $errors = $post_id->get_error_messages();
      foreach ($errors as $error) {
        echo "- " . $error . "<br />";
      }
    } else {
      $term_id = wp_set_object_terms($post_id, $tax_input['property-city'], 'property-city');
    }
  } catch (Exception $e) {
    echo $e;
  }
}

function get_cma_post_id_by_slug($cma_id)
{
  $the_slug = 'CMA' . $cma_id;
  $args = array(
    'name'        => $the_slug,
    'post_type'   => 'cma',
    'post_status' => 'publish',
    'numberposts' => 1
  );
  $cma_posts = get_posts($args);
  if ($cma_posts) {
    // echo 'ID on the first post found ' . $cma_posts[0]->ID;
    return $cma_posts[0]->ID;
  } else {
    return false;
  }
}
