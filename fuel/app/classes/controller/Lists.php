<?php 
use \Model\Users;
use Firebase\JWT\JWT;
class Controller_Lists extends Controller_Base
{
	public function post_create()
    {
        $authenticated = $this->authenticate();
        $arrayAuthenticated = json_decode($authenticated, true);
         if($arrayAuthenticated['authenticated'])
         {
            try 
            {
                if ( ! isset($_POST['name'])) 
                {
                    return $this->respuesta(400, 'Algun paramentro esta vacio', '');
                }
                
                $input = $_POST;
                $name = $input['name'];
                $decodedToken = self::decodeToken();

                $list = new Model_Lists();
                $list->title = $name;
                $list->user = Model_Users::find($decodedToken->id);
                $list->save();
                $json = $this->response(array(
                    //LISTA CREADA CON TODAS LAS VARIABLES
                    'code' => 201,
                    'message' => 'lista creada',
                    'name' => $name,
                ));
                return $json;
            } 
            catch (Exception $e) 
            {
                //ERROR EN EL SERVIDOR 
                $json = $this->response(array(
                    'code' => 500,
                    'message' => 'Error del servidor',
                ));
                return $json;
            }
        }
        else
        {
            //TIENES QUE METER EL TOKEN EN AUTHENTICACION PARA QUE PUEDAS CREAR LISTAS
            $json = $this->response(array(
                    'code' => 401,
                    'message' => 'Usuarios no autenticado',
            ));
            return $json;
         }
     }
        
    //Obtener todos los datos 
    public function get_show()
    {
        $authenticated = $this->authenticate();
        $arrayAuthenticated = json_decode($authenticated, true);
         if($arrayAuthenticated['authenticated'])
         {
                $decodedToken = self::decodeToken();
                if(isset($_GET['id'])){
                    $id = $_GET['id'];
                    $list = Model_Lists::find('all',
                                                    array('where' => array(
                                                    array('id_user', '=', $decodedToken->id),
                                                    array('id', '=', $id) 
                                                    )
                                                )
                                            );
                    if(!empty($list))
                    {
                        //SE MUESTRAN TODAS LAS LISTAS YA QUE HAS PUESTO BIEN LOS ID
                        return $this->respuesta(200, 'mostrando la lista', Arr::reindex($list));                            
                    }
                    else
                    {
                        //AL NO HABER CREADO LA LISTA NO TE DEJA VISUALIZAR,PERO NO ES UN ERROR MAS BIEN DICHO ES SOLO AVISO
                            $json = $this->response(array(
                                 'code' => 202,
                                 'message' => 'Aun no tienes ninguna lista',
                                    'data' => ''
                                ));
                                return $json;
                    }
            
                }
                else
                {
                    $lists = Model_Lists::find('all', 
                                                    array('where' => array(
                                                        array('id_user', '=', $decodedToken->id), 
                                                        )
                                                    )
                                                );
                    if(!empty($lists)){
                        //SE MUESTRAN TODAS LAS LISTAS YA QUE HAS PUESTO BIEN LOS ID
                        return $this->respuesta(200, 'mostrando listas del usuario', Arr::reindex($lists));                           
                    }
                    else
                    {
                        //AL NO HABER CREADO LA LISTA NO TE DEJA VISUALIZAR,PERO NO ES UN ERROR MAS BIEN DICHO ES SOLO AVISO
                        $json = $this->response(array(
                                     'code' => 202,
                                     'message' => 'Aun no tienes ninguna lista',
                                        'data' => ''
                                    ));
                                    return $json;
                    }
                }
            }
            else
            {
                //TIENES QUE PONER EL TOKEN EN LA AUTHENTICACION
                $json = $this->response(array(
                             'code' => 401,
                             'message' => 'NO AUTORIZACION',
                                'data' => ''
                            ));
                            return $json;
            }
    }
    

    //Borrar las listas
    public function post_delete()
    {   
        $authenticated = $this->authenticate();
        $arrayAuthenticated = json_decode($authenticated, true);
         if($arrayAuthenticated['authenticated'])
         {
            if (!isset($_POST['id'])) 
            {
                return $this->respuesta(400, 'Falta el parametro id', '');
            }
            $lists = Model_Lists::find($_POST['id']);
            if(!empty($lists))
            {
                $listsName = $lists->title;
                $lists->delete();
                $json = $this->response(array(
                'code' => 200,
                'message' => 'lista borrada',
                'name' => $listsName,
                ));
            }else{
                //AL METER LOS ID CORRECTOS SE TE BORRA
                $json = $this->response(array(
                    'code' => 400,
                    'message' => 'lista no encontrada',
                    'name' => '',
                ));
            }
            return $json;
        }
        else
        {
            //METE EL TOKEN EN LA AUTHENTICACION
            $json = $this->response(array(
                    'code' => 401,
                    'message' => 'Usuarios no autenticado,Autenticate!',
            ));
            return $json;
        }
    }




