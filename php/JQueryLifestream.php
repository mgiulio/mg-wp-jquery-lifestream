<?php

require_once 'JQueryLifestreamBase.php';

final class mgJQueryLifestream extends mgJQueryLifestreamBase  {

	function __construct() {
		parent::__construct(array());
		
		$this->menu_slug = 'jls';
		$this->settings_group = $this->menu_slug;
		
		add_action('widgets_init', array($this, 'register_widget'));
		if (is_admin()) {
			add_action('admin_init', array($this, 'setup_settings'));
			add_action('admin_menu', array($this, 'setup_menu'));
			add_action("pre_update_option_jls", array($this, 'regenerate_js'));
			add_filter("plugin_action_links_{$this->main_plugin_file}", array($this, 'setup_plugin_action_links'));
		}
		else {
			add_shortcode('jls', array($this, 'run_shortcode'));
		}
	}
	
	function setup_plugin_action_links($actions) {
		$actions['Settings'] = "<a href=\"{$this->settings_page_url}\" title=\"Configure the lifestream\">Settings</a>"; 
		return $actions;
	}
	
	function on_installation() {
		$ok = $this->check_requirements();
		if (!is_wp_error($ok))
			$this->setup_default_options();
		else {
			trigger_error('Failed requirements: ' .implode($ok->get_error_messages(), '. '), E_USER_ERROR);
			return;
		}
	}
	
	private function setup_default_options() {
		$this->update_option(array(
			'no_services' => true,
			'limit' => 10,
			'services' => array(
				'bitbucket' => array('user' => ''),
				'bitly' => array('user' => ''),
				'blogger' => array('user' => ''),
				'citeulike' => array('user' => ''),
				'dailymotion' => array('user' => ''),
				'delicious' => array('user' => ''),
				'deviantart' => array('user' => ''),
				'disqus' => array('user' => ''),
				'dribbble' => array('user' => ''),
				'facebook_page' => array('user' => ''),
				'flickr' => array('user' => ''),
				'foomark' => array('user' => ''),
				'formspring' => array('user' => ''),
				'forrst' => array('user' => ''),
				'foursquare' => array('user' => ''),
				'gimmebar' => array('user' => ''),
				'github' => array('user' => ''),
				'googleplus' => array('user' => ''),
				'googlereader' => array('user' => ''),
				'hypem' => array('user' => ''),
				'instapaper' => array('user' => ''),
				'iusethis' => array('user' => ''),
				'lastfm' => array('user' => ''),
				'librarything' => array('user' => ''),
				'mendeley' => array('user' => ''),
				'miso' => array('user' => ''),
				'mlkshk' => array('user' => ''),
				'pinboard' => array('user' => ''),
				'posterous' => array('user' => ''),
				'quora' => array('user' => ''),
				'reddit' => array('user' => ''),
				'rss' => array('user' => ''),
				'slideshare' => array('user' => ''),
				'snipplr' => array('user' => ''),
				'stackoverflow' => array('user' => ''),
				'tumblr' => array('user' => ''),
				'twitter' => array('user' => ''),
				'vimeo' => array('user' => ''),
				'wikipedia' => array('user' => ''),
				'wordpress' => array('user' => ''),
				'youtube' => array('user' => ''),
				'zotero' => array('user' => '')
			)
		));
	}
	
	private function check_requirements() {
		$err_msgs = array();
		
		// WP version check
		if (version_compare(get_bloginfo('version'), '3.0', '<'))
			$err_msgs[] = 'WordPress 3.0 or later';
			
		// PHP version
		if (version_compare(phpversion(), '5.2.4' , '<'))
			$err_msgs[] = 'PHP 5.2.4 or later';
			
		if (empty($err_msgs))
			return true;
		
		$wp_err = new WP_Error();
		$c = 0;
		foreach ($err_msgs as $m)
			$wp_err->add($c++, $m);
		return $wp_err;
	}
	
	function register_widget() {
		require_once 'Widget.php';
		register_widget('JLSWidget');
	}
	
