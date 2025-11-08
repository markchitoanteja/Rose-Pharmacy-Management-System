<?php include_once 'header.php'; ?>

<div class="content-wrapper" id="content">
    <div class="container-fluid">

        <!-- Page Title & Add Note Button -->
        <div class="page-title row mb-4">
            <div class="col-6">
                <h3>Notes & Announcements</h3>
            </div>
            <div class="col-6">
                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addNoteModal">
                    <i class="fas fa-plus mr-1"></i>
                    Add Note
                </button>
            </div>
        </div>

        <!-- Notes Table -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">All Notes</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Owner</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $notes = $db->custom_query("
                            SELECT n.*, u.full_name AS owner
                            FROM notes n
                            JOIN users u ON n.user_id = u.user_id
                            ORDER BY n.created_at DESC
                        ");
                        ?>
                        <?php if ($notes): ?>
                            <?php foreach ($notes as $note): ?>
                                <tr>
                                    <td><?= htmlspecialchars($note['title']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($note['content'])) ?></td>
                                    <td><?= htmlspecialchars($note['owner']) ?></td>
                                    <td><?= date("F j, Y g:i A", strtotime($note['created_at'])) ?></td>
                                    <td class="text-center">
                                        <!-- View Button -->
                                        <i class="fas fa-eye text-info mr-2 view_note"
                                            role="button"
                                            data-id="<?= $note['note_id'] ?>"
                                            data-title="<?= htmlspecialchars($note['title']) ?>"
                                            data-content="<?= htmlspecialchars($note['content']) ?>"
                                            data-owner="<?= htmlspecialchars($note['owner']) ?>"
                                            data-date="<?= date('F j, Y g:i A', strtotime($note['created_at'])) ?>"
                                            title="View Note"></i>

                                        <?php if ($note['user_id'] == $_SESSION['user_id']): ?>
                                            <!-- Edit Button -->
                                            <i class="fas fa-edit text-primary mr-2 edit_note"
                                                role="button"
                                                data-id="<?= $note['note_id'] ?>"
                                                data-title="<?= htmlspecialchars($note['title']) ?>"
                                                data-content="<?= htmlspecialchars($note['content']) ?>"
                                                title="Edit Note"></i>

                                            <!-- Delete Button -->
                                            <i class="fas fa-trash text-danger delete_note"
                                                role="button"
                                                data-id="<?= $note['note_id'] ?>"
                                                title="Delete Note"></i>
                                        <?php else: ?>
                                            <span class="text-muted">---</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" role="dialog" aria-labelledby="addNoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="addNoteLabel">Add New Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form action="javascript:void(0)" id="add_note_form" method="POST">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="note_title">Title</label>
                            <input type="text" class="form-control" id="note_title" name="title" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="note_created_at">Date Created</label>
                            <input type="text" class="form-control text-muted" id="note_created_at" value="<?= date('F j, Y') ?>" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="note_content">Content</label>
                        <textarea class="form-control" id="note_content" name="content" rows="4" required></textarea>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" form="add_note_form">Submit</button>
            </div>

        </div>
    </div>
</div>

<!-- View/Edit Note Modal -->
<div class="modal fade" id="viewEditNoteModal" tabindex="-1" role="dialog" aria-labelledby="viewEditNoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="viewEditNoteLabel">View Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form action="notes_process.php" id="edit_note_form" method="POST">
                    <input type="hidden" name="note_id" id="edit_note_id">
                    <div class="form-group">
                        <label for="edit_note_title">Title</label>
                        <input type="text" class="form-control" id="edit_note_title" name="title" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_note_content">Content</label>
                        <textarea class="form-control" id="edit_note_content" name="content" rows="4" readonly></textarea>
                    </div>
                    <small class="text-muted">Posted by <span id="edit_note_owner"></span> on <span id="edit_note_date"></span></small>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary d-none" id="save_note_changes" form="edit_note_form">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>