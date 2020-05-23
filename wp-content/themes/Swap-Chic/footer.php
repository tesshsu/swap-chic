<?php
/**
 * The template for displaying the footer
**/

?>
	</main>
	<?php if(displayFooter(getPath())) { ;?>
		<footer class="mobile">
			<?php 
				$scope = getScopeString($_GET);
			?>
			<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/actualites/'; if(!empty($_GET)){ echo $scope; } ?>" class="active"><img src="<?php echo get_template_directory_uri().'/assets/images/fil.svg' ?>" alt="">Mon fil</a>
			<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/catalogue/'; if(!empty($_GET)){ echo $scope; }'#produits' ?>" "><img src="<?php echo get_template_directory_uri().'/assets/images/catalogue.svg' ?>" alt="">Mon catalogue</a>
		</footer>
	<?php } ?>

<?php wp_footer(); ?>

</body>
</html>
