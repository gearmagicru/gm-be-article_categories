<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\ArticleCategories\Helper;

use Gm;
use Gm\Site\Data\Model\Article;
use Gm\Data\Model\DataModel;
use Gm\Mvc\Module\BaseModule;

/**
 * Помощник.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\ArticleCategories\Helper
 * @since 1.0
 */
class Helper extends DataModel
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\ArticleCategories\Module
     */
    public BaseModule $module;

    /**
     * Удаляет все статьи, которые представлены как главная страница (статьи) категории.
     * 
     * Обновление статей у которых указана категория.
     * 
     * @param array|null
     * 
     * @return bool Если false, ошибка удаления или обновления записей.
     */
    public function deleteArticles(array $categories = null): bool
    {
        /** @var \Gm\Db\Adapter\Adapter $db */
        $db = $this->getDb();
        /** @var \Gm\Db\Sql\QueryBuilder $builder */
        $builder = $db->getQueryBuilder();

        // если удаление только выбранных категорий
        if ($categories) {
            /** @var \Gm\NestedSet\Nodes $nestedSet */
            $nestedSet = $this->module->getNestedSet();
            foreach ($categories as $category) {
                // если есть потомки
                if ($nestedSet->getChildCount($category) > 0) {
                    $categorySelect = $builder
                        ->select('{{article_category}}')
                            ->columns([$nestedSet->idColumn])
                            ->where([
                                $nestedSet->leftColumn . ' >= ' . $category[$nestedSet->leftColumn],
                                $nestedSet->rightColumn . ' <= ' . $category[$nestedSet->rightColumn]
                            ]);
                    $builder
                        ->delete('{{article}}')
                            ->where(['slug_type' => Article::SLUG_HOME])
                            ->where->in('category_id', $categorySelect);
                    // удаление главных страниц (статей) категорий
                    $command = $db->createCommand($builder->getSqlString())->execute();
                    if ($command->getResult() !== true) return false;

                    $builder
                        ->update('{{article}}')
                            ->set(['category_id' => null])
                            ->where->in('category_id', $categorySelect);
                    // обновление всех статей у которых установлены категории
                    $command = $db->createCommand($builder->getSqlString())->execute();
                    if ($command->getResult() !== true) return false;
                // если нет потомков
                } else {
                    $builder
                        ->delete('{{article}}')
                            ->where(['slug_type' => Article::SLUG_HOME, 'category_id' => $category[$nestedSet->idColumn]]);
                    // удаление главной страницы (статьи) категории
                    $command = $db->createCommand($builder->getSqlString())->execute();
                    if ($command->getResult() !== true) return false;

                    $builder
                        ->update('{{article}}')
                            ->set(['category_id' => null])
                            ->where(['category_id' => $category[$nestedSet->idColumn]]);
                    // обновление статей у которых установлена категория
                    $command = $db->createCommand($builder->getSqlString())->execute();
                    if ($command->getResult() !== true) return false;
                }
            }

        // если удаление всех категорий
        } else {
            $builder
                ->delete('{{article}}')
                    ->where(['slug_type' => Article::SLUG_HOME])
                    ->where->isNotNull('category_id');
            // удаление главных страниц (статей) категорий
            $command = $db->createCommand($builder->getSqlString())->execute();
            if ($command->getResult() !== true) return false;

            $builder
                ->update('{{article}}')
                    ->set(['category_id' => null])
                    ->where->isNotNull('category_id');
            // обновление всех статей у которых установлены категории
            $command = $db->createCommand($builder->getSqlString())->execute();
            if ($command->getResult() !== true) return false;
        }
        return true;
    }
}
