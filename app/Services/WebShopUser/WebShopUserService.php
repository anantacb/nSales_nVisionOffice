<?php

namespace App\Services\WebShopUser;

use App\Contracts\ServiceDto;
use App\Models\Company\WebShopUser;
use App\Repositories\Eloquent\Company\WebShopUser\WebShopUserRepositoryInterface;
use Illuminate\Http\Request;

class WebShopUserService implements WebShopUserServiceInterface
{
    private WebShopUserRepositoryInterface $webShopUserRepository;

    public function __construct(
        WebShopUserRepositoryInterface $webShopUserRepository
    )
    {
        $this->webShopUserRepository = $webShopUserRepository;
    }

    public function details(Request $request): ServiceDto
    {
        $response = $this->webShopUserRepository->findByKeyValue($request->input("Key"), $request->input("Value"));
        return new ServiceDto("WebShopUser Retrieved Successfully.", 200, $response);
    }

    public function createTestUser(Request $request): ServiceDto
    {
        try {
            $user = new WebShopUser();
            $user->Name = $request->input("Name");
            $user->Name = $request->input("Name");
            $user->Email = $request->input("Email");
            $user->Login = $request->input("Login");
            $user->Initials = $request->input("Login");
            $user->AccountNumber = $request->input("AccountNumber");
            $user->Password = bcrypt($request->input("Login"));

            $user->save();

            $user->refresh();

            return new ServiceDto("Test WebShopUser Created Successfully.", 200, $user);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
