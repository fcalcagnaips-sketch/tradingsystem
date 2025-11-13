<?php
require_once __DIR__ . '/includes/auth.php';

// Solo admin possono accedere
requireAdmin();

$user = getCurrentUser();
$conn = getDBConnection();

$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'toggle_active') {
        $userId = intval($_POST['user_id']);
        $stmt = $conn->prepare("UPDATE users SET active = NOT active WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            $success = 'Stato utente aggiornato';
        }
        $stmt->close();
    }
    
    elseif ($action === 'assign_role') {
        $userId = intval($_POST['user_id']);
        $roleName = $_POST['role_name'];
        if (assignRole($userId, $roleName)) {
            $success = 'Ruolo assegnato con successo';
        } else {
            $error = 'Errore nell\'assegnazione del ruolo';
        }
    }
    
    elseif ($action === 'remove_role') {
        $userId = intval($_POST['user_id']);
        $roleName = $_POST['role_name'];
        if (removeRole($userId, $roleName)) {
            $success = 'Ruolo rimosso con successo';
        } else {
            $error = 'Errore nella rimozione del ruolo';
        }
    }
}

// Get all users with their roles
$query = "
    SELECT u.*, 
           GROUP_CONCAT(r.name) as roles,
           GROUP_CONCAT(r.display_name) as role_names
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
";
$result = $conn->query($query);
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$allRoles = getAllRoles();

// Page configuration
$pageTitle = "Gestione Utenti";
$additionalCSS = [
    'https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css'
];
$additionalJS = [
    'https://code.jquery.com/jquery-3.7.0.min.js',
    'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js'
];

include __DIR__ . '/includes/header.php';
?>

<style>
    .badge {
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 4px;
    }
    .badge-admin {
        background: #f1416c;
        color: #fff;
    }
    .badge-manager {
        background: #ffc700;
        color: #fff;
    }
    .badge-user {
        background: #009ef7;
        color: #fff;
    }
    .status-active {
        color: #50cd89;
    }
    .status-inactive {
        color: #f1416c;
    }
</style>

<div class="container-fluid">

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista Utenti</h3>
        </div>
        <div class="card-body">
            <table id="usersTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Nome Completo</th>
                        <th>Email</th>
                        <th>Telefono</th>
                        <th>Ruoli</th>
                        <th>Stato</th>
                        <th>Registrato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($u['full_name'] ?? $u['first_name'] . ' ' . $u['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['phone'] ?? '-'); ?></td>
                        <td>
                            <?php 
                            if ($u['roles']) {
                                $userRoles = explode(',', $u['roles']);
                                foreach ($userRoles as $role) {
                                    $badgeClass = 'badge-' . $role;
                                    echo "<span class='badge $badgeClass me-1'>" . ucfirst($role) . "</span>";
                                }
                            } else {
                                echo '<span class="text-muted">Nessun ruolo</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($u['active']): ?>
                                <i class="bi bi-check-circle status-active"></i> Attivo
                            <?php else: ?>
                                <i class="bi bi-x-circle status-inactive"></i> Disattivo
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#rolesModal<?php echo $u['id']; ?>">
                                    <i class="bi bi-shield-check"></i>
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Confermi cambio stato?')">
                                        <i class="bi bi-power"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal per gestione ruoli -->
                    <div class="modal fade" id="rolesModal<?php echo $u['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Gestisci Ruoli - <?php echo htmlspecialchars($u['username']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <h6>Ruoli Attuali:</h6>
                                    <div class="mb-3">
                                        <?php 
                                        if ($u['roles']) {
                                            $userRoles = explode(',', $u['roles']);
                                            foreach ($userRoles as $role) {
                                                echo "<form method='POST' class='d-inline me-2'>";
                                                echo "<input type='hidden' name='action' value='remove_role'>";
                                                echo "<input type='hidden' name='user_id' value='{$u['id']}'>";
                                                echo "<input type='hidden' name='role_name' value='$role'>";
                                                echo "<button type='submit' class='btn btn-sm btn-danger'>";
                                                echo ucfirst($role) . " <i class='bi bi-x'></i>";
                                                echo "</button>";
                                                echo "</form>";
                                            }
                                        } else {
                                            echo '<p class="text-muted">Nessun ruolo assegnato</p>';
                                        }
                                        ?>
                                    </div>

                                    <h6>Assegna Nuovo Ruolo:</h6>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="assign_role">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <div class="input-group">
                                            <select name="role_name" class="form-select" required>
                                                <option value="">Seleziona ruolo...</option>
                                                <?php foreach ($allRoles as $role): ?>
                                                    <option value="<?php echo $role['name']; ?>">
                                                        <?php echo $role['display_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-primary">Assegna</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/it-IT.json'
            },
            pageLength: 25,
            order: [[0, 'desc']]
        });
    });
</script>

<?php
$conn->close();
include __DIR__ . '/includes/footer.php';
?>
