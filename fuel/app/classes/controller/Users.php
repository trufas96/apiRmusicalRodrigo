<?php
use \Model\Users;
use Firebase\JWT\JWT;
class Controller_Users extends Controller_Base
{
    private  $idAdmin = 1;
    private  $idUser = 2;

    public function post_register()
    {
        try {
            if ( !isset($_POST['userName']) || !isset($_POST['password']) || !isset($_POST['email'])) 
            {
            	return $this->respuesta(400, 'Algun paramentro esta vacio', '');
            }if(isset($_POST['x']) || isset($_POST['y'])){
            		
            		if(empty($_POST['x']) || empty($_POST['y'])){
	            		return $this->respuesta(400, 'Coordenadas vacias', '');
	            	}
            	}
            	else
            	{
            		return $this->respuesta(400, 'Coordenadas no definidas', '');
            	}
            if(!empty($_POST['userName']) && !empty($_POST['password']) && !empty($_POST['email']))
            {
            	if(strlen($_POST['password']) < 5)
            	{
            		return $this->respuesta(400, 'La contraseña debe tener al menos 5 caracteres', '');
            	}
				$input = $_POST;
	            $newUser = $this->newUser($input);
	           	$json = $this->saveUser($newUser);
	        }
	        else
	        {
	        	return $this->respuesta(400, 'Algun campo vacio', '');
	        }
        }catch (Exception $e){
        	return $this->respuesta(500, $e->getMessage(), '');
        }      
    }


    private function newUser($input)
    {
    		$user = new Model_Users();
            $user->userName = $input['userName'];
            $user->password = $this->encode($input['password']);
            $user->email = $input['email'];
            $user->id_device = $input['id_device'];
            $user->id_role = $this->idUser;
            $user->x = $input['x'];
            $user->y = $input['y'];
            return $user;
    }



    private function saveUser($user)
    {
    	$userExists = Model_Users::find('all', 
    								array('where' => array(
    													array('email', '=', $user->email),
    														)
    									)
    							);
    	if(empty($userExists)){
    		$userToSave = $user;
    		$userToSave->save();
    		$arrayData = array();
    		$arrayData['userName'] = $user->userName;
    		//AQUI SE GESTION QUE ESTA BIEN CREADO EL USUARIO
    		return $this->respuesta(201, 'Usuario creado', $arrayData);
    	}
    	else
    	{
    		//AQUI SE GESTIONA QUE ESTA CREADO ANTERIORMENTE
    		return $this->respuesta(204, 'Usuario registrado anteriormente', '');
    	}
    }





    public function post_login()
    {	
    	try
    	{
	        if ( !isset($_POST['userName']) || !isset($_POST['password']) ) {
	        	return $this->respuesta(400, 'alguno de los datos esta vacio', '');
	        }
	        else if( !empty($_POST['userName']) && !empty($_POST['password']))
	        {
	            $input = $_POST;
	            $user = Model_Users::find('all', 
		            						array('where' => array(
		            							array('userName', '=', $input['userName']), 
		            							array('password', '=', $this->encode($input['password']))
		            							)
		            						)
		            					);
	            if(!empty($user))
	            {
	            	$user = reset($user);
	            	$userName = $user->userName;
	            	$password = $user->password;
	            	$id = $user->id;
	            	$email = $user->email;
	            	$id_role = $user->id_role;
	                $token = $this->encodeToken($userName, $password, $id, $email, $id_role);
	                $arrayData = array();
	               	$arrayData['token'] = $token;
	               	return $this->respuesta(200, 'Log In correcto', $arrayData);
	        	}
	        	else
	        	{
	        		//DATOS ERRONEOS
	        		return $this->respuesta(400, 'algun dato erroneo', '');
	       		}
	     
	        }
	        else
	        {
	        	//CADENAS VACIAS 
	        	return $this->respuesta(400, 'No se permiten cadenas de texto vacias', '');
	        }
	        	
	    }
	    catch(Exception $e){
	    	//ERROR EN CONEXION/INTERNET
	    	return $this->respuesta(500, $e->getMessage(), '');
	    }
	}
	




