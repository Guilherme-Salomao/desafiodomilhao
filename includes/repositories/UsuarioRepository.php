<?php

declare (strict_types = 1);

/* =========================================================
   CONEXÃO
========================================================= */

require_once __DIR__ . '/../conexao.php';

/* =========================================================
   REPOSITORY DE USUÁRIOS
========================================================= */

class UsuarioRepository
{
    private PDO $pdo;

    /* =====================================================
       CONSTRUTOR
    ===================================================== */

    public function __construct()
    {
        $this->pdo = conectarBanco();
    }

    /* =====================================================
       VERIFICA SE LOGIN JÁ EXISTE
    ===================================================== */

    public function loginExiste(
        string $login
    ): bool {

        $sql = "
            SELECT id
            FROM usuarios
            WHERE login = :login
            LIMIT 1
        ";

        $stmt =
        $this->pdo->prepare(
            $sql
        );

        $stmt->bindValue(
            ':login',
            $login,
            PDO::PARAM_STR
        );

        $stmt->execute();

        return (bool) $stmt->fetch();
    }

    /* =====================================================
       BUSCA USUÁRIO PELO LOGIN
    ===================================================== */

    public function buscarPorLogin(
        string $login
    ): ?array {

        $sql = "
            SELECT
                id,
                nome,
                login,
                senha_hash,
                ativo,
                ultimo_login_em,
                criado_em,
                atualizado_em
            FROM usuarios
            WHERE login = :login
            LIMIT 1
        ";

        $stmt =
        $this->pdo->prepare(
            $sql
        );

        $stmt->bindValue(
            ':login',
            $login,
            PDO::PARAM_STR
        );

        $stmt->execute();

        $usuario =
        $stmt->fetch();

        return $usuario ?: null;
    }

    /* =====================================================
       BUSCA USUÁRIO PELO ID
    ===================================================== */

