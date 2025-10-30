<?php
require_once 'student_session_check.php';
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;">
  <div>
    <h2 style="color:#263c79;margin-bottom:6px;">Library Assistant</h2>
    <p style="color:#6b7280;margin:0;">Ask about your loans, due dates, visits, or search for books.</p>
  </div>
</div>

<div style="margin-top:20px;display:flex;gap:24px;align-items:stretch;">
  <!-- Left column - chat + quick actions -->
  <div style="flex:1.2;min-width:400px;">
    <div style="background:#fff;border:1px solid #e6e6e6;padding:20px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.05);height:100%;">
      <h3 style="margin-top:0;margin-bottom:12px;color:#263c79;font-size:18px;border-bottom:2px solid #cfac69;padding-bottom:8px;">Chat with Assistant</h3>
      
      <div id="chatbox" style="height:400px;overflow-y:auto;padding:14px;border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;display:flex;flex-direction:column;gap:10px;margin-bottom:10px;"></div>
      
      <div id="typing-indicator" style="display:none;padding:8px 12px;color:#6b7280;font-size:13px;font-style:italic;">
        <span style="display:inline-block;width:6px;height:6px;background:#cfac69;border-radius:50%;margin-right:4px;animation:pulse 1.5s ease-in-out infinite;"></span>
        Bot is typing...
      </div>

      <div style="display:flex;gap:10px;margin-top:10px;">
        <input id="chat-input" placeholder="Ask me anything... (e.g., when is my next due book?)" style="flex:1;padding:12px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;transition:border-color 0.2s;" onfocus="this.style.borderColor='#263c79'" onblur="this.style.borderColor='#d1d5db'" />
        <button id="chat-send" class="btn" style="background:#263c79;color:#fff;padding:12px 20px;border-radius:8px;border:none;font-weight:600;cursor:pointer;transition:background 0.2s;" onmouseover="this.style.background='#1e2f5f'" onmouseout="this.style.background='#263c79'">Ask</button>
      </div>

      <div style="margin-top:14px;padding-top:14px;border-top:1px solid #e5e7eb;">
        <p style="margin:0 0 8px 0;font-size:12px;color:#6b7280;font-weight:600;">QUICK ACTIONS</p>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <button class="quick-btn btn" data-cmd="my_loans" style="background:#fff;border:1px solid #cfac69;color:#263c79;padding:8px 14px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='#cfac69';this.style.color='#fff'" onmouseout="this.style.background='#fff';this.style.color='#263c79'">📚 My Loans</button>
          <button class="quick-btn btn" data-cmd="due_books" style="background:#fff;border:1px solid #cfac69;color:#263c79;padding:8px 14px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='#cfac69';this.style.color='#fff'" onmouseout="this.style.background='#fff';this.style.color='#263c79'">⏰ Due Books</button>
          <button class="quick-btn btn" data-cmd="visit_count" style="background:#fff;border:1px solid #cfac69;color:#263c79;padding:8px 14px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='#cfac69';this.style.color='#fff'" onmouseout="this.style.background='#fff';this.style.color='#263c79'">📊 My Visits</button>
          <button class="quick-btn btn" data-cmd="history_summary" style="background:#fff;border:1px solid #cfac69;color:#263c79;padding:8px 14px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='#cfac69';this.style.color='#fff'" onmouseout="this.style.background='#fff';this.style.color='#263c79'">📝 Summary</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Right column - results and search -->
  <div style="flex:0.8;min-width:340px;">
    <div style="background:#fff;border:1px solid #e6e6e6;padding:20px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.05);height:100%;">
      <h3 style="margin-top:0;margin-bottom:16px;color:#263c79;font-size:18px;border-bottom:2px solid #cfac69;padding-bottom:8px;">Quick View</h3>
      
      <div style="background:#f9fafb;padding:14px;border-radius:8px;margin-bottom:14px;border:1px solid #e5e7eb;">
        <h4 style="margin:0 0 10px 0;color:#263c79;font-size:14px;font-weight:600;">📚 Current Loans</h4>
        <div id="quick-loans" style="font-size:14px;color:#4b5563;">Loading loans…</div>
      </div>
      
      <div style="background:#f9fafb;padding:14px;border-radius:8px;margin-bottom:14px;border:1px solid #e5e7eb;">
        <h4 style="margin:0 0 10px 0;color:#263c79;font-size:14px;font-weight:600;">📊 Visit Statistics</h4>
        <div id="quick-visits" style="font-size:14px;color:#4b5563;">Loading visits…</div>
      </div>
      
      <div style="background:#f9fafb;padding:14px;border-radius:8px;border:1px solid #e5e7eb;">
        <h4 style="margin:0 0 10px 0;color:#263c79;font-size:14px;font-weight:600;">🔍 Search Books</h4>
        <div style="display:flex;gap:8px;">
          <input id="search-q" placeholder="Title or author" style="flex:1;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" />
          <button id="search-btn" class="btn" style="background:#263c79;color:#fff;padding:10px 16px;border-radius:6px;border:none;cursor:pointer;font-weight:600;" onmouseover="this.style.background='#1e2f5f'" onmouseout="this.style.background='#263c79'">Search</button>
        </div>
        <div id="search-result" style="margin-top:12px;max-height:300px;overflow-y:auto;"></div>
      </div>
    </div>
  </div>
