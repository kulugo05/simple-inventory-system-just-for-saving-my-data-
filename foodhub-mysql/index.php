<?php
// ============================================================
// MAIN PAGE - index.php
// Ito ang pangunahing page ng sistema pagkatapos mag-login.
// Single-page application - lahat ng views ay nilo-load dito
// gamit ang JavaScript na walang page reload.
// ============================================================

session_start();

// Kung hindi pa naka-login, i-redirect sa login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Bron Michael's FoodHub — Inventory</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>

<!-- Chart.js library para sa Sales Graph ng admin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>

<style>
/* =============================================================
   CSS VARIABLES AT RESET
   Dito nakatakda ang lahat ng kulay at font na ginagamit
   sa buong sistema. Para baguhin ang kulay, dito lang palitan.
============================================================= */
*,*::before,*::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --bg:        #0d0d0f;   /* Main background */
    --bg2:       #141416;   /* Card background */
    --bg3:       #1c1c1f;   /* Input background */
    --border:    #2a2a30;   /* Default border */
    --border2:   #3a3a42;   /* Hover border */
    --text:      #f0ede8;   /* Primary text */
    --text2:     #9d9a94;   /* Secondary text */
    --text3:     #5a5750;   /* Muted text */
    --accent:    #ff6b35;   /* Orange - main color */
    --accent2:   #ff8c5a;   /* Orange hover */
    --accentbg:  rgba(255,107,53,.12);
    --green:     #22c55e;
    --greenbg:   rgba(34,197,94,.12);
    --amber:     #f59e0b;
    --amberbg:   rgba(245,158,11,.12);
    --red:       #ef4444;
    --redbg:     rgba(239,68,68,.12);
    --blue:      #3b82f6;
    --bluebg:    rgba(59,130,246,.12);
    --r:  12px;  /* Border radius large */
    --r2:  8px;  /* Border radius medium */
    --r3:  6px;  /* Border radius small */
    --shadow: 0 4px 24px rgba(0,0,0,.4);
    --font-head: 'Syne', sans-serif;
    --font-body: 'DM Sans', sans-serif;
}

html { font-size: 15px; background: var(--bg); }
body {
    font-family: var(--font-body);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    overflow-x: hidden;
}

/* =============================================================
   SIDEBAR STYLES
   Nakalagay sa kaliwa, fixed position para hindi gumalaw
============================================================= */
#sidebar {
    width: 240px;
    min-height: 100vh;
    background: var(--bg2);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0;
    z-index: 100;
    transition: transform .3s ease;
}
.sidebar-logo {
    padding: 30px 25px 25px;
    border-bottom: 1px solid var(--border);
}
.logo-name {
    font-family: var(--font-head);
    font-weight: 800;
    font-size: 16px;
    line-height: 2;
}
.logo-sub {
    font-size: 11px;
    color: var(--text3);
    letter-spacing: .05em;
    text-transform: uppercase;
    margin-top: 5px;
}
.nav-section { padding: 16px 12px 8px; flex: 1; }
.nav-label {
    font-size: 10px;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--text3);
    padding: 0 8px;
    margin-bottom: 6px;
}
.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    border-radius: var(--r3);
    cursor: pointer;
    font-size: 14px;
    color: var(--text2);
    transition: all .15s ease;
    margin-bottom: 5px;
    margin-top: 25px;
    border: 1px solid transparent;
    padding: 1px 10px;
}
.nav-item:hover { background: var(--bg3); color: var(--text); }
.nav-item.active {
    background: var(--accentbg);
    color: var(--accent);
    border-color: rgba(255,107,53,.2);
}
.nav-item .icon { font-size: 16px; width: 20px; text-align: center; margin: 5px; }
.nav-item .badge {
    margin-left: auto;
    background: var(--red);
    color: #fff;
    border-radius: 99px;
    font-size: 10px;
    padding: 1px 6px;
    font-weight: 600;
}
.sidebar-footer {
    padding: 16px;
    border-top: 1px solid var(--border);
    font-size: 12px;
    color: var(--text3);
}

/* =============================================================
   MAIN CONTENT AREA
============================================================= */
#main { margin-left: 240px; flex: 1; min-height: 100vh; display: flex; flex-direction: column; }

/* Topbar - nakalagay sa taas, nananatili kahit mag-scroll */
#topbar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 30px 28px;
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0; z-index: 50;
    backdrop-filter: blur(8px);
}
.topbar-title {
    font-family: var(--font-head);
    font-weight: 700;
    font-size: 20px;
    flex: 1;
}
.search-wrap { position: relative; width: 280px; }
.search-wrap input {
    width: 100%;
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: var(--r2);
    padding: 8px 12px 8px 36px;
    color: var(--text);
    font-family: var(--font-body);
    font-size: 13px;
    outline: none;
    transition: border-color .2s;
}
.search-wrap input:focus { border-color: var(--accent); }
.search-wrap::before {
    content: '⌕';
    position: absolute;
    left: 11px; top: 50%;
    transform: translateY(-50%);
    color: var(--text3);
    font-size: 16px;
}

/* Content area kung saan naglo-load ang bawat view */
#content { padding: 28px; flex: 1; }

/* =============================================================
   BUTTONS
============================================================= */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: var(--r2);
    font-family: var(--font-body);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    outline: none;
    transition: all .15s ease;
}
.btn-primary { background: var(--accent2); color: #fff; }
.btn-primary:hover { background: var(--text3); transform: translateY(-1px); }
.btn-ghost { background: transparent; color: var(--text2); border: 1px solid var(--border); }
.btn-ghost:hover { background: var(--bg3); color: var(--text); }
.btn-danger { background: var(--redbg); color: var(--red); border: 1px solid rgba(239,68,68,.2); }
.btn-danger:hover { background: var(--red); color: #fff; }
.btn-sm { padding: 5px 12px; font-size: 12px; }

/* =============================================================
   STAT CARDS - mga summary numbers sa dashboard
============================================================= */
.stats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 28px; }
.stat-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 20px;
    position: relative;
    overflow: hidden;
    transition: border-color .2s, transform .2s;
}
.stat-card:hover { border-color: var(--border2); transform: translateY(-2px); }
/* Ang colored line sa taas ng bawat card */
.stat-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--card-color, var(--accent));
    border-radius: var(--r) var(--r) 0 0;
}
.stat-value { font-family: var(--font-head); font-size: 28px; font-weight: 700; line-height: 1; }
.stat-label { font-size: 12px; color: var(--text2); margin-top: 6px; text-transform: uppercase; letter-spacing: .05em; }

/* =============================================================
   TABLE STYLES
============================================================= */
.table-wrap { background: var(--bg2); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; }
.table-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}
.table-header-title { font-family: var(--font-head); font-weight: 600; font-size: 15px; flex: 1; }
table { width: 100%; border-collapse: collapse; }
thead th {
    padding: 10px 16px;
    font-size: 11px;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--text3);
    font-weight: 600;
    text-align: left;
    border-bottom: 1px solid var(--border);
    background: var(--bg3);
    white-space: nowrap;
}
tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: var(--bg3); }
tbody td { padding: 12px 16px; font-size: 13px; vertical-align: middle; }
.td-sku { font-family: monospace; font-size: 12px; color: var(--text2); background: var(--bg3); padding: 2px 6px; border-radius: 4px; }
.td-actions { display: flex; gap: 6px; }

/* =============================================================
   BADGES - para sa status indicators
============================================================= */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 99px; font-size: 11px; font-weight: 600; }
.badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.badge-green  { background: var(--greenbg); color: var(--green); }
.badge-amber  { background: var(--amberbg); color: var(--amber); }
.badge-red    { background: var(--redbg);   color: var(--red); }
.badge-blue   { background: var(--bluebg);  color: var(--blue); }
.b-blue       { background: var(--bluebg);  color: var(--blue); }

/* =============================================================
   MODAL STYLES - para sa mga popup forms
============================================================= */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.2);
    z-index: 200;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity .2s;
    backdrop-filter: blur(4px);
}
.modal-overlay.open { opacity: 1; pointer-events: all; }
.modal {
    background: var(--bg2);
    border: 1px solid var(--border2);
    border-radius: var(--r);
    width: 560px;
    max-width: 94vw;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--shadow);
    transform: translateY(16px);
    transition: transform .2s;
}
.modal-overlay.open .modal { transform: translateY(0); }
.modal-head {
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-head h2 { font-family: var(--font-head); font-weight: 700; font-size: 18px; }
.modal-close {
    width: 28px; height: 28px;
    border-radius: 6px;
    background: transparent;
    border: none;
    color: var(--text2);
    cursor: pointer;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .15s;
}
.modal-close:hover { background: var(--bg3); }
.modal-body { padding: 20px 24px; }
.modal-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; gap: 8px; justify-content: flex-end; }

