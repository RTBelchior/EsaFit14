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
    header('Location: Lista_Alimentacao.php');
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
    header('Location: Lista_Alimentacao.php');
    exit;
}

// Obter dados do plano
$plano = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($plano['Nome_Alimentacao']) ?> - Detalhes do Plano de Nutrição</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .plano-header {
            position: relative;
            background-color: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .plano-imagem {
            width: 100%;
            height: 350px;
            object-fit: cover;
        }
        
        .sem-imagem {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e9ecef;
            color: #6c757d;
        }
        
        .info-nutricional {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .info-item {
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #dee2e6;
            padding-bottom: 0.5rem;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .secao {
            background-color: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .ingredientes-lista {
            white-space: pre-line;
            line-height: 1.7;
        }
        
        .preparacao-texto {
            white-space: pre-line;
            line-height: 1.7;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="Lista_Alimentacao.php">Planos de Nutrição</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php
                        if (!empty($plano["Nome_Alimentacao"])) {
                            $nome_alimentacao_limpa = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $plano["Nome_Alimentacao"]);
                            echo nl2br(htmlspecialchars($nome_alimentacao_limpa));
                        } else {
                        echo '<p class="text-muted">Nenhum nome foi adicionado.</p>';
                        }
                    ?>    
                </li>
            </ol>
        </nav>
        
        <div class="plano-header">
            <?php if (!empty($plano['Imagem_Alimentacao'])): ?>
                <img src="<?= htmlspecialchars($plano['Imagem_Alimentacao']) ?>" alt="<?= htmlspecialchars($plano['Nome_Alimentacao']) ?>" class="plano-imagem">
            <?php else: ?>
                <div class="sem-imagem">
                    <i class="fas fa-utensils fa-5x"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <h1 class="mb-4">
            <?php
                if (!empty($plano["Nome_Alimentacao"])) {
                    $nome_alimentacao_limpa = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $plano["Nome_Alimentacao"]);
                    echo nl2br(htmlspecialchars($nome_alimentacao_limpa));
                } else {
                    echo '<p class="text-muted">Nenhum nome foi adicionado.</p>';
                }
            ?>  
        </h1>
        
        <div class="row">
            <div class="col-md-8">
                <!-- Seção de Ingredientes -->
                <div class="secao">
                    <h3><i class="fas fa-shopping-basket me-2 text-primary"></i>Ingredientes</h3>
                    <div class="ingredientes-lista mt-3">
                        <?php
                            if (!empty($plano["Ingredientes"])) {
                                $ingredientes_limpa = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $plano["Ingredientes"]);
                                echo nl2br(htmlspecialchars($ingredientes_limpa));
                            } else {
                                echo '<p class="text-muted">Nenhum ingredinte foi adicionado.</p>';
                            }
                        ?> 
                    </div>
                </div>
                
                <!-- Seção de Modo de Preparo -->
                <div class="secao">
                    <h3><i class="fas fa-blender me-2 text-primary"></i>Modo de Preparo</h3>
                    <div class="preparacao-texto mt-3">
                        <?php
                            if (!empty($plano["Preparacao"])) {
                                $preparacao_limpa = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $plano["Preparacao"]);
                                echo nl2br(htmlspecialchars($preparacao_limpa));
                            } else {
                            echo '<p class="text-muted">Nenhuma preparação foi adicionada.</p>';
                            }
                        ?> 
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Informações Nutricionais -->
                <div class="info-nutricional">
                    <h3 class="mb-4"><i class="fas fa-chart-pie me-2 text-primary"></i>Informação Nutricional</h3>
                    
                    <?php if (!is_null($plano['Energia'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-bolt me-2"></i>Energia:</span>
                        <strong><?= number_format($plano['Energia'], 1) ?> kcal</strong>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!is_null($plano['Proteinas'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-dumbbell me-2"></i>Proteínas:</span>
                        <strong><?= number_format($plano['Proteinas'], 1) ?> g</strong>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!is_null($plano['Gorduras'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-oil-can me-2"></i>Gorduras Totais:</span>
                        <strong><?= number_format($plano['Gorduras'], 1) ?> g</strong>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!is_null($plano['Gorduras_Saturadas'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-oil-can me-2"></i>Gorduras Saturadas:</span>
                        <strong><?= number_format($plano['Gorduras_Saturadas'], 1) ?> g</strong>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!is_null($plano['Hidratos_Carbono'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-bread-slice me-2"></i>Hidratos de Carbono:</span>
                        <strong><?= number_format($plano['Hidratos_Carbono'], 1) ?> g</strong>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!is_null($plano['Hidratos_Acucares'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-candy-cane me-2"></i>dos quais Açúcares:</span>
                        <strong><?= number_format($plano['Hidratos_Acucares'], 1) ?> g</strong>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!is_null($plano['Fibras'])): ?>
                    <div class="info-item">
                        <span><i class="fas fa-seedling me-2"></i>Fibras:</span>
                        <strong><?= number_format($plano['Fibras'], 1) ?> g</strong>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Botões de Ação -->
                <div class="d-grid gap-2">
                    <a href="Editar_Nutricao.php?id=<?= $plano['ID_Alimentacao'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Editar Plano
                    </a>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#excluirModal">
                        <i class="fas fa-trash me-2"></i>Excluir Plano
                    </button>
                    <a href="lista_alimentacao.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar para Lista
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Exclusão -->
    <div class="modal fade" id="excluirModal" tabindex="-1" aria-labelledby="excluirModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="excluirModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o plano de nutrição <strong><?= htmlspecialchars($plano['Nome_Alimentacao']) ?></strong>?</p>
                    <p class="text-danger">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="post" action="Lista_Alimentacao.php">
                        <input type="hidden" name="id" value="<?= $plano['ID_Alimentacao'] ?>">
                        <button type="submit" name="excluir" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>