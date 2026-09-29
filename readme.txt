=== Kirollos Magdy Portfolio Builder ===
Contributors: Kirollos Magdy
Author: Kirollos Magdy - WordPress Developer
Author URI: https://wa.me/+201016324429
Tags: elementor, portfolio, mouse effects, cursor, case study
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 3.9.5
License: Proprietary. All rights reserved.

Portfolio toolkit for Elementor: a portfolio post type, 14 widgets, interaction effects on every
element, an icon library, generated case-study pages and a blog article template.

== Description ==

Everything a developer portfolio needs, in one plugin. Works with the free version of Elementor.

= Portfolio content =

* **Portfolios** post type with Project Categories, Services and Industries taxonomies. It only
  registers when no theme or plugin has already claimed the name.
* Project fields on the edit screen: live website URL, completion month and a gallery of up to
  four screenshots, plus a secondary featured image.

= Elementor widgets =

All widgets load their CSS and JavaScript only on pages that use them.

* Animated Heading, Latest Projects, Portfolio Archive, Portfolio Logo Wall.
* Portfolio Slider, Portfolio Horizontal Slider, Portfolio Pin Spacer, Portfolio Filter List,
  Works Slider, Works Tabs Mini.
* Post Title, Post Image, Dynamic Post Meta.
* Latest Articles: blog grid with category navigation and Load more.

= Interaction panels =

Added to the **Advanced** tab of every Elementor section, column, container and widget.

* **Mouse Effects**: Mouse Track (Parallax), 3D Tilt with optional glare, Magnetic Pull.
* **Floating Effects**: continuous float, rotate and scale, pure CSS, previews live in the editor.
* **Mouse Cursor**: replace the pointer over an element with a dot or circle, text or an image.
* **Cursor Trail**: Particles, Sparkles, Glow Orb or Comet Tail drawn on a canvas behind the
  element's content.

Touch devices and visitors who prefer reduced motion are respected.

= Case-study pages =

**Kirollos Magdy > Case-Study Pages** builds an Elementor case-study page for each project from its
title, excerpt, content, logo, screenshots, website link, date and terms. Pages stay fully
editable in Elementor. A hand-made layout is backed up before it is replaced, and **Undo**
restores it. Empty Yoast meta descriptions and image alt text are filled in, never overwritten.

Shortcode: `[kmpp_related post="123" count="3"]` shows related projects as cards.

= Blog article template =

Single blog posts get a dark hero, cover image, reading column with a sticky table of contents,
reading progress bar, share buttons, author box, related articles and a call to action. Posts
built with Elementor are left alone.

= Icon library =

Elementor > Icon Library > **Kirollos Portfolio**, with the `kpio-` class prefix.

* Build icons: WordPress, Custom Plugin, Custom Theme, WooCommerce, Shopify, Performance.
* Platform icons: Mostaql, Khamsat, Freelancer, Upwork, Fiverr, Behance.

Freelancer, Upwork, Fiverr and Behance come from Simple Icons (CC0). Brand marks remain the
property of their owners.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or upload the zip under Plugins > Add New.
2. Activate it. Elementor 3.5 or newer is required for the widgets and panels; the portfolio post
   type, case-study builder and blog template work without it.
3. If you used the separate Mouse Effects, Portfolio Icons, KM Portfolio Pages, KM Blog Template
   or Latest Articles plugins, deactivate and delete them.

== Changelog ==

= 3.9.5 =
* Blog article template: "More articles" covers show in full at the cover ratio (1.91:1)
  instead of being cropped on the sides.
* Blog article template: the author is shown by First Name and Last Name from the profile,
  falling back to the display name, so the username never appears.

= 3.9.4 =
* New "Kirollos Magdy" admin menu, with the Kirollos Magdy logo, that holds everything in one
  place: an Overview page, Projects, Add New Project, Categories, Services, Industries,
  Case-Study Pages, and, when Kirollos Magdy Portfolio Importer is active, Portfolio Import and
  Blog Importer. The Overview also explains where the widgets, effects and icons live in
  Elementor. The separate Portfolios menu is gone; its screens are inside this menu.
* Latest Articles: cards now match the portfolio cards (header row with the category pill and
  reading time, cover image, dark title, muted excerpt, Learn more button and date). A
  one-time update clears the saved title, category and date colours so the new styles apply.
* Latest Articles: listed under the "Kirollos Magdy" widget category in Elementor.

= 3.9.3 =
* Latest Articles: Montserrat font. A one-time update switches fonts saved in the widget's
  Typography settings to Montserrat.
* Latest Articles: heading and navigation use the global Primary colour (card titles stay
  on Secondary).
* Latest Articles: the Uncategorized category never appears in the navigation or the
  category picker.
* Latest Articles: when no categories are picked, every category is shown. A saved list
  that was the old automatic default (it included Uncategorized and left out WordPress
  Development) is cleared by the one-time update.
