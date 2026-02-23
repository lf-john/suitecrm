<?php

namespace ContainerPZYCc8c;

include_once \dirname(__DIR__, 3).'/vendor/scheb/2fa-bundle/Security/TwoFactor/Condition/TwoFactorConditionInterface.php';
include_once \dirname(__DIR__, 3).'/vendor/scheb/2fa-bundle/Security/TwoFactor/Condition/AuthenticatedTokenCondition.php';
class AuthenticatedTokenConditionGhost98cdc67 extends \Scheb\TwoFactorBundle\Security\TwoFactor\Condition\AuthenticatedTokenCondition implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;
    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        "\0".parent::class."\0".'supportedTokens' => [parent::class, 'supportedTokens', null],
        'supportedTokens' => [parent::class, 'supportedTokens', null],
    ];
}
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('AuthenticatedTokenConditionGhost98cdc67', false)) {
    \class_alias(__NAMESPACE__.'\\AuthenticatedTokenConditionGhost98cdc67', 'AuthenticatedTokenConditionGhost98cdc67', false);
}
