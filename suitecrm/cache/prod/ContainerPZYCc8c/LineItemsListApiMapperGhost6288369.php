<?php

namespace ContainerPZYCc8c;

include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/Mappers/BaseModuleMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/Mappers/BaseFieldTypeMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/ApiRecordMappers/ApiRecordFieldTypeMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Module/Service/Fields/LineItems/Mappers/LineItemsListApiMapper.php';
class LineItemsListApiMapperGhost6288369 extends \App\Module\Service\Fields\LineItems\Mappers\LineItemsListApiMapper implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;
    private const LAZY_OBJECT_PROPERTY_SCOPES = [];
}
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('LineItemsListApiMapperGhost6288369', false)) {
    \class_alias(__NAMESPACE__.'\\LineItemsListApiMapperGhost6288369', 'LineItemsListApiMapperGhost6288369', false);
}
