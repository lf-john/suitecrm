<?php

namespace ContainerPZYCc8c;

include_once \dirname(__DIR__, 3).'/vendor/scheb/2fa-bundle/Security/TwoFactor/Condition/TwoFactorConditionRegistry.php';
class TwoFactorConditionRegistryGhost67cc6e4 extends \Scheb\TwoFactorBundle\Security\TwoFactor\Condition\TwoFactorConditionRegistry implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;
    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        "\0".parent::class."\0".'conditions' => [parent::class, 'conditions', null],
        'conditions' => [parent::class, 'conditions', null],
    ];
}
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('TwoFactorConditionRegistryGhost67cc6e4', false)) {
    \class_alias(__NAMESPACE__.'\\TwoFactorConditionRegistryGhost67cc6e4', 'TwoFactorConditionRegistryGhost67cc6e4', false);
}
