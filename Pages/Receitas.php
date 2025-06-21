<?php
// Configuração da conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esafit";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Definir charset para UTF-8
$conn->set_charset("utf8mb4");

// Definir título da página
$page_title = "EsaFit 24 - Planos Alimentares";

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
    
    /* Filtros de receitas */
    .filter-btn {
        transition: all 0.3s ease;
    }
    
    .filter-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .filter-btn.active {
        background-color: var(--secondary);
        color: white;
        box-shadow: 0 4px 15px rgba(255, 84, 0, 0.3);
    }
    
    /* Search input focus */
    .search-input:focus {
        border-color: var(--secondary);
        box-shadow: 0 0 0 3px rgba(255, 84, 0, 0.1);
    }
    
    /* Recipe cards */
    .recipe-card {
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
    }
    
    .recipe-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    .recipe-img {
        height: 250px;
        object-fit: cover;
    }
    
    .recipe-img-container {
        height: 250px;
        overflow: hidden;
        position: relative;
        background-color: #f8f9fa;
    }
    
    .no-image {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        color: #6c757d;
    }
    
    .nutri-badge {
        background-color: #e9f5db;
        color: #2c6e49;
        border: 1px solid #b7e4c7;
        display: inline-block;
        margin: 2px;
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 12px;
    }
    
    .scale-up {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease;
    }
    
    .scale-up.active {
        opacity: 1;
        transform: translateY(0);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #ff5400, #ff8533);
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 84, 0, 0.3);
    }
    
    .header-font {
        font-family: "Arial Black", sans-serif;
    }
';

// Definir scripts adicionais específicos desta página
$additional_scripts = '
<script>
    // Animation for elements when they scroll into view
    document.addEventListener("DOMContentLoaded", function() {
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
        
        // Filter functionality
        const filterButtons = document.querySelectorAll(".filter-btn");
        const recipeCards = document.querySelectorAll(".recipe-card");
        
        filterButtons.forEach(button => {
            button.addEventListener("click", function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove("active"));
                
                // Add active class to clicked button
                this.classList.add("active");
                
                const filterValue = this.textContent.trim();
                
                // Filter recipes based on nutritional content
                recipeCards.forEach(card => {
                    let shouldShow = true;
                    
                    if (filterValue !== "Todos") {
                        const energiaElement = card.querySelector(".energia-value");
                        const proteinaElement = card.querySelector(".proteina-value");
                        const carboidratoElement = card.querySelector(".carboidrato-value");
                        
                        const energia = energiaElement ? parseFloat(energiaElement.textContent) : 0;
                        const proteina = proteinaElement ? parseFloat(proteinaElement.textContent) : 0;
                        const carboidrato = carboidratoElement ? parseFloat(carboidratoElement.textContent) : 0;
                        
                        switch(filterValue) {
                            case "Alto Proteína":
                                shouldShow = proteina >= 20;
                                break;
                            case "Baixo Carboidrato":
                                shouldShow = carboidrato <= 20;
                                break;
                            case "Alta Energia":
                                shouldShow = energia >= 400;
                                break;
                            case "Baixa Caloria":
                                shouldShow = energia <= 300;
                                break;
                        }
                    }
                    
                    card.style.display = shouldShow ? "block" : "none";
                });
            });
        });
        
        // Search functionality
        const searchInput = document.querySelector(".search-input");
        if (searchInput) {
            searchInput.addEventListener("input", function() {
                const searchTerm = this.value.toLowerCase();
                
                recipeCards.forEach(card => {
                    const title = card.querySelector("h3").textContent.toLowerCase();
                    const shouldShow = title.includes(searchTerm);
                    card.style.display = shouldShow ? "block" : "none";
                });
            });
        }
    });
</script>
';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a365d;
            --secondary: #ff5400;
        }
        <?php echo $additional_styles; ?>
    </style>
</head>
<body>

<?php
// Incluir o header
include 'header.php';
?>

