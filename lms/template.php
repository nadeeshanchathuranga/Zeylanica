<?php
function renderAdminTemplate($title, $content, $success = null) {
    global $pdo;
    
    // Check if user is logged in and has role_id
    if (!isset($_SESSION['role_id'])) {
        header('Location: index.php');
        exit;
    }
    
    // Calculate base URL - all admin files are in /src/lms/ folder
    $currentPath = $_SERVER['PHP_SELF'];
    $lmsPos = strpos($currentPath, APP_BASE_URL);
    if ($lmsPos !== false) {
        $afterLms = substr($currentPath, $lmsPos + 5);
        $depth = substr_count($afterLms, '/');
        $baseUrl = str_repeat('../', $depth);
    } else {
        $baseUrl = '';
    }
    
    // Get menu items based on user role permissions
    $role_id = $_SESSION['role_id'];
    $stmt = $pdo->prepare("
        SELECT mi.* FROM menu_items mi 
        JOIN role_permissions rp ON mi.id = rp.menu_item_id 
        WHERE rp.role_id = ? 
        ORDER BY mi.sort_order
    ");
    $stmt->execute([$role_id]);
    $menuItems = $stmt->fetchAll();
    
    // Fallback: if no menu items found, show basic menu
    if (empty($menuItems)) {
        $menuItems = [
            ['name' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-tachometer-alt'],
            ['name' => 'Person Registration', 'url' => 'persons.php', 'icon' => 'fas fa-user-plus']
        ];
    }
    
    ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; }
        .sidebar { width: 250px; height: 100vh; background: white; box-shadow: 2px 0 6px rgba(0,0,0,0.1); position: fixed; left: 0; top: 0; z-index: 1000; }
        .sidebar-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem; }
        .sidebar-header h3 { font-size: 1.2rem; font-weight: 600; }
        .nav-links { padding: 0; }
        .nav-links a { display: flex; align-items: center; gap: 0.75rem; color: #374151; text-decoration: none; padding: 1rem 1.5rem; transition: all 0.3s; border-right: 3px solid transparent; }
        .nav-links a:hover { background: #f3f4f6; color: #667eea; border-right-color: #667eea; }
        .nav-links .icon { font-size: 1.2rem; color: #667eea; width: 20px; text-align: center; }
        .main-content { margin-left: 250px; min-height: 100vh; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .header h2 { font-weight: 600; font-size: 1.5rem; }
        .user-info { display: flex; align-items: center; gap: 1rem; font-size: 0.875rem; }
        .user-info a { color: white; text-decoration: none; padding: 0.5rem 1rem; background: rgba(255,255,255,0.2); border-radius: 6px; transition: background 0.3s; }
        .user-info a:hover { background: rgba(255,255,255,0.3); }
        .content { padding: 2rem; }
        .menu-toggle { display: none; }
        .overlay { display: none; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); padding: 1.5rem; margin-bottom: 2rem; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 12px; text-align: center; }
        .stat-box h4 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .stat-box p { opacity: 0.9; font-size: 0.875rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.875rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; color: #374151; font-size: 0.875rem; }
        .form-row { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
        input, select, textarea { padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #667eea; }
        .btn { padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: transform 0.2s; text-decoration: none; display: inline-block; }
        .btn:hover { transform: translateY(-1px); }
        .btn-active { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); }
        .btn-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .success { background: #d1fae5; color: #065f46; padding: 0.875rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #10b981; }
        .checkbox-group { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1rem 0; }
        .checkbox-item { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; }
        
        /* Enhanced Course UI Styles */
        .course-header, .form-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; margin: -2rem -2rem 2rem -2rem; border-radius: 12px 12px 0 0; }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .title-section h2.page-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem; }
        .title-section .page-subtitle { opacity: 0.9; font-size: 1rem; }
        .btn-primary { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); }
        .btn-secondary { background: rgba(255,255,255,0.2); color: white; }
        .btn-filter { background: linear-gradient(135deg, #059669 0%, #047857 100%); }
        .btn-clear { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); }
        
        .filters-card { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .filters-form { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .filter-group label { font-weight: 600; color: #374151; font-size: 0.875rem; }
        .filter-select { min-width: 150px; }
        
        .empty-state { background: white; border-radius: 12px; padding: 3rem; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .empty-icon { font-size: 4rem; margin-bottom: 1rem; opacity: 0.5; }
        
        .courses-grid { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; }
        .grid-header { padding: 1.5rem; border-bottom: 1px solid #e2e8f0; }
        .table-container { overflow-x: auto; }
        .courses-table { margin: 0; }
        .courses-table th { background: #f8fafc; padding: 1rem; font-weight: 600; }
        .course-row:hover { background: #f8fafc; }
        .course-details .course-title { font-weight: 600; color: #1f2937; margin-bottom: 0.25rem; }
        .course-details .course-description { color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem; }
        .course-details .skill-level { background: #e0e7ff; color: #3730a3; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
        .category-tag { background: #f3f4f6; color: #374151; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.875rem; }
        .status-badge { padding: 0.375rem 0.75rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; }
        .status-draft { background: #fef3c7; color: #92400e; }
        .status-published { background: #d1fae5; color: #065f46; }
        .status-archived { background: #f3f4f6; color: #374151; }
        .price-info .price { font-weight: 600; color: #059669; }
        .price-info .discount { color: #dc2626; font-size: 0.875rem; }
        .date-info .date { font-weight: 500; }
        .date-info .time { color: #6b7280; font-size: 0.875rem; }
        .action-buttons { display: flex; gap: 0.5rem; }
        .btn-edit { background: #3b82f6; padding: 0.5rem; }
        .btn-view { background: #8b5cf6; padding: 0.5rem; }
        .btn-publish { background: #059669; padding: 0.5rem; }
        .btn-archive { background: #d97706; padding: 0.5rem; }
        .inline-form { display: inline; }
        
        .form-container { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .course-form .form-section { margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 1px solid #e2e8f0; }
        .course-form .form-section:last-of-type { border-bottom: none; }
        .section-header { margin-bottom: 1.5rem; }
        .section-header h3 { color: #1f2937; font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; }
        .section-header p { color: #6b7280; font-size: 0.875rem; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label { display: block; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; }
        .form-group small { color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem; display: block; }
        .input-with-icon { position: relative; }
        .input-icon { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600; }
        .input-with-icon input { padding-left: 2rem; }
        .file-input { border: 2px dashed #d1d5db; background: #f9fafb; }
        
        .instructor-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
        .instructor-card { position: relative; }
        .instructor-card input[type="checkbox"] { position: absolute; opacity: 0; }
        .instructor-label { display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.3s; }
        .instructor-card input:checked + .instructor-label { border-color: #4f46e5; background: #f0f9ff; }
        .instructor-avatar { width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; }
        .instructor-info .instructor-name { font-weight: 600; color: #1f2937; }
        .instructor-info .instructor-role { color: #6b7280; font-size: 0.875rem; }
        
        .form-actions { display: flex; gap: 1rem; justify-content: flex-end; padding-top: 2rem; }
        .btn-large { padding: 1rem 2rem; font-size: 1rem; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-danger { background: #fee2e2; color: #991b1b; border-left: 4px solid #dc2626; }
        
        .thumbnail-preview { background: white; border-radius: 12px; padding: 2rem; margin-top: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .thumbnail-container { text-align: center; }
        .current-thumbnail { max-width: 300px; height: auto; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        
        /* Course View & Lesson Cards */
        .course-view-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; margin: -2rem -2rem 2rem -2rem; border-radius: 12px 12px 0 0; }
        .back-link { color: rgba(255,255,255,0.9); text-decoration: none; display: inline-block; margin-bottom: 1rem; }
        .back-link:hover { color: white; }
        .course-meta { display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap; }
        .meta-item { display: flex; align-items: center; gap: 0.5rem; opacity: 0.95; }
        
        .lessons-container { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .lessons-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #e2e8f0; }
        .view-toggle { display: flex; gap: 0.5rem; }
        .view-btn { background: #f3f4f6; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; }
        .view-btn.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        
        .lessons-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
        .lessons-list { display: flex; flex-direction: column; gap: 1rem; }
        
        .lesson-card { background: white; border: 2px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: all 0.3s; position: relative; }
        .lesson-card:hover { border-color: #667eea; box-shadow: 0 8px 16px rgba(102, 126, 234, 0.15); transform: translateY(-2px); }
        .lesson-order { position: absolute; top: 1rem; left: 1rem; background: rgba(0,0,0,0.7); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-weight: 600; z-index: 10; }
        .lesson-thumbnail { position: relative; width: 100%; height: 180px; background: #000; }
        .lesson-thumbnail iframe { width: 100%; height: 100%; }
        .lesson-duration { position: absolute; bottom: 0.5rem; right: 0.5rem; background: rgba(0,0,0,0.8); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; }
        .lesson-content { padding: 1.5rem; }
        .lesson-title { font-size: 1.125rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem; }
        .lesson-meta { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
        .meta-badge { background: #f3f4f6; color: #374151; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.875rem; }
        .meta-badge.visible { background: #d1fae5; color: #065f46; }
        .meta-badge.hidden { background: #fee2e2; color: #991b1b; }
        .lesson-stats { display: flex; gap: 1.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; }
        .stat-item { display: flex; align-items: center; gap: 0.5rem; color: #6b7280; font-size: 0.875rem; }
        .lesson-actions { display: flex; gap: 0.5rem; padding: 1rem; background: #f9fafb; border-top: 1px solid #e2e8f0; }
        .action-btn { flex: 1; padding: 0.75rem; border: none; border-radius: 6px; cursor: pointer; color: white; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .action-btn.edit { background: #3b82f6; }
        .action-btn.edit:hover { background: #2563eb; }
        .action-btn.view { background: #8b5cf6; }
        .action-btn.view:hover { background: #7c3aed; }
        .action-btn.delete { background: #ef4444; }
        .action-btn.delete:hover { background: #dc2626; }
        .delete-form { flex: 1; margin: 0; }
        
        .lesson-card { background: white; border: 2px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: all 0.3s; position: relative; }
        .lesson-card:hover { border-color: #667eea; box-shadow: 0 8px 16px rgba(102, 126, 234, 0.15); transform: translateY(-2px); }
        .lesson-order { position: absolute; top: 1rem; left: 1rem; background: rgba(0,0,0,0.7); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-weight: 600; z-index: 10; }
        .lesson-thumbnail { position: relative; width: 100%; height: 180px; background: #000; }
        .lesson-thumbnail iframe { width: 100%; height: 100%; }
        .lesson-duration { position: absolute; bottom: 0.5rem; right: 0.5rem; background: rgba(0,0,0,0.8); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; }
        .lesson-content { padding: 1.5rem; }
        .lesson-title { font-size: 1.125rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem; }
        .lesson-meta { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
        .meta-badge { background: #f3f4f6; color: #374151; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.875rem; }
        .meta-badge.visible { background: #d1fae5; color: #065f46; }
        .meta-badge.hidden { background: #fee2e2; color: #991b1b; }
        .lesson-stats { display: flex; gap: 1.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; }
        .stat-item { display: flex; align-items: center; gap: 0.5rem; color: #6b7280; font-size: 0.875rem; }
        .lesson-actions { display: flex; gap: 0.5rem; padding: 1rem; background: #f9fafb; border-top: 1px solid #e2e8f0; }
        .action-btn { flex: 1; padding: 0.75rem; border: none; border-radius: 6px; cursor: pointer; color: white; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .action-btn.edit { background: #3b82f6; }
        .action-btn.edit:hover { background: #2563eb; }
        .action-btn.view { background: #8b5cf6; }
        .action-btn.view:hover { background: #7c3aed; }
        .action-btn.delete { background: #ef4444; }
        .action-btn.delete:hover { background: #dc2626; }
        .delete-form { flex: 1; margin: 0; }
        @media (max-width: 768px) {
            .sidebar { left: -250px; transition: left 0.3s; }
            .sidebar.open { left: 0; }
            .main-content { margin-left: 0; }
            .menu-toggle { display: block; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }
            .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; }
            .overlay.show { display: block; }
            .content { padding: 1rem; }
            .form-row { flex-direction: column; }
            .stats { grid-template-columns: 1fr; }
            .checkbox-group { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="overlay" onclick="closeSidebar()"></div>
    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Admin Panel</h3>
        </div>
        <div class="nav-links">
            <?php foreach ($menuItems as $item): ?>
                <a href="<?= $baseUrl . $item['url'] ?>">
                    <span class="icon"><i class="<?= $item['icon'] ?>"></i></span>
                    <span><?= $item['name'] ?></span>
                </a>
            <?php endforeach; ?>
            <a href="<?= $baseUrl ?>logout.php">
                <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Logout</span>
            </a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="menu-toggle" onclick="openSidebar()">☰</button>
                <h2><?= $title ?></h2>
            </div>
            <div class="user-info">
                <span><?= $_SESSION['email'] ?> (<?= isset($_SESSION['role_name']) ? $_SESSION['role_name'] : 'User' ?>)</span>
                <a href="<?= $baseUrl ?>logout.php">Logout</a>
            </div>
        </div>
        
        <div class="content">
            <?php if ($success): ?>
                <div class="success"><?= $success ?></div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </div>
    
    <script>
        function openSidebar() {
            if (window.innerWidth <= 768) {
                document.getElementById('sidebar').classList.add('open');
                document.querySelector('.overlay').classList.add('show');
            }
        }
        
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.querySelector('.overlay').classList.remove('show');
        }
    </script>
</body>
</html>
<?php
    return ob_get_clean();
}
?>