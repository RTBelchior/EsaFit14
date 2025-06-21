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

// Desabilitar cache para garantir que imagens atualizadas sejam exibidas
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Verificar se o ID foi passado na URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit('ID do treino não especificado ou inválido');
}

$id_treino = intval($_GET['id']);

// Adicionando parâmetro de versão para controle de cache
$cache_version = isset($_GET['v']) ? $_GET['v'] : '';

// Obter dados da imagem
$stmt = $conn->prepare("SELECT Nome_Treino, Imagem_Treino, Data_Atualizacao FROM Planos_Treino WHERE ID_Treino = ?");
$stmt->bind_param("i", $id_treino);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    exit('Treino não encontrado');
}

$treino = $result->fetch_assoc();
$conn->close();

// Verificar se há imagem associada ao treino
if (empty($treino['Imagem_Treino'])) {
    http_response_code(404);
    exit('Imagem não encontrada para este treino');
}

// Obter caminho da imagem
$filepath = $treino['Imagem_Treino'];

// Verificar se o arquivo existe
if (!file_exists($filepath)) {
    http_response_code(404);
    exit('Arquivo de imagem não encontrado no servidor: ' . htmlspecialchars($filepath));
}

// Obter informações sobre o arquivo
$filesize = filesize($filepath);
$filename = basename($filepath);

// Usar filemtime para obter a data de modificação do arquivo como controle de versão
$file_modified_time = filemtime($filepath);

// Determinar o tipo MIME baseado na extensão do arquivo
$file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$content_type = '';

switch ($file_extension) {
    case 'jpg':
    case 'jpeg':
        $content_type = 'image/jpeg';
        break;
    case 'png':
        $content_type = 'image/png';
        break;
    case 'gif':
        $content_type = 'image/gif';
        break;
    default:
        $content_type = 'application/octet-stream';
}

// Enviar os headers para controle de cache com ETag
$etag = md5($file_modified_time . $filesize);
header('ETag: "' . $etag . '"');

// Se o navegador enviou um ETag e ele corresponde ao arquivo atual, enviar 304 Not Modified
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
    http_response_code(304); // Not Modified
    exit;
}

// Enviar os headers apropriados
header('Content-Type: ' . $content_type);
header('Content-Length: ' . $filesize);
header('Content-Disposition: inline; filename="' . $filename . '"');

// Em vez de usar cache máximo, forçamos revalidação
header('Cache-Control: must-revalidate');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $file_modified_time) . ' GMT');

// Ler e enviar o arquivo
readfile($filepath);
exit;
?>