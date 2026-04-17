<?php
require_once '../config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_name = $_SESSION['student_name'];

// Logic: Fetch Today's Menu
$today_day = date('l'); 
$today_query = "SELECT * FROM mess_menu WHERE day_of_week = '$today_day'";
$today_result = mysqli_query($conn, $today_query);
$today_menu = mysqli_fetch_assoc($today_result);

// Logic: Fetch Full Week
$weekly_query = "SELECT * FROM mess_menu ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
$weekly_result = mysqli_query($conn, $weekly_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mess Schedule | GUARDIAN HOSTEL Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary: #6366f1;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
            
            /* Meal specific colors */
            --breakfast: #fbbf24;
            --lunch: #4ade80;
            --snacks: #a855f7;
            --dinner: #3b82f6;
        }

        body { background: #0f172a; color: white; min-height: 100vh; }
        .mesh-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; background: radial-gradient(at 100% 100%, #1e1b4b 0%, #0f172a 50%); }

        .page-header { text-align: center; padding: 4rem 0 2rem; }
        .page-header h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 10px; }

        /* Today's Highlight Section */
        .today-section { margin-bottom: 4rem; }
        .section-tag { 
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--primary); color: white; padding: 8px 20px;
            border-radius: 50px; font-size: 0.75rem; font-weight: 800;
            text-transform: uppercase; margin-bottom: 2rem;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
        }

        .meal-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; }
        
        .meal-card {
            background: var(--glass); backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border); border-radius: 28px;
            padding: 2rem; transition: 0.3s; position: relative; overflow: hidden;
        }
        .meal-card::after {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px;
        }
        .meal-card:hover { transform: translateY(-8px); border-color: rgba(255,255,255,0.2); }

        /* Meal specific glows */
        .card-breakfast::after { background: var(--breakfast); box-shadow: 0 0 15px var(--breakfast); }
        .card-lunch::after { background: var(--lunch); box-shadow: 0 0 15px var(--lunch); }
        .card-snacks::after { background: var(--snacks); box-shadow: 0 0 15px var(--snacks); }
        .card-dinner::after { background: var(--dinner); box-shadow: 0 0 15px var(--dinner); }

        .meal-icon { font-size: 2rem; margin-bottom: 1.5rem; opacity: 0.9; }
        .meal-card h3 { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-dim); margin-bottom: 10px; }
        .meal-card p { font-size: 1.1rem; font-weight: 600; line-height: 1.5; color: white; }

        /* Weekly Table Section */
        .weekly-schedule { background: var(--glass); border-radius: 32px; border: 1px solid var(--glass-border); overflow: hidden; margin-bottom: 5rem; }
        .table-header { padding: 2rem; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center; }
        
        .modern-table { width: 100%; border-collapse: collapse; }
        .modern-table th { text-align: left; padding: 1.2rem 2rem; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; background: rgba(255,255,255,0.02); }
        .modern-table td { padding: 1.5rem 2rem; border-bottom: 1px solid var(--glass-border); font-size: 0.95rem; vertical-align: top; }
        
        .day-label { font-weight: 800; color: white; display: flex; align-items: center; gap: 10px; }
        .today-row { background: rgba(99, 102, 241, 0.05); }
        .today-row td { border-bottom: 1px solid var(--primary); }
        .status-dot { width: 8px; height: 8px; background: var(--lunch); border-radius: 50%; box-shadow: 0 0 8px var(--lunch); }

        @media (max-width: 768px) {
            .modern-table thead { display: none; }
            .modern-table td { display: block; padding: 1rem 2rem; }
            .modern-table td::before { content: attr(data-label); display: block; font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 5px; }
        }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>

    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="page-header">
            <h1>Nutritional Hub<span>.</span></h1>
            <p style="color: var(--text-dim);">Chef-curated weekly menu for a healthy student lifestyle</p>
        </header>

        <?php if ($today_menu): ?>
        <section class="today-section">
            <div style="text-align: center;">
                <div class="section-tag">
                    <i class="fas fa-fire-flame-curved"></i> Served Today • <?php echo $today_day; ?>
                </div>
            </div>

            <div class="meal-grid">
                <div class="meal-card card-breakfast">
                    <div class="meal-icon" style="color: var(--breakfast);"><i class="fas fa-mug-saucer"></i></div>
                    <h3>Breakfast</h3>
                    <p><?php echo htmlspecialchars($today_menu['breakfast']); ?></p>
                </div>

                <div class="meal-card card-lunch">
                    <div class="meal-icon" style="color: var(--lunch);"><i class="fas fa-bowl-rice"></i></div>
                    <h3>Main Lunch</h3>
                    <p><?php echo htmlspecialchars($today_menu['lunch']); ?></p>
                </div>

                <?php if ($today_menu['snacks']): ?>
                <div class="meal-card card-snacks">
                    <div class="meal-icon" style="color: var(--snacks);"><i class="fas fa-cookie-bite"></i></div>
                    <h3>Evening Snacks</h3>
                    <p><?php echo htmlspecialchars($today_menu['snacks']); ?></p>
                </div>
                <?php endif; ?>

                <div class="meal-card card-dinner">
                    <div class="meal-icon" style="color: var(--dinner);"><i class="fas fa-utensils"></i></div>
                    <h3>Dinner</h3>
                    <p><?php echo htmlspecialchars($today_menu['dinner']); ?></p>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <div class="weekly-schedule">
            <div class="table-header">
                <h3 style="font-weight: 800;"><i class="far fa-calendar-days" style="color: var(--primary); margin-right: 10px;"></i> Full Weekly Rotation</h3>
                <span style="font-size: 0.8rem; color: var(--text-dim); font-weight: 700;">ACTIVE SEASON 2026</span>
            </div>

            <div style="overflow-x: auto;">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Breakfast</th>
                            <th>Lunch</th>
                            <th>Snacks</th>
                            <th>Dinner</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($menu = mysqli_fetch_assoc($weekly_result)): 
                            $is_today = ($menu['day_of_week'] == $today_day);
                        ?>
                        <tr class="<?php echo $is_today ? 'today-row' : ''; ?>">
                            <td data-label="Day">
                                <div class="day-label">
                                    <?php if($is_today): ?><div class="status-dot"></div><?php endif; ?>
                                    <?php echo $menu['day_of_week']; ?>
                                </div>
                            </td>
                            <td data-label="Breakfast" style="color: #79b4fc;"><?php echo htmlspecialchars($menu['breakfast']); ?></td>
                            <td data-label="Lunch" style="color: #9dc9ff;"><?php echo htmlspecialchars($menu['lunch']); ?></td>
                            <td data-label="Snacks" style="color: var(--text-dim);"><?php echo htmlspecialchars($menu['snacks']) ?: '—'; ?></td>
                            <td data-label="Dinner" style="color: #99c7ff;"><?php echo htmlspecialchars($menu['dinner']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer style="text-align: center; padding: 3rem; color: var(--text-dim); font-size: 0.85rem;">
        &copy; 2026 GUARDIAN HOSTEL Hostel Management System. High-Quality Living.
    </footer>

</body>
</html>