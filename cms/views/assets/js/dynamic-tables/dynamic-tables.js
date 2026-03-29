/*=============================================
Paginación, Límite y Orden (Lógica Preservada)
=============================================*/
function initPagination(){
    var totalPages = $('#pagination').attr("totalPages");
    var defaultOpts = {
        totalPages: totalPages,
        first: '<i class="fas fa-angle-double-left"></i>',
        last: '<i class="fas fa-angle-double-right"></i>',
        prev: '<i class="fas fa-angle-left"></i>',
        next: '<i class="fas fa-angle-right"></i>'
    }
    $('#pagination').twbsPagination(defaultOpts).on("page", function(event,page){
        loadAjaxTable($("#contentModule").val(),$("#orderByTable").val(),$("#orderModeTable").val(),$("#limitTable").val(),page,"pagination",$("#searchTable").val(),$("#between1").val(),$("#between2").val());
    });
}
initPagination();

$(document).off("change", ".changeLimit").on("change", ".changeLimit", function(){
    var limit = $(this).val();
    $("#limitTable").val(limit);
    loadAjaxTable($("#contentModule").val(),$("#orderByTable").val(),$("#orderModeTable").val(),limit,1,"limit",$("#searchTable").val(),$("#between1").val(),$("#between2").val());
});

$(document).off("click", ".orderFilter").on("click", ".orderFilter", function(){
    var orderBy = $(this).attr("orderBy");
    var orderMode = $(this).attr("orderMode");
    $("#orderByTable").val(orderBy);
    $("#orderModeTable").val(orderMode);
    loadAjaxTable($("#contentModule").val(),orderBy,orderMode,$("#limitTable").val(),1,"order",$("#searchTable").val(),$("#between1").val(),$("#between2").val());
});

/*=============================================
Buscador dinámico
=============================================*/
var timer; 

$(document).off("input", "#searchItem, .searchItem").on("input", "#searchItem, .searchItem", function() {
    var search = $(this).val().trim();

    if (search.length > 0) {
        $("#clearSearch, .clearSearch").show();
    } else {
        $("#clearSearch, .clearSearch").hide();
    }

    clearTimeout(timer);

    timer = setTimeout(function() {
        
        // Bloqueo de altura para evitar brincos
        var fixHeight = $(".card").outerHeight();
        if(fixHeight > 0) $(".card").css("min-height", fixHeight + "px");

        if (search.length == 0) {
            $("#searchTable").val("");
            loadAjaxTable($("#contentModule").val(), $("#orderByTable").val(), $("#orderModeTable").val(), $("#limitTable").val(), 1, "search", "", $("#between1").val(), $("#between2").val());
            return;
        }

        if (search.length < 5) {
            $("#loadTable").html(`<tr><td colspan="20" class="text-center py-5 text-muted">
                <i class="fas fa-keyboard mb-2 fa-2x"></i><br>
                <span class="fs-6">Escribe al menos 5 caracteres para buscar...</span>
            </td></tr>`);
            $(".blockFooter").hide();
            $(".card").css("min-height", "auto");
            return; 
        }

        if (search != $("#searchTable").val()) {
            $("#searchTable").val(search);
            loadAjaxTable($("#contentModule").val(), $("#orderByTable").val(), $("#orderModeTable").val(), $("#limitTable").val(), 1, "search", search, $("#between1").val(), $("#between2").val());
        }
    }, 1200); 
});

$(document).off("click", "#clearSearch, .clearSearch").on("click", "#clearSearch, .clearSearch", function() {
    var fixHeight = $(".card").outerHeight();
    $(".card").css("min-height", fixHeight + "px");

    $("#searchItem, .searchItem").val("");
    $("#searchTable").val("");
    $(this).hide();

    clearTimeout(timer);
    timer = setTimeout(function(){
        loadAjaxTable($("#contentModule").val(), $("#orderByTable").val(), $("#orderModeTable").val(), $("#limitTable").val(), 1, "search", "", $("#between1").val(), $("#between2").val());
    }, 600);
});

/*=============================================
Función loadAjaxTable - TRANSICIÓN PULIDA
=============================================*/
function loadAjaxTable(contentModule,orderBy,orderMode,limit,page,filter,search,between1,between2){

    if(filter == "search" && search.length > 0 && search.length < 5) return;

    var startTime = new Date().getTime();

    if(filter == "search"){

        if(search == ""){
            /* Restableciendo: solo spinner sin texto */
            $("#loadTable").css("opacity", "0.5");
        } else {
            /* Buscando: spinner con texto */
            $("#loadTable").css("opacity", "0.7");
            $("#loadTable").html(`<tr><td colspan="20" class="text-center py-5">
                <div class="spinner-border text-primary spinner-border-sm mb-2" role="status"></div><br>
                <span class="fs-5 text-dark fw-bold">Buscando tu producto...</span>
            </td></tr>`);
            $(".blockFooter").hide();
        }

    } else {
        fncSweetAlert("loading", "Cargando registros...", "");
    }

    var data = new FormData();
    data.append("contentModule", contentModule);
    data.append("orderBy", orderBy);
    data.append("orderMode", orderMode);
    data.append("limit", limit);
    data.append("page", page);
    data.append("rolAdmin", $("#rolAdmin").val());
    data.append("search", search);
    data.append("between1", between1);
    data.append("between2", between2);

    $.ajax({
        url:"/ajax/dynamic-tables.ajax.php",
        method: "POST",
        data: data,
        contentType: false,
        cache: false,
        processData: false,
        success: function (response){ 

            var duration = new Date().getTime() - startTime;
            var minDelay = (filter == "search") ? 1000 : 0; 
            var finalDelay = (duration < minDelay) ? (minDelay - duration) : 0;

            setTimeout(function(){
                
                if(filter != "search"){ fncSweetAlert("close", "", ""); }

                try {
                    var res = JSON.parse(response);
                    
                    if(res.HTMLTable != "" && res.HTMLTable != null){
                        
                        // Primero inyectamos el contenido y luego restauramos la opacidad total
                        $("#loadTable").html(res.HTMLTable).css("opacity", "1");
                        
                        $(".blockFooter").show();
                        $(".card").css("min-height", "auto");

                        if(filter != "pagination"){
                            $("#cont-pagination").html(`<ul id="pagination" class="pagination pagination-sm rounded" totalPages="${res.totalPages}"></ul>`);
                            initPagination();
                        }
                    } else {
                        $("#loadTable").html(`<tr><td colspan="20" class="text-center py-5"><span class="fs-6">No hay resultados</span></td></tr>`).css("opacity", "1");
                        $(".blockFooter").hide();
                        $(".card").css("min-height", "auto");
                    }
                } catch (e) { 
                    console.error("Error JSON:", response); 
                    $(".card").css("min-height", "auto");
                    $("#loadTable").css("opacity", "1");
                }
            }, finalDelay); 
        },
        error: function(){
            $(".card").css("min-height", "auto");
            $("#loadTable").css("opacity", "1");
        }
    });
}