<!-- Main Content -->
<main>
    <!-- Hero Section para Receitas -->
    <section class="relative h-[50vh] md:h-[60vh] overflow-hidden" style="background: url('https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center; background-size: cover;">
        <div class="absolute inset-0 hero-overlay"></div>
        <div class="absolute inset-0 flex flex-col justify-center items-start p-6 md:p-16 text-white container mx-auto">
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-4 header-font">
                PLANOS <span class="text-orange-500">ALIMENTARES</span>
            </h1>
            <p class="text-lg md:text-xl max-w-lg mb-8 text-gray-200">
                Descubra planos alimentares nutritivos e deliciosos, especialmente desenvolvidos para complementar seu treino e estilo de vida ativo.
            </p>
        </div>
    </section>
    
    <!-- Filtros de Receitas -->
    <section class="bg-white py-8 shadow-md">
        <div class="container mx-auto px-6 md:px-10">
            <div class="flex flex-wrap items-center justify-center md:justify-between gap-4">
                <div class="flex flex-wrap gap-2 justify-center">
                    <button class="filter-btn active px-4 py-2 bg-gray-200 text-gray-700 rounded-full font-semibold hover:bg-gray-300 transition">Todos</button>
                    <button class="filter-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-full font-semibold hover:bg-gray-300 transition">Alto Proteína</button>
                    <button class="filter-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-full font-semibold hover:bg-gray-300 transition">Baixo Carboidrato</button>
                    <button class="filter-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-full font-semibold hover:bg-gray-300 transition">Alta Energia</button>
                    <button class="filter-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-full font-semibold hover:bg-gray-300 transition">Baixa Caloria</button>
                </div>
                <div class="relative w-full md:w-60">
                    <input type="text" placeholder="Pesquisar Receitas" class="search-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all">
                    <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Grid de Planos Alimentares da Base de Dados -->
    <section class="py-16 px-6 md:px-16 bg-gradient-to-b from-gray-100 to-white">
        <div class="container mx-auto">
            <h2 class="text-4xl md:text-5xl font-bold mb-2 text-center scale-up header-font">NOSSOS <span class="text-orange-500">PLANOS ALIMENTARES</span></h2>
            <div class="section-divider scale-up"></div>
            <p class="text-center text-gray-700 max-w-2xl mx-auto mb-12 scale-up">Descubra os nossos planos alimentares saudáveis e balanceados que irão complementar perfeitamente o seu programa de treino e objetivos de fitness.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                // Consultar todos os planos alimentares ordenados pelo ID (mais recentes primeiro)
                $sql = "SELECT ID_Alimentacao, Nome_Alimentacao, Imagem_Alimentacao, 
                        Preparacao, Energia, Gorduras, Gorduras_Saturadas,
                        Hidratos_Carbono, Hidratos_Acucares, Fibras, Proteinas
                        FROM Planos_Alimentacao 
                        ORDER BY ID_Alimentacao DESC";
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0):
                    while($row = $result->fetch_assoc()):
                ?>
                        <div class="bg-white rounded-lg shadow-lg recipe-card scale-up">
                            <div class="recipe-img-container rounded-t-lg">
                                <?php if (!empty($row["Imagem_Alimentacao"])): ?>
                                    <img src="<?= htmlspecialchars($row["Imagem_Alimentacao"]) ?>" class="recipe-img w-full" alt="<?= htmlspecialchars($row["Nome_Alimentacao"]) ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-utensils fa-4x text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Badge de energia -->
                                <?php if (!is_null($row["Energia"])): ?>
                                    <div class="absolute top-4 right-4 bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                        <?= number_format($row["Energia"], 0) ?> KCAL
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-6">
                                <div class="text-orange-500 text-3xl mb-3">
                                    <i class="fas fa-leaf"></i>
                                </div>
                                <h3 class="text-xl font-bold mb-4 header-font"><?= strtoupper(htmlspecialchars($row["Nome_Alimentacao"])) ?></h3>
                                
                                <!-- Informações nutricionais -->
                                <div class="flex items-center text-sm text-gray-500 mb-6 flex-wrap gap-2">
                                    <?php if (!is_null($row["Energia"])): ?>
                                        <span class="nutri-badge"><i class="fas fa-fire-alt mr-1"></i> <span class="energia-value"><?= number_format($row["Energia"], 0) ?></span> kcal</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!is_null($row["Proteinas"])): ?>
                                        <span class="nutri-badge"><i class="fas fa-dumbbell mr-1"></i> <span class="proteina-value"><?= number_format($row["Proteinas"], 1) ?></span>g prot</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!is_null($row["Hidratos_Carbono"])): ?>
                                        <span class="nutri-badge"><i class="fas fa-seedling mr-1"></i> <span class="carboidrato-value"><?= number_format($row["Hidratos_Carbono"], 1) ?></span>g carb</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!is_null($row["Gorduras"])): ?>
                                        <span class="nutri-badge"><i class="fas fa-tint mr-1"></i> <?= number_format($row["Gorduras"], 1) ?>g gord</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!is_null($row["Fibras"])): ?>
                                        <span class="nutri-badge"><i class="fas fa-wheat mr-1"></i> <?= number_format($row["Fibras"], 1) ?>g fibras</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Botão de visualização -->
                                <div class="text-center">
                                    <a href="abrir_alimentaçao.php?id=<?= $row["ID_Alimentacao"] ?>" class="inline-flex items-center font-bold text-blue-700 hover:text-orange-500 transition-colors">
                                        Ver Plano <i class="fas fa-long-arrow-alt-right ml-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full">
                        <?php if ($result === false): ?>
                            <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg text-center">
                                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                <p class="text-lg">Erro ao carregar planos alimentares:</p>
                                <p class="text-sm"><?= $conn->error ?></p>
                                <p class="text-sm mt-2">Verifique se a tabela 'Planos_Alimentacao' existe na base de dados.</p>
                            </div>
                        <?php else: ?>
                            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-6 py-4 rounded-lg text-center">
                                <i class="fas fa-info-circle text-2xl mb-2"></i>
                                <p class="text-lg">Nenhum plano alimentar registado ainda.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Seção de Dicas Nutricionais -->
    <section class="py-16 px-6 md:px-16 bg-white">
        <div class="container mx-auto">
            <h2 class="text-4xl md:text-5xl font-bold mb-2 text-center scale-up header-font">DICAS <span class="text-orange-500">NUTRICIONAIS</span></h2>
            <div class="section-divider scale-up"></div>
            <p class="text-center text-gray-700 max-w-2xl mx-auto mb-12 scale-up">Aprenda sobre nutrição esportiva e como otimizar sua alimentação para melhores resultados nos treinos.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-8 rounded-lg scale-up">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 header-font">TIMING DE NUTRIÇÃO</h3>
                    <p class="text-gray-700">Saiba quando consumir cada tipo de nutriente para maximizar seus resultados no treino e na recuperação.</p>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-8 rounded-lg scale-up">
                    <div class="text-green-600 text-4xl mb-4">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 header-font">PROTEÍNAS COMPLETAS</h3>
                    <p class="text-gray-700">Descubra as melhores fontes de proteína para construção e manutenção da massa muscular.</p>
                </div>
                
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-8 rounded-lg scale-up">
                    <div class="text-orange-600 text-4xl mb-4">
                        <i class="fas fa-fire-alt"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 header-font">ENERGIA SUSTENTADA</h3>
                    <p class="text-gray-700">Aprenda sobre carboidratos complexos e como manter energia constante durante todo o dia.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php echo $additional_scripts; ?>

<?php
include 'footer.php';
?>

</body>
</html>