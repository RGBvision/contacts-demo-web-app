<?php

class AppController extends Controller
{

    public function __construct()
    {
        parent::__construct();

        if (Dir::exists(__DIR__ . '/i18n/')) {
            i18n::addPath(__DIR__ . '/i18n/');
        }

        // Класс модели
        $modelClass = 'AppModel';
        $file = __DIR__ . "/$modelClass.php";

        if (is_file($file)) {
            Loader::addClass($modelClass, $file);
            self::$model = new $modelClass();
        } else {
            Json::output(['content' => i18n::_('app.error.model')], 501);
        }

    }

    public static function index(): void
    {
        if ($view = File::getContent(__DIR__ . '/AppView.html')) {
            Html::output($view);
        }

        Response::setStatus(501);
    }

    public static function contacts_get(): void
    {
        $columns = ['first_name', 'phone', 'dob'];

        $sort_column = (int)Request::post('order.0.column');
        $sort_dir = Request::post('order.0.dir') ?? 'asc';

        $res = [];

        $content = self::$model->getContacts($columns[$sort_column], $sort_dir);

        $res['draw'] = (int)Request::post('draw');
        $res['data'] = $content;

        Json::output(['content' => $res]);
    }

    public static function contact_add(string $name, string $phone, string $date): void
    {
        if (
            ($_name = Valid::normalizeName($name))
            && ($_phone = Valid::normalizePhone($phone, true))
            && ($_date = Valid::normalizeDate($date))
        ) {

            if (self::$model->addContact($_name, $_phone, $_date)) {
                Json::output(['content' => i18n::_('app.success.contact_added')]);
            }

            Json::output(['content' => i18n::_('app.error.add_contact')], 409);

        }

        Json::output(['content' => i18n::_('app.error.wrong_data')], 400);
    }

    public static function contact_delete(string $phone): void
    {
        if ($_phone = Valid::normalizePhone($phone, true)) {

            if (self::$model->deleteContact($_phone)) {
                Json::output(['content' => i18n::_('app.success.contact_deleted')]);
            }

            Json::output(['content' => i18n::_('app.error.delete_contact')], 404);

        }

        Json::output(['content' => i18n::_('app.error.wrong_data')], 400);
    }

}