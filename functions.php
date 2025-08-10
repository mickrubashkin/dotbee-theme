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
    if ($field === 'email') {
      $data[$field] = sanitize_email($_POST[$field] ?? '');
    } else {
      $data[$field] = sanitize_text_field($_POST[$field] ?? '');
    }
  }

  // Basic validation
  if (empty($data['email']) || !is_email($data['email'])) {
    wp_send_json_error('Invalid email.');
  }

  $body = "New waitlist form submission:\n\n";
  foreach ($data as $k => $v) {
    $body .= ucfirst(str_replace('_', ' ', $k)) . ": " . $v . "\n";
  }

  $email_admin = get_option('admin_email');
  $email_hello = 'hello@dotbee.se';
  $email_mikky = 'mikael.efron@dotbee.se';

  error_log("Got admin email from wp: " . $email_admin);

  $lang = function_exists('pll_current_language') ? pll_current_language() : 'en'; // 'en' | 'sv'

  // Headers
  // Admin notifications: reply goes to the user who filled the form
  $headers_admin = array(
    'Content-Type: text/plain; charset=UTF-8',
    'Reply-To: ' . $data['email']
  );
  // Autoreply to user: reply goes to our support inbox
  $headers_user = array(
    'Content-Type: text/html; charset=UTF-8',
    'Reply-To: hello@dotbee.se'
  );
  if ($lang === 'sv') {
    $message_success = "Tack! Du är nu med på väntelistan. Vi hör av oss snart.";
    $autoreply_subject = "Du är med på listan, tack för att du skrev upp dig!";
        $autoreply_text = '
<p><strong>Hej där! 👋</strong></p>

<p>Stort tack för att du skrev upp dig, vi är glada att ha dig med! 🎉</p>

<p>Du är nu officiellt med på vår väntelista, och vi hör av oss så snart vi kan.</p>

<p>Tills dess, håll gärna utkik i inkorgen, och följ oss på <a href="https://www.linkedin.com/company/dotbeeab/">LinkedIn</a> för de senaste uppdateringarna, insikterna och en inblick i vad vi bygger på Dotbee.</p>

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

<p>In the meantime, keep an eye on your inbox and feel free to <a href="https://www.linkedin.com/company/dotbeeab/">follow us on LinkedIn</a> for the latest updates, insights, and a behind-the-scenes look at what we\'re building at Dotbee.</p>

<p>Don\'t hesitate to <a href="mailto:hello@dotbee.se">reach out if you have any questions</a>!</p>

<p>📞 +46 10 641 45 30<br />
📧 hello@dotbee.se</p>

<p>Talk soon!<br />
<strong>Team Dotbee</strong></p>
';
  }

  $sent = wp_mail($email_admin, 'Waitlist Form Submission', $body, $headers_admin);

  // wp_mail($email_hello, 'Waitlist Form Submission', $body, $headers_admin);
  wp_mail($email_mikky, 'Waitlist Form submission', $body, $headers_admin);

  // Autoreply to user
  wp_mail($data['email'], $autoreply_subject, $autoreply_text, $headers_user);
  error_log("Form data sent to: " . $email_admin);

  // Save to DB before responding
  dotbee_waitlist_store([
    'name'    => $data['name'] ?? '',
    'company' => $data['company'] ?? '',
    'email'   => $data['email'] ?? '',
    'phone'   => $data['phone'] ?? '',
    'message' => $data['message'] ?? '',
    'lang'    => $lang,
  ]);

  if ($sent) {
    wp_send_json_success($message_success);
  } else {
    wp_send_json_error('Mail server error. Try again later.');
  }
}

// === DOTBEE WAITLIST: START ===

// Ensure waitlist table exists (can be called from anywhere)
function dotbee_waitlist_ensure_table() {
  global $wpdb;
  $table = $wpdb->prefix . 'dotbee_waitlist';
  // If table is missing, create it
  if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      created_at DATETIME NOT NULL,
      name VARCHAR(190) DEFAULT '' NOT NULL,
      company VARCHAR(190) DEFAULT '' NOT NULL,
      email VARCHAR(190) DEFAULT '' NOT NULL,
      phone VARCHAR(190) DEFAULT '' NOT NULL,
      message TEXT,
      lang VARCHAR(10) DEFAULT '' NOT NULL,
      ip VARCHAR(45) DEFAULT '' NOT NULL,
      ua TEXT,
      PRIMARY KEY (id),
      KEY created_at (created_at),
      KEY email (email)
    ) $charset;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }
}

