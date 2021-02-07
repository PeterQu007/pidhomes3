<!--
  Market Stats Report Template File
  July 18 2020

  chart data is requested by ajax
  php builds the neighborhood codes needed for request chart data
-->

<?php

global $context, $_metabox, $env;

use PIDHomes\Metabox as Metabox;
use Timber\Timber;

use const PIDHomes\PID_CHART_YEARS;
use const PIDHomes\PID_CHART_YEARS_RANGE;

$display_social_share         = get_option('theme_display_social_share', 'true');
$inspiry_share_property_label = get_option('inspiry_share_property_label');
$inspiry_print_property_label = get_option('inspiry_print_property_label');
// All markets | City code | district code | Neighborhood Code
// Only archive page have property-city query var
// Single Post Page does not have property-city query var
$community = $env->location_term->slug;

$communityID = get_the_ID();
// Set Report Level
// Get chart parameters
// $years = get_query_var('y');
$years = $env->query_vars['years'];
if ($years[0] == '') {
  $years = explode(',', PID_CHART_YEARS);
}
// $months = get_query_var('mh');
$months = $env->query_vars['month'];
if ($months == '') {
  $months = 1;
}

//$property_type = strtolower(get_query_var('dwell'));
$property_type = strtolower($env->query_vars['property_type']);
if ($property_type == '') {
  $property_type = 'all';
}

//$chart_type = get_query_var('chart');
$chart_type = $env->query_vars['chart_type'];
if ($chart_type == '') {
  $chart_type = 'dollar';
}
if ($chart_type == 'perc') {
  $chart_type = 'percentage';
}

$post_lang = get_query_var('lang'); // cn: simplified chinese; hk: traditional chinese
$lang_set = pid_translate($post_lang, 2); // option 2 for market chart translation; 1 for market archive translation

$market = json_decode($args);
$marketReportLevel = $market->report_level;
$location = $market->location;
if (!isset($market->render_market)) {
  $market->render_market = false;
}

switch ($marketReportLevel) {
  case 0:
    $metabox = Metabox::get_gva_city_metabox();
    $chartCanvasID = "line_chart_1";
    break;
  case 1:
  case 2:
    $metabox = Metabox::get_city_district_metabox($community);
    $chartCanvasID = "line_chart_2";
    break;
  case 3:
  default:
    $metabox = Metabox::get_city_district_nbh_metabox_by($community);
    $chartCanvasID = "line_chart_3";
    break;
}

$neighborhood_code_string = '';
$neighborhood_code_query_string = '';
$neighborhood_codes = [];
$neighborhood_names = [];
foreach ($metabox as $meta) {
  if ($meta['get_chartdata']) {
    $neighborhood_code_string .= $meta['3'] . ",";
    $neighborhood_names[$meta['3']] = $meta['1'];
  }
}
$neighborhood_code_string = rtrim($neighborhood_code_string, ',');
set_query_var('nbh_filter_for_table', $neighborhood_code_string); // send filter string to archive-market.php
$neighborhood_codes = explode(',', $neighborhood_code_string);
// Build neighborhood codes as mysql query IN operator's requirement:
foreach ($neighborhood_codes as $code) {
  $neighborhood_code_query_string .= "'" . $code . "'" . ",";
}
$neighborhood_code_query_string = rtrim($neighborhood_code_query_string, ',');


global $wpdb;

