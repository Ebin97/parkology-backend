<?php

namespace App\Providers;

use App\Services\Facades\FAnswer;
use App\Services\Facades\FBase;
use App\Services\Facades\FCity;
use App\Services\Facades\FNotification;
use App\Services\Facades\FPharmacy;
use App\Services\Facades\FProduct;
use App\Services\Facades\FQuiz;
use App\Services\Facades\FRedeem;
use App\Services\Facades\FSale;
use App\Services\Facades\FSetting;
use App\Services\Facades\FSpeciality;
use App\Services\Facades\FTheme;
use App\Services\Facades\FThemeLevel;
use App\Services\Facades\FType;
use App\Services\Facades\FUser;
use App\Services\Facades\FUserQuiz;
use App\Services\Interfaces\IAnswer;
use App\Services\Interfaces\IBase;
use App\Services\Interfaces\ICity;
use App\Services\Interfaces\INotification;
use App\Services\Interfaces\IPharmacy;
use App\Services\Interfaces\IProduct;
use App\Services\Interfaces\IQuiz;
use App\Services\Interfaces\IRedeem;
use App\Services\Interfaces\ISale;
use App\Services\Interfaces\ISetting;
use App\Services\Interfaces\ISpeciality;
use App\Services\Interfaces\ITheme;
use App\Services\Interfaces\IThemeLevel;
use App\Services\Interfaces\IType;
use App\Services\Interfaces\IUser;
use App\Services\Interfaces\IUserQuiz;
use Illuminate\Support\ServiceProvider;

class FacadeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(IBase::class, FBase::class);

        $this->app->singleton(IUser::class, FUser::class);
        $this->app->singleton(ICity::class, FCity::class);
        $this->app->singleton(IPharmacy::class, FPharmacy::class);
        $this->app->singleton(ITheme::class, FTheme::class);
        $this->app->singleton(IThemeLevel::class, FThemeLevel::class);

        $this->app->singleton(ISpeciality::class, FSpeciality::class);

        $this->app->singleton(IProduct::class, FProduct::class);
        $this->app->singleton(INotification::class, FNotification::class);

        $this->app->singleton(ISetting::class, FSetting::class);

        $this->app->singleton(IType::class, FType::class);

        $this->app->singleton(IQuiz::class, FQuiz::class);

        $this->app->singleton(IAnswer::class, FAnswer::class);

        $this->app->singleton(IUserQuiz::class, FUserQuiz::class);

        $this->app->singleton(IRedeem::class, FRedeem::class);

        $this->app->singleton(ISale::class, FSale::class);

    }
}
