<style>
    :root {
    --admin-primary: #fbbf24; /* Security Gold */
    --admin-dark: #0f172a;
    --admin-border: rgba(255, 255, 255, 0.1);
    --admin-glass: rgba(255, 255, 255, 0.03);
}

.admin-navbar {
    background: var(--admin-dark);
    padding: 0.7rem 0;
    position: sticky;
    top: 0;
    z-index: 2000;
    border-bottom: 1px solid var(--admin-border);
}

.nav-wrapper {
    max-width: 1500px;
    margin: 0 auto;
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Branding */
.brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; }
.brand-logo {
    width: 40px; height: 40px; background: var(--admin-primary);
    color: #000; display: flex; align-items: center; justify-content: center;
    border-radius: 10px; font-size: 1.2rem;
}
.brand-text span { display: block; font-weight: 800; font-size: 1.2rem; letter-spacing: 0.5px; line-height: 1; }
.brand-text small { font-size: 0.65rem; color: var(--admin-primary); font-weight: 700; text-transform: uppercase; }

/* Menu Styling */
.main-menu {
    display: flex;
    list-style: none;
    background: rgba(0,0,0,0.2);
    padding: 5px;
    border-radius: 15px;
    border: 1px solid var(--admin-border);
}

.main-menu a {
    text-decoration: none;
    color: #94a3b8;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 8px 14px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: 0.3s;
}

.main-menu a:hover { color: white; }
.main-menu a.active { background: var(--admin-primary); color: #000; box-shadow: 0 4px 15px rgba(251, 191, 36, 0.2); }

/* Status Hub */
.admin-hub { display: flex; align-items: center; gap: 15px; }

.status-badge {
    background: rgba(251, 191, 36, 0.1);
    border: 1px solid rgba(251, 191, 36, 0.2);
    padding: 6px 15px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--admin-primary);
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
}

.pulse-dot {
    width: 6px; height: 6px; background: var(--admin-primary);
    border-radius: 50%; box-shadow: 0 0 10px var(--admin-primary);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(0.95); opacity: 0.7; }
    50% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(0.95); opacity: 0.7; }
}

.power-off-btn {
    width: 40px; height: 40px;
    background: rgba(248, 113, 113, 0.1);
    color: #f87171;
    display: flex; align-items: center; justify-content: center;
    border-radius: 10px; text-decoration: none; transition: 0.3s;
}

.power-off-btn:hover { background: #ef4444; color: white; transform: rotate(90deg); }

/* Responsive */
.menu-handler { display: none; background: none; border: none; color: white; font-size: 1.5rem; }

@media (max-width: 1250px) {
    .menu-handler { display: block; }
    .nav-content {
        display: none; position: absolute; top: 100%; left: 0; width: 100%;
        background: var(--admin-dark); padding: 2rem; flex-direction: column;
        border-bottom: 1px solid var(--admin-border);
    }
    .nav-content.active { display: flex; }
    .main-menu { flex-direction: column; background: none; border: none; width: 100%; }
    .admin-hub { width: 100%; justify-content: space-between; margin-top: 20px; border-top: 1px solid var(--admin-border); padding-top: 20px; }
}
</style>
<nav class="admin-navbar">
    <div class="nav-wrapper">
        <a href="dashboard.php" class="brand">
            <div class="brand-logo"><i class="fas fa-user-shield"></i></div>
            <div class="brand-text">
                <span>GUARDIAN HOSTEL</span>
                <small>ADMIN PANEL</small>
            </div>
        </a>

        <button class="menu-handler" id="adminMenuHandler">
            <i class="fas fa-bars"></i>
        </button>

        <div class="nav-content" id="adminNavContent">
            <ul class="main-menu">
                <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="students.php" class="<?= basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Students</a></li>
                <li><a href="rooms.php" class="<?= basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : '' ?>">
                    <i class="fas fa-door-open"></i> Rooms</a></li>
                <li><a href="room_requests.php" class="<?= basename($_SERVER['PHP_SELF']) == 'room_requests.php' ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i> Requests</a></li>
                <li><a href="rent_payments.php" class="<?= basename($_SERVER['PHP_SELF']) == 'rent_payments.php' ? 'active' : '' ?>">
                    <i class="fas fa-file-invoice-dollar"></i> Rent</a></li>
                <li><a href="complaints.php" class="<?= basename($_SERVER['PHP_SELF']) == 'complaints.php' ? 'active' : '' ?>">
                    <i class="fas fa-headset"></i> Complaints</a></li>
                <li><a href="announcements.php" class="<?= basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : '' ?>">
                    <i class="fas fa-bullhorn"></i> Post</a></li>
                <li><a href="mess.php" class="<?= basename($_SERVER['PHP_SELF']) == 'mess.php' ? 'active' : '' ?>">
                    <i class="fas fa-utensils"></i> Mess</a></li>
            </ul>

            <div class="admin-hub">
                <div class="status-badge">
                    <div class="pulse-dot"></div>
                    <span>Verified Admin</span>
                </div>
                <a href="logout.php" class="power-off-btn" title="Secure Logout">
                    <i class="fas fa-power-off"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
    const btn = document.getElementById('adminMenuHandler');
    const nav = document.getElementById('adminNavContent');
    btn.addEventListener('click', () => {
        nav.classList.toggle('active');
        btn.querySelector('i').classList.toggle('fa-bars');
        btn.querySelector('i').classList.toggle('fa-times');
    });
</script>