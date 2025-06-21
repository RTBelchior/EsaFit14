<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esafit";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: Receitas.php');
    exit;
}

// Obter ID do plano a ser visualizado
$id_plano = intval($_GET['id']);

// Buscar informações do plano de alimentação
$stmt = $conn->prepare("SELECT * FROM Planos_Alimentacao WHERE ID_Alimentacao = ?");
$stmt->bind_param("i", $id_plano);
$stmt->execute();
$result = $stmt->get_result();

// Verificar se o plano existe
if ($result->num_rows === 0) {
    header('Location: Receitas.php');
    exit;
}

// Obter dados do plano
$plano = $result->fetch_assoc();

// Definir título da página
$page_title = htmlspecialchars($plano['Nome_Alimentacao']) . " - EsaFit 24";

// Definir estilos específicos desta página
$additional_styles = '
    .plano-header {
        position: relative;
        background: linear-gradient(135deg, #1a365d, #2d5a87);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .plano-imagem {
        width: 100%;
        height: 400px;
        object-fit: cover;
    }
    
    .sem-imagem {
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        color: #6c757d;
    }
    
    .info-nutricional {
        background: linear-gradient(135deg, #fff, #f8f9fa);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
    }
    
    .info-item {
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background-color: #fff;
        border-radius: 8px;
        border-left: 4px solid #ff5400;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .info-item:last-child {
        margin-bottom: 0;
    }
    
    .secao {
        background: linear-gradient(135deg, #fff, #f8f9fa);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
    }
    
    .ingredientes-lista {
        white-space: pre-line;
        line-height: 1.8;
        background-color: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        border-left: 4px solid #28a745;
    }
    
    .preparacao-texto {
        white-space: pre-line;
        line-height: 1.8;
        background-color: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }
    
    .breadcrumb {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 8px;
        padding: 1rem 1.5rem;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: "→";
        color: #ff5400;
        font-weight: bold;
    }
    
    .btn-voltar {
        background: linear-gradient(135deg, #1a365d, #2d5a87);
        color: white;
        padding: 12px 30px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 12px rgba(26, 54, 93, 0.3);
    }
    
    .btn-voltar:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(26, 54, 93, 0.4);
        color: white;
        text-decoration: none;
    }
    
    .nutrition-badge {
        background: linear-gradient(135deg, #ff5400, #ff8533);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 1.1rem;
    }
    
    h1 {
        color: #1a365d;
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    h3 {
        color: #1a365d;
        font-weight: 700;
    }
    
    .container {
        max-width: 1200px;
    }
';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a365d;
            --secondary: #ff5400;
        }
        <?php echo $additional_styles; ?>
    </style>
</head>
<body class="bg-light">

<?php include 'Header.php'; ?>

<main>
    <div class="container py-5">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="Receitas.php" class="text-decoration-none" style="color: var(--secondary);">
                        <i class="fas fa-utensils me-2"></i>Planos Alimentares
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php
                        if (!empty($plano["Nome_Alimentacao"])) {
                            $nome_alimentacao_limpa = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $plano["Nome_Alimentacao"]);
                            echo htmlspecialchars($nome_alimentacao_limpa);
                        } else {
                            echo 'Plano sem nome';
                        }
                    ?>    
                </li>
            </ol>
        </nav>
        
        <!-- Header do Plano -->
        <div class="plano-header">
            <?php if (!empty($plano['Imagem_Alimentacao'])): ?>
                <img src="<?= htmlspecialchars($plano['Imagem_Alimentacao']) ?>" alt="<?= htmlspecialchars($plano['Nome_Alimentacao']) ?>" class="plano-imagem">
            <?php else: ?>
                <div class="sem-imagem">
                    <i class="fas fa-utensils fa-5x"></i>
                </div>
            <?php endif; ?>
            
            <!-- Overlay com informação de calorias -->
            <?php if (!is_null($plano['Energia'])): ?>
                <div class="position-absolute top-0 end-0 m-4">
                    <span class="nutrition-badge">
                        <i class="fas fa-fire-alt me-2"></i><?= number_format($plano['Energia'], 0) ?> KCAL
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Título do Plano -->
        <h1 class="mb-4 text-center">
            <?php
                if (!empty($plano["Nome_Alimentacao"])) {
                    $nome_alimentacao_limpa = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $plano["Nome_Alimentacao"]);
                    echo nl2br(htmlspecialchars($nome_alimentacao_limpa));
                } else {
                    echo 'Plano Alimentar';
                }
            ?>  
        </h1>
        
        <div class="row">
            <!-- Conteúdo Principal -->
            <div class="col-lg-8">
                <!-- Seção de Ingredientes -->
                <div class="secao">
                    <h3><i class="fas fa-shopping-basket me-3" style="color: var(--secondary);"></i>Ingredientes</h3>
                    <div class="ingredientes-lista mt-3">
                        <?php
                            if (!empty($plano["Ingredientes"])) {
                                $ingredientes_limpa = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $plano["Ingredientes"]);
                                echo nl2br(htmlspecialchars($ingredientes_limpa));
                            } else {
                                echo '<p class="text-muted"><i class="fas fa-info-circle me-2"></i>Nenhum ingrediente foi adicionado.</p>';
                            }
                        ?> 
                    </div>
                </div>
                
                <!-- Seção de Modo de Preparo -->
                <div class="secao">
                    <h3><i class="fas fa-blender me-3" style="color: var(--secondary);"></i>Modo de Preparo</h3>
                    <div class="preparacao-texto mt-3">
                        <?php
                            if (!empty($plano["Preparacao"])) {
                                $preparacao_limpa = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $plano["Preparacao"]);
                                echo nl2br(htmlspecialchars($preparacao_limpa));
                            } else {
                                echo '<p class="text-muted"><i class="fas fa-info-circle me-2"></i>Nenhuma preparação foi adicionada.</p>';
                            }
                        ?> 
                    </div>
                </div>
            </div>
            
            <!-- Sidebar com Informações Nutricionais -->
            <div class="col-lg-4">
                <!-- Informações Nutricionais -->
                <div class="info-nutricional">
                    <h3 class="mb-4 text-center">
                        <i class="fas fa-chart-pie me-2" style="color: var(--secondary);"></i>Informação Nutricional
                    </h3>
                    
                    <?php if (!is_null($plano['Energia'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-bolt me-2 text-warning"></i>Energia:</span>
                        <strong><?= number_format($plano['Energia'], 1) ?> kcal</strong>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!is_null($plano['Proteinas'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-dumbbell me-2 text-danger"></i>Proteínas:</span>
                        <strong><?= number_format($plano['Proteinas'], 1) ?> g</strong>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!is_null($plano['Gorduras'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-oil-can me-2 text-info"></i>Gorduras Totais:</span>
                        <strong><?= number_format($plano['Gorduras'], 1) ?> g</strong>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!is_null($plano['Gorduras_Saturadas'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-oil-can me-2 text-secondary"></i>Gorduras Saturadas:</span>
                        <strong><?= number_format($plano['Gorduras_Saturadas'], 1) ?> g</strong>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!is_null($plano['Hidratos_Carbono'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-bread-slice me-2 text-primary"></i>Hidratos de Carbono:</span>
                        <strong><?= number_format($plano['Hidratos_Carbono'], 1) ?> g</strong>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!is_null($plano['Hidratos_Acucares'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-candy-cane me-2 text-success"></i>dos quais Açúcares:</span>
                        <strong><?= number_format($plano['Hidratos_Acucares'], 1) ?> g</strong>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!is_null($plano['Fibras'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-seedling me-2 text-success"></i>Fibras:</span>
                        <strong><?= number_format($plano['Fibras'], 1) ?> g</strong>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Botão de Voltar -->
                <div class="text-center">
                    <a href="Receitas.php" class="btn-voltar">
                        <i class="fas fa-arrow-left me-2"></i>Voltar aos Planos
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'Footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>