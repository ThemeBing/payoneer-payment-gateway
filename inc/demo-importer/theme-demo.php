<?php
// Exit if accessed directly
if(! defined('ABSPATH')) {
	exit;
}

// Start Class
class ThemeBing_Install_Themes {

	/**
	 * Start things up
	 */
	public function __construct() {
		add_action('admin_menu', array($this, 'add_page'), 999);
		add_action('admin_enqueue_scripts',  array($this, 'enqueue_scripts'));
	}

	// Enqueue script
	function enqueue_scripts() {
		// CSS
		wp_enqueue_style('themes-demo', plugin_dir_url( __FILE__ ) . '/assets/css/themes.css');

		// JS
		wp_enqueue_script( 'themes-demo', plugin_dir_url( __FILE__ ) . '/assets/js/themes.js', array('jquery'), wp_get_theme()->get( 'Version' ), true );
	}

	/**
	 * Add sub menu page for the custom CSS input
	 *
	 * @since 1.0.0
	 */
	public function add_page() {
		add_menu_page(
			esc_html__('ThemeBing Themes', 'wc-payoneer-payment-gateway'),
			esc_html__('TB Themes', 'wc-payoneer-payment-gateway'),
			'manage_options',
			'themebing-themes',
			array($this, 'create_admin_theme_page')
		);
	}

	/**
	 * Settings page output
	 *
	 * @since 1.0.0
	 */
	public function create_admin_theme_page() {


		$data = wp_remote_retrieve_body( wp_remote_get( plugin_dir_url( __FILE__ ) . 'data.json' ));
		
		$themes = json_decode( $data , true );?>

		<div class="themes-showcase wrap">

			<h2 class="wp-heading-inline"><?php esc_attr_e('Themes from ThemeBing', 'wc-payoneer-payment-gateway'); ?></h2>
			<?php
			$show = false;
			if ($show): ?>
			<div class="theme-filter">
				<ul class="theme-filter-links">
					<li class="current"><a href="#"><?php echo esc_html__( 'All','wc-payoneer-payment-gateway' ) ?></a></li>
					<?php 
					if ($themes) {
						foreach ($themes['categories'] as $key => $category) { ?>
							<li><a href="#" data-cat="<?php echo esc_attr( strtolower($category) ) ?>"><?php echo esc_html( $category ) ?></a></li>
						<?php }
					} 
					?>
				</ul>
				<div class="themebing-search">
					<input type="text" class="themebing-search-input" name="themebing-search" value="" placeholder="<?php esc_html_e('Search themes...', 'wc-payoneer-payment-gateway'); ?>">
				</div>
			</div>
			<?php endif ?>
			
			<div class="theme-browser rendered">

				<div class="themes wp-clearfix">
					<?php 
					if ($themes) {
						foreach ($themes['products'] as $key => $theme) { ?>
						<div class="theme" tabindex="0" aria-describedby="twentytwentyone-action twentytwentyone-name" data-slug="twentytwentyone">
							<div class="theme-screenshot">
								<img src="<?php echo esc_url( $theme["image"] ) ?>" alt="<?php echo esc_attr( $theme["name"] ) ?>">
							</div>
							<span class="more-details"><?php echo esc_html( $theme["name"] ) ?></span>
							<div class="theme-id-container">
								<h3 class="theme-name"><?php echo esc_html( $theme["name"] ) ?></h3>
								<div class="theme-actions">
									<a href="<?php echo esc_url( $theme["buy"] ) ?>" target="_blank" class="button button-primary theme-install" data-name="Twenty Twenty-One" data-slug="twentytwentyone" aria-label="Install Twenty Twenty-One"><?php echo esc_html__( 'Install','wc-payoneer-payment-gateway' ) ?></a>
									<a href="<?php echo esc_url( $theme["preview_url"] ) ?>" target="_blank" class="button preview install-theme-preview"><?php echo esc_html__( 'Preview','wc-payoneer-payment-gateway' ) ?></a>
								</div>
							</div>
						</div>
					<?php }
					} ?>

				</div>

			</div>

		</div>

	<?php }
}
new ThemeBing_Install_Themes();