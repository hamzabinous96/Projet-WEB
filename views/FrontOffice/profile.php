<?php include 'layouts/header.php'; ?>

    <div class="container">
        <div class="profile-header">
            <h2>My Profile</h2>
            <div>
                <a href="index.php?action=edit_profile" class="btn btn-primary">Edit Profile</a>
            </div>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="profile-card">
            <div class="profile-avatar">
                <?php if($this->user->profile_picture && file_exists($this->user->profile_picture)): ?>
                    <img src="<?php echo $this->user->profile_picture; ?>" alt="Profile Picture">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?php echo strtoupper(substr($this->user->first_name, 0, 1) . substr($this->user->last_name, 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="profile-info">
                <h3><?php echo $this->user->first_name . ' ' . $this->user->last_name; ?></h3>
                <p><strong>User Type:</strong> <?php echo ucfirst($this->user->user_type); ?></p>
                <p><strong>Email:</strong> <?php echo $this->user->email; ?></p>
                <p><strong>Phone:</strong> <?php echo $this->user->phone ?: 'Not provided'; ?></p>
                <p><strong>Status:</strong>
                    <span class="status-badge status-<?php echo $this->user->status; ?>">
                    <?php echo ucfirst($this->user->status); ?>
                </span>
                </p>
                <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($this->user->created_at)); ?></p>

                <div class="profile-actions">
                    <a href="index.php?action=change_password" class="btn btn-warning">Change Password</a>
                </div>
            </div>
        </div>
    </div>

<?php include 'layouts/footer.php'; ?>