# wptheme

This repository contains **jbportal** — a full-featured WordPress job board theme.

It is inspired by popular job-board themes (Civi Job Board on ThemeForest among others) and ships
with the same set of core functionalities, but uses an original layout and a fresh teal + coral color
palette instead of the typical navy/orange seen in commercial job board themes.

## What's inside

```
jbportal/
├── style.css                    Theme header / metadata
├── functions.php                Theme bootstrap
├── header.php / footer.php / sidebar.php / index.php / front-page.php
├── single.php / page.php / search.php / searchform.php / 404.php / comments.php
├── single-job_listing.php       Single job page (apply form, sidebar, related jobs)
├── archive-job_listing.php      Jobs archive (filter sidebar)
├── single-company.php           Single company page
├── archive-company.php          Companies grid
├── single-candidate.php         Single candidate profile
├── archive-candidate.php        Candidates grid
├── inc/
│   ├── enqueue.php              Scripts & styles
│   ├── post-types.php           Jobs / Companies / Candidates / Applications
│   ├── taxonomies.php           Categories, Types, Locations, Skills, Industries
│   ├── meta-boxes.php           Salary, location, apply email, social links, etc.
│   ├── customizer.php           Colors / hero / layout / contact info
│   ├── template-functions.php   Helpers + jobs archive query filter
│   ├── widgets.php              Recent Jobs / Job Filter / Featured Companies
│   ├── shortcodes.php           [jbportal_jobs], [jbportal_pricing], etc.
│   ├── ajax.php                 Bookmarks, newsletter, load more
│   ├── applications.php         Front-end apply + post-a-job submission
│   └── dashboard.php            Dashboard data + actions
├── templates/
│   ├── template-post-job.php
│   ├── template-dashboard.php
│   ├── template-pricing.php
│   └── template-contact.php
├── template-parts/
│   ├── content.php / content-job.php / content-company.php / job-search.php
└── assets/
    ├── css/main.css             Full theme stylesheet
    ├── css/admin.css
    └── js/main.js               Bookmarks, mobile nav, newsletter, apply form
```

## Feature parity with the reference theme

| Feature                                | jbportal |
|----------------------------------------|----------|
| Job listings CPT                       | Yes      |
| Companies CPT                          | Yes      |
| Candidates CPT                         | Yes      |
| Applications CPT                       | Yes      |
| Job alerts CPT (saved searches)        | Yes      |
| Company reviews CPT (1–5 stars)        | Yes      |
| Direct messages CPT (employer ⇄ candidate) | Yes  |
| Categories / Types / Locations / Skills / Industries / Tags | Yes |
| Hero with combined search              | Yes      |
| Job archive with advanced filters      | Yes      |
| Featured / urgent / remote badges      | Yes      |
| Bookmarks (AJAX)                       | Yes      |
| Front-end "Post a Job"                 | Yes      |
| Apply form (file upload + URL)         | Yes      |
| Application status pipeline (new → reviewing → interviewing → offered → hired/rejected) | Yes |
| Auto-expire jobs (cron + deadline)     | Yes      |
| Daily job-alert email digest (cron)    | Yes      |
| Employer / Candidate dashboard tabs    | Yes      |
| Employer & Candidate roles + capabilities | Yes   |
| Front-end company profile editor       | Yes      |
| Front-end candidate profile editor     | Yes      |
| Resume database with filters           | Yes      |
| Direct messaging in dashboard          | Yes      |
| Pricing plans                          | Yes      |
| Paid posting + featured upgrade via WooCommerce | Yes |
| Job credits per user                   | Yes      |
| Testimonials                           | Yes      |
| Contact page (wp_mail)                 | Yes      |
| Newsletter subscribe                   | Yes      |
| Customizer (colors, hero, copy)        | Yes      |
| Custom widgets                         | Yes      |
| Shortcodes                             | Yes      |
| Gutenberg block patterns + dynamic blocks | Yes   |
| One-click demo data importer           | Yes      |
| Map embed on job & company pages       | Yes      |
| Magic-link sign-in (passwordless email) | Yes     |
| Social login hooks (Google/Facebook via plugin) | Yes |
| Dark mode (auto / light / dark toggle) | Yes      |
| RTL stylesheet                         | Yes      |
| Translation-ready (.pot template)      | Yes      |
| 404, search, comments, RSS             | Yes      |

## Setup

1. Drop the `jbportal` folder into `wp-content/themes/` and activate the theme.
2. Visit Settings -> Permalinks once to flush the rewrite rules.
3. Or use **Tools → jbportal Demo Data** to one-click install sample companies, jobs, candidates and all the pages below.
4. If creating pages manually, assign these templates:
   - `Post a Job` → *Post a Job*
   - `Dashboard` → *Dashboard*
   - `Pricing` → *Pricing*
   - `Contact` → *Contact*
   - `Edit Company Profile` → *Employer Profile (Edit Company)*
   - `Edit Candidate Profile` → *Candidate Profile (Edit)*
   - `Resumes` → *Resume Database*
4. Open Appearance -> Customize -> jbportal Options to tune colors and hero copy.

## Color scheme

A deliberately distinct palette from the reference theme:

- Primary: `#0d9488` (teal)
- Secondary: `#fb7185` (coral)
- Accent: `#f59e0b` (amber)
- Dark: `#0f172a` (slate)

All four colors are editable in the Customizer.
