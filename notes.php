<?php include_once 'header.php'; ?>

<style>
    /* Ensure truncation works properly */
    .notes-table td.truncate-cell {
        max-width: 250px;
        /* Adjust per column or table width */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }

    /* Make sure the table cells don’t wrap or overlap */
    .notes-table td {
        vertical-align: middle;
    }

    .note-content {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 5px;
    }

    .note-content::-webkit-scrollbar {
        width: 6px;
    }

    .note-content::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 10px;
    }
</style>

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
                <table class="table table-bordered datatable notes-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Title</th>
                            <th style="width: 35%;">Content</th>
                            <th style="width: 15%;">Owner</th>
                            <th style="width: 20%;">Date</th>
                            <th style="width: 10%;" class="text-center">Actions</th>
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
                                    <td class="truncate-cell" title="<?= htmlspecialchars($note['title']) ?>">
                                        <?= htmlspecialchars($note['title']) ?>
                                    </td>
                                    <td class="truncate-cell" title="<?= htmlspecialchars($note['content']) ?>">
                                        <?= htmlspecialchars($note['content']) ?>
                                    </td>
                                    <td class="truncate-cell" title="<?= htmlspecialchars($note['owner']) ?>">
                                        <?= htmlspecialchars($note['owner']) ?>
                                    </td>
                                    <td class="truncate-cell" title="<?= date('F j, Y g:i A', strtotime($note['created_at'])) ?>">
                                        <?= date("F j, Y g:i A", strtotime($note['created_at'])) ?>
                                    </td>
                                    <td class="text-center">
                                        <i class="fas fa-eye text-info mr-1 view_note" role="button" data-id="<?= $note['note_id'] ?>" title="View Note"></i>

                                        <?php if ($note['user_id'] == $_SESSION['user_id']): ?>
                                            <i class="fas fa-pencil-alt text-primary mr-1 edit_note" role="button" data-id="<?= $note['note_id'] ?>" title="Edit Note"></i>
                                            <i class="fas fa-trash-alt text-danger delete_note" role="button" data-id="<?= $note['note_id'] ?>" title="Delete Note"></i>
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
                <form action="javascript:void(0)" id="add_note_form">
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

<!-- Edit Note Modal -->
<div class="modal fade" id="editNoteModal" tabindex="-1" role="dialog" aria-labelledby="viewEditNoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="viewEditNoteLabel">Edit Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form action="javascript:void(0)" id="edit_note_form">
                    <input type="hidden" name="note_id" id="edit_note_id">
                    <div class="form-group">
                        <label for="edit_note_title">Title</label>
                        <input type="text" class="form-control" id="edit_note_title" name="title">
                    </div>
                    <div class="form-group">
                        <label for="edit_note_content">Content</label>
                        <textarea class="form-control" id="edit_note_content" name="content" rows="4"></textarea>
                    </div>
                    <small class="text-muted">Posted by <span id="edit_note_owner"></span> on <span id="edit_note_date"></span></small>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="save_note_changes" form="edit_note_form">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- View Note Modal -->
<div class="modal fade" id="viewNoteModal" tabindex="-1" role="dialog" aria-labelledby="viewNoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="viewNoteLabel">View Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body bg-light">
                <div id="viewNoteContent" class="p-3 border rounded bg-white shadow-sm">

                    <!-- Note Header -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 id="view_note_title" class="mb-0 font-weight-bold text-primary"></h5>
                        <small class="text-muted" id="view_note_date"></small>
                    </div>

                    <!-- Note Content -->
                    <div class="note-content mb-4" style="white-space: pre-line;">
                        <p id="view_note_content" class="mb-0 text-dark" style="font-size: 1rem; line-height: 1.6;"></p>
                    </div>

                    <!-- Owner Info -->
                    <div class="border-top pt-2 text-right">
                        <small class="text-muted">
                            Posted by: <strong id="view_note_owner" class="text-secondary"></strong>
                        </small>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>