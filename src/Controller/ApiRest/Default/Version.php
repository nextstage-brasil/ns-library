<?php

namespace NsLibrary\Controller\ApiRest\Default;

use NsLibrary\Config;
use NsLibrary\Controller\ApiRest\AbstractApiRestController;
use NsUtil\Api;
use NsUtil\Databases\Migrations;

class Version extends AbstractApiRestController
{

    public function __construct(Api $api)
    {
        $this->init($api);
    }

    public function list(): void
    {
        $out = [];
        $out['app'] = Config::getData('appname');
        $out['version'] = file_get_contents(Config::getData('path') . '/version');
        if ($this->dados['k'] === 'migrations') {
            $out['migrations'] = Migrations::run(Config::getData('path'), [], (Config::getData('ENVIRONMENT') === 'prod'));
        }
        $this->response($out);
    }
}
