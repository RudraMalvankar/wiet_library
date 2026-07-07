<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['faculty_id'])) {
    header('Location: faculty_login.php');
    exit();
}
require_once '../includes/db_connect.php';
?>
<style>
    .page-title { color: #263c79; font-size: 24px; font-weight: 700; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #cfac69; }
    .search-box { display: flex; gap: 10px; margin-bottom: 25px; }
    .search-box input { flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px; }
    .search-box input:focus { outline: none; border-color: #cfac69; }
    .search-box button { padding: 12px 24px; background: #263c79; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
    .search-box button:hover { background: #1e2d5f; }
    .results-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .results-table th { background: #263c79; color: white; padding: 12px; text-align: left; font-weight: 600; }
    .results-table td { padding: 12px; border-bottom: 1px solid #e9ecef; }
    .results-table tr:hover { background: rgba(207,172,105,0.1); }
    .status-available { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; }
    .status-issued { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; }
    .empty-state { text-align: center; padding: 60px 20px; color: #6c757d; }
    .empty-state i { font-size: 48px; margin-bottom: 15px; opacity: 0.3; }
</style>
<h2 class="page-title"><i class="fas fa-search"></i> Search Books</h2>
<div class="search-box">
    <input type="text" id="searchQuery" placeholder="Search by title, author, ISBN, or keyword..." onkeypress="if(event.key==='Enter') searchBooks()">
    <button onclick="searchBooks()"><i class="fas fa-search"></i> Search</button>
</div>
<div id="searchResults">
    <div class="empty-state">
        <i class="fas fa-search"></i>
        <h3>Search the Library Catalog</h3>
        <p>Enter a search term above to find books in the library collection.</p>
    </div>
</div>
<script>
async function searchBooks() {
    const query = document.getElementById('searchQuery').value.trim();
    if (!query) return;

    document.getElementById('searchResults').innerHTML = '<div style="text-align:center;padding:40px;color:#666;"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i><p style="margin-top:10px;">Searching...</p></div>';

    try {
        const response = await fetch(`/wiet_lib/opac.php?search=${encodeURIComponent(query)}&ajax=1`);
        if (!response.ok) {
            const fallback = await fetch(`../admin/api/books.php?action=search&q=${encodeURIComponent(query)}`);
            const fallbackData = await fallback.json();
            if (fallbackData.success && fallbackData.data) {
                displayResults(fallbackData.data);
            } else {
                document.getElementById('searchResults').innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><h3>No Results</h3><p>No books found matching your search.</p></div>';
            }
        } else {
            const html = await response.text();
            document.getElementById('searchResults').innerHTML = html;
        }
    } catch (error) {
        document.getElementById('searchResults').innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle" style="color:#dc3545;"></i><h3>Error</h3><p>Failed to search. Please try again.</p></div>';
    }
}

function displayResults(data) {
    if (!data || data.length === 0) {
        document.getElementById('searchResults').innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><h3>No Results</h3><p>No books found matching your search.</p></div>';
        return;
    }
    let html = '<div style="overflow-x:auto;"><table class="results-table"><thead><tr><th>Title</th><th>Author</th><th>Publisher</th><th>ISBN</th><th>Available</th></tr></thead><tbody>';
    data.forEach(book => {
        html += `<tr>
            <td><strong>${escapeHtml(book.Title || 'Unknown')}</strong></td>
            <td>${escapeHtml(book.Author1 || '-')}</td>
            <td>${escapeHtml(book.Publisher || '-')}</td>
            <td>${escapeHtml(book.ISBN || '-')}</td>
            <td><span class="status-available">${book.AvailableCopies || 0} / ${book.TotalCopies || 0}</span></td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    document.getElementById('searchResults').innerHTML = html;
}

function escapeHtml(text) {
    if (typeof text !== 'string') return '';
    return text.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'})[c]);
}
</script>