/* =============================================================
   FORM STYLES
============================================================= */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { display: flex; flex-direction: column; gap: 5px; }
.form-group.full { grid-column: 1/-1; }
.form-group label { font-size: 11px; font-weight: 600; color: var(--text2); text-transform: uppercase; letter-spacing: .05em; }
.form-group input,
.form-group select,
.form-group textarea {
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: var(--r3);
    padding: 9px 11px;
    color: var(--text);
    font-family: var(--font-body);
    font-size: 13px;
    outline: none;
    transition: border-color .2s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { border-color: var(--accent); }
.form-group textarea { resize: vertical; min-height: 70px; }

/* Para sa Sales form fields */
.fg { margin-bottom: 14px; }
.fg label { display: block; font-size: 11px; font-weight: 600; color: var(--text2); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 5px; }
.fg input,
.fg select { width: 100%; background: var(--bg3); border: 1px solid var(--border); border-radius: var(--r3); padding: 9px 11px; color: var(--text); font-family: var(--font-body); font-size: 13px; outline: none; transition: border-color .2s; }
.fg input:focus,
.fg select:focus { border-color: var(--accent); }
.fg .hint { font-size: 11px; color: var(--text3); margin-top: 4px; }

/* Total display sa Sales modal */
.total-box {
    background: var(--bg3);
    border-radius: var(--r2);
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.total-val { font-family: var(--font-head); font-size: 20px; font-weight: 700; color: var(--accent); }

/* =============================================================
   STOCK ALERTS (Dashboard)
============================================================= */
.alerts-list { display: flex; flex-direction: column; gap: 8px; }
.alert-item {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: var(--r2);
    padding: 12px 14px;
}
.alert-icon { font-size: 18px; }
.alert-info { flex: 1; }
.alert-name { font-weight: 500; font-size: 13px; }
.alert-sku { font-size: 11px; color: var(--text3); margin-top: 2px; font-family: monospace; }
.alert-qty { text-align: right; font-family: var(--font-head); font-weight: 700; font-size: 18px; }
.alert-qty-label { font-size: 10px; color: var(--text3); text-align: right; margin-top: 1px; }

/* =============================================================
   ADJUST STOCK BUTTONS
============================================================= */
.adj-btns { display: flex; gap: 8px; }
.adj-type {
    flex: 1;
    padding: 10px;
    border-radius: var(--r2);
    border: 1px solid var(--border);
    background: transparent;
    cursor: pointer;
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--text2);
    transition: all .15s;
    text-align: center;
}
.adj-type.selected-add    { background: var(--greenbg); color: var(--green); border-color: rgba(34,197,94,.3); }
.adj-type.selected-remove { background: var(--redbg); color: var(--red); border-color: rgba(239,68,68,.3); }

/* =============================================================
   STOCK LOGS
============================================================= */
.log-row { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-bottom: 1px solid var(--border); font-size: 13px; }
.log-type { width: 22px; height: 22px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px; }
.log-add    { background: var(--greenbg); color: var(--green); }
.log-remove { background: var(--redbg);   color: var(--red); }

/* =============================================================
   TOAST NOTIFICATION - lumilitaw sa kanan-baba
============================================================= */
#toast {
    position: fixed;
    bottom: 24px; right: 24px;
    z-index: 999;
    background: var(--bg2);
    border: 1px solid var(--border2);
    border-radius: var(--r2);
    padding: 12px 16px;
    font-size: 13px;
    box-shadow: var(--shadow);
    transform: translateY(8px);
    opacity: 0;
    transition: all .25s ease;
    pointer-events: none;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 220px;
}
#toast.show  { transform: translateY(0); opacity: 1; }
#toast.success::before { content: '✓'; color: var(--green); }
#toast.error::before   { content: '✕'; color: var(--red); }
#toast.info::before    { content: 'ℹ'; color: var(--blue); }

/* Loading spinner para habang naglo-load ang data */
.loading { text-align: center; padding: 40px; color: var(--text3); }
@keyframes spin { to { transform: rotate(360deg); } }
.spinner {
    width: 24px; height: 24px;
    border: 2px solid var(--border);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin .6s linear infinite;
    margin: 0 auto 10px;
}

/* Responsive para sa mas maliit na screen */
@media(max-width:900px) {
    #sidebar { transform: translateX(-100%); }
    #sidebar.open { transform: none; }
    #main { margin-left: 0; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .form-grid { grid-template-columns: 1fr; }
}
@media(max-width:540px) {
    .stats-grid { grid-template-columns: 1fr; }
    .search-wrap { width: 160px; }
}
</style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════
     SIDEBAR - Navigation menu sa kaliwa
═══════════════════════════════════════════════════════ -->
<aside id="sidebar">
    <div class="sidebar-logo">
        <img src="logo.png" alt="BM Logo"
             style="width:70px;height:70px;border-radius:10px;object-fit:cover;display:block;margin:0 auto 20px;"/>
        <div class="logo-name">Bron Michael's</div>
        <div class="logo-sub">FoodHub Inventory</div>
    </div>

    <nav class="nav-section">
        <div class="nav-label">Menu</div>

        <!-- Mga navigation items - ginagamit ang data-view para malaman kung anong page ipapakita -->
        <div class="nav-item active" data-view="dashboard">
            <span class="icon">⊞</span> Dashboard
        </div>
        <div class="nav-item" data-view="inventory">
            <span class="icon">☰</span> Inventory
            <!-- Badge na nagpapakita ng bilang ng low stock items -->
            <span class="badge" id="nav-alerts" style="display:none">0</span>
        </div>
        <div class="nav-item" data-view="categories">
            <span class="icon">⊟</span> Categories
        </div>
        <div class="nav-item" data-view="sales">
            <span class="icon">💰</span> Sales
        </div>
        <div class="nav-item" data-view="logs">
            <span class="icon">⊙</span> Stock Logs
        </div>

        <!-- Admin-only navigation items - hindi makikita ng staff -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <div class="nav-label" style="margin-top:20px;">Admin</div>
        <div class="nav-item" data-view="accounts">
            <span class="icon">👤</span> User Accounts
        </div>
        <div class="nav-item" data-view="activestaff">
            <span class="icon">⊕</span> Active Staff
        </div>
        <?php endif; ?>
    </nav>

    <!-- Clock sa ibaba ng sidebar -->
    <div class="sidebar-footer" id="clock" style="text-align:center;">--:--:--</div>
</aside>

<!-- ═══════════════════════════════════════════════════════
     MAIN CONTENT - Topbar at content area
═══════════════════════════════════════════════════════ -->
<div id="main">
    <!-- Topbar na naglalaman ng title, search, at user info -->
    <div id="topbar">
        <span class="topbar-title" id="topbar-title">Dashboard</span>
        <div class="search-wrap">
            <input type="text" id="global-search" placeholder="Search items…"/>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <!-- Pangalan ng naka-login na user -->
            <span style="font-size:13px;color:var(--text2);">
                👤 <?php echo htmlspecialchars($_SESSION['full_name']); ?>
            </span>
            <!-- Sign Out button -->
            <a href="logout.php"
               style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;
                      background:var(--redbg);color:var(--red);
                      border:1px solid rgba(239,68,68,.2);
                      border-radius:var(--r2);font-size:13px;text-decoration:none;">
                Sign Out
            </a>
        </div>
    </div>

    <!-- Dito naglo-load ang content ng bawat view -->
    <div id="content"></div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODALS - Mga popup forms
═══════════════════════════════════════════════════════ -->

