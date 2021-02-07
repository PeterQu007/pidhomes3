<?php

/** 
 *  MODULE OF FUNCTION.PHP
 *  ======================
 *  @neighborhoodID
 *  @return metabox for section banner menu
 *  
 *  @since [.200908] TRANSFER TO CLASS
 *  @
 */

namespace PIDHomes;

class Metabox
{
  private static $taxo = 'property-city';
  const NON_ACTIVE = 'metabox__blog_home-link';

  /**
   * @param NONE
   */
  public function __construct()
  {
    global $language;
  }

  /**
   * Get City Terms:: Top Level Location Terms
   * @example gva | vancouver surrey burnaby richmond ...
   */
  public static function get_gva_city_metabox()
  {
    $taxo = self::$taxo;
    $metabox = [];
    // Get gva term_id;
    $gva = PIDTerms::get_PIDTerm_by('slug', 'gva', $taxo);

    // Fetch property-city top level terms
    $city_terms = PIDTerms::get_PIDTerms(array(
      'taxonomy' => $taxo,
      'parent' => 0, //get direct children
      'exclude' => $gva->term_id, // exclude gva
      'orderby' => 'name', //district slug is named by [city]-#
      'order' => 'ASC', // ascending: 'ASC', descending: 'DESC',
      'hide_empty' => false,
    ));

    $metabox = self::create_metabox_by_pidterms($city_terms);

    return $metabox;
  }

  /**
   * @version Get 2 Level Location Terms By its Slug
   * @param city: string | property_city slug
   * @return metabox: array of normalized city members
   * @example surrey || north surrey | cloverdale | south surrey 
   */
  public static function get_city_district_metabox($city_slug)
  {
    $metabox = [];
    $taxo = self::$taxo;

    $city_term = PIDTerms::get_PIDTerm_by('slug', $city_slug);

    $district_terms = PIDTerms::get_PIDTerms(array(
      'taxonomy' => $taxo,
      'parent' => isset($city_term) ? $city_term->term_id : null, //get direct children
      'orderby' => 'slug', //district slug is named by [city]-#
      'order' => 'ASC', //'DESC',
      'hide_empty' => false,
    ));

    if (!$district_terms) {
      $terms = [$city_term];
    } else {
      $terms = array_merge([$city_term], $district_terms);
    }
    $metabox = self::create_metabox_by_pidterms($terms);

    return $metabox;
  }

  /**
   * @version Get 3 Level Location Terms By its Slug
   * @param nbh_slug
   * @return metabox[]
   */
  public static function get_city_district_nbh_metabox_by($nbh_slug)
  {
    $metabox = [];
    $terms = [];

    // get this term
    $_term = PIDTerms::get_PIDTerm_by('slug', $nbh_slug);
    // check the term level
    $term_level = PIDTerms::get_community_level($_term->term_id);
    array_push($terms, $_term);

    switch ($term_level) {
      case 2:
        // get parent term, North Surrey -> get Surrey
        if ($parent_term = PIDTerms::get_PIDTerm_by('id', $_term->parent)) {
          $terms = array_merge([$parent_term], $terms);
        }
        // get grand parent term, North Surrey -> get Fraser Heights etc.
        if ($children_terms = PIDTerms::get_PIDTerms(array(
          "child_of" => $_term->term_id,
          "taxonomy" => self::$taxo,
          'orderby' => 'name',
          'order' => 'ASC',
          'hide_empty' => false
        ))) {
          $terms = array_merge($terms, $children_terms);
        }
        $terms = PIDTerms::get_family_pidterms_by($nbh_slug);
        break;
      case 3:
      default:
        // get ancestors of $nbh_slug for example
        // e.g. Fraser Heights -> get North Surrey -> Surrey
        $terms = PIDTerms::get_ancestor_and_sibling_pidterms('slug', $nbh_slug);
        break;
    }

    $metabox = self::create_metabox_by_pidterms($terms);

    return $metabox;
  }

