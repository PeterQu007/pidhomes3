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

class PIDHomesSEO
{
  public $location;
  public $site_locale;
  public $post_type;
  public $page_title;
  public $page_description;
  public $page_url;
  public $page_url_with_query;
  public $og_description;
  public $og_site_name;
  public $twitter_card;
  public $twitter_image;
  public $fb_image;
  public $lang;
  public $map_quest_image_uri;
  public $map_google_image_uri;
  public $is_bot_search_engine;

  /**
   * @param NONE
   */
  public function __construct($env)
  {
    global $language, $wp;
    $this->is_bot_search_engine = $this->is_bot_search_engine();
    $this->page_url = home_url($wp->request);
    $this->page_url_with_query = $this->build_social_share_url(home_url(add_query_arg($_GET, $wp->request)));
    $this->site_locale = get_locale();
    $this->location = $env->community_label;
    $this->post_type = $env->post_type;
    $this->page_title = $this->set_page_title();
    $this->page_description = $this->set_meta_description();
    $this->lang = $language;
    $this->map_quest_image_uri = $env->map_quest_image_uri;
    $this->map_google_image_uri = $env->map_google_image_uri;

    $this->set_meta_twitter();
    $this->set_meta_og();
  }

  public function set_page_title()
  {
    $_page_titles = [];
    $_page_title = get_field('page_title'); // set page_title in the page manually
    $_is_search_engine = $this->is_bot_search_engine;

    if (!empty($_page_title)) {
      // if page title has been set manually, return it
      return $_page_title;
    }

    $post_type = $this->post_type;
    $location = $this->location;
    // if is the home page / front page:
    if (is_home() || is_front_page()) {
      if ($_is_search_engine) {
        // for search engine, only do surrey realtor, helping to campaign keyword "Surrey Realtor"
        $_page_titles[0] = __("Surrey REALTOR | Peter Qu | Buy & Sell", 'pidhomes');
      } else {
        // for social media, change to greater vancouver realtor, helping to branding
        $_page_titles[0] = __("Vancouver REALTOR | Peter Qu | Buy & Sell", 'pidhomes');
      }
      return $_page_titles[0];
    }

    // if is other pages:
    // if is search engine, change the loaction in the page title, help to campaign more keywords by location
    if ($_is_search_engine) {
      switch ($post_type) {
        case 'community':
          $_page_titles[0] = sprintf(__("%s REALTOR", 'pidhomes'), $location);
          $_page_titles[1] = __("Peter Qu", 'pidhomes');
          $_page_titles[2] = sprintf(__('%s Communities Expert', 'pidhomes'), $location);
          $_page_titles[3] = __("Professionalism | Integrity | Diligence", 'pidhomes');
          break;
        case 'market':
          $_page_titles[2] = sprintf(__('%s REALTOR', 'pidhomes'), $location);
          $_page_titles[1] = __("Peter Qu", 'pidhomes');
          $_page_titles[0] = sprintf(__("%s Home Price Expert", 'pidhomes'), $location);
          $_page_titles[3] = "PIDHOMES.ca";
          break;
        case 'cma':
          $_page_titles[0] = sprintf(__("%s REALTOR", 'pidhomes'), $location);
          $_page_titles[1] = __("Peter Qu", 'pidhomes');
          $_page_titles[2] = sprintf(__('%s Market Analysis Expert', 'pidhomes'), $location);
          $_page_titles[3] = __("Professionalism | Integrity | Diligence", 'pidhomes');
          break;
        case 'rps_listing':
          $_page_titles[0] = sprintf(__("%s REALTOR", 'pidhomes'), $location);
          $_page_titles[1] = __("Peter Qu", 'pidhomes');
          $_page_titles[2] = sprintf(__('%s Listing Expert', 'pidhomes'), $location);
          $_page_titles[3] = __("Professionalism | Integrity | Diligence", 'pidhomes');
          break;
        case 'page':
          $_page_titles[0] = sprintf(__("%s REALTOR", 'pidhomes'), $location);
          $_page_titles[1] = __("Peter Qu", 'pidhomes');
          $_page_titles[2] = sprintf(__('%s Listing Expert', 'pidhomes'), $location);
          $_page_titles[3] = __("Professionalism | Integrity | Diligence", 'pidhomes');
          break;
        default:
          $_page_titles[0] = sprintf(__("%s REALTOR", 'pidhomes'), $location);
          $_page_titles[1] = __("Peter Qu", 'pidhomes');
          $_page_titles[2] = sprintf(__('%s Real Estate Expert', 'pidhomes'), $location);
          $_page_titles[3] = __("Professionalism | Integrity | Diligence", 'pidhomes');
          break;
      }
    } else {
      // if is not search engine, only branding to greater vancouver realtor, helping to brand one focus
      $_page_titles[0] = __("Vancouver REALTOR", 'pidhomes');
      $_page_titles[1] = __("Peter Qu", 'pidhomes');
      switch ($post_type) {
        case 'community':
          $_page_titles[2] = sprintf(__('%s Communities Summary Report', 'pidhomes'), $location);
          $_page_titles[3] = __("Professionalism | Integrity | Diligence", 'pidhomes');
          break;
        case 'market':
          $_page_titles[2] = sprintf(__('%s Real Estate Market Charts', 'pidhomes'), $location);
          $_page_titles[3] = "PIDHOMES.ca";
          break;
        case 'cma':
          $_page_titles[2] = sprintf(__('%s Market Analysis Report', 'pidhomes'), $location);
          $_page_titles[3] = __("Professionalism | Integrity | Diligence", 'pidhomes');
          break;
        case 'rps_listing':
          $_page_titles[2] = sprintf(__('%s Active Listings', 'pidhomes'), $location);
          $_page_titles[3] = __("Professionalism | Integrity | Diligence", 'pidhomes');
          break;
        case 'page':
          $_page_titles[2] = sprintf(__('%s Listing', 'pidhomes'), $location);
          $_page_titles[3] = __("Professionalism | Integrity | Diligence", 'pidhomes');
          break;
        default:
          $_page_titles[2] = sprintf(__('%s Real Estate Expert', 'pidhomes'), $location);
          $_page_titles[3] = __("Professionalism | Integrity | Diligence", 'pidhomes');
          break;
      }
    }
    $page_title = implode(" | ", $_page_titles);
    // var_dump($page_title);
    return $page_title;
  }

