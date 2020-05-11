<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Votes;

class VotesController extends Controller
{
	public function __construct(){
	}

	public function index(){
		// Buscar todas las votaciones en la base de datos
		$votes = Votes::all();

		if(is_object($votes) && sizeof($votes) != 0){
			$data = array(
				'status'				=> 'success',
				'code'					=> 200,
				'votes'					=> $votes
			);
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'No se han encontrado votos registrados en la base de datos del sistema'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function show($id){
		// Buscar el voto en la base de datos
		$vote = Votes::find($id);

		if(is_object($vote) && sizeof($vote) != 0){
			$data = array(
				'status'				=> 'success',
				'code'					=> 200,
				'vote'					=> $vote
			);
		} else {
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'No se ha encontrado ningún voto con id '.$id.' registrado en la base de datos del sistema'
			);
		}

		// Devolver respuesta
		return response()->json($data, $data['code']);
	}

	public function searchVote($userId, $categoryId){
		$vote = Votes::where('user_id', $userId)
					 ->where('votes_category_id', $categoryId)
					 ->first();

		if(is_object($vote) && $vote != null){
			$data = array(
				'status'				=> 'error',
				'code'					=> 404,
				'message'				=> 'El usuario autenticado ya ha realizado la votación para la categoría '
			);
		} else {
			$data = array(
				'status'				=> 'success',
				'code'					=> 200
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
				'user_id'				=> 'required|numeric',
				'postulates_id'			=> 'required|numeric',
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
				$vote = new Votes();

				$vote->user_id 			= $params->user_id;
				$vote->postulates_id 	= $params->postulates_id;
				$vote->votes_category_id= $params->votes_category_id;

				$vote->save();

				$data = array(
					'status'			=> 'success',
					'code'				=> 200,
					'message'			=> 'Se ha registrado un nuevo voto correctamente al sistema'
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
}