	public function post_forgotPassword()
	{
		try
		{
			$input = $_POST;
			if ( !isset($_POST['userName']) || !isset($_POST['email']) ) 
			{
				//
				return $this->respuesta(400, 'alguno de los datos esta vacio', '');
	        }
	        else if( !empty($_POST['userName']) && !empty($_POST['email']))
	        {
		    	$user = Model_Users::find('all', 
		           					array('where' => array(
		           							array('userName', '=', $input['userName']), 
		           							array('email', '=', $input['email'])
		           							)
		           						)
		           					);
			    if($user != null)
			    {
			   		   	$user = reset($user);
		            	$userName = $user->userName;
		            	$password = $user->password;
		            	$id = $user->id;
		            	$email = $user->email;
		            	$id_role = $user->id_role;
		                $token = $this->encodeToken($userName, $password, $id, $email, $id_role);
		                $arrayData = array();
		               	$arrayData['token'] = $token;
		               	//GESTIONA EL CAMBIO DE PASS Y ES CORRECTO
		               	return $this->respuesta(200, 'forgot correcto', $arrayData);
			    }
			    else
			    {
			    	//EL USUARIO NO SE ENCONTRO POR TANTO NO PUEDE CAMBIAR LA PASS HASTA QUE NO SEA BUENA LA CONTRASEÑA
			    	return $this->respuesta(400, 'Usuario no encontrado.', '');
			    }
			}
		}
		catch(Exception $e){
			//ERROR SERVIDOR
			return $this->respuesta(500, $e->getMessage(), '');
		}
	}




	public function post_changePassword()
	{
		$authenticated = $this->authenticate();
    	$arrayAuthenticated = json_decode($authenticated, true);
    	
    	 if($arrayAuthenticated['authenticated'])
    	 {

			$newPassword = $_POST['newPassword'];
			$confirmPassword = $_POST['confirmPassword'];

			if(!isset($newPassword) || !isset($confirmPassword)) 
			{
				//
				return $this->respuesta(400, 'parametro no definido', "");
			}
				if(($_POST["newPassword"] == $_POST["confirmPassword"]))
				{
				$decodedToken = $this->decodeToken();
				$user = Model_Users::find('all', 
				            					array('where' => array(
				            							array('userName', '=', $decodedToken->userName), 
				            							array('password', '=', $decodedToken->email)
				            							)
				            						)
				            					);
					if(isset($newPassword) && isset($confirmPassword))
					{
						if(!empty($newPassword)|| !empty($confirmPassword))
						{
							if(strlen($newPassword) >= 5)
							{
								$userTochange = Model_Users::find($decodedToken->id);
								$userTochange ->password = $this->encode($newPassword);
								$userTochange -> save();

								$userName = $userTochange->userName;
				            	$password = $userTochange->password;
				            	$id = $userTochange->id;
				            	$email = $userTochange->email;
				            	$id_role = $userTochange->id_role;

								$token = $this->encodeToken($userName, $password, $id, $email, $id_role);
								$arrayData = array();
			               		$arrayData['token'] = $token;
			               		//LA CONTRASEÑA SE MODIFICO CORRECTAMENTE DESPUES DE COMBIARLA
			               		return $this->respuesta(200, 'Contraseña modificada correctamente', $arrayData);
					 	  }
					 	  else
					 	  {
					 	  	//NECESITA QUE LA PASS TENGA MAS DE 5 CARACTERES
					   		 return $this->respuesta(204, 'Contraseña demasiado corta', "");
					   	  }
				    	}
				    	else
				    	{
				    		//LA CONTRASEÑA ESTA VACIA
				    		return $this->respuesta(400, 'Contraseña vacios', "");
				        }
					}
					else
					{
						//CAMPO VACIO A LA HORA DE CAMBIARLA
						return $this->respuesta(400, 'Campos vacios', "");
					}
				}
				else
				{
					//LAS CONTRASEÑAS NO COINCIDEN
					return $this->respuesta(400, 'las contraseñas no coinciden', "");
				}
		}
		else
		{
			//NO HAS PUESTO EL TOKEN EN AUTENTICACION(BASE)
			return $this->respuesta(400, 'NO AUTORIZADO', "");
		}

	}




	public function get_show()
	{
		$authenticated = $this->authenticate();
    	$arrayAuthenticated = json_decode($authenticated, true);
    
    	 if($arrayAuthenticated['authenticated'])
    	 {
        	$decodedToken = self::decodeToken();

	    			$arrayData = array();
	    			$arrayData['userName'] = $decodedToken->userName;
	    			//RECOGE LOS DATOS PERFECTAMENTE Y LOS ENVIA/OBTIENE
	    			return $this->respuesta(200, 'info User', $arrayData);
    	}
    	else
    	{
    			//NO HA SIDO AUTENTICADO POR TANTO COLOCA EL TOKEN
    			return $this->respuesta(401, 'NO AUTORIZACION','');
    	}
    }
   
}


