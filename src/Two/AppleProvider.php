<?php

namespace Laravel\Socialite\Two;

use Illuminate\Support\Arr;

class AppleProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    protected $scopes = ['name', 'email'];

    /**
     * {@inheritdoc}
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://appleid.apple.com/auth/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://appleid.apple.com/auth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return parent::getTokenFields($code) + ['grant_type' => 'authorization_code'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByAccessTokenResponse($response)
    {
        $token = Arr::get($response, 'id_token');

        return array_merge([
            'id_token' => $token
        ], json_decode(base64_decode(explode('.', $token)[1]), true));
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param string|null $state
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        return parent::getCodeFields($state) + [
            'response_mode' => 'form_post'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        // Refresh_token
        // User info already in the token response, you only get the email the first time
        // No user meta data in the grant_type refresh_token, https://developer.apple.com/documentation/signinwithapplerestapi/generate_and_validate_tokens
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['sub'],
            'nickname' => null,
            'name' => null,
            'email' => Arr::get($user, 'email'),
            'avatar' => null,
        ]);
    }
}
