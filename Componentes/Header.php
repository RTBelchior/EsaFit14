<?php
// Verificar se a sessão já foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// Conectar ao banco de dados (só se não estiver já conectado)
if (!isset($conn)) {
    $host = "localhost";
    $utilizador = "root";
    $senha = "";
    $dbname = "esafit";

    $conn = new mysqli($host, $utilizador, $senha, $dbname);

    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }
}

// Obter dados do usuário
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM Utilizadores WHERE ID_Utilizador = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    header("Location: Login.php");
    exit();
}

$username = $user['Nome_utilizador'];
// Definir imagem padrão se não houver foto de perfil
$foto_perfil = !empty($user['Foto_perfil']) ? $user['Foto_perfil'] : 'https://via.placeholder.com/50x50/0F52BA/FFFFFF?text=' . substr($username, 0, 1);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($page_title) ? $page_title : 'EsaFit 24'; ?></title>
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
        }
        
        h1, h2, h3, h4, .header-font {
            font-family: 'Oswald', sans-serif;
            font-style: italic;
            font-weight: 700;
            letter-spacing: 0.025em;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
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
        }
        
        /* Animações refinadas */
        .fade-in {
            opacity: 0;
            transition: opacity 1.2s cubic-bezier(0.39, 0.575, 0.565, 1);
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
        
        .scale-up {
            transform: scale(0.92);
            opacity: 0;
            transition: transform 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.7s ease;
        }
        
        .scale-up.active {
            transform: scale(1);
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
        
        /* Cards estilizados */
        .card {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.2);
        }
        
        /* Menu dropdown */
        .profile-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        /* Hero section overlay */
        .hero-overlay {
            background: linear-gradient(to right, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%);
        }
        
        /* Navbar refinements */
        .nav-link {
            position: relative;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 3px;
            bottom: 0;
            left: 50%;
            background-color: var(--secondary);
            transition: all 0.3s ease;
            transform: translateX(-50%);
            border-radius: 3px;
        }
        
        .nav-link:hover::after {
            width: 70%;
        }
        
        /* Footer upgrade */
        .footer-link {
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .footer-link:hover {
            transform: translateX(5px);
            color: var(--secondary);
        }

        /* Estilos para a foto de perfil no menu */
        .profile-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--secondary);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .profile-avatar:hover {
            transform: scale(1.1);
            border-color: white;
            box-shadow: 0 4px 15px rgba(255, 84, 0, 0.4);
        }

        /* Fallback para quando não há imagem */
        .profile-avatar-fallback {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary), #ff7700);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .profile-avatar-fallback:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(255, 84, 0, 0.4);
        }

        <?php echo isset($additional_styles) ? $additional_styles : ''; ?>
    </style>
    <?php echo isset($additional_head_content) ? $additional_head_content : ''; ?>
</head>
<body class="bg-slate-50 text-gray-900 text-lg">
    <!-- Header -->
    <header class="gradient-bg text-white sticky top-0 z-40 shadow-lg">
        <div class="container mx-auto flex justify-between items-center py-5 px-6 md:px-10">
            <div class="text-4xl md:text-5xl font-bold pulse header-font flex items-center">
                <span href="Pagina_Inicial.php" class="text-white">Esa</span><span href="Pagina_Inicial.php" class="text-orange-500">Fit</span><span href="Pagina_Inicial.php" class="text-white">24</span>
            </div>
            <nav class="flex items-center justify-between space-x-10 text-xl font-medium">
                <a class="nav-link hover:text-orange-400" href="Pagina_Inicial.php">
                    Página Inicial
                </a>
                <div class="relative group">
                    <a class="nav-link hover:text-orange-400 flex items-center" href="#">
                        Planos de Treino
                        <i class="fas fa-chevron-down text-sm ml-1"></i>
                    </a>
                    <div class="absolute left-0 top-full hidden group-hover:block bg-white text-gray-800 py-3 w-56 shadow-xl z-20 text-lg rounded-lg border-t-4 border-orange-500">
                        <a class="block px-4 py-2 hover:bg-gray-100 hover:text-orange-500 transition-colors" href="Costas.php">
                            <i class="fas fa-angle-right mr-2 text-orange-500"></i>Costas
                        </a>
                        <a class="block px-4 py-2 hover:bg-gray-100 hover:text-orange-500 transition-colors" href="Abdominal.php">
                            <i class="fas fa-angle-right mr-2 text-orange-500"></i>Abdominal
                        </a>
                        <a class="block px-4 py-2 hover:bg-gray-100 hover:text-orange-500 transition-colors" href="Glúteos.php">
                            <i class="fas fa-angle-right mr-2 text-orange-500"></i>Glúteos
                        </a>
                        <a class="block px-4 py-2 hover:bg-gray-100 hover:text-orange-500 transition-colors" href="Pernas.php">
                            <i class="fas fa-angle-right mr-2 text-orange-500"></i>Pernas
                        </a>
                        <a class="block px-4 py-2 hover:bg-gray-100 hover:text-orange-500 transition-colors" href="Peito.php">
                            <i class="fas fa-angle-right mr-2 text-orange-500"></i>Peito
                        </a>
                        <a class="block px-4 py-2 hover:bg-gray-100 hover:text-orange-500 transition-colors" href="Braços.php">
                            <i class="fas fa-angle-right mr-2 text-orange-500"></i>Braços
                        </a>
                        <a class="block px-4 py-2 hover:bg-gray-100 hover:text-orange-500 transition-colors" href="Cardio.php">
                            <i class="fas fa-angle-right mr-2 text-orange-500"></i>Cardio
                        </a>
                    </div>
                </div>
                <a class="nav-link hover:text-orange-400" href="Receitas.php">
                    Receitas
                </a>
            </nav>
            <div class="flex items-center space-x-6">
                <div class="relative" id="userProfileDropdown">
                    <!-- Substituir o ícone pela foto de perfil -->
                    <?php if (!empty($user['Foto_perfil'])): ?>
                        <img src="<?php echo htmlspecialchars($user['Foto_perfil']); ?>" 
                             alt="<?php echo htmlspecialchars($username); ?>" 
                             class="profile-avatar"
                             title="<?php echo htmlspecialchars($username); ?>">
                    <?php else: ?>
                        <div class="profile-avatar-fallback" title="<?php echo htmlspecialchars($username); ?>">
                            <?php echo strtoupper(substr($username, 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="profile-menu absolute right-0 top-full mt-2 bg-white text-gray-800 py-2 w-48 shadow-lg z-20 text-base rounded-lg border-t-4 border-orange-500">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($username); ?></p>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($user['Email'] ?? ''); ?></p>
                        </div>
                        <a class="block px-4 py-3 hover:bg-gray-100 text-gray-700 transition-colors flex items-center" href="Perfil.php">
                            <i class="fas fa-user-alt mr-3 text-orange-500"></i>Ver Perfil
                        </a>
                        <a class="block px-4 py-3 hover:bg-gray-100 text-gray-700 transition-colors flex items-center" href="Editar_Perfil.php">
                            <i class="fas fa-edit mr-3 text-blue-600"></i>Editar Perfil
                        </a>
                        <div class="h-px bg-gray-200 mx-4 my-1"></div>
                        <a class="block px-4 py-3 hover:bg-red-50 text-red-600 transition-colors flex items-center" href="Logout.php">
                            <i class="fas fa-sign-out-alt mr-3"></i>Terminar Sessão
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
</html>