<?php
// ============================================
// TOAST NOTIFICATION HELPER
// Save this as: includes/toast.php
//
// HOW TO USE from any page:
//   1. Before redirecting: $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Course added!'];
//   2. header("Location: somepage.php");
//   3. exit;
//
// Then include this file right before </body> in footer.php
// It will automatically show the toast and clear it after
// ============================================

if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    $type  = $toast['type'] ?? 'info';
    $msg   = htmlspecialchars($toast['msg'] ?? '');

    $icons = [
        'success' => '✅',
        'error'   => '⚠️',
        'info'    => 'ℹ️',
    ];
    $icon = $icons[$type] ?? 'ℹ️';

    echo '
    <div class="toast-container" id="toastContainer">
        <div class="toast-box toast-' . $type . '" id="toastBox">
            <span class="toast-icon">' . $icon . '</span>
            <p class="toast-message">' . $msg . '</p>
            <button class="toast-close" onclick="closeToast()">&times;</button>
        </div>
    </div>
    <script>
        function closeToast() {
            const box = document.getElementById("toastBox");
            if (box) {
                box.classList.add("fade-out");
                setTimeout(() => box.remove(), 300);
            }
        }
        // Auto-dismiss after 4 seconds
        setTimeout(closeToast, 4000);
    </script>
    ';

    // ✅ Clear the toast after showing it once — prevents it
    // from reappearing on next page load
    unset($_SESSION['toast']);
}
