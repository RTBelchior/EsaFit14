<?php session_start();  
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
  die("Falha na ligação: " . $conn->connect_error); 
}  

// Obter os dados do utilizador 
$user_id = $_SESSION['user_id']; 
$sql = "SELECT * FROM Utilizadores WHERE ID_Utilizador = ?"; 
$stmt = $conn->prepare($sql); 
$stmt->bind_param("i", $user_id); 
$stmt->execute(); 
$result = $stmt->get_result();  

if ($result->num_rows > 0) {   
  $user = $result->fetch_assoc(); 
} else {   
  header("Location: Login.php");   
  exit(); 
}  

$stmt->close(); 
$conn->close();   

// Converter altura para cm, se necessário 
$altura = isset($user['Altura']) ? $user['Altura'] : ''; 
if ($altura > 0 && $altura < 10) {   
  $altura = $altura * 100; 
} 
?> 
<!DOCTYPE html> 
<html lang="pt"> 
<head>   
  <meta charset="UTF-8" />   
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>   
  <title>Editar Perfil | EsaFit 24</title> 
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
     border: none;
     cursor: pointer;
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
     width: 120px;
     height: 120px;
     border-radius: 50%;
     object-fit: cover;
     border: 4px solid var(--secondary);
     box-shadow: 0 10px 30px rgba(255, 84, 0, 0.3);
     transition: all 0.3s ease;
     cursor: pointer;
   }

   .profile-pic:hover {
     transform: scale(1.05);
     box-shadow: 0 15px 40px rgba(255, 84, 0, 0.4);
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
     text-decoration: none;
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

   .form-group {
     margin-bottom: 1.5rem;
   }

   .form-group label {
     display: block;
     margin-bottom: 0.5rem;
     font-weight: 600;
     color: var(--dark);
     font-size: 0.95rem;
   }

   .form-group input {
     width: 100%;
     padding: 0.875rem 1rem;
     border: 2px solid #e2e8f0;
     border-radius: 0.75rem;
     font-size: 1rem;
     transition: all 0.3s ease;
     background: white;
     color: var(--dark);
   }

   .form-group input:focus {
     outline: none;
     border-color: var(--primary);
     box-shadow: 0 0 0 3px rgba(15, 82, 186, 0.1);
     transform: translateY(-1px);
   }

   .upload-btn {
     background: var(--primary);
     color: white;
     padding: 0.5rem 1rem;
     border-radius: 0.5rem;
     cursor: pointer;
     transition: all 0.3s ease;
     display: inline-block;
     font-weight: 600;
     font-size: 0.875rem;
     text-transform: uppercase;
     letter-spacing: 0.05em;
     box-shadow: 0 4px 15px rgba(15, 82, 186, 0.3);
   }

   .upload-btn:hover {
     background: var(--primary-dark);
     transform: translateY(-2px);
     box-shadow: 0 6px 20px rgba(15, 82, 186, 0.4);
   }

   .error-message {
     color: #ef4444;
     font-size: 0.875rem;
     margin-top: 0.5rem;
     display: none;
     font-weight: 500;
   }

   .alert {
     padding: 1rem 1.25rem;
     margin-bottom: 1.5rem;
     border-radius: 0.75rem;
     font-weight: 500;
     display: flex;
     align-items: center;
     gap: 0.75rem;
     box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
   }

   .alert-danger {
     background: linear-gradient(135deg, #fef2f2, #fecaca);
     border: 2px solid #ef4444;
     color: #dc2626;
   }

   .alert-danger::before {
     content: '\f071';
     font-family: 'Font Awesome 6 Free';
     font-weight: 900;
     font-size: 1.2rem;
   }

   .required-field {
     color: var(--secondary);
   }

   .avatar-section {
     text-align: center;
     margin-bottom: 2rem;
   }

   .avatar-section img {
     margin-bottom: 1rem;
   }

   .form-container {
     max-width: 600px;
     margin: 0 auto;
   }

   .section-divider {
     height: 2px;
     background: linear-gradient(90deg, transparent, var(--primary), transparent);
     margin: 2rem 0;
     border-radius: 1px;
   }

   @media (max-width: 768px) {
     .profile-pic {
       width: 100px;
       height: 100px;
     }
   }
  </style>
</head> 
<body class="bg-slate-50 text-gray-900 min-h-screen">
  <!-- Header Section -->
  <section class="relative h-64 overflow-hidden" style="background: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center; background-size: cover;">
    <div class="absolute inset-0 hero-overlay"></div>
    <div class="absolute inset-0 flex items-center justify-between p-6">
      <a href="perfil.php" class="back-btn slide-in">
        <i class="fas fa-arrow-left"></i>
      </a>
      <div class="text-white text-center slide-in">
        <div class="text-3xl md:text-4xl font-bold pulse header-font">
          <span class="text-white">Esa</span><span class="text-orange-500">Fit</span><span class="text-white">24</span>
        </div>
        <p class="text-lg mt-2">Editar Perfil</p>
      </div>
      <div class="w-12"></div> <!-- Spacer for centering -->
    </div>
  </section>

  <div class="container mx-auto px-6 py-8">
    <div class="form-container">
      <!-- Main Form Card -->
      <div class="card bg-white p-8 scale-up">
        <div class="text-center mb-8">
          <div class="flex items-center justify-center gap-3 mb-4">
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
              <i class="fas fa-user-edit text-orange-500 text-lg"></i>
            </div>
            <h1 class="text-3xl font-bold header-font text-gray-800">Editar Perfil</h1>
          </div>
          <p class="text-gray-600">Atualize suas informações pessoais</p>
        </div>

        <!-- Error Messages -->
        <?php
        if (isset($_SESSION['profile_errors']) && !empty($_SESSION['profile_errors'])) {
            echo '<div class="alert alert-danger scale-up">';
            foreach ($_SESSION['profile_errors'] as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
            unset($_SESSION['profile_errors']);
        }
        ?>

        <form id="profileForm" action="Atualizar_Perfil.php" method="post" enctype="multipart/form-data">
          <!-- Avatar Section -->
          <div class="avatar-section scale-up">
            <?php if (!empty($user['Foto_perfil'])): ?>           
              <img id="avatarPreview" src="<?php echo htmlspecialchars($user['Foto_perfil']); ?>" alt="Foto de Perfil" class="profile-pic mx-auto">         
            <?php else: ?>           
              <img id="avatarPreview" src="https://via.placeholder.com/120x120/0F52BA/FFFFFF?text=Foto" alt="Foto de Perfil" class="profile-pic mx-auto">         
            <?php endif; ?>
            
            <div class="mt-4">
              <label class="upload-btn">
                <i class="fas fa-camera mr-2"></i>
                Alterar Foto           
                <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/gif" hidden>         
              </label>
            </div>
            <div id="avatarError" class="error-message">Formato de imagem inválido. Apenas JPG, JPEG, PNG e GIF são permitidos.</div>
          </div>

          <div class="section-divider"></div>

          <!-- Personal Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name Field -->
            <div class="form-group md:col-span-2 scale-up">
              <label for="name">
                <i class="fas fa-user text-orange-500 mr-2"></i>
                Nome Completo <span class="required-field">*</span>
                <span class="text-sm text-gray-500">(mínimo 6 caracteres)</span>
              </label>
              <input
                type="text"
                id="name"
                name="name"
                value="<?php echo htmlspecialchars($user['Nome_utilizador'] ?? ''); ?>"
                placeholder="Insere o teu nome completo"
                minlength="6"
                required
              >
              <div id="nameError" class="error-message">
                Por favor, insira o seu nome completo (mínimo 6 caracteres).
              </div>
            </div>

            <!-- Height Field -->
            <div class="form-group scale-up">
              <label for="height">
                <i class="fas fa-ruler-vertical text-blue-600 mr-2"></i>
                Altura <span class="required-field">*</span>
                <span class="text-sm text-gray-500">(cm)</span>
              </label>
              <input
                type="number"
                id="height"
                name="height"
                value="<?php echo htmlspecialchars($altura); ?>"
                step="1"
                min="100"
                max="250"
                placeholder="Ex: 175"
                required
              >
              <div id="heightError" class="error-message">Altura inválida (100-250 cm).</div>
            </div>

            <!-- Weight Field -->
            <div class="form-group scale-up">
              <label for="weight">
                <i class="fas fa-weight text-green-600 mr-2"></i>
                Peso <span class="required-field">*</span>
                <span class="text-sm text-gray-500">(kg)</span>
              </label>
              <input
                type="number"
                id="weight"
                name="weight"
                value="<?php echo htmlspecialchars($user['Peso'] ?? ''); ?>"
                step="0.1"
                min="30"
                max="250"
                placeholder="Ex: 70.5"
                required
              >
              <div id="weightError" class="error-message">Peso inválido (30-250 kg).</div>
            </div>

            <!-- Email Field -->
            <div class="form-group md:col-span-2 scale-up">
              <label for="email">
                <i class="fas fa-envelope text-purple-600 mr-2"></i>
                Email <span class="required-field">*</span>
              </label>
              <input
                type="email"
                id="email"
                name="email"
                value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>"
                placeholder="exemplo@email.com"
                required
              >
              <div id="emailError" class="error-message">Email inválido.</div>
            </div>
          </div>

          <div class="section-divider"></div>

          <!-- Submit Button -->
          <div class="text-center">
            <button type="submit" class="btn-primary inline-flex items-center gap-2 text-lg px-8 py-3">
              <i class="fas fa-save"></i>
              Guardar Alterações
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- JavaScript -->
  <script>
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

    // Avatar preview functionality
    document.getElementById('avatarInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('avatarPreview');
      const errorDiv = document.getElementById('avatarError');
      
      if (file) {
        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
          errorDiv.style.display = 'block';
          return;
        }
        
        errorDiv.style.display = 'none';
        
        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });

    // Form validation
    document.getElementById('profileForm').addEventListener('submit', function(e) {
      let isValid = true;
      
      // Name validation
      const name = document.getElementById('name').value.trim();
      const nameError = document.getElementById('nameError');
      if (name.length < 6) {
        nameError.style.display = 'block';
        isValid = false;
      } else {
        nameError.style.display = 'none';
      }
      
      // Height validation
      const height = parseInt(document.getElementById('height').value);
      const heightError = document.getElementById('heightError');
      if (height < 100 || height > 250) {
        heightError.style.display = 'block';
        isValid = false;
      } else {
        heightError.style.display = 'none';
      }
      
      // Weight validation
      const weight = parseFloat(document.getElementById('weight').value);
      const weightError = document.getElementById('weightError');
      if (weight < 30 || weight > 250) {
        weightError.style.display = 'block';
        isValid = false;
      } else {
        weightError.style.display = 'none';
      }
      
      // Email validation
      const email = document.getElementById('email').value.trim();
      const emailError = document.getElementById('emailError');
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        emailError.style.display = 'block';
        isValid = false;
      } else {
        emailError.style.display = 'none';
      }
      
      if (!isValid) {
        e.preventDefault();
      }
    });

    // Input focus animations
    document.querySelectorAll('input').forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'translateY(-2px)';
      });
      
      input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'translateY(0)';
      });
    });
  </script>
</body> 
</html>