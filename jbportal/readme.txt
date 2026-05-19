=== jbportal ===

Contributors: jbportal
Tags: job-board, jobs, employment, recruitment, custom-post-types, responsive-layout, custom-colors, translation-ready
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A modern, full-featured job board WordPress theme.

== Description ==

jbportal is a clean, modern job-board theme inspired by the layout patterns of popular job platforms but
rebuilt from scratch with a fresh teal + coral color scheme. It bundles every core feature you need to
run a job marketplace:

* Custom post types: Jobs, Companies, Candidates, and Applications
* Custom taxonomies: Job categories, types, locations, tags, industries and skills
* Advanced search and filter on the jobs archive (keyword, location, category, type, remote, salary)
* Single job page with apply form (file upload + URL), salary, location, skills and related jobs
* Single company page with all open positions and contact details
* Featured candidates archive and single profile
* Front-end "Post a Job" submission (auto-draft or publish based on user role)
* Employer / Candidate dashboard with tabs for Jobs, Applications, Bookmarks and Profile
* Bookmarks (AJAX, persisted per user with local cache)
* Pricing page with three plans (Starter / Growth / Enterprise)
* Contact page template (wp_mail powered)
* Newsletter subscribe (AJAX)
* Customizer panel for colors, hero copy, layout and contact info
* Three custom widgets: Recent Jobs, Job Filter, Featured Companies
* Six shortcodes: `[jbportal_jobs]`, `[jbportal_companies]`, `[jbportal_categories]`, `[jbportal_job_search]`, `[jbportal_pricing]`, `[jbportal_stats]`, `[jbportal_testimonials]`
* Page templates: Post a Job, Dashboard, Pricing, Contact
* Responsive layout with sticky header and mobile menu
* WooCommerce-ready, translation-ready, GPL licensed

== Setup ==

1. Activate the theme.
2. Visit Settings → Permalinks once to flush rewrite rules.
3. Create the following pages and assign the matching page templates:
   * "Post a Job" → template "Post a Job" (slug `post-a-job`)
   * "Dashboard"  → template "Dashboard"  (slug `dashboard`)
   * "Pricing"    → template "Pricing"    (slug `pricing`)
   * "Contact"    → template "Contact"    (slug `contact`)
4. Tweak colors and hero copy under Appearance → Customize → jbportal Options.

== Changelog ==

= 1.0.0 =
* Initial release.
