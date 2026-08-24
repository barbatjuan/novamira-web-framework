# Gallery Bootstrap Integrity Specification

## Purpose

Defines the guarantees that keep the generated HTML-mockup gallery
(`skills/html-mockup/assets/gallery/index.html`) available and correct once
its build output is no longer version-tracked: the framework-audit gate that
detects a missing build, the documentation that tells a contributor to
produce it, and the untrack-but-retain guarantee for the artifact itself.

**Note on capability classification.** `openspec/specs/` currently holds no
capability domains (only `.gitkeep`), and no `SKILL.md` behavior contract
changes in this proposal — `skills/html-mockup/SKILL.md` is explicitly
unmodified, and `skills/framework-audit/SKILL.md` delegates individual audit
rows to the script ("Add checks there, never in prose here"), so no existing
skill contract is touched. On that basis this spec agrees with the
proposal's "Modified Capabilities: None."

It diverges on one narrow point: `RT_GALLERY_NOT_BUILT` is a new, permanent,
testable assertion the framework-audit tool will make about every audited
repository from now on, runs inside the standing test chain
(`openspec/config.yaml` `testing.test_runner`), and exists specifically to
close a silent-failure mode this same change introduces. That is durable
system behavior worth a baseline, not a one-off git operation — unlike the
branch fast-forward, PR, or CodeGraph init, which are non-repeating actions
with no ongoing behavior to assert and are intentionally left out of this
spec. It is captured below as an **ADDED** capability, `gallery-bootstrap-integrity`.

## Requirements

### Requirement: Gallery Build Detection Gate

`framework-audit.php` MUST report a FAIL-level `RT_GALLERY_NOT_BUILT` row
when the gallery generator (`skills/html-mockup/assets/gallery/_build-gallery.php`)
is present in the audited root and its `index.html` output is absent from
that root. It MUST NOT emit this row when the generator itself is absent,
regardless of `index.html`'s presence or absence.

#### Scenario: Generator present, output missing
- GIVEN an audited root containing `_build-gallery.php`
- AND no `index.html` under `skills/html-mockup/assets/gallery/`
- WHEN `framework-audit.php` runs
- THEN it emits a FAIL row `RT_GALLERY_NOT_BUILT`
- AND the message names `php skills/html-mockup/assets/gallery/_build-gallery.php` as the fix

#### Scenario: Generator present, output built
- GIVEN an audited root containing both `_build-gallery.php` and `index.html`
- WHEN `framework-audit.php` runs
- THEN it does not emit `RT_GALLERY_NOT_BUILT`

#### Scenario: Generator absent (test fixture root)
- GIVEN an audited root with no `_build-gallery.php` (e.g. an `fx_gal()` fixture that writes only `index.html`)
- WHEN `framework-audit.php` runs
- THEN it does not emit `RT_GALLERY_NOT_BUILT`, regardless of whether `index.html` exists

### Requirement: Gallery Bootstrap Documentation

The gallery build command MUST be present and discoverable in `README.md`
under `## Install`, alongside the existing `install.ps1` / `install.sh`
steps and the "Re-run to update after `git pull`" line. `README.md`
`## Install` carries no test or audit step, so no ordering claim applies
there. The ordering obligation binds in `CONTRIBUTING.md` `## Testing a
change` instead: the gallery build command MUST appear before the `&&`
test chain (`CONTRIBUTING.md:225`), and `RT_GALLERY_NOT_BUILT` MUST be
listed as a row in the audit row table there.

#### Scenario: README install discoverability
- GIVEN a contributor reading `README.md` `## Install`
- WHEN they look for how to get a working local copy of the framework
- THEN the gallery build command is present in that section, alongside
  `install.ps1` / `install.sh` and the "Re-run to update after `git pull`" line

#### Scenario: CONTRIBUTING testing chain order
- GIVEN a contributor reading `CONTRIBUTING.md` `## Testing a change`
- WHEN they read the section from top to bottom
- THEN the gallery build command appears before the `&&` test chain at line 225

#### Scenario: CONTRIBUTING row table entry
- GIVEN a contributor reading the audit row table in `CONTRIBUTING.md`
- WHEN they scan it for gallery-related rows
- THEN `RT_GALLERY_NOT_BUILT` is listed with its trigger condition and fix command

### Requirement: Gallery Artifact Remains On Disk After Untracking

`skills/html-mockup/assets/gallery/index.html` MUST NOT be tracked by git
after this change, and MUST remain present on the local filesystem.

#### Scenario: Untrack preserves the file
- GIVEN the gallery `index.html` is removed from the git index via `git rm --cached`
- AND `.gitignore` lists the gallery `index.html` path
- WHEN `git ls-files` is run
- THEN it does not list the gallery `index.html`
- AND the file still exists on disk at its original path
