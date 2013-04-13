<?php

require_once 'JQueryLifestreamBase.php';

final class mgJQueryLifestream extends mgJQueryLifestreamBase  {

	function __construct() {
		parent::__construct(array());
		
		$this->menu_slug = 'jls';
		$this->settings_group = $this->menu_slug;
		
		add_action('admin_init', array($this, 'setup_settings'));
		add_action('admin_menu', array($this, 'setup_menu'));
		
		add_shortcode('jls', array($this, 'run_shortcode'));
	}
	
	function setup_menu() {
		add_options_page(
			'jQuery Lifestream',
			'jQuery Lifestream',
			'manage_options',
			$this->menu_slug,
			array($this, 'render_menu_page')
		);
	}
	
	function setup_settings() {
		add_settings_section(
			'jls_services', 
			'Services', 
			array($this, 'services_desc'),
			$this->menu_slug
		);
		
		add_settings_field(
			"jls_services_twitter_user",
			'Twitter',
			array($this, 'render_twitter'),
			$this->menu_slug,
			'jls_services'
		);

		register_setting(
			$this->settings_group, 
			'jls'//,
			//array($this, $validation)
		);
	}
	
	function services_desc() {
		echo "Fill in the username for the services you want to include in the lifestream";
	}
	
	function render_twitter() {
		$cfg = get_option('jls');
		$service_cfg = $cfg['services']['twitter'];
		$user = $service_cfg['user'];
		?>
			<input 
				name="jls[services][twitter][user]" 
				type="text" 
				value="<?php echo $user ?>"
			>
		<?php
	}
	
	function render_menu_page() {
		if (!current_user_can('manage_options'))  {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		?>
			<div class="wrap">
				<?php screen_icon(); ?>
				<h2>jQuery Lifestream</h2>
				<form action="options.php" method="post">
					<?php
						settings_fields($this->settings_group);
						do_settings_sections($this->menu_slug);
					?>
					<?php submit_button( __( 'Save Changes' ), 'primary', 'Update' ); ?>
			</form>
				
			</div>
		<?php
	}
	
	function run_shortcode() {
		$jsl_handle = $this->plugin_prefix . 'jls_js';
		wp_enqueue_script(
			$jsl_handle,
			"{$this->url['js']}jls/jquery.lifestream.min.js",
			array('jquery'), 
			'', 
			true
		);
		wp_enqueue_script(
			$this->plugin_prefix . 'run_jls_js',
			"{$this->url['js']}run_jls.js",
			array($jsl_handle), 
			'', 
			true
		);
		
		$html = '<div class="jls_container"></div>';
		
		return $html;
	}
	
}