<?php
// =======================================================
// BCAC591: Upcoming Alumni Events & Reunions
// =======================================================

$page_title = "Upcoming Events & Reunions";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Handle RSVP / Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rsvp') {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to RSVP for college events.');
        header('Location: ' . base_url('login.php'));
        exit();
    }

    $event_title = trim($_POST['event_title'] ?? 'Event');
    set_flash('success', "🎉 You have successfully registered for '$event_title'! A confirmation has been linked to your account.");
    header('Location: ' . base_url('events.php'));
    exit();
}

// Active Filter Tab
$category_filter = $_GET['cat'] ?? 'all';

// Curated Event Records (BCAC591 Department Showcase)
$events = [
    [
        'id' => 1,
        'title' => 'Annual Alumni Homecoming & Silver Jubilee Meet 2026',
        'category' => 'Reunion',
        'category_badge' => 'badge-referred',
        'date' => '15 October 2026',
        'time' => '10:00 AM - 05:00 PM IST',
        'venue' => 'Main University Auditorium & Campus Lawns',
        'audience' => 'All Alumni, Faculty & Final Year Students',
        'description' => 'The flagship annual gathering celebrating 25 glorious years of the Computer Applications Department. Featuring keynote addresses by distinguished alumni, nostalgia walk, department achievements exhibition, networking luncheon, and student cultural performances.',
        'speakers' => 'Distinguished Alumni from Google, Microsoft, TCS & Amazon',
        'spots' => '450 Alumni & Students Registered',
        'funding_linked' => true
    ],
    [
        'id' => 2,
        'title' => 'Alumni Tech Talk: Scalable Cloud Architecture & GenAI in Industry',
        'category' => 'Tech Talk',
        'category_badge' => 'badge-shortlisted',
        'date' => '05 November 2026',
        'time' => '03:00 PM - 05:30 PM IST',
        'venue' => 'Department Seminar Hall B & Hybrid Google Meet',
        'audience' => 'BCA, B.Tech & MCA Students',
        'description' => 'Deep-dive interactive masterclass led by alumni software engineers working on enterprise cloud infrastructure. Learn how microservices, distributed caches, and modern LLM application stacks are engineered in tier-1 tech companies.',
        'speakers' => 'Rahul Sharma (Senior SWE, Google) & Alumni Panel',
        'spots' => '180 Attendees Expected',
        'funding_linked' => false
    ],
    [
        'id' => 3,
        'title' => 'Department Hackathon 2026: Code for Campus Solutions',
        'category' => 'Hackathon',
        'category_badge' => 'badge-pending',
        'date' => '22 - 23 November 2026',
        'time' => '36-Hour Continuous Hackathon',
        'venue' => 'Computer Lab 3 & Central Innovation Center',
        'audience' => 'Enrolled College Students (Teams of 2-4)',
        'description' => 'A 36-hour challenge where student teams solve real campus operational issues—from digital hostel passes to timetable scheduling. Sponsored by Alumni Contributions with cash prizes, merit certificates, and direct referral opportunities.',
        'speakers' => 'Judged & Mentored by Senior Alumni Tech Leads',
        'spots' => '₹50,000 Prize Pool Sponsored by Alumni',
        'funding_linked' => true
    ],
    [
        'id' => 4,
        'title' => 'Mock Technical Interview & Placement Readiness Bootcamp',
        'category' => 'Career Drive',
        'category_badge' => 'badge-shortlisted',
        'date' => '10 December 2026',
        'time' => '10:00 AM - 04:00 PM IST',
        'venue' => 'Placement Cell Interview Rooms & Zoom Breakouts',
        'audience' => 'Pre-Final & Final Year Students',
        'description' => '1-on-1 mock technical interviews, resume vetting sessions, and live coding feedback conducted by verified alumni hiring managers. Get tailored feedback on DSA, System Design fundamentals, and behavioral interview readiness.',
        'speakers' => 'Organized jointly with College Placement Cell',
        'spots' => 'Limited to 60 Pre-registered Candidates',
        'funding_linked' => false
    ]
];

