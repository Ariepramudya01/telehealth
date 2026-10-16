<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['skrining_id', 'pertanyaan_spr_id', 'jawaban', 'skor'])]
class SkriningDetail extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jawaban' => 'boolean',
        ];
    }

    /**
     * Get the skrining this detail belongs to.
     */
    public function skrining(): BelongsTo
    {
        return $this->belongsTo(Skrining::class);
    }

    /**
     * Get the pertanyaan SPR for this detail.
     */
    public function pertanyaanSpr(): BelongsTo
    {
        return $this->belongsTo(PertanyaanSpr::class);
    }
}
