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

$conn->set_charset("utf8mb4");

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
$foto_perfil = !empty($user['Foto_perfil']) ? $user['Foto_perfil'] : 'https://via.placeholder.com/150';

// Função para verificar se um treino está nos favoritos
function isFavorito($conn, $user_id, $treino_id) {
    $stmt = $conn->prepare("SELECT ID_TreinoFav FROM Treino_Favoritos WHERE ID_Utilizador = ? AND ID_Treino = ?");
    $stmt->bind_param("ii", $user_id, $treino_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_fav = $result->num_rows > 0;
    $stmt->close();
    return $is_fav;
}

// Definir título da página
$page_title = "EsaFit 24 - Treino de Braços";

// Definir estilos adicionais específicos desta página
$additional_styles = '
   .muscle-card {
     border-radius: 1rem;
     overflow: hidden;
     box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
     transition: all 0.3s ease;
   }
   
   .muscle-card:hover {
     transform: translateY(-5px);
     box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.2);
   }

   .treino-card {
     background: white;
     border-radius: 1rem;
     overflow: hidden;
     box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
     transition: all 0.3s ease;
     position: relative;
   }
   
   .treino-card:hover {
     transform: translateY(-5px);
     box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.2);
   }

   .img-container {
     height: 280px;
     overflow: hidden;
     background-color: #f8f9fa;
   }
   
   .img-container img {
     width: 100%;
     height: 100%;
     object-fit: cover;
   }
   
   .no-image {
     display: flex;
     align-items: center;
     justify-content: center;
     height: 100%;
     color: #6c757d;
   }

   .musculos-badge {
     background-color: var(--secondary) !important;
     margin: 0.125rem;
     padding: 0.25rem 0.5rem;
     border-radius: 0.25rem;
     font-size: 0.75rem;
   }

   /* Hero section overlay para braços */
   .hero-overlay-bracos {
     background: linear-gradient(to right, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%);
   }

   /* Estilos para o botão de favoritos */
   .favorite-btn {
     position: absolute;
     top: 15px;
     right: 15px;
     background: rgba(255, 255, 255, 0.9);
     border: none;
     border-radius: 50%;
     width: 45px;
     height: 45px;
     display: flex;
     align-items: center;
     justify-content: center;
     cursor: pointer;
     transition: all 0.3s ease;
     box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
     z-index: 10;
   }

   .favorite-btn:hover {
     background: rgba(255, 255, 255, 1);
     transform: scale(1.1);
     box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
   }

   .favorite-btn i {
     font-size: 20px;
     transition: all 0.3s ease;
   }

   .favorite-btn.favorited i {
     color: #e74c3c;
     animation: heartBeat 0.6s ease-in-out;
   }

   .favorite-btn:not(.favorited) i {
     color: #bdc3c7;
   }

   .favorite-btn:not(.favorited):hover i {
     color: #e74c3c;
   }

   @keyframes heartBeat {
     0% { transform: scale(1); }
     25% { transform: scale(1.3); }
     50% { transform: scale(1.1); }
     75% { transform: scale(1.25); }
     100% { transform: scale(1); }
   }

   /* Toast notification */
   .toast {
     position: fixed;
     top: 20px;
     right: 20px;
     background: #2ecc71;
     color: white;
     padding: 15px 25px;
     border-radius: 8px;
     box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
     z-index: 1000;
     opacity: 0;
     transform: translateX(100%);
     transition: all 0.3s ease;
   }

   .toast.show {
     opacity: 1;
     transform: translateX(0);
   }

   .toast.error {
     background: #e74c3c;
   }

   .toast.info {
     background: #3498db;
   }
';

// Definir scripts adicionais específicos desta página
$additional_scripts = '
<script>
// Função para toggle de favoritos
async function toggleFavorito(button) {
    const treinoId = button.getAttribute("data-treino-id");
    const icon = button.querySelector("i");
    
    // Desabilitar o botão temporariamente
    button.disabled = true;
    
    try {
        const response = await fetch("favoritos_toggle.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                treino_id: parseInt(treinoId)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.action === "added") {
                // Adicionar classe de favorito
                button.classList.add("favorited");
                showToast(data.message, "success");
            } else if (data.action === "removed") {
                // Remover classe de favorito
                button.classList.remove("favorited");
                showToast(data.message, "info");
            }
        } else {
            showToast(data.message || "Erro ao processar favorito", "error");
        }
    } catch (error) {
        console.error("Erro:", error);
        showToast("Erro de conexão. Tente novamente.", "error");
    } finally {
        // Reabilitar o botão
        button.disabled = false;
    }
}

