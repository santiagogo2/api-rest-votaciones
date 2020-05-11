<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Postulates;

class PostulatesController extends Controller
{
	public function __construct(){

	}

	public function index(){
		// Buscar todos los portulados en la base de datos
		$postulates = Postulates::with('VotesCategory')
								->orderBy('name', 'desc')
								->get();

		if(is_object($postulates) && sizeof($postulates) != 0){
			$data = array(
				'status'				=> 'success',
				'code'					=> 200,
				'postulates'			=> $postulates
			);
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'No se han encontrado postulados registrados en la base de datos del sistema'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function show($id){
		// Buscar un postulado en la base de datos
		$postulate = Postulates::with('VotesCategory')
							   ->find($id);

		if(is_object($postulate) && $postulate != null){
			$data = array(
				'status'				=> 'success',
				'code'					=> 200,
				'postulate'				=> $postulate
			);
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'No se ha encontrado ningún postulado con id '.$id.' registrado en la base de datos del sistema'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function getPostulateByCategory($category){
		$postulates = Postulates::where('votes_category_id', $category)
								->orWhere('id', 1)
								->orderBy('name', 'asc')
								->get();

		if(is_object($postulates) && sizeof($postulates) != 0){
			$data = array(
				'status'				=> 'success',
				'code'					=> 200,
				'postulates'			=> $postulates
			);
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'No se han encontrado postulados registrados en la base de datos del sistema en la categoría seleccionada'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function getPostulateByCategoryWithResults($category){
		$prueba = $category;
		$postulates = Postulates::with(['Votes' => function($query) use ($category){
									$query->where('votes_category_id', $category);
								}])
								->where('votes_category_id', $category)
								->orWhere('id', 1)
								->orderBy('name', 'asc')
								->get();

		if(is_object($postulates) && sizeof($postulates) != 0){
			$data = array(
				'status'				=> 'success',
				'code'					=> 200,
				'postulates'			=> $postulates
			);
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'No se han encontrado postulados registrados en la base de datos del sistema en la categoría seleccionada'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function getPostulateImage($filename){
		$isset = \Storage::disk('postulates')->exists($filename);

		if($isset){
			$file = \Storage::disk('postulates')->get($filename);
			return new Response($file, 200);
		} else {
			$data = array(
				'status'			=> 'error',
				'code'				=> 404,
				'message'			=> 'La imágen que está buscando no existe en el servidor'
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

		if(is_object($params) && $params != null){
			// Validar los datos ingresados
			$validate = \Validator::make($params_array, [
				'id_number'				=> 'required|numeric|unique:postulates',
				'name'					=> 'required|regex:/^[\pL\s\-]+$/u',
				'surname'				=> 'required|regex:/^[\pL\s\-]+$/u',
				'description'			=> 'required',
				'photo'					=> 'nullable',
				'votes_category_id'		=> 'required|numeric'
			]);
			if($validate->fails()){
				$data = array(
					'status'			=> 'error',
					'code'				=> 400,
					'message'			=> 'La validación de los datos ha fallado. Revise la configuración del sistema',
					'errors'			=> $validate->errors()
                );
			} else {
				// Guardar los datos en la base de datos
				$postulate = new Postulates();

				$postulate->id_number	= $params->id_number;
				$postulate->name 		= $params->name;
				$postulate->surname 	= $params->surname;
				$postulate->description = $params->description;
				$postulate->photo 		= $params->photo;
				$postulate->votes_category_id = $params->votes_category_id;

				$postulate->save();

				$data = array(
					'status'			=> 'success',
					'code'				=> 200,
					'message'			=> 'Se ha registrado un nuevo postulado correctamente al sistema'
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

	public function upload(Request $request){
		// Recoger los datos de la petición
		$image = $request->file('file0');

		// Validar que si es una imagen
		$validate = \Validator::make($request->all(), [
			'file0'						=> 'required|image|mimes:jpg,jpeg,png,gif'
		]);
		if($validate->fails()){
			$data = array(
				'status'			=> 'error',
				'code'				=> 400,
				'message'			=> 'La validación de los datos ha fallado. Revise la configuración del sistema',
				'errors'			=> $validate->errors()
			);
		} else {
			// Guardar la imágen en el disco
			if($image){
				$image_name = time().$image->getClientOriginalName();
				\Storage::disk('postulates')->put($image_name, \File::get($image));

				$data = array(
					'status'				=> 'success',
					'code'					=> 200,
					'image'					=> $image_name
				);
			} else {
				$data = array(
					'status'				=> 'error',
					'code'					=> 400,
					'message'				=> 'No se ha podido subir la imágen al servidor'
				);
			}
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function update($id, Request $request){
		// Buscar si el postulado existe en la base de datos
		$postulate = Postulates::find($id);

		if(is_object($postulate) && $postulate != null){
			// Recoger los datos json del request
			$json = $request->input('json', null);
			$params = json_decode($json);
			$params_array = json_decode($json, true);

			if(is_object($params) && $params != null){
				// Validar los datos ingresados
				$validate = \Validator::make($params_array, [
					'id_number'				=> 'required|numeric|unique:postulates,id_number,'.$id,
					'name'					=> 'required|regex:/^[\pL\s\-]+$/u',
					'surname'				=> 'required|regex:/^[\pL\s\-]+$/u',
					'description'			=> 'required',
					'photo'					=> 'nullable',
					'votes_category_id'		=> 'required|numeric'
				]);
				if($validate->fails()){
					$data = array(
						'status'			=> 'error',
						'code'				=> 400,
						'message'			=> 'La validación de los datos ha fallado. Revise la configuración del sistema',
						'errors'			=> $validate->errors()
					);
				} else {
					// Eliminar los datos que no se deseen actualizar
					unset($params_array['id']);
					unset($params_array['created_at']);
					unset($params_array['updated_at']);
					unset($params_array['votes_category']);

					// $params_array['name'] = strtoupper($params_array['name']);
					// $params_array['surname'] = strtoupper($params_array['surname']);
					// $params_array['description'] = strtoupper($params_array['description']);

					// Actualizar los datos
					$postulate = Postulates::where('id', $id)
										   ->update($params_array);

					if($postulate != 0){
						$data = array(
							'status'	=> 'success',
							'code'		=> 200,
							'message'	=> 'El postulado '.$params_array['name'].' '.$params_array['surname'].' se actualizó correctamente',
							'changes'	=> $params_array
						);
					} else {
						$data = array(
							'status'	=> 'error',
							'code'		=> 400,
							'message'	=> 'El postulado '.$params_array['name'].' '.$params_array['surname'].' no se ha podido actualizar en el sistema'
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
				'message'				=> 'El postulado que está intentado actualizar, no existe en la base de datos'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function destroy($id){
		// Buscar si el postulado que desea eliminar existe en la base de datos
		$postulate = Postulates::find($id);

		if(is_object($postulate) && $postulate != null){
			$name = $postulate->name.' '.$postulate->surname;
			if($postulate->photo){
				$filename = $postulate->photo;
				$isset = \Storage::disk('postulates')->exists($filename);
				if($isset){
					\Storage::disk('postulates')->delete($filename);
				}
			}

			$postulate->delete();

			$data = array(
				'status'				=> 'success',
				'code'					=> 200,
				'message'				=> 'El postulado '.$name.' se ha eliminado correctamente de la base de datos'
			);
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'El postulado que está intentado eliminar, no existe en la base de datos'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}
}