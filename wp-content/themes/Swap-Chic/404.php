<?php
/**
 * The template for displaying 404 pages (not found)
**/

get_header(); ?>

<div class="notfound">
	<p class="h1">Page introuvable...</p>
	<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'] ?>">Revenir à votre fil</a>
</div>

<?php
get_footer();
