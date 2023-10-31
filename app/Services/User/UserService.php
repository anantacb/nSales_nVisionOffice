<?php

namespace App\Services\User;


use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyUser\CompanyUserRepositoryInterface;
use App\Repositories\Eloquent\Office\CompanyUserRole\CompanyUserRoleRepositoryInterface;
use App\Repositories\Eloquent\Office\User\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserService implements UserServiceInterface
{
    protected UserRepositoryInterface $userRepository;
    protected CompanyUserRepositoryInterface $companyUserRepository;
    protected CompanyUserRoleRepositoryInterface $companyUserRoleRepository;
    protected CompanyRepositoryInterface $companyRepository;

    public function __construct(
        UserRepositoryInterface            $userRepository,
        CompanyUserRepositoryInterface     $companyUserRepository,
        CompanyUserRoleRepositoryInterface $companyUserRoleRepository,
        CompanyRepositoryInterface         $companyRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->companyUserRepository = $companyUserRepository;
        $this->companyUserRoleRepository = $companyUserRoleRepository;
        $this->companyRepository = $companyRepository;
    }

    public function authUserDetails(): ServiceDto
    {
        return new ServiceDto('Auth User Details Retrieved Successfully', 200, Auth::user());
    }

    public function getUsers(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            [
                'name' => 'companyUsers.roles'
            ]
        ];
        $companies = $this->userRepository->paginatedData($request);
        return new ServiceDto("Users retrieved!!!", 200, $companies);
    }

    public function getDevelopers(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['filter_by_relation'] = [
            ["relation" => 'companyUsers.roles', "column" => "Type", "operator" => "=", "values" => 'Developer'],
        ];
        $companies = $this->userRepository->paginatedData($request);
        return new ServiceDto("Developers retrieved!!!", 200, $companies);
    }

    public function getCompanyUsers(Request $request): ServiceDto
    {
        $request = $request->all();
        $request["relations"] = [
            [
                "name" =>
                    [
                        'companyUser' => function ($q) use ($request) {
                            $q->with([
                                'roles' => function ($q) {
                                    $q->where('Type', '!=', 'Developer');
                                }
                            ])->where('CompanyId', $request['CompanyId']);
                        }
                    ]
            ]
        ];

        $companyUsers = $this->userRepository->paginatedNonDevelopersData($request);
        return new ServiceDto("Company Users retrieved!!!", 200, $companyUsers);
    }

    public function createCompanyUser(Request $request): ServiceDto
    {
        $salt = generateRandomString(32, true);
        $password = generateRandomString(8);
        $hash = strtoupper(sha1($salt . $password));

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
            ['column' => 'CompanyId', 'operand' => '=', 'value' => $request->get('CompanyId')]
        ], [], '', 'Number', true);

        $number = $latestCompanyUser->Number + 1;

        $companyUser = $this->companyUserRepository->create([
            'CompanyId' => $request->get('CompanyId'),
            'UserId' => $user->Id,
            'Number' => $number,
            'CultureName' => $request->get('CultureName'),
            'Initials' => $request->get('Initials'),
            'LicenceType' => $request->get('LicenceType'),
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

    public function assignToCompany(Request $request): ServiceDto
    {
        $userId = $request->get('UserId');
        $companyId = $request->get('CompanyId');
        $latestCompanyUser = $this->companyUserRepository->firstByAttributes([
            ['column' => 'CompanyId', 'operand' => '=', 'value' => $companyId]
        ], [], '', 'Number', true);

        $number = $latestCompanyUser->Number + 1;

        $companyUser = $this->companyUserRepository->create([
            'CompanyId' => $companyId,
            'UserId' => $userId,
            'Number' => $number,
            'CultureName' => $request->get('CultureName'),
            'Initials' => $request->get('Initials'),
            'LicenceType' => $request->get('LicenceType'),
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

        return new ServiceDto('User Assigned Successfully', 200, []);
    }

    public function companyUserDetails(Request $request): ServiceDto
    {
        $companyUserId = $request->get('UserId');
        $companyId = $request->get('CompanyId');
        $relations = [
            'companyUser' => function ($q) use ($companyId) {
                $q->with([
                    'roles' => function ($q2) {
                        $q2->where('Type', '!=', 'Developer');
                    }
                ])->where('CompanyId', $companyId);
            },

            'devices' => function ($q) use ($companyId) {
                $q->with([
                    'companyDevice' => function ($q2) use ($companyId) {
                        $q2->where('CompanyId', $companyId);
                    }
                ])->whereHas('companyDevice', function ($q3) use ($companyId) {
                    $q3->where('CompanyId', $companyId);
                });
            }
        ];

        $companyUser = $this->userRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $companyUserId]
        ], $relations);
        return new ServiceDto("Company Users retrieved!!!", 200, $companyUser);
    }

    public function updateCompanyUser(Request $request): ServiceDto
    {
        $companyUserId = $request->get('CompanyUserId');
        $companyUser = $this->companyUserRepository->findByIdAndUpdate($companyUserId, [
            'Initials' => $request->get('Initials'),
            'LicenceType' => $request->get('LicenceType'),
            'Territory' => $request->get('Territory'),
            'Note' => $request->get('Note')
        ]);

        $companyUser->load('companyUserRoles');


        $currentRoleIds = $companyUser->companyUserRoles->pluck('RoleId')->toArray();

        $requestedRoleIds = $request->get('RoleIds');

        if ($currentRoleIds !== $requestedRoleIds) {
            $newRoleIds = array_diff($requestedRoleIds, $currentRoleIds);
            $deletedRoleIds = array_diff($currentRoleIds, $requestedRoleIds);

            foreach ($deletedRoleIds as $deletedRoleId) {
                $this->companyUserRoleRepository->deleteByAttributes([
                        ['column' => 'RoleId', 'operand' => '=', 'value' => $deletedRoleId],
                        ['column' => 'CompanyUserId', 'operand' => '=', 'value' => $companyUserId]
                    ]
                );
            }

            foreach ($newRoleIds as $newRoleId) {
                $this->companyUserRoleRepository->create([
                    'RoleId' => $newRoleId,
                    'CompanyUserId' => $companyUserId
                ]);
            }
        }

        // TODO Send Mail

        return new ServiceDto('User Updated Successfully', 200, []);
    }

    public function details(Request $request): ServiceDto
    {
        $userId = $request->get('UserId');

        $relations = [
            'companyUsers' => function ($q) {
                $q->with(['roles', 'company']);
            }
        ];

        $user = $this->userRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $userId]
        ], $relations);

        return new ServiceDto("User retrieved!!!", 200, $user);
    }

    public function update(Request $request): ServiceDto
    {
        $userId = $request->get('Id');
        $user = $this->userRepository->findByIdAndUpdate($userId, [
            'Name' => $request->get('Name'),
            'PhoneNo' => $request->get('PhoneNo'),
            'MobileNo' => $request->get('MobileNo'),
            'Email' => $request->get('Email'),
            'Login' => $request->get('Email')
        ]);

        return new ServiceDto('User Updated Successfully', 200, $user);
    }

    public function getAllCompanyUsers(Request $request): ServiceDto
    {
        $companyId = $request->get('CompanyId');
        $relations = [
            'user' => function ($q) {
                $q->select(['Id', 'Name', 'Initials']);
            }
        ];

        $filter_by_relation = [];
        if ($request->get('ExcludeDevelopers')) {
            $filter_by_relation = [
                ["relation" => 'roles', "column" => "Type", "operator" => "!=", "values" => 'Developer'],
            ];
        }
        $companyUsers = $this->companyUserRepository->getByAttributes([
            ['column' => 'CompanyId', 'operand' => '=', 'value' => $companyId]
        ], $relations, ['Id', 'UserId', 'CompanyId', 'Initials'], '', false, $filter_by_relation);

        return new ServiceDto('Company Users Retrieved Successfully', 200, $companyUsers);
    }

    public function tagDeveloperToAllCompanies(Request $request): ServiceDto
    {
        $userId = $request->get('UserId');

        $user = $this->userRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $userId]
        ]);

        $relations = [
            'roles' => function ($q) {
                $q->where('Type', 'Developer');
            }
        ];
        $companies = $this->companyRepository->getByAttributes([], $relations);

        foreach ($companies as $company) {
            if ($company->roles) {
                $companyUser = $this->companyUserRepository->firstOrCreate([
                    'CompanyId' => $company->Id,
                    'UserId' => $user->Id,
                    'Commission' => 0
                ]);

                $this->companyUserRoleRepository->firstOrCreate([
                    'RoleId' => $company->roles[0]->Id,
                    'CompanyUserId' => $companyUser->Id
                ]);
            }
        }

        return new ServiceDto('Successfully Assigned To All Companies.', 200, []);
    }
}
