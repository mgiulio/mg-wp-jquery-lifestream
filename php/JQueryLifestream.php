<?php

require_once 'JQueryLifestreamBase.php';

final class mgJQueryLifestream extends mgJQueryLifestreamBase  {

	function __construct() {
		parent::__construct(array());
		
		$this->install_option();
		
		$this->menu_slug = 'jls';
		$this->settings_group = $this->menu_slug;
		
		add_action('admin_init', array($this, 'setup_settings'));
		add_action('admin_menu', array($this, 'setup_menu'));
		
		add_shortcode('jls', array($this, 'run_shortcode'));
	}
	
	private function install_option() {
		if (get_option('jls'))
			return;
		
		update_option('jls', array(
			'services' => array(
				'bitbucket' => array('user' => ''),
				'bitly' => array('user' => ''),
				'blogger' => array('user' => ''),
				'citeulike' => array('user' => ''),
				'dailymotion' => array('user' => ''),
				'delicious' => array('user' => ''),
				'deviantart' => array('user' => ''),
				'disqus' => array('user' => ''),
				'dribble' => array('user' => ''),
				'facebook_page' => array('user' => ''),
				'flickr' => array('user' => ''),
				'foomark' => array('user' => ''),
				'formspring' => array('user' => ''),
			)
		));
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
		register_setting(
			$this->settings_group, 
			'jls'//,
			//array($this, $validation)
		);
		
		add_settings_section(
			'jls_services', 
			'Services', 
			array($this, 'services_desc'),
			$this->menu_slug
		);
		
		$cfg = get_option('jls');
		$services = $cfg['services'];
		foreach ($services as $service_name => $service_cfg) {
			$service_renderer = new ServiceRenderer($service_name, $service_cfg);
			add_settings_field(
				"jls_services_{$service_name}_user",
				$service_name,
				array($service_renderer, 'render'),
				$this->menu_slug,
				'jls_services'
			);
		}
	}
	
	function services_desc() {
		echo "Fill in the username for the services you want to include in the lifestream";
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

class ServiceRenderer {
	
	function __construct($name, $cfg) {
		$this->name = $name;
		$this->cfg = $cfg;
	}
	
	function render() {
		?>
			<input 
				name="jls[services][<?php echo $this->name; ?>][user]" 
				type="text" 
				value="<?php echo $this->cfg['user']; ?>"
			>
		<?php
	}
	
}