# Quick Testing Guide - Circulation Workflow

## Prerequisites

1. **Start XAMPP:**
   - Apache running
   - MySQL running

2. **Login:**
   - Navigate to `http://localhost/wiet_lib/admin/admin_login.php`
   - Login with admin credentials
   - Go to Circulation page

## Test Issue Books

### Test 1: Manual Member Search

1. Go to **Issue Books** tab
2. In "Step 1: Scan or Search Member" section
3. Enter a valid Member Number in the input field (e.g., from your Member table)
4. Press Enter or click outside the field
5. **Expected:** Member info card appears with Name, Member No, Group, Books Issued

### Test 2: Manual Book Search

1. After member is found, go to "Step 2: Scan or Search Book"
2. Enter a valid Accession Number (e.g., from your Holding table with Status='Available')
3. Press Enter or click outside the field
4. **Expected:** Book info card appears with Title, Author, AccNo, Location

### Test 3: Issue Book

1. After both member and book are found
2. Check that Issue Date is today's date
3. Check that Due Date is 15 days from today
4. (Optional) Add remarks
5. Click **Issue Book** button
6. **Expected:**
   - Alert shows success message with member name, book title, and due date
   - Statistics cards update
   - Active Circulations table refreshes and shows new issue
   - Form resets (all fields cleared, info cards hidden)

### Test 4: Error Handling

**Test 4.1: Invalid Member**
- Enter a non-existent Member Number (e.g., 999999)
- **Expected:** Error message "Member 999999 not found!"

**Test 4.2: Invalid Book**
- Enter a non-existent Accession Number
- **Expected:** Error message "Book with AccNo XXX not found!"

**Test 4.3: Unavailable Book**
- Enter an AccNo that exists but Status is not "Available" (e.g., already issued)
- **Expected:** Error message "Book is not available for issue! Current status: Issued"

**Test 4.4: Missing Member or Book**
- Try clicking Issue Book without selecting member
- **Expected:** Button is disabled (grayed out)

---

## Test Return Books

### Test 1: Manual Book Search for Return

1. Go to **Return Books** tab
2. In "Scan or Search Book to Return" section
3. Enter an AccNo that is currently issued (check Active Circulations table)
4. Press Enter or click outside the field
5. **Expected:**
   - Return Book info card appears with Book Title, Member Name, Issue Date, Due Date, Overdue Days
   - If overdue, Fine Calculator section appears with fine amount
   - Return button becomes enabled

### Test 2: Return Book (Not Overdue)

1. After finding a book that's not overdue
2. Select condition from dropdown (e.g., "Good")
3. (Optional) Add remarks
4. Click **Return Book** button
5. **Expected:**
   - Alert shows success message with book title, member name, and fine (₹0.00)
   - Statistics cards update
   - Active Circulations table refreshes (book removed)
   - Return History table refreshes (book added)
   - Form resets

### Test 3: Return Book (Overdue)

1. To test overdue, you can manually update a circulation in database:
   ```sql
   UPDATE Circulation 
   SET IssueDate = DATE_SUB(NOW(), INTERVAL 20 DAY),
       DueDate = DATE_SUB(NOW(), INTERVAL 5 DAY)
   WHERE CirculationID = [some_id]
   LIMIT 1;
   ```
2. Search for that book in Return tab
3. **Expected:**
   - Error/warning message shows overdue info
   - Fine Calculator displays: "5 days × ₹2.00 = ₹10.00"
   - Red/yellow highlighting for overdue status
4. Complete the return
5. Alert shows fine amount

### Test 4: Error Handling

**Test 4.1: No Active Circulation**
- Enter an AccNo that exists but is not currently issued
- **Expected:** Error message "No active circulation found for AccNo: XXX"

**Test 4.2: Invalid AccNo**
- Enter a non-existent AccNo
- **Expected:** Error message "No active circulation found for AccNo: XXX"

---

## Test Scanning (Optional - Requires Physical QR/Barcode)

### Member QR Scanning

1. Generate a QR code containing a Member Number (use any online QR generator)
2. Click **Start Scan** button in Member section
3. Allow camera permissions
4. Hold QR code in front of camera
5. **Expected:** Auto-fills Member Number and searches automatically

### Book Barcode Scanning

1. If you have books with QR codes/barcodes generated
2. Click **Start Scan** button in Book section
3. Hold barcode/QR in front of camera
4. **Expected:** Auto-fills AccNo and searches automatically

### Simulate Scanning

