<?php
require_once '../config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id   = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

$request_id = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;

// Request Validation
$request_query = "SELECT rr.*, s.name as student_name, s.email, s.phone 
                  FROM room_requests rr 
                  JOIN students s ON rr.student_id = s.id 
                  WHERE rr.id = $request_id AND rr.student_id = $student_id AND rr.status IN ('Pending', 'Approved')";
$request_result = mysqli_query($conn, $request_query);

if (mysqli_num_rows($request_result) == 0) {
    header('Location: apply_room.php');
    exit();
}

$request = mysqli_fetch_assoc($request_result);

if ($request['payment_status'] == 'Paid') {
    header('Location: apply_room.php?already_paid=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout | GUARDIAN HOSTEL</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary: #6366f1;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
        }

        body { background: #0f172a; color: white; min-height: 100vh; font-family: 'Plus Jakarta Sans', sans-serif; }
        .mesh-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; background: radial-gradient(at 0% 0%, #1e1b4b 0%, #0f172a 50%); }

        .checkout-header { text-align: center; padding: 4rem 0 2rem; }
        .checkout-header h1 { font-size: 2.2rem; font-weight: 800; letter-spacing: -1px; }

        .checkout-wrapper { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 400px; gap: 2.5rem; padding: 0 20px 4rem; }

        .glass-box { background: var(--glass); backdrop-filter: blur(20px); border: 1px solid var(--border); border-radius: 28px; padding: 2.5rem; }

        .invoice-card { background: white; color: #0f172a; border-radius: 28px; padding: 2.5rem; position: sticky; top: 100px; }
        .invoice-card h3 { font-weight: 800; margin-bottom: 1.5rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        .inv-row { display: flex; justify-content: space-between; margin-bottom: 0.8rem; font-size: 0.9rem; font-weight: 500; }
        .inv-total { border-top: 2px solid #f1f5f9; margin-top: 1.5rem; padding-top: 1.5rem; text-align: center; }
        .inv-total h2 { font-size: 2.2rem; font-weight: 800; color: var(--primary); }

        .btn-pay { 
            width: 100%; background: var(--primary); color: white; border: none; padding: 18px; 
            border-radius: 14px; font-weight: 800; cursor: pointer; transition: 0.3s; margin-top: 2rem;
            display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 15px;
        }
        .btn-pay:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4); }
        .btn-pay:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        .rzp-info { text-align: center; padding: 2rem 0 1rem; }
        .rzp-info i { font-size: 2.5rem; color: #6366f1; display: block; margin-bottom: 1rem; }
        .rzp-info h3 { font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem; }
        .rzp-info p { color: var(--text-dim); font-size: 0.9rem; margin-bottom: 1.5rem; }
        .rzp-methods { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem; }
        .rzp-badge { background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 10px; padding: 8px 16px; font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 6px; }

        @media (max-width: 950px) { .checkout-wrapper { grid-template-columns: 1fr; } .invoice-card { position: static; } }
    </style>
</head>
<body>

    <div class="mesh-bg"></div>
    <?php include 'includes/nav.php'; ?>

    <main class="container">
        <header class="checkout-header">
            <h1>Secure Payment Terminal<span>.</span></h1>
            <p style="color: var(--text-dim);">Verify your booking details and complete payment</p>
        </header>

        <div class="checkout-wrapper">
            <div class="main-content">
                <div class="glass-box">
                    <div class="rzp-info">
                        <i class="fas fa-shield-check"></i>
                        <h3>Pay Securely with Razorpay</h3>
                        <p>Choose from multiple payment options inside the secure Razorpay checkout</p>
                        <div class="rzp-methods">
                            <span class="rzp-badge"><i class="fas fa-credit-card"></i> Card</span>
                            <span class="rzp-badge"><i class="fas fa-mobile-screen"></i> UPI</span>
                            <span class="rzp-badge"><i class="fas fa-university"></i> Net Banking</span>
                            <span class="rzp-badge"><i class="fas fa-wallet"></i> Wallet</span>
                        </div>
                        <button id="rzp-btn" onclick="openRazorpay()" class="btn-pay" style="max-width: 350px; margin: 0 auto;">
                            <i class="fas fa-lock"></i> PAY &#8377;<?php echo number_format($request['total_amount']); ?> SECURELY
                        </button>
                        <p style="margin-top: 1rem; font-size: 0.72rem; color: var(--text-dim);">
                            <i class="fas fa-shield"></i> 256-bit SSL Encrypted &nbsp;|&nbsp; Powered by Razorpay
                        </p>
                    </div>
                </div>

                <div style="margin-top: 2rem; text-align: center;">
                    <a href="apply_room.php" style="color: var(--text-dim); text-decoration: none; font-size: 0.9rem; font-weight: 600;">
                        <i class="fas fa-arrow-left"></i> Back to Room Selection
                    </a>
                </div>
            </div>

            <aside class="invoice-side">
                <div class="invoice-card">
                    <h3>Summary</h3>
                    <div class="inv-row"><span>Student ID</span><span>#<?php echo $student_id; ?></span></div>
                    <div class="inv-row"><span>Selection</span><span><?php echo $request['room_type']; ?> Sharing</span></div>
                    <div class="inv-row"><span>Mess Plan</span><span><?php echo $request['mess_included'] ? 'Included' : 'None'; ?></span></div>
                    
                    <div class="inv-total">
                        <span style="font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Amount to Pay</span>
                        <h2>&#8377;<?php echo number_format($request['total_amount']); ?></h2>
                    </div>

                    <button onclick="openRazorpay()" class="btn-pay" id="rzp-btn2">
                        SECURE PAY <i class="fas fa-lock"></i>
                    </button>
                    
                    <p style="text-align: center; font-size: 0.7rem; color: #94a3b8; margin-top: 1.5rem;">
                        <i class="fas fa-shield-check"></i> 256-bit SSL Encrypted Payment
                    </p>
                </div>
            </aside>
        </div>
    </main>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
async function openRazorpay() {
    var btn  = document.getElementById('rzp-btn');
    var btn2 = document.getElementById('rzp-btn2');
    btn.disabled  = true;  btn.innerHTML  = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    btn2.disabled = true;  btn2.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

    try {
        const res = await fetch('create_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'amount=<?php echo $request["total_amount"] * 100; ?>&purpose=room_booking'
        });
        const order = await res.json();

        if (order.error) {
            alert('Error: ' + order.error);
            resetButtons();
            return;
        }

        const options = {
            key:         order.key_id,
            amount:      order.amount,
            currency:    order.currency,
            order_id:    order.order_id,
            name:        'Guardian Hostel',
            description: 'Room Booking Payment',
            image:       '',
            prefill: {
                name:    '<?php echo htmlspecialchars($request["student_name"]); ?>',
                email:   '<?php echo htmlspecialchars($request["email"]); ?>',
                contact: '<?php echo htmlspecialchars($request["phone"]); ?>'
            },
            notes: {
                request_id: '<?php echo $request_id; ?>'
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
                    request_id:          '<?php echo $request_id; ?>',
                    purpose:             'room_booking'
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
                ondismiss: function() { resetButtons(); }
            }
        };
        new Razorpay(options).open();

    } catch(e) {
        alert('Something went wrong. Please try again.');
        resetButtons();
    }
}

function resetButtons() {
    var btn  = document.getElementById('rzp-btn');
    var btn2 = document.getElementById('rzp-btn2');
    btn.disabled  = false; btn.innerHTML  = '<i class="fas fa-lock"></i> PAY &#8377;<?php echo number_format($request["total_amount"]); ?> SECURELY';
    btn2.disabled = false; btn2.innerHTML = 'SECURE PAY <i class="fas fa-lock"></i>';
}
</script>
</body>
</html>
