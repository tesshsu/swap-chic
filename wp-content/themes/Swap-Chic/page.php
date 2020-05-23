<?php
/**
 * The template for displaying all pages
**/

get_header(); 
global $post;
$content = apply_filters('the_content', $post->post_content); 
?>?

<div class="page">
<?php 
    print '<p class="h1">'.apply_filters('the_title', $post->post_title).'</p>';
    print $content;
?>
</div>


<?php
get_footer();
?>