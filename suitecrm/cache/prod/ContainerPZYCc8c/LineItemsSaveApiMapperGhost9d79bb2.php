<?php

namespace ContainerPZYCc8c;

include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/Mappers/BaseModuleMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/Mappers/BaseFieldTypeMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/ApiRecordMappers/ApiRecordFieldTypeMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Module/Service/Fields/LineItems/Mappers/LineItemsApiMapperTrait.php';
include_once \dirname(__DIR__, 3).'/core/backend/Module/Service/Fields/LineItems/Mappers/LineItemsSaveApiMapper.php';
class LineItemsSaveApiMapperGhost9d79bb2 extends \App\Module\Service\Fields\LineItems\Mappers\LineItemsSaveApiMapper implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;
    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        "\0".'*'."\0".'linkedRecordsProvider' => [parent::class, 'linkedRecordsProvider', null],
        'linkedRecordsProvider' => [parent::class, 'linkedRecordsProvider', null],
    ];
}
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('LineItemsSaveApiMapperGhost9d79bb2', false)) {
    \class_alias(__NAMESPACE__.'\\LineItemsSaveApiMapperGhost9d79bb2', 'LineItemsSaveApiMapperGhost9d79bb2', false);
}
