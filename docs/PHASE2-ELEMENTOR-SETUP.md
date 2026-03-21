# Phase 2: Elementor Implementation Setup

Date: March 21, 2026

## What was implemented
- Elementor plugin downloaded into `wp-content/plugins/elementor`.
- Hello Elementor theme downloaded into `wp-content/themes/hello-elementor`.
- Child theme added: `wp-content/themes/hello-elementor-csa`.
- Launch plugin added: `wp-content/plugins/csa-launch-kit`.

## What the child theme includes
- Brand color tokens and typography (Fraunces + Nunito Sans).
- Mobile-first layout utility classes used in starter page content.
- CTA/button styles, cards, quick-facts grid, notes, map block, and form styling.

## What the launch plugin includes
- One-click setup tool at `Tools > CSA Launch Kit`.
- Preflight audit panel in `Tools > CSA Launch Kit` that counts unresolved `[VERIFY]` and `[DO NOT PUBLISH UNTIL CONFIRMED]` markers.
- One-click preflight report download (`.txt`) from `Tools > CSA Launch Kit` for owner/client handoff records.
- One-click indexing mode toggles in `Tools > CSA Launch Kit`:
- `Enable Staging Mode (Noindex)`
- `Enable Production Indexing`
- Technical readiness checks in preflight: active theme/plugin, homepage assignment, permalink mode, valid tour email, valid map URL, and staging indexing notice.
- Dashboard widget (`CSA Launch Status`) with quick links and live blocking-item count.
- Starter page generation for:
- Home
- About
- Programs
- Gallery
- FAQ
- Contact / Schedule a Tour
- Careers (optional)
- Parent Resources (optional)
- Privacy Policy
- Menu generation and assignment to Hello Elementor header/footer locations.
- Homepage assignment to the generated Home page.
- Built-in shortcode form: `[csa_schedule_tour_form]`.
- Tour request storage as private admin entries (`Tour Requests` post type).
- Email notifications for new form submissions.
- Form settings page at `Settings > CSA Tour Form`.
- Business profile settings page at `Settings > CSA Business Profile`.
- Citation copy block + JSON reference on Business Profile settings for consistent NAP updates across directories.
- Reusable shortcodes for centralized profile fields:
- `[csa_address]`
- `[csa_phone_link]`
- `[csa_email_link]`
- `[csa_hours]`
- `[csa_call_button]`
- `[csa_tour_button]`
- `[csa_map_embed]`
- Automatic LocalBusiness schema output when required profile fields are fully verified.
- Automatic FAQPage schema output on the FAQ page when at least two publish-ready Q/A pairs are detected.
- Schema toggles in Business Profile settings allow disabling built-in schema when an SEO plugin already outputs schema.

## Exact activation sequence
1. Activate theme `Hello Elementor CSA`.
2. Activate plugin `Elementor`.
3. Activate plugin `CSA Launch Kit`.
4. Go to `Tools > CSA Launch Kit` and click `Run Starter Setup`.
5. Go to `Settings > CSA Tour Form` and set notification email + confirmation text.
6. Open each generated page in Elementor and finalize layout/content.
7. Run preflight check in `Tools > CSA Launch Kit` until blocking count is `0`.

## Required post-setup edits
- Replace all `[VERIFY]` placeholders.
- Replace all `[DO NOT PUBLISH UNTIL CONFIRMED]` blocks with confirmed facts.
- Set real phone/email/hours in visible page sections.
- Replace placeholder media with approved real photos.
- Test map, links, form submission, and notification inbox delivery.

## Elementor resume notes
- This implementation is intentionally Elementor-compatible and includes Elementor + Hello Elementor in the stack.
- Generated pages can be opened directly in Elementor for visual customization and portfolio screenshots.

## Known constraints in this environment
- WP-CLI and PHP binaries are not available in this shell path, so activation/setup must be run through WordPress Admin UI.
