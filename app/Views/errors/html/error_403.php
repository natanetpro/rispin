<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? '403 Forbidden' ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .container {
            text-align: center;
            padding: 40px;
        }
        .error-code {
            font-size: 120px;
            font-weight: 700;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #ffeaa7;
        }
        .error-message {
            font-size: 16px;
            color: #b2bec3;
            margin-bottom: 30px;
            max-width: 400px;
        }
        .icon-lock {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.8;
        }
        .btn-back {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #0984e3, #6c5ce7);
            color: #fff;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(9, 132, 227, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-lock">🔒</div>
        <div class="error-code">403</div>
        <h1 class="error-title">Access Forbidden</h1>
        <p class="error-message"><?= $message ?? 'You do not have permission to access this page.' ?></p>
        <a href="javascript:history.back()" class="btn-back">← Go Back</a>
    </div>
</body>
</html>
