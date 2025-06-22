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

$errors = [];
$success = false;

// Processar os dados do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Obter dados do formulário
        $nome_treino = $conn->real_escape_string($_POST["nome_treino"]);
        $musculos_alvos = isset($_POST["musculos_alvos"]) ? $_POST["musculos_alvos"] : ''; // Não escapar ainda, vamos processar depois
        $preparacao = $conn->real_escape_string($_POST["preparacao"]);
        $execucao = $conn->real_escape_string($_POST["execucao"]);
        $dicas = $conn->real_escape_string($_POST["dicas"]);
        
        // Inicializar variável para o caminho da imagem
        $imagem_caminho = null;
        
        // Processar upload de imagem, se houver
        if (isset($_FILES["imagem_treino"]) && $_FILES["imagem_treino"]["error"] == 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 10 * 1024 * 1024; // Aumentado para 10MB para acomodar GIFs
            
            // Obter o tipo de arquivo usando finfo para verificação mais precisa
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $file_type = $finfo->file($_FILES["imagem_treino"]["tmp_name"]);
            
            // Verificar tipo de arquivo
            if (!in_array($file_type, $allowedTypes)) {
                throw new Exception("Apenas imagens JPG, PNG e GIF são permitidas. Tipo detectado: " . $file_type);
            }
            
            // Verificar tamanho do arquivo
            if ($_FILES["imagem_treino"]["size"] > $maxFileSize) {
                throw new Exception("A imagem deve ter no máximo 10MB.");
            }
            
            // Verificar se o diretório uploads existe, se não, criá-lo
            $upload_dir = 'uploads/planos_treino/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new Exception("Erro ao criar diretório de upload. Verifique as permissões do servidor.");
                }
            }
            
            // Gerar nome único para o arquivo
            $imagem_nome = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-]/', '_', basename($_FILES["imagem_treino"]["name"]));
            $imagem_caminho = $upload_dir . $imagem_nome;
            
            // Mover a imagem para a pasta de upload
            if (!move_uploaded_file($_FILES["imagem_treino"]["tmp_name"], $imagem_caminho)) {
                throw new Exception("Erro ao fazer upload da imagem. Verifique as permissões da pasta uploads.");
            }
            
            // Verificar se o arquivo existe após o upload
            if (!file_exists($imagem_caminho)) {
                throw new Exception("Erro: o arquivo não foi salvo corretamente.");
            }
        }
        
        // Preparar statement SQL para inserir o treino (sem o campo Video_Treino)
        $stmt = $conn->prepare("INSERT INTO Planos_Treino (Nome_Treino, Imagem_Treino, Preparacao, Execucao, Dicas) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nome_treino, $imagem_caminho, $preparacao, $execucao, $dicas);
        
        // Executar query para inserir o treino
        if (!$stmt->execute()) {
            throw new Exception("Erro ao salvar o plano de treino: " . $conn->error);
        }
        
        // Obter o ID do treino inserido
        $id_treino = $conn->insert_id;
        $stmt->close();
        
        // Processar os músculos alvo
        if (!empty($musculos_alvos)) {
            $musculos_array = explode(',', $musculos_alvos);
            
            foreach ($musculos_array as $musculo) {
                $musculo = trim($conn->real_escape_string($musculo));
                
                if (empty($musculo)) continue;
                
                // Verificar se o músculo já existe no banco
                $stmt = $conn->prepare("SELECT ID_Musculo FROM Musculos WHERE Nome_Musculo = ?");
                $stmt->bind_param("s", $musculo);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    // Músculo já existe, obter o ID
                    $row = $result->fetch_assoc();
                    $id_musculo = $row['ID_Musculo'];
                } else {
                    // Músculo não existe, inserir
                    $stmt = $conn->prepare("INSERT INTO Musculos (Nome_Musculo) VALUES (?)");
                    $stmt->bind_param("s", $musculo);
                    $stmt->execute();
                    $id_musculo = $conn->insert_id;
                }
                
                // Criar relação entre treino e músculo
                $stmt = $conn->prepare("INSERT INTO Treino_Musculos (ID_Treino, ID_Musculo) VALUES (?, ?)");
                $stmt->bind_param("ii", $id_treino, $id_musculo);
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao relacionar treino com músculo: " . $conn->error);
                }
            }
        }
        
        // Commit da transação
        $conn->commit();
        $success = true;
        
        // Sucesso - redirecionar para listagem ou mostrar mensagem
        header("Location: Lista_Treinos.php?mensagem=sucesso");
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
            echo '<p>Plano de treino cadastrado com sucesso!</p>';
            echo '<p><a href="Lista_Treinos.php" class="btn btn-primary">Ver lista de treinos</a></p>';
            echo '</div>';
        }
        ?>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>