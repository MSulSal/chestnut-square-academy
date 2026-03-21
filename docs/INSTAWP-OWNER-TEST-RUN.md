# InstaWP Owner Test Run Plan

Use this for a full owner rehearsal before connecting the real domain.

## Goal
Give the director full control in a live-like environment without DNS/domain cutover risk.

## Setup Steps
1. Create InstaWP site from current package/migration.
2. Add owner account with Administrator role.
3. Confirm these are active:
- Hello Elementor CSA theme
- Elementor
- CSA Launch Kit
- SEO/Backup/SMTP/Security plugins
4. Keep indexing in staging/noindex mode during test run.

## Owner Test Tasks
1. Fill `Settings > CSA Business Profile` with sample-safe owner data.
2. Fill `Settings > CSA Tour Form`.
3. Update each key page in Elementor:
- Home
- About
- Programs
- Gallery
- FAQ
- Contact / Schedule a Tour
4. Replace all placeholder tokens.
5. Submit a real test tour form and confirm email receipt.
6. Run `Tools > CSA Launch Kit` preflight and resolve blockers.
7. Download preflight report and review together.

## Pass Criteria
1. Owner can update text/images on all main pages without help.
2. Owner can maintain contact details from Business Profile settings.
3. Form submissions are received reliably.
4. Preflight blockers are `0`.
5. Owner confirms confidence operating the site.

## Post-Test Decision
If owner test run passes:
1. Freeze content.
2. Export final migration package.
3. Move to production host.
4. Connect real domain only after final verification.
