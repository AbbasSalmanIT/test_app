<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(Request $request)
    {

        $text = strtolower($request->input('text'));

        $results = DB::select("
        SELECT food.id as id, 
        food.name as name, 
        food.description as description, 
        food.price as price,
        cat_root_vw.id as cat_id, 
        cat_root_vw.name as cat_name, 
        food_root_counts.root_id as root_id, 
        cat_root_vw.root_name as root_name, 
        food_root_counts.root_id_count as root_count, 
        food_cat_counts.food_id_count as cat_count
 FROM food
 JOIN cat_root_vw
 ON food.category_id = cat_root_vw.id
 JOIN (
     SELECT cat_root_vw.id AS cat_id, cat_root_vw.name,
         COUNT(food.id) AS food_id_count 
         FROM food
         JOIN cat_root_vw
         ON   food.category_id = cat_root_vw.id 
          WHERE LOWER(food.name) LIKE '%$text%' 
         OR LOWER(cat_root_vw.name) LIKE '%$text%'
         OR LOWER(cat_root_vw.root_name) LIKE '%$text%'
         GROUP BY cat_root_vw.id,  cat_root_vw.name
         ORDER BY COUNT(food.id) DESC ) food_cat_counts
 ON cat_root_vw.id = food_cat_counts.cat_id
 JOIN (
     SELECT cat_root_vw.root_id, 
     COUNT(food.id) AS root_id_count 
     FROM cat_root_vw 
     JOIN food
     ON cat_root_vw.id = food.category_id 
     WHERE LOWER(food.name) LIKE '%$text%' 
     OR LOWER(cat_root_vw.name) LIKE '%$text%'
     OR LOWER(cat_root_vw.root_name) LIKE '%$text%'
     GROUP BY cat_root_vw.root_id 
     ORDER BY COUNT(food.id) DESC ) food_root_counts
 ON cat_root_vw.root_id = food_root_counts.root_id
 WHERE LOWER(food.name) LIKE '%$text%'
 OR LOWER(cat_root_vw.name) LIKE '%$text%'
 OR LOWER(cat_root_vw.root_name) LIKE '%$text%'
 ORDER BY food_root_counts.root_id_count DESC, food_cat_counts.food_id_count DESC, food.name ASC
        ");



        return view('welcome', [
            'text' => $text,
            'results' => $results
        ]);
    }
}
