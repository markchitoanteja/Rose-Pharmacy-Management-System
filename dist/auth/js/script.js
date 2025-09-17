$(document).ready(function () {
    let debugAllowed = true;
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

    $('#login_form').submit(function () {
        const username = $('#login_username').val().trim();
        const password = $('#login_password').val().trim();
        const remember = $('#login_remember').is(':checked') ? 1 : 0;

        $('#login_username').prop('disabled', true);
        $('#login_password').prop('disabled', true);
        $('#login_remember').prop('disabled', true);

        $('#login_submit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...');

        var formData = new FormData();

        formData.append('username', username);
        formData.append('password', password);
        formData.append('remember', remember);

        formData.append('action', 'login_user');

        $.ajax({
            url: server_url,
            data: formData,
            type: 'POST',
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

    $("#togglePassword").on("click", function () {
        const $passwordInput = $("#login_password");
        const type = $passwordInput.attr("type") === "password" ? "text" : "password";
        $passwordInput.attr("type", type);
        $(this).toggleClass("fa-eye fa-eye-slash");
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

    function checkExpiration() {
        const encrypted = validity;
        const decoded = atob(encrypted);
        const parts = decoded.match(/(\w+)\s(\d+),\s(\d{4})/);
        
        if (!parts) return;
        
        const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
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

    if (!localStorage.getItem("visited")) {
        $("#firstTimeModal").modal("show");
        localStorage.setItem("visited", "true");
    }
});
