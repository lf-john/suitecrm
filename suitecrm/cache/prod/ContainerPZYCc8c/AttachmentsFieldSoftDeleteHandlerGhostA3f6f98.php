<?php

namespace ContainerPZYCc8c;

include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/RecordDeleteHandlers/BaseModuleDeleteHandlerInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/RecordDeleteHandlers/RecordFieldTypeDeleteHandlerInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Module/Service/Fields/Attachments/DeleteHandlers/AttachmentsFieldSoftDeleteHandler.php';
class AttachmentsFieldSoftDeleteHandlerGhostA3f6f98 extends \App\Module\Service\Fields\Attachments\DeleteHandlers\AttachmentsFieldSoftDeleteHandler implements \Symfony\Component\VarExporter\LazyObjectInterface
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

if (!\class_exists('AttachmentsFieldSoftDeleteHandlerGhostA3f6f98', false)) {
    \class_alias(__NAMESPACE__.'\\AttachmentsFieldSoftDeleteHandlerGhostA3f6f98', 'AttachmentsFieldSoftDeleteHandlerGhostA3f6f98', false);
}
