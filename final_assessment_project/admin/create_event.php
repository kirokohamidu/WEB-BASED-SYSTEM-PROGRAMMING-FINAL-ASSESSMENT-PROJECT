<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$pageTitle = 'Create Event - ' . APP_NAME;
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $category = sanitize($_POST['category'] ?? 'Academic');
        $eventDate = $_POST['event_date'] ?? '';
        $location = sanitize($_POST['location'] ?? '');
        
        if (empty($title) || strlen($title) < 3) {
            $errors[] = 'Title must be at least 3 characters.';
        }
        if (empty($description)) {
            $errors[] = 'Description is required.';
        }
        if (empty($eventDate)) {
            $errors[] = 'Event date is required.';
        }
        if (empty($location)) {
            $errors[] = 'Location is required.';
        }
        
        $validCategories = ['Academic', 'Sports', 'Cultural', 'Religious', 'Social', 'Career'];
        if (!in_array($category, $validCategories)) {
            $category = 'Academic';
        }
        
        if (empty($errors)) {
            try {
                $pdo = getDBConnection();
                
                // Handle image upload
                $imageUrl = null;
                if (!empty($_FILES['event_image']['tmp_name'])) {
                    $uploadDir = __DIR__ . '/../uploads/';
                    $fileName = time() . '_' . basename($_FILES['event_image']['name']);
                    $targetPath = $uploadDir . $fileName;
                    
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $fileType = mime_content_type($_FILES['event_image']['tmp_name']);
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        $errors[] = 'Invalid image type. Only JPG, PNG, GIF, WEBP allowed.';
                    } elseif ($_FILES['event_image']['size'] > 2 * 1024 * 1024) {
                        $errors[] = 'Image too large. Max 2MB.';
                    } elseif (move_uploaded_file($_FILES['event_image']['tmp_name'], $targetPath)) {
                        $imageUrl = 'uploads/' . $fileName;
                    } else {
                        $errors[] = 'Failed to upload image.';
                    }
                }
                
                if (empty($errors)) {
                    $stmt = $pdo->prepare("INSERT INTO events (title, description, category, event_date, location, image_url, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $category, $eventDate, $location, $imageUrl, $_SESSION['user_id']]);
                    
                    setFlashMessage('success', 'Event created successfully!');
                    redirect(APP_URL . '/admin/events.php');
                }
                
            } catch (PDOException $e) {
                $errors[] = 'Failed to create event. Please try again.';
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h1>Create New Event</h1>
    <p>Add a new event to the university calendar</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="form-container">
    <form method="POST" action="" id="createEventForm" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        
        <div class="form-group">
            <label for="title">Event Title *</label>
            <input type="text" id="title" name="title" required minlength="3" 
                   value="<?php echo $_POST['title'] ?? ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" required><?php echo $_POST['description'] ?? ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" name="category" required>
                <option value="Academic" <?php echo ($_POST['category'] ?? '') === 'Academic' ? 'selected' : ''; ?>>Academic</option>
                <option value="Sports" <?php echo ($_POST['category'] ?? '') === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                <option value="Cultural" <?php echo ($_POST['category'] ?? '') === 'Cultural' ? 'selected' : ''; ?>>Cultural</option>
                <option value="Religious" <?php echo ($_POST['category'] ?? '') === 'Religious' ? 'selected' : ''; ?>>Religious</option>
                <option value="Social" <?php echo ($_POST['category'] ?? '') === 'Social' ? 'selected' : ''; ?>>Social</option>
                <option value="Career" <?php echo ($_POST['category'] ?? '') === 'Career' ? 'selected' : ''; ?>>Career</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="event_date">Event Date & Time *</label>
            <input type="datetime-local" id="event_date" name="event_date" required
                   value="<?php echo $_POST['event_date'] ?? ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="location">Location *</label>
            <input type="text" id="location" name="location" required
                   value="<?php echo $_POST['location'] ?? ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="event_image">Event Image</label>
            <input type="file" id="event_image" name="event_image" accept="image/*">
            <small class="form-hint">Upload an image for this event (optional). Max 2MB.</small>
        </div>
        
        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Create Event</button>
            <a href="events.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