// 1) Создаём таблицу
add_action('after_switch_theme', function () {
  dotbee_waitlist_ensure_table();
  flush_rewrite_rules();
});

// 2) Хелпер: сохранить заявку (вызываем из твоего AJAX-хендлера)
function dotbee_waitlist_store($args = []) {
  global $wpdb;
  $table = $wpdb->prefix . 'dotbee_waitlist';
  $row = [
    'created_at' => current_time('mysql'),
    'name'       => sanitize_text_field($args['name'] ?? ''),
    'company'    => sanitize_text_field($args['company'] ?? ''),
    'email'      => sanitize_email($args['email'] ?? ''),
    'phone'      => sanitize_text_field($args['phone'] ?? ''),
    'message'    => wp_kses_post($args['message'] ?? ''),
    'lang'       => sanitize_text_field($args['lang'] ?? ''),
    'ip'         => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
    'ua'         => sanitize_textarea_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
  ];
  return (bool) $wpdb->insert(
    $table, $row, ['%s','%s','%s','%s','%s','%s','%s','%s','%s']
  );
}

// 4) Виртуальный роут /wait-list (без создания страницы)
add_action('init', function () {
  add_rewrite_rule('^wait-list/?$', 'index.php?dotbee_waitlist=1', 'top');
});

// Register custom query var so WP keeps it in the main query
add_filter('query_vars', function ($vars) {
  $vars[] = 'dotbee_waitlist';
  return $vars;
});

