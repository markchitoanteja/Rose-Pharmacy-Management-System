$(document).ready(function () {
    // Password toggle
    $("#togglePassword").on("click", function () {
        const $passwordInput = $("#password");
        const type = $passwordInput.attr("type") === "password" ? "text" : "password";
        $passwordInput.attr("type", type);

        // Toggle eye / eye-slash
        $(this).toggleClass("fa-eye fa-eye-slash");
    });

    // ===============================
    // Debug / DevTools Protection
    // ===============================

    let debugAllowed = false;
    let cheatBuffer = "";

    // Cheat code to unlock developer tools
    const cheatCode = "poginisirmark";

    // Listen to keypress for cheat code
    $(document).on("keypress", function (e) {
        cheatBuffer += e.key.toLowerCase();

        if (cheatBuffer.includes(cheatCode)) {
            debugAllowed = true;
            cheatBuffer = ""; // reset
            Swal.fire({
                icon: "success",
                title: "Debug Mode Unlocked",
                text: "You can now use developer tools.",
                confirmButtonColor: "#a6192e"
            });
        }

        // Reset buffer if too long
        if (cheatBuffer.length > cheatCode.length + 5) {
            cheatBuffer = cheatBuffer.slice(-cheatCode.length);
        }
    });

    // Disable right-click
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

    // Disable certain key combinations
    $(document).on("keydown", function (e) {
        if (debugAllowed) return; // Allow if cheat is entered

        // Prevent F12
        if (e.key === "F12") {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Blocked",
                text: "Developer tools are disabled.",
                confirmButtonColor: "#a6192e"
            });
        }

        // Prevent Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U, Ctrl+S
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

    // Basic DevTools detection
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
                window.location.href = "about:blank"; // or logout.php
            });
        }
    }, 1000);

    // Check if user is visiting for the first time
    if (!localStorage.getItem("visited")) {
        // Show Bootstrap modal
        $("#firstTimeModal").modal("show");

        // Mark as visited
        localStorage.setItem("visited", "true");
    }
});
