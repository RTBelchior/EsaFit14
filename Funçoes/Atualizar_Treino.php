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

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: Lista_Treinos.php?error=metodo_invalido");
    exit();
}

// Verificar se o ID foi fornecido
if (!isset($_POST['id_treino']) || !is_numeric($_POST['id_treino'])) {
    header("Location: Lista_Treinos.php?error=id_invalido");
    exit();
}

$id_treino = intval($_POST['id_treino']);

// Inicializar array de erros
$errors = [];

// Verificar se o treino existe
$check_stmt = $conn->prepare("SELECT Imagem_Treino FROM Planos_Treino WHERE ID_Treino = ?");
$check_stmt->bind_param("i", $id_treino);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: Lista_Treinos.php?error=treino_nao_encontrado");
    exit();
}

$treino_atual = $result->fetch_assoc();
$imagem_atual = $treino_atual['Imagem_Treino'];
$check_stmt->close();

// Iniciar transação
$conn->begin_transaction();

try {
    // Obter dados do formulário
    $nome_treino = $conn->real_escape_string($_POST["nome_treino"]);
    $preparacao = $conn->real_escape_string($_POST["preparacao"] ?? '');
    $execucao = $conn->real_escape_string($_POST["execucao"] ?? '');
    $dicas = $conn->real_escape_string($_POST["dicas"] ?? '');
    
    // Processar músculos alvos
    $musculos_alvos = isset($_POST["musculos_alvos"]) ? $_POST["musculos_alvos"] : '';
    
    // Inicializar caminho da imagem com o valor atual
    $imagem_caminho = $imagem_atual;
    
    // Verificar se o usuário escolheu remover a imagem
    $remover_imagem = isset($_POST['remover_imagem']) && $_POST['remover_imagem'] == '1';
    
    // Processar upload de nova imagem ou remover imagem existente
    if ($remover_imagem) {
        // Se houver uma imagem atual e o usuário quiser removê-la
        if (!empty($imagem_atual) && file_exists($imagem_atual)) {
            unlink($imagem_atual); // Remover arquivo do servidor
        }
        $imagem_caminho = null; // Definir como null no banco de dados
    } 
    // Se foi enviada uma nova imagem
    elseif (isset($_FILES["imagem_treino"]) && $_FILES["imagem_treino"]["error"] == 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 10 * 1024 * 1024; // 10MB
        
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
        $novo_caminho = $upload_dir . $imagem_nome;
        
        // Mover a imagem para a pasta de upload
        if (!move_uploaded_file($_FILES["imagem_treino"]["tmp_name"], $novo_caminho)) {
            throw new Exception("Erro ao fazer upload da imagem. Verifique as permissões da pasta uploads.");
        }
        
        // Se já existir uma imagem anterior, removê-la para não ocupar espaço desnecessário
        if (!empty($imagem_atual) && file_exists($imagem_atual) && $imagem_atual != $novo_caminho) {
            unlink($imagem_atual);
        }
        
        $imagem_caminho = $novo_caminho;
    }
    
    // Preparar e executar o UPDATE
    $stmt = $conn->prepare("UPDATE Planos_Treino SET Nome_Treino = ?, Imagem_Treino = ?, Preparacao = ?, Execucao = ?, Dicas = ?, Data_Atualizacao = NOW() WHERE ID_Treino = ?");
    $stmt->bind_param("sssssi", $nome_treino, $imagem_caminho, $preparacao, $execucao, $dicas, $id_treino);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao atualizar o treino: " . $conn->error);
    }
    
    $stmt->close();
    
    // Atualizar músculos
    if (isset($musculos_alvos)) {
        // Primeiro, remover todas as relações existentes
        $stmt = $conn->prepare("DELETE FROM Treino_Musculos WHERE ID_Treino = ?");
        $stmt->bind_param("i", $id_treino);
        $stmt->execute();
        $stmt->close();
        
        // Depois, adicionar as novas relações
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
    }
    
    // Commit da transação
    $conn->commit();
    
    // Redirecionar de volta para a página de detalhes
    header("Location: Ver_Treino.php?id=$id_treino&mensagem=atualizado");
    exit();
    
} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    
    // Se uma nova imagem foi carregada e houve erro, remover o arquivo
    if (isset($novo_caminho) && file_exists($novo_caminho) && $novo_caminho != $imagem_atual) {
        unlink($novo_caminho);
    }
    
    $timestamp = time();
    header("Location: Ver_Treino.php?id=" . $id_treino . "&cache=" . $timestamp);
    exit();
}

$conn->close();
?>