<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$pageTitle = 'Welcome - ' . APP_NAME;

// Get stats
try {
    $pdo = getDBConnection();
    $totalEvents = $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
    $upcomingEvents = $pdo->query('SELECT COUNT(*) FROM events WHERE event_date >= NOW()')->fetchColumn();
    $totalRSVPs = $pdo->query("SELECT COUNT(*) FROM rsvps WHERE status = 'attending'")->fetchColumn();
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
} catch (PDOException $e) {
    $totalEvents = $upcomingEvents = $totalRSVPs = $totalUsers = 0;
}

// Get upcoming events
try {
    $stmt = $pdo->prepare('SELECT e.*, u.full_name as creator_name FROM events e LEFT JOIN users u ON e.created_by = u.id WHERE e.event_date >= NOW() ORDER BY e.event_date ASC LIMIT 6');
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}

// Check RSVP status for logged-in students
$rsvpedEvents = [];
if (isLoggedIn() && isStudent()) {
    $userId = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT event_id FROM rsvps WHERE user_id = ? AND status = 'attending'");
        $stmt->execute([$userId]);
        $rsvpedEvents = array_column($stmt->fetchAll(), 'event_id');
    } catch (PDOException $e) {
        $rsvpedEvents = [];
    }
}
?>
<?php
$bodyClass = 'home-page';
include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="logo-container">
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT_wz3_1v9U3mJWHZ1zQLc3g2diAN5yWCNtuQ&amp;s" alt="Uganda Martyrs University Logo">
    </div>
    <h1>Uganda Martyrs University Events</h1>
    <p>Discover, join, and manage university events. From academic seminars to sports galas and cultural celebrations.</p>
    <div class="hero-actions">
        <a href="events.php" class="btn btn-primary">Browse Events</a>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-outline">Get Started</a>
        <?php elseif (isAdmin()): ?>
            <a href="admin/events.php" class="btn btn-outline">Manage Events</a>
        <?php endif; ?></div>
</section>

<section class="stats-grid">
    <div class="stat-card">
        <h3>Total Events</h3>
        <div class="stat-value"><?php echo $totalEvents; ?></div></div>
    <div class="stat-card">
        <h3>Upcoming</h3>
        <div class="stat-value"><?php echo $upcomingEvents; ?></div></div>
    <div class="stat-card">
        <h3>Total RSVPs</h3>
        <div class="stat-value"><?php echo $totalRSVPs; ?></div></div>
    <div class="stat-card">
        <h3>Students</h3>
        <div class="stat-value"><?php echo $totalUsers; ?></div></div>
</section>

<section>
    <div class="section-heading"><h2>Upcoming Events</h2></div>

    <?php if (empty($events)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128197;</div>
            <h3>No upcoming events</h3>
            <p>Check back later for new events.</p></div>
    <?php else: ?>
        <div class="events-slider-wrapper">
            <button class="slider-btn slider-prev" onclick="slideEvents(-1)">&#10094;</button>
<div class="events-slider" id="eventsSlider">
                <?php foreach ($events as $index => $event): ?>
                    <div class="event-card slider-card" data-index="<?php echo $index; ?>">
                        <div class="event-image" <?php if (!empty($event['image_url'])): ?>style="background: url('<?php echo APP_URL . '/' . $event['image_url']; ?>') center/cover no-repeat;"<?php endif; ?>>
                            <span>&#9733;</span>
                            <span class="event-category"><?php echo $event['category']; ?></span></div>
                        <div class="event-body">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <div class="event-meta">
                                <span>&#128197; <?php echo formatDate($event['event_date']); ?></span>
                                <span>&#128205; <?php echo htmlspecialchars($event['location']); ?></span></div>
                            <p class="event-description"><?php echo htmlspecialchars(substr($event['description'], 0, 120)); ?>...</p>
                            <div class="event-actions">
                                <a href="event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary btn-sm">View Details</a>
                                <?php if (isLoggedIn() && isStudent() && !in_array($event['id'], $rsvpedEvents ?? [])): ?>
                                    <a href="rsvp.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm">RSVP</a>
                                <?php endif; ?></div></div></div>
                <?php endforeach; ?></div>
            <button class="slider-btn slider-next" onclick="slideEvents(1)">&#10095;</button></div>
        <div class="text-center mt-3">
            <a href="events.php" class="btn btn-primary">View All Events</a></div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