	function regenerate_js($cfg) {
		$out = '';
		
		$available_services = $cfg['services'];
		$stream_services = array();
		
		foreach ($available_services as $s_name => $s_cfg) {
			if (empty($s_cfg['user']))
				continue;
			$stream_services[] = array(
				'service' => $s_name,
				'user' => $s_cfg['user']
			);
			$out .= file_get_contents("{$this->path['js']}jls/src/services/{$s_name}.js");
		}
		
		if (empty($stream_services))
			$cfg['no_services'] = true;
		else {
			$cfg['no_services'] = false;
			$out = file_get_contents("{$this->path['js']}jls/src/core.js") . $out;
			$js_service_list = json_encode($stream_services);
			ob_start();
			?>
				(function($) {
					$(function() {
						$('.jls_container').lifestream({
							limit: <?php echo $cfg['limit']; ?>,
							list: <?php echo $js_service_list; ?>
						});
				
					});
				})(jQuery);
			<?php
			$out .= ob_get_contents();
			ob_end_clean();
			file_put_contents("{$this->path['js']}jls.js", $out);
		}
		
		return $cfg;
	}
	
	function setup_menu() {
		add_options_page(
			'jQuery Lifestream',
			'jQuery Lifestream',
			'manage_options',
			$this->menu_slug,
			array($this, 'render_menu_page')
		);
		
		$this->settings_page_url = admin_url("options-general.php?page={$this->menu_slug}");
	}
	
	function setup_settings() {
		register_setting(
			$this->settings_group, 
			$this->plugin_option_name,
			array($this, 'validate')
		);
	
		add_settings_section(
			'jls_services', 
			'Services', 
			array($this, 'services_desc'),
			$this->menu_slug
		);
		
		$cfg = $this->get_option();
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
		ServiceRenderer::$plugin_option_name = $this->plugin_option_name;
		
		add_settings_section(
			'jls_misc', 
			'Misc', 
			array($this, 'misc_desc'),
			$this->menu_slug
		);
		
		add_settings_field(
			'jls_limit',
			'limit',
			array($this, 'render_limit'),
			$this->menu_slug,
			'jls_misc'
		);
	}
	
	function misc_desc() {
		echo "General settings";
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
		return $this->render_lifestream();
	}
	
	function render_lifestream() {
		$cfg = $this->get_option();

		if ($cfg['no_services'])
			return '';
			
		wp_enqueue_script(
			$this->plugin_prefix . 'jls_js',
			"{$this->url['js']}jls.js",
			array(),
			'', 
			true
		);
		
		$html = '<div class="jls_container"></div>';
		
		return $html;
	}
	
	function render_limit() {
		$cfg = $this->get_option();
		?>
			<input 
				name="<?php echo $this->plugin_option_name; ?>[limit]" 
				type="text" 
				value="<?php echo $cfg['limit']; ?>"
			>
		<?php
	}
	
	private function is_int_in_range($str, $min, $max) {
		if (!is_numeric($str))
			return false;
		$x = $str + 0;
		if (!is_int($x))
			return false;
		return $min <= $x && $x <= $max;
	}
	
	function validate($in) {
		$out = $this->get_option();
		
		if ($this->is_int_in_range($in['limit'], 1, 1000))
			$out['limit'] = $in['limit'];
		else
			add_settings_error(
				'jls_limit', 
				'jls_limit', 
				'Limit must be an integer in the range [1, 1000]',
				'error'
			);
		
		return $out;
	}
	
}

class ServiceRenderer {

	static $plugin_option_name;
	
	function __construct($name, $cfg) {
		$this->name = $name;
		$this->cfg = $cfg;
	}
	
	function render() {
		?>
			<input 
				name="<?php echo self::$plugin_option_name; ?>[services][<?php echo $this->name; ?>][user]" 
				type="text" 
				value="<?php echo $this->cfg['user']; ?>"
			>
		<?php
	}
	
}