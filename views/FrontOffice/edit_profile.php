<?php include 'layouts/header.php'; ?>

    <div class="container">
        <div class="profile-header">
            <h2>Edit Profile</h2>
            <a href="index.php?action=profile" class="btn btn-secondary">Back to Profile</a>
        </div>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="auth-form">
            <form method="POST" action="index.php?action=edit_profile" enctype="multipart/form-data" id="editProfileForm" novalidate>
                <div class="form-group text-center">
                    <div class="profile-picture-upload">
                        <?php if($this->user->profile_picture && file_exists($this->user->profile_picture)): ?>
                            <img src="<?php echo $this->user->profile_picture; ?>" alt="Profile Picture" id="preview-image">
                        <?php else: ?>
                            <div class="avatar-placeholder-large" id="preview-placeholder">
                                <?php echo strtoupper(substr($this->user->first_name, 0, 1) . substr($this->user->last_name, 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <label for="profile_picture" class="upload-label">
                            Change Picture
                        </label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display:none;">
                        <span class="validation-message" id="profile_picture_message"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label>First Name:</label>
                    <input type="text" name="first_name" id="edit_first_name" value="<?php echo $this->user->first_name; ?>" autocomplete="given-name">
                    <span class="validation-message" id="edit_first_name_message"></span>
                </div>

                <div class="form-group">
                    <label>Last Name:</label>
                    <input type="text" name="last_name" id="edit_last_name" value="<?php echo $this->user->last_name; ?>" autocomplete="family-name">
                    <span class="validation-message" id="edit_last_name_message"></span>
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="text" name="email" id="edit_email" value="<?php echo $this->user->email; ?>" autocomplete="email">
                    <span class="validation-message" id="edit_email_message"></span>
                </div>

                <div class="form-group">
                    <label>Phone:</label>
                    <input type="text" name="phone" id="edit_phone" value="<?php echo $this->user->phone; ?>" autocomplete="tel">
                    <span class="validation-message" id="edit_phone_message"></span>
                </div>

                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>

<?php include 'layouts/footer.php'; ?>