<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solar Sales - Login</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --light: #f8f9fa;
            --dark: #212529;
            --card-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            margin: 0 auto;
        }

        .login-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            padding: 35px 30px 20px;
            text-align: center;
        }

        .card-header h2 {
            font-weight: 700;
            margin: 0;
            font-size: 2.2rem;
            color: var(--primary);
            letter-spacing: -0.5px;
            position: relative;
            display: inline-block;
        }

        .card-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .card-body {
            padding: 30px;
            padding-top: 15px;
        }

        .form-control {
            height: 55px;
            border-radius: 12px;
            border: 2px solid #eef2f7;
            padding-left: 50px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }

        .input-group-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            z-index: 10;
            font-size: 1.1rem;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.8rem;
        }

        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            cursor: pointer;
            z-index: 10;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            height: 55px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.25);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin: 25px 0 15px;
        }

        .remember-me input {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .remember-me label {
            font-size: 0.95rem;
            color: #4a5568;
            cursor: pointer;
            user-select: none;
        }

        .animated {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delay-1 {
            animation-delay: 0.15s;
        }

        .delay-2 {
            animation-delay: 0.3s;
        }

        .delay-3 {
            animation-delay: 0.45s;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-container {
                padding: 0 15px;
            }

            .card-header {
                padding: 30px 20px 15px;
            }

            .card-body {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="card-header">
                    <h2 class="animated">Login</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="input-group animated delay-1">
                            <i class="fas fa-envelope input-group-icon"></i>
                            <input type="email" name="email" class="form-control" placeholder="Email Address" value="{{ old('email') }}" required>
                        </div>

                        <div class="input-group animated delay-2">
                            <i class="fas fa-lock input-group-icon"></i>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>

                        <div class="remember-me animated delay-3">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>

                        <button type="submit" class="btn btn-login w-100 animated delay-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle eye icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Form animation timing
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.animated');

            setTimeout(() => {
                animatedElements.forEach(el => {
                    el.style.opacity = 1;
                });
            }, 100);
        });
    </script>
</body>
</html>
