<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "conexion.php";

/* ==========================================
   🔹 GUARDAR ESTATUS INDIVIDUAL (AJAX)
========================================== */
if (isset($_POST["ajax_guardar_estatus_diligencia"])) {
    $id = intval($_POST["id_diligencia"]);
    $estatus = $_POST["estatus_diligencia"];
    $sql = "UPDATE exhorto_diligencias SET estatus_diligencia=? WHERE id_diligencia=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("si", $estatus, $id);
    $stmt->execute();
    $stmt->close();
    echo "ok";
    exit;
}

/* ==========================================
   🔹 GUARDAR FECHA / HORA / OBSERVACIONES INDIVIDUAL
========================================== */
if (isset($_POST["ajax_guardar_datos"])) {
    $id_diligencia = intval($_POST["id_diligencia"]);
    $fecha = trim($_POST["fecha_diligencia"] ?? "");
    $hora  = trim($_POST["hora_diligencia"] ?? "");
    $obs   = $_POST["observaciones_diligencia"] ?? null;

    $fecha = ($fecha === "") ? null : $fecha;
    $hora  = ($hora === "") ? null : $hora;

    $sql = "UPDATE exhorto_diligencias 
            SET fecha_diligencia=?, hora_diligencia=?, observaciones_diligencia=? 
            WHERE id_diligencia=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssi", $fecha, $hora, $obs, $id_diligencia);
    $stmt->execute();
    $stmt->close();
    echo "ok";
    exit;
}

/* ==========================================
   🔹 GUARDAR FECHA/HORA GLOBAL (AUDIENCIA)
========================================== */
if (isset($_POST["ajax_guardar_global"])) {
    $tipo = $_POST["tipo_diligencia"] ?? "";
    $fecha = trim($_POST["fecha_diligencia"] ?? "");
    $hora  = trim($_POST["hora_diligencia"] ?? "");
    $id_expediente = intval($_POST["id_expediente"] ?? 0);

    $fecha = ($fecha === "") ? null : $fecha;
    $hora  = ($hora === "") ? null : $hora;

    if (!empty($tipo) && $id_expediente > 0) {
        $sql = "UPDATE exhorto_diligencias 
                SET fecha_diligencia=?, hora_diligencia=? 
                WHERE LOWER(diligencia)=LOWER(?) AND id_exhorto=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssi", $fecha, $hora, $tipo, $id_expediente);
        $stmt->execute();
        $stmt->close();
        echo "ok";
        exit;
    }
}

/* ==========================================
   🔹 SUBIR PDF SIN RECARGAR (AJAX)
========================================== */
if (isset($_POST["ajax_subir_pdf"])) {
    $id_diligencia = intval($_POST["id_diligencia"]);
    $id_expediente = intval($_POST["id_expediente"]);
    $ruta_final = "";

    if (!empty($_FILES["pdf_file"]["name"])) {
        $nombreArchivo = "diligencia_" . $id_diligencia . "_" . time() . ".pdf";
        $ruta_final = "../uploads/" . $nombreArchivo;

        if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $ruta_final)) {
            $sql = "UPDATE exhorto_diligencias SET pdf_path=? WHERE id_diligencia=?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("si", $ruta_final, $id_diligencia);
            $stmt->execute();
            $stmt->close();

            echo "ok:" . $ruta_final;
            exit;
        } else {
            echo "error_subida";
            exit;
        }
    }
}

