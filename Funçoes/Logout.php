<?php
session_start();

// Definir mensagem de sucesso antes de destruir a sessão
$_SESSION['logout_message'] = "Sessão terminada com sucesso!";

sleep(1);

// Armazenar a mensagem em uma variável temporária
$logout_message = $_SESSION['logout_message'];

session_destroy();

// Iniciar nova sessão apenas para a mensagem
session_start();
$_SESSION['logout_message'] = $logout_message;

header("Location: login.php");
exit();
?>