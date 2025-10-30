Library Chatbot (non-AI) - README

## Overview

This is a lightweight chatbot backend for the WIET library system. It does not use external AI. Instead it exposes student-focused queries over the existing database and returns JSON responses. The student-facing widget can call these APIs and display results.

## Endpoints

- GET /chatbot/api/bot.php?action=my_loans

  - Returns current active loans for the logged-in student.

- GET /chatbot/api/bot.php?action=due_books

  - Returns books that are due soon or overdue for the logged-in student.

- GET /chatbot/api/bot.php?action=visit_count

  - Returns total visits and visits in the last 30 days.

- GET /chatbot/api/bot.php?action=search_books&q=<query>

  - Searches books by title/author/ISBN and returns availability info.

- GET /chatbot/api/bot.php?action=book_info&catno=<catno>

  - Returns detailed info + availability for a specific book catalog number.

- GET /chatbot/api/bot.php?action=history_summary
  - Returns summary: visits, borrows, last borrow date.

## Installation / Integration

1. Copy the `chatbot` folder into your project root (already created).
2. The API relies on the student session check at `student/student_session_check.php`. Include the widget in `student/layout.php` for logged-in students.

Example embed (add to `student/layout.php` where appropriate):

<div id="chatbot-widget">
  <script type="module">
    import { showMyLoans, showVisitCount } from '/wiet_lib/chatbot/widget.js';
    document.addEventListener('DOMContentLoaded', () => {
      const container = document.getElementById('chatbot-widget');
      const loansDiv = document.createElement('div');
      container.appendChild(loansDiv);
      showMyLoans(loansDiv);
      const visitsDiv = document.createElement('div');
      container.appendChild(visitsDiv);
      showVisitCount(visitsDiv);
    });
  </script>
</div>

## Security

- The API requires the student to be authenticated via `student_session_check.php`. It will use `$_SESSION['member_no']` to identify the student.
- All DB queries use prepared statements.

## Testing

Open `http://localhost/wiet_lib/chatbot/test_api.php` in your browser while logged in as a student, or uncomment the session variables in that file for quick local testing.
