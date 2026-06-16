<?php

namespace Laravel\Repository\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Laravel\Repository\Criteria\RequestCriteria;
use Laravel\Repository\Tests\TestCase;

class RequestCriteriaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('pq_products', function ($t) {
            $t->id();
            $t->string('name');
            $t->string('sku')->unique();
            $t->decimal('price', 8, 2)->default(0);
            $t->boolean('available')->default(true);
        });

        PqProduct::create(['name' => 'Apple', 'sku' => 'APL', 'price' => 1.50, 'available' => true]);
        PqProduct::create(['name' => 'Banana', 'sku' => 'BAN', 'price' => 0.80, 'available' => true]);
        PqProduct::create(['name' => 'Cherry', 'sku' => 'CHR', 'price' => 3.00, 'available' => false]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('pq_products');
        parent::tearDown();
    }

    private function repoWithRequest(array $params): PqProductRepository
    {
        $request = Request::create('/', 'GET', $params);
        $repo    = new PqProductRepository($this->app);
        $repo->pushCriteria(new RequestCriteria($request));

        return $repo;
    }

    // ── search ────────────────────────────────────────────────────────────

    public function test_search_filters_by_like_field(): void
    {
        $results = $this->repoWithRequest(['search' => 'Ap'])->all();

        $this->assertCount(1, $results);
        $this->assertSame('Apple', $results->first()->name);
    }

    public function test_search_with_field_value_syntax(): void
    {
        $results = $this->repoWithRequest(['search' => 'sku:BAN'])->all();

        $this->assertCount(1, $results);
        $this->assertSame('Banana', $results->first()->name);
    }

    public function test_no_search_returns_all_records(): void
    {
        $results = $this->repoWithRequest([])->all();

        $this->assertCount(3, $results);
    }

    // ── orderBy / sortedBy ────────────────────────────────────────────────

    public function test_order_by_asc(): void
    {
        $results = $this->repoWithRequest(['orderBy' => 'name', 'sortedBy' => 'asc'])->all();

        $this->assertSame('Apple', $results->first()->name);
        $this->assertSame('Cherry', $results->last()->name);
    }

    public function test_order_by_desc(): void
    {
        $results = $this->repoWithRequest(['orderBy' => 'name', 'sortedBy' => 'desc'])->all();

        $this->assertSame('Cherry', $results->first()->name);
        $this->assertSame('Apple', $results->last()->name);
    }

    // ── filter (column selection) ─────────────────────────────────────────

    public function test_filter_limits_columns_returned(): void
    {
        $results = $this->repoWithRequest(['filter' => 'id;name'])->all();

        $first = $results->first()->toArray();
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayNotHasKey('price', $first);
    }

    // ── with (eager loading) ──────────────────────────────────────────────

    public function test_invalid_search_join_defaults_to_or(): void
    {
        $results = $this->repoWithRequest([
            'search'     => 'Apple',
            'searchJoin' => 'invalid',
        ])->all();

        $this->assertGreaterThanOrEqual(1, $results->count());
    }

    public function test_search_join_and_requires_all_conditions(): void
    {
        $results = $this->repoWithRequest([
            'search'     => 'name:Apple;sku:BAN',
            'searchJoin' => 'and',
        ])->all();

        $this->assertCount(0, $results);
    }
}

// ── Fakes ─────────────────────────────────────────────────────────────────

class PqProduct extends \Illuminate\Database\Eloquent\Model
{
    protected $table    = 'pq_products';
    protected $fillable = ['name', 'sku', 'price', 'available'];
    public $timestamps  = false;

    protected $casts = ['available' => 'boolean', 'price' => 'float'];
}

class PqProductRepository extends \Laravel\Repository\Eloquent\BaseRepository
{
    protected array $fieldSearchable = ['name' => 'like', 'sku' => '='];

    public function model(): string
    {
        return PqProduct::class;
    }
}
