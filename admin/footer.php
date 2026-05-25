<style>
.app-footer {
    position: fixed;
    bottom: 0;
    width: 100%;
    background: #ffffff;
    border-top: 1px solid #ddd;
    z-index: 999;
}
.app-footer a {
    text-decoration: none;
    color: #333;
    font-size: 12px;
}
.app-footer .footer-btn {
    flex: 1;
    text-align: center;
    padding: 6px 0;
}
body {
    padding-bottom: 70px; /* footer space */
}
</style>

<div class="app-footer d-flex">
    <div class="footer-btn">
        <a href="javascript:history.back()">⬅<br>Back</a>
    </div>

    <div class="footer-btn">
        <a href="/">🏠<br>Home</a>
    </div>

    <div class="footer-btn">
        <a href="contact.php">📞<br>Contact</a>
    </div>
</div>
