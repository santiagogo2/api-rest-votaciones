<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Votes extends Model
{
	protected $table = 'votes';

	public function Postulates(){
		return $this->hasMany('App\Postulates');
	}

	public function User(){
		return $this->hasMany('App\User');
	}

	public function VotesCategory(){
		return $this->hasMany('App\VotesCategory');
	}
}