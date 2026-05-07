<?php  
require_once __DIR__ . '/config/config.php';  
require_once __DIR__ . '/includes/functions.php';  
require_once __DIR__ . '/config/database.php';  

// Student only  
requireStudent();

// Determine if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax) { header('Content-Type: application/json'); }

$response = ['success' => false, 'message' => ''];

// Accept inputs from GET (links) or POST (forms/AJAX)
$csrfToken = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
$eventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT) 
           ?: filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$action = sanitize($_GET['action'] ?? $_POST['action'] ?? 'rsvp');

if (!validateCSRFToken($csrfToken)) {
    $response['message'] = 'Invalid security token.';
    if ($isAjax) { echo json_encode($response); exit; }
    setFlashMessage('error', $response['message']);
    redirect(APP_URL . '/events.php');
}

if (!$eventId) {
    $response['message'] = 'Invalid event ID.';
    if ($isAjax) { echo json_encode($response); exit; }
    setFlashMessage('error', $response['message']);
    redirect(APP_URL . '/events.php');
}

try {
    $pdo = getDBConnection();
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT id, title FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();
    if (!$event) {
        $response['message'] = 'Event not found.';
        if ($isAjax) { echo json_encode($response); exit; }
        setFlashMessage('error', $response['message']);
        redirect(APP_URL . '/events.php');
    }

    if ($action === 'rsvp') {
        try {
            $stmt = $pdo->prepare("INSERT INTO rsvps (user_id, event_id, status) VALUES (?, ?, 'attending')");
            $stmt->execute([$userId, $eventId]);
            $response['success'] = true;
            $response['message'] = 'You have successfully RSVP\'d to "' . $event['title'] . '"!';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { $response['message'] = 'You have already RSVP\'d to this event.'; }
            else { throw $e; }
        }
    } elseif ($action === 'cancel') {
        $stmt = $pdo->prepare("DELETE FROM rsvps WHERE user_id = ? AND event_id = ?");
        $stmt->execute([$userId, $eventId]);
        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Your RSVP has been cancelled.';
        } else {
            $response['message'] = 'You have not RSVP\'d to this event.';
        }
    } else {
        $response['message'] = 'Invalid action.';
    }
} catch (PDOException $e) {
    error_log("RSVP error: " . $e->getMessage());
    $response['message'] = 'Failed to process RSVP. Please try again.';
}

if ($isAjax) {
    echo json_encode($response);
    exit;
}

// Browser GET/POST: redirect with flash message
setFlashMessage($response['success'] ? 'success' : 'error', $response['message']);
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($referer, 'event_details.php') !== false) { redirect($referer); }
redirect(APP_URL . '/events.php');
