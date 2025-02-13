<?php
/**
 * Модуль веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\ArticleCategories;

use Gm\NestedSet\Nodes;

/**
 * Модуль категорий материала.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\ArticleCategories
 * @since 1.0
 */
class Module extends \Gm\Panel\Module\Module
{
    /**
     * {@inheritdoc}
     */
    public string $id = 'gm.be.article_categories';

    /**
     * Модель Nested Set (вложенного множества).
     * 
     * @var Nodes
     */
    protected Nodes $nestedSet;

    /**
     * Возвращает модель Nested Set (вложенного множества).
     * 
     * @param null $dataManager
     * 
     * @return Nodes
     */
    public function getNestedSet($dataManager = null): Nodes
    {
        if (!isset($this->nestedSet)) {
            $this->nestedSet = new Nodes([
                'tableName'    => $dataManager ? $this->dataManager->tableName : '{{article_category}}',
                'parentColumn' => $dataManager ? $this->dataManager->parentKey : 'ns_parent'
            ]);
        }
        return $this->nestedSet;
    }
}
