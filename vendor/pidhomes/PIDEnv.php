<?php

/** 
 *  MODULE OF FUNCTION.PHP
 *  ======================
 *  @neighborhoodID
 *  @return pidterms 
 *  @since [.200913] SEPARATE FROM Metabox CLASS
 *  @
 */

namespace PIDHomes;

class PIDEnv
{
  public $taxo = 'property-city';
  public static $location;
  public static $remote_ip;
  public static $is_myself_ip;

  const NON_ACTIVE = 'metabox__blog_home-link';
  const MYSELF_IPS = ['', '::1', '162.208.220.164'];

  public $post_id;
  public $post_type;
  public $post_type_labels;
  public $community_term_id;
  public $page_nbh;
  public $page_school;
  public $location_term;
  public $community_level;
  public $community_label;
  public $community_section_h1;
  public $market_section_h1;
  public $location_city_term;
  public $head_variation;
  public $is_a_single_post; // a single post
  public $is_archive; // a archive page
  public $banner_title;
  public $map_quest_image_uri; // image for social media
  public $map_google_image_uri; // image for social media
  public $language; // 
  public $query_vars = [];

  /**
   * @param NONE
   */
  public function __construct($location = null)
  {
    global $language;
    $this->language = $language;
    $this->get_uri_query_vars();
    $this->post_id = get_the_ID(); //First Community Post ID
    $this->post_type = get_post_type() ? get_post_type() : get_query_var('post_type', 'community'); // save post type
    switch ($this->post_type) {
      case 'page':
      case 'listing':
        $this->post_type = 'rps_listing';
        break;
    }
    $this->post_type_labels = get_post_type_labels(get_post_type_object($this->post_type));
    if (!isset($this->post_type_labels->plural_name)) {
      switch ($this->post_type) {
        case 'rps_listing':
        case 'page':
        case 'listing':
          $this->post_type_labels->plural_name = 'listings';
          break;
        default:
          $this->post_type_labels->plural_name = 'communities';
          break;
      }
    }
    self::$location = $location ?? self::get_location();
    $this->location_term = PIDTerms::get_PIDTerm_by('slug', self::$location);
    if (!$this->location_term) {
      return;
    }
    $this->map_quest_image_uri = $this->set_map_image($this->location_term);
    $this->map_google_image_uri = $this->set_map_google_image($this->location_term);
    $this->community_term_id = $this->location_term->term_id;
    $this->location_city_term = PIDTerms::get_city_pidterms('id', $this->community_term_id); // get the top level city term
    $this->page_nbh = get_query_var('page1', 1); //args[]:: string $var, mixed $default = ''
    $this->page_school = get_query_var('page2', 1); //args[]:: string $var, mixed $default = ''
    $this->community_level = PIDTerms::get_community_level($this->location_term->term_id);
    $this->community_label = $this->location_term->label; // for Page Banner Title
    $this->community_section_h1 = sprintf(__("%s Communities List", 'pidhomes'), $this->community_label);
    $this->market_section_h1 = sprintf(__("%s Real Estate Market Charts", "pidhomes"), $this->community_label);

    $this->head_variation = get_option('inspiry_listing_header_variation');

    $this->is_a_single_post = is_single();
    $this->is_archive = is_archive();

    $this->set_banner_title();

    self::$remote_ip = $this->get_ip_address();
    self::$is_myself_ip = !!array_search(self::$remote_ip, self::MYSELF_IPS);
  }

  public function get_pid_translate($location = null)
  {
    global $language;
    $location = $location ?? self::$location;

    $lang_set = [];
    $property_type = $this->query_vars['property_type'];
    $chart_type = $this->query_vars['chart_type'];
    $years = $this->query_vars['years'];
    $months = $this->query_vars['month'];
    $translate_options = array(
      'location' => $location,
      'property_type' => $property_type,
      'chart_type' => $chart_type,
      'years' => $years,
      'month' => $months,
      'translation_group' => 1 // 1 for market archive, 2 for chart selection
    );
    $lang_set = pid_po_translate($language, $translate_options);
    return $lang_set;
  }

  public static function get_location()
  {
    if (is_singular('community') or is_singular('market')) {
      $_location = get_query_var('name', 'surrey');
    } elseif (is_archive()) {
      $_location = trim(get_query_var('property-city', 'surrey')); //get location slug
    } else {
      $_location = 'surrey';
    }
    self::$location = $_location;
    return $_location;
  }

  public function set_location($location)
  {
    $_location_term = PIDTerms::get_PIDTerm_by('slug', $location);
    $this->location_term = $_location_term;
    self::$location = $_location_term->name;
  }

