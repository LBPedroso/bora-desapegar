<?php
/**
 * Controller: Peca
 * Coordena cadastro e manutencao de pecas
 */

require_once __DIR__ . '/../models/Peca.php';

class PecaController {
    private $pecaModel;

    public function __construct() {
        $this->pecaModel = new Peca();
    }

    public function listar($filtros = []) {
        return $this->pecaModel->findComFiltros($filtros);
    }

    public function buscar($id) {
        return $this->pecaModel->findById($id);
    }

    public function categorias() {
        return $this->pecaModel->listarCategorias();
    }

    public function criar($dados, $arquivoFoto = null) {
        $erro = $this->validarDados($dados);
        if ($erro) {
            return ['success' => false, 'message' => $erro];
        }

        $foto = $this->processarFoto($arquivoFoto, 'default.jpg');

        $payload = [
            'nome' => trim($dados['nome']),
            'categoria' => trim($dados['categoria']),
            'tamanho' => trim($dados['tamanho']),
            'preco' => (float) $dados['preco'],
            'foto' => $foto,
            'observacao' => trim($dados['observacao'] ?? ''),
            'status' => in_array(($dados['status'] ?? 'disponivel'), ['disponivel', 'vendido'], true) ? $dados['status'] : 'disponivel'
        ];

        $id = $this->pecaModel->create($payload);

        return ['success' => true, 'message' => 'Peca cadastrada com sucesso.', 'id' => $id];
    }

    public function atualizar($id, $dados, $arquivoFoto = null) {
        $pecaAtual = $this->pecaModel->findById($id);
        if (!$pecaAtual) {
            return ['success' => false, 'message' => 'Peca nao encontrada.'];
        }

        $erro = $this->validarDados($dados);
        if ($erro) {
            return ['success' => false, 'message' => $erro];
        }

        $fotoAtual = $pecaAtual['foto'] ?? 'default.jpg';
        $foto = $this->processarFoto($arquivoFoto, $fotoAtual);

        $payload = [
            'nome' => trim($dados['nome']),
            'categoria' => trim($dados['categoria']),
            'tamanho' => trim($dados['tamanho']),
            'preco' => (float) $dados['preco'],
            'foto' => $foto,
            'observacao' => trim($dados['observacao'] ?? ''),
            'status' => in_array(($dados['status'] ?? 'disponivel'), ['disponivel', 'vendido'], true) ? $dados['status'] : 'disponivel'
        ];

        $this->pecaModel->update($id, $payload);

        return ['success' => true, 'message' => 'Peca atualizada com sucesso.'];
    }

    public function excluir($id) {
        $peca = $this->pecaModel->findById($id);
        if (!$peca) {
            return ['success' => false, 'message' => 'Peca nao encontrada.'];
        }

        if (($peca['status'] ?? '') === 'vendido') {
            return ['success' => false, 'message' => 'Nao e possivel excluir uma peca vendida.'];
        }

        $this->pecaModel->delete($id);

        $foto = $peca['foto'] ?? 'default.jpg';
        if ($foto !== 'default.jpg') {
            $caminho = __DIR__ . '/../public/assets/img/pecas/' . $foto;
            if (file_exists($caminho)) {
                @unlink($caminho);
            }
        }

        return ['success' => true, 'message' => 'Peca excluida com sucesso.'];
    }

    private function validarDados($dados) {
        if (empty(trim($dados['nome'] ?? ''))) {
            return 'Informe o nome da peca.';
        }

        if (empty(trim($dados['categoria'] ?? ''))) {
            return 'Informe a categoria da peca.';
        }

        if (empty(trim($dados['tamanho'] ?? ''))) {
            return 'Informe o tamanho da peca.';
        }

        if (!isset($dados['preco']) || !is_numeric($dados['preco']) || (float) $dados['preco'] < 0) {
            return 'Informe um preco valido.';
        }

        return null;
    }

