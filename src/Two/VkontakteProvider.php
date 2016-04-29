<?php
namespace Laravel\Socialite\Two;

class VkontakteProvider extends AbstractProvider implements ProviderInterface {

    protected $scopes = ['email'];

    protected function getAuthUrl($state) {
        return $this->buildAuthUrlFromBase(
            'https://oauth.vk.com/authorize', $state
        );
    }

    protected function getTokenUrl() {
        return 'https://oauth.vk.com/access_token';
    }

    protected function getUserByToken($token) {
        $response = $this->getHttpClient()->get(
            'https://api.vk.com/method/users.get?user_ids='.$token['user_id'].'&fields=uid,first_name,last_name,screen_name,photo_100,verified,city,site,has_mobile,contacts'
        );
        $response = json_decode($response->getBody()->getContents(), true)['response'][0];
        return array_merge($response, [
            'email' => array_get($token, 'email'),
        ]);
    }

    protected function mapUserToObject(array $user) {
        return (new User())->setRaw($user)->map([
            'id' => $user['uid'],
            'nickname' => $user['screen_name'],
            'name' => $user['first_name'].' '.$user['last_name'],
            'email' => array_get($user, 'email'),
            'avatar' => $user['photo_100'],
        ]);
    }

    protected function getTokenFields($code) {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }

    protected function parseAccessToken($body) {
        return json_decode($body, true);
    }
}