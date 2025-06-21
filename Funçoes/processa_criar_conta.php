<?php
session_start();

$host = "localhost";
$utilizador = "root";
$pass = "";
$dbname = "esafit";

// Criar conexão
$conn = new mysqli($host, $utilizador, $pass, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Definir charset para lidar corretamente com caracteres especiais
$conn->set_charset("utf8mb4");

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recolher dados do formulário
    $nome = trim($_POST['nome']);
    $idade = $_POST['idade'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $peso = $_POST['peso'];
    $altura = $_POST['altura'];
    
    // Array para armazenar mensagens de erro
    $errors = [];
    
    // Validar nome
    if (strlen($nome) < 3) {
        $errors[] = "O nome deve ter pelo menos 3 caracteres.";
    }
    
    // Validar idade
    if ($idade < 12 || $idade > 100) {
        $errors[] = "A idade deve estar entre 12 e 100 anos.";
    }
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "O email fornecido não é válido.";
    } else {
        // Verificar se o email já existe na base de dados
        $stmt = $conn->prepare("SELECT ID_Utilizador FROM Utilizadores WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Este email já está registado. Por favor, tente outro ou faça login.";
        }
        $stmt->close();
    }
    
    // Validar password
    if (strlen($password) < 8) {
        $errors[] = "A palavra-passe deve ter pelo menos 8 caracteres.";
    }
    
    // Confirmar password
    if ($password !== $confirmPassword) {
        $errors[] = "As palavras-passe não coincidem.";
    }
    
    // Validar peso
    if ($peso < 30 || $peso > 250) {
        $errors[] = "O peso deve estar entre 30 e 250 kg.";
    }
    
    // Validar altura
    if ($altura < 1.00 || $altura > 2.50) {
        $errors[] = "A altura deve estar entre 1.00 e 2.50 metros.";
    }
    
    // Processar upload de foto (se existir)
    $foto_perfil = null;
    if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        // Verificar tipo de arquivo
        if (!in_array($_FILES['fotoPerfil']['type'], $allowedTypes)) {
            $errors[] = "Apenas imagens JPG, PNG e GIF são permitidas.";
        }
        
        // Verificar tamanho do arquivo
        if ($_FILES['fotoPerfil']['size'] > $maxFileSize) {
            $errors[] = "A imagem deve ter no máximo 5MB.";
        }
        
        // Se não houver erros, mover o arquivo para o diretório desejado
        if (empty($errors)) {
            // Verificar se o diretório uploads existe, se não, criá-lo
            $upload_dir = 'uploads/fotos_perfil/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $foto_nome = uniqid() . '_' . basename($_FILES['fotoPerfil']['name']);
            $foto_path = $upload_dir . $foto_nome;
            
            // Mover a foto para a pasta de upload
            if (move_uploaded_file($_FILES['fotoPerfil']['tmp_name'], $foto_path)) {
                $foto_perfil = $foto_path; // Armazenar o caminho da foto
            } else {
                $errors[] = "Erro ao fazer upload da foto. Verifique as permissões da pasta uploads.";
            }
        }
    }
    
    // Adicionar logs para diagnóstico
    error_log("Processando registro de usuário: " . $email);
    
    // Se não houver erros, inserir o utilizador na base de dados
    if (empty($errors)) {
        try {
            // Hash da password para segurança
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Preparar e executar a query
            $stmt = $conn->prepare("INSERT INTO Utilizadores (Nome_utilizador, Foto_perfil, Idade, Email, Password, Peso, Altura) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Erro na preparação da query: " . $conn->error);
            }
            
            $stmt->bind_param("ssissdd", $nome, $foto_perfil, $idade, $email, $hashed_password, $peso, $altura);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar a query: " . $stmt->error);
            }
            
            // Registo bem-sucedido
            $_SESSION['success_message'] = "Conta criada com sucesso! Pode agora iniciar sessão.";
            header("Location: login.php");
            exit();
            
        } catch (Exception $e) {
            $errors[] = "Erro ao criar conta: " . $e->getMessage();
            error_log("Erro no registro: " . $e->getMessage());
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
    
    // Se houver erros, guardar para exibir no formulário
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_data'] = [
            'nome' => $nome,
            'idade' => $idade,
            'email' => $email,
            'peso' => $peso,
            'altura' => $altura
        ];
        
        // Redirecionar de volta para o formulário
        header("Location: criar_conta.php");
        exit();
    }
}

// Fechar conexão
$conn->close();
?>