<!-- Modal para sa Pag-add at Pag-edit ng Inventory Item -->
<div class="modal-overlay" id="modal-item">
    <div class="modal">
        <div class="modal-head">
            <h2 id="modal-item-title">Add Item</h2>
            <button class="modal-close" onclick="closeModal('modal-item')">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="item-id"/>
            <div class="form-grid">
                <div class="form-group">
                    <label>Item Name*</label>
                    <input type="text" id="f-name" placeholder="e.g. Chicken Breast"/>
                </div>
                <div class="form-group">
                    <label>SKU*</label>
                    <input type="number" id="f-sku" placeholder="e.g. 1001"/>
                </div>
                <div class="form-group">
                    <label>Category*</label>
                    <select id="f-category"><option value="">Select…</option></select>
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <select id="f-unit">
                        <option>pcs</option><option>kg</option><option>g</option>
                        <option>L</option><option>mL</option><option>pack</option>
                        <option>box</option><option>bag</option><option>dozen</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity*</label>
                    <input type="number" id="f-qty" min="0" placeholder="0"/>
                </div>
                <div class="form-group">
                    <label>Reorder Level</label>
                    <input type="number" id="f-reorder" min="0" placeholder="10"/>
                </div>
                <div class="form-group">
                    <label>Cost Price (₱)*</label>
                    <input type="number" id="f-cost" min="0" step="0.01" placeholder="0.00"/>
                </div>
                <div class="form-group">
                    <label>Sell Price (₱)</label>
                    <input type="number" id="f-sell" min="0" step="0.01" placeholder="0.00"/>
                </div>
                <div class="form-group full">
                    <label>Supplier</label>
                    <input type="text" id="f-supplier" placeholder="Supplier name"/>
                </div>
                <div class="form-group full">
                    <label>Description</label>
                    <textarea id="f-desc" placeholder="Optional notes…"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('modal-item')">Cancel</button>
            <button class="btn btn-primary" id="btn-save-item">Save Item</button>
        </div>
    </div>
</div>

<!-- Modal para sa Pag-adjust ng Stock (Add/Remove) -->
<div class="modal-overlay" id="modal-adjust">
    <div class="modal" style="width:400px">
        <div class="modal-head">
            <h2>Adjust Stock</h2>
            <button class="modal-close" onclick="closeModal('modal-adjust')">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="adj-id"/>
            <p id="adj-item-name" style="font-weight:600;margin-bottom:16px;font-size:15px"></p>
            <div class="adj-btns" style="margin-bottom:16px">
                <button class="adj-type selected-add" id="adj-add" onclick="selectAdjType('add')">＋ Add Stock</button>
                <button class="adj-type" id="adj-remove" onclick="selectAdjType('remove')">－ Remove Stock</button>
            </div>
            <div class="form-group" style="margin-bottom:12px">
                <label>Amount</label>
                <input type="number" id="adj-amount" min="1" placeholder="Enter quantity"/>
            </div>
            <div class="form-group">
                <label>Reason</label>
                <input type="text" id="adj-reason" placeholder="e.g. Delivery received, Sold…"/>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('modal-adjust')">Cancel</button>
            <button class="btn btn-primary" id="btn-save-adj">Confirm</button>
        </div>
    </div>
</div>

<!-- Modal para sa Pagpapakita ng Items sa isang Category -->
<div class="modal-overlay" id="modal-cat-items">
    <div class="modal" style="width:680px">
        <div class="modal-head">
            <h2 id="cat-modal-title">Category Items</h2>
            <button class="modal-close" onclick="closeModal('modal-cat-items')">✕</button>
        </div>
        <div class="modal-body" id="cat-modal-body"></div>
    </div>
</div>

<!-- Modal para sa Pag-record ng Sale -->
<div class="modal-overlay" id="modal-sale">
    <div class="modal">
        <div class="modal-head">
            <h2>Record Sale</h2>
            <button class="modal-close" onclick="closeModal('modal-sale')">✕</button>
        </div>
        <div class="modal-body">
            <div class="fg">
                <label>Category</label>
                <select id="sale-category" onchange="loadMenuItems(this.value)">
                    <option value="">Select category…</option>
                    <option>Sizzling</option>
                    <option>Main Course - Chicken</option>
                    <option>Main Course - Beef</option>
                    <option>Main Course - Pork</option>
                    <option>Main Course - Seafood</option>
                    <option>Breakfast & Silog</option>
                    <option>Noodles & Pasta</option>
                    <option>Snacks & Appetizer</option>
                    <option>Sandwiches & Burgers</option>
                    <option>Milktea</option>
                    <option>Frappe</option>
                    <option>Fruit Juice</option>
                    <option>Beverages</option>
                    <option>Vegetables</option>
                </select>
            </div>
            <div class="fg">
                <label>Menu Item</label>
                <select id="sale-item" onchange="setSalePrice(this)">
                    <option value="">Select item…</option>
                </select>
            </div>
            <div class="fg">
                <label>Quantity</label>
                <input type="number" id="sale-qty" min="1" value="1" oninput="updateSaleTotal()"/>
            </div>
            <div class="fg">
                <label>Price (₱)</label>
                <input type="number" id="sale-price" min="0" step="0.01" placeholder="0.00" oninput="updateSaleTotal()"/>
                <div class="hint">Auto-filled — pwede baguhin kung may discount</div>
            </div>
            <div class="total-box">
                <span style="font-size:13px;color:var(--text2);">Total</span>
                <span class="total-val" id="sale-total">₱0.00</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('modal-sale')">Cancel</button>
            <button class="btn btn-primary" onclick="saveSale()">✅ Record Sale</button>
        </div>
    </div>
</div>

<!-- Modal para sa Pag-add ng Bagong User Account (Admin only) -->
<div class="modal-overlay" id="modal-add-user">
    <div class="modal" style="width:460px">
        <div class="modal-head">
            <h2>Add Account</h2>
            <button class="modal-close" onclick="closeModal('modal-add-user')">✕</button>
        </div>
        <div class="modal-body">
            <div class="fg"><label>Full Name</label><input type="text" id="u-name" placeholder="Juan dela Cruz"/></div>
            <div class="fg"><label>Username</label><input type="text" id="u-user" placeholder="juan123"/></div>
            <div class="fg"><label>Password</label><input type="password" id="u-pass" placeholder="Password"/></div>
            <div class="fg">
                <label>Role</label>
                <select id="u-role">
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('modal-add-user')">Cancel</button>
            <button class="btn btn-primary" onclick="saveNewUser()">Save</button>
        </div>
    </div>
</div>

<!-- Modal para sa Pag-reset ng Password ng User -->
<div class="modal-overlay" id="modal-reset-user">
    <div class="modal" style="width:420px">
        <div class="modal-head">
            <h2>Reset Password</h2>
            <button class="modal-close" onclick="closeModal('modal-reset-user')">✕</button>
        </div>
        <div class="modal-body">
            <p id="reset-user-name" style="font-size:13px;color:var(--text2);margin-bottom:14px;"></p>
            <div class="fg">
                <label>New Password</label>
                <input type="password" id="reset-user-pass" placeholder="New password"/>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('modal-reset-user')">Cancel</button>
            <button class="btn btn-primary" onclick="confirmResetUser()">Reset</button>
        </div>
    </div>
</div>

<!-- Toast notification element -->
<div id="toast"></div>

<!-- ═══════════════════════════════════════════════════════
     JAVASCRIPT - Lahat ng logic ng sistema
═══════════════════════════════════════════════════════ -->
<script>

// ─────────────────────────────────────────────────────────
// CLOCK - Ina-update ang footer clock tuwing segundo
// ─────────────────────────────────────────────────────────
function updateClock() {
    const now  = new Date();
    const time = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    const date = now.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('clock').textContent = date + ' · ' + time;
}
updateClock();
setInterval(updateClock, 1000); // I-update tuwing 1 segundo

// ─────────────────────────────────────────────────────────
// API ENDPOINTS - Base URLs ng lahat ng API calls
// ─────────────────────────────────────────────────────────
const API = {
    items:      '/foodhub-mysql/api/items.php',
    reports:    '/foodhub-mysql/api/reports.php',
    adjust:     '/foodhub-mysql/api/stock.php',
    logs:       '/foodhub-mysql/api/logs.php',
    categories: '/foodhub-mysql/api/categories.php',
};
const API_SALES = '/foodhub-mysql/api/sales.php';
const API_MENU  = '/foodhub-mysql/api/menu_items.php';
const API_USERS = '/foodhub-mysql/api/users.php';

// Estado ng kasalukuyang view at stock adjustment type
let currentView = 'dashboard';
let adjType     = 'add';

