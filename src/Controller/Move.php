<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\ArticleCategories\Controller;

use Gm;
use Gm\Panel\Http\Response;
use Gm\Panel\Helper\ExtForm;
use Gm\Panel\Helper\ExtCombo;
use Gm\Panel\Widget\EditWindow;
use Gm\Panel\Controller\FormController;

/**
 * Контроллер перемещения категории материала.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\ArticleCategories\Controller
 * @since 1.0
 */
class Move extends FormController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'Move';

    /**
     * {@inheritdoc}
     */
    public function createWidget(): EditWindow
    {
        /** @var EditWindow $window */
        $window = parent::createWidget();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $window->width = 500;
        $window->autoHeight = true;
        $window->layout = 'fit';
        $window->title = '#{move.title}';
        $window->titleTpl = '#{move.titleTpl}';
        $window->iconCls = 'g-icon-svg gm-acategories__icon-move-to';

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->resizable = false;
        $window->form->bodyPadding = 10;
        $window->form->defaults = [
            'labelAlign' => 'right',
            'labelWidth' => 120,
            'anchor'     => '100%',
        ];
        $window->form->items = [
            ExtCombo::trigger('#Move to', 'moveTo', 'categories', false)
        ];
        $window->form->router->route = Gm::alias('@match', '/move');
        $window->form->router->state = $window->form::STATE_CUSTOM;
        $window->form->router->rules = [
            'perform' => '{route}/perform/{id}',
            'data'    => '{route}/data/{id}'
        ];
        $window->form->buttons = ExtForm::buttons([
            'info',
            'save' => [
                'text'    => $this->t('Apply'),
                'handler' => 'onFormAction',
                'handlerArgs' => [
                    'routeRule' => 'perform',
                ]
            ],
            'cancel'
        ]);
        return $window;
    }

    /**
     * Действие "move" выполняет перемещение категории материала по указанному 
     * идентификатору.
     * 
     * @return Response
     */
    public function performAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Backend\ArticleCategory\Model\Move|null $model */
        $model = $this->getModel($this->defaultModel);
        if ($model === null) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

         /** @var \Gm\Backend\ArticleCategory\Model\Move|null $form*/
        $form = $model->get();
        if ($form === null) {
            $response
                ->meta->error(Gm::t(BACKEND, 'No data to perform action'));
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->makeAppEventName(), [$this, $form]);
        }

        // валидация атрибутов модели
        if (!$form->validate()) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Error filling out form fields: {0}', [$form->getError()]));
            return $response;
        }

        // перемещение категории
        if (!$form->move()) {
            $response
                ->meta->error($form->hasErrors() ? $form->getError() : Gm::t(BACKEND, 'Could not save data'));
            return $response;
        }
        return $response;
    }
}
