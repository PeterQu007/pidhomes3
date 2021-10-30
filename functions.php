<?php

/**
 * PIDHomes REAL HOMES CHILD THEME 
 * MAIN FUNCTION FILE
 * @version
 * @var functions.php
 * @file 
 */

/**
 * @version [.200830] Add Composer / Cosmopolitan for Locale Settings
 * @var RECODING locale settings
 * @var INSTALL composer require salarmehr/cosmopolitan
 * @link https://github.com/salarmehr/cosmopolitan
 * 
 * @version [.200831] Add Text Domain Features
 * @var HOOK after_setup_theme
 * @link https://developer.wordpress.org/themes/advanced-topics/child-themes/ [internationalization]
 * 
 * @var AJAX_CALL change language
 * @link 
 */
// load Cosmopolitan
require_once 'vendor/pid-autoload.php';

use Salarmehr\Cosmopolitan\Cosmo;
use PIDHomes\{Metabox, PIDEnv};
use const PIDHomes\{PID_CHART_YEARS};


$timber = new Timber\Timber();

global $language;

// load PID CONSTANTS
require_once('pid-wp-db-config.php'); //include PID CONSTANTS
// load language translate
require_once('inc/pid_translate.php'); //language translation
// load overrides
// require_once('inc/pid_override_agents_list_widget.php'); //override agent card widget
require_once('inc/pid_override_wp_query.php'); //override wordpress class WP_QUERY
// load loadmore ajax handler
require_once('inc/pid_loadmore_communities.php');
// load home pagination function
require_once('inc/pid_home_paginator.php');


/**
 * @version [.200831] WP NOT LOAD for NON-WP ajax call
 * @version -- MOVE @var $locale_cosmo_string HEAR
 * 
 */

// $test_env = new PIDEnv(PIDEnv::get_location());
// var_dump($test_env);

$pid_locale = get_locale();
switch ($pid_locale) {
	case 'zh_CN':
		$locale_cosmo_string = $locales[1];
		break;
	case 'en_CA':
	case 'en_US':
		$locale_cosmo_string = $locales[0];
		break;
}
// [$locale, $timezone] = $locale_cosmo_string;
$locale = $locale_cosmo_string[0];
$timezone = $locale_cosmo_string[1];
$cosmo = new Cosmo($locale, ['timezone' => $timezone]);

function get_relative_url()
{
	$home_path = rtrim(parse_url(home_url(), PHP_URL_PATH), '/');
	$path = trim(substr(add_query_arg(array()), strlen($home_path)), '/');
	$qs = array_keys($_GET);
	if (!empty($qs)) {
		$path = remove_query_arg($qs, $path);
	}
	return $path;
}

/**
 * Set up My Child Theme's textdomain.
 *
 * Declare textdomain for this child theme.
 * Translations can be added to the /languages/ directory.
 */
function pidhomes_theme_setup()
{
	load_child_theme_textdomain('pidhomes', get_stylesheet_directory() . '/languages');
}
add_action('after_setup_theme', 'pidhomes_theme_setup');

// if doing ajax call, do not load the plugins
function wpmdbc_exclude_plugins($plugins)
{
	if (!defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || false === strpos($_POST['action'], 'uploadimage')) {
		return $plugins;
	}
	define('WP_USE_THEMES', false);
	foreach ($plugins as $key => $plugin) {
		unset($plugins[$key]);
	}
	return $plugins;
}
add_filter('option_active_plugins', 'wpmdbc_exclude_plugins');

/************
 * NOTICE
 * AFTER CHANGE THE REWRITE RULE, WORDPRESS NEEDS RESET PERMALINK ON ADMIN PANEL
 */
//Add Rewrite Rules
add_action('init', 'PID_rewrite');
function PID_rewrite()
{
	// add_rewrite_rule('^schools/([^/]*)/page/([^/]*)/?', 'index.php?post_type=school&property-neighborhood=$matches[1]&page2=$matches[2]', 'top');
	// add_rewrite_rule('^schools/([^/]*)/?', 'index.php?post_type=school&property-neighborhood=$matches[1]', 'top');
	// add_rewrite_rule('^school/([^/]*)/?', 'index.php?post_type=school&name=$matches[1]', 'top');
	//Community
	add_rewrite_rule('^communities/([^/]*)/page/([^/]*)/?', 'index.php?post_type=community&property-city=$matches[1]&page1=$matches[2]', 'top');
	add_rewrite_rule('^communities/([^/]*)/?', 'index.php?&post_type=community&property-city=$matches[1]', 'top');
	add_rewrite_rule('^community/([^/]*)/?', 'index.php?post_type=community&name=$matches[1]', 'top');
	add_rewrite_rule('^communities/?', 'index.php?post_type=community&property-city=gva', 'top');
	//Market
	// add_rewrite_rule('^markets/cn/([^/]*)/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?', 'index.php?post_type=market&property-city=$matches[1]&dwell=$matches[2]&chart=$matches[3]&y=$matches[4]&mh=$matches[5]', 'top');
	// add_rewrite_rule('^markets/cn/([^/]*)/?', 'index.php?post_type=market&lang=cn&property-city=$matches[1]', 'top');
	// add_rewrite_rule('^markets/cn/?', 'index.php?post_type=market&lang=cn&property-city=gva', 'top');
	add_rewrite_rule('^markets/([^/]*)/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?', 'index.php?post_type=market&property-city=$matches[1]&dwell=$matches[2]&chart=$matches[3]&y=$matches[4]&mh=$matches[5]', 'top');
	add_rewrite_rule('^markets/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?', 'index.php?post_type=market&property-city=$matches[1]&dwell=$matches[2]&chart=$matches[3]&y=$matches[4]', 'top');
	add_rewrite_rule('^markets/([^/]*)/([^/]*)/([^/]*)/?', 'index.php?post_type=market&property-city=$matches[1]&dwell=$matches[2]&chart=$matches[3]', 'top');
	add_rewrite_rule('^markets/([^/]*)/([^/]*)/?', 'index.php?post_type=market&property-city=$matches[1]&dwell=$matches[2]', 'top');
	add_rewrite_rule('^markets/([^/]*)/?', 'index.php?post_type=market&property-city=$matches[1]', 'top');
	add_rewrite_rule('^markets/?', 'index.php?post_type=market&property-city=gva', 'top');
	// rps-listing
	add_rewrite_rule('^listings/([^/]*)/?', 'index.php?post_type=rps_listing&property-city=$matches[1]', 'top');
	add_rewrite_rule('^listings/?', 'index.php?post_type=rps_listing&property-city=gva', 'top');
}

