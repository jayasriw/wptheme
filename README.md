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

| Feature                              | jbportal |
|--------------------------------------|----------|
| Job listings CPT                     | Yes      |
| Companies CPT                        | Yes      |
| Candidates CPT                       | Yes      |
| Applications CPT                     | Yes      |
| Categories / Types / Locations / Skills | Yes   |
| Hero with combined search            | Yes      |
| Job archive with advanced filters    | Yes      |
| Featured / urgent / remote badges    | Yes      |
| Bookmarks (AJAX)                     | Yes      |
| Front-end "Post a Job"               | Yes      |
| Apply form (file upload + URL)       | Yes      |
| Employer / Candidate dashboard       | Yes      |
| Pricing plans                        | Yes      |
| Testimonials                         | Yes      |
| Contact page (wp_mail)               | Yes      |
| Newsletter subscribe                 | Yes      |
| Customizer (colors, hero, copy)      | Yes      |
| Custom widgets                       | Yes      |
| Shortcodes                           | Yes      |
| 404, search, comments, RSS           | Yes      |

## Setup

1. Drop the `jbportal` folder into `wp-content/themes/` and activate the theme.
2. Visit Settings -> Permalinks once to flush the rewrite rules.
3. Create the following pages and assign matching templates:
   - `Post a Job` -> *Post a Job*
   - `Dashboard` -> *Dashboard*
   - `Pricing` -> *Pricing*
   - `Contact` -> *Contact*
4. Open Appearance -> Customize -> jbportal Options to tune colors and hero copy.

## Color scheme

A deliberately distinct palette from the reference theme:

- Primary: `#0d9488` (teal)
- Secondary: `#fb7185` (coral)
- Accent: `#f59e0b` (amber)
- Dark: `#0f172a` (slate)

All four colors are editable in the Customizer.
