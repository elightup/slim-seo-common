<?php
namespace eLightUp\SlimSEO\Common\Settings;

class Post {
	public static function setup(): void {
		add_action( 'admin_print_styles-post.php', [ __CLASS__, 'enqueue' ] );
		add_action( 'admin_print_styles-post-new.php', [ __CLASS__, 'enqueue' ] );
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_box' ] );
	}

	public static function enqueue(): void {
		if ( ! self::is_valid() ) {
			return;
		}

		$tabs = self::get_tabs();

		if ( count( $tabs ) > 1 ) {
			wp_enqueue_script( 'slim-seo-components', 'https://cdn.jsdelivr.net/gh/elightup/slim-seo@master/js/components.js', [], '1.0.0', true );
		}

		do_action( 'slim_seo_meta_box_enqueue' );
	}

	public static function add_meta_box() {
		if ( ! self::is_valid() ) {
			return;
		}

		$tabs = self::get_tabs();

		if ( empty( $tabs ) ) {
			return;
		}

		$context    = apply_filters( 'slim_seo_meta_box_context', 'normal' );
		$priority   = apply_filters( 'slim_seo_meta_box_priority', 'low' );
		$post_types = self::get_post_types();

		foreach ( $post_types as $post_type ) {
			add_meta_box( 'slim-seo', __( 'Search Engine Optimization', 'slim-seo' ), [ __CLASS__, 'render' ], $post_type, $context, $priority );
		}
	}

	public static function render(): void {
		$tabs = self::get_tabs();

		if ( empty( $tabs ) ) {
			return;
		}

		if ( count( $tabs ) > 1 ) {
			self::render_tabs();
		}

		self::render_panels();
	}

	public static function render_tabs(): void {
		$tabs = self::get_tabs();

		echo '<nav class="ss-tab-list">';

		foreach ( $tabs as $key => $label ) {
			printf( '<a href="#%s" class="ss-tab">%s</a>', esc_attr( $key ), esc_html( $label ) );
		}

		echo '</nav>';
	}

	public static function render_panels(): void {
		$panels = self::get_panels();

		if ( 1 === count( $panels ) ) {
			echo reset( $panels ); // phpcs:ignore

			return;
		}

		foreach ( $panels as $key => $panel ) {
			printf( '<div id="%s" class="ss-tab-pane">%s</div>', esc_attr( $key ), $panel ); // phpcs:ignore
		}
	}

	public static function get_tabs(): array {
		$tabs = apply_filters( 'slim_seo_meta_box_tabs', [] );

		return $tabs;
	}

	public static function get_panels(): array {
		$panels = apply_filters( 'slim_seo_meta_box_panels', [] );

		return $panels;
	}

	public static function is_valid(): bool {
		$post_types = self::get_post_types();
		$screen     = get_current_screen();

		return in_array( $screen->post_type, $post_types, true );
	}

	public static function get_post_types(): array {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );

		unset( $post_types['attachment'] );

		$post_types = apply_filters( 'slim_seo_post_types', $post_types );
		$post_types = array_keys( $post_types );
		$post_types = apply_filters( 'slim_seo_meta_box_post_types', $post_types );

		return $post_types;
	}
}
