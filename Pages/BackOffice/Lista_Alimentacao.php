<?php
$page_title = "Planos de Alimentação - EsaFit 24";

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

// Processar exclusão de plano de alimentação
$mensagem = "";
if (isset($_POST['excluir']) && isset($_POST['id'])) {
    $id_excluir = intval($_POST['id']);
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Primeiro, buscar informações do plano para excluir a imagem, se existir
        $stmt = $conn->prepare("SELECT Imagem_Alimentacao FROM Planos_Alimentacao WHERE ID_Alimentacao = ?");
        $stmt->bind_param("i", $id_excluir);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Se houver uma imagem, excluí-la do sistema de arquivos
            if (!empty($row['Imagem_Alimentacao'])) {
                // Verificar se é um caminho de arquivo local ou uma URL
                $imagem_path = $row['Imagem_Alimentacao'];
                // Remover possíveis parâmetros de URL que possam causar problemas
                $imagem_path = preg_replace('/\?.*/', '', $imagem_path);
                
                // Verificar se o arquivo existe
                if (file_exists($imagem_path) && is_file($imagem_path)) {
                    unlink($imagem_path);
                }
            }
        }
        
        // Excluir o plano de alimentação
        $stmt = $conn->prepare("DELETE FROM Planos_Alimentacao WHERE ID_Alimentacao = ?");
        $stmt->bind_param("i", $id_excluir);
        $stmt->execute();
        
        // Commit da transação
        $conn->commit();
        
        $mensagem = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                      Plano de alimentação excluído com sucesso!
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                      Erro ao excluir plano de alimentação: ' . $e->getMessage() . '
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
    <title>Lista de Planos de Nutrição</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-nutricao {
            transition: transform 0.3s;
            height: 100%;
        }
        .card-nutricao:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .nutri-badge {
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
        .nutri-values {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .nutri-badge {
            background-color: #e9f5db;
            color: #2c6e49;
            border: 1px solid #b7e4c7;
        }
        .card-footer {
            background-color: #fff;
            border-top: 1px solid rgba(0,0,0,.05);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Planos de Nutrição</h1>
            <a href="Formulario-Planos-Nutricao.html" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Plano de Nutrição
            </a>
        </div>
        
        <?php 
        // Exibir mensagem de sucesso do parâmetro URL (para redirecionamentos)
        if (isset($_GET['mensagem']) && $_GET['mensagem'] == 'sucesso'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Plano de nutrição adicionado com sucesso!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php 
        // Exibir mensagem de operações realizadas nesta página
        echo $mensagem; 
        ?>
        
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            // Consultar todos os planos de alimentação ordenados pelo ID (mais recentes primeiro)
            $sql = "SELECT ID_Alimentacao, Nome_Alimentacao, Imagem_Alimentacao, 
                    Energia, Proteinas, Gorduras, Hidratos_Carbono 
                    FROM Planos_Alimentacao 
                    ORDER BY ID_Alimentacao DESC";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0):
                while($row = $result->fetch_assoc()):
            ?>
                    <div class="col">
                        <div class="card card-nutricao h-100">
                            <div class="img-container">
                                <?php if (!empty($row["Imagem_Alimentacao"])): ?>
                                    <img src="<?= htmlspecialchars($row["Imagem_Alimentacao"]) ?>" class="card-img-top" alt="<?= htmlspecialchars($row["Nome_Alimentacao"]) ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-utensils fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row["Nome_Alimentacao"]) ?></h5>
                                
                                <div class="card-text mt-3">
                                    <p class="mb-1"><strong>Informação Nutricional:</strong></p>
                                    <div class="nutri-values">
                                        <?php if (!is_null($row["Energia"])): ?>
                                            <span class="badge nutri-badge"><i class="fas fa-bolt me-1"></i> <?= number_format($row["Energia"], 1) ?> kcal</span>
                                        <?php endif; ?>
                                        
                                        <?php if (!is_null($row["Proteinas"])): ?>
                                            <span class="badge nutri-badge"><i class="fas fa-dumbbell me-1"></i> <?= number_format($row["Proteinas"], 1) ?>g prot</span>
                                        <?php endif; ?>
                                        
                                        <?php if (!is_null($row["Gorduras"])): ?>
                                            <span class="badge nutri-badge"><i class="fas fa-oil-can me-1"></i> <?= number_format($row["Gorduras"], 1) ?>g gord</span>
                                        <?php endif; ?>
                                        
                                        <?php if (!is_null($row["Hidratos_Carbono"])): ?>
                                            <span class="badge nutri-badge"><i class="fas fa-bread-slice me-1"></i> <?= number_format($row["Hidratos_Carbono"], 1) ?>g carb</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between">
                                    <a href="Ver_Nutricao.php?id=<?= $row["ID_Alimentacao"] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detalhes
                                    </a>
                                    <div>
                                        <a href="editar_nutricao.php?id=<?= $row["ID_Alimentacao"] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- Formulário para exclusão -->
                                        <form method="post" style="display:inline-block;">
                                            <input type="hidden" name="id" value="<?= $row["ID_Alimentacao"] ?>">
                                            <button type="submit" name="excluir" class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Tem certeza que deseja excluir este plano de nutrição?');">
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
                        Nenhum plano de nutrição registado ainda. Clique em "Novo Plano de Nutrição" para adicionar o primeiro!
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>