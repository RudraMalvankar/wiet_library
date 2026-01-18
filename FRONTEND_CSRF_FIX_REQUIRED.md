# Frontend CSRF Token Fix Required

**Date**: January 3, 2026  
**Priority**: HIGH - These pages will get 403 errors on form submission  
**Status**: Partially Fixed (1/3 complete)

---

## Issue Summary

Three admin pages are making POST requests without CSRF tokens, causing 403 Forbidden responses from the API.

---

## Files Status

### ✅ FIXED: admin/student-management.php

- Added CSRF token variable: `let csrfToken = null;`
- Added fetch function: `fetchCSRFToken()`
- Added to DOMContentLoaded: `fetchCSRFToken();`
- Fixed 3 POST requests:
  - `saveStudent()` - Line ~1710
  - `saveStudentInline()` - Line ~1736
  - `deleteStudent()` - Line ~1839

### ⚠️ NEEDS FIX: admin/members.php

**Add at beginning of script section (before line 1027)**:

```javascript
let csrfToken = null;

// Fetch CSRF token on page load
async function fetchCSRFToken() {
  try {
    const response = await fetch("api/members.php?action=get-csrf-token");
    const result = await response.json();
    if (result.success) {
      csrfToken = result.token;
      console.log("✅ CSRF token loaded");
    }
  } catch (error) {
    console.error("Failed to load CSRF token:", error);
  }
}
```

**Modify DOMContentLoaded (Line 1715)**:

```javascript
document.addEventListener("DOMContentLoaded", function () {
  fetchCSRFToken(); // ADD THIS LINE
  loadStatistics();
  loadMembersTable();
});
```

**Fix POST Request #1 - addMember() function (~Line 1048)**:

```javascript
const memberData = {
  action: "add",
  csrf_token: csrfToken, // ADD THIS LINE
  MemberName: formData.get("MemberName"),
  // ... rest of fields
};
```

**Fix POST Request #2 - updateMember() function (~Line 1436)**:

```javascript
const memberData = {
  action: "update",
  csrf_token: csrfToken, // ADD THIS LINE
  MemberNo: currentMemberNo,
  // ... rest of fields
};
```

**Fix POST Request #3 - deleteMember() function (~Line 1531)**:

```javascript
body: JSON.stringify({
  action: "delete",
  csrf_token: csrfToken, // ADD THIS LINE
  memberNo: memberNo,
});
```

---

### ⚠️ NEEDS FIX: admin/library-events.php

**Add at beginning of script section**:

```javascript
let csrfToken = null;

// Fetch CSRF token on page load
async function fetchCSRFToken() {
  try {
    const response = await fetch("api/events.php?action=get-csrf-token");
    const result = await response.json();
    if (result.success) {
      csrfToken = result.token;
      console.log("✅ CSRF token loaded");
    }
  } catch (error) {
    console.error("Failed to load CSRF token:", error);
  }
}
```

**Modify DOMContentLoaded**:

```javascript
document.addEventListener("DOMContentLoaded", function () {
  fetchCSRFToken(); // ADD THIS LINE
  loadEvents();
  loadRegistrations();
});
```

**Fix 7 POST Requests** (Lines 16, 37, 1920, 1943, 1997, 2015, 2031):

For each POST request, add `csrf_token: csrfToken` to the body. Example:

```javascript
// OLD:
body: JSON.stringify({
  action: "mark_attendance",
  RegistrationID: regId,
});

// NEW:
body: JSON.stringify({
  action: "mark_attendance",
  csrf_token: csrfToken, // ADD THIS
  RegistrationID: regId,
});
```

---

## Testing After Fix

### Test admin/members.php:

1. Open Members Management page
2. Check browser console for "✅ CSRF token loaded"
3. Try adding a new member - should succeed (no 403 error)
4. Try updating a member - should succeed
5. Try deleting a member - should succeed

### Test admin/library-events.php:

1. Open Library Events page
2. Check browser console for "✅ CSRF token loaded"
3. Try creating an event - should succeed
4. Try updating an event - should succeed
5. Try marking attendance - should succeed
6. Try deleting registration - should succeed

---

## Current API Status

All APIs already have CSRF validation enabled:

- ✅ `admin/api/members.php` - Has CSRF validation
- ✅ `admin/api/events.php` - Has CSRF validation
- ✅ `admin/api/event_registrations.php` - Has CSRF validation

The APIs are ready, just need frontend to send tokens.

---

## Quick Fix Command

To verify after manual fix, check console logs:

```javascript
// Open browser console and check for:
"✅ CSRF token loaded";
```

If token is loaded but still getting 403, check:

1. Token is being sent in request body
2. Token variable name is exactly `csrf_token`
3. Token is not null/undefined when request is made

---

## Priority

**HIGH** - Users will encounter errors when trying to:

- Add/edit/delete members
- Create/manage library events
- Mark event attendance
- Register for events

These are core admin functions that are currently broken.

---

**Fixed By**: GitHub Copilot  
**Date**: January 3, 2026  
**Remaining Work**: 2 files need manual CSRF token integration
