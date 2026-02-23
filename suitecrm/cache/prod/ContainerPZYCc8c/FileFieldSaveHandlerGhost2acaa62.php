<?php

namespace ContainerPZYCc8c;

include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/RecordSaveHandlers/BaseModuleSaveHandlerInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/RecordSaveHandlers/RecordFieldTypeSaveHandlerInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Module/Service/Fields/File/SaveHandlers/FileFieldSaveHandler.php';
class FileFieldSaveHandlerGhost2acaa62 extends \App\Module\Service\Fields\File\SaveHandlers\FileFieldSaveHandler implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;
    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        "\0".'*'."\0".'mediaObjectManager' => [parent::class, 'mediaObjectManager', null],
        "\0".'*'."\0".'moduleNameMapper' => [parent::class, 'moduleNameMapper', null],
        'mediaObjectManager' => [parent::class, 'mediaObjectManager', null],
        'moduleNameMapper' => [parent::class, 'moduleNameMapper', null],
    ];
}
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('FileFieldSaveHandlerGhost2acaa62', false)) {
    \class_alias(__NAMESPACE__.'\\FileFieldSaveHandlerGhost2acaa62', 'FileFieldSaveHandlerGhost2acaa62', false);
}
