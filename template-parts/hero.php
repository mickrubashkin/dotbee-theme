<?php
$title = get_field('hero_title');
$desc = get_field('hero_description');
$btn_text = get_field('hero_button_text');
$btn_link = get_field('hero_button_link');
$img = get_field('hero_image');
?>

<section id="hero" class="hero color-section" data-bg="#FFF4EA">
  <div class="hero__hr"></div>
  <div class="hero__inner">
    <div class="hero__content">
      <?php if ($title): ?>
        <h1 data-bg="#FFF4EA"><?php echo esc_html($title); ?></h1>
      <?php endif; ?>

      <?php if ($desc): ?>
        <p><?php echo esc_html($desc); ?></p>
      <?php endif; ?>

      <?php if ($btn_text && $btn_link): ?>
        <a href="<?php echo esc_url($btn_link); ?>" class="hero__button">
          <?php echo esc_html($btn_text); ?>
        </a>
      <?php endif; ?>
    </div>

    <div class="hero__images">
      <?php if ($img): ?>
        <img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
      <?php endif; ?>
    </div>
  </div>
</section>