<?php 
/**
 * Main template file for WP Resume
 *
 * HTML5 and hResume compliant
 *
 * @package wp_resume
 * @author Benjamin J. Balter
 * @since 1.0a
 */

//Retrieve plugin options for later use
$options = wp_resume_get_options();
?>
		<div class="resume hresume">
			<header class="vcard">
				<h2 class="fn n url" id="name"><a href="<?php bloginfo('url'); ?>"><?php echo $options['name']; ?></a></h2>
				<ul>
					<?php //loop through contact info fields
						foreach ($options['contact_info'] as $field=>$value) { ?>
						<?php 
							//per hCard specs (http://microformats.org/profile/hcard) adr needs to be an array
							if ( is_array( $value ) ) { ?>
							<div id="<?php echo $field; ?>">
								<?php foreach ($value as $subfield => $subvalue) { ?>
									<li class="<?php echo $subfield; ?>"><?php echo $subvalue; ?></li>
								<?php } ?>
							</div>
						<?php } elseif ($field == 'email') { ?>
							<li><a href="mailto:<?php echo $value; ?>" class="<?php echo $field; ?>"><?php echo $value; ?></a></li>
						<?php } else { ?>
							<li class="<?php echo $field; ?>"><?php echo $value; ?></li>
						<?php } ?>
					<?php } ?>
				</ul>
			</header>
			<?php if (! empty( $options['summary'] ) ) { ?>
			<summary class="summary">
				<?php echo $options['summary']; ?>
			</summary>
			<?php } ?>
<?php 		
			//Loop through each resume section
			foreach ( wp_resume_get_sections() as $section) { 

?>
			<section class="vcalendar" id="<?php echo $section->slug; ?>">
				<header><?php echo $section->name; ?></header>
<?php			
				//Initialize our org. variable 
				$current_org=''; 
				
				//retrieve all posts in the current section using our custom loop query
				$posts = wp_resume_query( $section->slug );
				
				//loop through all posts in the current section using the standard WP loop
				if ( $posts->have_posts() ) : while ( $posts->have_posts() ) : $posts->the_post();
				
					//Retrieve details on the current position's organization
					$organization = wp_resume_get_org( get_the_ID() ); 
				
					//If this is the first organization, or if this org. is different from the previous, format output acordingly
					if ($organization && $organization->term_id != $current_org) {
					
						//If this is a new org., but not the first, end the previous org's article tag
						if ($current_org != '') { 
?>
				</article>
<?php 				
						} 
						
						//store this org's ID to our internal variable for the next loop
						$current_org = $organization->term_id; 
						
						//Format organization header output
						?>
				<article class="organization <?php echo $section->slug; ?> vevent" id="<?php echo $organization->slug; ?>">
					<header>
						<div class="orgName summary" id="<?php echo $organization->slug; ?>-name"><?php echo $organization->name; ?></div>
						<div class="location"><?php echo $organization->description; ?></div>
					</header>
<?php 				
					//End if new org
					}  
?>
					<section class="vcard">
						<a href="#name" class="include" title="<?php echo $options['title']; ?>"></a>
						<a href="#<?php echo $organization->slug; ?>-name" class="include" title="<?php echo $organization->name; ?>"></a>
						<div class="title"><?php echo the_title(); ?></div>
						<div class="date"><?php echo wp_resume_format_date( get_the_ID() ); ?></div>
						<details>
						<?php the_content(); ?>
<?php 			//If the current user can edit posts, output the link
				if ( current_user_can( 'edit_posts' ) ) 
					edit_post_link('Edit'); 	
?>
						</details><!-- .details -->
					</section> <!-- .vcard -->
<?php 		
				//End loop
				endwhile; endif;	
?>
<?php 		if ( $organization ) { ?>
				</article><!-- .organization -->
<?php 		} ?>
			</section><!-- .section -->
<?php } ?> 
		</div><!-- #resume -->
<?php
	//Reset query so the page displays comments, etc. properly
	wp_reset_query();
?>