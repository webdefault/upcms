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
	const DATABASE_HOST = '127.0.0.1'; // Default: localhost
	const DATABASE_PORT = '3306'; // Default: 3306
	const DATABASE_USER = 'root';
	const DATABASE_PASS = '';
	const DATABASE_NAME = 'kldit-example';
	
	const DATABASE_SSL		= false;
	const DATABASE_SSL_CA	= NULL;//"mysql-ca.pem";
	const DATABASE_SSL_KEY	= NULL;//"mysql-client-key.pem";
	const DATABASE_SSL_CERT	= NULL;//"mysql-client-cert.pem";
	
	const SYSTEM_TABLE_PREFIX = '';
	
	const DEFAULT_APPLICATION_DIR = 'app';
	const APPLICATION_STARTER = 'starter';
	
	// Website custom
	// const DEFAULT_KEYWORDS = '';
	// const DEFAULT_META_DESCRIPTION = '';
	// const DEFAULT_NAME_AUTHOR = '';
	// const SITE_NAME = '';
	
	const FULL_APP_PATH = '/Users/orlandoleite/Sites/kldit-example/';
	const STORAGE_PATH = 'storage';
	const BASE_SITE = 'http://localhost/kldit-example/';
	
	## SITE CONFIG
	const PATH_NAME = 'kldit-example/'; // Default: '/'
	const SITE_HTTP = 'http://localhost/';
	
	const PATH_NAMES = '';
	const SITE_HTTPS = '';
	
	## CONFIG ##
	// Environment: production | development | adjusting
	const ENV = 'development';
}

class CMSConfig
{
	const APP_NAME = 'Teste';
	const USERS_TABLE_NAME = 'users';
	const USERS_NAME_COLUMN = 'name';
	const USERS_USERNAME_COLUMN = 'email';
	const USERS_PASSWORD_COLUMN = 'password';
	const USERS_SESSION_VAR = 'upteste_login_id';

	const ALLOW_DOMAIN = 'http://cloud.bugios.com';
	
	const DEBUG = true;

	const PATH_NAME = '';

	const FORM_USERNAME_PLACEHOLDER = 'E-mail';
	const FORM_PASSWORD_PLACEHOLDER = 'Senha';

	const CONNECTION_SESSION_VAR = 'teste_connection_id';
	
	const CONNECTION_HASH = '12345678';

	const CMS_DIR = 'admin';
}

?>