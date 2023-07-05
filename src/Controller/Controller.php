<?php

namespace NsLibrary\Controller;


/**
 * TODO Auto-generated comment.
 */
class Controller extends AbstractController
{

        public function __construct()
        {
        }

        public static function toArray($object, $detalhes = false, array $fieldToIgnore = [])
        {
                return (new self())->objectToArray($object, $detalhes, $fieldToIgnore);
        }
}
