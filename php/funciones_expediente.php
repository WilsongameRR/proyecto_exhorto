<?php
include "conexion.php";

/* ===================================================
   🔹 ACTUALIZAR DATOS DE EXPEDIENTE (sin AJAX)
   Se ejecuta cuando se envía un formulario por POST
=================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["guardar_expediente"])) {
    $id_expediente = intval($_POST["id_expediente"] ?? 0);
    $nuevo_estatus = trim($_POST["estatus"] ?? "");
    $nuevo_folio   = trim($_POST["folio_destino"] ?? "");
    $fecha_recep   = trim($_POST["fecha_recepcion"] ?? "");

    if ($id_expediente > 0) {
        if ($nuevo_estatus !== "") {
            $stmt = $con->prepare("UPDATE expedientes SET estatus=? WHERE id_expediente=?");
            $stmt->bind_param("si", $nuevo_estatus, $id_expediente);
            $stmt->execute();
            $stmt->close();
        }

        if ($nuevo_folio !== "") {
            $stmt = $con->prepare("UPDATE expedientes SET folio_destino=? WHERE id_expediente=?");
            $stmt->bind_param("si", $nuevo_folio, $id_expediente);
            $stmt->execute();
            $stmt->close();
        }

        if ($fecha_recep !== "") {
            $stmt = $con->prepare("UPDATE expedientes SET fecha_recepcion=? WHERE id_expediente=?");
            $stmt->bind_param("si", $fecha_recep, $id_expediente);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: ../viewExpediente.php?id=" . $id_expediente . "&msg=guardado");
    exit();
}

/* ===================================================
   🔹 GENERAR NÚMERO DE EXHORTO ####/AAAA
=================================================== */
function generarNumeroExhorto($con) {
    $anio_actual = date("Y");
    $sql = "SELECT exhorto FROM expedientes WHERE exhorto LIKE '%/$anio_actual' ORDER BY id_expediente DESC LIMIT 1";
    $res = $con->query($sql);
    $ultimo = 0;
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $partes = explode("/", $row["exhorto"]);
        if (isset($partes[0]) && is_numeric($partes[0])) $ultimo = intval($partes[0]);
        $res->close();
    }
    return str_pad($ultimo + 1, 4, "0", STR_PAD_LEFT) . "/" . $anio_actual;
}

/* ===================================================
   🔹 GENERAR NÚMERO DE EXPEDIENTE ####/AAAA-TUA
=================================================== */
function generarNumeroExpediente($con, $id_tua_origen) {
    $anio = date("Y");
    $tua_usuario = "";
    $resTua = $con->query("SELECT tua FROM cat_tuas WHERE id = $id_tua_origen LIMIT 1");
    if ($resTua && $resTua->num_rows > 0) $tua_usuario = $resTua->fetch_assoc()["tua"];
    if ($resTua) $resTua->close();
    $nuevo_num = "0001/$anio-$tua_usuario";
    $sqlUlt = "SELECT num_expediente FROM expedientes WHERE num_expediente LIKE '%/$anio-$tua_usuario' ORDER BY id_expediente DESC LIMIT 1";
    if ($res = $con->query($sqlUlt)) {
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $ultimo = intval(explode("/", $row["num_expediente"])[0]);
            $nuevo_num = str_pad($ultimo + 1, 4, "0", STR_PAD_LEFT) . "/$anio-$tua_usuario";
        }
        $res->close();
    }
    return $nuevo_num;
}

