<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

$response = ['success' => true, 'events' => [], 'message' => ''];

try {
    $pdo = getDBConnection();
    
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
    $countSql = "SELECT COUNT(*) FROM events e $whereClause";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalCount = $stmt->fetchColumn();
    
    // Get events with creator info
    $sql = "SELECT e.*, u.full_name as creator_name 
            FROM events e 
            LEFT JOIN users u ON e.created_by = u.id 
            $whereClause 
            ORDER BY e.event_date ASC 
            LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    // Bind parameters dynamically
    $paramIndex = 1;
    foreach ($params as $param) {
        $stmt->bindValue($paramIndex++, $param);
    }
    $stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $events = $stmt->fetchAll();
    
    // Check RSVP status for logged-in students
    if (isLoggedIn() && isStudent()) {
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT event_id FROM rsvps WHERE user_id = ? AND status = 'attending'");
        $stmt->execute([$userId]);
        $rsvpedEvents = array_column($stmt->fetchAll(), 'event_id');
        
        foreach ($events as &$event) {
            $event['has_rsvped'] = in_array($event['id'], $rsvpedEvents);
        }
    }
    
    $response['events'] = $events;
    $response['total'] = $totalCount;
    $response['pages'] = ceil($totalCount / $perPage);
    $response['current_page'] = $page;
    
} catch (PDOException $e) {
    error_log("Get events error: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = 'Failed to retrieve events.';
}

echo json_encode($response);

