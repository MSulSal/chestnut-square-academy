# Chestnut Square Academy Website

WordPress + Elementor build package for Chestnut Square Academy in Downtown McKinney, Texas.

## What Is Included
- Site planning blueprint: `docs/CSA-Website-Build-Blueprint.md`
- Delivery worklog: `docs/WORKLOG.md`
- Elementor setup guide: `docs/PHASE2-ELEMENTOR-SETUP.md`
- Elementor build checklist: `docs/ELEMENTOR-BUILD-CHECKLIST.md`
- Fact verification matrix: `docs/FACT-VERIFICATION-MATRIX.md`
- Launch runbook: `docs/LAUNCH-DAY-RUNBOOK.md`
- Owner quick guide: `docs/OWNER-QUICK-GUIDE.md`
- Owner data entry map: `docs/OWNER-DATA-ENTRY-MAP.md`
- Owner edit mode guide: `docs/OWNER-EDIT-MODE.md`
- Native parity mode guide: `docs/NATIVE-PARITY-MODE.md`
- InstaWP owner rehearsal plan: `docs/INSTAWP-OWNER-TEST-RUN.md`

## Current Site Status
- Core pages are set up for no-code editing with Elementor.
- Header navigation, footer menus, and gallery management are dashboard-editable.
- The active implementation is optimized for owner handoff and ongoing non-technical edits.

## Editing Profiles
Open `Tools > CSA Site Tools` in WordPress Admin.

- `Owner Edit Mode`: stricter component layout for simple drag-and-drop updates.
- `Native Parity Mode` (recommended): closest visual parity while keeping pages widget-level editable.

The active profile is stored in `kms_seed_profile` (`owner-edit` or `native-parity`).

## Quick Start (LocalWP)
1. Start the site in LocalWP.
2. In WordPress Admin, activate the Chestnut child theme and required plugins.
3. Open `Tools > CSA Site Tools` and run `Native Parity Seed`.
4. Open `Appearance > Menus` and confirm primary/footer menus.
5. Open `Appearance > Customize > CSA Footer Content` and confirm business details.
6. Open each page in Elementor and replace text/images as needed.
7. Test Contact/Tour workflow and mobile navigation before deployment.

## Notes
- Facts that are uncertain should still be verified before publish.
- Domain ownership and listing consistency (NAP) must be confirmed before launch.
