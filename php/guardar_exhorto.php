<?php
session_start();
if (empty($_SESSION["userid"])) {
    header("Location: /proyecto_exhorto/index.php?error=Sin_sesion_iniciada");
    exit();
}

include "conexion.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_usuario = intval($_SESSION["userid"]);
    $id_tua_origen = intval($_SESSION["fk_id_tua"]);

    // ==============================
    // CAMPOS PRINCIPALES
    // ==============================
    $num_expediente   = $con->real_escape_string($_POST["num_expediente"]);
    $id_estado        = intval($_POST["id_estado_exh"]);
    $id_municipio     = intval($_POST["id_municipio_exh"]);
    $id_nucleo        = intval($_POST["id_nucleo_exh"]);
    $exhorto          = $con->real_escape_string($_POST["exhorto"]);
    $id_tua_destino   = intval($_POST["id_tua_destino_exh"]);

    // ==============================
    // INSERTAR EN TABLA EXPEDIENTES
    // ==============================
    $sql = "INSERT INTO expedientes (
                num_expediente,
                exhorto,
                id_estado,
                id_municipio,
                id_nucleo,
                id_tua_origen,
                id_tua_destino,
                f_registro
            ) VALUES (
                '$num_expediente',
                '$exhorto',
                $id_estado,
                $id_municipio,
                $id_nucleo,
                $id_tua_origen,
                $id_tua_destino,
                NOW()
            )";

    if ($con->query($sql)) {
        $id_expediente = $con->insert_id;

        // ==========================================
        // GUARDAR LAS DILIGENCIAS RELACIONADAS
        // ==========================================
        if (!empty($_POST["diligencias"])) {
            foreach ($_POST["diligencias"] as $dil) {

                $tipo              = $con->real_escape_string($dil["diligencia"]);
                $otro              = isset($dil["otro_diligencia"]) ? $con->real_escape_string($dil["otro_diligencia"]) : "";
                $nombre_dest       = isset($dil["nombre_destinatario"]) ? $con->real_escape_string($dil["nombre_destinatario"]) : "";
                $fecha_dil         = !empty($dil["fecha_diligencia"]) ? "'".$con->real_escape_string($dil["fecha_diligencia"])."'" : "NULL";
                $hora_dil          = !empty($dil["hora_diligencia"]) ? "'".$con->real_escape_string($dil["hora_diligencia"])."'" : "NULL";
                $estatus_dil       = $con->real_escape_string($dil["estatus_diligencia"]);
                $observaciones_dil = isset($dil["observaciones_diligencia"]) ? $con->real_escape_string($dil["observaciones_diligencia"]) : "";

                $sql_dil = "INSERT INTO exhorto_diligencias (
                                id_exhorto,
                                diligencia,
                                otro_diligencia,
                                nombre_destinatario,
                                fecha_diligencia,
                                hora_diligencia,
                                estatus_diligencia,
                                observaciones_diligencia
                            ) VALUES (
                                $id_expediente,
                                '$tipo',
                                '$otro',
                                '$nombre_dest',
                                $fecha_dil,
                                $hora_dil,
                                '$estatus_dil',
                                '$observaciones_dil'
                            )";
                $con->query($sql_dil);
            }
        }

        // ==========================================
        // GUARDAR MOVIMIENTO AUTOMÁTICO
        // ==========================================
        $accion = "Creación de exhorto";
        $sql_mov = "INSERT INTO movimientos_expedientes (
                        id_expediente,
                        id_tua_origen,
                        id_tua_destino,
                        accion,
                        usuario_id,
                        observaciones
                    ) VALUES (
                        $id_expediente,
                        $id_tua_origen,
                        $id_tua_destino,
                        '$accion',
                        $id_usuario,
                        'Registro inicial de exhorto'
                    )";
        $con->query($sql_mov);

        // ==========================================
        // REDIRECCIÓN CON MENSAJE DE ÉXITO
        // ==========================================
        header("Location: /proyecto_exhorto/bienvenida.php?msg=guardado");
        exit();

    } else {
        // ❌ Error al guardar el exhorto
        die("❌ Error al guardar el exhorto: " . $con->error);
    }

} else {
    // Si el método no es POST, redirige
    header("Location: /proyecto_exhorto/bienvenida.php");
    exit();
}
?>
