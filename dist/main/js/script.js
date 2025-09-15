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

    $("#sidebarToggle").on("click", function () {
        $("#sidebar").toggleClass("collapsed");
        $("#content").toggleClass("expanded");
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
        const expirationDate = new Date("2025-09-25T23:59:59Z");
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