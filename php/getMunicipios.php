<?php
include "conexion.php";
header('Content-Type: application/json');

if (!isset($_POST["id_estado"])) {
    echo json_encode([]);
    exit;
}

$id_estado = intval($_POST["id_estado"]);

$sql = "SELECT id, municipio FROM cat_municipios WHERE id_estado = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_estado);
$stmt->execute();
$result = $stmt->get_result();

$municipios = [];
while ($row = $result->fetch_assoc()) {
    $municipios[] = $row;
}

$stmt->close();
echo json_encode($municipios);
?>
