<?php
/**
 * One portfolio card.
 *
 * Both the Portfolio Archive and the Latest Projects strip draw this, so a card
 * on the homepage and a card on the portfolio page are the same object rather
 * than two copies that drift apart the next time one is changed.
 *
 * It is called inside the loop and reads the current post.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KMPB_Portfolio_Card {

	/**
	 * @param array $settings The widget settings driving the card.
	 */
	public static function render( $settings ) {

		$post_id = get_the_ID();
		$link    = get_permalink( $post_id );
		$live    = get_post_meta( $post_id, 'project_url', true );

		$industry_ids = wp_get_post_terms( $post_id, 'portfolio_industry', array( 'fields' => 'ids' ) );
		$service_ids  = wp_get_post_terms( $post_id, 'portfolio_service', array( 'fields' => 'ids' ) );
		$industry_ids = is_wp_error( $industry_ids ) ? array() : $industry_ids;
		$service_ids  = is_wp_error( $service_ids ) ? array() : $service_ids;

		$logo_id = class_exists( 'KMPB_Portfolio_Meta' ) ? KMPB_Portfolio_Meta::logo_id( $post_id ) : get_post_thumbnail_id( $post_id );

		// With the project pages switched off, the images and the name are just
		// content: they render as plain elements rather than dead links.
		$link_media = ! empty( $settings['link_media'] );
		$link_title = ! empty( $settings['link_title'] );
		$show_cta   = ! empty( $settings['show_cta'] );
		$live_cta   = ( ! empty( $settings['show_live_cta'] ) && $live );

		$media_open  = $link_media ? '<a class="kmpb-pa__logo" href="' . esc_url( $link ) . '">' : '<span class="kmpb-pa__logo">';
		$media_close = $link_media ? '</a>' : '</span>';
		$shot_open   = $link_media ? '<a class="kmpb-pa__shot" href="' . esc_url( $link ) . '">' : '<span class="kmpb-pa__shot">';

		$shot_count = isset( $settings['gallery_count'] ) ? max( 0, min( 4, (int) $settings['gallery_count'] ) ) : 4;
		$shots      = array();

		if ( $shot_count && class_exists( 'KMPB_Portfolio_Meta' ) ) {
			$shots = array_slice( KMPB_Portfolio_Meta::gallery_ids( $post_id ), 0, $shot_count );
		}

		$work_date = '';

		if ( ! empty( $settings['show_date'] ) && class_exists( 'KMPB_Portfolio_Meta' ) ) {
			$work_date = KMPB_Portfolio_Meta::work_date( $post_id, isset( $settings['date_format'] ) ? $settings['date_format'] : 'F Y' );
		}
		?>
		<article class="kmpb-pa__card"
			data-industry="<?php echo esc_attr( implode( ' ', array_map( 'strval', $industry_ids ) ) ); ?>"
			data-service="<?php echo esc_attr( implode( ' ', array_map( 'strval', $service_ids ) ) ); ?>">

			<header class="kmpb-pa__head">
				<?php echo $media_open; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php
					if ( $logo_id ) {
						echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'alt' => get_the_title( $post_id ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				<?php echo $media_close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<?php if ( ! empty( $settings['show_head_link'] ) && $live ) : ?>
					<?php
					$chip = isset( $settings['head_link_text'] ) ? trim( (string) $settings['head_link_text'] ) : '';

					if ( '' === $chip ) {
						$chip = preg_replace( '#^https?://(www\.)?#i', '', untrailingslashit( $live ) );
					}
					?>
					<a class="kmpb-pa__visit" href="<?php echo esc_url( $live ); ?>" target="_blank" rel="noopener noreferrer">
						<span class="kmpb-pa__visit-text"><?php echo esc_html( $chip ); ?></span>
						<svg class="kmpb-pa__visit-icon" viewBox="0 0 24 24" width="13" height="13" fill="none"
							stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
							aria-hidden="true" focusable="false">
							<path d="M7 17 17 7" />
							<path d="M8 7h9v9" />
						</svg>
					</a>
				<?php endif; ?>
			</header>

			<?php if ( $shots ) : ?>
				<?php
				$full = ( ! isset( $settings['shot_fit'] ) || 'cover' !== $settings['shot_fit'] );

				// Two across at most, however many photos the card carries.
				$shot_cols = min( 2, count( $shots ) );
				$shot_size = $full ? 'large' : 'medium_large';
				?>
				<div class="kmpb-pa__shots<?php echo $full ? ' kmpb-pa__shots--full' : ''; ?>"
					style="--kmpb-pa-shot-cols:<?php echo esc_attr( (string) $shot_cols ); ?>">
					<?php foreach ( $shots as $shot_id ) : ?>
						<?php echo $shot_open; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php
							echo wp_get_attachment_image( $shot_id, $shot_size, false, array( 'alt' => get_the_title( $post_id ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						<?php echo $media_close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="kmpb-pa__body">
				<h3 class="kmpb-pa__name">
					<?php if ( $link_title ) : ?>
						<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
					<?php else : ?>
						<?php echo esc_html( get_the_title() ); ?>
					<?php endif; ?>
				</h3>

				<?php if ( ! empty( $settings['show_terms'] ) ) : ?>
					<?php
					$labels = array();
					foreach ( array( 'portfolio_industry', 'portfolio_service' ) as $tax ) {
						$names = wp_get_post_terms( $post_id, $tax, array( 'fields' => 'names' ) );
						if ( ! is_wp_error( $names ) && $names ) {
							$labels = array_merge( $labels, $names );
						}
					}
					?>
					<?php if ( $labels ) : ?>
						<p class="kmpb-pa__terms"><?php echo esc_html( implode( ' • ', $labels ) ); ?></p>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( ! empty( $settings['show_excerpt'] ) && has_excerpt( $post_id ) ) : ?>
					<p class="kmpb-pa__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 18, '…' ) ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $settings['show_link'] ) && $live ) : ?>
					<a class="kmpb-pa__url" href="<?php echo esc_url( $live ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( preg_replace( '#^https?://(www\.)?#i', '', untrailingslashit( $live ) ) ); ?>
					</a>
				<?php endif; ?>

				<div class="kmpb-pa__foot">
					<?php if ( $show_cta ) : ?>
						<a class="kmpb-pa__cta" href="<?php echo esc_url( $link ); ?>">
							<?php echo esc_html( $settings['cta_text'] ); ?>
						</a>
					<?php endif; ?>

					<?php if ( $live_cta ) : ?>
						<a class="kmpb-pa__cta kmpb-pa__cta--live<?php echo ( 'outline' === $settings['live_cta_style'] ) ? ' kmpb-pa__cta--outline' : ''; ?>"
							href="<?php echo esc_url( $live ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $settings['live_cta_text'] ); ?>
							<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor"
								stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
								<path d="M7 17 17 7" />
								<path d="M8 7h9v9" />
							</svg>
						</a>
					<?php endif; ?>

					<?php if ( $work_date ) : ?>
						<time class="kmpb-pa__date" datetime="<?php echo esc_attr( (string) get_post_meta( $post_id, 'work_date', true ) ); ?>">
							<?php echo esc_html( $work_date ); ?>
						</time>
					<?php endif; ?>
				</div>
			</div>
		</article>
		<?php
	}

}
