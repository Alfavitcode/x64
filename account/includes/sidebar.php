<!-- Боковое меню профиля пользователя -->
<div class="profile-card profile-sidebar">
    <div class="profile-menu-header">
        <h5 class="mb-0">Личный кабинет</h5>
        <a href="logout.php" class="text-danger small" title="Выйти">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
    <ul class="profile-menu">
        <li class="profile-menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
            <a href="profile.php">
                <i class="fas fa-user"></i>
                Мой профиль
            </a>
        </li>
        <li class="profile-menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>">
            <a href="orders.php">
                <i class="fas fa-shopping-bag"></i>
                Мои заказы
            </a>
        </li>
        <li class="profile-menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
            <a href="settings.php">
                <i class="fas fa-cog"></i>
                Настройки
            </a>
        </li>
        <li class="profile-menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'telegram.php' ? 'active' : ''; ?>">
            <a href="telegram.php">
                <i class="fab fa-telegram-plane"></i>
                Привязка Telegram
            </a>
        </li>
        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Администратор'): ?>
        <li class="profile-menu-item">
            <a href="../admin/index.php">
                <i class="fas fa-tachometer-alt"></i>
                Админ-панель
            </a>
        </li>
        <?php endif; ?>
        <li class="profile-menu-item">
            <a href="logout.php" class="text-danger">
                <i class="fas fa-sign-out-alt"></i>
                Выйти
            </a>
        </li>
    </ul>
</div> 