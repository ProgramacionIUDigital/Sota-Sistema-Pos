<?php 
/*=============================================
1. Capturar datos para editar (Si existe ID)
=============================================*/
$data = null;
if(!empty($routesArray[2])){
    $url = $module->title_module."?linkTo=id_".$module->suffix_module."&equalTo=".base64_decode($routesArray[2]);
    $method = "GET";
    $fields = array();
    $data = CurlController::request($url,$method,$fields);
    if($data->status == 200){
        $data = json_decode(json_encode($data->results[0]),true);
    }
}

/*=============================================
2. Carga forzada de columnas
=============================================*/
if(empty($module->columns)){
    $urlCol = "columns?linkTo=id_module_column&equalTo=".$module->id_module;
    $getColumns = CurlController::request($urlCol, "GET", array());
    if($getColumns->status == 200){
        $module->columns = $getColumns->results;
    }
}

$block1 = ceil(count($module->columns)/2);
$block2 = count($module->columns) - $block1;
?>

<div class="col">
    
    <form method="POST" id="form-manage" novalidate enctype="multipart/form-data" autocomplete="off">

        <?php 
            require_once "controllers/dynamic.controller.php";
            $manageData = new DynamicController();
            $manageData->manage(); 
        ?>

        <div class="card rounded">

            <input type="hidden" name="module" value='<?php echo json_encode($module) ?>'>

            <?php if (!empty($data) && empty($routesArray[3])): ?>
                <input type="hidden" name="idItem" value="<?php echo $routesArray[2] ?>"> 
            <?php endif ?>

            <div class="card-header bg-white rounded-top py-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="/<?php echo $module->url_page ?>" class="btn btn-default border btn-sm rounded px-3 py-2">Regresar</a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-default btn-sm rounded backColor py-2 px-3 btn-guardar-custom">Guardar Registro</button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row row-cols-1 row-cols-lg-2">
                    <div class="col">
                        <?php for ($i = 0; $i < $block1; $i++): ?>
                            <?php include "blocks/blocks.php" ?>
                        <?php endfor ?>
                    </div>
                    <?php if ($block2 > 0): ?>
                        <div class="col">
                            <?php for ($i = $block1; $i < count($module->columns); $i++): ?>
                                <?php include "blocks/blocks.php" ?>
                            <?php endfor ?>
                        </div>
                    <?php endif ?>
                </div>
            </div>

            <div class="card-footer bg-white rounded-bottom py-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="/<?php echo $module->url_page ?>" class="btn btn-default border btn-sm rounded px-3 py-2">Regresar</a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-default btn-sm rounded backColor py-2 px-3 btn-guardar-custom">Guardar Registro</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
/*=============================================
VALIDACIONES DE TIPO (NÚMEROS Y TEXTO)
=============================================*/
$(document).on("input", "[id*='phone'], [id*='contact'], [id*='qty'], [id*='dni'], [id*='stock'], [id*='document']", function(){
    if (this.value && /[^0-9]/.test(this.value)) {
        alert("solo numeros");
        this.value = this.value.replace(/[^0-9]/g, '');
    }
});

$(document).on("input", "[id*='supplier'], [id*='name'], [id*='lastname'], [id*='title']", function(){
    if (this.value && /[^a-zA-ZáéíóúÁÉÍÓÚñÑ ]/.test(this.value)) {
        alert("solo texto");
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ ]/g, '');
    }
    
    // Capitalizar: Primera letra de cada palabra en Mayúscula, el resto en Minúscula
    this.value = this.value.toLowerCase().replace(/(^|\s)\S/g, function(l) {
        return l.toUpperCase();
    });
});

function validarEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

