        <!-- About Us Modal -->
        <div class="modal fade" id="aboutUsModal" tabindex="-1" role="dialog" aria-labelledby="aboutUsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="aboutUsModalLabel">About Us</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <!-- Company Header -->
                        <div class="text-center mb-4">
                            <h4 class="font-weight-bold text-primary mb-1">Rose Pharmacy Incorporated</h4>
                            <p class="text-muted mb-0">Your trusted partner in health and wellness.</p>
                        </div>

                        <hr>

                        <!-- About Section -->
                        <div class="mb-4">
                            <p class="mb-0">
                                For decades, <strong>Rose Pharmacy</strong> has been committed to providing
                                high-quality medicines, essential healthcare products, and personalized service.
                                We strive to promote healthier communities through compassion, care,
                                and professionalism.
                            </p>
                        </div>

                        <!-- Mission Section -->
                        <div class="mb-4">
                            <h6 class="text-uppercase font-weight-bold text-secondary">Our Mission</h6>
                            <p class="mb-0">
                                To make healthcare more accessible, reliable, and affordable —
                                while ensuring every customer feels valued and cared for.
                            </p>
                        </div>

                        <!-- App Info Card -->
                        <div class="bg-light border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="font-weight-bold text-dark">App Version</span>
                                <span class="text-muted"><?= env('APP_VERSION') ?></span>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="text-center">
                            <small class="text-muted d-block">&copy; 2025 Rose Pharmacy Incorporated</small>
                            <small class="text-muted">All rights reserved.</small>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Account Settings Modal -->
        <div class="modal fade" id="accountSettingsModal" tabindex="-1" role="dialog" aria-labelledby="accountSettingsLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">

                    <!-- Header -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="accountSettingsLabel">Account Settings</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">
                        <?php
                        $user = $db->select_one("users", "user_id = ?", [$_SESSION['user_id']]);
                        $role = $db->select_one("roles", "role_id = ?", [$user['role_id']]);
                        ?>

                        <form action="javascript:void(0)" id="account_settings_form">
                            <!-- Profile Section -->
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="account_settings_full_name">Full Name</label>
                                    <input type="text" class="form-control" id="account_settings_full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="account_settings_username">Username</label>
                                    <input type="text" class="form-control" id="account_settings_username" value="<?= htmlspecialchars($user['username']) ?>" required>
                                </div>
                            </div>

                            <!-- Security Section -->
                            <div class="form-group">
                                <label for="account_settings_old_password">Old Password</label>
                                <input type="password" class="form-control" id="account_settings_old_password">
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="account_settings_new_password">New Password <small class="text-muted">(leave blank if unchanged)</small></label>
                                        <input type="password" class="form-control" id="account_settings_new_password">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="account_settings_confirm_password">Confirm New Password</label>
                                        <input type="password" class="form-control" id="account_settings_confirm_password">
                                    </div>
                                </div>
                            </div>

                            <!-- Account Details -->
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="role">Role</label>
                                    <input type="text" class="form-control font-weight-bold" id="role" value="<?= htmlspecialchars($role['role_name']) ?>" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="createdAt">Member Since</label>
                                    <input type="text" class="form-control text-muted" id="createdAt" value="<?= date('F j, Y', strtotime($user['created_at'])) ?>" readonly>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="account_settings_submit" form="account_settings_form">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            &copy; <?= date("Y"); ?> Rose Pharmacy Incorporated. All rights reserved.
        </footer>
        </div>

        <script>
            const base_url = <?= json_encode(base_url()) ?>;
            const server_url = <?= json_encode(base_url() . 'server') ?>;
            const app_validity = <?= json_encode(env('APP_VALIDITY')) ?>;
            const app_debug = <?= json_encode(env('APP_DEBUG')) ?>;
            const notification = <?= isset($_SESSION['notification']) ? json_encode($_SESSION['notification']) : 'null'; ?>;
        </script>

        <script src="dist/plugins/jquery/jquery.min.js?ver=<?= env('APP_VERSION') ?>"></script>
        <script src="dist/plugins/bootstrap/js/bootstrap.bundle.min.js?ver=<?= env('APP_VERSION') ?>"></script>
        <script src="dist/plugins/sweetalert/js/sweetalert.min.js?ver=<?= env('APP_VERSION') ?>"></script>
        <!-- DataTables JS -->
        <script src="dist/plugins/datatables/js/jquery.dataTables.min.js?ver=<?= env('APP_VERSION') ?>"></script>
        <script src="dist/plugins/datatables/js/dataTables.bootstrap4.min.js?ver=<?= env('APP_VERSION') ?>"></script>
        <script src="dist/main/js/script.js?ver=<?= env('APP_VERSION') ?>"></script>

        </body>

        </html>

        <?php unset($_SESSION['notification']); ?>