// Função para mostrar notificações toast
function showToast(message, type = "success") {
    // Remover toast existente se houver
    const existingToast = document.querySelector(".toast");
    if (existingToast) {
        existingToast.remove();
    }
    
    // Criar novo toast
    const toast = document.createElement("div");
    toast.className = `toast ${type === "error" ? "error" : type === "info" ? "info" : ""}`;
    toast.textContent = message;
    
    // Adicionar ao body
    document.body.appendChild(toast);
    
    // Mostrar toast
    setTimeout(() => {
        toast.classList.add("show");
    }, 100);
    
    // Remover toast após 3 segundos
    setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Animation for elements when they scroll into view
document.addEventListener("DOMContentLoaded", function() {
    const fadeInElements = document.querySelectorAll(".fade-in");
    const slideInElements = document.querySelectorAll(".slide-in");
    const scaleUpElements = document.querySelectorAll(".scale-up");
    
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
                    element.classList.add("active");
                }, index * 200);
            }
        });
        
        scaleUpElements.forEach((element, index) => {
            if (isInViewport(element)) {
                setTimeout(() => {
                    element.classList.add("active");
                }, index * 150);
            }
        });
    }
    
    // Initialize animations
    handleScrollAnimations();
    
    // Listen for scroll
    window.addEventListener("scroll", handleScrollAnimations);
});
</script>
';

// Incluir o header
include 'Header.php';
?>

