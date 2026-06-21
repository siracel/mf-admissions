=== Admissions — Isabet Academy Program Finder ===
Contributors: mf
Tags: admissions, program finder, education, shortcode
Requires at least: 6.0
Tested up to: 6.5
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A gender + grade based Program Finder for the Isabet Academy Admissions page.

== Description ==

The visitor answers "Who is the student?" (boy/girl) and "Which grade / age?",
and the plugin instantly shows the matching program card and a CTA that links to
the relevant page. There is no page reload (SPA behavior), and selections can be
carried in the URL (`?gender=male&grade=5`).

= Highlights =

* The decision table is NOT hard-coded — it is edited from the admin panel.
* Each gender + grade range maps directly to a WordPress page. The result card is
  built from that page: title, auto excerpt, and its featured image.
* Each rule can carry an optional Label (badge, e.g. "Full Boarding") and an
  optional Note (e.g. "High School is at our Delaware campus." or a Preschool
  toilet-training requirement) shown on the result card.
* When a rule has no page assigned (e.g. girls grades 3–5, where no boarding is
  offered), a friendly "no program available — let's talk" card with a contact
  CTA is shown instead of a dead end.
* Accessible: keyboard navigable, focus moves to step 2 on selection,
  `aria-pressed`, `aria-live="polite"`, `role="group"`.
* Mobile friendly: vertical stacking, minimum 44–48px touch targets,
  `prefers-reduced-motion` support.
* Analytics: `program_finder_step1`, `program_finder_step2`, `program_finder_result`,
  and `program_finder_cta_click` events are sent via dataLayer / gtag / DOM
  CustomEvent. URL pre-selection does NOT fire analytics events.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` (or upload the ZIP).
2. Activate the plugin. The default decision table and settings are created
   automatically.
3. Go to the "Admissions" menu and, in the Decision Table, assign the destination
   page for each gender + grade row. Optionally fill in a Label and Note per row.
4. Add the `[admissions_program_finder]` shortcode to the Admissions (and/or
   Academics) page. If you use WPBakery, you can also pick the
   "Admissions — Program Finder" element.

== Changelog ==

= 1.1.0 =
* Result card enriched: featured image, optional per-rule Label (badge) and Note.
* Friendlier "no program available" card (with contact CTA) when a rule has no
  page — used for valid no-program cases such as girls grades 3–5.
* Default girls rules split into Middle School (6–8) and High School (9–12) to
  reflect the Delaware High School campus; seeded badges and notes per the brief.
* Keyboard focus now moves to step 2 after the gender is chosen.
* URL pre-selection (`?gender=&grade=`) no longer fires analytics events.
* Removed the unused "Programs" custom post type (decision table is page-based).

= 1.0.0 =
* Initial release.
