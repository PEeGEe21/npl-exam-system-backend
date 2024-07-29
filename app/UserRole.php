<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $fillable = ['role_id','user_id','default'];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function role(){
        return $this->belongsTo(Role::class,'role_id');
    }
    public function ScopeDefaultRole($query,$user_id){
        return $query->whereRaw('user_id=? AND `default`=?',[$user_id,1])->first();
    }

    public function ScopeByFilter($query,$data){
        $whereClause=[];
        $whereParam=[];
        foreach($data as $k=>$v){
            $whereClause[]=$k.'=?';
            $whereParam[]=$v;
        }
        $whereClause=implode(' and ',$whereClause);
        $result= $query->whereRaw($whereClause,$whereParam);
        return ($result);
    }
}
