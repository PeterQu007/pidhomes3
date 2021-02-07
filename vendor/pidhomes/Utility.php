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

class Utility
{
  private static $taxo = 'property-city';
  public static $location_slug = '';
  public static $language;
  public static $isset_google_map_api = false;
  const NON_ACTIVE = 'metabox__blog_home-link';

  /**
   * @param NONE
   */
  public function __construct($args = '')
  {
    global $language;
    self::$language = $language;
    self::$location_slug = self::get_location_slug();
    switch ($args) {
      case 'google_map':
      case 'google-map':
        add_action('wp_enqueue_scripts', array($this, 'pid_google_map'), PHP_INT_MAX);
        self::$isset_google_map_api = true;
        break;
    }
  }

  /**
   * Debug
   */
  public static function fetchCommunityPosts($post_type, $term_slug, $page)
  {
    wp_reset_postdata();
    wp_reset_query();
    $pid_posts = new \PID_WP_Query(array(
      'post_type' => $post_type,
      'tax_query' => array(
        array(
          'taxonomy' => 'property-city',
          'field' => 'slug',
          'terms' => $term_slug,
        ),
      ),
      'paged' => $page, //find the last page for URL
      'posts_per_page' => 3,
    ));
    return $pid_posts;
  }

  public static
  function exceptions_error_handler($severity, $message, $filename, $lineno)
  {
    if (error_reporting() == 0) {
      return;
    }
    if (error_reporting() & $severity) {
      throw new \ErrorException($message, 0, $severity, $filename, $lineno);
    }
  }

