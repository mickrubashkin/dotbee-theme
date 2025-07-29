<?php
/*
Template Name: Terms Page
*/
get_header();

$title = get_field('terms_title');
$picture = get_field('terms_picture');
$text = get_field('terms_text');

?>

<main>
    <section class="terms">
        <div class="hero__hr"></div>
        <div class="terms__inner">
            <?php if ($picture): ?>
                <div class="terms__image">
                    <img src="<?= esc_url($picture['url']); ?>" alt="<?= esc_attr($picture['alt']); ?>">
                </div>
            <?php endif; ?>

            <div class="terms__content">
                <?php if ($title): ?>
                <h1 class="terms__title"><?= esc_html($title); ?></h1>
                <?php endif; ?>

                
                <ul class="terms-links">
                    <li><a class="terms-link" href="/wp-content/uploads/2025/07/Allmanna-villkor-Dotbee-2025-07-01.pdf" target="_blank">Allmänna villkor</a></li>
                    <li><a class="terms-link" href="/wp-content/uploads/2025/07/General-Terms-and-Conditions-Dotbee-2025-07-01.pdf" target="_blank">General Terms And Conditions</a></li>
                    <li><a class="terms-link" href="/wp-content/uploads/2025/07/Personuppgiftsbitradesavtal-Dotbee.pdf" target="_blank">Personuppgiftbiträdesavtal</a></li>
                    <li><a class="terms-link" href="/wp-content/uploads/2025/07/Data-Processing-Agreement-Dotbee.pdf" target="_blank">Data processing agreement</a></li>
                </ul>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>