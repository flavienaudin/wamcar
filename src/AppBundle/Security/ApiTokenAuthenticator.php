<?php

namespace AppBundle\Security;

use AppBundle\Security\SecurityInterface\ApiUserProvider;
use AppBundle\Security\SecurityInterface\HasApiCredential;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ApiTokenAuthenticator extends AbstractGuardAuthenticator
{

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return $request->headers->has('secret') && $request->query->has('client_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        $secret = $request->headers->get('secret');
        $clientId = $request->query->get('client_id');

        // What you return here will be passed to getUser() as $credentials
        return [
            'secret' => $secret,
            'clientId' => $clientId
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $clientId = $credentials['clientId'];

        if(!$clientId) {
            return null;
        }

        if(!$userProvider instanceof ApiUserProvider) {
            return null;
        }

        return $userProvider->getByClientId($clientId);
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if(!$user instanceof HasApiCredential) {
            return false;
        }

        return $user->getApiSecret() === $credentials['secret'];
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(
            ['message' => strtr($exception->getMessageKey(), $exception->getMessageData())],
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $request->getSession()->set('AUTH_USER', $token->getUser());
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse(
            ['message' => 'Authentication Required'],
            Response::HTTP_UNAUTHORIZED
        );
    }

}
