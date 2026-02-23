<?php

namespace ContainerPZYCc8c;

include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/Mappers/BaseModuleMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/Mappers/BaseFieldTypeMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/ApiRecordMappers/ApiRecordFieldTypeMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Module/Service/Fields/LineItems/Mappers/LineItemsApiMapperTrait.php';
include_once \dirname(__DIR__, 3).'/core/backend/Module/Service/Fields/LineItems/Mappers/LineItemsRetrieveApiMapper.php';
class LineItemsRetrieveApiMapperGhost80d1d1e extends \App\Module\Service\Fields\LineItems\Mappers\LineItemsRetrieveApiMapper implements \Symfony\Component\VarExporter\LazyObjectInterface
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

if (!\class_exists('LineItemsRetrieveApiMapperGhost80d1d1e', false)) {
    \class_alias(__NAMESPACE__.'\\LineItemsRetrieveApiMapperGhost80d1d1e', 'LineItemsRetrieveApiMapperGhost80d1d1e', false);
}
