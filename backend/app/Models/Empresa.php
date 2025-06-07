<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class Empresa extends Authenticatable
{
    use HasFactory, HasApiTokens;

    /**
     * The table associated with the model.
     */
    protected $table = 'empresas';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nome',
        'cnpj',
        'endereco',
        'email',
        'senha',
        'imagem',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'senha',
    ];

    /**
     * Get the members associated with the company.
     */
    public function membros(): HasMany
    {
        return $this->hasMany(Membro::class, 'empresa_id');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($empresa) {
            if (!empty($empresa->senha)) {
                $empresa->senha = bcrypt($empresa->senha);
            }
        });

        static::updating(function ($empresa) {
            if ($empresa->isDirty('senha') && !empty($empresa->senha)) {
                $empresa->senha = bcrypt($empresa->senha);
            }
        });
    }

    /**
     * Get formatted CNPJ
     */
    public function getCnpjFormatted(): string
    {
        if (!$this->cnpj) {
            return '';
        }
        
        $cnpjNumbers = preg_replace('/\D/', '', $this->cnpj);
        
        if (strlen($cnpjNumbers) === 14) {
            return substr($cnpjNumbers, 0, 2) . '.' . 
                   substr($cnpjNumbers, 2, 3) . '.' . 
                   substr($cnpjNumbers, 5, 3) . '/' . 
                   substr($cnpjNumbers, 8, 4) . '-' . 
                   substr($cnpjNumbers, 12, 2);
        }
        
        return $this->cnpj;
    }
}
