<?php
/**
 * Copyright since 2007 Carmine Di Gruttola
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    cdigruttola <c.digruttola@hotmail.it>
 * @copyright Copyright since 2007 Carmine Di Gruttola
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use cdigruttola\VirtualCombinations\Form\DataHandler\ProductVirtualCombinationsFormDataHandler;
use cdigruttola\VirtualCombinations\Form\ProductVirtualCombinationsType;
use PrestaShop\PrestaShop\Adapter\Product\Combination\Update\CombinationDeleter;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductType;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataProvider\FormDataProviderInterface;

class VirtualCombinations extends Module
{
    public function __construct()
    {
        $this->name = 'virtualcombinations';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'cdigruttola';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.2', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->trans('Virtual product with combination', [], 'Modules.Virtualcombinations.Main');
        $this->description = $this->trans('Admin can create virtual product with combination.', [], 'Modules.Virtualcombinations.Main');
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        include dirname(__FILE__) . '/sql/install.php';

        if (!parent::install()
            || !$this->registerHook('actionProductFormBuilderModifier')
            || !$this->registerHook('actionProductFormDataProviderData')
            || !$this->registerHook('actionAfterUpdateProductFormHandler')) {
            return false;
        } else {
            return true;
        }
    }

    public function uninstall()
    {
        include dirname(__FILE__) . '/sql/uninstall.php';
        return parent::uninstall();
    }

    public function hookActionAfterUpdateProductFormHandler($params)
    {
        $productId = (int) $params['id'];
        $form_data = $params['form_data']['options']['virtual_combinations'];

        /** @var ProductVirtualCombinationsFormDataHandler $handler */
        $handler = $this->get('cdigruttola.virtual_combinations.form.identifiable_object.data_handler.product_form_data_handler');
        if (!empty($form_data)) {
            $handler->createOrUpdate($form_data);

            $enableVirtualCombinations = $form_data['active'] ?? false;
            if ($enableVirtualCombinations) {
                Db::getInstance()->update('product', ['product_type' => ProductType::TYPE_COMBINATIONS], 'id_product = ' . $productId);
                Db::getInstance()->update('product', ['is_virtual' => 1], 'id_product = ' . $productId);
            } else {
//                Db::getInstance()->update('product', ['product_type' => ProductType::TYPE_VIRTUAL], 'id_product = ' . $productId);
//                /** @var CombinationDeleter $combinationDeleter */
//                $combinationDeleter = $this->get('PrestaShop\PrestaShop\Adapter\Product\Combination\Update\CombinationDeleter');
//                $combinationDeleter->deleteAllProductCombinations(new ProductId($productId), ShopConstraint::allShops());
            }
        }
    }

    public function hookActionProductFormBuilderModifier($params)
    {
        $productId = (int) $params['id'];
        $product = new \Product($productId);
        if (Validate::isLoadedObject($product) && $product->is_virtual) {
            $formBuilder = $params['form_builder']->get('options');
            $formBuilder->add(
                'virtual_combinations',
                ProductVirtualCombinationsType::class, [
                    'label' => $this->trans('Virtual combinations', [], 'Modules.Virtualcombinations.Main'),
                ]
            );
        }
    }

    public function hookActionProductFormDataProviderData($params)
    {
        /** @var FormDataProviderInterface $formDataProvider */
        $formDataProvider = $this->get('cdigruttola.virtual_combinations.form.identifiable_object.data_provider.product_data_provider');
        $params['data']['options']['virtual_combinations'] = $formDataProvider->getData($params['id']);
    }
}
