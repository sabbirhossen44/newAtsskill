<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'current_amount' => 'decimal:2',
            'withdraw_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select('id', 'name', 'email', 'image');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(WithdrawMethod::class, 'method', 'name');
    }

}
