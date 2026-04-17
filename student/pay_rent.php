<?php
require_once '../config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id   = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$error  = '';
$success = '';

// Fetch Payment Settings
$settings_query = mysqli_query($conn, "SELECT setting_key, setting_value FROM rent_payment_settings");
$settings = [];
while ($row = mysqli_fetch_assoc($settings_query)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$rent_due_day           = $settings['rent_due_day'] ?? 5;
$advance_months_allowed = $settings['advance_months_allowed'] ?? 6;
$late_payment_fine      = $settings['late_payment_fine'] ?? 100;
$grace_period_days      = $settings['grace_period_days'] ?? 3;
$enable_advance         = $settings['enable_advance_payment'] ?? 1;

// Fetch Room Data
$room_query = "SELECT rr.*, r.room_number, r.room_type as room_type_detail, r.rent 
               FROM room_requests rr 
               LEFT JOIN rooms r ON rr.room_id = r.id 
               WHERE rr.student_id = $student_id 
               AND rr.status = 'Approved' 
               AND rr.payment_status = 'Paid'
               ORDER BY rr.response_date DESC 
               LIMIT 1";
$room_result = mysqli_query($conn, $room_query);

if (mysqli_num_rows($room_result) == 0) {
    $no_room = true;
} else {
    $no_room      = false;
    $room_data    = mysqli_fetch_assoc($room_result);
    $monthly_rent = $room_data['total_amount'];
    $allocation_date = $room_data['room_allocation_date'] ?? $room_data['response_date'];

    // Calculate Due Status
    $last_paid_query = mysqli_query($conn, "SELECT MAX(payment_month) as last_month FROM monthly_rent_payments WHERE student_id = $student_id AND payment_status = 'Success'");
    $last_paid_row   = mysqli_fetch_assoc($last_paid_query);
    $last_paid_month = $last_paid_row['last_month'];

    $current_month_start = date('Y-m-01');
    $due_month = (!$last_paid_month || $last_paid_month < $current_month_start)
        ? $current_month_start
        : date('Y-m-01', strtotime($last_paid_month . ' +1 month'));

    $current_due_date = date('Y-m-' . str_pad($rent_due_day, 2, '0', STR_PAD_LEFT));
    $is_overdue = (strtotime($current_due_date) + ($grace_period_days * 86400)) < time() && $due_month <= $current_month_start;

    $history_query  = "SELECT * FROM monthly_rent_payments WHERE student_id = $student_id ORDER BY payment_month DESC";
    $history_result = mysqli_query($conn, $history_query);
}

if (isset($_GET['success'])) {
    $success = "Payment successful! Transaction: " . htmlspecialchars($_GET['txn']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent & Payments | GUARDIAN HOSTEL</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary: #6366f1;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
        }

        body { background: #0f172a; color: white; min-height: 100vh; overflow-x: hidden; }
        .mesh-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; background: radial-gradient(at 0% 0%, #1e1b4b 0%, #0f172a 50%); }

        .page-header { text-align: center; padding: 4rem 0 2rem; }
        .page-header h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; }

        .rent-banner { background: var(--glass); backdrop-filter: blur(15px); border: 1px solid var(--glass-border); border-radius: 24px; padding: 2rem; margin-bottom: 2.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 2rem; }
        .status-pill { text-align: center; }
        .status-pill span { display: block; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .status-pill strong { font-size: 1.25rem; font-weight: 800; }

        .checkout-grid { display: grid; grid-template-columns: 1fr 400px; gap: 2rem; margin-bottom: 4rem; }
        .glass-box { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 24px; padding: 2.5rem; }

        .grid-options { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 1rem; margin: 1.5rem 0; }
        .opt-label { 
            background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); 
            padding: 1.5rem 1rem; border-radius: 16px; text-align: center; cursor: pointer; transition: 0.3s;
        }
        .opt-label:hover { border-color: var(--primary); background: rgba(99, 102, 241, 0.05); }
        .opt-label.active { border-color: var(--primary); background: var(--primary); }
        .opt-label input { display: none; }

        .overdue-banner { background: #ef4444; color: white; border-radius: 16px; padding: 1rem 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem; }

        .summary-card { position: sticky; top: 100px; background: white; color: #0f172a; border-radius: 24px; padding: 2rem; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 1rem; font-weight: 600; }
        .total-box { background: #f8fafc; padding: 1.5rem; border-radius: 16px; margin-top: 1rem; text-align: center; }
        .total-box h2 { font-size: 2.2rem; font-weight: 800; color: var(--primary); }

        .btn-pay { 
            width: 100%; background: var(--primary); color: white; border: none; padding: 18px; 
            border-radius: 14px; font-weight: 800; cursor: pointer; transition: 0.3s; margin-top: 1.5rem;
            display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 15px;
        }
        .btn-pay:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(99,102,241,0.4); }
        .btn-pay:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        .rzp-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 1rem; }
        .rzp-badge { background: #f1f5f9; border-radius: 8px; padding: 4px 10px; font-size: 0.72rem; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 4px; }

        .history-card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 24px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1.2rem 2rem; background: rgba(255,255,255,0.02); font-size: 0.8rem; color: var(--text-dim); text-transform: uppercase; }
        td { padding: 1.2rem 2rem; border-bottom: 1px solid var(--glass-border); font-size: 0.9rem; }
        .badge { padding: 4px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; }
        .badge-paid { background: rgba(16, 185, 129, 0.1); color: #10b981; }

        @media (max-width: 1000px) { .checkout-grid { grid-template-columns: 1fr; } .summary-card { position: static; } }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>
    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="page-header">
            <h1>Rent & Invoices<span>.</span></h1>
            <p style="color: var(--text-dim);">Manage your accommodation payments and history</p>
        </header>

        <?php if ($no_room): ?>
            <div class="glass-box" style="text-align: center; padding: 5rem;">
                <i class="fas fa-house-circle-exclamation" style="font-size: 4rem; color: #fbbf24; margin-bottom: 1.5rem;"></i>
                <h2>No Room Allocated</h2>
                <p style="color: var(--text-dim); margin: 1rem 0 2rem;">You need an approved room allocation to manage rent.</p>
                <a href="apply_room.php" class="btn-primary" style="padding: 15px 40px; text-decoration: none;">Apply Now</a>
            </div>
        <?php else: ?>

            <?php if ($success): ?><div class="alert alert-success" style="margin-bottom: 2rem;"><?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger" style="margin-bottom: 2rem;"><?php echo $error; ?></div><?php endif; ?>

            <div class="rent-banner">
                <div class="status-pill"><span>Allocated Room</span><strong><?php echo $room_data['room_number'] ?? 'TBA'; ?></strong></div>
                <div class="status-pill"><span>Monthly Rent</span><strong>&#8377;<?php echo number_format($monthly_rent); ?></strong></div>
                <div class="status-pill"><span>Last Payment</span><strong><?php echo $last_paid_month ? date('M Y', strtotime($last_paid_month)) : 'None'; ?></strong></div>
                <div class="status-pill"><span>Next Due</span><strong style="color: var(--primary);"><?php echo date('M Y', strtotime($due_month)); ?></strong></div>
            </div>

            <div class="checkout-grid">
                <div class="payment-main">
                    <?php if ($is_overdue): ?>
                        <div class="overdue-banner">
                            <i class="fas fa-clock-rotate-left" style="font-size: 1.5rem;"></i>
                            <div>
                                <strong style="display: block;">Payment Overdue</strong>
                                <span style="font-size: 0.85rem; opacity: 0.9;">Late fee of &#8377;<?php echo $late_payment_fine; ?> applied.</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="glass-box">
                        <label style="font-weight: 700; color: var(--text-dim); text-transform: uppercase; font-size: 0.75rem;">Select Payment Period</label>
                        <div class="grid-options" id="monthOpts">
                            <label class="opt-label active">
                                <input type="radio" name="months_ui" value="1" checked onchange="updateTotal(1)">
                                <strong>1 Month</strong>
                                <span style="display:block; font-size: 0.7rem; opacity: 0.6; margin-top: 4px;"><?php echo date('M Y', strtotime($due_month)); ?></span>
                            </label>
                            <?php if ($enable_advance): ?>
                                <?php for ($i = 2; $i <= min($advance_months_allowed, 3); $i++): ?>
                                <label class="opt-label">
                                    <input type="radio" name="months_ui" value="<?php echo $i; ?>" onchange="updateTotal(<?php echo $i; ?>)">
                                    <strong><?php echo $i; ?> Months</strong>
                                    <span style="display:block; font-size: 0.7rem; opacity: 0.6; margin-top: 4px;">Advance</span>
                                </label>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top: 2rem; padding: 2rem; background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.2); border-radius: 16px; text-align: center;">
                            <i class="fas fa-shield-check" style="font-size: 2rem; color: #6366f1; display:block; margin-bottom: 0.75rem;"></i>
                            <p style="color: var(--text-dim); font-size: 0.9rem; margin-bottom: 1.5rem;">Click below to open the secure Razorpay payment window</p>
                            <button id="rzp-rent-btn" onclick="openRentPayment()" class="btn-pay" style="max-width: 320px; margin: 0 auto;">
                                <i class="fas fa-lock"></i> PAY WITH RAZORPAY
                            </button>
                        </div>
                    </div>
                </div>

                <div class="payment-side">
                    <div class="summary-card">
                        <h3 style="margin-bottom: 1.5rem; font-weight: 800; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">Payment Summary</h3>
                        <div class="summary-row"><span>Rent Amount</span><span id="sum-rent">&#8377;<?php echo number_format($monthly_rent); ?></span></div>
                        <div class="summary-row"><span>Late Fee</span><span id="sum-fine">&#8377;<?php echo $is_overdue ? $late_payment_fine : 0; ?></span></div>
                        <div class="summary-row" style="color: #10b981;"><span>Tax & Service</span><span>&#8377;0</span></div>
                        
                        <div class="total-box">
                            <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Payable</span>
                            <h2 id="sum-total">&#8377;<?php echo number_format($monthly_rent + ($is_overdue ? $late_payment_fine : 0)); ?></h2>
                        </div>

                        <button onclick="openRentPayment()" class="btn-pay" id="rzp-rent-btn2">
                            AUTHORIZE PAYMENT <i class="fas fa-lock"></i>
                        </button>

                        <div class="rzp-badges">
                            <span class="rzp-badge"><i class="fas fa-credit-card"></i> Card</span>
                            <span class="rzp-badge"><i class="fas fa-mobile-screen"></i> UPI</span>
                            <span class="rzp-badge"><i class="fas fa-university"></i> Net Banking</span>
                            <span class="rzp-badge"><i class="fas fa-wallet"></i> Wallet</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="history-card">
                <div style="padding: 2rem; border-bottom: 1px solid var(--glass-border);">
                    <h3 style="font-weight: 800;"><i class="fas fa-receipt" style="color: var(--primary); margin-right: 10px;"></i> Recent Invoices</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr><th>Receipt</th><th>Period</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php while($p = mysqli_fetch_assoc($history_result)): ?>
                            <tr>
                                <td style="font-family: monospace;"><?php echo $p['receipt_number'] ?? $p['transaction_id']; ?></td>
                                <td style="font-weight: 700;"><?php echo date('M Y', strtotime($p['payment_month'])); ?></td>
                                <td style="font-weight: 700;">&#8377;<?php echo number_format($p['total_amount']); ?></td>
                                <td><?php echo $p['payment_method']; ?></td>
                                <td><span class="badge badge-paid">PAID</span></td>
                                <td style="color: var(--text-dim);"><?php echo date('d M, Y', strtotime($p['payment_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php endif; ?>
    </main>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const baseRent = <?php echo $no_room ? 0 : $monthly_rent; ?>;
const overdue  = <?php echo (!$no_room && $is_overdue) ? 'true' : 'false'; ?>;
const fine     = <?php echo $no_room ? 0 : $late_payment_fine; ?>;
const dueMonth = '<?php echo $no_room ? '' : $due_month; ?>';

let selectedMonths = 1;

function updateTotal(months) {
    selectedMonths = months;
    const subtotal    = baseRent * months;
    const currentFine = (overdue && months === 1) ? fine : 0;
    const total       = subtotal + currentFine;

    document.getElementById('sum-rent').innerText  = '\u20B9' + subtotal.toLocaleString('en-IN');
    document.getElementById('sum-fine').innerText  = '\u20B9' + currentFine.toLocaleString('en-IN');
    document.getElementById('sum-total').innerText = '\u20B9' + total.toLocaleString('en-IN');

    document.querySelectorAll('#monthOpts .opt-label').forEach(el => el.classList.remove('active'));
    document.querySelector('input[name="months_ui"]:checked').parentElement.classList.add('active');
}

async function openRentPayment() {
    const months      = selectedMonths;
    const currentFine = (overdue && months === 1) ? fine : 0;
    const totalAmount = (baseRent * months) + currentFine;
    const totalPaise  = totalAmount * 100;

    var btn  = document.getElementById('rzp-rent-btn');
    var btn2 = document.getElementById('rzp-rent-btn2');
    btn.disabled  = true;  btn.innerHTML  = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    btn2.disabled = true;  btn2.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

    try {
        const res = await fetch('create_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'amount=' + totalPaise + '&purpose=rent'
        });
        const order = await res.json();

        if (order.error) {
            alert('Error: ' + order.error);
            resetRentButtons();
            return;
        }

        const options = {
            key:         order.key_id,
            amount:      order.amount,
            currency:    order.currency,
            order_id:    order.order_id,
            name:        'Guardian Hostel',
            description: 'Rent Payment - ' + months + ' Month(s)',
            prefill: {
                name:    '<?php echo !$no_room ? htmlspecialchars($student_name) : ""; ?>',
                email:   '',
                contact: ''
            },
            theme: { color: '#6366f1' },
            handler: function(response) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'verify_payment.php';
                const fields = {
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id:   response.razorpay_order_id,
                    razorpay_signature:  response.razorpay_signature,
                    months_to_pay:       months,
                    due_month:           dueMonth,
                    purpose:             'rent'
                };
                for (const [k, v] of Object.entries(fields)) {
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = k; inp.value = v;
                    form.appendChild(inp);
                }
                document.body.appendChild(form);
                form.submit();
            },
            modal: {
                ondismiss: function() { resetRentButtons(); }
            }
        };
        new Razorpay(options).open();

    } catch(e) {
        alert('Something went wrong. Please try again.');
        resetRentButtons();
    }
}

function resetRentButtons() {
    var btn  = document.getElementById('rzp-rent-btn');
    var btn2 = document.getElementById('rzp-rent-btn2');
    btn.disabled  = false; btn.innerHTML  = '<i class="fas fa-lock"></i> PAY WITH RAZORPAY';
    btn2.disabled = false; btn2.innerHTML = 'AUTHORIZE PAYMENT <i class="fas fa-lock"></i>';
}
</script>
</body>
</html>
