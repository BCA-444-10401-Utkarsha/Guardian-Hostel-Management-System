<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Logic: Update Mess Fee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_price'])) {
    $mess_fee = (float)$_POST['mess_fee'];
    if ($mess_fee >= 0) {
        $query = "UPDATE settings SET setting_value = '$mess_fee' WHERE setting_key = 'mess_fee_monthly'";
        if (mysqli_query($conn, $query)) {
            $success = 'Mess fee updated successfully!';
        } else { $error = 'Failed to update mess fee.'; }
    }
}

// Logic: Update Daily Menu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_menu'])) {
    $day_of_week = mysqli_real_escape_string($conn, $_POST['day_of_week']);
    $breakfast = mysqli_real_escape_string($conn, trim($_POST['breakfast']));
    $lunch = mysqli_real_escape_string($conn, trim($_POST['lunch']));
    $snacks = mysqli_real_escape_string($conn, trim($_POST['snacks']));
    $dinner = mysqli_real_escape_string($conn, trim($_POST['dinner']));

    if (!empty($day_of_week) && !empty($breakfast) && !empty($lunch) && !empty($dinner)) {
        $query = "UPDATE mess_menu SET breakfast='$breakfast', lunch='$lunch', snacks='$snacks', dinner='$dinner' WHERE day_of_week='$day_of_week'";
        if (mysqli_query($conn, $query)) {
            $success = 'Mess menu for '.$day_of_week.' updated successfully!';
        } else { $error = 'Failed to update menu.'; }
    } else { $error = 'Please fill all required fields.'; }
}

// Logic: Fetch Current Data
$mess_fee_query = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'mess_fee_monthly'");
$current_mess_fee = 2000;
if ($mess_fee_query && mysqli_num_rows($mess_fee_query) > 0) {
    $current_mess_fee = (float)mysqli_fetch_assoc($mess_fee_query)['setting_value'];
}

