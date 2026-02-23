<?php

namespace ContainerPZYCc8c;

include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/Mappers/BaseModuleMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/Mappers/BaseFieldMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/ApiRecordMappers/ApiRecordFieldMapperInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Schedulers/Service/Fields/JobIntervalListApiMapper.php';
class JobIntervalListApiMapperGhostBc29761 extends \App\Schedulers\Service\Fields\JobIntervalListApiMapper implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;
    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        "\0".'*'."\0".'cronHandler' => [parent::class, 'cronHandler', null],
        "\0".'*'."\0".'moduleNameMapper' => [parent::class, 'moduleNameMapper', null],
        'cronHandler' => [parent::class, 'cronHandler', null],
        'moduleNameMapper' => [parent::class, 'moduleNameMapper', null],
    ];
}
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('JobIntervalListApiMapperGhostBc29761', false)) {
    \class_alias(__NAMESPACE__.'\\JobIntervalListApiMapperGhostBc29761', 'JobIntervalListApiMapperGhostBc29761', false);
}
