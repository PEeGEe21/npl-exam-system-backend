<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CbtResultsStore extends Model
{
    protected $fillable  = [
        'student_id',
        'test_id',
        'test_data',
    ];
    protected $guarded = ['id'];

    protected $casts = [
        'test_data'    => 'array'
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
        return $this->hasOne(CbtQuestion::class, 'id', 'question_id');
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
        $result = $query->whereRaw($whereClause, $whereParam);
        return ($result);
    }
}