    public function buscarPorId(
        int $usuarioId
    ): ?array {

        $sql = "
            SELECT
                id,
                nome,
                login,
                ativo,
                ultimo_login_em,
                criado_em,
                atualizado_em
            FROM usuarios
            WHERE id = :usuario_id
            LIMIT 1
        ";

        $stmt =
        $this->pdo->prepare(
            $sql
        );

        $stmt->bindValue(
            ':usuario_id',
            $usuarioId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $usuario =
        $stmt->fetch();

        return $usuario ?: null;
    }

    /* =====================================================
       BUSCA ÁREAS ATIVAS PELOS IDS

       Usado no cadastro para garantir que as áreas
       realmente existem e estão ativas.
    ===================================================== */

    public function buscarAreasAtivasPorIds(
        array $ids
    ): array {

        if (empty($ids)) {
            return [];
        }

        /* =================================================
           NORMALIZA IDS
        ================================================= */

        $idsNormalizados = [];

        foreach ($ids as $id) {

            $id =
            (int) $id;

            if ($id > 0) {

                $idsNormalizados[] =
                    $id;

            }

        }

        $idsNormalizados =
            array_values(
            array_unique(
                $idsNormalizados
            )
        );

        if (empty($idsNormalizados)) {
            return [];
        }

        /* =================================================
           MARCADORES
        ================================================= */

        $marcadores =
            implode(
            ',',
            array_fill(
                0,
                count($idsNormalizados),
                '?'
            )
        );

        /* =================================================
           CONSULTA
        ================================================= */

        $sql = "
            SELECT
                id,
                nome,
                slug,
                descricao,
                icone,
                ativo,
                ordem
            FROM areas
            WHERE ativo = 1
              AND id IN ($marcadores)
            ORDER BY
                ordem ASC,
                nome ASC
        ";

        $stmt =
        $this->pdo->prepare(
            $sql
        );

        /* =================================================
           BIND DOS IDS
        ================================================= */

        foreach (
            $idsNormalizados as $indice => $id
        ) {

            $stmt->bindValue(
                $indice + 1,
                $id,
                PDO::PARAM_INT
            );

        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /* =====================================================
       CRIA SOLICITAÇÃO DE CADASTRO

       Cria:
       - usuário
       - ativo = 0
       - vínculos em usuario_areas

       Tudo dentro de uma transação.
    ===================================================== */

    public function criarSolicitacao(
        string $nome,
        string $login,
        string $senhaHash,
        array $areasIds
    ): int {

        try {

            /* =================================================
               INICIA TRANSAÇÃO
            ================================================= */

            $this->pdo->beginTransaction();

            /* =================================================
               CRIA USUÁRIO

               O usuário SEMPRE nasce inativo.
            ================================================= */

            $sqlUsuario = "
                INSERT INTO usuarios (
                    nome,
                    login,
                    senha_hash,
                    ativo
                )
                VALUES (
                    :nome,
                    :login,
                    :senha_hash,
                    0
                )
            ";

            $stmtUsuario =
            $this->pdo->prepare(
                $sqlUsuario
            );

            $stmtUsuario->bindValue(
                ':nome',
                $nome,
                PDO::PARAM_STR
            );

            $stmtUsuario->bindValue(
                ':login',
                $login,
                PDO::PARAM_STR
            );

            $stmtUsuario->bindValue(
                ':senha_hash',
                $senhaHash,
                PDO::PARAM_STR
            );

            $stmtUsuario->execute();

            /* =================================================
               ID DO NOVO USUÁRIO
            ================================================= */

            $usuarioId =
            (int) $this->pdo->lastInsertId();

            if ($usuarioId <= 0) {

                throw new RuntimeException(
                    'Não foi possível identificar o usuário criado.'
                );

            }

            /* =================================================
               VINCULA ÁREAS
            ================================================= */

            $sqlArea = "
                INSERT INTO usuario_areas (
                    usuario_id,
                    area_id
                )
                VALUES (
                    :usuario_id,
                    :area_id
                )
            ";

            $stmtArea =
            $this->pdo->prepare(
                $sqlArea
            );

            foreach ($areasIds as $areaId) {

                $areaId =
                (int) $areaId;

                if ($areaId <= 0) {
                    continue;
                }

                $stmtArea->bindValue(
                    ':usuario_id',
                    $usuarioId,
                    PDO::PARAM_INT
                );

                $stmtArea->bindValue(
                    ':area_id',
                    $areaId,
                    PDO::PARAM_INT
                );

                $stmtArea->execute();

            }

            /* =================================================
               CONFIRMA TRANSAÇÃO
            ================================================= */

            $this->pdo->commit();

            return $usuarioId;

        } catch (Throwable $erro) {

            /* =================================================
               DESFAZ TRANSAÇÃO EM CASO DE ERRO
            ================================================= */

            if ($this->pdo->inTransaction()) {

                $this->pdo->rollBack();

            }

            throw $erro;

        }
    }

    /* =====================================================
       LISTA ÁREAS AUTORIZADAS DO USUÁRIO
    ===================================================== */

    public function listarAreasDoUsuario(
        int $usuarioId
    ): array {

        $sql = "
            SELECT
                a.id,
                a.nome,
                a.slug,
                a.descricao,
                a.icone,
                a.ativo,
                a.ordem
            FROM usuario_areas ua

            INNER JOIN areas a
                ON a.id = ua.area_id

            WHERE ua.usuario_id = :usuario_id
              AND a.ativo = 1

            ORDER BY
                a.ordem ASC,
                a.nome ASC
        ";

        $stmt =
        $this->pdo->prepare(
            $sql
        );

        $stmt->bindValue(
            ':usuario_id',
            $usuarioId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /* =====================================================
       VERIFICA SE USUÁRIO TEM ACESSO A UMA ÁREA
    ===================================================== */

    public function usuarioPossuiArea(
        int $usuarioId,
        int $areaId
    ): bool {

        $sql = "
            SELECT 1
            FROM usuario_areas ua

            INNER JOIN areas a
                ON a.id = ua.area_id

            WHERE ua.usuario_id = :usuario_id
              AND ua.area_id = :area_id
              AND a.ativo = 1

            LIMIT 1
        ";

        $stmt =
        $this->pdo->prepare(
            $sql
        );

        $stmt->bindValue(
            ':usuario_id',
            $usuarioId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':area_id',
            $areaId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    /* =====================================================
       ATUALIZA ÚLTIMO LOGIN
    ===================================================== */

    public function atualizarUltimoLogin(
        int $usuarioId
    ): void {

        $sql = "
            UPDATE usuarios
            SET ultimo_login_em = NOW()
            WHERE id = :usuario_id
        ";

        $stmt =
        $this->pdo->prepare(
            $sql
        );

        $stmt->bindValue(
            ':usuario_id',
            $usuarioId,
            PDO::PARAM_INT
        );

        $stmt->execute();
    }

    /* =====================================================
       VERIFICA SE USUÁRIO ESTÁ ATIVO
    ===================================================== */

    public function estaAtivo(
        int $usuarioId
    ): bool {

        $sql = "
            SELECT ativo
            FROM usuarios
            WHERE id = :usuario_id
            LIMIT 1
        ";

        $stmt =
        $this->pdo->prepare(
            $sql
        );

        $stmt->bindValue(
            ':usuario_id',
            $usuarioId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $ativo =
        $stmt->fetchColumn();

        return (int) $ativo === 1;
    }
}
