<?php
// books_edit.php — View/edit book metadata, acquisition, holdings
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$catNo = $_GET['catNo'] ?? null;
if (!$catNo) { echo 'No book selected.'; exit; }
// TODO: Fetch book, acquisition, holdings from DB
// TODO: Render editable form for all fields
// TODO: On submit, POST to api/books.php?action=update
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book</title>
</head>
<body>
    <h2>Edit Book</h2>
    <!-- FORM PLACEHOLDER -->
    <form id="editBookForm">
        <!-- All book, acquisition, holding fields go here -->
        <button type="submit">Save Changes</button>
    </form>
    <div id="result"></div>
    <script>
    // TODO: JS to load book data, handle submit, call API, show result
    </script>
</body>
</html>
