<?php
/**
 * Single blog post.
 *
 * @package KM_Blog_Template
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$kmbt_post     = get_post();
	$kmbt_category = kmbt_primary_category( $kmbt_post->ID );
	$kmbt_author   = (int) $kmbt_post->post_author;
	$kmbt_has_img  = has_post_thumbnail();
	$kmbt_updated  = get_the_modified_time( 'U' ) - get_the_time( 'U' ) > DAY_IN_SECONDS;

	list( $kmbt_content, $kmbt_toc ) = kmbt_toc( apply_filters( 'the_content', get_the_content() ) );
	$kmbt_show_toc                   = count( $kmbt_toc ) >= 2;
	?>
	<div class="kmbt-progress" aria-hidden="true"><span class="kmbt-progress__bar"></span></div>

	<main id="main" class="kmbt<?php echo $kmbt_has_img ? ' kmbt--has-cover' : ''; ?>">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'kmbt-post' ); ?>>

			<header class="kmbt-hero">
				<div class="kmbt-wrap kmbt-hero__inner">
					<nav class="kmbt-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'km-blog-template' ); ?>">
						<ol>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'km-blog-template' ); ?></a></li>
							<li><a href="<?php echo esc_url( kmbt_blog_url() ); ?>"><?php esc_html_e( 'Blog', 'km-blog-template' ); ?></a></li>
							<?php if ( $kmbt_category ) : ?>
								<li><a href="<?php echo esc_url( get_category_link( $kmbt_category ) ); ?>"><?php echo esc_html( $kmbt_category->name ); ?></a></li>
							<?php endif; ?>
						</ol>
					</nav>

					<?php if ( $kmbt_category ) : ?>
						<a class="kmbt-hero__cat" href="<?php echo esc_url( get_category_link( $kmbt_category ) ); ?>"><?php echo esc_html( $kmbt_category->name ); ?></a>
					<?php endif; ?>

					<h1 class="kmbt-hero__title"><?php the_title(); ?></h1>

					<?php if ( has_excerpt() ) : ?>
						<p class="kmbt-hero__dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>

					<div class="kmbt-meta">
						<?php echo get_avatar( $kmbt_author, 44, '', '', array( 'class' => 'kmbt-meta__avatar' ) ); ?>
						<div class="kmbt-meta__text">
							<span class="kmbt-meta__author">
								<?php esc_html_e( 'By', 'km-blog-template' ); ?>
								<a href="<?php echo esc_url( get_author_posts_url( $kmbt_author ) ); ?>" rel="author"><?php echo esc_html( kmbt_author_name( $kmbt_author ) ); ?></a>
							</span>
							<span class="kmbt-meta__row">
								<?php if ( $kmbt_updated ) : ?>
									<?php esc_html_e( 'Updated', 'km-blog-template' ); ?>
									<time datetime="<?php echo esc_attr( get_the_modified_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_modified_date() ); ?></time>
								<?php else : ?>
									<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								<?php endif; ?>
								<span class="kmbt-dot" aria-hidden="true">·</span>
								<?php
								/* translators: %d: minutes */
								echo esc_html( sprintf( _n( '%d min read', '%d min read', kmbt_reading_minutes( $kmbt_post ), 'km-blog-template' ), kmbt_reading_minutes( $kmbt_post ) ) );
								?>
							</span>
						</div>
					</div>
				</div>
			</header>

			<?php if ( $kmbt_has_img ) : ?>
				<figure class="kmbt-cover kmbt-wrap">
					<?php
					the_post_thumbnail(
						'full',
						array(
							'class'         => 'kmbt-cover__img',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'decoding'      => 'async',
							'sizes'         => '(max-width: 1180px) calc(100vw - 32px), 1140px',
						)
					);
					$kmbt_caption = get_the_post_thumbnail_caption();
					if ( $kmbt_caption ) :
						?>
						<figcaption><?php echo esc_html( $kmbt_caption ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>

			<div class="kmbt-wrap kmbt-layout<?php echo $kmbt_show_toc ? '' : ' kmbt-layout--single'; ?>">
				<div class="kmbt-main">
					<?php if ( $kmbt_show_toc ) : ?>
						<details class="kmbt-toc kmbt-toc--inline">
							<summary><?php esc_html_e( 'On this page', 'km-blog-template' ); ?></summary>
							<?php echo kmbt_toc_list( $kmbt_toc ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in kmbt_toc_list(). ?>
						</details>
					<?php endif; ?>

					<div class="kmbt-content entry-content">
						<?php echo $kmbt_content; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output. ?>
					</div>

					<?php
					wp_link_pages(
						array(
							'before' => '<nav class="kmbt-pages">',
							'after'  => '</nav>',
						)
					);
					?>

					<footer class="kmbt-foot">
						<?php
						$kmbt_tags = get_the_tags();
						if ( $kmbt_tags ) :
							?>
							<ul class="kmbt-tags" aria-label="<?php esc_attr_e( 'Tags', 'km-blog-template' ); ?>">
								<?php foreach ( $kmbt_tags as $kmbt_tag ) : ?>
									<li><a href="<?php echo esc_url( get_tag_link( $kmbt_tag ) ); ?>" rel="tag">#<?php echo esc_html( $kmbt_tag->name ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<div class="kmbt-foot__share">
							<span class="kmbt-label"><?php esc_html_e( 'Share this article', 'km-blog-template' ); ?></span>
							<?php echo kmbt_share( $kmbt_post ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in kmbt_share(). ?>
						</div>

						<section class="kmbt-author" aria-label="<?php esc_attr_e( 'About the author', 'km-blog-template' ); ?>">
							<?php echo get_avatar( $kmbt_author, 88, '', '', array( 'class' => 'kmbt-author__avatar' ) ); ?>
							<div class="kmbt-author__body">
								<span class="kmbt-label"><?php esc_html_e( 'Written by', 'km-blog-template' ); ?></span>
								<p class="kmbt-author__name"><?php echo esc_html( kmbt_author_name( $kmbt_author ) ); ?></p>
								<?php
								$kmbt_bio = get_the_author_meta( 'description', $kmbt_author );
								if ( $kmbt_bio ) :
									?>
									<p class="kmbt-author__bio"><?php echo esc_html( $kmbt_bio ); ?></p>
								<?php endif; ?>
								<p class="kmbt-author__links">
									<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'More about me', 'km-blog-template' ); ?></a>
									<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Work with me', 'km-blog-template' ); ?></a>
								</p>
							</div>
						</section>
					</footer>
				</div>

				<?php if ( $kmbt_show_toc ) : ?>
					<aside class="kmbt-aside" aria-label="<?php esc_attr_e( 'Article navigation', 'km-blog-template' ); ?>">
						<div class="kmbt-aside__sticky">
							<nav class="kmbt-toc kmbt-toc--side" aria-labelledby="kmbt-toc-title">
								<span id="kmbt-toc-title" class="kmbt-label"><?php esc_html_e( 'On this page', 'km-blog-template' ); ?></span>
								<?php echo kmbt_toc_list( $kmbt_toc ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in kmbt_toc_list(). ?>
							</nav>
							<div class="kmbt-aside__share">
								<span class="kmbt-label"><?php esc_html_e( 'Share', 'km-blog-template' ); ?></span>
								<?php echo kmbt_share( $kmbt_post, 'stack' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in kmbt_share(). ?>
							</div>
						</div>
					</aside>
				<?php endif; ?>
			</div>
		</article>

		<?php
		$kmbt_related = kmbt_related( $kmbt_post );
		if ( $kmbt_related ) :
			?>
			<section class="kmbt-related" aria-labelledby="kmbt-related-title">
				<div class="kmbt-wrap">
					<h2 id="kmbt-related-title" class="kmbt-section-title"><?php esc_html_e( 'More', 'km-blog-template' ); ?> <span><?php esc_html_e( 'articles', 'km-blog-template' ); ?></span></h2>
					<div class="kmbt-cards">
						<?php
						foreach ( $kmbt_related as $kmbt_item ) :
							$kmbt_item_cat = kmbt_primary_category( $kmbt_item->ID );
							$kmbt_link     = get_permalink( $kmbt_item );
							?>
							<article class="kmbt-card">
								<a class="kmbt-card__media" href="<?php echo esc_url( $kmbt_link ); ?>" tabindex="-1" aria-hidden="true">
									<?php
									if ( has_post_thumbnail( $kmbt_item ) ) {
										echo get_the_post_thumbnail( $kmbt_item, 'medium_large', array( 'alt' => '', 'loading' => 'lazy' ) );
									}
									?>
								</a>
								<div class="kmbt-card__body">
									<?php if ( $kmbt_item_cat ) : ?>
										<span class="kmbt-label"><?php echo esc_html( $kmbt_item_cat->name ); ?></span>
									<?php endif; ?>
									<h3 class="kmbt-card__title"><a href="<?php echo esc_url( $kmbt_link ); ?>"><?php echo esc_html( get_the_title( $kmbt_item ) ); ?></a></h3>
									<p class="kmbt-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $kmbt_item ), 22 ) ); ?></p>
									<span class="kmbt-card__meta">
										<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $kmbt_item ) ); ?>"><?php echo esc_html( get_the_date( '', $kmbt_item ) ); ?></time>
										<span class="kmbt-dot" aria-hidden="true">·</span>
										<?php
										/* translators: %d: minutes */
										echo esc_html( sprintf( _n( '%d min read', '%d min read', kmbt_reading_minutes( $kmbt_item ), 'km-blog-template' ), kmbt_reading_minutes( $kmbt_item ) ) );
										?>
									</span>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( comments_open() || get_comments_number() ) : ?>
			<section class="kmbt-comments">
				<div class="kmbt-wrap kmbt-wrap--narrow">
					<?php comments_template(); ?>
				</div>
			</section>
		<?php endif; ?>

		<section class="kmbt-cta">
			<div class="kmbt-wrap">
				<h2 class="kmbt-cta__title"><?php esc_html_e( 'Need a fast,', 'km-blog-template' ); ?> <span><?php esc_html_e( 'well-built', 'km-blog-template' ); ?></span> <?php esc_html_e( 'website?', 'km-blog-template' ); ?></h2>
				<p class="kmbt-cta__text"><?php esc_html_e( 'Tell me what you need and I will reply with a clear plan, timeline and quote.', 'km-blog-template' ); ?></p>
				<a class="kmbt-btn" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Start a project', 'km-blog-template' ); ?> <span aria-hidden="true">↗</span></a>
			</div>
		</section>
	</main>
	<?php
endwhile;

get_footer();
