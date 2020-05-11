<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VotesCategory extends Model
{
	protected $table = 'votes_category';

	protected $fillable = [
		'name','startDate','startTime','endDate','endTime'
	];
}
