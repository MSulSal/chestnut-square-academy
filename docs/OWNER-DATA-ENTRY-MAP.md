# Owner Data Entry Map

Use this map to fill in real business data without developer help.

## Where To Enter Data
1. `Settings > CSA Business Profile`
2. `Settings > CSA Tour Form`
3. `Pages > Edit with Elementor` (replace all `[VERIFY]` tokens in text content)

## Field-by-Field Guide
| Field | Example Format | Where It Appears On Site | Where To Edit |
|---|---|---|---|
| Business Name | `Chestnut Square Academy` | Schema/citations/profile references | `Settings > CSA Business Profile` |
| Address | `402 S Chestnut St, McKinney, TX 75069` | Contact page, quick facts, citation block | `Settings > CSA Business Profile` |
| Phone | `(972) 555-0123` | Call buttons, contact details, phone links | `Settings > CSA Business Profile` |
| Public Email | `director@example.com` | Contact page and email links | `Settings > CSA Business Profile` |
| Hours | `Monday-Friday, 6:00 AM-6:00 PM` | Home quick facts, contact page, FAQ references | `Settings > CSA Business Profile` |
| Map Embed URL | `https://www.google.com/maps?q=...&output=embed` | Contact page map | `Settings > CSA Business Profile` |
| Business Description | `Trusted early learning...` | LocalBusiness schema description | `Settings > CSA Business Profile` |
| Tour Notification Email | `enrollment@example.com` | Admin receives form submissions | `Settings > CSA Tour Form` |
| Tour Success Message | `Thank you. Your tour request...` | Message shown after form submit | `Settings > CSA Tour Form` |
| Program details / FAQs / staff text | Replace placeholders with approved facts | Home/About/Programs/FAQ pages | `Pages > Edit with Elementor` |

## Required Data Rules
1. Do not leave `[VERIFY]` or `[DO NOT PUBLISH UNTIL CONFIRMED]` on public pages.
2. Do not publish until domain ownership verification is checked in Business Profile settings.
3. Keep NAP (name/address/phone) exactly consistent across website + directories.
4. Run `Tools > CSA Launch Kit` and ensure blockers are `0` before launch.
