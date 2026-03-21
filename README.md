# Chestnut Square Academy Website

Elementor-friendly WordPress build package for Chestnut Square Academy.

## What is in this repo
- Planning blueprint: `docs/CSA-Website-Build-Blueprint.md`
- Running worklog: `docs/WORKLOG.md`
- Phase 2 implementation guide: `docs/PHASE2-ELEMENTOR-SETUP.md`
- Elementor page build flow: `docs/ELEMENTOR-BUILD-CHECKLIST.md`
- Fact verification gate: `docs/FACT-VERIFICATION-MATRIX.md`
- Launch execution runbook: `docs/LAUNCH-DAY-RUNBOOK.md`
- Handoff credentials template: `docs/HANDOFF-CREDENTIAL-INVENTORY.md`
- Non-technical owner guide: `docs/OWNER-QUICK-GUIDE.md`
- Owner data entry map: `docs/OWNER-DATA-ENTRY-MAP.md`
- InstaWP owner rehearsal plan: `docs/INSTAWP-OWNER-TEST-RUN.md`
- Content source notes for seeded copy: `docs/CONTENT-SOURCE-NOTES.md`
- Hello Elementor child theme: `app/public/wp-content/themes/hello-elementor-csa`
- Launch/setup plugin: `app/public/wp-content/plugins/csa-launch-kit`
- Launch kit release notes: `docs/CSA-LAUNCH-KIT-CHANGELOG.md`

Starter setup now creates required pages plus optional `Careers`, `Parent Resources`, and `Privacy Policy` pages.
The launch kit also outputs LocalBusiness + FAQ schema automatically when fields/content are publish-ready.
Schema output can be toggled in `Settings > CSA Business Profile` to avoid duplicate schema with SEO plugins.
Business Profile settings include a copy-ready NAP block and JSON reference for citation cleanup.

## Current Build Phase
- The current generated site is a structured scaffold, not the final visual finish.
- It now includes richer location-based starter copy seeded from public listing signals and clearly marked `[VERIFY]` gates.
- Final polished appearance and section composition are completed in Elementor during Phase 2.

## Quick Start (LocalWP)
1. Start the site in LocalWP.
2. In WordPress Admin, go to `Appearance > Themes` and activate `Hello Elementor CSA`.
3. Go to `Plugins` and activate:
- `Elementor`
- `CSA Launch Kit`
- `Yoast SEO` (`wordpress-seo`)
- `UpdraftPlus`
- `WP Mail SMTP`
- `Fluent Forms`
- `Solid Security` (`better-wp-security`)
4. Go to `Tools > CSA Launch Kit` and click `Run Starter Setup`.
5. (Optional) click `Activate Recommended Plugins` in `Tools > CSA Launch Kit`.
6. Go to `Settings > CSA Business Profile` and fill in verified business details.
7. Go to `Settings > CSA Tour Form` and set notification email.
8. Open each page in Elementor and replace all `[VERIFY]` placeholders.
9. Run `Tools > CSA Launch Kit` preflight until blocking count is `0`.
10. Test tour form submission from the Contact page.
11. Download and archive the preflight report from `Tools > CSA Launch Kit`.
12. In `Tools > CSA Launch Kit`, switch indexing mode appropriately (staging noindex vs production indexing).
13. In `Settings > CSA Business Profile`, check domain ownership/DNS verification once confirmed.

## Notes
- This project intentionally flags uncertain facts with `[VERIFY]` and `[DO NOT PUBLISH UNTIL CONFIRMED]`.
- Domain ownership/data consistency checks are required before launch.
