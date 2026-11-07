<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Named-route / authorization contract; no Tahap 2,4,6 schema assumed. */
class Stage7MergeContractTest extends TestCase
{
    public function test_required_routes_keep_names_after_module_merges(): void
    {
        foreach ([
            'account.login', 'auth.login', 'auth.logout',
            'account.profile', 'partner.portal', 'admin.dashboard',
            'db-catalog.index', 'db-catalog.show',
            'admin.db-categories.index', 'partner.db-services.index',
            'db-feedback.reviews.public', 'db-feedback.reviews.store',
            'db-feedback.complaints.mine', 'partner.db-feedback.reviews.index',
            'admin.db-feedback.reviews.index', 'admin.db-feedback.complaints.index',
        ] as $routeName) {
            $this->assertTrue(Route::has($routeName), "Route wajib hilang: {$routeName}");
        }
    }

    public function test_mutation_routes_have_auth_and_explicit_role_middleware(): void
    {
        foreach ([
            'admin.db-categories.store'=>'role:admin',
            'admin.db-feedback.reviews.moderate'=>'role:admin',
            'admin.db-feedback.complaints.update'=>'role:admin',
            'partner.db-services.store'=>'role:partner',
            'partner.db-feedback.reviews.reply'=>'role:partner',
            'db-feedback.reviews.store'=>'role:citizen',
            'db-feedback.complaints.store'=>'role:citizen',
        ] as $routeName=>$role) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, "Route tidak ada: {$routeName}");
            $middleware = $route->gatherMiddleware();
            $this->assertContains('auth', $middleware, "Route {$routeName} wajib auth");
            $this->assertContains($role, $middleware, "Route {$routeName} wajib {$role}");
        }
    }

    public function test_public_routes_do_not_require_authentication(): void
    {
        foreach (['db-catalog.index', 'db-feedback.reviews.public'] as $name) {
            $this->assertNotContains('auth', Route::getRoutes()->getByName($name)->gatherMiddleware());
        }
    }
}
