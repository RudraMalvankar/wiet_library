# WIET Library Requirements Implementation Audit

Date: 2026-04-20
Scope: Audit of 23 requested points against current WIET Library codebase.
Method: Source-code verification across landing page, admin modules, APIs, and shared functions.

---

## Executive Summary

- Total points audited: 23
- Done: 13
- Partial: 5
- Not done: 5

Status meaning:

- Done: Implemented in code and behavior is available.
- Partial: Implemented in part, but requirement is not fully met.
- Not done: No complete implementation found, or major missing pieces remain.

---

## Point-by-Point Status

1. Institute Library

- Status: Done
- Notes: Public landing page is implemented as WIET Library home/about.

2. 150+ seats

- Status: Done
- Notes: Landing page shows 150+ study seats.

3. Digital Learning Center photos

- Status: Done
- Notes: Dedicated Digital Learning Center section with image exists.

4. Study Area photos with students

- Status: Partial
- Notes: Study area section/image exists, but no strict guarantee the image contains students.

5. Research Technical Journal photos

- Status: Partial
- Notes: Research resources/journal section exists, but requirement asks for specific technical journal photos.

6. Administration staff info with photos

- Status: Partial
- Notes: Staff cards/info exist, but current images are placeholders.

7. Timings, Mail, Charges, Phone no. and extension no.

- Status: Partial
- Notes: Timings, email, and phone exist. Charges and extension number fields are missing.

8. WIET Library (remove "clg")

- Status: Not done
- Notes: Multiple screens still use WIET College Library naming.

9. Overdue Books

- Status: Done
- Notes: Overdue stats and logic implemented in books/circulation/reports APIs and UI.

10. Computer -> Computer Engineering

- Status: Done
- Notes: Mapping/usage of Computer Engineering is present across student and footfall flows.

11. Member No: 25176 -> 251076

- Status: Not done
- Notes: No dedicated migration/padding rule found for this exact conversion.

12. Variable length change from 5 to 8

- Status: Not done
- Notes: No global 8-digit membership-number enforcement found.

13. Example: 25176 -> 25100076 (Library Membership No)

- Status: Partial
- Notes: Membership number concept exists, but this target formatting policy is not implemented system-wide.

14. Dead stock / Unavailable books physically not in library

- Status: Done
- Notes: Holding statuses include Unavailable/Dead Stock/Lost/Damaged with counters and stats.

15. Lost/Damaged books should not be returned

- Status: Done
- Notes: Normal return flow blocks Lost/Damaged/Dead Stock conditions.

16. Date for when unavailable books will be available should be notified

- Status: Done
- Notes: Availability notification flow exists using expected available date and notes.

17. Daily and Total Issued Books Report and Statistics

- Status: Done
- Notes: Daily, range total, and grand total issued reporting implemented.

18. Fine payment condition in Return Books Management

- Status: Done
- Notes: Overdue return requires fine payment confirmation + receipt no + date + amount + QR reference.

19. Due date auto-generation from issue date

- Status: Done
- Notes: Due date is auto-calculated from entered issue date (+15 days).

20. Reset form not working in Circulation

- Status: Done
- Notes: Reset functions exist for issue and return forms and are wired to UI.

21. Inactive members should not be able to issue books

- Status: Done
- Notes: Both frontend and backend block issue for inactive members.

22. QR code mismatch should alert and block issue

- Status: Done
- Notes: Frontend and API include mismatch detection and block issuing.

23. Additional Features

- 23.1 Biometric Login System -> Footfall
  - Status: Not done
  - Notes: Footfall currently supports QR/manual check-in, not biometric authentication.
- 23.2 Library Footfall Visits -> Duration
  - Status: Done
  - Notes: Duration is calculated and reported on checkout and analytics views.
- 23.3 Year-wise validity according to Drop Year should be updated
  - Status: Not done
  - Notes: Validity management exists, but no DropYear-based automatic validity policy found.

---

## What Is Not Done Yet and How to Implement

### A) Branding cleanup (Point 8)

Current gap:

- System still uses "WIET College Library" text in multiple UI templates and messages.

Implementation:

