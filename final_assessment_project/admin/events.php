<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$pageTitle = 'Manage Events - ' . APP_NAME;
$pdo = getDBConnection();

// Handle delete via GET (as a fallback/link)
$eventId = filter_input(INPUT_GET, 'delete_id', FILTER_VALIDATE_INT);
if ($eventId) {
    if (validateCSRFToken($_GET['csrf_token'] ?? '')) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            if ($stmt->fetch()) {
                $pdo->prepare("DELETE FROM rsvps WHERE event_id = ?")->execute([$eventId]);
                $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$eventId]);
                setFlashMessage('success', 'Event deleted successfully!');
            } else {
                setFlashMessage('error', 'Event not found.');
            }
        } catch (PDOException $e) {
            setFlashMessage('error', 'Failed to delete event.');
        }
    } else {
        setFlashMessage('error', 'Invalid security token.');
    }
    redirect(APP_URL . '/admin/events.php');
}

// Get events
try {
    $stmt = $pdo->prepare("
        SELECT e.*, u.full_name as creator_name,
               (SELECT COUNT(*) FROM rsvps WHERE event_id = e.id AND status = 'attending') as rsvp_count
        FROM events e
        LEFT JOIN users u ON e.created_by = u.id
        ORDER BY e.event_date DESC
    ");
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}

$flash = getFlashMessage();
$csrfToken = generateCSRFToken();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<?php if ($flash): ?>
    <div class="alert alert-<?php echo $flash['type']; ?> mb-3">
        <?php echo $flash['message']; ?>
    </div>
<?php endif; ?>



<div class="manage-header">
    <div>
        <h1>Manage Events</h1>
        <p>Create, edit, and delete university events</p>
    </div>
    <a href="create_event.php" class="btn btn-primary">+ Create New Event</a>
</div>



<?php if (empty($events)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">&#128197;</div>
        <h3>No events yet</h3>
        <p>Get started by creating your first university event.</p>
        <a href="create_event.php" class="btn btn-primary mt-2">Create Event</a>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Category</th>
                    <th>Date & Time</th>
                    <th>Location</th>
                    <th class="text-center">RSVPs</th>
                    <th>Created By</th>
                    <th class="actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td>
                            <strong class="event-table-title"><?php echo htmlspecialchars($event['title']); ?></strong>
                            <p class="event-table-desc">
                                <?php echo htmlspecialchars(substr($event['description'], 0, 60)); ?>...
                            </p>
                        </td>
                        <td>
                            <span class="category-tag"><?php echo $event['category']; ?></span>
                        </td>
                        <td><?php echo formatDate($event['event_date']); ?></td>
                        <td><?php echo htmlspecialchars($event['location']); ?></td>
                        <td class="text-center">
                            <span class="rsvp-count"><?php echo $event['rsvp_count']; ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($event['creator_name'] ?? 'Unknown'); ?></td>
                        <td>
                            <div class="actions">
                                <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                <a href="events.php?delete_id=<?php echo $event['id']; ?>&csrf_token=<?php echo $csrfToken; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