* Latest Articles: category badges use the global Secondary colour.
* Latest Articles: much smaller category badge, and a Learn more button on every card,
  with its own show/hide switch and text setting.

= 3.9.2 =
* Latest Articles: card titles use the global Secondary colour. A one-time update points
  the saved title colour at Secondary; new widgets use it by default.
* Latest Articles: smaller category badge (10px text, tighter padding).

= 3.9.1 =
* Latest Articles: redesigned to common web design standards. Covers fill the whole image
  area at the blog cover ratio (1.91:1) with no placeholder band; the Image Height and
  Thumbnail Image Fit settings are replaced by Image Aspect Ratio. Compact pill filters,
  the section heading stays on one line, card titles are 18px, cards share the same height
  with the date pinned to the bottom, and keyboard focus is visible.
* Latest Articles now uses Arial. A one-time update switches any font saved in the widget's
  Typography settings to Arial; colours and other saved settings are kept.
* Case-study pages: related project cards show one screenshot instead of up to four.

= 3.9.0 (merged build) =
* Now includes three plugins that used to be separate. Deactivate and delete them after
  updating.
  - KM Portfolio Pages 2.2.3: Portfolios > Elementor Pages case-study builder, the
    [kmpp_related] shortcode and the lightbox arrow fix. If the separate plugin is still
    active, this plugin skips its own copy and shows a notice instead of loading twice.
  - KM Blog Template 1.0.0: the single blog post template. Same safeguard as above.
  - Latest Articles widget, now named kmpb-latest-articles. Pages that use the widget under
    its previous name are migrated automatically once, and their Elementor CSS is rebuilt.
* Portfolio widgets: removed the hidden legacy widget aliases, renamed internal CSS classes
  and data attributes to the kmpw- prefix, and fixed the front-end scripts, which were
  listening for the old widget names and never initialised the sliders, filter list, pin
  spacer, works tabs or post image effects.

= 2.6.2 =
* Mostaql and Khamsat are now the real Arabic wordmarks rather than letter marks, matching how
  the Fiverr wordmark renders. Sources kept in assets/icons for future rebuilds.

= 2.6.1 =
* The Khamsat icon is now the real brand mark, traced from the supplied logo, replacing the
  placeholder letter mark. Mostaql is still a letter mark pending artwork.

= 2.6.0 =
* New: Projects Grid widget - lists projects as a responsive grid with an optional category
  filter bar, image/title/excerpt/meta/services toggles and full style controls.
* New: six icons in the Kirollos Portfolio library - Mostaql, Khamsat, Freelancer, Upwork,
  Fiverr and Behance. Compiled into a second font file so the original six are untouched.
* New: transparent header is applied to every project again, writing the real theme meta.
* The demo design now runs light with a dark hero, and empty case-study blocks hide their
  own heading instead of leaving it stranded.

= 2.5.1 =
* Fixed the Highlighted shapes. All eight were authored against one generic overlay stretched
  over the word's full line box, so underlines cut through the text, the circle was offset and
  clipped, and the zigzag and curly shapes rendered as plain slashes. Each shape now declares
  a placement - wrap, under or through - with geometry authored for that box.

= 2.5.0 =
* New: Animated Heading widget, a free-Elementor equivalent of Pro's Animated Headline.
  Highlighted mode draws one of eight SVG shapes around a word as it scrolls into view;
  Rotating mode cycles words with Typing, Fade, Slide Up/Down, Drop In, Flip or Clip.
  Full Content and Style tabs, and a Kirollos Magdy category in the widget panel.

= 2.4.0 =
* Reordered the demo design: gallery now sits directly after the featured image.
* The headline numbers moved into a full-width purple band, matching the stats strip on the
  home page, and the case study section now runs on white.
* New kmpb-light and kmpb-accent-band CSS classes. Add either to any Elementor section under
  Advanced > CSS Classes to flip everything inside it between the light, dark and accent
  palettes, shortcode output included.

= 2.3.2 =
* Added: "Ignore Pointer Over" to Cursor Trail. Takes a CSS selector for anything layered on top
  of the element, and the trail fades out while the pointer is over it. The trail decides whether
  the pointer is inside it by geometry alone, so an overlapping element such as a floating header
  kept the trail running underneath and drowned out that element's own hover effect. An invalid
  selector is ignored rather than left to break the other effects.

= 2.3.1 =
* Fixed: a white band appeared under the header on Elementor-built project pages. The 150px top
  padding added in 2.1.0 sat on the transparent wrapper, so it showed the theme body background
  rather than the design. Builder mode now has no wrapper padding; header clearance comes from
  the hero section's own top padding, which is on the dark background and editable in Elementor.

= 2.3.0 =
* Removed the transparent header feature entirely. The theme header is no longer touched in
  any way, so it can be managed directly in the theme.

