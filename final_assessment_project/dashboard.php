<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

requireAuth();

$pageTitle = 'Dashboard - ' . APP_NAME;
$pdo = getDBConnection();
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userRole = $_SESSION['user_role'];
$isAdmin = isAdmin();

// ─── Stats ───
try {
    if ($isAdmin) {
        $totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
        $upcomingEvents = $pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= NOW()")->fetchColumn();
        $totalRSVPs = $pdo->query("SELECT COUNT(*) FROM rsvps WHERE status = 'attending'")->fetchColumn();
        $totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    } else {
        $myUpcomingRSVPs = $pdo->prepare("SELECT COUNT(*) FROM rsvps r JOIN events e ON r.event_id = e.id WHERE r.user_id = ? AND r.status = 'attending' AND e.event_date >= NOW()");
        $myUpcomingRSVPs->execute([$userId]);
        $myUpcomingRSVPs = $myUpcomingRSVPs->fetchColumn();

        $totalEvents = $pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= NOW()")->fetchColumn();

        $attendedEvents = $pdo->prepare("SELECT COUNT(*) FROM rsvps r JOIN events e ON r.event_id = e.id WHERE r.user_id = ? AND r.status = 'attending' AND e.event_date < NOW()");
        $attendedEvents->execute([$userId]);
        $attendedEvents = $attendedEvents->fetchColumn();

        $availableEvents = $pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= NOW()")->fetchColumn();
    }
} catch (PDOException $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
}

// ─── Upcoming Events (Admin: all; Student: their RSVPs) ───
try {
    if ($isAdmin) {
        $stmt = $pdo->prepare("SELECT e.*, u.full_name as creator_name FROM events e LEFT JOIN users u ON e.created_by = u.id WHERE e.event_date >= NOW() ORDER BY e.event_date ASC LIMIT 6");
        $stmt->execute();
        $upcomingEventsList = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT e.*, r.status as rsvp_status FROM events e JOIN rsvps r ON e.id = r.event_id WHERE r.user_id = ? AND r.status = 'attending' AND e.event_date >= NOW() ORDER BY e.event_date ASC LIMIT 6");
        $stmt->execute([$userId]);
        $upcomingEventsList = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $upcomingEventsList = [];
}

// ─── Recent Activity ───
try {
    if ($isAdmin) {
        $stmt = $pdo->prepare("SELECT r.*, u.full_name as user_name, e.title as event_title FROM rsvps r JOIN users u ON r.user_id = u.id JOIN events e ON r.event_id = e.id ORDER BY r.created_at DESC LIMIT 8");
        $stmt->execute();
        $recentActivity = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT e.* FROM events e WHERE e.event_date >= NOW() AND e.id NOT IN (SELECT event_id FROM rsvps WHERE user_id = ?) ORDER BY e.event_date ASC LIMIT 6");
        $stmt->execute([$userId]);
        $recentActivity = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $recentActivity = [];
}

$flash = getFlashMessage();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- Flash Messages -->
<?php if ($flash): ?>
    <div class="alert alert-<?php echo $flash['type']; ?> mb-3">
        <?php echo $flash['message']; ?>
    </div>
<?php endif; ?>

<!-- Welcome Banner -->
<section class="dashboard-welcome">
    <div class="welcome-content">
        <div class="welcome-text">
            <h1>Welcome back, <?php echo htmlspecialchars($userName); ?>!</h1>
            <p><?php echo $isAdmin ? 'Manage university events and monitor activity from your admin dashboard.' : 'Discover events, track your RSVPs, and stay connected with campus life.'; ?></p>
        </div>
        <div class="welcome-meta">
            <span class="role-badge role-<?php echo $userRole; ?>"><?php echo ucfirst($userRole); ?></span>
            <span class="welcome-date">&#128197; <?php echo date('l, F j, Y'); ?></span>
        </div>
    </div>
</section>

<!-- Stats Grid -->
<section class="stats-grid">
    <?php if ($isAdmin): ?>
        <div class="stat-card">
            <h3>Total Events</h3>
            <div class="stat-value"><?php echo $totalEvents ?? 0; ?></div>
        </div>
        <div class="stat-card">
            <h3>Upcoming</h3>
            <div class="stat-value"><?php echo $upcomingEvents ?? 0; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total RSVPs</h3>
            <div class="stat-value"><?php echo $totalRSVPs ?? 0; ?></div>
        </div>
        <div class="stat-card">
            <h3>Students</h3>
            <div class="stat-value"><?php echo $totalStudents ?? 0; ?></div>
        </div>
    <?php else: ?>
        <div class="stat-card">
            <h3>My Upcoming RSVPs</h3>
            <div class="stat-value"><?php echo $myUpcomingRSVPs ?? 0; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Events</h3>
            <div class="stat-value"><?php echo $totalEvents ?? 0; ?></div>
        </div>
        <div class="stat-card">
            <h3>Attended Events</h3>
            <div class="stat-value"><?php echo $attendedEvents ?? 0; ?></div>
        </div>
        <div class="stat-card">
            <h3>Available Events</h3>
            <div class="stat-value"><?php echo $availableEvents ?? 0; ?></div>
        </div>
    <?php endif; ?>
</section>

<!-- Quick Actions -->
<section class="quick-actions">
    <h2 class="section-title">Quick Actions</h2>
    <div class="actions-grid">
        <?php if ($isAdmin): ?>
            <a href="admin/create_event.php" class="action-card">
                <div class="action-icon">&#10133;</div>
                <h4>Create Event</h4>
                <p>Add a new university event</p>
            </a>
            <a href="admin/events.php" class="action-card">
                <div class="action-icon">&#128203;</div>
                <h4>Manage Events</h4>
                <p>Edit or delete existing events</p>
            </a>
            <a href="index.php" class="action-card">
                <div class="action-icon">&#128270;</div>
                <h4>Browse Events</h4>
                <p>View all public events</p>
            </a>
        <?php else: ?>
            <a href="index.php" class="action-card">
                <div class="action-icon">&#128270;</div>
                <h4>Browse Events</h4>
                <p>Discover upcoming events</p>
            </a>
            <a href="my_rsvps.php" class="action-card">
                <div class="action-icon">&#128197;</div>
                <h4>My RSVPs</h4>
                <p>View your registered events</p>
            </a>
            <a href="index.php#events" class="action-card">
                <div class="action-icon">&#11088;</div>
                <h4>Recommended</h4>
                <p>Events you might like</p>
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- Two Column Layout -->
<section class="dashboard-grid">
    <!-- Left Column: Events -->
    <div class="dashboard-column">
        <div class="dashboard-card">
            <div class="card-header">
                <h2><?php echo $isAdmin ? 'Upcoming Events' : 'My Upcoming Events'; ?></h2>
                <a href="<?php echo $isAdmin ? 'admin/events.php' : 'my_rsvps.php'; ?>" class="btn btn-secondary btn-sm">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingEventsList)): ?>
                    <div class="empty-state compact">
                        <div class="empty-state-icon">&#128197;</div>
                        <h4><?php echo $isAdmin ? 'No upcoming events' : 'No upcoming RSVPs'; ?></h4>
                        <p><?php echo $isAdmin ? 'Create an event to get started.' : 'Browse events and RSVP to see them here.'; ?></p>
                    </div>
                <?php else: ?>
                    <div class="events-slider-wrapper">
                        <button class="slider-btn slider-prev" onclick="slideEvents(-1)">&#10094;</button>
