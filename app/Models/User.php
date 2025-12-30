<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Import untuk Filament Multi-Tenancy
use Filament\Models\Contracts\FilamentUser; // <--- 1. TAMBAH INI
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// 👇 2. TAMBAHKAN 'FilamentUser' DI SINI
class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        // 'store_id' <--- 3. SUDAH DIHAPUS (Karena sekarang pakai tabel store_user)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi Many-to-Many (User Staff/Owner bisa masuk banyak toko)
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class);
    }

    // Relasi One-to-Many (Khusus Owner, toko yang dia BUAT/MILIKI)
    public function myStores(): HasMany
    {
        return $this->hasMany(Store::class, 'user_id');
    }

    // Logika Akses Panel Admin
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'owner';
    }

    // Logika Multi-Tenancy: Ambil daftar toko user ini
    public function getTenants(Panel $panel): Collection
    {
        return $this->stores;
    }

    // Logika Multi-Tenancy: Cek izin akses ke toko tertentu
    public function canAccessTenant(Model $tenant): bool
    {
        return $this->stores->contains($tenant);
    }
}