add_action('init', 'pid_rewrite_add_endpoint');
if (!function_exists('pid_rewrite_add_endpoint')) {
	function pid_rewrite_add_endpoint()
	{
		add_rewrite_endpoint('cn', EP_ALL);
	}
}

function getTheFirstImage()
{
	$files = get_children('post_parent=' . get_the_ID() . '&post_type=attachment&post_mime_type=image');
	if ($files) :
		$keys = array_reverse(array_keys($files));
		$j = 0;
		$num = $keys[$j];
		$image = wp_get_attachment_image($num, 'large', false);
		$imagepieces = explode('"', $image);
		$imagepath = $imagepieces[1];
		$thumb = wp_get_attachment_thumb_url($num);
		return $image;
	else :
		return false;
	endif;
}

if (is_admin()) {
	// Only add google map api key for acf plug-in on admin pages
	// No need to load google map api js for admin pages
	if (!function_exists('pidHomesMapKey')) {
		function pidHomesMapKey($api)
		{
			$api['key'] = 'AIzaSyAczOjPVWMravPAIpPPegKgPtTFiipbgMM';
			return $api;
		}
		add_filter('acf/fields/google_map/api', 'pidHomesMapKey');
	}

	if (!function_exists('my_admin_style')) {
		//Change the property city tax UI box height
		//https://www.intelliwolf.com/add-custom-css-wordpress-admin-area/
		function my_admin_style()
		{
			wp_enqueue_style('admin-style', get_stylesheet_directory_uri() . '/css/admin-style.css');
		}
		add_action('admin_enqueue_scripts', 'my_admin_style');
	}
} else {

	if (!function_exists('inspiry_enqueue_child_styles')) {
		//PIDHomes:: Add Google Map API Key to ACF fields
		function inspiry_enqueue_child_styles()
		{
			global $language, $is_pid_home;
			// dequeue and deregister parent default css
			wp_dequeue_style('parent-default');
			wp_deregister_style('parent-default');

			// dequeue parent custom css
			wp_dequeue_style('parent-custom');

			// parent default css
			wp_enqueue_style('parent-default', get_template_directory_uri() . '/style.css');

			// parent custom css
			wp_enqueue_style('parent-custom');

			// child default css
			wp_enqueue_style('child-default', get_stylesheet_uri(), array('parent-default'), '1.0', 'all');

			// child custom css
			// wp_enqueue_style('chart-css', get_stylesheet_directory_uri() . '/js/Chart.min.css', null, '1.4', 'all');
			wp_enqueue_style('child-custom', get_stylesheet_directory_uri() . '/css/child-custom.css', array('child-default'), '1.4', 'all');

			// chart bundle js (including moment.js bundled)
			wp_enqueue_script('chart-js', get_stylesheet_directory_uri() . '/js/Chart.bundle.min.js', null, '1.4', true);
			wp_enqueue_script('chart-js-plugin', get_stylesheet_directory_uri() . '/js/chartjs-plugin-crosshair.js', null, '1.4', true);
			// gauge.js
			wp_enqueue_script('cma-gauge-js', get_stylesheet_directory_uri() . '/js/gauge.min.js', null, '1.4', true);
			// child custom js
			wp_enqueue_script('child-custom-js', get_stylesheet_directory_uri() . '/js/child-custom.js', array('jquery'), '1.4', true);
			wp_enqueue_script('index-js', get_stylesheet_directory_uri() . '/build/index.js', array('jquery'), '1.4', true);
			// html2canvas.min.js
			// wp_enqueue_script('html2canvas', get_stylesheet_directory_uri() . '/js/html2canvas.min.js', null, '1.4', true);

			// load php data for loadmore.js
			// loadmore-js
			if (!is_home() || !$is_pid_home) {
				wp_localize_script('child-custom-js', 'pid_Data', array(
					'siteurl' => get_site_url(),
					'nonce' => wp_create_nonce('wp_rest'),
					'first_page' => get_pagenum_link(1),
					'language' => $language
				));
			}
		}
		add_action('wp_enqueue_scripts', 'inspiry_enqueue_child_styles', PHP_INT_MAX);
	}

	/**
	 * @version [.200830]
	 * @var OVERRIDE RealHomes map function for market and community 
	 */
	if (!function_exists('inspiry_is_map_needed')) {
		function inspiry_is_map_needed()
		{
			if (is_tax('property-city')) {
				return false;
			} else {
				// original function
				if (is_page_template('templates/contact.php') && (get_post_meta(get_the_ID(), 'theme_show_contact_map', true) == '1')) {
					return true;
				} elseif (is_page_template('templates/submit-property.php')) {
					return true;
				} elseif (is_singular('property') && (get_option('theme_display_google_map') == 'true')) {
					return true;
				} elseif (is_singular('community')) {
					return true;
				} elseif (is_page_template('templates/home.php')) {
					$theme_homepage_module = get_post_meta(get_the_ID(), 'theme_homepage_module', true);
					if (isset($_GET['module'])) {
						$theme_homepage_module = $_GET['module'];
					}
					if ($theme_homepage_module == 'properties-map') {
						return true;
					}
				} elseif (is_page_template('templates/properties-search.php')) {
					$theme_search_module = get_option('theme_search_module', 'properties-map');
					if ('classic' === INSPIRY_DESIGN_VARIATION && ('properties-map' == $theme_search_module)) {
						return true;
					} elseif ('classic' === INSPIRY_DESIGN_VARIATION && ('simple-banner' == $theme_search_module)) {
						return false;
					} elseif ('modern' === INSPIRY_DESIGN_VARIATION) {
						return true;
					}
				} elseif (is_page_template(array(
					'templates/properties-search.php',
					'templates/properties-search-half-map.php',
					'templates/half-map-layout.php',
					'templates/properties-search-left-sidebar.php',
					'templates/properties-search-right-sidebar.php'
				))) {
					return true;
				} elseif (is_page_template(array(
					'templates/list-layout.php',
					'templates/grid-layout.php',
					'templates/list-layout-full-width.php',
					'templates/grid-layout-full-width.php'
				)) || is_tax('property-city') || is_tax('property-status') || is_tax('property-type') || is_tax('property-feature') || is_post_type_archive('property')) {
					// Theme Listing Page Module
					$theme_listing_module = get_option('theme_listing_module');
					// Only for demo purpose only
					if (isset($_GET['module'])) {
						$theme_listing_module = $_GET['module'];
					}
					if ($theme_listing_module == 'properties-map') {
						return true;
					}
				}
				return false;
			}
		}
	}

	if (!function_exists('pid_googleMap')) {
		function pid_googleMap()
		{
			// google map api js for regular wp pages
			// for some reason, RealHomes load google map api for some pages, but not for single and property-city taxo.
			global $language;
			switch ($language) {
				case 'cn':
					$lang = 'zh'; // language setting for google map chinese
					break;
				default:
					$lang = 'en';
					break;
			}
			wp_enqueue_script('googleMap', "//maps.googleapis.com/maps/api/js?key=AIzaSyAczOjPVWMravPAIpPPegKgPtTFiipbgMM&language=$lang", null, '1.0', true);
		}
		if (is_tax('property-city')) {
			add_action('wp_enqueue_scripts', 'pid_googleMap', PHP_INT_MAX);
		}
	}
}

