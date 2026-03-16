<?php
/**
 * Controller: Venda
 * Coordena registro de vendas e dados do dashboard
 */

require_once __DIR__ . '/../models/Venda.php';
require_once __DIR__ . '/../models/Peca.php';

class VendaController {
    private $vendaModel;
    private $pecaModel;

    public function __construct() {
        $this->vendaModel = new Venda();
        $this->pecaModel = new Peca();
    }

    public function registrar($dados) {
        $cliente = trim($dados['cliente'] ?? '');
        $pecaId = (int) ($dados['peca_id'] ?? 0);
        $valor = $dados['valor'] ?? null;

        if ($cliente === '') {
            return ['success' => false, 'message' => 'Informe o nome da cliente.'];
        }

        if ($pecaId <= 0) {
            return ['success' => false, 'message' => 'Selecione uma peca disponivel.'];
        }

        if ($valor !== null && $valor !== '' && (!is_numeric($valor) || (float) $valor < 0)) {
            return ['success' => false, 'message' => 'Informe um valor de venda valido.'];
        }

        try {
            $vendaId = $this->vendaModel->registrarVenda(
                $cliente,
                $pecaId,
                $valor === '' ? null : (float) $valor
            );

            return ['success' => true, 'message' => 'Venda registrada com sucesso.', 'id' => $vendaId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function listarRecentes($limite = 50) {
        return $this->vendaModel->listarComPeca($limite);
    }

    public function pecasDisponiveis() {
        return $this->pecaModel->findDisponiveis();
    }

    public function estatisticas() {
        return $this->vendaModel->estatisticasDashboard();
    }

    public function vendasPorCategoria() {
        return $this->vendaModel->vendasPorCategoria();
    }
}
