<?php
$form_title = get_field('form_title');
$form_button = get_field('form_button');
$form_policy = get_field('form_policy');

$lang = pll_current_language(); // 'en' | 'sv'

if ($lang === 'sv') {
    $placeholder_name = 'Namn*';
    $placeholder_company = 'Företag*';
    $placeholder_email = 'E-post*';
    $placeholder_phone = 'Telefon';
    $placeholder_message = 'Meddelande';
} else {
    $placeholder_name = '*Name';
    $placeholder_company = '*Company';
    $placeholder_email = '*Email';
    $placeholder_phone = '*Phone';
    $placeholder_message = 'Your Message';
}
?>

<section id="contact" class="waitlist color-section" data-bg="#EEF0FF">
    <!-- <div class="waitlist__hr"></div> -->
    <div class="waitlist__inner">
        <div class="waitlist__header">
            <h1 data-bg="#EEF0FF"><?php echo esc_html($form_title ?: 'Join the Waiting List'); ?></h1>
        </div>

        <div class="waitlist__form">
            <form id="waitlist-form" method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="waitlist_form">
                <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
                <input type="hidden" name="form_start" id="form_start">

                <div class="form-group">
                    <input type="text" name="name" autocomplete="name" placeholder="<?php echo($placeholder_name) ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="company" placeholder="<?php echo($placeholder_company) ?>">
                </div>
                <div class="form-group">
                    <input type="email" name="email" autocomplete="email" placeholder="<?php echo($placeholder_email) ?>" required>
                </div>
                <div class="form-group">
                    <input type="tel" name="phone" autocomplete="tel" placeholder="<?php echo($placeholder_phone) ?>" required>
                </div>
                <div class="form-group">
                    <textarea name="message" placeholder="<?php echo($placeholder_message) ?>"></textarea>
                </div>

                <?php if ($form_policy): ?>
                    <div class="form-policy">
                    <label>
                        <input type="checkbox" name="policy" required>
                        <?= $form_policy; ?>
                    </label>
                    </div>
                <?php endif; ?>

                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                <script>
                    grecaptcha.ready(function () {
                        grecaptcha.execute('6LcYNJIrAAAAAOFWmOCCOgfL6lDxAKMPFYlg0ohe', { action: 'submit' }).then(function (token) {
                        document.getElementById('g-recaptcha-response').value = token;
                        });
                    });
                </script>

                <button type="submit"><?= esc_html($form_button ?: 'Submit'); ?></button>
            </form>

            <div id="waitlist-form-message"></div>
        </div>
    </div>
</section>