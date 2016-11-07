<?php namespace Genetsis\core\OAuth\Collections;

class AuthMethods {
    const GRANT_TYPE_AUTH_CODE = 'authorization_code';
    const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';
    const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    const GRANT_TYPE_VALIDATE_BEARER = 'urn:es.cocacola:oauth2:grant_type:validate_bearer';
    const GRANT_TYPE_EXCHANGE_SESSION = 'urn:es.cocacola:oauth2:grant_type:exchange_session';
}