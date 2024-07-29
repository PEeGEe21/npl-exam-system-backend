<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CbtResult extends Model
{
    public static $not_attended = 0;
    public static $viewed = 1;
    public static $attended = 2;
    public static $time_color = "green";

    public static $states = [
        0   => 'not_attended',
        1  => 'viewed',
        2  => 'attended',
    ];


    public static $selection_all = 1;
    public static $selection_date_range = 2;

    protected $guarded = ['id'];

    protected $casts = [
        'started'   => 'boolean',
        'finished'  => 'boolean',
        'question_test_ids' => 'array'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function scores()
    {
        return $this->hasMany(CbtResultsScore::class, 'result_id', 'id');
    }

    public function test()
    {
        return $this->hasOne(CbtTest::class, 'id', 'test_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'id', 'student_id');
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
