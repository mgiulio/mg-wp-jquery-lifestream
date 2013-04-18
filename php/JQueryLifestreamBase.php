<?php

abstract class mgJQueryLifestreamBase {

	protected $plugin_prefix;
	protected $url = array();
	protected $path = array();

	function __construct($cfg) {
		$this->plugin_prefix = strtolower(get_class($this)) . '_';
		$this->plugin_option_name = rtrim($this->plugin_prefix, '_');
		
		$main_plugin_file = 'mg-wp-jquery-lifestream/plugin.php';
		register_activation_hook($main_plugin_file, array($this, 'on_activation'));
		
		$d = dirname(__FILE__);
		$pdu = plugin_dir_url($d);
		$pdp = plugin_dir_path($d);
		$this->url = array(
			'plugin' => $pdu,
			'js' => "{$pdu}js/"
		);
		$this->path = array(
			'plugin' => $pdp,
			'js' => "{$pdp}js/"
		);
	}
	
	function on_activation() {
		if (!get_option($this->plugin_option_name)) {
			add_option($this->plugin_option_name, array());
			$this->on_installation();
		}
	}
	
	protected function add_action($wp_action_string, $method, $priority = 10, $accepted_args = 1) {
		add_action($wp_action_string, array($this, $method), $priority, $accepted_args);
	}
	
	protected function is_ajax_request($action) {
		return
			defined('DOING_AJAX' ) && 
			DOING_AJAX &&
			!empty($_REQUEST['action']) &&
			$_REQUEST['action'] === $action
		;
	}
	
	protected function inject_js($script, $params = array()) {
		$js_handle = $this->plugin_prefix . $script . '_js';
		
		wp_enqueue_script(
			$js_handle,
			$this->url['js'] . $script . '.js',
			array('jquery'), 
			'', 
			true
		);
		
		if (!empty($params))
			wp_localize_script($js_handle, $this->plugin_prefix . 'args', $params);
	}
	
	protected function log($x) {
		if (WP_DEBUG === true) {
			if (is_array($x) || is_object($x)) {
				error_log('Utils::log:' . print_r($x, true));
			} else {
				error_log('Utils::log:' . $x);
			}
		}
	}
	
}