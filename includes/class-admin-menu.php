<?php
/**
 * The "Kirollos Magdy" admin menu.
 *
 * One top-level menu holds everything from this plugin and from Kirollos Magdy Portfolio
 * Importer: the Overview page, the Projects post type and its taxonomies, Case-Study Pages,
 * Portfolio Import and Blog Importer. Other code attaches its screens to KMPB_ADMIN_MENU.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KMPB_Admin_Menu {

	const POST_TYPE  = 'portfolios';
	const TAXONOMIES = array(
		'portfolio_category' => 'Categories',
		'portfolio_service'  => 'Services',
		'portfolio_industry' => 'Industries',
	);

	public function __construct() {
		// The post type moves under this menu. Runs on every request because the post
		// type is registered on the front end too.
		add_filter( 'register_post_type_args', array( $this, 'post_type_args' ), 20, 2 );

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'menu' ), 9 );
		add_action( 'admin_menu', array( $this, 'order' ), 999 );
		add_filter( 'parent_file', array( $this, 'parent_file' ) );
		add_filter( 'submenu_file', array( $this, 'submenu_file' ) );
		add_action( 'admin_head', array( $this, 'menu_icon_css' ) );
	}

	public function post_type_args( $args, $post_type ) {
		if ( self::POST_TYPE === $post_type ) {
			$args['show_in_menu'] = KMPB_ADMIN_MENU;
		}
		return $args;
	}

	public function menu() {
		add_menu_page(
			__( 'Kirollos Magdy', 'kirollos-magdy-portfolio-builder' ),
			__( 'Kirollos Magdy', 'kirollos-magdy-portfolio-builder' ),
			'edit_posts',
			KMPB_ADMIN_MENU,
			array( $this, 'render' ),
			$this->icon(),
			3
		);

		add_submenu_page( KMPB_ADMIN_MENU, __( 'Overview', 'kirollos-magdy-portfolio-builder' ), __( 'Overview', 'kirollos-magdy-portfolio-builder' ), 'edit_posts', KMPB_ADMIN_MENU, array( $this, 'render' ) );

		if ( post_type_exists( self::POST_TYPE ) ) {
			add_submenu_page( KMPB_ADMIN_MENU, __( 'Add New Project', 'kirollos-magdy-portfolio-builder' ), __( 'Add New Project', 'kirollos-magdy-portfolio-builder' ), 'edit_posts', 'post-new.php?post_type=' . self::POST_TYPE );
		}
		foreach ( self::TAXONOMIES as $taxonomy => $label ) {
			if ( taxonomy_exists( $taxonomy ) ) {
				add_submenu_page( KMPB_ADMIN_MENU, $label, $label, 'manage_categories', 'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=' . self::POST_TYPE );
			}
		}
	}

	/**
	 * Fixed order for the submenu, whichever plugin or hook added each item.
	 */
	public function order() {
		global $submenu;
		if ( empty( $submenu[ KMPB_ADMIN_MENU ] ) ) {
			return;
		}
		$wanted = array(
			KMPB_ADMIN_MENU,
			'edit.php?post_type=' . self::POST_TYPE,
			'post-new.php?post_type=' . self::POST_TYPE,
		);
		foreach ( array_keys( self::TAXONOMIES ) as $taxonomy ) {
			$wanted[] = 'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=' . self::POST_TYPE;
		}
		$wanted = array_merge( $wanted, array( 'kmpp', 'kmpfi-portfolio-import', 'kmbi-blog-importer' ) );

		$items = $submenu[ KMPB_ADMIN_MENU ];
		usort(
			$items,
			static function ( $a, $b ) use ( $wanted ) {
				$pa = array_search( $a[2], $wanted, true );
				$pb = array_search( $b[2], $wanted, true );
				$pa = false === $pa ? 100 : $pa;
				$pb = false === $pb ? 100 : $pb;
				return $pa - $pb;
			}
		);
		$submenu[ KMPB_ADMIN_MENU ] = $items; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/** Keep the menu open on the taxonomy screens of the post type. */
	public function parent_file( $parent_file ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && isset( self::TAXONOMIES[ $screen->taxonomy ] ) ) {
			return KMPB_ADMIN_MENU;
		}
		return $parent_file;
	}

	public function submenu_file( $submenu_file ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && isset( self::TAXONOMIES[ $screen->taxonomy ] ) ) {
			return 'edit-tags.php?taxonomy=' . $screen->taxonomy . '&post_type=' . self::POST_TYPE;
		}
		return $submenu_file;
	}

	/** The Kirollos Magdy logo, in its own colours. A file URL is shown as an image, not recoloured. */
	private function icon() {
		return KMPB_URL . 'assets/img/logo.svg';
	}

	public function menu_icon_css() {
		echo '<style>#toplevel_page_' . esc_attr( KMPB_ADMIN_MENU ) . ' .wp-menu-image img{width:20px;height:20px;padding:7px 0 0;opacity:1}</style>';
	}

	/* ------------------------------------------------------------ overview */

	private function card( $title, $text, $links ) {
		$links = array_filter( $links );
		if ( ! $links ) {
			return;
		}
		echo '<div class="kmpb-ov__card"><h2>' . esc_html( $title ) . '</h2><p>' . esc_html( $text ) . '</p><p class="kmpb-ov__links">';
		$first = true;
		foreach ( $links as $label => $url ) {
			printf( '<a class="button%s" href="%s">%s</a> ', $first ? ' button-primary' : '', esc_url( $url ), esc_html( $label ) );
			$first = false;
		}
		echo '</p></div>';
	}

	public function render() {
		$pt        = self::POST_TYPE;
		$projects  = post_type_exists( $pt ) ? (int) wp_count_posts( $pt )->publish : 0;
		$articles  = (int) wp_count_posts( 'post' )->publish;
		$importer  = defined( 'KMPFI_VERSION' );
		$blog_imp  = class_exists( 'KMBI_Admin', false );
		$can_pages = current_user_can( 'edit_others_posts' ) && function_exists( 'kmpp_render_admin' );
		?>
		<div class="wrap kmpb-ov">
			<h1 class="kmpb-ov__title"><img src="<?php echo esc_url( KMPB_URL . 'assets/img/logo.svg' ); ?>" alt="" width="36" height="36"> <?php esc_html_e( 'Kirollos Magdy', 'kirollos-magdy-portfolio-builder' ); ?></h1>
			<p class="kmpb-ov__intro">
				<?php
				printf(
					/* translators: 1: plugin version 2: number of projects 3: number of articles */
					esc_html__( 'Portfolio Builder %1$s. %2$d published projects, %3$d published articles. Everything for the portfolio lives in this menu.', 'kirollos-magdy-portfolio-builder' ),
					esc_html( KMPB_VERSION ),
					(int) $projects,
					(int) $articles
				);
				?>
			</p>

			<div class="kmpb-ov__grid">
				<?php
				$this->card(
					__( 'Projects', 'kirollos-magdy-portfolio-builder' ),
					__( 'Add and edit portfolio projects: title, excerpt, content, logo, screenshots, live website link and completion month.', 'kirollos-magdy-portfolio-builder' ),
					array(
						__( 'All Projects', 'kirollos-magdy-portfolio-builder' )    => post_type_exists( $pt ) ? admin_url( 'edit.php?post_type=' . $pt ) : '',
						__( 'Add New Project', 'kirollos-magdy-portfolio-builder' ) => post_type_exists( $pt ) ? admin_url( 'post-new.php?post_type=' . $pt ) : '',
					)
				);

				$tax_links = array();
				foreach ( self::TAXONOMIES as $taxonomy => $label ) {
					if ( taxonomy_exists( $taxonomy ) ) {
						$tax_links[ $label ] = admin_url( 'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=' . $pt );
					}
				}
				$this->card( __( 'Categories, Services and Industries', 'kirollos-magdy-portfolio-builder' ), __( 'The terms used for filters on the portfolio page and for the tags shown on each project card.', 'kirollos-magdy-portfolio-builder' ), $tax_links );

				$this->card(
					__( 'Case-Study Pages', 'kirollos-magdy-portfolio-builder' ),
					__( 'Build or rebuild the Elementor case-study page of each project from its own data, or undo a generated page.', 'kirollos-magdy-portfolio-builder' ),
					array( __( 'Open Case-Study Pages', 'kirollos-magdy-portfolio-builder' ) => $can_pages ? admin_url( 'admin.php?page=kmpp' ) : '' )
				);

				if ( $importer ) {
					$this->card(
						__( 'Portfolio Import', 'kirollos-magdy-portfolio-builder' ),
						__( 'Create all projects in one click, import screenshots from a zip, set completion dates and tidy unused terms.', 'kirollos-magdy-portfolio-builder' ),
						array( __( 'Open Portfolio Import', 'kirollos-magdy-portfolio-builder' ) => current_user_can( 'manage_options' ) ? admin_url( 'admin.php?page=kmpfi-portfolio-import' ) : '' )
					);
				}
				if ( $blog_imp ) {
					$this->card(
						__( 'Blog Importer', 'kirollos-magdy-portfolio-builder' ),
						__( 'Publish or update the bundled SEO articles with covers, categories, tags, dates and Yoast SEO fields.', 'kirollos-magdy-portfolio-builder' ),
						array(
							__( 'Open Blog Importer', 'kirollos-magdy-portfolio-builder' ) => current_user_can( 'publish_posts' ) ? admin_url( 'admin.php?page=kmbi-blog-importer' ) : '',
							__( 'All Articles', 'kirollos-magdy-portfolio-builder' )       => admin_url( 'edit.php' ),
						)
					);
				}
				if ( ! $importer ) {
					echo '<div class="kmpb-ov__card kmpb-ov__card--muted"><h2>' . esc_html__( 'Importers', 'kirollos-magdy-portfolio-builder' ) . '</h2><p>' . esc_html__( 'Activate Kirollos Magdy Portfolio Importer to add Portfolio Import and Blog Importer to this menu.', 'kirollos-magdy-portfolio-builder' ) . '</p></div>';
				}
				?>

				<div class="kmpb-ov__card kmpb-ov__card--wide">
					<h2><?php esc_html_e( 'Inside Elementor', 'kirollos-magdy-portfolio-builder' ); ?></h2>
					<ul>
						<li><strong><?php esc_html_e( 'Widgets:', 'kirollos-magdy-portfolio-builder' ); ?></strong> <?php esc_html_e( 'search the Elementor panel for the "Kirollos Magdy" and "Portfolio Builder" categories (Latest Projects, Portfolio Archive, Latest Articles and more).', 'kirollos-magdy-portfolio-builder' ); ?></li>
						<li><strong><?php esc_html_e( 'Effects:', 'kirollos-magdy-portfolio-builder' ); ?></strong> <?php esc_html_e( 'select any element, open the Advanced tab and look for Mouse Effects, Floating Effects, Mouse Cursor and Cursor Trail.', 'kirollos-magdy-portfolio-builder' ); ?></li>
						<li><strong><?php esc_html_e( 'Icons:', 'kirollos-magdy-portfolio-builder' ); ?></strong> <?php esc_html_e( 'in any icon picker, choose the "Kirollos Portfolio" library.', 'kirollos-magdy-portfolio-builder' ); ?></li>
						<li><strong><?php esc_html_e( 'Blog article template:', 'kirollos-magdy-portfolio-builder' ); ?></strong> <?php esc_html_e( 'applied automatically to every blog post written in the normal editor.', 'kirollos-magdy-portfolio-builder' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
		<style>
			.kmpb-ov__title{display:flex;align-items:center;gap:12px}
			.kmpb-ov__title img{flex:0 0 auto}
			.kmpb-ov__intro{font-size:14px;color:#50575e;max-width:760px}
			.kmpb-ov__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-top:20px;max-width:1200px}
			.kmpb-ov__card{display:flex;flex-direction:column;padding:18px 20px;background:#fff;border:1px solid #dcdcde;border-left:4px solid #9d00ff;border-radius:8px}
			.kmpb-ov__card h2{margin:0 0 6px;font-size:16px}
			.kmpb-ov__card p{margin:0 0 14px;color:#50575e}
			.kmpb-ov__card ul{margin:0;padding-left:18px;list-style:disc;color:#50575e}
			.kmpb-ov__card li{margin-bottom:6px}
			.kmpb-ov__links{margin-top:auto!important;margin-bottom:0!important;display:flex;flex-wrap:wrap;gap:6px}
			.kmpb-ov__card--wide{grid-column:1 / -1}
			.kmpb-ov__card--muted{border-left-color:#dcdcde}
		</style>
		<?php
	}
}
