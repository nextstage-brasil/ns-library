<?php

namespace NsLibrary\Controller;

/**
 * TODO Auto-generated comment.
 */
interface InterfaceApiRest {
    
    public function __construct(\NsUtil\Api $api);

    public function index();

    public function read();

    public function create();

    public function update();

    public function delete();
}
