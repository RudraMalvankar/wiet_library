WIET-LIB Explaination
Level-wise flow (High-level UI pages & actions)
A. Admin / Super Admin (role = Admin)
Pages (file names suggested in /admin/ folder):
login.php — admin authentication (role check).
dashboard.php — system overview: active members, books available, books
issued, pending returns, dropbox status, footfall summary.
books_add.php — form to add book (creates entries in Books , Acquisition , Holding ).
(Use DB fields: book, acquisition, holding.)
books_edit.php — view / edit book metadata; edit acquisition/holding.
inventory.php — search & manage inventory (filter by status, location, ISBN,
keywords).
circulation_issue.php — issue books (scan student QR or search by MemberNo;
scan/enter AccNo). Inserts into Circulation . Update Holding.Status .
circulation_return.php — accept returns (scan AccNo or process DropReturn
matched return). Inserts into Return , updates Holding.Status .
dropbox_monitor.php — view DropBox heartbeats and queue of DropReturn items
(accept/reject).
members_add.php — create Member + Student record(s); generate MemberNo +
StudentID + QR + barcode; set default username/password.
admins_manage.php — manage admin accounts and roles.
events_manage.php — add/modify library events.
analytics.php — run prebuilt reports (footfall heatmap, most-borrowed books,
overdue list, fines summary).
notifications.php — send notifications (email/SMS/push) for due/overdue/new
arrivals.
recommendations_manage.php — approve/decline student recommendations and
seed system recommendations.
reports_export.php — export CSV/PDF of analytics.
Key admin flows:
1. Add Book → insert to Books , Acquisition and create Holding records for each copy
with unique AccNo and BarCode . Generate QR + barcode and store file path in
Holding.BarCode (or a dedicated column if desired).
2. Issue Book → admin scans Student QR (or search) → identifies MemberNo →
scans AccNo of copy → insert Circulation with DueDate, set Holding.Status = 'Issued'
and increment Member.BooksIssued .
3. Return Book → admin scans AccNo → find open Circulation → create Return and
compute fine → set Holding.Status = 'Available' and decrement Member.BooksIssued .
4. Dropbox Return (auto) → record in DropReturn , attempt to match open Circulation
and auto-create Return if matched; else mark for manual review.
B. Student
Pages (under /student/ ):
login.php — login by MemberNo/StudentID and password.
mybooks.php — current issued books, due dates, renew button (if allowed).
history.php — borrow history (Circulation + Return).
search.php — search books by Title/Author/ISBN/keywords (search uses Books ,
Holding.Status ).
eresources.php — list from E_Resources .
recommend.php — submit recommendation (inserts into Recommendations ).
footfall.php — show last visits and time spent (from Footfall ).
notifications.php — view due/overdue/new arrival alerts.
digital_id.php — show digital card with QR and barcode (MemberNo encoded).
profile.php — change password, upload photo; show Student details.
Student flows:
Login using MemberNo/StudentID.
Scan their student QR at counters or dropbox to auto-login in kiosk mode
(DropBox flow).
Renew books if within renewal policy and no pending reservations/fines.
C. DropBox (kiosk device / endpoint)
Device scans Student QR → fetch MemberNo → device logs MemberScanAt in
DropReturn entry (with DropBoxID ) and shows "Now scan book".
Device scans Book QR/BarCode → append BookScanAt and set AccNo .
Server attempts to match Circulation record where AccNo is issued to that
member and open → create Return record and set Outcome = 'ACCEPTED' , set
ProcessedAt and ProcessedBy (NULL if auto). If unmatched, set Outcome = 'REJECTED'
or ALREADY_RETURNED .
DropBox should periodically heartbeat to update DropBox.LastHeartbeatAt .
D. Footfall Tracker
At entry, scan Student QR or barcode → record Footfall row with TimeIn . When
exiting, scan again → update TimeOut . Admin can view time spent, visits per
day, week, month.
Footfall data is sensitive; store only MemberNo + timestamps. Secure API and
use HTTPS.
E. Recommendation Engine
Two components:
1. Rule-based: Recommend books similar to current borrow history using
subject/keywords and author match (join Circulation → Holding → Books →
find frequently borrowed categories).
2. Collaborative filter (basic): For each student, find books borrowed by
other students who borrowed similar sets and rank by popularity (simpler:
co-occurrence counts).
Recommendations are stored in Recommendations table (or a separate
RecommendationResults table) and surfaced in student/mybooks.php & admin dashboard.
F. Chatbot (DB-aware)
Should answer queries like:
"Is X available?" → check Books + Holding.Status .
"What books by Author Y?" → search Books.Author1|2|3 .
"Show my borrowed books" → given Student authenticated (or scanned
QR), read Circulation .
"Recommend books like X" → use recommendation engine.
The chatbot must run queries read-only except where allowed (e.g., student
can ask to recommend a book — that inserts into Recommendations ).
Keep explicit constraint: Chatbot must not expose other students' personal
data.
Chatbot knowledge base includes book metadata, availability, events, FAQs,
and summary of rules (issue limits, fine policy).
