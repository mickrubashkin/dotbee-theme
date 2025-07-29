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


  // Sending form data to email
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

  $email_admin = get_option('admin_email');
  $email_hello = 'hello@dotbee.se';
  $email_noreply = 'no-reply@dotbee.se';

  // error_log("Sending email to: " . $email_admin);
  // error_log("Email body:\n" . $body);
  error_log("On line 100 ok.");

  $lang = pll_current_language(); // 'en' | 'sv'

  $headers = array('Content-Type: text/html; charset=UTF-8');
  if ($lang === 'sv') {
    $message_success = "Tack! Du är nu med på väntelistan. Vi hör av oss snart.";
    $autoreply_subject = "Du är med på listan, tack för att du skrev upp dig!";
        $autoreply_text = '
<p><strong>Hej där! 👋</strong></p>

<p>Stort tack för att du skrev upp dig, vi är glada att ha dig med! 🎉</p>

<p>Du är nu officiellt med på vår väntelista, och vi hör av oss så snart vi kan.</p>

<p>Tills dess, håll gärna utkik i inkorgen, och följ oss på <a href="https://www.linkedin.com/company/dotbee">LinkedIn</a> för de senaste uppdateringarna, insikterna och en inblick i vad vi bygger på Dotbee.</p>

<p>Tveka inte att <a href="mailto:hello@dotbee.se">kontakta oss om du har några frågor</a>!</p>

<p>📞 +46 10 641 45 30<br />
📧 hello@dotbee.se</p>

<p>Vi hörs!<br />
<strong>Team Dotbee</strong></p>
';
  } else {
    $message_success = "Thanks! You're now on the waiting list. We'll be in touch soon.";
    $autoreply_subject = "You're on the list, thanks for signing up!";
    $autoreply_text = '
<p><strong>Hey there! 👋</strong></p>

<p>Thank you so much for signing up, we’re happy to have you on board. 🎉</p>

<p>You\'re now officially on our waiting list, and we\'ll get in touch as soon as we can.</p>

<p>In the meantime, keep an eye on your inbox and feel free to <a href="https://www.linkedin.com/company/dotbee">follow us on LinkedIn</a> for the latest updates, insights, and a behind-the-scenes look at what we\'re building at Dotbee.</p>

<p>Don\'t hesitate to <a href="mailto:hello@dotbee.se">reach out if you have any questions</a>!</p>

<p>📞 +46 10 641 45 30<br />
📧 hello@dotbee.se</p>

<p>Talk soon!<br />
<strong>Team Dotbee</strong></p>
';
  }

  // $sent_to_hello = wp_mail($email_hello, 'Waitlist Form Submission', $body);
  $sent_to_noreply = wp_mail($email_noreply, 'Waitlist Form Submission', $body);
  error_log("Line 149 ok.");
  

  if ($sent_to_noreply) {
    error_log("Line 153 ok.");
    error_log("Autoreply trigger OK. Sending autoreply to: " . $data['email']);
    wp_mail($data['email'], $autoreply_subject, $autoreply_text, $headers);
    wp_send_json_success($message_success);
  } else {
    wp_send_json_error('Mail server error. Try again later.');
  }

}