// ─────────────────────────────────────────────────────────
// HELPER FUNCTIONS - Ginagamit sa buong code
// ─────────────────────────────────────────────────────────

// Nagse-send ng API request at nagre-return ng JSON response
async function req(url, method = 'GET', body = null) {
    try {
        const opts = { method, headers: { 'Content-Type': 'application/json' } };
        if (body) opts.body = JSON.stringify(body);
        const res = await fetch(url, opts);
        return await res.json();
    } catch (e) {
        console.error("API Error:", e);
        return null;
    }
}

// Nagpapakita ng toast notification sa kanan-baba ng screen
function toast(msg, type = 'success') {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.className   = 'show ' + type;
    setTimeout(() => { t.className = ''; }, 3000);
}

// Nagco-convert ng string sa HTML-safe na format (para maiwasan ang XSS)
function esc(s) {
    const d = document.createElement('div');
    d.textContent = String(s || '');
    return d.innerHTML;
}

// Nagbubukas/nagsasara ng modal
function openModal(id)  { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

// Format ng peso amount
function fmt(n)      { return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2 }); }
function fmtSales(n) { return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2 }); }

// ─────────────────────────────────────────────────────────
// NAVIGATION - Nagha-handle ng pagpapalit ng views
// ─────────────────────────────────────────────────────────

// I-attach ang click event sa bawat nav item
document.querySelectorAll('.nav-item').forEach(el => {
    el.addEventListener('click', () => {
        const view = el.dataset.view;
        if (view) switchView(view);
    });
});

// Nagpapalit ng active view
function switchView(view) {
    // I-update ang active state ng sidebar items
    document.querySelectorAll('.nav-item').forEach(x => {
        x.classList.toggle('active', x.dataset.view === view);
    });
    // I-update ang title sa topbar
    document.getElementById('topbar-title').textContent =
        view.charAt(0).toUpperCase() + view.slice(1);
    renderView(view);
}

// Naglo-load ng tamang view batay sa pinili
async function renderView(view) {
    currentView = view;
    // Ipakita ang loading spinner habang naglo-load
    document.getElementById('content').innerHTML =
        '<div class="loading"><div class="spinner"></div>Loading…</div>';

    if      (view === 'dashboard')   await renderDashboard();
    else if (view === 'inventory')   await renderInventory();
    else if (view === 'categories')  await renderCategories();
    else if (view === 'logs')        await renderLogs();
    else if (view === 'sales')       await renderSales();
    else if (view === 'accounts')    await renderAccounts();
    else if (view === 'activestaff') await renderActiveStaff();
}

// ─────────────────────────────────────────────────────────
// DASHBOARD VIEW
// Nagpapakita ng stats, best sellers, stock alerts,
// at sales graph (admin lang)
// ─────────────────────────────────────────────────────────
async function renderDashboard() {
    const res = await req(API.reports);
    if (!res) return;

    // I-update ang low stock badge sa sidebar
    const alertCount = (Number(res.low_stock_count) || 0) + (Number(res.out_of_stock) || 0);
    const navBadge   = document.getElementById('nav-alerts');
    if (navBadge) {
        navBadge.textContent  = alertCount;
        navBadge.style.display = alertCount > 0 ? 'flex' : 'none';
    }

    // Gumawa ng HTML para sa stock alerts
    let alertRows = (res.alerts || []).map(a => `
        <div class="alert-item">
            <div class="alert-icon">${a.status === 'out_of_stock' ? '🔴' : '🟡'}</div>
            <div class="alert-info">
                <div class="alert-name">${esc(a.name)}</div>
                <div class="alert-sku">${esc(a.sku)}</div>
            </div>
            <div style="text-align:right">
                <div class="alert-qty" style="color:${a.status === 'out_of_stock' ? 'var(--red)' : 'var(--amber)'}">
                    ${a.quantity}
                </div>
                <div class="alert-qty-label">/ ${a.reorder_lvl} reorder</div>
            </div>
        </div>`).join('');

    // I-render ang dashboard content
    document.getElementById('content').innerHTML = `
        <!-- 4 summary stat cards -->
        <div class="stats-grid">
            <div class="stat-card" style="--card-color:var(--accent)">
                <div class="stat-value">${res.total_products}</div>
                <div class="stat-label">Total Items</div>
            </div>
            <div class="stat-card" style="--card-color:var(--blue)">
                <div class="stat-value">${fmt(res.total_value)}</div>
                <div class="stat-label">Total Value</div>
            </div>
            <div class="stat-card" style="--card-color:var(--amber)">
                <div class="stat-value">${res.low_stock_count}</div>
                <div class="stat-label">Low Stock</div>
            </div>
            <div class="stat-card" style="--card-color:var(--red)">
                <div class="stat-value">${res.out_of_stock}</div>
                <div class="stat-label">Out of Stock</div>
            </div>
        </div>

        <!-- Best Sellers at Stock Alerts side by side -->
        <div style="display:grid;grid-template-columns:1fr 340px;gap:20px">
            <!-- Best Sellers na may category dropdown filter -->
            <div class="table-wrap">
                <div class="table-header">
                    <span class="table-header-title">🏆 Best Selling Products</span>
                    <div style="position:relative;" id="best-dropdown-wrap">
                        <button onclick="toggleBestDropdown()" id="best-btn"
                                style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;
                                       background:var(--bg3);border:1px solid var(--border);
                                       border-radius:6px;font-size:12px;color:var(--text2);cursor:pointer;">
                            <span id="best-cat-label">All Categories</span> ▾
                        </button>
                        <!-- Dropdown menu ng categories -->
                        <div id="best-dropdown"
                             style="display:none;position:absolute;top:calc(100% + 4px);right:0;
                                    background:var(--bg3);border:1px solid var(--border2);
                                    border-radius:8px;min-width:200px;z-index:100;
                                    box-shadow:0 8px 24px rgba(0,0,0,.5);overflow-y:auto;max-height:300px;">
                            <div onclick="filterBestSellers('all','All Categories')" style="padding:9px 14px;font-size:12px;color:var(--text2);cursor:pointer;" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background='transparent'">🍽️ All Categories</div>
                            <div style="height:1px;background:var(--border);margin:4px 0;"></div>
                            <div onclick="filterBestSellers('Sizzling','Sizzling')" style="padding:9px 14px;font-size:12px;color:var(--text2);cursor:pointer;" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background='transparent'">🍳 Sizzling</div>
                            <div onclick="filterBestSellers('Main Course - Chicken','Chicken')" style="padding:9px 14px;font-size:12px;color:var(--text2);cursor:pointer;" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background='transparent'">🍗 Chicken</div>
                            <div onclick="filterBestSellers('Main Course - Beef','Beef')" style="padding:9px 14px;font-size:12px;color:var(--text2);cursor:pointer;" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background='transparent'">🥩 Beef</div>
                            <div onclick="filterBestSellers('Main Course - Pork','Pork')" style="padding:9px 14px;font-size:12px;color:var(--text2);cursor:pointer;" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background='transparent'">🐷 Pork</div>
                            <div onclick="filterBestSellers('Main Course - Seafood','Seafood')" style="padding:9px 14px;font-size:12px;color:var(--text2);cursor:pointer;" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background='transparent'">🦐 Seafood</div>
                            <div onclick="filterBestSellers('Breakfast & Silog','Breakfast')" style="padding:9px 14px;font-size:12px;color:var(--text2);cursor:pointer;" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background='transparent'">🍳 Breakfast</div>
                            <div onclick="filterBestSellers('Noodles & Pasta','Noodles & Pasta')" style="padding:9px 14px;font-size:12px;color:var(--text2);cursor:pointer;" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background='transparent'">🍜 Noodles & Pasta</div>
                            <div onclick="filterBestSellers('Snacks & Appetizer','Snacks')" style="padding:9px 14px;font-size:12px;color:var(--text2);cursor:pointer;" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background='transparent'">🍟 Snacks</div>
                            <div onclick="filterBestSellers('Milktea','Milktea')" style="padding:9px 14px;font-size:12px;color:var(--text2);cursor:pointer;" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background='transparent'">🧋 Milktea</div>
                            <div onclick="filterBestSellers('Frappe','Frappe')" style="padding:9px 14px;font-size:12px;color:var(--text2);cursor:pointer;" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background='transparent'">☕ Frappe</div>
                            <div onclick="filterBestSellers('Beverages','Beverages')" style="padding:9px 14px;font-size:12px;color:var(--text2);cursor:pointer;" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background='transparent'">🥤 Beverages</div>
                            <div onclick="filterBestSellers('Vegetables','Vegetables')" style="padding:9px 14px;font-size:12px;color:var(--text2);cursor:pointer;" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background='transparent'">🥦 Vegetables</div>
                        </div>
                    </div>
                </div>
                <!-- Dito ipapakita ang best sellers list -->
                <div id="best-sellers-list" style="padding:8px 0;"></div>
            </div>

            <!-- Stock Alerts -->
            <div class="table-wrap">
                <div class="table-header">
                    <span class="table-header-title">Stock Alerts</span>
                </div>
                <div class="alerts-list" style="padding:15px">
                    ${alertRows || '<p style="color:var(--text3)">All items in stock</p>'}
                </div>
            </div>
        </div>

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <!-- Sales Graph - visible lang sa admin -->
        <div class="table-wrap" style="margin-top:20px;" id="sales-graph-wrap">
            <div class="table-header" style="flex-wrap:wrap;gap:8px;">
                <span class="table-header-title">📈 Sales Graph</span>
                <!-- Month picker para sa graph filter -->
                <input type="month" id="graph-month"
                       style="background:var(--bg3);border:1px solid var(--accent);border-radius:var(--r2);
                              padding:6px 10px;color:var(--accent);font-size:12px;outline:none;
                              cursor:pointer;color-scheme:dark;"
                       onchange="loadSalesGraph(document.getElementById('graph-days').value, this.value)"/>
                <!-- Days filter dropdown -->
                <select id="graph-days"
                        onchange="loadSalesGraph(this.value, document.getElementById('graph-month').value)"
                        style="background:var(--bg3);border:1px solid var(--border);border-radius:var(--r2);
                               padding:6px 10px;color:var(--text2);font-size:12px;outline:none;cursor:pointer;">
                    <option value="7">Last 7 days</option>
                    <option value="14">Last 14 days</option>
                    <option value="30" selected>Last 30 days</option>
                </select>
            </div>
            <div style="padding:16px 20px;">
                <!-- 3 summary stats ng sales -->
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
                    <div style="background:var(--bg3);border-radius:var(--r2);padding:14px;">
                        <div style="font-size:11px;color:var(--text3);text-transform:uppercase;margin-bottom:4px;">Today</div>
                        <div style="font-family:var(--font-head);font-size:22px;font-weight:700;" id="graph-stat-today">₱0</div>
                    </div>
                    <div style="background:var(--bg3);border-radius:var(--r2);padding:14px;">
                        <div style="font-size:11px;color:var(--text3);text-transform:uppercase;margin-bottom:4px;">This Week</div>
                        <div style="font-family:var(--font-head);font-size:22px;font-weight:700;" id="graph-stat-week">₱0</div>
                    </div>
                    <div style="background:var(--bg3);border-radius:var(--r2);padding:14px;">
                        <div style="font-size:11px;color:var(--text3);text-transform:uppercase;margin-bottom:4px;">This Month</div>
                        <div style="font-family:var(--font-head);font-size:22px;font-weight:700;" id="graph-stat-month">₱0</div>
                    </div>
                </div>
                <!-- Canvas para sa Chart.js bar chart -->
                <div style="position:relative;width:100%;height:260px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>`;

    // I-load ang best sellers at (kung admin) ang sales graph
    loadBestSellers('all');
    <?php if ($_SESSION['role'] === 'admin'): ?>
    loadSalesGraph(30);
    <?php endif; ?>
}

