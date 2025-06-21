<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta | EsaFit 24</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --primary: #0F52BA;
            --primary-dark: #0A3A8C;
            --secondary: #FF5400;
            --dark: #1A202C;
            --light: #F8FAFC;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
        }
        
        h1, h2, h3, h4, .header-font {
            font-family: 'Oswald', sans-serif;
            font-style: italic;
            font-weight: 700;
            letter-spacing: 0.025em;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }
        
        .btn-primary {
            background-color: var(--secondary);
            color: white;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-family: 'Oswald', sans-serif;
            letter-spacing: 0.05em;
            font-style: italic;
            box-shadow: 0 4px 15px rgba(255, 84, 0, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 84, 0, 0.4);
        }
        
        .form-floating {
            position: relative;
        }
        
        .form-floating input {
            padding: 1.2rem 1rem 0.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .form-floating input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(255, 84, 0, 0.1);
        }
        
        .form-floating label {
            position: absolute;
            top: 1.2rem;
            left: 1rem;
            transition: all 0.3s ease;
            pointer-events: none;
            color: #64748b;
            font-weight: 500;
        }
        
        .form-floating input:focus + label,
        .form-floating input:not(:placeholder-shown) + label {
            top: 0.25rem;
            font-size: 0.75rem;
            color: var(--secondary);
            font-weight: 600;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        }
        
        .photo-upload-area {
            background: rgba(255, 255, 255, 0.1);
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 1rem;
            transition: all 0.3s ease;
        }
        
        .photo-upload-area:hover {
            border-color: var(--secondary);
            background: rgba(255, 84, 0, 0.05);
        }
        
        .photo-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 3px;
            transition: all 0.3s ease;
        }
        
        .photo-preview-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert {
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(239, 68, 68, 0.3);
            background: rgba(239, 68, 68, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }
        .stagger-5 { animation-delay: 0.5s; }
        .stagger-6 { animation-delay: 0.6s; }
        
        .bg-hero {
            background: url('https://images.unsplash.com/photo-1605296867304-46d5465a13f1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }
        
        .bg-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(15, 82, 186, 0.7), rgba(255, 84, 0, 0.5));
            backdrop-filter: blur(2px);
        }
    </style>
</head>
<body class="bg-hero min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4 relative z-10">
        <div class="glass-card w-full max-w-2xl p-8 md:p-12">
            <!-- Header -->
            <div class="text-center mb-8 fade-in">
                <div class="text-5xl md:text-6xl font-bold mb-4 header-font text-white">
                    <span>Esa</span><span class="text-orange-500">Fit</span><span>24</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-white header-font mb-2">CRIAR CONTA</h1>
                <p class="text-white/80 text-lg">Junte-se à nossa comunidade fitness</p>
            </div>
            
            <?php
            // Mostrar erros (se existirem)
            if (isset($_SESSION['register_errors']) && !empty($_SESSION['register_errors'])) {
                echo '<div class="alert text-red-100 fade-in stagger-1">';
                echo '<div class="flex items-center gap-2 mb-2">';
                echo '<i class="fas fa-exclamation-triangle text-red-400"></i>';
                echo '<span class="font-semibold">Erro ao criar conta:</span>';
                echo '</div>';
                foreach ($_SESSION['register_errors'] as $error) {
                    echo '<p class="ml-6">• ' . htmlspecialchars($error) . '</p>';
                }
                echo '</div>';
                unset($_SESSION['register_errors']);
            }
            
            // Recuperar dados preenchidos anteriormente
            $nome = isset($_SESSION['register_data']['nome']) ? htmlspecialchars($_SESSION['register_data']['nome']) : '';
            $idade = isset($_SESSION['register_data']['idade']) ? htmlspecialchars($_SESSION['register_data']['idade']) : '';
            $email = isset($_SESSION['register_data']['email']) ? htmlspecialchars($_SESSION['register_data']['email']) : '';
            $peso = isset($_SESSION['register_data']['peso']) ? htmlspecialchars($_SESSION['register_data']['peso']) : '';
            $altura = isset($_SESSION['register_data']['altura']) ? htmlspecialchars($_SESSION['register_data']['altura']) : '';
            
            if (isset($_SESSION['register_data'])) {
                unset($_SESSION['register_data']);
            }
            ?>
            
            <form id="registerForm" action="processa_criar_conta.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()" class="space-y-6">
                
                <!-- Foto de Perfil -->
                <div class="photo-upload-area p-6 text-center fade-in stagger-2">
                    <div class="photo-preview mx-auto mb-4" id="photoPreview">
                        <div class="photo-preview-inner">
                            <img src="" alt="Foto de perfil" id="previewImage" class="w-full h-full object-cover rounded-full" style="display: none;">
                            <div id="photoPlaceholder" class="text-white/60">
                                <i class="fas fa-camera text-3xl mb-2"></i>
                                <p class="text-sm">Foto de Perfil</p>
                            </div>
                        </div>
                    </div>
                    <input type="file" id="fotoPerfil" name="fotoPerfil" accept="image/*" class="hidden">
                    <button type="button" class="btn-primary" onclick="document.getElementById('fotoPerfil').click()">
                        <i class="fas fa-upload mr-2"></i>CARREGAR FOTO
                    </button>
                </div>
                
                <!-- Nome Completo -->
                <div class="form-floating fade-in stagger-3">
                    <input type="text" id="nome" name="nome" value="<?php echo $nome; ?>" placeholder=" " required class="w-full">
                    <label for="nome">
                        <i class="fas fa-user mr-2 text-orange-500"></i>Nome Completo
                    </label>
                    <div id="nomeError" class="error-message" style="display: none;">
                        <i class="fas fa-exclamation-circle"></i>
                        Por favor, insira o seu nome completo.
                    </div>
                </div>
                
                <!-- Linha com Idade e Email -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 fade-in stagger-4">
                    <div class="form-floating">
                        <input type="number" id="idade" name="idade" min="12" max="100" value="<?php echo $idade; ?>" placeholder=" " required class="w-full">
                        <label for="idade">
                            <i class="fas fa-birthday-cake mr-2 text-orange-500"></i>Idade
                        </label>
                        <div id="idadeError" class="error-message" style="display: none;">
                            <i class="fas fa-exclamation-circle"></i>
                            Idade inválida (12-100 anos).
                        </div>
                    </div>
                    
                    <div class="form-floating">
                        <input type="email" id="email" name="email" value="<?php echo $email; ?>" placeholder=" " required class="w-full">
                        <label for="email">
                            <i class="fas fa-envelope mr-2 text-orange-500"></i>Email
                        </label>
                        <div id="emailError" class="error-message" style="display: none;">
                            <i class="fas fa-exclamation-circle"></i>
                            Email inválido.
                        </div>
                    </div>
                </div>
                
                <!-- Passwords -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 fade-in stagger-5">
                    <div class="form-floating">
                        <input type="password" id="password" name="password" placeholder=" " required class="w-full">
                        <label for="password">
                            <i class="fas fa-lock mr-2 text-orange-500"></i>Palavra-passe
                        </label>
                        <div id="passwordError" class="error-message" style="display: none;">
                            <i class="fas fa-exclamation-circle"></i>
                            A palavra-passe deve ter pelo menos 8 caracteres.
                        </div>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder=" " required class="w-full">
                        <label for="confirmPassword">
                            <i class="fas fa-lock mr-2 text-orange-500"></i>Confirmar Palavra-passe
                        </label>
                        <div id="confirmPasswordError" class="error-message" style="display: none;">
                            <i class="fas fa-exclamation-circle"></i>
                            As palavras-passe não coincidem.
                        </div>
                    </div>
                </div>
                
                <!-- Peso e Altura -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 fade-in stagger-6">
                    <div class="form-floating">
                        <input type="number" id="peso" name="peso" step="0.1" min="30" max="250" value="<?php echo $peso; ?>" placeholder=" " required class="w-full">
                        <label for="peso">
                            <i class="fas fa-weight mr-2 text-orange-500"></i>Peso (kg)
                        </label>
                        <div id="pesoError" class="error-message" style="display: none;">
                            <i class="fas fa-exclamation-circle"></i>
                            Peso inválido (30-250 kg).
                        </div>
                    </div>
                    
                    <div class="form-floating">
                        <input type="number" id="altura" name="altura" step="0.01" min="1.00" max="2.50" value="<?php echo $altura; ?>" placeholder=" " required class="w-full">
                        <label for="altura">
                            <i class="fas fa-ruler-vertical mr-2 text-orange-500"></i>Altura (m)
                        </label>
                        <div id="alturaError" class="error-message" style="display: none;">
                            <i class="fas fa-exclamation-circle"></i>
                            Altura inválida (1.00-2.50 m).
                        </div>
                    </div>
                </div>
                
                <!-- Botão Submit -->
                <button type="submit" class="btn-primary w-full text-xl py-4 fade-in stagger-6">
                    <i class="fas fa-user-plus mr-2"></i>CRIAR CONTA
                </button>
            </form>
            
            <!-- Links -->
            <div class="text-center mt-8 fade-in stagger-6">
                <p class="text-white/80 text-lg">
                    Já tem uma conta? 
                    <a href="login.php" class="text-orange-400 hover:text-orange-300 font-semibold transition-colors duration-300 hover:underline">
                        Iniciar Sessão
                    </a>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        // Preview da foto de perfil
        document.getElementById('fotoPerfil').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImage = document.getElementById('previewImage');
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                    document.getElementById('photoPlaceholder').style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });
        
        function validateForm() {
            let valid = true;
            const nome = document.getElementById('nome').value;
            const idade = document.getElementById('idade').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const peso = document.getElementById('peso').value;
            const altura = document.getElementById('altura').value;
            
            // Reseta todas as mensagens de erro
            document.querySelectorAll('.error-message').forEach(el => {
                el.style.display = 'none';
            });
            
            // Valida nome
            if (nome.trim().length < 3) {
                document.getElementById('nomeError').style.display = 'flex';
                valid = false;
            }
            
            // Valida idade
            if (idade < 12 || idade > 120) {
                document.getElementById('idadeError').style.display = 'flex';
                valid = false;
            }
            
            // Valida email
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                document.getElementById('emailError').style.display = 'flex';
                valid = false;
            }
            
            // Valida password
            if (password.length < 8) {
                document.getElementById('passwordError').style.display = 'flex';
                valid = false;
            }
            
            // Confirma password
            if (password !== confirmPassword) {
                document.getElementById('confirmPasswordError').style.display = 'flex';
                valid = false;
            }
            
            // Valida peso
            if (peso < 30 || peso > 250) {
                document.getElementById('pesoError').style.display = 'flex';
                valid = false;
            }
            
            // Valida altura
            if (altura < 1.00 || altura > 2.50) {
                document.getElementById('alturaError').style.display = 'flex';
                valid = false;
            }
            
            return valid;
        }
        
        // Adiciona efeitos de focus nos inputs
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>