// Minimal Chatbot widget JS (non-AI) - calls chatbot/api/bot.php
// Usage: include this file and create a <div id="chatbot-widget"></div>

async function chatbotRequest(action, params = {}) {
    const url = new URL('/wiet_lib/chatbot/api/bot.php', window.location.origin);
    url.searchParams.set('action', action);
    Object.keys(params).forEach(k => url.searchParams.set(k, params[k]));

    const resp = await fetch(url.toString(), { credentials: 'include' });
    return resp.json();
}

export async function showMyLoans(container) {
    const res = await chatbotRequest('my_loans');
    if (!res.success) return container.innerText = res.message || 'Error';
    container.innerHTML = '';
    if (res.data.length === 0) {
        container.innerHTML = '<p>No active loans.</p>';
        return;
    }
    const ul = document.createElement('ul');
    res.data.forEach(item => {
        const li = document.createElement('li');
        li.innerHTML = `<strong>${item.Title}</strong> — Due: ${item.DueDate} ${item.DaysOverdue>0?`(<span style="color:red">${item.DaysOverdue}d overdue</span>)`:''}`;
        ul.appendChild(li);
    });
    container.appendChild(ul);
}

export async function showVisitCount(container) {
    const res = await chatbotRequest('visit_count');
    if (!res.success) return container.innerText = res.message || 'Error';
    container.innerHTML = `<p>Total visits: <strong>${res.data.total}</strong><br/>Last 30 days: <strong>${res.data.last_30_days}</strong></p>`;
}

export async function searchBooks(query, container) {
    const res = await chatbotRequest('search_books', { q: query });
    if (!res.success) return container.innerText = res.message || 'Error';
    container.innerHTML = '';
    if (res.data.length === 0) { container.innerText = 'No books found.'; return; }
    const table = document.createElement('table');
    table.style.width = '100%';
    table.border = '1';
    const thead = document.createElement('thead');
    thead.innerHTML = '<tr><th>Title</th><th>Author</th><th>Available</th></tr>';
    table.appendChild(thead);
    const tbody = document.createElement('tbody');
    res.data.forEach(b => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${b.Title}</td><td>${b.Author1||''}</td><td>${b.AvailableCopies||0}/${b.TotalCopies||0}</td>`;
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    container.appendChild(table);
}

// Simple text-based chat style helper
export function appendMessage(container, from, text) {
    // Create chat bubble
    const wrapper = document.createElement('div');
    wrapper.className = from === 'You' ? 'chat-bubble user' : 'chat-bubble bot';

    const inner = document.createElement('div');
    inner.className = 'chat-text';
    inner.textContent = text;
    wrapper.appendChild(inner);

    // Timestamp small
    const ts = document.createElement('div');
    ts.className = 'chat-ts';
    const d = new Date();
    ts.textContent = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    wrapper.appendChild(ts);

    // Append and scroll
    container.appendChild(wrapper);
    container.scrollTop = container.scrollHeight;
}

// Basic styles injection for chat bubbles when widget.js is imported
if (typeof document !== 'undefined') {
    const styleId = 'chatbot-widget-styles';
    if (!document.getElementById(styleId)) {
        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            .chat-bubble{max-width:85%;margin:8px;display:inline-block}
            .chat-bubble.user{align-self:flex-end;background:#263c79;color:#fff;padding:10px 14px;border-radius:16px 16px 6px 16px}
            .chat-bubble.bot{align-self:flex-start;background:#f1f5f9;color:#0f172a;padding:10px 14px;border-radius:16px 16px 16px 6px}
            .chat-text{font-size:14px;line-height:1.3}
            .chat-ts{font-size:11px;color:rgba(0,0,0,0.4);margin-top:4px;text-align:right}
        `;
        document.head.appendChild(style);
    }
}
