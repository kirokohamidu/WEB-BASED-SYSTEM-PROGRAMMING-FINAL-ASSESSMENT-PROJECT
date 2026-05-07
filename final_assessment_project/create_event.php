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

// Validation
$title = sanitize($_POST['title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$category = sanitize($_POST['category'] ?? 'Academic');
$eventDate = $_POST['event_date'] ?? '';
$location = sanitize($_POST['location'] ?? '');

$errors = [];

if (empty($title) || strlen($title) < 3) {
    $errors[] = 'Title must be at least 3 characters.';
}

if (empty($description)) {
    $errors[] = 'Description is required.';
}

if (empty($eventDate)) {
    $errors[] = 'Event date is required.';
} elseif (strtotime($eventDate) === false) {
    $errors[] = 'Invalid event date format.';
}

if (empty($location)) {
    $errors[] = 'Location is required.';
}

$validCategories = ['Academic', 'Sports', 'Cultural', 'Religious', 'Social', 'Career'];
if (!in_array($category, $validCategories)) {
    $category = 'Academic';
}

if (!empty($errors)) {
    $response['message'] = implode(' ', $errors);
    echo json_encode($response);
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO events (title, description, category, event_date, location, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $category, $eventDate, $location, $_SESSION['user_id']]);
    
    $response['success'] = true;
    $response['message'] = 'Event created successfully!';
    $response['event_id'] = $pdo->lastInsertId();
    
} catch (PDOException $e) {
    error_log("Create event error: " . $e->getMessage());
    $response['message'] = 'Failed to create event. Please try again.';
}

echo json_encode($response);