if (!function_exists('inspiry_load_translation_from_child')) {
	/**
	 * Load translation files from child theme
	 */
	function inspiry_load_translation_from_child()
	{
		load_child_theme_textdomain('framework', get_stylesheet_directory() . '/languages');
	}

	add_action('after_setup_theme', 'inspiry_load_translation_from_child');
}

if (get_query_var('lang') == '') {
	add_filter('wpdatatables_filter_table_description', 'wpdt_my_hook', 10, 2);
	if (!function_exists('wpdt_my_hook')) {
		function wpdt_my_hook($object, $table_id)
		{

			$url = $_SESSION['url'];
			// var_dump($url);
			// var_dump($url_ref);
			if (strstr($url, "/cn/")) {
				if ($table_id == 40) {
					$object->dataTableParams->aLengthMenu = array(
						array(
							10, 25, 50, 75, 100
						),
						array(
							10, 25, 50, 75, 100
						)
					);
				}
			}
			return $object;
		}
	}
}


// add_filter('wpdatatables_filter_table_title', 'change_table_title', 10, 2);
// if (!function_exists('change_table_title')) {
// 	function change_table_title($tableTitle, $tabelID)
// 	{
// 		if ($tabelID == 40) {
// 			$tableTitle = "测试";
// 		}
// 		return $tableTitle;
// 	}
// }

// 'wpdatatables_filter_query_before_limit', $query, $this->getWpId())
// wddatatable:: if use server side processing, use hook before limit built:
// if not use server side processing, use hook wpdatatables_filter_mysql_query
// add_filter('wpdatatables_filter_query_before_limit', 'wpdt_translateTableQuery', 10, 2);
// if (!function_exists('wpdt_newQuery')) {
// 	function wpdt_translateTableQuery($query, $tableID)
// 	{
// 		$url = $_SESSION['url'];

// 		if (strstr($url, "/cn/")) {
// 			if ($tableID == 40) {
// 				//$query = "SELECT wp_pid_cities.`City_ID`, wp_pid_cities.`City_Name`, wp_pid_cities.`City_Chinese_Name` AS City_Full_Name FROM wp_pid_cities";
// 				$query = "SELECT wp_pid_counter() AS `Rank`, `City_Name_CN` AS `City_Name`, `Change%`, `Current HPI`, `January HPI`, `HPI Change`, `Neighborhood_ID` FROM wp_pid_van_city_HPI_change_pivotal";
// 			}
// 		}

// 		return $query;
// 	}
// }

add_filter('wpdatatables_filter_column_cssClassArray', 'wpdt_show_col_cn', 10, 2);
if (!function_exists('wpdt_show_col_cn')) {
	function wpdt_show_col_cn($classes, $columnTitle)
	{
		global $language;
		$colx = $columnTitle;
		$cx = $classes;
		if ($language == "en") {
			switch (strtolower($columnTitle)) {
				case 'city_name_cn':
					$cx = $classes . " " . "pid_wpdatatable_hidden_column";
					break;
			}
		}
		return $cx;
	}
}


// if (!function_exists('wpdt_translateTableQuery')) {
// 	function wpdt_translateTableQuery($tableMetadata, $tableId)
// 	{
// 		$url = $_SESSION['url'];

// 		if (strstr($url, "/cn/")) {
// 			if ($tableId == 40) {
// 				$tableMetadata->content = "SELECT wp_pid_counter() AS `Rank`, 城市名 AS `City_Name`, `Change%`, `Current HPI`, `January HPI`, `HPI Change`, `Neighborhood_ID` FROM wp_pid_van_city_HPI_change_pivotal";
// 			}
// 		}
// 		return $tableMetadata;
// 	}
// }
// add_filter('wpdatatables_filter_table_metadata', 'wpdt_newTableQuery', 10, 2);


