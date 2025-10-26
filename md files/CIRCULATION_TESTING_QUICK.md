# 🧪 Circulation System - Quick Test Guide

## Quick Test Scenarios

### ✅ Test 1: Issue Book (Happy Path)

**Steps:**
1. Open `admin/circulation.php`
2. Click "Start Camera" under Member Scanner (or enter member number manually)
3. Scan member QR code or enter: `2511` or `2512`
4. ✓ Member info should display
5. Click "Start Camera" under Book Scanner (or enter AccNo manually)
6. Scan book QR or enter: `ACC001001`
7. ✓ Book info should display
8. Set issue date (today) and due date (15 days later)
9. Click "Issue Book"
10. ✓ Success message should appear
11. ✓ Dashboard stats should update

**Expected Database Changes:**
```sql
-- Circulation table
INSERT INTO Circulation (MemberNo, AccNo, IssueDate, DueDate, Status)
VALUES (2511, 'ACC001001', '2024-10-25', '2024-11-09', 'Active');

-- Holding table
UPDATE Holding SET Status = 'Issued' WHERE AccNo = 'ACC001001';

-- Member table (if BooksIssued column exists)
UPDATE Member SET BooksIssued = BooksIssued + 1 WHERE MemberNo = 2511;
```

---

### ✅ Test 2: Return Book (On Time)

**Steps:**
1. Switch to "Return Books" tab
2. Click "Start Camera" or enter AccNo: `ACC001001`
3. ✓ Book circulation info should display
4. ✓ Should show "0 days overdue"
5. Select condition: "Good"
6. Click "Return Book"
7. ✓ Success message: "Returned on time - No fine"
8. ✓ Book should disappear from Active Circulations

**Expected Database Changes:**
```sql
-- Circulation table
UPDATE Circulation 
SET Status = 'Returned', 
    ReturnDate = '2024-10-25',
    FineAmount = 0
WHERE CirculationID = ?;

-- Holding table
UPDATE Holding SET Status = 'Available' WHERE AccNo = 'ACC001001';

-- Member table
UPDATE Member SET BooksIssued = BooksIssued - 1 WHERE MemberNo = 2511;
```

---

### ✅ Test 3: Return Book (Overdue)

**Setup:** Manually change DueDate in database to past date
```sql
UPDATE Circulation 
SET DueDate = DATE_SUB(CURDATE(), INTERVAL 5 DAY)
WHERE AccNo = 'ACC001002';
```

**Steps:**
1. Go to "Return Books" tab
2. Enter AccNo: `ACC001002`
3. ✓ Should show "5 days overdue"
4. ✓ Fine calculator shows: "₹10.00" (5 days × ₹2)
5. Click "Return Book"
6. ✓ Confirm fine dialog appears
7. Click OK
8. ✓ Success message shows fine collected

**Expected Result:**
- Fine recorded in database
- Book returned
- Status updated

---

### ❌ Test 4: Try Issue to Inactive Member

**Setup:** Create inactive member or set existing member to inactive
```sql
UPDATE Member SET Status = 'Inactive' WHERE MemberNo = 2513;
```

**Steps:**
1. Try to scan/search member: `2513`
2. ✓ Should show error: "Member is Inactive. Cannot issue books"
3. ✓ Member info should NOT display
4. ✓ Issue button should remain disabled

---

### ❌ Test 5: Try Issue Already-Issued Book

**Setup:** Book should already be issued (Status='Issued')

**Steps:**
1. Select valid active member
2. Try to scan book: `ACC001002` (already issued)
3. ✓ Should show error: "Book is not available for issue! Currently issued to another member."
4. ✓ Book info should NOT display
5. ✓ Issue button should remain disabled

---

### ❌ Test 6: Try Issue When Book Limit Reached

**Setup:** Member should have MaxBooks=3 and BooksIssued=3
```sql
UPDATE Member SET BooksIssued = 3, MaxBooks = 3 WHERE MemberNo = 2514;
```

**Steps:**
1. Try to scan member: `2514`
2. ✓ Should show error: "Member has reached maximum book limit (3/3)"
3. ✓ Member info should NOT display

---

## 🔍 Browser Console Tests

### Test Member Search
```javascript
// Open browser console (F12)
const testMember = async () => {
    const response = await fetch('api/members.php?action=get&memberNo=2511');
    const data = await response.json();
    console.log('Member Data:', data);
};
testMember();
```

**Expected Output:**
```json
{
    "success": true,
    "data": {
        "MemberNo": 2511,
        "MemberName": "undefined",
        "Status": "Active",
        "BooksIssued": 0,
        "MaxBooks": 3
    }
}
```

### Test Book Lookup
```javascript
const testBook = async () => {
    const response = await fetch('api/books.php?action=lookup&accNo=ACC001001');
    const data = await response.json();
    console.log('Book Data:', data);
};
testBook();
```

**Expected Output:**
```json
{
    "success": true,
    "data": {
        "AccNo": "ACC001001",
        "CatNo": 1001,
        "Title": "Book Title",
        "Status": "Available"
    }
}
```

