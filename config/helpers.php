<?php
/**
 * Funções auxiliares do sistema
 */

/**
 * Valida CPF
 * @param string $cpf CPF para validar (com ou sem formatação)
 * @return bool Retorna true se o CPF é válido
 */
function validarCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/\D/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais (CPF inválido)
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    
    // Validação do primeiro dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += $cpf[$i] * (10 - $i);
    }
    $resto = 11 - ($soma % 11);
    $digito1 = ($resto >= 10) ? 0 : $resto;
    
    if ($digito1 != $cpf[9]) {
        return false;
    }
    
    // Validação do segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += $cpf[$i] * (11 - $i);
    }
    $resto = 11 - ($soma % 11);
    $digito2 = ($resto >= 10) ? 0 : $resto;
    
    if ($digito2 != $cpf[10]) {
        return false;
    }
    
    return true;
}

/**
 * Valida email
 * @param string $email Email para validar
 * @return bool Retorna true se o email é válido
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida telefone brasileiro
 * @param string $telefone Telefone para validar
 * @return bool Retorna true se o telefone é válido
 */
function validarTelefone($telefone) {
    $telefone = preg_replace('/\D/', '', $telefone);
    // Verifica se tem 10 ou 11 dígitos (com DDD)
    return strlen($telefone) >= 10 && strlen($telefone) <= 11;
}

/**
 * Valida CEP brasileiro
 * @param string $cep CEP para validar
 * @return bool Retorna true se o CEP é válido
 */
function validarCEP($cep) {
    $cep = preg_replace('/\D/', '', $cep);
    return strlen($cep) == 8;
}

/**
 * Formata CPF
 * @param string $cpf CPF para formatar
 * @return string CPF formatado (000.000.000-00)
 */
function formatarCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) != 11) return $cpf;
    return substr($cpf, 0, 3) . '.' . 
           substr($cpf, 3, 3) . '.' . 
           substr($cpf, 6, 3) . '-' . 
           substr($cpf, 9, 2);
}

/**
 * Formata telefone
 * @param string $telefone Telefone para formatar
 * @return string Telefone formatado
 */
function formatarTelefone($telefone) {
    $telefone = preg_replace('/\D/', '', $telefone);
    if (strlen($telefone) == 11) {
        return '(' . substr($telefone, 0, 2) . ') ' . 
               substr($telefone, 2, 5) . '-' . 
               substr($telefone, 7);
    } elseif (strlen($telefone) == 10) {
        return '(' . substr($telefone, 0, 2) . ') ' . 
               substr($telefone, 2, 4) . '-' . 
               substr($telefone, 6);
    }
    return $telefone;
}

/**
 * Formata CEP
 * @param string $cep CEP para formatar
 * @return string CEP formatado (00000-000)
 */
function formatarCEP($cep) {
    $cep = preg_replace('/\D/', '', $cep);
    if (strlen($cep) != 8) return $cep;
    return substr($cep, 0, 5) . '-' . substr($cep, 5);
}

/**
 * Sanitiza string
 * @param string $string String para sanitizar
 * @return string String sanitizada
 */
function sanitizar($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}