// ─────────────────────────────────────────────────────────
// BEST SELLERS FUNCTIONS
// ─────────────────────────────────────────────────────────

// Toggle para sa category dropdown ng best sellers
function toggleBestDropdown() {
    const dd = document.getElementById('best-dropdown');
    if (dd) dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
}

// I-filter ang best sellers batay sa napiling category
async function filterBestSellers(cat, label) {
    document.getElementById('best-cat-label').textContent = label;
    document.getElementById('best-dropdown').style.display = 'none';
    loadBestSellers(cat);
}

// Naglo-load ng best sellers data mula sa API
async function loadBestSellers(cat) {
    const url = cat === 'all'
        ? API_SALES + '?type=month'
        : API_SALES + '?type=month&category=' + encodeURIComponent(cat);

    const res   = await fetch(url);
    const d     = await res.json();
    const items = d.best_sellers || [];
    const ranks = ['🥇', '🥈', '🥉'];
    const maxQty = items[0]?.total_qty || 1;

    const el = document.getElementById('best-sellers-list');
    if (!el) return;

    // Gumawa ng HTML para sa bawat best seller item
    el.innerHTML = items.length
        ? items.slice(0, 5).map((item, i) => `
            <div style="display:flex;align-items:center;gap:10px;padding:8px 16px;border-bottom:1px solid var(--border);">
                <!-- Rank number o medal emoji -->
                <div style="width:22px;height:22px;border-radius:50%;background:var(--bg3);
                            font-size:11px;display:flex;align-items:center;justify-content:center;">
                    ${ranks[i] || i + 1}
                </div>
                <div style="flex:1;">
                    <div style="font-size:13px;font-weight:500;">${esc(item.item_name)}</div>
                    <!-- Progress bar batay sa % ng max qty -->
                    <div style="height:3px;background:var(--border);border-radius:2px;margin-top:4px;overflow:hidden;">
                        <div style="height:100%;background:var(--accent);border-radius:2px;
                                    width:${Math.round(item.total_qty / maxQty * 100)}%;"></div>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:12px;font-weight:700;color:var(--accent);">${fmt(item.total_sales)}</div>
                    <div style="font-size:10px;color:var(--text3);">${item.total_qty} orders</div>
                </div>
            </div>`).join('')
        : '<p style="text-align:center;padding:20px;color:var(--text3);">No sales data yet</p>';
}

// Isara ang dropdown kapag nag-click sa labas nito
document.addEventListener('click', function (e) {
    if (!e.target.closest('#best-dropdown-wrap')) {
        const dd = document.getElementById('best-dropdown');
        if (dd) dd.style.display = 'none';
    }
});

// ─────────────────────────────────────────────────────────
// SALES GRAPH (Admin only)
// Gumagamit ng Chart.js para gumawa ng bar chart
// ─────────────────────────────────────────────────────────
let salesChartInstance = null; // I-store ang chart instance para mapalitan

async function loadSalesGraph(days = 30, month = null) {
    let url = API_SALES + '?type=graph&days=' + days;
    if (month) url += '&month=' + month;

    const res = await fetch(url);
    const d   = await res.json();
    if (!d.success) return;

    // I-update ang 3 stat cards
    const todayEl = document.getElementById('graph-stat-today');
    const weekEl  = document.getElementById('graph-stat-week');
    const monthEl = document.getElementById('graph-stat-month');
    if (todayEl) todayEl.textContent = fmt(d.today_total || 0);
    if (weekEl)  weekEl.textContent  = fmt(d.week_total  || 0);
    if (monthEl) monthEl.textContent = fmt(d.month_total || 0);

    // I-set ang month picker sa current month kung walang pinili
    const monthPicker = document.getElementById('graph-month');
    if (monthPicker && !month) {
        const now = new Date();
        monthPicker.value = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
    }

    // I-render ang bar chart gamit ang Chart.js
    const canvas = document.getElementById('salesChart');
    if (!canvas) return;
    if (salesChartInstance) salesChartInstance.destroy(); // Tanggalin ang luma bago gumawa ng bago

    salesChartInstance = new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: d.labels || [],
            datasets: [{
                label: 'Sales',
                data: d.values || [],
                backgroundColor: '#ff6b35',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { font: { size: 11 }, color: '#888', maxRotation: 45, autoSkip: true } },
                y: {
                    ticks: {
                        font: { size: 11 }, color: '#888',
                        callback: v => '₱' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v)
                    },
                    grid: { color: 'rgba(128,128,128,0.1)' }
                }
            }
        }
    });
}

