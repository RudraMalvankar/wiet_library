# Full Automated Cybersecurity + Functional Audit Report

Date: 2026-04-02
Workspace: C:/xampp/htdocs/wiet_lib
Execution mode: Automated static + runtime HTTP + mobile dependency/type checks
Primary run timestamp: 2026-04-02_224329

## 1) Executive Summary

A full automated audit was executed across the codebase, including:
- PHP syntax validation across all PHP files.
- Functional HTTP endpoint matrix for root, admin pages/APIs, student pages, and mobile APIs.
- Static cybersecurity pattern scanning.
- CSRF heuristic scan for server-rendered forms.
- Student mobile app TypeScript and npm dependency audit.

Headline result:
- Build/syntax health is good.
- Mobile dependency risk is currently clean.
- Security hardening and auth/redirect behavior still need work.

## 2) Key Metrics

From full_audit_summary_2026-04-02_224329.json:
- PHP files checked: 152
- PHP syntax failures: 0
- HTTP checks executed: 95
- HTTP fatal-signature hits: 2 (heuristic; likely false positives from page content)
- HTTP 2xx: 24
- HTTP 4xx: 35
- HTTP 5xx: 0
- Security pattern hits: 23
- Forms detected: 24
- Forms missing CSRF marker (heuristic): 19
- Mobile TypeScript check: PASS (exit 0)
- Mobile npm audit: PASS (0 vulnerabilities)

## 3) Artifacts Generated (Automated-Test)

- full_audit.ps1
- full_audit_summary_2026-04-02_224329.json
- full_php_lint_2026-04-02_224329.csv
- full_http_matrix_2026-04-02_224329.csv
- full_security_patterns_2026-04-02_224329.csv
- full_form_csrf_heuristic_2026-04-02_224329.csv
- full_mobile_tsc_2026-04-02_224329.txt
- full_mobile_npm_audit_2026-04-02_224329.json

## 4) Functional Testing Findings

### 4.1 Positive

- Root pages tested and reachable (200):
  - index.php
  - opac.php
- No HTTP 5xx observed in the automated matrix.
- Student mobile API contract behavior appears consistent:
  - Protected endpoints return 401 without token.
  - Method-protected endpoints return 405 when incorrect method used.

### 4.2 Issues / Instability Signals

- 36 targets returned status = -1 during HTTP probing.
- Typical body sample for these cases: "Operation is not valid due to the current state of the object."
- Affected mostly protected admin/student pages where redirect/session behavior likely caused web request state handling issues during non-browser probing.

Interpretation:
- These are not confirmed code crashes.
- They indicate redirect/auth flow handling is not cleanly observable by this non-authenticated probe style and should be validated with browser/session-aware E2E tests.

### 4.3 Fatal Heuristic Rows

Two pages were flagged by body-signature heuristic:
- admin/circulation.php
- admin/settings.php

Manual interpretation:
- Response snippets look like normal HTML/CSS output.
- These are likely false positives from broad signature matching and should not be treated as confirmed runtime fatals.

## 5) Cybersecurity Findings (Static)

Summary by rule:
- weak_hash: 11
- debug_leak: 9
- dangerous_function: 2
- sql_concat_risk: 1

### 5.1 High-Priority

1. Command execution present in backup/restore API:
- admin/api/backup-restore.php:130
- admin/api/backup-restore.php:324
- Pattern: exec($command, ...)
- Risk: command injection if command construction is not strictly validated.

2. SQL dynamic concatenation risk:
- admin/api/bulk-import.php:240
- Pattern: DESCRIBE query built using concatenated table name.
- Risk: SQL injection if table name source is not strictly allowlisted.

### 5.2 Medium-Priority

3. Debug disclosure markers found in runtime-related files:
- admin/ajax-handler.php:19 (display_errors enabled)
- admin/debug-circulation-api.php:33 (print_r session)
- footfall/test-checkin.php, footfall/api/test-records.php, footfall/api/test-stats.php
- Risk: information leakage in non-production contexts if exposed.

4. Weak hash usage (md5/sha1) appears in multiple files.
- Many usages appear non-password related (cache keys, identifiers, QR helper data).
- Recommendation: keep non-crypto contexts isolated and avoid using weak hashes for anything security-sensitive.

## 6) CSRF Coverage Heuristic

- 24 files contained forms.
- 19 files did not show explicit CSRF marker tokens by heuristic scan.

Important note:
- This is a heuristic. Some pages may use centralized AJAX/CSRF handling not visible as inline hidden inputs.
- Still, this is a strong signal to perform targeted verification on all POST/PUT/DELETE form submissions.

## 7) Mobile App Security and Functional Baseline

- TypeScript check: pass (npx tsc --noEmit)
- npm audit: no known vulnerabilities in current dependency graph

## 8) Overall Risk Rating

Current automated assessment: Moderate

Why:
- No syntax breaks and no direct HTTP 5xx in this run.
- But command execution usage, dynamic SQL concatenation risk, debug markers, and large CSRF uncertainty keep production risk above low.

## 9) Prioritized Remediation Plan

P0 (Immediate)
1. Harden command execution paths in admin/api/backup-restore.php:
- Use strict allowlist for executable and args.
- Escape/validate all dynamic parts.
- Restrict endpoint to highest-privilege admin role.

2. Fix SQL concatenation in admin/api/bulk-import.php:
- Enforce strict allowlist of table names before query execution.
- Avoid direct concatenation from request-controlled values.

P1 (Short-term)
3. Disable debug output in production paths:
- Remove or gate display_errors, print_r, and test endpoints.
- Restrict test/debug files from web access.

4. CSRF validation hardening:
- Verify token generation/validation coverage for all state-changing forms and API endpoints.
- Standardize one CSRF middleware pattern across admin/student flows.

P2 (Hardening)
5. Replace md5 usage where security semantics may apply.
- Keep only in non-security contexts (cache/file dedupe).
- Use stronger alternatives when integrity/security is expected.

6. Add authenticated browser E2E regression suite:
- Login flows (admin/student)
- CRUD for books/members
- Circulation/fines
- Mobile API auth + protected endpoint behavior

## 10) Limitations of This Automated Run

- Most runtime probes were unauthenticated by design.
- Redirect/session-heavy pages may return non-actionable status -1 under Invoke-WebRequest with redirect disabled.
- Static pattern scanning indicates risk signals, not proof of exploitability.

## 11) Conclusion

The codebase is stable at syntax/type and dependency levels, but not yet fully security-hardened for production without additional remediation around command execution controls, SQL dynamic query hardening, debug exposure cleanup, and CSRF coverage verification.
