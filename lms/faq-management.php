<?php
require_once 'config.php';
require_once 'template.php';
require_once 'services/FAQService.php';
require_once 'auth.php';

checkMenuPermission($pdo, 'faq');

$faqService = new FAQService($pdo);
$success = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
                $faqService->createFAQ($_POST['question'], $_POST['answer'], $_POST['display_order'], $_SESSION['user_id']);
                $success = 'FAQ created successfully';
                break;
                
            case 'update':
                $faqService->updateFAQ($_POST['id'], $_POST['question'], $_POST['answer'], $_POST['display_order'], $_POST['is_active']);
                $success = 'FAQ updated successfully';
                break;
                
            case 'delete':
                $faqService->deleteFAQ($_POST['id']);
                $success = 'FAQ deleted successfully';
                break;
                
            case 'toggle':
                $faqService->toggleFAQStatus($_POST['id']);
                $success = 'FAQ status updated';
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$faqs = $faqService->getAllFAQs(false);

ob_start();
?>
<div class="card">
    <h3>Add New FAQ</h3>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="form-row">
            <input type="text" name="question" placeholder="Question" required style="flex: 2;">
            <textarea name="answer" placeholder="Answer" required style="flex: 2; min-height: 60px;"></textarea>
            <input type="number" name="display_order" placeholder="Order" value="0" style="flex: 0.5;">
            <button type="submit" class="btn">Add FAQ</button>
        </div>
    </form>
</div>

<div class="card">
    <h3>FAQs</h3>
    <table>
        <tr>
            <th>Order</th>
            <th>Question</th>
            <th>Answer</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($faqs as $faq): ?>
        <tr>
            <td><?= $faq['display_order'] ?></td>
            <td><strong><?= htmlspecialchars($faq['question']) ?></strong></td>
            <td><?= htmlspecialchars(substr($faq['answer'], 0, 100)) ?><?= strlen($faq['answer']) > 100 ? '...' : '' ?></td>
            <td>
                <span class="status-badge <?= $faq['is_active'] ? 'status-published' : 'status-draft' ?>">
                    <?= $faq['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
            </td>
            <td><?= date('M j, Y', strtotime($faq['created_at'])) ?></td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-edit" onclick="editFAQ(<?= htmlspecialchars(json_encode($faq)) ?>)" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" class="inline-form">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="id" value="<?= $faq['id'] ?>">
                        <button type="submit" class="btn <?= $faq['is_active'] ? 'btn-archive' : 'btn-publish' ?>" title="<?= $faq['is_active'] ? 'Deactivate' : 'Activate' ?>">
                            <i class="fas fa-<?= $faq['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                        </button>
                    </form>
                    <form method="POST" class="inline-form" onsubmit="return confirm('Delete this FAQ?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $faq['id'] ?>">
                        <button type="submit" class="btn btn-danger" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; margin: 5% auto; padding: 2rem; border-radius: 12px; max-width: 600px;">
        <h3>Edit FAQ</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="editId">
            <div style="margin-bottom: 1rem;">
                <label>Question</label>
                <input type="text" name="question" id="editQuestion" required style="width: 100%; margin-top: 0.5rem;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label>Answer</label>
                <textarea name="answer" id="editAnswer" rows="4" required style="width: 100%; margin-top: 0.5rem;"></textarea>
            </div>
            <div style="margin-bottom: 1rem;">
                <label>Display Order</label>
                <input type="number" name="display_order" id="editOrder" style="width: 100%; margin-top: 0.5rem;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label>Status</label>
                <select name="is_active" id="editStatus" style="width: 100%; margin-top: 0.5rem;">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn">Update FAQ</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editFAQ(faq) {
        document.getElementById('editId').value = faq.id;
        document.getElementById('editQuestion').value = faq.question;
        document.getElementById('editAnswer').value = faq.answer;
        document.getElementById('editOrder').value = faq.display_order;
        document.getElementById('editStatus').value = faq.is_active;
        document.getElementById('editModal').style.display = 'block';
    }
    
    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target.id === 'editModal') {
            closeModal();
        }
    }
</script>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('FAQ Management', $content, $success);
?>