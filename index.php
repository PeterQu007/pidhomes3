<?php

/**
 * Theme Index File
 *
 * @package realhomes
 */

use PIDHomes\{PIDEnv, Utility};

$utility = new Utility();
$env = new PIDEnv($utility::$location_slug);

$is_pid_home = false;

$post_type_regex = '/([a-z_-]+)[&|$]/i';
$post_type_regex2 = '/post_type=([a-z_-]+)[&|$]/i';
$post_type = '';
$matches = [];
if (isset($_GET['post_type'])) {
  if (preg_match($post_type_regex, $_GET['post_type'], $matches)) {
    $post_type = $matches[1];
  };
} else {
  if (count($_GET) > 0 && preg_match($post_type_regex2, $_GET[0], $matches)) {
    $post_type = $matches[1];
  };
}

switch ($post_type) {
  case 'rps_listing':
    get_template_part('realtypress/property-results');
    break;
  default:
    get_template_part('assets/' . INSPIRY_DESIGN_VARIATION . '/partials/index');
    // get_template_part('realtypress/property-results');
    break;
}
