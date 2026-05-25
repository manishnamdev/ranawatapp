<style>
.app-footer {
    position: fixed;
    bottom: 0;
    width: 100%;
    background: #ffffff;
    border-top: 1px solid #ddd;
    display: flex;
    z-index: 999;
    box-shadow: 0 -4px 14px rgba(0, 0, 0, 0.06);
}

.app-footer a {
    flex: 1;
    text-align: center;
    padding: 8px 4px 10px;
    color: #555;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-weight: 600;
}

.app-footer .nav-icon {
    font-size: 20px;
    line-height: 1;
}

.app-footer .nav-text {
    font-size: 11px;
    line-height: 1.2;
}

.app-footer a.active {
    color: #28a745;
}
</style>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['member_id']);
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$appBase = ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');
?>
<div class="app-footer">
    <a href="index.php">
        <span class="nav-icon">🏠</span>
        <span class="nav-text">Home</span>
    </a>
    <a href="vote.php">
        <span class="nav-icon">🗳️</span>
        <span class="nav-text">Vote</span>
    </a>
    <a href="contact.php">
        <span class="nav-icon">📞</span>
        <span class="nav-text">Contact</span>
    </a>
    <?php if ($isLoggedIn): ?>
        <a href="logout.php">
            <span class="nav-icon">🚪</span>
            <span class="nav-text">Logout</span>
        </a>
    <?php endif; ?>
</div>

<?php if ($isLoggedIn): ?>
<?php $pushConfig = require __DIR__ . "/../config/push.php"; ?>
<script>
(function () {
    const publicKey = "<?= htmlspecialchars($pushConfig['publicKey'], ENT_QUOTES); ?>";
    const appBase = "<?= htmlspecialchars($appBase, ENT_QUOTES); ?>";

    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        return;
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
    }

    async function saveSubscription(subscription) {
        await fetch(`${appBase}/push_subscribe.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(subscription),
        });
    }

    async function syncSubscription() {
        const registration = await navigator.serviceWorker.register(`${appBase}/sw.js`, {
            scope: `${appBase || ''}/`,
        });
        let subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(publicKey),
            });
        }

        await saveSubscription(subscription.toJSON());
        return true;
    }

    function createEnableButton() {
        let button = document.getElementById('push-enable-btn');
        if (!button) {
            button = document.createElement('button');
            button.id = 'push-enable-btn';
            button.type = 'button';
            button.style.position = 'fixed';
            button.style.right = '14px';
            button.style.bottom = '84px';
            button.style.zIndex = '1200';
            button.style.border = 'none';
            button.style.borderRadius = '999px';
            button.style.padding = '12px 16px';
            button.style.background = '#0d6efd';
            button.style.color = '#fff';
            button.style.boxShadow = '0 8px 18px rgba(13,110,253,0.25)';

            button.addEventListener('click', async function () {
                try {
                    if (Notification.permission === 'denied') {
                        alert('Notifications are blocked in this browser. Please allow notifications in browser settings first.');
                        return;
                    }

                    const permission = Notification.permission === 'granted'
                        ? 'granted'
                        : await Notification.requestPermission();

                    if (permission === 'granted') {
                        const subscribed = await syncSubscription();
                        if (subscribed) {
                            button.remove();
                        }
                    }
                } catch (error) {
                    console.error('Notification permission failed', error);
                }
            });

            document.body.appendChild(button);
        }

        button.textContent = Notification.permission === 'denied'
            ? 'Notifications Blocked'
            : 'Enable Notifications';
    }

    window.addEventListener('load', async function () {
        try {
            const registration = await navigator.serviceWorker.register(`${appBase}/sw.js`, {
                scope: `${appBase || ''}/`,
            });
            const existingSubscription = await registration.pushManager.getSubscription();

            if (existingSubscription) {
                await saveSubscription(existingSubscription.toJSON());
                const existingButton = document.getElementById('push-enable-btn');
                if (existingButton) {
                    existingButton.remove();
                }
            } else {
                createEnableButton();
            }
        } catch (error) {
            console.error('Push subscription failed', error);
            createEnableButton();
        }
    });
})();
</script>
<?php endif; ?>

</body>
</html>
