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
        $response = $this->webShopUserRepository->findByKeyValue($request->get("Key"), $request->get("Value"));
        return new ServiceDto("WebShopUser Retrieved Successfully.", 200, $response);
    }

    public function createTestUser(Request $request): ServiceDto
    {
        try {
            $user = new WebShopUser();
            $user->Name = $request->get("Name");
            $user->Name = $request->get("Name");
            $user->Email = $request->get("Email");
            $user->Login = $request->get("Login");
            $user->Initials = $request->get("Login");
            $user->AccountNumber = $request->get("AccountNumber");
            $user->Password = bcrypt($request->get("Login"));

            $user->save();

            $user->refresh();

            return new ServiceDto("Test WebShopUser Created Successfully.", 200, $user);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
