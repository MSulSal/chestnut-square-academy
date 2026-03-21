# Chestnut Square Academy Website Build Blueprint

Date: March 21, 2026
Prepared for: Chestnut Square Academy (Downtown McKinney, TX)

## 1. Executive Summary
Chestnut Square Academy should launch a simple, mobile-first WordPress website that converts visitors into tour requests and phone calls while building local trust quickly. The build will use Gutenberg + a lightweight theme (Kadence) + lean plugins, so the non-technical director can edit content safely after handoff.

The site is intentionally designed to avoid bloat, avoid unverified claims, and avoid trust-breaking patterns seen in weak childcare sites. It includes complete page structure, production-ready draft copy, SEO setup, image strategy, form schema, launch checklist, and plain-English owner handoff.

## 2. Facts We Can Use Publicly
These are publicly visible signals as of March 21, 2026:

- Business name in public use: Chestnut Square Academy.
- Public location signal: Downtown McKinney, Texas.
- Address appears in multiple listings: 402 S Chestnut St, McKinney, TX 75069.
- Public listings indicate it is/was a licensed child care center with identifier 1647263 and listed capacity 46.
- Public listings show typical weekday operating window around 6:00 AM-6:00 PM.
- Public listings indicate transportation and field trips may be offered.
- Public listings indicate meals and Spanish-related offerings may be associated, but consistency is mixed across directories.
- The domain chestnutsquareacademy.com currently resolves to unrelated gambling/spam content (high trust risk and ownership risk).
- Current owned-web presence appears weaker than social + directory presence.

## 3. FACTS TO VERIFY BEFORE PUBLISHING
Do not publish any item below until owner confirms in writing.

- [DO NOT PUBLISH UNTIL CONFIRMED] Primary phone number for website header and CTA buttons.
- [DO NOT PUBLISH UNTIL CONFIRMED] Public-facing email address for enrollment/tours.
- [DO NOT PUBLISH UNTIL CONFIRMED] Exact business hours (especially open/close times and holiday schedule).
- [DO NOT PUBLISH UNTIL CONFIRMED] Exact age bands currently enrolled.
- [DO NOT PUBLISH UNTIL CONFIRMED] Whether school-age care is currently active.
- [DO NOT PUBLISH UNTIL CONFIRMED] Whether transportation services are currently active.
- [DO NOT PUBLISH UNTIL CONFIRMED] Whether field trips are currently active for currently enrolled age groups.
- [DO NOT PUBLISH UNTIL CONFIRMED] Whether breakfast/lunch/snacks are included and which meals are provided.
- [DO NOT PUBLISH UNTIL CONFIRMED] Whether Spanish is formal instruction, language exposure, or occasional enrichment.
- [DO NOT PUBLISH UNTIL CONFIRMED] Exact Texas Rising Star status and level (if any).
- [DO NOT PUBLISH UNTIL CONFIRMED] Legal business name and payment/contact entity name.
- [DO NOT PUBLISH UNTIL CONFIRMED] Domain ownership transfer/control and registrar account access.
- [VERIFY] Map pin accuracy at address entrance (parking and safest drop-off point).
- [VERIFY] ADA/accessibility details (entry route, parent access info if applicable).
- [VERIFY] Photo permissions/releases for children in any published images.

