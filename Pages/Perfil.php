<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: Login.php");
  exit();
}

$host = "localhost";
$utilizador = "root";
$senha = "";
$dbname = "esafit";

$conn = new mysqli($host, $utilizador, $senha, $dbname);

if ($conn->connect_error) {
  die("Falha na conexão: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM Utilizadores WHERE ID_Utilizador = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();

if (!$user) {
  header("Location: Login.php");
  exit();
}

$username = $user['Nome_utilizador'];
$foto_perfil = !empty($user['Foto_perfil']) ? $user['Foto_perfil'] : 'https://via.placeholder.com/150';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil de <?php echo htmlspecialchars($username); ?> | EsaFit 24</title>
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

   .btn-secondary {
     background-color: var(--primary);
     color: white;
     font-weight: bold;
     padding: 0.5rem 1rem;
     border-radius: 0.5rem;
     transition: all 0.3s ease;
     text-transform: uppercase;
     font-family: 'Oswald', sans-serif;
     letter-spacing: 0.05em;
     font-style: italic;
     box-shadow: 0 4px 15px rgba(15, 82, 186, 0.3);
   }
   
   .btn-secondary:hover {
     transform: translateY(-2px);
     box-shadow: 0 6px 20px rgba(15, 82, 186, 0.4);
     background-color: var(--primary-dark);
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
     transform: translateY(-5px);
     box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.2);
   }

   .profile-pic {
     width: 150px;
     height: 150px;
     border-radius: 50%;
     object-fit: cover;
     border: 5px solid var(--secondary);
     box-shadow: 0 10px 30px rgba(255, 84, 0, 0.3);
     transition: all 0.3s ease;
   }

   .profile-pic:hover {
     transform: scale(1.05);
     box-shadow: 0 15px 40px rgba(255, 84, 0, 0.4);
   }

   .imc-normal {
     color: #22c55e;
     font-weight: bold;
   }

   .imc-abaixo {
     color: #3b82f6;
     font-weight: bold;
   }

   .imc-sobre {
     color: #f59e0b;
     font-weight: bold;
   }

   .imc-obesidade {
     color: #ef4444;
     font-weight: bold;
   }

   .message {
     border-radius: 0.75rem;
     padding: 1rem 1.5rem;
     margin-bottom: 2rem;
     display: flex;
     align-items: center;
     gap: 0.75rem;
     font-weight: 600;
     box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
     transition: all 0.6s ease;
   }

   .message.success {
     background: linear-gradient(135deg, #dcfce7, #bbf7d0);
     border: 2px solid #22c55e;
     color: #15803d;
   }

   .stat-card {
     background: white;
     border-radius: 1rem;
     padding: 1.5rem;
     box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
     transition: all 0.3s ease;
     border-left: 4px solid var(--secondary);
   }

   .stat-card:hover {
     transform: translateY(-3px);
     box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.15);
   }

   .back-btn {
     background: white;
     border: 2px solid var(--primary);
     color: var(--primary);
     width: 50px;
     height: 50px;
     border-radius: 50%;
     display: flex;
     align-items: center;
     justify-content: center;
     font-size: 1.2rem;
     transition: all 0.3s ease;
     box-shadow: 0 4px 15px rgba(15, 82, 186, 0.2);
     cursor: pointer;
   }

   .back-btn:hover {
     background: var(--primary);
     color: white;
     transform: translateY(-2px);
     box-shadow: 0 6px 20px rgba(15, 82, 186, 0.3);
   }

   .hero-overlay {
     background: linear-gradient(to right, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%);
   }

   .empty-data {
     color: #6b7280;
     font-style: italic;
   }
  </style>
</head>
<body class="bg-slate-50 text-gray-900 min-h-screen">
  <!-- Header Section -->
  <section class="relative h-64 overflow-hidden" style="background: url('https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center; background-size: cover;">
    <div class="absolute inset-0 hero-overlay"></div>
    <div class="absolute inset-0 flex items-center justify-between p-6">
      <button onclick="voltar()" class="back-btn slide-in">
        <i class="fas fa-arrow-left"></i>
      </button>
      <div class="text-white text-center slide-in">
        <div class="text-3xl md:text-4xl font-bold pulse header-font">
          <span class="text-white">Esa</span><span class="text-orange-500">Fit</span><span class="text-white">24</span>
        </div>
        <p class="text-lg mt-2">Perfil do Utilizador</p>
      </div>
      <div class="w-12"></div> <!-- Spacer for centering -->
    </div>
  </section>

  <div class="container mx-auto px-6 py-8 max-w-4xl">
    <!-- Success Message -->
    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="message success scale-up">
        <i class="fas fa-check-circle text-xl"></i>
        <span><?php echo htmlspecialchars($_SESSION['success_message']); ?></span>
      </div>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Profile Info Card -->
    <div class="card bg-white p-8 mb-8 scale-up">
      <div class="text-center">
        <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil de <?php echo htmlspecialchars($username); ?>" class="profile-pic mx-auto mb-6">
        <h1 class="text-4xl font-bold mb-4 header-font text-gray-800"><?php echo htmlspecialchars($username); ?></h1>
        <button class="btn-primary inline-flex items-center gap-2" onclick="window.location.href='editar_perfil.php'">
          <i class="fas fa-edit"></i> Editar Perfil
        </button>
      </div>
    </div>

    <!-- User Stats Grid -->
    <?php if (isset($user['Peso']) && isset($user['Altura']) && $user['Altura'] > 0): ?>
      <?php
        $altura_original = $user['Altura'];
        $altura_metros = $altura_original > 3 ? $altura_original / 100 : $altura_original;
        $imc = $user['Peso'] / ($altura_metros * $altura_metros);
        $imc_categoria = '';
        $imc_class = '';

        if ($imc < 18.5) {
            $imc_categoria = 'Abaixo do peso';
            $imc_class = 'imc-abaixo';
        } elseif ($imc < 25) {
            $imc_categoria = 'Peso normal';
            $imc_class = 'imc-normal';
        } elseif ($imc < 30) {
            $imc_categoria = 'Sobrepeso';
            $imc_class = 'imc-sobre';
        } else {
            $imc_categoria = 'Obesidade';
            $imc_class = 'imc-obesidade';
        }
      ?>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Peso Card -->
        <div class="stat-card scale-up">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
              <i class="fas fa-weight text-orange-500"></i>
            </div>
            <h3 class="text-lg font-bold header-font">Peso</h3>
          </div>
          <p class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($user['Peso']); ?> kg</p>
        </div>

        <!-- Altura Card -->
        <div class="stat-card scale-up">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <i class="fas fa-ruler-vertical text-blue-600"></i>
            </div>
            <h3 class="text-lg font-bold header-font">Altura</h3>
          </div>
          <p class="text-2xl font-bold text-gray-800"><?php echo number_format($altura_metros, 2); ?> m</p>
        </div>

        <!-- IMC Card -->
        <div class="stat-card scale-up">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <i class="fas fa-calculator text-green-600"></i>
            </div>
            <h3 class="text-lg font-bold header-font">IMC</h3>
          </div>
          <p class="text-2xl font-bold text-gray-800"><?php echo number_format($imc, 1); ?></p>
          <span class="<?php echo $imc_class; ?> text-sm"><?php echo $imc_categoria; ?></span>
        </div>
      </div>
    <?php else: ?>
      <div class="card bg-white p-8 mb-8 scale-up">
        <div class="text-center py-8">
          <i class="fas fa-info-circle text-4xl text-gray-400 mb-4"></i>
          <p class="empty-data text-lg">Dados de altura e peso não disponíveis</p>
          <p class="text-gray-500 mt-2">Complete seu perfil para ver suas estatísticas</p>
        </div>
      </div>
    <?php endif; ?>

    <!-- Biography Section -->
    <?php if (!empty($user['Biografia'])): ?>
      <div class="card bg-white p-8 mb-8 scale-up">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
            <i class="fas fa-quote-left text-orange-500 text-lg"></i>
          </div>
          <h3 class="text-2xl font-bold header-font">Biografia</h3>
        </div>
        <p class="text-gray-700 leading-relaxed">
          <?php echo nl2br(htmlspecialchars($user['Biografia'])); ?>
        </p>
      </div>
    <?php endif; ?>
  </div>

  <!-- JavaScript -->
  <script>
    function voltar() {
      window.location.href = "pagina_inicial.php";
    }

    // Animation for elements when they scroll into view
    const slideInElements = document.querySelectorAll('.slide-in');
    const scaleUpElements = document.querySelectorAll('.scale-up');
    
    // Function to check if element is in viewport
    function isInViewport(element) {
      const rect = element.getBoundingClientRect();
      return (
        rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.85 &&
        rect.bottom >= 0
      );
    }
    
    // Function to handle scroll animations
    function handleScrollAnimations() {
      slideInElements.forEach((element, index) => {
        if (isInViewport(element)) {
          setTimeout(() => {
            element.classList.add('active');
          }, index * 200);
        }
      });
      
      scaleUpElements.forEach((element, index) => {
        if (isInViewport(element)) {
          setTimeout(() => {
            element.classList.add('active');
          }, index * 150);
        }
      });
    }
    
    // Initialize animations
    window.addEventListener('load', () => {
      handleScrollAnimations();
    });
    
    // Listen for scroll
    window.addEventListener('scroll', () => {
      handleScrollAnimations();
    });

    // Success message handling
    document.addEventListener('DOMContentLoaded', function() {
      const message = document.querySelector('.message');
      if (message) {
        setTimeout(function() {
          message.style.opacity = '0';
          setTimeout(function() {
            message.style.display = 'none';
          }, 600);
        }, 5000);
      }
    });
  </script>
</body>
</html>