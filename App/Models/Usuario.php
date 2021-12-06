<?php

  namespace App\Models;

  use MF\Model\Model;

  class Usuario extends Model {
    private $id;
    private $nome;
    private $email;
    private $senha;

    public function __get($attr) {
      return $this->$attr;
    }

    public function __set($attr, $value) {
      $this->$attr = $value;      
    }

    public function salvar() {
      $query = "insert into usuarios(nome, email, senha) values(?, ?, ?)";
      $stmt = $this->db->prepare($query);
      $stmt->bindValue(1, $this->__get('nome'));
      $stmt->bindValue(2, $this->__get('email'));
      $stmt->bindValue(3, $this->__get('senha'));
      $stmt->execute();

      return $this;
    }

    public function validarCadastro() {
      $valido = true;

      if (strlen($this->__get('nome') < 3 || strlen($this->__get('email')) < 3 || strlen($this->__get('senha')) < 3)) {
        $valido = false;
      }

      return $valido;
    }

    public function getUsuarioPorEmail() {
      $query = "select nome, email from usuarios where email = ?";
      $stmt = $this->db->prepare($query);
      $stmt->bindValue(1, $this->__get('email'));
      $stmt->execute();

      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function autenticar() {
      $query = "select id, nome, email from usuarios where email = ? and senha = ?";
      $stmt = $this->db->prepare($query);
      $stmt->bindValue(1, $this->__get('email'));
      $stmt->bindValue(2, $this->__get('senha'));
      $stmt->execute();

      $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

      if(!empty($usuario['id']) && !empty($usuario['nome'])) {
        $this->__set('id', $usuario['id']);
        $this->__set('nome', $usuario['nome']);
      }

      return $this;
    }

    public function getAll() {
      $query = "Select 
                  id, nome, email, (select 
                                      count(*) 
                                    from 
                                      usuarios_seguidores as us
                                    where
                                      us.id_usuario = ? and us.id_usuario_seguindo = u.id
                                    ) as seguindo_sn
                from 
                  usuarios as u
                where 
                  u.nome like ? and u.id != ?";
      $stmt = $this->db->prepare($query);
      $stmt->bindValue(1, $this->__get('id'));
      $stmt->bindValue(2, '%'.$this->__get('nome').'%');
      $stmt->bindValue(3, $this->__get('id'));
      $stmt->execute();

      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function seguirUsuario($id_usuario_seguindo) {
      $query = "insert into usuarios_seguidores(id_usuario, id_usuario_seguindo) values(?, ?)";
      $stmt = $this->db->prepare($query);
      $stmt->bindValue(1, $this->__get('id'));
      $stmt->bindValue(2, $id_usuario_seguindo);
      $stmt->execute();

      return true;
    }

    public function deixarSeguirUsuario($id_usuario_seguindo) {
      $query = "delete from usuarios_seguidores where id_usuario = ? and id_usuario_seguindo = ?";
      $stmt = $this->db->prepare($query);
      $stmt->bindValue(1, $this->__get('id'));
      $stmt->bindValue(2, $id_usuario_seguindo);
      $stmt->execute();

      return true;
    }

    public function getInfoUsuario() {
      $query = "select nome from usuarios where id = ?";
      $stmt = $this->db->prepare($query);
      $stmt->bindValue(1, $this->__get('id'));
      $stmt->execute();

      return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getTotalTweets() {
      $query = "select count(*) as total_tweets from tweets where id_usuario = ?";
      $stmt = $this->db->prepare($query);
      $stmt->bindValue(1, $this->__get('id'));
      $stmt->execute();

      return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getTotalSeguindo() {
      $query = "select count(*) as total_seguindo from usuarios_seguidores where id_usuario = ?";
      $stmt = $this->db->prepare($query);
      $stmt->bindValue(1, $this->__get('id'));
      $stmt->execute();

      return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getTotalSeguidores() {
      $query = "select count(*) as total_seguidores from usuarios_seguidores where id_usuario_seguindo = ?";
      $stmt = $this->db->prepare($query);
      $stmt->bindValue(1, $this->__get('id'));
      $stmt->execute();

      return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
  }
?>