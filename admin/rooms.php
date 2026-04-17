<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Logic: Handle Add Room
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_room'])) {
    $room_number = mysqli_real_escape_string($conn, trim($_POST['room_number']));
    $room_type = mysqli_real_escape_string($conn, $_POST['room_type']);
    $capacity = (int)$_POST['capacity'];
    $rent = (float)$_POST['rent'];

    if (!empty($room_number) && !empty($room_type) && $capacity > 0 && $rent > 0) {
        $check = mysqli_query($conn, "SELECT * FROM rooms WHERE room_number = '$room_number'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Room number already exists';
        } else {
            $query = "INSERT INTO rooms (room_number, room_type, capacity, rent) VALUES ('$room_number', '$room_type', $capacity, $rent)";
            if (mysqli_query($conn, $query)) {
                $success = 'Room added successfully';
            } else { $error = 'Failed to add room'; }
        }
    } else { $error = 'Please fill all fields correctly'; }
}

// Logic: Handle Edit Room
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_room'])) {
    $room_id = (int)$_POST['room_id'];
    $room_number = mysqli_real_escape_string($conn, trim($_POST['room_number']));
    $room_type = mysqli_real_escape_string($conn, $_POST['room_type']);
    $capacity = (int)$_POST['capacity'];
    $rent = (float)$_POST['rent'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    if (!empty($room_number) && !empty($room_type) && $capacity > 0 && $rent > 0) {
        $query = "UPDATE rooms SET room_number = '$room_number', room_type = '$room_type', capacity = $capacity, rent = $rent, status = '$status' WHERE id = $room_id";
        if (mysqli_query($conn, $query)) {
            $success = 'Room updated successfully';
        } else { $error = 'Failed to update room'; }
    } else { $error = 'Please fill all fields correctly'; }
}

// Logic: Handle Delete Room
if (isset($_GET['delete'])) {
    $room_id = (int)$_GET['delete'];
    if (mysqli_query($conn, "DELETE FROM rooms WHERE id = $room_id")) {
        $success = 'Room deleted successfully';
    } else { $error = 'Failed to delete room'; }
}

// Logic: Data Query
$rooms_query = "SELECT r.*, (SELECT COUNT(*) FROM room_requests rr WHERE rr.room_id = r.id AND rr.status = 'Approved') as actual_occupied FROM rooms r ORDER BY r.room_number";
$rooms_result = mysqli_query($conn, $rooms_query);