/* ==========================================
   🔹 FUNCIÓN PRINCIPAL: MOSTRAR DILIGENCIAS
========================================== */
function mostrarTablaDiligencias($con, $id_expediente, $id_tua_sesion) {
    // Obtener TUA origen y destino
    $sqlTuas = "SELECT id_tua_origen, id_tua_destino FROM expedientes WHERE id_expediente = ?";
    $stmtT = $con->prepare($sqlTuas);
    $stmtT->bind_param("i", $id_expediente);
    $stmtT->execute();
    $stmtT->bind_result($id_tua_remitente, $id_tua_destinatario);
    $stmtT->fetch();
    $stmtT->close();

    $es_destinatario = ($id_tua_sesion == $id_tua_destinatario);
    $es_remitente = ($id_tua_sesion == $id_tua_remitente);

    // Cargar diligencias
    $sqlD = "SELECT * FROM exhorto_diligencias WHERE id_exhorto = ?";
    $stmt = $con->prepare($sqlD);
    $stmt->bind_param("i", $id_expediente);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0) {
        echo "<p class='text-center'><i>No hay diligencias registradas.</i></p>";
        return;
    }

    // Agrupar diligencias por tipo
    $diligencias = [];
    while ($d = $res->fetch_assoc()) {
        $tipo = ucfirst(strtolower(trim($d["diligencia"])));
        $diligencias[$tipo][] = $d;
    }
    $stmt->close();
?>
<style>
.table-diligencias {
    width: 85%;
    margin: 15px auto 25px;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    border-collapse: separate;
    border-spacing: 0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    font-size: 14px;
}
.table-diligencias th {
    background-color: #f8f9fa;
    text-align: center;
    vertical-align: middle;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 1px solid #dee2e6;
    padding: 10px;
}
.table-diligencias td {
    text-align: center;
    vertical-align: middle;
    padding: 8px;
    border-top: 1px solid #dee2e6;
}
.diligencia-header {
    background-color: #f1f3f5;
    border: 1px solid #d6d8db;
    border-radius: 10px;
    width: 85%;
    margin: 30px auto 15px;
    padding: 10px 25px;
    text-align: center;
    font-size: 1.3rem;
    font-weight: 600;
    color: #2c3e50;
}
.toast-msg {
    position: fixed;
    top: 30px;
    left: 50%;
    transform: translateX(-50%);
    background: #28a745;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: 9999;
}
</style>

<?php foreach ($diligencias as $tipo => $items): 
    $isEmplazamiento = (strtolower($tipo) === "emplazamiento");
    $fechaBD = $items[0]["fecha_diligencia"] ?? "";
    $horaBD  = $items[0]["hora_diligencia"] ?? "";
?>
<div class="diligencia-header">
    <i class="fa fa-folder-open"></i> Diligencia – <?= htmlspecialchars($tipo) ?>
</div>

<?php if ($isEmplazamiento): ?>
<div style="text-align:center; margin-bottom:15px;">
    <div style="display:inline-block; margin-right:40px;">
        <b>📅 Fecha de audiencia:</b><br>
        <?= $fechaBD ? htmlspecialchars(date('d/m/Y', strtotime($fechaBD))) : "<span class='text-muted'>Sin fecha</span>"; ?>
    </div>
    <div style="display:inline-block;">
        <b>⏰ Hora de audiencia:</b><br>
        <?= $horaBD ? htmlspecialchars(date('g:i A', strtotime($horaBD))) : "<span class='text-muted'>Sin hora</span>"; ?>
    </div>
</div>
<?php endif; ?>

<table class="table table-diligencias">
<thead>
<tr>
<th>#</th><th>Destinatario</th><th>Estatus</th><th>Observaciones</th><th>Documento PDF</th>
</tr>
</thead>
<tbody>
<?php $i=1; foreach ($items as $d): ?>
<tr>
<td><?= $i++; ?></td>
<td><?= htmlspecialchars($d["nombre_destinatario"]); ?></td>

<td>
    <b><?= htmlspecialchars($d["estatus_diligencia"]); ?></b>
</td>

<td>
    <?= empty(trim($d["observaciones_diligencia"])) 
        ? "<span class='text-muted'>Sin observaciones</span>"
        : "<textarea readonly class='form-control-plaintext' style='resize:none;background:none;border:none;'>".htmlspecialchars($d["observaciones_diligencia"])."</textarea>"; ?>
</td>

<td>
    <?php if (!empty($d["pdf_path"])): ?>
        <a href="<?= htmlspecialchars($d["pdf_path"]); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="glyphicon glyphicon-file"></i> Ver PDF
        </a>
    <?php else: ?>
        <span class="text-muted">Sin documento</span>
    <?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endforeach; ?>
<?php } ?>
