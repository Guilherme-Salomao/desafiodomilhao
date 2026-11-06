<?php
declare (strict_types = 1);
/* ============================================================
   DESAFIO DO MILHÃO
   REPOSITORY - ÁREAS
============================================================ */
require_once __DIR__ . '/../conexao.php';
class AreaRepository
{
    private PDO $pdo;
    /* ========================================================
       CONSTRUTOR
    ======================================================== */
    public function __construct()
    {
        $this->pdo = conectarBanco();
    }
    /* ========================================================
       LISTAR ÁREAS ATIVAS
    ======================================================== */
    public function listarAtivas(): array
    {
        $sql = "
            SELECT
                id,
                nome,
                slug,
                descricao,
                icone,
                ordem
            FROM areas
            WHERE ativo = 1
            ORDER BY
                ordem ASC,
                nome ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    /* ========================================================
       BUSCAR ÁREA POR SLUG
    ======================================================== */
    public function buscarPorSlug(string $slug): ?array
    {
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
            WHERE slug = :slug
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':slug' => $slug,
        ]);
        $area = $stmt->fetch();
        return $area ?: null;
    }
    /* ========================================================
       BUSCAR ÁREA ATIVA POR SLUG
    ======================================================== */
    public function buscarAtivaPorSlug(string $slug): ?array
    {
        $sql = "
            SELECT
                id,
                nome,
                slug,
                descricao,
                icone,
                ordem
            FROM areas
            WHERE slug = :slug
              AND ativo = 1
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':slug' => $slug,
        ]);
        $area = $stmt->fetch();
        return $area ?: null;
    }
    /* ========================================================
       BUSCAR ÁREA POR ID
    ======================================================== */
    public function buscarPorId(int $id): ?array
    {
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
            WHERE id = :id
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );
        $stmt->execute();
        $area = $stmt->fetch();
        return $area ?: null;
    }
}