  public function set_meta_description()
  {
    $_meta_desc = [];
    $_meta_desc[0] = get_field('meta_description');
    $_is_search_engine = $this->is_bot_search_engine;

    if (!empty($_meta_desc[0])) {
      return $_meta_desc[0];
    }
    $post_type = $this->post_type;
    $location = $this->location;
    // use the same principle as page title, distinguish the search engine and the social media
    if (is_home() || is_front_page()) {
      if ($_is_search_engine) {
        $_meta_desc[0] = __("Surrey REALTOR, Peter Qu, Home for sale in Surrey, South Surrey, White Rock, Fraser Heights, Fleetwood, Cloverdale, Single House, Townhouse, Apartment Condo By Surrey Real Estate Agent Peter Qu", 'pidhomes');
      } else {
        $_meta_desc[0] = __("Vancouver REALTOR, Peter Qu, Home for sale in Vancouver,Surrey,Burnaby,Richmond,Coquitlam,Port Moody,White Rock,Single House, Townhouse, Apartment Condo By Vancouver Real Estate Agent Peter Qu", 'pidhomes');
      }
      return $_meta_desc[0];
    }

    if ($_is_search_engine) {
      switch ($post_type) {
        case 'community':
          $_meta_desc[0] =
            __("Surrey Real Estate Neighborhoods And Communities Expert, Surrey REALTOR, Peter Qu, Surrey Home for sale, South Surrey, Cloverdale, White Rock, Fraser Heights, Fleetwood, Morgan Heights", 'pidhomes');
          $_meta_desc[1] = __("Surrey Real Estate Professional and Real Estate Expert, single house, townhouse, apartment, condo, residential, commercial", 'pidhomes');
          break;
        case 'market':
          $_meta_desc[0] =
            __("Surrey Real Estate Home Price Expert, Free Home Evaluation, Surrey REALTOR, Peter Qu, Surrey Home for sale", 'pidhomes');
          $_meta_desc[1] = __("Surrey Real Estate Professional and Expert, South Surrey, White Rock, Fraser Heights, Fleetwood, Morgan Heights, Cloverdale, Guilford", 'pidhomes');
          break;
        case 'cma':
          $_meta_desc[0] =
            __("Surrey Real Estate CMA Market Analysis Expert, Surrey REALTOR, Peter Qu, Surrey Home for sale", 'pidhomes');
          $_meta_desc[1] = __("Surrey Real Estate Professional and Expert, South Surrey, White Rock, Fraser Heights, Fleetwood, Morgan Heights, Cloverdale, Guilford", 'pidhomes');
          break;
        case 'listing':
        case 'rps_listing':
        case 'page':
          $_meta_desc[0] = sprintf(__("%s Home For Sale, Listing Expert, Single House, Townhouse, Apartment, Condo, Surrey REALTOR, Peter Qu", 'pidhomes'), $this->location);
          $_meta_desc[1] = __("Surrey Real Estate Professional and Expert, South Surrey, White Rock, Fraser Heights, Fleetwood, Morgan Heights, Cloverdale, Guilford", 'pidhomes');
          break;
        case 'page':
        default:
          $_meta_desc[0] = __("Surrey REALTOR, Peter Qu, Buy and Sell in Surrey, Home for sale in Surrey, South Surrey, White Rock, Fraser Heights, Fleetwood, Cloverdale, Single House, Townhouse, Apartment Condo By Surrey Realtor Peter Qu", 'pidhomes');
          $_meta_desc[1] = __("Surrey Real Estate Professional and Expert, South Surrey, White Rock, Fraser Heights, Fleetwood, Morgan Heights, Cloverdale, Guilford", 'pidhomes');
          break;
      }
    } else {
      $_meta_desc[0] = __("Vancouver REALTOR, Peter Qu, Buy and Sell in Vancouver, Home for sale in Vancouver, Single House, Townhouse, Apartment Condo By Vancouver Realtor Peter Qu", 'pidhomes');
      $_meta_desc[1] = __("Vancouver Real Estate Professional and Expert, Vancouver,Burnaby,Richmond,Surrey,White Rock,Coquitlam", 'pidhomes');
    }
    $meta_desc = implode(" | ", $_meta_desc);
    return $meta_desc;
  }

