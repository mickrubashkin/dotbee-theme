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
      <div class="story__text-wrapper">
        <div class="story__text">
          <?php echo wp_kses_post($text); ?>
          <p class="story__footer"><?php echo esc_html($footer); ?></p>
        </div>
      </div>

      <!-- <button class="story__toggle">
        <span>Read more</span>
        <span>
          <svg width="13" height="12" viewBox="0 0 13 12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0.96 4.96334H11.408L10.56 5.58734L5.312 0.323341H7.392L12.752 5.71534L7.392 11.1073H5.296L10.56 5.84334L11.408 6.46734H0.96V4.96334Z" fill="#767676"/>
          </svg>
        </span>
      </button> -->
    </div>
  </div>
</section>