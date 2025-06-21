<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// Verificar se o formulário foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: Perfil.php");
    exit();
}

// Conectar ao banco de dados
$host = "localhost";
$utilizador = "root";
$senha = "";
$dbname = "esafit";

$conn = new mysqli($host, $utilizador, $senha, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Obter ID do usuário da sessão
$user_id = $_SESSION['user_id'];

// Obter valores do formulário
$nome = $conn->real_escape_string($_POST["name"]);
$altura = isset($_POST['height']) ? (float)$_POST['height'] : null;
$peso = isset($_POST['weight']) ? (float)$_POST['weight'] : null;
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Inicializar array para erros
$_SESSION['profile_errors'] = [];

// Para fins de debug, armazenar os dados do formulário na sessão
$_SESSION['debug_form_data'] = [
    'name' => $nome,
    'height' => $altura,
    'weight' => $peso,
    'email' => $email,
    'files' => isset($_FILES) ? $_FILES : 'No files'
];

// Processar upload da imagem de perfil
$foto_perfil = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0 && $_FILES['avatar']['size'] > 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['avatar']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed)) {
        // Diretório para salvar as imagens
        $upload_dir = "uploads/fotos_perfil/";
        
        // Criar diretório se não existir
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Gerar nome único para o arquivo
        $new_filename = uniqid('profile_') . '.' . $ext;
        $destination = $upload_dir . $new_filename;
        
        // Mover o arquivo para o diretório de destino
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
            $foto_perfil = $destination;
        } else {
            error_log("Falha ao mover o arquivo enviado. Error: " . $_FILES['avatar']['error']);
            $_SESSION['profile_errors'][] = "Erro ao fazer upload da imagem. Por favor, tente novamente.";
        }
    } else {
        error_log("Tipo de arquivo não permitido: " . $ext);
        $_SESSION['profile_errors'][] = "Formato de imagem inválido. Apenas JPG, JPEG, PNG e GIF são permitidos.";
        header("Location: Perfil.php");
        exit();
    }
} else if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] != UPLOAD_ERR_NO_FILE) {
    error_log("Problema com o arquivo: Error code = " . $_FILES['avatar']['error'] . ", Size = " . $_FILES['avatar']['size']);
    
    // Mensagens de erro específicas com base no código de erro
    switch($_FILES['avatar']['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $_SESSION['profile_errors'][] = "O arquivo é muito grande. Tamanho máximo permitido é de 2MB.";
            break;
        case UPLOAD_ERR_PARTIAL:
            $_SESSION['profile_errors'][] = "O upload do arquivo foi interrompido. Por favor, tente novamente.";
            break;
        default:
            $_SESSION['profile_errors'][] = "Ocorreu um erro no upload da imagem. Por favor, tente novamente.";
    }
    
    if (!empty($_SESSION['profile_errors'])) {
        header("Location: Perfil.php");
        exit();
    }
}

// Preparar a query SQL para atualização
$fields = [];
$types = "";
$values = [];

// Adicionar campos que serão atualizados
if (!empty($nome)) {
    $fields[] = "Nome_utilizador = ?";
    $types .= "s";
    $values[] = $nome;
}

// Converter altura de centímetros para metros se necessário (se maior que 10)
if ($altura !== null) {
    // Se o formulário envia em centímetros, converter para metros antes de salvar
    if ($altura > 10) {
        $altura = $altura / 100;
    }
    $fields[] = "Altura = ?";
    $types .= "d";
    $values[] = $altura;
}

if ($peso !== null) {
    $fields[] = "Peso = ?";
    $types .= "d";
    $values[] = $peso;
}

if (!empty($email)) {
    $fields[] = "Email = ?";
    $types .= "s";
    $values[] = $email;
}

if ($foto_perfil !== null) {
    $fields[] = "Foto_perfil = ?";
    $types .= "s";
    $values[] = $foto_perfil;
}

// Adicionar ID do usuário no final dos parâmetros
$types .= "i";
$values[] = $user_id;

// Se houver campos para atualizar
if (count($fields) > 0) {
    $sql = "UPDATE Utilizadores SET " . implode(", ", $fields) . " WHERE ID_Utilizador = ?";
    
    // Debug para ver a query gerada
    error_log("Query SQL: " . $sql);
    error_log("Tipos de parâmetros: " . $types);
    error_log("Valores: " . implode(", ", array_map('strval', $values)));
    
    $stmt = $conn->prepare($sql);
    
    // Verificar se a preparação foi bem-sucedida
    if ($stmt === false) {
        $_SESSION['profile_errors'][] = "Erro na preparação da query: " . $conn->error;
        error_log("Erro MySQL ao preparar: " . $conn->error);
        header("Location: Perfil.php");
        exit();
    }
    
    try {
        // Aplicar os parâmetros dinamicamente
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Perfil atualizado com sucesso!";
            error_log("Perfil atualizado com sucesso para o usuário ID: " . $user_id);
        } else {
            $_SESSION['profile_errors'][] = "Erro ao atualizar o perfil: " . $stmt->error;
            error_log("Erro MySQL ao executar: " . $stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['profile_errors'][] = "Erro ao processar os dados: " . $e->getMessage();
        error_log("Exception: " . $e->getMessage());
        header("Location: Perfil.php");
        exit();
    }
} else {
    $_SESSION['success_message'] = "Nenhuma alteração detectada.";
    error_log("Nenhuma alteração detectada para o usuário ID: " . $user_id);
}

$conn->close();

// Redirecionar para a página de perfil ou index se houver erros
if (!empty($_SESSION['profile_errors'])) {
    header("Location: Perfil.php");
} else {
    header("Location: Perfil.php");
}
exit();
?>