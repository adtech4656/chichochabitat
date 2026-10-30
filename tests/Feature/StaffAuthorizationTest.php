<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Models\Branch;
use App\Models\Department;
use App\Models\StaffProfile;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_head_can_only_query_and_view_staff_in_their_department(): void
    {
        $this->seed(AccessControlSeeder::class);

        [$branch, $otherBranch] = [
            Branch::create(['name' => 'Abidjan', 'code' => 'ABJ']),
            Branch::create(['name' => 'Bouake', 'code' => 'BKE']),
        ];
        $sales = Department::create(['branch_id' => $branch->id, 'name' => 'Sales', 'code' => 'SALES']);
        $legal = Department::create(['branch_id' => $branch->id, 'name' => 'Legal', 'code' => 'LEGAL']);
        $otherSales = Department::create(['branch_id' => $otherBranch->id, 'name' => 'Sales', 'code' => 'SALES']);

        $head = $this->staffUser('department_head', $branch, $sales);
        $colleague = $this->staffUser('agent', $branch, $sales);
        $outside = $this->staffUser('legal', $branch, $legal);
        $outsideProfile = $outside->staffProfile()->withoutGlobalScopes()->firstOrFail();
        $this->staffUser('agent', $otherBranch, $otherSales);

        $this->actingAs($head);

        $this->assertEqualsCanonicalizing(
            [$head->staffProfile->id, $colleague->staffProfile->id],
            StaffProfile::query()->pluck('id')->all(),
        );
        $this->assertFalse(Gate::forUser($head)->allows('view', $outsideProfile));
        $this->assertTrue(Gate::forUser($head)->allows('view', $colleague->staffProfile));
    }

    public function test_general_manager_with_manage_all_permission_can_query_all_branches(): void
    {
        $this->seed(AccessControlSeeder::class);

        $branch = Branch::create(['name' => 'Abidjan', 'code' => 'ABJ']);
        $otherBranch = Branch::create(['name' => 'Bouake', 'code' => 'BKE']);
        $firstDepartment = Department::create(['branch_id' => $branch->id, 'name' => 'Sales', 'code' => 'SALES']);
        $secondDepartment = Department::create(['branch_id' => $otherBranch->id, 'name' => 'Legal', 'code' => 'LEGAL']);

        $this->staffUser('agent', $branch, $firstDepartment);
        $this->staffUser('agent', $otherBranch, $secondDepartment);
        $manager = $this->staffUser('general_manager', $branch, $firstDepartment);

        $this->actingAs($manager);

        $this->assertTrue($manager->hasPermission('manage_all_departments'));
        $this->assertSame(3, StaffProfile::query()->count());
        $this->assertSame(2, Department::query()->visibleTo($manager)->count());
    }

    public function test_users_without_staff_access_cannot_query_staff_profiles(): void
    {
        $this->seed(AccessControlSeeder::class);

        $branch = Branch::create(['name' => 'Abidjan', 'code' => 'ABJ']);
        $department = Department::create(['branch_id' => $branch->id, 'name' => 'Sales', 'code' => 'SALES']);
        $this->staffUser('agent', $branch, $department);
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->actingAs($customer);

        $this->assertSame(0, StaffProfile::query()->count());
    }

    public function test_public_registration_cannot_assign_privileged_roles_or_permissions(): void
    {
        $this->seed(AccessControlSeeder::class);

        $this->post('/register', [
            'name' => 'Untrusted Signup',
            'email' => 'untrusted@example.com',
            'password' => 'Password123',
            'role' => 'admin',
            'permissions' => ['manage_all_departments', 'users.permissions'],
        ])->assertRedirect();

        $user = User::query()->where('email', 'untrusted@example.com')->firstOrFail();

        $this->assertSame('customer', $user->role);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertFalse($user->hasPermission('manage_all_departments'));
    }

    public function test_admin_can_open_branch_and_staff_assignment_forms(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $this->actingAs($admin);

        $this->get('/admin/branches/create')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/users/create')->assertOk();
    }

    public function test_admin_assigns_department_scope_and_polymorphic_staff_profile(): void
    {
        $this->seed(AccessControlSeeder::class);
        $branch = Branch::create(['name' => 'Abidjan', 'code' => 'ABJ']);
        $department = Department::create(['branch_id' => $branch->id, 'name' => 'Sales', 'code' => 'SALES']);
        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $staff = User::factory()->create();

        $this->actingAs($admin);

        UserResource::synchronizeAdminFields($staff, [
            'role_assignments' => ['department_head'],
            'permissions' => [],
            'staff_branch_id' => $branch->id,
            'staff_department_id' => $department->id,
            'employee_number' => 'CI-EMP-001',
            'job_title' => 'Sales Department Head',
            'date_of_birth' => '1990-01-01',
            'address' => 'Abidjan',
        ]);

        $staff->refresh();
        $profile = $staff->staffProfile()->withoutGlobalScopes()->firstOrFail();

        $this->assertSame('department_head', $staff->role);
        $this->assertTrue($staff->hasRole('department_head'));
        $this->assertTrue($staff->hasPermission('staff.manage'));
        $this->assertSame($branch->id, $profile->branch_id);
        $this->assertSame($department->id, $profile->department_id);
        $this->assertSame($staff->getMorphClass(), $profile->profileable_type);
        $this->assertSame($staff->id, $profile->profileable_id);
    }

    public function test_reseeding_does_not_overwrite_admin_customized_role_permissions(): void
    {
        $this->seed(AccessControlSeeder::class);

        Role::findByName('department_head', 'web')->syncPermissions(['staff.view']);
        $this->seed(AccessControlSeeder::class);

        $permissions = Role::findByName('department_head', 'web')
            ->permissions
            ->pluck('name')
            ->all();

        $this->assertSame(['staff.view'], $permissions);
    }

    private function staffUser(string $role, Branch $branch, Department $department): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();
        $user->assignRole($role);
        $user->staffProfile()->create([
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'job_title' => ucfirst(str_replace('_', ' ', $role)),
        ]);

        return $user;
    }
}