$results = $wpdb->get_results("SELECT Neighborhood_Code, Neighborhood_Name
                                  FROM wp_pid_neighborhoods
                                  WHERE Neighborhood_Code IN (" . $neighborhood_code_query_string . ") ORDER BY Neighborhood_Name;
                                ");
$nbh_names = null;
foreach ($results as $nbh) {
  $nbh_code = trim($nbh->Neighborhood_Code);
  $nbh_names[@$nbh_code] = trim($nbh->Neighborhood_Name);
}
// Build the metabox title block
set_query_var('is_market', true);
set_query_var('nbh_codes', $neighborhood_code_string);
set_query_var('nbh_names', json_encode(isset($nbh_names) ? $nbh_names : array()));
set_query_var('chartCanvasID', $chartCanvasID);
// temperately added [.200910]
set_query_var('report-level', $marketReportLevel);
get_template_part('/pid-partials/content', 'metabox-ajax');

$market_context['pid_market_form'] = "pidMarketForm_$chartCanvasID";
$market_context['select_property_type'] = $lang_set['select_property_type'];

$property_typies = ['all', 'detached', 'townhouse', 'apartment'];
$_context = [];
foreach ($property_typies as $pt) {
  $_context["pid_property_radio_id"] = "pid_{$pt}_{$chartCanvasID}";
  $_context["pid_property_radio_name"] = "Property_Type_$chartCanvasID";
  $_context["pid_property_radio_value"] = ucfirst($pt);
  $_context["pid_property_radio_checked"] = $property_type == $pt ? "checked='checked'" : null;
  $_context["pid_label_innertext"] = $lang_set[$pt];
  $market_context["property_type_select_radios"][$pt] = $_context;
  $_context = [];
}

$market_context['pid_property_grouped_by_nbh'] = "pid_group_by_nbh_$chartCanvasID";
$market_context['pid_property_grouped_by_nbh_name'] = "Property_Type_$chartCanvasID";
$market_context['pid_property_grouped_by_nbh_innertext'] = $lang_set['groupbynbh'];
$market_context["pid_property_grouped_by_nbh_checked"] = $property_type == 'groupbynbh' ? "checked='checked'" : null;

$market_context['pid_market_form_time'] = "pidMarketForm_Time_$chartCanvasID";
$market_context['pid_market_form_time_year'] = $lang_set['select_year'];

$year_selects = PID_CHART_YEARS_RANGE; //[2017, 2018, 2019, 2020, 2021];
$_context = [];
foreach ($year_selects as $yr) {
  $_context["year_checkbox_id"] = "pid_{$yr}_{$chartCanvasID}";
  $_context["year_checkbox_name"] = "Stats_Year_$chartCanvasID";
  $_context["year_checkbox_value"] = "$yr";
  $_context["year_checkbox_checked"] = array_search($yr, $years) === false ? null : "checked='checked'";
  $market_context["year_selects"][$yr] = $_context;
  $_context = [];
}

$market_context['pid_market_form_start_month'] = "pid_start_month_$chartCanvasID";
$market_context['pid_market_form_month_label'] = $lang_set['select_month'];
$market_context['pid_market_form_month_name'] = "Stats_month_$chartCanvasID";
$_context = [];
for ($i = 1; $i <= 12; $i++) {
  $_context['month_value'] = "$i";
  $_context['month_selected'] = $months == $i ? 'selected' : null;
  $market_context['month_selects']["$i"] = $_context;
}

$market_context['pid_market_form_chart_type'] = "pidMarketForm_ChartType_$chartCanvasID";
$market_context['pid_market_form_ct_label'] = $lang_set['select_chart'];
$chart_types = ['dollar', 'percentage'];
$_context = [];
foreach ($chart_types as $ct) {
  $_context['pid_ct_radio_id'] = "pid_{$ct}_$chartCanvasID";
  $_context['pid_ct_radio_name'] = "Chart_Type_$chartCanvasID";
  $_context['pid_ct_radio_value'] = $ct;
  $_context['pid_ct_radio_selected'] = $chart_type == $ct ? "checked='checked'" : null;
  $_context['pid_ct_label'] = $lang_set["chart_$ct"];
  $market_context['chart_type_selects'][$ct] = $_context;
}

// share link for marketing
$isHttps =
  $_SERVER['HTTPS']
  ?? $_SERVER['REQUEST_SCHEME']
  ?? $_SERVER['HTTP_X_FORWARDED_PROTO']
  ?? null;

$isHttps =
  $isHttps && (strcasecmp('on', $isHttps) == 0
    || strcasecmp('https', $isHttps) == 0);

// $protocol = strtolower(current(explode('/', $_SERVER['SERVER_PROTOCOL'])));
$protocol = $isHttps ? 'https' : 'http';
$market_context['pid_share_link'] =
  "$protocol://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$market_context['chart_canvas_wrapper_id'] = "canvas_wrapper_$chartCanvasID";
$market_context['chart_canvas_id'] = $chartCanvasID;
global $wp;
$market_context['fb_share_and_like_link'] = home_url($wp->request);
$market_context['social_share_label'] = $inspiry_print_property_label;
$section_title = "GVA Chart"; // temperary setting for get rid of undefined variable
if (wp_is_mobile()) {
  $market_context['social_is_mobile'] = esc_html('mobile');
}
$market_context['social_section_title'] = $section_title;

$market_context['canvas_drawing_container']  = "canvas_drawing_$chartCanvasID";

$context['market'] = $market_context;
$context['market']['metabox'] = $_metabox;
unset($market_context);

if ($market->render_market) {
  $_market = [];
  $_market = $context['market'];
?>
  <section class="rh_section rh_section--flex rh_wrap--padding rh_wrap--topPadding pid_wrap--padding">
    <div class='rh_page__main' style="width:70%">
      <?php
      Timber::render('partials-twig/components/market-stats.twig', $_market);
      ?>
    </div>
    <div class="rh_page rh_page_sidebar">
      <?php
      get_sidebar('default');
      ?>
    </div>
  </section>
<?php
}
