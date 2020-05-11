<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Postulates extends Model
{
	protected $table = 'postulates';

	public function VotesCategory(){
		return $this->belongsTo('App\VotesCategory', 'votes_category_id');
	}

	protected $fillable = [
		'name', 'surname', 'photo', 'votes_category_id'
	];
}