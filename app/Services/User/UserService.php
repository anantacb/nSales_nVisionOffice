<?php

namespace App\Services\User;


use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Role\CompanyUserRoleRepositoryInterface;
use App\Repositories\Eloquent\Office\User\CompanyUserRepositoryInterface;
use App\Repositories\Eloquent\Office\User\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserService implements UserServiceInterface
{
    protected UserRepositoryInterface $userRepository;
    protected CompanyUserRepositoryInterface $companyUserRepository;
    protected CompanyUserRoleRepositoryInterface $companyUserRoleRepository;

    public function __construct(
        UserRepositoryInterface            $userRepository,
        CompanyUserRepositoryInterface     $companyUserRepository,
        CompanyUserRoleRepositoryInterface $companyUserRoleRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->companyUserRepository = $companyUserRepository;
        $this->companyUserRoleRepository = $companyUserRoleRepository;
    }

    public function authUserDetails(): ServiceDto
    {
        return new ServiceDto('Auth User Details Retrieved Successfully', 200, Auth::user());
    }

    public function createCompanyUser(Request $request): ServiceDto
    {
        $salt = generateRandomString(32, true);
        $hash = strtoupper(sha1($salt . $request->get('Password')));

        $user = $this->userRepository->create([
            'Name' => $request->get('Name'),
            'Initials' => $request->get('Initials'),
            'PhoneNo' => $request->get('PhoneNo'),
            'MobileNo' => $request->get('MobileNo'),
            'Email' => $request->get('Email'),
            'Login' => $request->get('Email'),
            'CultureName' => $request->get('CultureName'),
            'Hash' => $hash,
            'Salt' => $salt,
            'Disabled' => $request->get('Disabled'),
        ]);

        $latestCompanyUser = $this->companyUserRepository->firstByAttributes([
            ['column' => 'CompanyId', 'operand' => '=', 'value' => $request->get('company_id')]
        ], [], '', 'Number', true);

        $number = $latestCompanyUser->Number + 1;

        $companyUser = $this->companyUserRepository->create([
            'CompanyId' => $request->get('company_id'),
            'UserId' => $user->Id,
            'Number' => $number,
            'CultureName' => $request->get('CultureName'),
            'Initials' => $request->get('Initials'),
            'Territory' => $request->get('Territory'),
            'Commission' => $request->get('Commission'),
            'Billable' => $request->get('Billable'),
            'Note' => $request->get('Note')
        ]);

        foreach ($request->get('RoleIds') as $roleId) {
            $this->companyUserRoleRepository->create([
                'RoleId' => $roleId,
                'CompanyUserId' => $companyUser->Id
            ]);
        }

        // TODO Send Mail
        
        return new ServiceDto('User Created Successfully', 200, $user);
    }
}
