<?php

namespace ContainerPZYCc8c;

include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/RecordSaveHandlers/BaseModuleSaveHandlerInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Data/Service/Record/RecordSaveHandlers/RecordFieldTypeSaveHandlerInterface.php';
include_once \dirname(__DIR__, 3).'/core/backend/Module/Service/Fields/LineItems/SaveHandlers/LineItemsSaveHandler.php';
class LineItemsSaveHandlerGhostFcf7cbf extends \App\Module\Service\Fields\LineItems\SaveHandlers\LineItemsSaveHandler implements \Symfony\Component\VarExporter\LazyObjectInterface
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

if (!\class_exists('LineItemsSaveHandlerGhostFcf7cbf', false)) {
    \class_alias(__NAMESPACE__.'\\LineItemsSaveHandlerGhostFcf7cbf', 'LineItemsSaveHandlerGhostFcf7cbf', false);
}
