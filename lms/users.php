<?php
require_once 'config.php';
require_once 'template.php';
require_once 'auth.php';

checkMenuPermission($pdo, 'users');

$success = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role_id = $_POST['role_id'];
        
        $stmt = $pdo->prepare("INSERT INTO users (email, password, role_id) VALUES (?, ?, ?)");
        $stmt->execute([$email, $password, $role_id]);
        $success = "User added successfully";
    }
    
    if (isset($_POST['delete_user'])) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_POST['user_id']]);
        $success = "User deleted successfully";
    }
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPages = ceil($totalUsers / $limit);

$users = $pdo->query("
    SELECT u.*, r.name as role_name 
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    ORDER BY u.created_at DESC
    LIMIT $limit OFFSET $offset
")->fetchAll();

$roles = $pdo->query("SELECT * FROM roles ORDER BY name")->fetchAll();

ob_start();
?>
<div class="card">
    <h3>Add New User</h3>
    <form method="POST">
        <div class="form-row">
            <input type="email" name="email" placeholder="Email address" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role_id" required>
                <option value="">Select Role</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>"><?= $role['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="add_user" class="btn">Add User</button>
        </div>
    </form>
</div>

<div class="card">
    <h3>Users</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created</th>
            <th>Action</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= $user['email'] ?></td>
            <td><?= $user['role_name'] ?></td>
            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
            <td>
                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <button type="submit" name="delete_user" class="btn btn-danger" onclick="return confirm('Delete user?')">Delete</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
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
    <div style="text-align: center; color: #666;">Showing all <?= $totalUsers ?> users</div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('User Management', $content, $success);
?>