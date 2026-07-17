# Time locker (`tool_timelocker`)

A Moodle admin tool that bulk-schedules gradebook **lock-after dates** across
a course's gradable activities on a timed-session basis. Pick a module type
(quizzes, assignments, and any other activity with a gradebook grade item),
split the activities into sessions, and the tool writes each session's lock
date straight into the activities' own grade items' native `locktime` — core's
grade-lock enforcement takes it from there, no custom locking engine
involved.

## Requirements

- Moodle 5.0 or later

## Installation

1. Copy the plugin directory into `<moodleroot>/public/admin/tool/timelocker/`
2. Visit Site administration → Notifications to run the upgrade

## How it works

From a course's administration menu, a teacher or manager opens **Time
locker**, picks an activity type, and sets a schedule start date, session
length (in days) and how many activities go in each session. The tool splits
the course's activities of that type into sessions in course order, and for
each selected activity writes a lock date onto the end of its session window.

Applying the schedule calls `set_locktime()` on every `itemtype = 'mod'` grade
item belonging to the selected activities. It does not lock anything itself:
core's `\core\task\grade_cron_task` scheduled task periodically scans for
grade items whose `locktime` has passed and flips them to `locked`, exactly
as if a teacher had set the lock date by hand in the gradebook. This mirrors
how [Activity dates](https://github.com/adamjenkins/moodle-tool_activitydates)
writes native module open/close dates rather than reinventing availability.

Activities left unselected are untouched by default, but can optionally have
their lock date cleared ("Reset unselected").

Each course keeps a single Time locker schedule. Switching to a different
activity type and saving replaces the previous selection: any student notes
for the previously selected activities stop showing, although lock dates that
were already written to those activities' grade items remain in place (clear
them in the gradebook, or via "Reset unselected", if no longer wanted).

## Student note

Optionally, an activity can show a note on its own page telling students when
its grade will lock (or that it has already locked). The note is added via
the Hooks API and always reflects the grade item's *live* `locktime`/`locked`
state — so it stays accurate even if a teacher subsequently changes the lock
date directly in the gradebook.

Where the note appears depends on the Moodle version, as the header-extras
region it prefers (`$PAGE->add_header_extras()`) was only added in Moodle 5.2:

| Moodle | Note placement |
| --- | --- |
| 5.2 and later | Near the activity's dates, in the same region as other activity-page notices |
| 5.0, 5.1 | At the top of the activity page |

## Settings

Site administration → Plugins → Admin tools → Time locker provides site-wide
defaults for the scheduling form: session length, activities per session, and
whether the student-facing note is shown by default for newly selected
activities.

## Privacy

This plugin stores only course-level scheduling configuration (which activity
type, session settings, which activities are selected, and their note
toggles) — it does not store or process any personal user data, and
implements Moodle's privacy `null_provider`.

## Acknowledgement

This plugin was inspired by, and adapts the session-scheduling approach of,
[Driprelease](https://moodle.org/plugins/tool_driprelease) by Marcus Green,
and of [Activity dates](https://github.com/adamjenkins/moodle-tool_activitydates).
Where those plugins control access or write module-level open/close dates,
Time locker writes native gradebook grade-item lock dates instead, letting
Moodle core's own grade-lock enforcement do the work.

## License

GNU GPL v3 or later — https://www.gnu.org/licenses/gpl-3.0.html
