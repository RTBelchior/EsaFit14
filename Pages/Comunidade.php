<html lang="pt">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>EsaFit 24 - Comunidade</title>
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
     transform: translateY(-5px);
     box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.2);
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
   
   /* Post styling - Instagram-like */
   .post-card {
     border-radius: 0.75rem;
     overflow: hidden;
     background-color: white;
     box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
     margin-bottom: 1.5rem;
     transition: all 0.3s ease;
   }
   
   .post-card:hover {
     transform: translateY(-5px);
     box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
   }
   
   .post-header {
     display: flex;
     align-items: center;
     padding: 0.75rem 1rem;
   }
   
   .post-avatar {
     width: 2.5rem;
     height: 2.5rem;
     border-radius: 50%;
     margin-right: 0.75rem;
     object-fit: cover;
   }
   
   .post-username {
     font-weight: 600;
     color: var(--dark);
   }
   
   .post-time {
     font-size: 0.75rem;
     color: #6B7280;
   }
   
   .post-image-container {
     position: relative;
     width: 100%;
     overflow: hidden;
   }
   
   .post-image {
     width: 100%;
     height: auto;
     aspect-ratio: 1 / 1;
     object-fit: cover;
     transition: transform 0.5s ease;
   }
   
   .post-actions {
     padding: 0.75rem 1rem;
     display: flex;
     justify-content: space-between;
   }
   
   .post-likes {
     font-weight: 600;
     margin-bottom: 0.5rem;
   }
   
   .post-caption {
     margin-bottom: 0.5rem;
     line-height: 1.5;
   }
   
   .post-comments-count {
     color: #6B7280;
     font-size: 0.875rem;
     margin-bottom: 0.5rem;
     cursor: pointer;
   }
   
   .post-add-comment {
     border-top: 1px solid #E5E7EB;
     padding-top: 0.75rem;
     display: flex;
     align-items: center;
   }
   
   .comment-input {
     flex: 1;
     border: none;
     outline: none;
     padding: 0.5rem 0;
     font-size: 0.875rem;
   }
   
   .post-comment-btn {
     color: var(--primary);
     font-weight: 600;
     cursor: pointer;
   }
   
   /* Stories */
   .stories-container {
     display: flex;
     overflow-x: auto;
     padding: 1rem;
     margin-bottom: 1.5rem;
     scrollbar-width: none; /* Firefox */
   }
   
   .stories-container::-webkit-scrollbar {
     display: none; /* Chrome, Safari, Opera */
   }
   
   .story {
     display: flex;
     flex-direction: column;
     align-items: center;
     margin-right: 1.25rem;
     cursor: pointer;
   }
   
   .story-avatar-border {
     padding: 0.2rem;
     background: linear-gradient(45deg, #FFB400, #FF5C00, #FF0058, #FF00A2);
     border-radius: 50%;
     margin-bottom: 0.5rem;
   }
   
   .story-avatar {
     width: 4rem;
     height: 4rem;
     border-radius: 50%;
     border: 3px solid white;
     object-fit: cover;
   }
   
   .story-username {
     font-size: 0.75rem;
     text-align: center;
     max-width: 4rem;
     overflow: hidden;
     text-overflow: ellipsis;
     white-space: nowrap;
   }
   
   /* Action buttons */
   .action-icon {
     cursor: pointer;
     font-size: 1.5rem;
     color: #1F2937;
     transition: all 0.2s ease;
   }
   
   .action-icon:hover {
     color: var(--secondary);
     transform: scale(1.1);
   }
   
   /* Create post button */
   .create-post-btn {
     position: fixed;
     bottom: 2rem;
     right: 2rem;
     width: 3.5rem;
     height: 3.5rem;
     border-radius: 50%;
     background-color: var(--secondary);
     color: white;
     display: flex;
     align-items: center;
     justify-content: center;
     font-size: 1.5rem;
     box-shadow: 0 4px 15px rgba(255, 84, 0, 0.4);
     transition: all 0.3s ease;
     z-index: 10;
   }
   
   .create-post-btn:hover {
     transform: translateY(-5px) rotate(90deg);
     box-shadow: 0 8px 25px rgba(255, 84, 0, 0.5);
   }
   
   /* Community stats banner */
   .community-stats {
     background: linear-gradient(135deg, var(--primary), var(--primary-dark));
     padding: 1.5rem;
     border-radius: 1rem;
     color: white;
     box-shadow: 0 10px 25px -5px rgba(15, 82, 186, 0.3);
     margin-bottom: 2rem;
   }
   
   .stat-number {
     font-size: 2rem;
     font-weight: 700;
     display: block;
     margin-bottom: 0.5rem;
   }
   
   .stat-label {
     font-size: 0.875rem;
     opacity: 0.8;
   }
  </style>
 </head>
 <body class="bg-slate-50 text-gray-900">

    <?php
        include 'Header.php';
    ?>
  
  <!-- Main Content -->
  <main class="container mx-auto px-4 py-8 md:px-10">
    <!-- Page Title -->
    <div class="text-center mb-8">
      <h1 class="text-4xl md:text-5xl font-bold mb-4 header-font">COMUNIDADE <span class="text-orange-500">ESAFIT24</span></h1>
      <p class="text-lg text-gray-600 max-w-2xl mx-auto">Conecte-se com outros membros, compartilhe seu progresso e inspire-se com histórias de transformação da nossa comunidade fitness.</p>
    </div>
    
    <!-- Community Stats -->
    <div class="community-stats grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 scale-up">
      <div class="text-center">
        <span class="stat-number">24,587</span>
        <span class="stat-label">MEMBROS ATIVOS</span>
      </div>
      <div class="text-center">
        <span class="stat-number">127,932</span>
        <span class="stat-label">POSTS COMPARTILHADOS</span>
      </div>
      <div class="text-center">
        <span class="stat-number">8,745</span>
        <span class="stat-label">TREINOS CONCLUÍDOS</span>
      </div>
      <div class="text-center">
        <span class="stat-number">5,210</span>
        <span class="stat-label">TRANSFORMAÇÕES</span>
      </div>
    </div>
    
    <!-- Stories -->
    <div class="stories-container scale-up">
      <div class="story">
        <div class="story-avatar-border">
          <img src="/api/placeholder/80/80" alt="Avatar" class="story-avatar">
        </div>
        <span class="story-username">Seu Story</span>
      </div>
      <div class="story">
        <div class="story-avatar-border">
          <img src="/api/placeholder/80/80" alt="Avatar" class="story-avatar">
        </div>
        <span class="story-username">joana_fit</span>
      </div>
      <div class="story">
        <div class="story-avatar-border">
          <img src="/api/placeholder/80/80" alt="Avatar" class="story-avatar">
        </div>
        <span class="story-username">carlos_trainer</span>
      </div>
      <div class="story">
        <div class="story-avatar-border">
          <img src="/api/placeholder/80/80" alt="Avatar" class="story-avatar">
        </div>
        <span class="story-username">pedro_saude</span>
      </div>
      <div class="story">
        <div class="story-avatar-border">
          <img src="/api/placeholder/80/80" alt="Avatar" class="story-avatar">
        </div>
        <span class="story-username">marta_workout</span>
      </div>
      <div class="story">
        <div class="story-avatar-border">
          <img src="/api/placeholder/80/80" alt="Avatar" class="story-avatar">
        </div>
        <span class="story-username">coach_bruno</span>
      </div>
      <div class="story">
        <div class="story-avatar-border">
          <img src="/api/placeholder/80/80" alt="Avatar" class="story-avatar">
        </div>
        <span class="story-username">ana_cardio</span>
      </div>
      <div class="story">
        <div class="story-avatar-border">
          <img src="/api/placeholder/80/80" alt="Avatar" class="story-avatar">
        </div>
        <span class="story-username">felipe_strong</span>
      </div>
    </div>
    
    <!-- Posts Feed -->
    <div class="max-w-2xl mx-auto">
      <!-- Post 1 -->
      <div class="post-card scale-up">
        <div class="post-header">
          <img src="/api/placeholder/50/50" alt="Avatar" class="post-avatar">
          <div>
            <div class="post-username">joana_fit</div>
            <div class="post-time">2 horas atrás</div>
          </div>
          <div class="ml-auto text-gray-400 cursor-pointer">
            <i class="fas fa-ellipsis-h"></i>
          </div>
        </div>
        <div class="post-image-container">
          <img src="/api/placeholder/600/600" alt="Post image" class="post-image">
        </div>
        <div class="p-4">
          <div class="post-actions flex mb-3">
            <div class="flex space-x-4">
              <i class="far fa-heart action-icon"></i>
              <i class="far fa-comment action-icon"></i>
              <i class="far fa-paper-plane action-icon"></i>
            </div>
            <div class="ml-auto">
              <i class="far fa-bookmark action-icon"></i>
            </div>
          </div>
          <div class="post-likes">247 curtidas</div>
          <div class="post-caption">
            <span class="font-semibold">joana_fit</span> 
            Dia de treino de pernas completo! 💪 Suando muito hoje para alcançar meus objetivos. Quem mais está focado nessa semana? #EsaFit24 #TreinoDePernas
          </div>
          <div class="post-comments-count">Ver todos os 32 comentários</div>
          <div class="post-add-comment">
            <input type="text" placeholder="Adicione um comentário..." class="comment-input">
            <div class="post-comment-btn">Publicar</div>
          </div>
        </div>
      </div>
      
      <!-- Post 2 -->
      <div class="post-card scale-up">
        <div class="post-header">
          <img src="/api/placeholder/50/50" alt="Avatar" class="post-avatar">
          <div>
            <div class="post-username">coach_bruno</div>
            <div class="post-time">5 horas atrás</div>
          </div>
          <div class="ml-auto text-gray-400 cursor-pointer">
            <i class="fas fa-ellipsis-h"></i>
          </div>
        </div>
        <div class="post-image-container">
          <img src="/api/placeholder/600/600" alt="Post image" class="post-image">
        </div>
        <div class="p-4">
          <div class="post-actions flex mb-3">
            <div class="flex space-x-4">
              <i class="far fa-heart action-icon"></i>
              <i class="far fa-comment action-icon"></i>
              <i class="far fa-paper-plane action-icon"></i>
            </div>
            <div class="ml-auto">
              <i class="far fa-bookmark action-icon"></i>
            </div>
          </div>
          <div class="post-likes">412 curtidas</div>
          <div class="post-caption">
            <span class="font-semibold">coach_bruno</span> 
            Dica do dia: Para quem está começando, foque na qualidade do movimento e não na quantidade de peso! 📝 Sempre prefira executar o exercício com técnica correta. Se tiver dúvidas, peça ajuda a um dos nossos instrutores! #DicasEsaFit24 #TreinoSeguro
          </div>
          <div class="post-comments-count">Ver todos os 56 comentários</div>
          <div class="post-add-comment">
            <input type="text" placeholder="Adicione um comentário..." class="comment-input">
            <div class="post-comment-btn">Publicar</div>
          </div>
        </div>
      </div>
      
      <!-- Post 3 -->
      <div class="post-card scale-up">
        <div class="post-header">
          <img src="/api/placeholder/50/50" alt="Avatar" class="post-avatar">
          <div>
            <div class="post-username">pedro_saude</div>
            <div class="post-time">1 dia atrás</div>
          </div>
          <div class="ml-auto text-gray-400 cursor-pointer">
            <i class="fas fa-ellipsis-h"></i>
          </div>
        </div>
        <div class="post-image-container">
          <img src="/api/placeholder/600/600" alt="Post image" class="post-image">
        </div>
        <div class="p-4">
          <div class="post-actions flex mb-3">
            <div class="flex space-x-4">
              <i class="far fa-heart action-icon"></i>
              <i class="far fa-comment action-icon"></i>
              <i class="far fa-paper-plane action-icon"></i>
            </div>
            <div class="ml-auto">
              <i class="far fa-bookmark action-icon"></i>
            </div>
          </div>
          <div class="post-likes">189 curtidas</div>
          <div class="post-caption">
            <span class="font-semibold">pedro_saude</span> 
            ◾ ANTES vs DEPOIS ◾ 6 meses de dedicação com o plano EsaFit24 Premium! Agradeço a todos que me apoiaram nessa jornada, especialmente aos treinadores do EsaFit que não me deixaram desistir. A transformação vai além do físico! 🔄 #Transformação #AntesDopois #EsaFit24
          </div>
          <div class="post-comments-count">Ver todos os 78 comentários</div>
          <div class="post-add-comment">
            <input type="text" placeholder="Adicione um comentário..." class="comment-input">
            <div class="post-comment-btn">Publicar</div>
          </div>
        </div>
      </div>
      
      <!-- Post 4 -->
      <div class="post-card scale-up">
        <div class="post-header">
          <img src="/api/placeholder/50/50" alt="Avatar" class="post-avatar">
          <div>
            <div class="post-username">ana_cardio</div>
            <div class="post-time">2 dias atrás</div>
          </div>
          <div class="ml-auto text-gray-400 cursor-pointer">
            <i class="fas fa-ellipsis-h"></i>
          </div>
        </div>
        <div class="post-image-container">
          <img src="/api/placeholder/600/600" alt="Post image" class="post-image">
        </div>
        <div class="p-4">
          <div class="post-actions flex mb-3">
            <div class="flex space-x-4">
              <i class="far fa-heart action-icon"></i>
              <i class="far fa-comment action-icon"></i>
              <i class="far fa-paper-plane action-icon"></i>
            </div>
            <div class="ml-auto">
              <i class="far fa-bookmark action-icon"></i>
            </div>
          </div>
          <div class="post-likes">327 curtidas</div>
          <div class="post-caption">
            <span class="font-semibold">ana_cardio</span> 
            Acabei de completar minha primeira maratona! 🏃‍♀️ Quem diria que há um ano eu mal conseguia correr 3km! Agradeço ao programa de cardio do EsaFit24 por me preparar tão bem para esse desafio. Próxima meta: ultramaratona! 🏆 #EsaFit24 #Maratona #Superação
          </div>
          <div class="post-comments-count">Ver todos os 45 comentários</div>
          <div class="post-add-comment">
            <input type="text" placeholder="Adicione um comentário..." class="comment-input">
            <div class="post-comment-btn">Publicar</div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Create Post Button -->
    <a href="#" class="create-post-btn">
      <i class="fas fa-plus"></i>
    </a>
  </main>
  
<?php
    include 'Footer.php';
?>
  
  <!-- JavaScript -->
  <script>
    // Animation for elements when they scroll into view
    const fadeInElements = document.querySelectorAll('.fade-in');
    const slideInElements = document.querySelectorAll('.slide-in');
    const scaleUpElements = document.querySelectorAll('.scale-up');
    
    // Fade in hero image immediately
    const heroImage = document.getElementById('heroImage');
    setTimeout(() => {
      heroImage.style.opacity = 1;
    }, 300);
    
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
    
    // User profile dropdown functionality
    const userProfileDropdown = document.getElementById('userProfileDropdown');
    const profileMenu = userProfileDropdown.querySelector('.profile-menu');
    let hideTimeout;
    
    userProfileDropdown.addEventListener('mouseenter', () => {
      clearTimeout(hideTimeout);
      profileMenu.style.display = 'block';
    });
    
    userProfileDropdown.addEventListener('mouseleave', () => {
      hideTimeout = setTimeout(() => {
        if (!profileMenu.matches(':hover')) {
          profileMenu.style.display = 'none';
        }
      }, 100);
    });
    
    profileMenu.addEventListener('mouseenter', () => {
      clearTimeout(hideTimeout);
    });
    
    profileMenu.addEventListener('mouseleave', () => {
      hideTimeout = setTimeout(() => {
        profileMenu.style.display = 'none';
      }, 100);
    });
  </script>
 </body>
</html>