//wpdatatables_filter_table_metadata( $tableMetadata, $tableId )
add_filter('wpdatatables_filter_columns_metadata', 'wpdt_newColHeader', 10, 2);
function wpdt_newColHeader($columnsMetadata, $tabelID)
{
	if (is_admin()) {
		// if in the admin interface, do not translate
		return $columnsMetadata;
	}
	global $language;
	if ($language == 'cn') {
		foreach ($columnsMetadata as $columnMeta) {
			$display_header = '';
			//NOTE:: convert to lowercase
			switch (strtolower($columnMeta->display_header)) {
				case 'rank':
					$display_header = '排名';
					break;
				case 'city_name':
				case 'city name':
				case 'city':
					$display_header = '英文名';
					break;
				case 'city_name_cn':
					$display_header = '城市';
					break;
				case 'city_district_name_cn':
					$display_header = '城市/社区';
					break;
				case 'city/district':
					$display_header = '英文名';
					break;
				case 'city/district english':
					$display_header = '英文名';
					break;
				case 'neighborhood':
					$display_header = '社区';
					break;
				case 'change%':
					$display_header = '涨幅比例%';
					break;
				case 'month change%':
					$display_header = '当月涨幅比例%';
					break;
				case 'current hpi':
					$display_header = '当月房价指数';
					break;
				case 'last month hpi':
					$display_header = '上月房价指数';
					break;
				case 'month change$':
					$display_header = '当月涨幅金额$';
					break;
				case 'jan hpi':
				case 'january hpi':
					$display_header = '一月房价指数';
					break;
				case 'hpi change':
					$display_header = '涨幅金额$';
					break;
				case 'all hpi':
				case 'all':
					$display_header = '综合基准房价';
					break;
				case 'detached hpi':
				case 'detached':
					$display_header = '独立屋基准房价';
					break;
				case 'townhouse hpi':
				case 'townhouse':
					$display_header = '城市屋基准房价';
					break;
				case 'apartment hpi':
				case 'apartment':
					$display_header = '公寓基准房价';
					break;
				case 'no':
					$display_header = '序号';
					break;
				case 'address':
					$display_header = '地址';
					break;
				case 'unit no':
					$display_header = '单元号';
					break;
				case 'price':
					$display_header = '价格';
					break;
				case 'days on market':
					$display_header = '挂牌天数';
					break;
				case 'total floor area':
					$display_header = '居住面积';
					break;
				case 'strata fee':
					$display_header = '管理费';
					break;
				case 'land value':
					$display_header = '土地价值';
					break;
				case 'house value':
					$display_header = '物业价值';
					break;
				case 'lotpricepersqft':
					$display_header = '土地每尺价格';
					break;
				case 'improvepricepersqft':
					$display_header = '物业每尺价格';
					break;
				case 'bc assess':
					$display_header = '总政府估价';
					break;
				case 'plannum':
					$display_header = '小区号';
					break;
				case 'listdate':
					$display_header = '挂牌日期';
					break;
				case 'mls':
					$display_header = 'MLS号码';
					break;
				case 'year built':
					$display_header = '建造年份';
					break;
				case 'link':
					$display_header = ' ';
					break;
				case 'price per sqft':
					$display_header = '每尺价格';
					break;
				case 'change% to bca':
					$display_header = '相对政府估价涨跌幅%';
					break;
				case 'lot size':
					$display_header = '宅地面积';
					break;
				default:
					// only compare lowercase
					$display_header = $columnMeta->display_header;
					break;
			}
			$columnMeta->display_header = $display_header;
		}
	} else {
		foreach ($columnsMetadata as $columnMeta) {
			switch (strtolower($columnMeta->display_header)) {
				case 'city_name_cn':
				case 'city_district_name_cn':
					$columnMeta->width = 0;
					break;
			}
		}
	}
	return $columnsMetadata;
}

// add_action('wp_ajax_get_wdtable', 'wdtGetAjaxData');
// add_action('wp_ajax_nopriv_get_wdtable', 'wdtGetAjaxData');
/** =============== */
add_filter('query_vars', 'pid_query_vars_filter', 0, 1);
function pid_query_vars_filter($vars)
{
	$vars[] = 'page1';
	$vars[] = 'page2';
	$vars[] = 'lang';
	$vars[] = 'chart'; // perc, doll, ...
	$vars[] = 'loc'; // surrey, burnaby...
	$vars[] = 'dwell'; //for chart
	$vars[] = 'y'; // year for chart
	$vars[] = 'mh'; // start month, cannot use m, it's internal var
	return $vars;
}

add_action('wp_ajax_loadmore', 'pid_loadmore_ajax_handler'); // wp_ajax_{action}
add_action('wp_ajax_nopriv_loadmore', 'pid_loadmore_ajax_handler'); // wp_ajax_nopriv_{action}
function pid_loadmore_ajax_handler()
{
	// $X = set_debug(__FILE__);
	// prepare our arguments for the query
	$args = json_decode(stripslashes($_POST['query']), true);
	$args['paged'] = $_POST['page']; // we need next page to be loaded
	$args['post_status'] = 'publish';
	$session_id = $_POST['session_id'];

	// it is always better to use WP_Query but not here
	query_posts($args);
	// print_X($X, __LINE__, $args['paged']);
	$post_type = $args['post_type'];
	$post_type_labels = get_post_type_labels(get_post_type_object($post_type));

	if (have_posts()) :
		$iLoop = 1;
		// run the loop
		while (have_posts()) : the_post();
			// print_X($X, __LINE__, '$iLoop::', $iLoop++);
			// look into your theme code how the posts are inserted, but you can use your own HTML of course
			// do you remember? - my example is adapted for Twenty Seventeen theme
			// get_template_part( 'pid-partials/content', get_post_format() );
			//get_template_part('pid-partials/content', 'x-post');
?>
			<div style="text-align: left" class="<?php echo $session_id ?>">
				<h3><a href="<?php echo str_replace(
												"/" . strtolower($post_type_labels->name) . "/",
												"/" . strtolower($post_type_labels->singular_name) . "/",
												strtolower(get_the_permalink())
											); ?>">
						<?php the_title(); ?></a>
				</h3>
				<div><?php the_excerpt(); ?> </div>
			</div>
<?php

		endwhile;

	endif;
	die; // here we exit the script and even no wp_reset_query() required!
}

/****************
 * CUSTOMIZE THE PAGINATOR
 */
