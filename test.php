<?php
$passwordIngresada = '.@`DR4rq{GNg80RE^3qr';
$hashBD = '$2y$10$7FJm6yMZzj3q6w7ZKQ5Z5e9n7V5m5Zy5y4k3nYyYF8Q1mH9CqXQ2G';

if (password_verify($passwordIngresada, $hashBD)) {
    echo 'Contraseña correcta';
} else {
    echo 'Contraseña incorrecta';
}


$password = '.@`DR4rq{GNg80RE^3qr';

$hash = password_hash($password, PASSWORD_BCRYPT);

echo $hash;
