<?php
require_once 'config.php';
require_once 'template.php';
require_once 'auth.php';

checkMenuPermission($pdo, 'roles');

$success = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_role'])) {
        $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
        $stmt->execute([$_POST['name'], $_POST['description']]);
        $success = "Role created successfully";
    }
    
    if (isset($_POST['update_permissions'])) {
        $role_id = $_POST['role_id'];
        
        // Clear existing permissions
        $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$role_id]);
        
        // Add new permissions
        if (isset($_POST['menu_items'])) {
            $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, menu_item_id) VALUES (?, ?)");
            foreach ($_POST['menu_items'] as $menu_id) {
                $stmt->execute([$role_id, $menu_id]);
            }
        }
        $success = "Permissions updated successfully";
    }
    
    if (isset($_POST['delete_role'])) {
        $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
        $stmt->execute([$_POST['role_id']]);
        $success = "Role deleted successfully";
    }
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalRoles = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
$totalPages = ceil($totalRoles / $limit);

$roles = $pdo->query("SELECT * FROM roles ORDER BY name LIMIT $limit OFFSET $offset")->fetchAll();
$menuItems = $pdo->query("SELECT * FROM menu_items ORDER BY sort_order")->fetchAll();

// Get current permissions for each role
$permissions = [];
foreach ($roles as $role) {
    $stmt = $pdo->prepare("SELECT menu_item_id FROM role_permissions WHERE role_id = ?");
    $stmt->execute([$role['id']]);
    $permissions[$role['id']] = array_column($stmt->fetchAll(), 'menu_item_id');
}

ob_start();
?>
<div class="card">
    <h3>Create New Role</h3>
    <form method="POST">
        <div class="form-row">
            <input type="text" name="name" placeholder="Role Name" required>
            <textarea name="description" placeholder="Role Description" rows="2"></textarea>
            <button type="submit" name="add_role" class="btn">Create Role</button>
        </div>
    </form>
</div>

<div class="card">
    <h3>Manage Roles & Permissions</h3>
    <?php foreach ($roles as $role): ?>
        <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h4><?= $role['name'] ?></h4>
                    <p style="color: #666; font-size: 0.875rem;"><?= $role['description'] ?></p>
                </div>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="role_id" value="<?= $role['id'] ?>">
                    <button type="submit" name="delete_role" class="btn btn-danger" onclick="return confirm('Delete role?')">Delete</button>
                </form>
            </div>
            
            <form method="POST">
                <input type="hidden" name="role_id" value="<?= $role['id'] ?>">
                <h5 style="margin-bottom: 0.5rem;">Menu Permissions:</h5>
                <div class="checkbox-group">
                    <?php foreach ($menuItems as $item): ?>
                        <div class="checkbox-item">
                            <input type="checkbox" 
                                   name="menu_items[]" 
                                   value="<?= $item['id'] ?>" 
                                   id="role_<?= $role['id'] ?>_menu_<?= $item['id'] ?>"
                                   <?= in_array($item['id'], $permissions[$role['id']]) ? 'checked' : '' ?>>
                            <label for="role_<?= $role['id'] ?>_menu_<?= $item['id'] ?>">
                                <i class="<?= $item['icon'] ?>"></i> <?= $item['name'] ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="update_permissions" class="btn" style="margin-top: 1rem;">Update Permissions</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
<div class="card">
    <div style="display: flex; justify-content: center; gap: 0.5rem; align-items: center;">
        <span style="margin-right: 1rem;">Page <?= $page ?> of <?= $totalPages ?></span>
        
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="btn">← Previous</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="btn <?= $i == $page ? 'btn-active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn">Next →</a>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div style="text-align: center; color: #666;">Showing all <?= $totalRoles ?> roles</div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Role Management', $content, $success);
?>