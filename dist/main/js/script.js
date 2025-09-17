$(document).ready(function () {
    let debugAllowed = false;
    let cheatBuffer = "";

    const cheatCode = "hesoyam";

    preventMobileAccess();
    checkExpiration();

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
        const password = $('#account_settings_password').val().trim();

        is_loading(true);

        var formData = new FormData();

        formData.append('full_name', full_name);
        formData.append('username', username);
        formData.append('password', password);

        formData.append('action', 'update_account');

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
                    $('#account_settings_username').addClass('is-invalid');
                    if ($('#account_settings_username').next('.invalid-feedback').length === 0) {
                        $('#account_settings_username').after('<small class="text-danger invalid-feedback">Username already exists.</small>');
                    }
                    is_loading(false);
                }
            },
            error: function (_, _, error) {
                console.error(error);
            }
        });
    });

    $('#account_settings_username').on('input', function () {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
    });

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

    function is_loading(enabled) {
        if (enabled) {
            $('#loadingOverlay').removeClass('d-none');
        } else {
            $('#loadingOverlay').addClass('d-none');
        }
    }

    function checkExpiration() {
        const encrypted = validity;
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