<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
	public function index(){
		// Buscar usuarios en la base de datos		
		$users = User::orderBy('id_number','desc')->get();

		if(is_object($users) && sizeof($users) != 0){
			$data = array(
				'status'			=> 'success',
				'code'				=> 200,
				'users'				=> $users
			);
		} else {
			$data = array(
				'status'			=> 'error',
				'code'				=> 404,
				'message'			=> 'No se han encontrado usuarios en la base de datos'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function show($id){
		// Buscar el usuario en la base de datos
		$user = User::find($id);

		if(is_object($user) && $user != null){
			$data = array(
				'status'			=> 'success',
				'code'				=> 200,
				'user'				=> $user
			);
		} else {
			$data = array(
				'status'			=> 'error',
				'code'				=> 404,
				'message'			=> 'No se ha encontrado ningún usuario con el id '.$id.' en la base de datos'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function showByIdNumber($idNumber){
		// Buscar el usuario por número de cédula
		$user = User::where('id_number', $idNumber)
					->get();

		if(is_object($user) && sizeof($user) != 0){
			$data = array(
				'status'			=> 'success',
				'code'				=> 200,
				'user'				=> $user
			);
		} else {
			$data = array(
				'status'			=> 'error',
				'code'				=> 404,
				'message'			=> 'No se ha encontrado ningún usuario con número de cédula '.$idNumber.' en la base de datos'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function store(Request $request){
		// Recoger los datos json del request
		$json = $request->input('json', null);
		$params = json_decode($json);
		$params_array = json_decode($json, true);

		if(is_object($params)){
			// Validar los datos ingresados
			$validate = \Validator::make($params_array, [
				'id_number'				=> 'required|unique:user',
				'name'					=> 'required|regex:/^[\pL\s\-]+$/u',
				'surname'				=> 'required|regex:/^[\pL\s\-]+$/u',
				'role'					=> 'required',
				'password'				=> 'required',
				'autorized_categories'	=> 'required'
			]);
			if($validate->fails()){
				$data = array(
					'status'			=> 'error',
					'code'				=> 400,
					'message'			=> 'La validación de los datos ha fallado. Revise la configuración del sistema',
					'errors'			=> $validate->errors()
				);
			} else {
				// Cifrar la contraseña
				$password_hash = hash('SHA256', $params->password);

				// Guardar los datos en la base de datos
				$user = new User();

				$user->id_number 			= $params->id_number;
				$user->name 				= $params->name;
				$user->surname 				= $params->surname;
				$user->role 				= $params->role;
				$user->password 			= $password_hash;
				$user->autorized_categories	= $params->autorized_categories;

				$user->save();

				$data = array(
					'status'			=> 'success',
					'code'				=> 200,
					'message'			=> 'Se ha guardado correctamente un nuevo usuario en la base de datos'
				);
			}
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 400,
				'message'				=> 'Se han ingresado los datos json de manera incorrecta. Error en el servicio'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function massiveStore(Request $request){
		// Recoger los datos json del request
		$json = $request->input('json', null);
		$params = json_decode($json);
		$params_array = json_decode($json, true);

		$errors = 0;
		$errorsMessage = '';
		$successes = 0;
		$successesMessage = '';

		for($i=0; $i < sizeof($params); $i++){
			if(is_object($params[$i])){
				// Validar los datos ingresados
				$validate = \Validator::make($params_array[$i], [
					'id_number'				=> 'required|unique:user',
					'name'					=> 'required|regex:/^[\pL\s\-]+$/u',
					'surname'				=> 'required|regex:/^[\pL\s\-]+$/u',
					'password'				=> 'required',
					'autorized_categories'	=> 'required'
				]);
				if($validate->fails()){
					$errors++;
					$errorsMessage = $errorsMessage.' '.$params[$i]->id_number;
				} else{
					// Cifrar la contraseña 
					$password_hash = hash('SHA256', $params[$i]->password);

					// Guardar el usuario
					$user = new User();

					$user->id_number 			= $params[$i]->id_number;
					$user->name 				= $params[$i]->name;
					$user->surname 				= $params[$i]->surname;
					$user->role 				= 'user';
					$user->password 			= $password_hash;
					$user->autorized_categories	= $params[$i]->autorized_categories;

					$user->save();

					$successes++;
					$successesMessage = $successesMessage.' '.$params[$i]->id_number;
				}
			} else {
				$errors ++;
			}
		}
		$data = array(
			'status'			=> 'success',
			'code'				=> 200,
			'errors'			=> $errors,
			'errorsMessage'		=> $errorsMessage,
			'successes'			=> $successes,
			'successesMessage'	=> $successesMessage
		);
		return response()->json($data, $data['code']);
	}

	public function update($id, Request $request){
		// Buscar el usuario que se desea actualizar en la base de datos
		$user = User::find($id);

		if(is_object($user) && $user != null){
			// Recoger los datos json del request
			$json = $request->input('json', null);
			$params = json_decode($json);
			$params_array = json_decode($json, true);

			if(is_object($params)){
				// Validar los datos ingresados
				$validate = \Validator::make($params_array, [
					'id_number'				=> 'required|unique:user,id_number,'.$id,
					'name'					=> 'required|regex:/^[\pL\s\-]+$/u',
					'surname'				=> 'required|regex:/^[\pL\s\-]+$/u',
					'role'					=> 'required',
					'autorized_categories'	=> 'required'
				]);
				if($validate->fails()){
					$data = array(
						'status'			=> 'error',
						'code'				=> 400,
						'message'			=> 'La validación de los datos ha fallado. Revise la configuración del sistema',
						'errors'			=> $validate->errors()
					);
				} else {
					// Eliminar los datos que no se desean actualizar
					unset($params_array['id']);
					unset($params_array['password']);
					unset($params_array['created_at']);
					unset($params_array['updated_at']);

					// Actualizar el registro
					$user = User::where('id', $id)
								->update($params_array);

					if($user != 0){
						$data = array(
							'status'		=> 'success',
							'code'			=> 200,
							'message'		=> 'El usuario '.$params_array['id_number'].' se ha actualizado correctamente',
							'changes'		=> $params_array
						);
					} else {
						$data = array(
							'status'		=> 'error',
							'code'			=> 400,
							'message'		=> 'El usuario '.$params_array['id_number'].' no se ha podido actualizar en la base de datos'
						);
					}
				}
			} else {
				$data = array(
					'status'			=> 'error',
					'code'				=> 400,
					'message'			=> 'Se han ingresado los datos json de manera incorrecta. Error en el servicio'
				);
			}
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'El usuario que está intentado actualizar no existe en la base de datos'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function passwordUpdate($id, Request $request){
		// Buscar el usuario que se desea actualizar en la base de datos
		$user = User::find($id);

		if(is_object($user) && $user != null){
			// Recoger los datos json del request
			$json = $request->input('json', null);
			$params = json_decode($json);
			$params_array = json_decode($json, true);

			if(is_object($params)){
				// Validar los datos ingresados
				$validate = \Validator::make($params_array, [
					'password'			=> 'required'
				]);
				if($validate->fails()){
					$data = array(
						'status'		=> 'error',
						'code'			=> 400,
						'message'		=> 'La validación de los datos ha fallado. Revise la configuración del sistema',
						'errors'		=> $validate->errors()
					);
				} else {
					// Eliminar los datos que no se desean actualizar
					unset($params_array['id']);
					$id_number = $params_array['id_number'];
					unset($params_array['id_number']);
					unset($params_array['name']);
					unset($params_array['surname']);
					unset($params_array['role']);
					unset($params_array['created_at']);
					unset($params_array['updated_at']);
					unset($params_array['autorized_categories']);

					// Cifrar la contraseña
					$password_hash = hash('SHA256', $params->password);
					$params_array['password'] = $password_hash;

					// Actualizar el registro
					$user = User::where('id', $id)
								->update($params_array);

					if($user != 0){
						$data = array(
							'status'		=> 'success',
							'code'			=> 200,
							'message'		=> 'La contraseña del usuario '.$id_number.' se ha actualizado correctamente',
							'changes'		=> $params_array
						);
					} else {
						$data = array(
							'status'		=> 'error',
							'code'			=> 400,
							'message'		=> 'No se ha podido actualizar la contraseña del usuario '.$params_array['password']
						);
					}
				}
			} else {
				$data = array(
					'status'			=> 'error',
					'code'				=> 400,
					'message'			=> 'Se han ingresado los datos json de manera incorrecta. Error en el servicio'
				);
			}
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'El usuario que está intentado actualizar no existe en la base de datos'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function destroy($id){
		// Buscar el usuario que se desea eliminar en la base de datos
		$user = User::find($id);

		if(is_object($user) && $user != null){
			$id_number = $user->id_number;

			$user->delete();

			$data = array(
				'status'				=> 'success',
				'code'					=> 200,
				'message'				=> 'El usuario '.$id_number.' se ha eliminado correctamente de la base de datos'
			);
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'El usuario que está intentado eliminar no existe en la base de datos'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function login(Request $request){
		// Definir la variable jwtAuth
		$jwtAuth = new \JwtAuth();
		// Recibir los datos por POST
		$json = $request->input('json', true);
		$params = json_decode($json);
		$params_array = json_decode($json, true);

		if(is_object($params) && $params != null){
			// Validar los datos
			$validate = \Validator::make($params_array, [
				'id_number'		=> 'required',
				'password'		=> 'required'
			]);
			if($validate->fails()){
				$data = array(
					'status'	=> 'error',
					'code'		=> 400,
					'message'	=> 'La validación de datos ha fallado. Comuniquese con el administrador de la plataforma',
					'errors'	=> $validate->errors()
				);
			} else{
				// Cifrar la contraseña
				$hash_password = hash('SHA256', $params->password);

				// Devolver el token o los datos
				$signup = $jwtAuth->signup($params->id_number, $hash_password);
				if(isset($params->gettoken)){
					$signup = $jwtAuth->signup($params->id_number, $hash_password, true);
				}
				if($signup){
					$data = array(
						'status'	=> 'success',
						'code'		=> 200,
						'signup'	=> $signup
					);
				} else{
					$data = array(
						'status'	=> 'error',
						'code'		=> 401,
						'message'	=> 'Los datos ingresados son incorrectos. Login incorrecto'
					);
				}					
			}
				
		} else{
			$data = array(
				'status'		=> 'error',
				'code'			=> 400,
				'message'		=> 'Se han ingresado los datos al servidor de manera incorrecta. Error en el servicio'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}
}