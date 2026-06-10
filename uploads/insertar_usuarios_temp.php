<?php
require_once __DIR__ . '/../conexion.php';

$fecha = date('dmYHis');

$usuarios = [
    ['nombre' => 'Ana Luisa Palacios Moreno', 'username' => 54253104, 'email' => 'alupamo27@gmail.com', 'telefono' => '3218777268'],
    ['nombre' => 'David Alejandro Gomez cardona', 'username' => 1048045640, 'email' => 'davidconstitucionpolitica@gmail.com', 'telefono' => '3137167363'],
    ['nombre' => 'Angie Alexandra Alape Sánchez', 'username' => 1017226673, 'email' => 'angiea.alape@gmail.com', 'telefono' => '3108581627'],
    ['nombre' => 'Francy Lorena Cautiva Sosa', 'username' => 1020449759, 'email' => 'lore.cautiva92@gmail.com', 'telefono' => '3134347843'],
    ['nombre' => 'Lucy Fernanda Mora Rivas', 'username' => 1035582810, 'email' => 'lufemori@gmail.com', 'telefono' => '3147160885'],
    ['nombre' => 'Alberto Jose Lopez Berrocal', 'username' => 1063162139, 'email' => 'albhertolopez21@gmail.com', 'telefono' => '3106510780'],
    ['nombre' => 'Carlos Alberto Arrieta Urbiña', 'username' => 1104405366, 'email' => 'carlosalbertoarrietaurbina2@gmail.com', 'telefono' => '3115897754'],
    ['nombre' => 'Lady Tiffany Olaya González', 'username' => 1128432694, 'email' => 'leidysolaya9@gmail.com', 'telefono' => '3245669832'],
    ['nombre' => 'Zunilda Chaverra Córdoba', 'username' => 1149434448, 'email' => 'Zunisita16@gmail.com', 'telefono' => '3102221790'],
    ['nombre' => 'Aldair Andrés cuadrado hidalgo', 'username' => 1063082115, 'email' => 'aldaircuadrado992@gmail.com', 'telefono' => '3147936971'],
    ['nombre' => 'Luis Carlos Estrada Chica', 'username' => 8126068, 'email' => 'luiscarlosestradachica745@gmail.com', 'telefono' => '3202297684'],
    ['nombre' => 'Hamilton Eliecer Villa Jaramillo', 'username' => 1152704853, 'email' => 'hamiltonvilla@cendiacademico.edu.co', 'telefono' => '3104436236'],
    ['nombre' => 'Yulieth Catherine Olarte Hernández', 'username' => 43871435, 'email' => 'yolarte173@gmail.com', 'telefono' => '3054416887'],
    ['nombre' => 'Wilmar Mauricio Restrepo Morales', 'username' => 98603197, 'email' => 'maurpo2015@gmail.com', 'telefono' => '3217600444'],
    ['nombre' => 'Adriana cárdenas', 'username' => 43115183, 'email' => 'adrianatecno.cendi@gmail.com', 'telefono' => '3205432169'],
    ['nombre' => 'Nelson Daniel Restrepo Madrid', 'username' => 1033340469, 'email' => 'nelsondanielrestrepo@gmail.com', 'telefono' => '3208900965'],
    ['nombre' => 'Kell Yesit Pacheco', 'username' => 8056921, 'email' => 'Pachecokellyesit@gmail.com', 'telefono' => '3116030519'],
    ['nombre' => 'Gildardo Antonio Morales Pescador', 'username' => 15924899, 'email' => 'gildardomorales.docente@gmail.com', 'telefono' => '3057944421'],
    ['nombre' => 'Yerlyn Chaverra Cordoba', 'username' => 1077425395, 'email' => 'Yerlynchaverra@cendiacademico.edu.co', 'telefono' => '3117121402'],
    ['nombre' => 'Cristian Fabián Alarcón Hernández', 'username' => 1104700566, 'email' => 'matematicasdesdecero08@gmail.com', 'telefono' => '3144247226'],
    ['nombre' => 'Elis Viviana Rivas Domínguez', 'username' => 35896298, 'email' => 'elisviviana0810@gmail.com', 'telefono' => '3045706057'],
    ['nombre' => 'Deisy Alejandra Salazar Restrepo', 'username' => 1152202270, 'email' => 'deyalesar@gmail.com', 'telefono' => '3007510879'],
    ['nombre' => 'Diego emir palacios casas', 'username' => 1017219434, 'email' => 'diego_e44@hotmail.com', 'telefono' => '3012259698'],
    ['nombre' => 'Ana Milena Lemos Renteria', 'username' => 1077432287, 'email' => 'amlemos39@gmail.com', 'telefono' => '3206573340'],
    ['nombre' => 'fredy elías medrano prestán', 'username' => 73149714, 'email' => 'fredyeliasmedrano82@gmail.com', 'telefono' => '3505085700'],
];

$query = "INSERT INTO users (username, password, nombre, rol, rol_informativo, extra_rol, foto, orden, fechaCreacionUser, email, genero, telefono, direccion, edad) VALUES (?, ?, ?, 5, 0, 0, 'default.png', 0, ?, ?, '', ?, '', 0)";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'isssss', $username, $password_hash, $nombre, $fecha, $email, $telefono);

$usernames = array_column($usuarios, 'username');
$placeholders = implode(',', array_fill(0, count($usernames), '?'));
$types = str_repeat('i', count($usernames));
$delete_query = "DELETE FROM users WHERE username IN ($placeholders)";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, $types, ...$usernames);
mysqli_stmt_execute($delete_stmt);
mysqli_stmt_close($delete_stmt);

$insertados = 0;
$errores = [];

foreach ($usuarios as $u) {
    $username = $u['username'];
    $password_hash = password_hash((string)$username, PASSWORD_DEFAULT);
    $nombre = $u['nombre'];
    $email = $u['email'];
    $telefono = $u['telefono'];

    if (mysqli_stmt_execute($stmt)) {
        $insertados++;
    } else {
        $errores[] = $username . ': ' . mysqli_error($conn);
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

echo "Usuarios insertados correctamente: $insertados\n";
if (!empty($errores)) {
    echo "Errores:\n" . implode("\n", $errores) . "\n";
}