  /**
   * Get 3 Level Terms By Post ID
   * The Terms are assigned to the Post when edit in wordpress admin
   * @example Surrey -> North Surrey -> Fraser Heights -> PIDHomesPost
   */
  public static function get_city_district_nbh_metabox($nbh_post_id)
  {
    $taxo = self::$taxo;
    $metabox = [];
    $locations = array([], [], []);

    $locs = PIDTerms::get_the_PIDTerms($nbh_post_id);

    foreach ($locs as $loc) {
      $has_children = get_term_children($loc->term_id, $taxo);
      //Get Neighborhood (Level 3) Term
      if (!$has_children) {
        $locations[2]['term_id'] = $loc->term_id;
        $locations[2]['term_name'] = $loc->name;
        $locations[2]['term_slug'] = $loc->slug;
        $locations[2]['term_label'] = $loc->label;
        continue;
      }
      //Get District (Level 2) Term
      if ($loc->parent && $has_children) {
        $locations[1]['term_id'] = $loc->term_id;
        $locations[1]['term_name'] = $loc->name;
        $locations[1]['term_slug'] = $loc->slug;
        $locations[1]['term_label'] = $loc->label;
        continue;
      }
      //Get City (Level 1) Term
      if (!$loc->parent) {
        $locations[0]['term_id'] = $loc->term_id;
        $locations[0]['term_name'] = $loc->name;
        $locations[0]['term_slug'] = $loc->slug;
        $locations[0]['term_label'] = $loc->label;
      }
    }

    $metabox = self::create_metabox($locations);

    return $metabox;
  }


  /**
   * @version Get nbh Codes and Names
   * @param location: string | property_city slug
   * @param market_report_level: integer 1, 2, 3
   * @return nbhCodes_And_nbhNames: array
   */
  public static function get_nbhCodes_and_nbhNames($location, $market_report_level)
  {

    switch ($market_report_level) {
      case 1:
        $metabox = self::get_gva_city_metabox();
        break;
      case 2:
        $metabox = self::get_city_and_family_metabox_by_slug($location);
        break;
      case 3:
        $metabox = self::get_city_district_nbh_metabox_by($location);
        break;
    }

    if (!$metabox) {
      return false;
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
    $neighborhood_name_string = json_encode(isset($nbh_names) ? $nbh_names : array());
    $nbhCodesAndNames = array();
    array_push($nbhCodesAndNames, $neighborhood_code_string);
    array_push($nbhCodesAndNames, $neighborhood_name_string);
    return $nbhCodesAndNames;
  }



  /*******************
   * Private Functions
   * PID prefix functions are used to add i18n supports
   *******************/
  /**
   * @param $locations:: Array of Normalized location
   * @return $metabox:: Array of normalized location terms
   */
  private static function create_metabox($locations)
  {
    $metabox = [];
    foreach ($locations as $location) {
      $nbh_code = get_term_meta($location['term_id'], 'neighborhood_code', true);
      array_push($metabox, array(
        '0' => $location['term_id'],
        '1' => $location['term_name'],
        '2' => $location['term_slug'],
        '3' => $nbh_code,
        '4' => true,
        '5' => true,
        '6' => $location['term_label'],
        'Term_ID' => $location['term_id'],
        'Term_Name' => $location['term_name'],
        'Term_Slug' => $location['term_slug'],
        'Term_Code' => $nbh_code,
        'show_metabox' => true,
        'get_chartdata' => true,
        'Term_Label' => $location['term_label']
      ));
    }
    return $metabox;
  }

  private static function create_metabox_by_pidterms($pid_terms)
  {
    $metabox = [];
    foreach ($pid_terms as $pid_term) {
      $term_code = get_term_meta($pid_term->term_id, 'neighborhood_code', true);
      array_push($metabox, array(
        '0' => $pid_term->term_id,
        '1' => $pid_term->name,
        '2' => $pid_term->slug,
        '3' => $term_code,
        '6' => $pid_term->label,
        'Term_ID' => $pid_term->term_id,
        'Term_Name' => $pid_term->name,
        'Term_Slug' => $pid_term->slug,
        'Term_Code' => $term_code,
        'show_metabox' => true,
        'get_chartdata' => true,
        'Term_Label' => $pid_term->label
      ));
    }
    return $metabox;
  }




  /**
   * @version Get City and Family Members
   * @param city:: string, property-city slug
   * @param market_report_level
   * @return 
   */
  private static function get_city_and_family_metabox_by_slug($city)
  {
    $taxo = self::$taxo;
    $metabox = false;

    $city_term = PIDTerms::get_PIDTerm_by('slug', $city);
    $district_terms = [];

    if ($city_term) {
      // Get City District, e.g. :: North Surrey
      $district_terms = PIDTerms::get_PIDTerms(array(
        'taxonomy' => $taxo,
        'child_of' => $city_term->term_id, // get all children
        'orderby' => 'name', //district slug is named by [city]-#
        'order' => 'ASC', //'DESC',
        'hide_empty' => false,
      ));

      if ($district_terms) {
        // if district_terms or nbh_terms exist, merge both terms
        $city_family = array_merge([$city_term], $district_terms);
      } else {
        $city_family = [$city_term];
      }

      $metabox = self::create_metabox_by_pidterms($city_family);
    }

    return $metabox;
  }
}
// End of Metabox Class