// ─────────────────────────────────────────────────────────
// INVENTORY VIEW
// Nagpapakita ng listahan ng raw materials
// ─────────────────────────────────────────────────────────
async function renderInventory(searchStr = '', date = null) {
    // Buuin ang URL na may optional na search at date filter
    let url = API.items + '?search=' + encodeURIComponent(searchStr);
    if (date) url += '&date=' + date;

    const res   = await req(url);
    const items = res?.data || [];

    // Gumawa ng HTML para sa bawat row ng inventory table
    let rowsHTML = items.map(item => {
        // Tukuyin ang kulay ng status badge
        let statusColor = item.quantity <= 0
            ? "badge-red"
            : (item.quantity <= item.reorder_level ? "badge-amber" : "badge-green");

        return `
            <tr>
                <td><span class="td-sku">${esc(item.sku)}</span></td>
                <td><b>${esc(item.name)}</b></td>
                <td>${esc(item.category)}</td>
                <td><b>${item.quantity}</b> <small>${esc(item.unit)}</small></td>
                <td>₱${Number(item.cost_price).toFixed(2)}</td>
                <td><span class="badge ${statusColor}">${(item.status || 'in_stock').replace('_', ' ')}</span></td>
                <td style="font-size:11px;color:var(--text3)">
                    ${item.updated_at ? item.updated_at.split(' ')[0] : item.created_at ? item.created_at.split(' ')[0] : '—'}
                </td>
                <td class="td-actions">
                    <button class="btn btn-ghost" onclick="openAdjust('${item.id}','${esc(item.name)}')" title="Adjust Stock">⇅</button>
                    <button class="btn btn-ghost" onclick="openEditItem('${item.id}')">✎</button>
                    <button class="btn btn-danger" onclick="deleteItem('${item.id}','${esc(item.name)}')">✕</button>
                </td>
            </tr>`;
    }).join('');

    document.getElementById('content').innerHTML = `
        <div class="table-wrap">
            <div class="table-header">
                <span class="table-header-title">Inventory List</span>
                ${searchStr ? `<span style="font-size:12px;color:var(--accent)">Results for: "${esc(searchStr)}"</span>` : ''}
                <!-- Date picker para ma-filter ang items by date -->
                <input type="date" id="inv-date-picker"
                       style="background:var(--bg3);border:1px solid var(--accent);border-radius:var(--r2);
                              padding:6px 10px;color:var(--accent);font-size:12px;outline:none;
                              cursor:pointer;color-scheme:dark;"
                       onchange="renderInventory('', this.value)"/>
                <button class="btn btn-primary btn-sm" onclick="openAddItem()">＋ Add Item</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>SKU</th><th>Pangalan</th><th>Category</th>
                        <th>Qty</th><th>Price</th><th>Status</th>
                        <th>Date</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${rowsHTML || '<tr><td colspan="8" style="text-align:center;padding:40px;">No items found.</td></tr>'}
                </tbody>
            </table>
        </div>`;
}

// ─────────────────────────────────────────────────────────
// INVENTORY ACTIONS
// ─────────────────────────────────────────────────────────

// Buksan ang modal para sa pag-add ng bagong item
function openAddItem() {
    document.getElementById('item-id').value = '';
    document.getElementById('modal-item-title').textContent = 'Add New Item';
    document.querySelectorAll('#modal-item input, #modal-item textarea').forEach(i => i.value = '');
    populateCategorySelect();
    openModal('modal-item');
}

// I-save ang bagong item o i-update ang existing
document.getElementById('btn-save-item').addEventListener('click', async () => {
    const id   = document.getElementById('item-id').value;
    const body = {
        name:        document.getElementById('f-name').value,
        sku:         document.getElementById('f-sku').value,
        category:    document.getElementById('f-category').value,
        unit:        document.getElementById('f-unit').value,
        quantity:    document.getElementById('f-qty').value,
        reorder_lvl: document.getElementById('f-reorder').value,
        cost_price:  document.getElementById('f-cost').value,
        sell_price:  document.getElementById('f-sell')?.value || 0,
        supplier:    document.getElementById('f-supplier')?.value || '',
        description: document.getElementById('f-desc')?.value || ''
    };
    const res = await req(id ? `${API.items}?id=${id}` : API.items, id ? 'PUT' : 'POST', body);
    if (res?.success) {
        closeModal('modal-item');
        renderInventory();
        toast('Item Saved!');
    } else {
        alert("Error: " + (res?.message || "Check Database"));
    }
});

// Burahin ang item pagkatapos mag-confirm
window.deleteItem = async function (id, name) {
    if (!confirm(`Delete ${name}?`)) return;
    const res = await req(`${API.items}?id=${id}`, 'DELETE');
    if (res?.success) { toast('Deleted Successfully', 'danger'); renderInventory(); }
};

// Buksan ang edit modal na may pre-filled na data
window.openEditItem = async function (id) {
    const res = await req(`${API.items}?id=${id}`);
    if (res?.data?.[0]) {
        const d = res.data[0];
        document.getElementById('modal-item-title').textContent = 'Edit Item: ' + d.name;
        document.getElementById('item-id').value    = d.id;
        document.getElementById('f-name').value     = d.name;
        document.getElementById('f-sku').value      = d.sku;
        document.getElementById('f-qty').value      = d.quantity;
        document.getElementById('f-cost').value     = d.cost_price;
        document.getElementById('f-reorder').value  = d.reorder_level;
        document.getElementById('f-unit').value     = d.unit || 'pcs';
        document.getElementById('f-sell').value     = d.sell_price || '';
        document.getElementById('f-supplier').value = d.supplier || '';
        document.getElementById('f-desc').value     = d.description || '';
        await populateCategorySelect(d.category);
        openModal('modal-item');
    }
};

// Mag-populate ng category dropdown sa item form
async function populateCategorySelect(selected = '') {
    const res    = await req(API.categories);
    const cats   = res?.data || [];
    const select = document.getElementById('f-category');
    if (select) {
        select.innerHTML = '<option value="">Select Category...</option>' +
            cats.map(c => `<option value="${esc(c.name)}" ${selected === c.name ? 'selected' : ''}>${esc(c.name)}</option>`).join('');
    }
}

// ─────────────────────────────────────────────────────────
// STOCK ADJUSTMENT
// ─────────────────────────────────────────────────────────

// Buksan ang adjust stock modal
window.openAdjust = function (id, name) {
    document.getElementById('adj-id').value = id;
    document.getElementById('adj-item-name').innerText = name;
    document.getElementById('adj-amount').value = '';
    document.getElementById('adj-reason').value = '';
    selectAdjType('add'); // Default ay add
    openModal('modal-adjust');
};

// I-highlight ang napiling type (add o remove)
window.selectAdjType = function (type) {
    adjType = type;
    document.getElementById('adj-add').className    = type === 'add'    ? 'adj-type selected-add'    : 'adj-type';
    document.getElementById('adj-remove').className = type === 'remove' ? 'adj-type selected-remove' : 'adj-type';
};

// I-save ang stock adjustment
document.getElementById('btn-save-adj').addEventListener('click', async () => {
    const amount = document.getElementById('adj-amount').value;
    if (!amount || amount <= 0) { toast('Lagyan ng amount!', 'error'); return; }

    const res = await req(API.adjust, 'POST', {
        id:     document.getElementById('adj-id').value,
        type:   adjType,
        amount: document.getElementById('adj-amount').value,
        reason: document.getElementById('adj-reason').value
    });

    if (res?.success) {
        closeModal('modal-adjust');
        renderInventory();
        toast('Stock Updated!');
    } else {
        toast(res?.message || 'Error! Hindi na-update ang stock.', 'error');
    }
});

// ─────────────────────────────────────────────────────────
// CATEGORIES VIEW
// ─────────────────────────────────────────────────────────
async function renderCategories() {
    const res  = await req(API.categories);
    const cats = res?.data || [];

    const rows = cats.map(c => `
        <tr>
            <td>${esc(c.name)}</td>
            <td style="display:flex;gap:20px;">
                <button class="btn btn-ghost btn-sm" onclick="viewCategoryItems('${esc(c.name)}')">👁 View Items</button>
                <button class="btn btn-danger btn-sm" onclick="deleteCategory('${c.id}','${esc(c.name)}')">Delete</button>
            </td>
        </tr>`).join('');

    document.getElementById('content').innerHTML = `
        <div style="display:grid;grid-template-columns:340px 1fr;gap:20px">
            <!-- Form para sa pag-add ng category -->
            <div class="table-wrap">
                <div class="modal-head" style="padding:15px"><h2>Add Category</h2></div>
                <div style="padding:15px;display:flex;gap:10px">
                    <input type="text" id="new-cat-input" placeholder="Category name..." style="flex:1"/>
                    <button class="btn btn-primary" onclick="addCategory()">Add</button>
                </div>
            </div>
            <!-- Table ng lahat ng categories -->
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Category Name</th><th>Action</th></tr></thead>
                    <tbody>${rows || '<tr><td colspan="2">No categories</td></tr>'}</tbody>
                </table>
            </div>
        </div>`;
}

