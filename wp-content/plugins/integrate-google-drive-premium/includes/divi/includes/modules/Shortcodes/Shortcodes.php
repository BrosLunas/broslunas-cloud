<?php

namespace IGD\Divi;

use IGD\Shortcode;
use IGD\Shortcode_Builder;

class Shortcodes extends \ET_Builder_Module {

	public $slug = 'igd_shortcodes';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://softlabbd.com/integrate-google-drive/',
		'author'     => 'SoftLab',
		'author_uri' => 'https://softlabbd.com/',
	);

	public function init() {
		$this->name = esc_html__( 'Google Drive Shortcodes', 'integrate-google-drive' );


		$this->settings_modal_toggles = [
			'general' => [
				'toggles' => [
					'main_content' => 'Module Configuration',
				],
			],
		];

		$this->advanced_fields = [
			'background'     => false,
			'borders'        => false,
			'box_shadow'     => false,
			'button'         => false,
			'filters'        => false,
			'fonts'          => false,
			'margin_padding' => false,
			'text'           => false,
			'link_options'   => false,
			'height'         => false,
			'scroll_effects' => false,
			'animation'      => false,
			'transform'      => false,
		];
	}

	public function get_fields() {
		$shortcodes = Shortcode_Builder::instance()->get_shortcodes();

		if ( ! empty( $shortcodes ) ) {
			$shortcodes = array_column( $shortcodes, 'title', 'id' );
		}

		$shortcodes = [ '0' => __( 'Select Shortcode', 'integrate-google-drive' ) ] + $shortcodes;

		return array(
			'id' => array(
				'label'           => esc_html__( 'Select Shortcode Module', 'integrate-google-drive' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'description'     => esc_html__( 'Select a shortcode module', 'integrate-google-drive' ),
				'toggle_slug'     => 'main_content',
				'default'         => '0',
				'options'         => $shortcodes,
			),
		);
	}

	public function render( $attrs, $content = null, $render_slug = null ) {
		$id = $this->props['id'];


		if ( ! $id ) {
			return;
		}

		ob_start();

		echo Shortcode::instance()->render_shortcode( [
			'id' => $id,
		] );

		return ob_get_clean();
	}
}

new Shortcodes;
