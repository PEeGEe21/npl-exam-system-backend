<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CbtQuestion extends Model
{
    protected $guarded = ['id'];

    public static $choice  = 1;
    public static $selection  = 2;
    public static $subjective  = 3;
    public static $comprehension  = 4;
    public static $theory  = 5;

    public static $text_type  = 1;
    public static $fileupload  = 2;



    public function scopeExists($query,$data){
        $question =$query->whereRaw('subject_id= ? and level_id= ? and lower(question)= ?'
            ,[$data['subject_id'],$data['level_id'],strtolower($data['question'])])
            ->select('id')
            ->first();
        return $question;
    }

    public function difficulty()
    {
        return $this->hasOne(CbtDifficulty::class, 'id', 'difficulty_id');
    }

    public function optionType()
    {
        return $this->hasOne(CbtOptionType::class, 'id', 'option_type_id');
    }
    public function optionAnswerType()
    {
        return $this->hasOne(CbtOptionAnswerType::class, 'id', 'option_answer_type_id');
    }
    public function topic()
    {
        return $this->hasOne(CbtTopic::class, 'id', 'topic_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function answers(){
        return $this->hasMany(CbtAnswer::class,'question_id');
    }

    public function questionTest(){
        return $this->hasOne(CbtQuestionTest::class,'question_id','id');
    }

    public function subQuestion()
    {
        return $this->hasMany(CbtSubQuestion::class, 'question_id','id');
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
