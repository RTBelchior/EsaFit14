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

// Verificar se o ID foi passado na URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: Lista_Treinos.php?error=id_invalido");
    exit();
}

$id_treino = intval($_GET['id']);

// Obter detalhes do treino
$stmt = $conn->prepare("SELECT * FROM Planos_Treino WHERE ID_Treino = ?");
$stmt->bind_param("i", $id_treino);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: Lista_Treinos.php?error=treino_nao_encontrado");
    exit();
}

$treino = $result->fetch_assoc();

// Obter músculos relacionados (para estrutura normalizada)
$musculos = [];
try {
    $musculos_query = "SELECT m.Nome_Musculo 
                      FROM Musculos m
                      JOIN Treino_Musculos tm ON m.ID_Musculo = tm.ID_Musculo
                      WHERE tm.ID_Treino = ?";
    $stmt = $conn->prepare($musculos_query);
    $stmt->bind_param("i", $id_treino);
    $stmt->execute();
    $musculos_result = $stmt->get_result();
    
    while($musculo_row = $musculos_result->fetch_assoc()) {
        $musculos[] = $musculo_row['Nome_Musculo'];
    }
} catch (Exception $e) {
    // Caso esteja usando a estrutura original, obtém os músculos da coluna Musculos_Alvos
    if (isset($treino['Musculos_Alvos']) && !empty($treino['Musculos_Alvos'])) {
        $musculos = explode(',', $treino['Musculos_Alvos']);
    }
}

// Determinar se a imagem é um GIF
$is_gif = false;
if (!empty($treino["Imagem_Treino"])) {
    $file_extension = strtolower(pathinfo($treino["Imagem_Treino"], PATHINFO_EXTENSION));
    $is_gif = ($file_extension === 'gif');
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Treino - <?= htmlspecialchars($treino["Nome_Treino"]) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .treino-header {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .img-treino {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .musculos-badge {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .section-title {
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 10px;
            margin-top: 30px;
            margin-bottom: 15px;
            color: #495057;
        }
        
        .content-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .btn-back {
            margin-right: 10px;
        }
        
        .text-content {
            white-space: pre-line;
        }
    </style>
</head>
<body>
    <div class="treino-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1><?= htmlspecialchars($treino["Nome_Treino"]) ?></h1>
                <div>
                    <a href="Lista_Treinos.php" class="btn btn-outline-secondary btn-back">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <a href="Editar_Treino.php?id=<?= $id_treino ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <div class="col-md-8">
                <div class="content-section">
                    <h3 class="section-title">Preparação</h3>
                    <div class="text-content">
                        <?php
                        if (!empty($treino["Preparacao"])) {
                            $preparacao_limpa = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $treino["Preparacao"]);
                            echo nl2br(htmlspecialchars($preparacao_limpa));
                        } else {
                        echo '<p class="text-muted">Nenhuma informação de preparação foi adicionada.</p>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="content-section">
                    <h3 class="section-title">Execução</h3>
                    <div class="text-content">
                        <?php
                        if (!empty($treino["Execucao"])) {
                            $execucao_limpa = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $treino["Execucao"]);
                            echo nl2br(htmlspecialchars($execucao_limpa));
                        } else {
                            echo '<p class="text-muted">Nenhuma informação de Execução foi adicionada.</p>';
                        }
                        ?>
                    </div>
                </div>

                <div class="content-section">
                    <h3 class="section-title">Dicas</h3>
                    <div class="text-content">
                        <?php
                        if (!empty($treino["Dicas"])) {
                            $dicas_limpa = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $treino["Dicas"]);
                            echo nl2br(htmlspecialchars($dicas_limpa));
                        } else {
                            echo '<p class="text-muted">Nenhuma Dica foi adicionada.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="content-section">
                    <h3 class="section-title">Tipo de Treino</h3>
                    <?php if (!empty($musculos)): ?>
                        <div class="d-flex flex-wrap">
                            <?php foreach($musculos as $musculo): ?>
                                <span class="badge bg-primary musculos-badge"><?= htmlspecialchars(trim($musculo)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Nenhum músculo especificado.</p>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($treino["Imagem_Treino"])): ?>
                    <div class="content-section">
                        <h3 class="section-title">
                            <?= $is_gif ? 'Demonstração do Exercício' : 'Imagem do Exercício' ?>
                        </h3>
                        <img src="Get_Imagem.php?id=<?= $id_treino ?>" alt="<?= htmlspecialchars($treino["Nome_Treino"]) ?>" class="img-treino">
                    </div>
                <?php endif; ?>
                
                <?php if (isset($treino["Data_Criacao"])): ?>
                    <div class="content-section">
                        <h3 class="section-title">Informações Adicionais</h3>
                        <p><strong>Data de criação:</strong> <?= date('d/m/Y H:i', strtotime($treino["Data_Criacao"])) ?></p>
                        <?php if (isset($treino["Data_Atualizacao"]) && !empty($treino["Data_Atualizacao"])): ?>
                            <p><strong>Última atualização:</strong> <?= date('d/m/Y H:i', strtotime($treino["Data_Atualizacao"])) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>