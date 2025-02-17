$(document).ready(function () {
    const filasPorPagina = 5;
    let paginaActual = 1;

    function mostrarFilasPagina(numeroPagina) {
        const filas = $('#tablaPreguntas tbody tr');
        const totalFilas = filas.length;
        const inicio = (numeroPagina - 1) * filasPorPagina;
        const fin = inicio + filasPorPagina;

        filas.hide().slice(inicio, fin).show();
        actualizarBotones(totalFilas);
    }

    function generarPaginacion() {
        const totalFilas = $('#tablaPreguntas tbody tr').length;
        const totalPaginas = Math.ceil(totalFilas / filasPorPagina);

        let paginacionHTML = `<li class="page-item"><a class="page-link" href="#" id="anterior">&lt;</a></li>`;
        for (let i = 1; i <= totalPaginas; i++) {
            paginacionHTML += `<li class="page-item ${i === 1 ? "active" : ""}">
                                   <a class="page-link" href="#">${i}</a>
                               </li>`;
        }
        paginacionHTML += `<li class="page-item"><a class="page-link" href="#" id="siguiente">&gt;</a></li>`;

        $('#paginacion').html(paginacionHTML);
        mostrarFilasPagina(1);
    }

    function actualizarBotones(totalFilas) {
        const totalPaginas = Math.ceil(totalFilas / filasPorPagina);

        $('#anterior').parent().toggleClass('disabled', paginaActual === 1);
        $('#siguiente').parent().toggleClass('disabled', paginaActual === totalPaginas);

        $('#paginacion li').removeClass('active');
        $(`#paginacion li:has(a.page-link:contains(${paginaActual}))`).addClass('active');
    }

    $('#paginacion').on('click', 'li a.page-link', function (e) {
        e.preventDefault();
        let contenido = $(this).text();

        if (contenido === "<" && paginaActual > 1) {
            paginaActual--;
        } else if (contenido === ">" && paginaActual < Math.ceil($('#tablaPreguntas tbody tr').length / filasPorPagina)) {
            paginaActual++;
        } else if (!isNaN(contenido)) {
            paginaActual = parseInt(contenido);
        }

        mostrarFilasPagina(paginaActual);
    });

    generarPaginacion();
});
