<?php
include "conexion.php";

// ===================================================
// 🔹 PETICIÓN AJAX: actualizar estatus sin recargar
// ===================================================
if (isset($_POST["ajax_guardar_estatus_expediente"])) {
    $id = intval($_POST["id_expediente"]);
    $estatus = $_POST["estatus"];

    $sql = "UPDATE expedientes SET estatus=? WHERE id_expediente=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("si", $estatus, $id);
    $stmt->execute();               
    $stmt->close();

    echo "ok";
    exit;
}

// ===================================================
// 🔹 PETICIÓN AJAX: actualizar folio sin recargar
// ===================================================
if (isset($_POST["ajax_guardar_folio"])) {
    $id = intval($_POST["id_expediente"]);
    $folio = trim($_POST["folio_destino"]);

    $sql = "UPDATE expedientes SET folio_destino=? WHERE id_expediente=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("si", $folio, $id);
    $stmt->execute();
    $stmt->close();

    echo "ok";
    exit;
}

// ===================================================
// 🔹 GENERAR NÚMERO DE EXHORTO ####/AAAA
// ===================================================
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

// ===================================================
// 🔹 GENERAR NÚMERO DE EXPEDIENTE ####/AAAA-TUA
// ===================================================
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

// ===================================================
// 🔹 OBTENER DETALLE DE EXPEDIENTE
// ===================================================
function obtenerExpedientePorID($con, $id) {
    $sql = "SELECT e.id_expediente, e.num_expediente, e.exhorto, e.estatus, e.f_registro,
                   e.folio_destino, e.id_tua_origen, e.id_tua_destino,
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

// ===================================================
// 🔹 MOSTRAR TABLA DE INFORMACIÓN DEL EXPEDIENTE
// ===================================================
function mostrarDatosExpediente($expediente, $estatus_actual, $es_destinatario) { ?>
    <div id="toastMsg"></div>

    <table class="table table-bordered text-center align-middle">
        <tr><th>Número de Expediente</th><td><?= htmlspecialchars($expediente["num_expediente"]); ?></td></tr>
        <tr><th>Estado</th><td><?= htmlspecialchars($expediente["nombre_estado"]); ?></td></tr>
        <tr><th>Municipio</th><td><?= htmlspecialchars($expediente["nombre_municipio"]); ?></td></tr>
        <tr><th>Núcleo Agrario</th><td><?= htmlspecialchars($expediente["nombre_nucleo"]); ?></td></tr>
        <tr><th>Exhorto</th><td><?= htmlspecialchars($expediente["exhorto"]); ?></td></tr>
        <tr><th>TUA Origen</th><td><?= htmlspecialchars($expediente["tua_origen"] . " — " . $expediente["ciudad_origen"]); ?></td></tr>
        <tr><th>TUA Destino</th><td><?= htmlspecialchars($expediente["tua_destino"] . " — " . $expediente["ciudad_destino"]); ?></td></tr>
        <tr><th>Fecha Registro</th><td><?= $expediente["f_registro"] ? date("d/m/Y h:i A", strtotime($expediente["f_registro"])) : "Sin registro"; ?></td></tr>

        <!-- ✅ Estatus integrado -->
        <tr>
            <th>Estatus Actual</th>
            <td>
                <?php if ($es_destinatario): ?>
                    <select id="selectEstatusExpediente" class="estatus-select">
                        <?php
                        $estatuses = ["En Proceso", "Atendida", "Vencida", "Incompetencia"];
                        foreach ($estatuses as $op) {
                            $sel = ($estatus_actual == $op) ? "selected" : "";
                            echo "<option value='$op' $sel>$op</option>";
                        }
                        ?>
                    </select>
                <?php else: ?>
                    <span class="estatus-view"><?= htmlspecialchars($estatus_actual); ?></span>
                <?php endif; ?>
            </td>
        </tr>

        <!-- ✅ Folio integrado -->
        <tr>
            <th>Folio de Recepción</th>
            <td>
                <span id="folio_destino_label"
                      <?= $es_destinatario ? 'contenteditable="true" class="folio-editable" title="Haz clic para editar"' : 'class="folio-view-only"' ?>>
                    <?= htmlspecialchars($expediente["folio_destino"] ?? '') ?>
                </span>
            </td>
        </tr>
    </table>

    <?php if ($es_destinatario): ?>
    <script>
    document.addEventListener("DOMContentLoaded", ()=>{
        const folioEl = document.getElementById("folio_destino_label");
        const selectEstatus = document.getElementById("selectEstatusExpediente");
        const toast = document.getElementById("toastMsg");

        // === Mostrar toast verde centrado ===
        function mostrarToast(msg) {
            toast.innerHTML = `<div class="toast-box">${msg}</div>`;
            toast.style.opacity = "1";
            setTimeout(()=> toast.style.opacity = "0", 2500);
        }

        // === Guardar FOLIO ===
        folioEl?.addEventListener("blur", ()=>guardarFolio(folioEl.innerText.trim()));
        folioEl?.addEventListener("keydown", e=>{
            if(e.key==="Enter"){ e.preventDefault(); folioEl.blur(); }
        });

        async function guardarFolio(folio){
            const data = new FormData();
            data.append("ajax_guardar_folio","1");
            data.append("id_expediente","<?= (int)$expediente['id_expediente']; ?>");
            data.append("folio_destino",folio);
            const resp = await fetch("php/funciones_expediente.php",{method:"POST",body:data});
            if(resp.ok) mostrarToast("✅ Folio guardado correctamente");
        }

        // === Guardar ESTATUS ===
        selectEstatus?.addEventListener("change", async ()=>{
            const valor = selectEstatus.value;
            const data = new FormData();
            data.append("ajax_guardar_estatus_expediente","1");
            data.append("id_expediente","<?= (int)$expediente['id_expediente']; ?>");
            data.append("estatus",valor);
            const resp = await fetch("php/funciones_expediente.php",{method:"POST",body:data});
            if(resp.ok) mostrarToast("✅ Estatus actualizado a: " + valor);
        });
    });
    </script>

    <!-- ✅ Estilos finales -->
    <style>
    /* --- Centrar ambas columnas --- */
    table.table {
        width: 100%;
        margin: 0 auto;
        text-align: center;
    }
    .table th, .table td {
        text-align: center !important;
        vertical-align: middle !important;
    }

    /* --- Campos editables --- */
    .folio-editable, .estatus-select {
        border: 1px solid #bcbcbc;
        padding: 5px 8px;
        border-radius: 6px;
        min-width: 180px;
        background-color: #fff !important;
        color: #333;
        text-align: left;
        transition: all 0.2s ease;
    }
    .folio-editable:hover, .estatus-select:hover {
        border-color: #9c9c9c;
        cursor: text;
    }
    .folio-editable:focus, .estatus-select:focus {
        border-color: #28a745;
        outline: none;
        box-shadow: 0 0 4px rgba(40,167,69,0.4);
    }
    .folio-view-only, .estatus-view {
        border: 1px solid #dcdcdc;
        padding: 5px 8px;
        border-radius: 6px;
        background-color: #f9f9f9;
        color: #333;
        display: inline-block;
        min-width: 180px;
        text-align: left;
    }

    /* --- Toast verde centrado arriba --- */
    #toastMsg {
        position: fixed;
        top: 30px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    .toast-box {
        background-color: #28a745;
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.15);
        font-weight: 500;
        animation: fadeInOut 2.5s ease;
    }
    @keyframes fadeInOut {
        0% { opacity: 0; transform: translateY(-10px); }
        10%, 85% { opacity: 1; transform: translateY(0); }
        100% { opacity: 0; transform: translateY(-10px); }
    }
    </style>
    <?php endif;
}
?>
