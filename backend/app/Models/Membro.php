<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Membro extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The table associated with the model.
     */
    protected $table = 'membros';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nome',
        'data_nascimento',
        'telefone',
        'cpf',
        'email',
        'senha',
        'empresa_id',
        'imagem',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'senha',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'ultimo_login' => 'datetime',
            'email_verified_at' => 'datetime',
            'esta_logado' => 'boolean',
        ];
    }

    /**
     * Get the password attribute name for authentication.
     */
    public function getAuthPassword()
    {
        return $this->senha;
    }

    /**
     * Get the company associated with the member.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($membro) {
            if (!empty($membro->senha)) {
                $membro->senha = bcrypt($membro->senha);
            }
        });

        static::updating(function ($membro) {
            if ($membro->isDirty('senha') && !empty($membro->senha)) {
                $membro->senha = bcrypt($membro->senha);
            }
        });
    }

    /**
     * Get formatted CPF
     */
    public function getCpfFormatted(): string
    {
        if (!$this->cpf) {
            return '';
        }
        
        $cpfNumbers = preg_replace('/\D/', '', $this->cpf);
        
        if (strlen($cpfNumbers) === 11) {
            return substr($cpfNumbers, 0, 3) . '.' . 
                   substr($cpfNumbers, 3, 3) . '.' . 
                   substr($cpfNumbers, 6, 3) . '-' . 
                   substr($cpfNumbers, 9, 2);
        }
        
        return $this->cpf;
    }

    /**
     * Get data_nascimento attribute accessor for the DTO
     */
    public function getDataNascimentoAttribute($value)
    {
        return $value;
    }
}
