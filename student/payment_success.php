<?php
require_once '../config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$transaction_id = isset($_GET['transaction_id']) ? mysqli_real_escape_string($conn, $_GET['transaction_id']) : '';

if (empty($transaction_id)) {
    header('Location: apply_room.php');
    exit();
}

// Logic: Data retrieval remains identical
$payment_query = "SELECT p.*, rr.room_type, rr.mess_included, s.name, s.email 
                  FROM payments p 
                  JOIN room_requests rr ON p.room_request_id = rr.id 
                  JOIN students s ON p.student_id = s.id 
                  WHERE p.transaction_id = '$transaction_id' AND p.student_id = {$_SESSION['student_id']}";
$payment_result = mysqli_query($conn, $payment_query);

if (mysqli_num_rows($payment_result) == 0) {
    header('Location: apply_room.php');
    exit();
}

$payment = mysqli_fetch_assoc($payment_result);
$payment_details = json_decode($payment['payment_details'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed | GUARDIAN HOSTEL</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --success: #10b981;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
        }

        body { background: #0f172a; color: white; min-height: 100vh; font-family: 'Plus Jakarta Sans', sans-serif; }
        .mesh-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; background: radial-gradient(at 0% 0%, #1e1b4b 0%, #0f172a 50%); }

        .success-wrapper { max-width: 800px; margin: 4rem auto; padding: 0 20px; }

        /* Success Animation Icon */
        .check-container {
            width: 100px; height: 100px; background: var(--success); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.4);
            font-size: 3rem; animation: zoomIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* Receipt Card */
        .glass-receipt {
            background: var(--glass); backdrop-filter: blur(20px); border: 1px solid var(--border);
            border-radius: 32px; padding: 3rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            position: relative; overflow: hidden;
        }
        
        /* Decorative edges for receipt feel */
        .glass-receipt::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 6px;
            background: repeating-linear-gradient(90deg, var(--primary), var(--primary) 20px, transparent 20px, transparent 40px);
        }

        .receipt-header { text-align: center; margin-bottom: 2.5rem; }
        .receipt-header h2 { font-weight: 800; font-size: 1.8rem; letter-spacing: -0.5px; }
        .receipt-header p { color: var(--text-dim); font-size: 0.9rem; margin-top: 5px; }

        .info-row { display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid var(--border); }
        .info-row span { color: var(--text-dim); font-weight: 600; font-size: 0.9rem; }
        .info-row strong { font-weight: 700; color: white; }

        .total-banner {
            background: rgba(99, 102, 241, 0.1); border-radius: 16px; padding: 1.5rem;
            margin-top: 2rem; display: flex; justify-content: space-between; align-items: center;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }
        .total-banner h3 { font-size: 1.8rem; color: var(--primary); font-weight: 800; }

        .action-group { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 2.5rem; }
        .btn-action {
            padding: 16px; border-radius: 14px; font-weight: 800; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s;
            text-decoration: none; font-size: 0.9rem;
        }
        .btn-pdf { background: var(--primary); color: white; }
        .btn-pdf:hover { background: #4f46e5; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3); }
        .btn-print { background: rgba(255,255,255,0.05); color: white; border: 1px solid var(--border); }
        .btn-print:hover { background: rgba(255,255,255,0.1); }

        .footer-note { text-align: center; margin-top: 3rem; color: var(--text-dim); font-size: 0.85rem; line-height: 1.6; }
        .footer-note a { color: var(--primary); text-decoration: none; font-weight: 700; }

        @media (max-width: 600px) {
            .action-group { grid-template-columns: 1fr; }
            .glass-receipt { padding: 2rem 1.5rem; }
        }

        @media print {
            .main-navbar, .action-group, .mesh-bg, .footer-note, .check-container { display: none !important; }
            body { background: white !important; color: black !important; }
            .glass-receipt { background: white !important; border: 2px solid #eee !important; box-shadow: none !important; color: black !important; backdrop-filter: none !important; }
            .info-row strong, .receipt-header h2 { color: black !important; }
        }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>

    <?php include 'includes/nav.php'; ?>

    <div class="success-wrapper">
        <div class="check-container animate__animated animate__bounceIn">
            <i class="fas fa-check"></i>
        </div>

        <div class="glass-receipt animate__animated animate__fadeInUp">
            <div class="receipt-header">
                <h2>Booking Confirmed<span>.</span></h2>
                <p>Transaction ID: <?php echo $payment['transaction_id']; ?></p>
            </div>

            <div class="info-row">
                <span>Resident Name</span>
                <strong><?php echo htmlspecialchars($payment['name']); ?></strong>
            </div>
            <div class="info-row">
                <span>Email Address</span>
                <strong><?php echo htmlspecialchars($payment['email']); ?></strong>
            </div>
            <div class="info-row">
                <span>Date of Payment</span>
                <strong><?php echo date('d M Y, h:i A', strtotime($payment['payment_date'])); ?></strong>
            </div>
            <div class="info-row">
                <span>Room Specification</span>
                <strong><?php echo $payment['room_type']; ?> Sharing</strong>
            </div>
            <div class="info-row">
                <span>Mess Facility</span>
                <strong><?php echo $payment['mess_included'] ? 'Included' : 'Excluded'; ?></strong>
            </div>
            <div class="info-row">
                <span>Payment Source</span>
                <strong>
                    <i class="fas fa-<?php echo $payment['payment_method'] == 'Card' ? 'credit-card' : 'mobile-screen'; ?>" style="margin-right: 5px; color: var(--primary);"></i>
                    <?php echo $payment['payment_method']; ?>
                </strong>
            </div>

            <div class="total-banner">
                <span style="font-weight: 800; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">Amount Charged</span>
                <h3>₹<?php echo number_format($payment['amount'], 2); ?></h3>
            </div>

            <div class="action-group">
                <button onclick="window.print()" class="btn-action btn-print">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <button onclick="downloadReceipt()" class="btn-action btn-pdf">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
            </div>
        </div>

        <div class="footer-note">
            <p><i class="fas fa-shield-halved" style="color: var(--success); margin-right: 5px;"></i> Your booking is now secured. The warden will assign your room number within 24 hours.</p>
            <a href="dashboard.php" style="display: inline-block; margin-top: 20px;"><i class="fas fa-arrow-left"></i> Go to My Dashboard</a>
        </div>
    </div>

    <script>
        function downloadReceipt() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Premium Branding Colors
            const accent = [99, 102, 241]; // Indigo
            const dark = [15, 23, 42];    // Slate
            
            // Header Bar
            doc.setFillColor(dark[0], dark[1], dark[2]);
            doc.rect(0, 0, 210, 40, 'F');
            
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(22);
            doc.setFont('helvetica', 'bold');
            doc.text('GUARDIAN HOSTEL RESIDENCE', 105, 20, { align: 'center' });
            
            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            doc.text('OFFICIAL PAYMENT RECEIPT', 105, 30, { align: 'center' });

            // Body Content
            doc.setTextColor(dark[0], dark[1], dark[2]);
            doc.setFontSize(12);
            let y = 60;

            const rows = [
                ['Transaction ID', '<?php echo $payment['transaction_id']; ?>'],
                ['Student Name', '<?php echo $payment['name']; ?>'],
                ['Room Type', '<?php echo $payment['room_type']; ?> Sharing'],
                ['Payment Date', '<?php echo date('d M Y, h:i A', strtotime($payment['payment_date'])); ?>'],
                ['Payment Method', '<?php echo $payment['payment_method']; ?>'],
                ['Status', '<?php echo $payment['status']; ?>']
            ];

            rows.forEach(row => {
                doc.setFont('helvetica', 'bold');
                doc.text(row[0] + ':', 20, y);
                doc.setFont('helvetica', 'normal');
                doc.text(row[1], 80, y);
                y += 12;
            });

            // Total Box
            y += 10;
            doc.setFillColor(248, 250, 252);
            doc.roundedRect(20, y, 170, 25, 3, 3, 'F');
            
            doc.setFontSize(14);
            doc.setFont('helvetica', 'bold');
            doc.text('TOTAL PAID:', 30, y + 16);
            
            doc.setTextColor(accent[0], accent[1], accent[2]);
            doc.text('INR <?php echo number_format($payment['amount'], 2); ?>', 180, y + 16, { align: 'right' });

            // Footer
            doc.setFontSize(9);
            doc.setTextColor(148, 163, 184);
            doc.text('This is a digitally generated receipt. No signature required.', 105, 280, { align: 'center' });

            doc.save('GUARDIAN HOSTEL_Receipt_<?php echo $payment['transaction_id']; ?>.pdf');
        }
    </script>
</body>
</html>