    //cuando pulse el boton de crear cancion
    public function post_addSL()
    {
        
       $authenticated = $this->authenticate();
        $arrayAuthenticated = json_decode($authenticated, true);
         if($arrayAuthenticated['authenticated'])
         {
                if(!isset($_POST['id_song']) || !isset($_POST['id_list']))
                {
                    return $this->respuesta(400, 'Algun paramentro esta vacio', '');
                }
               
                $input = $_POST;
                $list = Model_Lists::find($input['id_list']);
                if(empty($list))
                {
                    return $this->respuesta(400, 'Esa lista no existente,CREALA TU MISMO', '');
                }
                $song = Model_Songs::find($input['id_song']);
                if(empty($song))
                {
                    return $this->respuesta(400, 'Esa cancion no existe,CREALA TU MISMO', '');
                }
                $addName = Model_ListsHaveSongs::find('all', array(
                    'where' => array(
                        array('id_list', $input['id_list']),
                        array('id_song', $input['id_song'])
                    ),
                ));
                if(!empty($addName))
                {
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Esa cancion ya existe en esta lista',
                        'data' => ''
                    ));
                    return $response;
                }
                $list = Model_Lists::find($input['id_list']);
                $list->Songs[] = Model_Songs::find($input['id_song']);  
                $list->save();
                $response = $this->response(array(
                    'code' => 200,
                    'message' => 'Cancion agregada',
                    'data' => ''
                ));
                return $response;
            }
         else
         {
            $json = $this->response(array(
                  'code' => 401,
                  'message' => 'Usuarios no autenticado',
            ));
        return $json;
         }
     }       
    
   


    //boton que actualice las listas que hay creadas.
    public function post_update()
    {
        $authenticated = $this->authenticate();
        $arrayAuthenticated = json_decode($authenticated, true);
         if($arrayAuthenticated['authenticated'])
         {
            if (!isset($_POST['id']) && ! isset($_POST['name']) ) 
            {
                //TIENES QUE METER BIEN LOS PARAMETROS
                $json = $this->response(array(
                    'code' => 400,
                    'message' => 'Parametros incorrectos'
                ));
                return $json;
            }
            $id = $_POST['id'];
            $updateList = Model_Lists::find($id);
            $title = $_POST['name'];

            if(!empty($updateList))
            {
                $decodedToken = self::decodeToken();
                if($decodedToken->id == $updateList->id_user)
                {
                    $updateList->title = $title;
                    $updateList->save();
                    //AL PONER EL NOMBRE Y EL ID Y HABER AÑADIDO CANCIONES DENTRO DE ELLA AL ACTUALIZAR SI DESPUES TE METES TESALDRAN TODAS LAS CANCIONES ACTUALIZADAS
                    $json = $this->response(array(
                    'code' => 200,
                    'message' => 'Lista actualizada, titulo nuevo:', $title,
                    ));
                }
                else
                {
                    //METE EL TOKEN EN AUTHENTICACION PARA QUE PUEDAS ACTUALIZARLO
                    $json = $this->response(array(
                        'code' => 401,
                        'message' => 'No estas autorizado a cambiar esa lista'
                    ));
                    return $json;
                }
            }
            else
            {
                //NO TIENES ESA LISTA CREADA METELA EN OTRA O CREA UNA LISTA CON ESE NOMBRE
                $json = $this->response(array(
                    'code' => 400,
                    'message' => 'lista no encontrada'
                ));
                return $json;
            }
        }
        else
        {
            //TOKEN EN AUTHENTICACION Y PODRAS 
            $json = $this->response(array(
                    'code' => 401,
                    'message' => 'Usuario no autenticado',
            ));
            return $json;
        }
    }    




    //cuando pulses encima de la cancion
    public function get_showSL()
    {
        try
        {
        $authenticated = $this->authenticate();
        $arrayAuthenticated = json_decode($authenticated, true);
         if($arrayAuthenticated['authenticated'])
         {
                if(!isset($_GET['id_list']))
                {
                    return $this->respuesta(400, 'Debes rellenar todos los campos', '');
                }
                $input = $_GET;
                $songsFromList = Model_ListsHaveSongs::find('all', array(
                    'where' => array(
                        array('id_list', $input['id_list'])
                    ),
                ));
                if(!empty($songsFromList)){
                    foreach ($songsFromList as $key => $list)
                    {
                        $songsOfList[] = Model_Songs::find($list->id_song);
                    }
                    foreach ($songsOfList as $key => $song)
                    {
                        $songs[] = $song;
                    }  
                    //CANCION ENCONTRADA
                    return $this->respuesta(200, 'Canciones encontradas', $songs);
                }
                else
                {
                    //NO EXISTEN LAS CANCIONES
                    return $this->respuesta(400, 'No existen canciones en la lista', '');
                }
            }
            else
            {
                //AUTENTICA TODO CON EL TOKEN PARA PODER VISUALIZAR LOS DATOS 
                return $this->respuesta(400, 'Error de autenticación', '');
            }
        }
        catch (Exception $e)
        {
            //ERROR CON EL SERVIDOR O CONEXION DE RED
            return $this->respuesta(500, 'Error del servidor', '');
        }
    }



   

}