- Standardize naming strings in landing, admin layout, student layout, settings defaults, and notification templates.

Codebase impact:

- Low to medium.
- Mostly template string updates and content harmonization.

DB impact:

- Low.
- Possibly update one settings value if library name is stored in DB-backed settings.

---

### B) Membership number policy migration to 8 digits (Points 11, 12, 13)

Current gap:

- No single enforced policy for converting old member numbers to required 8-digit format.
- New member creation currently increments numeric max without enforcing target 8-digit pattern.

Implementation:

1. Finalize canonical format rule (example-driven):
   - Existing 5-digit number to transformed 8-digit number (for example 25176 -> 25100076).
2. Introduce one shared formatter/validator in backend.
3. Update all create/import/update flows to use this formatter.
4. Run controlled migration script for existing records.
5. Update QR payload format if member number format is embedded.
6. Regression-test all lookups, joins, and reports.

Codebase impact:

- High.
- Affects members API, student management, circulation, footfall, mobile APIs, notification targeting, and any member-number search logic.

DB impact:

- High to very high (depends on current schema type of MemberNo and foreign key usage).
- MemberNo is a key reference across multiple tables; migration must preserve referential integrity.
- Must plan transactional migration and rollback strategy.

Risk:

- High if changed directly in production without staged migration.

---

### C) Biometric login for footfall (Point 23.1)

Current gap:

- Footfall flow is QR/manual only; no biometric device integration.

Implementation:

1. Select biometric mode (fingerprint scanner SDK/device or external biometric service).
2. Add enrollment workflow for members.
3. Add verification endpoint for scanner station.
4. Add fallback flow (QR/manual) for failures.
5. Add audit logs and security controls.

Codebase impact:

- High.
- New modules for enrollment, verification, device communication, and admin management UI.

DB impact:

- Medium to high.
- New biometric-related tables and audit trail metadata.

Compliance/security impact:

- High.
- Requires encryption, strict access controls, and data-retention policy.

---

### D) DropYear-based year-wise validity updates (Point 23.3)

Current gap:

- Validity operations exist, but no automatic policy tied to DropYear.

Implementation:

1. Define policy:
   - Example: ValidTill computed from admission year/program/drop year logic.
2. Add required fields if missing (DropYear, program duration or rules table).
3. Implement scheduled updater (daily/weekly) to recalculate validity and statuses.
4. Enforce in circulation eligibility checks.

Codebase impact:

- Medium to high.
- Student management, issue eligibility, possibly login and notification modules.

DB impact:

- Medium.
- New fields/tables/indexes and possibly background job tracking.

Operational impact:

- Medium.
- More members may become auto-inactive until renewed.

---

### E) Content completeness on landing page (Points 4, 5, 6, 7 partials)

Current gaps:

- Need final real photos (students, technical journals, actual staff photos).
- Need charges and extension number in contact block.

Implementation:

- Replace placeholder assets and copy with approved final content.
- Add Charges and Extension fields in contact/footer section.

Codebase impact:

- Low.
- Primarily static content and assets.

DB impact:

- None, unless made editable through settings.

---

## Suggested Implementation Order (Low Risk to High Risk)

1. Landing-page content completion and naming cleanup.
2. DropYear validity policy implementation.
3. Membership number migration to 8-digit standard.
4. Biometric footfall integration.

---

## Testing Checklist for Pending Work

- Branding/content
  - Verify all public/admin/student pages use approved naming.
  - Verify contact block includes timings, mail, charges, phone, extension.

- Membership number migration
  - Validate all CRUD and lookup paths with old and new member numbers.
  - Validate QR issue/scan flows post-migration.
  - Validate joins and reports for referential consistency.

- DropYear validity
  - Validate policy computation for sample cohorts.
  - Validate auto-status updates and issue denial behavior.

- Biometric
  - Verify enrollment, match/no-match, device-offline fallback, and audit logging.

---

## Conclusion

The system is strong in circulation controls, overdue/fine handling, unavailable/dead-stock tracking, QR mismatch protection, and reporting.
The main pending items are policy-level and integration-level changes: branding standardization, 8-digit membership-number migration, biometric footfall authentication, and DropYear-based validity automation.