## 4. Recommended WordPress Stack
### Exact WordPress Setup Plan (Near-Turnkey)
1. Local build in LocalWP (already present): keep all work in Gutenberg and avoid heavy page builders.
2. Install/update WordPress core and set:
- Site title: Chestnut Square Academy
- Tagline: Early Learning in Downtown McKinney
- Timezone: America/Chicago
- Permalinks: Post name
- Discourage indexing: ON in staging/dev only; OFF at launch
3. Theme setup:
- Install Kadence theme (free).
- Create global color/typography tokens to match brand palette.
- Enable wide/full alignments.
4. Essential plugins only (lean stack below).
5. Create pages and navigation in this order:
- Home, About, Programs, Gallery, FAQ, Contact / Schedule a Tour
- Optional: Careers, Parent Resources, Privacy Policy
6. Build reusable block patterns:
- Hero + dual CTA
- Quick facts strip
- Trust cards
- CTA banner
- FAQ preview
- Contact/map block
7. Add custom CSS from Section 6 in `Appearance > Customize > Additional CSS` (or child theme stylesheet if preferred).
8. Build Schedule a Tour form (Section 9 schema), embed on Contact page and in footer CTA modal/section.
9. SEO setup:
- Titles, meta, schema defaults, social image fallback, XML sitemap.
10. Email delivery:
- Configure WP Mail SMTP with owner-controlled mailbox.
- Send test to owner + admin before launch.
11. Backups + security baseline:
- Set daily backups and weekly offsite copy.
- Harden login and admin accounts.
12. Migration to Hostinger:
- Use Duplicator (or Hostinger Migrator) after owner sign-off.
- Replace staging URL, re-save permalinks, retest forms + email + map + schema.

### Theme + Plugin Recommendations
Theme:
- Kadence (free) for lightweight Gutenberg-first control and simple handoff.
- [OPTIONAL] Kadence Blocks (free) only if native core blocks feel limiting.

Plugins (lean, practical):
- Fluent Forms (free) or WPForms Lite: Schedule a Tour form.
- Rank Math SEO (free) or Yoast SEO (free): on-page SEO + schema controls.
- UpdraftPlus: scheduled backups.
- Solid Security Basic (or Wordfence if owner prefers known brand): login hardening + alerts.
- WP Mail SMTP: reliable form/email delivery.
- [OPTIONAL] Duplicator: migration package for staging to production.

Avoid:
- Elementor/Divi unless a specific requirement cannot be met with Gutenberg.
- Plugin overlap (multiple security, multiple SEO, multiple form plugins).

## 5. Website Architecture
### Final Site Architecture
Primary navigation:
- Home
- About
- Programs
- Gallery
- FAQ
- Contact / Schedule a Tour

Secondary/footer navigation:
- Careers [OPTIONAL]
- Parent Resources [OPTIONAL]
- Privacy Policy
- Terms [OPTIONAL]

Conversion architecture:
- Persistent top-right CTA: Schedule a Tour
- Secondary click-to-call button in hero + footer
- Contact details in header/footer
- Repeated trust + reassurance messaging near every form CTA

Template architecture (Gutenberg reusable sections):
- `HeroDualCTA`
- `QuickFactsRow`
- `TrustReasonsGrid`
- `ProgramCards`
- `PhotoBand`
- `FAQPreview`
- `BottomCTA`
- `ContactMapCard`

How this avoids anti-patterns:
- Domain hijack risk addressed before launch by ownership verification and strict DNS cutover plan.
- Thin microsite risk avoided with complete, scannable, trust-first page depth on core parent questions.
- Cluttered UX risk avoided by single clean menu, no exposed login/account UI, no duplicate nav bars, no noisy widgets.

## 6. Brand Direction
Design intent: warm, calm, trustworthy, local, and polished without corporate stiffness.

Color system:
- Chestnut: #7A4B2C
- Cream: #FFF7EF
- Sage: #B8C9B1
- Sky: #DCEFFB
- Marigold: #F4C567
- Ink: #24313A

Typography:
- Headings: Fraunces (Google Font)
- Body/UI: Nunito Sans (Google Font)
- Fallbacks: Georgia for headings, Arial/sans-serif for body where needed

