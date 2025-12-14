<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\services\AppleControllerService;
use common\models\AppleModel;
use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Application;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;
use yii\web\Session;

/**
 * Контроллер для управления яблоками.
 */
class AppleController extends Controller
{
    public function __construct($id, $module, protected AppleControllerService $controllerService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'generate' => ['POST'],
                    'fall' => ['POST'],
                    'eat' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Отображение списка всех яблок.
     */
    public function actionIndex(): string
    {
        $this->controllerService->updateAllRottenStatuses();

        $apples = AppleModel::find()->orderBy([
            'id' => SORT_DESC,
        ])->all();

        return $this->render('index', [
            'apples' => $apples,
        ]);
    }

    /**
     * Генерация случайного количества яблок.
     *
     * @return Response|array{success: bool, generated: int}
     * @throws Exception
     */
    public function actionGenerate(): Response|array
    {
        $result = $this->controllerService->generateApples();
        $response = ['success' => true, 'generated' => $result['generated']];

        /** @var Application $app */
        $app = Yii::$app;

        $request = $app->request;

        if ($request->isAjax) {
            $webResponse = $app->response;
            $webResponse->format = Response::FORMAT_JSON;
            return $response;
        }

        $session = $app->session;
        $session->setFlash('success', sprintf('Выросло яблок: %d шт.', $result['generated']));

        $controller = $app->controller;
        return $controller->redirect(['index']);
    }

    /**
     * Уронить яблоко на землю.
     *
     * @return Response|array{success: bool, error?: string}
     * @throws NotFoundHttpException
     */
    public function actionFall(int $id): Response|array
    {
        $apple = $this->findModel($id);
        $result = $this->controllerService->fallApple($apple);

        return $this->formatResponse(
            $result,
            'Яблоко упало на землю'
        );
    }

    /**
     * Съесть процент от яблока.
     *
     * @return Response|array{success: bool, error?: string}
     * @throws NotFoundHttpException
     */
    public function actionEat(int $id): Response|array
    {
        $apple = $this->findModel($id);
        /** @var Application $app */
        $app = Yii::$app;
        /** @var Request $request */
        $request = $app->request;
        $percentRaw = $request->post('percent', 0);
        $percent = is_numeric($percentRaw) ? (int)$percentRaw : 0;

        $result = $this->controllerService->eatApple($apple, $percent);

        return $this->formatResponse(
            $result,
            sprintf('Съедено %d%% яблока', $percent)
        );
    }

    /**
     * Удалить яблоко.
     *
     * @return Response|array{success: bool, error?: string}
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id): Response|array
    {
        $apple = $this->findModel($id);
        $result = $this->controllerService->deleteApple($apple);

        return $this->formatResponse(
            $result,
            'Яблоко успешно удалено',
            'Не удалось удалить яблоко'
        );
    }

    /**
     * Найти модель яблока по ID.
     *
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel(int $id): AppleModel
    {
        if (($model = AppleModel::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенное яблоко не найдено.');
    }


    /**
     * Форматировать ответ на основе типа запроса (AJAX или обычный).
     *
     * @param array{success: bool, error?: string, generated?: int} $result         Результат операции
     * @param string $successMessage Сообщение при успехе для обычного запроса
     * @param string $errorPrefix    Префикс для сообщения об ошибке
     * @return Response|array{success: bool, error?: string, generated?: int}
     */
    private function formatResponse(
        array $result,
        string $successMessage,
        string $errorPrefix = '',
    ): Response|array {
        /** @var Application $app */
        $app = Yii::$app;
        /** @var Request $request */
        $request = $app->request;
        /** @var Response $response */
        $response = $app->response;
        /** @var Session $session */
        $session = $app->session;

        if ($request->isAjax) {
            $response->format = Response::FORMAT_JSON;

            return $result;
        }

        if ($result['success']) {
            $session->setFlash('success', $successMessage);
        } else {
            $error = $result['error'] ?? 'Unknown error';
            $errorMessage = $errorPrefix ? $errorPrefix . ': ' . $error : $error;
            $session->setFlash('error', $errorMessage);
        }

        /** @var Controller $controller */
        $controller = $app->controller;
        return $controller->redirect(['index']);
    }
}
