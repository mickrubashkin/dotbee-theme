<?php
function dotbee_theme_setup() {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'dotbee_theme_setup');

function dotbee_register_menus() {
  register_nav_menus([
    'main_menu' => __('Main Menu', 'dotbee'),
  ]);
}
add_action('after_setup_theme', 'dotbee_register_menus');


function dotbee_enqueue_scripts() {
  wp_enqueue_style('dotbee-style', get_stylesheet_uri());

  wp_enqueue_script(
    'dotbee-main',
    get_template_directory_uri() . '/assets/js/main.js',
    array(),
    filemtime(get_template_directory() . '/assets/js/main.js'),
    true
  );

  wp_localize_script('dotbee-main', 'dotbee_ajax', [
    'ajax_url' => admin_url('admin-ajax.php')
  ]);
}
add_action('wp_enqueue_scripts', 'dotbee_enqueue_scripts');

add_action('wp_ajax_waitlist_form', 'dotbee_waitlist_form');
add_action('wp_ajax_nopriv_waitlist_form', 'dotbee_waitlist_form');

function dotbee_waitlist_form() {
  // Honeypot check
  if (!empty($_POST['website'])) {
    wp_send_json_error('Spam detected.');
  }

  // Time-based spam check
  $form_start = intval($_POST['form_start'] ?? 0);
  if (!$form_start || (time() - $form_start) < 2) {
    wp_send_json_error('Spam detected (timer).');
  }

  $admin_email = get_option('admin_email');
  $email1 = 'mikhail.rubashkin@gmail.com';
  $email2 = 'hello@dotbee.se';

  // var_dump($admin_email);

  $fields = ['name', 'company', 'email', 'phone', 'message'];
  $data = [];
  foreach ($fields as $field) {
    $data[$field] = sanitize_text_field($_POST[$field] ?? '');
  }

  // Validation example
  if (empty($data['email']) || !is_email($data['email'])) {
    wp_send_json_error('Please enter a valid email address.');
  }
  // if (empty($data['company']) || empty($data['job_title'])) {
  //   wp_send_json_error('Fill in all required fields.');
  // }

  $body = "New waitlist form submission:\n\n";
  foreach ($data as $k => $v) {
    $body .= ucfirst(str_replace('_', ' ', $k)) . ": " . $v . "\n";
  }

  error_log("Sending email to: " . $admin_email);
  error_log("Email body:\n" . $body);
  $sent = wp_mail($admin_email, 'Waitlist Form Submission', $body);
  $sent_to_mickhail = wp_mail($email1, 'Waitlist Form Submission', $body);
  $sent_to_hello = wp_mail($email2, 'Waitlist Form Submission', $body);

  if ($sent) {
    wp_send_json_success('Thank you! Your application has been received.');
  } else {
    wp_send_json_error('Mail server error. Try again later.');
  }
}