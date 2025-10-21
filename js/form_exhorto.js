$(function(){
    const anio = $("#num_expediente").val().split("/")[1]?.split("-")[0] || new Date().getFullYear();
    const tua = $("#num_expediente").val().split("-")[1] || "";

    // Estado -> Municipio
    $("#estado_exh").change(function(){
        var id_estado = $(this).val();
        if(id_estado){
            $.post("/proyecto_exhorto/almacen2/php/getMunicipios.php", {id_estado}, function(data){
                $("#municipio_exh").html('<option value="">Seleccione...</option>');
                $.each(data, function(i, municipio){
                    $("#municipio_exh").append('<option value="'+municipio.id+'">'+municipio.municipio+'</option>');
                });
                $("#nucleo_exh").html('<option value="">Seleccione un municipio primero</option>');
            }, "json");
        }
    });

    // Municipio -> Núcleo
    $("#municipio_exh").change(function(){
        var id_municipio = $(this).val();
        if(id_municipio){
            $.post("/proyecto_exhorto/almacen2/php/getNucleos.php", {id_municipio}, function(data){
                $("#nucleo_exh").html('<option value="">Seleccione...</option>');
                $.each(data, function(i, nucleo){
                    $("#nucleo_exh").append('<option value="'+nucleo.id+'">'+nucleo.nucleo+'</option>');
                });
            }, "json");
        }
    });

    // Validación del formato del número
    $("#num_expediente").on("blur", function(){
        let val = $(this).val().trim();
        if (/^[0-9]{1,5}$/.test(val)) {
            let padded = val.padStart(4, "0");
            $(this).val(`${padded}/${anio}-${tua}`);
        }
    });

    // Validación general al guardar
    $("#formExhorto").on("submit", function(e){
        const val = $("#num_expediente").val().trim();
        const regex = /^[0-9]{1,5}\/[0-9]{4}-[0-9]{1,3}$/;
        if(!regex.test(val)){
            e.preventDefault();
            alert("⚠️ El número de expediente debe tener el formato ####/AAAA-###");
        }
    });

    // Agregar diligencias
    let contador = 0;
    $("#agregar_diligencia").click(function(){
        contador++;
        const card = `
        <div class="col-md-6 diligencia-card" id="diligencia_${contador}">
            <h5><i class="fa fa-file"></i> Diligencia ${contador}</h5>
            <input type="text" name="diligencias[${contador}][diligencia]" class="form-control" placeholder="Nombre de la diligencia">
            <input type="text" name="diligencias[${contador}][nombre_destinatario]" class="form-control" placeholder="Nombre del destinatario">
            <select name="diligencias[${contador}][estatus_diligencia]" class="form-control">
                <option value="Pendiente">Pendiente</option>
                <option value="Realizada">Realizada</option>
                <option value="Cancelada">Cancelada</option>
            </select>
            <input type="file" name="diligencias[${contador}][archivo_diligencia]" class="form-control">
            <input type="date" name="diligencias[${contador}][fecha_diligencia]" class="form-control">
            <input type="text" name="diligencias[${contador}][observaciones_diligencia]" class="form-control" placeholder="Observaciones">
            <div class="text-right">
                <button type="button" class="eliminar_fila" data-id="${contador}">
                    <i class="fa fa-trash"></i> Eliminar
                </button>
            </div>
        </div>`;
        $("#contenedor_diligencias").append(card);
    });

    // Eliminar diligencia
    $(document).on("click", ".eliminar_fila", function(){
        const id = $(this).data("id");
        $("#diligencia_" + id).fadeOut(300, function(){ $(this).remove(); });
    });
});