  public function set_banner_title()
  {
    $banner_title = get_post_meta(get_the_ID(), 'REAL_HOMES_banner_title', true);
    if (empty($banner_title)) {
      $community_label = $this->community_label;
      if ($community_label) {
        switch ($this->post_type) {
          case 'market':
            $banner_title = sprintf(__('%s Real Estate Market Home Price Chart', 'pidhomes'), $community_label);
            break;
          case 'school':
            $banner_title = sprintf(__('%s Concise School Report', 'pidhomes'), $community_label);
            break;
          case 'community':
            $banner_title = sprintf(__('%s Real Estate Concise Community Report', 'pidhomes'), $community_label);
            break;
          case 'cma':
            $banner_title = sprintf(__('%s Real Estate Market Analysis Report', 'pidhomes'), $community_label);
            break;
          default:
            $banner_title = sprintf(__('%s Real Estate Market Summary Page', 'pidhomes'), $community_label);
            break;
        }
      } else {
        $banner_title = __('Greater Vancouver Real Estate Market Summary Page', 'pidhomes');
      }
    }
    $this->banner_title = $banner_title;
    return $banner_title;
  }

  public function get_ip_address()
  {
    $remote_ip = false;

    // Check for shared Internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
      $remote_ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      // Check for IP addresses passing through proxies
      // Check if multiple IP addresses exist in var
      if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
        $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($iplist as $ip) {
          if ($this->validate_ip($ip))
            $remote_ip = $ip;
        }
      } else {
        if ($this->validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
          $remote_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      }
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_X_FORWARDED'])) {
      $remote_ip = $_SERVER['HTTP_X_FORWARDED'];
    } elseif (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
      $remote_ip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR']) && $this->validate_ip($_SERVER['HTTP_FORWARDED_FOR'])) {
      $remote_ip = $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_FORWARDED'])) {
      $remote_ip = $_SERVER['HTTP_FORWARDED'];
    } else {
      // Return unreliable IP address since all else failed
      $remote_ip = $this->validate_ip($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
    }
    if ($this->bot_detected()) {
      return $remote_ip;
    }
    // SAVE THE IP ADDRESS TO THE DATABASE
    // for test purpose, set $remote_ip to my ip 162.208.220.164
    // ip to location code from stack overflow
    $PublicIP = $remote_ip == "::1" ? "162.208.220.164" : $remote_ip;
    $json     = file_get_contents("http://ipinfo.io/$PublicIP/geo");
    $json     = json_decode($json, true);
    $country  = $json['country'];
    $region   = $json['region'];
    $city     = $json['city'];
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $myself_ip = !!array_search($remote_ip, self::MYSELF_IPS);
    $myself_ip = (int)$myself_ip;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $is_mobile = (int)is_mobile();
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    // PUSH cma data to table wp_pid_cma
    require_once(get_stylesheet_directory() . '/db/pdoConn.php');
    $sql = "INSERT INTO " . PID_DB_NAME . ".wp_pid_ips
    (
      request_uri,
      ip_addr,
      country,
      region,
      city,
      time_stamp,
      myself_ip, 
      user_agent,
      is_mobile,
      referer
    )
    VALUES(?,?,?,?,?,?,?,?,?,?)";

    if (isset($pdo) && $pdo != null) {

      try {
        $stmt_insert_ips = $pdo->prepare($sql);
        $pdo->beginTransaction();
        $stmt_insert_ips->execute(
          array(
            substr($actual_link, 0, 200),
            $remote_ip,
            $country,
            $region,
            $city,
            date('Y/m/d/ h:i:s', time()),
            $myself_ip,
            $user_agent,
            $is_mobile,
            $referer
          )
        );
        $pdo->commit();
      } catch (\Exception $e) {
        $pdo->rollback();
        echo '["PDO error"]';
        $stmt_insert_ips = null;
        $pdo = null;
        throw $e;
      }
      $stmt_insert_ips = null;
      $pdo = null;
    }
    // RETURN THE IP ADDRESS
    return $remote_ip;
  }

  // how to detect search engine bots with php?
  public function bot_detected()
  {
    return (isset($_SERVER['HTTP_USER_AGENT'])
      && preg_match('/bot|crawl|slurp|spider|mediapartners|facebookexternalhit/i', $_SERVER['HTTP_USER_AGENT']));
  }