### Custom CSS (Production-Ready Visual System)
```css
:root {
  --csa-chestnut: #7a4b2c;
  --csa-cream: #fff7ef;
  --csa-sage: #b8c9b1;
  --csa-sky: #dceffb;
  --csa-marigold: #f4c567;
  --csa-ink: #24313a;
  --csa-white: #ffffff;
  --csa-radius-sm: 10px;
  --csa-radius-md: 16px;
  --csa-radius-lg: 24px;
  --csa-shadow: 0 10px 30px rgba(36, 49, 58, 0.08);
  --csa-max: 1140px;
}

body {
  font-family: "Nunito Sans", Arial, sans-serif;
  color: var(--csa-ink);
  background: linear-gradient(180deg, var(--csa-cream) 0%, #fffdf9 100%);
  font-size: 18px;
  line-height: 1.6;
}

h1,
h2,
h3,
h4,
h5,
h6 {
  font-family: "Fraunces", Georgia, serif;
  color: var(--csa-ink);
  line-height: 1.2;
  letter-spacing: 0.01em;
}

.site-header,
.site-footer {
  background: var(--csa-white);
}

.csa-section {
  max-width: var(--csa-max);
  margin: 0 auto;
  padding: clamp(24px, 4vw, 56px) clamp(16px, 3vw, 24px);
}

.csa-hero {
  background:
    radial-gradient(circle at 85% 15%, rgba(244, 197, 103, 0.35), transparent 45%),
    radial-gradient(circle at 15% 85%, rgba(184, 201, 177, 0.4), transparent 45%),
    var(--csa-cream);
  border-radius: var(--csa-radius-lg);
  box-shadow: var(--csa-shadow);
}

.csa-quickfacts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
  gap: 12px;
}

.csa-fact {
  background: var(--csa-white);
  border: 1px solid rgba(36, 49, 58, 0.1);
  border-radius: var(--csa-radius-sm);
  padding: 14px;
}

.csa-card {
  background: var(--csa-white);
  border: 1px solid rgba(36, 49, 58, 0.1);
  border-radius: var(--csa-radius-md);
  box-shadow: var(--csa-shadow);
  padding: 22px;
}

.wp-block-button__link,
button,
input[type="submit"] {
  border-radius: 999px;
  font-weight: 700;
  padding: 12px 20px;
  border: 2px solid transparent;
}

.csa-btn-primary .wp-block-button__link {
  background: var(--csa-chestnut);
  color: var(--csa-white);
}

.csa-btn-secondary .wp-block-button__link {
  background: var(--csa-marigold);
  color: var(--csa-ink);
}

a:focus,
button:focus,
input:focus,
textarea:focus,
select:focus {
  outline: 3px solid var(--csa-sky);
  outline-offset: 2px;
}

.csa-photo {
  border-radius: var(--csa-radius-md);
  overflow: hidden;
  aspect-ratio: 4 / 3;
  object-fit: cover;
}

@media (max-width: 768px) {
  body {
    font-size: 17px;
  }

  .csa-section {
    padding: 20px 16px;
  }
}
```

## 7. Page-by-Page Content Plan
### Home (Required Blocks in Order)
1. Hero (`Group + Columns`)
- H1 + short reassurance line
- Primary CTA: Schedule a Tour
- Secondary CTA: Call Now
- Hero image: smiling teacher greeting parent/child at entry
- Alt placeholder: "Teacher greeting family at Chestnut Square Academy entrance"
2. Quick Facts Strip (`Columns`)
- Location, hours, ages served [VERIFY], meals [VERIFY]
3. Why Families Choose Us (`Cards Grid`)
- 3-4 trust cards
4. Programs Snapshot (`Cards`)
- Infants [VERIFY], Toddlers [VERIFY], Preschool/Pre-K [VERIFY], School-age [VERIFY]
5. Authentic Photo Band (`Gallery`)
- 4-6 real photos
6. Local Story Snippet (`Media + Text`)
- Downtown McKinney identity + family-first tone
7. FAQ Preview (`Accordion`)
- 3 top questions + link to full FAQ
8. Bottom CTA (`Group Banner`)
- Schedule a Tour + Call Now
9. Contact + Map (`Columns`)
- Address, hours [VERIFY], phone [VERIFY], embedded map