= 2.2.1 =
* Fixed: the transparent header showed as Enable in the editor but did not apply. The value was
  being faked through the get_post_metadata filter; the theme does not read it that way. The
  real value is now written to the post, exactly as setting the dropdown by hand does.
* Only the active theme's meta key is written, and an explicit Disable is never overwritten.

= 2.2.0 =
* New: the theme transparent header is forced on for every project by default. Kadence and
  Astra are supported out of the box; other themes can be added with the
  kmpb_transparent_header_meta filter. Toggle in Portfolio Builder > Project Template.

= 2.1.0 =
* New: top-level "Portfolio Builder" admin menu with the K logo, replacing the Settings entry.
* Fixed: the author name appeared twice on the Plugins screen, because Plugin URI and Author
  URI are the same link and the matcher hit both rows.
* New: 150px top padding on the single project page, adjustable via --kmpb-top-pad.
* Removed the Role field.
* Metrics: clearer field labels (Big Number vs Caption) and long values now step down in size
  instead of overflowing the card.
* Contact button now uses the same styling as the Visit Live Site button, via [kmpb_contact],
  with the destination set in Portfolio Builder > Project Template.
* New: per-image gallery labels, entered under each thumbnail, plus a built-in lightbox with
  keyboard and swipe-free navigation.

= 2.0.0 =
* Category now renders as plain inline text by default. [kmpb_terms style="pills"] restores pills.
* Buttons can centre themselves: [kmpb_link align="center"], since Elementor Shortcode widgets
  have no alignment control.
* Gallery rebuilt: adjustable columns, gap, ratio, radius, hover style (zoom/lift/tilt/none)
  and a staggered reveal-on-scroll animation.
* Demo design no longer uses inline styles. All sizing, weight, spacing and colour are real
  Elementor control values, editable in the Style tab.
* New: Contact Me call to action using Elementor native Button widget.
* New: rebuild the selected template from the latest design, for templates made before newer
  fields existed.

= 1.9.0 =
* New: a native Project Details meta box, no ACF required. Client, industry, year, role,
  duration, live URL, services, tech stack, challenge, solution, results, three headline
  metrics, a client quote, and a gallery picker.
* New: [kmpb_metrics] and [kmpb_testimonial] shortcodes, both added to the demo design.
* Shortcodes now read plugin meta and ACF fields, preferring ACF only where it owns the field.

= 1.8.0 =
* Removed: the ACF field group registration added in 1.5.0. The plugin no longer creates or
  depends on any ACF fields.
* New: the plugin registers its own project-category taxonomy, so a Categories box appears on
  the project editor with no ACF involved. Show the terms with [kmpb_terms].

= 1.7.0 =
* New: [kmpb_terms] shortcode for taxonomy terms. Works with a registered taxonomy (ACF >
  Taxonomies) or an ACF Taxonomy field, and auto-detects the taxonomy when given no arguments.

= 1.6.0 =
* Redesigned the demo layout: centred hero with the title, intro, credit line and primary
  action, a full-width featured image, then the story on the left with a self-contained
  details card on the right, a centred gallery, and the pager.
* The details card is an inner section so it hugs its content instead of stretching to the
  full height of the row, and the action button is repeated inside it.

= 1.5.0 =
* New: the plugin registers the ACF "Project Details" field group itself, so the field names
  always match what the design expects. Can be turned off in Settings > Project Template.
* ACF Gallery is Pro only, so on free ACF three image fields are provided instead and the
  generated design adapts to match.

= 1.4.0 =
* New: the template creator now generates a complete, ready-made Elementor design (hero,
  featured image, content sections, facts sidebar, gallery, pager) already wired to ACF via
  shortcodes - fully editable in Elementor instead of starting from a blank canvas.

= 1.3.0 =
* New: a "Single Project" entry in the Edit with Elementor toolbar menu when viewing a project,
  opening the template that renders it - the same flow Elementor Pro gives for Single Product.
* New: one-click template creation in Settings > Project Template.

= 1.2.0 =
* New: design the Single Project page in Elementor free. Pick an Elementor template in
  Settings > Project Template and it renders for every project.
* New: ACF shortcodes ([kmpb_field], [kmpb_gallery], [kmpb_link], [kmpb_facts] and more) as a
  free-tier stand-in for Elementor Pro Dynamic Tags, with editor placeholders while designing.

= 1.1.0 =
* New: Single Project template for the project post type, rendered in PHP so it works without
  Elementor Pro. Reads ACF fields automatically and sorts them into buttons, a facts card, tag
  pills, content sections and a gallery.

= 1.0.0 =
* First release of the merged plugin: Mouse Effects, Floating Effects, Mouse Cursor and Cursor
  Trail panels, plus the Kirollos Portfolio icon library, under one plugin.
* Panels in the Advanced tab are branded with the Kirollos Magdy logo.
* The author name on the Plugins screen links to WhatsApp and opens in a new tab.