function pid_paginator($query, $session_id)
{

	// $X = set_debug(__FILE__);

	// get parameters from $wp_query object
	// how much posts to display per page (DO NOT SET CUSTOM VALUE HERE!!!)
	$posts_per_page = (int) $query->query_vars['posts_per_page'];
	// current page
	$current_page = (int) $query->query_vars['paged'];
	// the overall amount of pages
	$max_page = $query->max_num_pages;

	// we don't have to display pagination or load more button in this case
	if ($max_page <= 1) return;

	// set the current page to 1 if not exists
	if (empty($current_page) || $current_page == 0) $current_page = 1;

	// you can play with this parameter - how much links to display in pagination
	$links_in_the_middle = 10;
	$links_in_the_middle_minus_1 = $links_in_the_middle - 1;

	// the code below is required to display the pagination properly for large amount of pages
	// I mean 1 ... 10, 12, 13 .. 100
	// $first_link_in_the_middle is 10
	// $last_link_in_the_middle is 13
	$first_link_in_the_middle = $current_page - floor($links_in_the_middle_minus_1 / 2);
	$last_link_in_the_middle = $current_page + ceil($links_in_the_middle_minus_1 / 2);

	// some calculations with $first_link_in_the_middle and $last_link_in_the_middle
	if ($first_link_in_the_middle <= 0) $first_link_in_the_middle = 1;
	if (($last_link_in_the_middle - $first_link_in_the_middle) != $links_in_the_middle_minus_1) {
		$last_link_in_the_middle = $first_link_in_the_middle + $links_in_the_middle_minus_1;
	}
	if ($last_link_in_the_middle > $max_page) {
		$first_link_in_the_middle = $max_page - $links_in_the_middle_minus_1;
		$last_link_in_the_middle = (int) $max_page;
	}
	if ($first_link_in_the_middle <= 0) $first_link_in_the_middle = 1;

	// begin to generate HTML of the pagination
	$pagination = '<nav id="pid_pagination_' . $session_id . '" class="wpDataTables wpDataTablesWrapper no-footer" role="navigation">
                <div class="dataTables_paginate paging_full_numbers">';

	// arrow first page
	if ($current_page == 1) {
		$pagination .= '<a class="paginate_button first disabled pid-page-numbers" page_id="first"></a>';
	} else {
		$pagination .= '<a class="paginate_button first pid-page-numbers" page_id="first"></a>';
	}

	// when to display "..." and the first page before it
	// if ($first_link_in_the_middle >= 3 && $links_in_the_middle < $max_page) {
	// 	$pagination.= '<a class="paginate_button pid-page-numbers" page_id="1">1</a>'; //'. $first_page_url . $search_query . '

	// 	if( $first_link_in_the_middle != 2 )
	// 		$pagination .= '<span class="paginate_button pid-page-numbers extend">...</span>';
	// }

	// arrow left (previous page)
	if ($current_page == 1) {
		$pagination .= '<a class="paginate_button previous disabled pid-page-numbers" page_id="previous"></a>'; //'. $first_page_url . '/page/' . ($current_page-1) . $search_query . '
	} else {
		$pagination .= '<a class="paginate_button previous pid-page-numbers" page_id="previous"></a>'; //'. $first_page_url . '/page/' . ($current_page-1) . $search_query . '
	}

	$pagination .= '<span>';
	// loop page links in the middle between "..." and "..."
	// for($i = $first_link_in_the_middle; $i <= $last_link_in_the_middle; $i++) {
	// 	if($i == $current_page) {
	// 		$pagination.= '<a class="paginate_button current pid-page-numbers">'.$i.'</a>';
	// 	} else {
	// 		$pagination .= '<a class="paginate_button pid-page-numbers" page_id="' . $i . '" >' .$i. '</a>'; //'. $first_page_url . '/page/' . $i . $search_query .'
	// 	}
	// }

	for ($i = 1; $i <= $max_page; $i++) {
		if ($i == $current_page) {
			$pagination .= '<a class="paginate_button current pid-page-numbers" page_id="' . $i . '">' . $i . '</a>';
		} else {
			$pagination .= '<a class="paginate_button pid-page-numbers" page_id="' . $i . '" >' . $i . '</a>'; //'. $first_page_url . '/page/' . $i . $search_query .'
		}
	}


	// when to display "..." and the last page after it
	// if ( $last_link_in_the_middle < $max_page ) {

	// 	if( $last_link_in_the_middle != ($max_page-1) )
	// 		$pagination .= '<span class="pid-page-numbers extend">...</span>';

	// 	$pagination .= '<a class="pid-page-numbers" page_id="' . $max_page . '">'. $max_page .'</a>'; //'. $first_page_url . '/page/' . $max_page . $search_query .'
	// }
	$pagination .= '</span>';

	// arrow right (next page)
	// if ($current_page != $last_link_in_the_middle )
	//   $pagination.= '<a class="paginate_button next pid-page-numbers"></a>'; //'. $first_page_url . '/page/' . ($current_page+1) . $search_query .'

	if ($current_page == $max_page) {
		$pagination .= '<a class="paginate_button next disabled pid-page-numbers" page_id="next"></a>'; //'. $first_page_url . '/page/' . ($current_page+1) . $search_query .'
	} else {
		$pagination .= '<a class="paginate_button next pid-page-numbers" page_id="next"></a>'; //'. $first_page_url . '/page/' . ($current_page+1) . $search_query .'
	}
	// arrow last page
	if ($current_page == $max_page) {
		$pagination .= '<a class="paginate_button last disabled pid-page-numbers" page_id="last"></a>';
	} else {
		$pagination .= '<a class="paginate_button last pid-page-numbers" page_id="last"></a>';
	}

	// end HTML
	$pagination .= "</div></nav>\n";

	// haha, this is our load more posts link
	// if( $current_page < $max_page )
	// 	$pagination.= '<div id="misha_loadmore">More posts</div>';

	// replace first page before printing it
	// echo str_replace(array("/page/1?", "/page/1\""), array("?", "\""), $pagination);
	echo $pagination;
}

/**
 * @version [.200831] Add language switcher ajax call
 * @var AJAX_CALL
 */
add_action('wp_ajax_switchlanguage', 'pid_language_switcher_ajax_handler'); // wp_ajax_{action}
add_action('wp_ajax_nopriv_switchlanguage', 'pid_language_switcher_ajax_handler'); // wp_ajax_nopriv_{action}
function pid_language_switcher_ajax_handler()
{
	$lang = $_POST['language'];
	global $locales;
	switch ($lang) {
		case 'cn':
			$locale_cosmo_string = $locales[1];
			break;
		case 'en':
			$locale_cosmo_string = $locales[0];
			break;
	}
	// [$locale, $timezone] = $locale_cosmo_string;
	$locale = $locale_cosmo_string[0];
	$timezone = $locale_cosmo_string[1];
	$cosmo = new Cosmo($locale, ['timezone' => $timezone]);
	echo 'language switched to: ' . $lang;
}

