# Changes

## v0.1.1

**Final release. Time locker has moved to Activity dates.** Its features are
now the **Grade locks** tab of `tool_activitydates` 2.0 and later. The README
explains how to switch. Settings are not migrated, and lock dates already in the
gradebook stay in force.

- When `tool_activitydates` 2.0+ already shows a student lock note for an
  activity, Time locker no longer adds its own, so students see one note. This
  applies on the activity page and on the course page.
- New: an "Also show notes on the course page" option (per course, with a site
  default, off by default). Adds an upgrade step.
- Fixed: the activity table, and the order in which lock dates are assigned,
  now follow the course page. Activities inside a subsection are listed where
  the subsection sits.
