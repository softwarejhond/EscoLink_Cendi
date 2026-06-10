<?php
require_once __DIR__ . '/../conexion.php';

$fecha = date('dmYHis');

$usuarios = [
    ['nombre' => 'Alarcón Hernández Cristian Fabián', 'username' => 1104700566, 'email' => 'matematicasdesdecero08@gmail.com', 'telefono' => ''],
    ['nombre' => 'Alape Sánchez Angie Alexandra', 'username' => 1017226673, 'email' => 'angiea.alape@gmail.com', 'telefono' => ''],
    ['nombre' => 'Arrieta Urbina Carlos Alberto', 'username' => 1104405366, 'email' => 'pipelam24@hotmail.com', 'telefono' => ''],
    ['nombre' => 'Cárdenas Arango Adriana María', 'username' => 43115183, 'email' => 'adrianatecno.cendi@gmail.com', 'telefono' => ''],
    ['nombre' => 'Cautiva Sosa Francy Lorena', 'username' => 1020449759, 'email' => 'francycautiva@gmail.com', 'telefono' => ''],
    ['nombre' => 'Chaverra Córdoba Yerlyn', 'username' => 1077425395, 'email' => 'daneisser@hotmail.com', 'telefono' => ''],
    ['nombre' => 'Chaverra Córdoba Zunilda', 'username' => 1149434448, 'email' => 'zunisita16@gmail.com', 'telefono' => ''],
    ['nombre' => 'Cuadrado Hidalgo Aldair Andrés', 'username' => 1063082115, 'email' => 'aldaircuadrado992@gmail.com', 'telefono' => ''],
    ['nombre' => 'Estrada Chica Luis Carlos', 'username' => 8126068, 'email' => 'luiscarlosestradachica745@gmail.com', 'telefono' => ''],
    ['nombre' => 'Gómez Cardona David Alejandro', 'username' => 1048045640, 'email' => 'davis-140@hotmail.com', 'telefono' => ''],
    ['nombre' => 'Lemos Rentería Ana Milena', 'username' => 1077432287, 'email' => 'amlemos39@gmail.com', 'telefono' => ''],
    ['nombre' => 'López Berrocal Alberto José', 'username' => 1063162139, 'email' => 'albhertolopez21@gmail.com', 'telefono' => ''],
    ['nombre' => 'López Casas Sandra Patricia', 'username' => 43971982, 'email' => 'sandrapatricialopezcasas@gmail.com', 'telefono' => ''],
    ['nombre' => 'Medrano Prestán Fredy Elías', 'username' => 73149714, 'email' => 'fredyeliasmedrano82@gmail.com', 'telefono' => ''],
    ['nombre' => 'Morales Pescador Gildardo Antonio', 'username' => 15924899, 'email' => 'gildardomorales.docente@gmail.com', 'telefono' => ''],
    ['nombre' => 'Mora Rivas Lucy Fernanda', 'username' => 1035582810, 'email' => 'lufemori@gmail.com', 'telefono' => ''],
    ['nombre' => 'Olarte Hernández Yulieth Catherine', 'username' => 43871435, 'email' => 'yolarte173@gmail.com', 'telefono' => ''],
    ['nombre' => 'Olaya González Lady Tiffany', 'username' => 1128432694, 'email' => '01tiffany10@gmail.com', 'telefono' => ''],
    ['nombre' => 'Pacheco Julio Kell Yesit', 'username' => 8056921, 'email' => 'Kellyepacheco@gmail.com', 'telefono' => ''],
    ['nombre' => 'Palacios Moreno Ana Luisa', 'username' => 54253104, 'email' => 'alupamo@hotmail.com', 'telefono' => ''],
    ['nombre' => 'Palacios Casas Diego Emir', 'username' => 1017219434, 'email' => 'diego_e44@hotmail.com', 'telefono' => ''],
    ['nombre' => 'Restrepo Madrid Nelson Daniel', 'username' => 1033340469, 'email' => 'nelsondanielrestrepo@gmail.com', 'telefono' => ''],
    ['nombre' => 'Restrepo Morales Wilmar Mauricio', 'username' => 98603197, 'email' => 'maurpo2015@gmail.com', 'telefono' => ''],
    ['nombre' => 'Rivas Domínguez Elis Viviana', 'username' => 35896298, 'email' => 'elisviviana0810@gmail.com', 'telefono' => ''],
    ['nombre' => 'Salazar Restrepo Deisy Alejandra', 'username' => 1152202270, 'email' => 'deyalesar@gmail.com', 'telefono' => ''],
    ['nombre' => 'Villa Jaramillo Hamilton Eliécer', 'username' => 1152704853, 'email' => 'jvjaramillo@censa.edu.co', 'telefono' => ''],
    ['nombre' => 'Zapata Vélez María Alejandra', 'username' => 1017249685, 'email' => 'mazv08.97@gmail.com', 'telefono' => ''],
    ['nombre' => 'Anaya Galeano Marcela Alejandra', 'username' => 1017236581, 'email' => 'marcelaanaya@cendiacademico.edu.co', 'telefono' => ''],
    ['nombre' => 'García Areiza Sandra Milena', 'username' => 22212273, 'email' => 'garciaareizasandramilena@gmail.com', 'telefono' => ''],
    ['nombre' => 'Machuca Calao Omar de Jesus', 'username' => 71986902, 'email' => '01rupi01@gmail.com', 'telefono' => ''],
    ['nombre' => 'Machado Lemus Haismid', 'username' => 4799620, 'email' => 'haismid@gmail.com', 'telefono' => ''],
    ['nombre' => 'Martínez Medina Elizabeth del Socorro', 'username' => 43437651, 'email' => 'salomena0217@gmail.com', 'telefono' => ''],
    ['nombre' => 'Mejía Martínez Elizabeth', 'username' => 43204449, 'email' => 'mejiamartinez3@hotmail.com', 'telefono' => ''],
    ['nombre' => 'Sarmiento Moreno Carlos Andrés', 'username' => 71748578, 'email' => 'csarmientomoreno@gmail.com', 'telefono' => ''],
];

$usernames = array_column($usuarios, 'username');

$existing = [];
$result = mysqli_query($conn, "SELECT username FROM users WHERE username IN (" . implode(',', $usernames) . ")");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $existing[$row['username']] = true;
    }
}

$insertados = 0;
$omitidos = 0;
$errores = [];

$query = "INSERT INTO users (username, password, nombre, rol, rol_informativo, extra_rol, foto, orden, fechaCreacionUser, email, genero, telefono, direccion, edad) VALUES (?, ?, ?, 5, 0, 0, 'default.png', 0, ?, ?, '', ?, '', 0)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'isssss', $username, $password_hash, $nombre, $fecha, $email, $telefono);

foreach ($usuarios as $u) {
    $username = $u['username'];

    if (isset($existing[$username])) {
        $omitidos++;
        continue;
    }

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

echo "Insertados: $insertados | Omitidos (ya existían): $omitidos\n";
if (!empty($errores)) {
    echo "Errores:\n" . implode("\n", $errores) . "\n";
}
