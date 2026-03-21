# Elementor Build Checklist

Use this to build the final visual pages quickly in Elementor while keeping content consistent with the launch kit.

## Global Setup
1. In `Elementor > Settings`, ensure post type `Pages` is enabled.
2. In `Appearance > Themes`, confirm `Hello Elementor CSA` is active.
3. In `Settings > CSA Business Profile`, fill all verified fields before final publish.
4. In `Elementor > Site Settings`, set:
- Colors to match CSA palette from `style.css`
- Typography:
- Heading font: Fraunces
- Body font: Nunito Sans

## Home Page
1. Hero section: heading, intro text, dual CTA buttons (`[csa_tour_button]` and `[csa_call_button]`).
2. Quick facts row with icons: location `[csa_address]`, hours `[csa_hours]`, age placeholder, meals placeholder.
3. Trust cards (4 columns desktop, 1 column mobile).
4. Programs snapshot cards (Infants/Toddlers/Preschool/School-age placeholder).
5. Local identity section (Downtown McKinney message).
6. FAQ preview list + link to full FAQ page.
7. Bottom CTA strip with tour/call buttons.

## About Page
1. Intro section.
2. Approach/philosophy section.
3. Director message block `[VERIFY]`.
4. Community roots section.
5. Team section with staff placeholders.
6. Closing CTA button to tour page.

## Programs Page
1. Intro section.
2. Program cards by age band.
3. Daily rhythm section.
4. Meals/snacks section `[VERIFY]`.
5. Spanish exposure section `[VERIFY]`.
6. Transportation/field trips section `[VERIFY]`.
7. Closing CTA.

## Gallery Page
1. Intro section.
2. Image grid (start with placeholders if needed).
3. Use consistent aspect ratios to avoid layout shift.
4. Add meaningful alt text for each image.
5. Closing CTA.

## FAQ Page
1. Use heading + text pattern per Q/A (`H2` question + paragraph answer).
2. Keep wording aligned with verified facts only.
3. Do not leave unresolved placeholders at launch (FAQ schema is auto-generated from visible content).

## Contact / Schedule a Tour Page
1. Contact details card with shortcodes:
- `[csa_address]`
- `[csa_phone_link]`
- `[csa_email_link]`
- `[csa_hours]`
2. Map card using `[csa_map_embed]`.
3. Form card with `[csa_schedule_tour_form]`.
4. “What happens next” 3-step reassurance section.

## Optional Pages
1. Careers: active openings + application process.
2. Parent Resources: handbook/forms/calendar.
3. Privacy Policy: replace starter text with approved final policy.

## Final QA in Elementor
1. Mobile pass at ~390px width for every page.
2. Button contrast and focus states.
3. Heading order check (`H1` once per page).
4. Image compression + alt text completion.
5. Run `Tools > CSA Launch Kit` and confirm blockers are zero before publish.