<div class="events-slider" id="eventsSlider">
                            <?php foreach ($upcomingEventsList as $index => $event): ?>
                                <div class="event-card slider-card" data-index="<?php echo $index; ?>">
                                    <div class="event-image" <?php if (!empty($event['image_url'])): ?>style="background: url('<?php echo APP_URL . '/' . $event['image_url']; ?>') center/cover no-repeat;"<?php endif; ?>>
                                        <span>&#9733;</span>
                                        <span class="event-category"><?php echo $event['category']; ?></span>
                                    </div>
                                    <div class="event-body">
                                        <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                        <div class="event-meta">
                                            <span>&#128197; <?php echo formatDate($event['event_date']); ?></span>
                                            <span>&#128205; <?php echo htmlspecialchars($event['location']); ?></span>
                                        </div>
                                        <p class="event-description"><?php echo htmlspecialchars(substr($event['description'], 0, 120)); ?>...</p>
                                        <div class="event-actions">
                                            <?php if ($isAdmin): ?>
                                                <a href="admin/edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                            <?php else: ?>
                                                <a href="event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary btn-sm">View Details</a>
                                                <span class="rsvp-badge">Attending</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="slider-btn slider-next" onclick="slideEvents(1)">&#10095;</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Activity / Recommendations -->
    <div class="dashboard-column">
        <div class="dashboard-card">
            <div class="card-header">
                <h2><?php echo $isAdmin ? 'Recent RSVP Activity' : 'Recommended For You'; ?></h2>
            </div>
            <div class="card-body">
                <?php if (empty($recentActivity)): ?>
                    <div class="empty-state compact">
                        <div class="empty-state-icon">&#128227;</div>
                        <h4><?php echo $isAdmin ? 'No recent activity' : 'No recommendations yet'; ?></h4>
                        <p><?php echo $isAdmin ? 'RSVP activity will appear here.' : 'Check back soon for event suggestions.'; ?></p>
                    </div>
                <?php else: ?>
                    <?php if ($isAdmin): ?>
                        <div class="activity-list">
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-avatar">
                                        <?php echo strtoupper(substr($activity['user_name'], 0, 1)); ?>
                                    </div>
                                    <div class="activity-content">
                                        <p><strong><?php echo htmlspecialchars($activity['user_name']); ?></strong> RSVPed to <strong><?php echo htmlspecialchars($activity['event_title']); ?></strong></p>
                                        <span class="activity-time"><?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?></span>
                                    </div>
                                    <div class="activity-status">
                                        <span class="status-dot status-<?php echo $activity['status']; ?>"></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="events-list">
                            <?php foreach ($recentActivity as $event): ?>
                                <div class="event-row">
                                    <div class="event-row-date">
                                        <span class="month"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                                        <span class="day"><?php echo date('j', strtotime($event['event_date'])); ?></span>
                                    </div>
                                    <div class="event-row-info">
                                        <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                        <p>
                                            <span>&#128205; <?php echo htmlspecialchars($event['location']); ?></span>
                                            <span class="separator">|</span>
                                            <span class="category-tag"><?php echo $event['category']; ?></span>
                                        </p>
                                    </div>
                                    <div class="event-row-action">
                                        <a href="event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">RSVP</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

