<?php
/**
 * Copyright (c) 2010 Emerson Casas Salvador <salvaemerson@gmail.com> e Odair Rubleski <orubleski@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the “Software”), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * PrumoLogin controla o login do sistema
 */
class PrumoLogin
{
    private $password;
    private $fullName;
    private $logged;
    private $ident;
    private $pConnection;
    private $username;
    private $err;
    private $session;
    
    /**
     * Construtor da classe PrumoLogin
     *
     * @param $ident string: identificação do sistema (para o session)
     * @param $username string: nome do usuário
     * @param $password string: senha codificada
     */
    public function __construct($ident, $username, $password)
    {
        global $pConnectionPrumo;
        
        $this->ident = $ident;
        $this->pConnection = $pConnectionPrumo;
        $this->logged = false;
        $this->err = '';
        
        if ($username == '') {
            if (isset($_SESSION[$ident.'_prumoUserName'])) {
                $this->username = $_SESSION[$ident.'_prumoUserName'];
            }
        } else {
            $this->username = $username;
            $this->password = $password;
        }
        
        $this->session = ! empty($this->username);
    }
    
    /**
     * Retorna o erro de login
     *
     * @return string
     */
    public function getErr()
    {
        return $this->err;
    }
    
    /**
     * Verifica se tem usuário na sessão
     *
     * @return boolean
     */
    public function isSession()
    {
        return $this->session;
    }
    
    /**
     * Checa determinada senha com o registro no banco de dados
     *
     * @return bool
     */
    public function checkPassword()
    {
        $sql = 'SELECT' . PHP_EOL
             . '    password' . PHP_EOL
             . 'FROM ' . $this->pConnection->getSchema() . 'syslogin' . PHP_EOL
             . 'WHERE enabled=' . pFormatSql(true, 'boolean') . PHP_EOL
             . 'AND username=' . pFormatSql($this->username, 'string') . ';';
        $dbPassword = $this->pConnection->sqlQuery($sql);
        
        if (empty($dbPassword) || empty($this->password)) {
            return false;
        }
        
        $check = function_exists('sodium_crypto_pwhash_str_verify') ? sodium_crypto_pwhash_str_verify($dbPassword, $this->password) : false;
        
        // fallback para md5
        if ($check === false) {
            $check = md5($this->password) === $dbPassword;
        }
        
        if (function_exists('sodium_memzero')) {
            sodium_memzero($this->password);
            sodium_memzero($dbPassword);
            sodium_memzero($sql);
        }
        
        return $check;
    }
    
    /**
     * Altera a senha
     *
     * @param $newPassword string: nova senha
     *
     * @return bool
     */
    public function changePassword(string $newPassword)
    {
        if (function_exists('sodium_crypto_pwhash_str')) {
            $encPassword = sodium_crypto_pwhash_str(
                $newPassword,
                SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
                SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
            );
        } else {
            $encPassword = md5($newPassword);
        }
        
        $sql = 'UPDATE ' . $this->pConnection->getSchema() . 'syslogin' . PHP_EOL
             . 'SET "password"=' . pFormatSql($encPassword, 'string') . PHP_EOL
             . 'WHERE username=' . pFormatSql($this->username, 'string') . ';';
        $query = $this->pConnection->sqlQuery($sql);
        
        if (function_exists('sodium_memzero')) {
            sodium_memzero($newPassword);
            sodium_memzero($encPassword);
            sodium_memzero($sql);
        }
        
        return $query === false ? false : true;
    }
    
    /**
     * Faz login
     *
     * @return boolean
     */
    public function login()
    {
        if ($this->pConnection->getConnection() === false) {
            $this->err = $this->pConnection->getErr();
            return false;
        }
        
        $this->logged = $this->checkPassword();
        
        if ($this->logged) {
            $this->sessionRegister();
        } else {
            $this->err = _('Usuário ou senha incorreta');
        }
        
        return $this->logged;
    }
    
    /**
     * Faz logoff
     *
     * @return boolean
     */
    public function logoff()
    {
        return $this->sessionUnRegister();
    }
    
    /**
     * Registra a sessão
     *
     * @return boolean: estado da sessão
     */
    private function sessionRegister()
    {
        $sql = 'SELECT' . PHP_EOL
             . '    fullname' . PHP_EOL
             . 'FROM ' . $this->pConnection->getSchema() . 'syslogin' . PHP_EOL
             . 'WHERE username=' . pFormatSql($this->username, 'string') . ';';
        $this->fullName = $this->pConnection->sqlQuery($sql);
        
        $_SESSION[$this->ident.'_prumoUserName'] = $this->username;
        $_SESSION[$this->ident.'_prumoFullName'] = $this->fullName;
        $_SESSION[$this->ident.'_prumoUserPassword'] = $this->password;
        $_SESSION[$this->ident.'_config'] = $GLOBALS['pConfig']['appPath'].'/prumo.php';
        
        $this->session = (isset($_SESSION[$this->ident.'_prumoUserName']) && isset($_SESSION[$this->ident.'_prumoUserPassword']));
        
        return $this->isSession();
    }
    
    /**
     * Limpa a sessão
     *
     * @return boolean: estado da sessão
     */
    private function sessionUnRegister()
    {
        if (isset($_SESSION[$this->ident.'_prumoUserName'])) {
            unset($_SESSION[$this->ident.'_prumoUserName']);
        }
        
        if (isset($_SESSION[$this->ident.'_prumoUserPassword'])) {
            unset($_SESSION[$this->ident.'_prumoUserPassword']);
        }
        
        if (isset($_SESSION[$this->ident.'_config'])) {
            unset($_SESSION[$this->ident.'_config']);
        }
        
        session_write_close();
        
        $this->session = (isset($_SESSION[$this->ident.'_prumoUserName']) && isset($_SESSION[$this->ident.'_prumoUserPassword']));
        
        return $this->isSession();
    }
}

