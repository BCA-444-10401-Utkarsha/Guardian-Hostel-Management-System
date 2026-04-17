<style>
    :root {
    --nav-bg: #0f172a;
    --accent: #6366f1;
    --glass-white: rgba(255, 255, 255, 0.05);
    --border-white: rgba(255, 255, 255, 0.1);
}

.glass-navbar {
    background: var(--nav-bg);
    padding: 0.75rem 0;
    position: sticky;
    top: 0;
    z-index: 2000;
    border-bottom: 1px solid var(--border-white);
}

.nav-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: white;
    font-weight: 800;
    font-size: 1.3rem;
}

.brand-logo {
    width: 40px; height: 40px;
    background: var(--accent);
    display: flex; align-items: center; justify-content: center;
    border-radius: 10px;
}

.nav-content { display: flex; align-items: center; gap: 40px; }

.main-menu {
    display: flex;
    list-style: none;
    background: var(--glass-white);
    padding: 6px;
    border-radius: 14px;
    border: 1px solid var(--border-white);
}

.main-menu a {
    text-decoration: none;
    color: #94a3b8;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.main-menu a:hover { color: white; background: rgba(255,255,255,0.03); }
.main-menu a.active { background: var(--accent); color: white; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }

/* Unified End Hub */
.user-hub {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-btn, .power-btn {
    width: 42px; height: 42px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 12px;
    text-decoration: none;
    transition: 0.3s;
    border: 1px solid var(--border-white);
}

.user-btn { color: #94a3b8; background: var(--glass-white); }
.user-btn.active, .user-btn:hover { color: white; background: var(--accent); border-color: var(--accent); }

.power-btn { color: #f87171; background: rgba(248, 113, 113, 0.05); }
.power-btn:hover { background: #f87171; color: white; transform: rotate(90deg); }

/* Mobile Menu Handler */
.menu-handler { display: none; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }

@media (max-width: 1100px) {
    .menu-handler { display: block; }
    .nav-content {
        display: none; position: absolute; top: 100%; left: 0; width: 100%;
        background: var(--nav-bg); padding: 2rem; flex-direction: column;
        border-bottom: 1px solid var(--border-white);
    }
    .nav-content.is-open { display: flex; }
    .main-menu { flex-direction: column; width: 100%; background: none; border: none; }
    .main-menu a { padding: 12px; margin-bottom: 5px; }
}
</style>
<nav class="glass-navbar">
    <div class="nav-wrapper">
        <a href="dashboard.php" class="brand">
            <div class="brand-logo"><i class="fas fa-building-shield"></i></div>
            <span>GUARDIAN HOSTEL</span>
        </a>

        <button class="menu-handler" id="menuHandler">
            <i class="fas fa-bars"></i>
        </button>

        <div class="nav-content" id="navContent">
            <ul class="main-menu">
                <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="apply_room.php" class="<?= basename($_SERVER['PHP_SELF']) == 'apply_room.php' ? 'active' : '' ?>">
                    <i class="fas fa-bed"></i> Apply Room</a></li>
                <li><a href="pay_rent.php" class="<?= basename($_SERVER['PHP_SELF']) == 'pay_rent.php' ? 'active' : '' ?>">
                    <i class="fas fa-credit-card"></i> Pay Rent</a></li>
                <li><a href="mess.php" class="<?= basename($_SERVER['PHP_SELF']) == 'mess.php' ? 'active' : '' ?>">
                    <i class="fas fa-utensils"></i> Mess Menu</a></li>
                <li><a href="announcements.php" class="<?= basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : '' ?>">
                    <i class="fas fa-bullhorn"></i> News</a></li>
                <li><a href="complaint.php" class="<?= basename($_SERVER['PHP_SELF']) == 'complaint.php' ? 'active' : '' ?>">
                    <i class="fas fa-comment-dots"></i> Complaints</a></li>
            </ul>

            <div class="user-hub">
                <a href="profile.php" class="user-btn <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>" title="My Profile">
                    <i class="fas fa-user-astronaut"></i>
                </a>
                <a href="logout.php" class="power-btn" title="Sign Out">
                    <i class="fas fa-power-off"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
    const handler = document.getElementById('menuHandler');
    const content = document.getElementById('navContent');
    
    handler.addEventListener('click', () => {
        content.classList.toggle('is-open');
        handler.querySelector('i').classList.toggle('fa-bars');
        handler.querySelector('i').classList.toggle('fa-xmark');
    });
</script>