</div>

<style>
@keyframes pulse {
  0%, 100% { opacity: 0.4; }
  50% { opacity: 1; }
}
.chat-bubble {
  animation: slideIn 0.3s ease-out;
}
@keyframes slideIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
(function() {
  // Inline widget helper functions (from widget.js)
  function appendMessage(container, from, text) {
    const wrapper = document.createElement('div');
    wrapper.className = from === 'You' ? 'chat-bubble user' : 'chat-bubble bot';
    wrapper.style.cssText = from === 'You' 
      ? 'max-width:85%;margin:8px;display:block;align-self:flex-end;background:#263c79;color:#fff;padding:10px 14px;border-radius:16px 16px 6px 16px'
      : 'max-width:85%;margin:8px;display:block;align-self:flex-start;background:#f1f5f9;color:#0f172a;padding:10px 14px;border-radius:16px 16px 16px 6px';

    const inner = document.createElement('div');
    inner.style.fontSize = '14px';
    inner.style.lineHeight = '1.3';
    inner.textContent = text;
    wrapper.appendChild(inner);

    const ts = document.createElement('div');
    ts.style.fontSize = '11px';
    ts.style.color = 'rgba(0,0,0,0.4)';
    ts.style.marginTop = '4px';
    ts.style.textAlign = 'right';
    const d = new Date();
    ts.textContent = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    wrapper.appendChild(ts);

    container.appendChild(wrapper);
    container.scrollTop = container.scrollHeight;
  }

  async function showMyLoans(container) {
    try {
      console.log('[Chatbot] Fetching my loans...');
      const res = await fetch('/wiet_lib/chatbot/api/bot.php?action=my_loans', {credentials:'include'}).then(r=>r.json());
      console.log('[Chatbot] My loans response:', res);
      if (!res.success) { container.innerText = res.message || 'Error'; return; }
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
    } catch (e) {
      container.innerText = 'Error loading loans';
      console.error('[Chatbot] Error in showMyLoans:', e);
    }
  }

  async function showVisitCount(container) {
    try {
      console.log('[Chatbot] Fetching visit count...');
      const res = await fetch('/wiet_lib/chatbot/api/bot.php?action=visit_count', {credentials:'include'}).then(r=>r.json());
      console.log('[Chatbot] Visit count response:', res);
      if (!res.success) { container.innerText = res.message || 'Error'; return; }
      container.innerHTML = `<p>Total visits: <strong>${res.data.total}</strong><br/>Last 30 days: <strong>${res.data.last_30_days}</strong></p>`;
    } catch (e) {
      container.innerText = 'Error loading visits';
      console.error('[Chatbot] Error in showVisitCount:', e);
    }
  }

  async function searchBooks(query, container) {
    try {
      console.log('[Chatbot] Searching books:', query);
      const res = await fetch('/wiet_lib/chatbot/api/bot.php?action=search_books&q=' + encodeURIComponent(query), {credentials:'include'}).then(r=>r.json());
      console.log('[Chatbot] Search response:', res);
      if (!res.success) { container.innerText = res.message || 'Error'; return; }
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
    } catch (e) {
      container.innerText = 'Error searching books';
      console.error(e);
    }
  }

  // Main chatbot logic
  console.log('[Chatbot] Script starting...');
  
  const chatbox = document.getElementById('chatbox');
  const input = document.getElementById('chat-input');
  const send = document.getElementById('chat-send');
  const quick = document.querySelectorAll('.quick-btn');
  const quickLoans = document.getElementById('quick-loans');
  const quickVisits = document.getElementById('quick-visits');
  const searchBtn = document.getElementById('search-btn');
  const searchQ = document.getElementById('search-q');
  const searchResult = document.getElementById('search-result');

  // Check if critical elements exist
  console.log('[Chatbot] Elements found:', {
    chatbox: !!chatbox,
    input: !!input,
    send: !!send,
    quickLoans: !!quickLoans,
    quickVisits: !!quickVisits,
    searchBtn: !!searchBtn,
    searchQ: !!searchQ,
    searchResult: !!searchResult
  });

  if (!chatbox || !input || !send) {
    console.error('[Chatbot] Critical elements missing! Cannot initialize.');
    return;
  }

  function botSay(text) {
    appendMessage(chatbox, 'Bot', text);
    chatbox.scrollTop = chatbox.scrollHeight;
  }

  function userSay(text) {
    appendMessage(chatbox, 'You', text);
    chatbox.scrollTop = chatbox.scrollHeight;
  }

  async function runCommand(cmd, arg) {
    showTyping(true);
    try {
      switch(cmd) {
      case 'my_loans':
        userSay('Show my loans');
        const loans = await fetch('/wiet_lib/chatbot/api/bot.php?action=my_loans', {credentials:'include'}).then(r=>r.json());
        if (!loans.success) { botSay(loans.message||'Error'); return; }
        if (loans.data.length===0) { botSay('You have no active loans.'); } else {
          loans.data.forEach(l => botSay(`${l.Title} — due ${l.DueDate}${l.DaysOverdue>0? ' ('+l.DaysOverdue+'d overdue)': ''}`));
        }
        break;

      case 'due_books':
        userSay('Show due books');
        const due = await fetch('/wiet_lib/chatbot/api/bot.php?action=due_books', {credentials:'include'}).then(r=>r.json());
        if (!due.success) { botSay(due.message||'Error'); return; }
        if (due.data.length===0) { botSay('You have no due books.'); } else { due.data.forEach(d => botSay(`${d.Title} — due ${d.DueDate} (${d.status})`)); }
        break;

      case 'visit_count':
        userSay('Show my visits');
        const visits = await fetch('/wiet_lib/chatbot/api/bot.php?action=visit_count', {credentials:'include'}).then(r=>r.json());
        if (!visits.success) { botSay(visits.message||'Error'); return; }
        botSay(`Total visits: ${visits.data.total}. Last 30 days: ${visits.data.last_30_days}`);
        break;

      case 'history_summary':
        userSay('Show my history summary');
        const sum = await fetch('/wiet_lib/chatbot/api/bot.php?action=history_summary', {credentials:'include'}).then(r=>r.json());
        if (!sum.success) { botSay(sum.message||'Error'); return; }
        botSay(`Visits: ${sum.data.visits}. Borrows: ${sum.data.borrows}. Last borrow: ${sum.data.last_borrow||'N/A'}`);
        break;

      case 'search':
        userSay(arg||searchQ.value||'');
        await searchBooks(arg||searchQ.value, searchResult);
        botSay('Search results shown on the right');
        break;

      default:
        botSay('Sorry, I did not understand. Try: my loans, due books, my visits, search <title>');
        break;
      }
    } finally {
      showTyping(false);
    }
  }

  // typing indicator helper
  function showTyping(visible) {
    const t = document.getElementById('typing-indicator');
    if (!t) return;
    if (visible) {
      t.style.display = 'block';
      // animate dot
      if (!t._dotInterval) t._dotInterval = setInterval(() => {
        const d = document.getElementById('dot');
        if (!d) return;
        d.textContent = d.textContent.length < 3 ? d.textContent + '.' : '.';
      }, 400);
    } else {
      t.style.display = 'none';
      if (t._dotInterval) { clearInterval(t._dotInterval); t._dotInterval = null; }
    }
  }

  // Initialize quick view
  showMyLoans(quickLoans);
  showVisitCount(quickVisits);

  send.addEventListener('click', async () => {
    const txt = input.value.trim();
    if (!txt) return;

    userSay(txt);
    // Call backend 'ask' endpoint for context-aware handling
    try {
      showTyping(true);
      const res = await fetch('/wiet_lib/chatbot/api/bot.php?action=ask&q=' + encodeURIComponent(txt), {credentials:'include'}).then(r=>r.json());
      if (!res.success) { botSay(res.message || 'Error'); return; }

  if (res.reply) botSay(res.reply);

      // If action/data provided, render appropriately
      if (res.action && res.data) {
        if (res.action === 'my_loans' || res.action === 'due_books') {
          // show in chat a short list
          if (res.data.length === 0) {
            botSay('No items found.');
          } else {
            res.data.forEach(item => {
              botSay((item.Title || 'Untitled') + ' — Due: ' + (item.DueDate || 'N/A') + (item.DaysOverdue>0?(' ('+item.DaysOverdue+'d overdue)'):''));
            });
          }
        } else if (res.action === 'visit_count' || res.action === 'history_summary') {
          botSay(res.reply || JSON.stringify(res.data));
        } else if (res.action === 'search_books') {
          // render results on right side
          searchResult.innerHTML = '';
          if (!res.data || res.data.length === 0) {
            searchResult.innerText = 'No books found.';
          } else {
            // render card list with View + Reserve
            res.data.forEach(b => {
              const card = document.createElement('div');
              card.style.border = '1px solid #e6e6e6';
              card.style.padding = '10px';
              card.style.borderRadius = '8px';
              card.style.marginBottom = '8px';
              card.style.display = 'flex';
              card.style.justifyContent = 'space-between';
              card.style.alignItems = 'center';

              const info = document.createElement('div');
              info.innerHTML = `<div style="font-weight:700;color:#263c79">${b.Title}</div><div style="font-size:13px;color:#555">${b.Author1||''}</div><div style="font-size:12px;color:#666">Available: ${b.AvailableCopies||0}/${b.TotalCopies||0}</div>`;

              const actions = document.createElement('div');
              actions.style.display = 'flex';
              actions.style.gap = '8px';

              const view = document.createElement('a');
              view.href = '/wiet_lib/student/get_book_details.php?catno=' + encodeURIComponent(b.CatNo || b.CatNo);
              view.textContent = 'View';
              view.style.background = '#fff';
              view.style.border = '1px solid #cfac69';
              view.style.padding = '6px 10px';
              view.style.borderRadius = '6px';
              view.style.color = '#263c79';
              view.style.textDecoration = 'none';

              const reserve = document.createElement('button');
              reserve.textContent = (b.AvailableCopies||0) > 0 ? 'Borrow' : 'Reserve';
              reserve.disabled = false;
              reserve.style.padding = '6px 10px';
              reserve.style.borderRadius = '6px';
              reserve.style.border = 'none';
              reserve.style.background = '#263c79';
              reserve.style.color = '#fff';

              reserve.addEventListener('click', async () => {
                // If available, link to holding/borrow flow; else call reservation API
                if ((b.AvailableCopies||0) > 0) {
                  window.location.href = '/wiet_lib/student/get_book_details.php?catno=' + encodeURIComponent(b.CatNo || b.CatNo);
                  return;
                }
                reserve.disabled = true;
                reserve.textContent = 'Processing...';
                try {
                  const form = new URLSearchParams();
                  form.append('cat_no', b.CatNo);
                  const resp = await fetch('/wiet_lib/admin/api/reservations.php?action=reserve', { method: 'POST', body: form, credentials: 'include' });
                  const json = await resp.json();
                  if (json.success) {
                    botSay(json.message || 'Reserved successfully');
                    reserve.textContent = 'Reserved';
                    reserve.disabled = true;
                  } else {
                    botSay(json.message || 'Reservation failed');
                    reserve.disabled = false;
                    reserve.textContent = 'Reserve';
                  }
                } catch (e) {
                  console.error(e);
                  botSay('Reservation error');
                  reserve.disabled = false;
                  reserve.textContent = 'Reserve';
                }
              });

              actions.appendChild(view);
              actions.appendChild(reserve);

              card.appendChild(info);
              card.appendChild(actions);
              searchResult.appendChild(card);
            });
          }
        }
      }
    } catch (err) {
      console.error(err);
      botSay('Sorry, something went wrong.');
    }
    showTyping(false);
    input.value = '';
  });

  if (quick.length > 0) {
    quick.forEach(b => b.addEventListener('click', async (e) => {
      const cmd = e.currentTarget.getAttribute('data-cmd');
      console.log('[Chatbot] Quick button clicked:', cmd);
      await runCommand(cmd);
    }));
  }

  if (searchBtn) {
    searchBtn.addEventListener('click', async () => {
      console.log('[Chatbot] Search button clicked');
      await runCommand('search', searchQ.value.trim());
    });
  }

  // Initialize - show welcome message and load quick view
  setTimeout(() => {
    console.log('[Chatbot] Initialization starting...');
    botSay('Hello! I can help you with your library account. Ask me about loans, due dates, visits, or search for books.');
    if (quickLoans) showMyLoans(quickLoans);
    if (quickVisits) showVisitCount(quickVisits);
    console.log('[Chatbot] Initialization complete');
  }, 500);
})();
</script>
