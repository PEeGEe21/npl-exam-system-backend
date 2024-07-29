<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CbtTest extends Model
{

    public function difficulty()
    {
        return $this->hasOne(CbtDifficulty::class, 'id', 'difficulty_id');
    }

    public function optionType()
    {
        return $this->hasOne(CbtOptionType::class, 'id', 'option_type_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function questions()
    {
        return $this->hasMany(CbtQuestionTest::class, 'test_id');
    }

    public function results()
    {
        return $this->hasMany(CbtResult::class, 'test_id');
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
        return ($result);
    }
}
