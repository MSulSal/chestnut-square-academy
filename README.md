# Chestnut Square Academy Website

Elementor-friendly WordPress build package for Chestnut Square Academy.

## What is in this repo
- Planning blueprint: `docs/CSA-Website-Build-Blueprint.md`
- Running worklog: `docs/WORKLOG.md`
- Phase 2 implementation guide: `docs/PHASE2-ELEMENTOR-SETUP.md`
- Elementor page build flow: `docs/ELEMENTOR-BUILD-CHECKLIST.md`
- Fact verification gate: `docs/FACT-VERIFICATION-MATRIX.md`
- Hello Elementor child theme: `app/public/wp-content/themes/hello-elementor-csa`
- Launch/setup plugin: `app/public/wp-content/plugins/csa-launch-kit`

Starter setup now creates required pages plus optional `Careers`, `Parent Resources`, and `Privacy Policy` pages.
The launch kit also outputs LocalBusiness + FAQ schema automatically when fields/content are publish-ready.

## Quick Start (LocalWP)
1. Start the site in LocalWP.
2. In WordPress Admin, go to `Appearance > Themes` and activate `Hello Elementor CSA`.
3. Go to `Plugins` and activate:
- `Elementor`
- `CSA Launch Kit`
4. Go to `Tools > CSA Launch Kit` and click `Run Starter Setup`.
5. Go to `Settings > CSA Business Profile` and fill in verified business details.
6. Go to `Settings > CSA Tour Form` and set notification email.
7. Open each page in Elementor and replace all `[VERIFY]` placeholders.
8. Run `Tools > CSA Launch Kit` preflight until blocking count is `0`.
9. Test tour form submission from the Contact page.

## Notes
- This project intentionally flags uncertain facts with `[VERIFY]` and `[DO NOT PUBLISH UNTIL CONFIRMED]`.
- Domain ownership/data consistency checks are required before launch.