1. Click **Simulate Scan** buttons to test without physical codes
2. Enter test data when prompted
3. Verifies scanning workflow without actual camera

---

## Check Live Data Updates

### Statistics Cards

1. Note the current numbers in statistics cards at top:
   - Total Issued
   - Due Today
   - Overdue
   - Today's Returns

2. Issue a book
3. **Expected:** "Total Issued" increases by 1

4. Return a book
5. **Expected:** "Total Issued" decreases by 1, "Today's Returns" increases by 1

6. Wait 30 seconds
7. **Expected:** Stats auto-refresh (check console for API call)

### Active Circulations Table

1. Check the table below "Active Circulations"
2. Issue a book
3. **Expected:** New row appears immediately with book and member details
4. Return a book
5. **Expected:** Row disappears from table

### Return History Table

1. Switch to **Return History** tab in the table
2. Return a book
3. **Expected:** New row appears with return date, condition, fine

---

## Common Issues & Solutions

### Issue: "Member not found" but member exists

**Solution:** Check the API endpoint:
- Open browser DevTools (F12)
- Go to Network tab
- Search member again
- Check the API call to `api/members.php?action=get&memberNo=XXX`
- Verify response format
- Check if Member table has the record

### Issue: Camera not working

**Solution:**
- Check if you're using HTTPS or localhost (required for camera access)
- Check browser permissions (camera must be allowed)
- Try **Simulate Scan** buttons instead

### Issue: Issue button stays disabled

**Solution:**
- Check browser console for JavaScript errors
- Verify both `selectedMember` and `selectedBook` are not null
- Check that book Status is exactly "Available" (case-sensitive)

### Issue: Statistics not updating

**Solution:**
- Check browser console for errors
- Verify API endpoint `api/circulation.php?action=stats` returns correct data
- Check that `loadStatistics()` function is being called after issue/return

### Issue: Fine calculation wrong

**Solution:**
- Verify system date is correct
- Check that DueDate in database is in correct format (YYYY-MM-DD)
- Fine is ₹2 per day, calculated as: `(Today - DueDate) × 2`

---

## Sample Test Data

You can use this SQL to create test data:

```sql
-- Insert test member
INSERT INTO Member (MemberNo, MemberName, `Group`, Status, BooksIssued)
VALUES ('TEST001', 'Test User', 'Student', 'Active', 0);

-- Insert test book (if not exists)
INSERT INTO Books (CatNo, Title, Author1, Publisher, Year)
VALUES ('TESTCAT001', 'Test Book', 'Test Author', 'Test Publisher', '2024');

-- Insert test holding (available)
INSERT INTO Holding (CatNo, AccNo, Status, Location)
VALUES ('TESTCAT001', 'TESTACC001', 'Available', 'Test Section');

-- Insert test holding (issued) for return testing
INSERT INTO Holding (CatNo, AccNo, Status, Location)
VALUES ('TESTCAT001', 'TESTACC002', 'Issued', 'Test Section');

-- Insert active circulation for return testing
INSERT INTO Circulation (AccNo, MemberNo, IssueDate, DueDate, AdminID, Status)
VALUES ('TESTACC002', 'TEST001', DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_ADD(NOW(), INTERVAL 5 DAY), 1, 'Issued');

-- Update member's BooksIssued count
UPDATE Member SET BooksIssued = 1 WHERE MemberNo = 'TEST001';
```

---

## Performance Checks

1. **API Response Time:**
   - Open DevTools → Network tab
   - Trigger searches
   - API calls should complete in < 500ms

2. **Camera Initialization:**
   - Start Scan should activate camera in < 2 seconds
   - If slower, check camera resolution settings

3. **Page Load:**
   - Initial page load should be < 3 seconds
   - Statistics, active circulations, and return history should load in parallel

---

## Next Steps After Testing

If all tests pass:

1. ✅ **Issue Books workflow fully functional**
2. ✅ **Return Books workflow fully functional**
3. ✅ **Scanning infrastructure operational**
4. ✅ **Live data updates working**

You can proceed to:
- Train staff on using the system
- Generate QR codes for existing holdings (use `database/tools/batch_generate_qr.php`)
- Configure fine rates in settings
- Set up reports generation
- Implement soft delete for books
- Add notification system

---

## Quick Reference - Keyboard Shortcuts

- **Ctrl+K** - Open Quick Scan modal (fast lookup anywhere)
- **Enter** - Submit search in input fields
- **Tab** - Navigate between form fields
- **Esc** - Close modals

---

**Happy Testing! 🎉**

All circulation features are now ready for production use.
