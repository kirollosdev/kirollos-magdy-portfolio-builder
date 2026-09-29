<?php
/**
 * Extra fields on the portfolios post type.
 *
 * The portfolio-widgets module ships the secondary image meta box, but nothing
 * that stores the live site address or a set of screenshots. The portfolio
 * cards need both, so they are added here in the plugin rather than by editing
 * the module, which stays untouched and replaceable.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KMPB_Portfolio_Meta {

	const NONCE   = 'kmpb_portfolio_meta';
	const URL     = 'project_url';
	const GALLERY = 'gallery';
	const DATE    = 'work_date';

	/**
	 * How many photos a project may carry.
	 *
	 * The cards lay the gallery out in a fixed 2x2 block and pad it with
	 * placeholders when a project is short, so every card is the same height
	 * whatever it has. More than four would break that block.
	 */
	const LIMIT = 4;

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_box' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'media_assets' ) );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
	}

	/**
	 * The post type the module registered, read through its own accessor so a
	 * renamed post type stays in step.
	 *
	 * @return string
	 */
	public static function post_type() {
		if ( class_exists( '\KirollosMagdy\PortfolioWidgets\Module' ) ) {
			$module = \KirollosMagdy\PortfolioWidgets\Module::instance();
			if ( $module && method_exists( $module, 'post_type' ) ) {
				return $module->post_type();
			}
		}
		return 'portfolios';
	}

	public function add_box() {

		add_meta_box(
			'kmpb_portfolio_gallery',
			__( 'Project Gallery', 'kirollos-magdy-portfolio-builder' ),
			array( $this, 'render_gallery' ),
			self::post_type(),
			'normal',
			'high'
		);

		add_meta_box(
			'kmpb_portfolio_link',
			__( 'Project Link', 'kirollos-magdy-portfolio-builder' ),
			array( $this, 'render' ),
			self::post_type(),
			'side',
			'default'
		);
	}

	/**
	 * wp.media is what the gallery picker opens, and it is not on the post
	 * screen unless something asks for it.
	 *
	 * @param string $hook
	 */
	public function media_assets( $hook ) {

		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || self::post_type() !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();
	}

	/**
	 * @param WP_Post $post
	 */
	public function render( $post ) {

		wp_nonce_field( self::NONCE, self::NONCE );

		$value = get_post_meta( $post->ID, self::URL, true );
		?>
		<p>
			<label for="kmpb-project-url" style="display:block;margin-bottom:4px;font-weight:600">
				<?php esc_html_e( 'Live site address', 'kirollos-magdy-portfolio-builder' ); ?>
			</label>
			<input type="url" id="kmpb-project-url" name="<?php echo esc_attr( self::URL ); ?>"
				value="<?php echo esc_attr( $value ); ?>" placeholder="https://" style="width:100%">
		</p>
		<p class="description">
			<?php esc_html_e( 'Shown on the portfolio cards. Leave empty to hide the link.', 'kirollos-magdy-portfolio-builder' ); ?>
		</p>

		<hr style="margin:14px 0">

		<p>
			<label for="kmpb-work-date" style="display:block;margin-bottom:4px;font-weight:600">
				<?php esc_html_e( 'Work date', 'kirollos-magdy-portfolio-builder' ); ?>
			</label>
			<input type="month" id="kmpb-work-date" name="<?php echo esc_attr( self::DATE ); ?>"
				value="<?php echo esc_attr( get_post_meta( $post->ID, self::DATE, true ) ); ?>" style="width:100%">
		</p>
		<p class="description">
			<?php esc_html_e( 'The month the work was done. Shown on the card opposite the button.', 'kirollos-magdy-portfolio-builder' ); ?>
		</p>
		<?php
	}

	/**
	 * The gallery picker.
	 *
	 * Ids are stored as one comma separated string rather than an array, which
	 * is the shape the importer writes and the media matcher merges into, so
	 * all three agree without conversion.
	 *
	 * @param WP_Post $post
	 */
	public function render_gallery( $post ) {

		wp_nonce_field( self::NONCE, self::NONCE );

		$ids = self::stored_ids( $post->ID );
		$box = 'position:relative;width:90px;height:90px;border:1px solid #dcdcde;border-radius:4px;overflow:hidden';
		$img = 'width:100%;height:100%;object-fit:cover;display:block';
		$rm  = 'position:absolute;top:2px;right:2px;width:22px;height:22px;line-height:20px;padding:0;border:0;border-radius:50%;background:rgba(0,0,0,.65);color:#fff;cursor:pointer';
		?>
		<div class="kmpb-gallery">
			<p class="description" style="margin-top:0">
				<?php esc_html_e( 'Up to four screenshots, in this order. The cards lay them out in a fixed block and pad it with placeholders when there are fewer, so every card stays the same shape.', 'kirollos-magdy-portfolio-builder' ); ?>
			</p>

			<ul class="kmpb-gallery__list" style="display:flex;flex-wrap:wrap;gap:10px;margin:12px 0;padding:0;list-style:none">
				<?php
				foreach ( $ids as $id ) {

					$src = wp_get_attachment_image_url( $id, 'thumbnail' );

					if ( ! $src ) {
						continue;
					}

					printf(
						'<li data-id="%1$s" style="%2$s"><img src="%3$s" alt="" style="%4$s"><button type="button" class="kmpb-gallery__remove" aria-label="%5$s" style="%6$s">&times;</button></li>',
						esc_attr( (string) $id ),
						esc_attr( $box ),
						esc_url( $src ),
						esc_attr( $img ),
						esc_attr__( 'Remove', 'kirollos-magdy-portfolio-builder' ),
						esc_attr( $rm )
					);
				}
				?>
			</ul>

			<input type="hidden" class="kmpb-gallery__input" name="<?php echo esc_attr( self::GALLERY ); ?>"
				value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">

			<button type="button" class="button kmpb-gallery__add">
				<?php esc_html_e( 'Add images', 'kirollos-magdy-portfolio-builder' ); ?>
			</button>

			<span class="kmpb-gallery__count" style="margin-left:10px;color:#646970"></span>

			<button type="button" class="button-link kmpb-gallery__clear" style="margin-left:10px;color:#b32d2e">
				<?php esc_html_e( 'Clear all', 'kirollos-magdy-portfolio-builder' ); ?>
			</button>
		</div>

		<script>
		( function ( $ ) {
			var frame;
			var $box = $( '#kmpb_portfolio_gallery' );
			var limit = <?php echo (int) self::LIMIT; ?>;
			var boxCss = <?php echo wp_json_encode( $box ); ?>;
			var imgCss = <?php echo wp_json_encode( $img ); ?>;
			var rmCss  = <?php echo wp_json_encode( $rm ); ?>;

			function sync() {
				var ids = $box.find( '.kmpb-gallery__list li' ).map( function () {
					return $( this ).data( 'id' );
				} ).get();

				$box.find( '.kmpb-gallery__input' ).val( ids.join( ',' ) );

				var full = ids.length >= limit;

				$box.find( '.kmpb-gallery__add' ).prop( 'disabled', full );
				$box.find( '.kmpb-gallery__count' ).text( ids.length + ' / ' + limit );
			}

			$box.on( 'click', '.kmpb-gallery__add', function ( e ) {
				e.preventDefault();

				if ( ! frame ) {
					frame = wp.media( {
						title: <?php echo wp_json_encode( __( 'Add gallery images', 'kirollos-magdy-portfolio-builder' ) ); ?>,
						button: { text: <?php echo wp_json_encode( __( 'Add to gallery', 'kirollos-magdy-portfolio-builder' ) ); ?> },
						library: { type: 'image' },
						multiple: 'add'
					} );

					frame.on( 'select', function () {
						var existing = $box.find( '.kmpb-gallery__input' ).val().split( ',' ).filter( Boolean );

						frame.state().get( 'selection' ).map( function ( item ) {
							var att = item.toJSON();

							if ( existing.length >= limit || existing.indexOf( String( att.id ) ) !== -1 ) {
								return;
							}

							existing.push( String( att.id ) );

							var url = ( att.sizes && att.sizes.thumbnail ) ? att.sizes.thumbnail.url : att.url;

							$box.find( '.kmpb-gallery__list' ).append(
								'<li data-id="' + att.id + '" style="' + boxCss + '">' +
								'<img src="' + url + '" alt="" style="' + imgCss + '">' +
								'<button type="button" class="kmpb-gallery__remove" style="' + rmCss + '">&times;</button>' +
								'</li>'
							);
						} );

						sync();
					} );
				}

				frame.open();
			} );

			$box.on( 'click', '.kmpb-gallery__remove', function ( e ) {
				e.preventDefault();
				$( this ).closest( 'li' ).remove();
				sync();
			} );

			$box.on( 'click', '.kmpb-gallery__clear', function ( e ) {
				e.preventDefault();
				$box.find( '.kmpb-gallery__list' ).empty();
				sync();
			} );
			sync();
		}( jQuery ) );
		</script>
		<?php
	}

	/**
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function save( $post_id, $post ) {

		if ( ! isset( $_POST[ self::NONCE ] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $post || self::post_type() !== $post->post_type ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Both boxes print the same nonce, so each field is written only when it
		// was actually submitted rather than blanked by the other box's save.
		if ( isset( $_POST[ self::URL ] ) ) {

			$url = esc_url_raw( wp_unslash( $_POST[ self::URL ] ) );

			if ( '' === $url ) {
				delete_post_meta( $post_id, self::URL );
			} else {
				update_post_meta( $post_id, self::URL, $url );
			}
		}

		if ( isset( $_POST[ self::DATE ] ) ) {

			$date = sanitize_text_field( wp_unslash( $_POST[ self::DATE ] ) );

			// The month input hands back Y-m. A Y-m-d left behind by an older
			// build is trimmed to its month rather than thrown away.
			if ( preg_match( '/^(\d{4}-\d{2})-\d{2}$/', $date, $m ) ) {
				$date = $m[1];
			}

			if ( '' === $date || ! preg_match( '/^\d{4}-\d{2}$/', $date ) ) {
				delete_post_meta( $post_id, self::DATE );
			} else {
				update_post_meta( $post_id, self::DATE, $date );
			}
		}

		if ( isset( $_POST[ self::GALLERY ] ) ) {

			$ids = explode( ',', sanitize_text_field( wp_unslash( $_POST[ self::GALLERY ] ) ) );
			$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );

			// Enforced here as well as in the picker, since the picker is only
			// the browser's opinion of what was submitted.
			$ids = array_slice( $ids, 0, self::LIMIT );

			if ( $ids ) {
				update_post_meta( $post_id, self::GALLERY, implode( ',', $ids ) );
			} else {
				delete_post_meta( $post_id, self::GALLERY );
			}
		}
	}

	/**
	 * Exactly what is stored, with no featured-image fallback.
	 *
	 * @param int $post_id
	 * @return int[]
	 */
	private static function stored_ids( $post_id ) {

		$raw = get_post_meta( $post_id, self::GALLERY, true );

		if ( is_array( $raw ) ) {
			$ids = $raw;
		} else {
			$ids = ( '' === (string) $raw ) ? array() : explode( ',', (string) $raw );
		}

		return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
	}

	/**
	 * The gallery for the cards: what is stored, falling back to the featured
	 * image so a project without a gallery still shows something.
	 *
	 * @param int $post_id
	 * @return int[]
	 */
	public static function gallery_ids( $post_id ) {


		$ids = self::stored_ids( $post_id );

		if ( ! $ids ) {
			$thumb = (int) get_post_thumbnail_id( $post_id );
			if ( $thumb ) {
				$ids[] = $thumb;
			}
		}

		return array_slice( $ids, 0, self::LIMIT );
	}

	/**
	 * The work date, formatted for display.
	 *
	 * @param int    $post_id
	 * @param string $format A date() format. Defaults to the site's setting.
	 * @return string '' when no date is set.
	 */
	public static function work_date( $post_id, $format = '' ) {

		$raw = (string) get_post_meta( $post_id, self::DATE, true );

		if ( '' === $raw ) {
			return '';
		}

		// strtotime needs a day; what is stored is a month.
		$time = strtotime( preg_match( '/^\d{4}-\d{2}$/', $raw ) ? $raw . '-01' : $raw );

		if ( ! $time ) {
			return '';
		}

		if ( '' === $format ) {
			$format = 'F Y';
		}

		return date_i18n( $format, $time );
	}

	/**
	 * The card logo: the module's secondary image if set, else the featured
	 * image, so a card always has something to show.
	 *
	 * @param int $post_id
	 * @return int Attachment ID, 0 when there is none.
	 */
	public static function logo_id( $post_id ) {

		$key = function_exists( 'pw_secondary_image_meta_key' )
			? pw_secondary_image_meta_key()
			: '_secondary_featured_image';

		$id = (int) get_post_meta( $post_id, $key, true );

		if ( ! $id ) {
			$id = (int) get_post_thumbnail_id( $post_id );
		}

		return $id;
	}
}