// chart drawing image handler ajax
add_action('wp_ajax_uploadimage', 'pid_chart_image_ajax_handler'); // wp_ajax_{action}
add_action('wp_ajax_nopriv_uploadimage', 'pid_chart_image_ajax_handler'); // wp_ajax_nopriv_{action}
function pid_chart_image_ajax_handler()
{
	global $language;

	ignore_user_abort(true);
	if (PID_DEBUG_MODE) {
		ob_start();
	} else {
		ob_start(null, 0, PHP_OUTPUT_HANDLER_FLUSHABLE & PHP_OUTPUT_HANDLER_FLUSH & PHP_OUTPUT_HANDLER_REMOVABLE);
	}
	error_reporting(0);

	$filename = $_FILES["fileToUpload"]["name"];
	$chart_params = json_decode(stripslashes($_POST['chartParams']));
	$property_type = $chart_params->PropertyType;
	$chart_year = $chart_params->Years;
	$chart_type = $chart_params->ChartType;
	$chart_hoods = json_decode($chart_params->Communities);
	$community = json_decode($chart_params->Communities)[0];
	switch ($chart_type) {
		case 'dollar':
			$post_content1 = "<strong> Chart Type:</strong> HPI Price $";
			break;
		default:
			$post_content1 = "<strong> Chart Type:</strong> HPI Price Change %";
			break;
	}
	switch ($property_type) {
		case 'All':
			$post_content2 = "<strong> Property Type:</strong> All Property Types";
			break;
		case 'Detached':
			$post_content2 = "<strong> Property Type:</strong> Single House";
			break;
		case 'Townhouse':
			$post_content2 = "<strong> Property Type:</strong> Townhouse";
			break;
		case 'Apartment':
		case 'Condo':
		case 'Apartment/Condo':
			$post_content2 = "<strong> Property Type:</strong> Apartment/Condo";
			break;
		default:
			$post_content2 = "<strong> Property Type:</strong> Grouped By Community";
			break;
	}
	$hoods = "";
	$hoods_title = "";
	for ($i = 0; $i < count($chart_hoods); $i++) {
		$hoods .= "<li>$chart_hoods[$i]</li>";
		if ($i < 8) {
			$hoods_title .= $chart_hoods[$i] . " | ";
		}
	}
	$hoods = "<div><p><strong> Included City/Community:</strong></p><ul>$hoods</ul>";
	if (count($chart_hoods) > 8) {
		$title_hoods = "Great Vancouver Area";
	} else {
		$title_hoods = $hoods_title;
	}
	$post_title = get_locale() == 'zh_CN' ? "$title_hoods 房地产价格走势图 $chart_year" : "$title_hoods Real Estate Charts $chart_year";
	$post_content = "<div><p>$post_content1</p><div><div><p>$post_content2</p></div>";
	$post_content .= "$hoods";
	$post_description = wp_strip_all_tags($hoods_title . "|" . $post_content1 . " | " . $post_content2);
	// Add post meta data
	$meta_twitter_card_value = "meta name='twitter:card' content='summary_large_image'";
	$meta_twitter_title_value = "meta property='og:title' content='$post_title'";
	$meta_twitter_desc_value = "meta property='og:description' content='$post_description'";
	$meta_twitter_type_value = "meta property='og:type' content='article'";
	$new = array(
		'post_title' => $post_title, // {City} Home Price Chart {'Curent Year}
		'post_content' => $post_content, // {Townhouse: ... LIke a summary}
		'post_author' => 1,
		'post_status' => 'publish',
		'meta_input' => array(
			'twitter_card' => $meta_twitter_card_value,
			'twitter_title' => $meta_twitter_title_value,
			'twitter_desc' => $meta_twitter_desc_value,
			'twitter_type' => $meta_twitter_type_value
		)
	);
	$post_id = wp_insert_post($new);

	wp_update_post(array(
		'ID' => $post_id,
		'post_name' => "SS$post_id"
	));
	wp_set_object_terms($post_id, 'Advertisement', 'category');

	$post_url = get_post_permalink($post_id); // get_site_url() . "/?p=$post_id";
	echo $post_id . ",";
	// Add post meta data
	$meta_twitter_url_key = "twitter_url";
	$meta_twitter_url_value = "meta property='og:url' content='$post_url'";
	add_post_meta($post_id, $meta_twitter_url_key, $meta_twitter_url_value, true);
	$location = wp_upload_dir()['path'] . "/{$post_id}_$filename";
	$location_url = wp_upload_dir()['url'] . "/{$post_id}_$filename";
	$move_file = move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $location);
	$imageInfo = getimagesize($location);
	$image_w = $imageInfo[0];
	$image_h = $imageInfo[1];
	$uploadOk = 1;
	$imageFileType = pathinfo($location, PATHINFO_EXTENSION);
	$meta_twitter_pic_key = "twitter_pic";
	$meta_twitter_pic_value = "meta property='twitter:image' content='$location_url'";
	add_post_meta($post_id, $meta_twitter_pic_key, $meta_twitter_pic_value, true);
	$meta_twitter_w_key = "twitter_pic_w";
	$meta_twitter_w_value = "meta property='og:image:width' content='$image_w'";
	add_post_meta($post_id, $meta_twitter_w_key, $meta_twitter_w_value, true);
	$meta_twitter_h_key = "twitter_pic_h";
	$meta_twitter_h_value = "meta property='og:image:height' content='$image_h'";
	add_post_meta($post_id, $meta_twitter_h_key, $meta_twitter_h_value, true);
	/* Valid Extensions */
	$valid_extensions = array("jpg", "jpeg", "png");
	/* Check file extension */
	if (!in_array(strtolower($imageFileType), $valid_extensions)) {
		$uploadOk = 0;
	}

	switch ($community) {
		case '/Langley': // Pause testing
			set_thumbnail_by_worker($post_id);
			break;
		case '/Burnaby': // Pause testing
			set_thumbnail_byFastCGI($post_id);
			break;
		default:
			set_thumbnail($post_id);
			break;
	}
}

function set_thumbnail_byFastCGI($post_id)
{
	session_write_close();
	fastcgi_finish_request();
	set_thumbnail($post_id);
}

function set_thumbnail_by_worker($post_id)
{
	/***
	 * USE fsockopen()
	 */
	$postData = json_encode(array_merge($_POST, $_FILES, array('postID' => $post_id)));
	echo strlen($postData);
	// headers for the connection to worker.php
	$worker = PID_DEBUG_MODE ? GET_STYLESHEET_DIRECTORY_URI() . "/DB/WORKER.PHP" :
		"pidhomes.ca/wp-content/themes/realhomes-child-3/db/worker.php";
	$pid_HOST = PID_DEBUG_MODE ? 'localhost' : 'gator3140.hostgator.com';
	$pid_PORT = PID_DEBUG_MODE ? 80 : 443;
	$headers =  "POST $worker HTTP/1.1" . PHP_EOL;
	$headers .= "Host: $pid_HOST" . PHP_EOL;
	$headers .= "Content-Length: " . strlen($postData) . PHP_EOL;
	$headers .= "Content-Encoding: none" . PHP_EOL;
	$headers .= "Content-Type: application/json" . PHP_EOL;

	// create socket to the local webserver for calling background script
	// LIVE CODE:: connect to pidhomes.ca
	// $socketToWorker = fsockopen('ssl://pidhomes.ca', null, $errno, $err_msg);
	// LOCAL CODE:: connect to localhost
	$socketToWorker = fsockopen($pid_HOST, $pid_PORT, $errno, $err_msg);
	if (!$socketToWorker) die("fsockopen error #$errno: $err_msg");

	// Make the request and send the POST data
	fwrite($socketToWorker, $headers . PHP_EOL . $postData);

	//read until the 1st packet is returned, then stop. 8192 is arbitrary
	//but needs to be greater than the expected response from worker.php
	$data = fread($socketToWorker, 8192);
	fclose($socketToWorker);

	//send response to browser (work still ongoing in background)
	if (strpos($data, '200 OK') > 0) echo "Success";
	else echo "Background work failed. Error: $data";
}

