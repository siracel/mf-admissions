=== Admissions — Isabet Academy Program Finder ===
Contributors: mf
Author URI: https://mfdsgn.com/
Tags: admissions, program finder, education, school, shortcode
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An interactive, CMS-managed "Program Finder" that routes prospective families to the right program by gender and grade — built for Isabet Academy.

== Description ==

Choosing the right program is the first question every prospective family asks.
This plugin answers it in two taps: the visitor selects **who the student is**
(Boys / Girls) and **which grade or age**, and the matching program is revealed
instantly — with a clear call to action that links straight to that program's page.

No page reloads, no dead ends, and nothing hard-coded. Every gender + grade rule
is mapped to a WordPress page from the admin panel, so the admissions team can
change destinations, labels, and notes without touching code.

= Why it fits a boarding/day school =

A school with separate boys' and girls' academies, a preschool, a day-student
track, and a language school cannot present one generic "Academics" page without
confusing visitors. The Program Finder removes that friction by guiding each
family to exactly the program that applies to them.

= Key features =

* **Two-step finder** — gender, then grade/age. The result appears the moment
  both are chosen, and updates live as selections change.
* **CMS-managed decision table** — each gender + grade range maps to a page.
  First matching rule wins, so narrower ranges can sit on top.
* **Rich result card** — built automatically from the destination page: title,
  excerpt, and featured image, plus an optional **Label** (badge, e.g.
  "Full Boarding") and **Note** (e.g. a Delaware campus note or a preschool
  toilet-training requirement).
* **No dead ends** — when a range has no program (for example, a grade band a
  given gender isn't offered), a friendly "no program available — let's talk"
  card with a contact button is shown instead of an error.
* **Shareable selections** — the choice is reflected in the URL
  (`?gender=female&grade=11`), so links and campaigns can deep-link to a result.
* **Accessibility-first** — full keyboard support, focus moves to step 2 after a
  gender is chosen, `aria-pressed` state on options, `aria-live` result region,
  and `prefers-reduced-motion` support.
* **Mobile friendly** — responsive layout, 44–48px touch targets, stacked
  buttons on small screens.
* **Analytics built in** — `program_finder_step1`, `program_finder_step2`,
  `program_finder_result`, and `program_finder_cta_click` events are pushed to
  GTM `dataLayer`, `gtag`, and a DOM `CustomEvent`. URL pre-selection does not
  fire events, keeping funnel data clean.
* **Theme-safe** — all styles are scoped under `.admissions-finder`; nothing
  leaks into the surrounding theme.
* **WPBakery ready** — usable as the "Admissions — Program Finder" element or as
  a shortcode.

= Usage =

Embed the finder anywhere with the shortcode:

`[admissions_program_finder]`

If you use WPBakery Page Builder, add the **Admissions — Program Finder** element
from the Content category instead.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install the ZIP from
   **Plugins → Add New → Upload Plugin**.
2. Activate the plugin through the **Plugins** screen. A default decision table
   and display settings are created automatically.
3. Open the **Admissions** menu in the dashboard.
4. In the **Decision Table**, set the destination page for each gender + grade
   row. Optionally add a Label (badge) and a Note per row. Leave the page empty
   for any range you do not offer — the friendly "no program" card will be shown.
5. Under **Appearance & Text**, set the headings, button labels, the contact URL
   (used for the secondary "Get in Touch" button), and the grades to display.
6. Add `[admissions_program_finder]` to your Admissions page — and, ideally, the
   Academics landing page — or insert the WPBakery element.

== Frequently Asked Questions ==

= How are programs matched? =

By a decision table. Each row defines a gender and a grade range (Min–Max) and
points to a WordPress page. Rules are evaluated top to bottom and the first match
wins, so place narrower ranges above broader ones. Grade value `0` means
Preschool.

= Where does the result card content come from? =

From the destination page: its title, its excerpt (or an automatic summary of the
content if no excerpt is set), and its featured image. The optional Label and Note
come from the matching decision-table row.

= What happens when a grade band isn't offered for a gender? =

Leave that row's page empty. Instead of an error, the visitor sees a friendly
"there is currently no program available — let's talk" card with a contact button.

= Can I deep-link to a specific result? =

Yes. Append `?gender=male&grade=5` (or `grade=0` for Preschool) to the page URL.
The finder pre-selects that choice without firing analytics events.

= Does it work without WPBakery? =

Yes. The `[admissions_program_finder]` shortcode works in any page or post. The
WPBakery element is an optional convenience.

= How do I reset everything to the defaults? =

On the Admissions settings screen, use **Restore defaults**. This rebuilds the
decision table and settings (it does not delete your pages).

= Which analytics events are sent? =

`program_finder_step1` (gender chosen), `program_finder_step2` (grade chosen),
`program_finder_result` (a card was shown), and `program_finder_cta_click` (a CTA
was clicked). Each carries the relevant `gender`, `grade`, and `href` data and is
sent via `dataLayer`, `gtag`, and a `admissions:<event>` DOM CustomEvent.

== Screenshots ==

1. The two-step finder: choose gender, then grade/age.
2. A result card with featured image, program title, badge, and note.
3. The CMS decision table where each gender + grade range maps to a page.

== Changelog ==

= 1.1.0 =
* New: result card enriched with the destination page's featured image, plus an
  optional per-rule Label (badge) and Note.
* New: friendly "no program available" card (with contact CTA) whenever a rule
  has no page assigned — used for valid no-program ranges.
* New: keyboard focus moves to step 2 after the gender is selected.
* Change: default girls rules split into Middle School (6–8) and High School
  (9–12) to reflect the Delaware High School campus; seeded badges and notes.
* Change: URL pre-selection no longer fires analytics events.
* Fix: HTML entities in page titles/excerpts (e.g. `&#8217;`) are no longer
  double-encoded on the result card.
* Remove: dropped the unused "Programs" custom post type — the decision table is
  page-based.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.1.0 =
Richer result cards (image, badge, note), a friendlier no-program state, and a
fix for double-encoded characters in titles. After updating, purge any page/JS
cache so the new front-end assets are served.
