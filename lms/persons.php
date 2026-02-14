<?php
require_once 'config.php';
require_once 'template.php';
require_once 'auth.php';

checkMenuPermission($pdo, 'persons');

$success = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_person'])) {
        $stmt = $pdo->prepare("INSERT INTO persons (name, email, phone) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['email'], $_POST['phone']]);
        $success = "Person registered successfully";
    }
    
    if (isset($_POST['delete_person'])) {
        $stmt = $pdo->prepare("DELETE FROM persons WHERE id = ?");
        $stmt->execute([$_POST['person_id']]);
        $success = "Person deleted successfully";
    }
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalPersons = $pdo->query("SELECT COUNT(*) FROM persons")->fetchColumn();
$totalPages = ceil($totalPersons / $limit);

$persons = $pdo->query("SELECT * FROM persons ORDER BY created_at DESC LIMIT $limit OFFSET $offset")->fetchAll();

ob_start();
?>
<div class="card">
    <h3>Register New Person</h3>
    <form method="POST">
        <div class="form-row">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email address" required>
            <input type="tel" name="phone" placeholder="Phone number">
            <button type="submit" name="add_person" class="btn">Register</button>
        </div>
    </form>
</div>

<div class="card">
    <h3>Registered Persons</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Registered</th>
            <th>Action</th>
        </tr>
        <?php foreach ($persons as $person): ?>
        <tr>
            <td><?= $person['id'] ?></td>
            <td><?= $person['name'] ?></td>
            <td><?= $person['email'] ?></td>
            <td><?= $person['phone'] ?: '-' ?></td>
            <td><?= date('M j, Y', strtotime($person['created_at'])) ?></td>
            <td>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="person_id" value="<?= $person['id'] ?>">
                    <button type="submit" name="delete_person" class="btn btn-danger" onclick="return confirm('Delete person?')">Delete</button>
                </form>
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
    <div style="text-align: center; color: #666;">Showing all <?= $totalPersons ?> persons</div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Person Registration', $content, $success);
?>