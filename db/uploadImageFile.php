<?php

// echo "imageFile!";
// requires php5  

// $upload_dir = sys_get_temp_dir() . '\\';
// define('UPLOAD_DIR', $upload_dir);
// $img = $_POST['imgBase64'];
// $img = str_replace('data:image/png;base64,', '', $img);
// $img = str_replace(' ', '+', $img);
// $data = base64_decode($img);
// $file = UPLOAD_DIR . uniqid() . '.png';
// $success = file_put_contents($file, $data);
// print $success ? $file : 'Unable to save the file.';

// ignore_user_abort(true);
// set_time_limit(0);
// ob_start();
define('WP_USE_THEMES', false);
require_once("../../../../wp-load.php");
if (!function_exists('wp_crop_image')) {
  include(ABSPATH . 'wp-admin/includes/image.php');
}
/* Getting file name */
$filename = $_FILES['fileToUpload']['name'];
$chart_params = json_decode(stripslashes($_POST['chartParams']));
/* Location */
$location = wp_upload_dir()['path'] . $filename;
$uploadOk = 1;
$imageFileType = pathinfo($location, PATHINFO_EXTENSION);

/* Valid Extensions */
$valid_extensions = array("jpg", "jpeg", "png");
/* Check file extension */
if (!in_array(strtolower($imageFileType), $valid_extensions)) {
  $uploadOk = 0;
}

if ($uploadOk == 0) {
  echo 0;
} else {
  /* Upload file */
  if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $location)) {
    $new = array(
      'post_title' => 'Our new post',
      'post_content' => 'This is the content of our new post.',
      'post_status' => 'publish',
    );
    $post_id = wp_insert_post($new);
    if (is_wp_error($post_id)) {
      $errors = $post_id->get_error_messages();
      foreach ($errors as $error) {
        echo "- " . $error . "<br />";
      }
    } else {
      wp_set_object_terms($post_id, 'Advertisement', 'category');
    }

    if ($post_id) {
      echo $post_id . ",";
      // header('Connection: close');
      // header('Content-Length: ' . ob_get_length());
      // ob_end_flush();
      // ob_flush();
      // flush();

      session_write_close();
      fastcgi_finish_request();
      // Attachment attributes for file
      $attachment = array(
        'post_mime_type' => "image/$imageFileType",  // file type
        'post_title' => sanitize_file_name($location),  // sanitize and use image name as file name
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
        $message = $IMGFileName . ' has been added as featured image to post.';
      } else {
        $message = $IMGFileName . ' has NOT been added as featured image to post.';
      }
    }
  } else {
    echo 0;
  }
}
