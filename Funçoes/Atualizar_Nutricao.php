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

// Verificar se o método da requisição é POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: Lista_Alimentacao.php?error=metodo_invalido");
    exit();
}

// Verificar se o ID foi passado
if (!isset($_POST['id_alimentacao']) || !is_numeric($_POST['id_alimentacao'])) {
    header("Location: Lista_Alimentacao.php?error=id_invalido");
    exit();
}

$id_alimentacao = intval($_POST['id_alimentacao']);

// Verificar se o plano existe
$stmt = $conn->prepare("SELECT * FROM Planos_Alimentacao WHERE ID_Alimentacao = ?");
$stmt->bind_param("i", $id_alimentacao);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: Lista_Alimentacao.php?error=plano_nao_encontrado");
    exit();
}

$plano = $result->fetch_assoc();

// Limpar e validar os dados
$nome_alimentacao = trim($_POST['nome_alimentacao']);
$ingredientes = trim($_POST['ingredientes']);
$preparacao = trim($_POST['preparacao']);

// Valores numéricos (converter para NULL se vazio)
$energia = !empty($_POST['energia']) ? floatval($_POST['energia']) : null;
$proteinas = !empty($_POST['proteinas']) ? floatval($_POST['proteinas']) : null;
$gorduras = !empty($_POST['gorduras']) ? floatval($_POST['gorduras']) : null;
$gorduras_saturadas = !empty($_POST['gorduras_saturadas']) ? floatval($_POST['gorduras_saturadas']) : null;
$hidratos_carbono = !empty($_POST['hidratos_carbono']) ? floatval($_POST['hidratos_carbono']) : null;
$hidratos_acucares = !empty($_POST['hidratos_acucares']) ? floatval($_POST['hidratos_acucares']) : null;
$fibras = !empty($_POST['fibras']) ? floatval($_POST['fibras']) : null;

// Validar os campos obrigatórios
if (empty($nome_alimentacao)) {
    header("Location: Editar_Nutricao.php?id=$id_alimentacao&error=nome_obrigatorio");
    exit();
}

if (empty($ingredientes)) {
    header("Location: Editar_Nutricao.php?id=$id_alimentacao&error=ingredientes_obrigatorios");
    exit();
}

if (empty($preparacao)) {
    header("Location: Editar_Nutricao.php?id=$id_alimentacao&error=preparacao_obrigatoria");
    exit();
}

// Variável para armazenar o nome da imagem
$imagem_alimentacao = $plano['Imagem_Alimentacao'];

// Verificar se é para remover a imagem atual
if (isset($_POST['remover_imagem']) && $_POST['remover_imagem'] == '1') {
    // Se houver uma imagem atual, remova-a do servidor
    if (!empty($plano['Imagem_Alimentacao']) && file_exists("uploads/alimentacao/" . $plano['Imagem_Alimentacao'])) {
        unlink("uploads/alimentacao/" . $plano['Imagem_Alimentacao']);
    }
    $imagem_alimentacao = null;
}

// Verificar se há um arquivo de imagem para upload
if (isset($_FILES['imagem_alimentacao']) && $_FILES['imagem_alimentacao']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 10 * 1024 * 1024; // 10MB
    
    $file_type = $_FILES['imagem_alimentacao']['type'];
    $file_size = $_FILES['imagem_alimentacao']['size'];
    
    // Validar tipo de arquivo
    if (!in_array($file_type, $allowed_types)) {
        header("Location: Editar_Nutricao.php?id=$id_alimentacao&error=tipo_arquivo_invalido");
        exit();
    }
    
    // Validar tamanho do arquivo
    if ($file_size > $max_size) {
        header("Location: Editar_Nutricao.php?id=$id_alimentacao&error=arquivo_muito_grande");
        exit();
    }
    
    // Gerar um nome único para o arquivo
    $file_extension = pathinfo($_FILES['imagem_alimentacao']['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('nutricao_') . '.' . $file_extension;
    
    // Criar diretório se não existir
    if (!file_exists('uploads/alimentacao')) {
        mkdir('uploads/alimentacao', 0777, true);
    }
    
    $upload_path = 'uploads/alimentacao/' . $new_filename;
    
    // Mover o arquivo para o diretório de uploads
    if (move_uploaded_file($_FILES['imagem_alimentacao']['tmp_name'], $upload_path)) {
        // Se houver uma imagem anterior, remova-a
        if (!empty($plano['Imagem_Alimentacao']) && file_exists("uploads/alimentacao/" . $plano['Imagem_Alimentacao']) && !isset($_POST['remover_imagem'])) {
            unlink("uploads/alimentacao/" . $plano['Imagem_Alimentacao']);
        }
        
        $imagem_alimentacao = $new_filename;
    } else {
        header("Location: Editar_Nutricao.php?id=$id_alimentacao&error=falha_upload");
        exit();
    }
}

// Preparar e executar a atualização no banco de dados
$stmt = $conn->prepare("UPDATE Planos_Alimentacao SET 
                       Nome_Alimentacao = ?, 
                       Imagem_Alimentacao = ?, 
                       Ingredientes = ?, 
                       Preparacao = ?, 
                       Energia = ?, 
                       Proteinas = ?, 
                       Gorduras = ?, 
                       Gorduras_Saturadas = ?, 
                       Hidratos_Carbono = ?, 
                       Hidratos_Acucares = ?, 
                       Fibras = ? 
                       WHERE ID_Alimentacao = ?");

$stmt->bind_param("ssssdddddddi", 
                 $nome_alimentacao, 
                 $imagem_alimentacao, 
                 $ingredientes, 
                 $preparacao, 
                 $energia, 
                 $proteinas, 
                 $gorduras, 
                 $gorduras_saturadas, 
                 $hidratos_carbono, 
                 $hidratos_acucares, 
                 $fibras, 
                 $id_alimentacao);

if ($stmt->execute()) {
    // Sucesso
    header("Location: Ver_Nutricao.php?id=$id_alimentacao&success=plano_atualizado");
} else {
    // Erro ao atualizar
    header("Location: Editar_Nutricao.php?id=$id_alimentacao&error=falha_atualizacao");
}

$stmt->close();
$conn->close();
?>