<?php
namespace Frontend42\Mutator\Strategy;

use Core42\Hydrator\Mutator\Mutator;
use Core42\Hydrator\Mutator\Strategy\StrategyInterface;
use Frontend42\Block\BlockContainer;
use Frontend42\Block\BlockInterface;
use Frontend42\Block\Service\BlockPluginManager;
use Frontend42\Model\Block;

class BlockStrategy implements StrategyInterface
{
    /**
     * @var Mutator
     */
    protected $mutator;

    /**
     * @var BlockPluginManager
     */
    protected $blockPluginManager;

    /**
     * @var BlockContainer
     */
    protected $blockContainerProtoType;

    /**
     * BlockStrategy constructor.
     * @param Mutator $mutator
     * @param BlockPluginManager $blockPluginManager
     */
    public function __construct(
        Mutator $mutator,
        BlockPluginManager $blockPluginManager,
        BlockContainer $blockContainerProtoType
    ) {
        $this->mutator = $mutator;
        $this->blockPluginManager = $blockPluginManager;
        $this->blockContainerProtoType = $blockContainerProtoType;
    }

    /**
     * @param mixed $value
     * @param array $spec
     * @return mixed
     */
    public function hydrate($value, array $spec = [])
    {
        $blockContainer = clone $this->blockContainerProtoType;
        if (!is_array($value)) {
            return $blockContainer;
        }

        $blocks = [];
        foreach ($value as $id => $blockSpec) {
            if (!$this->blockPluginManager->has($blockSpec['__type__'])) {
                continue;
            }

            $blockModel = new Block();
            $blockModel->setId($id)
                ->setType($blockSpec['__type__'])
                ->setName($blockSpec['__name__'])
                ->setIndex($blockSpec['__index__']);

            $elements = array_filter($blockSpec, function ($key){
                return !in_array($key, ['__index__', '__type__', '__name__', '__deleted__']);
            }, ARRAY_FILTER_USE_KEY);

            /** @var BlockInterface $blockType */
            $blockType = $this->blockPluginManager->get($blockModel->getType());
            $elements = $this->mutator->hydrate($elements, $blockType->getElements());
            if (empty($elements)) {
                $elements = [];
            }
            $blockModel->setElements($elements);

            $blocks[] = $blockModel;
        }
        $blockContainer->setBlocks($blocks);

        return $blockContainer;
    }
}