### About
1. Page intro
2. School overview
3. Director message placeholder [VERIFY]
4. Philosophy/approach
5. Downtown McKinney community section
6. Team/staff placeholders
7. CTA to schedule tour

### Programs
1. Program overview intro with [VERIFY] note on final age group labels
2. Program cards by age:
- Infants [VERIFY]
- Toddlers [VERIFY]
- Preschool / Pre-K [VERIFY]
- School-age [VERIFY]
3. Daily routine timeline
4. Meals/snacks section [VERIFY]
5. Spanish enrichment section [VERIFY]
6. Transportation / field trips section [VERIFY]
7. Tour CTA

### Gallery
1. Short intro text (real moments, real classrooms)
2. Simple category filters (optional) or grouped headings:
- Classrooms
- Learning Moments
- Teacher Interactions
- Outdoor Play
- Our Location
3. Lightbox gallery
4. Bottom CTA

### FAQ
1. Intro paragraph
2. 8-12 question accordion
3. Link to contact page for unanswered questions
4. Bottom CTA

### Contact / Schedule a Tour
1. Contact header + reassurance text
2. Address + hours + phone + email (all validated)
3. Map block
4. Schedule a Tour form embed
5. "What happens next" steps (3 bullets)
6. Emergency or urgent-call note

### Optional Pages
- Careers [OPTIONAL]: simple hiring statement + email/form.
- Parent Resources [OPTIONAL]: calendar, forms, announcements.
- Privacy Policy: required for forms and analytics.

## 8. Full Draft Copy
Use this as launch-ready copy with placeholders where verification is needed.

### Home Copy
H1: Trusted Early Learning in Downtown McKinney

Subheadline: A warm, dependable place for your child to learn, play, and grow while your family feels informed and supported.

Primary CTA Button: Schedule a Tour
Secondary CTA Button: Call Now [VERIFY PHONE]

Quick Facts:
- Location: 402 S Chestnut St, McKinney, TX 75069 [VERIFY]
- Hours: Monday-Friday, 6:00 AM-6:00 PM [VERIFY]
- Ages Served: [VERIFY]
- Meals: Breakfast and lunch [VERIFY]

Section Heading: Why Families Choose Chestnut Square Academy
Body: Families choose Chestnut Square Academy for caring teachers, a welcoming environment, and clear daily communication. We focus on helping children feel safe, confident, and ready for each new stage of learning.

Trust Cards:
- Caring, Consistent Teachers: Your child is known by name and supported every day.
- Safe, Structured Days: Predictable routines help children feel secure and thrive.
- Family Communication: We keep parents informed so you always know how your child is doing.
- Community Roots: Proudly serving families in and around Downtown McKinney.

Programs Snapshot Heading: Programs for Early Childhood Stages
Card 1: Infants [VERIFY] - Nurturing care, responsive routines, and early sensory learning.
Card 2: Toddlers [VERIFY] - Guided exploration, language growth, and social development.
Card 3: Preschool / Pre-K [VERIFY] - Play-based learning that builds kindergarten-ready confidence.
Card 4: School-Age [VERIFY] - Before/after-school support and engaging activities.

Local Section Heading: A Neighborhood School in Historic Downtown McKinney
Body: We are proud to be part of the Downtown McKinney community. Our school blends a family-first atmosphere with a clean, modern learning environment where children can grow with confidence.

FAQ Preview Heading: Common Questions from New Families
- Do you offer tours? Yes. We encourage every family to visit, meet our team, and see classrooms in person.
- What ages do you accept? We serve children in specific age groups. Final placement depends on current enrollment and licensing [VERIFY].
- How do I get started? Complete the Schedule a Tour form or call us directly [VERIFY PHONE].

Bottom CTA Heading: Ready to Visit Chestnut Square Academy?
Body: We would love to meet your family, answer your questions, and help you find the right fit.
Buttons: Schedule a Tour | Call Now [VERIFY PHONE]

