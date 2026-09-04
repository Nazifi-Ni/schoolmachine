<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IAMS-ARMS | Login</title>
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-color: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            padding: 2rem;
        }

        .logo-container {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .logo-container i {
            color: var(--primary-blue);
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: var(--text-muted);
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-submit {
            width: 100%;
            background-color: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.875rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 2rem 0;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border-color);
        }

        .divider::before { margin-right: 1rem; }
        .divider::after { margin-left: 1rem; }

        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            background-color: white;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.875rem;
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-main);
            cursor: pointer;
            transition: background-color 0.2s;
            text-decoration: none;
        }

        .btn-google:hover {
            background-color: #f8fafc;
        }

        .btn-google img {
            width: 20px;
            height: 20px;
            margin-right: 0.75rem;
        }

        .alert {
            background-color: #fef2f2;
            color: #ef4444;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #fca5a5;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div style="text-center mb-4">
            <div style="display: flex; justify-content: center; margin-bottom: 15px;">
                <img src="/assets/images/school_logo.jpg" alt="School Logo" style="width: 120px; height: auto; border-radius: 8px;">
            </div>
            <h2 style="color: #0056b3; font-weight: bold; font-size: 1.4rem; line-height: 1.3; text-align: center; margin-bottom: 0.5rem;">
                ISMAIL AHMAD MEMORIAL<br>NURSERY AND PRIMARY SCHOOL
            </h2>
            <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">Sign in to the Academic Records Management System.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="/login" method="post">
            <div class="form-group">
                <label class="form-label">Username</label>
                <div class="input-wrapper">
                    <i class="far fa-envelope"></i>
                    <input type="text" name="username" class="form-control" placeholder="headteacher" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-submit">Sign in</button>
        </form>
    </div>
</body>
</html>
