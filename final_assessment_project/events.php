<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$pageTitle = 'Events - ' . APP_NAME;

$pdo = getDBConnection();

// Get filter values
$search = sanitize($_GET['search'] ?? '');
$category = sanitize($_GET['category'] ?? '');
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build query
$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(e.title LIKE ? OR e.description LIKE ? OR e.location LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($category)) {
    $whereConditions[] = "e.category = ?";
    $params[] = $category;
}

if (!empty($dateFrom)) {
    $whereConditions[] = "e.event_date >= ?";
    $params[] = $dateFrom;
}

if (!empty($dateTo)) {
    $whereConditions[] = "e.event_date <= ?";
    $params[] = $dateTo;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total count
try {
    $countSql = "SELECT COUNT(*) FROM events e $whereClause";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalCount = $stmt->fetchColumn();
} catch (PDOException $e) {
    $totalCount = 0;
}

// Get events with creator info
try {
    $sql = "SELECT e.*, u.full_name as creator_name 
            FROM events e 
            LEFT JOIN users u ON e.created_by = u.id 
            $whereClause 
            ORDER BY e.event_date ASC 
            LIMIT ? OFFSET ?";

    $stmt = $pdo->prepare($sql);
    $paramIndex = 1;
    foreach ($params as $param) {
        $stmt->bindValue($paramIndex++, $param);
    }
    $stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}

$totalPages = ceil($totalCount / $perPage);

// Check RSVP status for logged-in students
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

$flash = getFlashMessage();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- Flash Messages -->
<?php if ($flash): ?>
    <div class="alert alert-<?php echo $flash['type']; ?> mb-3">
        <?php echo $flash['message']; ?>
    </div>
<?php endif; ?>

<section class="hero" style="padding: 40px;">
    <h1>Events</h1>
    <p>Discover and RSVP to upcoming university events</p>
</section>

<!-- Filters -->
<section class="filters-bar">
    <form method="GET" action="events.php" style="display: contents;">
        <div class="form-group">
            <label for="search">Search</label>
            <input type="text" id="search" name="search" placeholder="Search events..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category">
                <option value="">All Categories</option>
                <option value="Academic" <?php echo $category === 'Academic' ? 'selected' : ''; ?>>Academic</option>
                <option value="Sports" <?php echo $category === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                <option value="Cultural" <?php echo $category === 'Cultural' ? 'selected' : ''; ?>>Cultural</option>
                <option value="Religious" <?php echo $category === 'Religious' ? 'selected' : ''; ?>>Religious</option>
                <option value="Social" <?php echo $category === 'Social' ? 'selected' : ''; ?>>Social</option>
                <option value="Career" <?php echo $category === 'Career' ? 'selected' : ''; ?>>Career</option>
            </select>
        </div>
        <div class="form-group">
            <label for="date_from">From</label>
            <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
        </div>
        <div class="form-group">
            <label for="date_to">To</label>
            <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if (!empty($search) || !empty($category) || !empty($dateFrom) || !empty($dateTo)): ?>
            <a href="events.php" class="btn btn-outline">Clear</a>
        <?php endif; ?>
    </form>
</section>

<!-- Events Grid -->
<section>
    <?php if (empty($events)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128197;</div>
            <h3>No events found</h3>
            <p>Try adjusting your search or filters.</p>
        </div>
    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
                <div class="event-card">
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
                            <a href="event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary btn-sm">View Details</a>
                            <?php if (isLoggedIn() && isStudent()): ?>
                                <?php if (in_array($event['id'], $rsvpedEvents ?? [])): ?>
                                    <span class="btn btn-sm" style="background:#d4edda;color:#155724;cursor:default;">Attending</span>
                                <?php else: ?>
                                    <a href="rsvp.php?event_id=<?php echo $event['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" class="btn btn-primary btn-sm">RSVP</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn btn-secondary btn-sm">Previous</a>
                <?php else: ?>
                    <button class="btn btn-secondary btn-sm" disabled>Previous</button>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <button class="btn btn-secondary btn-sm" style="background:var(--umu-navy);color:var(--umu-gold);" disabled><?php echo $i; ?></button>
                    <?php else: ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="btn btn-secondary btn-sm"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn btn-secondary btn-sm">Next</a>
                <?php else: ?>
                    <button class="btn btn-secondary btn-sm" disabled>Next</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

