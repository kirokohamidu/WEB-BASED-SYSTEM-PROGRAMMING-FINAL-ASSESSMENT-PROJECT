<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$eventId) {
    setFlashMessage('error', 'Invalid event ID.');
    redirect(APP_URL . '/events.php');
}

$pdo = getDBConnection();

// Get event details
try {
    $stmt = $pdo->prepare("
        SELECT e.*, u.full_name as creator_name 
        FROM events e 
        LEFT JOIN users u ON e.created_by = u.id 
        WHERE e.id = ?
    ");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();
    
    if (!$event) {
        setFlashMessage('error', 'Event not found.');
        redirect(APP_URL . '/events.php');
    }
} catch (PDOException $e) {
    setFlashMessage('error', 'Failed to load event details.');
    redirect(APP_URL . '/events.php');
}

// Check if student has RSVPed
$hasRSVPed = false;
if (isLoggedIn() && isStudent()) {
    $userId = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT id FROM rsvps WHERE user_id = ? AND event_id = ? AND status = 'attending'");
        $stmt->execute([$userId, $eventId]);
        $hasRSVPed = $stmt->fetch() !== false;
    } catch (PDOException $e) {
        $hasRSVPed = false;
    }
}

// Get RSVP count
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rsvps WHERE event_id = ? AND status = 'attending'");
    $stmt->execute([$eventId]);
    $rsvpCount = $stmt->fetchColumn();
} catch (PDOException $e) {
    $rsvpCount = 0;
}

$pageTitle = htmlspecialchars($event['title']) . ' - ' . APP_NAME;
$csrfToken = generateCSRFToken();
$flash = getFlashMessage();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<?php if ($flash): ?>
    <div class="alert alert-<?php echo $flash['type']; ?> mb-3">
        <?php echo $flash['message']; ?>
    </div>
<?php endif; ?>

<?php if (!empty($event['image_url'])): ?>
<section class="event-hero-banner" style="background: url('<?php echo APP_URL . '/' . $event['image_url']; ?>') center/cover no-repeat;"></section>
<?php endif; ?>

<section class="hero event-hero">
    <div class="event-hero-inner">
        <div>
            <span class="category-tag"><?php echo $event['category']; ?></span>
            <h1 class="event-hero-title"><?php echo htmlspecialchars($event['title']); ?></h1>
            <p class="event-hero-meta">Organized by <?php echo htmlspecialchars($event['creator_name'] ?? 'UMU'); ?></p>
        </div>
        <?php if (isLoggedIn() && isStudent()): ?>
            <?php if ($hasRSVPed): ?>
                <a href="rsvp.php?event_id=<?php echo $eventId; ?>&action=cancel&csrf_token=<?php echo $csrfToken; ?>" 
                   class="btn btn-outline"
                   onclick="return confirm('Cancel your RSVP for this event?');">
                    Cancel RSVP
                </a>
            <?php else: ?>
                <a href="rsvp.php?event_id=<?php echo $eventId; ?>&action=rsvp&csrf_token=<?php echo $csrfToken; ?>" 
                   class="btn btn-primary">
                    RSVP to Event
                </a>
            <?php endif; ?>
        <?php elseif (!isLoggedIn()): ?>
            <a href="login.php" class="btn btn-primary">Log in to RSVP</a>
        <?php endif; ?>
    </div>
</section>

<section class="dashboard-grid event-details-grid">
    <div class="dashboard-column">
        <div class="dashboard-card">
            <div class="card-header">
                <h2>About This Event</h2>
            </div>
            <div class="card-body">
                <p>
                    <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="dashboard-column event-details-sidebar">
        <div class="dashboard-card">
            <div class="card-header">
                <h2>Event Details</h2>
            </div>
            <div class="card-body">
                <div class="events-list">
                    <div class="event-row">
                        <div class="detail-icon-box">&#128197;</div>
                        <div class="event-row-info">
                            <p class="detail-label">Date & Time</p>
                            <p class="detail-value"><?php echo formatDate($event['event_date']); ?></p>
                        </div>
                    </div>

                    <div class="event-row">
                        <div class="detail-icon-box">&#128205;</div>
                        <div class="event-row-info">
                            <p class="detail-label">Location</p>
                            <p class="detail-value"><?php echo htmlspecialchars($event['location']); ?></p>
                        </div>
                    </div>

                    <div class="event-row">
                        <div class="detail-icon-box">&#128101;</div>
                        <div class="event-row-info">
                            <p class="detail-label">Attendees</p>
                            <p class="detail-value"><?php echo $rsvpCount; ?> student<?php echo $rsvpCount !== 1 ? 's' : ''; ?> attending</p>
                        </div>
                    </div>

                    <div class="event-row">
                        <div class="detail-icon-box">&#128336;</div>
                        <div class="event-row-info">
                            <p class="detail-label">Posted</p>
                            <p class="detail-value"><?php echo date('F j, Y', strtotime($event['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <?php if (isLoggedIn() && isStudent()): ?>
                    <div class="event-rsvp-box">
                        <?php if ($hasRSVPed): ?>
                            <div class="alert alert-success mb-0">
                                &#10003; You are attending this event!
                            </div>
                        <?php else: ?>
                            <a href="rsvp.php?event_id=<?php echo $eventId; ?>&action=rsvp&csrf_token=<?php echo $csrfToken; ?>"
                               class="btn btn-primary btn-block">
                                RSVP to Event
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<div class="back-link">
    <a href="events.php" class="btn btn-outline">&larr; Back to Events</a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

