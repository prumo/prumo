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
    private $connection;
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
    function __construct($ident, $username, $password)
    {
        global $pConnectionPrumo;
        
        $this->ident = $ident;
        $this->connection = $pConnectionPrumo;
        $this->logged = false;
        $this->err = '';
        
        if ($username == '') {
            
            if (isset($_SESSION[$ident.'_prumoUserName'])) {
                $this->username = $_SESSION[$ident.'_prumoUserName'];
            }
            
            if (isset($_SESSION[$ident.'_prumoUserPassword'])) {
                $this->password = $_SESSION[$ident.'_prumoUserPassword'];
            }
        } else {
            $this->username = $username;
            $this->password = $password;
        }
        
        $this->session = !empty($this->username);
    }
    
    /**
     * Retorna o array de erros de login
     *
     * @return array
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
     * Faz login
     *
     * @return boolean
     */
    public function login()
    {
        if ($this->connection->getConnection()) {
            
            $sql = 'SELECT password,fullname FROM '.$this->connection->getSchema().'syslogin '.
                   ' WHERE enabled='.pFormatSql(true,'boolean').
                   '   AND username='.pFormatSql($this->username,'string').';';
            $authentication = $this->connection->fetchAssoc($sql);
            
            $dbPassword = (isset($authentication['password'])) ? $authentication['password'] : '';
            
            $this->fullName = (isset($authentication['fullname'])) ? $authentication['fullname'] : $this->fullName = '';
             
            if ($dbPassword != '' && sodium_crypto_pwhash_str_verify($dbPassword, $this->password) === true) {
                $this->logged = true;
                $this->sessionRegister();
            } else {
                $this->logged = false;
                $this->err = _('Usuário ou senha incorreta');
            }
            sodium_memzero($this->password);
            $this->password = $dbPassword;
        } else {
            
            $this->logged = false;
            $this->err = $this->connection->getErr();
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
        $_SESSION[$this->ident.'_prumoUserName'] = $this->username;
        $_SESSION[$this->ident.'_prumoFullName'] = $this->fullName;
        $_SESSION[$this->ident.'_prumoUserPassword'] = $this->password;
        $_SESSION[$this->ident.'_config'] = $GLOBALS['pConfig']['appPath'].'/prumo.php';
        
        $this->session = (isset($_SESSION[$this->ident.'_prumoUserName']) and isset($_SESSION[$this->ident.'_prumoUserPassword']));
        
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
        
        $this->session = (isset($_SESSION[$this->ident.'_prumoUserName']) and isset($_SESSION[$this->ident.'_prumoUserPassword']));
        
        return $this->isSession();
    }
}

