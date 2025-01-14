<?php


namespace App\Domain\Service\DialplanBuilders;


use App\Domain\Service\Dialplan\Dialplan;
use App\Domain\Service\Dialplan\Extension;
use App\Domain\Service\Exceptions\NoFreeExtensionsForReserve;
use App\Domain\Service\ExtensionStorageService;

abstract class AbstractDialplanExtensionBuilder implements DialplanExtensionBuilderInterface
{
    /**
     * @var Extension
     */
    protected $exten;
    /**
     * @var Dialplan
     */
    protected $dialplan;
    /**
     * @var Extension[]
     */
    protected $relatedExtensions = [];

    /**
     * @var string
     */
    protected $nodeId;
    /**
     * @var string
     */
    protected $nodeType;
    /**
     * @var ExtensionStorageService
     */
    protected $extensionStorageService;

    /**
     * @var BuildContext
     */
    protected $buildContext;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ExtensionStorageService $extensionStorageService,
        Dialplan $dialplan,
        BuildContext $buildContext
    ) {
        $this->dialplan                = $dialplan;
        $this->extensionStorageService = $extensionStorageService;
        $this->buildContext            = $buildContext;
        $extenName                     = $this->extensionStorageService->allocateOne($buildContext->getPbxSchemeId());
        $this->exten                   = $this->dialplan->createExtension($extenName);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension(): Extension
    {
        return $this->exten;
    }

    /**
     * {@inheritdoc}
     */
    public function addRelatedExtension(Extension $extension, string $relationType): void
    {
        $this->relatedExtensions[$relationType] = $extension;
    }

    /**
     * {@inheritdoc}
     * @throws NoFreeExtensionsForReserve
     */
    public function build(array $payload): Extension
    {
        $this->nodeType = $payload['node_type']['type'];

        $text = 'Node ' . $payload['id'] . ' is executing';
        $this->exten->addPriority($this->dialplan->NoOp($text));

        $this->doBuild($payload, $this->buildContext);

        if ($payload['node_type']['type'] === 'action' && isset($this->relatedExtensions['direct'])) {
            $extension = $this->relatedExtensions['direct'];
            $context   = config('dialplan.default_context');
            $this->exten->addPriority(
                $this->dialplan->GoToStatement('start', $extension->getName(), $context)
            );
        }

        return $this->exten;
    }

    /**
     * @param array        $payload
     * @param BuildContext $buildContext
     *
     * @return Extension
     */
    abstract protected function doBuild(array $payload, BuildContext $buildContext): Extension;

    protected function getExtenIfPasses(): Extension
    {
        return $this->relatedExtensions['positive'] ?? null;
    }

    protected function getExtenIfDeclined(): Extension
    {
        return $this->relatedExtensions['negative'] ?? null;
    }

    protected function getDirectRelatedExten(): Extension
    {
        return $this->relatedExtensions['direct'] ?? null;
    }
}
