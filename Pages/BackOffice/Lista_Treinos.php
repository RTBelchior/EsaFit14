<?php
$page_title = "Planos de Treino - EsaFit 24";

include 'Header_Admin.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esafit";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


// Processar exclusão de treino
$mensagem = "";
if (isset($_POST['excluir']) && isset($_POST['id'])) {
    $id_excluir = intval($_POST['id']);
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Primeiro, buscar informações do treino para excluir a imagem, se existir
        $stmt = $conn->prepare("SELECT Imagem_Treino FROM Planos_Treino WHERE ID_Treino = ?");
        $stmt->bind_param("i", $id_excluir);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Se houver uma imagem, excluí-la do sistema de arquivos
            if (!empty($row['Imagem_Treino'])) {
                // Verificar se é um caminho de arquivo local ou uma URL
                $imagem_path = $row['Imagem_Treino'];
                // Remover possíveis parâmetros de URL que possam causar problemas
                $imagem_path = preg_replace('/\?.*/', '', $imagem_path);
                
                // Verificar se o arquivo existe
                if (file_exists($imagem_path) && is_file($imagem_path)) {
                    unlink($imagem_path);
                }
            }
        }
        
        // Excluir relações na tabela Treino_Musculos
        $stmt = $conn->prepare("DELETE FROM Treino_Musculos WHERE ID_Treino = ?");
        $stmt->bind_param("i", $id_excluir);
        $stmt->execute();
        
        // Excluir o treino
        $stmt = $conn->prepare("DELETE FROM Planos_Treino WHERE ID_Treino = ?");
        $stmt->bind_param("i", $id_excluir);
        $stmt->execute();
        
        // Commit da transação
        $conn->commit();
        
        $mensagem = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                      Treino excluído com sucesso!
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                      Erro ao excluir treino: ' . $e->getMessage() . '
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Planos de Treino</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-treino {
            transition: transform 0.3s;
            height: 100%;
        }
        .card-treino:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .musculos-badge {
            display: inline-block;
            margin: 5px;
            font-size: 0.8rem;
        }
        .card-img-top {
            height: 220px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }

        .img-container {
            height: 210px;
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
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Planos de Treino</h1>
            <a href="Formulario-Planos-Treino.html" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Treino
            </a>
        </div>
        
        <?php 
        // Exibir mensagem de sucesso do parâmetro URL (para redirecionamentos)
        if (isset($_GET['mensagem']) && $_GET['mensagem'] == 'sucesso'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Treino adicionado com sucesso!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php 
        // Exibir mensagem de operações realizadas nesta página
        echo $mensagem; 
        ?>
        
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            // Consultar todos os planos de treino ordenados pelo ID (mais recentes primeiro)
            // Verificando se as tabelas de relacionamento existem
            $check_tabela = $conn->query("SHOW TABLES LIKE 'Treino_Musculos'");
            
            if ($check_tabela->num_rows > 0) {
                // Se existir a tabela de relacionamento, usar JOIN
                $sql = "SELECT pt.ID_Treino, pt.Nome_Treino, pt.Imagem_Treino,
                        GROUP_CONCAT(m.Nome_Musculo SEPARATOR ', ') AS Musculos_Alvos
                        FROM Planos_Treino pt
                        LEFT JOIN Treino_Musculos tm ON pt.ID_Treino = tm.ID_Treino
                        LEFT JOIN Musculos m ON tm.ID_Musculo = m.ID_Musculo
                        GROUP BY pt.ID_Treino
                        ORDER BY pt.ID_Treino DESC";
            } else {
                // Se não existir, usar apenas a tabela principal
                $sql = "SELECT ID_Treino, Nome_Treino, Imagem_Treino, Musculos_Alvos 
                        FROM Planos_Treino 
                        ORDER BY ID_Treino DESC";
            }
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0):
                while($row = $result->fetch_assoc()):
                    // Dividir músculos alvos separados por vírgula
                    $musculos = !empty($row['Musculos_Alvos']) ? explode(',', $row['Musculos_Alvos']) : [];
            ?>
                    <div class="col">
                        <div class="card card-treino">
                            <div class="img-container">
                                <?php if (!empty($row["Imagem_Treino"])): ?>
                                    <img src="<?= htmlspecialchars($row["Imagem_Treino"]) ?>" class="card-img-top" alt="<?= htmlspecialchars($row["Nome_Treino"]) ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-dumbbell fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row["Nome_Treino"]) ?></h5>
                                
                                <p class="card-text">
                                    <strong>Tipo de Treino:</strong><br>
                                    <?php if (!empty($musculos) && $musculos[0] !== ""): ?>
                                        <?php foreach($musculos as $musculo): ?>
                                            <span class="badge bg-secondary musculos-badge"><?= htmlspecialchars(trim($musculo)) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Nenhum tipo de treino especificado</span>
                                    <?php endif; ?>
                                </p>
                                
                                <div class="d-flex justify-content-between mt-3">
                                    <a href="ver_treino.php?id=<?= $row["ID_Treino"] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detalhes
                                    </a>
                                    <div>
                                        <a href="editar_treino.php?id=<?= $row["ID_Treino"] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- Formulário para exclusão -->
                                        <form method="post" style="display:inline-block;">
                                            <input type="hidden" name="id" value="<?= $row["ID_Treino"] ?>">
                                            <button type="submit" name="excluir" class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Tem certeza que deseja excluir este treino?');">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Nenhum plano de treino registado ainda. Clique em "Novo Treino" para adicionar o primeiro!
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>