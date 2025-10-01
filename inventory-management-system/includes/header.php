<header class="header">
    <div class="header-left">
        <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
        <h1 class="header-title"><?php echo APP_NAME; ?></h1>
    </div>
    <div class="header-right">
        <span class="user-info">
            <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>
            <small>(<?php echo htmlspecialchars($_SESSION['role']); ?>)</small>
        </span>
        <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
    </div>
</header>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
    document.querySelector('.main-content').classList.toggle('shifted');
}
</script>
