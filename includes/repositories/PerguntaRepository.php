<?php

declare (strict_types = 1);

require_once __DIR__ . '/../conexao.php';

class PerguntaRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = conectarBanco();
    }

    public function listarPorUsuario(
        int $usuarioId,
        ?int $areaId = null,
        ?string $dificuldade = null,
        ?int $ativo = null,
        ?string $busca = null,
        ?int $limite = null,
        int $offset = 0
    ): array {

        $sql = "
            SELECT
                p.id,
                p.area_id,
                p.usuario_id,
                p.categoria_id,
                p.pergunta,
                p.explicacao,
                p.imagem,
                p.dificuldade,
                p.ativo,
                p.criado_em,
                p.atualizado_em,

                a.nome AS area_nome,
                a.slug AS area_slug,
                a.icone AS area_icone,

                u.nome AS usuario_nome

            FROM perguntas p

            INNER JOIN areas a
                ON a.id = p.area_id

            INNER JOIN usuarios u
                ON u.id = p.usuario_id

            INNER JOIN usuario_areas ua
                ON ua.area_id = p.area_id
               AND ua.usuario_id = :usuario_id

            WHERE a.ativo = 1
        ";

        $parametros = [
            ':usuario_id' => $usuarioId,
        ];

        if (
            $areaId !== null &&
            $areaId > 0
        ) {

            $sql .= "
                AND p.area_id = :area_id
            ";

            $parametros[':area_id'] =
                $areaId;
        }

        if (
            $dificuldade !== null &&
            in_array(
                $dificuldade,
                [
                    'facil',
                    'medio',
                    'dificil',
                ],
                true
            )
        ) {

            $sql .= "
                AND p.dificuldade = :dificuldade
            ";

            $parametros[':dificuldade'] =
                $dificuldade;
        }

        if (
            $ativo !== null &&
            in_array(
                $ativo,
                [0, 1],
                true
            )
        ) {

            $sql .= "
                AND p.ativo = :ativo
            ";

            $parametros[':ativo'] =
                $ativo;
        }

        if (
            $busca !== null &&
            trim($busca) !== ''
        ) {

            $sql .= "
                AND p.pergunta LIKE :busca
            ";

            $parametros[':busca'] =
            '%' . trim($busca) . '%';
        }

        $sql .= "
            ORDER BY
                CASE p.dificuldade
                    WHEN 'facil' THEN 1
                    WHEN 'medio' THEN 2
                    WHEN 'dificil' THEN 3
                    ELSE 4
                END ASC,
                a.nome ASC,
                p.id DESC
        ";

        if ($limite !== null && $limite > 0) {

            $sql .= "
                LIMIT :limite
                OFFSET :offset
            ";

            $parametros[':limite'] = $limite;

            $parametros[':offset'] = max(0, $offset);
        }

        $stmt =
        $this->pdo->prepare(
            $sql
        );

        foreach (
            $parametros as $chave => $valor
        ) {

            if (
                $chave === ':usuario_id' ||
                $chave === ':area_id' ||
                $chave === ':ativo' ||
                $chave === ':limite' ||
                $chave === ':offset'
            ) {

                $stmt->bindValue(
                    $chave,
                    (int) $valor,
                    PDO::PARAM_INT
                );

            } else {

                $stmt->bindValue(
                    $chave,
                    (string) $valor,
                    PDO::PARAM_STR
                );
            }
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contarFiltradasPorUsuario(
        int $usuarioId,
        ?int $areaId = null,
        ?string $dificuldade = null,
        ?int $ativo = null,
        ?string $busca = null
    ): int {

        $sql = "
            SELECT COUNT(DISTINCT p.id)

            FROM perguntas p

            INNER JOIN areas a
                ON a.id = p.area_id

            INNER JOIN usuario_areas ua
                ON ua.area_id = p.area_id
               AND ua.usuario_id = :usuario_id

            WHERE a.ativo = 1
        ";

        $parametros = [
            ':usuario_id' => $usuarioId,
        ];

        if ($areaId !== null && $areaId > 0) {

            $sql .= "
                AND p.area_id = :area_id
            ";

            $parametros[':area_id'] = $areaId;
        }

        if (
            $dificuldade !== null &&
            in_array($dificuldade, ['facil', 'medio', 'dificil'], true)
        ) {

            $sql .= "
                AND p.dificuldade = :dificuldade
            ";

            $parametros[':dificuldade'] = $dificuldade;
        }

        if ($ativo !== null && in_array($ativo, [0, 1], true)) {

            $sql .= "
                AND p.ativo = :ativo
            ";

            $parametros[':ativo'] = $ativo;
        }

        if ($busca !== null && trim($busca) !== '') {

            $sql .= "
                AND p.pergunta LIKE :busca
            ";

            $parametros[':busca'] = '%' . trim($busca) . '%';
        }

        $stmt = $this->pdo->prepare($sql);

        foreach ($parametros as $chave => $valor) {

            if (
                $chave === ':usuario_id' ||
                $chave === ':area_id' ||
                $chave === ':ativo'
            ) {
                $stmt->bindValue($chave, (int) $valor, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($chave, (string) $valor, PDO::PARAM_STR);
            }
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function contarPorUsuario(
        int $usuarioId
    ): int {

        $sql = "
            SELECT COUNT(DISTINCT p.id)

            FROM perguntas p

            INNER JOIN usuario_areas ua
                ON ua.area_id = p.area_id
               AND ua.usuario_id = :usuario_id

            INNER JOIN areas a
                ON a.id = p.area_id

            WHERE a.ativo = 1
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

        return (int) $stmt->fetchColumn();
    }

    public function contarAtivasPorUsuario(
        int $usuarioId
    ): int {

        $sql = "
            SELECT COUNT(DISTINCT p.id)

            FROM perguntas p

            INNER JOIN usuario_areas ua
                ON ua.area_id = p.area_id
               AND ua.usuario_id = :usuario_id

            INNER JOIN areas a
                ON a.id = p.area_id

            WHERE a.ativo = 1
              AND p.ativo = 1
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

        return (int) $stmt->fetchColumn();
    }

    public function contarInativasPorUsuario(
        int $usuarioId
    ): int {

        $sql = "
            SELECT COUNT(DISTINCT p.id)

            FROM perguntas p

            INNER JOIN usuario_areas ua
                ON ua.area_id = p.area_id
               AND ua.usuario_id = :usuario_id

            INNER JOIN areas a
                ON a.id = p.area_id

            WHERE a.ativo = 1
              AND p.ativo = 0
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

        return (int) $stmt->fetchColumn();
    }

    public function buscarPorIdAutorizado(
        int $perguntaId,
        int $usuarioId
    ): ?array {

        $sql = "
            SELECT
                p.id, p.area_id, p.usuario_id, p.pergunta, p.explicacao,
                p.dificuldade, p.ativo, p.criado_em, p.atualizado_em,
                a.nome AS area_nome, a.icone AS area_icone,
                u.nome AS usuario_nome
            FROM perguntas p
            INNER JOIN areas a ON a.id = p.area_id
            INNER JOIN usuarios u ON u.id = p.usuario_id
            INNER JOIN usuario_areas ua
                ON ua.area_id = p.area_id
               AND ua.usuario_id = :usuario_id
            WHERE p.id = :pergunta_id
              AND a.ativo = 1
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':pergunta_id', $perguntaId, PDO::PARAM_INT);
        $stmt->execute();

        $pergunta = $stmt->fetch();

        if (! $pergunta) {
            return null;
        }

        $sqlAlternativas = "
            SELECT letra, texto, correta, ordem
            FROM alternativas
            WHERE pergunta_id = :pergunta_id
            ORDER BY ordem ASC
        ";

        $stmtAlternativas = $this->pdo->prepare($sqlAlternativas);
        $stmtAlternativas->bindValue(':pergunta_id', $perguntaId, PDO::PARAM_INT);
        $stmtAlternativas->execute();

        $pergunta['alternativas'] = $stmtAlternativas->fetchAll();

        return $pergunta;
    }

    public function atualizar(
        int $perguntaId,
        int $areaId,
        string $pergunta,
        string $dificuldade,
        string $explicacao,
        int $ativo,
        array $alternativas,
        string $respostaCorreta
    ): void {

        try {
            $this->pdo->beginTransaction();

            $sqlPergunta = "
                UPDATE perguntas
                SET area_id = :area_id,
                    pergunta = :pergunta,
                    dificuldade = :dificuldade,
                    explicacao = :explicacao,
                    ativo = :ativo
                WHERE id = :pergunta_id
            ";

            $stmtPergunta = $this->pdo->prepare($sqlPergunta);
            $stmtPergunta->execute([
                ':area_id' => $areaId,
                ':pergunta' => $pergunta,
                ':dificuldade' => $dificuldade,
                ':explicacao' => $explicacao,
                ':ativo' => $ativo,
                ':pergunta_id' => $perguntaId,
            ]);

            $stmtExcluir = $this->pdo->prepare(
                'DELETE FROM alternativas WHERE pergunta_id = :pergunta_id'
            );
            $stmtExcluir->bindValue(':pergunta_id', $perguntaId, PDO::PARAM_INT);
            $stmtExcluir->execute();

            $stmtAlternativa = $this->pdo->prepare(" 
                INSERT INTO alternativas (pergunta_id, letra, texto, correta, ordem)
                VALUES (:pergunta_id, :letra, :texto, :correta, :ordem)
            ");

            $ordens = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4];

            foreach ($alternativas as $letra => $texto) {
                $stmtAlternativa->execute([
                    ':pergunta_id' => $perguntaId,
                    ':letra' => $letra,
                    ':texto' => $texto,
                    ':correta' => $letra === $respostaCorreta ? 1 : 0,
                    ':ordem' => $ordens[$letra],
                ]);
            }

            $this->pdo->commit();

        } catch (Throwable $erro) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $erro;
        }
    }

    /* =====================================================
       IMPORTA QUESTÕES JÁ VALIDADAS
    ===================================================== */

    public function importarEmLote(int $usuarioId, array $questoes): int
    {
        try {
            $this->pdo->beginTransaction();

            $stmtPergunta = $this->pdo->prepare("
                INSERT INTO perguntas (
                    area_id, usuario_id, pergunta, dificuldade,
                    explicacao, imagem, ativo
                ) VALUES (
                    :area_id, :usuario_id, :pergunta, :dificuldade,
                    :explicacao, NULL, :ativo
                )
            ");

            $stmtAlternativa = $this->pdo->prepare("
                INSERT INTO alternativas (
                    pergunta_id, letra, texto, correta, ordem
                ) VALUES (
                    :pergunta_id, :letra, :texto, :correta, :ordem
                )
            ");

            $ordens = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4];

            foreach ($questoes as $questao) {
                $stmtPergunta->execute([
                    ':area_id' => $questao['area_id'],
                    ':usuario_id' => $usuarioId,
                    ':pergunta' => $questao['pergunta'],
                    ':dificuldade' => $questao['dificuldade'],
                    ':explicacao' => $questao['explicacao'],
                    ':ativo' => $questao['ativo'],
                ]);

                $perguntaId = (int) $this->pdo->lastInsertId();

                foreach ($questao['alternativas'] as $letra => $texto) {
                    $stmtAlternativa->execute([
                        ':pergunta_id' => $perguntaId,
                        ':letra' => $letra,
                        ':texto' => $texto,
                        ':correta' => $letra === $questao['resposta_correta'] ? 1 : 0,
                        ':ordem' => $ordens[$letra],
                    ]);
                }
            }

            $this->pdo->commit();
            return count($questoes);
        } catch (Throwable $erro) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $erro;
        }
    }

    public function excluir(int $perguntaId): void
    {
        try {
            $this->pdo->beginTransaction();

            $stmtAlternativas = $this->pdo->prepare(
                'DELETE FROM alternativas WHERE pergunta_id = :pergunta_id'
            );
            $stmtAlternativas->bindValue(':pergunta_id', $perguntaId, PDO::PARAM_INT);
            $stmtAlternativas->execute();

            $stmtPergunta = $this->pdo->prepare(
                'DELETE FROM perguntas WHERE id = :pergunta_id'
            );
            $stmtPergunta->bindValue(':pergunta_id', $perguntaId, PDO::PARAM_INT);
            $stmtPergunta->execute();

            if ($stmtPergunta->rowCount() !== 1) {
                throw new RuntimeException('Questão não encontrada para exclusão.');
            }

            $this->pdo->commit();

        } catch (Throwable $erro) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $erro;
        }
    }

    public function buscarParaJogo(
        int $areaId,
        string $dificuldade,
        array $idsIgnorar = []
    ): ?array {

        $idsIgnorar = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $idsIgnorar),
                    static fn (int $id): bool => $id > 0
                )
            )
        );

        $sql = "
            SELECT id, pergunta, explicacao, dificuldade
            FROM perguntas
            WHERE area_id = :area_id
              AND dificuldade = :dificuldade
              AND ativo = 1
        ";

        if ($idsIgnorar !== []) {
            $marcadores = [];

            foreach ($idsIgnorar as $indice => $id) {
                $marcadores[] = ':ignorar_' . $indice;
            }

            $sql .= "\n              AND id NOT IN (" . implode(', ', $marcadores) . ')';
        }

        $sql .= "
            ORDER BY RAND()
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':area_id', $areaId, PDO::PARAM_INT);
        $stmt->bindValue(':dificuldade', $dificuldade, PDO::PARAM_STR);

        foreach ($idsIgnorar as $indice => $id) {
            $stmt->bindValue(':ignorar_' . $indice, $id, PDO::PARAM_INT);
        }

        $stmt->execute();

        $pergunta = $stmt->fetch();

        if (! $pergunta) {
            return null;
        }

        $stmtAlternativas = $this->pdo->prepare(" 
            SELECT letra, texto, correta
            FROM alternativas
            WHERE pergunta_id = :pergunta_id
            ORDER BY ordem ASC
        ");
        $stmtAlternativas->bindValue(':pergunta_id', (int) $pergunta['id'], PDO::PARAM_INT);
        $stmtAlternativas->execute();

        $pergunta['alternativas'] = $stmtAlternativas->fetchAll();

        return $pergunta;
    }
}
