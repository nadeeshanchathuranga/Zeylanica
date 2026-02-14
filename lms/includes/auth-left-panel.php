<?php
$progressWidth = $progressWidth ?? '0';
$customTitle = $customTitle ?? null;
$customSubtitle = $customSubtitle ?? null;
$customMessage = $customMessage ?? null;
$customIcon = $customIcon ?? null;
?>
<div class="auth-left-panel">
    <div class="book-image">
        <img src="<?= BASE_URL ?>assets/images/book-image.png" alt="Book" onerror="this.style.display='none'">
    </div>
    <div class="design2-image">
        <img src="<?= BASE_URL ?>assets/images/design2-image.png" alt="Design" onerror="this.style.display='none'">
    </div>
    <div class="brand-content">
        <?php if ($customIcon): ?>
            <div class="custom-icon"><?= $customIcon ?></div>
        <?php else: ?>
            <div class="logo">
                <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo" onerror="this.style.display='none'; this.parentNode.innerHTML='<i style=\'font-size:3rem;color:white\' class=\'fas fa-graduation-cap\'></i>'">
            </div>
        <?php endif; ?>
        <h1><?= $customTitle ?? 'Learn Anywhere, Anytime' ?></h1>
        <h2><?= $customSubtitle ?? 'Empower Your Future' ?></h2>
        <div class="welcome-section">
            <p><?= $customMessage ?? 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy.' ?></p>
        </div>
    </div>
    <div class="lecture-image">
        <img src="<?= BASE_URL ?>assets/images/lecture-image.png" alt="Lecture" onerror="this.style.display='none'">
    </div>
</div>