/* ===================================================
   🔹 OBTENER DETALLE DE EXPEDIENTE
=================================================== */
function obtenerExpedientePorID($con, $id) {
    $sql = "SELECT e.id_expediente, e.num_expediente, e.exhorto, e.estatus, e.f_registro,
                   e.folio_destino, e.fecha_recepcion, e.id_tua_origen, e.id_tua_destino,
                   est.estado AS nombre_estado,
                   mun.municipio AS nombre_municipio,
                   nac.nucleo AS nombre_nucleo,
                   t_origen.id AS id_tua_origen, t_origen.tua AS tua_origen, t_origen.ciudad_sede AS ciudad_origen,
                   t_destino.id AS id_tua_destino, t_destino.tua AS tua_destino, t_destino.ciudad_sede AS ciudad_destino
            FROM expedientes e
            LEFT JOIN cat_estados est ON e.id_estado = est.id
            LEFT JOIN cat_municipios mun ON e.id_municipio = mun.id
            LEFT JOIN cat_nucleos_agrarios nac ON e.id_nucleo = nac.id
            LEFT JOIN cat_tuas t_origen ON e.id_tua_origen = t_origen.id
            LEFT JOIN cat_tuas t_destino ON e.id_tua_destino = t_destino.id
            WHERE e.id_expediente=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $expediente = $res->fetch_assoc();
    $stmt->close();
    return $expediente;
}

/* ===================================================
   🔹 MOSTRAR TABLA DE INFORMACIÓN DEL EXPEDIENTE
=================================================== */
function mostrarDatosExpediente($expediente, $estatus_actual, $es_destinatario) { ?>
    <table class="table table-bordered text-center align-middle">
        <tr><th>Número de Expediente</th><td><?= htmlspecialchars($expediente["num_expediente"]); ?></td></tr>
        <tr><th>Estado</th><td><?= htmlspecialchars($expediente["nombre_estado"]); ?></td></tr>
        <tr><th>Municipio</th><td><?= htmlspecialchars($expediente["nombre_municipio"]); ?></td></tr>
        <tr><th>Núcleo Agrario</th><td><?= htmlspecialchars($expediente["nombre_nucleo"]); ?></td></tr>
        <tr><th>Exhorto</th><td><?= htmlspecialchars($expediente["exhorto"]); ?></td></tr>
        <tr><th>TUA Origen</th><td><?= htmlspecialchars($expediente["tua_origen"] . " — " . $expediente["ciudad_origen"]); ?></td></tr>
        <tr><th>TUA Destino</th><td><?= htmlspecialchars($expediente["tua_destino"] . " — " . $expediente["ciudad_destino"]); ?></td></tr>
        <tr><th>Fecha Registro</th><td><?= $expediente["f_registro"] ? date("d/m/Y h:i A", strtotime($expediente["f_registro"])) : "Sin registro"; ?></td></tr>

        <!-- Estatus -->
        <tr>
            <th>Estatus Actual</th>
            <td>
                <?php if ($es_destinatario): ?>
                    <select name="estatus" class="form-select text-center" style="max-width:220px; margin:auto;">
                        <?php
                        $estatuses = ["En Proceso", "Atendida", "Vencida", "Incompetencia"];
                        foreach ($estatuses as $op) {
                            $sel = ($estatus_actual == $op) ? "selected" : "";
                            echo "<option value='$op' $sel>$op</option>";
                        }
                        ?>
                    </select>
                <?php else: ?>
                    <?= htmlspecialchars($estatus_actual); ?>
                <?php endif; ?>
            </td>
        </tr>

        <!-- Folio -->
        <tr>
            <th>Folio de Recepción</th>
            <td>
                <?php if ($es_destinatario): ?>
                    <input type="text" name="folio_destino" value="<?= htmlspecialchars($expediente["folio_destino"] ?? '') ?>" class="form-control text-center" style="max-width:220px; margin:auto;">
                <?php else: ?>
                    <?= !empty($expediente["folio_destino"]) ? htmlspecialchars($expediente["folio_destino"]) : "<span class='text-muted'>Sin folio</span>"; ?>
                <?php endif; ?>
            </td>
        </tr>

        <!-- Fecha -->
        <tr>
            <th>Fecha de Recepción</th>
            <td>
                <?php if ($es_destinatario): ?>
                    <input type="date" name="fecha_recepcion" value="<?= htmlspecialchars($expediente["fecha_recepcion"] ?? '') ?>" class="form-control text-center" style="max-width:220px; margin:auto;">
                <?php else: ?>
                    <?= !empty($expediente["fecha_recepcion"]) ? htmlspecialchars($expediente["fecha_recepcion"]) : "<span class='text-muted'>Sin fecha</span>"; ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>
<?php } ?>
