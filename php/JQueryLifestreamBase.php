<?php

abstract class mgJQueryLifestreamBase {

	protected $plugin_prefix;
	protected $url = array();
	protected $path = array();
	protected $main_plugin_file;

	function __construct($cfg) {
		$this->plugin_prefix = strtolower(get_class($this)) . '_';
		$this->plugin_option_name = rtrim($this->plugin_prefix, '_');
		
		$this->main_plugin_file = 'mg-wp-jquery-lifestream/plugin.php';
		register_activation_hook($this->main_plugin_file, array($this, 'on_activation'));
		
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
		if (!$this->get_option()) {
			$this->on_installation();
			add_option($this->plugin_option_name, array());
		}
	}
	
	protected function add_action($wp_action_string, $method, $priority = 10, $accepted_args = 1) {
		add_action($wp_action_string, array($this, $method), $priority, $accepted_args);
	}
	
	protected function update_option($value) {
		return update_option($this->plugin_option_name, $value);
	}
	
	protected function get_option() {
		return get_option($this->plugin_option_name);
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
	
	protected function is_int_in_range($str, $min, $max) {
		if (!is_numeric($str))
			return false;
		$x = $str + 0;
		if (!is_int($x))
			return false;
		return $min <= $x && $x <= $max;
	}
	
}