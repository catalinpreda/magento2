<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View\Asset;

/**
 * \Iterator that aggregates one or more assets and provides a single public file with equivalent behavior
 */
class Merged implements \Iterator
{
    /**
     * Sub path for merged files relative to public view cache directory
     */
    const PUBLIC_MERGE_DIR  = '_merged';

    /**
     * @var \Magento\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Logger
     */
    protected $logger;

    /**
     * @var MergeStrategyInterface
     */
    protected $mergeStrategy;

    /**
     * @var MergeableInterface[]
     */
    protected $assets;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var \Magento\App\Dir
     */
    protected $dirs;

    /**
     * Whether initialization has been performed or not
     *
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Logger $logger
     * @param \Magento\App\Dir $dirs
     * @param MergeStrategyInterface $mergeStrategy
     * @param array $assets
     * @throws \InvalidArgumentException
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\Logger $logger,
        \Magento\App\Dir $dirs,
        MergeStrategyInterface $mergeStrategy,
        array $assets
    ) {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->dirs = $dirs;
        $this->mergeStrategy = $mergeStrategy;

        if (!$assets) {
            throw new \InvalidArgumentException('At least one asset has to be passed for merging.');
        }
        /** @var $asset MergeableInterface */
        foreach ($assets as $asset) {
            if (!($asset instanceof MergeableInterface)) {
                throw new \InvalidArgumentException(
                    'Asset has to implement \Magento\View\Asset\MergeableInterface.'
                );
            }
            if (!$this->contentType) {
                $this->contentType = $asset->getContentType();
            } else if ($asset->getContentType() != $this->contentType) {
                throw new \InvalidArgumentException(
                    "Content type '{$asset->getContentType()}' cannot be merged with '{$this->contentType}'."
                );
            }
        }
        $this->assets = $assets;
    }

    /**
     * Attempt to merge assets, falling back to original non-merged ones, if merging fails
     */
    protected function initialize()
    {
        if (!$this->isInitialized) {
            $this->isInitialized = true;
            try {
                $this->assets = array($this->getMergedAsset($this->assets));
            } catch (\Exception $e) {
                $this->logger->logException($e);
            }
        }
    }

    /**
     * Retrieve asset instance representing a merged file
     *
     * @param MergeableInterface[] $assets
     * @return AssetInterface
     */
    protected function getMergedAsset(array $assets)
    {
        $sourceFiles = $this->getPublicFilesToMerge($assets);
        $destinationFile = $this->getMergedFilePath($sourceFiles);

        $this->mergeStrategy->mergeFiles($sourceFiles, $destinationFile, $this->contentType);
        return $this->objectManager->create('Magento\View\Asset\PublicFile', array(
            'file' => $destinationFile,
            'contentType' => $this->contentType,
        ));
    }

    /**
     * Go through all the files to merge, ensure that they are public (publish if needed), and compose
     * array of public paths to merge
     *
     * @param MergeableInterface[] $assets
     * @return array
     */
    protected function getPublicFilesToMerge(array $assets)
    {
        $result = array();
        foreach ($assets as $asset) {
            $publicFile = $asset->getSourceFile();
            $result[$publicFile] = $publicFile;
        }
        return $result;
    }

    /**
     * Return file name for the resulting merged file
     *
     * @param array $publicFiles
     * @return string
     */
    protected function getMergedFilePath(array $publicFiles)
    {
        $jsDir = \Magento\Filesystem::fixSeparator($this->dirs->getDir(\Magento\App\Dir::PUB_LIB));
        $publicDir = \Magento\Filesystem::fixSeparator($this->dirs->getDir(\Magento\App\Dir::STATIC_VIEW));
        $prefixRemovals = array($jsDir, $publicDir);

        $relFileNames = array();
        foreach ($publicFiles as $file) {
            $file = \Magento\Filesystem::fixSeparator($file);
            $relFileNames[] = str_replace($prefixRemovals, '', $file);
        }

        $mergedDir = $this->dirs->getDir(\Magento\App\Dir::PUB_VIEW_CACHE) . '/'
            . self::PUBLIC_MERGE_DIR;
        return $mergedDir . '/' . md5(implode('|', $relFileNames)) . '.' . $this->contentType;
    }

    /**
     * {@inheritdoc}
     *
     * @return AssetInterface
     */
    public function current()
    {
        $this->initialize();
        return current($this->assets);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        $this->initialize();
        return key($this->assets);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->initialize();
        next($this->assets);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->initialize();
        reset($this->assets);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $this->initialize();
        return (bool)current($this->assets);
    }
}
