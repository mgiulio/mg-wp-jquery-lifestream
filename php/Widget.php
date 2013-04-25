<?php

class JLSWidget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'jls_widget',
			'jQuery Lifestream',
			array(
				'classname' => 'jls_widget',
				'description' => 'Display your lifestream'
			)
		);
	}
	
	function form($instance) {
		?>
			<a href="<?php echo admin_url("options-general.php?page=jls"); ?>">Lifestream configuration</a>
		<?php
	}
	
	function update($new_instance, $old_instance) {
	}
	
	function widget($args, $instance) {
		extract($args);
		
		echo $before_widget;
			echo $before_title;
				echo "Lifestream";
			echo $after_title;
			echo mgJQueryLifestream::render();
		echo $after_widget;
	}

}