$menus_query = "SELECT * FROM mess_menu ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
$menus_result = mysqli_query($conn, $menus_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mess Management | GUARDIAN HOSTEL Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        :root {
            --primary: #fbbf24;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
        }

        body { background: #020617; color: white; min-height: 100vh; font-family: 'Plus Jakarta Sans', sans-serif; }
        .mesh-bg { position: fixed; inset: 0; z-index: -1; background: radial-gradient(at 100% 0%, #1e1b4b 0%, #020617 50%); }

        .page-header { text-align: center; padding: 4rem 0 2rem; }
        .page-header h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; }

        /* Configuration Card */
        .config-card {
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            border-radius: 24px; padding: 2.5rem; margin-bottom: 3rem;
            box-shadow: 0 20px 40px rgba(217, 119, 6, 0.2); color: #000;
            display: flex; justify-content: space-between; align-items: center; gap: 2rem;
        }
        .config-text h3 { font-size: 1.5rem; font-weight: 800; margin-bottom: 5px; }
        .config-text p { font-size: 0.9rem; opacity: 0.8; font-weight: 600; }

        .fee-form { display: flex; gap: 10px; background: rgba(255,255,255,0.2); padding: 8px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.3); }
        .fee-form input { background: white; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 800; font-size: 1.2rem; width: 150px; outline: none; }
        .btn-update-fee { background: #000; color: white; border: none; padding: 0 25px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-update-fee:hover { background: #1e293b; transform: scale(1.02); }

        /* Weekly Table Panel */
        .glass-panel { background: var(--glass); backdrop-filter: blur(15px); border: 1px solid var(--border); border-radius: 32px; overflow: hidden; margin-bottom: 5rem; }
        .panel-head { padding: 2rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1.2rem 2rem; background: rgba(255,255,255,0.02); font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); font-size: 0.9rem; vertical-align: middle; }
        tr:hover { background: rgba(255,255,255,0.02); }

        .btn-edit-menu { background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2); padding: 8px 18px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-edit-menu:hover { background: #38bdf8; color: #000; }

        /* Modal Redesign */
        .modal { display: none; position: fixed; inset: 0; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(10px); z-index: 3000; align-items: center; justify-content: center; }
        .modal-content { background: #1e293b; border: 1px solid var(--border); width: 100%; max-width: 600px; border-radius: 32px; padding: 3rem; animation: slideUp 0.4s ease; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .form-group label { display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 8px; }
        input[type="text"] { width: 100%; background: rgba(0,0,0,0.3); border: 1px solid var(--border); border-radius: 12px; padding: 14px; color: white; margin-bottom: 1.2rem; outline: none; }

        @media (max-width: 900px) { .config-card { flex-direction: column; text-align: center; } .fee-form { width: 100%; } .fee-form input { flex: 1; } }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>
    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="page-header">
            <h1>Kitchen Logistics<span>.</span></h1>
            <p style="color: var(--text-dim);">Manage monthly billing and seasonal weekly menus</p>
        </header>

        <?php if ($success): ?><div class="alert alert-success" style="margin-bottom: 2rem;"><i class="fas fa-circle-check"></i> <?php echo $success; ?></div><?php endif; ?>

        <section class="config-card">
            <div class="config-text">
                <h3><i class="fas fa-coins"></i> Monthly Subscription Fee</h3>
                <p>Global rate applied to all student room applications</p>
            </div>
            <form method="POST" class="fee-form">
                <input type="number" name="mess_fee" value="<?php echo $current_mess_fee; ?>" min="0" step="0.01">
                <button type="submit" name="update_price" class="btn-update-fee">UPDATE RATE</button>
            </form>
        </section>

        <section class="glass-panel">
            <div class="panel-head">
                <h3 style="font-weight: 800;"><i class="far fa-calendar-alt" style="color: var(--primary); margin-right: 10px;"></i> Active Weekly Rotation</h3>
                <span style="font-size: 0.75rem; color: var(--text-dim); font-weight: 700; text-transform: uppercase;">Live Menu Schedule</span>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Weekday</th>
                            <th>Breakfast</th>
                            <th>Main Lunch</th>
                            <th>Evening</th>
                            <th>Dinner</th>
                            <th style="text-align: center;">Control</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($m = mysqli_fetch_assoc($menus_result)): ?>
                        <tr>
                            <td><strong style="color: white; font-size: 1rem;"><?php echo $m['day_of_week']; ?></strong></td>
                            <td style="color: #cbd5e1;"><?php echo htmlspecialchars($m['breakfast']); ?></td>
                            <td style="color: #cbd5e1;"><?php echo htmlspecialchars($m['lunch']); ?></td>
                            <td style="color: var(--text-dim);"><?php echo htmlspecialchars($m['snacks']) ?: '—'; ?></td>
                            <td style="color: #cbd5e1;"><?php echo htmlspecialchars($m['dinner']); ?></td>
                            <td style="text-align: center;">
                                <button class="btn-edit-menu" onclick="openEditModal('<?php echo $m['day_of_week']; ?>', '<?php echo addslashes($m['breakfast']); ?>', '<?php echo addslashes($m['lunch']); ?>', '<?php echo addslashes($m['snacks']); ?>', '<?php echo addslashes($m['dinner']); ?>')">
                                    <i class="fas fa-pen-to-square"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
                <h3 style="font-weight: 800;"><i class="fas fa-utensils" style="color: var(--primary);"></i> Edit <span id="modal-day"></span> Menu</h3>
                <span style="cursor: pointer; font-size: 1.5rem; color: var(--text-dim);" onclick="closeEditModal()">&times;</span>
            </div>
            
            <form method="POST">
                <input type="hidden" name="update_menu" value="1">
                <input type="hidden" name="day_of_week" id="edit_day_hidden">

                <div class="form-group">
                    <label>Breakfast Items</label>
                    <input type="text" name="breakfast" id="edit_breakfast" required>
                </div>
                <div class="form-group">
                    <label>Lunch Items</label>
                    <input type="text" name="lunch" id="edit_lunch" required>
                </div>
                <div class="form-group">
                    <label>Snacks (Optional)</label>
                    <input type="text" name="snacks" id="edit_snacks">
                </div>
                <div class="form-group">
                    <label>Dinner Items</label>
                    <input type="text" name="dinner" id="edit_dinner" required>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 18px; background: var(--primary); color: #000; border: none; border-radius: 14px; font-weight: 800; cursor: pointer;">
                    SAVE REVISIONS
                </button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(day, bf, lu, sn, di) {
            document.getElementById('modal-day').innerText = day;
            document.getElementById('edit_day_hidden').value = day;
            document.getElementById('edit_breakfast').value = bf;
            document.getElementById('edit_lunch').value = lu;
            document.getElementById('edit_snacks').value = sn;
            document.getElementById('edit_dinner').value = di;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(e) {
            if (e.target.className === 'modal') closeEditModal();
        }
    </script>
</body>
</html>