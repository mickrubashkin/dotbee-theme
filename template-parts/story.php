<?php
$heading = get_field('story_heading');
$image = get_field('story_image');
$name = get_field('story_name');
$role = get_field('story_role');
$text = get_field('story_text');
$footer = get_field('story_footer');
?>

<section id="story" class="story color-section" data-bg="#FFF2F8">
  <div class="story__hr"></div>
  <div class="story__inner">
    <div class="story__header">
      <h1><?php echo esc_html($heading); ?></h1>
    </div>
    <div class="story__content">
      <div class="story__image">
        <?php if ($image): ?>
          <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
          <p class="story__name"><?php echo esc_html($name); ?></p>
          <p class="story__role"><?php echo esc_html($role); ?></p>
        <?php endif; ?>
        <!-- <div class="text__hr"></div> -->
      </div>
      <!-- <div class="text__hr"></div> -->
      <div class="story__text">
        <?php echo wp_kses_post($text); ?>
        <p class="story__footer"><?php echo esc_html($footer); ?></p>
      </div>
    </div>
  </div>
</section>