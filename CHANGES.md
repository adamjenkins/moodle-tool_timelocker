# Changes

## v0.1.0

First public release.

- Bulk-schedules gradebook lock dates ("lock after") across a course's
  gradable activities, grouping them into timed sessions (start date,
  session length in days, activities per session) — the same
  session-staggering model as Driprelease and Activity dates.
- Writes native grade-item `locktime`; Moodle core's `grade_cron_task`
  enforces the lock, so no custom locking engine is involved.
- Optional student-facing note on the activity page showing when its grade
  will lock (or that it has already locked), added via the Hooks API and
  always reflecting the grade item's live lock state.
- Site default settings (session length, activities per session, show-note
  default), `tool/timelocker:manage` capability, course-navigation link,
  GDPR null privacy provider, events, and course-deletion cleanup.
- PHPUnit and Behat test coverage.
