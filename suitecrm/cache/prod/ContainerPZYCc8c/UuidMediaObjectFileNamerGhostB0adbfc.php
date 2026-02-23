<?php

namespace ContainerPZYCc8c;

include_once \dirname(__DIR__, 3).'/vendor/vich/uploader-bundle/src/Naming/NamerInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/MediaObjects/Services/UuidMediaObjectFileNamer.php';
class UuidMediaObjectFileNamerGhostB0adbfc extends \App\MediaObjects\Services\UuidMediaObjectFileNamer implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;
    private const LAZY_OBJECT_PROPERTY_SCOPES = [];
}
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('UuidMediaObjectFileNamerGhostB0adbfc', false)) {
    \class_alias(__NAMESPACE__.'\\UuidMediaObjectFileNamerGhostB0adbfc', 'UuidMediaObjectFileNamerGhostB0adbfc', false);
}
