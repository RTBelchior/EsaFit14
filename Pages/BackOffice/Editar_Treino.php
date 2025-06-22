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

// Verificar se o ID foi passado na URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: Lista_Treinos.php?error=id_invalido");
    exit();
}

$id_treino = intval($_GET['id']);

// Obter detalhes do treino
$stmt = $conn->prepare("SELECT * FROM Planos_Treino WHERE ID_Treino = ?");
$stmt->bind_param("i", $id_treino);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: Lista_Treinos.php?error=treino_nao_encontrado");
    exit();
}

$treino = $result->fetch_assoc();

// Obter músculos relacionados (para estrutura normalizada)
$musculos = [];
try {
    $musculos_query = "SELECT m.Nome_Musculo 
                      FROM Musculos m
                      JOIN Treino_Musculos tm ON m.ID_Musculo = tm.ID_Musculo
                      WHERE tm.ID_Treino = ?";
    $stmt = $conn->prepare($musculos_query);
    $stmt->bind_param("i", $id_treino);
    $stmt->execute();
    $musculos_result = $stmt->get_result();
    
    while($musculo_row = $musculos_result->fetch_assoc()) {
        $musculos[] = $musculo_row['Nome_Musculo'];
    }
} catch (Exception $e) {
    // Caso esteja usando a estrutura original, obtém os músculos da coluna Musculos_Alvos
    if (isset($treino['Musculos_Alvos']) && !empty($treino['Musculos_Alvos'])) {
        $musculos = explode(',', $treino['Musculos_Alvos']);
    }
}

$musculos_string = implode(',', $musculos);