// Helper Stats
$total_rooms = mysqli_num_rows($rooms_result);
$available_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM rooms WHERE status = 'Available'"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Inventory | GUARDIAN HOSTEL Admin</title>
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
        .page-header h1 { font-size: 2.2rem; font-weight: 800; letter-spacing: -1px; }

        .inventory-grid { display: grid; grid-template-columns: 380px 1fr; gap: 2rem; align-items: start; margin-bottom: 4rem; }

        /* Entry Form Section */
        .glass-card { background: var(--glass); backdrop-filter: blur(20px); border: 1px solid var(--border); border-radius: 28px; padding: 2.5rem; }
        
        label { display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        input, select { 
            width: 100%; background: rgba(0,0,0,0.3); border: 1px solid var(--border); 
            border-radius: 12px; padding: 14px; color: white; margin-bottom: 1.2rem; outline: none; transition: 0.3s;
        }
        input:focus { border-color: var(--primary); box-shadow: 0 0 15px rgba(251, 191, 36, 0.1); }

        .btn-add { 
            width: 100%; background: var(--primary); color: #000; border: none; padding: 16px; 
            border-radius: 14px; font-weight: 800; cursor: pointer; transition: 0.3s;
            display: flex; justify-content: center; gap: 10px;
        }
        .btn-add:hover { transform: translateY(-3px); background: #fff; box-shadow: 0 10px 25px rgba(251, 191, 36, 0.3); }

        /* Master Table Section */
        .panel-container { background: var(--glass); border: 1px solid var(--border); border-radius: 32px; overflow: hidden; }
        .panel-head { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1.2rem 1rem; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 1.2rem 1rem; border-bottom: 1px solid var(--border); font-size: 0.9rem; vertical-align: middle; }
        tr:hover { background: rgba(255,255,255,0.02); }

        /* Occupancy Bar */
        .occ-bar-container { width: 120px; display: flex; align-items: center; gap: 10px; }
        .occ-track { flex: 1; height: 6px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; }
        .occ-fill { height: 100%; transition: 0.5s ease; }

        /* Status Pills */
        .status-chip { padding: 4px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; }
        .chip-available { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .chip-full { background: rgba(239, 68, 68, 0.1); color: #f87171; }
        .chip-work { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }

        .btn-action { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border); background: var(--glass); color: white; cursor: pointer; transition: 0.3s; margin-left: 5px; }
        .btn-action:hover { border-color: var(--primary); color: var(--primary); transform: scale(1.1); }

        /* Modal */
        .modal { display: none; position: fixed; inset: 0; background: rgba(2, 6, 23, 0.8); backdrop-filter: blur(10px); z-index: 3000; align-items: center; justify-content: center; }
        .modal-content { background: #1e293b; border: 1px solid var(--border); width: 100%; max-width: 500px; border-radius: 28px; padding: 2.5rem; animation: zoomIn 0.3s; }

        @media (max-width: 1100px) { .inventory-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>
    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="page-header">
            <h1>Room Inventory<span>.</span></h1>
            <p style="color: var(--text-dim);">Manage physical assets and occupancy compliance</p>
        </header>

        <div class="inventory-grid">
            <section class="glass-card">
                <h3 style="margin-bottom: 2rem; font-weight: 800;"><i class="fas fa-plus-circle" style="color: var(--primary); margin-right: 10px;"></i> Register New Unit</h3>
                
                <?php if ($error): ?><div class="alert alert-danger" style="margin-bottom: 1.5rem;"><?php echo $error; ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success" style="margin-bottom: 1.5rem;"><?php echo $success; ?></div><?php endif; ?>

                <form method="POST">
                    <label>Internal Room Number</label>
                    <input type="text" name="room_number" placeholder="e.g., 204" required>
                    
                    <label>Accommodation Category</label>
                    <select id="room_type" name="room_type" required onchange="presetUnit()">
                        <option value="">Choose Specification</option>
                        <option value="Single">Single Sharing</option>
                        <option value="Double">Double Sharing</option>
                        <option value="Triple">Triple Sharing</option>
                    </select>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem;">
                        <div>
                            <label>Bed Cap.</label>
                            <input type="number" id="capacity" name="capacity" min="1" max="10" required>
                        </div>
                        <div>
                            <label>Monthly (₹)</label>
                            <input type="number" id="rent" name="rent" step="0.01" required>
                        </div>
                    </div>

                    <button type="submit" name="add_room" class="btn-add">
                        ADD ROOM <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </section>

            <section class="panel-container">
                <div class="panel-head">
                    <h3 style="font-weight: 800;"><i class="fas fa-list-ul" style="color: var(--primary); margin-right: 10px;"></i> Master Inventory</h3>
                    <span style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Total: <?php echo $total_rooms; ?> | Vacant: <?php echo $available_count; ?></span>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Room ID</th>
                                <th>Category</th>
                                <th>Occupancy Status</th>
                                <th>Monthly Bill</th>
                                <th>State</th>
                                <th style="text-align: right;">Control</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($r = mysqli_fetch_assoc($rooms_result)): 
                                $occ_perc = ($r['capacity'] > 0) ? ($r['actual_occupied'] / $r['capacity']) * 100 : 0;
                                $fill_color = ($occ_perc >= 100) ? '#ef4444' : ($occ_perc >= 50 ? '#fbbf24' : '#10b981');
                            ?>
                            <tr>
                                <td><strong style="color: white; font-size: 1rem;"><?php echo $r['room_number']; ?></strong></td>
                                <td style="color: #cbd5e1;"><?php echo $r['room_type']; ?></td>
                                <td>
                                    <div class="occ-bar-container">
                                        <div class="occ-track"><div class="occ-fill" style="width: <?php echo $occ_perc; ?>%; background: <?php echo $fill_color; ?>;"></div></div>
                                        <span style="font-weight: 700;"><?php echo $r['actual_occupied']; ?>/<?php echo $r['capacity']; ?></span>
                                    </div>
                                </td>
                                <td style="font-weight: 800; color: var(--primary);">₹<?php echo number_format($r['rent']); ?></td>
                                <td>
                                    <?php 
                                        $state = $r['status'];
                                        if ($state != 'Maintenance') { $state = ($r['actual_occupied'] >= $r['capacity']) ? 'Full' : 'Available'; }
                                        $chip_cls = ($state == 'Available') ? 'chip-available' : (($state == 'Full') ? 'chip-full' : 'chip-work');
                                    ?>
                                    <span class="status-chip <?php echo $chip_cls; ?>">
                                        <i class="fas fa-circle" style="font-size: 0.4rem;"></i> <?php echo strtoupper($state); ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <button class="btn-action" onclick="openEdit(<?php echo htmlspecialchars(json_encode($r)); ?>)"><i class="fas fa-edit"></i></button>
                                    <button class="btn-action" onclick="if(confirm('Delete this asset?')) window.location.href='?delete=<?php echo $r['id']; ?>'"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
                <h3 style="font-weight: 800;"><i class="fas fa-pen-to-square" style="color: var(--primary);"></i> Update Room Asset</h3>
                <span style="cursor: pointer; font-size: 1.5rem; color: var(--text-dim);" onclick="closeEdit()">&times;</span>
            </div>
            
            <form method="POST">
                <input type="hidden" name="room_id" id="e_id">
                <label>Room Number</label>
                <input type="text" name="room_number" id="e_num" required>
                
                <label>Category</label>
                <select name="room_type" id="e_type" required>
                    <option value="Single">Single Sharing</option>
                    <option value="Double">Double Sharing</option>
                    <option value="Triple">Triple Sharing</option>
                </select>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div><label>Capacity</label><input type="number" name="capacity" id="e_cap" required></div>
                    <div><label>Rent (₹)</label><input type="number" name="rent" id="e_rent" required></div>
                </div>

                <label>Operational Status</label>
                <select name="status" id="e_stat">
                    <option value="Available">Available / Active</option>
                    <option value="Maintenance">Under Maintenance</option>
                </select>

                <button type="submit" name="edit_room" class="btn-add" style="margin-top: 1rem;">UPDATE ASSET</button>
            </form>
        </div>
    </div>

    <script>
        // Preset logic
        function presetUnit() {
            const type = document.getElementById('room_type').value;
            const cap = document.getElementById('capacity');
            const rent = document.getElementById('rent');
            if (type === 'Single') { cap.value = 1; rent.value = 5000; }
            else if (type === 'Double') { cap.value = 2; rent.value = 3500; }
            else if (type === 'Triple') { cap.value = 3; rent.value = 2500; }
        }

        function openEdit(room) {
            document.getElementById('e_id').value = room.id;
            document.getElementById('e_num').value = room.room_number;
            document.getElementById('e_type').value = room.room_type;
            document.getElementById('e_cap').value = room.capacity;
            document.getElementById('e_rent').value = room.rent;
            document.getElementById('e_stat').value = room.status;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEdit() { document.getElementById('editModal').style.display = 'none'; }
        window.onclick = function(e) { if(e.target.className === 'modal') closeEdit(); }
    </script>
</body>
</html>