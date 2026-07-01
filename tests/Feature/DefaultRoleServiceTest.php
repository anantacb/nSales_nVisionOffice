<?php

namespace Tests\Feature;

use App\Models\Office\Role;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\Role\RoleRepositoryInterface;
use App\Services\DefaultRole\DefaultRoleServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class DefaultRoleServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** Run the transaction closure inline so no real DB connection is needed. */
    private function stubTransaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn (callable $callback) => $callback());
    }

    public function test_create_fans_the_new_default_role_out_to_companies_that_lack_it(): void
    {
        $this->stubTransaction();

        $template = new Role([
            'Name'        => 'Supervisor',
            'Type'        => 'Manager',
            'Description' => 'Oversees things',
        ]);

        $roleRepo = Mockery::mock(RoleRepositoryInterface::class);
        $companyRepo = Mockery::mock(CompanyRepositoryInterface::class);

        // Template row inserted with CompanyId = null.
        $roleRepo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn ($data) => $data['CompanyId'] === null && $data['Name'] === 'Supervisor'))
            ->andReturn($template);

        // Three active companies exist.
        $companyRepo->shouldReceive('getByAttributes')
            ->once()
            ->with([])
            ->andReturn(new Collection([(object) ['Id' => 1], (object) ['Id' => 2], (object) ['Id' => 3]]));

        // Company 1 already owns this role (plus the NULL template) -> only 2 and 3 are missing.
        $roleRepo->shouldReceive('getByAttributes')
            ->once()
            ->andReturn(new Collection([
                (object) ['CompanyId' => null],
                (object) ['CompanyId' => 1],
            ]));

        // Expect a single bulk insert for companies 2 and 3 only.
        $roleRepo->shouldReceive('insert')
            ->once()
            ->with(Mockery::on(function ($rows) {
                $companyIds = array_column($rows, 'CompanyId');
                return $companyIds === [2, 3]
                    && $rows[0]['Name'] === 'Supervisor'
                    && $rows[0]['Type'] === 'Manager'
                    && isset($rows[0]['InsertTime'], $rows[0]['UpdateTime']);
            }));

        $this->app->instance(RoleRepositoryInterface::class, $roleRepo);
        $this->app->instance(CompanyRepositoryInterface::class, $companyRepo);

        $service = $this->app->make(DefaultRoleServiceInterface::class);

        $request = new Request([
            'Name'        => 'Supervisor',
            'Type'        => 'Manager',
            'Description' => 'Oversees things',
        ]);

        $dto = $service->create($request);

        $this->assertSame(200, $dto->statusCode);
        $this->assertStringContainsString('added to 2 company(ies)', $dto->message);
    }

    public function test_create_inserts_nothing_when_every_company_already_has_the_role(): void
    {
        $this->stubTransaction();

        $template = new Role([
            'Name'        => 'Supervisor',
            'Type'        => 'Manager',
            'Description' => null,
        ]);

        $roleRepo = Mockery::mock(RoleRepositoryInterface::class);
        $companyRepo = Mockery::mock(CompanyRepositoryInterface::class);

        $roleRepo->shouldReceive('create')->once()->andReturn($template);

        $companyRepo->shouldReceive('getByAttributes')->once()->with([])
            ->andReturn(new Collection([(object) ['Id' => 1], (object) ['Id' => 2]]));

        $roleRepo->shouldReceive('getByAttributes')->once()
            ->andReturn(new Collection([
                (object) ['CompanyId' => null],
                (object) ['CompanyId' => 1],
                (object) ['CompanyId' => 2],
            ]));

        // No company is missing the role -> insert must never be called.
        $roleRepo->shouldNotReceive('insert');

        $this->app->instance(RoleRepositoryInterface::class, $roleRepo);
        $this->app->instance(CompanyRepositoryInterface::class, $companyRepo);

        $service = $this->app->make(DefaultRoleServiceInterface::class);

        $dto = $service->create(new Request(['Name' => 'Supervisor', 'Type' => 'Manager']));

        $this->assertSame(200, $dto->statusCode);
        $this->assertStringContainsString('added to 0 company(ies)', $dto->message);
    }
}
