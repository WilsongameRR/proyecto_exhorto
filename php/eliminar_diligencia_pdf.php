<?php
session_start();
if (empty($_SESSION["userid"])) {
    header("Location: ../index.php?error=Sin_sesion_iniciada");
    exit();
}

include "conexion.php";

$id_diligencia = intval($_POST["id_diligencia"] ?? 0);
$id_expediente = intval($_POST["id_expediente"] ?? 0);

if ($id_diligencia <= 0) {
    die("ID inválido.");
}

// Buscar ruta del archivo
$sql = "SELECT pdf_path FROM exhorto_diligencias WHERE id_diligencia = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_diligencia);
$stmt->execute();
$res = $stmt->get_result();
$pdf = $res->fetch_assoc()["pdf_path"] ?? null;
$stmt->close();

// Eliminar archivo físico si existe
if ($pdf && file_exists("../" . $pdf)) {
    unlink("../" . $pdf);
}

// Quitar ruta en la base de datos
$sql = "UPDATE exhorto_diligencias SET pdf_path = NULL WHERE id_diligencia = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_diligencia);
$stmt->execute();
$stmt->close();

// Redirigir con mensaje
header("Location: ../viewExpediente.php?id=$id_expediente&msg=pdf_deleted");
exit();
?>
