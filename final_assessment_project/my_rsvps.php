<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

requireStudent();

$pageTitle = 'My RSVPs - ' . APP_NAME;
$csrfToken = generateCSRFToken();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="page-header">
    <h1>My RSVP History</h1>
    <p>Events you have registered to attend</p>
</div>

<input type="hidden" id="csrfToken" value="<?php echo htmlspecialchars($csrfToken); ?>">

<div class="filters-bar">
    <div class="form-group">
        <label for="rsvpSearch">Search</label>
        <input type="text" id="rsvpSearch" placeholder="Search your RSVPs...">
    </div>
    <div class="form-group">
        <label for="rsvpCategory">Category</label>
        <select id="rsvpCategory">
            <option value="">All Categories</option>
            <option value="Academic">Academic</option>
            <option value="Sports">Sports</option>
            <option value="Cultural">Cultural</option>
            <option value="Religious">Religious</option>
            <option value="Social">Social</option>
            <option value="Career">Career</option>
        </select>
    </div>
    <button class="btn btn-primary" type="button" onclick="loadRSVPs()">Apply Filters</button>
</div>

<script>
    // Ensure CSRF token exists for RSVP POST requests
    (function () {
        const tokenInput = document.getElementById('csrfToken');
        if (!tokenInput) return;
        const token = tokenInput.value;

        // Prefer meta tag if header.js expects it
        let meta = document.querySelector('meta[name="csrf-token"]');
        if (!meta) {
            meta = document.createElement('meta');
            meta.setAttribute('name', 'csrf-token');
            document.head.appendChild(meta);
        }
        meta.setAttribute('content', token);
    })();
</script>

<div id="rsvpsContainer">
    <div class="text-center">
        <div class="loading"></div>
        <p>Loading your RSVPs...</p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
