<?php
require_once __DIR__ . '/../conexion.php';

$nombres = [
    '%Ana Luisa Palacios%',
    '%David Alejandro Gomez%',
    '%Angie Alexandra Alape%',
    '%Carlos Alberto Arrieta%',
    '%Lady Tiffany Olaya%',
    '%Aldair Andrés%',
    '%Elis Viviana Rivas%',
    '%Kell Yesit Pacheco%',
    '%Nelson%Restrepo%',
    '%Yerlyn Chaverra%',
    '%Fredy Elias Medrano%',
    '%Adriana Cardenas%',
];

echo "=== Buscando usuarios ===\n\n";
$encontrados = [];

foreach ($nombres as $n) {
    $query = "SELECT id, username, nombre, rol FROM users WHERE nombre LIKE ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $n);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['id'] . " | " . $row['username'] . " | " . $row['nombre'] . " | rol: " . $row['rol'] . "\n";
        $encontrados[] = $row['id'];
    }
    mysqli_stmt_close($stmt);
}

if (!empty($encontrados)) {
    $ids = implode(',', $encontrados);
    $update = "UPDATE users SET rol = 1 WHERE id IN ($ids)";
    if (mysqli_query($conn, $update)) {
        echo "\n=== Actualizados " . mysqli_affected_rows($conn) . " usuarios a rol = 1 ===\n";
    } else {
        echo "\nError al actualizar: " . mysqli_error($conn) . "\n";
    }
}

mysqli_close($conn);
