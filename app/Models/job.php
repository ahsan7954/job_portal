<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class job extends Model
{
    use HasFactory;
    public function jobType(){
         return $this->belongsTo(jobType::class);
    }
    public function category(){
        return $this->belongsTo(category::class);
   }
   public function applications(){
        return $this->hasMany(jobApplication::class);
   }
   public function user(){
        return $this->belongsTo(User::class);
   }
}