window.addCategory = async function () {
    const name = document.getElementById('new-cat-input').value;
    if (!name) return alert("Fill The Name First!");
    const res = await req(API.categories, 'POST', { name });
    if (res?.success) { renderCategories(); toast('Category Added'); }
};

window.deleteCategory = async function (id, name) {
    if (!confirm(`Delete category ${name}?`)) return;
    const res = await req(`${API.categories}?id=${id}`, 'DELETE');
    if (res?.success) { renderCategories(); toast('Category Deleted', 'danger'); }
};

// Ipakita ang mga items sa isang category sa popup modal
window.viewCategoryItems = async function (catName) {
    const res   = await req(API.items + '?category=' + encodeURIComponent(catName));
    const items = res?.data || [];

    const rows = items.length
        ? items.map(i => `
            <tr>
                <td><span class="td-sku">${esc(i.sku)}</span></td>
                <td><b>${esc(i.name)}</b></td>
                <td>${i.quantity} ${esc(i.unit)}</td>
                <td>₱${Number(i.cost_price).toFixed(2)}</td>
                <td><span class="badge ${i.status === 'in_stock' ? 'badge-green' : i.status === 'low_stock' ? 'badge-amber' : 'badge-red'}">${i.status.replace('_', ' ')}</span></td>
            </tr>`).join('')
        : `<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--text3)">Walang items sa category na ito</td></tr>`;

    document.getElementById('cat-modal-title').textContent = '📦 ' + catName;
    document.getElementById('cat-modal-body').innerHTML = `
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="padding:8px 12px;font-size:11px;color:var(--text3);text-align:left;border-bottom:1px solid var(--border)">SKU</th>
                    <th style="padding:8px 12px;font-size:11px;color:var(--text3);text-align:left;border-bottom:1px solid var(--border)">Name</th>
                    <th style="padding:8px 12px;font-size:11px;color:var(--text3);text-align:left;border-bottom:1px solid var(--border)">Qty</th>
                    <th style="padding:8px 12px;font-size:11px;color:var(--text3);text-align:left;border-bottom:1px solid var(--border)">Price</th>
                    <th style="padding:8px 12px;font-size:11px;color:var(--text3);text-align:left;border-bottom:1px solid var(--border)">Status</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>`;
    openModal('modal-cat-items');
};

// ─────────────────────────────────────────────────────────
// STOCK LOGS VIEW
// ─────────────────────────────────────────────────────────
async function renderLogs() {
    const res  = await req(API.logs);
    const logs = res?.data || [];

    const rows = logs.map(l => `
        <div class="log-row">
            <!-- Indicator kung add o remove -->
            <div class="log-type ${l.type === 'add' ? 'log-add' : 'log-remove'}">
                ${l.type === 'add' ? '↑' : '↓'}
            </div>
            <div style="flex:1">
                <b>${esc(l.item_name)}</b> — ${esc(l.reason || 'No reason')}
                <div style="font-size:11px;color:var(--text3)">${l.timestamp}</div>
            </div>
            <div style="font-weight:700;color:${l.type === 'add' ? 'var(--green)' : 'var(--red)'}">
                ${l.type === 'add' ? '+' : '-'}${l.amount}
            </div>
        </div>`).join('');

    document.getElementById('content').innerHTML = `
        <div class="table-wrap" style="max-width:700px;margin:0 auto">
            <div class="table-header">
                <span class="table-header-title">Recent Stock Activity</span>
            </div>
            <div style="padding:10px">
                ${rows || '<p style="text-align:center;padding:20px;color:var(--text3)">No activity logs yet.</p>'}
            </div>
        </div>`;
}

// ─────────────────────────────────────────────────────────
// SALES VIEW
// Nagpapakita ng sales record na may date picker
// ─────────────────────────────────────────────────────────
async function renderSales(date = null) {
    const today        = new Date().toISOString().split('T')[0];
    const selectedDate = date || today;
    const res          = await fetch(API_SALES + '?type=custom&date=' + selectedDate);
    const d            = await res.json();
    if (!d.success) return;

    const tbody = d.list.length
        ? d.list.map(s => `
            <tr>
                <td><b>${esc(s.item_name)}</b></td>
                <td><span class="badge b-blue">${esc(s.category)}</span></td>
                <td>${s.quantity}</td>
                <td>${fmtSales(s.price)}</td>
                <td style="color:var(--accent);font-weight:600">${fmtSales(s.total)}</td>
                <td style="color:var(--text3);font-size:12px">${s.created_at?.split(' ')[1]?.slice(0, 5) || '--'}</td>
            </tr>`).join('')
        : '<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text3)">No sales on this date</td></tr>';

    document.getElementById('content').innerHTML = `
        <!-- Summary stat cards -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px">
            <div class="stat-card" style="--card-color:var(--accent)">
                <div class="stat-value">${fmtSales(d.total_sales)}</div>
                <div class="stat-label">Sales Today</div>
            </div>
            <div class="stat-card" style="--card-color:var(--green)">
                <div class="stat-value">${d.total_orders}</div>
                <div class="stat-label">Orders Today</div>
            </div>
            <div class="stat-card" style="--card-color:var(--blue)">
                <div class="stat-value">${fmtSales(d.weekly_total)}</div>
                <div class="stat-label">This Week</div>
            </div>
        </div>
        <!-- Sales table na may date picker -->
        <div class="table-wrap">
            <div class="table-header">
                <span class="table-header-title">💰 Sales</span>
                <input type="date" id="sales-date-picker" value="${selectedDate}"
                       style="background:var(--bg3);border:1px solid var(--accent);border-radius:var(--r2);
                              padding:6px 10px;color:var(--accent);font-size:12px;outline:none;
                              cursor:pointer;color-scheme:dark;"
                       onchange="renderSales(this.value)"/>
                <button class="btn btn-primary btn-sm" onclick="openAddSale()">＋ Record Sale</button>
            </div>
            <table>
                <thead>
                    <tr><th>Item</th><th>Category</th><th>Qty</th><th>Price</th><th>Total</th><th>Time</th></tr>
                </thead>
                <tbody>${tbody}</tbody>
            </table>
        </div>`;
}

// Buksan ang modal para sa pag-record ng sale
function openAddSale() {
    document.getElementById('sale-category').value = '';
    document.getElementById('sale-item').innerHTML = '<option value="">Select item…</option>';
    document.getElementById('sale-qty').value      = 1;
    document.getElementById('sale-price').value    = '';
    document.getElementById('sale-total').textContent = '₱0.00';
    openModal('modal-sale');
}

// I-load ang menu items batay sa napiling category
async function loadMenuItems(cat) {
    if (!cat) { document.getElementById('sale-item').innerHTML = '<option value="">Select item…</option>'; return; }
    const res   = await fetch(API_MENU + '?category=' + encodeURIComponent(cat));
    const d     = await res.json();
    const items = d.data || [];
    const sel   = document.getElementById('sale-item');
    if (!items.length) { sel.innerHTML = '<option value="">No items found</option>'; return; }
    sel.innerHTML = '<option value="">Select item…</option>' +
        items.map(i => `<option value="${i.id}" data-price="${i.price}">${esc(i.name)} — ₱${Number(i.price).toFixed(2)}</option>`).join('');
}

// Auto-fill ang price kapag pumili ng menu item
function setSalePrice(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('sale-price').value = opt?.dataset?.price || '';
    updateSaleTotal();
}

// I-compute ang total habang nagta-type ng qty o price
function updateSaleTotal() {
    const qty   = parseFloat(document.getElementById('sale-qty').value) || 0;
    const price = parseFloat(document.getElementById('sale-price').value) || 0;
    document.getElementById('sale-total').textContent = fmtSales(qty * price);
}

