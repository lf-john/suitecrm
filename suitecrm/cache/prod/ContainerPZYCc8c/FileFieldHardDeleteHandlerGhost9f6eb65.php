<?php

namespace ContainerPZYCc8c;

include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/RecordDeleteHandlers/BaseModuleDeleteHandlerInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/RecordDeleteHandlers/RecordFieldTypeDeleteHandlerInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Module/Service/Fields/File/DeleteHandlers/FileFieldHardDeleteHandler.php';
class FileFieldHardDeleteHandlerGhost9f6eb65 extends \App\Module\Service\Fields\File\DeleteHandlers\FileFieldHardDeleteHandler implements \Symfony\Component\VarExporter\LazyObjectInterface
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

if (!\class_exists('FileFieldHardDeleteHandlerGhost9f6eb65', false)) {
    \class_alias(__NAMESPACE__.'\\FileFieldHardDeleteHandlerGhost9f6eb65', 'FileFieldHardDeleteHandlerGhost9f6eb65', false);
}
