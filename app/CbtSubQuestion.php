<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CbtSubQuestion extends Model
{
    protected $guarded = ['id'];

    public static $choice  = 1;
    public static $selection  = 2;



    public function scopeExists($query,$data){
        $question =$query->whereRaw('subject_id= ? and level_id= ? and lower(question)= ?'
            ,[$data['subject_id'],$data['level_id'],strtolower($data['question'])])
            ->select('id')
            ->first();
        return $question;
    }

    public function optionType()
    {
        return $this->hasOne(CbtOptionType::class, 'id', 'option_type_id');
    }
    public function optionAnswerType()
    {
        return $this->hasOne(CbtOptionAnswerType::class, 'id', 'option_answer_type_id');
    }

    public function answers(){
        return $this->hasMany(CbtSubAnswer::class,'question_id');
    }

    public function question(){
        return $this->hasOne(CbtQuestion::class,'question_id','id');
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