function set_thumbnail($post_id)
{
	$filename = $_FILES["fileToUpload"]["name"];
	$location = wp_upload_dir()['path'] . "/{$post_id}_$filename";
	$imageFileType = pathinfo($location, PATHINFO_EXTENSION);
	add_filter('intermediate_image_sizes_advanced', 'wpc_unset_imagesizes');
	function wpc_unset_imagesizes($sizes)
	{
		unset($sizes['thumbnail']);
		unset($sizes['medium']);
		unset($sizes['medium_large']);
		unset($sizes['large']);
	}

	$attachment = array(
		'post_mime_type' => "image/$imageFileType",  // file type
		'post_title' => sanitize_file_name($filename),  // sanitize and use image name as file name
		'post_content' => '',  // could use the image description here as the content
		'post_status' => 'inherit'
	);

	// insert and return attachment id
	$attachmentId = wp_insert_attachment($attachment, $location, $post_id);

	// insert and return attachment metadata
	$attachmentData = wp_generate_attachment_metadata($attachmentId, $location);

	// update and return attachment metadata
	wp_update_attachment_metadata($attachmentId, $attachmentData);

	// finally, associate attachment id to post id
	$success = set_post_thumbnail($post_id, $attachmentId);

	// was featured image associated with post?
	if ($success) {
		$message = $filename . ' has been added as featured image to post.';
	} else {
		$message = $filename . ' has NOT been added as featured image to post.';
	}

	echo $message;

	ob_end_flush();
	ob_flush();
	flush();
}

/**
 * @var GET_POST_TYPE_PLURAL
 */
function get_post_type_plural($post_type)
{
	switch ($post_type) {
		case 'community':
			$post_type_plural  = 'communities';
			$page = get_query_var('page1', 1);
			break;
		case 'school':
			$post_type_plural = 'schools';
			$page = get_query_var('page2', 1);
			break;
		case 'market':
			$post_type_plural = 'markets';
			break;
		case 'cma':
			$post_type_plural = 'cma';
			break;
		case 'demography':
			$post_type_plural = 'demography';
			break;
		default:
			$post_type_plural = 'communities';
			break;
	}
	return $post_type_plural;
}

/**
 * @var INSERT_META_DATA TO WP TERM OBJECT BY get_term_by()
 * Try to wrap meta key and meta value into the term object
 */
function pid_get_terms_i18n($args, $language)
{
	if (!isset($language)) {
		global $language;
	}
	$terms = get_terms($args);
	if ($terms) {
		if (count($terms) > 0) {
			foreach ($terms as $term) {
				pid_location_term_i18n($term, $language);
			}
		}
	}
	return $terms;
}

function pid_get_term_by_i18n($fieldName, $fieldValue)
{
	global $language;

	// define nbh_code as a meta value sign
	if ($fieldName == 'nbh_code') {
		$meta_query =	array(
			'key' 		=> 'neighborhood_code',
			'value' 	=> $fieldValue,
			'compare'	=> '='

		);
		$pid_terms = get_terms(array(
			'taxonomy' 			=> 'property-city',
			'hide_empty' 		=> false,
			'meta_query'		=> array($meta_query)
		));
		if (count($pid_terms) == 0) {
			return false;
		} else {
			$pid_term = $pid_terms[0];
		}
	} else {
		$pid_term = get_term_by($fieldName, $fieldValue, 'property-city');
	}
	$pid_term = pid_location_term_i18n($pid_term, $language);
	return $pid_term;
}

function pid_get_term_by($fieldName, $fieldValue)
{
	// define nbh_code as a meta value sign
	if ($fieldName == 'nbh_code') {
		$meta_query =	array(
			'key' 		=> 'neighborhood_code',
			'value' 	=> $fieldValue,
			'compare'	=> '='

		);
		$pid_terms = get_terms(array(
			'taxonomy' 			=> 'property-city',
			'hide_empty' 		=> false,
			'meta_query'		=> array($meta_query)
		));
		if (count($pid_terms) == 0) {
			return false;
		} else {
			$pid_term = $pid_terms[0];
			$pid_term->{'chinese_title'} = get_field('chinese_title', 'property-city_' . $pid_term->term_id);
			return $pid_term;
		}
	}
	$pid_term = get_term_by($fieldName, $fieldValue, 'property-city');
	$pid_term->{'chinese_title'} = get_field('chinese_title', 'property-city_' . $pid_term->term_id);
	return $pid_term;
}

function pid_add_chinese_title($term)
{
	global $language;
	switch ($language) {
		case 'cn':
			$term->{'chinese_title'} = get_field('chinese_title', 'property-city_' . $term->term_id);
			break;
		case 'hk':
			break;
		case 'en':
			break;
	}
	return $term;
}

/**
 * @since [.200901] Add Location Term Translation
 * @var location term
 * @var language string
 */
function pid_location_term_i18n($location, $language)
{
	switch ($language) {
		case 'cn':
			$location->{'i18n_title'} = get_field('chinese_title', 'property-city_' . $location->term_id);
			break;
		case 'hk':
			$location->{'i18n_title'} = get_field('hongkong_title', 'property-city_' . $location->term_id);
			break;
		case 'en':
			$location->{'i18n_title'} = $location->name;
			break;
	}
	$location->name = $location->i18n_title ? $location->i18n_title : $location->name;
	return $location;
}

// pid social shared pages
function is_social_share()
{
	$post_type = get_post_type();
	if (!$post_type) {
		$post_type = get_query_var('post_type');
	}
	$social_share_array = array('market', 'community', 'post');
	if (in_array($post_type, $social_share_array)) {
		return true;
	} else {
		return false;
	}
}

// pid is_mobile device
function is_mobile()
{
	if (empty($_SERVER['HTTP_USER_AGENT'])) {
		$is_mobile = false;
	} else if (
		strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false
		// many mobile devices (all iPhone, iPad, etc.)
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
		|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
	) {
		$is_mobile = true;
	} else {
		$is_mobile = false;
	}
	return $is_mobile;
}

