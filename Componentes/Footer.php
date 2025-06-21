<html>
    <footer class="bg-black text-white py-16">
        <div class="container mx-auto px-6 md:px-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div>
                    <div class="text-3xl font-bold mb-6 header-font">
                        <span class="text-white">Esa</span><span class="text-orange-500">Fit</span><span class="text-white">24</span>
                    </div>
                    <p class="text-gray-400 mb-6">
                        Quando cuidas do teu corpo, libertas a tua alma.
                    </p>
                </div>
                
                <div>
                    <h3 class="text-xl font-bold mb-6 header-font">LINKS RÁPIDOS</h3>
                    <ul class="space-y-3">
                        <li><a href="Pagina_Inicial.php" class="footer-link text-gray-400 hover:text-white">Página Inicial</a></li>
                        <li><a href="Receitas.php" class="footer-link text-gray-400 hover:text-white">Receitas</a></li>
                        <li><a href="perfil.php" class="footer-link text-gray-400 hover:text-white">Perfil</a></li>
                        <li><a href="editar_perfil.php" class="footer-link text-gray-400 hover:text-white">Editar Perfil</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-bold mb-6 header-font">TREINOS</h3>
                    <ul class="space-y-3">
                        <li><a href="SUBMENU_Costas.php" class="footer-link text-gray-400 hover:text-white">Costas</a></li>
                        <li><a href="SUBMENU_Abdominal.php" class="footer-link text-gray-400 hover:text-white">Abdominal</a></li>
                        <li><a href="SUBMENU_Pernas.php" class="footer-link text-gray-400 hover:text-white">Pernas</a></li>
                        <li><a href="SUBMENU_Peito.php" class="footer-link text-gray-400 hover:text-white">Peito</a></li>
                        <li><a href="SUBMENU_Braços.php" class="footer-link text-gray-400 hover:text-white">Braços</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-bold mb-6 header-font">CONTACTOS</h3>
                    <ul class="space-y-4">
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-3 text-orange-500"></i>
                            <span class="text-gray-400">Av. da Liberdade, 110<br>Lisboa, Portugal</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone-alt mr-3 text-orange-500"></i>
                            <span class="text-gray-400">(+351) 21 555 5555</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-orange-500"></i>
                            <span class="text-gray-400">contato@esafit24.pt</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm mb-4 md:mb-0">
                    © 2025 EsaFit 24. Todos os direitos reservados.
                </p>
                <div class="flex space-x-6">
                    <a href="#" class="text-sm text-gray-500 hover:text-white">Termos de Uso</a>
                    <a href="#" class="text-sm text-gray-500 hover:text-white">Política de Privacidade</a>
                    <a href="#" class="text-sm text-gray-500 hover:text-white">Cookies</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript Global -->
    <script>
        // User profile dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const userProfileDropdown = document.getElementById('userProfileDropdown');
            if (userProfileDropdown) {
                const profileMenu = userProfileDropdown.querySelector('.profile-menu');
                let hideTimeout;
                
                userProfileDropdown.addEventListener('mouseenter', () => {
                    clearTimeout(hideTimeout);
                    profileMenu.style.display = 'block';
                });
                
                userProfileDropdown.addEventListener('mouseleave', () => {
                    hideTimeout = setTimeout(() => {
                        if (!profileMenu.matches(':hover')) {
                            profileMenu.style.display = 'none';
                        }
                    }, 100);
                });
                
                profileMenu.addEventListener('mouseenter', () => {
                    clearTimeout(hideTimeout);
                });
                
                profileMenu.addEventListener('mouseleave', () => {
                    hideTimeout = setTimeout(() => {
                        profileMenu.style.display = 'none';
                    }, 100);
                });
            }

            // Animation for elements when they scroll into view
            const slideInElements = document.querySelectorAll('.slide-in');
            const scaleUpElements = document.querySelectorAll('.scale-up');
            
            // Function to check if element is in viewport
            function isInViewport(element) {
                const rect = element.getBoundingClientRect();
                return (
                    rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.85 &&
                    rect.bottom >= 0
                );
            }
            
            // Function to handle scroll animations
            function handleScrollAnimations() {
                slideInElements.forEach((element, index) => {
                    if (isInViewport(element)) {
                        setTimeout(() => {
                            element.classList.add('active');
                        }, index * 200);
                    }
                });
                
                scaleUpElements.forEach((element, index) => {
                    if (isInViewport(element)) {
                        setTimeout(() => {
                            element.classList.add('active');
                        }, index * 150);
                    }
                });
            }
            
            // Initialize animations
            handleScrollAnimations();
            
            // Listen for scroll
            window.addEventListener('scroll', handleScrollAnimations);
        });
    </script>
    
    <?php echo isset($additional_scripts) ? $additional_scripts : ''; ?>
</body>
</html>