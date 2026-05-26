<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Rankawat Samaj</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/jpeg" href="../assets/images/logo.jpg">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0f7ae5, #18b7de);
            --bg-color: #f8fafc;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        body { 
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-dark);
            padding-bottom: 80px; 
            padding-top: 60px; /* space for fixed top bar */
        }

        /* Sleek Top App Bar */
        .admin-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--primary-gradient);
            color: #fff;
            padding: 16px;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(15, 122, 229, 0.2);
            z-index: 1000;
        }
    </style>
</head>
<body>

<div class="admin-header">
    Admin Dashboard
</div>
