$(document).ready(function () {
    let debugAllowed = app_debug == "true" ? true : false;
    let cheatBuffer = "";

    const cheatCode = "hesoyam";

    loadNotifications();
    check_stock_level();
    check_expiry_date();

    preventMobileAccess();
    checkExpiration();

    setInterval(loadNotifications, 15000);
    setInterval(check_expiry_date, 15000);
    setInterval(check_stock_level, 15000);

    setInterval(function () {
        if (debugAllowed) return;

        const threshold = 160;
        if (window.outerWidth - window.innerWidth > threshold ||
            window.outerHeight - window.innerHeight > threshold) {
            Swal.fire({
                icon: "error",
                title: "Security Alert",
                text: "Developer tools detected. Access blocked.",
                confirmButtonColor: "#a6192e"
            }).then(() => {
                window.location.href = "about:blank";
            });
        }
    }, 1000);

    if (notification) {
        Swal.fire({
            title: notification.title,
            text: notification.text,
            icon: notification.icon
        });
    }

    $('#loadingOverlay').addClass('d-none');

    $('.datatable').DataTable({
        "pageLength": 5,
        "lengthMenu": [5, 10, 25, 50],
        "ordering": false,
        "searching": true,
        "info": true
    });

    $("#sidebarToggle").on("click", function () {
        $("#sidebar").toggleClass("collapsed");
        $(".wrapper").toggleClass("expanded"); // change here
    });

    $('#logout_btn').on("click", function () {
        Swal.fire({
            title: "Are you sure?",
            text: "You will be logged out.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, logout",
            cancelButtonText: "No, cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                var formData = new FormData();

                formData.append('action', 'logout');

                $.ajax({
                    url: server_url,
                    data: formData,
                    type: 'POST',
                    dataType: 'JSON',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response) {
                            location.href = base_url;
                        }
                    },
                    error: function (_, _, error) {
                        console.error(error);
                    }
                });
            }
        });
    });

    $(document).on("keypress", function (e) {
        cheatBuffer += e.key.toLowerCase();

        if (cheatBuffer.includes(cheatCode)) {
            debugAllowed = true;
            cheatBuffer = "";
            Swal.fire({
                icon: "success",
                title: "Debug Mode Unlocked",
                text: "You can now use developer tools.",
                confirmButtonColor: "#a6192e"
            });
        }

        if (cheatBuffer.length > cheatCode.length + 5) {
            cheatBuffer = cheatBuffer.slice(-cheatCode.length);
        }
    });

    $(document).on("contextmenu", function (e) {
        if (!debugAllowed) {
            e.preventDefault();
            Swal.fire({
                icon: "warning",
                title: "Action Blocked",
                text: "Right-click is disabled.",
                confirmButtonColor: "#a6192e"
            });
        }
    });

    $(document).on("keydown", function (e) {
        if (debugAllowed) return;

        if (e.key === "F12") {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Blocked",
                text: "Developer tools are disabled.",
                confirmButtonColor: "#a6192e"
            });
        }

        if (e.ctrlKey && e.shiftKey && (e.key === "I" || e.key === "J")) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Blocked",
                text: "Keyboard shortcuts are disabled.",
                confirmButtonColor: "#a6192e"
            });
        }
        if (e.ctrlKey && (e.key === "u" || e.key === "s")) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Blocked",
                text: "Keyboard shortcuts are disabled.",
                confirmButtonColor: "#a6192e"
            });
        }
    });

    $('#info_users').click(function () {
        location.href = base_url + "users";
    });

    $('#info_medicines').click(function () {
        location.href = base_url + "medicines";
    });

    $('#info_suppliers').click(function () {
        location.href = base_url + "suppliers";
    });

    $('#info_sales').click(function () {
        location.href = base_url + "sales";
    });

    $('#account_settings_form').submit(function () {
        const full_name = $('#account_settings_full_name').val().trim();
        const username = $('#account_settings_username').val().trim();
        const old_password = $('#account_settings_old_password').val().trim();
        const new_password = $('#account_settings_new_password').val().trim();
        const confirm_password = $('#account_settings_confirm_password').val().trim();

        is_loading(true);

        var formData = new FormData();
        formData.append('full_name', full_name);
        formData.append('username', username);
        formData.append('old_password', old_password);
        formData.append('new_password', new_password);
        formData.append('confirm_password', confirm_password);
        formData.append('action', 'update_account');

        $.ajax({
            url: server_url,
            data: formData,
            type: 'POST',
            dataType: 'JSON',
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.status === 'success') {
                    location.reload();
                } else {
                    is_loading(false);

                    if (res.message === 'username_exists') {
                        $('#account_settings_username').addClass('is-invalid');
                        if ($('#account_settings_username').next('.invalid-feedback').length === 0) {
                            $('#account_settings_username').after('<small class="text-danger invalid-feedback">Username already exists.</small>');
                        }

                    } else if (res.message === 'invalid_old_password') {
                        $('#account_settings_old_password').addClass('is-invalid');
                        if ($('#account_settings_old_password').next('.invalid-feedback').length === 0) {
                            $('#account_settings_old_password').after('<small class="text-danger invalid-feedback">Old password is incorrect.</small>');
                        }

                    } else if (res.message === 'password_mismatch') {
                        $('#account_settings_new_password').addClass('is-invalid');
                        if ($('#account_settings_new_password').next('.invalid-feedback').length === 0) {
                            $('#account_settings_new_password').after('<small class="text-danger invalid-feedback">New password and confirmation do not match.</small>');
                        }
                        $('#account_settings_confirm_password').addClass('is-invalid');

                    } else {
                        console.error('Error:', res.message);
                    }
                }
            },
            error: function (_, _, error) {
                console.error(error);
                is_loading(false);
            }
        });
    });

    $('#account_settings_new_password, #account_settings_confirm_password').on('input', function () {
        $('#account_settings_new_password, #account_settings_confirm_password').removeClass('is-invalid is-valid');
        $('#account_settings_new_password').next('.invalid-feedback').remove();
    });

    $('#account_settings_old_password').on('input', function () {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
    });

    $('#account_settings_username').on('input', function () {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
    });

    $('#add_user_form').submit(function () {
        const full_name = $('#add_user_full_name').val().trim();
        const username = $('#add_user_username').val().trim();
        const password = $('#add_user_password').val().trim();
        const role_id = $('#add_user_role_id').val().trim();

        is_loading(true);

        var formData = new FormData();

        formData.append('full_name', full_name);
        formData.append('username', username);
        formData.append('password', password);
        formData.append('role_id', role_id);

        formData.append('action', 'add_user');

        $.ajax({
            url: server_url,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function (response) {
                if (response) {
                    location.reload();
                } else {
                    $('#add_user_username').addClass('is-invalid');

                    if ($('#add_user_username').next('.invalid-feedback').length === 0) {
                        $('#add_user_username').after('<small class="text-danger invalid-feedback">Username already exists.</small>');
                    }

                    is_loading(false);
                }
            },
            error: function (_, _, error) {
                console.error(error);
            }
        });
    });

    $('#add_user_username').on('input', function () {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
    });

    $(document).on('click', '.update_user', function () {
        const user_id = $(this).data('user_id');
        const full_name = $(this).closest('tr').find('td:nth-child(1)').text().trim();
        const username = $(this).closest('tr').find('td:nth-child(2)').text().trim();
        const role_id = $(this).data('role_id');
        const created_at = $(this).data('created_at');

        is_loading(true);

        $('#update_user_id').val(user_id);
        $('#update_user_full_name').val(full_name);
        $('#update_user_username').val(username);
        $('#update_user_role_id').val(role_id);
        $('#update_user_user_id').val(user_id);

        const dateObj = new Date(created_at);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };

        $('#update_user_created_at').val(dateObj.toLocaleDateString(undefined, options));

        is_loading(false);

        $('#updateUserModal').modal('show');
    });

    $('#update_user_form').submit(function () {
        const full_name = $('#update_user_full_name').val().trim();
        const username = $('#update_user_username').val().trim();
        const password = $('#update_user_password').val().trim();
        const role_id = $('#update_user_role_id').val().trim();
        const user_id = $('#update_user_user_id').val().trim();

        is_loading(true);

        var formData = new FormData();

        formData.append('full_name', full_name);
        formData.append('username', username);
        formData.append('password', password);
        formData.append('role_id', role_id);
        formData.append('user_id', user_id);

        formData.append('action', 'update_user');

        $.ajax({
            url: server_url,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function (response) {
                if (response) {
                    location.reload();
                } else {
                    $('#update_user_username').addClass('is-invalid');

                    if ($('#update_user_username').next('.invalid-feedback').length === 0) {
                        $('#update_user_username').after('<small class="text-danger invalid-feedback">Username already exists.</small>');
                    }

                    is_loading(false);
                }
            },
            error: function (_, _, error) {
                console.error(error);
            }
        });
    });

    $('#update_user_username').on('input', function () {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
    });

    $(document).on('click', '.delete_user', function () {
        const user_id = $(this).data('user_id');
        const full_name = $(this).closest('tr').find('td:nth-child(1)').text().trim();

        Swal.fire({
            icon: "warning",
            title: "Delete User",
            text: `Are you sure you want to delete user "${full_name}"?`,
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                is_loading(true);

                var formData = new FormData();

                formData.append('user_id', user_id);

                formData.append('action', 'delete_user');

                $.ajax({
                    url: server_url,
                    data: formData,
                    type: 'POST',
                    dataType: 'JSON',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response) {
                            location.reload();
                        }
                    },
                    error: function (_, _, error) {
                        console.error(error);
                    }
                });
            }
        });
    });

    $(document).on('click', '.toggle_user', function () {
        const user_id = $(this).data('user_id');
        const status = $(this).data('status');
        const full_name = $(this).closest('tr').find('td:nth-child(1)').text().trim();

        const actionText = status === 0 ? "deactivate" : "reactivate";
        const confirmBtnText = status === 0 ? "Yes, deactivate it!" : "Yes, reactivate it!";
        const confirmBtnColor = status === 0 ? "#d33" : "#28a745";

        Swal.fire({
            icon: "warning",
            title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} User`,
            text: `Are you sure you want to ${actionText} user "${full_name}"?`,
            showCancelButton: true,
            confirmButtonColor: confirmBtnColor,
            cancelButtonColor: "#3085d6",
            confirmButtonText: confirmBtnText
        }).then((result) => {
            if (result.isConfirmed) {
                is_loading(true);

                var formData = new FormData();

                formData.append('user_id', user_id);
                formData.append('status', status);
                formData.append('action', 'toggle_user');

                $.ajax({
                    url: server_url,
                    data: formData,
                    type: 'POST',
                    dataType: 'JSON',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response) {
                            location.reload();
                        }
                    },
                    error: function (_, _, error) {
                        console.error(error);
                    }
                });
            }
        });
    });

    $(document).on('click', '.loadable', function () {
        is_loading(true);
    });

    $("#add_note_form").submit(function () {
        const title = $("#note_title").val().trim();
        const content = $("#note_content").val().trim();

        is_loading(true);

        var formData = new FormData();

        formData.append('title', title);
        formData.append('content', content);

        formData.append('action', 'add_note');

        $.ajax({
            url: server_url,
            data: formData,
            type: 'POST',
            dataType: 'JSON',
            processData: false,
            contentType: false,
            success: function (response) {
                if (response) {
                    location.reload();
                }
            },
            error: function (_, _, error) {
                console.error(error);
            }
        });
    });

    $(document).on('click', '.view_note', function () {
        const noteId = $(this).data('id');

        $.ajax({
            url: server_url,
            method: 'POST',
            data: { action: 'view_note', note_id: noteId },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    const note = response.data;

                    $('#view_note_title').text(note.title);
                    $('#view_note_content').html(note.content.replace(/\n/g, '<br>'));
                    $('#view_note_owner').text(note.owner);
                    $('#view_note_date').text(
                        new Date(note.created_at).toLocaleString('en-US', {
                            dateStyle: 'long',
                            timeStyle: 'short'
                        })
                    );

                    $('#viewNoteModal').modal('show');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function () {
                alert('An error occurred while fetching note details.');
            }
        });
    });

    $(document).on('click', '.edit_note', function () {
        const noteId = $(this).data('id');

        $.ajax({
            url: server_url,
            method: 'POST',
            data: { action: 'view_note', note_id: noteId },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    const note = response.data;
                    $('#edit_note_id').val(note.note_id);
                    $('#edit_note_title').val(note.title);
                    $('#edit_note_content').val(note.content);
                    $('#edit_note_owner').text(note.owner);
                    $('#edit_note_date').text(
                        new Date(note.created_at).toLocaleString('en-US', {
                            dateStyle: 'long',
                            timeStyle: 'short'
                        })
                    );
                    $('#edit_note_updated').text(
                        note.updated_at
                            ? `Last updated: ${new Date(note.updated_at).toLocaleString('en-US', {
                                dateStyle: 'long',
                                timeStyle: 'short'
                            })}`
                            : ''
                    );

                    $('#editNoteModal').modal('show');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function () {
                alert('An error occurred while fetching note details.');
            }
        });
    });

    $("#edit_note_form").submit(function (e) {
        const note_id = $("#edit_note_id").val();
        const title = $("#edit_note_title").val().trim();
        const content = $("#edit_note_content").val().trim();

        if (!title || !content) {
            alert("Please fill out all required fields.");
            return;
        }

        is_loading(true);

        var formData = new FormData();

        formData.append('note_id', note_id);
        formData.append('title', title);
        formData.append('content', content);

        formData.append('action', 'update_note');

        $.ajax({
            url: server_url,
            data: formData,
            type: 'POST',
            dataType: 'JSON',
            processData: false,
            contentType: false,
            success: function (response) {
                if (response) {
                    location.reload();
                }
            },
            error: function (_, _, error) {
                is_loading(false);
                console.error(error);
            }
        });
    });

    $(document).on('click', '.delete_note', function () {
        const note_id = $(this).data('id');
        const note_title = $(this).closest('tr').find('td:first').text().trim();

        Swal.fire({
            icon: "warning",
            title: "Delete Note",
            text: `Are you sure you want to delete the note "${note_title}"?`,
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                is_loading(true);

                var formData = new FormData();
                formData.append('note_id', note_id);
                formData.append('action', 'delete_note');

                $.ajax({
                    url: server_url,
                    data: formData,
                    type: 'POST',
                    dataType: 'JSON',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        location.reload();
                    },
                    error: function (_, _, error) {
                        is_loading(false);
                        console.error(error);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "A network or server error occurred."
                        });
                    }
                });
            }
        });
    });

    $("#addSupplierForm").submit(function () {
        const name = $("#add_supplier_name").val().trim();
        const contact = $("#add_supplier_contact").val().trim();
        const address = $("#add_supplier_address").val().trim();

        is_loading(true);

        var formData = new FormData();
        formData.append('name', name);
        formData.append('contact_number', contact);
        formData.append('address', address);
        formData.append('action', 'add_supplier');

        $.ajax({
            url: server_url,
            type: 'POST',
            data: formData,
            dataType: 'JSON',
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || "Failed to add supplier.");
                }
            },
            error: function (_, _, error) {
                console.error(error);
            }
        });
    });

    $(document).on('click', '.edit_supplier', function () {
        const supplier_id = $(this).data('id');

        $.ajax({
            url: server_url,
            type: 'POST',
            dataType: 'JSON',
            data: { action: 'get_supplier', supplier_id: supplier_id },
            success: function (response) {
                if (response.success) {
                    $('#edit_supplier_id').val(response.data.supplier_id);
                    $('#edit_supplier_name').val(response.data.name);
                    $('#edit_supplier_contact').val(response.data.contact_number);
                    $('#edit_supplier_address').val(response.data.address);

                    $('#editSupplierModal').modal('show');
                } else {
                    alert(response.message || 'Failed to fetch supplier data.');
                }
            },
            error: function (_, _, error) {
                console.error(error);
            }
        });
    });

    $('#editSupplierForm').submit(function () {
        const supplier_id = $('#edit_supplier_id').val();
        const name = $('#edit_supplier_name').val().trim();
        const contact = $('#edit_supplier_contact').val().trim();
        const address = $('#edit_supplier_address').val().trim();

        if (name === '') {
            alert('Supplier name is required.');
            return;
        }

        var formData = new FormData();
        formData.append('supplier_id', supplier_id);
        formData.append('name', name);
        formData.append('contact_number', contact);
        formData.append('address', address);
        formData.append('action', 'update_supplier');

        $.ajax({
            url: server_url,
            type: 'POST',
            data: formData,
            dataType: 'JSON',
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Failed to update supplier.');
                }
            },
            error: function (_, _, error) {
                console.error(error);
            }
        });
    });

    $(document).on('click', '.delete_supplier', function () {
        const supplier_id = $(this).data('id');
        const supplier_name = $(this).closest('tr').find('td:first').text().trim();

        Swal.fire({
            icon: "warning",
            title: "Delete Supplier",
            text: `Are you sure you want to delete the supplier "${supplier_name}"?`,
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                is_loading(true);

                var formData = new FormData();
                formData.append('supplier_id', supplier_id);
                formData.append('action', 'delete_supplier');

                $.ajax({
                    url: server_url,
                    type: 'POST',
                    data: formData,
                    dataType: 'JSON',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: response.message || "Failed to delete supplier."
                            });
                        }
                    },
                    error: function (_, _, error) {
                        is_loading(false);
                        console.error(error);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "A network or server error occurred."
                        });
                    }
                });
            }
        });
    });

    $("#addMedicineForm").submit(function () {
        const name = $("#add_medicine_name").val().trim();
        const category = $("#add_medicine_category").val().trim();
        const description = $("#add_medicine_description").val().trim();
        const unit_price = $("#add_medicine_unit_price").val().trim();
        const quantity = $("#add_medicine_quantity").val().trim();
        const expiry_date = $("#add_medicine_expiry_date").val().trim();
        const supplier_id = $("#add_medicine_supplier").val();

        if (name === "" || category === "" || description === "" || unit_price === "" || quantity === "" || expiry_date === "" || !supplier_id) {
            alert("Please fill out all required fields.");
            return;
        }

        is_loading(true); // optional loading indicator

        var formData = new FormData();
        formData.append('name', name);
        formData.append('category', category);
        formData.append('description', description);
        formData.append('unit_price', unit_price);
        formData.append('quantity', quantity);
        formData.append('expiry_date', expiry_date);
        formData.append('supplier_id', supplier_id);
        formData.append('action', 'add_medicine');

        $.ajax({
            url: server_url,
            type: 'POST',
            data: formData,
            dataType: 'JSON',
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || "Failed to add medicine.");
                }
            },
            error: function (_, _, error) {
                is_loading(false);
                console.error(error);
                alert("A network or server error occurred.");
            }
        });
    });

    $(document).on('click', '.edit_medicine', function () {
        const medicine_id = $(this).data('id');

        $.ajax({
            url: server_url,
            type: 'POST',
            data: { action: 'get_medicine', medicine_id: medicine_id },
            dataType: 'JSON',
            success: function (response) {
                if (response.success) {
                    const med = response.data;
                    $("#edit_medicine_id").val(med.medicine_id);
                    $("#edit_medicine_name").val(med.name);
                    $("#edit_medicine_category").val(med.category);
                    $("#edit_medicine_description").val(med.description);
                    $("#edit_medicine_unit_price").val(med.unit_price);
                    $("#edit_medicine_quantity").val(med.quantity);
                    $("#edit_medicine_expiry_date").val(med.expiry_date);
                    $("#edit_medicine_supplier").val(med.supplier_id);

                    $("#editMedicineModal").modal('show');
                } else {
                    alert(response.message || "Failed to fetch medicine details.");
                }
            },
            error: function (_, _, error) {
                is_loading(false);
                console.error(error);
                alert("A network or server error occurred.");
            }
        });
    });

    $("#editMedicineForm").submit(function () {
        const medicine_id = $("#edit_medicine_id").val();
        const name = $("#edit_medicine_name").val().trim();
        const category = $("#edit_medicine_category").val().trim();
        const description = $("#edit_medicine_description").val().trim();
        const unit_price = $("#edit_medicine_unit_price").val().trim();
        const quantity = $("#edit_medicine_quantity").val().trim();
        const expiry_date = $("#edit_medicine_expiry_date").val().trim();
        const supplier_id = $("#edit_medicine_supplier").val();

        is_loading(true);

        var formData = new FormData();
        formData.append('medicine_id', medicine_id);
        formData.append('name', name);
        formData.append('category', category);
        formData.append('description', description);
        formData.append('unit_price', unit_price);
        formData.append('quantity', quantity);
        formData.append('expiry_date', expiry_date);
        formData.append('supplier_id', supplier_id);
        formData.append('action', 'update_medicine');

        $.ajax({
            url: server_url,
            type: 'POST',
            data: formData,
            dataType: 'JSON',
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || "Failed to update medicine.");
                }
            },
            error: function (_, _, error) {
                is_loading(false);
                console.error(error);
                alert("A network or server error occurred.");
            }
        });
    });

    $(document).on('click', '.delete_medicine', function () {
        const medicine_id = $(this).data('id');
        const medicine_name = $(this).closest('tr').find('td:first').text().trim();

        Swal.fire({
            icon: "warning",
            title: "Delete Medicine",
            text: `Are you sure you want to delete the medicine "${medicine_name}"?`,
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                is_loading(true);

                var formData = new FormData();
                formData.append('medicine_id', medicine_id);
                formData.append('action', 'delete_medicine');

                $.ajax({
                    url: server_url,
                    data: formData,
                    type: 'POST',
                    dataType: 'JSON',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            is_loading(false);

                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: response.message || "Failed to delete medicine."
                            });
                        }
                    },
                    error: function (_, _, error) {
                        is_loading(false);
                        console.error(error);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "A network or server error occurred."
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.notification-item', function () {
        const notifId = $(this).data("id");
        const notifMessage = $(this).find(".notif-message").attr("title");
        const notifTime = $(this).find(".notif-time").text();

        // Show modal
        $("#notifMessage").text(notifMessage);
        $("#notifTime").text("Received: " + notifTime);
        $("#notificationModal").modal("show");

        // Mark as read in backend
        $.ajax({
            url: server_url,
            type: "POST",
            dataType: "JSON",
            data: {
                action: "mark_notification_read",
                notification_id: notifId
            },
            success: function (response) {
                if (response.success) {
                    // Update the clicked dropdown item
                    $(this).removeClass("unread font-weight-bold");

                    // Update the corresponding table row if it exists
                    const $tableRow = $(`.notification-row[data-id='${notifId}']`);
                    if ($tableRow.length) {
                        $tableRow.removeClass("font-weight-bold").find(".status-cell").html('<span class="text-success">Read</span>');
                    }

                    // Refresh badge
                    loadNotifications();
                }
            }.bind(this), // Bind "this" to maintain reference to dropdown item
            error: function (_, _, error) {
                console.error("Error marking notification read:", error);
            }
        });
    });

    $(".notifications-table tbody").on("mouseenter", ".notification-row", function () {
        $(this).css("cursor", "pointer");
    });

    $(document).on('click', '.notification-row', function () {
        const notifId = $(this).data("id");
        const notifMessage = $(this).find("td:first").attr("title");
        const notifTime = $(this).find("td:nth-child(2)").text();

        // Show the modal
        $("#notifMessage").text(notifMessage);
        $("#notifTime").text("Received: " + notifTime);
        $("#notificationModal").modal("show");

        // Mark as read in backend if unread
        if ($(this).hasClass("font-weight-bold")) {
            $.ajax({
                url: server_url,
                type: "POST",
                dataType: "JSON",
                data: {
                    action: "mark_notification_read",
                    notification_id: notifId
                },
                success: function (response) {
                    if (response.success) {
                        // Update UI immediately
                        $(`[data-id='${notifId}']`).removeClass("font-weight-bold").find(".status-cell").html('<span class="text-success">Read</span>');
                        loadNotifications(); // refresh dropdown/badge
                    }
                },
                error: function (_, _, error) {
                    console.error("Error marking notification read:", error);
                }
            });
        }
    });

    $(document).on('click', '#markAllReadBtn', function () {
        $.ajax({
            url: server_url,
            type: 'POST',
            dataType: 'JSON',
            data: { action: 'mark_all_notifications_read' },
            success: function (response) {
                if (response.success) {
                    // Update all rows immediately
                    $(".notification-row").removeClass("font-weight-bold").find(".status-cell").html('<span class="text-success">Read</span>');
                    loadNotifications(); // refresh dropdown/badge
                }
            },
            error: function (_, _, error) {
                console.error("Error marking all notifications read:", error);
            }
        });
    });

    $('#addToCartForm').submit(function (e) {
        e.preventDefault();
        let medicine = $('#medicine_id option:selected');
        let id = medicine.val();
        let name = medicine.text().split(' - ₱')[0].trim();
        let price = parseFloat(medicine.data('price'));
        let stock = parseInt(medicine.data('stock'));
        let qty = parseInt($('#quantity').val());

        if (!id || qty < 1) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid',
                text: 'Please select a medicine and enter valid quantity.'
            });
            return;
        }

        if (qty > stock) {
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: 'Insufficient stock!'
            });
            return;
        }

        let exists = false;
        $('#cartTable tbody tr').each(function () {
            if ($(this).data('id') == id) {
                let currentQty = parseInt($(this).find('td:nth-child(2)').text());
                let newQty = currentQty + qty;
                if (newQty > stock) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: 'Insufficient stock!'
                    });
                    exists = true;
                    return false;
                }
                $(this).find('td:nth-child(2)').text(newQty);
                $(this).find('td:nth-child(4)').text(formatPeso(newQty * price));
                exists = true;
                return false;
            }
        });

        if (!exists) {
            // Remove "Cart is empty" row if present
            $('#cartTable tbody .text-center').remove();

            $('#cartTable tbody').append(`
            <tr data-id="${id}">
                <td>${name}</td>
                <td>${qty}</td>
                <td>${formatPeso(price)}</td>
                <td>${formatPeso(price * qty)}</td>
                <td><button class="btn btn-danger btn-sm removeItem" type="button"><i class="fas fa-trash-alt"></i></button></td>
            </tr>
        `);
        }

        Swal.fire({
            icon: 'success',
            title: 'Added!',
            text: `${qty} x ${name} added to cart`,
            timer: 1500,
            showConfirmButton: false
        });

        updateTotal();
    });

    $(document).on('click', '.removeItem', function () {
        let row = $(this).closest('tr');
        let name = row.find('td:first').text();
        Swal.fire({
            title: `Remove ${name}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                row.remove();
                updateTotal();
                Swal.fire({
                    icon: 'success',
                    title: 'Removed!',
                    text: `${name} removed from cart`,
                    timer: 1000,
                    showConfirmButton: false
                });
            }
        });
    });

    $('#checkoutBtn').click(function () {
        // Collect cart items from table
        let cartItems = [];
        $('#cartTable tbody tr').each(function () {
            if ($(this).find('td').length === 5) {
                cartItems.push({
                    id: $(this).data('id'),
                    name: $(this).find('td:nth-child(1)').text(),
                    qty: parseInt($(this).find('td:nth-child(2)').text()),
                    price: parseFloat($(this).find('td:nth-child(3)').text().replace('₱', '').replace(/,/g, ''))
                });
            }
        });

        if (cartItems.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Cart Empty',
                text: 'Add items to cart before checkout.'
            });
            return;
        }

        // Compute total
        const total = cartItems.reduce((sum, item) => sum + item.price * item.qty, 0);

        // Step 1: Choose customer type
        Swal.fire({
            title: 'Select Customer Type',
            html: `
            <div class="text-left">
                <label><input type="radio" name="custType" value="regular" checked> Regular Customer</label><br>
                <label><input type="radio" name="custType" value="senior"> Senior Citizen (20% Off + VAT Exempt)</label><br>
                <label><input type="radio" name="custType" value="pwd"> PWD (20% Off + VAT Exempt)</label>
            </div>
        `,
            showCancelButton: true,
            confirmButtonText: 'Next',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                return $('input[name="custType"]:checked').val();
            }
        }).then((result) => {
            if (!result.isConfirmed) return;

            const type = result.value;
            let discountLabel = "None";
            let discountedTotal = total;

            // Step 2: Apply discount
            if (type === 'senior' || type === 'pwd') {
                const netBeforeDiscount = total / 1.12; // Remove 12% VAT
                discountedTotal = netBeforeDiscount * 0.8; // Apply 20% discount
                discountLabel = (type === 'senior') ? "Senior Citizen" : "PWD";
            }

            // Step 3: Confirm checkout summary
            Swal.fire({
                title: 'Confirm Checkout',
                html: `
                <div style="text-align:left;">
                    <b>Customer Type:</b> ${discountLabel}<br>
                    <b>Subtotal:</b> ${formatPeso(total)}<br>
                    ${discountLabel !== "None" ? `<b>Discounted Total:</b> ${formatPeso(discountedTotal)}<br>` : ""}
                    <b>Amount to Pay:</b> ${formatPeso(discountedTotal)}
                </div>
            `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Proceed',
                cancelButtonText: 'Cancel'
            }).then((res) => {
                if (!res.isConfirmed) return;

                is_loading(true);

                // Send data to backend
                var formData = new FormData();
                formData.append('action', 'checkout_cart');
                formData.append('cart', JSON.stringify(cartItems));
                formData.append('discount_type', type);
                formData.append('total', discountedTotal);

                $.ajax({
                    url: server_url,
                    type: 'POST',
                    data: formData,
                    dataType: 'JSON',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Checkout Error',
                                text: response.message || "Unable to process checkout."
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Checkout Error',
                            text: error || "Unable to process checkout."
                        });
                    }
                });
            });
        });
    });

    $(document).on('click', '.printReceipt', function () {
        const receiptNumber = $(this).data('receipt');

        $('#receiptContent').html('<div class="text-center">Loading...</div>');
        $('#receiptModal').modal('show');

        $.ajax({
            url: server_url,
            type: 'POST',
            dataType: 'JSON',
            data: { action: 'get_receipt', receipt_number: receiptNumber },
            success: function (response) {
                if (response.success) {
                    $('#receiptContent').html(response.html);
                } else {
                    $('#receiptContent').html('<div class="text-danger">Receipt not found.</div>');
                }
            },
            error: function () {
                $('#receiptContent').html('<div class="text-danger">Failed to fetch receipt.</div>');
            }
        });
    });

    $('#printReceiptBtn').click(function () {
        const printContents = document.getElementById('receiptContent').innerHTML;
        const printWindow = window.open('', '', 'height=600,width=400');
        printWindow.document.write('<html><head><title>Receipt</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body{font-family:monospace;font-size:12px;color:#333;}');
        printWindow.document.write('table{width:100%;border-collapse:collapse;margin-top:5px;}');
        printWindow.document.write('th,td{padding:2px;}');
        printWindow.document.write('hr{border-top:1px dashed #000;margin:5px 0;}');
        printWindow.document.write('img{display:block;margin:5px auto;}');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContents);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    });

    $('#salesFilterForm').submit(function (e) {
        e.preventDefault();
        const start = $('#start_date').val();
        const end = $('#end_date').val();

        $.ajax({
            url: server_url, // backend handler for filtered sales
            type: 'POST',
            dataType: 'JSON',
            data: {
                action: 'filter_sales',
                start_date: start,
                end_date: end
            },
            success: function (response) {
                if (response.success) {
                    let rows = '';
                    if (response.data.length === 0) {
                        rows = '<tr><td colspan="3" class="text-center">No sales found.</td></tr>';
                    } else {
                        response.data.forEach(sale => {
                            rows += `<tr>
                                <td>${sale.receipt_number}</td>
                                <td>${sale.sale_date}</td>
                                <td>${sale.total}</td>
                            </tr>`;
                        });
                    }
                    $('#salesTable tbody').html(rows);
                } else {
                    alert('Failed to fetch filtered sales.');
                }
            },
            error: function () {
                alert('Error fetching sales data.');
            }
        });
    });

    $('#exportPDF').click(function () {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'pt'); // portrait, points

        // --- HEADER ---
        const logoUrl = 'dist/auth/images/logo.png'; // your logo
        const startX = 40;
        let currentY = 40;

        // Add logo (optional)
        doc.addImage(logoUrl, 'PNG', startX, currentY, 50, 50);

        // Add title and pharmacy info
        doc.setFontSize(16);
        doc.setFont("helvetica", "bold");
        doc.text("Rose Pharmacy Inc.", 120, currentY + 20);
        doc.setFontSize(12);
        doc.setFont("helvetica", "normal");
        doc.text("Dolores, Eastern Samar", 120, currentY + 40);
        doc.text("Tel: 0912-345-6789", 120, currentY + 55);

        currentY += 80;

        // Report title
        doc.setFontSize(14);
        doc.setFont("helvetica", "bold");
        doc.text("Sales Report", startX, currentY);
        currentY += 20;

        // Date
        const now = new Date();
        doc.setFontSize(10);
        doc.setFont("helvetica", "normal");
        doc.text(`Generated: ${now.toLocaleString()}`, startX, currentY);
        currentY += 20;

        // --- TABLE ---
        // Replace ₱ with PHP for PDF compatibility
        $('#salesTable td').each(function () {
            $(this).text($(this).text().replace('₱', 'PHP'));
        });

        doc.autoTable({
            html: '#salesTable',
            startY: currentY,
            theme: 'grid',
            headStyles: { fillColor: [22, 160, 133], textColor: 255, fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [240, 240, 240] },
            styles: { font: 'helvetica', fontSize: 10, cellPadding: 4 },
            footStyles: { fillColor: [22, 160, 133], textColor: 255, fontStyle: 'bold' },
            didDrawPage: function (data) {
                // Add page number
                const page = doc.internal.getNumberOfPages();
                doc.setFontSize(8);
                doc.text(`Page ${page}`, data.settings.margin.left, doc.internal.pageSize.height - 10);
            }
        });

        // --- FOOTER ---
        const finalY = doc.lastAutoTable.finalY || currentY;
        doc.setFontSize(10);
        doc.setFont("helvetica", "italic");
        doc.text("Thank you for your business!", startX, finalY + 30);
        doc.text("Visit us again!", startX, finalY + 45);

        // Save PDF
        doc.save('sales_report.pdf');
    });

    function formatPeso(amount) {
        return '₱' + Number(amount).toLocaleString('fil-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateTotal() {
        let total = 0;
        let itemRows = 0;
        $('#cartTable tbody tr').each(function () {
            // Only count item rows, not the "Cart is empty" message
            if ($(this).find('td').length === 5) {
                let rowTotalStr = $(this).find('td:nth-child(4)').text().replace('₱', '').replace(/,/g, '');
                let rowTotal = parseFloat(rowTotalStr) || 0;
                total += rowTotal;
                itemRows++;
            }
        });
        $('#cartTotal').text(formatPeso(total));
        $('#checkoutBtn').prop('disabled', total === 0);

        // If cart is now empty, show "Cart is empty" row
        if (itemRows === 0) {
            $('#cartTable tbody').html(`
            <tr>
                <td colspan="5" class="text-center">Cart is empty</td>
            </tr>
        `);
        }
    }

    function check_stock_level() {
        $.ajax({
            url: server_url,
            type: 'POST',
            data: { action: 'check_stock_level' },
            dataType: 'JSON',
            error: function (_, _, error) {
                console.error("Error checking stock levels:", error);
            }
        });
    }

    function check_expiry_date() {
        $.ajax({
            url: server_url,
            type: 'POST',
            data: { action: 'check_expiry_date' },
            dataType: 'JSON',
            error: function (_, _, error) {
                console.error("Error checking expiry dates:", error);
            }
        });
    }

    function loadNotifications() {
        $.ajax({
            url: server_url,
            type: 'POST',
            dataType: 'JSON',
            data: { action: 'get_notifications' },
            success: function (response) {
                const container = $("#notificationsContainer");
                const countBadge = $("#notificationCount");

                container.empty();

                if (response.success && response.data.length > 0) {
                    let unreadCount = 0;
                    const latestFive = response.data.slice(0, 5);

                    latestFive.forEach(n => {
                        const isUnread = n.is_read == 0;
                        if (isUnread) unreadCount++;

                        container.append(`
                        <a href="javascript:void(0)" 
                           class="dropdown-item notification-item ${isUnread ? 'unread' : ''}" 
                           data-id="${n.notification_id}">
                            <span class="notif-message" title="${n.message}">${n.message}</span>
                            <span class="notif-time">${formatDateTime(n.created_at)}</span>
                        </a>
                    `);
                    });

                    countBadge.text(unreadCount > 0 ? unreadCount : '');
                } else {
                    container.append('<p class="text-center text-muted mb-0 py-3">No new notifications</p>');
                    countBadge.text('');
                }
            },
            error: function (_, _, error) {
                console.error(error);
            }
        });
    }

    function formatDateTime(dateStr) {
        const d = new Date(dateStr);
        return d.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    function is_loading(enabled) {
        if (enabled) {
            $('#loadingOverlay').removeClass('d-none');
        } else {
            $('#loadingOverlay').addClass('d-none');
        }
    }

    function checkExpiration() {
        const encrypted = app_validity;
        const decoded = atob(encrypted);
        const parts = decoded.match(/(\w+)\s(\d+),\s(\d{4})/);

        if (!parts) return;

        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        const month = monthNames.indexOf(parts[1]);
        const day = parseInt(parts[2], 10);
        const year = parseInt(parts[3], 10);
        const expirationDate = new Date(Date.UTC(year, month, day, 23, 59, 59));
        const now = new Date();

        if (now > expirationDate) {
            Swal.fire({
                icon: "error",
                title: "Access Expired",
                text: "This application has expired. Please contact support.",
                confirmButtonColor: "#a6192e"
            }).then(() => {
                window.location.href = "about:blank";
            });
        }
    }

    function preventMobileAccess() {
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        if (isMobile) {
            Swal.fire({
                icon: "error",
                title: "Access Denied",
                text: "Mobile devices are not supported. Please use a desktop browser.",
                confirmButtonColor: "#a6192e"
            }).then(() => {
                window.location.href = "about:blank";
            });
        }
    }
});