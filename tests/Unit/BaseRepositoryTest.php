<?php

namespace Laravel\Repository\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Laravel\Repository\Eloquent\BaseRepository;
use Laravel\Repository\Exceptions\RepositoryException;
use Laravel\Repository\Tests\TestCase;

// ── Fakes ─────────────────────────────────────────────────────────────────

class PqUser extends Model
{
    protected $table = 'pq_users';
    protected $fillable = ['name', 'email', 'active'];
    public $timestamps = false;
}

class PqUserRepository extends BaseRepository
{
    protected array $fieldSearchable = ['name' => 'like', 'email' => '='];

    public function model(): string
    {
        return PqUser::class;
    }
}

// ── Tests ──────────────────────────────────────────────────────────────────

class BaseRepositoryTest extends TestCase
{
    private PqUserRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('pq_users', function ($t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->boolean('active')->default(true);
        });

        $this->repo = new PqUserRepository($this->app);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('pq_users');
        parent::tearDown();
    }

    // ── model() validation ─────────────────────────────────────────────────

    public function test_invalid_model_class_throws_repository_exception(): void
    {
        $this->expectException(RepositoryException::class);

        new class ($this->app) extends BaseRepository {
            public function model(): string { return \stdClass::class; }
        };
    }

    // ── all() ──────────────────────────────────────────────────────────────

    public function test_all_returns_empty_collection_when_no_records(): void
    {
        $this->assertCount(0, $this->repo->all());
    }

    public function test_all_returns_all_records(): void
    {
        PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com']);
        PqUser::create(['name' => 'Bob', 'email' => 'bob@example.com']);

        $this->assertCount(2, $this->repo->all());
    }

    // ── create() ──────────────────────────────────────────────────────────

    public function test_create_persists_and_returns_model(): void
    {
        $user = $this->repo->create(['name' => 'Alice', 'email' => 'alice@example.com']);

        $this->assertInstanceOf(PqUser::class, $user);
        $this->assertSame('Alice', $user->name);
        $this->assertDatabaseHas('pq_users', ['email' => 'alice@example.com']);
    }

    // ── find() ────────────────────────────────────────────────────────────

    public function test_find_returns_correct_model(): void
    {
        $created = PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com']);

        $found = $this->repo->find($created->id);

        $this->assertSame($created->id, $found->id);
        $this->assertSame('Alice', $found->name);
    }

    public function test_find_throws_when_record_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repo->find(999);
    }

    // ── findByField() ──────────────────────────────────────────────────────

    public function test_find_by_field_returns_matching_records(): void
    {
        PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com']);
        PqUser::create(['name' => 'Bob', 'email' => 'bob@example.com']);

        $results = $this->repo->findByField('name', 'Alice');

        $this->assertCount(1, $results);
        $this->assertSame('Alice', $results->first()->name);
    }

    // ── findWhere() ────────────────────────────────────────────────────────

    public function test_find_where_filters_correctly(): void
    {
        PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com', 'active' => true]);
        PqUser::create(['name' => 'Bob', 'email' => 'bob@example.com', 'active' => false]);

        $results = $this->repo->findWhere(['active' => true]);

        $this->assertCount(1, $results);
        $this->assertSame('Alice', $results->first()->name);
    }

    // ── findWhereIn() ─────────────────────────────────────────────────────

    public function test_find_where_in_returns_matching_records(): void
    {
        $a = PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com']);
        PqUser::create(['name' => 'Bob', 'email' => 'bob@example.com']);
        $c = PqUser::create(['name' => 'Carol', 'email' => 'carol@example.com']);

        $results = $this->repo->findWhereIn('id', [$a->id, $c->id]);

        $this->assertCount(2, $results);
    }

    // ── findWhereNotIn() ──────────────────────────────────────────────────

    public function test_find_where_not_in_excludes_matching_records(): void
    {
        $a = PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com']);
        PqUser::create(['name' => 'Bob', 'email' => 'bob@example.com']);

        $results = $this->repo->findWhereNotIn('id', [$a->id]);

        $this->assertCount(1, $results);
        $this->assertSame('Bob', $results->first()->name);
    }

    // ── update() ──────────────────────────────────────────────────────────

    public function test_update_modifies_and_returns_model(): void
    {
        $user = PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com']);

        $updated = $this->repo->update(['name' => 'Alicia'], $user->id);

        $this->assertSame('Alicia', $updated->name);
        $this->assertDatabaseHas('pq_users', ['id' => $user->id, 'name' => 'Alicia']);
    }

    // ── updateOrCreate() ──────────────────────────────────────────────────

    public function test_update_or_create_creates_when_not_found(): void
    {
        $user = $this->repo->updateOrCreate(
            ['email' => 'new@example.com'],
            ['name' => 'New User']
        );

        $this->assertInstanceOf(PqUser::class, $user);
        $this->assertDatabaseHas('pq_users', ['email' => 'new@example.com']);
    }

    public function test_update_or_create_updates_when_found(): void
    {
        PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com']);

        $user = $this->repo->updateOrCreate(
            ['email' => 'alice@example.com'],
            ['name' => 'Alicia']
        );

        $this->assertSame('Alicia', $user->name);
        $this->assertSame(1, PqUser::count());
    }

    // ── delete() ──────────────────────────────────────────────────────────

    public function test_delete_removes_record(): void
    {
        $user = PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com']);

        $this->repo->delete($user->id);

        $this->assertDatabaseMissing('pq_users', ['id' => $user->id]);
    }

    // ── deleteWhere() ─────────────────────────────────────────────────────

    public function test_delete_where_removes_matching_records(): void
    {
        PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com', 'active' => false]);
        PqUser::create(['name' => 'Bob', 'email' => 'bob@example.com', 'active' => true]);

        $this->repo->deleteWhere(['active' => false]);

        $this->assertSame(1, PqUser::count());
        $this->assertDatabaseHas('pq_users', ['name' => 'Bob']);
    }

    // ── pluck() / lists() ─────────────────────────────────────────────────

    public function test_pluck_returns_collection_of_values(): void
    {
        PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com']);
        PqUser::create(['name' => 'Bob', 'email' => 'bob@example.com']);

        $names = $this->repo->pluck('name');

        $this->assertContains('Alice', $names);
        $this->assertContains('Bob', $names);
    }

    public function test_lists_is_alias_for_pluck(): void
    {
        PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com']);

        $this->assertEquals(
            $this->repo->pluck('name'),
            $this->repo->lists('name')
        );
    }

    // ── paginate() ────────────────────────────────────────────────────────

    public function test_paginate_returns_paginator_with_correct_count(): void
    {
        foreach (range(1, 20) as $i) {
            PqUser::create(['name' => "User $i", 'email' => "user{$i}@example.com"]);
        }

        $paginator = $this->repo->paginate(10);

        $this->assertSame(10, $paginator->perPage());
        $this->assertSame(20, $paginator->total());
        $this->assertCount(10, $paginator->items());
    }

    // ── scopeQuery() ──────────────────────────────────────────────────────

    public function test_scope_query_filters_results(): void
    {
        PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com', 'active' => true]);
        PqUser::create(['name' => 'Bob', 'email' => 'bob@example.com', 'active' => false]);

        $results = $this->repo->scopeQuery(fn ($q) => $q->where('active', true))->all();

        $this->assertCount(1, $results);
        $this->assertSame('Alice', $results->first()->name);
    }

    // ── orderBy() ─────────────────────────────────────────────────────────

    public function test_order_by_sorts_results(): void
    {
        PqUser::create(['name' => 'Bob', 'email' => 'bob@example.com']);
        PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com']);

        $results = $this->repo->orderBy('name', 'asc')->all();

        $this->assertSame('Alice', $results->first()->name);
        $this->assertSame('Bob', $results->last()->name);
    }

    // ── firstOrNew() / firstOrCreate() ────────────────────────────────────

    public function test_first_or_new_does_not_persist(): void
    {
        $user = $this->repo->firstOrNew(['email' => 'new@example.com', 'name' => 'New']);

        $this->assertFalse($user->exists);
        $this->assertDatabaseMissing('pq_users', ['email' => 'new@example.com']);
    }

    public function test_first_or_create_persists_when_not_found(): void
    {
        $user = $this->repo->firstOrCreate(['email' => 'new@example.com', 'name' => 'New']);

        $this->assertTrue($user->exists);
        $this->assertDatabaseHas('pq_users', ['email' => 'new@example.com']);
    }

    // ── getFieldsSearchable() ─────────────────────────────────────────────

    public function test_get_fields_searchable_returns_configured_fields(): void
    {
        $this->assertSame(['name' => 'like', 'email' => '='], $this->repo->getFieldsSearchable());
    }

    // ── Criteria ──────────────────────────────────────────────────────────

    public function test_push_and_apply_criteria_filters_results(): void
    {
        PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com', 'active' => true]);
        PqUser::create(['name' => 'Bob', 'email' => 'bob@example.com', 'active' => false]);

        $criteria = new class implements \Laravel\Repository\Contracts\CriteriaInterface {
            public function apply(mixed $model, \Laravel\Repository\Contracts\RepositoryInterface $repo): mixed
            {
                return $model->where('active', true);
            }
        };

        $results = $this->repo->pushCriteria($criteria)->all();

        $this->assertCount(1, $results);
        $this->assertSame('Alice', $results->first()->name);
    }

    public function test_push_invalid_criteria_throws_exception(): void
    {
        $this->expectException(RepositoryException::class);

        $this->repo->pushCriteria(new \stdClass());
    }

    public function test_skip_criteria_bypasses_criteria(): void
    {
        PqUser::create(['name' => 'Alice', 'email' => 'alice@example.com', 'active' => true]);
        PqUser::create(['name' => 'Bob', 'email' => 'bob@example.com', 'active' => false]);

        $criteria = new class implements \Laravel\Repository\Contracts\CriteriaInterface {
            public function apply(mixed $model, \Laravel\Repository\Contracts\RepositoryInterface $repo): mixed
            {
                return $model->where('active', true);
            }
        };

        $results = $this->repo->pushCriteria($criteria)->skipCriteria(true)->all();

        $this->assertCount(2, $results);
    }

    public function test_pop_criteria_removes_specific_criteria(): void
    {
        $criteria = new class implements \Laravel\Repository\Contracts\CriteriaInterface {
            public function apply(mixed $model, \Laravel\Repository\Contracts\RepositoryInterface $repo): mixed
            {
                return $model->where('active', true);
            }
        };

        $this->repo->pushCriteria($criteria);
        $this->assertCount(1, $this->repo->getCriteria());

        $this->repo->popCriteria($criteria);
        $this->assertCount(0, $this->repo->getCriteria());
    }
}