// Determinar se a imagem é um GIF
$is_gif = false;
if (!empty($treino["Imagem_Treino"])) {
    $file_extension = strtolower(pathinfo($treino["Imagem_Treino"], PATHINFO_EXTENSION));
    $is_gif = ($file_extension === 'gif');
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Treino - <?= htmlspecialchars($treino["Nome_Treino"]) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 5px;
        }
        
        .tag-container {
            display: flex;
            flex-wrap: wrap;
            padding: 5px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            min-height: 38px;
            background-color: #fff;
        }
        
        .tag {
            display: inline-flex;
            align-items: center;
            margin: 2px;
            padding: 2px 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .tag .close {
            margin-left: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
        }
        
        .tag-input {
            flex-grow: 1;
            border: none;
            outline: none;
            padding: 5px;
            font-size: 14px;
            min-width: 60px;
        }
        
        .required-field::after {
            content: " *";
            color: red;
        }
        
        .current-image {
            margin-top: 10px;
            margin-bottom: 10px;
            text-align: center;
            border: 1px solid #dee2e6;
            padding: 10px;
            border-radius: 5px;
        }
        
        .checkbox-container {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4">Editar Treino</h2>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            
            <form id="treinoForm" method="post" action="Atualizar_Treino.php" enctype="multipart/form-data">
                <input type="hidden" name="id_treino" value="<?= $id_treino ?>">
                <input type="hidden" name="imagem_atual" value="<?= htmlspecialchars($treino["Imagem_Treino"] ?? '') ?>">
                
                <!-- Nome do Treino -->
                <div class="mb-3">
                    <label for="nomeTreino" class="form-label required-field">Nome do Treino</label>
                    <input type="text" class="form-control" id="nomeTreino" name="nome_treino" required 
                           value="<?= htmlspecialchars($treino["Nome_Treino"]) ?>">
                </div>
                
                <!-- Imagem -->
                <div class="mb-3">
                    <label for="imagemTreino" class="form-label">Imagem</label>
                    <?php if (!empty($treino["Imagem_Treino"])): ?>
                        <div class="current-image">
                            <p><strong><?= $is_gif ? 'Demonstração atual:' : 'Imagem atual:' ?></strong></p>
                            <img src="Get_Imagem_Treino.php?id=<?= $id_treino ?>" alt="Imagem atual" class="preview-image">
                            
                            <div class="checkbox-container">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="removerImagem" name="remover_imagem" value="1">
                                    <label class="form-check-label" for="removerImagem">
                                        Remover esta imagem
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="input-group">
                        <input type="file" class="form-control" id="imagemTreino" name="imagem_treino" accept="image/*">
                        <button class="btn btn-outline-secondary" type="button" id="limparImagem">Limpar</button>
                    </div>
                    <div id="previewImagem" class="mt-2"></div>
                    <small class="text-muted">Selecione uma nova imagem para substituir a atual. Aceita JPG, PNG e GIF.</small>
                </div>
                
                <!-- Músculos Alvos -->
                <div class="mb-3">
                    <label for="musculosAlvos" class="form-label">Músculos Alvos</label>
                    <div class="tag-container" id="tagContainer">
                        <input type="text" id="musculoInput" class="tag-input" placeholder="Digite e pressione Enter">
                    </div>
                    <input type="hidden" id="musculosAlvosHidden" name="musculos_alvos" value="<?= htmlspecialchars($musculos_string) ?>">
                    <small class="text-muted">Pressione Enter após digitar cada músculo ou selecione das sugestões</small>
                </div>
                
                <!-- Sugestões rápidas de músculos -->
                <div class="mb-3">
                    <div class="d-flex flex-wrap gap-1" id="sugestoesMusculos"></div>
                </div>
                
                <!-- Preparação -->
                <div class="mb-3">
                    <label for="preparacao" class="form-label">Preparação</label>
                    <textarea class="form-control" id="preparacao" name="preparacao" rows="4"><?= htmlspecialchars(str_replace(["\\r\\n", "\\n", "\\r"], "\n", $treino["Preparacao"] ?? '')) ?></textarea>
                </div>
                
                <!-- Execução -->
                <div class="mb-3">
                    <label for="execucao" class="form-label">Execução</label>
                    <textarea class="form-control" id="execucao" name="execucao" rows="4"><?= htmlspecialchars(str_replace(["\\r\\n", "\\n", "\\r"], "\n", $treino["Execucao"] ?? '')) ?></textarea>
                </div>
                
                <!-- Dicas -->
                <div class="mb-3">
                    <label for="dicas" class="form-label">Dicas</label>
                    <textarea class="form-control" id="dicas" name="dicas" rows="4"><?= htmlspecialchars(str_replace(["\\r\\n", "\\n", "\\r"], "\n", $treino["Dicas"] ?? '')) ?></textarea>
                </div>
                
                <!-- Botões -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="ver_treino.php?id=<?= $id_treino ?>" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lista de músculos comuns para sugestões
            const musculosComuns = [
                'Peito', 'Antebraço', 'Tríceps', 'Bíceps', 'Ombros',  'Costas', 'Abdômen', 'Pernas', 'Glúteos', 'Cardio'
            ];
            
            const sugestoesMusculos = document.getElementById('sugestoesMusculos');
            const tagContainer = document.getElementById('tagContainer');
            const musculoInput = document.getElementById('musculoInput');
            const musculosAlvosHidden = document.getElementById('musculosAlvosHidden');
            const imagemTreino = document.getElementById('imagemTreino');
            const previewImagem = document.getElementById('previewImagem');
            const limparImagem = document.getElementById('limparImagem');
            const removerImagem = document.getElementById('removerImagem');
            
            // Adicionar sugestões de músculos
            musculosComuns.forEach(musculo => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm btn-outline-secondary';
                btn.textContent = musculo;
                btn.addEventListener('click', () => adicionarTag(musculo));
                sugestoesMusculos.appendChild(btn);
            });
            
            // Gerenciar tags de músculos
            const tags = new Set();
            
            // Carregar músculos existentes
            const musculos = '<?= addslashes($musculos_string) ?>'.split(',');
            musculos.forEach(musculo => {
                if (musculo.trim()) {
                    adicionarTag(musculo.trim());
                }
            });
            
            function atualizarTagsHidden() {
                musculosAlvosHidden.value = Array.from(tags).join(',');
            }
            
            function adicionarTag(texto) {
                if (!texto || tags.has(texto)) return;
                
                tags.add(texto);
                
                const tagElement = document.createElement('span');
                tagElement.className = 'tag';
                tagElement.innerHTML = `${texto} <span class="close">&times;</span>`;
                
                tagElement.querySelector('.close').addEventListener('click', function() {
                    tagContainer.removeChild(tagElement);
                    tags.delete(texto);
                    atualizarTagsHidden();
                });
                
                tagContainer.insertBefore(tagElement, musculoInput);
                musculoInput.value = '';
                atualizarTagsHidden();
            }
            
            musculoInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    const valor = this.value.trim();
                    if (valor) {
                        adicionarTag(valor);
                    }
                }
            });
            
            // Preview de imagem
            imagemTreino.addEventListener('change', function() {
                previewImagem.innerHTML = '';
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'preview-image';
                        previewImagem.appendChild(img);
                    }
                    reader.readAsDataURL(this.files[0]);
                    
                    // Desmarcar checkbox de remover imagem se uma nova imagem for selecionada
                    if (removerImagem) {
                        removerImagem.checked = false;
                    }
                }
            });
            
            limparImagem.addEventListener('click', function() {
                imagemTreino.value = '';
                previewImagem.innerHTML = '';
            });
            
            // Se o checkbox de remover imagem for marcado, desabilite o input de arquivo
            if (removerImagem) {
                removerImagem.addEventListener('change', function() {
                    if (this.checked) {
                        imagemTreino.value = '';
                        previewImagem.innerHTML = '';
                    }
                });
            }
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>