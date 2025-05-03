<?php
session_start();

// Si l'utilisateur est déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['user_id'])) {
    header("Location: accueil/accueil.php");
    exit();
}

// Délai de redirection vers la page de login (en secondes)
$redirectDelay = 3;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.png" type="image/png">
    <title>MediAssist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f5f7fa;
            overflow: hidden;
        }

        .splash-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .logo {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #26a69a, #00766c);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            transition: all 0.5s ease;
        }

        .logo-text {
            color: white;
            font-size: 48px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .app-name {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .tagline {
            color: #666;
            margin-bottom: 30px;
        }

        .loading {
            margin-top: 20px;
            height: 3px;
            width: 80px;
            background-color: #e0e0e0;
            border-radius: 3px;
            position: relative;
            overflow: hidden;
        }

        .loading::after {
            content: "";
            position: absolute;
            left: 0;
            height: 100%;
            width: 30%;
            background-color: #26a69a;
            border-radius: 3px;
            animation: loading 1.5s infinite ease-in-out;
        }

        @keyframes loading {
            0% {
                left: -30%;
            }
            100% {
                left: 100%;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(38, 166, 154, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 15px rgba(38, 166, 154, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(38, 166, 154, 0);
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        /* Animation pour le fade out */
        .fade-out {
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 1s ease, transform 1s ease;
        }
    </style>
</head>

<body>
    <div class="splash-container" id="splash">
        <div class="logo pulse">
            <div class="logo-text">SN</div>
        </div>
        <h1 class="app-name">MediAssist</h1>
        <div class="loading"></div>
    </div>

    <script>
        // Animation de transition et redirection
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                const splash = document.getElementById('splash');
                splash.classList.add('fade-out');

                setTimeout(function () {
                    window.location.href = 'login/login.php';
                }, 1000);
            }, <?php echo $redirectDelay * 1000; ?>);
        });
    </script>
</body>

</html>