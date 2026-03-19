<?php 
class DynamicController{
	public function manage(){
		if(isset($_POST["module"])){
			echo '<script>
				fncMatPreloader("on");
				fncSweetAlert("loading", "Procesando...", "");
			</script>';
			$module = json_decode($_POST["module"]);

			/*=============================================
			Editar datos
			=============================================*/
			if(isset($_POST["idItem"])){
				$url = $module->title_module."?id=".base64_decode($_POST["idItem"])."&nameId=id_".$module->suffix_module."&token=".$_SESSION["admin"]->token_admin."&table=admins&suffix=admin";
				$method = "PUT";
				$fields = "";
				$count = 0;
				foreach ($module->columns as $key => $value) {
					if($value->type_column == "password" && !empty($_POST[$value->title_column])){
						$fields.= $value->title_column."=".crypt(trim($_POST[$value->title_column]),'$2a$07$azybxcags23425sdg23sdfhsd$')."&";
					}else if($value->type_column == "email"){
						$fields.= $value->title_column."=".trim($_POST[$value->title_column])."&";
					}else{
						$fields.= $value->title_column."=".urlencode(trim($_POST[$value->title_column]))."&";
					}
					
					$count++;
					if($count == count($module->columns)){
						$fields = substr($fields,0,-1);
						$update = CurlController::request($url,$method,$fields);
						if($update->status == 200){
							echo '<script>
								fncMatPreloader("off");
								fncFormatInputs();
								fncSweetAlert("success","El registro ha sido actualizado con éxito", "");
								window.location.replace("/'.$module->url_page.'");
							</script>';
							exit();
						}
					}
				}

			}else{
		
				/*=============================================
				Crear datos
				=============================================*/
				$url = $module->title_module."?token=".$_SESSION["admin"]->token_admin."&table=admins&suffix=admin";
				$method = "POST";
				$fields = array();
				$count = 0;
				foreach ($module->columns as $key => $value) {
					if($value->type_column == "password"){
						$fields[$value->title_column] = crypt(trim($_POST[$value->title_column]),'$2a$07$azybxcags23425sdg23sdfhsd$');
					}else if($value->type_column == "email"){
						$fields[$value->title_column] = trim($_POST[$value->title_column]);
					}else{
						$fields[$value->title_column] = urlencode(trim($_POST[$value->title_column]));
					}
					
					$count++;
					if($count == count($module->columns)){
						$fields["date_created_".$module->suffix_module] = date("Y-m-d");
						$save = CurlController::request($url,$method,$fields);
						if($save->status == 200){

							/*=============================================
							ACTUALIZAR STOCK al registrar una COMPRA
							Si el módulo es purchases, sumamos qty al stock
							del producto relacionado
							=============================================*/
							if($module->title_module == "purchases" && 
							   isset($_POST["id_product_purchase"]) && 
							   isset($_POST["qty_purchase"])){

								$idProducto = (int)$_POST["id_product_purchase"];
								$qty        = (int)$_POST["qty_purchase"];

								if($idProducto > 0 && $qty > 0){

									// 1. Traer stock actual del producto
									$urlStock  = "products?linkTo=id_product&equalTo=".$idProducto."&select=stock_product";
									$resStock  = CurlController::request($urlStock,"GET",array());

									if($resStock->status == 200){
										$stockActual  = (int)$resStock->results[0]->stock_product;
										$nuevoStock   = $stockActual + $qty;

										// 2. Actualizar stock del producto
										$urlUpdate = "products?id=".$idProducto."&nameId=id_product&token=".$_SESSION["admin"]->token_admin."&table=admins&suffix=admin";
										CurlController::request($urlUpdate,"PUT","stock_product=".$nuevoStock);
									}
								}
							}

							echo '<script>
								fncMatPreloader("off");
								fncFormatInputs();
								fncSweetAlert("success","El registro ha sido guardado con éxito", "");
								window.location.replace("/'.$module->url_page.'");
							</script>';
							exit();
						}
					}
				}
			}
		}
	}
}
