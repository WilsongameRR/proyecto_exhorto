<?php
include "php/conexion.php";

if (isset($_POST['id_municipio'])) {
    $id_municipio = intval($_POST['id_municipio']);
    $query = $con->query("SELECT id, tua, distrito, ciudad_sede 
                          FROM cat_tuas 
                          WHERE id_municipio = $id_municipio 
                          ORDER BY tua");

    $tuas = [];
    while ($row = $query->fetch_assoc()) {
        $tuas[] = $row;
    }

    echo json_encode($tuas);
}
?>
