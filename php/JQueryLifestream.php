<?php

require_once 'JQueryLifestreamBase.php';

final class mgJQueryLifestream extends mgJQueryLifestreamBase  {

	function __construct() {
		parent::__construct(array());
		
		add_shortcode('jls', array($this, 'run_shortcode'));
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