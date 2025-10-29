<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: student_login.php');
    exit();
}
require_once __DIR__ . '/../includes/db_connect.php';

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function d($date){ return $date ? date('M j, Y', strtotime($date)) : ''; }
function t($time){ return $time ? date('g:i A', strtotime($time)) : ''; }

$events = [];
try {
    $sql = "SELECT e.*, (SELECT COUNT(*) FROM event_registrations er WHERE er.EventID = e.EventID) AS Registered FROM library_events e WHERE e.Status IN ('Active','Upcoming','Completed') ORDER BY CASE WHEN e.Status='Active' THEN 1 WHEN e.Status='Upcoming' THEN 2 ELSE 3 END, e.StartDate DESC";
    $events = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Events fetch error: ' . $e->getMessage());
}

$active   = array_values(array_filter($events, fn($ev)=>($ev['Status']??'')==='Active'));
$upcoming = array_values(array_filter($events, fn($ev)=>($ev['Status']??'')==='Upcoming'));
$completed= array_values(array_filter($events, fn($ev)=>($ev['Status']??'')==='Completed'));
?>
<style>
.tabs{display:flex;gap:10px;border-bottom:1px solid #e5e7eb;margin-bottom:16px}
.tab{background:none;border:none;padding:10px 14px;font-weight:700;color:#555;border-bottom:3px solid transparent;cursor:pointer}
.tab.active{color:#263c79;border-bottom-color:#cfac69}
.section{display:none}
.section.active{display:block}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px}
.card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,.06);padding:14px}
.title{margin:4px 0 0;font-size:18px;font-weight:800;color:#111827}
.row{margin:6px 0;color:#374151}
.footer{padding-top:12px;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center}
.bar{height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden;margin-top:6px}
.fill{height:100%;background:#cfac69}
.btn{background:#263c79;color:#fff;border:none;border-radius:8px;padding:8px 12px;font-weight:700;cursor:pointer}
.empty{text-align:center;color:#64748b;padding:24px;border:2px dashed #e5e7eb;border-radius:10px}
</style>

<h2 style="color:#263c79;margin:0 0 10px 0">Library Events</h2>
<div class="tabs" id="tabs">
  <button class="tab active" data-target="#sec-active">Active (<?php echo count($active); ?>)</button>
  <button class="tab" data-target="#sec-upcoming">Upcoming (<?php echo count($upcoming); ?>)</button>
  <button class="tab" data-target="#sec-completed">Completed (<?php echo count($completed); ?>)</button>
</div>

<div class="section active" id="sec-active">
  <?php if ($active): ?>
  <div class="grid">
    <?php foreach($active as $e): $cap=max(1,(int)($e['Capacity']??0)); $reg=(int)($e['Registered']??0); $pct=(int)min(100,max(0,round($reg*100/$cap))); ?>
    <div class="card">
      <div style="font-size:12px;color:#64748b;text-transform:uppercase;font-weight:700"><?php echo h($e['EventType']??'Event'); ?></div>
      <div class="title"><?php echo h($e['EventTitle']??''); ?></div>
      <?php if(!empty($e['Description'])): ?><div class="row"><?php echo h($e['Description']); ?></div><?php endif; ?>
      <div class="row"><b>Date:</b> <?php echo d($e['StartDate']??null); ?><?php if(($e['StartDate']??null)&&($e['EndDate']??null)&&$e['StartDate']!==$e['EndDate']): ?> - <?php echo d($e['EndDate']); ?><?php endif; ?></div>
      <div class="row"><b>Time:</b> <?php echo t($e['StartTime']??null); ?><?php if(!empty($e['EndTime'])): ?> - <?php echo t($e['EndTime']); ?><?php endif; ?></div>
      <div class="row"><b>Venue:</b> <?php echo h($e['Venue']??''); ?></div>
      <div class="row"><b>Organizer:</b> <?php echo h($e['OrganizedBy']??''); ?></div>
      <div class="footer">
        <div style="flex:1">
          <div><?php echo $reg; ?>/<?php echo $cap; ?> registered</div>
          <div class="bar"><div class="fill" style="width:<?php echo $pct; ?>%"></div></div>
        </div>
        <button class="btn" onclick="alert('Registration coming soon')">Register</button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?><div class="empty">No Active Events</div><?php endif; ?>
</div>

<div class="section" id="sec-upcoming">
  <?php if ($upcoming): ?>
  <div class="grid">
    <?php foreach($upcoming as $e): $cap=max(1,(int)($e['Capacity']??0)); $reg=(int)($e['Registered']??0); $pct=(int)min(100,max(0,round($reg*100/$cap))); ?>
    <div class="card">
      <div style="font-size:12px;color:#64748b;text-transform:uppercase;font-weight:700"><?php echo h($e['EventType']??'Event'); ?></div>
      <div class="title"><?php echo h($e['EventTitle']??''); ?></div>
      <?php if(!empty($e['Description'])): ?><div class="row"><?php echo h($e['Description']); ?></div><?php endif; ?>
      <div class="row"><b>Date:</b> <?php echo d($e['StartDate']??null); ?><?php if(($e['StartDate']??null)&&($e['EndDate']??null)&&$e['StartDate']!==$e['EndDate']): ?> - <?php echo d($e['EndDate']); ?><?php endif; ?></div>
      <div class="row"><b>Time:</b> <?php echo t($e['StartTime']??null); ?><?php if(!empty($e['EndTime'])): ?> - <?php echo t($e['EndTime']); ?><?php endif; ?></div>
      <div class="row"><b>Venue:</b> <?php echo h($e['Venue']??''); ?></div>
      <div class="row"><b>Organizer:</b> <?php echo h($e['OrganizedBy']??''); ?></div>
      <div class="footer">
        <div style="flex:1">
          <div><?php echo $reg; ?>/<?php echo $cap; ?> registered</div>
          <div class="bar"><div class="fill" style="width:<?php echo $pct; ?>%"></div></div>
        </div>
        <button class="btn" onclick="alert('Registration coming soon')">Register</button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?><div class="empty">No Upcoming Events</div><?php endif; ?>
</div>

<div class="section" id="sec-completed">
  <?php if ($completed): ?>
  <div class="grid">
    <?php foreach($completed as $e): ?>
    <div class="card">
      <div style="font-size:12px;color:#64748b;text-transform:uppercase;font-weight:700"><?php echo h($e['EventType']??'Event'); ?></div>
      <div class="title"><?php echo h($e['EventTitle']??''); ?></div>
      <?php if(!empty($e['Description'])): ?><div class="row"><?php echo h($e['Description']); ?></div><?php endif; ?>
      <div class="row"><b>Date:</b> <?php echo d($e['StartDate']??null); ?><?php if(($e['StartDate']??null)&&($e['EndDate']??null)&&$e['StartDate']!==$e['EndDate']): ?> - <?php echo d($e['EndDate']); ?><?php endif; ?></div>
      <div class="row"><b>Venue:</b> <?php echo h($e['Venue']??''); ?></div>
      <div class="footer"><div><?php echo (int)($e['Registered']??0); ?> attended</div></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?><div class="empty">No Completed Events</div><?php endif; ?>
</div>

<script>
(function(){
  const tabs=[...document.querySelectorAll('#tabs .tab')];
  const secs={'#sec-active':document.getElementById('sec-active'),'#sec-upcoming':document.getElementById('sec-upcoming'),'#sec-completed':document.getElementById('sec-completed')};
  tabs.forEach(btn=>btn.addEventListener('click',()=>{
    tabs.forEach(b=>b.classList.remove('active'));
    Object.values(secs).forEach(s=>s.classList.remove('active'));
    btn.classList.add('active');
    const t=btn.getAttribute('data-target');
    if(secs[t]) secs[t].classList.add('active');
  }));
})();
</script>
