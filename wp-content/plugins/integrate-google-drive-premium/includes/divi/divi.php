<?php

if ( ! function_exists( 'igd_initialize_extension' ) ):
	/**
	 * Creates the extension's main class instance.
	 *
	 * @since 1.0.0
	 */
	function igd_initialize_extension() {
		if ( class_exists( 'IGD\DiviExtension' ) ) {
			return;
		}

		require_once plugin_dir_path( __FILE__ ) . 'includes/DiviExtension.php';
	}

	add_action( 'divi_extensions_init', 'igd_initialize_extension' );

endif;