  public static function get_location_slug()
  {
    $location_slug = '';
    $location_slug = get_query_var('property-city');
    if ($location_slug === '') {
      if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != "") {
        $query_vars = $_SERVER['QUERY_STRING'];
        $query_vars = str_replace('%20', ' ', $query_vars); // recover space
        $query_vars = str_replace('%3D', '=', $query_vars); // recover = 
        $query_vars = str_replace('%24', '$', $query_vars); // recover $
        $query_vars = str_replace('%2C', ',', $query_vars); // recover ,
        // For facebaook, recover from unicode
        $query_vars = str_replace('\\\\u002520', ' ', $query_vars); // recover space
        $query_vars = str_replace('\\\\u00253D', '=', $query_vars); // recover = 
        $query_vars = str_replace('\\\\u002524', '$', $query_vars); // recover $
        $query_vars = str_replace('\\\\u00252C', ',', $query_vars); // recover ,
        $input_city_regex = '/input_city=([a-z\s_\+]+)/i';
        $input_community_name_regex = '/input_community_name=([a-z\s_\+]+)/i';
        $input_neighbourhood_regex = '/input_neighbourhood=([a-z\s_\+]+)/i';

        $matches = [];
        if (preg_match($input_neighbourhood_regex, $query_vars, $matches)) {
          $neighbourhood = str_replace('+', ' ', $matches[1]);
          $location_term = get_term_by('name', str_replace('_', ' ', $neighbourhood), 'property-city');
        } elseif (preg_match($input_community_name_regex, $query_vars, $matches)) {
          $community = str_replace('+', ' ', $matches[1]);
          $location_term = get_term_by('name', str_replace('_', ' ', $community), 'property-city');
        } elseif (preg_match($input_city_regex, $query_vars, $matches)) {
          $city = str_replace('+', ' ', $matches[1]);
          $location_term = get_term_by('name', str_replace('_', ' ', $city), 'property-city');
        } else {
          $location_term = get_term_by('slug', 'gva', 'property-city');
        };
        $location_slug = $location_term->slug;
      }
      if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] == "") {
        // TRY TO GET LOCATION FROM POST NAME
        $post_name = get_post_field('post_name', get_the_ID());
        $location_regex = '/(^[a-z][a-z\s_-]*)[_|-]/i';
        $matches = [];
        if (preg_match($location_regex, $post_name, $matches)) {
          $location_slug = $matches[1];
          $location_slug = str_replace(' ', '-', $location_slug);
          $location_slug = str_replace('%20', '-', $location_slug);
          $location_slug = str_replace('_', '-', $location_slug);
        }
        $location_term = get_term_by('slug', $location_slug, 'property-city');
        if (!$location_term) {
          $location_slug = 'gva';
        }
      }
    }
    return $location_slug;
  }

  public static function pid_google_map()
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

  public function get_rps_listing_uri_query_vars(array $args)
  {
    if ($args === array() || count($args) >= 3) {
      // the args have been processed correctly by realtypress
      return $args;
    }
    if ($this->has_string_keys($args)) {
      $query_vars = $args[array_keys($args)[0]];
      if ($query_vars === '' && count($args) <= 2) {
        $query_vars = array_keys($args)[0];
      }
    } else {
      return $args;
    }
    $query_vars = str_replace('%20', ' ', $query_vars); // recover space
    $query_vars = str_replace('%3D', '=', $query_vars); // recover = 
    $query_vars = str_replace('%24', '$', $query_vars); // recover $
    $query_vars = str_replace('%2C', ',', $query_vars); // recover ,
    // For facebaook, recover from unicode
    $query_vars = str_replace('\\\\u002520', ' ', $query_vars); // recover space
    $query_vars = str_replace('\\\\u00253D', '=', $query_vars); // recover = 
    $query_vars = str_replace('\\\\u002524', '$', $query_vars); // recover $
    $query_vars = str_replace('\\\\u00252C', ',', $query_vars); // recover ,

    $view_regex = '/view=([a-z]+)/i';
    $input_property_type_regex = '/input_property_type=([a-z\s])+/i';
    $input_business_type_regex = '/input_business_type=([a-z\s]+)/i';
    $input_transaction_type_regex = '/input_transaction_type=([a-z\s]+)/i';
    $input_building_type_regex = '/input_building_type=([a-z\s\/]+)/i';
    $input_construction_style_regex = '/input_construction_style=([a-z\s\/]+)/i';
    $input_bedrooms_regex = '/input_bedrooms=(\d+,\d+)/';
    $input_bedrooms_max_regex = '/input_bedrooms_max=(\d+)/';
    $input_baths_regex = '/input_baths=(\d+,\d+)/';
    $input_baths_max_regex = '/input_baths_max=(\d+)/';
    $input_price_regex = '/input_price=(\d+,\d+)/i';
    $input_price_max_regex = '/input_price_max=(\d+)/i';
    $input_street_address_regex = '/input_street_address=([a-z\d\s#-]+)/i';
    $input_city_regex = '/input_city=([a-z\s_-]+)/i';
    $input_community_name_regex = '/input_community_name=([a-z\s_-]+)/i';
    $input_neighbourhood_regex = '/input_neighbourhood=([a-z\s_-]+)/i';
    $input_province_regex = '/input_province=([a-z]+)/i';
    $input_postal_code_regex = '/input_postal_code=([a-z]\d[a-z]\d[a-z]\d)/i';
    $input_mls_regex = '/input_mls=([a-z\d]+)/i';
    $input_description_regex = '/input_description=([a-z\d\s#]+)/i';
    $input_condominium_regex = '/input_condominium=([0|1])/';
    $input_pool_regex = '/input_pool=([0|1])/';
    $input_waterfront_regex = '/input_waterfront=([0|1])/';
    $input_open_house_regex = '/input_open_house=([0|1])/';
    $sort_regex = '/sort=([a-z\s,_]+)/i';
    $input_agent_id_regex = '/input_agent_id=(\d+)/';
    $input_office_id_regex = '/input_office_id=(\d+)/';
    $num_columns_regex = '/num_columns=([\d,]+)/';
    $max_results_regex = '/max_results=(\d+)/';

    $query_vars = str_replace('pp=', 'posts_per_page=', $query_vars);
    $query_vars = str_replace('st=', 'sort=', $query_vars);
    $query_vars = str_replace('vw=', 'view=', $query_vars);
    $query_vars = str_replace('pt=', 'input_property_type=', $query_vars);
    $query_vars = str_replace('b1=', 'input_business_type=', $query_vars);
    $query_vars = str_replace('tt=', 'input_transaction_type=', $query_vars);
    $query_vars = str_replace('b2=', 'input_building_type=', $query_vars);
    $query_vars = str_replace('cs=', 'input_construction_style=', $query_vars);
    $query_vars = str_replace('bd=', 'input_bedrooms=', $query_vars);
    $query_vars = str_replace('bm=', 'input_bedrooms_max=', $query_vars);
    $query_vars = str_replace('ba=', 'input_baths=', $query_vars);
    $query_vars = str_replace('b3=', 'input_baths_max=', $query_vars);

    $query_vars = str_replace('pr=', 'input_price=', $query_vars);
    $query_vars = str_replace('pm=', 'input_price_max=', $query_vars);
    $query_vars = str_replace('sa=', 'input_street_address=', $query_vars);
    $query_vars = str_replace('pv=', 'input_province=', $query_vars);
    $query_vars = str_replace('po=', 'input_postal_code=', $query_vars);
    $query_vars = str_replace('ml=', 'input_mls=', $query_vars);
    $query_vars = str_replace('de=', 'input_description=', $query_vars);
    $query_vars = str_replace('co=', 'input_condominium=', $query_vars);
    $query_vars = str_replace('pl=', 'input_pool=', $query_vars);
    $query_vars = str_replace('wa=', 'input_waterfront=', $query_vars);
    $query_vars = str_replace('oh=', 'input_open_house=', $query_vars);

    // $x.replace('posts_per_page=','pp=').replace('sort=','st=').replace('view=','vw=').replace('input_property_type=','pt=').replace('input_business_type=','b1=')
    // .replace('input_building_type=','b2=').replace('input_construction_style=','cs=').replace('input_bedrooms=','bd=').replace('input_bedrooms_max=','bm=').replace('input_baths=','ba=').replace('input_baths_max=','b3=')
    // .replace('input_price=','pr=').replace('input_price_max=','pm=').replace('input_street_address=','sa=').replace('input_province=','pv=').replace('input_postal_code=','po=').replace('input_mls=','ml=')
    // .replace('input_description=','de=').replace('input_condominium=','co=').replace('input_pool=','pl=').replace('input_waterfront=','wa=').replace('input_open_house=','oh=')
    // window.location.href.replace(/[a-z_-]+=&/g,'').replace(/&/g,'$').replace('posts_per_page=','pp=').replace('sort=','st=').replace('view=','vw=').replace('input_property_type=','pt=').replace('input_business_type=','b1=').replace('input_building_type=','b2=').replace('input_construction_style=','cs=').replace('input_bedrooms=','bd=').replace('input_bedrooms_max=','bm=').replace('input_baths=','ba=').replace('input_baths_max=','b3=').replace('input_price=','pr=').replace('input_price_max=','pm=').replace('input_street_address=','sa=').replace('input_province=','pv=').replace('input_postal_code=','po=').replace('input_mls=','ml=').replace('input_description=','de=').replace('input_condominium=','co=').replace('input_pool=','pl=').replace('input_waterfront=','wa=').replace('input_open_house=','oh=')

    $matches = [];

    // test $property_type is normal or is combined query vars
    $array_query_vars = explode('$', $query_vars);
    $args[array_keys($args)[0]] = $array_query_vars[0];

    if (count($array_query_vars) > 1) {
      // combined query vars
      if (preg_match($view_regex, $query_vars, $matches)) {
        $args['view'] = $matches[1];
      };
      if (preg_match($input_property_type_regex, $query_vars, $matches)) {
        $args['input_property_type'] = $matches[1];
      };
      if (preg_match($input_business_type_regex, $query_vars, $matches)) {
        $args['input_business_type'] = $matches[1];
      }
      if (preg_match($input_transaction_type_regex, $query_vars, $matches)) {
        $args['input_transaction_type'] = $matches[1];
      }
      if (preg_match($input_building_type_regex, $query_vars, $matches)) {
        $args['input_building_type'] = $matches[1];
      };
      if (preg_match($input_construction_style_regex, $query_vars, $matches)) {
        $args['input_construction_style'] = $matches[1];
      };
      if (preg_match($input_bedrooms_regex, $query_vars, $matches)) {
        $args['input_bedrooms'] = $matches[1];
      }
      if (preg_match($input_bedrooms_max_regex, $query_vars, $matches)) {
        $args['input_bedrooms_max'] = $matches[1];
      }

      if (preg_match($input_baths_regex, $query_vars, $matches)) {
        $args['input_baths'] = $matches[1];
      };
      if (preg_match($input_baths_max_regex, $query_vars, $matches)) {
        $args['input_baths_max'] = $matches[1];
      };
      if (preg_match($input_price_regex, $query_vars, $matches)) {
        $args['input_price'] = $matches[1];
      }
      if (preg_match($input_price_max_regex, $query_vars, $matches)) {
        $args['input_price_max'] = $matches[1];
      }
      if (preg_match($input_street_address_regex, $query_vars, $matches)) {
        $args['input_street_address'] = $matches[1];
      };
      if (preg_match($input_city_regex, $query_vars, $matches)) {
        $args['input_city'] = $matches[1];
      };
      if (preg_match($input_community_name_regex, $query_vars, $matches)) {
        $args['input_community_name'] = $matches[1];
      }
      if (preg_match($input_neighbourhood_regex, $query_vars, $matches)) {
        $args['input_neighbourhood'] = $matches[1];
      }

      // group 3
      if (preg_match($input_province_regex, $query_vars, $matches)) {
        $args['input_province'] = $matches[1];
      };
      if (preg_match($input_postal_code_regex, $query_vars, $matches)) {
        $args['input_postal_code'] = $matches[1];
      };
      if (preg_match($input_mls_regex, $query_vars, $matches)) {
        $args['input_mls'] = $matches[1];
      }
      if (preg_match($input_description_regex, $query_vars, $matches)) {
        $args['input_description'] = $matches[1];
      }
      if (preg_match($input_condominium_regex, $query_vars, $matches)) {
        $args['input_condominium'] = $matches[1];
      };
      if (preg_match($input_pool_regex, $query_vars, $matches)) {
        $args['input_pool'] = $matches[1];
      };
      if (preg_match($input_waterfront_regex, $query_vars, $matches)) {
        $args['input_waterfront'] = $matches[1];
      }
      if (preg_match($input_open_house_regex, $query_vars, $matches)) {
        $args['input_open_house'] = $matches[1];
      }
      //group 5
      if (preg_match($sort_regex, $query_vars, $matches)) {
        $args['sort'] = $matches[1];
      }
      if (preg_match($input_agent_id_regex, $query_vars, $matches)) {
        $args['input_agent_id'] = $matches[1];
      };
      if (preg_match($input_office_id_regex, $query_vars, $matches)) {
        $args['input_office_id'] = $matches[1];
      };
      if (preg_match($num_columns_regex, $query_vars, $matches)) {
        $args['num_columns'] = $matches[1];
      }
      if (preg_match($max_results_regex, $query_vars, $matches)) {
        $args['max_results'] = $matches[1];
      }
    } else {
      // normal query vars
    }

    return $args;
  }


  //checking whether the array is zero-indexed and sequential
  public static function isAssoc(array $arr)
  {
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
  }

  //To merely check whether the array has non-integer keys (not whether the array is sequentially-indexed or zero-indexed):
  public static function has_string_keys(array $array)
  {
    return count(array_filter(array_keys($array), 'is_string')) > 0;
  }
  //---------------------
} // END OF UTILITY CLASS
