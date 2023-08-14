<?php

/**
 * Plugin Name:       Custom Filters
 * Plugin URI:        https://github.com/nrauf90/custom_filters
 * Description:       A plugin to create a custom filters for post. Plugin will generate shortcode so user can use that filter on any page to show post and filter all posts based on category, tags and date range
 * Version:           1.0.0
 * Author:            Muhammad Noman Rauf
 * Author URI:        https://github.com/nrauf90
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */
class CustomFilters{
	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	protected $pluginPath;
	protected $pluginUrl;

	/**
	 * Returns an instance of this class.
	 * @return CustomFilters
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new CustomFilters();
			self::$instance->init();
		}

		return self::$instance;

	}

	private function __construct() {
		// Set Plugin Path
		$this->pluginPath = dirname(__FILE__);

		// Set Plugin URL
		$this->pluginUrl = WP_PLUGIN_URL . '/custom-filters';
	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 * @return void
	 */
	public function init() {
		add_action( "wp_enqueue_scripts", array(
			$this,
			"custom_filters_enqueue_scripts"
		) ); // enqueue custom plugin script and style in theme

		add_shortcode( 'custom_filters', array($this, 'custom_filters_shortcode') );
    }

	// Enqueue necessary scripts and styles
	function custom_filters_enqueue_scripts() {
		wp_enqueue_style( 'custom-style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
	}

	// Create shortcode for displaying the custom archive page
	function custom_filters_shortcode() {
		ob_start();

		$category_filter = isset( $_GET['category_filter'] ) ? sanitize_text_field( $_GET['category_filter'] ) : '';
		$tag_filter      = isset( $_GET['tag_filter'] ) ? sanitize_text_field( $_GET['tag_filter'] ) : '';
		$start_date      = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
		$end_date        = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';
		$paged = get_query_var('paged') ? get_query_var('paged') : 1;

		?>
        <!--Filtering forms and logic code here-->
        <div class="custom-filters-container">
            <div class="custom-filters-form">
                <form action="<?php echo esc_url( get_permalink() ); ?>" method="get">
                    <select name="category_filter">
                        <option value="">All Categories</option>
						<?php
						$categories = get_categories();
						foreach ( $categories as $category ) {
							$selected = '';
							if($category->slug === $category_filter){
								$selected = 'selected';
							}
							echo '<option value="' . esc_attr( $category->slug ) . '" '.$selected.'>' . esc_html( $category->name ) . '</option>';
						}
						?>
                    </select>
                    <select name="tag_filter">
                        <option value="">All Tags</option>
						<?php
						$tags = get_tags();
						foreach ( $tags as $tag ) {
							$selected = '';
							if($tag->slug === $tag_filter){
								$selected = 'selected';
							}
							echo '<option value="' . esc_attr( $tag->slug ) . '" '.$selected.'>' . esc_html( $tag->name ) . '</option>';
						}
						?>
                    </select>
					<?php
					$from_date = '';
					$to_date = '';
					if($start_date){
						$from_date = date('Y-m-d', strtotime($start_date));
					}
					if($end_date){
						$to_date = date('Y-m-d', strtotime($end_date));
					}
					?>
                    <input type="date" name="start_date" value="<?php echo $from_date; ?>">
                    <input type="date" name="end_date"  value="<?php echo $to_date; ?>">
                    <input type="submit" value="Filter">
                </form>
            </div>
            <div class="customer-filters-results">
                <ul class="custom-filters-posts">
					<?php
					$args = array(
						'post_type' => 'post', // Adjust post type if needed
						'paged' => $paged,
					);

					if (!empty($category_filter)) {
						$args['category_name'] = $category_filter;
					}

					if (!empty($tag_filter)) {
						$args['tag'] = $tag_filter;
					}

					if (!empty($start_date) && !empty($end_date)) {
						$args['date_query'] = array(
							'after' => $start_date,
							'before' => $end_date,
							'inclusive' => true,
						);
					}
					$query = new WP_Query( $args );
					if ( $query->have_posts() ) :
						while ( $query->have_posts() ) : $query->the_post();
							?>
                            <li class="single-post">
                                <img src="<?php the_post_thumbnail_url();?>" alt="" class="post-thumbnail">
                                <h2 class="post-title">
                                    <a href="<?php the_permalink();?>" class="post-permalink"> <?php the_title()?></a>
                                </h2>
                                <div class="post-content">
									<?php the_excerpt();?>
                                </div>
                            </li>
						<?php
						endwhile;

					else :
						echo 'No posts found.';
					endif;
					?>
                </ul>
                <?php
                // Pagination
                echo paginate_links(array(
	                'total' => $query->max_num_pages,
	                'current' => $paged,
                ));
                wp_reset_postdata();
                ?>
            </div>
        </div>

		<?php


		return ob_get_clean();
	}

}

add_action( "plugins_loaded", array( "CustomFilters", "get_instance" ) );

