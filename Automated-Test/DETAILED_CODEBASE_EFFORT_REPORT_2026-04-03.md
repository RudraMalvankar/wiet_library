# Detailed Codebase and Effort Report

Date: 2026-04-03
Workspace: wiet_lib

## 1) Executive Summary

This repository is a mixed web + mobile system with a large dependency footprint.

- Total files in repository (including dependencies): 30,607
- Source-like files (including dependencies): 17,337
- Total repository size (including dependencies): 285.37 MB
- Practical app modules analyzed deeply: 7 modules
- Practical app code analyzed deeply: 176 files, 71,151 lines, 633 estimated function definitions

## 2) Codebase Inventory (Top-Level)

Detected major codebases/modules:

1. Admin portal (PHP + JS): admin/
2. Student portal (PHP): student/
3. Footfall system (PHP + JS): footfall/
4. Mobile app (React Native/TypeScript): student-mobile-app/src/
5. Shared backend utilities: includes/
6. Database scripts/migrations/tools: database/
7. Chatbot widget/API: chatbot/

Additional root/public files also exist (index.php, opac.php, developer.php, dropbox.php, docs).

## 3) Repository Metrics (Including Dependencies)

- Total files: 30,607
- Source-like files: 17,337
- Size: 285.37 MB

### Extension distribution (including dependencies)

- .js: 9,996 files
- .ts: 4,482 files
- .md: 1,149 files
- .json: 1,002 files
- .tsx: 408 files
- .php: 154 files
- .html: 60 files
- .ps1: 55 files
- .sql: 18 files
- .css: 7 files
- .bat: 6 files

### Largest directories by file count (including dependencies)

- student-mobile-app/: 29,893 files
- libs/: 426 files
- admin/: 68 files
- md files/: 59 files
- student/: 43 files
- Automated-Test/: 24 files
- database/: 22 files
- images/: 17 files
- footfall/: 13 files

## 4) Practical Engineering Metrics (Core App Modules)

The table below excludes dependency-heavy folders and focuses on your main implementation areas.

| Module                 |   Files |      Lines | PHP Functions | JS/TS Functions | Total Functions |
| ---------------------- | ------: | ---------: | ------------: | --------------: | --------------: |
| admin                  |      67 |     48,937 |           467 |              46 |             513 |
| student                |      42 |     13,747 |            65 |               0 |              65 |
| database               |      22 |      3,313 |             8 |               0 |               8 |
| footfall               |      12 |      1,883 |             9 |               0 |               9 |
| student-mobile-app/src |      24 |      1,752 |             0 |               7 |               7 |
| includes               |       4 |      1,018 |            30 |               0 |              30 |
| chatbot                |       5 |        501 |             1 |               0 |               1 |
| **Total**              | **176** | **71,151** |       **580** |          **53** |         **633** |

## 5) What Was Fixed in This Session Scope (High-Level)

Based on validated changes and behavior checks:

- Books Management:
  - Add Book modal flow stabilized
  - Duplicate/orphan form block removed
  - payload compatibility and validation improvements
  - View Book modal positioning/viewport fixes
- Export PDF:
  - fixed invalid content-header behavior
  - print-to-PDF rendering and pagination improvements
- Footfall:
  - check-in messaging logic improved for already-active users
  - active visitors loading bug fixed (SQL ambiguity)
  - folder root now opens scanner directly
- Layout:
  - content and modal viewport fit adjustments

## 6) Time and Effort Matrix

Important: exact engineering hours cannot be computed perfectly without a time-tracking system.
The estimates below are evidence-based from task complexity, number of affected modules, and observed file modification windows.

### 6.1 Observed recent edit window (from key file timestamps)

- Earliest in recent batch: 2026-04-03 12:38:46
- Latest in recent batch: 2026-04-03 12:55:51
- Observed active batch duration: ~17 minutes (latest patch burst only)

### 6.2 Estimated total effort for the full multi-issue stabilization work

| Phase                             | Scope                                               |       Estimated Hours |
| --------------------------------- | --------------------------------------------------- | --------------------: |
| Diagnosis and root-cause analysis | cross-module debugging, API/UI tracing              |             3.0 - 4.5 |
| Books workflow fixes              | modal/layout, add flow, payload normalization       |             3.5 - 5.0 |
| Reports/export fixes              | PDF export flow + print layout adjustments          |             1.5 - 2.5 |
| Footfall workflow fixes           | scan/check-in behavior, API query fixes, routing    |             2.5 - 4.0 |
| Validation and regression checks  | lint checks, endpoint probes, UI verification loops |             2.0 - 3.0 |
| Documentation/reporting overhead  | summaries, diagnostics, status writeups             |             1.0 - 2.0 |
| **Estimated Total**               |                                                     | **13.5 - 21.0 hours** |

## 7) Risk and Maintenance Notes

- Large dependency footprint (especially student-mobile-app) can hide signal in repository-wide metrics.
- books-management.php is still a high-complexity file and a refactor target.
- Some third-party/library code may still show future PHP deprecation warnings; those can be handled separately.

## 8) Recommended Next Metric Upgrades (If You Want Even More Detail)

1. Add automated metrics script to output weekly trend (files, LOC, function counts).
2. Track exact work hours using Git commit timestamps + task tags.
3. Add module-level test coverage percentage to this report.
4. Add bug-reopen rate and mean-time-to-fix (MTTR) for operations dashboard.

## 9) Methodology Note

Function counts are regex-based estimates (not AST-level parsing), so they are suitable for planning and complexity comparisons, not compiler-grade exactness.
