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
    
    if ($action === 'create_role') {
        $name = trim($_POST['name']);
        $displayName = trim($_POST['display_name']);
        $description = trim($_POST['description']);
        
        $stmt = $conn->prepare("INSERT INTO roles (name, display_name, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $displayName, $description);
        
        if ($stmt->execute()) {
            $success = 'Ruolo creato con successo';
        } else {
            $error = 'Errore nella creazione del ruolo: ' . $stmt->error;
        }
        $stmt->close();
    }
    
    elseif ($action === 'delete_role') {
        $roleId = intval($_POST['role_id']);
        
        // Check if role is assigned to users
        $check = $conn->query("SELECT COUNT(*) as count FROM user_roles WHERE role_id = $roleId");
        $row = $check->fetch_assoc();
        
        if ($row['count'] > 0) {
            $error = 'Impossibile eliminare: ruolo assegnato a ' . $row['count'] . ' utenti';
        } else {
            $stmt = $conn->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->bind_param("i", $roleId);
            
            if ($stmt->execute()) {
                $success = 'Ruolo eliminato con successo';
            } else {
                $error = 'Errore nell\'eliminazione del ruolo';
            }
            $stmt->close();
        }
    }
}

// Get all roles with user count
$query = "
    SELECT r.*, COUNT(ur.user_id) as user_count
    FROM roles r
    LEFT JOIN user_roles ur ON r.id = ur.role_id
    GROUP BY r.id
    ORDER BY r.name
";
$result = $conn->query($query);
$roles = [];
while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}

// Page configuration
$pageTitle = "Gestione Ruoli";
include __DIR__ . '/includes/header.php';
?>

<style>
    .role-card {
        border: 1px solid #eff2f5;
        border-radius: 8px;
        padding: 20px;
        transition: all 0.2s;
    }
    .role-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }
    .role-name {
        font-size: 18px;
        font-weight: 600;
        color: #181c32;
    }
    .role-description {
        color: #7e8299;
        font-size: 14px;
        margin: 10px 0;
    }
    .user-count {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        background: #f5f8fa;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
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

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ruoli Disponibili</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($roles as $role): ?>
                        <div class="col-md-6 mb-3">
                            <div class="role-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="role-name"><?php echo htmlspecialchars($role['display_name']); ?></div>
                                    <?php if ($role['user_count'] == 0): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="delete_role">
                                        <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Confermi eliminazione?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                <div class="role-description"><?php echo htmlspecialchars($role['description']); ?></div>
                                <div class="mt-3">
                                    <span class="user-count">
                                        <i class="bi bi-people me-2"></i>
                                        <?php echo $role['user_count']; ?> utenti
                                    </span>
                                    <span class="badge bg-secondary ms-2"><?php echo $role['name']; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Crea Nuovo Ruolo</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="create_role">
                        
                        <div class="mb-3">
                            <label class="form-label">Nome Tecnico *</label>
                            <input type="text" name="name" class="form-control" required 
                                   pattern="[a-z_]+" 
                                   title="Solo lettere minuscole e underscore"
                                   placeholder="es: super_user">
                            <small class="text-muted">Solo lettere minuscole e underscore</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nome Visualizzato *</label>
                            <input type="text" name="display_name" class="form-control" required
                                   placeholder="es: Super Utente">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descrizione</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Descrivi i permessi e le responsabilità di questo ruolo..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i>Crea Ruolo
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Info</h3>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">
                        <i class="bi bi-info-circle me-2"></i>
                        I ruoli definiscono i permessi degli utenti nel sistema.
                    </p>
                    <p class="small text-muted mb-2">
                        <i class="bi bi-shield-check me-2"></i>
                        Un utente può avere più ruoli contemporaneamente.
                    </p>
                    <p class="small text-muted mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Non puoi eliminare ruoli assegnati ad utenti.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include __DIR__ . '/includes/footer.php';
?>
