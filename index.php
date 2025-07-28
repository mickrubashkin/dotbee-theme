<?php get_header(); ?>

<main>
  <h2>Welcome to Dotbee</h2>
  <?php
  if (have_posts()) :
    while (have_posts()) : the_post();
      the_content();
    endwhile;
  else :
    echo '<p>No content found</p>';
  endif;
  ?>
</main>

<?php get_footer(); ?>