<!-- Main Content -->
<main>
  <!-- Hero Section para Braços -->
  <section class="relative h-[40vh] overflow-hidden" style="background: url('https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?q=80&w=1000&auto=format&fit=crop') no-repeat center center; background-size: cover;">
     <div class="absolute inset-0 hero-overlay-bracos"></div>
     <div class="absolute inset-0 flex flex-col justify-center items-start p-6 md:p-16 text-white container mx-auto">
      <h1 class="text-5xl md:text-6xl font-bold mb-4 header-font slide-in">
       TREINO DE <span class="text-orange-500">BRAÇOS</span>
      </h1>
      <p class="text-lg md:text-xl max-w-lg mb-8 text-gray-200 slide-in">
       Desenvolve força, definição e músculos tonificados com os nossos exercícios especializados para braços.
      </p>
     </div>
  </section>
  
  <!-- Seção de Músculos -->
  <section class="py-16 px-6 md:px-16 bg-white">
    <div class="container mx-auto">
      <h2 class="text-4xl font-bold mb-12 text-center header-font scale-up">GRUPOS <span class="text-orange-500">MUSCULARES</span> DOS BRAÇOS</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Músculo 1 -->
        <div class="muscle-card bg-white border border-gray-100 flex flex-col items-center p-6 scale-up">
          <div class="h-48 w-48 overflow-hidden rounded-full mb-6 bg-gray-100 flex items-center justify-center">
            <img src="https://t4.ftcdn.net/jpg/01/10/74/03/360_F_110740333_xtBlkKYC6AcG9VGD0QfP4hPdsN9e1blp.jpg" alt="Bíceps" class="w-full h-full object-cover"/>
          </div>
          <h3 class="text-xl font-bold mb-3 text-center header-font text-gray-800">BÍCEPS</h3>
          <p class="text-gray-600 text-center text-base">Músculos localizados na parte frontal do braço responsáveis pela flexão do cotovelo.</p>
        </div>
        
        <!-- Músculo 2 -->
        <div class="muscle-card bg-white border border-gray-100 flex flex-col items-center p-6 scale-up">
          <div class="h-48 w-48 overflow-hidden rounded-full mb-6 bg-gray-100 flex items-center justify-center">
            <img src="https://as1.ftcdn.net/v2/jpg/01/10/74/06/1000_F_110740617_0hvWsZt01yL4o7ciS1LSkJVjTws1ffFV.jpg" alt="Tríceps" class="w-full h-full object-cover"/>
          </div>
          <h3 class="text-xl font-bold mb-3 text-center header-font text-gray-800">TRÍCEPS</h3>
          <p class="text-gray-600 text-center text-base">Músculos localizados na parte posterior do braço responsáveis pela extensão do cotovelo.</p>
        </div>
        
        <!-- Músculo 3 -->
        <div class="muscle-card bg-white border border-gray-100 flex flex-col items-center p-6 scale-up">
          <div class="h-48 w-48 overflow-hidden rounded-full mb-6 bg-gray-100 flex items-center justify-center">
            <img src="https://ortopedistadeombro.com.br/wp-content/uploads/2023/04/Musculo-do-Ombro-Guia-Completo-para-Entender-sua-Anatomia-e-Funcionamento.webp" alt="Ombros" class="w-full h-full object-cover"/>
          </div>
          <h3 class="text-xl font-bold mb-3 text-center header-font text-gray-800">OMBROS</h3>
          <p class="text-gray-600 text-center text-base">Compostos pelos deltoides, auxiliam nos movimentos dos braços e dão volume à parte superior.</p>
        </div>
        
        <!-- Músculo 4 -->
        <div class="muscle-card bg-white border border-gray-100 flex flex-col items-center p-6 scale-up">
          <div class="h-48 w-48 overflow-hidden rounded-full mb-6 bg-gray-100 flex items-center justify-center">
            <img src="https://st2.depositphotos.com/1909187/10981/i/950/depositphotos_109811942-stock-photo-forearm-muscles-anatomy-muscles-isolated.jpg" alt="Antebraço" class="w-full h-full object-cover"/>
          </div>
          <h3 class="text-xl font-bold mb-3 text-center header-font text-gray-800">ANTEBRAÇO</h3>
          <p class="text-gray-600 text-center text-base">Músculos responsáveis pelos movimentos do pulso e dedos, fundamentais para o grip.</p>
        </div>
      </div>
      
      <div class="text-center mt-16">
        <a href="#" class="btn-primary inline-block scale-up">INICIAR TREINO DE BRAÇOS</a>
      </div>
    </div>
  </section>
  
  <!-- Seção de Planos de Treino -->
  <section class="py-16 px-6 md:px-16 bg-gray-50">
    <div class="container mx-auto">
        <h2 class="text-4xl font-bold mb-12 text-center header-font scale-up">PLANOS DE <span class="text-orange-500">TREINO</span></h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            // Array com os músculos específicos que queremos filtrar
            $musculos_bracos = array('bícep', 'trícep', 'ombro', 'deltoid', 'antebraç');
            
            // Consultar apenas os planos de treino relacionados especificamente com ombros, tríceps, bíceps e antebraços
            $check_tabela = $conn->query("SHOW TABLES LIKE 'Treino_Musculos'");
            
            if ($check_tabela->num_rows > 0) {
                // Se existir a tabela de relacionamento, usar JOIN com filtro específico
                $sql = "SELECT DISTINCT pt.ID_Treino, pt.Nome_Treino, pt.Imagem_Treino,
                        GROUP_CONCAT(DISTINCT m.Nome_Musculo SEPARATOR ', ') AS Musculos_Alvos
                        FROM Planos_Treino pt
                        INNER JOIN Treino_Musculos tm ON pt.ID_Treino = tm.ID_Treino
                        INNER JOIN Musculos m ON tm.ID_Musculo = m.ID_Musculo
                        WHERE (LOWER(m.Nome_Musculo) LIKE '%bícep%' 
                           OR LOWER(m.Nome_Musculo) LIKE '%tricep%' 
                           OR LOWER(m.Nome_Musculo) LIKE '%trícep%'
                           OR LOWER(m.Nome_Musculo) LIKE '%ombro%' 
                           OR LOWER(m.Nome_Musculo) LIKE '%deltoid%'
                           OR LOWER(m.Nome_Musculo) LIKE '%antebraç%'
                           OR LOWER(m.Nome_Musculo) LIKE '%antebraco%')
                        GROUP BY pt.ID_Treino, pt.Nome_Treino, pt.Imagem_Treino
                        ORDER BY pt.ID_Treino DESC";
            } else {
                // Se não existir, usar apenas a tabela principal com filtro específico
                $sql = "SELECT ID_Treino, Nome_Treino, Imagem_Treino, Musculos_Alvos 
                        FROM Planos_Treino 
                        WHERE (LOWER(Nome_Treino) LIKE '%bícep%' 
                           OR LOWER(Nome_Treino) LIKE '%tricep%'
                           OR LOWER(Nome_Treino) LIKE '%trícep%'
                           OR LOWER(Nome_Treino) LIKE '%ombro%'
                           OR LOWER(Nome_Treino) LIKE '%deltoid%'
                           OR LOWER(Nome_Treino) LIKE '%antebraç%'
                           OR LOWER(Nome_Treino) LIKE '%antebraco%'
                           OR LOWER(Musculos_Alvos) LIKE '%bícep%' 
                           OR LOWER(Musculos_Alvos) LIKE '%tricep%'
                           OR LOWER(Musculos_Alvos) LIKE '%trícep%'
                           OR LOWER(Musculos_Alvos) LIKE '%ombro%'
                           OR LOWER(Musculos_Alvos) LIKE '%deltoid%'
                           OR LOWER(Musculos_Alvos) LIKE '%antebraç%'
                           OR LOWER(Musculos_Alvos) LIKE '%antebraco%')
                        ORDER BY ID_Treino DESC";
            }
            
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0):
                while($row = $result->fetch_assoc()):
                    // Dividir músculos alvos separados por vírgula
                    $musculos = !empty($row['Musculos_Alvos']) ? explode(',', $row['Musculos_Alvos']) : [];
                    
                    // Filtrar apenas músculos dos braços (dupla verificação)
                    $musculos_filtrados = array();
                    foreach($musculos as $musculo) {
                        $musculo_limpo = strtolower(trim($musculo));
                        foreach($musculos_bracos as $musculo_braco) {
                            if (strpos($musculo_limpo, $musculo_braco) !== false) {
                                $musculos_filtrados[] = trim($musculo);
                                break;
                            }
                        }
                    }

                    // Verificar se o treino está nos favoritos
                    $is_favorito = isFavorito($conn, $user_id, $row['ID_Treino']);
            ?>
                    <div class="treino-card scale-up">
                        <!-- Botão de Favoritos -->
                        <button class="favorite-btn <?= $is_favorito ? 'favorited' : '' ?>" 
                                data-treino-id="<?= $row['ID_Treino'] ?>" 
                                onclick="toggleFavorito(this)">
                            <i class="fas fa-heart"></i>
                        </button>

                        <div class="img-container">
                            <?php if (!empty($row["Imagem_Treino"])): ?>
                                <img src="<?= htmlspecialchars($row["Imagem_Treino"]) ?>" alt="<?= htmlspecialchars($row["Nome_Treino"]) ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-dumbbell fa-3x"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-3 header-font"><?= htmlspecialchars($row["Nome_Treino"]) ?></h3>
                            
                            <div class="mb-4">
                                <strong class="text-gray-700">Músculos Trabalhados:</strong><br>
                                <?php if (!empty($musculos_filtrados)): ?>
                                    <div class="mt-2">
                                        <?php foreach($musculos_filtrados as $musculo): ?>
                                            <span class="musculos-badge text-white"><?= htmlspecialchars($musculo) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php elseif (!empty($musculos) && $musculos[0] !== ""): ?>
                                    <div class="mt-2">
                                        <?php foreach($musculos as $musculo): ?>
                                            <span class="musculos-badge text-white"><?= htmlspecialchars(trim($musculo)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-500 text-sm">Músculos dos braços</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-center">
                                <a href="abrir_treino.php?id=<?= $row["ID_Treino"] ?>" class="btn-primary inline-block">
                                    <i class="fas fa-eye mr-2"></i> Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full">
                    <div class="bg-blue-50 border border-blue-200 text-blue-800 p-6 rounded-lg text-center scale-up">
                        <i class="fas fa-info-circle text-3xl mb-4"></i>
                        <p class="text-lg">Nenhum plano de treino específico para bíceps, tríceps, ombros ou antebraços encontrado.</p>
                        <p class="text-sm mt-2">Os exercícios serão adicionados em breve.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
  </section>
  
  <!-- Call to Action -->
  <section class="py-16 px-6 gradient-bg text-white text-center">
    <div class="container mx-auto">
      <h2 class="text-4xl font-bold mb-6 header-font scale-up">ALCANCE SEUS <span class="text-orange-500">OBJETIVOS</span> NOS BRAÇOS</h2>
      <p class="text-xl mb-8 max-w-2xl mx-auto scale-up">Descubra os nossos planos de treino personalizados e comece hoje a transformar seus bíceps, tríceps, ombros e antebraços</p>
    </div>
  </section>
</main>

<?php
include 'Footer.php';
?>