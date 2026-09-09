<?php
// =======================================================
// BCAC591: Event Funding & Alumni Contribution Showcase
// (Front-End Simulated Contribution Workflow)
// =======================================================

$page_title = "Event Funding & Alumni Contributions";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$submitted = false;
$pledge_amount = 0;
$contributor_name = is_logged_in() ? $_SESSION['user_name'] : '';

// Front-End Form Processing Simulation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contribute') {
    $contributor_name = trim($_POST['name'] ?? 'Alumnus');
    $pledge_amount    = (int)($_POST['amount'] ?? 1000);
    $submitted = true;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>🤝 Event Funding & Alumni Contributions</h1>
        <p>Alumni giving back: Support upcoming college reunions, technical symposiums, and student scholarship events.</p>
    </div>
</div>

<?php if ($submitted): ?>
    <div class="alert alert-success" style="padding: 20px;">
        <div>
            <h3 style="margin-bottom: 4px;">🎉 Thank You for Your Contribution, <?= e($contributor_name) ?>!</h3>
            <p style="font-size: 0.95rem;">Your simulated pledge of <strong>₹<?= number_format($pledge_amount) ?></strong> towards the Annual Alumni Homecoming Meet has been recorded on the front-end showcase.</p>
        </div>
        <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 24px; align-items: start;">
    
    <!-- Left Column: Featured Event & Goal Progress -->
    <div>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
                <div>
                    <span class="badge badge-referred">Upcoming Event</span>
                    <h2 style="font-size: 1.5rem; margin-top: 6px; color: var(--text);">Annual Alumni Homecoming & Silver Jubilee 2026</h2>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.85rem; color: var(--text-muted);">Event Date</span>
                    <div style="font-weight: 700; color: var(--primary);">15 October 2026</div>
                </div>
            </div>

            <p style="color: var(--secondary); margin-bottom: 20px; line-height: 1.6;">
                The college is hosting its biggest annual gathering to celebrate the Silver Jubilee of the Computer Applications Department. 
                Funds contributed by alumni go directly towards student hackathon prizes, event logistics, and establishing a departmental book bank.
            </p>

            <!-- Funding Progress Bar -->
            <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 18px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; font-weight: 700; margin-bottom: 6px;">
                    <span>Fundraising Progress: 65% Completed</span>
                    <span style="color: var(--success);">₹97,500 / ₹1,50,000</span>
                </div>

                <div class="progress-container">
                    <div class="progress-bar" style="width: 65%;"></div>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); margin-top: 8px;">
                    <span>Goal: ₹1,50,000</span>
                    <span>48 Alumni Contributors</span>
                    <span>18 Days Left</span>
                </div>
            </div>

            <!-- Wall of Contributors -->
            <h3 style="font-size: 1.15rem; margin-bottom: 12px;">🏆 Recent Alumni Contributors</h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; background: #ffffff; border: 1px solid var(--border); padding: 12px 16px; border-radius: 6px;">
                    <div>
                        <strong>Rahul Sharma</strong> (Batch 2021 &bull; Google)<br>
                        <small style="color: var(--text-muted);">"Happy to sponsor the 1st prize for the upcoming student hackathon!"</small>
                    </div>
                    <span style="font-weight: 700; color: var(--success); font-size: 1.1rem;">₹10,000</span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; background: #ffffff; border: 1px solid var(--border); padding: 12px 16px; border-radius: 6px;">
                    <div>
                        <strong>Priya Nair</strong> (Batch 2020 &bull; Microsoft)<br>
                        <small style="color: var(--text-muted);">"Proud to give back to our BCA Department."</small>
                    </div>
                    <span style="font-weight: 700; color: var(--success); font-size: 1.1rem;">₹5,000</span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; background: #ffffff; border: 1px solid var(--border); padding: 12px 16px; border-radius: 6px;">
                    <div>
                        <strong>Vikas Malhotra</strong> (Batch 2018 &bull; Amazon)<br>
                        <small style="color: var(--text-muted);">"Looking forward to meeting all professors at the reunion!"</small>
                    </div>
                    <span style="font-weight: 700; color: var(--success); font-size: 1.1rem;">₹7,500</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Contribution Pledge Form (Front-End Simulation) -->
    <div>
        <div class="card">
            <h3 class="card-title" style="font-size: 1.2rem;">💳 Contribute / Pledge Funds</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
                Front-end simulated alumni sponsorship portal.
            </p>

            <form method="POST" action="">
                <input type="hidden" name="action" value="contribute">

                <div class="form-group">
                    <label class="form-label" for="name">Your Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= e($contributor_name) ?>" placeholder="e.g. Rahul Sharma" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Select Contribution Amount *</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="setAmount(1000)">₹1,000</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="setAmount(2500)">₹2,500</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="setAmount(5000)">₹5,000</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="setAmount(10000)">₹10,000</button>
                    </div>
                    <input type="number" id="amount" name="amount" class="form-control" value="2500" min="100" placeholder="Custom amount in ₹" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="payment_mode">Payment Method (Simulated)</label>
                    <select id="payment_mode" name="payment_mode" class="form-control">
                        <option value="upi">UPI / QR (Google Pay, PhonePe, Paytm)</option>
                        <option value="card">Debit / Credit Card</option>
                        <option value="netbanking">Internet Banking</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="message">Message / Note for College</label>
                    <textarea id="message" name="message" class="form-control" rows="3" placeholder="Leave an encouraging message for your juniors..."></textarea>
                </div>

                <button type="submit" class="btn btn-success btn-block" style="font-size: 1rem; padding: 12px;">
                    🤝 Submit Pledge Contribution
                </button>
            </form>
        </div>
    </div>

</div>

<script>
function setAmount(val) {
    document.getElementById('amount').value = val;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
