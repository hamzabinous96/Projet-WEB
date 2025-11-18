<?php include 'layouts/header.php'; ?>

    <div class="container">
        <div class="profile-header">
            <h2>Change Password</h2>
            <a href="index.php?action=profile" class="btn btn-secondary">Back to Profile</a>
        </div>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="auth-form">
            <form method="POST" action="index.php?action=change_password" id="changePasswordForm" novalidate>
                <div class="form-group">
                    <label>Current Password:</label>
                    <input type="password" name="current_password" id="current_password" autocomplete="current-password">
                    <span class="validation-message" id="current_password_message"></span>
                </div>

                <div class="form-group">
                    <label>New Password:</label>
                    <input type="password" name="new_password" id="new_password" autocomplete="new-password">
                    <span class="validation-message" id="new_password_message"></span>
                    <small class="form-text">Minimum 6 characters</small>
                </div>

                <div class="form-group">
                    <label>Confirm New Password:</label>
                    <input type="password" name="confirm_password" id="change_confirm_password" autocomplete="new-password">
                    <span class="validation-message" id="change_confirm_password_message"></span>
                </div>

                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>

<?php include 'layouts/footer.php'; ?>