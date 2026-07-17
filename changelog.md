# Changelog

All notable changes to `tool_timelocker` are documented in this file.

## [0.1.0] - 2026-07-17

### Added

- Initial release: version metadata, `tool/timelocker:manage` capability,
  site default settings (session length, activities per session, show-note
  default), course-navigation link, GDPR null privacy provider, and
  language strings.
- Detection of a course's gradable activity types (any module type with an
  `itemtype = 'mod'` gradebook grade item).
- Course page for bulk-scheduling gradebook lock dates: pick an eligible
  activity type, set a schedule start, session length and activities per
  session, and select activities in a table showing each activity's current
  pending lock date.
- Applying the schedule calls `set_locktime()` on the selected activities'
  own grade item(s); core's `\core\task\grade_cron_task` enforces the lock
  when the date passes. Unselected activities can optionally have their
  lock date reset.
- Optional student-facing note, added via the Hooks API
  (`before_standard_top_of_body_html_generation`), telling students when an
  activity's grade will lock or was locked; it always reads the grade
  item's live lock state.
- Events (`locks_updated`, `locks_viewed`) and a `course_deleted` observer
  that removes the course's scheduling configuration.
- PHPUnit coverage of the scheduling core, the note hook, and privacy
  deletion; Behat coverage of the course scheduling UI and the student note.
- The session-scheduling approach is adapted from
  [Driprelease](https://moodle.org/plugins/tool_driprelease) by Marcus
  Green and from Activity dates: activities of a course are split into
  fixed-length sessions and each session is given a lock date. Unlike those
  plugins, Time locker writes native gradebook grade-item `locktime`
  directly, relying on Moodle core's own grade-lock enforcement.
