<?php
/*
Template Name: Privacy Policy Page
*/
get_header();

$title = get_field('privacy_policy_title');
$picture = get_field('privacy_policy_picture');
$text = get_field('privacy_policy_text');
?>

<main>
    <section class="privacy-policy">
        <div class="hero__hr"></div>
        <div class="privacy-policy__inner">
            <?php if ($picture): ?>
                <div class="privacy-policy__image">
                    <img src="<?= esc_url($picture['url']); ?>" alt="<?= esc_attr($picture['alt']); ?>">
                </div>
            <?php endif; ?>

            <div class="privacy-policy__content">
                <?php if ($title): ?>
                <h1 class="privacy-policy__title"><?= esc_html($title); ?></h1>
                <?php endif; ?>

                <?php if ($text): ?>
                <div class="privacy-policy__text">
                    <?= wp_kses_post($text); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>