### Test Issue API
```javascript
const testIssue = async () => {
    const response = await fetch('api/circulation.php?action=issue', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            memberNo: 2511,
            accNo: 'ACC001001',
            issueDate: '2024-10-25',
            dueDate: '2024-11-09'
        })
    });
    const data = await response.json();
    console.log('Issue Result:', data);
};
testIssue();
```

---

## 📊 Database Verification Queries

### Check Circulation Created
```sql
SELECT c.*, m.MemberName, h.AccNo, b.Title
FROM Circulation c
JOIN Member m ON c.MemberNo = m.MemberNo
JOIN Holding h ON c.AccNo = h.AccNo
JOIN Books b ON h.CatNo = b.CatNo
WHERE c.Status = 'Active'
ORDER BY c.IssueDate DESC
LIMIT 10;
```

### Check Book Status Updated
```sql
SELECT AccNo, CatNo, Status, Location
FROM Holding
WHERE AccNo IN ('ACC001001', 'ACC001002', 'ACC001003');
```

### Check Member Book Count
```sql
SELECT MemberNo, MemberName, BooksIssued, MaxBooks,
       (SELECT COUNT(*) FROM Circulation WHERE MemberNo = m.MemberNo AND Status = 'Active') as ActualIssued
FROM Member m
WHERE MemberNo IN (2511, 2512, 2513);
```

### Check Overdue Books
```sql
SELECT c.*, 
       DATEDIFF(CURDATE(), c.DueDate) as DaysOverdue,
       DATEDIFF(CURDATE(), c.DueDate) * m.FinePerDay as FineAmount
FROM Circulation c
JOIN Member m ON c.MemberNo = m.MemberNo
WHERE c.Status = 'Active' 
  AND c.DueDate < CURDATE()
ORDER BY c.DueDate ASC;
```

---

## 🐛 Common Issues & Solutions

### Issue: "Member not found"
**Check:**
```sql
SELECT * FROM Member WHERE MemberNo = 2511;
```
If empty, member doesn't exist. Add test member:
```sql
INSERT INTO Member (MemberNo, MemberName, Status, MaxBooks, BooksIssued)
VALUES (2511, 'Test Student', 'Active', 3, 0);
```

### Issue: "Book not found"
**Check:**
```sql
SELECT h.*, b.Title 
FROM Holding h
JOIN Books b ON h.CatNo = b.CatNo
WHERE h.AccNo = 'ACC001001';
```

### Issue: Camera not starting
**Solutions:**
1. Use HTTPS (localhost works on HTTP)
2. Grant camera permissions in browser
3. Check browser console for errors
4. Try manual entry instead

### Issue: Book status not updating
**Check triggers:**
```sql
-- Check if trigger exists
SHOW TRIGGERS LIKE 'Circulation';

-- Manually test update
UPDATE Holding SET Status = 'Issued' WHERE AccNo = 'ACC001001';
SELECT AccNo, Status FROM Holding WHERE AccNo = 'ACC001001';
```

---

## ✅ Complete Test Checklist

### Member Operations
- [ ] Scan valid member QR
- [ ] Search member by number
- [ ] Display member info correctly
- [ ] Reject inactive member
- [ ] Reject member at book limit
- [ ] Handle member not found

### Book Operations
- [ ] Scan valid book QR
- [ ] Search book by AccNo
- [ ] Display book info correctly
- [ ] Reject already-issued book
- [ ] Reject damaged/lost book
- [ ] Handle book not found

### Issue Operations
- [ ] Issue book successfully
- [ ] Create circulation record
- [ ] Update book status to Issued
- [ ] Increment member book count
- [ ] Refresh dashboard stats
- [ ] Validate issue/due dates
- [ ] Prevent duplicate issues

### Return Operations
- [ ] Return book on time (no fine)
- [ ] Return overdue book (with fine)
- [ ] Calculate fine correctly
- [ ] Update circulation record
- [ ] Update book status to Available
- [ ] Decrement member book count
- [ ] Record fine in database
- [ ] Refresh dashboard and history

### UI/UX
- [ ] Camera starts correctly
- [ ] Camera stops correctly
- [ ] Success messages display
- [ ] Error messages display
- [ ] Loading indicators work
- [ ] Tabs switch correctly
- [ ] Forms reset after action
- [ ] Buttons disabled during processing

---

## 🎯 Success Criteria

**System is working correctly if:**

1. ✅ Member scan → Displays member details
2. ✅ Book scan → Displays book availability
3. ✅ Issue book → Creates circulation + updates book status + updates member count
4. ✅ Return book → Closes circulation + frees book + updates member count
5. ✅ Overdue detection → Calculates fine correctly
6. ✅ Validations → Prevents invalid operations
7. ✅ Dashboard → Shows accurate real-time stats
8. ✅ Database → All tables synchronized correctly

---

## 📞 Quick Support

**If tests fail:**
1. Check browser console (F12) for JavaScript errors
2. Check network tab for API request/response
3. Run database verification queries
4. Check PHP error logs (`error_log` file)
5. Verify database connection in `includes/db_connect.php`

---

*Happy Testing! 🚀*
