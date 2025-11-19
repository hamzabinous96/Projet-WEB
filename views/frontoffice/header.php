<header class="header">
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php">WeConnect</a>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php?page=courses" class="nav-link">Courses</a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=our_coaches" class="nav-link">Our Coaches</a>
                </li
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a href="index.php?page=coach_profile" class="nav-link">My Profile</a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?page=chat" class="nav-link">Messages</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="index.php?page=role_selection" class="nav-link">Get Started</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>