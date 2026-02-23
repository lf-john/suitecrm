<?php

namespace ContainerPZYCc8c;

include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/RecordDeleteHandlers/RecordDeleteHandlerRegistryInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/RecordDeleteHandlers/ModuleDeleteHandlerTrait.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/RecordDeleteHandlers/RecordDeleteHandlerTrait.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/RecordDeleteHandlers/RecordDeleteHandlerRegistry.php';
class RecordDeleteHandlerRegistryGhostF615e8a extends \App\Data\Service\Record\RecordDeleteHandlers\RecordDeleteHandlerRegistry implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;
    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        "\0".'*'."\0".'registry' => [parent::class, 'registry', null],
        'registry' => [parent::class, 'registry', null],
    ];
}
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('RecordDeleteHandlerRegistryGhostF615e8a', false)) {
    \class_alias(__NAMESPACE__.'\\RecordDeleteHandlerRegistryGhostF615e8a', 'RecordDeleteHandlerRegistryGhostF615e8a', false);
}
