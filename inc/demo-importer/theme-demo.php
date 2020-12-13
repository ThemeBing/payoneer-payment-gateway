<?php
// Exit if accessed directly
if(! defined('ABSPATH')) {
	exit;
}

function add_page() {
	add_menu_page(
		esc_html__('ThemeBing Themes', 'tb-themes'),
		esc_html__('TB Themes', 'tb-themes'),
		'manage_options',
		'themebing-themes', 'create_admin_theme_page'
	);
}
add_action('admin_menu', 'add_page', 999);
	
// Enqueue script
function enqueue_scripts() {
	// CSS
	wp_enqueue_style('themes-demo', plugin_dir_url( __FILE__ ) . '/assets/css/themes.css');

	// JS
	wp_enqueue_script( 'themes-demo', plugin_dir_url( __FILE__ ) . '/assets/js/themes.js', array('jquery'), wp_get_theme()->get( 'Version' ), true );
}

add_action('admin_enqueue_scripts', 'enqueue_scripts');

function get_themes_data() {

	$dir = plugins_url( '/', __FILE__ );

	$data = array(
		'Azim' => array(
			'categories'        => array('FREE', 'Clothing Fashion', 'Electronics', 'Furniture'),
			'demo_class'        => 'free-demo',
			'xml_file'     		=> $dir . 'azim/content.xml',
			'theme_settings' 	=> $dir . 'azim/customizer.json',
			'widgets_file'  	=> $dir . 'azim/widgets.wie',
			'form_file'  		=> $dir . 'azim/redux.json',
			'preview_image'		=> $dir . 'azim/demo.jpg',
			'preview_url'		=> 'https://themebing.com/wp/azim/',
			'required_plugins'  => array(
				'free' => array(
					array(
						'slug'  	=> 'elementor',
						'init'  	=> 'elementor/elementor.php',
						'name'  	=> 'Elementor',
					),
					array(
						'slug'  	=> 'woocommerce',
						'init'  	=> 'woocommerce/woocommerce.php',
						'name'  	=> 'WooCommerce',
					),
					array(
						'slug'  	=> 'wpforms-lite',
						'init'  	=> 'wpforms-lite/wpforms.php',
						'name'  	=> 'WPForms',
					),
				)
			),
		), 
	);
}

function create_admin_theme_page() { ?>

	<div class="themes-showcase wrap">

		<h2 class="wp-heading-inline"><?php esc_attr_e('Themes from ThemeBing', 'woovina-sites'); ?></h2>
		<ul class="theme-filter-links">
			<?php print_r(get_themes_data()) ?>
			<li><a href="#">All</a></li>
			<li><a href="#">All</a></li>
			<li><a href="#">All</a></li>
			<li><a href="#">All</a></li>
			<li><a href="#">All</a></li>
			<li><a href="#">All</a></li>
			<li><a href="#">All</a></li>
			<li><a href="#">All</a></li>
		</ul>
		<div clas="themebing-search">
			<input type="text" class="themebing-search-input" name="themebing-search" value="" placeholder="<?php esc_html_e('Search themes...', 'tb-themes'); ?>">
		</div>
		<div class="theme-browser rendered">

			<div class="themes wp-clearfix">

				<div class="theme" tabindex="0" aria-describedby="twentytwentyone-action twentytwentyone-name" data-slug="twentytwentyone">
					<div class="theme-screenshot">
						<img src="//ts.w.org/wp-content/themes/twentytwentyone/screenshot.png?ver=1.0" alt="">
					</div>
					<span class="more-details">Details &amp; Preview</span>
					<div class="theme-author">By WordPress.org</div>
					<div class="theme-id-container">
						<h3 class="theme-name">Twenty Twenty-One</h3>
						<div class="theme-actions">
							<a class="button button-primary theme-install" data-name="Twenty Twenty-One" data-slug="twentytwentyone" href="http://localhost/test/wp-admin/update.php?action=install-theme&amp;theme=twentytwentyone&amp;_wpnonce=5e15b5ca11" aria-label="Install Twenty Twenty-One">Install</a>
							<button class="button preview install-theme-preview">Preview</button>
						</div>
					</div>
				</div>

			</div>

		</div>

	</div>

<?php }