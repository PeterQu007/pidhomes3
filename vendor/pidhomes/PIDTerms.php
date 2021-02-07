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

class PIDTerms
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
   * @version get_gva_cities
   * @param 
   * @return gva_cities :: WP_Terms
   */
  public static function get_gva_cities()
  {
    $taxo = self::$taxo;
    $gva_term = get_term_by('slug', 'gva', $taxo);
    $gva_term_id = $gva_term->id;
    $cities = self::get_PIDTerms(array(
      'taxonomy' => 'property-city',
      'parent' => 0,
      'hide_empty' => false,
      'order' => 'DESC', //DESC: descending; ASC: ascending
      'exclude' => array($gva_term_id), // exclude gva
      'meta_query' => array(array(
        'key' => 'featured',
        'value' => true,
        'compare' => '='
      ))
    ));
    return $cities;
  }

  /**
   * Get 3 Level Terms By Post ID
   * The Terms are assigned to the Post when edit in wordpress admin
   * @example Surrey -> North Surrey -> Fraser Heights -> PIDHomesPost
   */
  public static function get_ancestor_pidterms($field_name, $field_value)
  {
    $taxo = self::$taxo;
    $ancestors = [];
    $_term_id = '';
    $_args = array(
      'format' => 'slug',
      'separator' => ',',
      'link' => false,
      'inclusive' => true
    );
    switch ($field_name) {
      case 'id':
        $_term_id = $field_value;
        break;
      case 'name':
        break;
      case 'slug':
        $_term = get_term_by('slug', $field_value, $taxo);
        $_term_id = $_term->term_id;
        break;
    }

    $_term_slugs = get_term_parents_list($_term_id, $taxo, $_args);
    $_term_slugs = explode(',', $_term_slugs);
    foreach ($_term_slugs as $_slug) {
      $ancestor = self::get_pidterm_by('slug', $_slug);
      if ($ancestor) {
        array_push($ancestors, $ancestor);
      }
    }

    return $ancestors;
  }

  /**
   * Get 3 Level Terms By Post ID
   * The Terms are assigned to the Post when edit in wordpress admin
   * @example Surrey -> North Surrey -> Fraser Heights -> PIDHomesPost
   */
  public static function get_ancestor_and_sibling_pidterms($field_name, $field_value)
  {
    $taxo = self::$taxo;
    $all_members = [];
    $_term_id = '';
    $_args = array(
      'format' => 'slug',
      'separator' => ',',
      'link' => false,
      'inclusive' => false // not include this-term
    );
    switch ($field_name) {
      case 'id':
        $_term_id = $field_value;
        $_term = get_term_by('id', $field_value, $taxo);
        break;
      case 'name':
        break;
      case 'slug':
        $_term = get_term_by('slug', $field_value, $taxo);
        $_term_id = $_term->term_id;
        break;
    }

    $_term_slugs = get_term_parents_list($_term_id, $taxo, $_args);
    $_term_slugs = explode(',', $_term_slugs);
    foreach ($_term_slugs as $_slug) {
      $ancestor = self::get_pidterm_by('slug', $_slug);
      if ($ancestor) {
        array_push($all_members, $ancestor);
      }
    }
    // get sibling terms
    $_siblings = self::get_PIDTerms(array(
      'taxonomy' => $taxo,
      'parent' => $_term->parent,
      'hide_empty' => false,
    ));
    if ($_siblings) {
      $all_members = array_merge($all_members, $_siblings);
    }

    return $all_members;
  }

  /**
   * Get 3 Level Terms By Post ID
   * The Terms are assigned to the Post when edit in wordpress admin
   * @example Surrey -> North Surrey -> Fraser Heights -> PIDHomesPost
   */
  public static function get_city_pidterms($field_name, $field_value)
  {
    // slug 0 is the city slug
    $city = false;
    $ancestors = self::get_ancestor_pidterms($field_name, $field_value);
    if ($ancestors) {
      $city = $ancestors[0];
    }
    return $city;
  }


  /**
   * @version Get Parents and Children
   * @param nbh_slug string | 'surrey1'
   * @return family_terms PIDTerms | Surrey -> North Surrey -> [Fraser Heights ...]
   * @example Surrey -> North Surrey -> Fraser Heights
   */
  public static function get_family_pidterms_by($nbh_slug)
  {
    $_term = self::get_PIDTerm_by('slug', $nbh_slug);
    $terms = [];
    array_push($terms, $_term);
    // get parent term, North Surrey -> get Surrey
    if ($parent_term = self::get_PIDTerm_by('id', $_term->parent)) {
      $terms = array_merge([$parent_term], $terms);
    }
    // get grand parent term, North Surrey -> get Fraser Heights etc.
    if ($children_terms = self::get_PIDTerms(array(
      "child_of" => $_term->term_id,
      "taxonomy" => self::$taxo,
      'orderby' => 'name',
      'order' => 'ASC',
      'hide_empty' => false
    ))) {
      $terms = array_merge($terms, $children_terms);
    }
    return $terms;
  }

  /**
   * @version get_city_district_PIDTerms_by
   * @param field_name:: slug string
   * @param field_value:: location string
   * @return PIDTerms:: WP_Terms Array
   * @example (slug, surrey) => surrey | north surrey | fraser heights ...
   */
  public static function get_city_district_PIDTerms_by($field_name, $field_value)
  {
    $taxo = self::$taxo;
    $city_term = self::get_PIDTerm_by($field_name, $field_value);

    $district_terms = self::get_PIDTerms(array(
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

    return $terms;
  }

  /**
   * @version get_city_district_nbh_PIDTerms_by
   * @param field_name:: slug string
   * @param field_value:: location string
   * @return PIDTerms:: WP_Terms Array
   * @example (slug, surrey) => surrey | north surrey | fraser heights ...
   */
  public static function get_city_district_nbh_PIDTerms_by($field_name, $field_value)
  {
    $terms = [];

    // get this term
    $this_term = self::get_PIDTerm_by($field_name, $field_value);
    // check the term level
    $term_level = get_field('community_level', self::$taxo . '_' . $this_term->term_id);
    array_push($terms, $this_term);

    switch ($term_level) {
      case 2:
        // get parent term, North Surrey -> get Surrey
        if ($parent_term = self::_get_PIDTerm_by('id', $this_term->parent)) {
          $terms = array_merge([$parent_term], $terms);
        }
        // get grand parent term, North Surrey -> get Fraser Heights etc.
        if ($children_terms = self::get_PIDTerms(array(
          "child_of" => $this_term->term_id,
          "taxonomy" => self::$taxo,
          'orderby' => 'name',
          'order' => 'ASC',
          'hide_empty' => false
        ))) {
          $terms = array_merge($terms, $children_terms);
        }
        break;
      case 3:
      default:
        // get parent term Fraser Heights -> get North Surrey
        $parent_term = self::_get_PIDTerm_by('id', $this_term->parent);
        if (!$parent_term) {
          $terms = array_merge([$parent_term], $terms);
          // get grand parent term, Fraser Heights -> get Surrey
          $grant_parent_term = self::_get_PIDTerm_by('id', $parent_term->parent);
          if (!$grant_parent_term) {
            $terms = array_merge([$grant_parent_term], $terms);
          }
        }
        break;
    }

    return $terms;
  }


  /**
   * @param $nbhPostID:: integer ID# of the Post/PIDHomesPost
   * @return PIDTerms_with_i18n_label
   */
  public static function get_the_PIDTerms($nbhPostID)
  {
    $taxo = self::$taxo;
    $terms = get_the_terms($nbhPostID, $taxo);
    $PIDTerms = false;
    if ($terms && count($terms) > 0) {
      $PIDTerms = self::_get_PIDTerms($terms);
    }
    return $PIDTerms;
  }

  /**
   * @param $args for getting WP_Terms
   * @return PIDTerms_with_i18n_label
   */
  public static function get_PIDTerms($args)
  {
    $terms = get_terms($args);
    $PIDTerms = false;
    if ($terms && count($terms) > 0) {
      $PIDTerms = self::_get_PIDTerms($terms);
    }
    return $PIDTerms;
  }

  /**
   * @param $terms:: regular WP_Term
   * @return $terms:: PIDTerm With language label
   */
  private static function _get_PIDTerms($terms)
  {
    foreach ($terms as $term) {
      $term->{'label'} =
        self::get_i18n_label($term->term_id) ?? $term->name;
      $term->{'neighborhood_code'} = self::get_neighborhood_code($term->term_id);
      $term->{'map_location'} = self::get_map_location($term->term_id);
    }
    return $terms;
  }

  /**
   * @param
   * @return
   */
  public static function get_PIDTerm_by($field_name, $field_value)
  {
    $taxo = self::$taxo;
    $pid_term = false;

    // define nbh_code as a meta value sign
    if ($field_name == 'nbh_code') {
      $meta_query =  array(
        'key'     => 'neighborhood_code',
        'value'   => $field_value,
        'compare'  => '='

      );
      $pid_terms = get_terms(array(
        'taxonomy'       => $taxo,
        'hide_empty'     => false,
        'meta_query'    => array($meta_query)
      ));
      if (!$pid_terms || count($pid_terms) == 0) {
        return false;
      } else {
        $pid_term = $pid_terms[0];
        $pid_term->{'label'} = self::get_i18n_label($pid_term->term_id) ?? $pid_term->name;
        $pid_term->{'neighborhood_code'} = self::get_neighborhood_code($pid_term->term_id);
        $pid_term->{'map_location'} = self::get_map_location($pid_term->term_id);
        return $pid_term;
      }
    }
    $pid_term = get_term_by($field_name, $field_value, $taxo);
    if ($pid_term) {
      $pid_term->{'label'} = self::get_i18n_label($pid_term->term_id) ?? $pid_term->name;
      $pid_term->{'neighborhood_code'} = self::get_neighborhood_code($pid_term->term_id);
      $pid_term->{'map_location'} = self::get_map_location($pid_term->term_id);
    }
    return $pid_term;
  }

  /**
   * @param term_id:: integer term_id
   * @return term's i18n_label as per language setting
   */
  private static function get_i18n_label($term_id)
  {
    global $language;
    $taxo = self::$taxo;
    $term_label = "";
    switch ($language) {
      case 'cn':
        $term_label = get_field('chinese_title', "{$taxo}_" . $term_id);
        break;
      case 'hk':
        $term_label = get_field('hongkong_title', "{$taxo}_" . $term_id);
        break;
      case 'en':
        $term_label = null;
        break;
    }
    if (empty($term_label)) {
      return null;
    }
    return $term_label;
  }

  /**
   * @param term_id:: integer term_id
   * @return term's neighborhood code 
   */
  private static function get_neighborhood_code($term_id)
  {
    $taxo = self::$taxo;
    $term_neighborhood_code = "";
    $term_neighborhood_code = get_field('neighborhood_code', "{$taxo}_" . $term_id);
    if (empty($term_neighborhood_code)) {
      return "";
    }
    return $term_neighborhood_code;
  }

  private static function get_map_location($term_id)
  {
    $taxo = self::$taxo;
    $term_map_location = "";
    $term_map_location = get_field('map_location', "{$taxo}_" . $term_id);
    if (empty($term_map_location)) {
      return "";
    }
    return $term_map_location;
  }

  /**
   * @version get_community_level
   * @param community_term_id | int
   * @return community_level | int
   */
  public static function get_community_level($community_term_id)
  {
    $community_level = get_field('community_level', self::$taxo . '_' . $community_term_id) ?? 3;
    return $community_level;
  }
}
// End of Metabox Class
