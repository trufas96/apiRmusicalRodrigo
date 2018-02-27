<?php 
class Controller_Songs extends Controller_Base
{

	public function post_create()
    {
        $authenticated = $this->authenticate();
        $arrayAuthenticated = json_decode($authenticated, true);
         if($arrayAuthenticated['authenticated'])
         {
            try {
                if ( ! isset($_POST['title']) && ! isset($_POST['url'])) 
                {
                    return $this->respuesta(400, 'parametros incorrectos', '');
                }
                
                $input = $_POST;
                $title = $input['title'];
                $url = $input['url'];
                $song = new Model_Songs();
                $song->title = $title;
                $song->url = $url;
                $song->save();
                //HAS PUESTO TODOS LOS DATOS BIEN Y POR TANTO HAS CREADO LA CANCION
                return $this->respuesta(200, 'Cancion creada', '');
            } 
            catch (Exception $e) 
            {
                //ERROR DE SERVIDOR
                $json = $this->response(array(
                    'code' => 500,
                    'message' => 'error del servidor',
                ));
                return $json;
            }
        }
        else
        {
            //METE EL TOKEN EN LA AUTHENTICACION
        	$json = $this->response(array(
                    'code' => 401,
                    'message' => 'Usuario no autenticado',
                ));
                return $json;
        }
        
    }


    //Borrar las canciones
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
            $song = Model_Songs::find($_POST['id']);
            if(!empty($song))
            {
                $songsName = $song->title;
                $song->delete();
                $json = $this->response(array(
                'code' => 200,
                'message' => 'Cancion borrada',
                'name' => $songsName,
                ));
            }else{
                //AL METER LOS ID CORRECTOS SE TE BORRA
                $json = $this->response(array(
                    'code' => 400,
                    'message' => 'Cancion no encontrada',
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


    public function get_songs()
    {   
        $authenticated = $this->authenticate();
        $arrayAuthenticated = json_decode($authenticated, true);
         if($arrayAuthenticated['authenticated'])
         {
            try {
                
                
                $songs = Model_Songs::find('all');
	            $indexedSongs = Arr::reindex($songs);
	            foreach ($indexedSongs as $key => $song) {
	                $title[] = $song->title;
	                $url[] = $song->url;
	                $id[] = $song->id;
	            }
                //AL METER BIEN LOS DATOS SE TE CREA BIEN LA CANCION
                $json = $this->response(array(
                    'code' => 200,
                    'message' => 'Canciones',
                    'title' => $title,
                    'url' => $url,
                    'id' => $id,
                ));
                return $json;
            } 
            catch (Exception $e) 
            {
                //ERROR EN EL SERVIDOR O ERROR DE RED
                return $this->respuesta(500, 'Error del servidor', '');
            }
        }
        else
        {
            //METE EL TOKEN EN LA AUTHENTICACION
        	return $this->respuesta(401, 'Usuario no autenticado', '');
        }
        
    }
}