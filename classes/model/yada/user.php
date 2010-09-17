<?php defined('SYSPATH') or die ('No direct script access.');
/**
 * Jelly Auth User Model
 * @package Jelly Auth
 * @author	Israel Canasa
 */
class Model_Yada_User extends Yada_Model
{
	public static function initialize(Jelly_Meta $meta)
        {
                $meta->name_key('username')
			->fields(array(
			'id' => new Field_Primary,
			'username' => new Field_String(array(
				'unique' => TRUE,
				'rules' => array(
						'not_empty' => array(TRUE),
						'max_length' => array(32),
						'min_length' => array(3),
						'regex' => array('/^[\pL_.-]+$/ui')
					)
				)),
			'password' => new Field_Password(array(
				'hash_with' => array(Auth::instance(), 'hash_password'),
				'rules' => array(
					'not_empty' => array(TRUE),
					'max_length' => array(50),
					'min_length' => array(6)
				)
			)),
			'password_confirm' => new Field_Password(array(
				'in_db' => FALSE,
				'callbacks' => array(
					'matches' => array('Model_Auth_User', '_check_password_matches')
				),
				'rules' => array(
					'not_empty' => array(TRUE),
					'max_length' => array(50),
					'min_length' => array(6)
				)
			)),
			'email' => new Field_Email(array(
				'unique' => TRUE
			)),
			'logins' => new Field_Integer(array(
				'default' => 0
			)),
			'last_login' => new Field_Timestamp,
			'tokens' => new Field_HasMany(array(
				'foreign' => 'user_token'
			)),
			'roles' => new Field_ManyToMany
		));
    }



} // End Model_Auth_User