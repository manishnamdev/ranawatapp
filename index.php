<?php
session_start();

// Agar member login hai -> dashboard
if (isset($_SESSION['member_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <title>श्री रंकण भवन रांकावत समाज संस्था, रानी स्टे.</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Devanagari:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Noto Serif Devanagari', serif;
            background: #f4f6fb;
        }

        .header {
            position: sticky;
            top: 0;
            background: linear-gradient(135deg, #d32f2f, #f57c00);
            color: #fff;
            padding: 12px 8px 14px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .header-logo {
            width: 150px;
            height: auto;
            border-radius: 50%;
            object-fit: contain;
            background: transparent;
            padding: 0;
        }

        .header-text-wrap {
            text-align: left;
        }

        .header small {
            font-size: 13px;
            font-weight: 500;
        }

        .header h2 {
            margin: 4px 0;
            font-size: 19px;
            font-weight: 800;
        }

        .header .tagline {
            font-size: 14px;
            font-weight: 600;
            opacity: 0.9;
        }

        .container {
            padding: 18px;
        }

        .card {
            background: #ffffff;
            padding: 20px;
            border-radius: 18px;
            box-shadow: 0 8px 18px rgba(0,0,0,0.08);
        }

        .card h3 {
            text-align: center;
            margin-bottom: 14px;
            font-size: 17px;
            color: #374151;
        }

        .card ul {
            padding-left: 18px;
            font-size: 15px;
            color: #374151;
        }

        .card ul li {
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            font-size: 17px;
            font-weight: 600;
            border: none;
            border-radius: 14px;
            margin-top: 14px;
            cursor: pointer;
        }

        .btn-register {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            color: #0f5132;
        }

        .btn-login {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: #053b5c;
        }

        .btn-history {
            background: linear-gradient(135deg, #f6d365, #fda085);
            color: #7b4a03;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .help-link-wrap {
            margin-top: 16px;
            text-align: center;
        }

        .help-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 999px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            color: #1d4ed8;
            background: #eef4ff;
            border: 1px solid #c7d7ff;
            box-shadow: 0 4px 10px rgba(29, 78, 216, 0.08);
        }

        .help-link:hover {
            background: #e3eeff;
        }

        .footer {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: #6b7280;
        }

        .top-image {
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }

        .top-image img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 16px;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="assets/images/logo.jpg" alt="Logo" class="header-logo">
        <div class="header-text-wrap">
            <h2>श्री रंकण भवन रांकावत समाज संस्था, रानी स्टे.</h2>
            <small>सरकार द्वारा मान्यता प्राप्त रजिस्ट्रेशन क्रमांक 685/80-81</small>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="top-image">
                <img src="assets/images/banner.jpeg" alt="समाज सेवा समिति">
            </div>

            <h3>ऐप उपयोग निर्देश</h3>

            <ul>
                <li>यह ऐप समाज के सदस्यों की जानकारी हेतु बनाया गया है।</li>
                <li>नया सदस्य बनने हेतु <b>सदस्य जुड़ें</b> बटन दबाएँ।</li>
                <li>पहले से पंजीकृत सदस्य <b>Login करें</b>।</li>
                <li>कृपया सभी जानकारी सही एवं पूर्ण भरें।</li>
                <li>किसी भी समस्या हेतु समिति से संपर्क करें।</li>
            </ul>

            <button class="btn btn-register" onclick="goToRegister()">
                सदस्य जुड़ें
            </button>

            <button class="btn btn-login" onclick="goToLogin()">
                Login करें
            </button>

            <button class="btn btn-history" onclick="goToHistory()">
                इतिहास
            </button>

            <div class="help-link-wrap">
                <a href="forgot_pin.php" class="help-link">
                    🔑 लॉगिन पिन भूल गए?
                </a>
            </div>
        </div>

        <div class="footer">
            © श्री रंकण भवन रांकावत समाज संस्था
        </div>
    </div>

    <script>
        function goToRegister() {
            window.location.href = "register.php";
        }

        function goToLogin() {
            window.location.href = "login.php";
        }

        function goToHistory() {
            window.location.href = "history.php";
        }
    </script>
</body>
</html>
