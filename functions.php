<?php
// reCAPTCHA
$recaptcha_config_path = get_template_directory() . '/recaptcha-config.php';
if (file_exists($recaptcha_config_path)) {
  require_once $recaptcha_config_path;
}

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

  // reCAPTCHA v3 validation
  $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
  $recaptcha_secret = defined('RECAPTCHA_SECRET_KEY') ? RECAPTCHA_SECRET_KEY : '';

  $verify_response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
    'body' => [
      'secret' => $recaptcha_secret,
      'response' => $recaptcha_response,
      'remoteip' => $_SERVER['REMOTE_ADDR']
    ]
  ]);

  if (is_wp_error($verify_response)) {
    wp_send_json_error('Captcha error.');
  }

  $recaptcha_result = json_decode(wp_remote_retrieve_body($verify_response));

  if (
    empty($recaptcha_result->success) ||
    $recaptcha_result->score < 0.5 ||
    $recaptcha_result->action !== 'submit'
  ) {
    wp_send_json_error('Captcha verification failed.');
  }

  $email_admin = get_option('admin_email');
  $email_mick = 'mikhail.rubashkin@gmail.com';
  $email_hello = 'hello@dotbee.se';
  $email_noreply = 'no-reply@dotbee.se';

  $fields = ['name', 'company', 'email', 'phone', 'message'];
  $data = [];
  foreach ($fields as $field) {
    $data[$field] = sanitize_text_field($_POST[$field] ?? '');
  }

  // [ ] Think about future validation.

  $body = "New waitlist form submission:\n\n";
  foreach ($data as $k => $v) {
    $body .= ucfirst(str_replace('_', ' ', $k)) . ": " . $v . "\n";
  }

  error_log("Sending email to: " . $email_admin);
  error_log("Email body:\n" . $body);
  $sent = wp_mail($email_admin, 'Waitlist Form Submission', $body);
  $sent_to_mick = wp_mail($email_mick, 'Waitlist Form Submission', $body);
  $sent_to_hello = wp_mail($email_hello, 'Waitlist Form Submission', $body);
  $sent_to_noreply = wp_mail($email_noreply, 'Waitlist Form Submission', $body);

  if ($sent_to_hello) {
    wp_send_json_success('Thank you! Your application has been received.');
  } else {
    wp_send_json_error('Mail server error. Try again later.');
  }

}