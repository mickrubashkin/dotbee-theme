<?php
$heading = get_field('story_heading');
$image = get_field('story_image');
$name = get_field('story_name');
$role = get_field('story_role');
$text = get_field('story_text');
$footer = get_field('story_footer');
$current_lang = pll_current_language();

if ($current_lang === 'sv') {
  $toggleReadMore = 'Läs artikeln';
  $toggleReadLess = 'Minimera';
} else {
  $toggleReadMore = 'Read More';
  $toggleReadLess = 'Read Less';
}
?>

<section id="story" class="story color-section" data-bg="#FFF2F8">
  <div class="story__inner">
    <div class="story__header">
      <h1><?php echo esc_html($heading); ?></h1>
    </div>
    <div class="story__content">
      <div class="story__image">
        <?php if ($image): ?>
          <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
          <h3 class="story__name"><?php echo esc_html($name); ?></h3>
          <p class="story__role"><?php echo esc_html($role); ?></p>
        <?php endif; ?>
      </div>
      <div class="text__hr"></div>
      <div class="story__text-wrapper">
        <div class="story__text">
          <?php echo wp_kses_post($text); ?>
          <p class="story__footer"><?php echo esc_html($footer); ?></p>
        </div>
      </div>

      <div class="story__toggle-wrapper">
        <button class="story__toggle story__toggle--more">
          <span class="toggle-text"><?php echo($toggleReadMore) ?></span>
          <span class="toggle-icon arrow-right">
            <svg width="13" height="12" viewBox="0 0 13 12" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M0.96 5.488H11.408L10.56 6.112L5.312 0.848H7.392L12.752 6.24L7.392 11.632H5.296L10.56 6.368L11.408 6.992H0.96V5.488Z" fill="#333333"/>
            </svg>
          </span>
        </button>

        <button class="story__toggle story__toggle--less">
          <span class="toggle-icon arrow-left">
            <svg width="13" height="12" viewBox="0 0 13 12" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12.04 5.488H1.592L2.44 6.112L7.688 0.848H5.608L0.247999 6.24L5.608 11.632H7.704L2.44 6.368L1.592 6.992H12.04V5.488Z" fill="#333333"/>
            </svg>          
          </span>
          <span class="toggle-text"><?php echo($toggleReadLess) ?></span>
        </button>
      </div>
    </div>
  </div>
</section>