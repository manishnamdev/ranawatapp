<style>
    .mobile-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.03);
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 8px 0 12px;
        z-index: 1000;
    }
    
    .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .nav-item span.icon {
        font-size: 20px;
        margin-bottom: 4px;
        transition: transform 0.2s;
    }

    .nav-item:hover, .nav-item:active {
        color: #0f7ae5;
    }

    .nav-item:hover span.icon, .nav-item:active span.icon {
        transform: translateY(-2px);
    }
    
    .branding-footer {
        text-align: center;
        font-size: 11px;
        color: #64748b;
        padding: 20px 0 10px;
        font-weight: 500;
        width: 100%;
    }
</style>

<div class="branding-footer">
    Product of <a href="https://arbudaedutech.in" target="_blank" style="color: #64748b; pointer-events: auto;">Arbuda Edutech</a>
</div>

<div class="mobile-bottom-nav">
    <a href="dashboard.php" class="nav-item">
        <span class="icon">🏠</span>
        Home
    </a>

    <a href="members.php?status=pending" class="nav-item">
        <span class="icon">👥</span>
        Members
    </a>

    <a href="voting_settings.php" class="nav-item">
        <span class="icon">🗳️</span>
        Voting
    </a>

    <a href="logout.php" class="nav-item">
        <span class="icon">🚪</span>
        Logout
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
