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
						
						/*=============================================
						Validación de Email Lado Servidor (Editar)
						=============================================*/
						$email_val = trim($_POST[$value->title_column]);

						if (!filter_var($email_val, FILTER_VALIDATE_EMAIL)) {
							echo '<script>
								fncMatPreloader("off");
								fncFormatInputs();
								fncSweetAlert("error", "El formato del correo electrónico es inválido", "");
							</script>';
							return;
						}

						$fields.= $value->title_column."=".$email_val."&";

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

						/*=============================================
						Validación de Email Lado Servidor (Crear)
						=============================================*/
						$email_val = trim($_POST[$value->title_column]);

						if (!filter_var($email_val, FILTER_VALIDATE_EMAIL)) {
							echo '<script>
								fncMatPreloader("off");
								fncFormatInputs();
								fncSweetAlert("error", "El formato del correo electrónico es inválido", "");
							</script>';
							return;
						}

						$fields[$value->title_column] = $email_val;

					}else{
						$fields[$value->title_column] = urlencode(trim($_POST[$value->title_column]));
					}
					
					$count++;

					if($count == count($module->columns)){
						$fields["date_created_".$module->suffix_module] = date("Y-m-d");
						$save = CurlController::request($url,$method,$fields);

						if($save->status == 200){
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