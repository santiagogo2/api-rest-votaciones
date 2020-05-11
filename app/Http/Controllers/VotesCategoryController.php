<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VotesCategory;

class VotesCategoryController extends Controller
{
	public function index(){
		$categories = VotesCategory::all();

		if(is_object($categories) && sizeof($categories) != 0){
			$data = array(
				'status'				=> 'success',
				'code'					=> 200,
				'categories'			=> $categories
			);
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'No se han encontrado categorias de votaciones en la base de datos del sistema'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function show($id){
		$category = VotesCategory::find($id);

		if(is_object($category) && $category != null){
			$data = array(
				'status'				=> 'success',
				'code'					=> 200,
				'category'				=> $category
			);
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'No ser ha encontrado ninguna categoría de votación con el id '.$id
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function store(Request $request){
		// Obtener los datos json del request
		$json = $request->input('json', null);
		$params = json_decode($json);
		$params_array = json_decode($json, true);

		if(is_object($params) && $params != null){
			// Validar los datos ingresados
			$validate = \Validator::make($params_array, [
				'name'					=> 'required|unique:votes_category',
				'startDate'				=> 'required',
				'startTime'				=> 'required',
				'endDate'				=> 'required',
				'endTime'				=> 'required'
			]);
			if($validate->fails()){
				$data = array(
					'status'			=> 'error',
					'code'				=> 400,
					'message'			=> 'La validación de los datos ha fallado. Revise la configuración del sistema',
					'errors'			=> $validate->errors()
				);
			} else {
				$params->name = strtoupper($params->name);

				// Guardar los datos en la base de datos
				$category = new VotesCategory();

				$category->name 		= $params->name;
				$category->startDate 	= $params->startDate;
				$category->startTime 	= $params->startTime;
				$category->endDate 		= $params->endDate;
				$category->endTime 		= $params->endTime;

				$category->save();

				$data = array(
					'status'			=> 'success',
					'code'				=> 200,
					'message'			=> 'Se ha registrado una nueva categoría de votación correctamente al sistema'
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

	public function update($id, Request $request){
		// Validar si la categoría existe en la base de datos
		$category = VotesCategory::find($id);

		if(is_object($category) && $category != null){
			// Recoger los datos json del request
			$json = $request->input('json', null);
			$params = json_decode($json);
			$params_array = json_decode($json, true);

			if(is_object($params) && $params != null){
				// Validar los datos ingresados
				$validate = \Validator::make($params_array, [
					'name'				=> 'required|unique:votes_category,name,'.$id,
					'startDate'			=> 'required',
					'startTime'			=> 'required',
					'endDate'			=> 'required',
					'endTime'			=> 'required'
				]);
				if($validate->fails()){
					$data = array(
						'status'			=> 'error',
						'code'				=> 400,
						'message'			=> 'La validación de los datos ha fallado. Revise la configuración del sistema',
						'errors'			=> $validate->errors()
					);
				} else {
					// Eliminar lo que no se desea actualizar
					unset($params_array['id']);
					unset($params_array['created_at']);
					unset($params_array['updated_at']);

					// Actualizar el registro
					$params_array['name'] = strtoupper($params_array['name']);
					$category = VotesCategory::where('id', $id)
											 ->update($params_array);

					if($category != 0){
						$data = array(
							'status'	=> 'success',
							'code'		=> 200,
							'message'	=> 'La catergoría de votación '.$params_array['name'].' se actualizó correctamente',
							'changes'	=> $params_array
						);
					} else {
						$data = array(
							'status'	=> 'error',
							'code'		=> 400,
							'message'	=> 'La categoría de votación '.$params_array['name'].' no se ha podido actualizar en el sistema'
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
				'message'				=> 'La categoría de votación que está intentado actualizar, no existe en la base de datos'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function destroy($id){
		// Validar si el registro existe en la base de datos
		$category = VotesCategory::find($id);

		if(is_object($category) && $category != null){
			// Eliminar el registro
			$category_name = $category->name;

			$category->delete();

			$data = array(
				'status'				=> 'success',
				'code'					=> 200,
				'message'				=> 'Se ha eliminado la categoría de votación '.$category_name.' correctamente'
			);
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'La categoría de votación que está intentado actualizar, no existe en la base de datos'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}
}
