<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author crist
 */
interface DAOInterface {
 	
	public function save();
	public function remove();
	public function getById($pk);
	public function getAll($condicao, $getRelacoes);
        public function getByCondition($array, $getRelacoes);
}
