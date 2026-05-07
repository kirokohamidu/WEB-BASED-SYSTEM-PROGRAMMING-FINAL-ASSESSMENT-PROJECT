<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

// Student only
requireStudent();

header('Content-Type: application/json');

$response = ['success' => true, 'rsvps' => [], 'message' => ''];

try {
    $pdo = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    $search = sanitize($_GET['search'] ?? '');
    $category = sanitize($_GET['category'] ?? '');
    
    $whereConditions = ["r.user_id = ? AND r.status = 'attending'"];
    $params = [$userId];
    
    if (!empty($search)) {
        $whereConditions[] = "(e.title LIKE ? OR e.description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (!empty($category)) {
        $whereConditions[] = "e.category = ?";
        $params[] = $category;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    $sql = "SELECT r.*, e.title, e.description, e.category, e.event_date, e.location, e.image_url, e.created_at as event_created
            FROM rsvps r 
            JOIN events e ON r.event_id = e.id 
            $whereClause 
            ORDER BY e.event_date ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rsvps = $stmt->fetchAll();
    
    $response['rsvps'] = $rsvps;
    
} catch (PDOException $e) {
    error_log("Get RSVPs error: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = 'Failed to retrieve RSVP history.';
}

echo json_encode($response);

