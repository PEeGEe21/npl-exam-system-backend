<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CbtResultsScore extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'scored'    => 'boolean',
        'is_correct'    => 'boolean',
        'answer'    => 'array'
    ];

    public function result()
    {
        return $this->hasOne(CbtResult::class, 'id', 'result_id');
    }

    public function test()
    {
        return $this->hasOne(CbtTest::class, 'id', 'test_id');
    }

    public function question()
    {
        return $this->hasOne(CbtQuestionTest::class, 'id', 'question_test_id');
    }

    public function ScopeByFilter($query, $data)
    {
        $whereClause = [];
        $whereParam = [];
        foreach ($data as $k => $v) {
            $whereClause[] = $k . '=?';
            $whereParam[] = $v;
        }
        $whereClause = implode(' and ', $whereClause);
        $result = $query->whereRaw($whereClause, $whereParam); //Collect the result using ->first() or ->get()
//        dd ($result->get());
        return ($result);
    }
}