Contact Block:
- Address: 402 S Chestnut St, McKinney, TX 75069 [VERIFY]
- Phone: [VERIFY]
- Email: [VERIFY]
- Hours: Monday-Friday, [VERIFY]

### About Copy
H1: About Chestnut Square Academy

Intro: Chestnut Square Academy is an early learning center serving families in Downtown McKinney, Texas. We are committed to providing dependable care, a safe environment, and meaningful early-learning experiences for every child.

Section: Our Approach
Body: We believe children learn best when they feel secure, encouraged, and engaged. Our team combines warm relationships with age-appropriate activities that support social, emotional, and early academic growth.

Section: Message from Our Director [VERIFY]
Placeholder: "Welcome to Chestnut Square Academy. Our goal is to partner with your family and create a positive, nurturing experience for your child each day." [VERIFY DIRECTOR NAME/TITLE]

Section: Rooted in Downtown McKinney
Body: Our school is proud to be part of the Downtown McKinney community. We value neighborhood connection, family trust, and a welcoming atmosphere where parents feel comfortable and children feel at home.

Section: Meet Our Team [VERIFY]
Body: Our teachers and staff are dedicated to caring for each child with patience, consistency, and professionalism.

CTA: Schedule a Tour

### Programs Copy
H1: Programs

Intro: We offer age-appropriate care and learning experiences designed to support each stage of early childhood development. Final age-group availability is confirmed during your tour.

Program: Infants [VERIFY]
Body: Our infant care environment is calm, nurturing, and responsive. Teachers provide individualized feeding, sleep, and play routines that support healthy early development.

Program: Toddlers [VERIFY]
Body: Toddlers build confidence through guided play, language-rich interaction, movement, and social learning.

Program: Preschool / Pre-K [VERIFY]
Body: Preschool and Pre-K children participate in hands-on learning that builds early literacy, early math, independence, and classroom readiness.

Program: School-Age [VERIFY]
Body: [DO NOT PUBLISH UNTIL CONFIRMED] If active, this program supports children with structured before/after-school care and enrichment opportunities.

Section: A Typical Day
Body: Children follow a consistent daily rhythm that includes active play, guided learning, meals/snacks [VERIFY], rest time (age-dependent), and transition routines that help them feel secure.

Section: Meals and Snacks [VERIFY]
Body: [DO NOT PUBLISH UNTIL CONFIRMED] We provide meal options that support growing children and family schedules.

Section: Spanish Exposure / Enrichment [VERIFY]
Body: [DO NOT PUBLISH UNTIL CONFIRMED] Children may be introduced to Spanish through songs, vocabulary, and daily interactions.

Section: Transportation and Field Trips [VERIFY]
Body: [DO NOT PUBLISH UNTIL CONFIRMED] Transportation services and field trip participation depend on age group, schedule, and current program operations.

CTA: Schedule a Tour

### Gallery Copy
H1: Gallery
Intro: A look inside everyday moments at Chestnut Square Academy.

Subheadings:
- Classrooms
- Learning Through Play
- Teachers and Children
- Outdoor Moments
- Our Downtown Location

Closing CTA: Want to see our school in person? Schedule a Tour.

### FAQ Copy (10 Questions)
H1: Frequently Asked Questions

1. What are your hours?
Our publicly listed hours are Monday-Friday, around 6:00 AM-6:00 PM. Please confirm current hours directly with our office [VERIFY].

2. What ages do you serve?
We serve multiple age groups in early childhood. Current openings and age placements are confirmed during enrollment [VERIFY].

3. Do you offer tours?
Yes. Families are encouraged to schedule a tour to meet staff, see classrooms, and ask questions.

4. What should we bring for a tour?
Bring your questions, your preferred care schedule, and your child’s age so we can discuss best-fit options.

5. Do you provide meals or snacks?
Meals/snacks may be available based on program operations. Please confirm what is currently included [VERIFY].

