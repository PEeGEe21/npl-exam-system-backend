<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CbtQuestionTest extends Model
{
    protected $guarded = ['id'];

    public function scopeExists($query,$data){
        $topic=$query->whereRaw('subject_id= ? and school_id= ? and  session_id= ? and lower(name)= ?'
            ,[$data['subject_id'],$data['school_id'],$data['session_id'],strtolower($data['name'])])->first();
        return $topic;
    }

    public function test()
    {
        return $this->hasOne(CbtTest::class, 'id', 'test_id');
    }

    public function questiontemp()
    {
        return $this->hasOne(CbtQuestion::class, 'id', 'question_id');
    
    }

    public function ScopeSubjectQuestions($query, $data)
    {
        $query->where('subject_id', $data['subject_id']);
        $query->where('test_id', $data['test_id']);
        return $query->get();
    }

    public function ScopeOtherQuestionsCount($query, $data)
    {
        $query->where('subject_id', $data['subject_id']);
        $query->where('test_id', $data['test_id']);
        $query->whereHas('question', function ($q) use ($data) {
            $q->where('user_id', '!=',$data['user_id']);
        });

        return $query->count();
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


    public function subQuestion()
    {
        return $this->hasMany(CbtSubQuestion::class, 'question_id','question_id');
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
//        dd ($result->get());
        return ($result);
    }



    public function parseWhere($query,&$str,$is_nested = false){
        $tempstr = '';
        foreach ($query->wheres as $index => $where){
//            dump($where['type']);
            if($where['type'] == 'Nested'){
                $this->parseWhere($where['query'],$str,true);
            }
            else if ($where['type'] == 'Basic'){
                if(!empty($str)) {
                    if($index != 0)
                    $tempstr .= ' ' . $where['boolean'] . ' ';
                    else {
                        $str .= ' ' . $where['boolean'] . ' ';
                        if($is_nested)
                            $str .= '(';
                        else
                            $tempstr = '(';
                    }
                }
                else{
                    if($index == 0)
                        $tempstr = '(';
                }

                $tempstr .= $where['column']. ' '.$where['operator'].' '.$where['value'];
            }
            else if ($where['type'] == 'NotNull'){
                if(!empty($str)) {
                    if($index != 0)
                        $tempstr .= ' ' . $where['boolean'] . ' ';
                    else {
                        $str .= ' ' . $where['boolean'] . ' ';
                        if($is_nested)
                            $str .= '(';
                        else
                            $tempstr = '(';
                    }
                }
                else{
                    if($index == 0)
                        $tempstr = '(';
                }
                $tempstr .= $where['column']. ' is not null ';
            }
            else if ($where['type'] == 'Null'){
                if(!empty($str)) {
                    if($index != 0)
                        $tempstr .= ' ' . $where['boolean'] . ' ';
                    else {
                        $str .= ' ' . $where['boolean'] . ' ';
                        if($is_nested)
                            $str .= '(';
                        else
                            $tempstr = '(';
                    }
                }
                else{
                    if($index == 0)
                        $tempstr = '(';
                }
                $tempstr .= $where['column']. ' is null ';
            }
            else if ($where['type'] == 'In'){
                if(!empty($str)) {
//                    if(empty($tempstr))
//                        $tempstr .= '(';
                    if($index != 0)
                        $tempstr .= ' ' . $where['boolean'] . ' ';
                    else {
                        $str .= ' ' . $where['boolean'] . ' ';
                        if($is_nested)
                            $str .= '(';
                        else
                            $tempstr = '(';
                    }
                }
                else{
                    if($index == 0)
                        $tempstr = '(';
                }
                $tempstr .= $where['column']. ' '.$where['type'].' ('.implode($where['values'],',').')';
//               dump($where);
            }
            else{
//                dump($where);
            }
        }
        $str .= $tempstr;
        $str .= $is_nested?')':' ';
        return $str;
    }
    public function scopeNPerGroup($query, $group, $n = 10)
    {
        // queried table
        $table = ($this->getTable());

        $variables = '*';
            // if no columns already selected, let's select *
            if ( ! $query->getQuery()->columns)
            {
                $query->select("t1.*");
            }
            else{
                $variables = implode($query->getQuery()->columns,',');
            }
            $strWhere = '';
             $this->parseWhere($query->getQuery(),$strWhere,false);
//            dd($strWhere);

        // initialize MySQL variables inline
        $query->from( DB::raw("(SELECT @rank:=0, @group:=0) as vars, (select {$variables},RAND() as trand from {$table} where {$strWhere}) as t1") );



        // make sure column aliases are unique
        $groupAlias = 'group_'.md5(time());
        $rankAlias  = 'rank_'.md5(time());

        // apply mysql variables
        $query->addSelect(DB::raw(
            "@rank := IF(@group = {$group}, @rank+1, 1) as {$rankAlias}, @group := {$group} as {$groupAlias}"
        ));


        // make sure first order clause is the group order
        $query->getQuery()->orders = (array) $query->getQuery()->orders;
        array_unshift($query->getQuery()->orders, ['column' => 'trand', 'direction' => '']);
        array_unshift($query->getQuery()->orders, ['column' => $group, 'direction' => 'asc']);

        // prepare subquery
        $subQuery = $query->toSql();

        // prepare new main base Query\Builder
        $newBase = $this->newQuery()
            ->from(DB::raw("({$subQuery}) as {$table}"))
            ->mergeBindings($query->getQuery())
            ->where($rankAlias, '<=', $n)
            ->select(DB::raw(
               "{$variables}"
            ))
            ->getQuery();

        // replace underlying builder to get rid of previous clauses
        $query->setQuery($newBase);
    }

}
