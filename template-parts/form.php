<?php
$form_title = get_field('form_title');
$form_button = get_field('form_button');
$form_policy = get_field('form_policy');
?>

<section id="contact" class="waitlist color-section" data-bg="#EEF0FF">
    <div class="waitlist__hr"></div>
    <div class="waitlist__inner">
        <div class="waitlist__header">
            <h1 data-bg="#EEF0FF"><?php echo esc_html($form_title ?: 'Join the Waiting List'); ?></h1>
        </div>

        <div class="waitlist__form">
            <form id="waitlist-form" method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="waitlist_form">
                <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
                <input type="hidden" name="form_start" id="form_start">

                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <?php
                    $ph = get_field("form_field_{$i}_placeholder");
                    $name = get_field("form_field_{$i}_name");
                    if (!$ph || !$name) continue;
                    ?>
                    <?php if ($name === 'message'): ?>
                    <div class="form-group">
                        <textarea
                        name="<?= esc_attr($name); ?>"
                        placeholder="<?= esc_attr($ph); ?>"
                        required
                        ></textarea>
                    </div>
                    <?php else: ?>
                    <div class="form-group">
                        <input
                        type="text"
                        name="<?= esc_attr($name); ?>"
                        placeholder="<?= esc_attr($ph); ?>"
                        required
                        >
                    </div>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($form_policy): ?>
                    <div class="form-policy">
                    <label>
                        <input type="checkbox" name="policy" required>
                        <?= $form_policy; ?>
                    </label>
                    </div>
                <?php endif; ?>

                <button type="submit"><?= esc_html($form_button ?: 'Submit'); ?></button>
            </form>

            <div id="waitlist-form-message"></div>
        </div>
    </div>
</section>