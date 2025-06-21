<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: Login.php");
  exit();
}

// Conectar ao banco de dados
$host = "localhost";
$utilizador = "root";
$senha = "";
$dbname = "esafit";

$conn = new mysqli($host, $utilizador, $senha, $dbname);

if ($conn->connect_error) {
  die("Falha na conexão: " . $conn->connect_error);
}

// Definir charset para UTF-8
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
$page_title = "EsaFit 24 - Treino de Costas";

// Definir estilos adicionais específicos desta página
$additional_styles = '
   /* Estilos específicos para a página de costas */
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
include 'Header.php';
?>

<!-- Main Content -->
<main>
  <!-- Hero Section para Costas -->
  <section class="relative h-[40vh] overflow-hidden" style="background: url('/api/placeholder/1600/600') no-repeat center center; background-size: cover;">
     <div class="absolute inset-0 bg-gradient-to-r from-black to-transparent opacity-70"></div>
     <div class="absolute inset-0 flex flex-col justify-center items-start p-6 md:p-16 text-white container mx-auto">
      <h1 class="text-5xl md:text-6xl font-bold mb-4 header-font">
       TREINO <span class="text-orange-500">COSTAS</span>
      </h1>
      <p class="text-lg md:text-xl max-w-lg mb-8 text-gray-200">
       Fortalece a tua musculatura posterior e melhora a tua postura com os nossos treinos específicos para costas.
      </p>
     </div>
  </section>
  
  <!-- Seção de Grupos Musculares -->
  <section class="py-16 px-6 md:px-16 bg-white">
    <div class="container mx-auto">
      <h2 class="text-4xl font-bold mb-12 text-center header-font">GRUPOS <span class="text-orange-500">MUSCULARES</span> DAS COSTAS</h2>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Músculo 1 -->
        <div class="muscle-card bg-white border border-gray-100 flex flex-col">
          <div class="h-48 overflow-hidden">
            <img src="https://st2.depositphotos.com/6563466/43208/i/450/depositphotos_432083004-stock-photo-human-muscular-system-torso-muscles.jpg" alt="Grande dorsal" class="w-full h-full object-cover transition-transform duration-700 hover:scale-110"/>
          </div>
          <div class="p-5">
            <h3 class="text-xl font-bold mb-2 header-font text-gray-800">GRANDE DORSAL</h3>
            <p class="text-gray-600 text-base">Músculo principal das costas, responsável pela largura da parte superior do corpo.</p>
          </div>
        </div>
        
        <!-- Músculo 2 -->
        <div class="muscle-card bg-white border border-gray-100 flex flex-col">
          <div class="h-48 overflow-hidden">
            <img src="https://cdn.prod.website-files.com/64dd05b33f019f79a7ec8f43/66d9bcbfa7bd15ece435d75f_AD_4nXe9h65OjUPORQ8PlwxcDjjcM_FUr032K3MvqzLQY0zpmbYqwTcBe0xg2TKwsAjvNXUlnBhJES0RwcAA5ig1aS88M3ccqBT01J0mzyxeHgl6rpIVCFwdk3PCI33xwwfNl_wwxMnthnoPCd9UUp2hV5RMy7Vh.jpeg" alt="Trapézio" class="w-full h-full object-cover transition-transform duration-700 hover:scale-110"/>
          </div>
          <div class="p-5">
            <h3 class="text-xl font-bold mb-2 header-font text-gray-800">TRAPÉZIO</h3>
            <p class="text-gray-600 text-base">Músculo em forma de diamante que cobre parte superior das costas e pescoço.</p>
          </div>
        </div>
        
        <!-- Músculo 3 -->
        <div class="muscle-card bg-white border border-gray-100 flex flex-col">
          <div class="h-48 overflow-hidden">
            <img src="https://tucuerpohumano.com/wp-content/uploads/2018/09/2-Romboide-mayor1.jpg" alt="Romboides" class="w-full h-full object-cover transition-transform duration-700 hover:scale-110"/>
          </div>
          <div class="p-5">
            <h3 class="text-xl font-bold mb-2 header-font text-gray-800">ROMBOIDES</h3>
            <p class="text-gray-600 text-base">Músculos que conectam a coluna à escápula, importantes para a postura.</p>
          </div>
        </div>
        
        <!-- Músculo 4 -->
        <div class="muscle-card bg-white border border-gray-100 flex flex-col">
          <div class="h-48 overflow-hidden">
            <img src="https://www.kenhub.com/thumbor/eXoXdx5kKM2FPqn07EvS4P8HRWA=/fit-in/800x1600/filters:watermark(/images/logo_url.png,-10,-10,0):background_color(FFFFFF):format(jpeg)/images/library/14605/yISTLJl7lUrrzd9VJMdxwQ_Musculus_quadratus_lumborum_01.jpg" alt="Lombar" class="w-full h-full object-cover transition-transform duration-700 hover:scale-110"/>
          </div>
          <div class="p-5">
            <h3 class="text-xl font-bold mb-2 header-font text-gray-800">LOMBAR</h3>
            <p class="text-gray-600 text-base">Músculos da parte inferior das costas, cruciais para estabilidade e postura.</p>
          </div>
        </div>
      </div>
      
      <div class="text-center mt-16">
        <a href="#" class="btn-primary inline-block">INICIAR TREINO DE COSTAS</a>
      </div>
    </div>
  </section>
  
  <!-- Seção de Planos de Treino -->
  <section class="py-16 px-6 md:px-16 bg-gray-50">
    <div class="container mx-auto">
        <h2 class="text-4xl font-bold mb-12 text-center header-font">PLANOS DE <span class="text-orange-500">TREINO</span></h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            // Consultar apenas os planos de treino relacionados com costas
            // Verificando se as tabelas de relacionamento existem
            $check_tabela = $conn->query("SHOW TABLES LIKE 'Treino_Musculos'");
            
            if ($check_tabela->num_rows > 0) {
                // Se existir a tabela de relacionamento, usar JOIN com filtro para costas
                $sql = "SELECT pt.ID_Treino, pt.Nome_Treino, pt.Imagem_Treino,
                        GROUP_CONCAT(m.Nome_Musculo SEPARATOR ', ') AS Musculos_Alvos
                        FROM Planos_Treino pt
                        INNER JOIN Treino_Musculos tm ON pt.ID_Treino = tm.ID_Treino
                        INNER JOIN Musculos m ON tm.ID_Musculo = m.ID_Musculo
                        WHERE m.Nome_Musculo LIKE '%costa%' OR m.Nome_Musculo LIKE '%dorsal%' 
                           OR m.Nome_Musculo LIKE '%trapéz%' OR m.Nome_Musculo LIKE '%romboid%'
                           OR m.Nome_Musculo LIKE '%lombar%' OR pt.Nome_Treino LIKE '%costa%'
                           OR pt.Nome_Treino LIKE '%dorsal%' OR pt.Nome_Treino LIKE '%trapéz%'
                        GROUP BY pt.ID_Treino
                        ORDER BY pt.ID_Treino DESC";
            } else {
                // Se não existir, usar apenas a tabela principal com filtro
                $sql = "SELECT ID_Treino, Nome_Treino, Imagem_Treino, Musculos_Alvos 
                        FROM Planos_Treino 
                        WHERE Nome_Treino LIKE '%costa%' OR Nome_Treino LIKE '%dorsal%'
                           OR Nome_Treino LIKE '%trapéz%' OR Musculos_Alvos LIKE '%costa%' 
                           OR Musculos_Alvos LIKE '%dorsal%' OR Musculos_Alvos LIKE '%trapéz%'
                           OR Musculos_Alvos LIKE '%romboid%' OR Musculos_Alvos LIKE '%lombar%'
                        ORDER BY ID_Treino DESC";
            }
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0):
                while($row = $result->fetch_assoc()):
                    // Dividir músculos alvos separados por vírgula
                    $musculos = !empty($row['Musculos_Alvos']) ? explode(',', $row['Musculos_Alvos']) : [];
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
                                    <i class="fas fa-dumbbell fa-3x"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-3 header-font"><?= htmlspecialchars($row["Nome_Treino"]) ?></h3>
                            
                            <div class="mb-4">
                                <strong class="text-gray-700">Tipo de Treino:</strong><br>
                                <?php if (!empty($musculos) && $musculos[0] !== ""): ?>
                                    <div class="mt-2">
                                        <?php foreach($musculos as $musculo): ?>
                                            <span class="musculos-badge text-white"><?= htmlspecialchars(trim($musculo)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-500 text-sm">Nenhum tipo de treino especificado</span>
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
                    <div class="bg-blue-50 border border-blue-200 text-blue-800 p-6 rounded-lg text-center">
                        <i class="fas fa-info-circle text-3xl mb-4"></i>
                        <p class="text-lg">Nenhum plano de treino de costas registado ainda.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
  </section>
  
  <!-- Call to Action -->
  <section class="py-16 px-6 gradient-bg text-white text-center">
    <div class="container mx-auto">
      <h2 class="text-4xl font-bold mb-6 header-font">CONSTRUA UMAS <span class="text-orange-500">COSTAS</span> FORTES</h2>
      <p class="text-xl mb-8 max-w-2xl mx-auto">Descubra os nossos planos de treino personalizados para costas e obtenha resultados mais rapidamente</p>
    </div>
  </section>
</main>

<?php
include 'Footer.php';
?>