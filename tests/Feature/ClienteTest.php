<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    private User    $user;
    private Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->empresa] = $this->loginConEmpresa();
    }

    private function sessionEmpresaActual(): array
    {
        return $this->sessionEmpresa($this->empresa);
    }

    // ── INDEX ──────────────────────────────────────────────────

    public function test_index_devuelve_listado(): void
    {
        Cliente::factory()->count(5)->create(['empresa_id' => $this->empresa->id]);

        $response = $this->withSession($this->sessionEmpresaActual())
                         ->get(route('clientes.index'));

        $response->assertOk();
        $response->assertViewIs('clientes.index');
    }

    public function test_index_requiere_autenticacion(): void
    {
        auth()->logout();

        $response = $this->get(route('clientes.index'));

        $response->assertRedirect(route('login'));
    }

    // ── CREATE ─────────────────────────────────────────────────

    public function test_create_muestra_formulario(): void
    {
        $response = $this->withSession($this->sessionEmpresaActual())
                         ->get(route('clientes.create'));

        $response->assertOk();
        $response->assertViewIs('clientes.create');
    }

    // ── STORE ──────────────────────────────────────────────────

    public function test_store_crea_cliente_persona_natural(): void
    {
        $response = $this->withSession($this->sessionEmpresaActual())
                         ->post(route('clientes.store'), [
                             'tipo_persona'     => 'natural',
                             'tipo_documento'   => 'CC',
                             'numero_documento' => '1234567890',
                             'nombres'          => 'Juan',
                             'apellidos'        => 'Pérez',
                             'regimen'          => 'simple',
                             'email'            => 'juan@example.com',
                             'celular'          => '3001234567',
                             'municipio'        => 'Bogotá',
                             'departamento'     => 'Cundinamarca',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clientes', [
            'numero_documento' => '1234567890',
            'empresa_id'       => $this->empresa->id,
        ]);
    }

    public function test_store_crea_cliente_persona_juridica(): void
    {
        $response = $this->withSession($this->sessionEmpresaActual())
                         ->post(route('clientes.store'), [
                             'tipo_persona'        => 'juridica',
                             'tipo_documento'      => 'NIT',
                             'numero_documento'    => '900123456',
                             'digito_verificacion' => '7',
                             'razon_social'        => 'Empresa XYZ SAS',
                             'regimen'             => 'responsable_iva',
                             'email'               => 'info@xyz.com',
                         ]);

        $response->assertRedirect();
        // El controlador aplica strtoupper condicionalmente; verificamos que el registro existe
        $this->assertDatabaseHas('clientes', [
            'numero_documento' => '900123456',
            'empresa_id'       => $this->empresa->id,
        ]);
    }

    public function test_store_falla_sin_numero_documento(): void
    {
        $response = $this->withSession($this->sessionEmpresaActual())
                         ->post(route('clientes.store'), [
                             'tipo_persona'   => 'natural',
                             'tipo_documento' => 'CC',
                             'nombres'        => 'Juan',
                             'apellidos'      => 'Pérez',
                         ]);

        $response->assertSessionHasErrors('numero_documento');
    }

    // ── SHOW / EDIT ────────────────────────────────────────────

    public function test_show_devuelve_vista(): void
    {
        $cliente = Cliente::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->withSession($this->sessionEmpresaActual())
                         ->get(route('clientes.show', $cliente));

        $response->assertOk();
    }

    public function test_edit_devuelve_formulario(): void
    {
        $cliente = Cliente::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->withSession($this->sessionEmpresaActual())
                         ->get(route('clientes.edit', $cliente));

        $response->assertOk();
        $response->assertViewIs('clientes.edit');
    }

    // ── DESTROY ────────────────────────────────────────────────

    public function test_destroy_elimina_cliente(): void
    {
        $cliente = Cliente::factory()->create(['empresa_id' => $this->empresa->id]);

        $this->withSession($this->sessionEmpresaActual())
             ->delete(route('clientes.destroy', $cliente));

        $this->assertSoftDeleted('clientes', ['id' => $cliente->id]);
    }

    // ── AISLAMIENTO MULTI-EMPRESA ──────────────────────────────

    public function test_no_ve_clientes_de_otra_empresa(): void
    {
        $otraEmpresa = Empresa::factory()->create();
        Cliente::factory()->count(3)->create(['empresa_id' => $otraEmpresa->id]);
        Cliente::factory()->count(2)->create(['empresa_id' => $this->empresa->id]);

        $response = $this->withSession($this->sessionEmpresaActual())
                         ->get(route('clientes.index'));

        // Solo debe ver los 2 clientes de su empresa
        $response->assertViewHas('clientes', function ($clientes) {
            return $clientes->total() === 2;
        });
    }
}
