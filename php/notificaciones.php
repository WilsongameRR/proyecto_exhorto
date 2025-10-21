<?php
function mostrarNotificacion($msg, $id_expediente = 0) {
    if (empty($msg)) return;

    $clase = "toast-success";
    $mensaje = "";

    if ($msg === "estatus") {
        $mensaje = "✅ Estatus del exhorto actualizado.";
    } elseif ($msg === "diligencia") {
        $mensaje = "📄 Estatus de diligencia actualizado.";
        $clase = "toast-info";
    } elseif ($msg === "error") {
        $mensaje = "❌ Ocurrió un error al procesar la solicitud.";
        $clase = "toast-danger";
    }

    echo "
    <style>
    #toastMsg {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: none;
    }
    .toast-box {
        padding: 12px 18px;
        border-radius: 8px;
        color: #fff;
        font-size: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        animation: fadeIn 0.4s;
    }
    .toast-success { background-color: #28a745; }
    .toast-info { background-color: #17a2b8; }
    .toast-danger { background-color: #dc3545; }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(-10px);}
        to {opacity: 1; transform: translateY(0);}
    }
    </style>

    <div id='toastMsg'></div>

    <script>
    $(document).ready(function(){
        const toast = `<div class='toast-box $clase'>$mensaje</div>`;
        $('#toastMsg').html(toast).fadeIn(400);
        if (window.history.replaceState) {
            const url = window.location.href.split('?')[0] + '?id=$id_expediente';
            window.history.replaceState(null, null, url);
        }
        setTimeout(()=> $('#toastMsg').fadeOut(600, ()=> $('#toastMsg').html('')), 3500);
    });
    </script>
    ";
}
?>
