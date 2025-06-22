<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esafit";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    exit();
}

$conn->set_charset("utf8mb4");

// Verificar se o ID foi fornecido e é válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit();
}

$id_alimentacao = intval($_GET['id']);

// Preparar e executar a consulta para obter a imagem
$stmt = $conn->prepare("SELECT Imagem_Alimentacao FROM Planos_Alimentacao WHERE ID_Alimentacao = ?");
if (!$stmt) {
    http_response_code(500);
    exit();
}

$stmt->bind_param("i", $id_alimentacao);
$stmt->execute();
$result = $stmt->get_result();

// Verificar se o registro foi encontrado
if ($result->num_rows === 0) {
    http_response_code(404);
    exit();
}

$row = $result->fetch_assoc();
$imagem_dados = $row['Imagem_Alimentacao'];

// Verificar se há dados de imagem
if (empty($imagem_dados)) {
    http_response_code(404);
    exit();
}

// Detectar o tipo MIME da imagem
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->buffer($imagem_dados);

// Verificar se é um tipo de imagem válido
$tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mime_type, $tipos_permitidos)) {
    // Se não conseguir detectar o tipo ou não for válido, assumir JPEG como padrão
    $mime_type = 'image/jpeg';
}

// Definir headers apropriados
header('Content-Type: ' . $mime_type);
header('Content-Length: ' . strlen($imagem_dados));
header('Cache-Control: public, max-age=3600'); // Cache por 1 hora
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

// Prevenir cache se em desenvolvimento (opcional)
// header('Cache-Control: no-cache, no-store, must-revalidate');
// header('Pragma: no-cache');
// header('Expires: 0');

// Enviar os dados da imagem
echo $imagem_dados;

// Fechar conexões
$stmt->close();
$conn->close();
?>