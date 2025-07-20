<?php

namespace Pterodactyl\Addons\UserApiKeys\Http\Controllers;

use Pterodactyl\Models\User;
use Pterodactyl\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Http\Controllers\Api\Application\ApplicationApiController;
use Pterodactyl\Addons\UserApiKeys\Http\Requests\StoreApiKeyRequest;
use Pterodactyl\Transformers\Api\Client\ApiKeyTransformer;

class ApiKeyController extends ApplicationApiController
{
    /**
     * Returns all the API keys that exist for the given user.
     */
    public function index(int $userId): array
    {
        $user = User::findOrFail($userId);

        return $this->fractal->collection($user->apiKeys)
            ->transformWith($this->getTransformer(ApiKeyTransformer::class))
            ->toArray();
    }

    /**
     * Store a new API key for a user's account.
     *
     * @throws DisplayException
     */
    public function store(StoreApiKeyRequest $request, int $userId): array
    {
        $user = User::findOrFail($userId);

        if ($user->apiKeys->count() >= 25) {
            throw new DisplayException('This user has reached the account limit for number of API keys.');
        }

        $token = $user->createToken(
            $request->input('description'),
            $request->input('allowed_ips')
        );

        Activity::event('user:api-key.create')
            ->subject($token->accessToken)
            ->property('identifier', $token->accessToken->identifier)
            ->log();

        return $this->fractal->item($token->accessToken)
            ->transformWith($this->getTransformer(ApiKeyTransformer::class))
            ->addMeta(['secret_token' => $token->plainTextToken])
            ->toArray();
    }

    /**
     * Deletes a given API key.
     */
    public function delete(int $userId, string $identifier): JsonResponse
    {
        $user = User::findOrFail($userId);

        /** @var ApiKey $key */
        $key = $user->apiKeys()
            ->where('key_type', ApiKey::TYPE_ACCOUNT)
            ->where('identifier', $identifier)
            ->firstOrFail();

        Activity::event('user:api-key.delete')
            ->property('identifier', $key->identifier)
            ->log();

        $key->delete();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
