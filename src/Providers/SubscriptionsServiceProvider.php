<?php

declare(strict_types=1);

namespace Rinvex\Subscriptions\Providers;

use Rinvex\Subscriptions\Models\Plan;
use Illuminate\Support\ServiceProvider;
use Rinvex\Support\Traits\ConsoleTools;
use Rinvex\Subscriptions\Models\PlanFeature;
use Rinvex\Subscriptions\Models\PlanSubscription;
use Rinvex\Subscriptions\Models\PlanSubscriptionUsage;
use Rinvex\Subscriptions\Console\Commands\MigrateCommand;
use Rinvex\Subscriptions\Console\Commands\PublishCommand;
use Rinvex\Subscriptions\Console\Commands\RollbackCommand;

class SubscriptionsServiceProvider extends ServiceProvider
{
    use ConsoleTools;

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'rinvex.subscriptions');

        // Bind eloquent models to IoC container
        $this->app->bind('rinvex.subscriptions.plan', Plan::class);
        $this->app->bind('rinvex.subscriptions.plan_feature', PlanFeature::class);
        $this->app->bind('rinvex.subscriptions.plan_subscription', PlanSubscription::class);
        $this->app->bind('rinvex.subscriptions.plan_subscription_usage', PlanSubscriptionUsage::class);

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrateCommand::class,
                PublishCommand::class,
                RollbackCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish Resources
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('rinvex.subscriptions.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'rinvex/subscriptions::migrations');

        if ($this->autoloadMigrations('rinvex.subscriptions')) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        }
    }

    /**
     * Check if the autoload migrations option is enabled.
     *
     * @param string $package
     * @return bool
     */
    protected function autoloadMigrations(string $package): bool
    {
        return config("{$package}.autoload_migrations", true);
    }
}