  public function set_meta_og()
  {
    $_is_search_engine = $this->is_bot_search_engine;
    if ($_is_search_engine) {
      $this->og_site_name = __("Surrey REALTOR Peter Qu", 'pidhomes');
    } else {
      $this->og_site_name = __("Vancouver REALTOR Peter Qu", 'pidhomes');
    }
    $this->og_title = $this->page_title;
  }

  public function set_meta_twitter()
  {
    $upload_dir = wp_upload_dir();
    $this->twitter_card = "summary_large_image";
    $this->twitter_image = $this->lang == 'en' ?
      $upload_dir['baseurl'] . "/2020/09/Landing-Page-1-Mobile-EN.jpg" :
      $upload_dir['baseurl'] . "/2020/09/Landing-Page-1-Mobile-CN.jpg";
    // use map quest image:
    $this->twitter_image = $this->map_quest_image_uri;
    // $this->fb_image = is_post_type_archive('market') ? $this->map_quest_image_uri : $this->map_google_image_uri;
    $this->fb_image = $this->map_quest_image_uri;
  }

  public function get_social_meta($post_id)
  {
    $tw_card = get_post_meta($post_id, 'twitter_card');
    $tw_url = get_post_meta($post_id, 'twitter_url');
    $tw_title = get_post_meta($post_id, 'twitter_title');
    $tw_image = get_post_meta($post_id, 'twitter_pic');
    $tw_image_w = get_post_meta($post_id, 'twitter_pic_w');
    $tw_image_h = get_post_meta($post_id, 'twitter_pic_h');
    $tw_desc = get_post_meta($post_id, 'twitter_desc');
    $tw_type = get_post_meta($post_id, 'twitter_type');
    if ($tw_image) {
      $tw_image = str_replace("'", '"', $tw_image[0]);
      $og_image = str_replace("twitter:image", "og:image", $tw_image);
      echo   "<$og_image />";
    } else {
      echo '<meta name="og:image" content="' . $this->fb_image . '">';
    }
    if ($tw_image_w) {
      echo "<$tw_image_w[0] />"; //og:image:width
    };
    if ($tw_image_h) {
      echo "<$tw_image_h[0] />"; //og:image:height
    };

    if ($tw_card) {
      $tw_card = str_replace("'", '"', $tw_card[0]);
      echo "<$tw_card />";
    } else {
      echo '<meta name="twitter:card" content="' . $this->twitter_card . '">';
    }
    if ($tw_image) {
      echo "<$tw_image />";
    } else {
      echo '<meta property="twitter:image" content="' . $this->twitter_image . '">';
    }
    if ($tw_url) {
      $tw_url = str_replace("'", '"', $tw_url[0]);
      echo "<$tw_url />";
    } else {
      echo '<meta property="og:url" content="' . $this->page_url_with_query . '">';
    }
    if ($tw_title) {
      $tw_title = str_replace("'", '"', $tw_title[0]);
      echo "<$tw_title />";
    } else {
      echo '<meta property="og:title" content="' . $this->page_title . '">';
    }
    if ($tw_desc) {
      $tw_desc = str_replace("'", '"', $tw_desc[0]);
      echo "<$tw_desc />";
    } else {
      echo '<meta property="og:description" content="' . $this->page_description . '">';
    }
    if ($tw_type) {
      $tw_type = str_replace("'", '"', $tw_type[0]);
      echo "<$tw_type />";
    } else {
      echo '<meta property="og:type" content="article">';
    }

    echo '<meta property="og:site_name" content="' . $this->og_site_name . '">';
    echo '<meta property="og:locale" content="' . $this->site_locale . '">';
  }

