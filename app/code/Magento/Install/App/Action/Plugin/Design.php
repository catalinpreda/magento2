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

namespace Magento\Install\App\Action\Plugin;

class Design
{
    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Core\Model\Theme\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\View\DesignInterface
     */
    protected $_viewDesign;

    /**
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Core\Model\App $app
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\View\DesignInterface $viewDesign
     * @param \Magento\Core\Model\Theme\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\App\RequestInterface $request,
        \Magento\Core\Model\App $app,
        \Magento\View\LayoutInterface $layout,
        \Magento\View\DesignInterface $viewDesign,
        \Magento\Core\Model\Theme\CollectionFactory $collectionFactory
    ) {
        $this->_viewDesign = $viewDesign;
        $this->_collectionFactory = $collectionFactory;
        $this->_request = $request;
        $this->_app = $app;
        $this->_layout = $layout;
    }

    /**
     * Initialize design
     *
     * @param array $arguments
     * @return array
     */
    public function beforeDispatch(array $arguments = array())
    {
        $areaCode = $this->_layout->getArea();
        $area = $this->_app->getArea($areaCode);
        $area->load(\Magento\Core\Model\App\Area::PART_CONFIG);

        /** @var $themesCollection \Magento\Core\Model\Theme\Collection */
        $themesCollection = $this->_collectionFactory->create();
        $themeModel = $themesCollection->addDefaultPattern($areaCode)
            ->addFilter('theme_path', $this->_viewDesign->getConfigurationDesignTheme($areaCode))
            ->getFirstItem();
        $this->_viewDesign->setArea($areaCode)->setDesignTheme($themeModel);

        $area->detectDesign($this->_request);
        $area->load(\Magento\Core\Model\App\Area::PART_TRANSLATE);
        return $arguments;
    }
}