6. Is Spanish part of your program?
Spanish exposure/enrichment may be offered in age-appropriate ways. Please confirm current implementation [VERIFY].

7. Do you offer transportation services?
Transportation may be available depending on program and age group. Please confirm current routes and eligibility [VERIFY].

8. Do you take field trips?
Field trips may be part of select programs, depending on age and season [VERIFY].

9. How does enrollment work?
Start with a tour request. After your visit, our team will share next steps for availability, paperwork, and start dates.

10. How quickly will someone follow up?
We aim to reply as soon as possible during business hours. For urgent questions, call us directly [VERIFY PHONE].

### Contact / Schedule a Tour Copy
H1: Contact and Schedule a Tour

Intro: We know choosing care is a big decision. We are here to answer your questions and help you feel confident about next steps.

Contact Details:
- Address: 402 S Chestnut St, McKinney, TX 75069 [VERIFY]
- Phone: [VERIFY]
- Email: [VERIFY]
- Hours: Monday-Friday, [VERIFY]

What Happens Next:
1. Submit the form with your preferred day/time.
2. Our team will contact you to confirm your visit.
3. Tour the school, meet staff, and discuss enrollment options.

Reassurance Note: We are happy to answer practical questions before your visit so your family knows what to expect.

## 9. Form Plan
Form name: Schedule a Tour

Fields (required unless marked optional):
- Parent/Guardian Name
- Child Age (dropdown + "Other")
- Phone Number
- Email Address
- Preferred Tour Day/Time
- Questions / Notes (optional textarea)

Validation and UX:
- Simple one-column mobile layout.
- Clear required labels and inline error messages.
- Consent checkbox: "I agree to be contacted about my tour request." 

Confirmation message:
- "Thank you. Your tour request has been received. Our team will contact you soon to confirm your visit."

Admin routing:
- Primary recipient: director email [VERIFY]
- Secondary recipient: backup admin email [VERIFY]
- Email subject: "New Tour Request - Chestnut Square Academy"

Anti-spam:
- Honeypot field ON
- reCAPTCHA or Cloudflare Turnstile ON
- Limit submissions per IP if spam appears

Privacy note under form:
- "Your information is used only to respond to your tour request and is not sold to third parties."

## 10. SEO Plan
### Per-Page SEO Setup
Home
- SEO Title: Chestnut Square Academy | Daycare & Early Learning in Downtown McKinney, TX
- Meta Description: Discover a warm, trusted early learning center in Downtown McKinney. Schedule a tour at Chestnut Square Academy.
- H1: Trusted Early Learning in Downtown McKinney
- Internal Links: About, Programs, FAQ, Contact
- Local Keywords: daycare downtown mckinney, early learning mckinney tx, childcare mckinney

About
- SEO Title: About Chestnut Square Academy | Family-First Childcare in McKinney
- Meta Description: Learn about Chestnut Square Academy, our approach, and our commitment to families in Downtown McKinney.
- H1: About Chestnut Square Academy
- Internal Links: Home, Programs, Contact
- Local Keywords: childcare center mckinney, daycare near downtown mckinney

Programs
- SEO Title: Programs | Chestnut Square Academy McKinney, TX
- Meta Description: Explore age-appropriate childcare and early learning programs at Chestnut Square Academy in McKinney.
- H1: Programs
- Internal Links: About, FAQ, Contact
- Local Keywords: infant care mckinney [VERIFY], toddler care mckinney [VERIFY], preschool mckinney [VERIFY]

Gallery
- SEO Title: Gallery | Chestnut Square Academy Classrooms & Learning Moments
- Meta Description: View photos of classrooms, activities, and everyday moments at Chestnut Square Academy.
- H1: Gallery
- Internal Links: Home, Programs, Contact
- Local Keywords: daycare photos mckinney, preschool classrooms mckinney