  public function set_pidhomes_title()
  {
    global $language;
    if ($language != '') {
      $title_tag = current_theme_supports('title-tag');
      if ($title_tag) {
        add_filter('pre_get_document_title', [$this, 'pid_change_page_title']);
      }
      add_filter('the_title', [$this, 'pid_change_post_title'], 10, 2);
    } else {
      $title_tag = current_theme_supports('title-tag');
      if ($title_tag) {
        add_filter('pre_get_document_title', [$this, 'pid_change_page_title']);
      }
    }
  }

  public function unset_pidhomes_title()
  {
    add_action('wp_head', [$this, 'wp_render_title_tag'], 1);
  }

  public function wp_render_title_tag()
  {
    remove_action('wp_head', 'wp_render_title_tag', 1);
  }

  public function pid_change_page_title($title)
  {
    global $lang_set;
    return wp_strip_all_tags($this->page_title);
  }

  public function pid_change_post_title($title, $id)
  {
    if (get_post_type($id) == "post") {
      global $lang_set;
      $title = $this->page_title;
    }
    return  $title;
  }

  // how to detect search engine bots with php?
  public function bot_detected()
  {
    return (isset($_SERVER['HTTP_USER_AGENT'])
      && preg_match('/bot|crawl|slurp|spider|mediapartners|facebookexternalhit/i', $_SERVER['HTTP_USER_AGENT']));
  }

  // check if is a search engine
  public function is_bot_search_engine()
  {
    // refer to https://www.facebook.com/robots.txt, for bot names
    return (isset($_SERVER['HTTP_USER_AGENT'])
      && preg_match('/applebot|baidu|-google|googlebot|bingbot|crawl|slurp|spider|mediapartners|ia_archiver|msnbot|naverbot|pingdom|seznambot|slurp|teoma|yandex|yeti/i', $_SERVER['HTTP_USER_AGENT']));
  }
  // check if is a social media
  public function is_bot_social_media()
  {
    return (isset($_SERVER['HTTP_USER_AGENT'])
      && preg_match('/twitterbot|twitter|facebookexternalhit/i', $_SERVER['HTTP_USER_AGENT']));
  }

