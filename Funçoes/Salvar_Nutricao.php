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

// Inicializar array de erros
$errors = [];
$success = false;

// Processar os dados do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Obter dados do formulário
        $nome_alimentacao = $conn->real_escape_string($_POST["nome_alimentacao"]);
        $ingredientes = $conn->real_escape_string($_POST["ingredientes"]);
        $preparacao = $conn->real_escape_string($_POST["preparacao"]);
        
        // Obter valores nutricionais - tratando valores nulos
        $energia = !empty($_POST["energia"]) ? floatval($_POST["energia"]) : null;
        $gorduras = !empty($_POST["gorduras"]) ? floatval($_POST["gorduras"]) : null;
        $gorduras_saturadas = !empty($_POST["gorduras_saturadas"]) ? floatval($_POST["gorduras_saturadas"]) : null;
        $hidratos_carbono = !empty($_POST["hidratos_carbono"]) ? floatval($_POST["hidratos_carbono"]) : null;
        $hidratos_acucares = !empty($_POST["hidratos_acucares"]) ? floatval($_POST["hidratos_acucares"]) : null;
        $fibras = !empty($_POST["fibras"]) ? floatval($_POST["fibras"]) : null;
        $proteinas = !empty($_POST["proteinas"]) ? floatval($_POST["proteinas"]) : null;
        
        // Inicializar variável para o caminho da imagem
        $imagem_caminho = null;
        
        // Processar upload de imagem, se houver
        if (isset($_FILES["imagem_alimentacao"]) && $_FILES["imagem_alimentacao"]["error"] == 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 10 * 1024 * 1024; // Aumentado para 10MB para acomodar GIFs
            
            // Obter o tipo de arquivo usando finfo para verificação mais precisa
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $file_type = $finfo->file($_FILES["imagem_alimentacao"]["tmp_name"]);
            
            // Verificar tipo de arquivo
            if (!in_array($file_type, $allowedTypes)) {
                throw new Exception("Apenas imagens JPG, PNG e GIF são permitidas. Tipo detectado: " . $file_type);
            }
            
            // Verificar tamanho do arquivo
            if ($_FILES["imagem_alimentacao"]["size"] > $maxFileSize) {
                throw new Exception("A imagem deve ter no máximo 10MB.");
            }
            
            // Verificar se o diretório uploads existe, se não, criá-lo
            $upload_dir = 'uploads/planos_nutricao/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new Exception("Erro ao criar diretório de upload. Verifique as permissões do servidor.");
                }
            }
            
            // Gerar nome único para o arquivo
            $imagem_nome = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-]/', '_', basename($_FILES["imagem_alimentacao"]["name"]));
            $imagem_caminho = $upload_dir . $imagem_nome;
            
            // Mover a imagem para a pasta de upload
            if (!move_uploaded_file($_FILES["imagem_alimentacao"]["tmp_name"], $imagem_caminho)) {
                throw new Exception("Erro ao fazer upload da imagem. Verifique as permissões da pasta uploads.");
            }
            
            // Verificar se o arquivo existe após o upload
            if (!file_exists($imagem_caminho)) {
                throw new Exception("Erro: o arquivo não foi salvo corretamente.");
            }
        }
        
        // Preparar statement SQL para inserir o plano de alimentação
        $stmt = $conn->prepare("INSERT INTO Planos_Alimentacao (Nome_Alimentacao, Imagem_Alimentacao, Ingredientes, Preparacao, 
                                Energia, Gorduras, Gorduras_Saturadas, Hidratos_Carbono, Hidratos_Acucares, Fibras, Proteinas) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssddddddd", 
            $nome_alimentacao, 
            $imagem_caminho, 
            $ingredientes, 
            $preparacao,
            $energia,
            $gorduras,
            $gorduras_saturadas,
            $hidratos_carbono,
            $hidratos_acucares,
            $fibras,
            $proteinas
        );
        
        // Executar query para inserir o plano de alimentação
        if (!$stmt->execute()) {
            throw new Exception("Erro ao salvar o plano de alimentação: " . $conn->error);
        }
        
        // Commit da transação
        $conn->commit();
        $success = true;
        
        // Sucesso - redirecionar para listagem ou mostrar mensagem
        header("Location: Lista_Alimentacao.php?mensagem=sucesso");
        exit();
        
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        
        // Verificar se há imagem que precisa ser excluída em caso de erro
        if (isset($imagem_caminho) && file_exists($imagem_caminho)) {
            unlink($imagem_caminho); // Remover a imagem em caso de falha
        }
        
        $errors[] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processando formulário</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <?php
        // Se chegou até aqui com erros, exibir os erros
        if (!empty($errors)) {
            echo '<div class="alert alert-danger">';
            foreach ($errors as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '<p><a href="javascript:history.back()" class="btn btn-primary">Voltar e tentar novamente</a></p>';
            echo '</div>';
        }

        // Mostrar mensagem de sucesso se operação foi bem-sucedida
        if ($success) {
            echo '<div class="alert alert-success">';
            echo '<p>Plano de alimentação registado com sucesso!</p>';
            echo '<p><a href="Lista_Alimentacao.php" class="btn btn-primary">Ver lista de planos de alimentação</a></p>';
            echo '</div>';
        }
        ?>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>