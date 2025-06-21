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
$page_title = "EsaFit 24 - Treino de Cardio";

// Definir estilos adicionais específicos desta página
$additional_styles = '
   /* Estilos específicos para a página cardio */
   .intensity-card {
     border-radius: 1rem;
     overflow: hidden;
     box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
     transition: all 0.3s ease;
   }
   
   .intensity-card:hover {
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
    toast.className = `toast ${type === "error" ? "error" : ""}`;
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
</script>
';

// Incluir o header
include 'header.php';
?>

<!-- Main Content -->
<main>
  <!-- Hero Section para Cardio -->
  <section class="relative h-[40vh] overflow-hidden" style="background: url('/api/placeholder/1600/600') no-repeat center center; background-size: cover;">
     <div class="absolute inset-0 bg-gradient-to-r from-black to-transparent opacity-70"></div>
     <div class="absolute inset-0 flex flex-col justify-center items-start p-6 md:p-16 text-white container mx-auto">
      <h1 class="text-5xl md:text-6xl font-bold mb-4 header-font">
       TREINO <span class="text-orange-500">CARDIO</span>
      </h1>
      <p class="text-lg md:text-xl max-w-lg mb-8 text-gray-200">
       Aumenta a tua resistência, melhora a tua saúde cardiovascular e queima calorias com os nossos treinos de cardio.
      </p>
     </div>
  </section>
  
  <!-- Seção de Níveis de Intensidade -->
  <section class="py-16 px-6 md:px-16 bg-white">
    <div class="container mx-auto">
      <h2 class="text-4xl font-bold mb-12 text-center header-font">NÍVEIS DE <span class="text-orange-500">INTENSIDADE</span> CARDIO</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Intensidade 1 -->
        <div class="intensity-card bg-white border border-gray-100 flex flex-col items-center p-6">
          <div class="h-48 w-48 overflow-hidden rounded-full mb-6 bg-gray-100 flex items-center justify-content">
            <img src="https://www.prevoir.pt/bloguemomentus/wp-content/uploads/2021/07/caminhadas-diarias-1.jpg" alt="Baixa Intensidade" class="w-full h-full object-cover"/>
          </div>
          <h3 class="text-xl font-bold mb-3 text-center header-font text-gray-800">BAIXA INTENSIDADE</h3>
          <p class="text-gray-600 text-center text-base">Exercícios suaves e constantes, ideais para iniciantes e recuperação. Foco em duração, não em intensidade.</p>
        </div>
        
        <!-- Intensidade 2 -->
        <div class="intensity-card bg-white border border-gray-100 flex flex-col items-center p-6">
          <div class="h-48 w-48 overflow-hidden rounded-full mb-6 bg-gray-100 flex items-center justify-center">
            <img src="https://www.blog.bioritmo.com.br/wp-content/uploads/2021/12/shutterstock_722394541-1.jpg" alt="Média Intensidade" class="w-full h-full object-cover"/>
          </div>
          <h3 class="text-xl font-bold mb-3 text-center header-font text-gray-800">MÉDIA INTENSIDADE</h3>
          <p class="text-gray-600 text-center text-base">Equilibra esforço e recuperação. Perfeito para desenvolvimento de resistência e queima de gordura.</p>
        </div>
        
        <!-- Intensidade 3 -->
        <div class="intensity-card bg-white border border-gray-100 flex flex-col items-center p-6">
          <div class="h-48 w-48 overflow-hidden rounded-full mb-6 bg-gray-100 flex items-center justify-center">
            <img src="https://runplace.com.br/wp-content/uploads/2020/01/ajustes-na-corrida-1536x864.jpg" alt="Alta Intensidade" class="w-full h-full object-cover"/>
          </div>
          <h3 class="text-xl font-bold mb-3 text-center header-font text-gray-800">ALTA INTENSIDADE</h3>
          <p class="text-gray-600 text-center text-base">Exercícios explosivos e intervalados. Maximiza a queima calórica e o condicionamento em menos tempo.</p>
        </div>
      </div>
      
      <div class="text-center mt-16">
        <a href="#" class="btn-primary inline-block">INICIAR TREINO CARDIO</a>
      </div>
    </div>
  </section>
  
  <!-- Seção de Planos de Treino -->
  <section class="py-16 px-6 md:px-16 bg-gray-50">
    <div class="container mx-auto">
        <h2 class="text-4xl font-bold mb-12 text-center header-font">PLANOS DE <span class="text-orange-500">TREINO</span></h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            // Consultar apenas os planos de treino relacionados com cardio
            // Primeiro verificar se existem as tabelas necessárias
            $check_musculos = $conn->query("SHOW TABLES LIKE 'Musculos'");
            $check_treino_musculos = $conn->query("SHOW TABLES LIKE 'Treino_Musculos'");
            
            if ($check_musculos->num_rows > 0 && $check_treino_musculos->num_rows > 0) {
                // Query corrigida para buscar especificamente treinos de cardio
                $sql = "SELECT DISTINCT pt.ID_Treino, pt.Nome_Treino, pt.Imagem_Treino, pt.Musculos_Alvos,
                        GROUP_CONCAT(DISTINCT m.Nome_Musculo SEPARATOR ', ') AS Musculos_Relacionados
                        FROM Planos_Treino pt
                        INNER JOIN Treino_Musculos tm ON pt.ID_Treino = tm.ID_Treino
                        INNER JOIN Musculos m ON tm.ID_Musculo = m.ID_Musculo
                        WHERE m.Nome_Musculo = 'Cárdio'
                        GROUP BY pt.ID_Treino, pt.Nome_Treino, pt.Imagem_Treino, pt.Musculos_Alvos
                        ORDER BY pt.Data_Criacao DESC";
                        
                $result = $conn->query($sql);
                
                // Se não encontrar treinos na tabela de relacionamento, tentar buscar pelo campo Musculos_Alvos
                if (!$result || $result->num_rows == 0) {
                    $sql_fallback = "SELECT ID_Treino, Nome_Treino, Imagem_Treino, Musculos_Alvos,
                                     Musculos_Alvos as Musculos_Relacionados
                                     FROM Planos_Treino 
                                     WHERE Musculos_Alvos LIKE '%Cárdio%' 
                                        OR Musculos_Alvos LIKE '%cardio%'
                                        OR Musculos_Alvos LIKE '%cardiovascular%'
                                        OR Nome_Treino LIKE '%cardio%' 
                                        OR Nome_Treino LIKE '%corrida%'
                                        OR Nome_Treino LIKE '%HIIT%' 
                                        OR Nome_Treino LIKE '%cicl%'
                                        OR Nome_Treino LIKE '%aeróbico%'
                                     ORDER BY Data_Criacao DESC";
                    $result = $conn->query($sql_fallback);
                }
            } else {
                // Fallback: usar apenas a tabela principal
                $sql = "SELECT ID_Treino, Nome_Treino, Imagem_Treino, Musculos_Alvos,
                        Musculos_Alvos as Musculos_Relacionados
                        FROM Planos_Treino 
                        WHERE Musculos_Alvos LIKE '%Cárdio%' 
                           OR Musculos_Alvos LIKE '%cardio%'
                           OR Musculos_Alvos LIKE '%cardiovascular%'
                           OR Nome_Treino LIKE '%cardio%' 
                           OR Nome_Treino LIKE '%corrida%'
                           OR Nome_Treino LIKE '%HIIT%' 
                           OR Nome_Treino LIKE '%cicl%'
                           OR Nome_Treino LIKE '%aeróbico%'
                        ORDER BY Data_Criacao DESC";
                $result = $conn->query($sql);
            }
            
            if ($result && $result->num_rows > 0):
                while($row = $result->fetch_assoc()):
                    // Usar os músculos relacionados se disponíveis, senão usar Musculos_Alvos
                    $musculos_display = !empty($row['Musculos_Relacionados']) ? $row['Musculos_Relacionados'] : $row['Musculos_Alvos'];
                    $musculos = !empty($musculos_display) ? explode(',', $musculos_display) : ['Cárdio'];
                    
                    // Verificar se o treino está nos favoritos
                    $is_favorito = isFavorito($conn, $user_id, $row['ID_Treino']);
            ?>
                    <div class="treino-card">
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
                                    <i class="fas fa-heartbeat fa-3x"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-3 header-font"><?= htmlspecialchars($row["Nome_Treino"]) ?></h3>
                            
                            <div class="mb-4">
                                <strong class="text-gray-700">Tipo de Treino:</strong><br>
                                <div class="mt-2">
                                    <?php foreach($musculos as $musculo): ?>
                                        <span class="musculos-badge text-white"><?= htmlspecialchars(trim($musculo)) ?></span>
                                    <?php endforeach; ?>
                                </div>
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
                    <div class="bg-orange-50 border border-orange-200 text-orange-800 p-6 rounded-lg text-center">
                        <i class="fas fa-heartbeat text-3xl mb-4"></i>
                        <h3 class="text-xl font-bold mb-2">Nenhum treino de cardio encontrado</h3>
                        <p>Certifique-se de que existem treinos com o músculo-alvo "Cárdio" na base de dados.</p>
                        <div class="mt-4 text-sm text-orange-600">
                            <p><strong>Dica:</strong> Verifique se:</p>
                            <ul class="list-disc list-inside mt-2">
                                <li>Existe o músculo "Cárdio" na tabela Musculos</li>
                                <li>Os treinos estão associados ao músculo "Cárdio" na tabela Treino_Musculos</li>
                                <li>Ou se o campo Musculos_Alvos contém a palavra "Cárdio"</li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
  </section>
  
  <!-- Call to Action -->
  <section class="py-16 px-6 gradient-bg text-white text-center">
    <div class="container mx-auto">
      <h2 class="text-4xl font-bold mb-6 header-font">MELHORE SUA <span class="text-orange-500">RESISTÊNCIA</span> CARDIOVASCULAR</h2>
      <p class="text-xl mb-8 max-w-2xl mx-auto">Descubra os nossos planos de treino cardio personalizados e inicie sua jornada para uma vida mais saudável</p>
    </div>
  </section>
</main>

<?php
include 'Footer.php';
?>