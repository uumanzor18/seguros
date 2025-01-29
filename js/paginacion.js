$(document).ready(function() {
    var divisionPaginas = 0;
    // Número de filas por página
    var paginaActual = 1;
  
    // Oculta todas las filas y luego muestra solo las de la página actual
    function mostrarFilasPagina(numeroPagina) {
        if ($('#tablaPreguntas tbody tr[seccion="' + numeroPagina + '"]').length) {
            $('#tablaPreguntas tbody tr').hide();
            $('#tablaPreguntas tbody tr[seccion="' + numeroPagina + '"]').show();
            actualizarBotones();
        }
    }
  
    // Calcula el número de páginas y genera los enlaces de paginación
    function generarPaginacion() {
        $('#tablaPreguntas tbody tr').each(function(i) {
            var seccionActual = parseInt($(this).attr('seccion'));
            if (seccionActual > divisionPaginas) {
                divisionPaginas = seccionActual;
            }
        });
        var totalPaginas = divisionPaginas;
      
        $('#paginacion').empty();
        $('#paginacion').append('<li class="page-item"><a class="page-link" href="#" id="anterior"><</a></li>');
        for (var i = 1; i <= totalPaginas; i++) {
            if (i === paginaActual) {
                $('#paginacion').append('<li class="page-item active current-page hide-on-small-screen2"><a class="page-link" href="#">' + i + '</a></li>');
            } else {
                $('#paginacion').append('<li class="page-item hide-on-small-screen2"><a class="page-link" href="#">' + i + '</a></li>');
            }
        }
        $('#paginacion').append('<li class="page-item"><a class="page-link" href="#" id="siguiente">></a></li>');
  
        // Añade eventos de clic a los enlaces de paginación
        $('#paginacion li').click(function() {
            if (!$(this).hasClass("disabled")) {
                var contenido = $(this).text();
                //paginaActual = parseInt($("#paginacion li.active").text());
                if (contenido === "<") {
                    paginaActual = paginaActual -1;
                    var liElement = $('ul.pagination li:has(a.page-link:contains("'+paginaActual+'"))');
                    if (liElement.hasClass("disabled")) {
                        paginaActual = paginaActual -1;
                    }
                    mostrarFilasPagina(paginaActual);
                } else if (contenido === ">") {
                    paginaActual = paginaActual + 1;
                    var liElement = $('ul.pagination li:has(a.page-link:contains("'+paginaActual+'"))');
                    if (liElement.hasClass("disabled")) {
                        paginaActual = paginaActual +1;
                    }
                    mostrarFilasPagina(paginaActual);
                } else {
                    contenido = parseInt(contenido);
                    paginaActual = contenido
                    mostrarFilasPagina(parseInt(contenido));
                }

            }
        });
  
        // Muestra la primera página por defecto
        mostrarFilasPagina(1);
    }
  
    // Actualiza el estado de los botones "Anterior" y "Siguiente"
    function actualizarBotones() {
        if (paginaActual === 1) {
            $('#anterior').parent().addClass('disabled');
        } else {
            $('#anterior').parent().removeClass('disabled');
        }
  
        var totalPaginas = divisionPaginas;
        if (paginaActual == totalPaginas || totalPaginas == 0) {
            $('#siguiente').parent().addClass('disabled');
        } else {
            $('#siguiente').parent().removeClass('disabled');
        }
    
        // Actualiza la clase "active" para resaltar la página actual
        $('#paginacion li').removeClass('active');
        $('#paginacion li:contains(' + paginaActual + ')').addClass('active');
    }
  
    // Genera la paginación al cargar la página
    generarPaginacion();
  });