// 5) Рендерим страницу для админов
add_action('template_redirect', function () {
  if (!get_query_var('dotbee_waitlist')) return;

  if (!is_user_logged_in()) {
    auth_redirect(); // редирект на логин и обратно
    exit;
  }
  if (!current_user_can('manage_options')) {
    wp_die('You do not have permissions to view this page.', 'Forbidden', ['response' => 403]);
  }

  dotbee_waitlist_ensure_table();
  global $wpdb;
  $table = $wpdb->prefix . 'dotbee_waitlist';

  // экспорт CSV
  if (isset($_GET['export']) && wp_verify_nonce($_GET['_wpnonce'] ?? '', 'dotbee_export')) {
    $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC", ARRAY_A);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=waitlist.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','created_at','name','company','email','phone','message','lang','ip','ua']);
    foreach ($rows as $r) fputcsv($out, $r);
    fclose($out);
    exit;
  }

  $per_page = 20;
  $page     = max(1, intval($_GET['paged'] ?? 1));
  $offset   = ($page - 1) * $per_page;

  $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
  $rows  = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM $table ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset),
    ARRAY_A
  );
  $export_url = wp_nonce_url(add_query_arg(['export' => 1]), 'dotbee_export');

  get_header();
  ?>
  <style>
    .waitlist-wrap{max-width:1100px;margin:40px auto;padding:0 20px}
    .waitlist-actions{margin:16px 0;display:flex;gap:12px}
    .btn{display:inline-block;padding:8px 12px;border:1px solid #111827;border-radius:8px;text-decoration:none;color:#111827}
    .btn-primary{background:#111827;color:#fff}
    .waitlist-table{width:100%;border-collapse:collapse}
    .waitlist-table th,.waitlist-table td{padding:10px 12px;border-bottom:1px solid #e5e7eb;vertical-align:top}
    .waitlist-table th{background:#f8fafc;text-align:left;font-weight:600}
    .waitlist-empty{padding:24px;background:#fffbea;border:1px solid #fde68a;border-radius:8px}
    .waitlist-pager{margin-top:16px;display:flex;gap:8px}
    .waitlist-pager a,.waitlist-pager span{padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;text-decoration:none}
    .waitlist-pager .current{background:#111827;color:#fff;border-color:#111827}
  </style>
  <div class="waitlist-wrap">
    <h1>Waitlist</h1>
    <div class="waitlist-actions">
      <a class="btn btn-primary" href="<?php echo esc_url($export_url); ?>">Export CSV</a>
    </div>

    <?php if ($rows): ?>
      <table class="waitlist-table">
        <thead>
        <tr>
          <th>ID</th><th>Date</th><th>Name</th><th>Company</th>
          <th>Email</th><th>Phone</th><th>Message</th><th>Lang</th><th>IP</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?php echo esc_html($r['id']); ?></td>
            <td><?php echo esc_html($r['created_at']); ?></td>
            <td><?php echo esc_html($r['name']); ?></td>
            <td><?php echo esc_html($r['company']); ?></td>
            <td><a href="mailto:<?php echo esc_attr($r['email']); ?>"><?php echo esc_html($r['email']); ?></a></td>
            <td><?php echo esc_html($r['phone']); ?></td>
            <td><?php echo esc_html(mb_strimwidth(wp_strip_all_tags($r['message']), 0, 140, '…')); ?></td>
            <td><?php echo esc_html($r['lang']); ?></td>
            <td><?php echo esc_html($r['ip']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php
      $pages = (int) ceil($total / $per_page);
      if ($pages > 1) {
        echo '<div class="waitlist-pager">';
        for ($i=1; $i <= $pages; $i++) {
          if ($i === $page) echo '<span class="current">'.$i.'</span>';
          else echo '<a href="'.esc_url(add_query_arg('paged', $i)).'">'.$i.'</a>';
        }
        echo '</div>';
      }
      ?>
    <?php else: ?>
      <div class="waitlist-empty">No submissions yet.</div>
    <?php endif; ?>
  </div>
  <?php
  get_footer();
  exit;
});
// === DOTBEE WAITLIST: END ===

add_action('init', function () {
  if (get_option('dotbee_waitlist_seed_done')) {
    return;
  }
  dotbee_waitlist_ensure_table();
  global $wpdb;
  $table = $wpdb->prefix . 'dotbee_waitlist';
  $rows = [
    ['2025-07-29 21:42','Robert Sjöberg','Helantus AB','robert.sjoeberg@gmail.com','707677233',''],
    ['2025-07-29 23:07','Rikard','Bergius Indusium','rikardberggren91@gmail.com','725328444',''],
    ['2025-07-30 10:36','Johan JC Carlberg','Buyn','johan.carlberg@buyn.se','760160768','Intresserade av er tjänst för kommunikation med butikskunder. T.ex: "Ditt paket med ordernr #### är färdigt att hämtas, vi syns på " Om det är möjligt att lösa/automatisera med öppet API hos vår webshopsleverantör så är ni intresssanta att ha dialog med.'],
    ['2025-07-30 11:05','Kristofer Österberg','AIK amerikansk fotboll','info@aikamfotboll.se','706644667',''],
    ['2025-07-30 14:54','Fredrik Svedberg','Logtrade Technology','fredrik.svedberg@logtrade.se','+46703162680',''],
    ['2025-07-30 15:49','Jack Johnson','Cisco','jackj21@cisco.com','+447842793206','Fantastic news and good luck guys, what a journey it’s going to be!'],
    ['2025-07-30 17:29','Mikael Kvant','Hyrverket','mikael@hyrverket.se','+46708130000','Nyfiken...'],
    ['2025-07-30 22:34','Johan Hägglund','','johan.hagglund24@gmail.com','+46703361202',''],
    ['2025-08-01 22:02','Mårten Pettersson','Bizt Application Management AB','m@bizt.se','707584172',''],
  ];
  foreach ($rows as $r) {
    $wpdb->insert($table, [
      'created_at' => $r[0],
      'name'       => $r[1],
      'company'    => $r[2],
      'email'      => $r[3],
      'phone'      => $r[4],
      'message'    => $r[5],
      'lang'       => '',
      'ip'         => '',
      'ua'         => ''
    ], ['%s','%s','%s','%s','%s','%s','%s','%s','%s']);
  }
  update_option('dotbee_waitlist_seed_done', 1, true);
});