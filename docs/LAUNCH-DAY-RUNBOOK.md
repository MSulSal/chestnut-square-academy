# Launch Day Runbook

Use this runbook for the final cutover from staging to production (Hostinger).

## T-24 Hours
1. Confirm all facts in `FACT-VERIFICATION-MATRIX.md` are resolved.
2. Confirm owner account access:
- Domain registrar
- Hostinger
- WordPress admin
- SMTP/email provider
- Google Business Profile
3. Freeze content edits except launch team.
4. Download latest preflight report from `Tools > CSA Launch Kit`.

## T-2 Hours
1. Run fresh backup (database + files).
2. Verify SSL is active on production.
3. Confirm form notification inbox receives test emails.
4. Confirm `Tools > CSA Launch Kit` blockers are `0`.
5. Verify NAP consistency one final time:
- Site footer/header
- Contact page
- Google Business Profile

## Launch Window
1. Complete migration to production.
2. Set `Tools > CSA Launch Kit > Enable Production Indexing`.
3. Re-save permalinks (`Settings > Permalinks > Save`).
4. Clear any caches (plugin/server/CDN if used).
5. Validate live:
- Home loads on mobile and desktop
- Nav links
- Contact map
- Tour form submit + email receive
- FAQ page schema visibility in page source

## T+1 Hour
1. Submit production sitemap in Search Console.
2. Update Google Business Profile website URL.
3. Spot-check branded search result and map listing.
4. Save a post-launch preflight report for records.

## T+24 Hours
1. Review form submissions and inbox delivery.
2. Confirm no major console errors or broken links.
3. Confirm owner can log in and edit key pages.
4. Log completion notes in project tracker.

## Rollback Criteria
Rollback if any of the following occur:
- Form submissions fail or do not email.
- Critical pages fail to load.
- DNS points incorrectly to unrelated content.
- Major contact details are wrong on production.

Rollback steps:
1. Restore previous stable backup.
2. Set indexing back to staging mode if needed.
3. Re-verify production settings before retrying cutover.
