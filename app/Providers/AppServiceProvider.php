<?php

namespace App\Providers;
use App\Models\Absensi;          
use App\Observers\AbsensiObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Siswa::class => \App\Policies\SiswaPolicy::class,
        \App\Models\Absensi::class => \App\Policies\AbsensiPolicy::class,
        \App\Models\Kelas::class => \App\Policies\KelasPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\IndikasiSiswa::class => \App\Policies\IndikasiSiswaPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Absensi::observe(AbsensiObserver::class);

        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
