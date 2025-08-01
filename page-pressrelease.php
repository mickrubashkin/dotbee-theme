<?php
/*
Template Name: Press Release Page
*/
get_header();

$title = get_field('press_release_title');
$picture = get_field('press_release_picture');
$text = get_field('press_release_text');
?>

<main>
    <section class="press-release">
        <div class="press-release__inner">
            <?php if ($picture): ?>
                <div class="press-release__image">
                    <img src="<?= esc_url($picture['url']); ?>" alt="<?= esc_attr($picture['alt']); ?>">
                </div>
            <?php endif; ?>

            <div class="press-release__content">
                <?php if ($title): ?>
                    <h1 class="press-release__title"><?= esc_html($title); ?></h1>
                <?php endif; ?>

                <?php if ($text): ?>
                    <div class="press-release__text">
                        <?= wp_kses_post($text); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>