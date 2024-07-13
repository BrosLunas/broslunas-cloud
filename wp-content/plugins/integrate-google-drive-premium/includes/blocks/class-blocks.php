<?php

namespace IGD;

defined( 'ABSPATH' ) || exit();

class Blocks {
	/**
	 * @var null
	 */
	protected static $instance = null;

	public function __construct() {
		add_filter( 'block_categories_all', [ $this, 'filter_block_categories' ], 10, 2 );
		add_action( 'init', [ $this, 'register_block' ] );
		add_action( 'admin_print_styles', [ $this, 'add_pro_styles' ] );
	}

	public function add_pro_styles() {

		if ( ! igd_fs()->can_use_premium_code__premium_only() ) { ?>
			<style>
				.editor-block-list-item-igd-browser:after,
				.editor-block-list-item-igd-gallery:after,
				.editor-block-list-item-igd-uploader:after,
				.editor-block-list-item-igd-media:after,
				.editor-block-list-item-igd-slider:after,
				.editor-block-list-item-igd-search:after {
					content: '\f160';
					font-family: 'dashicons', serif;
					font-size: 20px;
					color: #eaeaea;
					position: absolute;
					top: 5px;
					right: 5px;
				}
			</style>
		<?php }
	}

	public function register_block() {
		$pro_blocks = [
			'browser',
			'gallery',
			'uploader',
			'media',
			'search',
			'slider',
		];

		$free_blocks = [
			'embed',
			'download',
			'view',
		];

		$blocks = igd_fs()->can_use_premium_code__premium_only() ? array_merge( $pro_blocks, $free_blocks ) : array_merge( $free_blocks, $pro_blocks );

		// Add 'shortcodes' at the beginning of the array
		array_unshift( $blocks, 'shortcodes' );

		// Register all blocks
		foreach ( $blocks as $block ) {
			$callback = $block === 'shortcodes' ? [ $this, 'render_module_shortcode_block' ] : [
				$this,
				'render_module_block'
			];

			register_block_type( IGD_INCLUDES . '/blocks/build/' . $block, [
				'render_callback' => $callback,
			] );
		}
	}


	public function render_module_shortcode_block( $attributes, $content ) {
		$id = ! empty( $attributes['id'] ) ? $attributes['id'] : [];

		return Shortcode::instance()->render_shortcode( [ 'id' => $id ] );
	}

	public function render_module_block( $attributes, $content ) {
		$data = ! empty( $attributes['data'] ) ? $attributes['data'] : [];

		return Shortcode::instance()->render_shortcode( [], $data );
	}

	function filter_block_categories( $block_categories, $editor_context ) {
		if ( ! empty( $editor_context->post ) ) {
			$new_categories = [
				[
					'slug' => 'igd-category',
					'title' => __( 'Integrate Google Drive', 'integrate-google-drive' ),
					'icon' => null,
				]
			];

			$block_categories = array_merge( $block_categories, $new_categories );
		}

		return $block_categories;
	}

	/**
	 * @return Blocks|null
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

Blocks::instance();