// I-save ang sale record
async function saveSale() {
    const itemId = document.getElementById('sale-item').value;
    const qty    = document.getElementById('sale-qty').value;
    const price  = document.getElementById('sale-price').value;
    if (!itemId || !qty || !price) { toast('Punan ang lahat!', 'error'); return; }

    const res = await fetch(API_SALES, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ menu_item_id: itemId, quantity: qty, price })
    });
    const d = await res.json();
    if (d.success) { toast('Sale recorded! ' + fmtSales(d.total)); closeModal('modal-sale'); renderSales(); }
    else toast(d.message || 'Error!', 'error');
}

// ─────────────────────────────────────────────────────────
// USER ACCOUNTS VIEW (Admin only)
// ─────────────────────────────────────────────────────────
async function renderAccounts() {
    const res   = await fetch(API_USERS);
    const d     = await res.json();
    const users = d.data || [];

    // Kalkulahin ang stats
    const total    = users.length;
    const active   = users.filter(u => u.is_active == 1).length;
    const inactive = users.filter(u => u.is_active == 0).length;
    const staff    = users.filter(u => u.role === 'staff').length;

    // Gumawa ng table rows para sa bawat user
    const rows = users.map(u => `
        <tr>
            <td><b>${esc(u.full_name)}</b></td>
            <td style="font-family:monospace;color:var(--text2)">${esc(u.username)}</td>
            <td><span class="badge ${u.role === 'admin' ? 'badge-amber' : 'badge-blue'}">${u.role}</span></td>
            <td><span class="badge ${u.is_active ? 'badge-green' : 'badge-red'}">${u.is_active ? 'Active' : 'Inactive'}</span></td>
            <td style="color:var(--text3);font-size:12px">${u.last_login ? new Date(u.last_login).toLocaleString('en-PH') : 'Never'}</td>
            <td>
                <div style="display:flex;gap:6px;">
                    ${u.id != <?php echo $_SESSION['user_id']; ?> ? `
                    <button class="btn btn-sm ${u.is_active ? 'btn-danger' : 'btn-ghost'}"
                            onclick="toggleUserAcc(${u.id},${u.is_active})">
                        ${u.is_active ? 'Deactivate' : 'Activate'}
                    </button>
                    <button class="btn btn-ghost btn-sm"
                            onclick="resetUserPass(${u.id},'${esc(u.full_name)}')">🔑 Reset</button>
                    ` : '<span style="font-size:12px;color:var(--text3)">— You —</span>'}
                </div>
            </td>
        </tr>`).join('');

    document.getElementById('content').innerHTML = `
        <!-- Stats ng accounts -->
        <div class="stats-grid">
            <div class="stat-card" style="--card-color:var(--accent)"><div class="stat-value">${total}</div><div class="stat-label">Total Accounts</div></div>
            <div class="stat-card" style="--card-color:var(--green)"><div class="stat-value">${active}</div><div class="stat-label">Active</div></div>
            <div class="stat-card" style="--card-color:var(--red)"><div class="stat-value">${inactive}</div><div class="stat-label">Inactive</div></div>
            <div class="stat-card" style="--card-color:var(--blue)"><div class="stat-value">${staff}</div><div class="stat-label">Staff</div></div>
        </div>
        <!-- Table ng lahat ng user accounts -->
        <div class="table-wrap">
            <div class="table-header">
                <span class="table-header-title">👥 All Accounts</span>
                <button class="btn btn-primary btn-sm" onclick="openAddUser()">＋ Add Account</button>
            </div>
            <table>
                <thead>
                    <tr><th>Full Name</th><th>Username</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr>
                </thead>
                <tbody>${rows || '<tr><td colspan="6" style="text-align:center;padding:30px;">No accounts found.</td></tr>'}</tbody>
            </table>
        </div>`;
}

// ─────────────────────────────────────────────────────────
// ACTIVE STAFF VIEW (Admin only)
// ─────────────────────────────────────────────────────────
async function renderActiveStaff() {
    const res   = await fetch(API_USERS);
    const d     = await res.json();
    // Filter para makita lang ang active users
    const staff = (d.data || []).filter(u => u.is_active == 1);

    const rows = staff.map(u => `
        <tr>
            <!-- Avatar circle na may initials ng pangalan -->
            <td>
                <div style="width:34px;height:34px;border-radius:50%;
                            background:rgba(255,107,53,.15);color:var(--accent);
                            display:flex;align-items:center;justify-content:center;
                            font-weight:700;font-size:13px;">
                    ${esc(u.full_name.substring(0, 2).toUpperCase())}
                </div>
            </td>
            <td><b>${esc(u.full_name)}</b></td>
            <td style="font-family:monospace;color:var(--text2)">${esc(u.username)}</td>
            <td><span class="badge ${u.role === 'admin' ? 'badge-amber' : 'badge-blue'}">${u.role}</span></td>
            <td style="color:var(--text3);font-size:12px">${u.last_login ? new Date(u.last_login).toLocaleString('en-PH') : 'Never'}</td>
        </tr>`).join('');

    document.getElementById('content').innerHTML = `
        <div class="table-wrap">
            <div class="table-header">
                <span class="table-header-title">🟢 Currently Active Accounts</span>
                <span style="font-size:12px;color:var(--text2)">${staff.length} active</span>
            </div>
            <table>
                <thead>
                    <tr><th></th><th>Full Name</th><th>Username</th><th>Role</th><th>Last Login</th></tr>
                </thead>
                <tbody>${rows || '<tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text3)">No active staff</td></tr>'}</tbody>
            </table>
        </div>`;
}

// ─────────────────────────────────────────────────────────
// USER ACCOUNT ACTIONS (Admin only)
// ─────────────────────────────────────────────────────────

// Buksan ang add user modal
function openAddUser() {
    document.getElementById('u-name').value = '';
    document.getElementById('u-user').value = '';
    document.getElementById('u-pass').value = '';
    document.getElementById('u-role').value = 'staff';
    openModal('modal-add-user');
}

// I-save ang bagong user account
async function saveNewUser() {
    const fullname = document.getElementById('u-name').value.trim();
    const username = document.getElementById('u-user').value.trim();
    const password = document.getElementById('u-pass').value;
    const role     = document.getElementById('u-role').value;
    if (!fullname || !username || !password) { toast('Punan ang lahat!', 'error'); return; }

    const res = await fetch(API_USERS, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ fullname, username, password, role })
    });
    const d = await res.json();
    if (d.success) { toast('Account created!'); closeModal('modal-add-user'); renderAccounts(); }
    else toast(d.message || 'Error!', 'error');
}

// I-setup ang reset password modal
let resetUserId = null;
function resetUserPass(id, name) {
    resetUserId = id;
    document.getElementById('reset-user-name').textContent = 'Resetting password for: ' + name;
    document.getElementById('reset-user-pass').value = '';
    openModal('modal-reset-user');
}

// I-save ang bagong password
async function confirmResetUser() {
    const password = document.getElementById('reset-user-pass').value;
    if (!password) { toast('Enter new password!', 'error'); return; }

    const res = await fetch(API_USERS, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id: resetUserId, password })
    });
    const d = await res.json();
    if (d.success) { toast('Password reset!'); closeModal('modal-reset-user'); }
    else toast(d.message || 'Error!', 'error');
}

// I-toggle ang active/inactive status ng user
async function toggleUserAcc(id, isActive) {
    if (!confirm((isActive ? 'Deactivate' : 'Activate') + ' this user?')) return;
    const res = await fetch(API_USERS, {
        method:  'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id, is_active: isActive ? 0 : 1 })
    });
    const d = await res.json();
    if (d.success) { toast('Updated!'); renderAccounts(); }
    else toast(d.message || 'Error!', 'error');
}

// ─────────────────────────────────────────────────────────
// INITIALIZATION - Tatakbo ito pagkatapos mag-load ang page
// ─────────────────────────────────────────────────────────
window.addEventListener('DOMContentLoaded', () => {
    // I-load ang dashboard bilang default view
    renderView('dashboard');

    // I-setup ang global search para sa inventory
    const globalSearch = document.getElementById('global-search');
    if (globalSearch) {
        globalSearch.addEventListener('input', (e) => {
            const val = e.target.value;
            // Kung hindi pa nasa inventory view, lumipat doon
            if (currentView !== 'inventory' && val.trim() !== '') {
                switchView('inventory');
            }
            renderInventory(val);
        });
    }
});

</script>
</body>
</html>
