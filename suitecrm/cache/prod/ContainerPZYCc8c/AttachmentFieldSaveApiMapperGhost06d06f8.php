<?php

namespace ContainerPZYCc8c;

include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/Mappers/BaseModuleMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/Mappers/BaseFieldTypeMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/ApiRecordMappers/ApiRecordFieldTypeMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Module/Service/Fields/Attachments/Mappers/AttachmentFieldApiMapperTrait.php';
include_once \dirname(__DIR__, 3).'/core/backend/Module/Service/Fields/Attachments/Mappers/AttachmentFieldSaveApiMapper.php';
class AttachmentFieldSaveApiMapperGhost06d06f8 extends \App\Module\Service\Fields\Attachments\Mappers\AttachmentFieldSaveApiMapper implements \Symfony\Component\VarExporter\LazyObjectInterface
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

if (!\class_exists('AttachmentFieldSaveApiMapperGhost06d06f8', false)) {
    \class_alias(__NAMESPACE__.'\\AttachmentFieldSaveApiMapperGhost06d06f8', 'AttachmentFieldSaveApiMapperGhost06d06f8', false);
}
