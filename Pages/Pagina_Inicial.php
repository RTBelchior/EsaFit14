<?php
// Definir título da página
$page_title = "EsaFit 24 - Página Inicial";

// Definir estilos adicionais específicos desta página
$additional_styles = '
    /* Hero section overlay */
    .hero-overlay {
        background: linear-gradient(to right, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%);
    }
    
    /* Divider para seções */
    .section-divider {
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, var(--secondary), var(--primary));
        margin: 1rem auto 2rem;
        border-radius: 2px;
    }
';

// Definir scripts adicionais específicos desta página
$additional_scripts = '
<script>
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

// Incluir configuração da base de dados
$host = 'localhost';
$dbname = 'EsaFit';
$username = 'root';
$password = '';

// Consultar os 3 treinos com mais likes
$treinos_populares = [];
try {
    // Criar conexão PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar se a tabela Treino_Musculos existe
    $check_tabela = $pdo->query("SHOW TABLES LIKE 'Treino_Musculos'");
    
    if ($check_tabela->rowCount() > 0) {
        // Se existir a tabela de relacionamento, usar JOIN como na página listar_treinos
        $stmt = $pdo->prepare("
            SELECT 
                pt.ID_Treino,
                pt.Nome_Treino,
                pt.Imagem_Treino,
                pt.Preparacao,
                pt.Execucao,
                pt.Dicas,
                GROUP_CONCAT(m.Nome_Musculo SEPARATOR ', ') AS Musculos_Alvos,
                COUNT(tf.ID_TreinoFav) as total_likes
            FROM Planos_Treino pt
            LEFT JOIN Treino_Musculos tm ON pt.ID_Treino = tm.ID_Treino
            LEFT JOIN Musculos m ON tm.ID_Musculo = m.ID_Musculo
            LEFT JOIN Treino_Favoritos tf ON pt.ID_Treino = tf.ID_Treino
            GROUP BY pt.ID_Treino, pt.Nome_Treino, pt.Imagem_Treino, pt.Preparacao, pt.Execucao, pt.Dicas
            ORDER BY total_likes DESC, pt.ID_Treino ASC
            LIMIT 3
        ");
    } else {
        // Se não existir, usar apenas a tabela principal
        $stmt = $pdo->prepare("
            SELECT 
                pt.ID_Treino,
                pt.Nome_Treino,
                pt.Imagem_Treino,
                pt.Preparacao,
                pt.Execucao,
                pt.Dicas,
                pt.Musculos_Alvos,
                COUNT(tf.ID_TreinoFav) as total_likes
            FROM Planos_Treino pt
            LEFT JOIN Treino_Favoritos tf ON pt.ID_Treino = tf.ID_Treino
            GROUP BY pt.ID_Treino, pt.Nome_Treino, pt.Imagem_Treino, pt.Preparacao, pt.Execucao, pt.Dicas, pt.Musculos_Alvos
            ORDER BY total_likes DESC, pt.ID_Treino ASC
            LIMIT 3
        ");
    }
    
    $stmt->execute();
    $treinos_populares = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fechar conexão
    $pdo = null;
} catch (PDOException $e) {
    // Em caso de erro, definir array vazio e continuar com fallback
    $treinos_populares = [];
    // Opcional: registar erro para debug
    error_log("Erro na consulta de treinos populares: " . $e->getMessage());
}

// Incluir o header
include 'header.php';
?>

<!-- Main Content -->
<main>
    <!-- Hero Section -->
    <section class="relative h-[60vh] md:h-[85vh] overflow-hidden" style="background: url('https://images.unsplash.com/photo-1599058917212-d750089bc07e?q=80&w=1469&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center fixed; background-size: cover;">
        <div class="absolute inset-0 hero-overlay"></div>
        <div class="absolute inset-0 flex flex-col justify-center items-start p-6 md:p-16 text-white container mx-auto">
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold leading-tight slide-in mb-4 header-font">
                O <span class="text-orange-500">EsaFit 24</span>
                <br/>
                CHEGOU A
                <br/>
                PORTUGAL!
            </h1>
            <p class="text-lg md:text-xl max-w-lg slide-in mb-8 text-gray-200">
                Transforma o teu corpo, eleva a tua mente e supera os teus limites com os nossos programas de treino personalizados.
            </p>
            <button class="btn-primary slide-in">
                COMEÇAR AGORA
            </button>
        </div>
    </section>

    <!-- Intro Section -->
    <section class="py-16 px-6 md:px-16 container mx-auto">
        <h2 class="text-4xl md:text-5xl font-bold mb-2 text-center scale-up header-font">
            COMECE SUA TRANSFORMAÇÃO
        </h2>
        <div class="section-divider scale-up"></div>
        <p class="text-xl md:text-2xl leading-relaxed scale-up text-center max-w-4xl mx-auto mt-6 text-gray-700">
            Queres transformar os teus treinos em algo verdadeiramente <span class="text-orange-500 font-semibold">extraordinário</span>? No
            <strong class="text-blue-700">EsaFit 24</strong>, tu acabas de encontrar o lugar certo. Nossa equipa de instrutores está comprometida em oferecer-te uma experiência de treino inigualável.
        </p>
    </section>

    <!-- Treinos Mais Populares -->
    <section class="py-16 px-6 md:px-16 bg-gradient-to-b from-gray-100 to-white">
        <div class="container mx-auto">
            <h2 class="text-4xl md:text-5xl font-bold mb-2 text-center scale-up header-font">TREINOS MAIS POPULARES</h2>
            <div class="section-divider scale-up"></div>
            <p class="text-center text-gray-700 max-w-2xl mx-auto mb-12 scale-up">Os treinos preferidos da nossa comunidade! Descobre os exercícios que estão conquistando mais corações e transformando mais vidas.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php if (!empty($treinos_populares)): ?>
                    <?php foreach ($treinos_populares as $index => $treino): ?>
                        <div class="card bg-white scale-up">
                            <div class="h-70 overflow-hidden relative">
                                <?php if (!empty($treino['Imagem_Treino'])): ?>
                                    <!-- Usar o mesmo método do listar_treinos.php -->
                                    <img src="<?= htmlspecialchars($treino['Imagem_Treino']) ?>" 
                                         alt="<?php echo htmlspecialchars($treino['Nome_Treino']); ?>" 
                                         class="w-full h-full object-cover transition-transform duration-700 hover:scale-110"/>
                                <?php else: ?>
                                    <!-- Container para ícone quando não há imagem, igual ao listar_treinos -->
                                    <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                                        <i class="fas fa-dumbbell text-gray-400 text-4xl"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-8">
                                <h3 class="text-2xl font-bold mb-3 header-font"><?php echo htmlspecialchars($treino['Nome_Treino']); ?></h3>
                                
                                <div class="mb-4">
                                    <span class="text-sm font-semibold text-gray-600">Tipo de Treino:</span>
                                </div>
                                
                                <!-- Badge com músculos alvo -->
                                <?php if (!empty($treino['Musculos_Alvos'])): ?>
                                    <?php 
                                    // Dividir músculos separados por vírgula e criar badges
                                    $musculos = explode(',', $treino['Musculos_Alvos']);
                                    foreach ($musculos as $musculo): 
                                        $musculo = trim($musculo);
                                        if (!empty($musculo)):
                                    ?>
                                        <span class="inline-block bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-bold mb-2 mr-2">
                                            <?php echo htmlspecialchars($musculo); ?>
                                        </span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                <?php else: ?>
                                    <span class="inline-block bg-gray-500 text-white px-3 py-1 rounded-full text-sm font-bold mb-2">
                                        Treino Completo
                                    </span>
                                <?php endif; ?>
                                
                                <!-- Número de likes -->
                                <div class="flex items-center justify-between mb-4 mt-4">
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-heart text-red-500 mr-2"></i>
                                        <span class="font-medium"><?php echo $treino['total_likes']; ?> likes</span>
                                    </div>
                                </div>
                                
                                <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg transition-colors flex items-center justify-center">
                                    <i class="fas fa-eye mr-2"></i>
                                    VER DETALHES
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback caso não haja treinos na base de dados -->
                    <div class="col-span-full text-center py-12">
                        <div class="text-gray-400 text-6xl mb-4">
                            <i class="fas fa-dumbbell"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-600 mb-2">Ainda não há treinos disponíveis</h3>
                        <p class="text-gray-500">Os treinos mais populares aparecerão aqui em breve!</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-12">
                <a href="SUBMENU_Braços.php" class="btn-primary inline-block">VER TODOS OS TREINOS</a>
            </div>
        </div>
    </section>
</main>

<?php
// Incluir o footer
include 'footer.php';
?>