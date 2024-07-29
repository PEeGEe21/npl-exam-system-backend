<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CbtAnswer extends Model
{
    protected $guarded = ['id'];

    public function scopeExists($query,$data){
        $topic=$query->whereRaw('subject_id= ? and school_id= ? and  session_id= ? and lower(name)= ?'
            ,[$data['subject_id'],$data['school_id'],$data['session_id'],strtolower($data['name'])])->first();
        return $topic;
    }

    public function ScopeByFilter($query,$data){
        $whereClause=[];
        $whereParam=[];
        foreach($data as $k=>$v){
            $whereClause[]=$k.'=?';
            $whereParam[]=$v;
        }
        $whereClause=implode(' and ',$whereClause);
        $result= $query->whereRaw($whereClause,$whereParam); //Collect the result using ->first() or ->get()
        return ($result);
    }
}