  // build social media friendly url
  public function build_social_share_url($url)
  {
    $_url = $url;
    $_url = str_replace('posts_per_page=', 'pp=', $_url);
    $_url = str_replace('sort=', 'st=', $_url);
    $_url = str_replace('view=', 'vw=', $_url);
    $_url = str_replace('input_property_type=', 'pt=', $_url);
    $_url = str_replace('input_business_type=', 'b1=', $_url);
    $_url = str_replace('input_building_type=', 'b2=', $_url);
    $_url = str_replace('input_construction_style=', 'cs=', $_url);
    $_url = str_replace('input_transaction_type=', 'tt=', $_url);
    $_url = str_replace('input_bedrooms=', 'bd=', $_url);
    $_url = str_replace('input_bedrooms_max=', 'bm=', $_url);
    $_url = str_replace('input_baths=', 'ba=', $_url);
    $_url = str_replace('input_baths_max=', 'b3=', $_url);
    $_url = str_replace('input_price=', 'pr=', $_url);
    $_url = str_replace('input_price_max=', 'pm=', $_url);
    $_url = str_replace('input_street_address=', 'sa=', $_url);
    $_url = str_replace('input_province=', 'pv=', $_url);
    $_url = str_replace('input_postal_code=', 'po=', $_url);
    $_url = str_replace('input_mls=', 'ml=', $_url);
    $_url = str_replace('input_description=', 'de=', $_url);
    $_url = str_replace('input_condominium=', 'co=', $_url);
    $_url = str_replace('input_pool=', 'pl=', $_url);
    $_url = str_replace('input_waterfront=', 'wa=', $_url);
    $_url = str_replace('input_open_house=', 'oh=', $_url);

    $_url = str_replace('posts_per_page&', 'pp=', $_url);
    $_url = str_replace('sort=', 'st=', $_url);
    $_url = str_replace('view&', 'vw=', $_url);
    $_url = str_replace('input_property_type&', '', $_url);
    $_url = str_replace('input_business_type&', '', $_url);
    $_url = str_replace('input_building_type&', '', $_url);
    $_url = str_replace('input_construction_style&', '', $_url);
    $_url = str_replace('input_transaction_type', '', $_url);
    $_url = str_replace('input_bedrooms&', '', $_url);
    $_url = str_replace('input_bedrooms_max&', '', $_url);
    $_url = str_replace('input_baths&', '', $_url);
    $_url = str_replace('input_baths_max&', '', $_url);
    $_url = str_replace('input_price&', '', $_url);
    $_url = str_replace('input_price_max&', '', $_url);
    $_url = str_replace('input_street_address&', '', $_url);
    $_url = str_replace('input_community_name&', '', $_url);
    $_url = str_replace('input_neighbourhood&', '', $_url);
    $_url = str_replace('input_city&', '', $_url);
    $_url = str_replace('input_province&', '', $_url);
    $_url = str_replace('input_postal_code&', '', $_url);
    $_url = str_replace('input_mls&', '', $_url);
    $_url = str_replace('input_description&', '', $_url);
    $_url = str_replace('input_condominium&', '', $_url);
    $_url = str_replace('input_pool&', '', $_url);
    $_url = str_replace('input_waterfront&', '', $_url);
    $_url = str_replace('input_open_house&', '', $_url);

    $_url = str_replace('&', '$', $_url);
    $_url = str_replace(' ', '_', $_url);
    return $_url;

    //modify the social link for share.min.js module of REALHOMES
    //replace('posts_per_page$','').replace('sort$','').replace('view$','').replace('input_property_type$','').replace('input_business_type$','').replace('input_building_type$','').replace('input_construction_style$','').replace('input_bedrooms$','').replace('input_bedrooms_max$','').replace('input_baths$','').replace('input_baths_max$','').replace('input_price$','').replace('input_price_max$','').replace('input_street_address$','').replace('input_province$','').replace('input_postal_code$','').replace('input_mls$','').replace('input_description$','').replace('input_condominium$','').replace('input_pool$','').replace('input_waterfront$','').replace('input_open_house$','')
    //.replace('input_city$','').replace('input_community_name$','').replace('neighbourhood$','')
    //.replace(' ','%20')
  }
}