  /**
   * Ensures an IP address is both a valid IP address and does not fall within
   * a private network range.
   */
  public function validate_ip($ip)
  {
    return true;
    //Code below only works for IPV4, escape it for now

    //::1 is the actual IP. It is an ipv6 loopback address 
    //(i.e. localhost). 
    //If you were using ipv4 it would be 127.0.0.1.
    if (strtolower($ip) === 'unknown' || $ip === ':::1' || $ip === '127.0.0.1')
      return false;

    // Generate IPv4 network address
    $ip = ip2long($ip);

    // If the IP address is set and not equivalent to 255.255.255.255
    if ($ip !== false && $ip !== -1) {
      // Make sure to get unsigned long representation of IP address
      // due to discrepancies between 32 and 64 bit OSes and
      // signed numbers (ints default to signed in PHP)
      $ip = sprintf('%u', $ip);

      // Do private network range checking
      if ($ip >= 0 && $ip <= 50331647)
        return false;
      if ($ip >= 167772160 && $ip <= 184549375)
        return false;
      if ($ip >= 2130706432 && $ip <= 2147483647)
        return false;
      if ($ip >= 2851995648 && $ip <= 2852061183)
        return false;
      if ($ip >= 2886729728 && $ip <= 2887778303)
        return false;
      if ($ip >= 3221225984 && $ip <= 3221226239)
        return false;
      if ($ip >= 3232235520 && $ip <= 3232301055)
        return false;
      if ($ip >= 4294967040)
        return false;
    }
    return true;
  }

  public function set_map_image($location)
  {
    $map_quest_uri = "https://open.mapquestapi.com/staticmap/v5/map?key=";
    $map_quest_key = "O4do0E5XvLukINybgodeWyriP8GGQQ7n";
    $loc = str_replace(" ", "%20", $location->name);
    $map_quest_center = "$loc,BC";
    if (!empty($location->map_location)) {
      $map_center_lat = $location->map_location['lat'];
      $map_center_lng = $location->map_location['lng'];
      $map_quest_image_uri = "$map_quest_uri$map_quest_key&zoom=10&center=$map_center_lat,$map_center_lng";
    } else {
      $map_quest_image_uri = "$map_quest_uri$map_quest_key&zoom=10&center=$map_quest_center";
    }
    return $map_quest_image_uri;
  }

  public function set_map_google_image($location)
  {
    $map_quest_uri = "https://maps.googleapis.com/maps/api/staticmap?";
    $map_size = "size=600x300";
    $map_path_fill = "fillcolor:0xAA000033%7Ccolor:0xFFFFFF00%7C";
    $map_path_encode = "enc:";
    $map_lang = $this->language == 'cn' ? 'zh' : 'en';
    $map_language = "language=$map_lang";
    $map_quest_key = "key=AIzaSyA0L_4hWeBKknmaycp-dgXrPx6bhutKxQE";
    $loc = str_replace(" ", "%20", $location->name);
    $map_google_center = "$loc,BC";
    if (!empty($location->map_location)) {
      $map_center_lat = $location->map_location['lat'];
      $map_center_lng = $location->map_location['lng'];
      $map_google_image_uri = `$map_quest_uri$map_quest_key&$map_size&zoom=12&center=$map_center_lat,$map_center_lng&$map_language`;
    } else {
      $map_google_image_uri = `$map_quest_uri$map_quest_key&$map_size&zoom=12&center=$map_google_center&$map_language`;
    }
    return $map_google_image_uri;
  }

  public function get_uri_query_vars()
  {
    $property_type_regex = '/\w+/';
    $chart_type_regex = '/chart=([p|d]\w+)/';
    $years_regex = '/y=((\d{4},?)+)/';
    $month_regex = '/mh=(\d{1,2})/';
    $query_vars = strtolower(get_query_var('dwell', 'all'));

    $match_property_type = [];
    $match_chart_type = [];
    $match_years = [];
    $match_month = [];

    // test $property_type is normal or is combined query vars
    $array_query_vars = explode('$', $query_vars);
    if (count($array_query_vars) > 1) {
      // combined query vars
      if (preg_match($property_type_regex, $query_vars, $match_property_type)) {
        $property_type = $match_property_type[0];
      } else {
        $property_type = 'groupbynbh';
      };
      if (preg_match($chart_type_regex, $query_vars, $match_chart_type)) {
        $chart_type = $match_chart_type[1];
      } else {
        $chart_type = 'perc';
      }
      if (preg_match($years_regex, $query_vars, $match_years)) {
        $years = $match_years[1];
        $years = explode(",", $years);
      } else {
        $years = explode(",", PID_CHART_YEARS);
      }
      if (preg_match($month_regex, $query_vars, $match_month)) {
        $month = $match_month[1];
      } else {
        $month = 1;
      }
    } else {
      // normal query vars
      $property_type = get_query_var('dwell', 'groupbynbh');
      $chart_type = get_query_var('chart', 'perc');
      $years = get_query_var('y', PID_CHART_YEARS);
      $years = explode(",", $years);
      $month = get_query_var('mh', '1');
    }

    $this->query_vars = array(
      'property_type' => $property_type,
      'chart_type' => $chart_type,
      'years' => $years,
      'month' => $month
    );

    return $this->query_vars;
  }
}
