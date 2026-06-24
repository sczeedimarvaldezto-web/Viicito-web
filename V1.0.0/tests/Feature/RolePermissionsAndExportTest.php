<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserRole;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RolePermissionsAndExportTest extends TestCase
{
    public function test_employee_role_is_denied_for_owner_only_access(): void
    {
        $user = new class {
            public $role;

            public function __construct()
            {
                $this->role = (object) ['name' => 'employee'];
            }
        };

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($user);

        $middleware = new EnsureUserRole();
        $request = Request::create('/api/productos/1', 'DELETE');

        $response = $middleware->handle($request, fn ($req) => response()->json(['ok' => true]), 'owner');

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Acceso denegado.', $response->getData(true)['message']);
    }

    public function test_sales_export_service_builds_csv_headers_and_filename(): void
    {
        $service = new ReportExportService();

        $this->assertSame([
            'Documento',
            'Fecha',
            'Subtotal',
            'IVA',
            'Total',
            'Método de Pago',
            'Estado',
        ], $service->salesHeaders());

        $this->assertStringContainsString('ventas', $service->filename('ventas'));
        $this->assertStringEndsWith('.csv', $service->filename('ventas'));
    }
}
