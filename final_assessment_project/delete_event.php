<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

// Admin only
requireAdmin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $response['message'] = 'Invalid security token.';
    echo json_encode($response);
    exit;
}

$eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
if (!$eventId) {
    $response['message'] = 'Invalid event ID.';
    echo json_encode($response);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Check if event exists
    $stmt = $pdo->prepare("SELECT id FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    
    if (!$stmt->fetch()) {
        $response['message'] = 'Event not found.';
        echo json_encode($response);
        exit;
    }
    
    // Delete related RSVPs first (due to foreign key)
    $stmt = $pdo->prepare("DELETE FROM rsvps WHERE event_id = ?");
    $stmt->execute([$eventId]);
    
    // Delete event
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    
    $response['success'] = true;
    $response['message'] = 'Event deleted successfully!';
    
} catch (PDOException $e) {
    error_log("Delete event error: " . $e->getMessage());
    $response['message'] = 'Failed to delete event. Please try again.';
}

echo json_encode($response);

