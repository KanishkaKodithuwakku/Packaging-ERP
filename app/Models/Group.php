<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $table = 'groups';
    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'affects_gross',
    ];

    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }

    public function children_groups()
    {
        return $this->hasMany(Group::class, 'parent_id')->with(['children_groups', 'ledgers']);
    }
}
