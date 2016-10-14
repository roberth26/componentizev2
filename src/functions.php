<?php
$components = array();
$mobile_css = array();
$js = array();

add_action( 'wp_enqueue_scripts', function() {
	// move jquery to footer and use cdn
	wp_deregister_script( 'jquery' );
	wp_register_script(
		'jquery',
		'https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js',
		'',
		'2.1.3',
		true
	);
	wp_enqueue_script( 'jquery' );
	wp_enqueue_style(
		'main',
		get_stylesheet_directory_uri() . '/style.css',
		'',
		null
	);
});

add_filter( 'template_include', function( $t ) {
	$template = str_replace( '.php', '', basename( $t ) );
	wp_enqueue_style(
		'template-' . $template,
		get_stylesheet_directory_uri() . "/dist/templates/{$template}/{$template}.min.css",
		array( 'main' )
	);
	wp_enqueue_script(
		'template-' . $template,
		get_stylesheet_directory_uri() . "/dist/templates/{$template}/{$template}.min.js",
		array( 'jquery' ),
		null,
		true
	);
	return $t;
});

abstract class AbstractComponent {

	public abstract function render( $props );

	public function set_above_fold( $is_above_fold ) {
		$this->is_above_fold = $is_above_fold;
	}

	public function is_above_fold() {
		return $this->is_above_fold;
	}

	public function set_external( $is_external ) {
		$this->is_external = $is_external;
	}

	public function is_external() {
		return $this->is_external;
	}

	public function set_imported( $is_imported ) {
		$this->is_imported = $is_imported;
	}

	public function is_imported() {
		return $this->is_imported;
	}

	private $name;
	private $is_above_fold;
	private $is_external;
	private $is_imported;
}

function import( $component, $above_fold = true, $external = false ) {
	global $components;
	if ( array_key_exists( $component, $components ) ) {
		if ( $above_fold && !$components[ $component ]->is_above_fold() )
			$components[ $component ]->set_above_fold( true );
		if ( $external && !$components[ $component ]->is_external() ) {
			$components[ $component ]->set_external( true );
		}
	} else {
		if ( !class_exists( $component ) )
			return;
		$the_component = new $component();
		$the_component->set_above_fold( $above_fold );
		$the_component->set_external( $external );
		$components[ $component ] = $the_component;
		if ( $external ) {			
			wp_enqueue_script(
				$component,
				get_stylesheet_directory_uri() . '/dist/components/' . $component . '/' . $component . '.min.js',
				array( 'jquery' ),
				null,
				true
			);
			wp_enqueue_style(
				$component,
				get_stylesheet_directory_uri() . '/dist/components/' . $component . '/' . $component . '.min.css',
				array( 'main' )
			);
		};
	}
}

add_action( 'wp_head', function() {
	global $components;
	global $mobile_css;
	echo "<style>";
	foreach( $components as $component_name => $component ) {
		if ( !$component->is_external() )
			echo $mobile_css[ $component_name ];
	}
	echo "</style>";
});

add_action( 'wp_footer', function() {
	global $components;
	global $js;
	echo "<script>";
	foreach( $components as $component_name => $component ) {
		if ( !$component->is_external() )
			echo $js[ $component_name ];
	}
	echo "</script>";
});

?>