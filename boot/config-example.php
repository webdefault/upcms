<?php

/**
 * @classe Classe de configuração
 *
 */
class Config
{
	const LANGUAGE = 'pt_BR';
	## PDO CONFIG
	const DATABASE_TYPE = 'mysql';
	const DATABASE_HOST = 'localhost'; // Default: localhost
	// const DATABASE_HOST = 'localhost'; // Default: localhost
	const DATABASE_PORT = '3306'; // Default: 3306
	const DATABASE_USER = 'root';
	const DATABASE_PASS = '';
	const DATABASE_NAME = 'meu-banco';
	
	const SYSTEM_TABLE_PREFIX = 'sys_';
	
	const DEFAULT_APPLICATION_DIR = 'app';
	const APPLICATION_STARTER = 'starter';
	
	const TOTAL_GRID_ITENS = 3;
	const TOTAL_LIST_ITENS = 15;
	
	const DEFAULT_KEYWORDS = '';
	const DEFAULT_META_DESCRIPTION = '';
	const DEFAULT_NAME_AUTHOR = '';
	
	const SITE_NAME = '';
	
	//const FULL_APP_PATH = '/var/www/html/storages/bringme/';
	const FULL_APP_PATH = '/Users/......../Sites/rastreio/';
	
	const STORAGE_PATH = 'storage';
	
	## SITE CONFIG
	const PATH_NAME = 'minha-url/'; // Default: '/'
	const SITE_HTTP = 'http://localhost/';
	
	// const PATH_NAMES = '';
	// const SITE_HTTPS = 'https://bringmeapp.com.br/';
	
	const PASSWORD_HASH = '';
	const APNS_PASSPHRASE = '';
	const APNS_PASSPHRASE_DEV = '';
	
	const PAGARME_API_KEY = '';
	const GOOGLE_PLACE_ID = '';
	
	## ADVANCE CONFIG
	const ENV = 'development';// Environment: production | development | adjusting
	const USERS_PASSWORD_HASH = '1234567890987654321'; //sah1() = 5eda471047663e1597b03ae09d03d98b20affabe
}

class CMSConfig
{
	const APP_NAME = 'Teste';
	const USERS_TABLE_NAME = 'users';
	const USERS_USERNAME_COLUMN = 'email';
	const USERS_PASSWORD_COLUMN = 'password';
	const USERS_PASSWORD_HASH = '1234567890987654321';
	const USERS_SESSION_VAR = 'upteste_login_id';

	const ALLOW_DOMAIN = 'http://upcms.net';

	const PATH_NAME = '';

	const FORM_USERNAME_PLACEHOLDER = 'E-mail';
	const FORM_PASSWORD_PLACEHOLDER = 'Senha';

	const CONNECTION_SESSION_VAR = 'teste_connection_id';
	const CONNECTION_HASH = 'e45pu87t5%$4rij';
	const CONNECTION_PATH = '//localhost/cofreonline/wscms/';

	const BACK_CONNECTION_PATH = '//localhost/cofreonline/admin/';
	
	const CMS_DIR = 'admin';
}

?>