// Filter events by category
if ($category_filter !== 'all') {
    $events = array_filter($events, function($e) use ($category_filter) {
        return strtolower($e['category']) === strtolower($category_filter);
    });
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>🎉 Upcoming Alumni Events & Reunions</h1>
        <p>Stay connected with campus life: Reunions, technical workshops, hackathons, and placement mentorship bootcamps.</p>
    </div>
    <div>
        <a href="<?= base_url('event-contribute.php') ?>" class="btn btn-primary">🤝 Support Event Funding &rarr;</a>
    </div>
</div>

<!-- Category Filter Tabs -->
<div style="display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap;">
    <a href="<?= base_url('events.php?cat=all') ?>" class="btn btn-sm <?= ($category_filter === 'all') ? 'btn-primary' : 'btn-secondary' ?>">All Events (4)</a>
    <a href="<?= base_url('events.php?cat=Reunion') ?>" class="btn btn-sm <?= ($category_filter === 'Reunion') ? 'btn-primary' : 'btn-secondary' ?>">Reunions & Meets</a>
    <a href="<?= base_url('events.php?cat=Tech Talk') ?>" class="btn btn-sm <?= ($category_filter === 'Tech Talk') ? 'btn-primary' : 'btn-secondary' ?>">Tech Talks & Workshops</a>
    <a href="<?= base_url('events.php?cat=Hackathon') ?>" class="btn btn-sm <?= ($category_filter === 'Hackathon') ? 'btn-primary' : 'btn-secondary' ?>">Hackathons</a>
    <a href="<?= base_url('events.php?cat=Career Drive') ?>" class="btn btn-sm <?= ($category_filter === 'Career Drive') ? 'btn-primary' : 'btn-secondary' ?>">Placement Bootcamps</a>
</div>

<!-- Featured Alumni Contribution Callout Banner -->
<div class="card" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; margin-bottom: 28px; padding: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <span class="badge badge-referred" style="margin-bottom: 6px; display: inline-block;">Alumni Giving Back</span>
            <h3 style="color: #1e3a8a; margin-bottom: 4px; font-size: 1.25rem;">Support the Annual Alumni Homecoming & Hackathon 2026</h3>
            <p style="color: #3b82f6; margin-bottom: 0; font-size: 0.95rem;">
                Help us reach our fundraising milestone of ₹1,50,000 for student prizes, lab resources, and reunion arrangements.
            </p>
        </div>
        <div>
            <a href="<?= base_url('event-contribute.php') ?>" class="btn btn-primary" style="background: #1d4ed8; border-color: #1d4ed8; white-space: nowrap;">
                View Contribution Showcase &rarr;
            </a>
        </div>
    </div>
</div>

<!-- Events Listing Grid -->
<?php if (empty($events)): ?>
    <div class="card" style="text-align: center; padding: 40px;">
        <p style="color: var(--text-muted); margin-bottom: 0;">No events found in this category.</p>
    </div>
<?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <?php foreach ($events as $event): ?>
            <div class="card" style="padding: 24px; transition: transform 0.2s, box-shadow 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 12px; margin-bottom: 14px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                            <span class="badge <?= $event['category_badge'] ?>"><?= e($event['category']) ?></span>
                            <span style="font-size: 0.85rem; color: var(--text-muted);">Audience: <?= e($event['audience']) ?></span>
                        </div>
                        <h2 style="font-size: 1.35rem; color: var(--text); margin-bottom: 0;"><?= e($event['title']) ?></h2>
                    </div>

                    <div style="text-align: right; background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 8px 14px;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Event Date</span>
                        <strong style="color: var(--primary); font-size: 1.05rem;"><?= e($event['date']) ?></strong>
                    </div>
                </div>

                <p style="color: var(--secondary); line-height: 1.6; margin-bottom: 18px; font-size: 0.95rem;">
                    <?= e($event['description']) ?>
                </p>

                <!-- Event Details Row -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; background: #f8fafc; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; font-size: 0.9rem;">
                    <div>
                        <span style="color: var(--text-muted); font-size: 0.8rem; display: block; text-transform: uppercase;">🕒 Time</span>
                        <strong><?= e($event['time']) ?></strong>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); font-size: 0.8rem; display: block; text-transform: uppercase;">📍 Venue</span>
                        <strong><?= e($event['venue']) ?></strong>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); font-size: 0.8rem; display: block; text-transform: uppercase;">🎙️ Featured Guest / Organizers</span>
                        <strong><?= e($event['speakers']) ?></strong>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); font-size: 0.8rem; display: block; text-transform: uppercase;">👥 Participation</span>
                        <strong style="color: var(--success);"><?= e($event['spots']) ?></strong>
                    </div>
                </div>

                <!-- Action Footer -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <?php if ($event['funding_linked']): ?>
                            <a href="<?= base_url('event-contribute.php') ?>" class="btn btn-sm btn-secondary" style="color: var(--primary); border-color: var(--primary);">
                                💖 Sponsor / Pledge Funds
                            </a>
                        <?php endif; ?>
                    </div>

                    <div>
                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Confirm RSVP registration for <?= addslashes($event['title']) ?>?');">
                            <input type="hidden" name="action" value="rsvp">
                            <input type="hidden" name="event_title" value="<?= e($event['title']) ?>">
                            <button type="submit" class="btn btn-primary">
                                🎟️ RSVP / Register for Event
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
