<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{
	public $key;

	public function __construct(){
		$this->key = 'Esta_es_la_Key_para_las_Votaciones_que se _realizen en la _subred_Sur';
	}

	public function signup($id_number, $password, $getToken=null){
		// Buscar si existe el usuario con sus credenciales.
		$user = User::where([
			'id_number'			=> $id_number,
			'password'			=> $password
		])->first();

		// Comprobar si son correctas, es decir, si devuelve un objeto
		$signup = false;
		if(is_object($user) && $user != null){
			$signup = true;
		}

		// Generar el token del usuario identificado
		if($signup){
			$token = array(
				'sub'					=> $user->id,
				'id_number'				=> $user->id_number,
				'name'					=> $user->name,
				'surname'				=> $user->surname,
				'role'					=> $user->role,
				'autorized_categories'	=> $user->autorized_categories,
				'iat'					=> time(),
				'exp'					=> time() + (24*60*60)
			);

			$jwt = JWT::encode($token, $this->key, 'HS256');

			// Devolver los datos decodificados o el token en función de un parametro
			if(is_null($getToken)){
				$data = $jwt;
			} else{
				$decoded = JWT::decode($jwt, $this->key, ['HS256']);
				$data = $decoded;
			}
			return $data;
		}
		return false;		
	}

	public function checkToken($jwt, $getIdentity=false){
		$auth = false;

		try{
			$jwt = str_replace('"', '', $jwt);
			$decoded = JWT::decode($jwt, $this->key, ['HS256']);
		} catch(\UnexpectedValueException $e){
			$auth = false;
		} catch(\DomainException $e){
			$auth = false;
		}

		if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
			$auth = true;
		} else{
			$auth = false;
		}

		if($getIdentity){
			return $decoded;
		}

		return $auth;
	}
}
?>