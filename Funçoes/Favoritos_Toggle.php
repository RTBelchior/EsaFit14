<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilizador não autenticado']);
    exit();
}

$host = "localhost";
$utilizador = "root";
$senha = "";
$dbname = "esafit";

$conn = new mysqli($host, $utilizador, $senha, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Falha na conexão']);
    exit();
}

$conn->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $treino_id = isset($input['treino_id']) ? intval($input['treino_id']) : 0;
    $user_id = $_SESSION['user_id'];
    
    if ($treino_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID do treino inválido']);
        exit();
    }
    
    // Verificar se já existe nos favoritos
    $check_stmt = $conn->prepare("SELECT ID_TreinoFav FROM Treino_Favoritos WHERE ID_Utilizador = ? AND ID_Treino = ?");
    $check_stmt->bind_param("ii", $user_id, $treino_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Se já existe, remover dos favoritos
        $delete_stmt = $conn->prepare("DELETE FROM Treino_Favoritos WHERE ID_Utilizador = ? AND ID_Treino = ?");
        $delete_stmt->bind_param("ii", $user_id, $treino_id);
        
        if ($delete_stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'action' => 'removed',
                'message' => 'Treino removido dos favoritos'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao remover dos favoritos']);
        }
        $delete_stmt->close();
    } else {
        // Se não existe, adicionar aos favoritos
        $insert_stmt = $conn->prepare("INSERT INTO Treino_Favoritos (ID_Utilizador, ID_Treino) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $user_id, $treino_id);
        
        if ($insert_stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'action' => 'added',
                'message' => 'Treino adicionado aos favoritos'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar aos favoritos']);
        }
        $insert_stmt->close();
    }
    
    $check_stmt->close();
}

$conn->close();
?>