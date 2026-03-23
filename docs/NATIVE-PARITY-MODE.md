# Native Parity Mode

## Goal
Keep the strongest visual/layout parity while staying fully editable in Elementor at the widget level.

## How To Run
1. Open `Tools > CSA Site Tools` in WordPress Admin.
2. Click `Run Native Parity Seed`.
3. Keep `Overwrite existing page content` enabled when doing a full reset.

## What This Mode Does
- Seeds core pages with native Elementor widgets (`heading`, `text-editor`, `image`, `button`, containers).
- Avoids HTML-widget-only sections so owners can edit content without code.
- Applies parity helper CSS/JS on the frontend to preserve layout behavior.
- Automatically suppresses parity helper JS inside Elementor editor contexts to protect drag-and-drop editing.

## Tradeoff
Exact clone-level behavior can still vary because Elementor and third-party source implementations differ. This mode is the best balance for real-world no-code owner editing plus close visual parity.

## Session Log
- `feat(structure): move About into Home and route About nav to on-page anchor`
  - Removed separate About page from small-business blueprint flow and archived `/company/`.
  - Added Home About sections with anchor target `#about-home`.
  - Updated header/footer menu defaults and one-time nav migration to point About links to `/#about-home`.
- `feat(life-at-chestnut): add age-group tabs above gallery`
  - Added interactive age tabs (Infants, Toddlers, Early Preschool, Preschool & Pre-K) above gallery images.
- `refactor(contact): simplify to one unified content block`
  - Replaced image-based two-card contact layout with one text-focused section.
  - Removed privacy-policy reference from contact flow and retired `/privacy-policy/`.
- `feat(home+life): merge About content and activate Life tabs with image updates`
  - Home: removed Director Message section and removed About image component.
  - Home: merged “Our Approach” content directly into the “A Small School with a Big Heart” block.
  - Life at Chestnut: tab clicks now update a visible featured image using random gallery items.
  - Bumped one-time structure refresh flag to `1.0.3` so updated seeded content is applied.
- `fix(interactions+ui): wire Learning by Age Group tabs and polish About approach card`
  - Added frontend JS binding for `.life-age-tab` to switch active panel reliably (index fallback when data attrs are stripped).
  - Added featured-image random swap on each tab click using gallery images.
  - Styled `#about-home .card-copy` for cleaner hierarchy, softer container finish, and clearer approach bullets.
  - Bumped child-theme version to `1.0.62` for cache-safe asset refresh.
- `fix(anchor+responsive): correct About scroll target and complete responsive hardening pass`
  - Added deterministic anchor scrolling for `#about-home` with sticky-header offset compensation.
  - Added `scroll-margin-top` on `#about-home` to prevent hash landing mid-section.
  - Responsive polish across header/nav, hero text fit, life-age tabs, FAQ spacing, contact typography, and footer layout.
  - Bumped child-theme version to `1.0.63` for CSS/JS cache refresh.
- `fix(mobile-header): remove top gap and card styling from mobile navbar`
  - Mobile header now sits flush to top (`top: 0`) including admin-bar state on mobile.
  - Removed rounded card treatment from burger control and open mobile menu panel.
  - Flattened mobile header row spacing so logo + burger are not wrapped in a card-like container.
  - Bumped child-theme version to `1.0.64` for immediate stylesheet refresh.
- `fix(hero+copy): normalize landing responsiveness and update age references`
  - Added final hero size/position normalization for mobile and desktop so cover + headline scale consistently.
  - Tightened bottom-left headline sizing clamps to prevent overflow/cropping at small viewports.
  - Updated age wording from `5/6` to `4/5` in active content sources and replacement maps.
  - Bumped child-theme version to `1.0.65` for cache-safe CSS refresh.
- `fix(landing+copy): stabilize cover responsiveness and enforce 4/5 age wording`
  - Added a final landing-section override block to keep hero height viewport-fit with sticky header compensation across desktop/tablet/mobile.
  - Normalized hero copy anchoring and headline clamps to prevent oversized/overflow text during responsive transitions.
  - Added explicit `5/6 -> 4/5` replacements (literal + regex) in real-data replacement pipeline and bumped text migration version to `1.0.5`.
  - Bumped child-theme version to `1.0.66` for cache-safe stylesheet refresh.
- `fix(mobile-hero): remove top gap and stop tagline clipping`
  - Added mobile-only hero hardening so `.hero-overlay` and `.background-image` are always absolutely positioned to fill the hero bounds.
  - Forced mobile hero container to `padding: 0` and viewport-fit height with sticky-header compensation.
  - Reduced mobile tagline clamp to preserve full phrase visibility at small viewport heights.
  - Bumped child-theme version to `1.0.67` for cache-safe stylesheet refresh.
- `fix(mobile-hero-pass2): eliminate residual white band and tighten headline fit`
  - Forced direct hero text-editor widgets to absolute fill on mobile to prevent default Elementor widget spacing from creating a top gap.
  - Zeroed internal widget-container margins/padding/line-height for those hero helper widgets.
  - Further reduced mobile headline clamp to keep full “Rooted in Care. Growing Together.” visible on shorter viewports.
  - Bumped child-theme version to `1.0.68` for cache-safe stylesheet refresh.
- `fix(mobile-offset): align sticky header compensation with actual mobile navbar height`
  - Reduced `--csa-sticky-header-height-mobile` from `95px` to `76px` to match the real mobile header row.
  - Added mobile flush overrides so `main` and first hero section cannot introduce extra top spacing.
  - Kept sticky behavior and no-code Elementor structure unchanged; CSS-only adjustment.
  - Bumped child-theme version to `1.0.69`.
- `fix(migration): enforce CSA active theme and remove legacy child themes`
  - Added MU plugin `csa-theme-guard` so migrated installs always switch to `hello-elementor-csa-site` when available.
  - Renamed active child theme label to `Chestnut Square Academy` in theme metadata.
  - Removed deprecated `hello-elementor-csa` theme files and local `hello-elementor-kiddie-mock` folder to prevent accidental fallback.
  - Bumped child-theme version to `1.0.70`.
- `fix(migration-runtime): force CSA theme options and replace stale legacy content paths`
  - Upgraded `csa-theme-guard` to override `template` and `stylesheet` at runtime via `pre_option_*` filters (safer than one-time switching).
  - Added persistence step to write corrected theme options once.
  - Added content-level replacements for old theme asset paths and legacy `Kiddie Academy`/`5/6` text so imported DB content cannot regress visuals or wording.
- `fix(regression): restore post-migration visual parity after fallback drift`
  - Root cause identified: runtime drift temporarily dropped `csa-site` body marker on some requests while stale mobile shell/card styles and oversized hero clamps remained, producing a barebones middle + oversized tagline.
  - Added MU-plugin body-class guard to always provide `csa-site` and `page-home` markers on frontend.
  - Restored mobile sticky/header baseline (`--csa-sticky-header-height-mobile: 76px`) and removed mobile navbar card shell styling (no rounded burger card, no menu panel rounding/shadow).
  - Rebalanced hero headline clamp variables to non-overflow values and bumped child-theme version to `1.0.71`.