FAQ
- SEO Title: FAQ | Chestnut Square Academy, McKinney TX
- Meta Description: Get answers to common parent questions about tours, programs, hours, and enrollment at Chestnut Square Academy.
- H1: Frequently Asked Questions
- Internal Links: Programs, Contact
- Local Keywords: daycare questions mckinney, childcare enrollment mckinney

Contact
- SEO Title: Contact Chestnut Square Academy | Schedule a Tour in McKinney, TX
- Meta Description: Contact Chestnut Square Academy and schedule a tour. Find our location, hours, and next steps.
- H1: Contact and Schedule a Tour
- Internal Links: Home, FAQ, Programs
- Local Keywords: schedule daycare tour mckinney, daycare contact mckinney

### Schema Recommendations
- LocalBusiness schema (or ChildCare schema if plugin supports subtype):
- `name`, `address`, `telephone` [VERIFY], `openingHours` [VERIFY], `url`, `geo`, `sameAs` (verified social links only), `image`.
- FAQ schema on FAQ page only (must exactly match visible FAQ text).

### Image Alt-Text Conventions
- Describe real scene, not SEO stuffing.
- Format: "[Activity] at Chestnut Square Academy in McKinney" when location context is useful.
- Avoid repeating identical alt text across gallery.

### Google Business Profile + Citation Consistency
- Confirm and lock NAP (Name, Address, Phone) across:
- Google Business Profile
- Facebook
- Major childcare directories
- Any chamber/local listings
- Remove or correct outdated phone/email/hours.
- Ensure site URL in GBP points to owned clean domain only.

## 11. Image / Asset Plan
Image priority:
1. Real photos from school (best trust signal)
2. Approved Facebook images with explicit usage rights
3. Minimal stock placeholders only until real photos are ready

Recommended image list by page:
- Home: hero exterior or welcome moment, 4 trust/support photos
- About: director portrait [VERIFY], team group photo [VERIFY], building/local context image
- Programs: one image per program band [VERIFY]
- Gallery: 18-30 photos total at launch target
- Contact: exterior entry and map screenshot backup

Placeholder strategy if photos are delayed:
- Use neutral gradient background cards + concise captions
- Keep a simple text wordmark in header until final logo assets are approved

Alt-text placeholders (replace at upload):
- "Infant classroom activity at Chestnut Square Academy" [VERIFY PROGRAM]
- "Toddler sensory play with teacher guidance" [VERIFY PROGRAM]
- "Preschool circle time in Downtown McKinney childcare center" [VERIFY PROGRAM]
- "Exterior of Chestnut Square Academy on South Chestnut Street" [VERIFY]

## 12. Accessibility + Performance Plan
Accessibility:
- Minimum body text ~17-18px on mobile.
- Strong contrast (Ink on Cream, white on Chestnut CTA).
- Buttons sized for touch targets.
- Form labels always visible, never placeholder-only.
- Keyboard-focus styles clearly visible.
- Use heading hierarchy H1 -> H2 -> H3 cleanly.
- Provide meaningful alt text; decorative images empty alt.

Performance:
- Use WebP where possible.
- Compress images before upload and provide explicit dimensions.
- Avoid giant hero sliders/video autoplay.
- Keep plugin count lean.
- Enable caching and minification carefully (test forms after).
- Use local/system fallback fonts until web fonts load.
- Target stable layout by defining image/container aspect ratios.

## 13. Hosting + Domain + Handoff Plan
### Host Recommendation (Hostinger)
Recommended tier: Hostinger Business WordPress plan (or equivalent current tier that includes staging + daily backups).

Why this tier is the best fit:
- Enough headroom for image-heavy childcare content.
- Staging support for safe edits.
- Managed WordPress features and backups suitable for non-technical owners.
- Better long-term support than minimal starter tier for business-critical contact forms.

