<?php
require_once __DIR__ . '/includes/auth.php';

// Require login (or specific role if needed)
requireLogin();
// requireAdmin(); // Uncomment if only admin

$user = getCurrentUser();

// Page configuration
$pageTitle = "Titolo Pagina";
$additionalCSS = [
    // 'https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css'
];
$additionalJS = [
    // 'https://code.jquery.com/jquery-3.7.0.min.js',
    // 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js'
];

// Include header with sidebar and toolbar
include __DIR__ . '/includes/header.php';
?>

<!-- Your page content here -->
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Il tuo contenuto</h3>
        </div>
        <div class="card-body">
            <p>Questa è una pagina di esempio con sidebar e header.</p>
        </div>
    </div>
</div>

<?php
// Include footer
include __DIR__ . '/includes/footer.php';
?>
