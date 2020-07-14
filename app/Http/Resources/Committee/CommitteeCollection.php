<?php

namespace App\Http\Resources\Committee;

use App\Committee;
use App\User;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\DB;

class CommitteeCollection extends Resource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function position($pos)
    {
        $volunteer = DB::table('users')->join('volunteers','users.id','=','volunteers.user_id')->join('vol_committees', function ($join) use ($pos) {
            $join->on('volunteers.id', '=', 'vol_committees.vol_id')
                 ->where('vol_committees.position', '=', $pos )->where('season_id',DB::table('seasons')->where('isActive',1)->value('id'))->where('vol_committees.committee_id',$this->id);
        })
        ->select('users.firstName' , 'users.lastName','volunteers.id')->get();
        return $volunteer;
    }
    public function toArray($request)
    {
        $numOfVolunteers = DB::table('vol_committees')->where('committee_id',$this->id)->where('vol_committees.position', '=', 'volunteer')->where('season_id',DB::table('seasons')->where('isActive',1)->value('id'))->get();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'chapter_id' =>$this->chapter_id,
            'mentor' => self::position('mentor'),
            'director' => self::position('director'),
            'hr_coordinator' => self::position('hr_coordinator'),
            'numOfVolunteers' => $numOfVolunteers->count(),
        ];
    }
}
