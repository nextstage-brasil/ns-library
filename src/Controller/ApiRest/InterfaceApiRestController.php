<?php

namespace NsLibrary\Controller\ApiRest;

interface ControllerApiRestInterface {

    function list(): void;

    function read(): void;

    function create(): void;

    function update(): void;

    function delete(): void;
}