$(document).on("click", ".btn-guardar-custom", function(e){
    
    var faltantes = [];
    var form = $("#form-manage");
    var camposAValidar = [];

    var moduleData = JSON.parse(form.find('input[name="module"]').val());
    var nombreModulo = moduleData.url_page; 

    /*=============================================
    CONFIGURACIÓN POR MÓDULO
    =============================================*/
    if (nombreModulo.includes("product")) {
        camposAValidar = [
            { id: "title_product",    nombre: "Nombre del Producto" },
            { id: "img_product",      nombre: "Imagen" },
            { id: "sku_product",      nombre: "SKU" },
            { id: "stock_product",    nombre: "Stock" },
            { id: "discount_product", nombre: "Descuento" }
        ];
    } else if (nombreModulo.includes("purchase") || nombreModulo.includes("compra")) {
        camposAValidar = [
            { id: "supplier", nombre: "Proveedor" },
            { id: "cost",     nombre: "Costo" },
            { id: "qty",      nombre: "Cantidad" },
            { id: "contact",  nombre: "Teléfono" }
        ];
    } else if (nombreModulo.includes("categor")) {
        camposAValidar = [
            { id: "title_category",  nombre: "Categoría" },
            { id: "img_category",    nombre: "Imagen" },
            { id: "order_category",  nombre: "Orden" },
            { id: "status_category", nombre: "Estado" }
        ];
    } else if (nombreModulo.includes("client")) {
        camposAValidar = [
            { id: "id_document_client", nombre: "Documento de Identidad" },
            { id: "dni_client",         nombre: "Documento (DNI)" },
            { id: "name_client",        nombre: "Nombre" },
            { id: "lastname_client",    nombre: "Apellido" },
            { id: "email_client",       nombre: "Email" },
            { id: "address_client",     nombre: "Dirección" },
            { id: "phone_client",       nombre: "Teléfono" },
            { id: "id_office_client",   nombre: "Sucursal" }
        ];
    } else if (nombreModulo.includes("office") || nombreModulo.includes("sucursal")) {
        camposAValidar = [
            { id: "title_office",   nombre: "Nombre de la Sucursal" },
            { id: "address_office", nombre: "Dirección" },
            { id: "phone_office",   nombre: "Teléfono" }
        ];
    }

    form.find('input, select, textarea').removeClass("is-invalid").css("border", "");

    camposAValidar.forEach(function(campo) {
        var $input = form.find('[id*="'+campo.id+'"], [name*="'+campo.id+'"]');
        
        if ($input.length > 0) {
            var valor = $input.val() ? $input.val().trim() : "";
            var idInput = $input.attr("id") ? $input.attr("id").toLowerCase() : "";
            var esEdicion = form.find('input[name="idItem"]').length > 0;

            if (campo.id.includes("img_") && esEdicion) return;

            var esExcepcionCero = campo.id.includes("discount") || campo.id.includes("order");

            // VALIDACIÓN CORREO (Sin el return que bloqueaba el resto)
            if (idInput.includes("email") && valor !== "" && !validarEmail(valor)) {
                alert("Agregue un correo correcto");
                $input.val(""); 
                faltantes.push("- " + campo.nombre + " (Formato incorrecto)");
                $input.css("border", "2px solid red").addClass("is-invalid");
            } 
            // VALIDACIÓN CAMPOS VACÍOS (Aquí entrará el Apellido)
            else if (valor === "" || valor === "null" || (valor === "0" && !esExcepcionCero)) {
                faltantes.push("- " + campo.nombre);
                $input.css("border", "2px solid red").addClass("is-invalid");
            } else {
                $input.css("border", "2px solid #28a745").addClass("is-valid");
            }
        }
    });

    if (faltantes.length > 0) {
        alert("⚠ Campos obligatorios faltantes o incorrectos:\n\n" + faltantes.join("\n"));
        return false;
    } else {
        form.submit();
    }
});

/* FEEDBACK VISUAL EN TIEMPO REAL */
$(document).on("input change", "input, select, textarea", function(){
    var id = $(this).attr("id") ? $(this).attr("id").toLowerCase() : "";
    var val = $(this).val() ? $(this).val().trim() : "";
    if (id.includes("utility") || id.includes("utilidad")) return;

    if (val !== "" && val !== "null") {
        $(this).css("border", "2px solid #28a745").removeClass("is-invalid").addClass("is-valid");
    } else {
        $(this).css("border", "2px solid red").addClass("is-invalid").removeClass("is-valid");
    }
});
</script>