// OVERRIDE REALHOMES ERROR FUNCTIONS
if (!function_exists('inspiry_is_rvr_enabled')) {
	/**
	 * Check if Realhomes Vacation Rentals plugin is activated and enabled
	 *
	 * @return bool
	 */
	function inspiry_is_rvr_enabled()
	{
		return false;

		$rvr_settings = get_option('rvr_settings');
		$rvr_enabled  = $rvr_settings['rvr_activation'];

		if ($rvr_enabled && class_exists('Realhomes_Vacation_Rentals')) {
			return true;
		}

		return false;
	}
}

/** 
 *@since [.200907]
 *@var OVERRIDE_REALHOMES_PLUGIN_EASY_REAL_ESTATE
 */
// 
if (function_exists('ere_send_contact_message_cfos')) {
	remove_action('wp_ajax_send_message_cfos', 'ere_send_contact_message_cfos');
	remove_action('wp_ajax_nopriv_send_message_cfos', 'ere_send_contact_message_cfos');
	/**
	 * Handler for Contact form on contact page template
	 */
	function pid_ere_send_contact_message_cfos()
	{

		if (isset($_POST['email'])) :

			/*
			 * Verify Nonce
			 */
			if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'send_cfos_message_nonce')) {
				echo json_encode(array(
					'success' => false,
					'message' => esc_html__('Unverified Nonce!', 'easy-real-estate')
				));
				die;
			}

			/* Verify Google reCAPTCHA */
			ere_verify_google_recaptcha();

			/*
			 * Sanitize and Validate Target email address that will be configured from theme options
			 */
			if (isset($_POST['the_id'])) {
				$to_email = sanitize_email(get_post_meta($_POST['the_id'], 'theme_contact_form_email_cfos', true));
				if (!$to_email) {
					$to_email = "pqu007@gmail.com";
				}
			} else {
				$to_email = '';
			}

			$to_email = is_email($to_email);
			if (!$to_email) {
				echo json_encode(array(
					'success' => false,
					'message' => esc_html__('Target Email address is not properly configured!', 'easy-real-estate')
				));
				die;
			}

			/*
			 * Sanitize and Validate contact form input data
			 */
			$from_name = sanitize_text_field($_POST['name']);
			$phone_number = sanitize_text_field($_POST['number']);
			$message = stripslashes($_POST['message']);

			$from_email = sanitize_email($_POST['email']);
			$from_email = is_email($from_email);
			if (!$from_email) {
				echo json_encode(array(
					'success' => false,
					'message' => esc_html__('Provided Email address is invalid!', 'easy-real-estate')
				));
				die;
			}

			/*
			 * Email Subject
			 */
			$email_subject = esc_html__('New message sent by', 'easy-real-estate') . ' ' . $from_name . ' ' . esc_html__('using home contact form at', 'easy-real-estate') . ' ' . get_bloginfo('name');

			/*
			 * Email Body
			 */
			$email_body = array();

			$email_body[] = array(
				'name'  => esc_html__('Name', 'easy-real-estate'),
				'value' => $from_name,
			);

			if (!empty($phone_number)) {
				$email_body[] = array(
					'name'  => esc_html__('Phone Number', 'easy-real-estate'),
					'value' => $phone_number,
				);
			}

			$email_body[] = array(
				'name'  => esc_html__('Email', 'easy-real-estate'),
				'value' => $from_email,
			);

			$email_body[] = array(
				'name'  => esc_html__('Message', 'easy-real-estate'),
				'value' => $message,
			);

			if ('1' == get_option('inspiry_gdpr_in_email', '0')) {
				$GDPR_agreement = $_POST['gdpr'];
				if (!empty($GDPR_agreement)) {
					$email_body[] = array(
						'name'  => esc_html__('GDPR Agreement', 'easy-real-estate'),
						'value' => $GDPR_agreement,
					);
				}
			}

			$email_body = ere_email_template($email_body, 'contact_form_over_slider');

			/*
			 * Email Headers ( Reply To and Content Type )
			 */
			$headers = array();

			/* Send CC of contact form message if configured */
			if (isset($_POST['the_id'])) {
				$cc_email = sanitize_email(get_post_meta($_POST['the_id'], 'theme_contact_form_email_cc_cfos', true));
			} else {
				$cc_email = '';
			}

			$cc_email = explode(',', $cc_email);
			if (!empty($cc_email)) {
				foreach ($cc_email as $ind_email) {
					$ind_email = sanitize_email($ind_email);
					$ind_email = is_email($ind_email);
					if ($ind_email) {
						$headers[] = "Cc: $ind_email";
					}
				}
			}

			/* Send BCC of contact form message if configured */
			if (isset($_POST['the_id'])) {
				$bcc_email = sanitize_email(get_post_meta($_POST['the_id'], 'theme_contact_form_email_bcc_cfos', true));
			} else {
				$bcc_email = '';
			}

			$bcc_email = explode(',', $bcc_email);
			if (!empty($bcc_email)) {
				foreach ($bcc_email as $ind_email) {
					$ind_email = sanitize_email($ind_email);
					$ind_email = is_email($ind_email);
					if ($ind_email) {
						$headers[] = "Bcc: $ind_email";
					}
				}
			}

			$headers[] = "Reply-To: $from_name <$from_email>";
			$headers[] = "Content-Type: text/html; charset=UTF-8";
			$headers = apply_filters("inspiry_contact_mail_header", $headers);    // just in case if you want to modify the header in child theme

			if (wp_mail($to_email, $email_subject, $email_body, $headers)) {

				if ('1' === get_option('ere_contact_form_webhook_integration', '0')) {
					ere_forms_safe_webhook_post($_POST, 'contact_form_over_slider');
				}

				echo json_encode(array(
					'success' => true,
					'message' => esc_html__('Message Sent Successfully!', 'easy-real-estate')
				));
			} else {
				echo json_encode(array(
					'success' => false,
					'message' => esc_html__('Server Error: WordPress mail function failed!', 'easy-real-estate')
				));
			}

		else :
			echo json_encode(array(
				'success' => false,
				'message' => esc_html__('Invalid Request !', 'easy-real-estate')
			));
		endif;

		do_action('inspiry_after_contact_form_submit');

		die;
	}

	add_action('wp_ajax_nopriv_send_message_cfos', 'pid_ere_send_contact_message_cfos');
	add_action('wp_ajax_send_message_cfos', 'pid_ere_send_contact_message_cfos');
}
