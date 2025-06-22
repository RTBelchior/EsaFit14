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
    header("Location: Lista_Alimentacao.php?error=id_invalido");
    exit();
}

$id_alimentacao = intval($_GET['id']);

// Obter detalhes do plano de alimentação
$stmt = $conn->prepare("SELECT * FROM Planos_Alimentacao WHERE ID_Alimentacao = ?");
$stmt->bind_param("i", $id_alimentacao);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: Lista_Alimentacao.php?error=plano_nao_encontrado");
    exit();
}

$plano = $result->fetch_assoc();

// Determinar se a imagem é um GIF
$is_gif = false;
if (!empty($plano["Imagem_Alimentacao"])) {
    $file_extension = strtolower(pathinfo($plano["Imagem_Alimentacao"], PATHINFO_EXTENSION));
    $is_gif = ($file_extension === 'gif');
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Plano de Nutrição - <?= htmlspecialchars($plano["Nome_Alimentacao"]) ?></title>
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
        
        /* Estilo para feedback de erro */
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 80%;
            color: #dc3545;
        }
        
        .is-invalid ~ .invalid-feedback {
            display: block;
        }
        
        .is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        /* Estilos específicos para informações nutricionais */
        .info-nutricional {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .info-nutricional h4 {
            margin-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        
        .info-nutricional .form-group {
            margin-bottom: 10px;
        }
        
        .info-nutricional .input-group-text {
            width: 40px;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4">Editar Plano de Nutrição</h2>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            
            <form id="nutricaoForm" method="post" action="Atualizar_Nutricao.php" enctype="multipart/form-data">
                <input type="hidden" name="id_alimentacao" value="<?= $id_alimentacao ?>">
                <input type="hidden" name="imagem_atual" value="<?= htmlspecialchars($plano["Imagem_Alimentacao"] ?? '') ?>">
                
                <!-- Nome do Plano -->
                <div class="mb-3">
                    <label for="nomeAlimentacao" class="form-label required-field">Nome do Plano de Alimentação</label>
                    <input type="text" class="form-control" id="nomeAlimentacao" name="nome_alimentacao" required 
                           value="<?= htmlspecialchars($plano["Nome_Alimentacao"]) ?>">
                    <div class="invalid-feedback">
                        Por favor, forneça um nome para o plano de alimentação.
                    </div>
                </div>
                
                <!-- Imagem -->
                <div class="mb-3">
                    <label for="imagemAlimentacao" class="form-label">Imagem</label>
                    <?php if (!empty($plano["Imagem_Alimentacao"])): ?>
                        <div class="current-image">
                            <p><strong><?= $is_gif ? 'Demonstração atual:' : 'Imagem atual:' ?></strong></p>
                            <img src="Get_Imagem_Alimentacao.php?id=<?= $id_alimentacao ?>" alt="Imagem atual" class="preview-image">
                            
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
                        <input type="file" class="form-control" id="imagemAlimentacao" name="imagem_alimentacao" accept="image/jpeg,image/png,image/gif">
                        <button class="btn btn-outline-secondary" type="button" id="limparImagem">Limpar</button>
                    </div>
                    <div class="invalid-feedback" id="imagemFeedback">
                        Apenas imagens JPG, PNG e GIF são permitidas.
                    </div>
                    <small class="text-muted">Selecione uma nova imagem para substituir a atual. Aceita JPG, PNG e GIF (máx. 10MB).</small>
                    <div id="previewImagem" class="mt-2"></div>
                </div>
                
                <!-- Ingredientes -->
                <div class="mb-3">
                    <label for="ingredientes" class="form-label required-field">Ingredientes</label>
                    <textarea class="form-control" id="ingredientes" name="ingredientes" rows="5" required><?= htmlspecialchars(str_replace(["\\r\\n", "\\n", "\\r"], "\n", $plano["Ingredientes"] ?? '')) ?></textarea>
                    <small class="text-muted">Liste todos os ingredientes necessários para a preparação.</small>
                    <div class="invalid-feedback">
                        Por favor, liste os ingredientes necessários.
                    </div>
                </div>
                
                <!-- Preparação -->
                <div class="mb-3">
                    <label for="preparacao" class="form-label required-field">Modo de Preparo</label>
                    <textarea class="form-control" id="preparacao" name="preparacao" rows="5" required><?= htmlspecialchars(str_replace(["\\r\\n", "\\n", "\\r"], "\n", $plano["Preparacao"] ?? '')) ?></textarea>
                    <small class="text-muted">Descreva o passo a passo para a preparação.</small>
                    <div class="invalid-feedback">
                        Por favor, descreva o modo de preparo.
                    </div>
                </div>
                
                <!-- Informações Nutricionais -->
                <div class="info-nutricional">
                    <h4>Informações Nutricionais (por porção)</h4>
                    
                    <div class="row g-3">
                        <!-- Energia -->
                        <div class="col-md-6">
                            <label for="energia" class="form-label">Energia (kcal)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="energia" name="energia" step="0.01" min="0" 
                                       value="<?= htmlspecialchars($plano["Energia"] ?? '') ?>">
                                <span class="input-group-text">kcal</span>
                            </div>
                        </div>
                        
                        <!-- Proteínas -->
                        <div class="col-md-6">
                            <label for="proteinas" class="form-label">Proteínas</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="proteinas" name="proteinas" step="0.01" min="0" 
                                       value="<?= htmlspecialchars($plano["Proteinas"] ?? '') ?>">
                                <span class="input-group-text">g</span>
                            </div>
                        </div>
                        
                        <!-- Gorduras -->
                        <div class="col-md-6">
                            <label for="gorduras" class="form-label">Gorduras Totais</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="gorduras" name="gorduras" step="0.01" min="0" 
                                       value="<?= htmlspecialchars($plano["Gorduras"] ?? '') ?>">
                                <span class="input-group-text">g</span>
                            </div>
                        </div>
                        
                        <!-- Gorduras Saturadas -->
                        <div class="col-md-6">
                            <label for="gorduras_saturadas" class="form-label">Gorduras Saturadas</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="gorduras_saturadas" name="gorduras_saturadas" step="0.01" min="0" 
                                       value="<?= htmlspecialchars($plano["Gorduras_Saturadas"] ?? '') ?>">
                                <span class="input-group-text">g</span>
                            </div>
                        </div>
                        
                        <!-- Hidratos de Carbono -->
                        <div class="col-md-6">
                            <label for="hidratos_carbono" class="form-label">Hidratos de Carbono</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="hidratos_carbono" name="hidratos_carbono" step="0.01" min="0" 
                                       value="<?= htmlspecialchars($plano["Hidratos_Carbono"] ?? '') ?>">
                                <span class="input-group-text">g</span>
                            </div>
                        </div>
                        
                        <!-- Açúcares -->
                        <div class="col-md-6">
                            <label for="hidratos_acucares" class="form-label">dos quais Açúcares</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="hidratos_acucares" name="hidratos_acucares" step="0.01" min="0" 
                                       value="<?= htmlspecialchars($plano["Hidratos_Acucares"] ?? '') ?>">
                                <span class="input-group-text">g</span>
                            </div>
                        </div>
                        
                        <!-- Fibras -->
                        <div class="col-md-6">
                            <label for="fibras" class="form-label">Fibras</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="fibras" name="fibras" step="0.01" min="0" 
                                       value="<?= htmlspecialchars($plano["Fibras"] ?? '') ?>">
                                <span class="input-group-text">g</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botões -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="ver_nutricao.php?id=<?= $id_alimentacao ?>" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imagemAlimentacao = document.getElementById('imagemAlimentacao');
            const previewImagem = document.getElementById('previewImagem');
            const limparImagem = document.getElementById('limparImagem');
            const removerImagem = document.getElementById('removerImagem');
            const nutricaoForm = document.getElementById('nutricaoForm');
            const imagemFeedback = document.getElementById('imagemFeedback');
            
            // Validação de tipo de arquivo para imagens
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            const maxFileSize = 10 * 1024 * 1024; // 10MB para acomodar GIFs
            let imagemValida = true;
            
            // Função para validar a imagem
            function validarImagem(file) {
                // Reset estado anterior
                imagemAlimentacao.classList.remove('is-invalid');
                
                // Se não houver arquivo, consideramos válido (não é obrigatório)
                if (!file) {
                    imagemValida = true;
                    return true;
                }
                
                // Verificar tipo de arquivo
                if (!allowedTypes.includes(file.type)) {
                    imagemAlimentacao.classList.add('is-invalid');
                    imagemFeedback.textContent = `Apenas imagens JPG, PNG e GIF são permitidas. Tipo detectado: ${file.type}`;
                    imagemValida = false;
                    return false;
                }
                
                // Verificar tamanho do arquivo
                if (file.size > maxFileSize) {
                    imagemAlimentacao.classList.add('is-invalid');
                    imagemFeedback.textContent = 'A imagem deve ter no máximo 10MB.';
                    imagemValida = false;
                    return false;
                }
                
                imagemValida = true;
                return true;
            }
            
            // Preview de imagem com validação
            imagemAlimentacao.addEventListener('change', function() {
                previewImagem.innerHTML = '';
                
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    
                    // Validar o arquivo selecionado
                    if (validarImagem(file)) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'preview-image';
                            previewImagem.appendChild(img);
                        }
                        reader.readAsDataURL(file);
                        
                        // Desmarcar checkbox de remover imagem se uma nova imagem for selecionada
                        if (removerImagem) {
                            removerImagem.checked = false;
                        }
                    } else {
                        // Limpar o campo se o arquivo for inválido
                        this.value = '';
                    }
                }
            });
            
            limparImagem.addEventListener('click', function() {
                imagemAlimentacao.value = '';
                previewImagem.innerHTML = '';
                imagemAlimentacao.classList.remove('is-invalid');
                imagemValida = true;
            });
            
            // Se o checkbox de remover imagem for marcado, desabilite o input de arquivo
            if (removerImagem) {
                removerImagem.addEventListener('change', function() {
                    if (this.checked) {
                        imagemAlimentacao.value = '';
                        previewImagem.innerHTML = '';
                    }
                });
            }
            
            // Submit do formulário com validação
            nutricaoForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Previne o envio automático do formulário
                
                let formValido = true;
                
                // Validar o nome do plano (campo obrigatório)
                const nomeAlimentacao = document.getElementById('nomeAlimentacao');
                if (!nomeAlimentacao.value.trim()) {
                    nomeAlimentacao.classList.add('is-invalid');
                    nomeAlimentacao.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    formValido = false;
                } else {
                    nomeAlimentacao.classList.remove('is-invalid');
                }
                
                // Validar ingredientes (campo obrigatório)
                const ingredientes = document.getElementById('ingredientes');
                if (!ingredientes.value.trim()) {
                    ingredientes.classList.add('is-invalid');
                    if (formValido) {
                        ingredientes.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    formValido = false;
                } else {
                    ingredientes.classList.remove('is-invalid');
                }
                
                // Validar preparação (campo obrigatório)
                const preparacao = document.getElementById('preparacao');
                if (!preparacao.value.trim()) {
                    preparacao.classList.add('is-invalid');
                    if (formValido) {
                        preparacao.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    formValido = false;
                } else {
                    preparacao.classList.remove('is-invalid');
                }
                
                // Verificar se há imagem selecionada e se é válida
                if (imagemAlimentacao.files.length > 0 && !imagemValida) {
                    imagemAlimentacao.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    formValido = false;
                }
                
                // Se tudo estiver validado, enviar o formulário
                if (formValido) {
                    this.submit();
                }
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>