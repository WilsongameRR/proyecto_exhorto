$(document).ready(function() {    

    $(document).ajaxStart(function () {
		  Swal.fire({
			title: "Por favor espere ",
			text: "Procesando información",
			allowOutsideClick: 'false',
			allowEscapeKey: 'false'
		  });
		  Swal.showLoading();
		});
	$(document).ajaxStop(function () {
		if(Swal.isLoading()){
			Swal.hideLoading();
			Swal.clickConfirm();
		}
		});

    var url = './php/EstadosS/paginarES.php';
        $.ajax({ 
            url: url,   
            success: function (datas) {
            var array = eval(datas);     
            t=$('#agrega-registros').DataTable( {
            data:array,
            "paging": true,
            columns : [
            { title : "Nombre del estado" }, 
            { title : "Editar" }, 
            { title : "Elimnar" }
            ],
            sScrollY: "330px",
            bPaginate: false,
            bSort : false,
            language: {
              "zeroRecords": "No se ha encontrado ning&uacute;n registro",
              "info": "_TOTAL_ registros en existencia",
              "infoEmpty": "No hay registros disponibles",
              "infoFiltered": "(Se han filtrado _MAX_ registros)",
              "search": "Buscar"
            }
        } );  //llave de cierre datatable  
             
        }//llave de cierre success
        });//llave de cierre ajax
        return false;    
    });


$(function(){

   $('#agregar').on('click', function () {
    $('#formulario')[0].reset();
    $("#myModal").modal({show:true});   
    $('#edi').hide();    
    $('#reg').show();
    $('#p').text('Registrar estado');
}); 

});//llave de cierre function


function agregaRegistro() {

    var url = './php/EstadosS/agregarES.php';
    if(!verificarCampos()){
        Swal.fire({
            type: 'warning',
            title: 'Oops...',
            text: 'Por favor completa todos los campos',
          })
    }
    else {
        $.ajax({
            type: 'POST',
            url: url,
            data: $('#formulario').serialize(),
            success: function () {
                Swal.fire({
                    type: 'success',
                    title: "Se agrego correctamente",   
                    timer: 1500,   
                    showConfirmButton:false    	              	    
                    }).then(function(){
                        $('#formulario')[0].reset();
                        window.location = "./viewEstadosS.php";
                });	 
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert('Ocurrio un error al agregar');
                window.location = "./viewEstadosS.php";
            }
        });    
    }
}

function editarES(id) {
    $('#formulario')[0].reset();
    $("#myModal").modal({show:true});   
    var url = './php/EstadosS/EditarES.php';
    $.ajax({
        type: 'POST',
        url: url,
        data: {id: +id},
        success: function (valores) {
            var datos = eval(valores);            
            $('#reg').hide();
            $('#edi').show();            
            $('#p').text('Editar estado');
            $('#id').val(id);
            $('#nombreES').val(datos[0]);
        }

    });
}

function Actualizar() {
    var url = './php/EstadosS/actualizarES.php';
    if(!verificarCampos()){
        Swal.fire({
            type: 'warning',
            title: 'Oops...',
            text: 'Por favor completa todos los campos',
          })
    }
    else {
        $.ajax({
            type: 'POST',
            url: url,
            data: $('#formulario').serialize(),
            success: function (registro) {
                Swal.fire({
                    type: 'success',
                    title: "Se actualizo correctamente",   
                    timer: 1500,   
                    showConfirmButton:false    	              	    
                    }).then(function(){
                        $('#formulario')[0].reset();
                        window.location = "./viewEstadosS.php";
                });	 
    
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert('Ocurrio un error al editar');
                window.location = "./viewEstadosS.php";
            }
        });    
    }
}


function eliminarES(id) {
    swal.fire({
        title: 'Borrar el registro?',  			
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Aceptar',
          cancelButtonText:'Cancelar'
      }).then((result) => {
          if(result.value){
            var url = './php/EstadosS/eliminarES.php';   
            $.ajax({
                type: 'POST',
                url: url,
                data: {id: +id},
                success: function (registro) {
                    Swal.fire({
                        type: 'success',
                        title: "Se borro correctamente",   
                        timer: 1500,   
                        showConfirmButton:false    	              	    
                        }).then(function(){                            
                            window.location = "./viewEstadosS.php";
                    });	                                                  
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert('Ocurrio un error al eliminar');
                    window.location = "./viewEstadosS.php";
                }
            });                   
          }
      });

}
function verificarCampos(){
    var flagNombre = 0;
    if($("#nombreES").val() != '' && $("#nombreES").val() != null){
        flagNombre = 1;
    }
    
    if(flagNombre == 1)
        return true;	
    else
        return false;
}

