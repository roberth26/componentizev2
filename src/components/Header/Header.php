<?php
class Header extends AbstractComponent {
	function __construct() {
		$this->name = 'Header';
	}
	
	function render( $props ) {
		wp_head();
		echo $this->name;
	}
}
?>