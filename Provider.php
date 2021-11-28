<?php

namespace SocialiteProviders\OpenStreetMap;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    public const IDENTIFIER = 'OPENSTREETMAP';

    /**
     * {@inheritdoc}
     */
    protected $scopes = ['read_prefs'];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://www.openstreetmap.org/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://www.openstreetmap.org/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            'https://api.openstreetmap.org/api/0.6/user/details',
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );

        $xml = simplexml_load_string((string) $response->getBody());
        $user = $xml->user;
        $avatar = $user->img['href'];

        return array(
            'id' => $user['id'],
            'username' => $user['display_name'],
            'avatar' => $avatar,
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id'       => $user['elements'][0]['id'],
            'nickname' => null,
            'name'     => $user['elements'][0]['name'],
            'email'    => null,
            'avatar'   => $user['elements'][0]['avatar'],
        ]);
    }
}
