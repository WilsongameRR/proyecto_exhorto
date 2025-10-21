<?php
include "conexion.php";
header('Content-Type: application/json');

// Verifica que venga el id_municipio por POST
if (!isset($_POST["id_municipio"])) {
    echo json_encode([]);
    exit;
}

$id_municipio = intval($_POST["id_municipio"]);

// OJO: usamos la tabla cat_nucleos_agrarios y la columna id_municipio
$sql = "SELECT id, nucleo FROM cat_nucleos_agrarios WHERE id_municipio = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_municipio);
$stmt->execute();
$result = $stmt->get_result();



$nucleos = [];
while ($row = $result->fetch_assoc()) {
    $nucleos[] = $row;
}

$stmt->close();
echo json_encode($nucleos);
?>
