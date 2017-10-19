<?php

/* *********************************************************************
 *
 *  Prumo Framework para PHP é um framework vertical para
 *  desenvolvimento rápido de sistemas de informação web.
 *  Copyright (C) 2010 Emerson Casas Salvador <salvaemerson@gmail.com>
 *  e Odair Rubleski <orubleski@gmail.com>
 *
 *  This file is part of Prumo.
 *
 *  Prumo is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3, or (at your option)
 *  any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 * ******************************************************************* */

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
            $autentication = $this->connection->sql2Array($sql);
            
            $password = (isset($autentication[0]['password'])) ? $autentication[0]['password'] : '';
            
            $this->fullName = (isset($autentication[0]['fullname'])) ? $autentication[0]['fullname'] : $this->fullName = '';
             
            if ($password != '' and $this->password == $password) {
                $this->logged = true;
                $this->sessionRegister();
            } else {
                $this->logged = false;
                $this->err = _('Usuário ou senha incorreta');
            }
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