    private function processarFoto($arquivoFoto, $fotoAtual) {
        if (!$arquivoFoto || ($arquivoFoto['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $fotoAtual;
        }

        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extensao = strtolower(pathinfo($arquivoFoto['name'], PATHINFO_EXTENSION));

        if (!in_array($extensao, $extensoesPermitidas, true)) {
            return $fotoAtual;
        }

        if (($arquivoFoto['size'] ?? 0) > 5 * 1024 * 1024) {
            return $fotoAtual;
        }

        $nomeSeguro = preg_replace('/[^a-zA-Z0-9._-]/', '', $arquivoFoto['name']);
        $baseNome = pathinfo($nomeSeguro, PATHINFO_FILENAME);
        $baseNome = $baseNome !== '' ? $baseNome : 'foto';
        $novoNome = uniqid('peca_', true) . '_' . $baseNome . '.' . $extensao;
        $destino = __DIR__ . '/../public/assets/img/pecas/' . $novoNome;

        $salvouEnquadrada = $this->salvarImagemEnquadrada(
            $arquivoFoto['tmp_name'],
            $destino,
            1000,
            800,
            $extensao
        );

        if (!$salvouEnquadrada && !move_uploaded_file($arquivoFoto['tmp_name'], $destino)) {
            return $fotoAtual;
        }

        if ($fotoAtual !== 'default.jpg') {
            $caminhoAntigo = __DIR__ . '/../public/assets/img/pecas/' . $fotoAtual;
            if (file_exists($caminhoAntigo)) {
                @unlink($caminhoAntigo);
            }
        }

        return $novoNome;
    }

    private function salvarImagemEnquadrada($origemTemporaria, $destinoFinal, $larguraAlvo, $alturaAlvo, $extensao) {
        if (!function_exists('imagecreatetruecolor') || !function_exists('getimagesize')) {
            return false;
        }

        $info = @getimagesize($origemTemporaria);
        if (!$info || empty($info[0]) || empty($info[1])) {
            return false;
        }

        $tipo = (int) ($info[2] ?? 0);
        $imagemOrigem = $this->criarImagemAPartirDoTipo($origemTemporaria, $tipo);
        if (!$imagemOrigem) {
            return false;
        }

        if ($tipo === IMAGETYPE_JPEG) {
            $imagemOrigem = $this->corrigirOrientacaoJpeg($origemTemporaria, $imagemOrigem);
        }

        $larguraOrigem = imagesx($imagemOrigem);
        $alturaOrigem = imagesy($imagemOrigem);

        if ($larguraOrigem <= 0 || $alturaOrigem <= 0) {
            imagedestroy($imagemOrigem);
            return false;
        }

        $escala = max($larguraAlvo / $larguraOrigem, $alturaAlvo / $alturaOrigem);
        $larguraEscalada = (int) ceil($larguraOrigem * $escala);
        $alturaEscalada = (int) ceil($alturaOrigem * $escala);
        $origemX = (int) max(0, floor(($larguraEscalada - $larguraAlvo) / 2));
        $origemY = (int) max(0, floor(($alturaEscalada - $alturaAlvo) / 2));

        $imagemEscalada = imagecreatetruecolor($larguraEscalada, $alturaEscalada);
        $imagemFinal = imagecreatetruecolor($larguraAlvo, $alturaAlvo);

        $tiposComTransparencia = [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
        if (in_array($tipo, $tiposComTransparencia, true)) {
            imagealphablending($imagemEscalada, false);
            imagesavealpha($imagemEscalada, true);
            $transparenteEscalada = imagecolorallocatealpha($imagemEscalada, 0, 0, 0, 127);
            imagefilledrectangle($imagemEscalada, 0, 0, $larguraEscalada, $alturaEscalada, $transparenteEscalada);

            imagealphablending($imagemFinal, false);
            imagesavealpha($imagemFinal, true);
            $transparenteFinal = imagecolorallocatealpha($imagemFinal, 0, 0, 0, 127);
            imagefilledrectangle($imagemFinal, 0, 0, $larguraAlvo, $alturaAlvo, $transparenteFinal);
        }

        imagecopyresampled(
            $imagemEscalada,
            $imagemOrigem,
            0,
            0,
            0,
            0,
            $larguraEscalada,
            $alturaEscalada,
            $larguraOrigem,
            $alturaOrigem
        );

        imagecopyresampled(
            $imagemFinal,
            $imagemEscalada,
            0,
            0,
            $origemX,
            $origemY,
            $larguraAlvo,
            $alturaAlvo,
            $larguraAlvo,
            $alturaAlvo
        );

        $salvou = $this->salvarImagemPorExtensao($imagemFinal, $destinoFinal, $extensao);

        imagedestroy($imagemOrigem);
        imagedestroy($imagemEscalada);
        imagedestroy($imagemFinal);

        return $salvou;
    }

    private function criarImagemAPartirDoTipo($arquivo, $tipo) {
        if ($tipo === IMAGETYPE_JPEG && function_exists('imagecreatefromjpeg')) {
            return @imagecreatefromjpeg($arquivo);
        }
        if ($tipo === IMAGETYPE_PNG && function_exists('imagecreatefrompng')) {
            return @imagecreatefrompng($arquivo);
        }
        if ($tipo === IMAGETYPE_GIF && function_exists('imagecreatefromgif')) {
            return @imagecreatefromgif($arquivo);
        }
        if ($tipo === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) {
            return @imagecreatefromwebp($arquivo);
        }

        return false;
    }

    private function salvarImagemPorExtensao($imagem, $destino, $extensao) {
        if ($extensao === 'jpg' || $extensao === 'jpeg') {
            return function_exists('imagejpeg') ? @imagejpeg($imagem, $destino, 88) : false;
        }
        if ($extensao === 'png') {
            return function_exists('imagepng') ? @imagepng($imagem, $destino, 6) : false;
        }
        if ($extensao === 'gif') {
            return function_exists('imagegif') ? @imagegif($imagem, $destino) : false;
        }
        if ($extensao === 'webp') {
            return function_exists('imagewebp') ? @imagewebp($imagem, $destino, 85) : false;
        }

        return false;
    }

    private function corrigirOrientacaoJpeg($arquivo, $imagem) {
        if (!function_exists('exif_read_data') || !function_exists('imagerotate')) {
            return $imagem;
        }

        $dadosExif = @exif_read_data($arquivo);
        $orientacao = (int) ($dadosExif['Orientation'] ?? 1);

        if ($orientacao === 3) {
            $rotacionada = @imagerotate($imagem, 180, 0);
            if ($rotacionada) {
                imagedestroy($imagem);
                return $rotacionada;
            }
        }

        if ($orientacao === 6) {
            $rotacionada = @imagerotate($imagem, -90, 0);
            if ($rotacionada) {
                imagedestroy($imagem);
                return $rotacionada;
            }
        }

        if ($orientacao === 8) {
            $rotacionada = @imagerotate($imagem, 90, 0);
            if ($rotacionada) {
                imagedestroy($imagem);
                return $rotacionada;
            }
        }

        return $imagem;
    }
}
