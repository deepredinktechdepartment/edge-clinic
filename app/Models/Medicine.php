<?php

// app/Models/Medicine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id', 'name', 'price_inr', 'is_discontinued',
        'manufacturer_name', 'type', 'pack_size_label',
        'short_composition1', 'short_composition2',
        'substitute0', 'substitute1', 'substitute2', 'substitute3', 'substitute4',
        'consolidated_side_effects',
        'use0', 'use1', 'use2', 'use3', 'use4',
        'chemical_class', 'habit_forming', 'therapeutic_class', 'action_class',
    ];

    protected $casts = [
        'price_inr'       => 'decimal:2',
        'is_discontinued' => 'boolean',
        'habit_forming'   => 'boolean',
    ];
}