### Domain Strategy (Critical)
- Current `chestnutsquareacademy.com` is unsafe/hijacked and must not be linked until ownership is recovered and DNS is clean.
- Confirm registrar ownership and lock domain account with owner-controlled email + 2FA.
- If recovery is delayed, launch temporarily on a clean owner-controlled domain [OPTIONAL], then 301 redirect when primary domain is secured.

### Migration Workflow
1. Build and approve on LocalWP.
2. Optional staging review (password-protected).
3. Provision Hostinger production under owner account.
4. Migrate with Duplicator/Hostinger Migrator.
5. Update URLs, permalinks, forms, SMTP, schema, and analytics.
6. Final QA + launch.

### Credentials That Must Be Handed Off (Owner-Controlled)
- Domain registrar login
- Hostinger login and billing access
- WordPress admin login
- SMTP/email service login
- Google Business Profile owner access
- Google Analytics/Search Console access
- Cloudflare/DNS login if used
- Backup plugin destination access

## 14. Launch Checklist
Pre-launch content:
- Confirm all [VERIFY] and [DO NOT PUBLISH UNTIL CONFIRMED] fields.
- Replace placeholder contact info.
- Replace placeholder photos where possible.

Technical:
- SSL active and forced HTTPS.
- XML sitemap live.
- Robots indexing enabled for production.
- Form submission and email delivery tested.
- Backup run successfully.
- 404 page and redirects checked.

SEO/local:
- NAP consistent sitewide and in GBP.
- Meta titles/descriptions complete.
- LocalBusiness schema valid.
- FAQ schema valid.

UX/accessibility:
- Mobile nav and CTA buttons tested.
- Color contrast check passed.
- Keyboard test passed for menu + form.

Post-launch (first 7 days):
- Monitor form deliveries daily.
- Monitor uptime and speed.
- Check Google indexing status.

## 15. Director-Friendly Explanation
This website setup is designed so you own everything directly.

Cost clarity in plain English:
- Hosting is not a fee paid to the freelancer. It is your normal website vendor cost, like paying an electric bill for your site to stay online.
- Domain, hosting, and optional business email are owner-paid third-party services.
- A freelancer can pay an initial invoice for convenience, but ownership should always remain in your account.
- Renewals are expected annual/monthly vendor renewals, not hidden project fees.

Renewals/cost types to explain before handoff:
- Domain renewal
- Hosting renewal
- Optional premium plugin/theme renewals
- Business email renewal (if used)
- Optional care plan/support hours

## 16. Risks and Open Questions
Key risks:
- Domain trust risk remains high until `chestnutsquareacademy.com` is fully recovered and secured.
- Public contact data inconsistency may reduce conversion if not cleaned before launch.
- Unverified claims (programs, meals, transportation, Spanish, school-age, TRS level) can create legal/trust issues if published prematurely.

Open questions to resolve now:
- Confirm final phone/email/hours and best tour contact method.
- Confirm exact active program bands and current availability.
- Confirm whether Texas Rising Star can be claimed publicly and how it must be worded.
- Confirm whether school-age program is active now.
- Confirm rights/permissions for all photo assets.

Source notes used for this plan (reviewed March 21, 2026):
- The Learning Experience McKinney: https://thelearningexperience.com/centers/mckinney/
- Primrose Stone Brooke: https://www.primroseschools.com/schools/stone-brooke
- Bright Horizons center pages (TX examples including Legacy): https://child-care-preschool.brighthorizons.com/tx/plano/legacy/our-center
- Chestnut Square Historic Village: https://www.chestnutsquare.org/
- Downtown McKinney official page: https://www.mckinneytexas.org/3574/Downtown-McKinney
- Anti-pattern domain issue: https://chestnutsquareacademy.com/
- Anti-pattern clutter example: https://topoftheworldpreschool.com/keeping-informed
- Public listing signal (address/hours/license/program hints): https://winnie.com/place/chestnut-square-academy-mckinney-3
- Hostinger plan references: https://www.hostinger.com/wordpress-hosting
