<?php
session_start();
 
$erro = "";
$host = "localhost";
$utilizador = "root";
$senha = "";
$dbname = "esafit";
 
$conn = new mysqli($host, $utilizador, $senha, $dbname);
 
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $userPassword = trim($_POST['password'] ?? '');
 
    if (!empty($email) && !empty($userPassword)) {
        // Verificar se são as credenciais de admin
        if ($email === "admin@esafit.pt" && $userPassword === "1234") {
            // Criar entrada fictícia para admin na sessão
            $_SESSION['user_id'] = -1; // ID especial para admin
            $_SESSION['email'] = $email;
            $_SESSION['nome'] = 'Administrador';
            $_SESSION['is_admin'] = true;
           
            // Admin vai para a área administrativa
            header("Location: lista_treinos.php");
            exit();
        }
       
        // Tentar com nome da tabela em maiúscula primeiro
        $stmt = $conn->prepare("SELECT * FROM Utilizadores WHERE Email = ?");
        if (!$stmt) {
            // Se falhar, tentar com minúscula
            $stmt = $conn->prepare("SELECT * FROM utilizadores WHERE Email = ?");
        }
       
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
 
            if ($resultado && $resultado->num_rows > 0) {
                $linha = $resultado->fetch_assoc();
               
                if (password_verify($userPassword, $linha['Password'])) {
                    // Login bem-sucedido para utilizador normal
                    $_SESSION['user_id'] = $linha['ID_Utilizador'];
                    $_SESSION['email'] = $linha['Email'];
                    $_SESSION['nome'] = $linha['Nome_utilizador'];
                    $_SESSION['is_admin'] = false;
                   
                    // Utilizador normal vai para a página inicial
                    header("Location: Pagina_Inicial.php");
                    exit();
                } else {
                    $erro = "Email ou palavra-passe incorretos.";
                }
            } else {
                $erro = "Email ou palavra-passe incorretos.";
            }
           
            $stmt->close();
        } else {
            $erro = "Erro na base de dados. Verifique se a tabela 'Utilizadores' existe.";
        }
    } else {
        $erro = "Por favor, preencha todos os campos.";
    }
}
 
mysqli_close($conn);
?>
 
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sessão | EsaFit 24</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --primary: #0F52BA;
            --primary-dark: #0A3A8C;
            --secondary: #FF5400;
            --dark: #1A202C;
            --light: #F8FAFC;
        }
       
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            min-height: 100vh;
        }
       
        h1, h2, h3, h4, .header-font {
            font-family: 'Oswald', sans-serif;
            font-style: italic;
            font-weight: 700;
            letter-spacing: 0.025em;
        }
       
        .btn-primary {
            background-color: var(--secondary);
            color: white;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-family: 'Oswald', sans-serif;
            letter-spacing: 0.05em;
            font-style: italic;
            box-shadow: 0 4px 15px rgba(255, 84, 0, 0.3);
        }
       
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 84, 0, 0.4);
            background-color: #e04800;
        }
       
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
       
        .form-input {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }
       
        .form-input:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(255, 84, 0, 0.1);
            outline: none;
        }
       
        .slide-in {
            transform: translateY(60px);
            opacity: 0;
            transition: transform 1s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 1s ease;
        }
       
        .slide-in.active {
            transform: translateY(0);
            opacity: 1;
        }
       
        .pulse {
            animation: pulse 2.5s infinite;
        }
       
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }
       
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #dc2626;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
       
        .success-message {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #16a34a;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
       
        .logout-message {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #2563eb;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            font-weight: 500;
            animation: fadeInScale 0.5s ease-out;
        }
       
        @keyframes fadeInScale {
            0% {
                opacity: 0;
                transform: scale(0.9) translateY(-10px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
       
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
       
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }
       
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            left: 10%;
            animation-delay: 0s;
        }
       
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            left: 70%;
            animation-delay: 5s;
        }
       
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            left: 40%;
            animation-delay: 10s;
        }
       
        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }
       
        /* Background pattern */
        .bg-pattern {
            background-image:
                radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
        }
    </style>
</head>
<body class="bg-pattern">
    <!-- Floating Shapes Animation -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
   
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="login-card rounded-2xl p-8 md:p-12 w-full max-w-md slide-in">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="text-4xl md:text-5xl font-bold pulse header-font mb-2">
                    <span class="text-gray-800">Esa</span><span class="text-orange-500">Fit</span><span class="text-gray-800">24</span>
                </div>
                <p class="text-gray-600 font-medium">Bem-vindo de volta!</p>
            </div>
           
            <!-- Logout Message -->
            <?php if (isset($_SESSION['logout_message'])): ?>
                <div class="logout-message">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    <?php echo $_SESSION['logout_message']; ?>
                </div>
                <?php unset($_SESSION['logout_message']); ?>
            <?php endif; ?>
           
            <!-- Success Message -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle mr-3"></i>
                    <?php echo $_SESSION['success_message']; ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
           
            <!-- Error Message -->
            <?php if (!empty($erro)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle mr-3"></i>
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>
           
            <!-- Login Form -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-blue-600"></i>Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        class="form-input w-full px-4 py-3 rounded-lg text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-orange-500"
                        placeholder="Digite seu email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>
               
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-blue-600"></i>Palavra-passe
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="form-input w-full px-4 py-3 rounded-lg text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-orange-500"
                        placeholder="Digite sua palavra-passe"
                    >
                </div>
               
                <button type="submit" class="btn-primary w-full py-4 text-lg font-bold">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    ENTRAR
                </button>
            </form>
           
            <!-- Divider -->
            <div class="flex items-center my-8">
                <div class="flex-1 h-px bg-gray-300"></div>
                <span class="px-4 text-gray-500 font-medium">ou</span>
                <div class="flex-1 h-px bg-gray-300"></div>
            </div>
           
            <!-- Register Link -->
            <div class="text-center">
                <p class="text-gray-600 mb-4">Não tem conta?</p>
                <a href="criar_conta.php" class="inline-flex items-center font-bold text-blue-700 hover:text-orange-500 transition-colors duration-300 text-lg">
                    <i class="fas fa-user-plus mr-2"></i>
                    CRIAR CONTA
                </a>
            </div>
           
            <!-- Admin Login Info -->
            <div class="text-center mt-6 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Admin: admin@esafit.pt | Password: 1234
                </p>
            </div>
           
            <!-- Footer -->
            <div class="text-center mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    © 2025 EsaFit 24. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </div>
   
    <script>
        // Initialize slide-in animation
        window.addEventListener('load', () => {
            const slideElements = document.querySelectorAll('.slide-in');
            slideElements.forEach((element, index) => {
                setTimeout(() => {
                    element.classList.add('active');
                }, index * 200);
            });
        });
       
        // Auto-hide logout message after 5 seconds
        const logoutMessage = document.querySelector('.logout-message');
        if (logoutMessage) {
            setTimeout(() => {
                logoutMessage.style.transition = 'all 0.5s ease-out';
                logoutMessage.style.opacity = '0';
                logoutMessage.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    logoutMessage.style.display = 'none';
                }, 500);
            }, 5000);
        }
       
        // Add ripple effect to button
        document.querySelector('.btn-primary').addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.height, rect.width);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
           
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
           
            this.appendChild(ripple);
           
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
       
        // Enhanced focus effects
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
           
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    </script>
   
    <style>
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            animation: ripple-effect 0.6s linear;
            pointer-events: none;
        }
       
        @keyframes ripple-effect {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
       
        .focused label {
            color: var(--secondary) !important;
        }
    </style>
</body>
</html>